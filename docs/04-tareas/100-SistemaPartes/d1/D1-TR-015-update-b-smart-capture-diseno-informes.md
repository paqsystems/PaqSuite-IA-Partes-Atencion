# Plan de implementación — TR-015-update-b (Smart Capture diseño, oleada 2 B)

> Parte **D1** (2026-11-13). **No código en este paso.** Implementación = Parte **D** (T1–T9).  
> SoT: [TR-015-update-b](../TR-015-update-b-smart-capture-diseno-informes.md) · [C1](../TR-015-update-b-smart-capture-diseno-informes-C1-ambiguity-review.md) · HU-015-update · SPEC-015-update.

## Alcance entendido

Productizar **camino B**: panel **GEN-03** bajo el diseñador GEN-15 en `/emisiones/disenador`, turno Laravel con **BYOK GEN-16**, acciones **whitelist** validadas contra schema `partes.informes.consultaDetallada`, **patch REPX en sidecar** + **refresh** del diseñador, **sin** Prompt-to-Report DX, **sin** auto-Save.

Repos: **PaqSuite-IA-FRAMEWORK** (`dx-reporting` T1–T3) + **PaqSuite-IA-Partes-Atencion** (T4–T9).

Fuera: camino A/C, TR-016 productiva, Emitir, mobile, voz/imagen, nuevos `processCode`, GEN nuevo publicado.

## Fuentes leídas

- SPEC-015-update oleada 2 B + A1; HU-015-update; TR-015-update-b + C1 (D-01…D-13)
- Spike + D-VERIFICACION aplicador ACT-01..03
- Partes: `ReportDesignerHostPage.tsx`, `DxReportDesignerApp.tsx`, `enableReportDesignerAi.ts`, `vite.config.ts` (proxy `/DXXRD`), `partesSmartCaptureTurn.ts`, `EmissionsController.php`, rutas `emissions/design/*`
- Framework: `FileReportStorage.cs`, `DesignerModelFactory.cs`, `ReportingControllers.cs`, scripts `SpikeApplyAct0*`, `ReportDesignerContext` (`reportCode` = storage DX), GEN-03 `SmartCaptureTurnAction` = `{ action, payload, resultado }`

## Decisiones D1 (cierran detalle de implementación)

| ID | Decisión |
|----|----------|
| **D1-01** | Shape acciones en envelope GEN-03: `{ "action": "<type §5.1>", "payload": { …params }, "resultado": "ok" }`. El FE lee `action`+`payload` para apply; **no** reutilizar `setFieldMap` de carga diaria. |
| **D1-02** | `reportUrl` del turn/apply = `context.reportCode` del diseñador (GEN-15). Si `null`/vacío → SC degradado / no turn (D-13). |
| **D1-03** | Montaje SC: wrapper en `ReportDesignerHostPage` / panel hijo que renderiza `DxReportDesignerPanel` **arriba** y `SmartCapturePanel` **abajo** cuando `processCode === partes.informes.consultaDetallada` y `!isNativeApp()`. |
| **D1-04** | Desactivar IA DX: no llamar `enableReportDesignerAiServices()` en `DxReportDesignerApp` (o no-op gated); test Vitest actualizado. |
| **D1-05** | Vite (+ ops preview): proxy `/api/layout` → `VITE_DX_REPORTING_HOST` (D-11). |
| **D1-06** | BE turn: nuevo servicio host `DesignAssistantTurnService` (o equivalente) + método en `EmissionsController` (o controller dedicado bajo mismo middleware `emission.design`); reutilizar `SmartCaptureTurnGuard` + `LlmCredentialResolver` como TR-010; **prompt/system** distinto (layout whitelist, no draft tarea). |
| **D1-07** | Schema columnas para validar `field`: leer JSON sidecar path documentado **o** duplicar catálogo embebido Partes alineado a `PartesConsultaDetalladaEmissionPort` / schema TR-015 (mismo set de `name`); preferir fuente única ya usada en host si existe. |
| **D1-08** | Dirty designer: si no hay API DX confiable de “isDirty”, v1 = confirmación **siempre** antes del primer apply de un turno con actions (mensaje i18n), o best-effort si el widget expone flag; documentar en F1. |
| **D1-09** | Refresh: incrementar `designerRemountKey` / remount `DxReportDesignerPanel` con mismo `reportCode` tras apply OK (evita F5 full page). |
| **D1-10** | PR: **dos repos** (FW + Partes) o Partes primero con sidecar lab local; no mezclar TR-016. |

## Impacto esperado

### Base de datos

- **Sin** migraciones Must.
- Layouts: archivos `.repx` en sidecar `data/layouts/` (igual TR-011).

### Backend (Partes)

| Ítem | Detalle |
|------|---------|
| Ruta | `POST /api/v1/emissions/design/processes/{processCode}/assistant/turn` |
| Auth | Sanctum + `X-Paq-Cliente` + `emission.design` (`EmissionCapabilityGuard`) |
| Servicio | Validar processCode Must; BYOK; LLM → acciones whitelist; filtrar fields schema; D-12 |
| OpenAPI | Extender `OpenApiPathsEmissions` |
| Tests | Feature: 200 / 403 / 422 / `configurationRequired` |
| **No** | Persistir REPX ni catálogo desde el turn |

Archivos probables: `routes/api.php`, `EmissionsController.php` (o `DesignAssistantController.php`), `app/Services/Emissions/DesignAssistantTurnService.php`, proposal port LLM, tests Feature/Unit.

### Frontend (Partes)

| Ítem | Detalle |
|------|---------|
| Host | `ReportDesignerHostPage` + componente `EmissionDesignSmartCapture` (nombre orientativo) |
| Turn client | Wrapper tipo `partesEmissionDesignSmartCaptureTurn.ts` → `postSmartCaptureTurn` al path D-04 |
| Apply | `fetch('/api/layout/apply', …)` same-origin |
| Refresh | D1-09 |
| AI DX | D1-04 |
| Proxy | `vite.config.ts` D1-05 |
| i18n | `partes.emissionDesign.*` × 5 locales |
| Testids | TR RN-B-11 |

Archivos: `ReportDesignerHostPage.tsx`, `DxReportDesignerApp.tsx`, `enableReportDesignerAi.ts`, `vite.config.ts`, locales, tests Vitest.

### Framework — sidecar

| Ítem | Detalle |
|------|---------|
| T1 | `LayoutApplyService` (C#) desde spike ACT-01..03 + `setPageOrientation`; `POST /api/layout/apply` |
| T2 | D4 cleanup stubs en Save/`SetData`; ACT-03 `XRSummary` si XRE090 |
| T3 | Runbook: apply, proxy, sin OpenAI obligatorio para B |

Archivos: nuevo `Controllers/LayoutApplyController.cs` (o similar), `Reporting/LayoutApply*.cs`, `Program.cs` DI, `FileReportStorage` hook Save, tests C#, runbook.

### Tests

- Unit BE: whitelist / field inventado / processCode.
- Feature BE: turn.
- Vitest FE: montaje SC, no native, AI DX off, proxy path constante.
- Sidecar: apply fixture REPX.
- Manual F1: batería §4.4 + L-B → `D-VERIFICACION-TR-015-update-b-*.md`.

### Documentación / DevOps

- Runbook FW (T3).
- Banner TR-016 ya puesto; al F: `19-ia-diseno-informes.md` si hace falta.
- Preview: nginx/Vite proxy `/api/layout` (igual lab).
- Forge: sin secret OpenAI sidecar **Must** para diseño B (BYOK usuario).

## Orden de trabajo (Parte D)

| Fase | Tareas | Repo | Notas |
|------|--------|------|-------|
| **1** | T1 | FW | Apply endpoint + ACT-01..03 + orientation |
| **2** | T2 | FW | D4 + XRSummary |
| **3** | T4 | Partes | Turn Laravel (puede empezar en paralelo tras contrato actions estable) |
| **4** | T6 (+ proxy T) | Partes | SC + wire turn/apply/refresh + AI off; **bloquea** en apply lab T1 |
| **5** | T5 + T7 | Partes | OpenAPI + i18n |
| **6** | T9 | Ambos | Regresión Save + GetDesignerModel |
| **7** | T8 | Partes | F1 checklist |
| **8** | T3 | FW | Runbook (puede ir con T1/T2) |

Paralelo: T4 ↔ T1 (contrato JSON actions acordado); T7 ↔ T6; T3 con T2.

## Riesgos

| Riesgo | Mitigación D1/D |
|--------|-----------------|
| `EmissionReportDesignerPage` no expone slot “bajo DX” fácil | Wrapper en `renderDesigner`: fragmento DX+SC en un `div` columna |
| Dirty real no detectable | D1-08 confirmación |
| Apply concurrente / overwrite | last-write; usuario Save |
| Shape actions vs GEN-03 | D1-01 |
| Pin SDK sin exports SC | Verificar pin `@paqsuite/react-core` ≥ TR-010; bump solo si falta export |
| Lab sidecar DLL lock | `stop-sidecar-port-5055.ps1` antes de rebuild |

## Tests a ejecutar (al cerrar D/E)

```text
# Partes FE
cd frontend && npm run test:run -- emissionDesign ReportDesigner enableReportDesignerAi vite-plugins

# Partes BE
cd backend && vendor/bin/phpunit --filter=DesignAssistant|EmissionsDesign

# Sidecar
cd services/dx-reporting && dotnet test   # o proyecto test apply

# Manual
Checklist TR §2 CA-01…12 + Gherkin HU → D-VERIFICACION-TR-015-update-b-<fecha>.md
```

## Dudas / bloqueos

- **Ninguno bloqueante** post-C1.
- Opcional release: proxy Forge nginx vs solo Vite (D1 asume same-origin en todos los entornos front).

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR: Sí**
- No reabrir Prompt-to-Report; no SC carga diaria; no mobile; no auto-save; proceso único Must.

## Listo para Parte D

**Sí** — tras confirmación humana de este plan (o comando «Parte D» / «Implementá la TR»).

Jira: [PIAPARTES-62](https://paqsystems.atlassian.net/browse/PIAPARTES-62) → **Verificando** tras Parte D (2026-11-13). Evidencia: [D-VERIFICACION-TR-015-update-b-2026-11-13](../../updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-b-2026-11-13.md).
