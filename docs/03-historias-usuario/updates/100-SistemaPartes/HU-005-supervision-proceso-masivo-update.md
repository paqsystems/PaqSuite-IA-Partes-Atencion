# HU-005-update – Proceso masivo y `es_tarea`

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-005-update |
| Título | Proceso masivo: solo tareas (`es_tarea = true`) |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-07-31 |
| SPEC origen | [SPEC-005-supervision-proceso-masivo-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-005-supervision-proceso-masivo-update.md) |
| TR relacionada(s) | [TR-005-supervision-proceso-masivo-update](../../../04-tareas/updates/100-SistemaPartes/TR-005-supervision-proceso-masivo-update.md) |
| HU base | [HU-005-supervision-proceso-masivo](../../100-SistemaPartes/HU-005-supervision-proceso-masivo.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 31/07/2026 |
| Control # | 1 |
| Ítem | Proceso Masivo — filtrar EsTarea |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

---

## Narrativa

Como supervisor  
quiero que el proceso masivo solo trabaje sobre tareas (`esTarea = true`)  
para no cerrar ni alterar compras de horas en el lote de supervisión.

## Alcance incluido

- Listado e ids del masivo: solo `es_tarea = true`.
- Lotes (cerrado / atributos) rechazan ids con `es_tarea = false` (atómico).

## Fuera de alcance

- DDL; paquete de horas; alta de compras.

## Criterios de aceptación

- [x] **CA-U01** Grilla/ids masivo no incluyen `esTarea = false`.
- [x] **CA-U02** Lote con id `esTarea = false` → error; cero cambios.

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-31 | Parte G: HU-update CC-PQ 31/07/2026. |
