# Verificación F1 — TR-015 Piloto IA diseño informes (oleada 1)

| Campo | Valor |
|-------|--------|
| **TR / HU / SPEC** | [TR-015](../../100-SistemaPartes/TR-015-piloto-dx-reporting-ia.md) · [HU-015](../../../03-historias-usuario/100-SistemaPartes/HU-015-ia-diseno-informes.md) · [SPEC-015](../../../05-open-spec/100-SistemaPartes/SPEC-015-ia-diseno-informes.md) |
| **Producto** | [19-ia-diseno-informes](../../../02-producto/Sistema-Partes-IA/19-ia-diseno-informes.md) |
| **Manual** | [SPEC-010-emisiones-informes](../../../99-manual-usuario/SPEC-010-emisiones-informes.md) § Asistencia IA piloto |
| **Ops:** [adopcion-tr-015-piloto-ia-diseno-sidecar](../../../06-operacion/adopcion-tr-015-piloto-ia-diseno-sidecar.md) · **Guía batería §4.4:** [guia-bateria-manual-tr-015-ia-diseno](../../../06-operacion/guia-bateria-manual-tr-015-ia-diseno.md) |
| **Jira** | PIAPARTES-53 (Epic) · PIAPARTES-54 (HU) · PIAPARTES-55 (TR) |
| **Fecha** | 2026-10-30 |
| **Entorno objetivo** | Lab: sidecar `:5055` + `PAQSUITE_EMISSION_DX_MODE=remote` + `OPENAI_API_KEY` en **proceso sidecar** |

---

## Resultado F1 (agente)

- **En curso** — implementación Parte D T1–T6 cerrada en código/docs; **veredicto A \| B \| C no firmado** hasta ejecutar batería §4.4 #1–#7 con IA habilitada.

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
| 1 | «Listá los partes por fecha y cliente» | **Pendiente** | |
| 2 | «Agrupá por cliente ordenados por fecha» | **Pendiente** | |
| 3 | «Totalizá minutos por técnico y mostrá horas» | **Pendiente** | Campo `duracionMinutos`; synonyms schema incluyen «técnico» → `usuarioNombre` |
| 4 | «Solo partes sin cargo» | **Pendiente** | Campo `sinCargo` |
| 5 | «Formato horizontal, título, encabezado, número de página» | **Pendiente** | |
| 6 | Sobre informe abierto: «Agregá subtotal de horas» | **Pendiente** | Driver veredicto **A vs B/C** |
| 7 | «Agregá rentabilidad de cada parte» | **Pendiente** | Debe **rechazar** o no inventar campo |
| 8 | Guardar como variante | **Pendiente** | Save As + catálogo GEN-15 (callbacks existentes) |

**Checklist previo a #1–#8**

- [ ] Sidecar FW desplegado / `dotnet run` con build post–Parte D
- [ ] `OPENAI_API_KEY` (o Azure) en env del **sidecar**, no Laravel
- [ ] Licencia DX incluye extensiones IA (CA-07 / D-A1-06)
- [ ] Partes: remote DX + proxy `/DXXRD`
- [ ] Usuario con permiso `emission.design`

---

## Matriz veredicto SPEC §5

| Dimensión | Pregunta | Conclusión (2026-10-30) | Impacto |
|-----------|----------|-------------------------|---------|
| Licencia / costo DX IA | ¿Permitido en nuestra suscripción? | **Pendiente** confirmación ops | Go / no-go piloto productivo |
| BYOK piloto | ¿Key solo sidecar (no GEN-16) aceptable? | **Sí** para oleada 1 (TR-015 Must) | Camino A documentado en runbook §6 |
| Generar (pruebas 1–5) | ¿Calidad aceptable ES? | **Pendiente** batería | Si OK → veredicto **A** probable |
| Modificar (prueba 6) | ¿Web 26.1 alcanza? | **Pendiente** | Si no → **B** o **C** |
| Regresión GEN-15 | ¿Save / catálogo / diseñador sin romper TR-011? | **Parcial** — Vitest OK; E2E ver abajo | Must pass antes de firmar |
| Esfuerzo SC | ¿Costo camino B vs A? | **Pendiente** post-F1 | Decisión oleada 2 |

### Decisión oleada 2

| Veredicto | Significado | Estado |
|-----------|-------------|--------|
| **A** | DX IA suficiente; oleada 2 = schema + operación Framework | **No firmado** |
| **B** | Modificación web insuficiente → Smart Capture bajo diseñador | **No firmado** |
| **C** | Híbrido SC + sidecar Prompt-to-Report | **No firmado** |

**Fila obligatoria:** `Veredicto = ___` — completar tras batería §4.4 y filas «Pendiente» de la matriz.

---

## Criterios SPEC §6 (CA-15)

| CA | Estado |
|----|--------|
| CA-15-01 Schema en sidecar | **OK** (archivos FW commit) |
| CA-15-02 IA habilitada entorno piloto | **Parcial** — código listo; falta env + smoke wizard |
| CA-15-03 Pruebas #1–#5 | **Pendiente** |
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
