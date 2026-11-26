# TR-015-update-m – Imágenes, logo y QR (CHK-15..17, oleada 11)

| Campo | Valor |
|-------|--------|
| Estado | **Finalizado** |
| Jira | [PIAPARTES-79](https://paqsystems.atlassian.net/browse/PIAPARTES-79) |
| HU / SPEC | [HU-015-update-m](../../03-historias-usuario/100-SistemaPartes/HU-015-update-m-imagenes-chk15-17.md) · [SPEC oleada 11](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-11-imagenes-chk15-17-update.md) |
| TR previa | [TR-015-update-l](./TR-015-update-l-calculados-chk11-14.md) |
| D1 | [D1-TR-015-update-m](./d1/D1-TR-015-update-m-chk15-17.md) |

## 1) Contrato acciones (whitelist v1.3)

| Acción | Payload | Sidecar (applier) | CHK |
|--------|---------|-------------------|-----|
| `setReportWatermarkImage` | `{ "source": "default" }` | `LayoutReportWatermarkApplier` (nuevo) | 15 |
| `setPageHeaderLogo` | `{ "source": "empresa" }` | `LayoutPageHeaderLogoApplier` (nuevo) | 16 |
| `addDetailBarCode` | `{ "field", "symbology": "QRCode" }` | `LayoutDetailBarCodeApplier` (nuevo) | 17 |

Host Partes: extender `DesignLayoutWhitelist`, heurísticas (`DesignLayoutImagesHeuristics` o extensión planner), mensajes noop i18n `partes.emissionDesign.*`, turno SC sin cambio de contrato API.

## 2) Matriz y scripts

| Artefacto | Repo |
|-----------|------|
| Matriz CHK-15..17 `mvp` | Framework `15-reportes-emisiones-ia-checklist-matriz.json` (v4) |
| Eval | `eval-emission-ia-oleada11-chk15-17-apply.ps1` (nuevo) |
| Assets lab | `services/dx-reporting/assets/watermark-pilot.png`, `logo-pilot.png` |

## 3) Tests (parte E)

| Capa | Comando | Resultado 2026-11-25 |
|------|---------|-------------------|
| Planner | `php vendor/bin/phpunit --filter DesignLayoutChecklistPlannerTest` | OK (30) |
| Planner unit | `php vendor/bin/phpunit --filter DesignLayoutActionPlannerTest` | OK (26) |
| Sidecar | `dotnet test --filter LayoutImagesOleada11` | OK (3) |
| Apply | `.\eval-emission-ia-oleada11-chk15-17-apply.ps1` | PASS 16→15→17 |

## 4) Smoke manual

Orden: CHK-16 → CHK-15 → CHK-17. Guía: [prompts oleada 11](../../06-operacion/prompts-nuevo-hilo-oleada-11-chk15-17-sdd.md) § smoke.

## 5) Entregables F

| Doc | Nombre previsto |
|-----|-----------------|
| F1 | `D-VERIFICACION-TR-015-update-m-chk15-17-YYYY-MM-DD.md` |
| F | `F-TR-015-update-m-oleada11-chk15-17.md` |