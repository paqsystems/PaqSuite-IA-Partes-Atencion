# F — TR-015-update-d / d2 / d3 (oleada 4 — detalle, título, CHK-08 texto)

| Campo | Valor |
|-------|--------|
| TRs | [TR-015-update-d](./TR-015-update-d-detalle-campo-chk01.md) · [TR-015-update-d2](./TR-015-update-d2-detalle-texto-fijo-chk02.md) · [TR-015-update-d3](./TR-015-update-d3-titulo-page-bands-chk03.md) |
| HU / SPEC | Oleada 4 documentada en TR (sin SPEC update separado); continuidad [TR-015-update-b](./TR-015-update-b-smart-capture-diseno-informes.md) |
| F1 | [D-VERIFICACION oleada4 CHK01-03](../updates/100-SistemaPartes/D-VERIFICACION-oleada4-chk01-03-2026-11-18.md) |
| Fecha cierre F | 2026-11-18 (smoke) · F formal 2026-11-25 |

## Resultado

**Aprobado** — oleada 4 piloto Consulta detallada: detalle (`addDetailField`, `addDetailStaticText`), título y bandas (`setReportTitle`), y título literal **CONSULTA DETALLADA** (CHK-08 texto, misma acción que CHK-03 con otro payload).

## Smoke manual (operador)

| CHK | Prompt canónico | Resultado | Notas |
|-----|-----------------|-----------|-------|
| CHK-01 | `Agregá la columna observación en el detalle` | **OK** | Ver D oleada4 |
| CHK-02 | `Agregá un texto fijo que diga Borrador en el detalle` | **OK** | Ver D oleada4 |
| CHK-03 | `Cambiá el título a Consulta detallada` | **OK** | Page header/footer mínimos |
| CHK-08 | `Cambiá el título a CONSULTA DETALLADA` | **OK** | Texto literal; formato → CHK-08-format (oleada 3b, TR-g) |

## Automatizado (no sustituye filas anteriores)

| Capa | Evidencia |
|------|-----------|
| Planner matriz | `php vendor/bin/phpunit --filter DesignLayoutChecklistPlannerTest` (CHK-01…08 con `applyAssert`) |
| Apply sidecar | `eval-emission-ia-checklist-apply.ps1` (matriz Framework, incl. CHK-08) |
| .NET | `LayoutDetailFieldApplier`, `LayoutDetailStaticTextApplier`, `LayoutReportTitleApplier` |

## Follow-up documental

- **No** usar la lista “Pendientes manual UI” de [D-VERIFICACION-TR-015-update-b](../updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-b-2026-11-13.md) para CHK-01, 02, 03, 08 — quedó **obsoleta** tras oleadas 4+.