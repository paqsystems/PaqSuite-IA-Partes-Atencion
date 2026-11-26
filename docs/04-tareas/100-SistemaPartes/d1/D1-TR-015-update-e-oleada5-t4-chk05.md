# Plan de implementación — TR-015-update-e (oleada 5), hito **T4 CHK-05**

> Parte **D1** (2026-11-20). Gate T3: [D-VERIFICACION T3](../D-VERIFICACION-TR-015-update-e-t3-chk07-2026-11-20.md) **Aprobado**.  
> SoT: [TR-015-update-e](../TR-015-update-e-page-header-informes.md) · SPEC §4.4 · D-04.

## Alcance

Implementar **solo** CHK-05 / `wrapReportTitleInPanel`:

- Sidecar: `lblReportTitle` en **PageHeaderBand fila 2** dentro de `XRPanel` `pnlReportTitle` con borde visible.
- Si el título solo está en `ReportHeaderBand`, copiar texto a fila 2 y envolver (no SP).
- Sin título: texto desde `DisplayName` del informe.
- Idempotencia: panel + borde + label en fila 2 → `ya_colocados`.
- Partes: heurística prompt canónico «panel/recuadro» + título; no confundir con `setReportTitle` sin panel.
- Matriz CHK-05 → `mvp` + `applyAssert`; eval oleada 5 incluye CHK-05.

## Decisiones D1 (T4)

| ID | Decisión |
|----|----------|
| **D1-G01** | `LayoutPageHeaderTitlePanelApplier.cs`: `EnsurePageHeaderBand`; panel `pnlReportTitle` en `Row2Y`. |
| **D1-G02** | Borde v1: `BorderSide.All`, `BorderWidth` 1f, color negro. |
| **D1-G03** | Ancho panel = ancho imprimible; alto ≈ **28f**; label `MiddleCenter` dentro del panel. |
| **D1-G04** | Resolver texto: label page header → label report header → `report.DisplayName` (trim 1..200). |
| **D1-G05** | Noop: panel existe, label hijo `lblReportTitle`, Y panel ≈ Row2Y, bordes visibles. |
| **D1-G06** | Whitelist sidecar + Partes; planner LLM + `sanitize` payload vacío. |
| **D1-G07** | `tryMatchTitlePanel`: encerrar/envolver + título + panel/recuadro; excluir si solo cambio de texto sin panel. |
| **D1-G08** | FE i18n `titlePanelAlreadyPlaced`; eval `repxContains`: `pnlReportTitle`, `lblReportTitle`. |
| **D1-G09** | Cierre oleada 5: eval CHK-04..07 en un script; F1 T4 habilita cierre TR oleada. |

## Orden Parte D

1. FW applier + tests + `LayoutApplyService`
2. Matriz + eval + checklist + guía
3. Partes heurística + whitelist + planner + PHPUnit
4. FE noop + i18n ×5
5. Build/tests + reinicio sidecar + eval 4 CHK