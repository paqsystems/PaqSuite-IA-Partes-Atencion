# Plan de implementación — TR-015-update-f (oleada 6, **CHK-18**)

> Parte **D1** (2026-11-21). Decisiones humanas confirmadas. Parte **D** en curso.

## Decisiones acordadas

| Tema | Elección |
|------|----------|
| Pie de grupo vacío | Altura **28px** |
| Código sidecar | Applier dedicado `LayoutGroupByFieldApplier.cs` |
| Prompts | Solo canónico «Agrupá por cliente» (CHK-18) |
| SDD | HU + SPEC-update + TR-update-f + Jira |
| Eval apply | `partes_consultaDetallada_principal`; manual flexible |
| Estado parcial | Reparar pie faltante; noop solo header+footer completos |
| CHK-19 | Fuera de corrida |
| Trazabilidad | Este archivo + TR/HU/SPEC |

## Alcance

CHK-18: `addGroupByField` → `clienteNombre` → REPX con `GroupHeaderBand`, `GroupFooter`, noop `ya_agrupado`, mensaje SC.

Ver TR-015-update-f para CA y DoD.