# Plan de implementación — TR-015-update-g (oleada 3b, **CHK-08-format**)

> Parte **D1** (2026-11-21). **Parte D** autorizada 2026-11-21. Jira: PIAPARTES-66 / 67 / 68. **No** CHK-27 en la misma corrida. **No** `setReportTitle` (CHK-08 texto).

## Decisiones propuestas (confirmar en «sí, parte D» si hace falta)

| Tema | Propuesta D1 |
|------|----------------|
| Resolución `reportTitle` | Todos los `lblReportTitle` en **PageHeader** (recursivo, incl. `pnlReportTitle`) **+** legacy en **ReportHeader** |
| Noop | Solo si **cada** target ya cumple bold + underline + `MiddleCenter` del patch |
| Sufijo apply | `noop:setControlAppearance:ya_formateado` (constante en applier, patrón oleada 5/6) |
| Eval apply | Script **solo** `CHK-08-format` (no mezclar CHK-27) |
| Baseline eval | Ajustar `.sc-backup` de `spike-layout-b-1` con título **sin** subrayado/centrado (o fixture dedicado) para que el 1.er apply **mute** REPX |
| SDD | TR-update-g + D-VERIFICACION + smoke acotado; Jira al cierre F si el usuario lo pide |
| CHK-05 | Sin cambios en `wrapReportTitleInPanel`; formato solo muta `lblReportTitle` dentro del panel |

---

## Alcance entendido

Cerrar el hueco operativo de **CHK-08-format**: prompt canónico `Poné el título en negrita, subrayado y centrado` → planner `setControlAppearance` con `target.kind: reportTitle` → sidecar aplica estilo al **título visible** tras oleada 5 (page header fila 2 / panel) y en **report header** legacy. Incluye **re-aplicar** sin cambios: mensaje SC claro (i18n). No toca CHK-08 texto ni CHK-27.

---

## Fuentes leídas

- **Matriz:** `PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json` (ítem `CHK-08-format`)
- **TR base:** [TR-015-update-c](../TR-015-update-c-apariencia-controles-informes.md) (oleada 3, **Finalizado** con observaciones smoke)
- **C#:** `LayoutControlAppearanceApplier.cs` (`CollectReportTitleLabels` solo `ReportHeaderBand`); `LayoutPageHeaderTitlePanelApplier.cs` (patrón búsqueda profunda); `LayoutApplyService.cs` (`setControlAppearance` → `bool`, sin `NoopDetailSuffix`)
- **PHP:** `DesignLayoutAppearanceHeuristics.php` (`titleStyle` excluye «cambiá título a …»); `DesignLayoutActionPlanner.php` (orden: page header **antes** que `tryMatch` apariencia)
- **FE:** `emissionDesignSmartCaptureTurn.ts` (noop por sufijo en page header / group; **sin** rama título formato)
- **Eval:** `eval-emission-ia-oleada3-apply.ps1` (CHK-08-format + CHK-27)
- **Fixture:** `spike-layout-b-1.repx` — título en ReportHeader ya con Bold+Underline+MiddleCenter (eval pasa por needles sin mutación)

---

## Impacto esperado

### Base de datos

Ninguno.

### Backend (Partes)

- Sin cambios de contrato API salvo mensajes i18n nuevos.
- Verificar que `DesignLayoutChecklistPlannerTest` ya aserta CHK-08-format (matriz); opcional endurecer payload (`bold`, `underline`, `textAlign`).
- **No** tocar orden planner salvo regresión detectada (título+panel+formato compuesto fuera de prompt canónico).

### Backend / sidecar (Framework)

| Cambio | Archivo |
|--------|---------|
| Resolver `reportTitle` en page header + panel + report header | `LayoutControlAppearanceApplier.cs` (reutilizar idea `FindTitleLabelDeep` de CHK-05) |
| Devolver `LayoutApplyStepResult` + `ya_formateado` | `LayoutControlAppearanceApplier.cs` + `LayoutApplyService.cs` |
| Tests reportTitle: page header anidado, legacy dual, noop | `LayoutControlAppearanceTests.cs` (nuevo casos) |
| Baseline eval | `data/layouts/spike-layout-b-1.sc-backup` (o layout hermano) |
| Script eval acotado | `eval-emission-ia-chk08-format-apply.ps1` (nuevo) o parametrizar oleada3 con `-Only CHK-08-format` |

### Frontend

- Clave `partes.emissionDesign.appearance.titleAlreadyFormatted` (5 locales).
- Rama en `emissionDesignSmartCaptureTurn.ts`: `applied === 0` + `noop:setControlAppearance:ya_formateado`.

### Tests

| Capa | Qué |
|------|-----|
| PHPUnit | `DesignLayoutChecklistPlannerTest` (CHK-08-format); `test_heuristics_title_appearance` payloads |
| .NET | Nuevos tests `reportTitle` page header / noop |
| PS | `eval-emission-ia-chk08-format-apply.ps1` con sidecar `:5055` |
| Manual F1 | Mismo prompt 2 veces → 2.ª sin backup / mensaje ya formateado |

### Documentación

- `TR-015-update-g-chk08-format.md` (TR-update, tareas T6–T8)
- Actualizar `smoke-oleada3-apariencia-controles.md` §1 o addendum CHK-08-format (re-apply)
- `D-VERIFICACION-TR-015-update-g-*.md`
- Guía CHK: nota oleada 5 + CHK-08-format
- Este D1

### DevOps

Ninguno (sin migrate/seed). Reiniciar sidecar tras cambios C#.

---

## Orden de trabajo

1. **Framework — resolución targets:** Reemplazar `CollectReportTitleLabels` por colección que: (a) busque `lblReportTitle` en `PageHeaderBand` recursivo; (b) añada labels en `ReportHeaderBand` si existen y no están ya en la lista (evitar duplicar misma instancia).
2. **Framework — noop:** Si hay targets y ninguno cambia → `LayoutApplyStepResult(false, "ya_formateado")`. Si algún target falta estilo → mutar solo los necesarios → `Changed=true`.
3. **Framework — ApplyService:** Cambiar firma apply appearance a `LayoutApplyStepResult` (como page header / group).
4. **Framework — tests:** Caso A: label en panel page header; Caso B: solo report header; Caso C: segundo apply noop.
5. **Framework — fixture + script eval:** Baseline REPX «plano» + script solo CHK-08-format; validar needles post-restore.
6. **Partes — i18n + SC turn:** Mensaje re-apply.
7. **Partes — E:** PHPUnit filter DesignLayout (sin regresión CHK-03/05).
8. **Docs + F1 + smoke manual.**

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Doble `lblReportTitle` (header + page) con textos distintos | Aplicar apariencia a **ambos**; no cambiar texto (frontera CHK-03/08 texto) |
| `ResolveReportTitleControls` hoy llama `setReportTitle` si no hay label | Mantener fallback solo si **ningún** label en page/report; no crear título en report header si ya hay en page |
| Eval oleada3 ya PASS con REPX pre-formateado | Corregir `.sc-backup`; opcional assert `applied > 0` en script nuevo |
| CHK-05 `wrapReportTitleInPanel` pone `MiddleCenter` al crear panel | Noop formato debe exigir también bold+underline |
| Planner mezcla panel + formato en un mensaje compuesto | Fuera de prompt canónico; no ampliar heurísticas en esta corrida |
| CHK-27 mezclado en script viejo | No usar `eval-emission-ia-oleada3-apply.ps1` en E/F1 de esta hit; script dedicado |

---

## Frontera CHK-05 vs CHK-08-format

| | CHK-05 `wrapReportTitleInPanel` | CHK-08-format |
|---|-------------------------------|---------------|
| Acción | Posición, panel, borde, fila 2 | Bold, underline, alineación |
| Control | `pnlReportTitle` + `lblReportTitle` | Solo apariencia del label |
| Noop | `ya_colocados` (panel ya OK) | `ya_formateado` (estilos ya OK) |
| Orden usuario | Típico: panel **antes** formato | Independiente; formato no mueve bandas |

No modificar CHK-06/07 ni columnas page header.

---

## Tests a ejecutar (parte E)

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test tests\PaqSuite.DxReporting.Tests\PaqSuite.DxReporting.Tests.csproj --filter "FullyQualifiedName~LayoutControlAppearance"

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-chk08-format-apply.ps1
```

Smoke manual (F1): diseñador SC → prompt canónico → apply → Guardar opcional → **mismo prompt** → mensaje «ya formateado» sin banner pendiente si `applied=0`.

---

## Dudas / bloqueos

1. ¿Crear **TR-015-update-g** formal + Jira (como CHK-18) o tratar como CC sobre update-c? **Recomendación D1:** update-g para trazabilidad oleada 3b.
2. ¿Fixture único `spike-layout-b-1` vs layout con panel ya aplicado para test .NET? **Recomendación:** test .NET construye reporte en memoria; fixture backup solo para PS apply.
3. ¿Eliminar título legacy de ReportHeader cuando solo existe en page header? **Fuera de alcance** (CHK-08-format no mueve bandas).

---

## Confirmación de alcance

- Sin cambio funcional fuera de **CHK-08-format** / complemento de TR-015-update-c: **Sí**
- Sin CHK-08 texto (`setReportTitle`): **Sí**
- Sin CHK-27 en la misma corrida: **Sí**
- Sin commit/push hasta pedido: **Sí**