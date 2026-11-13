# TR-015-update-b – Smart Capture en diseño de informes (oleada 2, camino B)

Jira: [PIAPARTES-62](https://paqsystems.atlassian.net/browse/PIAPARTES-62) (Task bajo Epic PIAPARTES-53; Story HU = PIAPARTES-61)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-015-update](../../03-historias-usuario/100-SistemaPartes/HU-015-update-ia-diseno-informes-camino-b.md) · [PIAPARTES-61](https://paqsystems.atlassian.net/browse/PIAPARTES-61) |
| **SPEC relacionada** | [SPEC-015-update oleada 2 B](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-2-b-update.md) |
| **SPEC base** | [SPEC-015](../../05-open-spec/100-SistemaPartes/SPEC-015-ia-diseno-informes.md) |
| **Limitaciones MVP** | [20-ia-diseno-informes-limitaciones-mvp-b](../../02-producto/Sistema-Partes-IA/20-ia-diseno-informes-limitaciones-mvp-b.md) |
| **Spike** | [comparativa](../../06-operacion/spike-015-oleada2-aplicador-layout-comparativa.md) · [D-VERIFICACION](../../06-operacion/D-VERIFICACION-spike-015-oleada2-aplicador-2026-11-10.md) — **APTO camino B** |
| **Norma GEN** | **GEN-03** (`SmartCapturePanel` + turno v1) · **GEN-15** (diseñador / Save) · **GEN-16** (BYOK) · **GEN-20** · **GEN-02** |
| **Épica** | 100 — Sistema Partes ([PIAPARTES-53](https://paqsystems.atlassian.net/browse/PIAPARTES-53)) |
| **Prioridad** | MUST-HAVE |
| **Roles** | `emission.design` + desktop + `/emisiones/disenador` |
| **Dependencias** | [TR-011](./TR-011-reportes-emisiones.md); [TR-015](./TR-015-piloto-dx-reporting-ia.md) (schema + veredicto B); [TR-010](./TR-010-smart-capture-carga-diaria.md) (patrón GEN-03, **no** mismos endpoints); Framework `dx-reporting` + fix `GetDesignerModel`/`processCode` |
| **Clasificación** | HU COMPLEJA (host + sidecar) |
| **Última actualización** | 2026-11-13 |
| **Estado** | **Pendiente de Revisión** (Parte D — 2026-11-13) |
| **Evidencia D** | [D-VERIFICACION-TR-015-update-b-2026-11-13](../updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-b-2026-11-13.md) |
| **Revisión C1** | [C1 ambiguity review](./TR-015-update-b-smart-capture-diseno-informes-C1-ambiguity-review.md) — **Apto con observaciones** |
| **Obsoleto funcional (camino A)** | [SPEC-016](../../05-open-spec/100-SistemaPartes/SPEC-016-prompt-to-report-edicion-informes.md) / [TR-016](./TR-016-edicion-ia-informes-existentes-prompt-to-report.md) — **no** es la entrega MVP; pivot trazabilidad a este TR |

**Origen:** [HU-015-update](../../03-historias-usuario/100-SistemaPartes/HU-015-update-ia-diseno-informes-camino-b.md)  
**Referencia SPEC:** [SPEC-015-update](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-2-b-update.md)  
**Plan D1:** [D1-TR-015-update-b-smart-capture-diseno-informes](./d1/D1-TR-015-update-b-smart-capture-diseno-informes.md)

**Plantilla regla 19:**  
Diseño informes Partes oleada 2 B: adoptar **GEN-03** + **GEN-15**; motor = **acciones whitelist** + **patch REPX en sidecar**; no Prompt-to-Report DX; no reimplementar panel SC ni diseñador. SoT: SPEC-update + HU-015-update + este TR. Host: `partes.informes.consultaDetallada`, `emission.design`.

**Referencias técnicas:**

- Host FE: `frontend/src/features/partes/informes/ReportDesignerHostPage.tsx`, `DxReportDesignerPanel.tsx`, `DxReportDesignerApp.tsx`
- Host BE SC carga (patrón, no copiar dominio): `PartesTareaSmartCaptureTurnService`, `POST /api/v1/partes/tareas/asistente/turn`
- Sidecar: `PaqSuite-IA-FRAMEWORK/services/dx-reporting/` · scripts spike `scripts/spike/Apply-Act0{1,2,3}-*.ps1`
- Schema: `data/schemas/partes.informes.consultaDetallada.json`

---

## Decisiones TR (cierran preguntas HU / A1)

| # | Tema | Decisión |
|---|------|----------|
| D-01 | Nombre TR | **`TR-015-update-b`** (esta entrega). TR-016 / SPEC-016 = obsoletos funcionales para MVP B. |
| D-02 | Aplicador | **Patch REPX en sidecar** (veredicto spike B). **No** mutación JS DX como camino productivo v1. |
| D-03 | Refresh | **Obligatorio** tras apply: recargar modelo del diseñador (`reportUrl` / `GetDesignerModel`) y mensaje i18n de actualización. Sin exigir full page F5 si el host puede remount del panel; si no, remount/reopen variante documentado. |
| D-04 | Turno Laravel | **`POST /api/v1/emissions/design/processes/{processCode}/assistant/turn`** — envelope GEN-20; distinto de `/partes/tareas/asistente/turn`. |
| D-05 | Apply sidecar | **`POST /api/layout/apply`** (sidecar, auth operativa alineada a DXXRD / API key interna documentada en runbook). Body: `reportUrl` + `actions[]` ya validadas. Laravel **no** persiste REPX vía este apply como “Save”; solo muta archivo layout en disco del sidecar. Persistencia catálogo = Save GEN-15 existente. |
| D-06 | Orquestación FE | 1) `postSmartCaptureTurn` (o wrapper host) → 2) si `actions[]` no vacío → `apply` sidecar → 3) refresh diseñador → 4) `applySmartCaptureActions` solo para side-effects UI del panel (p. ej. limpiar pending), **sin** interpretar REPX en JS. |
| D-07 | L-B-08 | **v1 = solo texto** en SC diseño. Voz/imagen = Should diferido. |
| D-08 | IA DX Partes | **Desactivar** `enableReportDesignerAiServices` / equivalente en host Partes; sidecar puede quedar sin OpenAI obligatorio para diseño B. |
| D-09 | Whitelist v1 | Tabla §5.1 (mínimo spike ACT-01..03 + orientación). Ampliar = cambio TR/L-B. |
| D-10 | D4 / XRE090 | **Must** en esta TR: cleanup stubs al Save (D4); ACT-03 productivo con `XRSummary` si hace falta para evitar XRE090. |
| D-11 | Proxy apply (C1) | FE llama apply por **same-origin** `/api/layout/...` (Vite proxy → `VITE_DX_REPORTING_HOST`; equivalente nginx en preview/prod). **Prohibido** depender de CORS directo browser→`:5055` como camino feliz. Sin API key en el browser en v1. |
| D-12 | Acciones inválidas (C1) | Turno LLM: `type` fuera de §5.1 → **omitir** esa acción + `replyText` de limitación (no fallar todo el turno). **422** si el cliente envía payload apply/turn manifiestamente inválido (schema request). |
| D-13 | Dirty + reportUrl (C1) | Turno/apply requieren `reportUrl` de variante de catálogo. Si el diseñador tiene cambios sin guardar: confirmar (i18n) antes de apply; el refresh **descarta** in-memory (L-B-11). Sin `reportUrl` → no ejecutar turn (mensaje claro). |

---

## 1) HU refinada (resumen)

### In scope

- Montar `SmartCapturePanel` **debajo** del DX en `/emisiones/disenador` tras confirmar `partes.informes.consultaDetallada`.
- Turno host diseño (D-04) con BYOK GEN-16; `replyText` + `actions[]` whitelist; `pendingChoice` si el contrato GEN-03 lo requiere para desambiguar campo/acción.
- Aplicador REPX sidecar (D-02/D-05) + refresh (D-03).
- Desactivar Prompt-to-Report en Partes (D-08).
- Batería §4.4 #1–#8 vía SC; L-B-01…11; proceso único Must.
- Regresión Save / Save As TR-011.
- i18n `partes.emissionDesign.*` (5 locales).
- Tests: unit BE turno/validación; FE montaje/gate; Feature API; checklist F1 (E2E Playwright diseñador = Should si frágil).

### Out of scope

- Camino A/C; TR-016 productiva; Emitir; mobile; chat GEN-21 mutando REPX; auto-save; REPX crudo del LLM; otros `processCode`; voz/imagen v1 (D-07); nuevo menú.

### Discrepancias SPEC ↔ HU

Ninguna de alcance. Mecanismo aplicador quedó abierto en SPEC §6; **cerrado aquí D-02** con evidencia spike.

---

## 2) Criterios de aceptación (AC)

| AC HU | Verificación técnica |
|-------|----------------------|
| CA-01 | Panel SC `data-testid` bajo DX tras confirm proceso; oculto sin diseño |
| CA-02 | Tras turn+apply+refresh, cambio visible en lienzo (ACT-01 o ACT-02 smoke) |
| CA-03 | Flujo proceso → diseñador sin regresión shell |
| CA-04 | Save / Save As callbacks GEN existentes OK; turno **no** escribe catálogo Laravel |
| CA-05 | Prompt “rentabilidad…” → `actions=[]` o error dominio; sin columna inventada |
| CA-06 | Sin UI Prompt-to-Report / AI wizard en Partes (D-08) |
| CA-07 | Checklist F1 referencia L-B; L-B-12 excluida |
| CA-08 | Evidencia #1–#6+#8 documentada en `D-VERIFICACION-TR-015-update-b-*` |
| CA-09 | Sin BYOK: `configurationRequired` / mensaje Preferencias; sin POST turn efectivo |
| CA-10 | Sin `emission.design` → 403/4709 según contrato emisiones; native deny |
| CA-11 | Acción fuera de whitelist rechazada en BE antes de apply |
| CA-12 | `GetDesignerModel` + `processCode` → data source proceso; sin stub `Dataset` activo |

### Escenarios Gherkin

Reutilizar los de [HU-015-update](../../03-historias-usuario/100-SistemaPartes/HU-015-update-ia-diseno-informes-camino-b.md) (panel, agrupación, incremental, #7, whitelist, BYOK, permisos). Evidencia F1 debe citar esos escenarios.

---

## 3) Reglas de implementación

| ID | Regla |
|----|--------|
| RN-B-01 | **Prohibido** persistir layout al catálogo desde el turno; solo Save/Save As GEN-15 (R-ID-20). |
| RN-B-02 | **Prohibido** aplicar XML/REPX crudo del LLM; solo `actions[]` de §5.1 tras validación schema. |
| RN-B-03 | Authz turno: Bearer + `X-Paq-Cliente` + **`emission.design`** (no `partes.profile` de carga diaria). |
| RN-B-04 | `processCode` Must = `partes.informes.consultaDetallada` en v1; otro código → 422. |
| RN-B-05 | Campos en acciones = `name` técnico del schema JSON (no inventar). |
| RN-B-06 | Acceso negocio host: SP si hay persistencia nueva; turno LLM = excepción GEN-like auth/plumbing documentada; **apply** es sidecar (no SQL). |
| RN-B-07 | No copiar carpetas GEN; usar exports `@paqsuite/react-core` / `laravel-core` Smart Capture. |
| RN-B-08 | Sidecar apply: idempotencia best-effort por `(reportUrl, actionType, params hash)` en request; fallos → envelope/error claro, **sin** Save parcial en Laravel. |
| RN-B-09 | Tras apply exitoso el FE **debe** refresh (D-03); no dejar lienzo stale. |
| RN-B-10 | i18n 5 locales; sin literales ES en JSX. |
| RN-B-11 | `data-testid`: `emissionDesignSmartCapture`, `emissionDesignAssistantTurn`, mensajes error `emissionDesignFormError` / panel. |

---

## 4) Impacto en datos

- **Sin** migraciones Laravel obligatorias para v1 (turno stateless + layouts sidecar existentes).
- REPX en `data/layouts/{reportUrl}.repx` del sidecar (igual TR-011).
- Schema JSON existente (TR-015); sin diccionario duplicado.
- Si se registra bitácora de turnos: solo si TR lo añade en Should; **no** Must.

---

## 5) Contratos de API

### 5.1 Whitelist acciones v1 (Must)

| `type` | Parámetros | Efecto (REPX) | Spike |
|--------|------------|---------------|-------|
| `setReportTitle` | `title: string` (1…200) | `DisplayName` / título informe | ACT-01 |
| `addGroupByField` | `field: string` (schema) | GroupHeader + GroupField | ACT-02 |
| `addGroupSum` | `field: string` (numérico schema); `summary?: "sum"` default sum | GroupFooter + summary/`XRSummary` | ACT-03 |
| `setPageOrientation` | `orientation: "portrait" \| "landscape"` | Orientación página | A1 mín. |

**Should (si esfuerzo bajo):** `removeGroupByField`, `setDetailColumns` (lista de `name` schema, max N).

Cualquier otro `type` → **D-12** (omitir en turno LLM; 422 solo payload cliente inválido).

### 5.2 Turno Laravel

`POST /api/v1/emissions/design/processes/{processCode}/assistant/turn`

**Auth:** Sanctum + `X-Paq-Cliente` + permiso `emission.design`.

**Request (JSON):**

```json
{
  "message": "agrupá por cliente y sumá los minutos",
  "reportUrl": "partes.consultaDetallada.principal",
  "locale": "es",
  "pendingChoice": null
}
```

**Response 200 envelope GEN-20:**

```json
{
  "error": 0,
  "respuesta": "OK",
  "resultado": {
    "replyText": "Voy a agrupar por cliente y sumar minutos.",
    "actions": [
      { "type": "addGroupByField", "field": "clienteNombre" },
      { "type": "addGroupSum", "field": "duracionMinutos", "summary": "sum" }
    ],
    "pendingChoice": null,
    "configurationRequired": false
  }
}
```

| Caso | Comportamiento |
|------|----------------|
| Sin BYOK | `configurationRequired: true`, `actions: []`, mensaje Preferencias |
| Campo fuera schema | `actions: []` o sin esa acción; `replyText` rechazo (#7) |
| `processCode` ≠ Must | 422 |
| Sin `emission.design` | 403 / código emisiones existente |
| LLM timeout | error envelope; clave i18n; sin apply |

### 5.3 Apply sidecar

`POST {DX_REPORTING}/api/layout/apply`

```json
{
  "reportUrl": "partes.consultaDetallada.principal",
  "processCode": "partes.informes.consultaDetallada",
  "actions": [
    { "type": "addGroupByField", "field": "clienteNombre" }
  ]
}
```

**Response:** `{ "ok": true, "applied": 1, "reportUrl": "..." }` o error estructurado (**no** envelope GEN-20; el FE traduce a i18n).

- Path browser: **`/api/layout/apply`** vía proxy same-origin (**D-11**); añadir en `vite.config.ts` junto a `/DXXRD`.
- Implementación: evolucionar scripts spike `SpikeApplyAct0*` a servicio C# compartido.
- Revalidar `field` contra schema del `processCode` también en sidecar (defensa en profundidad).
- **D4:** al pipeline de Save GEN existente (o hook post-save), limpiar stubs `Dataset`/`componentN` huérfanos.

### 5.4 Errores

| Situación | Capa | Nota |
|-----------|------|------|
| Validación acción | Laravel 422 | envelope; no apply |
| Apply falla bindings | Sidecar 4xx | FE muestra error; no refresh como éxito |
| Save usuario | GEN-15 existente | sin cambio contrato 470x salvo bug |

---

## 6) Cambios Frontend

| Área | Cambio |
|------|--------|
| `ReportDesignerHostPage` | Slot/`renderDesigner`: montar `SmartCapturePanel` debajo del DX cuando proceso confirmado + `emission.design` + **no** native |
| Turno | Cliente HTTP al path D-04; headers plataforma; BYOK gate como TR-010 |
| Post-turn | Si dirty → confirm (D-13); si hay actions → `POST /api/layout/apply` (proxy D-11) → refresh diseñador (remount key `reportUrl`+timestamp) |
| IA DX | Quitar/no llamar `enableReportDesignerAiServices` en Partes (D-08) |
| i18n | `partes.emissionDesign.hint`, `.appliedRefresh`, `.configurationRequired`, `.actionRejected`, `.applyFailed`, … 5 locales |
| UX | Toast/banner: asistente aplicó cambios; diseñador actualizado (spike §5) |
| Testids | RN-B-11 |

---

## 7) Plan de tareas

| ID | Repo | Tipo | Descripción | DoD |
|----|------|------|-------------|-----|
| **T1** | FW | Sidecar | Servicio apply REPX: ACT-01..03 + `setPageOrientation`; endpoint `/api/layout/apply`; tests unit C# | Apply en lab sobre variante; Validate Bindings OK |
| **T2** | FW | Sidecar | D4 cleanup stubs en Save/persist layout; ACT-03 con `XRSummary` si XRE090 | Field List limpio post-Save; sin XRE090 en smoke subtotal |
| **T3** | FW | Docs | Runbook: apply endpoint, auth, D-08 sin OpenAI diseño | Runbook actualizado |
| **T4** | Partes | BE | `DesignAssistantTurn` service + controller ruta D-04; validación schema+whitelist; LLM vía GEN-16 | Feature test turn OK/422/403/BYOK |
| **T5** | Partes | BE | OpenAPI + claves envelope i18n | Spec OpenAPI en repo |
| **T6** | Partes | FE | Montar SC; proxy `/api/layout`; wire turn+apply+refresh+dirty confirm; desactivar AI DX | Vitest montaje/gate; smoke manual |
| **T7** | Partes | FE | i18n 5 locales + testids | Claves presentes |
| **T8** | Partes | QA | Batería §4.4 #1–#8 + L-B checklist → `D-VERIFICACION-TR-015-update-b-YYYY-MM-DD.md` | CA-08 documentado |
| **T9** | Ambos | Regresión | Save/Save As + GetDesignerModel processCode (CA-12) | Sin regresión TR-011 |

**Orden sugerido:** T1 → T2 → T4 → T6 → T5/T7 → T8/T9 → T3.

---

## 8) Tests

| Capa | Qué |
|------|-----|
| Unit BE | Whitelist filter; rechazo campo inventado; processCode guard |
| Feature BE | POST turn 200/403/422; `configurationRequired` |
| Unit/FE | Panel visible solo con diseño; no native; no enable AI DX |
| Sidecar | Apply ACT-01..03 sobre REPX fixture |
| Manual F1 | Gherkin HU + Validate Bindings + Save As |

---

## 9) Docs / ops

- Actualizar `19-ia-diseno-informes.md` (oleada 2 B productiva) si hace falta al cierre F.
- Runbook sidecar (T3).
- Nota: TR-016 / SPEC-016 obsoletos funcionales → comentario en esos md en T8/docs.
- Manual usuario (GEN-99 / write-user-manual) en cierre F1/F si se pide.

---

## 10) Riesgos y mitigaciones

| Riesgo | Mitigación |
|--------|------------|
| Refresh pierde undo DX | Mensaje UX; aceptar en L-B-11; documentar en F1 |
| Apply concurrente dos pestañas | last-write; usuario Save responsable |
| LLM inventa `type` | Drop + replyText (RN-B-02) |
| Esfuerzo apply > estimado | Priorizar ACT-01..03; si `setPageOrientation` falla en lab, gap F1 sin bloquear el resto (sigue Must intentar) |
| Proxy/CORS apply | D-11 same-origin; no CORS :5055 |

---

## 11) Definition of Done (TR)

- [x] T1–T7 DoD código/tests automatizados (Parte D 2026-11-13)
- [ ] T8/T9 batería manual + regresión Save → F1
- [ ] CA HU-015-update 01…12 verificados o justificados en F1
- [x] Prompt-to-Report no accesible en Partes (`AIServicesEnabled(false)`)
- [ ] Sin regresión Save GEN-15 (smoke F1)
- [x] C1 Apto → D implementado → Estado **Pendiente de Revisión**

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-13 | Parte C: TR-015-update-b desde SPEC-update + HU-015-update; decisiones D-01…D-10; Jira PIAPARTES-62. |
| 2026-11-13 | Parte C1: Apto con observaciones; D-11…D-13; Estado **Especificado**. |
| 2026-11-13 | Parte D1: plan implementación (sin código). |
| 2026-11-13 | Parte D: T1–T7 implementados (apply sidecar, D4, turn BE, SC FE, OpenAPI, i18n, runbook §6.2); tests unit/feature/vitest; [D-VERIFICACION](../updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-b-2026-11-13.md); Estado **Pendiente de Revisión**. T8/T9 manual → E/F1. |
