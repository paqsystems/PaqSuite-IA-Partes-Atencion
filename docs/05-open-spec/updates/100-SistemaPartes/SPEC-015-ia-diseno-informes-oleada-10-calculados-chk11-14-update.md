# SPEC-015-update (oleada 10) — Calculados CHK-11..14

| Campo | Valor |
|-------|--------|
| Estado | **Especificado** (2026-11-24) |
| HU | [HU-015-update-l](../../../03-historias-usuario/100-SistemaPartes/HU-015-update-l-calculados-chk11-14.md) |
| TR | [TR-015-update-l](../../../04-tareas/100-SistemaPartes/TR-015-update-l-calculados-chk11-14.md) |
| Depende | Oleada 9 (CHK-14 report footer) |

## Acciones

- `addCalculatedField` (sum | iif)
- `addDetailCalculatedLabel`
- `addReportSum` con `calculatedField`

CHK-11 prompt: `Creá un campo calculado que sume duracionMinutos` (solo numéricos schema).