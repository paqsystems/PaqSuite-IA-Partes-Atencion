# SPEC-001 – Layouts persistentes de grillas

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-001 |
| Título | Layouts persistentes de grillas |
| Épica / carpeta | 000 – Generalidades |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-001](../../03-historias-usuario/000-Generalidades/HU-001-layouts-grilla.md) |
| TR relacionada(s) | — (vincular al crear TR) |

---

## 1. Resumen ejecutivo

Los usuarios necesitan guardar y reutilizar configuraciones de grillas (columnas, filtros, agrupación, totales) sin repetir trabajo. El sistema debe persistir layouts identificados por proceso y `grid_id`, permitir uso compartido entre usuarios y restringir edición/eliminación al creador.

---

## 2. Alcance

### 2.1 En alcance

- Guardar, guardar como, cargar y eliminar layouts por grilla (`proceso` = `pq_menus.procedimiento`, `grid_id`, `layout_name`).
- Persistencia en `pq_grid_layouts` con `user_id` del creador.
- Al abrir pantalla: restaurar último layout usado por ese usuario si existe (mecanismo de “último usado” a definir en implementación).
- Regla: cualquier usuario puede **usar** cualquier layout; solo el creador **modifica o elimina** el suyo.

### 2.2 Fuera de alcance

- Layouts globales sin `user_id` distintos del modelo propuesto salvo evolución acordada.
- Multi-empresa / empresa activa (MONO).

---

## 3. Actores y contexto

- **Actor:** usuario operador de grillas (listados/ABM).
- **Precondiciones:** tabla `pq_grid_layouts`, estándar de grillas DevExtreme, API CRUD de layouts.

---

## 4. Comportamiento funcional

1. **Guardar:** persiste columnas visibles, orden, filtros, agrupaciones, ordenamiento, totalizadores. Actualizar layout seleccionado o “Guardar como” con nombre nuevo. Guardar sobre plantilla original equivale a “Guardar como”.
2. **Cargar:** selector de layouts filtrado por `proceso` + `grid_id`; al elegir, aplicar configuración de inmediato.
3. **Eliminar:** solo si `user_id` coincide con el usuario actual; ocultar o deshabilitar para layouts ajenos.
4. **Identificación:** cada grilla: `proceso`, `layout_name`, `grid_id` (ej. `default`, `master`, `detalle`).

---

## 5. Criterios verificables

- [ ] CRUD de layouts contra `pq_grid_layouts` con filtros por proceso + grid_id.
- [ ] Solo el creador puede actualizar o borrar un layout concreto.
- [ ] Todos pueden listar y aplicar cualquier layout del proceso/grid.
- [ ] Restauración del último layout usado por usuario al entrar a la pantalla (según diseño de “último usado”).
- [ ] Alineación con `.cursor/rules/24-devextreme-grid-standards.md`.

---

## 6. Impacto técnico (visión para TR)

- **Datos:** `pq_grid_layouts` (id, user_id, proceso, grid_id, layout_name, layout_data, is_default, timestamps).
- **API:** endpoints de listado, create, update, delete, posible registro de uso.
- **Frontend:** integración en wrapper DataGrid / pantallas con `proceso` y `grid_id`.

---

## 7. Riesgos y supuestos

- “Último usado” puede requerir tabla auxiliar o campo de preferencias; debe definirse en TR sin contradir CA de la HU.
- Layouts compartidos: claridad UX de quién es el autor al editar.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-001 |

---

**Trazabilidad:** [HU-001](../../03-historias-usuario/000-Generalidades/HU-001-layouts-grilla.md).
