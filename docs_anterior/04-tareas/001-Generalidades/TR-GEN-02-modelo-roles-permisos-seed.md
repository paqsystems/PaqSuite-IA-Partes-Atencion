# TR-GEN-02-modelo-roles-permisos-seed — Modelo de roles/permisos y seed MVP

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-modelo-roles-permisos-seed](../../03-historias-usuario/001-Generalidades/HU-GEN-02-modelo-roles-permisos-seed.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | Base de seguridad del bloque (primera TR del orden); prerequisito operativo: seed de `pq_menus` ([TR-GEN-01-menu-general-sidebar](TR-GEN-01-menu-general-sidebar.md)) |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-05-29 (D implementado) |

**Origen:** [HU-GEN-02-modelo-roles-permisos-seed](../../03-historias-usuario/001-Generalidades/HU-GEN-02-modelo-roles-permisos-seed.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Titulo
Sembrar roles, permisos y usuarios iniciales del MVP de forma idempotente y trazable.

### Narrativa
Como equipo tecnico, queremos contar con seeds reproducibles para autenticar y autorizar usuarios MVP sin ABM de seguridad en UI.

### In scope / Out of scope
- In scope: seed de `Pq_Rol`, `Pq_Permiso`, `PQ_RolAtributo`, usuarios de prueba (incl. casos negativos login) y sincronizacion de matriz de permisos MVP.
- In scope: comando de ejecucion para dev/CI, orden de ejecucion con seed de menu y validaciones de idempotencia.
- Out of scope: ABM UI de usuarios/roles/permisos, tabla pivote rol-permiso (no existe en MONO), columna `IDEmpresa` en `Pq_Permiso`, politicas avanzadas (2FA, hardening).

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Existe un comando artisan documentado para poblar seguridad MVP.
- **AC-02**: El seed crea/actualiza `Pq_Rol` y `Pq_Permiso` sin duplicados.
- **AC-03**: Se generan usuarios de prueba cliente, vendedor, supervisor **y casos negativos** (`usuario.sinPermiso.mvp`, `usuario.sinVinculo.mvp`, `vendedor.sinMenu.mvp`).
- **AC-04**: Se **completa/sincroniza** `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` (archivo borrador ya existente).
- **AC-05**: Reejecutar seed conserva integridad e idempotencia.
- **AC-06**: Rol **Supervisor** con `AccesoTotal = true` en **`Pq_Rol`** (`supervisor.mvp`); rol **Vendedor acotado** con `AccesoTotal = false` + filas `PQ_RolAtributo` (`vendedor.acotado.mvp`).
- **AC-07**: Vinculo comercial `cod_login` en `PQ_PARTES_CLIENTES` / `PQ_PARTES_USUARIOS` alineado a cada usuario seed que lo requiera.
- **AC-08**: Tras seed, login de cliente/vendedor/supervisor resuelve perfil en bootstrap ([TR-GEN-02-login-sesion](TR-GEN-02-login-sesion.md)).
- **AC-09**: Si upsert detecta la misma clave natural con datos inconsistentes (conflicto no reconciliable), el seeder **falla explicitamente** con mensaje claro (no silencia ni duplica).

### Escenarios Gherkin

```gherkin
Feature: Seed de seguridad MVP

  Scenario: Ejecucion inicial
    Given una base de datos con pq_menus sembrado
    When se ejecuta php artisan paqsuite:seed-seguridad-mvp
    Then existen roles Cliente, Vendedor y Supervisor en Pq_Rol
    And existen asignaciones Pq_Permiso para usuarios de prueba

  Scenario: Reejecucion idempotente
    Given una base ya sembrada
    When se ejecuta nuevamente el comando de seed
    Then no se duplican filas en Pq_Rol ni Pq_Permiso

  Scenario: Usuario de prueba puede autenticar
    Given el seed ejecutado
    When el usuario vendedor.acotado.mvp inicia sesion
    Then obtiene token y Pq_Permiso vigente

  Scenario: Matriz documentada
    Given el seed ejecutado
    Then matriz-permisos-mvp.md refleja rol ↔ capacidades menu del seed

  Scenario: Conflicto de clave natural
    Given una fila Pq_Rol con NombreRol "Supervisor" y AccesoTotal false
    When se ejecuta el seed esperando AccesoTotal true
    Then el comando falla con error explicito de conflicto
```

---

## 3) Reglas de Negocio

1. **RN-01**: `Pq_Rol` (definicion + `AccesoTotal`) y `Pq_Permiso` (asignacion usuario→rol) son fuente de autorizacion MVP.
2. **RN-02**: Un usuario de prueba de camino feliz representa exactamente un perfil funcional (cliente o vendedor o supervisor).
3. **RN-03**: La matriz `matriz-permisos-mvp.md` debe reflejar los mismos permisos cargados por seed.
4. **RN-04**: Sin fila valida en `Pq_Permiso`, el login no habilita operacion posterior.
5. **RN-05**: Rol **Supervisor** en seed lleva **`AccesoTotal = true` en `Pq_Rol`** (smoke menu completo).
6. **RN-06**: Rol **Vendedor** en seed lleva **`AccesoTotal = false` en `Pq_Rol`**; acceso a menu via `PQ_RolAtributo` granulares.
7. **RN-07**: Usuario seed de camino feliz debe tener `Pq_Permiso` **y** `cod_login` comercial; si falta vinculo → login 403 (`auth.noCommercialProfile`, D-01).
8. **RN-08**: Un login vincula **solo** cliente **o** vendedor comercial, nunca ambos (producto §7.2).
9. **RN-09**: No existe tabla pivote rol-permiso; la relacion es usuario→rol directa en `Pq_Permiso`.
10. **RN-10**: En MONO, `Pq_Permiso` **no usa** columna `IDEmpresa` (una asignacion por usuario).

---

## 4) Impacto en Datos

### Tablas afectadas
- `Pq_Rol`, `Pq_Permiso`, `PQ_RolAtributo`
- `users`
- `PQ_PARTES_CLIENTES`, `PQ_PARTES_USUARIOS` (campo `cod_login`, flag `supervisor`)

### 4.1 Esquema y claves naturales (upsert)

Referencia conceptual: [`seguridad-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/seguridad-permisos.md).

| Tabla | Clave natural upsert | Columnas relevantes seed |
|-------|----------------------|---------------------------|
| `Pq_Rol` | `NombreRol` | `DescripcionRol`, **`AccesoTotal`** (bool) |
| `Pq_Permiso` | `users.id` (o `CodigoUsuario` segun esquema fisico) | `IDRol` → `Pq_Rol.IDRol`. **Sin `IDEmpresa` en MONO.** |
| `PQ_RolAtributo` | `(IDRol, procedimiento)` o `(IDRol, menuKey)` segun esquema | `Permiso_Alta`, `Permiso_Baja`, `Permiso_Modi`, `Permiso_Repo` |
| `users` | `codigo` | `name`, `email`, `password_hash`, `activo`, `inhabilitado`, `first_login`, `locale`, `theme` |
| `PQ_PARTES_CLIENTES` | `cod_login` | vinculo a usuario cliente |
| `PQ_PARTES_USUARIOS` | `cod_login` | `supervisor` (bool), vinculo a usuario vendedor/supervisor |

**Notas de implementacion:**
- No crear tabla pivote rol-permiso.
- `AccesoTotal` se lee siempre desde **`Pq_Rol`**, no desde `Pq_Permiso`.
- Contraseña inicial de usuarios seed: variable de entorno **`SEED_MVP_PASSWORD`** (documentar en `.env.example`; no commitear secretos).

### 4.2 Mapeo `Pq_Rol` ↔ perfil funcional (cerrado en esta TR)

| `Pq_Rol.NombreRol` (clave seed) | `Pq_Rol.AccesoTotal` | `functionalProfile` (login `sessionContext`) | Perfil SPEC §7.3 |
|---------------------------------|----------------------|-----------------------------------------------|------------------|
| `Cliente` | false | `cliente` | Cliente |
| `VendedorAcotado` | false | `vendedor` | Vendedor (menú acotado) |
| `Supervisor` | true | `supervisor` | Supervisor |

**Que queda por definir en otras TR (no bloquea este seed):**

| Tema | TR responsable | Detalle pendiente |
|------|----------------|-------------------|
| Codigos `procedimiento` / `menuKey` en `PQ_RolAtributo` | TR-GEN-01-menu-general-sidebar + TR-GEN-02-autorizacion-menu-api | Valores exactos alineados al seed de los 11 `pq_menus` |
| Contenido API del dashboard acotado | TR-GEN-02-visibilidad-datos-SistemaPartes | KPIs en pesos del mes en curso (ver §4.3) |
| Permisos operativos por endpoint (`Permiso_Repo`, etc.) | TR-GEN-02-politicas-endpoints | Matriz endpoint ↔ atributo |
| Reglas SQL de visibilidad por cartera | TR-GEN-02-visibilidad-datos-SistemaPartes | Filtros cliente/vendedor/supervisor |

### 4.3 Orden de ejecucion de seeds

Ejecutar **en este orden** en dev/CI:

| Paso | Comando | TR origen |
|------|---------|-----------|
| 1 | `php artisan paqsuite:seed-menus-mvp` | TR-GEN-01-menu-general-sidebar |
| 2 | `php artisan paqsuite:seed-seguridad-mvp` | Esta TR |

Si `pq_menus` no tiene los 11 items MVP, `paqsuite:seed-seguridad-mvp` debe **fallar con mensaje explicito** indicando ejecutar primero el seed de menu.

### 4.4 Roles seed

| `Pq_Rol.NombreRol` | `AccesoTotal` | Uso |
|--------------------|---------------|-----|
| `Cliente` | false | Perfil cliente; menu/atributos minimos |
| `Vendedor` | false | Perfil vendedor sin atributos de menú |
| `VendedorAcotado` | false | Vendedor con `PQ_RolAtributo` (subconjunto §4.5) |
| `Supervisor` | **true** | Menu MVP completo (11 items habilitados) |

### 4.5 Subconjunto menu — rol Vendedor acotado (`vendedor.acotado.mvp`)

Coordinado con [TR-GEN-01-menu-general-sidebar](TR-GEN-01-menu-general-sidebar.md). Filas `PQ_RolAtributo` solo para:

| Item menu MVP (SPEC §8) | Incluido |
|-------------------------|----------|
| Carga pedidos/presupuestos | Si |
| Presupuestos ingresados | Si |
| Pedidos ingresados | Si |
| **Dashboard** | **Si** — ver contenido abajo |
| Pedidos pendientes (pantalla) | No |
| Deuda / cheques / historial / stock / tratativas / logs | No |

**Dashboard acotado (contenido funcional de referencia para TR visibilidad):** totales en **pesos (ARS)** del periodo **mes en curso** (inicialmente):

1. Presupuestos abiertos (monto total).
2. Pedidos ingresados (monto total).
3. Pedidos pendientes (monto total).
4. Cliente con presupuestos abiertos mas altos.
5. Cliente con pedidos ingresados mas altos.

> Este seed solo garantiza **acceso al item Dashboard** en menu; la API de datos la implementa TR-GEN-02-visibilidad-datos-SistemaPartes.

### 4.6 Usuarios seed (tabla ampliada)

Contraseña comun dev/CI: valor de **`SEED_MVP_PASSWORD`** (ej. en `.env.example`).

| `users.codigo` | `Pq_Rol` | `Pq_Permiso` | `cod_login` comercial | Tabla comercial | `supervisor` | `activo` | `inhabilitado` | `first_login` | `locale` | `theme` | Objetivo test |
|----------------|----------|--------------|----------------------|-----------------|--------------|----------|----------------|---------------|--------|---------|---------------|
| `cliente.mvp` | Cliente | Si | `CLI-MVP-001` | `PQ_PARTES_CLIENTES` | — | true | false | false | `es-AR` | `light` | Login OK perfil cliente |
| `vendedor.acotado.mvp` | VendedorAcotado | Si | `VEN-ACOT-MVP` | `PQ_PARTES_USUARIOS` | false | true | false | false | `es-AR` | `light` | Menu parcial + dashboard acotado |
| `supervisor.mvp` | Supervisor | Si | `VEN-SUP-MVP` | `PQ_PARTES_USUARIOS` | true | true | false | false | `es-AR` | `light` | AccesoTotal + menu completo |
| `usuario.sinPermiso.mvp` | — | **No** | — | — | — | true | false | false | `es-AR` | `light` | Login **403** `auth.noPermission` |
| `usuario.sinVinculo.mvp` | Vendedor | Si | — | sin fila | — | true | false | false | `es-AR` | `light` | Login **403** `auth.noCommercialProfile` |
| `vendedor.sinMenu.mvp` | Vendedor | Si | `VEN-SINMENU-MVP` | `PQ_PARTES_USUARIOS` | false | true | false | false | `es-AR` | `light` | Login OK; menu vacio (sin `PQ_RolAtributo`) |

**Coordinacion:** atributos menu compartidos con [TR-GEN-01-menu-general-sidebar](TR-GEN-01-menu-general-sidebar.md); casos 403 compartidos con [TR-GEN-02-login-sesion](TR-GEN-02-login-sesion.md).

---

## 5) Contratos de API y OpenAPI

> **Esta TR no agrega ni modifica endpoints.** Los contratos siguientes son referencia para seeds y tests de integracion con TR login/menu.

### 5.1 Endpoints del slice (referencia)

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| POST | `/api/v1/auth/login` | No (publico) | N/A | Si |
| GET | `/api/v1/user/menu` | Bearer Sanctum + `X-Paq-Cliente` | `Pq_Permiso` + `Pq_Rol` / `PQ_RolAtributo` | No |

### 5.2 Detalle por operacion (referencia)

#### POST `/api/v1/auth/login`

**Autorizacion:** publica (sin `security` en OpenAPI).

**Request:**

```json
{ "codigo": "vendedor.acotado.mvp", "password": "<SEED_MVP_PASSWORD>" }
```

Header **`X-Paq-Cliente`:** tenant stub (ej. `desarrollo`).

**Response 200:** envelope `error` / `respuesta` / `resultado` con token y `sessionContext`.

**Response 401:** credenciales invalidas (`auth.invalidCredentials`).

**Response 403:** sin `Pq_Permiso` (`auth.noPermission`) o sin `cod_login` (`auth.noCommercialProfile`).

#### GET `/api/v1/user/menu`

**Autorizacion:** sesion valida; filtro segun `Pq_Rol.AccesoTotal` y `PQ_RolAtributo`.

**Response 200:** envelope con arbol de menu autorizado.

**Response 401:** no autenticado.

**Response 403:** autenticado sin permiso (si aplica politica del slice menu).

### 5.3 Actualizacion matriz permisos

- [ ] **Completar/sincronizar** `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` (borrador existente).
- [ ] Cargar filas endpoint ↔ permiso para auth/menu base del MVP.
- [ ] Sincronizar matriz con seeds y OpenAPI en slices dependientes.

---

## 6) Cambios Frontend

### Pantallas / componentes
- Sin cambios directos obligatorios en esta TR.
- Impacto indirecto: login y sidebar consumen permisos sembrados.

### data-testid sugeridos
- `seed-security-status`
- `seed-user-role`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T0 | Backend | Validar prerequisito `pq_menus` (11 items) antes de seguridad | Fallo explicito si falta seed menu |
| T1 | Backend | Crear comando `php artisan paqsuite:seed-seguridad-mvp` | Ejecuta sin errores tras seed menu |
| T2 | Backend | Seed `Pq_Rol` (AccesoTotal) + `Pq_Permiso` | Upsert por claves §4.1; sin IDEmpresa |
| T3 | Backend | Usuarios §4.6 + `cod_login` comercial | 6 usuarios incl. negativos |
| T3b | Backend | `PQ_RolAtributo` acotado + supervisor | Subconjunto §4.5 |
| T4 | Docs | Completar `matriz-permisos-mvp.md` | Alineada con seed |
| T5 | Tests | Idempotencia + conflictos AC-09 + login smoke | AC-03–AC-08 |

---

## 8) Estrategia de Tests

- **Unit:** validaciones del seeder (upsert, normalizacion de codigos, conflicto AC-09).
- **Integration:** ejecucion real del comando tras `paqsuite:seed-menus-mvp`.
- **E2E:** smoke login por perfiles felices; 403 para `usuario.sinPermiso.mvp` y `usuario.sinVinculo.mvp` (coord. TR login).

---

## 9) Riesgos y Edge Cases

- Divergencia entre codigos de `PQ_RolAtributo` y `menuKey`/`procedimiento` del seed menu.
- Reejecucion en ambientes con datos parciales heredados.
- Ejecutar seed seguridad antes que seed menu (mitigado por T0).

---

## 10) Checklist final

### Checklist del slice
- [x] AC cumplidos (AC-08 login smoke → TR-GEN-02-login-sesion)
- [x] Seed y matriz planificados
- [x] Dependencias de login desbloqueadas

### Checklist normas transversales

> **N/A parcial** — esta TR no introduce endpoints. Aplicar en TR login, menu y politicas.

- [x] ~~Endpoints nuevos/modificados con policy en codigo~~ **N/A** (sin endpoints en este slice)
- [x] Matriz endpoint ↔ permiso **completada/sincronizada** (§5.3)
- [x] ~~OpenAPI en `/api/documentation` coherente~~ **N/A** aqui; slices dependientes
- [x] ~~401/403 documentados por operacion~~ **N/A** aqui
- [x] ~~Envelope JSON respetado~~ **N/A** aqui
- [x] ~~`X-Paq-Cliente` documentado~~ **N/A** aqui
- [x] ~~Tests API 401/403~~ **N/A** aqui; referencia §5.2 para seeds login
- [x] Sin ampliacion de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

### Backend
- Comando/seeder `paqsuite:seed-seguridad-mvp`.
- Variable `SEED_MVP_PASSWORD` en `.env.example`.

### Frontend
- Sin cambios directos.

### OpenAPI
- Sin cambios en este slice (auth/menu en TR dependientes).

### Docs
- `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` (completar/sincronizar)










