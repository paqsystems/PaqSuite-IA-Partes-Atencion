# HU-001 – Layouts persistentes de grillas

## Épica
000 – Generalidades

## Clasificación
SHOULD-HAVE

## Rol
Usuario que opera con grillas (listados o ABMs de Tablas)

## Narrativa

Como usuario que trabajo frecuentemente con grillas quiero guardar y recuperar formatos personalizados (columnas, filtros, agrupaciones, totalizadores) para no tener que reconfigurar la vista cada vez que accedo a la pantalla.

## Criterios de aceptación

### Guardar layout

- El usuario puede guardar el formato actual de la grilla con un nombre.
- El layout incluye: columnas visibles, orden de columnas, filtros aplicados, agrupaciones, ordenamiento, totalizadores configurados.
- "Guardar" sobre un layout seleccionado actualiza ese layout.
- "Guardar como..." crea un nuevo layout a partir del actual, permitiendo asignar otro nombre.
- "Guardar" sobre la plantilla original, actùa como "Guardar como..."
- Los layouts son compartidos: todos los usuarios pueden ver y usar cualquier layout definido.

### Cargar layout

- Al abrir una pantalla con grilla, se presenta el formato utilizado la última vez por dicho usuario (si existe).
- El usuario puede elegir entre varios layouts disponibles para esa grilla.
- Al seleccionar un layout, la grilla aplica inmediatamente su configuración.

### Eliminar layout

- El usuario puede eliminar un layout solo si fue creado por él mismo.
- Los layouts creados por otros usuarios no muestran opción de eliminar (o se muestra deshabilitada).

### Identificación de grillas

- Cada grilla se identifica por `proceso` (valor de `pq_menus.procedimiento`), `layout_name` y `grid_id`  (cuando hay varias grillas en la misma pantalla, ej. "default", "master", "detalle").
- Los layouts se filtran por proceso + grid_id.

## Tabla involucrada

- `pq_grid_layouts`: id, user_id, proceso, grid_id, layout_name, layout_data, is_default, created_at, updated_at

## Reglas de negocio

- Solo el creador (user_id) puede modificar o eliminar un layout.
- Todos los usuarios pueden usar cualquier layout.
- El "último usado" por usuario se determina por registro de uso (puede requerir tabla auxiliar o campo en preferencias; la implementación lo definirá).

## Dependencias

- Tabla `pq_grid_layouts` creada en Dictionary DB.
- Estándar de grillas implementado (`.cursor/rules/24-devextreme-grid-standards.md`).
- API para CRUD de layouts.

## Especificación (Open-Spec)

- [SPEC-001 – Layouts persistentes de grillas](../../05-open-spec/000-Generalidades/SPEC-001-layouts-grilla.md)

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema pq_grid_layouts
- `.cursor/rules/24-devextreme-grid-standards.md` – Estándar de grillas DevExtreme
