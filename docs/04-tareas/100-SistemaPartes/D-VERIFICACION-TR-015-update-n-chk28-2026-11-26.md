# Verificación del agente — TR-015-update-n (oleada 12 CHK-28)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-n](./TR-015-update-n-remove-detalle-chk28.md) |
| HU | [HU-015-update-n](../../03-historias-usuario/100-SistemaPartes/HU-015-update-n-remove-detalle-chk28.md) |
| Jira | PIAPARTES-81 / PIAPARTES-80 / PIAPARTES-53 |
| Fecha | 2026-11-26 |

## Resultado

**Aprobado** — D + E + smoke manual UI confirmado (2026-11-26). Incluye corrección post-smoke: eliminación también de `lblPageHdr_observacion` en page header.

## Evidencia revisada

| Eje | Hallazgo |
|-----|----------|
| Alcance | CHK-28: `removeDetailField` / `observacion`; detalle + título columna page header |
| Código | Framework: `LayoutDetailFieldRemoveApplier`, matriz CHK-28, `eval-emission-ia-oleada12-chk28-apply.ps1`. Partes: heurística + reorder planner + i18n noop FE |
| Backend | Planner matriz CHK-28; regresión CHK-29 `no_mapped` |
| Frontend | `emissionDesignSmartCaptureTurn` noop `ya_ausente` |
| Tests | Ver tabla comandos |
| Docs | Guía manual CHK-28 |

## Tests

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | **OK** — 59 tests (incl. CHK-28 + CHK-29 regresión) |
| `dotnet test --filter LayoutDetailFieldRemove` | **OK** — 2/2 |
| `eval-emission-ia-oleada12-chk28-apply.ps1` (sidecar `:5055`) | **PASS** apply + noop |

## Smoke manual (usuario)

| Prompt | Criterio | Resultado |
|--------|----------|-----------|
| `Quitá la columna observación del detalle` | Sin columna observación en **detalle** | **OK** |
| (mismo tras restore) | Sin título **Observación** en encabezado de columnas | **OK** |
| Re-prompt | Mensaje noop sin error HTTP | **OK** (sesión previa + re-smoke post-fix header) |

## Observaciones

- Sidecar reiniciado tras extensión page header (2026-11-26).
- Convención nombre: `lblPageHdr_{field}` alineada a `addPageHeaderColumnLabels`.

## Pendientes

- Commit/push (Framework + Partes) cuando el usuario lo autorice.

## Recomendación final

Cierre F: [F-TR-015-update-n-oleada12-chk28](./F-TR-015-update-n-oleada12-chk28.md).