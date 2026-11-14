# Plan de implementación — TR-016 (edición IA sobre informe existente)

> Parte **D1** (2026-11-06, ART). Implementación en **Parte D** (TR §7 T1–T6). SoT: [TR-016-edicion-ia-informes-existentes-prompt-to-report.md](../TR-016-edicion-ia-informes-existentes-prompt-to-report.md) · C1 **Apto con observaciones**.

## Alcance entendido

- El usuario con `emission.design` tiene un **`.repx` ya cargado** en Web Report Designer (variante de catálogo) y debe poder pedir a la IA un **cambio incremental** (ej. «Agregá subtotal de horas»), **revisarlo en el lienzo** y **guardar** con Save/Save As GEN-15.
- El trabajo **principal** es **Framework** `services/dx-reporting`: integrar o exponer el flujo que aplica Prompt-to-Report (o composición DX aprobada) **sobre el reporte abierto**, sin persistencia fuera del diseñador.
- **Partes:** validación de ciclo de vida FE (`reportUrl` cargado, `BeforeRender` / `AIServicesEnabled`), regresión Vitest, evidencia manual y doc runbook/manual mínimo.
- **No** Laravel de negocio nuevo, **no** motor IA paralelo en el host, **no** GEN-03.

## Fuentes leídas

- SPEC: [SPEC-016](../../05-open-spec/100-SistemaPartes/SPEC-016-prompt-to-report-edicion-informes.md)
- HU: [HU-016](../../03-historias-usuario/100-SistemaPartes/HU-016-ia-edicion-informes-existentes.md)
- TR: [TR-016](../TR-016-edicion-ia-informes-existentes-prompt-to-report.md)
- C1: [TR-016-C1](../TR-016-edicion-ia-informes-existentes-C1-ambiguity-review.md)
- Referencia piloto: [D1-TR-015](./D1-TR-015-piloto-dx-reporting-ia.md), [TR-015](../TR-015-piloto-dx-reporting-ia.md)
- Código: Framework `Program.cs`, `ReportingAiBootstrap.cs`; Partes `DxReportDesignerApp.tsx`, `DxReportDesignerPanel.tsx`, `enableReportDesignerAi.ts`

## Impacto esperado

### Base de datos

- Ninguno.

### Backend (Laravel Partes)

- Ninguno Must (RN-TR-16-02).

### Framework — sidecar (`PaqSuite.DxReporting`)

| Acción | Detalle |
|--------|---------|
| **T1** | Leer doc DevExpress 26.1 § Prompt-to-Report en Web Report Designer: flujos **nuevo reporte** vs **reporte abierto**; API cliente (`AIServices`/wizard); qué construye el “converter” actual (`AddPromptToReportConverter`). Salida: nota en runbook o comentario enlazado en `ReportingAiBootstrap.cs`. |
| **T2** | Si la doc DX confirma edición sobre modelo cargado: asegurar que el pipeline IA se invoca con el **reporte actual** (no solo creación). Si no existe API directa en web: diseñar **composición** (p. ej. llamada servidor que mergea resultado en modelo y devuelve al diseñador) **dentro del sidecar**, sin duplicar GEN-15 en Partes. DoD: prompt canónico sobre variante cargada modifica el lienzo. |
| **T3** | Ajustar `SetPromptAugmentation` / mensajes del converter para **edición incremental** (no reemplazar layout entero salvo pedido explícito); alinear con schema JSON existente (TR-015); reducir riesgo XRE023 (bindings a nombres de campo). |

**Archivos probables (FW):**

- `services/dx-reporting/src/PaqSuite.DxReporting/Reporting/ReportingAiBootstrap.cs`
- `services/dx-reporting/src/PaqSuite.DxReporting/Reporting/ProcessFieldSchemaCatalog.cs` (solo si T3 requiere augmentación distinta para “modo edición”)
- `services/dx-reporting/docs/06-operacion/runbook-dx-reporting-sidecar.md` (o README sidecar)
- Posibles extensiones en controladores DX (`MapControllers` / handlers DXXRD) según lo que exija T2 tras T1

### Frontend — Partes

| Acción | Detalle |
|--------|---------|
| **T4** | Verificar que `enableReportDesignerAiServices()` en `BeforeRender` corre con **`reportUrl` de variante existente** (el panel ya usa `key={reportUrl}`). Si DX no muestra IA cuando falta LLM, valorar **un aviso host** mínimo (solo si C1 lo exige para CA-016-08): banner bajo el diseñador usando `t('emissions.designer.*')` o claves `partes.informes.*` en 5 locales — **solo** si hay texto nuevo estable. |

**Archivos probables (Partes):**

- `frontend/src/features/partes/informes/DxReportDesignerApp.tsx`
- `frontend/src/features/partes/informes/DxReportDesignerPanel.tsx`
- Opcional: `frontend/src/locales/*/common.json` (o namespace usado por emisiones)

### Tests

- Vitest: `ReportDesignerHostPage.test.tsx` (regresión tras T4).
- Manual Must: checklist TR-016 §2 (CA-016-01…09).
- E2E: opcional si LLM estable en CI; si no, documentar exclusión en F1.

### Documentación

- `D-VERIFICACION-TR-016-<fecha>.md` en `docs/04-tareas/updates/100-SistemaPartes/` (T5).
- Runbook Framework (T6).

### DevOps

- Misma infra que TR-015: sidecar con `OPENAI_API_KEY` / `AZURE_OPENAI_*` en proceso .NET; sin secrets en Git.

## Orden de trabajo (Parte D)

| Fase | Tareas | Repo | Notas |
|------|--------|------|--------|
| 1 | T1 | FW | **Bloqueante** para T2 |
| 2 | T2 → T3 | FW | T2 puede requerir bump NuGet o wiring adicional según doc DX |
| 3 | T4 | Partes | Tras T2 en lab o con sidecar preview |
| 4 | T5 | Partes | Evidencia + REPX antes/después |
| 5 | T6 | FW + Partes | Operador repite con runbook |

Paralelo posible: T6 borrador con T1 (estructura runbook) sin afirmar comportamiento hasta T2.

## Riesgos (D1)

| Riesgo | Mitigación |
|--------|------------|
| DX Web no documenta modificación directa sobre REPX abierto | T2: composición en sidecar; no cerrar con doc-only |
| Regresión creación nueva (CA-016-09) | Smoke explícito post-T2 |
| CA-016-08 ambiguo (degradación visible) | T4: si DX calla el ribbon IA, banner host documentado en F1 |
| Colateral en layout (CA-016-03) | Capturas + lista de bandas/labels tocados en evidencia |

## Tests a ejecutar (post-D)

```powershell
# Framework — build sidecar
cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\src\PaqSuite.DxReporting
dotnet build

# Partes FE — regresión diseñador
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\frontend
npm run test -- --run src/features/partes/informes/ReportDesignerHostPage.test.tsx
```

Manual Must: script T5 (variante cargada → prompt → Validate Bindings → Save).

## Decisiones D1 (absorber en D)

1. **Fuente de verdad técnica:** resultado de **T1** (doc DX + inspección API). Si el flujo nativo existe, T2 lo cablea; si no, T2 define la composición mínima en sidecar.
2. **Sin endpoints Laravel** para IA en Partes (RN-TR-16-02).
3. **CA-016-08:** priorizar comportamiento DX; si no alcanza, un único banner host **i18n** (5 locales) — acordado en implementación T4, evidenciado en F1.
4. **PR cross-repo:** merge Framework primero o release coordinado documentado en F1 (mismo patrón TR-015).

## Dudas / bloqueos

- Ninguno que bloquee D1: la incógnita principal se resuelve en **T1**.

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR: Sí** (solo concreción técnica T1/T2 y posible UI mínima de degradación acotada a CA-016-08).
