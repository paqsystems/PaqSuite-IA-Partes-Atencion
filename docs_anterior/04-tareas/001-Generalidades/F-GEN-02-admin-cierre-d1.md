# Acta F — Cierre D1 epic admin roles y permisos (SPEC-001-02-admin)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


**Fecha:** 2026-06-19  
**Alcance:** TR-GEN-02-admin-roles, TR-GEN-02-admin-rol-atributos, TR-GEN-02-admin-permisos, TR-GEN-02-admin-permisos-bulk  
**Precondición:** C1 cerrada ([F-GEN-02-admin-cierre-c1.md](F-GEN-02-admin-cierre-c1.md))

## Veredicto

**D1 cerrado.** Verificación D y cierre F formal: [F-GEN-02-admin-cierre-formal.md](F-GEN-02-admin-cierre-formal.md).

## Entregables

| Slice | Backend | Frontend | Tests |
|-------|---------|----------|-------|
| T0 multi-rol | `UserRoleUnionService`, refactor menú/guards/sesión | — | `AdminSecurityFeatureTest` + menú existente |
| Roles CRUD | `AdminRoleController`, `AdminRoleService` | `RolesAdminPage`, `RoleFormModal` | Feature 9 casos |
| Atributos | `RoleAttributesService` | `RoleAttributesPage` | Feature atributos |
| Permisos | `AdminPermisoController`, lookup usuarios | `PermisosAdminPage`, modal | Feature CRUD |
| Bulk | `PermisoBatchService` | modales bulk DX | Feature batch + E2E smoke |

## Activación en dev

1. `.env`: `ADMIN_SECURITY_UI_ENABLED=true`
2. `php artisan paqsuite:seed-menus-mvp`
3. `php artisan paqsuite:seed-seguridad-mvp`
4. Login smoke: `supervisor.mvp` (rol Supervisor / acceso total)

## Pendientes post-D1 (no bloquean cierre)

- OpenAPI `/api/documentation` para rutas `/admin/*`
- TR-update `TR-GEN-02-autorizacion-menu-api` §3.4 (supersede D1-1 multi-rol)
- E2E ampliados con backend real (opcional)

## Referencias

- Matriz: [matriz-permisos-mvp.md](matriz-permisos-mvp.md) § Admin seguridad
- Migración índice: `2026_06_19_120000_add_unique_index_pq_rol_nombre_rol.php`






