# HU-015-update – Smart Capture en diseño de informes (oleada 2, camino B)

Jira: [PIAPARTES-61](https://paqsystems.atlassian.net/browse/PIAPARTES-61)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-015-update |
| Título | Asistencia IA en el diseñador de Consulta detallada vía Smart Capture (GEN-03) — oleada 2 camino B |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE (oleada 2 productiva) |
| Estado | **Especificado** (Parte **C1** — 2026-11-13) |
| SPEC origen | [SPEC-015-update oleada 2 B](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-2-b-update.md) |
| A1 | [A1 ambiguity review](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-2-b-update-A1-ambiguity-review.md) — Apto con observaciones |
| SPEC base | [SPEC-015-ia-diseno-informes](../../05-open-spec/100-SistemaPartes/SPEC-015-ia-diseno-informes.md) |
| HU previa | [HU-015 piloto oleada 1](./HU-015-ia-diseno-informes.md) (matriz F1 → **B**) |
| TR posterior | [TR-015-update-b](../../04-tareas/100-SistemaPartes/TR-015-update-b-smart-capture-diseno-informes.md) |
| Limitaciones MVP | [20-ia-diseno-informes-limitaciones-mvp-b](../../02-producto/Sistema-Partes-IA/20-ia-diseno-informes-limitaciones-mvp-b.md) _(SoT citado por SPEC-update §3.1 / CA-B2-06)_ |
| Evidencia spike | [D-VERIFICACION-spike-015-oleada2-aplicador-2026-11-10](../../06-operacion/D-VERIFICACION-spike-015-oleada2-aplicador-2026-11-10.md) _(SPEC §6 Spike Must)_ |
| Épica Jira | [PIAPARTES-53](https://paqsystems.atlassian.net/browse/PIAPARTES-53) |

**Plantilla regla 19 (oleada 2 B):**  
Diseño informes Partes: adoptar **GEN-03** (`SmartCapturePanel` + turno host) y **GEN-15** (diseñador / Save). Motor layout = **acciones whitelist host**, no Prompt-to-Report DX. SoT: SPEC-015-update + limitaciones L-B. Host: `partes.informes.consultaDetallada`, `emission.design`, `/emisiones/disenador`.

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-015-update | Dónde en esta HU |
|---------------------------------------|------------------|
| §1 Problema / decisión B / resultado esperado | Narrativa; Contexto; CA-01, CA-02, CA-04 |
| §2 GEN-15, GEN-03, GEN-16, GEN-20, GEN-02 | Alcance; Reglas; Supuestos; Fuera alcance |
| §2 Host: superficie, turno, whitelist, aplicador, schema, §4.4 | Alcance; CA-02, CA-05, CA-08 |
| §2 Framework: GetDesignerModel / processCode; sin GEN “IA diseño” | Precondiciones; CA-12; Fuera alcance |
| §2 Fuera GEN: A, C, GEN-21, SC mobile | Fuera alcance; CA-10 |
| §3.1 Montaje SC bajo DX (R-ID-30) | Alcance; CA-01; Gherkin Panel |
| §3.1 API turno + schema (R-ID-02, R-ID-10…12) | Alcance; R-ID-02; R-B2-02 |
| §3.1 `replyText` + `actions[]` → lienzo (R-ID-03) | CA-02; Flujo; Gherkin |
| §3.1 Crear y modificar (#1–#6, #8); rechazo #7 | CA-05, CA-08; Gherkin |
| §3.1 Desactivar IA DX (`AIServicesEnabled` / sin wizard) | CA-06; R-B2-DX |
| §3.1 Limitaciones MVP doc 20 (L-B) | Tabla L-B; CA-07 |
| §3.1 Prereq sidecar: schema por proceso; sin `Dataset` stub | Precondiciones; CA-12 |
| §3.2 Fuera alcance | Fuera alcance |
| §4.1 Flujo 5 pasos | Contexto flujo; Gherkin |
| §4.2 R-B2-01…05 | Reglas (IDs alineados) |
| §5 CA-B2-01…06 | CA-01…07 (mapeo abajo) |
| §6 Impacto FE/BE/FW + Spike Must | Preguntas abiertas; Riesgos; → TR |
| §7 Riesgos | Riesgos de ambigüedad |
| A1: whitelist / ruta API / SPEC-016 / L-B-08 / spike | Preguntas abiertas; Supuestos |

### Mapeo CA-B2 (SPEC §5) → CA de esta HU

| SPEC | HU |
|------|-----|
| CA-B2-01 | **CA-01** |
| CA-B2-02 | **CA-02** |
| CA-B2-03 | **CA-04** |
| CA-B2-04 | **CA-05** |
| CA-B2-05 | **CA-06** |
| CA-B2-06 | **CA-07** |
| _(derivados §3.1 / §4)_ | **CA-03**, **CA-08…12** |

| Limitación L-B (doc citado por SPEC) | Dónde en esta HU |
|--------------------------------------|------------------|
| L-B-01 proceso único Must | Alcance; CA-01 |
| L-B-02 whitelist acciones | Alcance; R-ID-32; CA-11 |
| L-B-03 pasos catálogo, no REPX crudo | R-B2-03; Fuera alcance |
| L-B-04 incremental | R-B2-04; CA-08 (#6); CA-11 |
| L-B-05 sin Prompt-to-Report DX | CA-06 |
| L-B-06 GEN-16 para turno | R-B2-05; CA-09 |
| L-B-07 desktop | R-B2-01; CA-10 |
| L-B-08 voz/imagen opcional v1 | Alcance Should; Pregunta abierta #6 |
| L-B-09 schema / rechazo #7 | R-B2-02; CA-05 |
| L-B-10 sin auto-guardar | R-ID-03 / R-ID-20; CA-04 |
| L-B-11 usuario revisa lienzo | Narrativa; CA-02 |
| L-B-12 lab camino A | Fuera alcance; CA-07 |

---

## Narrativa

Como usuario con permiso **`emission.design`**  
quiero describir en **Smart Capture** (debajo del diseñador DevExpress) cómo debe verse o cambiar el informe de **Consulta detallada**  
para que el sistema **aplique acciones permitidas** y yo **vea el resultado en el lienzo**, revise, edite manualmente si hace falta y **guarde** con Save / Save As (flujo GEN-15), **sin** depender del wizard Prompt-to-Report de DevExpress.

Como responsable de producto  
quiero la **primera entrega productiva** del veredicto **B** (piloto oleada 1 / matriz F1), con alcance acotado (limitaciones L-B del SPEC) y adopción **GEN-03** + **GEN-15**,  
para ofrecer ayuda IA en diseño sin reabrir el camino **A** cancelado ni confundir con chat documental (GEN-21) ni con Smart Capture de Carga diaria.

---

## Contexto funcional

El SPEC-update fija: el piloto **A** (IA nativa DevExpress Prompt-to-Report) **no** continúa en Partes; la oleada 2 productiva es **camino B** — Smart Capture bajo el mismo diseñador (`/emisiones/disenador`), con motor de **acciones whitelist** (no Prompt-to-Report DX).

### Flujo (SPEC §4.1)

1. Usuario confirma proceso Consulta detallada → carga diseñador + variante.
2. Escribe en Smart Capture (texto; voz/imagen según L-B-08).
3. Backend valida contra schema; LLM propone acciones whitelist.
4. Front o sidecar **aplica** acciones → **cambio visible** en diseñador.
5. Usuario puede seguir editando manualmente; **Guardar** cuando conforme.

**Distinciones (SPEC §2):** no es GEN-21 (avatar); no usa los mismos endpoints que SC de Carga diaria; BYOK es **GEN-16 del usuario**, no la clave OpenAI del sidecar del piloto A. Persistencia solo vía Save / Save As DX (R-ID-20 / R-ID-03).

### Precondiciones

- Diseñador GEN-15 operativo: `/emisiones/disenador`, sidecar DX, proceso `partes.informes.consultaDetallada`, permiso `emission.design`.
- Veredicto oleada 1 = **B** (SPEC metadatos / §8).
- Schema semántico del proceso (R-ID-10…12; SPEC §3.1).
- Sidecar: schema por **proceso**; **un** data source; **sin** placeholder `Dataset` cuando hay `processCode` (SPEC §3.1).
- Mejora Framework `GetDesignerModel` por `processCode` (SPEC §2 / A1).
- GEN-16 operativo para enviar turnos (R-B2-05 / L-B-06).
- Pin SDK con exports Smart Capture / helpers GEN-03 (`SmartCapturePanel`, contrato turno v1, `pendingChoice`, etc. — SPEC §2).
- Spike aplicador (§6 Must): viabilidad JS DX vs patch REPX — evidencia lab referida en metadatos; mecanismo exacto → TR.

### Actores

| Actor | Diseñador + Smart Capture diseño |
|-------|----------------------------------|
| Usuario con **`emission.design`** + menú diseñador + **desktop** | Sí (R-B2-01; A1: mismo actor oleada 1) |
| Usuario solo Emitir (sin diseño) | No |
| Mobile / native | No (SPEC §2 / §3.2; L-B-07) |
| Sin credencial LLM (GEN-16) | Diseño **manual** OK; panel informa; sin turno efectivo (R-B2-05) |

---

## Alcance incluido

- Montar **Smart Capture debajo** del diseñador en `/emisiones/disenador` dentro de `renderDesigner` / host GEN-15 (R-ID-30; SPEC §3.1).
- API host de **turno diseño** con `processCode`, `reportUrl`/variante activa, contexto schema (SPEC §3.1); envelope **GEN-20**; authz `emission.design`.
- Contrato turno GEN-03 v1: `replyText` + **`actions[]`** tipadas; soporte de flujo **`pendingChoice`** del SDK cuando el contrato lo exija (SPEC §2) — semántica de dominio diseño (no `save` de tarea Carga diaria).
- Aplicación de acciones que **refleja** cambio en el lienzo DX (R-ID-03 / CA-B2-02); estrategia aplicador (REPX sidecar vs API JS) = decisión de TR tras Spike Must (SPEC §6 / A1).
- Crear y **modificar** informe abierto: equivalente batería SPEC-015 base §4.4 **#1–#6** y **#8** vía SC; rechazo campo inexistente **#7** (SPEC §3.1).
- Desactivar asistencia IA nativa DX en Partes (`AIServicesEnabled` / sin wizard Prompt-to-Report) (SPEC §3.1; CA-B2-05).
- Respetar limitaciones MVP L-B documentadas (SPEC §3.1; CA-B2-06).
- i18n producto `partes.emissionDesign.*` (GEN-02).
- Regresión Save / Save As GEN-15 (CA-B2-03).
- Whitelist de acciones layout (L-B-02): **catálogo exacto de nombres/parámetros en TR** (A1); orientación mínima A1 para TR: agrupar, subtotal, título, orientación simple — sin inventar firma en esta HU.
- Modalidad **texto** Must; voz/imagen según L-B-08 (Should / decisión TR — A1 pregunta 2).

---

## Fuera de alcance

- Reabrir camino **A** o **C** (SPEC §3.2).
- Emitir, dataset de consulta, filtros, mobile, chat documental mutando REPX (SPEC §3.2).
- Persistir layout fuera de Save / Save As DX (R-ID-20).
- Reparar REPX de laboratorio del piloto A (L-B-12).
- Nuevo ítem de menú solo para IA (SPEC §3.2).
- Framework: nuevo componente GEN publicado (SPEC §3.2); GEN-21; SC en mobile (SPEC §2).
- SPEC-016 / entrega productiva camino A: **obsoleto funcional** (SPEC metadatos); trazabilidad a pivotear en TR (SPEC §7).
- SC de Carga diaria (mismos endpoints) — no es este turno (SPEC §2).
- Enumerar o fijar en HU el contrato binario de cada acción (queda en TR — A1).

---

## Reglas de negocio

| ID | Regla | Origen |
|----|--------|--------|
| R-B2-01 | Solo usuarios con `emission.design` y **desktop**. | SPEC §4.2 |
| R-B2-02 | Acciones solo sobre campos del schema del `processCode`. | SPEC §4.2 |
| R-B2-03 | Prohibido aplicar REPX/XML crudo sin validación (L-B-03). | SPEC §4.2 |
| R-B2-04 | Edición incremental por defecto (L-B-04). | SPEC §4.2 |
| R-B2-05 | Sin credencial GEN-16: panel informa; diseño manual operativo. | SPEC §4.2 |
| R-B2-DX | IA nativa DX / Prompt-to-Report **no** accesible en Partes (`AIServicesEnabled` / sin wizard). | SPEC §3.1 / CA-B2-05 |
| R-ID-01 | Selección y confirmación de proceso emisible antes del DX. | SPEC-015 base / flujo §4.1 |
| R-ID-02 | IA solo schema autorizado; no inventa atributos. | SPEC §3.1 |
| R-ID-03 | Usuario revisa en diseñador; persiste con Save / Save As GEN. | SPEC §3.1 / resultado §1 |
| R-ID-04 | Misma superficie host GEN-15; sin segundo diseñador. | SPEC §2 GEN-15 |
| R-ID-10…12 | Schema por proceso; metadata reutilizable; rechazo fuera de catálogo. | SPEC §3.1 |
| R-ID-20 | Prohibido persistir layout desde turno IA sin Save DX. | SPEC §3.2 |
| R-ID-30 | Panel GEN **debajo** del DX en `renderDesigner`. | SPEC §3.1 / base §4.5 |
| R-ID-31 | Turno GEN-03 v1; authz diseño; mobile excluido. | SPEC §2 / base §4.5 |
| R-ID-32 | Acciones whitelist documentada en TR; sin XML REPX crudo del LLM. | SPEC / base §4.5 / A1 |

---

## Criterios de aceptación

- [ ] **CA-01** (CA-B2-01) Panel SC visible bajo DX con proceso Consulta detallada confirmado y permiso diseño.
- [ ] **CA-02** (CA-B2-02) Tras turno exitoso con acciones válidas, cambio **visible** en lienzo (no solo texto en el panel).
- [ ] **CA-03** Flujo base: confirmar proceso → cargar diseñador + variante sin romper shell GEN-15 (regresión diseñador).
- [ ] **CA-04** (CA-B2-03) Save y Save As GEN-15 sin regresión tras uso de SC; **sin** auto-guardar por el turno (L-B-10 / R-ID-20).
- [ ] **CA-05** (CA-B2-04) Prompt tipo §4.4 **#7** (campo inexistente) → rechazado o sin campo inventado.
- [ ] **CA-06** (CA-B2-05) Wizard Prompt-to-Report / asistencia IA nativa DX **no** accesible en Partes.
- [ ] **CA-07** (CA-B2-06) En cierre F1: L-B-01…L-B-11 respetadas; L-B-12 ignorada (no backlog de lab A).
- [ ] **CA-08** Evidencia documentada en español vía SC para equivalente §4.4 **#1–#6** y **#8** (al menos: layout con campos schema, agrupación, totalización razonable, modificación sobre informe abierto, Save As).
- [ ] **CA-09** Sin credencial LLM: panel informa (R-B2-05); diseño manual usable.
- [ ] **CA-10** Sin `emission.design` o en mobile: no acceso al SC de diseño (R-B2-01).
- [ ] **CA-11** Pedido que no mapea a whitelist / L-B-04 → respuesta clara **sin** aplicar pasos inválidos ni REPX crudo (R-B2-03, R-B2-04).
- [ ] **CA-12** Con `processCode` presente: diseñador usa schema del proceso (data source de proceso; **sin** depender de placeholder `Dataset` como fuente activa — SPEC §3.1).

### Escenarios Gherkin (oleada 2 B)

```gherkin
Feature: Smart Capture en diseño de informe Consulta detallada
  Como diseñador con emission.design
  Quiero pedir cambios de layout en lenguaje natural bajo el diseñador DevExpress
  Para ver el resultado en el lienzo y guardar con GEN-15

  Background:
    Given un usuario con permiso emission.design y credencial LLM configurada
    And abre /emisiones/disenador
    And confirma el proceso partes.informes.consultaDetallada
    And carga una variante de informe existente o nueva

  Scenario: Panel Smart Capture visible y sin Prompt-to-Report DX
    Then ve el diseñador DevExpress
    And ve el panel Smart Capture debajo del lienzo
    And no ve el wizard Prompt-to-Report nativo de DevExpress

  Scenario: Aplicar agrupación y ver en lienzo
    When escribe en Smart Capture un pedido de agrupación por un campo del schema
    And el sistema responde con acciones de whitelist válidas
    Then el diseñador muestra el cambio de agrupación en el lienzo
    And el panel muestra replyText coherente con lo aplicado

  Scenario: Modificación incremental sobre informe abierto
    Given un informe ya abierto en el diseñador
    When pide un ajuste incremental permitido por la whitelist
    Then el cambio es visible en el lienzo
    And el resto del layout no se reemplaza por un REPX opaco no validado

  Scenario: Subtotal y guardado manual
    When pide totalizar un campo numérico del schema por grupo
    Then aparece expresión o control de total coherente visible en lienzo
    When guarda con Save del diseñador
    Then el catálogo GEN-15 actualiza la variante sin error
    And el turno no persistió el layout por sí solo

  Scenario: Campo fuera del schema (prueba 7)
    When pide agregar un concepto inexistente en el schema del proceso
    Then el sistema no agrega un campo inventado al layout
    And informa rechazo o limitación clara

  Scenario: Pedido fuera de whitelist
    When escribe un pedido que no mapea a ninguna acción permitida
    Then no se aplica REPX ni XML crudo del modelo
    And el panel informa la limitación de forma clara

  Scenario: Sin credencial LLM
    Given un usuario sin credencial LLM en Preferencias
    Then el panel informa que debe configurar BYOK
    And puede seguir editando el informe manualmente en el diseñador

  Scenario: Permisos
    Given un usuario sin emission.design
    When intenta abrir el diseñador
    Then no accede al diseñador ni al Smart Capture de diseño
```

---

## Supuestos explícitos

- Veredicto oleada 1 = **B**; camino **A** cancelado en Partes (SPEC metadatos / §1 / L-B-05).
- Sidecar expone schema correcto por `processCode` (SPEC §2; A1).
- `SmartCapturePanel` admite montaje bajo `renderDesigner` sin conflicto de layout que impida el CA-B2-01 (A1).
- Comportamiento **visible** (CA-B2-02) es el contrato de producto; el mecanismo (JS designer vs patch REPX) puede divergir solo en implementación D tras Spike Must (A1 / SPEC §6).
- Whitelist v1 puede ser **subset** de L-B-02; nombres/parámetros exactos = TR (A1).
- L-B-08: v1 puede ser **solo texto** si se difiere voz/imagen (decisión humana A1 / TR).
- Deudas técnicas de lab (p. ej. cleanup stubs al guardar, expresiones de summary) no cambian el alcance funcional de esta HU; se tratan en TR / F1.

---

## Preguntas abiertas

| # | Pregunta | Estado |
|---|----------|--------|
| 1–6, 8 | Cerradas en [TR-015-update-b](../../04-tareas/100-SistemaPartes/TR-015-update-b-smart-capture-diseno-informes.md) **D-01…D-10** | **Cerrado** |
| 7 | Evidencia F1: E2E Playwright vs checklist manual | Abierta → **C1 / T8** (TR: checklist Must; E2E Should) |

---

## Riesgos de ambigüedad

| Riesgo | Impacto | Mitigación en SDD |
|--------|---------|-------------------|
| API web DX insuficiente para mutar layout (SPEC §7) | Retraso o subset de acciones v1 | Spike Must + acotar whitelist en TR; patch REPX en sidecar como alternativa |
| Catálogo de acciones no enumerado en SPEC (A1) | Dos TRs distintas | Tabla acciones v1 **obligatoria** en Parte C |
| Esfuerzo mayor que camino A (SPEC §7) | Alcance/tiempo | Limitar a L-B; no ampliar proceso (L-B-01) |
| Confusión SC diseño vs SC carga / GEN-21 | Endpoints o UX incorrectos | R-ID-31; Fuera alcance; i18n `partes.emissionDesign.*` |
| Calidad visual no garantizada (L-B-11) | Expectativa de usuario | CA-02 + revisión humana en F1; sin auto-save |

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-13 | Parte B: HU-015-update oleada 2 camino B desde SPEC-015-update; Jira PIAPARTES-61. |
| 2026-11-13 | Parte B1: enriquecimiento desde SPEC-update + A1 (trazabilidad CA-B2, R-B2, CA-12 Dataset, Gherkin, riesgos). |
| 2026-11-13 | Parte C: enlazada [TR-015-update-b](../../04-tareas/100-SistemaPartes/TR-015-update-b-smart-capture-diseno-informes.md). |
| 2026-11-13 | Parte C1: TR Apto con observaciones → HU **Especificado**. |
