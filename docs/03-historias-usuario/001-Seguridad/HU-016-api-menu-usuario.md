# HU-016 – API de menú del usuario

## Épica
001 – Seguridad y Acceso

## Clasificación
MUST-HAVE

## Rol
Usuario autenticado

## Narrativa

Como usuario autenticado quiero que el sistema exponga un endpoint que devuelva mi menú de navegación filtrado por mis permisos (roles y atributos), para que el sidebar muestre solo las opciones a las que tengo acceso.

## Criterios de aceptación

- Existe un endpoint protegido (ej. `GET /api/v1/user/menu`) que devuelve el menú del usuario autenticado.
- El endpoint utiliza el **usuario de la sesión** para resolver roles vía **`Pq_Permiso`** (asignación **usuario → rol**, sin empresa en MONO).
- Los permisos de menú se resuelven con `pq_rol`, `pq_rol_atributo` y `pq_menus` según las reglas siguientes.
- Si el rol tiene `AccesoTotal = true`, se devuelven todas las opciones de `pq_menus` con `enabled = 1`.
- Si el rol no tiene AccesoTotal, solo se incluyen las opciones donde `pq_rol_atributo` tenga al menos un permiso (Alta, Baja, Modi o Repo) para ese rol.
- Si el usuario tiene **múltiples roles**, se aplica **unión**: se incluye cualquier opción autorizada por **algún** rol.
- Solo se incluyen ítems de `pq_menus` con `enabled = 1` y `text` no vacío.
- La respuesta incluye la estructura jerárquica: `id`, `text`, `parentId`, `orden`, `routeName` (o `procedimiento`) para que el frontend construya rutas.
- La respuesta se ordena por `parentId` y `orden`.
- Si el usuario no tiene asignaciones en `Pq_Permiso` o ningún rol le otorga ítems, se devuelve lista vacía (o menú mínimo según diseño, ej. Inicio, Perfil).
- El endpoint aplica middleware de **autenticación** únicamente (**no** middleware de empresa / `X-Company-Id` en MONO).

## Tablas involucradas

- `pq_menus`: id, text, idparent, orden, procedimiento, routeName, enabled
- `pq_permiso`: **IDUsuario**, **IDRol** (MONO; columna `IDEmpresa` del legado no participa en la lógica)
- `pq_rol`: id, acceso_total
- `pq_rol_atributo`: id_rol, id_opcion_menu, permiso_alta, permiso_baja, permiso_modi, permiso_repo

## Reglas de negocio

- Usuario sin filas en `Pq_Permiso` o sin roles que autoricen menú: lista vacía o solo ítems públicos (ej. Inicio, Perfil).
- Usuario con rol AccesoTotal: menú completo (opciones habilitadas de `pq_menus`).
- Usuario con rol sin AccesoTotal: solo opciones con atributos con al menos un permiso en `pq_rol_atributo`.
- Se admite esquema con columnas en PascalCase o snake_case.

## Dependencias

- HU-001 (Login)
- HU-014 (Atributos de rol)
- HU-015 (Menú seed versionado)
- Tablas `pq_menus`, `pq_permiso`, `pq_rol`, `pq_rol_atributo`

## Especificación (Open-Spec)

- [SPEC-016 – API de menú del usuario](../../05-open-spec/001-Seguridad/SPEC-016-api-menu-usuario.md)

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquemas
- `docs/04-tareas/001-Seguridad/TR-015-menu-sistema.md` – Menú del sistema
- RolAtributoController (lógica similar de pq_menus + pq_rol_atributo)
