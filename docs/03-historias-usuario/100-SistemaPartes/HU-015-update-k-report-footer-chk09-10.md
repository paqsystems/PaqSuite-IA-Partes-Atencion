# HU-015-update-k – Pie de informe (CHK-09, CHK-10)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-015-update-k |
| Estado | **Finalizado** (2026-11-24) |
| SPEC | [SPEC oleada 9](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-9-report-footer-chk09-10-update.md) |
| TR | [TR-015-update-k](../../04-tareas/100-SistemaPartes/TR-015-update-k-report-footer-chk09-10.md) |

## Narrativa

Como diseñador quiero agregar el **pie del informe** (report footer) y **contar registros** ahí, distinto del pie de página y del total por grupo.

## Criterios

| AC | Descripción |
|----|-------------|
| AC-01 | CHK-09 crea `ReportFooterBand` |
| AC-02 | CHK-10 muestra total de registros en ese pie |
| AC-03 | Re-apply idempotente |