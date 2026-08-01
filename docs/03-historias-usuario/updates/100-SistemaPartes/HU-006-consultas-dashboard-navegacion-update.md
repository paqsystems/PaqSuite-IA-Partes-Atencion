# HU-006-update – Filtro `es_tarea` y Paquete de Horas (cuenta corriente)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-006-update |
| Título | Consultas/dashboard con `es_tarea` y rehacer Paquete de Horas |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-07-31 |
| SPEC origen | [SPEC-006-consultas-dashboard-navegacion-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion-update.md) |
| TR relacionada(s) | [TR-006-consultas-dashboard-navegacion-update](../../../04-tareas/updates/100-SistemaPartes/TR-006-consultas-dashboard-navegacion-update.md) |
| HU base | [HU-006-consultas-dashboard-navegacion](../../100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 31/07/2026 |
| Control # | 1 |
| Ítems | Informes/Dashboard filtran EsTarea; Paquete de Horas rehacer; hallazgo cuenta corriente |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

---

## Narrativa

Como usuario de informes Partes  
quiero que detalle/agrupadas/dashboard muestren solo tareas, y que Paquete de Horas sea una cuenta corriente con saldo  
para analizar dedicación y paquetes anticipados sin mezclar semánticas.

## Alcance incluido

- Detalle, agrupadas, dashboard: solo `esTarea = true`.
- Paquete de Horas rehace: filtros periodo+cliente; mismos atributos que detalle; sin filtro `es_tarea`; fila Saldo inicial; columna Saldo; pivot sin Saldo.

## Fuera de alcance

- Alta de compras de horas (proceso a definir).
- DDL (HU-001-update).

## Criterios de aceptación

- [x] **CA-U01** Detalle/agrupadas/dashboard excluyen `esTarea = false`.
- [x] **CA-U02** Paquete de Horas incluye ambos tipos de movimiento en el periodo.
- [x] **CA-U03** Existe fila «Saldo inicial» con acumulado exclusive `fechaDesde` (+ tarea / − compra).
- [x] **CA-U04** Columna Saldo es running balance en grilla.
- [x] **CA-U05** Pivot del paquete no incluye Saldo.
- [x] **CA-U06** Filtro cliente disponible para asistente/supervisor.

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-31 | Parte G: HU-update CC-PQ 31/07/2026. |
