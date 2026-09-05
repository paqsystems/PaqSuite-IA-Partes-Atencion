# TR-010 – Smart Capture en modal de Carga diaria — adopción GEN-03

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-010-smart-capture-carga-diaria](../../03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md) |
| **SPEC relacionada** | [SPEC-010-smart-capture-carga-diaria](../../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Asistente / supervisor (cliente denegado) |
| **Dependencias** | [TR-004](./TR-004-operacion-carga-diaria.md) (modal + upsert tarea); [TR-002](./TR-002-identidad-funcional-y-acceso.md); [TR-003](./TR-003-maestros-y-catalogos.md); [TR-008](./TR-008-asistente-ia-chat-documental.md) (BYOK + `PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS`); `@paqsuite/react-core` (`SmartCapturePanel`, `postSmartCaptureTurn`, `applySmartCaptureActions`, `buildSmartCaptureTurnRequest`, `useSmartCapturePendingChoice`); GEN-03 / TR-GEN-03-* |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Pendiente |
| **Revisión C1** | Apto con observaciones (ver §11) |
| **Última actualización** | 2026-08-03 |

**Origen:** [HU-010](../../03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md)  
**Referencia SPEC:** [SPEC-010](../../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md)  
**Producto:** [14-smart-capture.md](../../02-producto/Sistema-Partes-IA/14-smart-capture.md) (D-SC-01…20)

**Referencia GEN (checkout Framework):**

- `docs/02-producto/03-asistente-inteligente-smart-capture.md`
- `docs/00-Conceptualizacion/02-componentes/smart-capture/` + patrón asistente operativo embebido
- TR-GEN-03-contrato-turno / UI (si existen bajo `docs/04-tareas/001-Generalidades/`)
- Paquete FE: `packages/js/react-core/src/features/smartCapture/*`
- Referencia comportamiento (no copiar): PedidosWeb `POST /api/v1/pedidos/carga/asistente/turn`

---

## 1) HU refinada (resumen)

### Narrativa

Como asistente o supervisor quiero usar Smart Capture en el modal de alta/edición de Carga diaria para proponer datos al formulario (texto/audio/imagen) y grabar con la misma validación que Guardar.

### In scope

- Montar `SmartCapturePanel` **dentro del Popup** de tarea (`CargaDiariaPage`), debajo del form.
- Endpoint host **`POST /api/v1/partes/tareas/asistente/turn`** (contrato GEN v1).
- Orquestación LLM + resolución catálogos + emisión `actions` / `pendingChoice`.
- FE: `applySmartCaptureActions` → draft; action `save` → mismo POST/PUT que Guardar (TR-004).
- Gates: BYOK, cerrado→disabled, no cliente, no native.
- Timeout LLM = **`config('paqsuite.llmTimeoutSeconds')`** / `PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS` (TR-008).
- Hint i18n Partes; OpenAPI; tests; manual breve.

### Out of scope

- SC en grilla/Excel/informes/masivo; redefinir GEN-03; chat documental mutando dominio; mobile; matcher substring; save en BE del turno; confirm overwrite cliente; timeout SC aparte.

### Decisiones TR (cierran preguntas HU § abiertas)

| Tema | Decisión |
|------|----------|
| Path turno | **`POST /api/v1/partes/tareas/asistente/turn`** (Must) |
| Authz | Mismos middleware que carga operativa: `auth:sanctum` + `paqsuite.instalacion` + `partes.profile` + **`partes.notCliente`** |
| Timeout | Reutilizar **`PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS`** / `paqsuite.llmTimeoutSeconds` (default 60) |
| Hint i18n | Clave **`partes.smartCapture.hint`** (texto fijo Partes en `es`/`en`/… del host) |
| testids | Panel GEN `smartCapture.*`; host adicional `partesCargaSmartCapture` en contenedor si hace falta |
| Persistencia `save` | Solo FE → APIs existentes `POST/PUT /api/v1/partes/tareas` (o helpers `PartesTareaOperations` del FE actuales) |
| `contractVersion` | **1** |

---

## 2) Criterios de aceptación (AC)

Mapear HU CA-01…16:

| AC | Verificación técnica |
|----|----------------------|
| CA-01 | Popup alta/edición monta `SmartCapturePanel` debajo del form; no en grilla |
| CA-02 | `enabled={false}` (o no usable) si `form.cerrado` / row cerrada |
| CA-03 | Sin credencial activa: panel GEN `configurationRequired` / Preferencias; no POST turno |
| CA-04 | Texto + dictado Web Speech + imágenes si `supportsVision` |
| CA-05 | Turno mock/feature aplica ≥1 `setField` al state del form |
| CA-06 | Alta: FE inicializa defaults al abrir create (TR-004); BE no resetea en edición; CA-06 HU cubierto |
| CA-07 | `needsChoice` → hilo numerado; `useSmartCapturePendingChoice`; eco en request |
| CA-08 | Fecha futura → `pendingChoice.kind=confirmFutureDate` **antes** de `setField` fecha |
| CA-09 | Action `save` (no regex en FE sobre mensaje); handler FE llama save existente |
| CA-10 | Save con draft incompleto: validación FE/API como Guardar; sin persistir |
| CA-11 | `setField` cliente overwrite sin diálogo extra |
| CA-12 | Edición: `draftContext` incluye valores actuales; omitidos no generan reset |
| CA-13 | BE ignora/niega cambio de asistente si `!esSupervisor` |
| CA-14 | Timeout turno = `paqsuite.llmTimeoutSeconds` (mismo env chat); FE `AbortSignal` acorde si aplica |
| CA-15 | `isNativeApp()` → no montar panel |
| CA-16 | Cliente: no llega al modal de carga; API turno → denegación `partes.notCliente` |

---

## 3) Reglas de negocio / implementación

| ID | Implementación host |
|----|---------------------|
| RN-TR-01 | Controller thin `PartesTareasAsistenteTurnController` → service `PartesTareaSmartCaptureTurnService` (o nombre camelCase equivalente). |
| RN-TR-02 | Request body = `SmartCaptureTurnRequestV1` (`contractVersion`, `message`, `modality`, `credentialId`, `draftContext`, `pendingChoice`, `images`). Validar con **`SmartCaptureTurnGuard`** de `laravel-core` si está disponible (códigos **4202–4207** GEN). |
| RN-TR-03 | Response `resultado` = `SmartCaptureTurnResultV1` (`replyText`, `actions[]`, `pendingChoice`, `configurationRequired`) dentro del envelope estándar. |
| RN-TR-04 | Sin LLM / credencial inválida → catálogo GEN-03: **`4201`** + HTTP **409** + `resultado.configurationRequired: true` (`smartCapture.configurationRequired`). **No** usar **4301** (eso es chat GEN-21 / TR-008). |
| RN-TR-05 | LLM: reutilizar stack BYOK / provider del chat (misma credencial `credentialId`); timeout `paqsuite.llmTimeoutSeconds`. |
| RN-TR-06 | Prompt sistema Partes: campos tarea, keywords grabación como **señales de intención** (no reglas de substring en host), defaults, instrucción de emitir `actions` tipadas. |
| RN-TR-07 | Post-LLM (o tools): resolver cliente/asistente/tipo por código **y** descripción (maestros usables SPEC-003); 0→`needsRefine`; 1→`setField`; >1→`needsChoice` (máx. ~10 opciones). |
| RN-TR-08 | Fecha > hoy (fecha servidor/instalación): no emitir `setField` fecha hasta confirmación; emitir `pendingChoice` con **`kind: "confirmFutureDate"`** + `options`/deferred según GEN + reply; tras mensaje de aceptación (`sí`/`confirmo`, case-insensitive) en el turno siguiente → `setField` fecha. **No** usar un `action` separado `confirmFutureDate` como mutador. |
| RN-TR-09 | Intención grabar → action `{ action: "save", payload: {}, resultado: "ok" }` **sin** ejecutar upsert en BE del turno. |
| RN-TR-10 | `!esSupervisor`: forzar `asistenteId` = sesión en draft efectivo; rechazar intento de otro asistente (reply + sin `setField` asistente). |
| RN-TR-11 | **Defaults de alta:** el FE ya inicializa el form de create (tipo default, flags false, asistente sesión) como hoy TR-004. El BE **no** pisa campos del draft con defaults salvo que el draft traiga el campo **null/ausente** y el modelo proponga valor; en edición, omitir campo = no emitir `setField` de ese campo. |
| RN-TR-12 | FE `onSend`: `buildSmartCaptureTurnRequest` + `draftContext` snapshot + `postSmartCaptureTurn(url)`; luego `applySmartCaptureActions`. |
| RN-TR-13 | Adapter `save`: validar form (mismas reglas `CargaDiariaPage` / TR-004) → `store`/`update` existentes → cerrar modal + `load()` filtros. |
| RN-TR-14 | No montar panel si `isNativeApp()` o identidad cliente. |
| RN-TR-15 | OpenAPI: path en `OpenApiPathsPartesOperacion` (o tag **Partes Tareas** / Smart Capture). |
| RN-TR-16 | Manual: párrafo en `docs/99-manual-usuario/Partes-Atencion.md` — SC en modal ≠ Asistente IA avatar. |
| RN-TR-17 | Acceso datos maestros/tareas vía SP / ops existentes (MUST BASE); sin Eloquent CRUD nuevo de dominio. |

### Actions Must (nombres estables)

| `action` | Efecto FE |
|----------|-----------|
| `setField` | `payload.field` (string) + `payload.value`. **Campos canónicos Must:** `clienteId`, `asistenteId`, `tipoTareaId`, `fecha` (ISO `yyyy-MM-dd`), `duracionMinutos`, `observacion`, `sinCargo`, `presencial`. Códigos/nombres son solo para lookup BE; el FE aplica ids/valores tipados. |
| `needsChoice` | UI choice; set `pendingChoice` (eco en siguiente request) |
| `needsRefine` | Solo `replyText` / pending; sin mutar draft |
| `save` | Disparar Guardar FE |
| `noop` / `help` | Sin mutación |

`pendingChoice.kind` Must para fecha futura: **`confirmFutureDate`**. Otros kinds de catálogo (cliente/tipo/asistente): p. ej. `chooseCliente`, `chooseTipoTarea`, `chooseAsistente` (nombres fijos en D1; no inventar por turno).

### `draftContext` Partes (Must shape)

```json
{
  "mode": "create|edit",
  "id": null,
  "cerrado": false,
  "clienteId": null,
  "clienteCode": null,
  "clienteNombre": null,
  "asistenteId": null,
  "asistenteCode": null,
  "tipoTareaId": null,
  "tipoTareaCode": null,
  "fecha": null,
  "duracionMinutos": null,
  "observacion": "",
  "sinCargo": false,
  "presencial": false,
  "esSupervisor": false,
  "rowVersion": null
}
```

Campos camelCase. FE envía snapshot actual del form; BE puede enriquecer/normalizar.

---

## 4) Impacto en datos

| Pieza | Detalle |
|-------|---------|
| Tablas nuevas | **Ninguna** Must |
| SP | Reutilizar maestros lookup + `pq_sp_partes_tarea_upsert` solo vía API Guardar FE (no desde turno) |
| LLM / BYOK | GEN-16 ya en host (TR-008): tabla + **SP** `pq_sp_llm_*` desplegados por entorno — ver Framework [`adopcion-gen-16-byok.md`](../../../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-gen-16-byok.md) |
| Menú | Sin ítem nuevo |
| Params | Sin param hint; timeout ya existente chat |
| Rollback | Quitar ruta + panel; sin migración down especial |

---

## 5) Contratos de API

| Método | Path | Auth |
|--------|------|------|
| POST | `/api/v1/partes/tareas/asistente/turn` | Bearer + `X-Paq-Cliente` + `partes.profile` + `partes.notCliente` |

**Request (JSON):** contrato GEN v1 (§3 RN-TR-02).  
**Response:** envelope `error` / `respuesta` / `resultado` con `SmartCaptureTurnResultV1`.

Errores Must:

| Caso | Comportamiento |
|------|----------------|
| No autenticado | 401 estándar |
| Cliente / sin perfil carga | Denegación middleware `partes.notCliente` (mismo patrón TR-004) |
| Sin LLM | **4201** + HTTP **409** + `resultado.configurationRequired: true` (GEN-03 / TR-GEN-03-contrato-turno) |
| Timeout LLM | Error envelope i18n; sin actions de save |

APIs de persistencia (**sin cambio de contrato**):

| Método | Path | Uso |
|--------|------|-----|
| POST | `/api/v1/partes/tareas` | Alta tras `save` |
| PUT | `/api/v1/partes/tareas/{id}` | Edición tras `save` |

---

## 6) Frontend

| Pieza | Detalle |
|-------|---------|
| Host | `frontend/src/features/partes/carga/CargaDiariaPage.tsx` (Popup) |
| Panel | `SmartCapturePanel` `@paqsuite/react-core` |
| Props | `enabled={!cerrado && !isNativeApp() && !isCliente}`; `hintKey`/`hintText` vía i18n `partes.smartCapture.hint`; credentials = mismas que chat/BYOK host; `onOpenPreferences` → modal LLM existente (TR-008) |
| Turno | Helper host: armar request + `postSmartCaptureTurn('/api/v1/partes/tareas/asistente/turn')` |
| Actions | `applySmartCaptureActions` + adapters setField/save/pendingChoice |
| State | Thread + `pendingChoice` en estado del modal (reset al cerrar Popup) |
| Mobile / cliente | No montar |
| i18n | `partes.smartCapture.*` (+ claves GEN del panel si aplica `t`) |

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD | Deps | Est. |
|----|------|-------------|-----|------|------|
| T1 | Backend | Ruta + controller + service turno (BYOK, timeout chat, envelope) | Feature: 409 sin LLM; 200 shape resultado | — | L |
| T2 | Backend | Resolución catálogos 0/1/N + fecha futura pending + defaults alta + gate asistente | Feature unit/service cases | T1 | L |
| T3 | Backend | Prompt/orquestación LLM + mapeo a actions (`setField`, `save`, choices) | Feature con fake LLM | T2 | L |
| T4 | Frontend | Montar panel en Popup + credentials/preferences + gates | CA-01…03, 15–16 | T1 | M |
| T5 | Frontend | `onSend` + apply actions + `save`→API Guardar + pendingChoice | CA-05…13 | T3,T4 | L |
| T6 | Docs | OpenAPI path + i18n hint + manual usuario | Spec OpenAPI genera; manual | T4 | S |
| T7 | Tests | Feature BE + Vitest adapters + E2E smoke panel en modal (sin LLM real si fake) | Suite verde | T2–T5 | M |

**Orden:** T1 → T2 → T3 → T4 → T5 → T6 → T7 (T4 puede avanzar en paralelo tras T1).

---

## 8) Estrategia de tests

| Capa | Casos |
|------|-------|
| Feature BE | Sin auth; cliente denegado; sin LLM → **4201**/409; 1 match setField; N matches needsChoice; 0 needsRefine; fecha futura pending; !supervisor no cambia asistente; save action **sin** insert DB en el turno |
| Unit FE | `enabled` false si cerrado/native; apply `setField`; `save` llama persistencia; omitir campo en edición no resetea |
| E2E | Login asistente → Carga → Nueva tarea → visible `smartCapture.panel` / hint (smoke; LLM mockeado o skip red) |
| Manual | Con LLM real: alta por voz/texto; ambigüedad; fecha futura; edición parcial; grabar |

---

## 9) Riesgos y edge cases

| Riesgo | Mitigación |
|--------|------------|
| Paquete SC incompleto en versión npm del host | Verificar exports `@paqsuite/react-core@2.2.1` (Verdaccio); bump pin si falta export |
| LLM inestable | Validar siempre en host post-modelo; no inventar ids |
| Popup altura / panel 280px | `dialogMaxHeightPx` ajustable; scroll GEN |
| Confusión chat vs SC | Manual + copy hint |
| PedidosWeb como copia | Solo referencia de comportamiento; contrato = GEN types FE |

---

## 10) Checklist DoD

- [ ] CA-01…16 HU
- [ ] `POST /api/v1/partes/tareas/asistente/turn` + OpenAPI
- [ ] Panel solo en modal carga; gates cerrado/cliente/native/BYOK
- [ ] Lookups 0/1/N; fecha futura al draft; edición parcial
- [ ] `save` → FE → API TR-004 (sin upsert en turno)
- [ ] Timeout = chat; hint `partes.smartCapture.hint`
- [ ] Manual + tests Feature/Vitest/E2E smoke
- [ ] Sin menú nuevo; sin mobile

---

## 11) Revisión C1 (ambigüedad)

**Estado:** Apto con observaciones  
**Puede pasar a D1/D:** Sí (tras leer observaciones cerradas abajo)

### Críticas (cerradas en esta TR)

- Sin LLM: Must **`4201` + 409** (TR-GEN-03-contrato-turno), **no** 4301 del chat (§3 RN-TR-04, §5).
- Fecha futura: solo `pendingChoice.kind = confirmFutureDate`; no action mutadora homónima (§3 RN-TR-08).
- `setField.payload.field`: lista canónica ids/valores tipados (§3 Actions); códigos solo en lookup BE.
- Defaults alta: FE al abrir create; BE no pisa edición por omisión (§3 RN-TR-11 / CA-06).

### Menores

- Nombres exactos `chooseCliente` / `chooseTipoTarea` / `chooseAsistente`: fijar en D1 si el paquete GEN ya trae kinds; no bloquear.
- Texto concreto de `partes.smartCapture.hint`: copy en i18n en T6 (contenido libre mientras la clave exista).
- Auditoría GEN-03 / bitácora `17`: Should; no Must Partes en esta TR salvo que el paquete lo exija al adoptar.
- Verificar en D1 que la versión de `@paqsuite/react-core` del host (`2.2.1` vía Verdaccio) exporte `SmartCapturePanel` + helpers.

### Contradicciones TR ↔ HU ↔ SPEC

- Ninguna de alcance. Path orientativo SPEC → fijado. Persistencia save y edición parcial alineadas A1.

### Supuestos

- TR-GEN-03-* y exports FE disponibles vía Framework path.
- Middleware `partes.notCliente` / `partes.profile` ya operativos (TR-004).

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-03 | Parte C: TR-010 desde SPEC-010 + HU-010 (post A1). |
| 2026-08-03 | C1: 4201 (no 4301); pendingChoice fecha; fields canónicos; defaults FE; veredicto Apto con observaciones. |
