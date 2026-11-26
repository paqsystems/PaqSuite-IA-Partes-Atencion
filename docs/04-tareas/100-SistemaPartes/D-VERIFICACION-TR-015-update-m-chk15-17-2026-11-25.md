# Verificación del agente — TR-015-update-m (oleada 11 CHK-15..17)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-m](./TR-015-update-m-imagenes-chk15-17.md) |
| HU | [HU-015-update-m](../../03-historias-usuario/100-SistemaPartes/HU-015-update-m-imagenes-chk15-17.md) |
| Jira | PIAPARTES-79 / PIAPARTES-78 / PIAPARTES-53 |
| Fecha | 2026-11-25 |

## Resultado

**Aprobado** — D + E + smoke manual UI confirmado (2026-11-25). Extensión QR multi-banda (`placement`) incluida en la misma prueba.

## Evidencia revisada

| Eje | Hallazgo |
|-----|----------|
| Alcance | CHK-15/16/17: whitelist + appliers sidecar + heurísticas host; sin upload arbitrario |
| Código | Framework: `LayoutReportWatermarkApplier`, `LayoutPageHeaderLogoApplier`, `LayoutDetailBarCodeApplier`, assets `data/assets/*.png`, eval script. Partes: `DesignLayoutImagesHeuristics`, i18n noop FE |
| Backend | Planner matriz + `sanitizeActions` nuevos tipos |
| Frontend | `emissionDesignSmartCaptureTurn` ramas noop watermark/logo/QR |
| Tests | Ver tabla comandos |
| Docs | Guía manual CHK-15..17 actualizada |

## Tests

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayoutChecklistPlannerTest` | **OK** — 30 tests, 128 assertions |
| `php vendor/bin/phpunit --filter DesignLayoutActionPlannerTest` | **OK** — 26 tests (+3 CHK oleada 11) |
| `dotnet test --filter FullyQualifiedName~LayoutImagesOleada11` | **OK** — 3/3 |
| `eval-emission-ia-oleada11-chk15-17-apply.ps1` (sidecar `:5055` rebuild) | **PASS** CHK-16 → 15 → 17 + re-apply noop |

## Observaciones

- Reiniciar sidecar `dx-reporting` tras pull (puerto 5055) antes de smoke UI en Partes.
- Smoke manual: orden **CHK-16 → CHK-15 → CHK-17** con prompts canónicos (`docs/06-operacion/prompts-nuevo-hilo-oleada-11-chk15-17-sdd.md`).

## Pendientes

- Ninguno funcional. Commit/push cuando el usuario lo autorice.

## Recomendación final

Cierre F documentado: [F-TR-015-update-m-oleada11-chk15-17](./F-TR-015-update-m-oleada11-chk15-17.md).