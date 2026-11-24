# D-VERIFICACION — TR-015-update-k (CHK-09, CHK-10)

| Fecha F1 | 2026-11-24 |

**Aprobado** — automatizado + smoke UI CHK-09 y CHK-10 **OK** (usuario 2026-11-24).

| Comando | OK |
|---------|-----|
| phpunit DesignLayout | 53 tests |
| dotnet LayoutReportFooter | 3 |
| eval-emission-ia-oleada9-chk09-10-apply.ps1 | PASS |

| CHK | Prompt | Smoke |
|-----|--------|-------|
| 09 | Agregá un pie de informe | **OK** (usuario 2026-11-24) |
| 10 | Totalizá todos los registros en el pie del informe | **OK** (usuario 2026-11-24) |