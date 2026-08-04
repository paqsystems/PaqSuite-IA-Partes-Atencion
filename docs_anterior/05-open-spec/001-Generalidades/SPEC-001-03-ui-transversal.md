# SPEC-001-03 - UI transversal

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionadas** | [HU-GEN-03-grillas-listados](../../03-historias-usuario/001-Generalidades/HU-GEN-03-grillas-listados.md), [HU-GEN-03-layouts-grilla](../../03-historias-usuario/001-Generalidades/HU-GEN-03-layouts-grilla.md), [HU-GEN-03-patron-abm](../../03-historias-usuario/001-Generalidades/HU-GEN-03-patron-abm.md), [HU-GEN-03-exportaciones](../../03-historias-usuario/001-Generalidades/HU-GEN-03-exportaciones.md) |
| **Estado** | En revisión |
| **Revisión A1** | Apto con observaciones (2026-06-01) — decisiones humanas cerradas en B1 |

## Objetivo

Definir patrones transversales de interfaz reutilizables en todo el producto: grillas DevExtreme, **layouts persistentes** de grilla, ABM, exportaciones, **tipologías de pantalla** (plantillas) y referencia base de pivots.

## Estado de ejecución

Implementable en MVP para estándares de UI compartidos.

## Decisiones humanas

| Tema | Decisión |
|------|----------|
| Checklist UI transversal | Vive en **contexto** `grillas.md` + `patrones-abm.md`; este SPEC referencia checklist mínimo abajo |
| **Plantillas vs layouts** | **`plantillas.md`** = tipologías de **pantalla** (listado, ABM modal, dashboard, etc.). **Layouts de grilla** = formatos guardados por usuario (`proceso` + `grid_id` + `layout_name`); ver § Layouts persistentes y `grillas.md` |
| Acciones por fila en grillas | **Íconos DevExtreme + tooltip** (`hint` + i18n); no botones con texto visible — regla Cursor `.cursor/rules/base/20-frontend/28-ui-grilla-acciones-iconos-tooltip.md` |
| Layouts en MVP SistemaPartes | **In scope** del bloque transversal si existe infraestructura común (`pq_grid_layouts` + API); producto §17.8: *layout configurable/guardado si ya está disponible por infraestructura común* |
| Pivots en MVP SistemaPartes | **Solo referencia documental** en release MVP; implementación avanzada → SPEC-001-08 (sin HU MVP) |
| Unicidad `layout_name` | **Único por `proceso` + `grid_id`** (global en esa grilla; un solo registro por nombre) |
| Cantidad de layouts | **Sin límite** por usuario en MVP |
| ABM alta/edición | **Siempre modal** en bloque transversal; excepción solo si una HU de negocio (SPEC-101) lo declara explícitamente |
| Agrupación / totalización | **Siempre** en listados que usan el estándar transversal de grilla |
| Exportación PDF | **Fuera** de HU-GEN-03-exportaciones; PDF previsto en **SPEC-001-06** (emisión), no en MVP transversal Excel |

## Fuente de verdad de producto

- `docs/02-producto/SistemaPartes/PROD-Sistema-Partes.md` — grillas/consultas de negocio; **§17.8 UX de grillas** (filtros, búsqueda, orden, layout guardado condicional, exportación, paginación)
- `docs/02-producto/SistemaPartes/INDICE-Sistema-Partes.md` — DevExtreme, consultas Must

## Fuentes (contexto MONO)

`docs/00-contexto/_mono/03-ui-transversal/` — `grillas.md`, `patrones-abm.md`, `exportaciones.md`, `plantillas.md`, `pivots.md`

Detalle operativo de layouts y exportación: **`grillas.md`** y **`exportaciones.md`** (este SPEC fija criterios medibles y trazabilidad HU).

## Reglas Cursor (implementación)

- **Acciones por fila en grillas:** `.cursor/rules/base/20-frontend/28-ui-grilla-acciones-iconos-tooltip.md`
- Estándar DataGrid MONO (complementario): `.cursor/rules/mono/08-devextreme-grid-standards.md`
- Modal ABM sobre grilla: `.cursor/rules/base/20-frontend/24-ui-abm-grilla-alta-edicion-modal.md`

## Alcance

- Estándares de grillas y listados (filtros, búsqueda, orden, paginación, columnas, agrupación/totalización según `grillas.md`).
- **Layouts persistentes** de grilla (guardar, guardar como, cargar, eliminar, último layout usado).
- **Columna de acciones por fila:** íconos DevExtreme + tooltip (no botones con texto).
- Patrón ABM por defecto para mantenimientos.
- Reglas de exportación inicial (modalidades básica/formateada en grillas).
- Criterios de **tipologías de pantalla** reutilizables (`plantillas.md`).
- Referencias de pivots (sin implementación pivot avanzada en MVP).

## Layouts persistentes de grilla (resumen normativo)

Fuente detallada: `grillas.md` § *Layouts persistentes*. Este SPEC obliga a trazarlos en **HU-GEN-03-layouts-grilla**.

### Identificación

| Clave | Origen | Uso |
|-------|--------|-----|
| `proceso` | `pq_menus.procedimiento` | Agrupa layouts, exportación y telemetría |
| `grid_id` | Identificador lógico en la pantalla | Distingue varias grillas en un mismo proceso |
| `layout_name` | Nombre del formato guardado | Identifica un layout concreto |

Filtro de datos: **`proceso` + `grid_id`**.

### Contenido de un layout

Columnas visibles y orden; filtros; agrupaciones; ordenamiento; **totalizadores del pie** (`Summary` DevExtreme: sum/count/avg/min/max por columna); anchos y demás propiedades del formato que el control persista sin ambigüedad. Al **cargar** un layout, deben restaurarse los totalizadores guardados.

### Operaciones (UI en toolbar superior inmediata de la grilla)

| Acción | Comportamiento |
|--------|----------------|
| **Guardar** | Actualiza el layout seleccionado del usuario creador |
| **Guardar como** | Crea un layout nuevo (propiedad del usuario que guarda) |
| **Cargar** | Aplica el layout elegido o el último usado por el usuario |
| **Eliminar** | Solo el **creador** del layout |

Los controles de layout deben convivir en la **misma franja superior** que exportar y acciones globales del listado — no en otra zona de la pantalla.

### Reglas de negocio

1. Todos los usuarios pueden **ver y aplicar** layouts existentes del mismo `proceso` + `grid_id`.
2. Solo el **creador** puede **modificar** (Guardar) o **eliminar** un layout.
3. Cualquier usuario puede partir de un layout ajeno y crear uno propio con **Guardar como**.
4. Si la vista activa es la **plantilla original del sistema**, **Guardar** se interpreta como **Guardar como**.
5. Al abrir la pantalla, restaurar el **último layout usado** por el usuario si existe.
6. Persistencia prevista en tabla **`pq_grid_layouts`** (contrato API en TR).

### Fuera de alcance de layouts en este SPEC

- Layouts de **pivot** (SPEC-001-08 / catálogo de plantillas pivot).
- Layout del **shell** post-login (SPEC-001-01).

## Fuera de alcance (SPEC completo)

- Component library completa de diseño.
- Implementación total de pivots avanzados en portal MVP.
- Pantallas de negocio SistemaPartes (SPEC-101).
- Exportación PDF y jobs batch (ver `exportaciones.md`).

## Checklist UI transversal mínimo (reutilizable en HU)

- [ ] Grilla DevExtreme con paginación y orden por columna.
- [ ] Filtros y búsqueda acordes a `grillas.md`.
- [ ] Estados loading / empty / error en listados.
- [ ] Selector de columnas, reorden y agrupación/totalización según `grillas.md`.
- [ ] **Layouts:** guardar, guardar como, cargar, eliminar (solo creador), último layout al reabrir — según § Layouts y `HU-GEN-03-layouts-grilla`.
- [ ] Exportación según `exportaciones.md` cuando el proceso lo permita.
- [ ] Acciones ABM alineadas a permisos (`Permiso_Alta/Modi/Baja/Repo`).
- [ ] Acciones por fila como **íconos DevExtreme** con **tooltip** i18n (regla `28-ui-grilla-acciones-iconos-tooltip.md`).
- [ ] `data-testid` en acciones principales de grilla (incl. íconos de fila y controles de layout).
- [ ] Textos vía i18n (SPEC-001-01).
- [ ] Consistencia con tema activo (SPEC-001-01).

## Entregables verificables

- Guía operativa: contexto `grillas.md` + este checklist.
- Patrón ABM: `patrones-abm.md`.
- Patrón layouts: § anterior + `HU-GEN-03-layouts-grilla`.
- Patrón acciones en grilla: regla `28-ui-grilla-acciones-iconos-tooltip.md`.
- Exportación: `exportaciones.md` + `HU-GEN-03-exportaciones`.
- Consistencia: sin contradicción entre grilla, layouts, ABM y exportación.

## Criterios de aceptación medibles

- [ ] Checklist anterior citado en al menos una HU de SistemaPartes con grilla.
- [ ] Grillas, layouts, ABM y exportación sin reglas contradictorias (revisión cruzada contexto).
- [ ] Existe al menos una HU dedicada a layouts (`HU-GEN-03-layouts-grilla`) trazada a § Layouts de este SPEC.
- [ ] Producto §17.8 queda cubierto por la combinación de HU-GEN-03-* (layout condicional a infra común documentado en TR).

## Trazabilidad HU / TR

| HU | TR | Tema |
|----|-----|------|
| [HU-GEN-03-grillas-listados](../../03-historias-usuario/001-Generalidades/HU-GEN-03-grillas-listados.md) | [TR-GEN-03-grillas-listados](../../04-tareas/001-Generalidades/TR-GEN-03-grillas-listados.md) | Grilla estándar, filtros, estados, columnas |
| [HU-GEN-03-layouts-grilla](../../03-historias-usuario/001-Generalidades/HU-GEN-03-layouts-grilla.md) | [TR-GEN-03-layouts-grilla](../../04-tareas/001-Generalidades/TR-GEN-03-layouts-grilla.md) | Layouts persistentes `pq_grid_layouts` |
| [HU-GEN-03-patron-abm](../../03-historias-usuario/001-Generalidades/HU-GEN-03-patron-abm.md) | [TR-GEN-03-patron-abm](../../04-tareas/001-Generalidades/TR-GEN-03-patron-abm.md) | ABM transversal sobre grilla |
| [HU-GEN-03-exportaciones](../../03-historias-usuario/001-Generalidades/HU-GEN-03-exportaciones.md) | [TR-GEN-03-exportaciones](../../04-tareas/001-Generalidades/TR-GEN-03-exportaciones.md) | Exportación Excel desde grilla |

## Historial de cambios

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 09/06/2026 | CC PQ #3 | Layouts persisten totalizadores del pie (footer Summary) |
| 09/06/2026 | Parte I | Unificación `SPEC-001-03-ui-transversal-update` |








