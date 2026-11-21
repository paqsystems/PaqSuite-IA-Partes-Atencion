# TR-015-update-f – Agrupación por cliente (CHK-18, oleada 6)

| Campo | Valor |
|-------|--------|
| **HU** | [HU-015-update-f](../../03-historias-usuario/100-SistemaPartes/HU-015-update-f-agrupacion-cliente-chk18.md) |
| **SPEC** | [SPEC-015-update oleada 6](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-6-agrupacion-chk18-update.md) |
| **D1** | [D1-TR-015-update-f-chk18](./d1/D1-TR-015-update-f-chk18.md) |
| **TR previa** | [TR-015-update-e](./TR-015-update-e-page-header-informes.md) |
| **Matriz** | `PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json` (CHK-18) |
| **Estado** | **Finalizado** (2026-11-21 — F1 CHK-18) |
| **Jira** | PIAPARTES-65 |

---

## 1) Objetivo

Cerrar **CHK-18**: prompt «Agrupá por cliente» → apply `addGroupByField` con header + footer vacío, noop idempotente, eval y smoke manual.

---

## 2) Sidecar (Framework)

| Artefacto | Entrega |
|-----------|---------|
| `LayoutGroupByFieldApplier.cs` | Header 28px + footer vacío 28px; noop `ya_agrupado`; reparación pie |
| `LayoutApplyService.cs` | Dispatcher `addGroupByField` → applier |
| `LayoutGroupByFieldTests.cs` | 3 tests unitarios |
| Matriz CHK-18 | `applyAssert.reportUrl = partes_consultaDetallada_principal` |
| `eval-emission-ia-oleada6-apply.ps1` | apply + re-apply CHK-18 |

---

## 3) Host Partes

| Artefacto | Entrega |
|-----------|---------|
| `emissionDesignSmartCaptureTurn.ts` | Rama noop `ya_agrupado` |
| `common.json` (×5) | `partes.emissionDesign.group.alreadyGrouped` |
| `DesignLayoutActionPlannerTest.php` | `test_heuristics_chk18_agrupar_por_cliente` |
| Guía manual | §CHK-18 noop + script oleada 6 |

Sin cambios whitelist (tipo ya existía).

---

## 4) Tests (parte E)

| Prueba | Resultado (2026-11-21) |
|--------|-------------------------|
| `phpunit --filter DesignLayout` | OK — 48 |
| `dotnet test --filter LayoutGroupByField` | OK — 3 |
| `eval-emission-ia-oleada6-apply.ps1` | **PASS CHK-18** |

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test --filter LayoutGroupByField

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-oleada6-apply.ps1
```

Smoke manual UI: pendiente F1.

---

## 5) Evidencia F1

[D-VERIFICACION-TR-015-update-f-chk18-2026-11-21.md](./D-VERIFICACION-TR-015-update-f-chk18-2026-11-21.md) — **F1 Aprobado** (smoke manual + eval oleada 6).

Captura: [`evidence/chk-18-smart-capture-2026-11-21.png`](./evidence/chk-18-smart-capture-2026-11-21.png).

---

## 6) Confirmación alcance

Solo CHK-18. Sin CHK-19/20/21.