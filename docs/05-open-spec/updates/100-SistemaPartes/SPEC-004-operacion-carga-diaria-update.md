# SPEC-004-update – Carga diaria: duración decimal, export Excel y plantillas GEN-11

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-004-update |
| Título | Carga diaria — duración decimal visible, export Excel y plantillas compartidas (GEN-11) |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-09-15 |
| SPEC base | [SPEC-004-operacion-carga-diaria](../../100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) |
| HU relacionada(s) | [HU-004-operacion-carga-diaria-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-004-operacion-carga-diaria-update.md) |
| TR relacionada(s) | [TR-004-operacion-carga-diaria-update](../../../04-tareas/updates/100-SistemaPartes/TR-004-operacion-carga-diaria-update.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Fuente | `00-ControlCalidad-PQ` |
| Fecha | 09/08/2026 |
| Control | #3 |
| Ítems | Carga Diaria — duración decimal; Plantillas de grillas de otros usuarios |

---

## 1. Resumen del delta

Tres cambios de alcance sobre SPEC-004 (el default de tipo de tarea **no** se redefine aquí: ya está en §4.4 / R-OP; el incumplimiento es bug de implementación, ver HU/TR-004-update):

1. **Duración decimal visible** en la grilla de carga diaria (además de `hh:mm`), exportable.
2. **Exportación Excel** de esa grilla pasa a **Must** (deja de estar fuera de alcance).
3. **Plantillas de layout GEN-11 compartidas** entre usuarios del mismo `proceso`+`gridId`. No hay parámetro de instalación ni de `PQ_PARAMETROS_GRAL` para optar por ver o no las ajenas.

Adopción Framework (MUST, no reimplementar):

```text
Plantillas de grilla en Carga diaria: adoptar GEN-11. UI/motor = ProcessDataGrid / GridLayoutToolbar.
SoT: Framework SPEC-001-11. Host: proceso `partes.carga.diaria`, gridId `cargaDiaria`; API `/api/v1/grid-layouts*`.
Export Excel de la grilla: adoptar GEN-11-export. UI/motor = `canExport` de ProcessDataGrid. No reimplementar.
```

---

## 2. Cambios de alcance vs SPEC-004 base

### 2.1 En alcance (agregar)

- Columna de **duración en horas decimales** visible en la grilla (`duracion_minutos / 60`), además de la columna en `hh:mm`.
- **Exportación Excel** del conjunto presentado en la grilla de carga diaria (capacidad GEN-11-export / `ProcessDataGrid`).

### 2.2 Fuera de alcance (corregir)

- Eliminar: «Exportación Excel como Must del MVP de carga».
- Queda fuera: importación masiva Excel (SPEC-009). El export de grilla **no** es esa importación.

### 2.3 §4.5 Listado — duración

Sustituir la viñeta de duración de grilla por:

- **Duración (hh:mm):** columna visible; celdas en **`hh:mm`** (ej. `02:15`, `15:30`, `14:45`).
- **Duración decimal:** columna visible adicional; valor = `duracion_minutos / 60` (horas). Ejemplos: `02:15` → `2.25`; `15:30` → `15.5`; `14:45` → `14.75`.
- Persistencia y API siguen en minutos. `duracionMinutos` puede seguir oculta por defecto (column chooser).
- Sumatoria DevExtreme sobre la columna decimal (o equivalente `duracionHoras`).
- Ambas columnas de duración **salen en el Excel** exportado (valor numérico decimal en la columna decimal; `hh:mm` o texto equivalente en la de reloj, según el export GEN).

### 2.4 §4.5 / impacto técnico — export Excel

La grilla de carga diaria **debe** exponer export Excel del framework (`canExport` / toolbar GEN). No inventar un exporter propio.

### 2.5 Plantillas de grilla (nuevo §4.10)

Carga diaria de [host Partes]: adoptar GEN-11. UI/motor = `ProcessDataGrid` + `GridLayoutToolbar`. No reimplementar.

| ID | Norma |
|----|--------|
| R-OP-15 | El listado de plantillas del par `proceso`+`gridId` es **compartido**: cualquier usuario autenticado del módulo **ve y puede aplicar** las plantillas de otros usuarios del mismo par. |
| R-OP-16 | **No** existe parámetro de instalación ni clave en `PQ_PARAMETROS_GRAL` para ocultar plantillas ajenas. El único flag GEN es `gridLayoutsEnabled` (oculta la toolbar de layouts; la grilla sigue operativa). |
| R-OP-17 | Solo el **creador** actualiza o elimina su plantilla; ajenas: aplicar y «Guardar como» habilitados; Guardar/Eliminar deshabilitados. Propias: sufijo visual ` (*)` (no persistido). |
| R-OP-18 | El contrato es el de GEN-11 (API `/api/v1/grid-layouts*`). El host no debe filtrar el listado solo por `user_id` del caller. |

**Alcance de pantallas:** el hallazgo se reportó en Carga de Partes Diarios. La API de layouts es **única del host**; al corregir el listado compartido, **masivo e informes** que usen `ProcessDataGrid` heredan el mismo contrato sin SPEC-update propio.

---

## 3. Reglas numeradas (delta)

| ID | Regla |
|----|--------|
| R-OP-05e | (reemplazo) Grilla: duración visible en **`hh:mm` y en horas decimales** (`minutos/60`); sumatoria sobre decimales; API/DB en minutos. |
| R-OP-05f | (nueva) Grilla de carga diaria: export Excel GEN del conjunto presentado. |
| R-OP-15…18 | Plantillas compartidas GEN-11; sin param opt-in; autoría del creador. |

---

## 4. Criterios verificables (delta)

- [ ] Grilla muestra duración en `hh:mm` **y** en decimal (`2.25` / `15.5` / `14.75` para los ejemplos del CC).
- [ ] El Excel exportado incluye ambas representaciones de duración (o al menos la decimal numérica además de `hh:mm`).
- [ ] Toolbar de export Excel usable en carga diaria (web).
- [ ] Usuario A ve y puede aplicar una plantilla guardada por usuario B en el mismo `proceso`+`gridId` (carga diaria).
- [ ] Usuario A no puede Guardar/Eliminar la plantilla de B (403 GEN); sí «Guardar como».
- [ ] No hay parámetro Partes para «ver plantillas de otros».

---

## 5. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Frontend | Segunda columna decimal visible; `canExport` en `ProcessDataGrid` de carga; i18n captions |
| Backend layouts | `GET /api/v1/grid-layouts` lista **todas** las plantillas del par (no solo `user_id`); DTO `isOwner` GEN-11 |
| Tests | Vitest decimal; Feature listado layouts ajeno; E2E humo export / default tipo (este último en TR-update, sin cambio SPEC) |

---

## 6. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-09-15 | Parte G CC-PQ #3 (09/08/2026): duración decimal + Excel Must + plantillas GEN-11 compartidas. |
