# SPEC-015-update (oleada 9) — Report Footer (CHK-09, CHK-10)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-9-report-footer-chk09-10 |
| Título | Pie de informe (Report Footer) y total de registros vía Smart Capture |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Especificado** (2026-11-24) |
| SPEC previa | [Oleada 7b CHK-20](./SPEC-015-ia-diseno-informes-oleada-7b-group-sum-chk20-update.md) |
| HU | [HU-015-update-k](../../../03-historias-usuario/100-SistemaPartes/HU-015-update-k-report-footer-chk09-10.md) |
| TR | [TR-015-update-k](../../../04-tareas/100-SistemaPartes/TR-015-update-k-report-footer-chk09-10.md) |
| Proceso piloto | `partes.informes.consultaDetallada` |

**GEN-03 + GEN-15** — mutación REPX en sidecar; heurística en Partes.

---

## 1. Resumen

- **CHK-09:** `Agregá un pie de informe` → `ensureReportFooter` (crea `ReportFooterBand`, distinto de `PageFooterBand` de CHK-03).
- **CHK-10:** `Totalizá todos los registros en el pie del informe` → `addReportSum` `{ summary: "count" }` con copy **«Total registros:»** + `sumCount()` en `ReportFooterBand`. Requiere pie de informe (batch 09→10 o layout previo).

## 2. Alcance

| CHK | Acción | assert REPX |
|-----|--------|-------------|
| 09 | `ensureReportFooter` | `ReportFooterBand` |
| 10 | `addReportSum` count | `Total registros`, `sumCount` |

Fuera: CHK-11..14, `addGroupSum`, CHK-03 PageFooter.

## 3. Criterios

| ID | Criterio |
|----|----------|
| CA-B9-01 | Planner CHK-09/10 → acciones matriz |
| CA-B9-02 | Apply + script `eval-emission-ia-oleada9-chk09-10-apply.ps1` PASS |
| CA-B9-03 | Re-apply noop `ya_existe` / `ya_totalizado` |
| CA-B9-04 | Sin regresión CHK-20 |

## 4. A1

**Apto** — CHK-10 canónico = **count** de registros (no suma global de minutos en v1).