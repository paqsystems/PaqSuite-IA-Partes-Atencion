# TR-015-update-g – Formato título reportTitle (CHK-08-format, oleada 3b)

| Campo | Valor |
|-------|--------|
| **HU** | [HU-015-update-g](../../03-historias-usuario/100-SistemaPartes/HU-015-update-g-formato-titulo-chk08.md) |
| **SPEC** | [SPEC-015-update oleada 3b](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-3b-formato-titulo-chk08-update.md) |
| **D1** | [D1-TR-015-update-g-chk08-format](./d1/D1-TR-015-update-g-chk08-format.md) |
| **TR previa** | [TR-015-update-c](./TR-015-update-c-apariencia-controles-informes.md) |
| **Matriz** | `PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json` (CHK-08-format) |
| **Estado** | **Pendiente de Revisión** (parte D 2026-11-21) |
| **Jira** | [PIAPARTES-66](https://paqsystems.atlassian.net/browse/PIAPARTES-66) (Epic) · [PIAPARTES-67](https://paqsystems.atlassian.net/browse/PIAPARTES-67) (HU) · [PIAPARTES-68](https://paqsystems.atlassian.net/browse/PIAPARTES-68) (TR) |

---

## 1) Objetivo

Cerrar huecos de **CHK-08-format**: resolución `reportTitle` en page header (panel) + report header legacy; noop `ya_formateado`; eval y smoke re-aplicar.

---

## 2) Sidecar (Framework)

| Artefacto | Entrega |
|-----------|---------|
| `LayoutControlAppearanceApplier.cs` | Targets multi-banda; `LayoutApplyStepResult` + `ya_formateado` |
| `LayoutApplyService.cs` | Dispatcher appearance → step result |
| `LayoutControlAppearanceTests.cs` | Casos reportTitle page header + noop |
| `spike-layout-b-1.repx.sc-backup` | Baseline título sin formato completo (eval) |
| `eval-emission-ia-chk08-format-apply.ps1` | Solo CHK-08-format (+ re-apply noop) |

---

## 3) Host Partes

| Artefacto | Entrega |
|-----------|---------|
| `emissionDesignSmartCaptureTurn.ts` | Rama noop `ya_formateado` |
| `common.json` (×5) | `partes.emissionDesign.appearance.titleAlreadyFormatted` |
| Guía manual | Nota CHK-08-format re-apply |

Sin cambios whitelist (`setControlAppearance` ya existía).

---

## 4) Tests (parte E)

| Prueba | Resultado (2026-11-21) |
|--------|-------------------------|
| `phpunit --filter DesignLayout` | **OK** — 48 tests |
| `dotnet test --filter LayoutControlAppearance` | **OK** — 3 tests |
| `eval-emission-ia-chk08-format-apply.ps1` | **PASS CHK-08-format** (sidecar reiniciado) |

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test --filter LayoutControlAppearance

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-chk08-format-apply.ps1
```

Smoke manual UI (re-aplicar prompt): pendiente **F1**.

---

## 5) Confirmación alcance

Solo CHK-08-format. Sin CHK-27. Sin CHK-08 texto.