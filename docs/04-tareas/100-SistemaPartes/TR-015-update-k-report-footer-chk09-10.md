# TR-015-update-k – Report Footer (CHK-09, CHK-10, oleada 9)

| Campo | Valor |
|-------|--------|
| HU / SPEC | [HU-015-update-k](../../03-historias-usuario/100-SistemaPartes/HU-015-update-k-report-footer-chk09-10.md) · [SPEC oleada 9](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-9-report-footer-chk09-10-update.md) |
| Estado | **Finalizado** (F 2026-11-24, smoke CHK-09/10 OK) |

## Entrega

| Capa | Artefacto |
|------|-----------|
| Sidecar | `LayoutReportFooterApplier`, `LayoutReportSumApplier`, whitelist |
| Host | `DesignLayoutReportFooterHeuristics`, whitelist, LLM prompt |
| Matriz | CHK-09/10 `mvp` |
| Script | `eval-emission-ia-oleada9-chk09-10-apply.ps1` |

## Tests (E)

| Comando | Resultado |
|---------|-----------|
| `phpunit --filter DesignLayout` | OK |
| `dotnet test --filter LayoutReportFooter` | OK (3) |
| `eval-emission-ia-oleada9-chk09-10-apply.ps1` | Ver F1 (sidecar) |

## F1 / F

| F1 | `D-VERIFICACION-TR-015-update-k-chk09-10-2026-11-24.md` |
| F | `F-TR-015-update-k-oleada9-chk09-10.md` |