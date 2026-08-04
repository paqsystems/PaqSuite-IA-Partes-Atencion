# TR-002 – Identidad funcional y acceso al módulo

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-002-identidad-funcional-y-acceso](../../03-historias-usuario/100-SistemaPartes/HU-002-identidad-funcional-y-acceso.md) |
| **SPEC relacionada** | [SPEC-002-identidad-funcional-y-acceso](../../05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Asistente / Supervisor / Cliente (funcionales); usuario solo GEN denegado |
| **Dependencias** | [TR-001](./TR-001-modelo-datos-modulo.md) (tablas `PQ_PARTES_USUARIOS` / `CLIENTES`); auth GEN (`LoginController`, `MeController`, Sanctum, envelope) |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Pendiente (D implementado — verificar F1) |
| **Última actualización** | 2026-07-30 (D) |

**Origen:** [HU-002](../../03-historias-usuario/100-SistemaPartes/HU-002-identidad-funcional-y-acceso.md)  
**Referencia SPEC:** [SPEC-002](../../05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md)  
**Envelope:** [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md)

---

## 1) HU refinada (resumen)

### Título
Identidad funcional y acceso al módulo Sistema Partes

### Narrativa
Como usuario autenticado del Framework, quiero que tras credenciales OK el sistema resuelva mi perfil Partes (asistente / supervisor / cliente) o me deniegue con mensaje claro, para operar el módulo con el contexto correcto.

### In scope
- Reemplazar `NoOpPostLoginBusinessGate` por gate Partes real.
- Resolución vía SP MUST por `users.id` → dominio usable (`activo=1`, `inhabilitado=0`).
- Denegación 403 / `error=3003` / `partes.auth.*` (no 3002 si password OK).
- Enriquecer login y `GET /api/v1/auth/me` con `resultado.partes`.
- Revalidación mid-session en `/me` y middleware listo para APIs de dominio.
- Perfil solo lectura desde avatar (campos R-ID-09).
- Seed `PQ_PARTES_USUARIOS` para `admin` y `PQ` con `supervisor=1` (rol Framework SUPERVISOR ya existe vía `PqPermisoSeeder`).
- Verificar FE: `firstLogin` → `/change-password` antes del shell (ya GEN; no romper).

### Out of scope
- Redefinir login/logout/forgot/reset/Sanctum/tenancy.
- ABM maestros (TR-003); carga/consultas/dashboard/mobile.
- Edición de perfil.
- Limpieza de columna legado `users.supervisor` (seguir sin usarla como fuente).

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | Credenciales OK sin perfil dominio usable → 403, `error=3003`, `respuesta=partes.auth.noFunctionalProfile`, sin token |
| AC-02 | Solo asistente activo → 200; `resultado.partes.tipoFuncional=asistente`; `asistenteId`; `esSupervisor` = columna dominio |
| AC-03 | Solo cliente con `user_id` usable → 200; `tipoFuncional=cliente`; `clienteId`; `esSupervisor=false` |
| AC-04 | Mismo `users.id` en ambas tablas usables → 403, `partes.auth.inconsistentProfile`, sin token |
| AC-05 | Inactivo o inhabilitado → mismo trato que sin perfil (`noFunctionalProfile`) |
| AC-06 | `GET /api/v1/auth/me` con sesión válida expone el mismo `resultado.partes` (re-resolución) |
| AC-07 | UI perfil desde avatar: solo lectura; tipo, `code`, nombre, supervisor si aplica, email dominio (o —), `users.usuario` |
| AC-08 | `esSupervisor` no se calcula desde `users.supervisor` |
| AC-09 | i18n FE (+ locales producto): `partes.auth.noFunctionalProfile`, `partes.auth.inconsistentProfile` |
| AC-10 | Tras inhabilitar/revocar con token vivo: `/me` → 403 + clave `partes.auth.*`; FE limpia sesión / re-login |
| AC-11 | Con `firstLogin=true` tras login OK, FE va a `/change-password` antes del Dashboard/shell |

### Escenarios Gherkin

(Heredar HU-002 + AC-10/AC-11.) Incluir Feature test de token no emitido cuando gate falla.

---

## 3) Reglas de negocio

R-ID-01…11 según SPEC/HU.

| ID | Detalle de implementación |
|----|---------------------------|
| RN-TR-01 | SP único de resolución: `pq_sp_partes_identidad_resolver` (@p_user_id). |
| RN-TR-02 | Gate falla **antes** de `createToken` (flujo actual `LoginController`). |
| RN-TR-03 | Claves `respuesta` literales i18n (no texto libre) en denegación. |
| RN-TR-04 | `resultado.partes` siempre objeto presente en login/`me` OK; nunca `null`. |
| RN-TR-05 | Middleware `EnsurePartesFunctionalProfile` reutiliza el mismo resolver; registrarlo y aplicarlo a `/auth/me` + grupo futuro `partes/*`. |

---

## 3.1) Informe C1 (2026-07-30)

Ver §11 al final (emitido y absorbido en esta TR).

---

## 4) Impacto en datos

### 4.1 SP `pq_sp_partes_identidad_resolver`

| Param | Tipo | Notas |
|-------|------|--------|
| `@p_user_id` | bigint | `users.id` |

**Resultado (una fila):**

| Columna | Significado |
|--------|-------------|
| `codigo_resultado` | `0` = OK; `1` = sin perfil usable; `2` = inconsistente (ambos) |
| `tipo_funcional` | `asistente` \| `cliente` \| NULL si fail |
| `asistente_id` | bigint NULL |
| `cliente_id` | bigint NULL |
| `es_supervisor` | bit (0 si cliente/fail) |
| `code` | nvarchar NULL |
| `nombre` | nvarchar NULL |
| `email` | nvarchar NULL (email de dominio para perfil) |

Algoritmo = SPEC-002 §4.2 (solo filas `activo=1` e `inhabilitado=0`). **No** leer `users.supervisor`.

### 4.2 Seed

| Seeder | Contenido |
|--------|-----------|
| `PqPartesUsuariosSeedSeeder` (o método en seeder Partes) | Para `users.usuario` ∈ {`admin`,`PQ`}: upsert `PQ_PARTES_USUARIOS` con `user_id`, `code` = mismo usuario (`admin`/`PQ`), `nombre` desde `users.name`, `email` desde `users.email`, `supervisor=1`, `activo=1`, `inhabilitado=0` |
| Orden | Tras `PqRolSeeder` / `PqPermisoSeeder` (users existen) y tras migraciones TR-001 |

Rol Framework `SUPERVISOR` para admin/PQ: **ya** en `PqPermisoSeeder` — no duplicar; verificar en test de seed.

### 4.3 Migraciones DDL

Ninguna nueva tabla. Solo script de despliegue del SP (migración `DB::unprepared` CREATE/ALTER PROCEDURE).

---

## 5) Contratos de API

### 5.1 Endpoints tocados (sin paths nuevos Must)

| Método | Path | Cambio |
|--------|------|--------|
| POST | `/api/v1/auth/login` | Gate Partes; merge `resultado.partes` si OK; sin token si fail |
| GET | `/api/v1/auth/me` | Re-resolver; merge `resultado.partes` o 403 |

Auth: login público + `X-Paq-Cliente`; `me` Bearer + tenant.

### 5.2 Envelope denegación

```json
{
  "error": 3003,
  "respuesta": "partes.auth.noFunctionalProfile",
  "resultado": {}
}
```

Inconsistente: `"respuesta": "partes.auth.inconsistentProfile"`. HTTP **403**.

Éxito login (fragmento):

```json
{
  "error": 0,
  "respuesta": "OK",
  "resultado": {
    "token": "...",
    "user": { "id": 1, "usuario": "admin", "email": "...", "locale": "es" },
    "firstLogin": false,
    "partes": {
      "tipoFuncional": "asistente",
      "asistenteId": 1,
      "clienteId": null,
      "esSupervisor": true,
      "code": "admin",
      "nombre": "Administrador",
      "email": "admin@partes.local"
    }
  }
}
```

**Nota C1:** `email` en `partes` extiende el mínimo SPEC §4.4 para cumplir R-ID-09/CA-07 sin endpoint extra; campos §4.4 siguen siendo obligatorios.

### 5.3 OpenAPI

- [ ] Actualizar descripción login/`me` con `resultado.partes` y errores 403 `partes.auth.*`.
- [ ] Sin nueva fila de matriz de permisos de menú (gate es transversal auth).

### 5.4 Backend — clases / binding

| Pieza | Acción |
|-------|--------|
| `PostLoginBusinessGate` (producto) | Extender contrato: `assertAllowed(User $user): array` → mapa a mergear en `resultado` (p. ej. `['partes' => [...]]`). `NoOp` devuelve `[]`. |
| `PartesPostLoginBusinessGate` | Implementación: llama resolver/SP; throw `PostLoginBusinessGateException` con `respuesta` correcta; return `['partes' => …]`. |
| `AppServiceProvider` | Bind `PostLoginBusinessGate` → `PartesPostLoginBusinessGate`. |
| `LoginController` | `$extra = assertAllowed($user);` merge en `buildSessionResultado`. |
| `MeController` | Resolver de nuevo; si fail → 403 envelope; si OK merge `partes`. |
| `EnsurePartesFunctionalProfile` | Middleware: resolver; fail → 403; success continúa (opcional attach request). Registrar en `bootstrap`/`Kernel`; aplicar a `me` y documentar grupo `partes/*` para TR-003+. |
| Adapter SP | `PartesIdentidadRepository` / similar: solo `DB::select`/`procedure` del SP (MUST). |

---

## 6) Cambios frontend

| Área | Cambio |
|------|--------|
| `authTypes.ts` | Tipo `PartesSessionContext` + `partes?: PartesSessionContext` en `AuthSession` / login resultado |
| `authSessionStore` | Persistir `partes` del login; refrescar desde `/me` |
| Login error | Mapear `respuesta` `partes.auth.*` a i18n (toast/alert); no tratar como invalid credentials |
| `UserAvatarMenu` / shell | Ítem o panel **Perfil Partes** solo lectura (`data-testid`: `partesProfileOpen`, `partesProfilePanel`) |
| Guards | Confirmar `firstLogin` → change-password (existente); tras 403 de `/me` por perfil → logout |
| i18n | Claves `partes.auth.noFunctionalProfile`, `partes.auth.inconsistentProfile`, labels perfil |

Sin rutas nuevas Must para perfil.

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1 | DB | Script SP `pq_sp_partes_identidad_resolver` | AC resolución §4.2 | M |
| T2 | Backend | Resolver PHP + exception mapping + gate + bind | AC-01…05, AC-08 | M |
| T3 | Backend | LoginController + MeController merge/`me` 403 | AC-01…06, AC-10 | M |
| T4 | Backend | Middleware `EnsurePartesFunctionalProfile` + registro | AC-10; listo TR-003 | S |
| T5 | DB/Seed | Seeder USUARIOS admin/PQ supervisor=1 | Seed idempotente; login OK | S |
| T6 | Frontend | Types + store + errores gate + perfil avatar | AC-07, AC-09, AC-11 | M |
| T7 | i18n | Claves auth + perfil en locales producto | AC-09 | S |
| T8 | Tests | Feature: sin perfil, asistente, supervisor, cliente, inconsistente, inactivo, revalidación `/me`; Vitest perfil/router si aplica | Suite verde | L |
| T9 | Docs | OpenAPI login/`me`; nota deploy seed | Checklist | S |

**Orden:** T1 → T2 → T3 → T4 → T5 → T8 (API); T6/T7 en paralelo tras contrato JSON estable; T9 al cierre.

---

## 8) Estrategia de tests

| Capa | Casos |
|------|--------|
| Feature PHP | Login: AC-01…05; sin token en fail; `users.supervisor=1` pero dominio `supervisor=0` → `esSupervisor=false`; `/me` OK y mid-session fail |
| Unit FE | Parse envelope gate; `resolvePostLoginRoute` con firstLogin (regresión) |
| E2E | Humo: login admin seed → shell; opcional perfil avatar visible |

Datos: factory/users de prueba + filas dominio; no depender solo de admin en todos los negativos.

---

## 9) Riesgos y edge cases

| Riesgo | Mitigación |
|--------|------------|
| Doble llamada SP login (assert + enrich) | Un solo resolve en gate que retorna payload |
| Token emitido si alguien reordena controller | Test Feature que gate fail ⇒ `personal_access_tokens` sin alta |
| Perfil con email NULL | Mostrar "—" / ocultar fila |
| Delimitación datos en APIs aún no existentes | Middleware listo; aplicar en TR-003+ |
| `users.supervisor` legado en seed | Ignorar en resolver; doc CA-08 |

---

## 10) Checklist final

- [ ] AC-01…11
- [ ] SP + seed + gate bind
- [ ] login/`me` + middleware
- [ ] FE tipos, i18n, perfil, firstLogin
- [ ] Feature tests
- [ ] OpenAPI
- [ ] Aviso deploy: migrate SP + seed Partes usuarios

---

## 11) Informe C1 + discrepancias

# Revisión de ambigüedad - TR-002

## Resultado general
- Estado: **Apto con observaciones** (cerradas en redacción C1 de esta TR)

## Ambigüedades críticas
- ~~Cómo enriquecer `resultado` si `assertAllowed(): void`~~ → **cerrado:** contrato producto `assertAllowed(User): array` mergeable; NoOp `[]`.
- ~~CA-10 ausente en lista HU (salto CA-09→CA-11)~~ → **cerrado en TR:** AC-10 revalidación explícito (alineado SPEC/Gherkin HU).

## Ambigüedades menores
- ~~`email` no estaba en tabla §4.4 SPEC~~ → **cerrado:** campo opcional en `partes.email` para CA-07; resto §4.4 obligatorio.
- Detalle visual del panel avatar (Popup vs Drawer): libre DevExtreme mientras testids y campos CA-07 se cumplan.
- Aplicación exacta del middleware a cada ruta Partes futura: TR-003+ lista rutas; TR-002 deja alias + `/me`.

## Contradicciones TR ↔ HU ↔ SPEC
- Ninguna abierta. Seed rol Framework ya cubierto por `PqPermisoSeeder`; TR solo exige filas dominio.

## Supuestos detectados
- TR-001 aplicado (tablas + triggers exclusividad).
- PedidosWeb/`AuthGuards` firstLogin ya en producto.

## Preguntas para decisión humana
- Ninguna bloqueante para D1.

## Recomendaciones
- Absorbidas arriba (firma gate, AC-10, `partes.email`).

## Veredicto
- **Puede pasar a D1/D: Sí**

### Discrepancias documentadas
- HU metadatos omitían CA-10 numerado; la TR lo restaura (contenido ya en Gherkin/SPEC).

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte C + C1: TR creada y apta con observaciones absorbidas. |
| 2026-07-30 | Parte D: SP resolver, gate Partes, middleware `/me`, seed admin/PQ, FE perfil + i18n, Feature tests OK. |

---

**Siguiente:** F1 verificación TR-002; o **D de TR-003** cuando se autorice.
