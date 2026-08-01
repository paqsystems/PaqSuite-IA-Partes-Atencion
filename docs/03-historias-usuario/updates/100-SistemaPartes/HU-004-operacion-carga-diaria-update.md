# HU-004-update – Carga diaria y `es_tarea`

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-004-update |
| Título | Carga diaria: listar y grabar solo tareas (`es_tarea = true`) |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-07-31 |
| SPEC origen | [SPEC-004-operacion-carga-diaria-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-004-operacion-carga-diaria-update.md) |
| TR relacionada(s) | [TR-004-operacion-carga-diaria-update](../../../04-tareas/updates/100-SistemaPartes/TR-004-operacion-carga-diaria-update.md) |
| HU base | [HU-004-operacion-carga-diaria](../../100-SistemaPartes/HU-004-operacion-carga-diaria.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 31/07/2026 |
| Control # | 1 |
| Ítem | Carga de Partes — asignar/filtrar EsTarea |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

---

## Narrativa

Como asistente o supervisor  
quiero que la carga diaria solo muestre y grabe tareas reales (`esTarea = true`)  
para no mezclar compras de horas en el circuito de registración diaria.

## Alcance incluido

- Listado de carga: solo `es_tarea = true`.
- Alta/edición: forzar `es_tarea = true`.

## Fuera de alcance

- DDL (HU-001-update); masivo; informes.

## Criterios de aceptación

- [x] **CA-U01** Listado de carga no incluye filas con `esTarea = false`.
- [x] **CA-U02** Tras alta/edición desde carga, `esTarea` queda `true`.

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-31 | Parte G: HU-update CC-PQ 31/07/2026. |
