# Verificación F1 — TR-008 Asistente IA

| Campo | Valor |
|-------|-------|
| TR | [TR-008](../../100-SistemaPartes/TR-008-asistente-ia-chat-documental.md) |
| HU | [HU-008](../../../03-historias-usuario/100-SistemaPartes/HU-008-asistente-ia-chat-documental.md) |
| SPEC | [SPEC-008](../../../05-open-spec/100-SistemaPartes/SPEC-008-asistente-ia-chat-documental.md) |
| Fecha | 2026-09-23 |
| Resultado | **OK — finalizado** |

## Evidencia automatizada

| Capa | Comando | Resultado |
|------|---------|-----------|
| Backend Chat/Corpus/BYOK | `php artisan test --filter=ChatAssistant` | **5 passed, 21 assertions** |
| Frontend | `npm run test -- --run` | **40 archivos, 135 tests passed** |
| E2E | `npx playwright test tests/e2e/chat-assistant.spec.ts` | **1 passed** |

## Cobertura verificada

- Avatar con **Asistente IA**, sin ayuda externa por URL.
- Navegación in-app a `/chat-assistant`, incluida la allowlist mobile.
- Empty sin BYOK y CTA de Preferencias.
- CRUD BYOK sin exponer secretos.
- Error `4301 / 409 / configurationRequired` sin credencial válida.
- Turno exitoso con cliente fake, respuesta orientativa y sin `actions`.
- Corpus Partes + Framework mediante manifest; fallback Partes-only si falta el root GEN.
- Timeout configurable mediante `PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS`.
- Manual de usuario actualizado.

## Pendientes operativos no bloqueantes

- Ejecutar migración y los SP `pq_sp_llm_*` en el SQL Server de despliegue.
- Configurar `PAQSUITE_GEN_DOCS_ROOT` cuando el corpus GEN no esté disponible desde el paquete instalado.
- Smoke con una API key real para validar el proveedor LLM; CI usa cliente fake y no requiere secretos.
