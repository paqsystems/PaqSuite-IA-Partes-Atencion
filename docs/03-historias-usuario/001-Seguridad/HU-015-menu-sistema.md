# HU-015 – Menú del sistema (seed versionado)

## Épica
001 – Seguridad y Acceso

## Clasificación
MUST-HAVE

## Rol
Sistema / Administrador

## Narrativa

Como administrador quiero que el menú del sistema esté versionado y sea reproducible en todos los entornos para garantizar consistencia entre dev, test y producción.

## Criterios de aceptación

- Existe un archivo seed versionado en el repo (ej. `PQ_MENUS.seed.json`) que representa el árbol de menú.
- Existe un script o seeder idempotente que sincroniza la tabla `pq_menus`.
- Al ejecutar el seed en un entorno vacío, el menú queda cargado correctamente.
- El orden dentro de cada parent es determinístico y consistente entre entornos.
- Existen constraints/índices mínimos para preservar integridad del árbol (ej. UNIQUE(parent, order)).
- El seed se integra en el flujo de deploy/migraciones (post-migration step).
- Ejecutar el seed 2 veces no duplica registros (idempotencia).

## Tabla involucrada

- `pq_menus`: id, text, expanded, Idparent, order, tipo, procedimiento, enabled, routeName, estructura

## Alcance

- Fuente de verdad en repo, script idempotente, integración en deploy.
- **Fuera de alcance (por ahora):** ABM completo de edición de menú en UI.

## Dependencias

- Tabla `pq_menus` creada

## Especificación (Open-Spec)

- [SPEC-015 – Menú del sistema (seed versionado)](../../05-open-spec/001-Seguridad/SPEC-015-menu-sistema-seed.md)

## Referencias

- `docs/03-historias-usuario/Historia_PQ_MENUS_seed.md` – Especificación completa del seed versionado
- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema pq_menus
