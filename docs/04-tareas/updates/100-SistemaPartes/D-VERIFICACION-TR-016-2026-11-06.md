# Verificación D — TR-016 Edición IA sobre informe existente (Prompt-to-Report)

| Campo | Valor |
|-------|-------|
| **TR / HU / SPEC** | [TR-016](../../100-SistemaPartes/TR-016-edicion-ia-informes-existentes-prompt-to-report.md) · [HU-016](../../../03-historias-usuario/100-SistemaPartes/HU-016-ia-edicion-informes-existentes.md) · [SPEC-016](../../../05-open-spec/100-SistemaPartes/SPEC-016-prompt-to-report-edicion-informes.md) |
| **Jira** | PIAPARTES-60 (TR) · PIAPARTES-59 (HU) · PIAPARTES-58 (Epic) |
| **Fecha** | 2026-11-06 |
| **Estado** | **Parcial (agente)** — código Framework + runbook + revisión FE; **batería manual CA-016 pendiente** (lab con LLM) |

---

## Entregado en Parte D (código / docs)

| Ítem | Evidencia |
|------|-----------|
| T1 Nota técnica DX + flujo “informe abierto” | Repo Framework `docs/06-operacion/runbook-dx-reporting-sidecar.md` §6.1 + enlace doc DX |
| T3 Augmentación edición incremental | FW `ProcessFieldSchemaCatalog.BuildIncrementalEditPromptAugmentation` + uso en `ReportingAiBootstrap.SetPromptAugmentation` |
| T6 Runbook ampliado | Mismo runbook §6.1; workaround `dotnet build` cuando el sidecar bloquea DLL |
| T4 Ciclo de vida FE | Sin cambio necesario: `DxReportDesignerApp` `BeforeRender` → `AIServicesEnabled(true)`; `DxReportDesignerPanel` `key={reportUrl}` fuerza remount al cambiar variante |
| Build sidecar (alternativa sin bloqueo) | `dotnet build … IntermediateOutputPath=obj\agent-tr016-check\…` → **OK** (cuando `PaqSuite.DxReporting.exe` en ejecución bloquea el `bin\Debug` default) |

---

## Pendiente (operador / F1)

| CA | Estado |
|----|--------|
| CA-016-01…07, 09 | Ejecutar en lab: variante cargada → flujo wizard “diseñar en asistente” → prompt canónico «Agregá subtotal de horas» → Validate Bindings → Save/Save As |
| CA-016-08 | Sin LLM: diseñador + edición manual; anotar si DX oculta ribbon IA |
| REPX antes/después | Adjuntar en PR o carpeta evidencia |

---

## Vitest (regresión T4)

- Comando: `npm run test -- --run src/features/partes/informes/ReportDesignerHostPage.test.tsx`
- Resultado: **2026-11-06** — Vitest **1 file, 2 tests passed** (duración ~40 s en agente)

---

## Parte E — tests automáticos (2026-11-06)

| Comando | Resultado |
|---------|-----------|
| `cd frontend && npm run test` (Vitest completo) | **68 files, 210 tests passed** (~3 min) |

**Fix en la corrida:** el virtual module `\0paq-jquery-esm` (`wrapJqueryUmdAsDefaultEsm` en `frontend/vite-plugins/reportingCjsInterop.ts`) cargaba el UMD de jQuery en el ámbito global de Vitest/ESM y chocaba con `module.exports` de solo lectura, o bien no detectaba la función exportada por CJS. Se envolvió la carga en `__paqLoadJQuery()` con `module` local y se resolvió la instancia comprobando `jq.Deferred` sin exigir `typeof jq === 'object'`. Test añadido en `frontend/vite-plugins/reportingCjsInterop.test.ts`.

---

## Próximo — pruebas manuales (operador / HU-016)

Previa: sidecar en `:5055` con credencial LLM, Partes con proxy `/DXXRD` y usuario `emission.design` (detalle: runbook Framework §6.1).

1. **Regresión creación (CA-016-09):** Prompt-to-Report **informe nuevo** → prompt corto en español → Save.
2. **Edición sobre variante (CA-016-02):** abrir variante de `partes.informes.consultaDetallada` → en el asistente, **«Diseñar en el asistente»** / equivalente → *«Agregá subtotal de horas por cliente»* → ver cambio en el lienzo.
3. **Validate Bindings:** sin errores XRE023; si hay, anotar campo.
4. **CA-016-04 / 05:** ajuste manual + Save / Save As → catálogo OK.
5. **Campo inexistente (CA-016-06):** p.ej. *«agregá rentabilidad por parte»* → rechazo o mensaje claro.
6. **Sin LLM (CA-016-08):** sidecar sin API key → diseñador + edición manual; capturar estado del ribbon IA.
7. **Evidencia:** REPX antes/después o capturas para F1 / PIAPARTES-60.
