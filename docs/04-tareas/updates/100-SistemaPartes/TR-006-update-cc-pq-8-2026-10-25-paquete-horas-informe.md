# TR-006-update — Informe Paquete de Horas saldo y destaque (CC PQ #8)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-006-consultas-dashboard-navegacion](../../100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) |
| **HU-update** | [HU-006-update-cc-pq-8-2026-10-25-paquete-horas-informe](../../../03-historias-usuario/updates/100-SistemaPartes/HU-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **SPEC-update** | [SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe](../../../05-open-spec/updates/100-SistemaPartes/SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **Estado** | Pendiente |
| **Referencia** | [`18-obtencion-saldo-por-renglon.md`](../../../02-producto/Sistema-Partes-IA/18-obtencion-saldo-por-renglon.md) |
| **Última actualización** | 2026-10-30 |

## 1) Delta backend (SP)

| Pieza | Acción |
|-------|--------|
| `pq_sp_partes_informe_paquete_horas_*` (nombre familia vigente en TR-006 base) | CTE saldo anterior por cliente; window/running sum por `fecha`,`id`; signo `es_tarea` |
| API envelope | Campo `saldoMinutos` o equivalente por fila + fila sintética `saldoInicial` |

Orden explícito: **`ORDER BY cliente_id, fecha ASC, id ASC`** tras insertar fila saldo inicial por cliente.

## 2) Delta frontend

| Pieza | Acción |
|-------|--------|
| `PaqueteHorasPage` / grid | Columna Saldo formateada `hh:mm`; fila saldo inicial |
| `cellPrepared` / customizeText Duración | Si `esTarea === false`: prefijo `-` y/o clase `partes-paquete-horas-duration-credit` (rojo) |
| Export grilla | Misma convención visual o texto negativo |

## 3) Tareas

| ID | Descripción |
|----|-------------|
| T1 | Reescribir/ajustar SP saldo (fixture SQL unit o integración) |
| T2 | Mapper API → grid |
| T3 | Estilos + i18n etiqueta saldo inicial |
| T4 | Tests integración saldo; Vitest formatter; E2E informe |

## 4) Tests mínimos

- Fixture 2 clientes, mezcla tareas/paquetes: saldos no cruzados.
- Misma fecha múltiples filas: orden por `id`.
- Celda paquete roja/negativa en UI test.
