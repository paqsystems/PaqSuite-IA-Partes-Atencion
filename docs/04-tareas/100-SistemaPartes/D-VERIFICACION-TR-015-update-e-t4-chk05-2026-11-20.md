# D-VERIFICACION — TR-015-update-e T4 (CHK-05)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-e](./TR-015-update-e-page-header-informes.md) § T4 |
| SPEC | [SPEC-015-update oleada 5](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) §4.4 |
| D1 | [D1-TR-015-update-e-oleada5-t4-chk05](./d1/D1-TR-015-update-e-oleada5-t4-chk05.md) |
| T3 base | [D-VERIFICACION T3](./D-VERIFICACION-TR-015-update-e-t3-chk07-2026-11-20.md) |
| Fecha cierre F1 | 2026-11-20 |

---

## Verificación agente (F1 hito T4)

### Resultado

**Aprobado** — T4 CHK-05 cierra la oleada 5 page header (CHK-04..07) a nivel implementación + apply script.

### Alcance

Solo **CHK-05** / `wrapReportTitleInPanel`.

### Entregables

| Capa | Artefacto |
|------|-----------|
| FW | `LayoutPageHeaderTitlePanelApplier.cs`, tests, `LayoutApplyService` |
| Partes BE | `tryMatchTitlePanel`, whitelist, planner + LLM |
| Partes FE | `titlePanelAlreadyPlaced` (×5), turno noop |
| Matriz | CHK-05 `mvp` + `applyAssert` |
| Doc | Guía manual §CHK-05; checklist FW |

### Evidencia manual

- Apply OK + **re-aplicar** con `pnlReportTitle` ya presente → mensaje `titlePanelAlreadyPlaced` (usuario, 2026-11-20).

### Tests (cierre F1 — 2026-11-20)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | **OK** — 47 tests, 148 assertions |
| `dotnet test … -c Release --filter PageHeader` | **OK** — 13 tests |
| `.\eval-emission-ia-oleada5-apply.ps1` | **OK** — PASS CHK-04..07 (`passed=4 failed=0`) |

### Criterios T4

| ID | Descripción | Veredicto |
|----|-------------|-----------|
| CA-B5-05 | Planner CHK-05 → `wrapReportTitleInPanel` | **OK** |
| AC-05 | `pnlReportTitle` fila 2 con borde | **OK** (.NET + eval) |
| CA-B5-06 | Apply oleada sin regresión L-B-10 | **OK** (eval integrado) |

### Observaciones

- v1 puede coexistir `lblReportTitle` en `ReportHeaderBand` y copia en page header (SPEC copia texto, no exige vaciar report header).

### Cierre oleada 5 (SDD)

Los cuatro hitos T1–T4 tienen F1 **Aprobado**. Pendiente revisión humana TR → **E/F** smoke UI completo y estado **Finalizado** si corresponde.