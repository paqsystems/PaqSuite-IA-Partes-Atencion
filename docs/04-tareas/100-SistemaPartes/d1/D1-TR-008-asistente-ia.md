# Plan de implementación - TR-008

## Alcance entendido

Adoptar en Partes el **Asistente IA (chat documental)** GEN-21 + BYOK GEN-16 mínimos para cumplir CA:

- Avatar: `showChat` → `/chat-assistant`; `showHelp=false`; Preferencias LLM en **modal** (avatar + chat).
- Backend: tabla/SP `pq_llm_credentials`, rutas `/api/v1/llm-credentials*`, `POST /api/v1/chat-assistant/turns` (thin controllers sobre servicios del paquete).
- Corpus host: `ManifestChatCorpusProvider` (Partes `docs/99-manual-usuario` + GEN vía `PAQSUITE_GEN_DOCS_ROOT`, sin RAG, sin copiar GEN).
- Mobile allowlist `/chat-assistant`; manual usuario; timeout producto 60 s.

**No** Smart Capture, ayuda URL, menú lateral, RAG, historial servidor, ni reimplementar UI/contrato GEN.

## Fuentes leídas

- SPEC: `docs/05-open-spec/100-SistemaPartes/SPEC-008-asistente-ia-chat-documental.md`
- HU: `docs/03-historias-usuario/100-SistemaPartes/HU-008-asistente-ia-chat-documental.md`
- TR: `docs/04-tareas/100-SistemaPartes/TR-008-asistente-ia-chat-documental.md` (C1 apto con obs.)
- Producto: `docs/02-producto/Sistema-Partes-IA/12-asistente-ia-ayuda-y-chat-documental.md`
- Código Partes: `ShellPage.tsx`, `AppRouter.tsx`, `partesMobilePolicy.ts`, `routes/api.php`, `SpUserPreferencesRepository`, `AppServiceProvider`, i18n `common.json`
- Framework: `ChatAssistantPage`, `LlmPreferencesPanel`, `ChatAssistantTurnService`, SP `pq_sp_llm_*`, smoke `capabilities.php` + controllers (patrón HTTP; **no** Eloquent en prod)

## Impacto esperado

### Base de datos

| Acción | Detalle |
|--------|---------|
| Crear | Migración `pq_llm_credentials` + `users.active_llm_credential_id` (modelo smoke GEN-16) |
| Desplegar | Copiar/instalar SP `pq_sp_llm_credentials_{list,get,insert,update,delete}` + `pq_sp_llm_active_preference_{get,set}` desde `laravel-core/database/sp/` → `backend/database/sp/` |
| Ajustar | Preferencias: o bien SPs active_preference_* (recomendado) o ampliar `pq_sp_user_preferences_*` — hoy stub siempre `activeLlmCredentialId=null` |
| Sin | Ítems `pq_menus`; embeddings; tablas corpus |

### Backend

| Crear | Rol |
|-------|-----|
| `SpLlmCredentialRepository` | Implementa `LlmCredentialRepository` + `ActiveLlmCredentialPreferenceRepository` vía `SpCaller` (MUST SP; **no** clonar Eloquent smoke) |
| `LlmCredentialsController` | Thin → `LlmCredentialService` + `LaravelCryptSecretCipher` |
| `ChatAssistantTurnsController` | Thin → `ChatAssistantTurnService` |
| Base helper envelope | Mapear `ChatAssistantDomainException` / excepciones LLM a `ApiResponse` (patrón smoke `CapabilityController`) |
| `ManifestChatCorpusProvider` | Lee manifest config; filtra tokens; `CorpusChunk` |
| `config/chat_assistant_corpus.php` | Paths Partes + GEN |
| Feature tests | credentials + turns (fake `LlmChatCompletionClient` en DI de test) |

| Modificar | Cambio |
|-----------|--------|
| `routes/api.php` | Grupo auth: llm-credentials + chat-assistant/turns |
| `AppServiceProvider` | Bindings: repos SP, `ChatCorpusProvider`, `LlmChatCompletionClient` (`ProviderRouting…`), cipher, opcional service |
| `config/paqsuite.php` | `chatAssistant.llmTimeoutSeconds` (default 60); allowlist multi si aplica |
| `.env.example` | `PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS`, `PAQSUITE_GEN_DOCS_ROOT` |

### Frontend

| Crear | Rol |
|-------|-----|
| `features/chatAssistant/ChatAssistantHostPage.tsx` | Monta `ChatAssistantPage` + `onOpenPreferences` |
| Hook/controller modal BYOK | `LlmPreferencesPanel` / `createLlmPreferencesModalController` compartido shell+chat |

| Modificar | Cambio |
|-----------|--------|
| `ShellPage.tsx` | `showChat`, `onChat`, `showPreferences`, `onPreferences`, `showHelp={false}`, labels i18n |
| `AppRouter.tsx` | Ruta `/chat-assistant` bajo shell + `RequirePartesMobilePolicy` |
| `partesMobilePolicy.ts` + test | Allowlist `/chat-assistant` |
| `i18n/locales/*/common.json` | `partes.chatAssistant.welcome`, `avatar.chat`, `avatar.preferences`, claves mínimas `chatAssistant.*` / `llmPreferences.*` (host; paquete no trae JSON) |

### Tests

- Unit: corpus provider; policy mobile
- Feature: 4301 sin LLM; CRUD credentials sin secreto; turno OK con client fake
- E2E Playwright: login → avatar → `/chat-assistant` → empty Preferencias
- Sin LLM de pago en CI

### Documentación

- `docs/99-manual-usuario/Partes-Atencion.md`: cómo abrir Asistente IA desde avatar
- OpenAPI Should si el host ya publica paths

### DevOps

- Deploy: `migrate` + scripts SP LLM
- Env: timeout + root docs GEN (prod: path instalado Framework docs; dev: sibling checkout)
- APP_KEY válida (cifrado secreto BYOK)

## Orden de trabajo

1. **T1 DB** — migración + SP LLM en `backend/database/sp/`
2. **T2 BE credentials** — `SpLlm*` + controller + routes + Feature CRUD
3. **T3 Corpus** — manifest config + `ManifestChatCorpusProvider` + unit
4. **T4 Turns** — controller + DI client timeout + Feature 4301/success fake
5. **T5 FE BYOK modal** — panel + `showPreferences` avatar
6. **T6 FE chat** — host page + ruta + i18n welcome
7. **T7 Avatar chat** — `showChat` / `onChat` / `showHelp=false`
8. **T8 Mobile** — allowlist + Vitest
9. **T9 Docs/env** — manual + `.env.example`
10. **T10 E2E** — Playwright empty state

Alineado a plan de tareas TR-008 §7.

## Riesgos

| Riesgo | Mitigación en D |
|--------|-----------------|
| Solo hay repo Eloquent en smoke | Implementar SP desde cero con `SpCaller` |
| Preferencias stub vs SPs active | Preferir `pq_sp_llm_active_preference_*`; alinear GET/PATCH `/user/preferences` si el Select FE también lee active ahí |
| GEN docs path ausente en servidor | Fallback Partes-only + log; no 500 |
| i18n GEN incompleto | Copiar set mínimo del smoke + claves Partes en 5 locales |
| Llamada LLM real en Feature | Bind fake `LlmChatCompletionClient` en tests |
| Confusión Smart Capture | Copy bienvenida; no montar SC |

## Tests a ejecutar

```text
# Backend
php artisan test --filter=LlmCredential
php artisan test --filter=ChatAssistant

# Frontend
npm run test -- partesMobilePolicy
npm run test:e2e -- (spec chat-assistant o tag)
npm run test:all   # al cerrar E
```

Smoke manual opcional: 1 turno con credencial real local.

## Dudas / bloqueos

| Tema | Decisión D1 (cerrada) |
|------|------------------------|
| ¿Eloquent smoke? | **No** — MUST `SpLlmCredentialRepository` |
| ¿Preferencias active vía qué SP? | **`pq_sp_llm_active_preference_*`** (paquete); repo único implementa ambos ports |
| ¿Modal vs ruta `/preferences`? | **Modal Must** (SPEC/TR); no hace falta página `/preferences` Must |
| ¿Tope corpus chars? | **~28 KiB** texto agregado (dentro de banda 24–32 KiB TR) |
| ¿Bloqueo paquetes Framework? | Path local ya apunta al Framework; verificar exports al arrancar D (smoke build si falta) |
| Preguntas humanas | **Ninguna bloqueante** |

## Archivos clave (mapa)

**Crear:** migración LLM; SP llm; `SpLlmCredentialRepository`; controllers Llm + ChatAssistant; `ManifestChatCorpusProvider`; `chat_assistant_corpus.php`; `ChatAssistantHostPage`; Feature/E2E tests.

**Modificar:** `api.php`, `AppServiceProvider`, `paqsuite.php`, `.env.example`, `ShellPage`, `AppRouter`, `partesMobilePolicy`(+test), `common.json` ×5, `Partes-Atencion.md`.

## Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**
- BYOK GEN-16 mínimo incluido porque la TR lo declara necesario para CA (no scope creep)
- Listo para **Parte D** tras OK humano
