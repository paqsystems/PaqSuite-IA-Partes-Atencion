# Plan de implementación - TR-010

## Alcance entendido

Adoptar **GEN-03 Smart Capture** en Partes **solo** dentro del **Popup** de alta/edición de tarea en **Carga diaria** (web):

- Endpoint host **`POST /api/v1/partes/tareas/asistente/turn`** (contrato v1 + `SmartCaptureTurnGuard`).
- Orquestación LLM (BYOK + timeout chat) → `replyText` + `actions` + `pendingChoice` (sin upsert en el turno).
- FE: `SmartCapturePanel` debajo del form; `applySmartCaptureActions`; `save` → mismo API Guardar TR-004.
- Gates: BYOK **4201**, cerrado→disabled, no cliente, no native.
- Lookups 0/1/N; fecha futura `pendingChoice.kind=confirmFutureDate`; edición parcial; hint `partes.smartCapture.hint`.
- OpenAPI + manual breve + tests.

**No:** SC en grilla/Excel/informes/masivo; redefinir GEN; save en BE del turno; confirm overwrite cliente; timeout SC aparte; menú nuevo; mobile; Eloquent CRUD nuevo de tareas.

## Fuentes leídas

- SPEC: `docs/05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md`
- HU: `docs/03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md`
- TR: `docs/04-tareas/100-SistemaPartes/TR-010-smart-capture-carga-diaria.md` (C1 apto; 4201 cerrado)
- Producto: `docs/02-producto/Sistema-Partes-IA/14-smart-capture.md` (D-SC-01…20)
- GEN: `TR-GEN-03-contrato-turno.md`, `TR-GEN-03-panel-ui.md`; FE `smartCapture/*`; PHP `SmartCaptureTurnGuard`, `SmartCaptureErrorCodes` (4201–4207 en `PaqSuiteEnvelopeCatalog`)
- Host: `CargaDiariaPage.tsx` (Popup + form), `partesTareaApi.ts`, `ChatAssistantTurnsController`, `LlmPreferencesModalHost`, `routes/api.php` (`partes.notCliente`), `paqsuite.llmTimeoutSeconds`
- Ref. comportamiento (no copiar): PedidosWeb `CargaAsistenteTurnController` / TurnService
- `@paqsuite/react-core@2.2.1` (Verdaccio): exports SC OK (`SmartCapturePanel` + helpers)

## Impacto esperado

### Base de datos

| Acción | Detalle |
|--------|---------|
| Crear | **Ninguna** migración Must |
| Reutilizar | BYOK / LLM credentials (TR-008); maestros + `pq_sp_partes_tarea_upsert` solo vía API Guardar FE |
| Sin | Tablas SC; ítem `pq_menus`; param hint |

### Backend

| Crear | Rol |
|-------|-----|
| `PartesTareasAsistenteTurnController` (invokable/thin) | Recibe body, casteos, llama service, envelope |
| `PartesTareaSmartCaptureTurnService` | Guard GEN → BYOK/vision options → LLM → post-proceso catálogos → actions |
| `PartesTareaSmartCaptureCatalogResolver` (o métodos privados) | Lookup cliente/asistente/tipo: 0/1/N |
| `PartesTareaSmartCaptureLlmClient` / adapter | Reutilizar stack provider chat (misma credencial); timeout `paqsuite.llmTimeoutSeconds` |
| Fake LLM (tests) | Feature sin red externa |
| Feature tests | 401; notCliente; 4201; setField 1 match; needsChoice; needsRefine; future date; !supervisor; save sin INSERT |

| Modificar | Cambio |
|-----------|--------|
| `routes/api.php` | Dentro del grupo `partes.profile` + `partes.notCliente`, **antes** de rutas `{id}` si aplica: `POST /partes/tareas/asistente/turn` |
| `OpenApiPathsPartesOperacion.php` (+ tags si hace falta) | Documentar path turno |
| `CapabilityEnvelopeController` / render | Mapear `SmartCaptureDomainException` → envelope (como chat) |

**Flujo service (resumen):**

1. `SmartCaptureTurnGuard::validateGen` con `SmartCaptureGuardOptions` (auth, hasValidLlmCredential, supportsVision, …).
2. Si pendingChoice eco (elección numérica / confirmFutureDate) → resolver diferidos sin re-interpretar todo el mensaje si aplica.
3. Llamar LLM con system prompt Partes (campos, keywords como señales, no substring host).
4. Parse/normalize → resolver catálogos → emitir `setField` / `needsChoice` / `needsRefine` / `save` / `noop`.
5. Fecha > hoy → **no** `setField` fecha; `pendingChoice.kind=confirmFutureDate`.
6. `save` en actions **sin** llamar upsert.
7. `!esSupervisor` → forzar asistente sesión; negar otro.
8. Respuesta `SmartCaptureTurnResultV1` en `resultado`.

**Nota:** `SmartCaptureThreadService::applyActions` del paquete aplica actions en **servidor**; **no** usarlo para persistir tareas en Partes (rompe R-SC-18). Auditoría `SmartCaptureAuditEmitter`: Should; noop writer si GEN-17 no Must.

### Frontend

| Crear | Rol |
|-------|-----|
| `partesSmartCaptureApi.ts` / helper turno | `buildSmartCaptureTurnRequest` + `postSmartCaptureTurn` + timeout/AbortSignal |
| `applyPartesSmartCaptureActions.ts` | Adapters `setField`/`save`/`needsChoice` sobre state del form |
| `buildPartesDraftContext.ts` | Snapshot camelCase TR § draftContext |
| Vitest | apply setField; save llama API; omit edit; enabled gates |

| Modificar | Cambio |
|-----------|--------|
| `CargaDiariaPage.tsx` | Bajo el form del Popup: `SmartCapturePanel`; credentials desde API LLM activa (mismo patrón chat/Shell); `onOpenPreferences` → `LlmPreferencesModalHost` local o lift state; `enabled={!cerrado && !native && !cliente}`; reset thread/pending al cerrar Popup |
| i18n host | `partes.smartCapture.hint` (+ claves panel si `t` las resuelve) |
| Popup | Valorar `height`/`maxHeight` para panel 280px + form (DX auto + scroll) |

| Sin | Montaje en grilla; ruta nueva; allowlist mobile del SC |

**`save` adapter:** reutilizar `handleSave` / `partesTareaApi` create-update actuales → cerrar modal + `load()`.

**Credentials:** listar/activar igual que `ChatAssistantHostPage` / endpoints `/api/v1/llm-credentials*` (TR-008).

### Tests

| Capa | Casos |
|------|-------|
| Feature BE | Guard 4201–4206 smoke; dominio Partes (choice, future date, owner, save sin DB) |
| Unit FE | Gates enabled; apply actions; draftContext edit; save→mock API |
| E2E | Login → Carga → Nueva tarea → `smartCapture.panel` visible (LLM mock/skip red) |
| Manual | LLM real: alta, ambigüedad, fecha futura, edición parcial, grabar |

### Documentación

- `docs/99-manual-usuario/Partes-Atencion.md`: SC en modal ≠ Asistente IA avatar
- Regenerar OpenAPI (`composer openapi` / `l5-swagger:generate`)
- Tras D: opcional nota en d1 folder (este archivo)

### DevOps

- Sin migrate Must
- Verificar `.env` ya tiene `PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS` (TR-008)
- Smoke: BYOK configurada

## Orden de trabajo

1. **T1** Backend: ruta + controller + service esqueleto + Guard + 4201 Feature.
2. **T2** Backend: resolvers catálogo 0/1/N + future date + gate asistente (fake LLM / sin modelo).
3. **T3** Backend: cliente LLM real + prompt + mapeo actions (fake en CI).
4. **T4** Frontend: montar panel + credentials + Preferencias + gates (paralelo post-T1).
5. **T5** Frontend: onSend + apply + save→API + pendingChoice UI.
6. **T6** OpenAPI + i18n hint + manual.
7. **T7** Tests Feature/Vitest/E2E smoke + ajustes.

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| LLM no determinista en CI | Fake/stub obligatorio en Feature; E2E sin red |
| Popup angosto + panel | `dialogMaxHeightPx`, scroll, ancho Popup ≥560 |
| Confundir ThreadService BE con persistencia | No aplicar `save` en servidor |
| Lookup fuzzy ambiguo | Criterio MVP: contains case-insensitive en code+nombre; tope 10; documentar en código |
| Credenciales FE duplicadas vs chat | Extraer hook compartido si ya existe; si no, copiar mínimo patrón ChatAssistantHostPage sin refactor amplio |
| `SmartCaptureDomainException` no mapeada | Extender `CapabilityEnvelopeController` como chat |

## Tests a ejecutar

- `backend`: Feature turno SC (+ unit resolver si aplica)
- `frontend`: Vitest helpers SC carga
- `frontend`: Playwright smoke panel en modal
- Manual: 1 flujo E2E con LLM demo

## Dudas / bloqueos

| # | Tema | Estado |
|---|------|--------|
| 1 | ¿Hook compartido credentials LLM o inline en CargaDiaria? | **D1:** inline mínimo / extraer solo si hay duplicación trivial; sin refactor Shell |
| 2 | ¿Kinds `chooseCliente` exactos en paquete? | **D1:** fijar strings Partes en service; alinear si GEN trae constantes |
| 3 | Auditoría SC Must | **No** Must Partes (C1); noop OK |

Sin bloqueos humanos para arrancar D.

## Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**
- Path, 4201, FE-save, edición parcial, sin confirm overwrite: respetados
- No se implementa SC en grilla ni Excel ni mobile

## Artefacto

Plan guardado en: `docs/04-tareas/100-SistemaPartes/d1/D1-TR-010-smart-capture.md`

---

**Listo para Parte D** cuando autorices: «Ejecutá la TR-010» / «Implementá».
