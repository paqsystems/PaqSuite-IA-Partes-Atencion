# TR-GEN-02-autorizacion-menu-api — Autorizacion de menu en API

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-autorizacion-menu-api](../../03-historias-usuario/001-Generalidades/HU-GEN-02-autorizacion-menu-api.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-modelo-roles-permisos-seed, TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-05-30 (D implementado) |

**Origen:** [HU-GEN-02-autorizacion-menu-api](../../03-historias-usuario/001-Generalidades/HU-GEN-02-autorizacion-menu-api.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Titulo
Exponer menu autorizado desde backend segun roles/permisos seed.

### Narrativa
Como sistema, quiero calcular el arbol de menu visible en backend para que el frontend no hardcodee permisos.

### In scope / Out of scope
- In scope: endpoint protegido de menu, servicio de filtrado por `Pq_Permiso`, salida en orden.
- In scope: manejo controlado de usuario sin items autorizados.
- Out of scope: render del sidebar y politicas de endpoints de negocio.

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Perfil supervisor (acceso total) recibe todos los items habilitados.
- **AC-02**: Perfil granular recibe solo items permitidos.
- **AC-03**: Usuario con varios roles recibe union de permisos.
- **AC-04**: Request sin token devuelve 401.
- **AC-05**: Token valido sin permiso devuelve 403 cuando corresponda.
- **AC-06**: OpenAPI documentado con 401/403 y envelope.
- **AC-07**: Tests integracion: acceso total, granular, sin atributos, union de 2 roles.

### Escenarios Gherkin

```gherkin
Feature: Menu autorizado por API

  Scenario: Menu por perfil granular
    Given un vendedor con permisos limitados
    When llama GET /api/v1/user/menu
    Then recibe solo items autorizados

  Scenario: Acceso total en seed
    Given un usuario cuyo rol tiene AccesoTotal en Pq_Rol
    When llama GET /api/v1/user/menu
    Then recibe todos los items habilitados de pq_menus

  Scenario: Union de permisos con dos roles
    Given un usuario con dos roles activos
    When llama GET /api/v1/user/menu
    Then recibe la union de items autorizados

  Scenario: Menu vacio controlado
    Given un usuario autenticado sin atributos de menu
    When llama GET /api/v1/user/menu
    Then recibe arbol vacio o minimo sin error 500

  Scenario: Request sin autenticacion
    Given una request sin Bearer token
    When llama GET /api/v1/user/menu
    Then recibe HTTP 401
```

---

## 3) Reglas de Negocio

1. **RN-01**: Frontend renderiza menu tal como lo retorna la API.
2. **RN-02**: Item deshabilitado en `pq_menus` no debe aparecer.
3. **RN-03**: Si no hay autorizaciones, la respuesta devuelve arbol vacio/minimo sin error 500.
4. **RN-04**: Visibilidad de menu no reemplaza autorizacion backend de endpoints.

### 3.1) Algoritmo `buildAuthorizedMenu`

```
Para cada nodo habilitado en pq_menus (orden por padre/orden):
  Si Pq_Rol.AccesoTotal del rol del usuario → incluir nodo
  Si no → incluir solo si procedimiento/atributo del nodo ∈ PQ_RolAtributo del rol del usuario
Construir arbol jerarquico con nodos incluidos (padres si tienen hijos visibles)
```

- **AccesoTotal:** rol Supervisor seed (`supervisor.mvp`).
- **Granular:** rol Vendedor acotado (`vendedor.acotado.mvp`) — subconjunto documentado en TR-GEN-01-menu-general-sidebar.
- **Union roles:** si el usuario tiene mas de un rol, unir allow-list de atributos (OR). **MVP:** una fila `Pq_Permiso` por usuario → ver D1-1.

### 3.2) Decisiones D1 — planificacion (cerradas 2026-05-30)

| # | Tema | Decision |
|---|------|----------|
| D1-1 | Union multi-rol (AC-03) | **N/A MVP:** un `Pq_Permiso` por usuario. Algoritmo documentado para OR futuro; **sin test union** en esta oleada. |
| D1-2 | `AccesoTotal` | `Pq_Rol.acceso_total = true` → todos los `pq_menus` con `enabled = true`. |
| D1-3 | Granular | Incluir nodo si `procedimiento` ∈ `PQ_RolAtributo` del rol con **`permiso_repo = true`**. |
| D1-4 | Sin `Pq_Permiso` | **403** `auth.noPermission` (misma regla que login). |
| D1-5 | Sin atributos de menu | **200** `resultado: []` — no 500 (RN-03); caso `vendedor.sinMenu.mvp`. |
| D1-6 | Shape respuesta 200 | **`resultado`** = array raiz de nodos (alineado TR-GEN-01-menu-general-sidebar): `id`, `menuKey`, `labelKey`, `text`, `routePath`, `procedimiento`, `order`, `nodeType`, `children`. |
| D1-7 | `menuKey` / `labelKey` | `menuKey` desde `paqsuite_mvp.menuItems`; fallback `procedimiento`. `labelKey` = `menu.{menuKey}`. |
| D1-8 | Cliente MVP | Rol `Cliente` sin `PQ_RolAtributo` → menu vacío **200** (esperado hasta TR sidebar/seed amplien atributos). |

### 3.3) Revision C1 — post-login/seed (2026-05-30)

| # | Hallazgo | Accion |
|---|----------|--------|
| C1-1 | Legacy `pq_menus`: `idparent`, `routeName`, `orden`, `enabled` | Mapeo en servicio; arbol por `idparent` |
| C1-2 | `PQ_RolAtributo` por rol, no por usuario | Igual que seed; filtro por `id_rol` de `Pq_Permiso` |
| C1-3 | AC-03 multi-rol vs MONO | D1-1: N/A MVP |
| C1-4 | 403 vs 200 vacio | D1-4 / D1-5 |
| C1-5 | Supervisor puede ver menus legacy enabled fuera seed | Aceptado; tests verifican subconjunto MVP minimo |

**Veredicto C1:** **Apto para D.**

### 3.4) TR-update — supersession multi-rol (C1 epic admin, 2026-06-19)

| Campo | Valor |
|-------|--------|
| **Origen** | [F-GEN-02-admin-cierre-c1](F-GEN-02-admin-cierre-c1.md) · [TR-GEN-02-admin-roles](TR-GEN-02-admin-roles.md) T0 |
| **Efecto** | La decision **D1-1** («N/A MVP — un Pq_Permiso por usuario») queda **superseded** al implementar el epic admin post-MVP. |

**Nueva regla (T0 admin):**

- Cargar **todas** las filas `Pq_Permiso` del usuario (`monoEmpresaId`).
- Menu = union de procedimientos con `permiso_repo` **OR** cualquier rol con `acceso_total`.
- Feature test obligatorio: usuario con 2 roles acotados → menu incluye union de ambos.

**Sin cambio:** perfil comercial en login (`SessionContextBuilder` / `CommercialProfileResolver`) sigue reglas ERP; ver TR-GEN-02-admin-roles R-C1-ADM-07.

---

## 4) Impacto en Datos

### Tablas afectadas
- `pq_menus`
- `Pq_Rol`
- `Pq_Permiso`
- tablas pivote de rol-permiso / rol-atributo (segun modelo real)

### Seed minimo para tests
- Menu MVP sembrado (11 items base del sidebar).
- Roles y permisos de `TR-GEN-02-modelo-roles-permisos-seed`.

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| GET | `/api/v1/user/menu` | Bearer Sanctum + `X-Paq-Cliente` | Permisos de menu en `Pq_Permiso` | No |

### 5.2 Detalle por operacion

#### GET `/api/v1/user/menu`
**Autorizacion:** usuario autenticado con permisos de menu vigentes.
**Request:** header `Authorization: Bearer ...` y `X-Paq-Cliente`.
**Response 200:** envelope con **`resultado`** = array raiz de nodos (ver D1-6 y ejemplo TR-GEN-01-menu-general-sidebar §5.2).
**Response 401:** no autenticado.
**Response 403:** autenticado sin permisos para menu solicitado.
**Response 5xx:** errores no controlados (deben minimizarse con fallback de lista vacia).

### 5.3 Actualizacion matriz permisos

- [x] Registrar fila de `/api/v1/user/menu` y permiso asociado.
- [x] Confirmar coherencia de permiso en descripcion OpenAPI (matriz).
- [ ] Validar 401/403 en spec generado `/api/documentation` — **oleada F**.

---

## 6) Cambios Frontend

### Pantallas / componentes
- Sidebar consume exclusivamente `GET /api/v1/user/menu`.
- Manejo de estado de menu vacio.

### data-testid sugeridos
- `sidebar-menu`
- `sidebar-menu-item`
- `sidebar-menu-empty`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T1 | Backend | Implementar servicio `buildAuthorizedMenu` | **Cumplido** (`AuthorizedMenuBuilder`) |
| T2 | Backend | Implementar endpoint `GET /api/v1/user/menu` | **Cumplido** |
| T3 | Frontend | Integrar consumo de menu autorizado | **Minimo** en shell (`SidebarMenu`); TreeView → TR sidebar |
| T4 | Tests | Integration para perfiles y errores | **Cumplido** (`UserMenuTest`; requiere SQL Server) |
| T5 | Docs | OpenAPI + matriz permisos | Matriz OK; OpenAPI → **oleada F** |

---

## 8) Estrategia de Tests

- **Unit:** armado del arbol de menu y union de permisos por roles.
- **Integration:** acceso total vs acotado vs vacio vs union roles; 401/403.
- **E2E:** visibilidad menu distinta supervisor vs vendedor acotado (coord. TR sidebar).

---

## 9) Riesgos y Edge Cases

- Menus huerfanos/ciclos por datos inconsistentes.
- Divergencia entre seed de menu y seed de permisos.
- Cache desactualizada de menu tras cambios de permiso.

---

## 10) Checklist final

> **Cierre post-D (2026-05-30):** Parte **F** diferida a oleada conjunta (OpenAPI `/api/documentation`).

### Evidencia

- `UserMenuTest` — supervisor (MVP items), acotado (4 proc.), vacio, 401, 400 tenant.
- Frontend: `SidebarMenu` consume API sin hardcode de permisos.
- Ejecutar `php artisan test` con SQL Server accesible.

### Checklist del slice
- [x] AC cumplidos (AC-03 N/A MVP; AC-06 → oleada F)
- [x] API menu autorizada implementada
- [x] Frontend consume menu backend sin hardcode (shell minimo)

### Checklist normas transversales
- [x] Endpoints con `auth:sanctum` + validacion `Pq_Permiso`
- [x] Matriz endpoint ↔ permiso actualizada
- [ ] ~~OpenAPI `/api/documentation`~~ **Oleada F**
- [x] 401/403 en codigo
- [x] Envelope JSON respetado
- [x] `X-Paq-Cliente` via middleware `paq.tenant`
- [x] Tests API 401 (+ 403 sin permiso si aplica)
- [x] Sin ampliacion de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

### Backend
- `app/Services/Menu/AuthorizedMenuBuilder.php`
- `app/Http/Controllers/UserMenuController.php`
- `routes/api.php`
- `tests/Feature/UserMenuTest.php`

### Frontend
- `src/features/menu/menuApi.ts`, `useUserMenu.ts`, `SidebarMenu.tsx`
- `src/features/auth/ShellPage.tsx` (integracion menu)

### OpenAPI
- Pendiente oleada F.

### Docs
- `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` (referencia login+menu)










