# Smoke manual — oleada 5 (CHK-04 … CHK-07)

## Precondiciones

- Sidecar `http://127.0.0.1:5055` (Release tras cambios FW).
- Partes lab: diseñador emisiones con tenant `DEV` (ver `deploy-infraestructura.md` / `.env.local`).
- Variante layout **`spike-layout-b-1`** (detalle con bindings).

## Checklist UI (cuatro prompts)

Usar la guía [`guia-manual-prompts-checklist-chk-emision-ia.md`](./guia-manual-prompts-checklist-chk-emision-ia.md). Orden sugerido:

| CHK | Prompt | Verificación rápida |
|-----|--------|-------------------|
| 04 | `Poné un título encima de cada columna en el encabezado de página` | `lblPageHdr_*` fila 3; banner **Guardar/Descartar**; re-aplicar → § idempotencia |
| 06 | `Poné el nombre de la empresa arriba a la izquierda` | `lblPageHdr_Empresa` fila 1; re-aplicar → `empresaAlreadyPlaced` |
| 07 | `Agregá la fecha con el mes en letras arriba a la derecha` | `pageInfoFecha` fila 1 der.; re-aplicar → `fechaAlreadyPlaced` |
| 05 | `Encerrá el título en un panel` | `pnlReportTitle` + `lblReportTitle` fila 2; re-aplicar → `titlePanelAlreadyPlaced` |

### Idempotencia (controles ya presentes)

Repetir el **mismo** prompt cuando el control ya está colocado:

| CHK | Condición | Mensaje SC (ES) | Detail apply esperado |
|-----|-----------|-----------------|------------------------|
| 04 | `lblPageHdr_*` alineados | Ya tiene los títulos de columna… | `noop:addPageHeaderColumnLabels:ya_colocados` |
| 05 | `pnlReportTitle` + borde fila 2 | Ya está encerrado en un panel… | `noop:wrapReportTitleInPanel:ya_colocados` |
| 06 | Mismo texto en `lblPageHdr_Empresa` | Ya muestra el nombre de empresa… | `noop:setPageHeaderEmpresa:ya_colocados` |
| 07 | `pageInfoFecha` con mismo formato/posición | Ya tiene la fecha de impresión… | `noop:setPageHeaderFecha:ya_colocados` |

**Esperado:** `applied: 0`, sin banner Guardar/Descartar, sin error HTTP, lienzo sin cambios innecesarios.

**Cierre manual:** oleada 5 **OK** (usuario 2026-11-20) — CHK-04…07, incl. re-aplicar CHK-05/07.

**CA-B5-06:** tras un apply con cambios, confirmar banner Guardar/Descartar (L-B-10); guardar y reabrir diseñador.

## Automatizado (Parte E)

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test tests\PaqSuite.DxReporting.Tests\PaqSuite.DxReporting.Tests.csproj -c Release --filter "FullyQualifiedName~PageHeader"

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-oleada5-apply.ps1
```

Reinicio sidecar:

```powershell
cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\stop-sidecar-port-5055.ps1
cd ..\src\PaqSuite.DxReporting
dotnet build -c Release
dotnet run -c Release --no-build --urls http://127.0.0.1:5055
```