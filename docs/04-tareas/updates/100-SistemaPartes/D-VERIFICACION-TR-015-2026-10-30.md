# Verificación F1 — TR-015 Piloto IA diseño informes (oleada 1)

| Campo | Valor |
|-------|--------|
| **TR / HU / SPEC** | [TR-015](../../100-SistemaPartes/TR-015-piloto-dx-reporting-ia.md) · [HU-015](../../../03-historias-usuario/100-SistemaPartes/HU-015-ia-diseno-informes.md) · [SPEC-015](../../../05-open-spec/100-SistemaPartes/SPEC-015-ia-diseno-informes.md) |
| **Producto** | [19-ia-diseno-informes](../../../02-producto/Sistema-Partes-IA/19-ia-diseno-informes.md) |
| **Manual** | [SPEC-010-emisiones-informes](../../../99-manual-usuario/SPEC-010-emisiones-informes.md) § Asistencia IA piloto |
| **Ops:** [adopcion-tr-015-piloto-ia-diseno-sidecar](../../../06-operacion/adopcion-tr-015-piloto-ia-diseno-sidecar.md) · **Guía batería §4.4:** [guia-bateria-manual-tr-015-ia-diseno](../../../06-operacion/guia-bateria-manual-tr-015-ia-diseno.md) |
| **Jira** | PIAPARTES-53 (Epic) · PIAPARTES-54 (HU) · PIAPARTES-55 (TR) |
| **Fecha** | 2026-10-30 (actualizado 2026-11-04) |
| **Entorno objetivo** | Lab: sidecar `:5055` + `PAQSUITE_EMISSION_DX_MODE=remote` + `OPENAI_API_KEY` en **proceso sidecar** |
| **Evidencia sidecar FW** | Repo Framework: `services/dx-reporting/docs/evidence/LAB-2026-11-04-prompt-to-report.md` · REPX: `…/docs/evidence/samples/Report-prueba-ia-1.repx` |

---

## Resultado F1 (agente)

- **Veredicto oleada 1 (2026-11-10): B** — Camino **A** (Prompt-to-Report DX) **cancelado** para producto Partes: sin entrega productiva estable; cuelgues wizard, schema incompleto por `reportUrl`, fallos de contrato. Continúa **oleada 2** vía Smart Capture bajo diseñador → [SPEC-015-update oleada 2](../../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-2-b-update.md).
- **Histórico parcial (lab):** Prompt-to-Report **completó** en lab 2026-11-04 («Report prueba ia 1»). Batería §4.4 #1–#8 incompleta; bindings **XRE023**. No bloquea veredicto **B**.

---

## Evidencia automatizada (2026-10-30)

| Pieza | Evidencia |
|-------|-----------|
| Schema semántico (CA-15-01) | FW `partes.informes.consultaDetallada.json` + alias `partes.consultaDetallada.principal.json` (14 columnas, `label` + synonyms Should) |
| Catálogo sin hardcode C# | `ProcessFieldSchemaCatalog.cs` — resolución JSON + candidatos Save As |
| IA condicional sidecar | `ReportingAiBootstrap.cs` — sin env → log stderr; con env → `AddPromptToReportConverter` + augmentación schema |
| Build sidecar | `dotnet build` OK (FW 26.1.4 + NuGet IA) |
| FE diseñador | `developmentMode` solo `import.meta.env.DEV` |
| Vitest | `ReportDesignerHostPage.test.tsx` — **2/2 OK** |
| E2E diseñador | Ver tabla **Tests** abajo |

---

## Batería piloto SPEC §4.4 (Must — operador `emission.design`, prompts en español)

Ejecutar con wizard **Prompt-to-Report** visible (sidecar con LLM). Capturas + REPX de muestra en carpeta del PR o adjunto Jira.

| # | Escenario | Estado | Evidencia / notas |
|---|-----------|--------|-------------------|
| 1 | «Listá los partes por fecha y cliente» | **Parcial (lab)** | 2026-11-04: generación única tipo consulta detallada; ver FW LAB + XRE023 |
| 2 | «Agrupá por cliente ordenados por fecha» | **Parcial (lab)** | Layout con subtotal por cliente; expresiones invalidan Validate Bindings |
| 3 | «Totalizá minutos por técnico y mostrá horas» | **Pendiente** | Campo `duracionMinutos`; synonyms schema incluyen «técnico» → `usuarioNombre` |
| 4 | «Solo partes sin cargo» | **Pendiente** | Campo `sinCargo` |
| 5 | «Formato horizontal, título, encabezado, número de página» | **Pendiente** | |
| 6 | Sobre informe abierto: «Agregá subtotal de horas» | **Pendiente** | Driver veredicto **A vs B/C** |
| 7 | «Agregá rentabilidad de cada parte» | **Pendiente** | Debe **rechazar** o no inventar campo |
| 8 | Guardar como variante | **Pendiente** | Save As + catálogo GEN-15 (callbacks existentes) |

**Checklist previo a #1–#8**

- [x] Sidecar FW desplegado / `dotnet run` con build post–Parte D (lab 2026-11-04)
- [x] `OPENAI_API_KEY` en **user-secrets** sidecar (`setup-openai-user-secrets.ps1`); no Laravel
- [x] Licencia DX + wizard Prompt-to-Report visible en diseñador (lab ST / DEV)
- [x] Partes: remote DX + proxy `/DXXRD` (`localhost:3001` → `:5055`)
- [x] Usuario con permiso `emission.design` (ST)

---

## Lab 2026-11-04 (evidencia operador + agente)

### Entorno

| Pieza | Valor |
|-------|--------|
| URL | `http://localhost:3001/emisiones/disenador?cliente=DEV` |
| Backend | `127.0.0.1:8012` |
| Sidecar | `127.0.0.1:5055` — health `reportingAiEnabled: true` |
| OpenAI | `gpt-4o-mini`; diagnóstico `Framework/services/dx-reporting/scripts/test-openai-key.ps1` |

### Cronología OpenAI

1. **429 quota** — `CHAT_HTTP_429` / sidecar `credit_balance_exhausted` → wizard *Report generation failed*.
2. Tras **créditos API** (key org personal): **`CHAT_HTTP_200_OK`** → generación posible.

### Smoke generación (Prompt-to-Report)

| Ítem | Evidencia |
|------|-----------|
| Nombre | Report prueba ia 1 |
| UI | Diseñador DevExpress abierto; tabla columnas alineadas a schema Consulta detallada |
| Agrupación | Subtotal by Cliente + totales `Sum(...)` |
| Paso intermedio | *Expanding the prompt…* — espera multi-minuto normal |
| Sidecar log | `GetDesignerModel` 200; tras quota fix sin `fail:` en corrida exitosa; preview `DXXRDV` 200 |
| Navegador (agente) | Snapshot diseñador + Validate Bindings con grilla XRE023 |

### Observación calidad (Must documentar)

DevExpress **Validate Bindings** → **XRE023** en Text/Visible: la IA enlazó **etiquetas** (`[Cliente]`, `[Duración en minutos]`) en lugar de **names** del schema (`clienteNombre`, `duracionMinutos`, …). SoT columnas: FW `partes.informes.consultaDetallada.json`.

**Seguimiento:** reforzar augmentación en sidecar o corrección manual en diseñador; no bloquea evidencia de **generación E2E oleada 1**.

### Artefactos

- FW: `services/dx-reporting/docs/evidence/LAB-2026-11-04-prompt-to-report.md`
- FW: `services/dx-reporting/docs/evidence/samples/Report-prueba-ia-1.repx`

---

## Matriz veredicto SPEC §5

| Dimensión | Pregunta | Conclusión (2026-10-30) | Impacto |
|-----------|----------|-------------------------|---------|
| Licencia / costo DX IA | ¿Permitido en nuestra suscripción? | **Pendiente** confirmación ops | Go / no-go piloto productivo |
| BYOK piloto | ¿Key solo sidecar (no GEN-16) aceptable? | **Sí** para oleada 1 (TR-015 Must) | Camino A documentado en runbook §6 |
| Generar (pruebas 1–5) | ¿Calidad aceptable ES? | **Parcial** — layout OK; XRE023 bindings | Veredicto **A** condicionado a fix schema/augmentación |
| Modificar (prueba 6) | ¿Web 26.1 alcanza? | **Pendiente** | Si no → **B** o **C** |
| Regresión GEN-15 | ¿Save / catálogo / diseñador sin romper TR-011? | **Parcial** — Vitest OK; E2E ver abajo | Must pass antes de firmar |
| Esfuerzo SC | ¿Costo camino B vs A? | **Pendiente** post-F1 | Decisión oleada 2 |

### Decisión oleada 2

| Veredicto | Significado | Estado |
|-----------|-------------|--------|
| **A** | DX IA suficiente; oleada 2 = schema + operación Framework | **No firmado** |
| **B** | Modificación web insuficiente → Smart Capture bajo diseñador | **Firmado 2026-11-10** |
| **C** | Híbrido SC + sidecar Prompt-to-Report | **Descartado** |

**Fila obligatoria:** `Veredicto = **B**` (decisión producto tras piloto A inestable / sin entrega productiva; oleada 2 → SPEC-015-update B).

---

## Verificación del agente (F1) — TR-015 piloto oleada 1

| Campo | Valor |
|-------|--------|
| TR | TR-015 |
| Fecha F1 | 2026-11-10 |
| Resultado | **Aprobado con observaciones** |

### Evidencia revisada

- Schema + bootstrap sidecar (FW); FE remote DX; lab 2026-11-04 generación única; Vitest diseñador 2/2; veredicto producto **B** documentado.

### Hallazgos críticos

- Ninguno que impida **cerrar el piloto** oleada 1: el objetivo era **decidir A\|B\|C**, no productizar camino A.
- Batería §4.4 #3–#8 **no** ejecutada en formalidad completa.

### Advertencias

- XRE023 bindings (labels vs `name`); E2E Playwright no corrido en PC; tests API emissions fallo esquema test SQLite (ajeno).

### Tests

- Vitest ReportDesignerHostPage: OK (2026-10-30).
- Playwright emisiones: **no ejecutado**.
- Batería manual §4.4: **parcial**.

### Pendientes

- Implementación oleada 2 (TR nueva / pivot) — fuera de TR-015.

### Recomendación final

- TR-015 → **Pendiente de revisión humana** (F1 agente OK con observaciones).
- Siguiente SDD: **Parte B** (HU oleada 2), **no** B1 hasta existir HU.

---

## Criterios SPEC §6 (CA-15)

| CA | Estado |
|----|--------|
| CA-15-01 Schema en sidecar | **OK** (archivos FW commit) |
| CA-15-02 IA habilitada entorno piloto | **OK lab** — user-secrets + wizard generó informe 2026-11-04 |
| CA-15-03 Pruebas #1–#5 | **Parcial** — smoke único; no batería formal |
| CA-15-04 Prueba #6 | **Pendiente** |
| CA-15-05 Prueba #7 | **Pendiente** |
| CA-15-06 Prueba #8 | **Pendiente** |
| CA-15-07 Matriz + veredicto | **En curso** (este doc) |
| CA-15-08 Smoke `/emisiones/disenador` | Ver **Tests** |

---

## Tests

| Comando | Resultado 2026-10-30 |
|---------|----------------------|
| `dotnet build` (FW sidecar) | OK |
| `npm run test -- --run src/features/partes/informes/ReportDesignerHostPage.test.tsx` | **2 passed** |
| `npx playwright test tests/e2e/partes-emisiones.spec.ts` | **No ejecutado** — falta `npx playwright install` (Chromium) en esta PC |
| `php artisan test --filter=ApiV1EmissionsPartesTest` | **Falló** — SQLite test DB sin columna `PQ_TASK_PROCESS.process_description` (deuda esquema test, no regresión TR-015 IA) |

---

## Recomendación agente

1. Operador con key: ejecutar #1–#8 y adjuntar evidencia en este archivo (o comentario Jira PIAPARTES-55).
2. Completar fila **Veredicto = A \| B \| C** y transicionar TR a **Pendiente de revisión** (Parte D cierre).
3. **No** abrir TR-016 oleada 2 hasta veredicto firmado (SPEC-015 / D-A1-05).

---

## Historial

| Fecha | Nota |
|-------|------|
| 2026-10-30 | Creado post Parte D (FW T1–T5, Partes T6, Vitest). Batería IA manual pendiente. |
| 2026-11-04 | Lab Prompt-to-Report OK (Report prueba ia 1); quota OpenAI resuelta; observación XRE023; FW LAB + REPX muestra; F1 **apto con observaciones** parcial. |
| 2026-11-10 | Veredicto piloto **B** firmado; oleada 2 SDD (SPEC-015-update B); limitaciones [20-ia-diseno…limitaciones-mvp-b](../../../02-producto/Sistema-Partes-IA/20-ia-diseno-informes-limitaciones-mvp-b.md). |
