# SPEC-015-update (oleada 2) – IA en diseño de informes — camino B (Smart Capture)

Jira: [PIAPARTES-53](https://paqsystems.atlassian.net/browse/PIAPARTES-53) (misma épica SPEC-015; **no** A0 nuevo — update sobre épica existente)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-2-b |
| Título | Asistencia IA en diseñador de emisiones vía Smart Capture (camino B) |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Especificado** (Parte A + A1 — 2026-11-10) |
| A1 | [A1 ambiguity review](./SPEC-015-ia-diseno-informes-oleada-2-b-update-A1-ambiguity-review.md) — Apto con observaciones |
| Última actualización | 2026-11-10 |
| SPEC base | [SPEC-015-ia-diseno-informes](../../100-SistemaPartes/SPEC-015-ia-diseno-informes.md) |
| SPEC satélite | [SPEC-016](../../100-SistemaPartes/SPEC-016-prompt-to-report-edicion-informes.md) — **obsoleto funcional** (sustituido por este update; camino A cancelado) |
| Limitaciones MVP | [20-ia-diseno-informes-limitaciones-mvp-b](../../../02-producto/Sistema-Partes-IA/20-ia-diseno-informes-limitaciones-mvp-b.md) |
| HU relacionada(s) | [HU-015-update](../../03-historias-usuario/100-SistemaPartes/HU-015-update-ia-diseno-informes-camino-b.md) · [PIAPARTES-61](https://paqsystems.atlassian.net/browse/PIAPARTES-61) |
| TR relacionada(s) | [TR-015-update-b](../../../04-tareas/100-SistemaPartes/TR-015-update-b-smart-capture-diseno-informes.md) |
| Origen decisión | Piloto oleada 1: camino **A** descartado (inestabilidad Prompt-to-Report web); veredicto **B** ST 2026-11-10 |
| Épica Jira histórica | [PIAPARTES-58](https://paqsystems.atlassian.net/browse/PIAPARTES-58) |

**Plantilla regla 19 (oleada 2 B):**  
Diseño informes Partes: adoptar **GEN-03** (`SmartCapturePanel` + turno host) y **GEN-15** (diseñador / Save). Motor layout = **acciones whitelist host**, no Prompt-to-Report DX. SoT: este SPEC + limitaciones L-B. Host: `partes.informes.consultaDetallada`, `emission.design`, `/emisiones/disenador`.

---

## 1. Resumen ejecutivo

- **Problema:** quien diseña el informe de Consulta detallada necesita ayuda en lenguaje natural para armar o ajustar el layout, sin dominar todas las bandas y expresiones de DevExpress.
- **Decisión:** el piloto **A** (IA nativa DevExpress Prompt-to-Report) **no** continúa en Partes; la oleada 2 productiva será **camino B** (Smart Capture bajo el mismo diseñador).
- **Resultado esperado:** con `emission.design`, el usuario pide cambios en el panel Smart Capture; el sistema aplica **acciones permitidas**; el usuario **ve el cambio en el diseñador gráfico**, revisa y **guarda** con GEN-15.

---

## 2. Qué GEN adoptamos (no reinventar)

| GEN | Rol en esta entrega | Qué **no** es |
|-----|---------------------|---------------|
| **GEN-15** | Shell `EmissionReportDesignerPage`, diseñador DX, catálogo reportes, Save / Save As, `emission.design`, sidecar **sin IA DX** para diseño. | No es un nuevo motor de informes ni un segundo diseñador. |
| **GEN-03** | `SmartCapturePanel`, contrato turno v1, `pendingChoice`, helpers SDK (`postSmartCaptureTurn`, `applySmartCaptureActions`, …). | No es el chat del avatar (GEN-21) ni SC de Carga diaria con los mismos endpoints. |
| **GEN-16** | Credencial LLM del **usuario** en el turno (BYOK). | No es la clave OpenAI del sidecar del piloto A. |
| **GEN-20** | Envelope `{ error, respuesta, resultado }` en APIs de turno. | — |
| **GEN-02** | i18n del panel y mensajes producto `partes.emissionDesign.*`. | — |

**Host (Partes) aporta:** superficie bajo `renderDesigner`, API turno diseño, **whitelist de acciones layout**, aplicador hacia el modelo del diseñador (REPX o API JS — definir en TR), schema semántico `partes.informes.consultaDetallada`, pruebas §4.4 adaptadas a B.

**Framework aporta (complemento, no GEN nuevo):** sidecar **GEN-15** clásico; mejora **GetDesignerModel** por `processCode` (CC-ST #3); **no** se desarrolla un GEN de “IA diseño” aparte: es adopción **GEN-03** en excepción D-A1-04 (solo diseñador + `emission.design`).

**Fuera de alcance GEN:** camino A (Prompt-to-Report UI DX), camino C, GEN-21, Smart Capture en mobile.

---

## 3. Alcance

### 3.1 En alcance

- Montar **Smart Capture debajo** del diseñador en `/emisiones/disenador` (R-ID-30 SPEC-015).
- API host: turno diseño con `processCode`, `reportUrl`/variante activa, contexto schema (R-ID-02, R-ID-10…12).
- Respuesta turno: `replyText` + **`actions[]`** tipadas; aplicación que **refleja** en el lienso del DX (R-ID-03).
- Crear y **modificar** informe abierto (equivalente batería §4.4 #1–#6 y #8, vía SC).
- Rechazo campo inexistente (#7).
- Desactivar asistencia IA nativa DX en Partes (`AIServicesEnabled` / sin wizard Prompt-to-Report).
- Limitaciones MVP documentadas en [20-ia-diseno…limitaciones-mvp-b](../../../02-producto/Sistema-Partes-IA/20-ia-diseno-informes-limitaciones-mvp-b.md).
- Prereq sidecar: schema por **proceso** (un data source; sin placeholder `Dataset` cuando hay `processCode`).

### 3.2 Fuera de alcance

- Reabrir camino **A** o **C**.
- Emitir, dataset de consulta, filtros, mobile, chat documental mutando REPX.
- Persistir layout fuera de Save / Save As DX (R-ID-20).
- Reparar REPX de laboratorio del piloto A (L-B-12).
- Nuevo ítem de menú solo para IA (superficie = diseñador existente).
- Framework: nuevo componente GEN publicado (salvo evolución futura del contrato de acciones si se transversaliza).

---

## 4. Comportamiento funcional (borrador)

### 4.1 Flujo

1. Usuario confirma proceso Consulta detallada → carga diseñador + variante.
2. Escribe en Smart Capture (texto; voz/imagen según L-B-08).
3. Backend valida contra schema; LLM propone acciones whitelist.
4. Front o sidecar **aplica** acciones → **cambio visible** en diseñador.
5. Usuario puede seguir editando manualmente; **Guardar** cuando conforme.

### 4.2 Reglas

| ID | Regla |
|----|--------|
| R-B2-01 | Solo `emission.design`, desktop. |
| R-B2-02 | Acciones solo sobre campos del schema del `processCode`. |
| R-B2-03 | Prohibido aplicar REPX/XML crudo sin validación (L-B-03). |
| R-B2-04 | Edición incremental por defecto (L-B-04). |
| R-B2-05 | Sin credencial GEN-16: panel informa; diseño manual operativo. |

---

## 5. Criterios verificables (SPEC)

- [ ] **CA-B2-01** Panel SC visible bajo DX con proceso confirmado y permiso diseño.
- [ ] **CA-B2-02** Tras turno exitoso, cambio **visible** en lienzo (no solo texto en chat).
- [ ] **CA-B2-03** Save / Save As GEN-15 sin regresión.
- [ ] **CA-B2-04** Prompt §4.4 #7 rechazado o sin campo inventado.
- [ ] **CA-B2-05** Wizard Prompt-to-Report DX **no** accesible en Partes.
- [ ] **CA-B2-06** Limitaciones L-B-01…L-B-11 respetadas en cierre F1; L-B-12 ignorada.

---

## 6. Impacto técnico (visión TR)

| Capa | Ítems |
|------|--------|
| FE | `ReportDesignerHostPage` / panel SC; aplicador; desactivar IA DX; i18n |
| BE | Turno diseño; servicio dominio; validación schema; opcional SP |
| FW sidecar | `processCode` en `GetDesignerModel`; sin OpenAI obligatorio para diseño |
| Spike **Must** | Viabilidad aplicador (JS DX vs patch REPX) |

---

## 7. Riesgos

- API web DX insuficiente para mutar layout → acotar acciones v1 o patch REPX en sidecar.
- Esfuerzo mayor que el estimado para A.
- TR-016 / SPEC-016 deben pivotear trazabilidad a este update.

---

## 8. SDD — estado y siguientes partes

| Parte | Estado | Siguiente |
|-------|--------|-----------|
| **A** (este documento) | **Hecho** | — |
| **A1** | **Hecho** | Ver enlace metadatos |
| **B** | **Hecho** (2026-11-13) | [HU-015-update](../../03-historias-usuario/100-SistemaPartes/HU-015-update-ia-diseno-informes-camino-b.md) |
| **B1** | **Hecho** (2026-11-13) | HU enriquecida |
| **C** | **Hecho** (2026-11-13) | [TR-015-update-b](../../../04-tareas/100-SistemaPartes/TR-015-update-b-smart-capture-diseno-informes.md) |
| **C1** | **Hecho** (2026-11-13) | [C1](../../../04-tareas/100-SistemaPartes/TR-015-update-b-smart-capture-diseno-informes-C1-ambiguity-review.md) — Apto con obs. |
| **D1** | **Hecho** (2026-11-13) | [D1 plan](../../../04-tareas/100-SistemaPartes/d1/D1-TR-015-update-b-smart-capture-diseno-informes.md) |
| **D** | **Siguiente** | Implementar T1–T9 |
| **E…F** | Pendiente | Tras D |

**Veredicto oleada 1 F1:** registrar en D-VERIFICACION-TR-015: **B** (A cancelado).

---

## 9. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-10 | Parte A: SPEC-update oleada 2 camino B; GEN-03 + GEN-15 + GEN-16; limitaciones en doc 20. |
| 2026-11-13 | Parte B: HU-015-update + Story PIAPARTES-61. |
| 2026-11-13 | Parte B1: HU-015-update enriquecida (lista para Parte C). |
| 2026-11-13 | Parte C: TR-015-update-b. |
| 2026-11-13 | Parte C1: Apto con observaciones; HU/TR Especificado. |
| 2026-11-13 | Parte D1: plan T1–T9 (FW apply + Partes SC/turn). |
