# HU-006-update — Informe Paquete de Horas saldo y destaque (CC PQ #8)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-006-consultas-dashboard-navegacion](../../100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) |
| **SPEC-update** | [SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe](../../../05-open-spec/updates/100-SistemaPartes/SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **TR-update** | [TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe](../../../04-tareas/updates/100-SistemaPartes/TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **Estado** | Pendiente |
| **Jira** | [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52) |

## Criterios de aceptación adicionales

- [ ] **CA-03** Filas `es_tarea = 0` con duración destacada (negativo/rojo).
- [ ] **CA-04** Filas `es_tarea = 1` presentación normal.
- [ ] **CA-05** Saldo inicial desde movimientos anteriores al periodo por cliente.
- [ ] **CA-06** Saldo por renglón acumula según `es_tarea`.
- [ ] **CA-07** Saldos aislados por cliente.
