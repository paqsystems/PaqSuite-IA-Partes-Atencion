# Verificación F1 + F — TR-008 Asistente IA (chat documental)

| Campo | Valor |
|-------|--------|
| **TR** | [TR-008](../../100-SistemaPartes/TR-008-asistente-ia-chat-documental.md) |
| **HU** | [HU-008](../../../03-historias-usuario/100-SistemaPartes/HU-008-asistente-ia-chat-documental.md) |
| **SPEC** | [SPEC-008](../../../05-open-spec/100-SistemaPartes/SPEC-008-asistente-ia-chat-documental.md) |
| **Plan D1** | [D1-TR-008](../../100-SistemaPartes/d1/D1-TR-008-asistente-ia.md) |
| **Fecha verificación** | 2026-08-01 |
| **Prueba manual** | Smoke UI (Playwright MCP) contra Vite `:3000` + Laravel sqlite `:8010` |

---

# F1 — Verificación del agente

## Resultado

- **Aprobado con observaciones**

## Evidencia revisada

- DB: migración `2026_08_01_200100_create_pq_llm_credentials_table.php`; scripts `backend/database/sp/pq_sp_llm_*.sql`
- BE: `SpLlmCredentialRepository`, `LlmCredentialsController`, `ChatAssistantTurnsController`, `ManifestChatCorpusProvider`, `HostHttpLlmChatCompletionClient`, rutas en `api.php`, bindings en `AppServiceProvider`
- FE: `ShellPage` (`showChat` / `showPreferences` / `showHelp={false}`), `ChatAssistantHostPage`, `LlmPreferencesModalHost`, ruta `/chat-assistant`, i18n, allowlist mobile
- Auth host: `installApiAuthFetch` + `Authenticate` (API sin `route('login')`)
- Preferencias activas LLM vía SP / sqlite fallbacks en `SpCaller` + `SpUserPreferencesRepository`
- Manual: `docs/99-manual-usuario/Partes-Atencion.md` (sección Asistente IA)
- Ajustes post-smoke: auth en llamadas GEN LLM/chat (401→500 → fix)

## Hallazgos críticos

- Ninguno abierto tras ajustes.

## Advertencias

1. **Auth GEN sin headers del host:** componentes `@paqsuite/react-core` (LLM/chat) llaman `apiRequest` sin `Authorization`. Mitigado en host con interceptor `installApiAuthFetch`. Ideal: que el paquete GEN acepte inyección de headers (follow-up Framework).
2. **Turno con API key de prueba:** smoke manual con secreto fake → UI muestra «No se pudo completar el turno» (esperado). Turno documental real **no** verificado contra proveedor de pago.
3. **SQL Server DEMO:** E2E/smoke local usaron **sqlite** (`e2e.sqlite` en `:8010`); instancia `192.168.41.2` no alcanzable en esta sesión. Deploy SQL Server: migrate + SP `pq_sp_llm_*` pendientes de corrida en servidor real.
4. **Durante smoke** la tabla `users` de `e2e.sqlite` quedó vacía (reseed `db:seed` restauró admin). Causa no atribuida con certeza a TR-008; documentar cuidado con BD e2e local.

## Sugerencias

- E2E adicional: alta BYOK desde modal + empty→composer (hoy E2E cubre avatar → `/chat-assistant`).
- Alinear redacción SPEC/HU a props reales `showChat` / `showHelp` (Parte I / unificación copy).
- Smoke post-deploy con API key real (1 pregunta Partes + 1 GEN).

## Tests

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=ChatAssistant` (sqlite) | **5 passed** (21 assertions) |
| `npm run test -- --run` (Vitest) | **33 passed** (10 files) |
| `npx playwright test tests/e2e/chat-assistant.spec.ts` | **1 passed** (proxy `VITE_API_PROXY_TARGET` → `:8010` sqlite) |

## Pendientes

- Deploy SQL Server: migrate LLM + SP `pq_sp_llm_*`
- Vars: `PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS`, `PAQSUITE_GEN_DOCS_ROOT`
- Cierre humano **Finalizado** cuando se acepte revisión (solo usuario)
- Commit/push solo con autorización

## Recomendación final

- Cerrar ciclo OpenSpec **E → manual → F1/F**: TR/HU a **Pendiente de Revisión**. Listo para revisión humana; no forzar **Finalizado**.

---

# F — openspec-05 (vs SPEC / HU / TR)

## Contexto

- TR: `docs/04-tareas/100-SistemaPartes/TR-008-asistente-ia-chat-documental.md`
- HU: `docs/03-historias-usuario/100-SistemaPartes/HU-008-asistente-ia-chat-documental.md`
- SPEC: `docs/05-open-spec/100-SistemaPartes/SPEC-008-asistente-ia-chat-documental.md`

## Resumen ejecutivo

Adopción host GEN-16/21 coherente con alcance Must. Completitud ✓ · Corrección ✓ (con observaciones de entorno) · Coherencia ✓.

## Completitud

| AC / DoD | Estado |
|----------|--------|
| AC-01 Avatar Asistente IA; sin ayuda URL | ✓ (smoke + E2E) |
| AC-02 `/chat-assistant` misma ventana; mobile allowlist | ✓ (código + Vitest policy + E2E) |
| AC-03 Empty sin LLM + CTA Preferencias | ✓ (smoke post-reseed) |
| AC-04 Con LLM: bienvenida + composer + envío | ✓ (smoke con credencial; turno proveedor fake falla en UI) |
| AC-05 Turno OK locale app | ⚠ Feature con cliente fake OK; proveedor real no smoke |
| AC-06 Sin `actions`; no muta dominio | ✓ (Feature + diseño service) |
| AC-07 4301 + 409 sin LLM | ✓ Feature |
| AC-08 Manifest Partes + GEN | ✓ Unit corpus + config |
| AC-09 Manual cómo abrir | ✓ |
| AC-10 i18n + testids GEN | ✓ |
| Migración + SP LLM | ✓ en repo; deploy SQL Server pendiente |
| Modal BYOK avatar = chat | ✓ |

## Corrección

- Props avatar reales `showChat` / `showHelp` / `showPreferences` (no aliases inventados).
- Persistencia BYOK vía SP (no Eloquent CRUD de negocio).
- Fix auth API: 401 JSON en `/api/*` (sin `Route [login]`).

## Coherencia

- Envelope y códigos alineados a GEN (4301 configuración).
- Producto `12-asistente-ia-…` D-AI-01…04 respetado (sin ayuda URL, sin Smart Capture en este canal).

## Próximos pasos

1. Revisión humana → **Finalizado** cuando se autorice.
2. Deploy migrate + SP en SQL Server + smoke con key real.
3. Commit cuando se autorice (sin push a `main`).
