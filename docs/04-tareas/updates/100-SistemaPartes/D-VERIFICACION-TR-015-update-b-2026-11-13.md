# Verificación post–Parte D — TR-015-update-b (Smart Capture diseño, oleada 2 B)

| Campo | Valor |
|-------|--------|
| **TR / HU / SPEC** | [TR-015-update-b](../../100-SistemaPartes/TR-015-update-b-smart-capture-diseno-informes.md) · [HU-015-update](../../../03-historias-usuario/100-SistemaPartes/HU-015-update-ia-diseno-informes-camino-b.md) · [SPEC-015-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-2-b-update.md) |
| **Plan D1** | [D1-TR-015-update-b](../../100-SistemaPartes/d1/D1-TR-015-update-b-smart-capture-diseno-informes.md) |
| **Jira** | Epic [PIAPARTES-53](https://paqsystems.atlassian.net/browse/PIAPARTES-53) · HU [PIAPARTES-61](https://paqsystems.atlassian.net/browse/PIAPARTES-61) · TR [PIAPARTES-62](https://paqsystems.atlassian.net/browse/PIAPARTES-62) |
| **Fecha** | 2026-11-13 |
| **Repos** | Framework `dx-reporting` (T1–T3) + Partes host (T4–T9) |
| **Estado Parte D** | Implementación T1–T9 cerrada a nivel código/tests automatizados |
| **Estado Parte E** | **2026-11-13** — tests TR Must **PASS**; regresión emisiones 1 fail preexistente; E2E bloqueado (browsers Playwright); T8/T9 manual → **F1** |

---

## Evidencia automatizada (Parte D)

| Pieza | Evidencia |
|-------|-----------|
| T1 Apply sidecar | `LayoutApplyController` `POST /api/layout/apply` · `LayoutApplyService` ACT-01..03 + `setPageOrientation` |
| T2 D4 + XRSummary | `LayoutStubCleanup` en apply + `FileReportStorage.SetData`; `XRSummary` en `addGroupSum` |
| T3 Runbook | FW `runbook-dx-reporting-sidecar.md` §6.2 |
| T4 Turn BE | `DesignAssistantTurnController` + `DesignAssistantTurnService` + `DesignLayoutActionPlanner` |
| T5 OpenAPI | `OpenApiPathsEmissions` path assistant/turn |
| T6 FE | `EmissionDesignSmartCaptureSlot` + proxy `/api/layout` + remount + `AIServicesEnabled(false)` |
| T7 i18n | `partes.emissionDesign.*` en es/en/pt/fr/it |
| Unit BE | `DesignLayoutActionPlannerTest` — **2/2 OK** |
| Feature BE | `ApiV1DesignAssistantTurnTest` — auth / 4709 / BYOK / 422 / heurística |
| Vitest FE | `enableReportDesignerAi.test.ts` + `emissionDesignSmartCaptureTurn.test.ts` — **4/4 OK** |

---

## Parte E — ejecución de tests (2026-11-13)

| Comando / suite | Resultado | Detalle |
|-----------------|-----------|---------|
| `vendor/bin/phpunit --filter DesignLayoutActionPlannerTest` | **PASS** | 2/2, 5 assertions |
| `vendor/bin/phpunit --filter ApiV1DesignAssistantTurnTest` | **PASS** | 6/6, 24 assertions |
| `vendor/bin/phpunit --filter ApiV1EmissionsPartesTest` | **FAIL 1/17** | `test_design_sin_acceso_total_4709_asistente_emite`: alta tarea 422 `partes.tarea.camposObligatorios` porque `clienteId=0` (seed sin cliente). **No** toca el turn design assistant. |
| `npx vitest run …enableReportDesignerAi… emissionDesignSmartCaptureTurn` | **PASS** | 2 files / 4 tests (`--pool=threads`) |
| `npx vitest run src/features/partes/informes` (carpeta) | **Inestable** | 25 tests PASS en 9 files + timeouts de workers (pool forks) → exit 1 de infra |
| Sidecar C# unit | **N/A** | Sin proyecto test; `dotnet build` OK (Parte D) |
| `npx playwright test …partes-emisiones --grep disenador` | **BLOCKED** | Falta Chromium Playwright: `npx playwright install` |

**Veredicto Parte E (TR-015-update-b):** tests Must del alcance **OK**. Fallo emisiones = deuda de fixture/seed (clienteId). E2E requiere install browsers + stack lab → **F1**.

---

## Checklist T8 — batería §4.4 / L-B (manual F1)

Ejecutar en lab: sidecar `:5055` + Partes + BYOK GEN-16 (no OpenAI sidecar obligatorio).

| # | Escenario (camino B) | Estado | Notas |
|---|----------------------|--------|-------|
| 1 | SC visible bajo diseñador (`emissionDesignSmartCapture`) | Pendiente F1 | Solo `consultaDetallada` + report abierto |
| 2 | Sin BYOK → `configurationRequired` | Pendiente F1 | |
| 3 | «Agrupá por cliente» → apply + refresh | Pendiente F1 | |
| 4 | «Sumá / totalizá minutos» → XRSummary / Validate Bindings | Pendiente F1 | |
| 5 | «Título: …» / orientación landscape | Pendiente F1 | |
| 6 | Pedido campo inventado → sin apply / reply rechazo | Pendiente F1 | |
| 7 | Confirm dirty → cancel no muta REPX | Pendiente F1 | |
| 8 | Save / Save As GEN-15 post-apply | Pendiente F1 | CA-12 |

**Prompt-to-Report:** no accesible en Partes (D-08).

---

## T9 — Regresión Save / GetDesignerModel

| Ítem | Estado | Notas |
|------|--------|-------|
| Save / Save As catálogo | Pendiente F1 | Sin cambio de contrato layout GEN-15 |
| `GetDesignerModel` + `processCode` | Pendiente F1 | Remount tras apply usa mismo `reportCode` |
| Field List limpio post-Save (D4) | Código listo | Smoke F1 |

---

## Gaps / follow-ups

- Batería manual #1–#8 → **Parte F1**.
- E2E: `npx playwright install` (Chromium) en la PC de lab.
- Fixture `ApiV1EmissionsPartesTest` (seed cliente) — fuera de alcance Must TR-015-update-b.
- Proxy Forge/nginx `/api/layout` en preview si no solo Vite.
- Manual usuario GEN-99 al cierre F si se pide.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-13 | Creado al cierre Parte D (código T1–T7 + tests + runbook; T8/T9 checklist para F1). |
| 2026-11-13 | Parte E: re-ejecución PHPUnit/Vitest Must PASS; 1 fail emisiones seed; E2E blocked Playwright browsers. |
