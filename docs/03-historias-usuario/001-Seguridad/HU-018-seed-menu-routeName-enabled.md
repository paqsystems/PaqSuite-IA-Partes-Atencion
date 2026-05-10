# HU-018 – Seed pq_menus: routeName y enabled para sidebar

## Épica
001 – Seguridad y Acceso

## Clasificación
SHOULD-HAVE

## Rol
Administrador / DevOps

## Narrativa

Como administrador quiero que las opciones de menú que deben mostrarse en el sidebar tengan `routeName` y `enabled` configurados correctamente en el seed, para que el menú dinámico (HU-016, HU-017) funcione sin ajustes manuales en base de datos.

## Criterios de aceptación

- Las opciones de Administración que apliquen en MONO (**Usuarios**, **Roles**, **Permisos**, **Atributos de rol**; sin empresas ni grupos empresarios) tienen `enabled = 1` en `PQ_MENUS.seed.v2.json`.
- Cada opción navegable tiene `routeName` con la ruta del frontend (ej. `/admin/usuarios`, `/admin/roles`, `/admin/permisos`).
- Las opciones Inicio y Perfil (si están en el seed) tienen `routeName`: `/` y `/perfil`.
- Los ítems solo de sección (sin ruta propia) pueden tener `routeName` null; el frontend los trata como etiquetas, no como enlaces.
- El seeder (PqMenuSeeder) incluye `routeName` en el upsert.
- La migración de `pq_menus` tiene columna `routeName` (nullable) si no existe.
- Documentar en `docs/backend/seed/PQ_MENUS/README.md` la convención de `routeName` para nuevas opciones.

## Reglas de negocio

- `routeName` debe coincidir con las rutas definidas en el router del frontend.
- Las opciones deshabilitadas (`enabled = 0`) no aparecen en el menú del usuario.

## Dependencias

- HU-015 (Menú del sistema seed versionado)
- Estructura de rutas del frontend (admin, perfil, etc.)

## Alcance

- Actualización del archivo seed `PQ_MENUS.seed.v2.json`.
- Verificación de PqMenuSeeder para que persista `routeName`.
- Documentación de convención.

## Especificación (Open-Spec)

- [SPEC-018 – Seed menú `routeName` y `enabled`](../../05-open-spec/001-Seguridad/SPEC-018-seed-menu-routename-enabled.md)

## Referencias

- `docs/backend/seed/PQ_MENUS/PQ_MENUS.seed.v2.json`
- `docs/backend/seed/PQ_MENUS/README.md`
- `backend/database/seeders/PqMenuSeeder.php`
