# Plan de implementación — TR-015-update-e (oleada 5), hito **T1 CHK-04**

> Parte **D1** (2026-11-19). **Sin código en este paso.** Parte **D** = solo T1 (no T2–T4 en la misma corrida salvo fixes bloqueantes).  
> SoT: [TR-015-update-e](../TR-015-update-e-page-header-informes.md) · [HU-015-update-e](../../03-historias-usuario/100-SistemaPartes/HU-015-update-e-page-header-informes.md) · [SPEC oleada 5](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) · C1 informal (Apto 2026-11-19).

## Alcance entendido

Implementar **solo** CHK-04 / `addPageHeaderColumnLabels`:

- Sidecar: labels de encabezado en **fila 3** del `PageHeaderBand`, alineados a controles del `DetailBand` con binding a campos del schema.
- Partes: whitelist + heurística del prompt canónico (+ columnas nombradas vía GEN-37 si aplica).
- Matriz: CHK-04 → `mvp`, `expectedActions`, `applyAssert`.
- Sin empresa, fecha, panel (T2–T4).
- Sin tocar persistencia Save/Descartar salvo flujo L-B-10 existente.

## Fuentes leídas

- SPEC §3.3, §4.1, D-01/D-06/D-08; HU AC-01, AC-02, AC-08; TR §1–§4 hito T1.
- `LayoutDetailFieldApplier.cs`, `LayoutReportTitleApplier.cs`, `LayoutApplyService.cs`.
- `ProcessFieldSchema` / `partes.informes.consultaDetallada.json` (`Label` para caption).
- `DesignLayoutActionPlanner.php`, `DesignLayoutDetailFieldHeuristics.php`, `DesignLayoutChecklistPlannerTest.php`.
- `emissionDesignSmartCaptureTurn.ts` (post-apply `applied` / sin mensaje hoy si `applied: 0`).

## Decisiones D1 (T1)

| ID | Decisión |
|----|----------|
| **D1-E01** | Nuevo `LayoutPageHeaderLayout.cs` con `Row1Y=0`, `Row2Y=24`, `Row3Y=48`, `MinBandHeightF=72`, `EnsurePageHeaderBand(report)` eleva altura si hace falta. T1 usa solo fila 3; T2–T4 reutilizan el mismo helper. |
| **D1-E02** | Applier devuelve `(bool changed, string? detailToken)`; `LayoutApplyService` escribe `details`: `ok:…` / `noop:…` o `noop:addPageHeaderColumnLabels:sin_columnas_detalle` (SPEC D-06). **No** excepción HTTP. |
| **D1-E03** | Resolver campos detalle: `XRLabel` en `DetailBand` con `ExpressionBinding` `Text` que contiene `[fieldName]`; `fieldName` debe existir en `schema.Columns` (case insensitive). Un campo = un label header (si hay duplicados, tomar el de menor `LocationFloat.X`). |
| **D1-E04** | Caption header = `column.Label` si no vacío; si no, `column.Name`. |
| **D1-E05** | `payload.fields[]` opcional: filtrar universo; sanitize Partes ⊆ `DesignLayoutWhitelist::SCHEMA_FIELDS`; sidecar omite inválidos; si tras filtro lista vacía → mismo noop token que sin detalle. |
| **D1-E06** | Heurística CHK-04: regex sobre prompt canónico y variantes (`título.*columna`, `encabezado de página`, `encima de cada columna`); acción `addPageHeaderColumnLabels` con `payload: []`. No mezclar con `addDetailField`. Orden en planner: **después** removal intent, **antes** appearance (evitar conflicto con “título” en apariencia). |
| **D1-E07** | Mensaje usuario AC-08: tras apply en FE, si `applied === 0` y `details` contiene `sin_columnas_detalle`, `onAssistantReply` con i18n `partes.emissionDesign.pageHeader.noDetailColumns` (5 locales). Si `ya_colocados`, clave `partes.emissionDesign.pageHeader.columnLabelsAlreadyPlaced`. Payload turn/apply MUST ser objeto JSON `{}`, no array `[]`. |
| **D1-E08** | `eval-emission-ia-oleada5-apply.ps1` con `targetIds = @('CHK-04')`; `applyAssert` en `spike-layout-b-1`; acepta `applied:0` si `noop:…:ya_colocados` y REPX cumple `repxContains` (`lblPageHdr_clienteNombre`, caption Cliente). |
| **D1-E09** | Fixture test .NET: informe en memoria con `DetailBand` + un binding `[fecha]` y `PageHeaderBand` vacío; assert label fila 3 `lblPageHdr_fecha`, `Text="Fecha"`, `LocationFloat.Y ≈ 48`. |
| **D1-E10** | `Empresa demo` / tenant DEV: **fuera** de T1 (T2). |

## Impacto esperado

### Base de datos

- Sin migraciones.

### Backend (Partes)

| Archivo | Cambio |
|---------|--------|
| `DesignLayoutWhitelist.php` | + `addPageHeaderColumnLabels` |
| `DesignLayoutPageHeaderHeuristics.php` | **Nuevo** — `tryMatchColumnLabels($message)` |
| `DesignLayoutActionPlanner.php` | Invocar heurística CHK-04 |
| `DesignLayoutActionPlanner.php` `sanitizeActions` | Validar `fields` array |
| `common.json` (×5) | `pageHeader.noDetailColumns` |
| Tests | `DesignLayoutActionPlannerTest` + matriz CHK-04 vía `DesignLayoutChecklistPlannerTest` |

Sin cambios en `EmissionsController` / SP.

### Frontend (Partes)

| Archivo | Cambio |
|---------|--------|
| `emissionDesignSmartCaptureTurn.ts` | D1-E07 parse `details` |
| `emissionDesignSmartCaptureTurn.test.ts` | Caso applied=0 + token |

### Framework — sidecar

| Archivo | Cambio |
|---------|--------|
| `LayoutPageHeaderLayout.cs` | **Nuevo** |
| `LayoutPageHeaderColumnLabelsApplier.cs` | **Nuevo** |
| `LayoutApplyService.cs` | whitelist + switch + detalle noop (D1-E02) |
| `LayoutPageHeaderColumnLabelsTests.cs` | **Nuevo** |
| `eval-emission-ia-oleada5-apply.ps1` | **Nuevo** |
| `15-reportes-emisiones-ia-checklist-matriz.json` | CHK-04 |

### Documentación

- `guia-manual-prompts-checklist-chk-emision-ia.md` §CHK-04.
- `smoke-oleada5-page-header-chk04.md` (nuevo, corto).
- TR §9 marcar T1 en progreso al abrir D (opcional).

### DevOps

- Rebuild sidecar en lab tras merge FW.
- Sin Forge/Vercel obligatorio para T1 local.

## Orden de trabajo (Parte D — T1)

| Paso | Repo | Tarea |
|------|------|--------|
| 1 | FW | `LayoutPageHeaderLayout` + applier CHK-04 |
| 2 | FW | Cableado `LayoutApplyService` + token noop |
| 3 | FW | Tests unitarios + `dotnet test` |
| 4 | FW | Matriz CHK-04 + script eval |
| 5 | Partes | Whitelist + `DesignLayoutPageHeaderHeuristics` + planner |
| 6 | Partes | PHPUnit matriz + planner |
| 7 | Partes | i18n ×5 |
| 8 | Partes | FE post-apply mensaje D1-E07 |
| 9 | Partes | Guía + smoke md |
| 10 | Ambos | Manual: prompt CHK-04 sobre `spike-layout-b-1` con detalle poblado |

**Repos:** dos commits lógicos (FW + Partes); coordinar pin sidecar si CI separado.

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Detalle sin labels (solo otros controles) | Solo `XRLabel` con binding; mensaje D-06 |
| `PageHeader` 24f de CHK-03 | `EnsurePageHeaderBand` sube a 72f |
| PHPUnit matriz ruta Framework en CI Linux | Mantener `MATRIX_PATH` o env documentado |
| Conflicto regex “título” vs appearance | Orden heurísticas D1-E06 |

## Tests a ejecutar (cierre T1)

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test tests\PaqSuite.DxReporting.Tests\PaqSuite.DxReporting.Tests.csproj --filter "FullyQualifiedName~PageHeaderColumnLabels"

# Sidecar :5055
.\scripts\eval-emission-ia-oleada5-apply.ps1
```

F1 T1: evidencia en `D-VERIFICACION-TR-015-update-e-t1-chk04-*.md` (crear al cerrar D).

## Dudas / bloqueos

- Ningún bloqueo para iniciar D.
- **No resolver en T1:** sincronizar título ReportHeader → fila 2 (T4/T2 conviven con legacy hasta entonces).

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR T1:** **Sí** — solo CHK-04 y dependencias mínimas (helper filas, mensaje noop, matriz, tests).
- T2–T4 explícitamente **excluidos** de esta corrida D.

## Veredicto D1

**Listo para Parte D (T1)** — ejecutar pasos 1–10 en orden FW → Partes.