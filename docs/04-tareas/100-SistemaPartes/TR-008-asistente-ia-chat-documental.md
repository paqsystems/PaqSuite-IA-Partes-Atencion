# TR-008 – Asistente IA (chat documental) — adopción host en Partes

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-008-asistente-ia-chat-documental](../../03-historias-usuario/100-SistemaPartes/HU-008-asistente-ia-chat-documental.md) |
| **SPEC relacionada** | [SPEC-008-asistente-ia-chat-documental](../../05-open-spec/100-SistemaPartes/SPEC-008-asistente-ia-chat-documental.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Asistente / supervisor / cliente (sesión Partes usable) |
| **Dependencias** | [TR-002](./TR-002-identidad-funcional-y-acceso.md) (shell post-login); paquetes Framework `@paqsuite/react-core@2.2.1` (Verdaccio) + `paqsuite/laravel-core@^1.3.3` (Satis, corpus GEN); GEN TR-GEN-21-chat-ui, TR-GEN-21-contrato-turno, TR-GEN-16-*, TR-GEN-08; [TR-007](./TR-007-mobile-capacitor.md) (policy mobile) |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-08-01 |
| **Revisión C1** | Apto con observaciones (ver §11) |

**Origen:** [HU-008](../../03-historias-usuario/100-SistemaPartes/HU-008-asistente-ia-chat-documental.md)  
**Referencia SPEC:** [SPEC-008](../../05-open-spec/100-SistemaPartes/SPEC-008-asistente-ia-chat-documental.md)  
**Producto:** [12-asistente-ia-ayuda-y-chat-documental.md](../../02-producto/Sistema-Partes-IA/12-asistente-ia-ayuda-y-chat-documental.md) (D-AI-01…04)

**Referencia GEN (checkout Framework):**

- `PaqSuite-IA-FRAMEWORK/docs/04-tareas/001-Generalidades/TR-GEN-21-chat-ui.md`
- `PaqSuite-IA-FRAMEWORK/docs/04-tareas/001-Generalidades/TR-GEN-21-contrato-turno.md`
- Smoke host de referencia: `apps/smoke-frontend` + `apps/smoke-backend` (routes `llm-credentials`, `chat-assistant/turns`)

---

## 1) HU refinada (resumen)

### Narrativa

Como usuario autenticado de Partes quiero abrir el **Asistente IA** desde el menú avatar para consultar ayuda del módulo y generalidades Framework sin operar el negocio.

### In scope

- Habilitar ítem avatar **Asistente IA** (`showChat: true`) y **sin** ayuda URL (`showHelp: false`).
- Ruta FE **`/chat-assistant`**: montar `ChatAssistantPage` de `@paqsuite/react-core`.
- Preferencias BYOK: modal desde el chat (`onOpenPreferences`); también desde avatar (`showPreferences: true`) abriendo el **mismo** modal.
- Adoptar en el host Partes el **plumbing GEN-16** (tabla + SP + rutas `/api/v1/llm-credentials*`) y el **turno GEN-21** (`POST /api/v1/chat-assistant/turns` wrapping `ChatAssistantTurnService`).
- Implementar `ChatCorpusProvider` con **manifest** Partes (`docs/99-manual-usuario`) + GEN (paths Framework, sin copiar archivos).
- Bienvenida i18n Partes; timeout LLM de producto; allowlist mobile `/chat-assistant`.
- Manual usuario: cómo abrir el asistente.

### Out of scope

- Ayuda externa por URL; Smart Capture; mutación de dominio vía chat; RAG Must; historial servidor; dictado STT; ítem menú lateral; redefinir contrato GEN del turno/UI.

### Discrepancias SPEC/HU → API real (MUST en D)

| Texto SPEC/HU | API / prop real Framework |
|---------------|---------------------------|
| `showChatAssistant` | **`showChat`** (`UserAvatarMenu`) |
| `showExternalHelp` | **`showHelp`** (dejar `false` / no montar) |
| Callback chat | **`onChat`** → `navigate('/chat-assistant')` |

No inventar aliases en el host; alinear copy documental en unificación / Parte I si se desea.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | Avatar muestra «Asistente IA» (`showChat`); no muestra ayuda URL (`showHelp` false) |
| AC-02 | `onChat` → `/chat-assistant` misma ventana; mobile in-app (allowlist) |
| AC-03 | Sin LLM válido: empty GEN + CTA Preferencias; envío bloqueado en UI |
| AC-04 | Con LLM: bienvenida Partes + TextArea ≤ 2000 + envío |
| AC-05 | Turno OK → `reply` en locale de app (`users.locale`, fallback `es`) |
| AC-06 | Respuesta **sin** `actions`; no muta tareas/maestros |
| AC-07 | POST sin LLM / credencial inválida → **4301** + HTTP **409** + `configurationRequired` |
| AC-08 | Manifest incluye entradas Partes **y** GEN; smoke documental o citations |
| AC-09 | `Partes-Atencion.md` (y/o índice) explica cómo abrir el asistente |
| AC-10 | i18n `chatAssistant.*` + `partes.chatAssistant.welcome`; testids GEN `chatAssistant.*` |

### Escenarios Gherkin

```gherkin
Feature: Asistente IA documental en Partes

  Scenario: Abrir chat desde el avatar
    Given estoy autenticado con perfil Partes usable
    When abro el menú del avatar
    Then veo la opción "Asistente IA"
    And no veo ayuda externa por URL
    When elijo "Asistente IA"
    Then navego a "/chat-assistant" en la misma ventana

  Scenario: Sin LLM no puedo consultar
    Given estoy en el chat documental sin configuración LLM válida
    Then veo un empty con acceso a Preferencias
    And el envío de consulta está bloqueado

  Scenario: Preferencias desde el chat
    Given estoy en "/chat-assistant" sin LLM
    When activo el CTA Preferencias
    Then se abre el modal BYOK (LlmPreferencesPanel)
    And no navego fuera del shell por window.open

  Scenario: Consulta orientativa con LLM
    Given tengo una configuración LLM válida habilitada
    And estoy en "/chat-assistant"
    When envío una pregunta sobre cómo cargar un parte
    Then recibo una respuesta orientativa en el idioma de la aplicación
    And el body no incluye "actions"
    And no se crea ni modifica ninguna tarea

  Scenario: Mobile in-app
    Given la app Capacitor native
    When abro Asistente IA
    Then la ruta "/chat-assistant" está permitida por partesMobilePolicy
    And la navegación permanece in-app
```

---

## 3) Reglas de negocio / implementación

R-AI-01…20 del SPEC/HU. Mapeo técnico:

| ID | Implementación host |
|----|---------------------|
| RN-TR-01 | `ShellPage`: `showChat`, `onChat={() => navigate('/chat-assistant')}`, `showHelp={false}`, `showPreferences`, `onPreferences` → abrir modal BYOK, labels i18n `avatar.chat` / `avatar.preferences`. |
| RN-TR-02 | `AppRouter`: ruta autenticada `/chat-assistant` → página host que monta `ChatAssistantPage` con `welcomeText` o `t('partes.chatAssistant.welcome')`, `turnUrl='/api/v1/chat-assistant/turns'`, `onOpenPreferences` = mismo modal. |
| RN-TR-03 | Modal BYOK: `LlmPreferencesPanel` + `createLlmPreferencesModalController` (o Popup DX equivalente). **Must** desde chat; **Must** desde avatar Preferencias (mismo componente). |
| RN-TR-04 | Backend: registrar rutas GEN-16 + GEN-21 bajo `auth:sanctum` + `paqsuite.instalacion` (patrón smoke `routes/capabilities.php`). Controllers thin: delegan a `LlmCredentialService` / `ChatAssistantTurnService` del paquete. |
| RN-TR-05 | Acceso datos LLM: **MUST SP** `pq_sp_llm_*` del paquete Framework (no Eloquent CRUD en APIs de producto). Migración host crea `pq_llm_credentials` + columna `users.active_llm_credential_id`. |
| RN-TR-06 | Binding `ChatCorpusProvider` → implementación Partes (`ManifestChatCorpusProvider`). Sin binding → corpus vacío permitido (GEN); Partes **Must** registrar el binding. |
| RN-TR-07 | Cliente LLM: usar implementación default del paquete (`ProviderRoutingLlmChatCompletionClient` o equivalente exportado). Timeout HTTP hacia proveedor = **`config('paqsuite.chatAssistant.llmTimeoutSeconds')`** default **60** (env `PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS`). |
| RN-TR-08 | Manifest corpus: archivo de config PHP o JSON versionado en el host (p. ej. `backend/config/chat_assistant_corpus.php`) listando paths relativos Partes bajo `base_path('../docs/99-manual-usuario')` y paths GEN bajo env `PAQSUITE_GEN_DOCS_ROOT` (path instalado; **sin** asumir sibling monorepo Framework). **Prohibido** copiar markdown GEN al repo Partes. |
| RN-TR-09 | Resolución corpus v1 (**sin RAG**): leer markdown de las entradas del manifest; filtrar por coincidencia simple de tokens del `message` (case-insensitive) y/o devolver un subconjunto acotado (tope chars documentado en código, p. ej. 24–32 KiB texto). Devolver `CorpusChunk` (`title`, `content`, `locator?`). Si GEN root ausente: solo Partes + log warning; no 500. |
| RN-TR-10 | Mobile: agregar **`/chat-assistant`** a `partesMobileAllowlist`; tests Vitest policy. Chat full-screen in-app (mismo componente). |
| RN-TR-11 | No SP de negocio Partes para el turno (plumbing IA/auth — excepción framework listada). |
| RN-TR-12 | Envelope errores turno: **4301–4306** según TR-GEN-21; malformado → **1002**; sin auth → **3001**. |

---

## 4) Impacto en datos

| Pieza | Detalle |
|-------|---------|
| Tabla nueva | `pq_llm_credentials` (columnas alineadas a migración smoke GEN-16: `user_id`, `nombre`, `proveedor`, `modelo`, `secreto_cifrado`, `base_url`, `supports_vision`, `enabled`, timestamps) |
| Columna | `users.active_llm_credential_id` nullable (FK lógica; cleanup en SP delete) |
| SP | Desplegar scripts canónicos Framework `packages/php/laravel-core/database/sp/pq_sp_llm_*.sql` (copia host: `backend/database/sp/`) en **cada** entorno install/update. Checklist Must Framework: [`adopcion-gen-16-byok.md`](../../../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-gen-16-byok.md) — **migrate + 7 SP**; sin SP, Preferencias falla al guardar la key (no confundir con key inválida). |
| Seed | No obligatorio para prod; tests: usuario Partes + 0/1 credencial fixture |
| Menú `pq_menus` | **Sin** ítem nuevo (entrada solo avatar) |
| Corpus | Solo archivos markdown existentes; sin tablas de embeddings |

Rollback: drop columna + drop tabla; revert routes/bindings.

---

## 5) Contratos de API

Base `/api/v1` — Bearer Sanctum + `X-Paq-Cliente`. Sin permiso de menú lateral específico.

### 5.1 BYOK (adopción GEN-16)

| Método | Path | Notas |
|--------|------|-------|
| GET | `/llm-credentials` | `{ items, activeLlmCredentialId, providers }` — sin secretos |
| POST | `/llm-credentials` | Alta; secreto cifrado at-rest |
| PATCH | `/llm-credentials/{id}` | Patch; secreto opcional |
| DELETE | `/llm-credentials/{id}` | Limpia active si coincidía |
| GET/PUT | `/llm-credentials/active` | Preferencia activa |

Códigos envelope GEN **4101–4104** (catálogo paquete). Shape DTO: sin campo secreto; `hasSecret` boolean.

### 5.2 Turno chat (adopción GEN-21)

`POST /api/v1/chat-assistant/turns`

**Request (conceptual):**

```json
{
  "contractVersion": 1,
  "credentialId": 1,
  "message": "¿Cómo cargo un parte?",
  "images": []
}
```

**Success `resultado`:** `{ "reply": "...", "citations": [{ "title": "...", "locator": "..." }], "configurationRequired": false }` — **sin** `actions`.

**Errores capacidad:**

| Código | HTTP | `respuesta` |
|-------:|-----:|-------------|
| 4301 | 409 | `chatAssistant.configurationRequired` |
| 4302 | 422 | `chatAssistant.unsupportedVersion` |
| 4303 | 422 | `chatAssistant.messageTooLong` |
| 4304 | 422 | `chatAssistant.imagesLimitExceeded` |
| 4305 | 422 | `chatAssistant.imageTooLarge` |
| 4306 | 422 | `chatAssistant.visionNotSupported` |

### 5.3 OpenAPI

Si el host publica OpenAPI: documentar adopción de paths anteriores (referencia GEN; no inventar shape paralelo).

---

## 6) Frontend

| Pieza | Detalle |
|-------|---------|
| Shell | `frontend/src/features/auth/ShellPage.tsx` — flags avatar + modal Preferencias |
| Página chat | `frontend/src/features/chatAssistant/ChatAssistantHostPage.tsx` (nombre orientativo) montando `ChatAssistantPage` |
| Router | `AppRouter.tsx` — ruta bajo `AuthenticatedShell` + `RequirePartesMobilePolicy` |
| Modal BYOK | Reutilizar exports `LlmPreferencesPanel` / `createLlmPreferencesModalController` |
| i18n | Claves GEN `chatAssistant.*` (catálogo paquete o copia host mínima) + **`partes.chatAssistant.welcome`** |
| testids | GEN: `chatAssistant.page`, `chatAssistant.message`, `chatAssistant.send`, `chatAssistant.emptyState.*`, `chatAssistant.preferences`; avatar: labels existentes + ítem chat del DropDown |
| Mobile | `partesMobilePolicy.ts` + test |

Props mínimas del host:

```ts
<ChatAssistantPage
  turnUrl="/api/v1/chat-assistant/turns"
  welcomeText={t('partes.chatAssistant.welcome')}
  onOpenPreferences={openLlmPreferencesModal}
  t={t}
/>
```

Modo selección LLM: **interno** (default del componente; `useLlmCredentialSelection`).

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD | Deps | Est. |
|----|------|-------------|-----|------|------|
| T1 | DB | Migración `pq_llm_credentials` + `users.active_llm_credential_id`; documentar deploy SP `pq_sp_llm_*` | migrate up/down OK | — | M |
| T2 | Backend | Repositorio SP + controllers `LlmCredentials*` + binding cipher/service | Feature tests CRUD sin secreto en JSON | T1 | L |
| T3 | Backend | `ManifestChatCorpusProvider` + config manifest Partes+GEN + binding DI | Unit: chunks / GEN ausente | — | M |
| T4 | Backend | Controller `ChatAssistantTurns` → `ChatAssistantTurnService` + timeout config | Feature: 4301 sin LLM; 200 con mock client | T2, T3 | L |
| T5 | Frontend | Modal BYOK + `showPreferences` en avatar | Abrir/cerrar modal; listar/crear credencial smoke | T2 | M |
| T6 | Frontend | Ruta `/chat-assistant` + `ChatAssistantPage` + i18n bienvenida | Empty + envío UI | T5 | M |
| T7 | Frontend | `showChat` / `onChat` / `showHelp=false` | CA-01/02 | T6 | S |
| T8 | Mobile | Allowlist `/chat-assistant` + tests policy | Vitest verde | T6 | S |
| T9 | Docs | Manual `Partes-Atencion.md` + `.env.example` timeout/GEN root; OpenAPI si aplica | CA-09 | T6 | S |
| T10 | Tests | Unit corpus; Feature turns/credentials; E2E Playwright abrir chat + empty sin LLM | §8 | T4–T8 | M |

**Orden sugerido:** T1 → T2 → T3 → T4 → T5 → T6 → T7 → T8 → T9 → T10.

---

## 8) Estrategia de tests

| Capa | Casos |
|------|-------|
| Unit FE | `partesMobilePolicy` incluye `/chat-assistant`; (opcional) helper i18n welcome |
| Unit BE | `ManifestChatCorpusProvider`: match mensaje; GEN root missing → solo Partes; tope tamaño |
| Feature BE | `POST /chat-assistant/turns` → 4301 sin credencial; malformado 1002; success con `LlmChatCompletionClient` fake (no llamar red real en CI); body sin `actions`; CRUD llm-credentials sin secreto |
| E2E | Login → avatar → Asistente IA → URL `/chat-assistant` → empty Preferencias visible sin LLM |
| Manual | Con credencial real (opcional local): 1 pregunta carga diaria + 1 pregunta shell GEN |

No Must: E2E con proveedor LLM de pago en CI.

---

## 9) Riesgos y edge cases

| Riesgo | Mitigación |
|--------|------------|
| HTTP controller no está en `laravel-core` (solo service) | Host thin controller (smoke); no reimplementar validaciones |
| Path GEN docs ausente en servidor | Env documentada; fallback Partes-only + warning |
| Usuario confunde con Smart Capture | Copy bienvenida Partes + fuera de alcance |
| Preferencias stub Partes incompleto | Esta TR **incluye** adopción GEN-16 mínima necesaria para CA BYOK |
| Timeout lento | Default 60 s + loading DX del componente GEN |
| Eloquent en smoke GEN | Partes **Must** SP en producto (BASE 74); no copiar Eloquent del smoke |

---

## 10) Checklist DoD

- [x] AC-01…10 *(AC-05 turno proveedor real = observación F1; Feature con fake OK)*
- [x] Migración + SP LLM en repo *(cada entorno SQL Server: migrate + deploy 7 `pq_sp_llm_*` — Framework [`adopcion-gen-16-byok.md`](../../../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-gen-16-byok.md))*
- [x] Rutas llm-credentials + chat-assistant/turns
- [x] Corpus provider + manifest
- [x] Avatar + ruta + modal BYOK
- [x] Mobile allowlist
- [x] Manual usuario
- [x] Unit + Feature + ≥1 E2E
- [x] `.env.example`: `PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS`, `PAQSUITE_GEN_DOCS_ROOT`
- [x] Sin ayuda URL; sin `actions` en turno

---

## 11) Revisión C1 (ambigüedad)

**Estado:** Apto con observaciones  
**Puede pasar a D1/D:** Sí (tras leer observaciones)

### Críticas (cerradas en esta TR)

- Prop avatar real = `showChat` (no `showChatAssistant`) — documentado en §1.
- BYOK ausente hoy en Partes → incluido como adopción GEN-16 en alcance de esta TR (necesario para CA-03/04/07).
- Endpoint HTTP no publicado en paquete → thin controller host obligatorio (§3 RN-TR-04).

### Menores

- Path exacto del corpus GEN en prod queda en env; default sibling Framework solo para dev.
- Tope de caracteres del corpus: orientar 24–32 KiB; afinable en D1 sin cambiar contrato API.
- OpenAPI formal: Should si el host ya publica catálogo.

### Contradicciones TR ↔ HU ↔ SPEC

- Ninguna de alcance: solo nomenclatura de props (mapeada).

### Supuestos

- Paquetes `@paqsuite/react-core@2.2.1` (Verdaccio) + `paqsuite/laravel-core@^1.3.3` (Satis) exponen `ChatAssistantPage`, `postChatAssistantTurn`, servicios PHP Chat/Llm + corpus GEN (`GenManualDocsRoot`).
- APP_KEY Laravel válida para `Crypt` del secreto BYOK.

### Preguntas humanas

Ninguna bloqueante.

### Recomendaciones

- En D1: clonar patrón smoke **sin** fakes de completion en prod (usar cliente real del paquete).
- Opcional post-MVP: alinear redacción SPEC/HU a `showChat` / `showHelp`.

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-01 | Parte C + C1: TR-008 desde SPEC-008 / HU-008; adopción host GEN-16/21. |
| 2026-08-01 | Parte D: adopción host (DB LLM, SP, credentials/turns, FE chat+BYOK, mobile, tests). |
| 2026-08-01 | Parte E + smoke manual + F1/F: [D-VERIFICACION-TR-008](../updates/100-SistemaPartes/D-VERIFICACION-TR-008-asistente-ia-2026-08-01.md); Estado → Pendiente de Revisión. |
