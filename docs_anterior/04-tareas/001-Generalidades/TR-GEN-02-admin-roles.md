# TR-GEN-02-admin-roles — ABM roles (`Pq_Rol`)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-admin-roles](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-roles.md) |
| **SPEC relacionada** | [SPEC-001-02-admin-mantenimiento-roles-permisos](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md) |
| **Épica** | 001-Generalidades / Acceso y seguridad (post-MVP) |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-02-modelo-roles-permisos-seed; TR-GEN-02-autorizacion-menu-api; TR-GEN-03-patron-abm; TR-GEN-01-menu-general-sidebar |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-19 (revisión C1 formal `/tr-ambiguity-review`) |

**Origen:** [HU-GEN-02-admin-roles](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-roles.md)  
**Referencia SPEC:** [SPEC-001-02-admin-mantenimiento-roles-permisos](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md)  
**Contexto:** [`mantenimiento-roles-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/mantenimiento-roles-permisos.md) · Tango [TR-012](https://github.com/paqsystems/PaqSuite-IA-TANGO/blob/main/docs/04-tareas/001-Seguridad/TR-012-administracion-roles.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Administración de roles reutilizables (`Pq_Rol`) en MONO.

### Narrativa
Como usuario autorizado a administrar seguridad, quiero dar de alta, editar, listar y eliminar roles para definir perfiles de acceso asignables vía `Pq_Permiso`.

### In scope / Out of scope
- **In scope:** grilla + modal ABM; indicador `AccesoTotal`; baja condicionada; acción **Atributos** cuando `AccesoTotal = false`; API CRUD; seed menú admin; infra transversal multi-rol (T0); flag `ADMIN_SECURITY_UI_ENABLED`.
- **Out of scope:** asignación usuario–rol (TR permisos); edición de atributos (TR atributos); ABM usuarios; seed destructivo MVP.

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Usuario autorizado lista roles con nombre, descripción y acceso total.
- **AC-02:** Alta válida persiste en `Pq_Rol` y refresca grilla.
- **AC-03:** Edición actualiza `nombre_rol`, `descripcion_rol`, `acceso_total`.
- **AC-04:** Nombre duplicado → HTTP 422 + clave i18n.
- **AC-05:** Baja de rol sin filas en `Pq_Permiso` → éxito con confirmación.
- **AC-06:** Baja de rol referenciado en `Pq_Permiso` → rechazo con mensaje claro.
- **AC-07:** Rol con `acceso_total = false` muestra acción **Atributos** navegable.
- **AC-08:** Usuario sin autorización → 403 API / ruta no accesible.
- **AC-09:** i18n `admin.roles.*` en es/en/pt/fr/it.
- **AC-10:** E2E smoke: listar y abrir modal alta (`roles-admin.spec.ts` adaptado de Tango).

### Escenarios Gherkin

(Heredados de HU-GEN-02-admin-roles.)

---

## 3) Reglas de Negocio

1. **RN-01:** Autorización del proceso vía `AdminSecurityAccessService` — `AccesoTotal` en **cualquier** rol del usuario **o** atributo ABM sobre procedimiento `pw_adminroles`.
2. **RN-02:** `nombre_rol` obligatorio y único (case-sensitive según collation BD; validar en tests).
3. **RN-03:** Rol con `acceso_total = true` no requiere `PQ_RolAtributo` para menú operativo.
4. **RN-04:** No eliminar rol con filas en `Pq_Permiso` (sin cascada).
5. **RN-05:** Patrón UI: modal sobre grilla (`TR-GEN-03-patron-abm`); alta con botón **+** nativo DevExtreme.
6. **RN-06:** Epic deshabilitado si `ADMIN_SECURITY_UI_ENABLED = false` (404 menú / gate frontend).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-19)

**Skill:** `/tr-ambiguity-review` · **Regla:** `16-tr-ambiguity-review` (PaqSuite-IA-BASE)

**Fuentes revisadas:** HU-GEN-02-admin-roles, SPEC-001-02-admin, `mantenimiento-roles-permisos.md`, TR-GEN-03-patron-abm, TR-GEN-02-autorizacion-menu-api (§3.2 D1-1), `_NORMAS-TRANSVERSALES-TR.md`, `envelope-respuestas.md`; código `AuthorizedMenuBuilder.php`, `VisibilityPermissionGuard.php`, `SessionContextBuilder.php`, `User.php`, `PublicConfigController.php`, migraciones `Pq_Rol` / `Pq_Permiso`.

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (aplicar resoluciones §3.2; T0 antes de QA funcional admin)

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución (→ D1) |
|----|------|--------|-------------------|
| AMB-C1-ADM-R01 | Multi-rol vs código MVP (`PqPermiso::query()->first()`) | Admin + SPEC permiten N filas; menú/guards/login devuelven solo un rol | **T0:** `UserRoleUnionService` + refactor `AuthorizedMenuBuilder`, `VisibilityPermissionGuard`. Actualizar `SessionContextBuilder`: `security.roles[]` con **todos** los roles; `accesoTotal = OR`; **perfil comercial** sigue viniendo de ERP (`CommercialProfileResolver`), no de `Pq_Permiso`. |
| AMB-C1-ADM-R02 | Contradicción TR-GEN-02-autorizacion-menu-api D1-1 («N/A MVP») vs epic admin | Implementadores pueden omitir union multi-rol | **TR-update** en `TR-GEN-02-autorizacion-menu-api`: nota «D1-1 superseded by TR-GEN-02-admin-roles T0» + Feature test union 2 roles. |
| AMB-C1-ADM-R03 | `AdminSecurityAccessService` vs guards actuales | 403 inconsistente entre admin y negocio | **T0b:** servicio único; admin exige `AccesoTotal` en **cualquier** rol del usuario **OR** atributo A/B/M/R sobre `pw_adminroles` / `pw_adminpermisos`. |
| AMB-C1-ADM-R04 | Flag epic deshabilitado | RN-06 mezcla «404 menú» sin definir API | Rutas `/api/v1/admin/*` → **404** envelope si flag false; frontend oculta menú vía `securityAdminEnabled` en `GET /config/public`. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M-C1-R01 | `id_empresa` opaco | Inyectar `config('paqsuite_seed.monoEmpresaId')` en writes; nunca en JSON cliente. |
| AMB-M-C1-R02 | Unicidad `nombre_rol` | Migración idempotente `uq_pq_rol_nombre_rol` (hoy no existe en DDL). |
| AMB-M-C1-R03 | Flag infra | Paridad `excelImportEnabled`: `config/paqsuite_mvp.php` → `securityAdminEnabled` + `PublicConfigController`. |
| AMB-M-C1-R04 | Refresh sesión | v1: re-login recomendado si cambian permisos del usuario logueado; sin invalidar tokens masivamente. |
| AMB-M-C1-R05 | Seed menú | Idempotente `grp_seguridad`, `pw_adminroles`, `pw_adminpermisos` + entradas `paqsuite_mvp.menuItems`. |
| AMB-M-C1-R06 | `User::permiso()` HasOne | Añadir `permisos()` HasMany; migrar consumidores en T0. |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| HU «Agregar» rol vs TR-GEN-03 botón **+** DX | Prevalece **TR-GEN-03**: alta con **+** nativo DataGrid; Gherkin HU = pulsar **+**. |
| SPEC CA-08 menú vs T0 multi-rol | Tras T0, menú = unión de roles; test Feature obligatorio en T0. |
| DELETE rol en uso: HU «rechazado» vs TR sin HTTP explícito | **422** negocio + clave `admin.roles.deleteInUse`. |

### Supuestos detectados

- Tablas `Pq_Rol`, `Pq_Permiso`, `PQ_RolAtributo` existentes (migraciones MVP).
- `supervisor.mvp` es operador admin en dev/E2E.
- Epic post-MVP: flag default **false** no afecta release portal actual.

### Preguntas para decisión humana

(Ninguna bloqueante — cerradas en §3.2.)

### Recomendaciones de ajuste de la TR

- [x] Detallar alcance T0 `SessionContextBuilder` (roles vs perfil comercial) — §3.2 R-C1-ADM-07.
- [ ] TR-update `TR-GEN-02-autorizacion-menu-api` (nota D1-1) — en mismo PR que T0 o acta F.
- [x] Alinear flag con patrón `PublicConfigController` existente.

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-19)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-ADM-01 | Flag infra | `securityAdminEnabled` en `config/paqsuite_mvp.php` (env `ADMIN_SECURITY_UI_ENABLED`), default **false**; expuesto en `GET /api/v1/config/public`. |
| R-C1-ADM-02 | Procedimiento menú roles | `pw_adminroles` → ruta `/admin/roles`. |
| R-C1-ADM-03 | Permisos API roles | GET → `Permiso_Repo`; POST → `Permiso_Alta`; PUT → `Permiso_Modi`; DELETE → `Permiso_Baja` sobre `pw_adminroles`. |
| R-C1-ADM-04 | Unicidad nombre | Validación FormRequest + índice único `uq_pq_rol_nombre_rol`. |
| R-C1-ADM-05 | Atributos en grilla | Navegación a `/admin/roles/:rolId/atributos`; visible solo si `!accesoTotal`. |
| R-C1-ADM-06 | TR-update menú API | Anotar supersession de D1-1 en `TR-GEN-02-autorizacion-menu-api`. |
| R-C1-ADM-07 | T0 SessionContext | `security.roles` = todos los `nombre_rol`; `accesoTotal` = OR; perfil comercial **sin** cambio de reglas ERP. |
| R-C1-ADM-08 | Flag off en API | Middleware admin → 404 envelope si `!securityAdminEnabled`. |
| R-C1-ADM-09 | Alta UI | Botón **+** DevExtreme (TR-GEN-03); `data-testid` `roles.form` en modal. |

---

## 3.3) Plan D1 — Implementación (2026-06-19)

**Estado:** Cerrado.

| # | Entrega | Estado |
|---|---------|--------|
| T0 | `UserRoleUnionService` + refactor menú/guards/sesión multi-rol | ✅ |
| T0b | `AdminSecurityAccessService` + middleware `admin.security.enabled` | ✅ |
| T0c | Flag `securityAdminEnabled` + seed menú `grp_seguridad` | ✅ |
| T1 | `AdminRoleController` CRUD + atributos + Feature tests | ✅ |
| T2 | `RolesAdminPage` + `RoleFormModal` + rutas `/admin/roles` | ✅ |
| T3 | E2E smoke `roles-admin.spec.ts` | ✅ |
| T4 | Matriz § Admin seguridad + acta F D1 | ✅ |

**Smoke dev:** `ADMIN_SECURITY_UI_ENABLED=true`, seeds menú/seguridad, login `supervisor.mvp`.

---

## 3.4) Verificación D (2026-06-19)

| Verificación | Resultado |
|--------------|-----------|
| `php artisan test --filter=AdminSecurityFeatureTest` | OK — 9 passed (incl. CRUD roles, flag off 404, 403 acotado) |
| Flag off → API admin 404 | OK — `testAdminRoutesReturn404WhenFlagDisabled` |
| CRUD rol + baja libre | OK — `testSupervisorCanListAndCreateRoles` |
| Baja rol en uso → 422 | OK — `testCannotDeleteRoleInUse` |
| Nombre duplicado → 422 | OK — `testDuplicateRoleNameReturns422` |
| T0 `UserRoleUnionService` en menú/guards | OK — refactor incluido en D1 |
| `RolesAdminPage` + `RoleFormModal` + rutas | OK — build frontend |
| i18n `admin.roles.*` (5 locales) | OK — claves en es/en/pt/fr/it |
| E2E smoke | OK — `roles-admin.spec.ts` 1 passed |
| `npm run build` | OK |
| Matriz § Admin seguridad | OK — aplicada |

### Trazabilidad AC

| AC | Evidencia | Estado D |
|----|-----------|----------|
| AC-01 | Feature list roles + grilla UI | ✅ |
| AC-02 | Feature create + modal alta E2E | ✅ |
| AC-03 | Feature update | ✅ |
| AC-04 | Feature duplicate 422 | ✅ |
| AC-05 | Feature delete rol libre | ✅ |
| AC-06 | Feature delete in use 422 | ✅ |
| AC-07 | Row action atributos + ruta (código + build) | ✅ |
| AC-08 | Feature 403 vendedor acotado + `AdminSecurityGate` | ✅ |
| AC-09 | i18n 5 locales | ✅ |
| AC-10 | E2E `roles-admin.spec.ts` | ✅ |

### Ajustes D observados

- OpenAPI anotado para `/admin/roles` pendiente (OBS-01 acta F).
- Modal usa `abmFormPopup` (patrón TR-GEN-03); testid página `roles.admin` / grilla `roles.grid`.

---

## 3.5) Verificación E (2026-06-19)

Ver [E-GEN-02-admin-tests.md](E-GEN-02-admin-tests.md) y [F-GEN-02-admin-cierre-e.md](F-GEN-02-admin-cierre-e.md). Resultado slice roles: **Apto** (Feature + E2E `roles-admin` + smoke API).

---

## 4) Impacto en Datos

### Tablas afectadas

| Tabla | Operación |
|-------|-----------|
| `Pq_Rol` | CRUD |
| `Pq_Permiso` | Solo lectura (validar baja) |
| `pq_menus` | Seed ítems admin (idempotente) |

### Seed menú admin (idempotente)

Extender `paqsuite:seed-menus-mvp` o comando dedicado `paqsuite:seed-admin-security-menu`:

| procedimiento | routeName | parent | enabled |
|---------------|-----------|--------|---------|
| `grp_seguridad` | — | 0 | true |
| `pw_adminroles` | `/admin/roles` | `grp_seguridad` | true |
| `pw_adminpermisos` | `/admin/permisos` | `grp_seguridad` | true |

Entradas paralelas en `config/paqsuite_mvp.menuItems` con `menuKey` `adminRoles`, `adminPermisos`.

### Seed mínimo para tests

- Usuario `supervisor.mvp` con `AccesoTotal` (smoke CRUD).
- Rol `VendedorAcotado` sin uso en permisos (baja OK).
- Rol con fila en `Pq_Permiso` (baja bloqueada).

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso |
|--------|------|------|---------|
| GET | `/api/v1/admin/roles` | Bearer + `X-Paq-Cliente` | `Permiso_Repo` + `pw_adminroles` |
| POST | `/api/v1/admin/roles` | Bearer + `X-Paq-Cliente` | `Permiso_Alta` + `pw_adminroles` |
| PUT | `/api/v1/admin/roles/{id}` | Bearer + `X-Paq-Cliente` | `Permiso_Modi` + `pw_adminroles` |
| DELETE | `/api/v1/admin/roles/{id}` | Bearer + `X-Paq-Cliente` | `Permiso_Baja` + `pw_adminroles` |

Gate adicional: middleware verifica `ADMIN_SECURITY_UI_ENABLED`.

### 5.2 Detalle por operación

#### GET `/api/v1/admin/roles`

**Query opcional:** `search` (filtro nombre/descripción).

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "items": [
      {
        "id": 1,
        "nombreRol": "Supervisor",
        "descripcionRol": "Acceso total MVP",
        "accesoTotal": true,
        "enUso": true
      }
    ]
  }
}
```

`enUso`: existe al menos una fila en `Pq_Permiso` con ese `id_rol`.

#### POST `/api/v1/admin/roles`

**Request:**

```json
{
  "nombreRol": "OperadorConsulta",
  "descripcionRol": "Solo consultas",
  "accesoTotal": false
}
```

**422:** `validation.duplicateRoleName` si nombre duplicado.

#### PUT `/api/v1/admin/roles/{id}`

Mismo body que POST (parcial permitido: solo campos enviados).

#### DELETE `/api/v1/admin/roles/{id}`

**422 negocio:** `admin.roles.deleteInUse` si referenciado en `Pq_Permiso`.

### 5.3 Actualización matriz permisos

- [x] Filas admin en [matriz-permisos-mvp.md](matriz-permisos-mvp.md) § Admin seguridad (aplicado D1 2026-06-19).

---

## 6) Cambios Frontend

### Pantallas / componentes

```text
frontend/src/features/admin/security/
  roles/
    RolesAdminPage.tsx
    RoleFormModal.tsx          # AbmFormPopup + Form DX
    rolesAdminApi.ts
  shared/
    useAdminSecurityEnabled.ts # lee config.public.securityAdminEnabled
```

- Ruta: `/admin/roles` en `protectedRoutes.tsx` (lazy).
- Grilla `DataGridDx` modo ABM; columnas: nombre, descripción, acceso total (Switch read-only en grilla / CheckBox en modal).
- Acción fila **Atributos** (`admin.roles.atributos`) → `navigate(/admin/roles/${id}/atributos)` si `!accesoTotal`.

### data-testid

| Elemento | testid |
|----------|--------|
| Página | `roles.admin` |
| Grilla | `roles.grid` |
| Modal alta/edición | `roles.form` |
| Atributos | `roles.atributos` |

### i18n

Claves `admin.roles.*` según [`mantenimiento-roles-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/mantenimiento-roles-permisos.md) § i18n.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T0 | Backend | `UserRoleUnionService` + refactor menu/guards multi-rol | Tests union 2 roles |
| T0b | Backend | `AdminSecurityAccessService` + middleware | Tests 403 granular |
| T0c | Config/Seed | Flag + seed menú `grp_seguridad` | Matriz actualizada |
| T1 | Backend | `AdminRoleController` CRUD + FormRequests | OpenAPI + Feature tests |
| T2 | Frontend | `RolesAdminPage` + modal | AC-01–AC-07 |
| T3 | Tests | E2E `roles-admin.spec.ts` | AC-10 |
| T4 | Docs | OpenAPI + matriz | Checklist §10 |

---

## 8) Estrategia de Tests

- **Unit:** validación unicidad; política baja en uso.
- **Integration:** CRUD 200; 401 sin token; 403 usuario `vendedor.acotado.mvp`; 422 duplicado y delete in use.
- **E2E:** supervisor lista roles y abre modal alta.

---

## 9) Riesgos y Edge Cases

- Roles seed MVP (`Supervisor`, `Cliente`, …) editables en UI — documentar que no renombrar en prod sin plan.
- Cambiar `acceso_total` de false→true no borra `PQ_RolAtributo` existentes (inofensivo).
- Collation SQL Server case-insensitive puede colisionar nombres que difieren solo en mayúsculas — alinear validación a collation.

---

## 10) Checklist final

### Checklist del slice

- [x] AC cumplidos
- [x] T0 multi-rol desplegado antes de QA admin
- [x] Flag default false hasta activación epic

### Checklist normas transversales

- [x] Endpoints nuevos/modificados con policy en código
- [x] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [x] 401 y 403 documentados por operación protegida
- [x] Envelope JSON respetado
- [x] X-Paq-Cliente documentado donde aplique
- [x] Tests API incluyen 401 y 403
- [x] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados (D1 2026-06-19)

### Backend

- `app/Services/Security/UserRoleUnion.php`
- `app/Services/Security/UserRoleUnionService.php`
- `app/Services/Security/AdminSecurityAccessService.php`
- `app/Services/Admin/AdminRoleService.php`
- `app/Services/Admin/RoleAttributesService.php`
- `app/Http/Controllers/Api/V1/Admin/AdminRoleController.php`
- `app/Http/Middleware/EnsureAdminSecurityEnabled.php`
- `config/paqsuite_admin_security.php`
- `config/paqsuite_mvp.php` (flag + menú admin)
- `routes/api.php` (grupo `/admin/roles`)
- `database/migrations/2026_06_19_120000_add_unique_index_pq_rol_nombre_rol.php`
- `tests/Feature/AdminSecurityFeatureTest.php`

### Frontend

- `frontend/src/features/admin/security/roles/*`
- `frontend/src/routes/adminSecurityRoutes.tsx`
- `frontend/src/features/config/api/publicConfigApi.ts` (`securityAdminEnabled`)

### Docs

- [matriz-permisos-mvp.md](matriz-permisos-mvp.md) § Admin seguridad
- [F-GEN-02-admin-cierre-d1.md](F-GEN-02-admin-cierre-d1.md)










