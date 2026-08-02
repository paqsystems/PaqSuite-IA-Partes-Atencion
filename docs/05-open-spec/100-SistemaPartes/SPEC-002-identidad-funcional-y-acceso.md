# SPEC-002 – Identidad funcional y acceso al módulo

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-002 |
| Título | Identidad funcional y acceso al módulo Sistema Partes |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-07-30 |
| HU relacionada(s) | [HU-002-identidad-funcional-y-acceso](../../03-historias-usuario/100-SistemaPartes/HU-002-identidad-funcional-y-acceso.md) |
| TR relacionada(s) | [TR-002-identidad-funcional-y-acceso](../../04-tareas/100-SistemaPartes/TR-002-identidad-funcional-y-acceso.md) |
| Depende de | [SPEC-001-modelo-datos-modulo](./SPEC-001-modelo-datos-modulo.md) |
| Fuentes | [`02-actores-identidad-y-acceso.md`](../../02-producto/Sistema-Partes-IA/02-actores-identidad-y-acceso.md), [`01-vision-y-alcance.md`](../../02-producto/Sistema-Partes-IA/01-vision-y-alcance.md); Framework `docs/02-producto/04-login-autenticacion-autorizacion.md` §2 (gate de negocio) |

---

## 1. Resumen ejecutivo

- **Problema:** el login Framework autentica contra `users`, pero el módulo necesita saber si esa identidad es **asistente** (con o sin supervisión) o **cliente con acceso**, y delimitar el universo de datos antes del shell operativo.
- **Resultado esperado:** un **gate de negocio post-credenciales** + **contexto funcional de sesión** (y perfil de solo lectura) que habiliten o denieguen el circuito operativo de Partes sin redefinir login GEN.

---

## 2. Alcance

### 2.1 En alcance

- Resolución funcional tras auth GEN OK: lookup por `users.id` → `PQ_PARTES_USUARIOS.user_id` o `PQ_PARTES_CLIENTES.user_id` (SPEC-001 R-MD-09).
- Validación de registros de dominio: `activo = 1` y `inhabilitado = 0`.
- Exclusividad: si el mismo `users.id` aparece en ambas tablas → configuración inconsistente → denegar acceso operativo.
- Gate de producto (`PostLoginBusinessGate` del host): fallo → **no** sesión operativa usable para Partes.
- Enriquecimiento del `resultado` de login / `GET /api/v1/auth/me` con el contexto funcional mínimo (campos §4.4).
- Delimitación primaria de universo de datos por tipo funcional (cliente / asistente / supervisor) — contrato para consultas y operación posteriores.
- Perfil visible de **solo lectura** del usuario reconocido.
- Contrato de mensajes envelope / i18n de denegación (no reutilizar `auth.invalidCredentials` si la password fue correcta).

### 2.2 Fuera de alcance

- Redefinir login, logout, forgot/reset, `firstLogin`, Sanctum, tenancy headers (Framework GEN).
- ABM de asistentes/clientes y alta de acceso (SPEC maestros).
- Carga diaria, supervisión masiva, consultas, dashboard, mobile (SPECs posteriores).
- Roles/permisos Framework (`pq_roles` / menú GEN) salvo coexistencia: el menú GEN puede ocultar pantallas, pero **no** sustituye la delimitación funcional de este SPEC.
- Detalle de revocación de acceso al cliente en **nuevo** login (SPEC-003); efecto en sesión viva: ver §4.8.
- Edición de perfil por el usuario final.

---

## 3. Actores y contexto

| Actor funcional | Condición de ingreso | Universo de datos (capa 1) |
|-----------------|----------------------|----------------------------|
| Asistente | Fila en `PQ_PARTES_USUARIOS` vinculada, activa y no inhabilitada | Propia actividad (`usuario_id` = su id) |
| Supervisor | Asistente con `supervisor = 1` | Universo supervisor del módulo (tareas de terceros, etc.; detalle en SPEC supervisión) |
| Cliente | Fila en `PQ_PARTES_CLIENTES` con `user_id` no nulo, activa y no inhabilitada | Solo su organización (`cliente_id` = su id) |
| Solo Framework (`users` sin vínculo dominio) | **Denegado** al circuito Partes | — |

Término preferido: **asistente** (sinónimo histórico: empleado).

Precondiciones: tablas SPEC-001 desplegadas; auth GEN operativo.

---

## 4. Comportamiento funcional

### 4.1 Pipeline (orden)

1. Middleware instalación (`X-Paq-Cliente` + proyecto) — Framework.
2. Validación credenciales `users` (activo / no inhabilitado / password) — Framework.
3. **Gate Partes** (este SPEC): resolver identidad funcional.
4. Si gate OK → emitir token / armar `resultado` enriquecido (incluye `firstLogin`); si falla → 403 + clave i18n de producto (sin token operativo Partes).
5. **Post-login FE (criterio PedidosWeb / Framework GEN):** si `firstLogin = true` → pantalla **cambio de contraseña** obligatorio **antes** del shell / dashboard / selector de empresa (si multi). Solo tras completar el cambio (o si `firstLogin = false`) → Dashboard Partes (Inicio).

**Alineación productos:** PedidosWeb aplica gate de perfil comercial en el armado de sesión de login y luego, en FE, `firstLogin` → `/change-password` antes del home. Framework documenta el mismo orden respecto del selector de empresas (patrón citado como Tango). No se halló repo de producto Tango aparte en el workspace; se adopta **PedidosWeb**.

El host ya expone el gancho `PostLoginBusinessGate`; la implementación NoOp actual **no** cumple este SPEC y debe sustituirse en la TR correspondiente.

### 4.2 Algoritmo de resolución

Entrada: `users.id` autenticado.

1. Buscar asistente: `PQ_PARTES_USUARIOS` donde `user_id = users.id` y `activo = 1` y `inhabilitado = 0`.
2. Buscar cliente: `PQ_PARTES_CLIENTES` donde `user_id = users.id` y `activo = 1` y `inhabilitado = 0`.
3. Resultados:
   - **Ambos** encontrados → inconsistencia (R-ID-04) → **denegar**.
   - **Solo asistente** → `tipoFuncional = asistente`; `esSupervisor = (supervisor == 1)`; `asistenteId = id`; `clienteId = null`.
   - **Solo cliente** → `tipoFuncional = cliente`; `esSupervisor = false`; `clienteId = id`; `asistenteId = null`.
   - **Ninguno** (o solo filas inactivas/inhabilitadas) → **denegar**.

Fuente de `esSupervisor`: **solo** `PQ_PARTES_USUARIOS.supervisor` (SPEC-001 R-MD-08). Ignorar `users.supervisor` legado.

### 4.3 Denegación (gate)

| Situación | HTTP | `error` | `respuesta` (i18n) |
|-----------|------|---------|-------------------|
| Sin perfil funcional / inactivo / inhabilitado | 403 | 3003 | `partes.auth.noFunctionalProfile` |
| Doble vínculo asistente+cliente | 403 | 3003 | `partes.auth.inconsistentProfile` |

- **No** usar `auth.invalidCredentials` (3002) si la contraseña fue válida.
- Mensaje claro al usuario, sin filtrar datos sensibles de otras cuentas.
- No emitir token de sesión usable para el shell Partes (si el Framework ya emitió pasos internos, el login debe fallar antes de devolver éxito con token — alineado al gancho actual del host que lanza antes de `createToken`).

### 4.4 Contexto funcional en sesión / API

Debe estar disponible tras login OK y en `GET /api/v1/auth/me` dentro de `resultado`, coexistiendo con campos GEN (`token` solo en login, `user`, `tenancy`, `db`, `firstLogin`, `empresas`, …).

**Forma canónica:** objeto anidado `resultado.partes` (evita colisión con campos GEN):

```json
"partes": {
  "tipoFuncional": "asistente",
  "asistenteId": 1,
  "clienteId": null,
  "esSupervisor": true,
  "code": "A001",
  "nombre": "Ana Pérez",
  "email": "ana@ejemplo.com"
}
```

| Campo (camelCase) | Tipo | Descripción |
|-------------------|------|-------------|
| `tipoFuncional` | string | `"asistente"` \| `"cliente"` |
| `asistenteId` | int \| null | `PQ_PARTES_USUARIOS.id` si asistente |
| `clienteId` | int \| null | `PQ_PARTES_CLIENTES.id` si cliente |
| `esSupervisor` | boolean | solo meaningful si asistente; si cliente → `false` |
| `code` | string | `code` funcional del registro de dominio |
| `nombre` | string | nombre visible del registro de dominio |
| `email` | string \| null | Email del registro de dominio (perfil R-ID-09); omitir o null si no hay |

El FE/shell usa `resultado.partes` para filtros, dashboard y habilitación de acciones; los SPEC de operación/consultas lo consumen sin redefinirlo.

### 4.5 Perfil de solo lectura

- Acceso desde el **menú del avatar del shell** (panel/modal); **sin** ruta/menú propio de “Mi perfil” en MVP.
- Muestra (solo lectura):
  - tipo funcional;
  - código funcional de dominio (`code`);
  - nombre;
  - flag supervisor si aplica;
  - **email** del registro de dominio (si existe; si NULL, ocultar o “—”);
  - **código de usuario Framework** (`users.usuario` / login).
- No es ABM de seguridad Framework.

### 4.6 Delimitación primaria de datos (contrato)

| `tipoFuncional` | Regla de universo |
|-----------------|-------------------|
| `cliente` | Solo filas cuyo `cliente_id` = `clienteId` de sesión |
| `asistente` y `esSupervisor = false` | Solo filas cuyo `usuario_id` = `asistenteId` de sesión (salvo pantallas futuras que el SPEC declare sin ese filtro) |
| `asistente` y `esSupervisor = true` | Universo supervisor (sin filtro por propietario propio); detalle de procesos en SPEC supervisión |

Los permisos de menú Framework pueden ocultar rutas; **no** reemplazan esta capa.

### 4.8 Revalidación en sesión viva (decisión A1)

Si tras el login el perfil deja de ser usable (revocación `user_id` en cliente, inhabilitación, inconsistencia, etc.):

- En **cada** request a APIs de dominio Partes **y** en `GET /api/v1/auth/me`: re-ejecutar la resolución §4.2.
- Si ya no hay perfil usable → **403** + `partes.auth.noFunctionalProfile` (o `inconsistentProfile` si aplica); el FE debe forzar re-login / limpiar sesión.
- No alcanza con confiar solo en el token Sanctum emitido al login.

### 4.9 Reglas numeradas

| ID | Regla |
|----|--------|
| R-ID-01 | Auth GEN primero; gate Partes después de credenciales OK; luego FE: `firstLogin` → change-password antes del shell (criterio PedidosWeb). |
| R-ID-02 | Resolución por `user_id` = `users.id`, no por igualdad de códigos. |
| R-ID-03 | Dominio usable solo si `activo` y no `inhabilitado`. |
| R-ID-04 | Exclusividad asistente vs cliente; doble vínculo → denegar. |
| R-ID-05 | Sin vínculo de dominio → denegar circuito Partes. |
| R-ID-06 | `esSupervisor` solo desde `PQ_PARTES_USUARIOS.supervisor`. |
| R-ID-07 | Fallo de gate: 403 / 3003 / claves `partes.auth.*` (no 3002). |
| R-ID-08 | Login/`me` exponen contexto §4.4 en `resultado.partes`. |
| R-ID-09 | Perfil MVP de solo lectura, vía panel/modal del avatar (sin ruta dedicada); campos: tipo, `code`, nombre, supervisor si aplica, email dominio, `users.usuario`. |
| R-ID-10 | Delimitación §4.6 es capa primaria para APIs de dominio posteriores. |
| R-ID-11 | Revalidar perfil usable en `/auth/me` y en **toda** API de dominio; fallo → 403 y re-login. |

---

## 5. Criterios verificables

- [ ] Tras credenciales OK, usuario sin fila de dominio activa → 403 + `partes.auth.noFunctionalProfile` y sin token de éxito.
- [ ] Usuario solo asistente activo → login 200 con `tipoFuncional=asistente` y `asistenteId` correcto; `esSupervisor` refleja la columna de dominio.
- [ ] Usuario solo cliente con `user_id` activo → login 200 con `tipoFuncional=cliente` y `clienteId` correcto; `esSupervisor=false`.
- [ ] Usuario en ambas tablas → 403 + `partes.auth.inconsistentProfile`.
- [ ] Asistente/cliente inhabilitado o inactivo → denegado como sin perfil usable.
- [ ] `GET /auth/me` devuelve el mismo contexto funcional que el login.
- [ ] Perfil UI: panel/modal desde avatar, solo lectura: tipo, `code`, nombre, supervisor si aplica, email dominio, `users.usuario`.
- [ ] No se lee `users.supervisor` para el flag de supervisión.
- [ ] i18n: claves `partes.auth.noFunctionalProfile` y `partes.auth.inconsistentProfile` presentes (locales del producto).
- [ ] Tras revocar/`inhabilitar` con token aún válido: `/auth/me` y APIs de dominio responden 403 (no siguen operando).

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | Implementar `PostLoginBusinessGate` real (reemplazar NoOp); resolver vía SP MUST; enriquecer login/`me`; revalidación R-ID-11. |
| Envelope | Reutilizar 3003; nuevas claves `respuesta` de producto. |
| Frontend | Consumir contexto en session store; perfil lectura vía avatar; mensajes de gate. |
| Seed | Vincular **`admin`** y **`PQ`** a filas `PQ_PARTES_USUARIOS` con `supervisor = 1` **y** asignarles rol Framework **supervisor** (permisos ABM + menú seguridad GEN). Asistentes comunes y clientes con acceso se crean vía maestros / seguridad; no van en seed Must. |
| Tests | Feature: casos §5 (sin perfil, asistente, supervisor, cliente, inconsistente, revalidación mid-session). |

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| Revocar acceso / inhabilitar con sesión viva | **Cerrado:** revalidar en `/auth/me` + APIs de dominio (R-ID-11). |
| Campo “empresa” en mobile | Cerrado en SPEC-007 (`X-Paq-Cliente`). |
| Usuarios solo GEN (ops) | No entran al circuito Partes salvo que tengan fila de dominio. |
| Menú GEN vs delimitación | Coexisten; delimitación §4.6 manda en datos. |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Versión inicial (Parte A) — gate + contexto sesión + perfil lectura. |
| 2026-07-30 | A1: apto con observaciones (forma `resultado.partes`; revocación mid-session diferida). |
| 2026-07-30 | A1 cierre: sesión viva = revalidar en `/me` + APIs dominio (R-ID-11). |
| 2026-07-30 | A1 cierre: perfil = panel/modal desde avatar (sin ruta propia). |
| 2026-07-30 | Batch HU: seed `admin`+`PQ` como supervisores Partes. |
| 2026-07-30 | Batch HU: perfil = mínimo + email dominio + `users.usuario`. |
| 2026-07-30 | Batch HU: orden post-login = PedidosWeb (gate → token → firstLogin/change-password → shell). |
| 2026-07-30 | Parte C+C1: enlazada [TR-002](../../04-tareas/100-SistemaPartes/TR-002-identidad-funcional-y-acceso.md). |

---

**Trazabilidad:** HU/TR en Partes B/C. Siguiente SPEC de dominio previsto: **SPEC-003 maestros y catálogos**.
