# CC-PQ-18 — Oleada 3 apariencia controles (Smart Capture diseño)

| Campo | Valor |
|-------|--------|
| Fecha | 2026-11-18 |
| Alcance | `setControlAppearance` — Framework sidecar + planner Partes |
| SPEC/HU/TR | [SPEC-015 oleada 3](../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-3-apariencia-update.md) · [HU-015-update-c](../03-historias-usuario/100-SistemaPartes/HU-015-update-c-apariencia-controles-informes.md) · [TR-015-update-c](../04-tareas/100-SistemaPartes/TR-015-update-c-apariencia-controles-informes.md) |
| CHK nuevos | CHK-08-format, CHK-27 |
| Evidencia | [D-VERIFICACION-TR-015-update-c-2026-11-18.md](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-c-2026-11-18.md) |

## Veredicto

**Cerrado** — F1 + smoke OK (2026-11-18). Ver [D-CIERRE oleada3](../04-tareas/updates/100-SistemaPartes/D-CIERRE-oleada3-apariencia-2026-11-18.md).

## Observaciones

- CHK-08-format requiere `lblReportTitle` o label en Report Header.
- CHK-27 usa layout `partes_consultaDetallada_principal` en applyAssert.
- Oleada 3b (padding, bordes, visible) permanece diferida en matriz.