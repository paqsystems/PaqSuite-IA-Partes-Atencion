# Plan de implementación — TR-015-update-e (oleada 5), hito **T3 CHK-07**

> Parte **D1** (2026-11-20). **Sin código en este paso.** Parte **D** = solo T3 (no T4 ni refactors T1/T2 salvo fixes bloqueantes).  
> Gate T2: [D-VERIFICACION T2](../D-VERIFICACION-TR-015-update-e-t2-chk06-2026-11-20.md) **Aprobado**.  
> SoT: [TR-015-update-e](../TR-015-update-e-page-header-informes.md) · [HU-015-update-e](../../03-historias-usuario/100-SistemaPartes/HU-015-update-e-page-header-informes.md) · [SPEC oleada 5](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) §4.3 · SPEC D-03.

## Alcance entendido

Implementar **solo** CHK-07 / `setPageHeaderFecha`:

- Sidecar: control **`XRPageInfo`** `pageInfoFecha` en **fila 1**, **arriba a la derecha**; `PageInfo = DateTime` (impresión/ejecución); **sin hora** en formato visible; default **`dd, MMMM 'de' yyyy`**.
- Partes: heurística del prompt canónico (+ variantes “fecha”, “mes en letras”, “arriba a la derecha”); **sin** confundir con `addGroupByField` por `fecha`.
- Matriz CHK-07 → `mvp`, `expectedActions`, `applyAssert`.
- Coexistencia fila 1 con `lblPageHdr_Empresa` (T2) — AC-07.
- Sin CHK-05 (T4), sin panel, sin nueva lógica empresa/columnas.

## Fuentes leídas

- SPEC §3.3, §4.3, D-03, D-05; HU AC-04, AC-07; TR §1, §4 hito T3, §8 locale fecha.
- `LayoutPageHeaderLayout.cs`, `LayoutPageHeaderEmpresaApplier.cs` (patrón fila 1 + `ya_colocados`).
- `LayoutApplyService.cs`, `DesignLayoutPageHeaderHeuristics.php`, `DesignLayoutActionPlanner.php`.
- Matriz CHK-07 (hoy `diferido`).
- Histórico CC: CHK-07 mal mapeado a izquierda (`D-VERIFICACION-TR-015-update-b-2026-11-18`) — MUST alinear derecha en applier.

## Decisiones D1 (T3)

| ID | Decisión |
|----|----------|
| **D1-G01** | Nuevo `LayoutPageHeaderFechaApplier.cs`: `EnsurePageHeaderBand`; crear/actualizar `pageInfoFecha` tipo `XRPageInfo`. |
| **D1-G02** | `PageInfo` = `DevExpress.XtraPrinting.PageInfo.DateTime` (fecha de ejecución/impresión, no campo de detalle). |
| **D1-G03** | `FormatString` default literal `dd, MMMM 'de' yyyy` (SPEC); payload opcional `format` reemplaza si viene 1..80 caracteres y **no** contiene patrón de hora (`H`, `h:mm`, `HH`); si inválido, usar default en sidecar (no 500). |
| **D1-G04** | Posición fila 1 derecha: `LocationFloat.Y = Row1Y`; `LocationFloat.X = printableWidth - controlWidth` con `printableWidth = report.PageWidth - report.Margins.Left - report.Margins.Right`, `controlWidth` fijo v1 ≈ **220f**, altura ≈ **18f**. Sin hora en formato ⇒ ancho acotado. |
| **D1-G05** | Idempotencia: si `pageInfoFecha` ya existe con mismo `PageInfo`, `FormatString` y posición ±2px → `LayoutApplyStepResult(false, "ya_colocados")` → detail `noop:setPageHeaderFecha:ya_colocados`. Búsqueda recursiva en `PageHeaderBand` (como T2). |
| **D1-G06** | Registrar `setPageHeaderFecha` en `AllowedTypes` + `switch` (mismo contrato `LayoutApplyStepResult` que CHK-04/06). |
| **D1-G07** | Heurística `tryMatchFecha($message)`: prompt canónico + (`fecha` + (`derecha`/`der`/`sup`) + (`mes en letras`/`mes en palabra`)); **excluir** si `agrup(a|á|ar).*(fecha)` o `columna.*fecha` / `detalle.*fecha` (prioridad: heurísticas grupo/detalle antes o guard clause al inicio de `tryMatchFecha`). Payload `{}` (`stdClass` en JSON). |
| **D1-G08** | Planner: invocar `tryMatchFecha` tras `tryMatchEmpresa`; `sanitizeActions` acepta `setPageHeaderFecha` con `format` opcional o payload vacío. LLM system prompt: añadir tipo + regla format. |
| **D1-G09** | FE: si `applied===0` y `setPageHeaderFecha:ya_colocados` → i18n `partes.emissionDesign.pageHeader.fechaAlreadyPlaced` (5 locales), análogo CHK-04/06. |
| **D1-G10** | Matriz CHK-07: `estado: mvp`, `expectedActions: [{ action: setPageHeaderFecha, payload: {} }]`, `applyAssert.repxContains`: `pageInfoFecha`, evidencia `PageInfo="DateTime"` y `FormatString` con mes (substring `MMMM` o literal guardado en diseño). `testLayers`: `phpunit_planner`, `ps_apply`, `manual_ui`. |
| **D1-G11** | `eval-emission-ia-oleada5-apply.ps1`: añadir `CHK-07` a `targetIds`; noop aceptable `setPageHeaderFecha:ya_colocados` + `repxContains`. |
| **D1-G12** | Tests .NET: crear/actualizar `pageInfoFecha`, Y≈0, X en mitad derecha; segundo apply → noop; opcional assert `FormatString` no contiene `HH`. |
| **D1-G13** | Cultura/locale: v1 no forzar cultura report en host; confiar en `FormatString` + preview DX. Test documenta resultado en entorno Windows es-AR/es-ES; riesgo TR §8 en F1 si mes sale en inglés (mitigación: `FormatString` con comillas en español o `report.Locale`/equivalente DX solo si F1 falla — **no** ampliar en D salvo bloqueante). |

## Impacto esperado

### Base de datos

- Sin migraciones.

### Backend (Partes)

| Archivo | Cambio |
|---------|--------|
| `DesignLayoutWhitelist.php` | + `setPageHeaderFecha` |
| `DesignLayoutPageHeaderHeuristics.php` | + `tryMatchFecha` |
| `DesignLayoutActionPlanner.php` | cableado + sanitize + prompt LLM |
| `common.json` (×5) | `pageHeader.fechaAlreadyPlaced` |
| Tests | `DesignLayoutActionPlannerTest` CHK-07; matriz vía `DesignLayoutChecklistPlannerTest` |

### Frontend (Partes)

| Archivo | Cambio |
|---------|--------|
| `emissionDesignSmartCaptureTurn.ts` | noop CHK-07 → i18n |

### Framework — sidecar

| Archivo | Cambio |
|---------|--------|
| `LayoutPageHeaderFechaApplier.cs` | **Nuevo** |
| `LayoutApplyService.cs` | whitelist + switch |
| `LayoutPageHeaderFechaTests.cs` | **Nuevo** |
| `eval-emission-ia-oleada5-apply.ps1` | + CHK-07 |
| `15-reportes-emisiones-ia-checklist-matriz.json` | CHK-07 |
| `15-reportes-emisiones-ia-checklist.md` | CHK-07 mvp |
| `guia-manual` Partes | §CHK-07 |

### DevOps

- Rebuild + reinicio sidecar `:5055` tras cambios FW (misma disciplina T2).

## Orden de trabajo (Parte D — T3)

| # | Repo | Tarea |
|---|------|--------|
| 1 | FW | `LayoutPageHeaderFechaApplier` + constantes alineación derecha |
| 2 | FW | `LayoutApplyService` + tests unitarios |
| 3 | FW | Matriz + eval script CHK-07 |
| 4 | Partes | Whitelist + `tryMatchFecha` + planner |
| 5 | Partes | PHPUnit + i18n + FE turno |
| 6 | Partes | Guía manual §CHK-07 |
| 7 | Ambos | `eval` + smoke manual: empresa izq. + fecha der. misma fila (AC-07) |

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Confusión prompt “fecha” ↔ agrupar por fecha | Guard clauses D1-G07 |
| Fecha a la izquierda (regresión CC) | D1-G04 X calculado desde ancho imprimible |
| Mes en inglés en preview | D1-G13; F1 manual |
| Solapamiento con `lblPageHdr_Empresa` | Anchos fijos; Y común fila 1 |
| Sidecar desactualizado | Reinicio post-build |

## Tests a ejecutar (cierre T3 / F1)

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test tests\PaqSuite.DxReporting.Tests\PaqSuite.DxReporting.Tests.csproj -c Release --filter "FullyQualifiedName~PageHeader"

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-oleada5-apply.ps1
```

F1: `D-VERIFICACION-TR-015-update-e-t3-chk07-*.md`.

## Dudas / bloqueos

| # | Tema | Resolución D1 |
|---|------|----------------|
| Q1 | ¿Forzar `es-AR` en report para mes en español? | Diferir a F1; no bloquear D si preview DX ya muestra mes en letras en lab |
| Q2 | ¿Payload `format` desde prompt usuario en v1? | Opcional; heurística v1 solo `{}`; formato custom = turno LLM o extensión TR posterior |

**Sin bloqueos** para iniciar Parte D.

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR T3:** **Sí** — solo CHK-07 y dependencias mínimas (noop FE, matriz, eval).
- **T4 CHK-05 explícitamente excluido** de esta corrida.

## Veredicto D1

**Listo para Parte D (T3)** — ejecutar pasos 1–7 en orden FW → Partes.