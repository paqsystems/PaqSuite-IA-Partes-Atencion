# TR-GEN-10-chat-documental — Chat documental del asistente IA

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-10-chat-documental](../../03-historias-usuario/001-Generalidades/HU-GEN-10-chat-documental.md) |
| **SPEC relacionada** | [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-10-configuracion-asistente-ia; TR-GEN-10-catalogo-proveedores-ia; TR-GEN-01-menu-avatar; TR-GEN-10-mensajes-asistente-ia |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-30 |

**Origen:** [HU-GEN-10-chat-documental](../../03-historias-usuario/001-Generalidades/HU-GEN-10-chat-documental.md)  
**Referencia SPEC:** [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Chat documental del asistente IA.

### Narrativa
Como usuario autenticado quiero abrir un chat asistente IA desde el menú avatar y hacer consultas sobre el sistema para recibir orientación operativa apoyada en la documentación aprobada sin perder mi pantalla actual.

### In scope / Out of scope
- **In scope:** ítem `Chat Asistente IA` en avatar, apertura en nueva pestaña del portal, ruta interna del chat, estado vacío con CTA si falta configuración, consultas textuales, corpus documental Fase 1, límites de longitud y UX asociada, respuestas orientativas con referencia documental.
- **Out of scope:** ejecución de acciones dentro del sistema, uso de corpus técnico/metodológico excluido, envío automático a soporte, persistencia de adjuntos.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario puede abrir `Chat Asistente IA` desde el menú avatar.
- **AC-02**: El chat se abre en nueva pestaña del portal y la pestaña original permanece disponible.
- **AC-03**: La opción de chat puede coexistir con la ayuda externa simple sin reemplazarla.
- **AC-04**: Si el usuario tiene configuración válida, puede enviar consultas textuales y recibir respuesta.
- **AC-05**: Si falta configuración, ve un estado vacío con mensaje claro y CTA a preferencias del usuario.
- **AC-06**: Una consulta de texto solo mayor a `2.000` caracteres no se envía.
- **AC-07**: Una consulta con texto e imágenes mayor a `1.000` caracteres no se envía.
- **AC-08**: El usuario ve contador visible y mensaje claro cuando supera el máximo permitido.
- **AC-09**: El chat utiliza solo el corpus documental definido para Fase 1.
- **AC-10**: Las respuestas se presentan como orientación operativa.

### Escenarios Gherkin

```gherkin
Feature: Chat documental del asistente IA

  Scenario: Abrir chat desde avatar
    Given un usuario autenticado
    When selecciona "Chat Asistente IA" en el menú avatar
    Then se abre una nueva pestaña del portal con el chat
    And la pestaña original permanece disponible

  Scenario: Consultar con configuración válida
    Given un usuario autenticado con configuración válida del chat
    When envía una consulta textual sobre el sistema
    Then recibe una respuesta orientativa
    And la respuesta se apoya en el corpus documental aprobado

  Scenario: Exceder límite de texto solo
    Given un usuario autenticado con configuración válida del chat
    When intenta enviar una consulta de texto solo mayor a 2000 caracteres
    Then el envío es bloqueado
    And ve un mensaje indicando el máximo permitido

  Scenario: Abrir chat sin configuración
    Given un usuario autenticado sin configuración válida
    When abre el chat
    Then ve un mensaje indicando que falta configuración
    And ve una CTA para ir a preferencias del usuario
```

---

## 3) Reglas de Negocio

1. **RN-01**: El chat se abre desde el menú avatar en una nueva pestaña del portal y no embebido en la pantalla actual.
2. **RN-02**: Si el usuario no tiene configuración válida, el chat no falla: informa el faltante y muestra CTA a configuración.
3. **RN-03**: La Fase 1 consulta solo `99-manual-usuario` y documentación operativa funcional estable aprobada.
4. **RN-04**: La respuesta debe posicionarse como orientación útil y no como resolución garantizada.
5. **RN-05**: Si hay referencia documental disponible, debe priorizarse sobre una respuesta puramente generativa.
6. **RN-06**: Una consulta de texto solo no puede superar `2.000` caracteres.
7. **RN-07**: Una consulta que incluya texto e imágenes no puede superar `1.000` caracteres de texto.
8. **RN-08**: La UI debe mostrar contador de caracteres y bloquear el envío cuando se exceda el límite.
9. **RN-09**: Se considera configuración válida cuando existe configuración personal, `isEnabled=true`, `hasApiKey=true` y `providerId` pertenece a un proveedor activo soportado.
10. **RN-10**: La Fase 1 opera con una whitelist técnica de corpus aprobados: `99-manual-usuario` y documentación operativa funcional estable, excluyendo SPEC/HU/TR y documentación técnica.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-30)

**Fuentes revisadas:** HU-GEN-10-chat-documental, SPEC-001-10-chat-asistente-ia, TR-GEN-10-configuracion-asistente-ia, TR-GEN-10-catalogo-proveedores-ia, TR-GEN-10-mensajes-asistente-ia, TR-GEN-10-imagenes-asistente-ia, TR-GEN-01-menu-avatar, `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`, `docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`, `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`, frontend (`AvatarMenu.tsx`, `ShellLayout.tsx`, `protectedRoutes.tsx`, `MenuSidebarTree.tsx`, slices `preferences`, `theme`), backend (`routes/api.php`, `UserPreferencesController`, `OpenApiSchemas.php`, tests actuales de preferences).

### Resultado general

- **Estado:** Apto
- **Puede pasar a D1:** **Sí**, con resoluciones C1 aceptadas para cerrar la definición de configuración válida, la ruta interna del chat y la coordinación con el slice de imágenes.

### Aceptación stakeholder (2026-05-30)

Se aceptan los supuestos detectados y las recomendaciones de ajuste de la TR bajo §3.1. El slice queda cerrado con:

- definición explícita de configuración válida para empty state vs chat habilitado;
- separación contractual entre validación de texto solo y validación combinada con imágenes;
- alineación del concepto de “perfil” con la superficie real de preferencias del usuario;
- delimitación técnica del corpus Fase 1 como whitelist operativa;
- supuestos aceptados: conversación stateless por request, referencias documentales sin URL pública obligatoria, validación duplicada frontend/backend y coexistencia con ayuda externa sin mezclar contratos.

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|------------------------------|
| AMB-C01 | **Ruta interna del chat y CTA de configuración no definidas** | La TR exige nueva pestaña del portal y CTA al perfil, pero hoy no existe ruta `chatAssistant` ni flujo `profile`; dos implementaciones podrían abrir URL externa, modal o ruta distinta. | **D1-1:** crear ruta protegida interna dedicada al chat (por ejemplo bajo el router autenticado) y hacer que el CTA del estado vacío navegue a la misma superficie real de preferencias definida en `TR-GEN-10-configuracion-asistente-ia`. |
| AMB-C02 | **“Configuración válida” no quedó contractualmente cerrada** | AC-04/AC-05 dependen de saber cuándo el chat puede operar; sin definición, backend/frontend pueden evaluar distinto (`hasConfiguration`, `isEnabled`, `hasApiKey`, proveedor activo, etc.). | **D1-2:** considerar válida una configuración cuando existe recurso personal, `isEnabled=true`, hay credencial disponible (`hasApiKey=true`) y `providerId` pertenece a un proveedor activo soportado. |
| AMB-C03 | **Límite de `1.000` para texto + imágenes depende de un payload aún no activo en este slice** | La TR menciona validación combinada, pero el contrato actual solo recibe `message`; eso puede derivar en validaciones fantasma o en una API incompatible con `TR-GEN-10-imagenes-asistente-ia`. | **D1-3:** en este slice base el endpoint acepta `message` y valida `2.000` para texto solo; la regla de `1.000` se activa únicamente cuando el payload incluya `images` según `TR-GEN-10-imagenes-asistente-ia`, preservando el mismo path. |
| AMB-C04 | **Contrato de respuesta insuficiente para la UX conversacional** | `reply`, `references` y `requiresSupportFollowup` existen, pero no se define si se devuelve estado de configuración, ids de mensaje o algún texto inicial; la UI puede terminar mezclando responsabilidades con la TR de mensajes editables. | **D1-4:** mantener este endpoint enfocado en la respuesta del asistente (`reply`, `references`, `requiresSupportFollowup`) y delegar mensaje inicial/empty state al frontend + `TR-GEN-10-mensajes-asistente-ia`; no introducir histórico ni ids conversacionales en esta fase. |
| AMB-C05 | **Corpus Fase 1 está delimitado funcionalmente, pero no operacionalmente** | Sin mecanismo técnico explícito, dos implementaciones pueden consultar carpetas distintas o usar contenido no aprobado. | **D1-5:** crear en backend un resolvedor/whitelist de corpus Fase 1 limitado a `99-manual-usuario` y documentación operativa aprobada; excluir explícitamente SPEC/HU/TR y docs técnicas. |
| AMB-C06 | **403 no justificado por el patrón actual** | Igual que en otros slices personales, la TR exige 403 sin definir policy adicional; eso empuja decisiones de autorización fuera del alcance documentado. | **D1-6:** documentar/testear `401` y `422` como obligatorios; `403` queda no aplicable en MVP actual salvo que una TR de seguridad lo introduzca expresamente. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | Apertura en nueva pestaña | El ítem del avatar abre una ruta interna con `window.open(..., '_blank', 'noopener,noreferrer')`, igual que el patrón existente de nueva pestaña. |
| AMB-M02 | Referencias documentales | Cada referencia debe devolver al menos `title` y `path`; `path` funciona como trazabilidad documental, no como URL pública obligatoria. |
| AMB-M03 | Contador UX | Mostrar contador decreciente o usado/total de forma estable y bloquear el submit antes de invocar la API. |
| AMB-M04 | E2E mínimos | Cubrir apertura desde avatar, empty state por falta de configuración y consulta válida con respuesta. |
| AMB-M05 | Ayuda externa coexistente | Mantener ambos ítems de avatar mientras el flujo de ayuda externa siga vigente; no ocultar uno por la presencia del otro. |

### Contradicciones TR ↔ código ↔ HU

| Contradicción | Resolución |
|---------------|------------|
| La TR pide ítem `avatarMenuItemChatAssistant`, pero el `AvatarMenu` actual no lo renderiza y no existe ruta interna del chat | **Cerrado:** D1 añade el ítem y la ruta protegida correspondiente. |
| La HU manda CTA “al perfil”, mientras el código actual no tiene slice `profile` y la configuración se viene alineando a `preferences` | **Cerrado:** la CTA apunta a la superficie real de preferencias del usuario definida por `TR-GEN-10-configuracion-asistente-ia`. |
| AC-07 habla de texto+imágenes, pero §5.2 del endpoint todavía no recibe `images` | **Cerrado:** el contrato base se prepara para extensión; la validación `1.000` se activa junto con el slice de imágenes. |
| La TR menciona “mensaje inicial” dentro del alcance, pero la responsabilidad detallada vive en `TR-GEN-10-mensajes-asistente-ia` | **Cerrado:** este slice cubre la página, el envío y la respuesta; el contenido del mensaje inicial/cierre se resuelve en la TR de mensajes. |

### Supuestos detectados

- La conversación en Fase 1 puede ser stateless por request; no se requiere histórico persistido del chat.
- El backend puede responder con referencias documentales aunque no exista una URL pública navegable para cada documento.
- La validación de límites se hace tanto en frontend como en backend.
- La coexistencia con ayuda externa no implica mezclar providers ni contratos.

### Preguntas para decisión humana

- Ninguna adicional. Las decisiones bloqueantes quedan cerradas con la aceptación stakeholder de esta revisión.

### Recomendaciones de ajuste de la TR

- **Aplicadas en esta revisión.**

### Veredicto C1

**Apta para D1 — C1 definitivamente cerrado.** El slice puede implementarse con ruta interna del chat, criterio de habilitación explícito y coordinación contractual cerrada con mensajes e imágenes.

---

## 3.2) Resoluciones C1 — pre-D1 (2026-05-30)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Ruta del chat | El chat se implementa como ruta protegida interna del portal, abierta en nueva pestaña desde el avatar. |
| R-C1-02 | CTA de configuración | El estado vacío redirige a la superficie real de preferencias/configuración personal del asistente, no a un `profile` ambiguo. |
| R-C1-03 | Configuración válida | Hay chat habilitado solo si existe configuración personal, `isEnabled=true`, `hasApiKey=true` y `providerId` sigue activo/soportado. |
| R-C1-04 | Contrato del mensaje | `POST /api/v1/chat-assistant/messages` devuelve `reply`, `references` y `requiresSupportFollowup`; no introduce histórico ni ids conversacionales en Fase 1. |
| R-C1-05 | Límite de longitud | En este slice base: `message` obligatorio y máximo `2.000` caracteres; la regla `1.000` para texto+imágenes se activa al extender el payload con `images`. |
| R-C1-06 | Corpus Fase 1 | Backend opera con una whitelist de corpus aprobados: `99-manual-usuario` + documentación operativa funcional estable, excluyendo SPEC/HU/TR y docs técnicas. |
| R-C1-07 | Seguridad | Endpoint protegido por autenticación + `X-Paq-Cliente`; `401` y `422` son obligatorios; `403` no aplica en MVP actual. |
| R-C1-08 | Coexistencia UX | `Chat Asistente IA` y ayuda externa simple pueden convivir simultáneamente en el avatar sin ocultarse entre sí. |

---

## 3.3) Plan D1 — Implementación (2026-05-30)

### Alcance entendido

Implementar la experiencia base del chat documental: acceso desde el avatar, apertura en nueva pestaña del portal, ruta protegida interna, estado vacío cuando no exista configuración válida, envío de consultas textuales y respuesta orientativa con referencias documentales. **Fuera:** histórico persistido, acciones dentro del sistema, corpus técnico/metodológico excluido, envío automático a soporte y storage de adjuntos.

### Fuentes leídas

- SPEC: `docs/05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md`
- HU: `docs/03-historias-usuario/001-Generalidades/HU-GEN-10-chat-documental.md`
- TR: `docs/04-tareas/001-Generalidades/TR-GEN-10-chat-documental.md`
- TR hermanas: `TR-GEN-10-configuracion-asistente-ia`, `TR-GEN-10-mensajes-asistente-ia`, `TR-GEN-10-imagenes-asistente-ia`
- Código: `frontend/src/features/avatar/components/AvatarMenu.tsx`, `frontend/src/app/layout/ShellHeader.tsx`, `frontend/src/app/router/protectedRoutes.tsx`, `frontend/src/app/router/AppRoutes.tsx`, `frontend/src/shared/http/client.ts`, `backend/routes/api.php`, `backend/app/OpenApi/OpenApiSchemas.php`, `backend/tests/Feature/OpenApiDocumentationTest.php`

### Impacto esperado

#### Base de datos

- Sin tabla nueva obligatoria para este slice.
- Lectura del recurso personal de configuración para resolver si el chat está habilitado.
- Lectura del corpus documental aprobado mediante whitelist técnica, sin persistir histórico de conversación.

#### Backend

- Nuevo endpoint `POST /api/v1/chat-assistant/messages`.
- Request validator para `message` y reglas de longitud.
- Servicio/orquestador del chat documental que valide configuración, resuelva corpus aprobado y devuelva `reply`, `references`, `requiresSupportFollowup`.
- Schemas OpenAPI y test feature dedicados.

#### Frontend

- Nueva ruta protegida del chat dentro del router autenticado.
- Nueva página `ChatAssistantPage` con composer, contador, lista de referencias y empty state.
- Integración del avatar para abrir la nueva pestaña con `window.open`.
- CTA del empty state a la superficie real de configuración del asistente.

#### Tests

- Unit frontend para contador, bloqueo por límite y resolución de empty state.
- Integration backend para 200/401/422, criterio de configuración válida y whitelist de corpus.
- E2E para apertura desde avatar, empty state, consulta válida y bloqueo por límite.

#### Documentación

- Actualizar matriz `matriz-permisos-mvp.md`.
- Documentar el contrato del endpoint y el criterio de configuración válida.

#### DevOps

- Sin cambios de infraestructura.
- El corpus Fase 1 debe resolverse desde contenido ya versionado/aprobado por el proyecto; no se planifica pipeline RAG externo en esta fase.

### Decisiones D1 (cerradas en C1)

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Ruta interna | Crear ruta protegida del chat dentro del router autenticado, abierta en nueva pestaña desde `AvatarMenu`. |
| D1-2 | Configuración válida | Backend y frontend usan el mismo criterio: `hasConfiguration`, `hasApiKey`, `isEnabled` y proveedor activo soportado. |
| D1-3 | Contrato base | `POST /api/v1/chat-assistant/messages` devuelve solo `reply`, `references`, `requiresSupportFollowup`. |
| D1-4 | Límite texto | En el slice base: `message` obligatorio y máximo `2.000`; el límite `1.000` se activa junto con imágenes en la TR hermana. |
| D1-5 | Corpus | Resolver whitelist explícita de `99-manual-usuario` + documentación operativa aprobada, excluyendo SPEC/HU/TR y docs técnicas. |
| D1-6 | UX | El empty state no rompe la página y deriva a preferencias del usuario. |
| D1-7 | Seguridad | 401 y 422 obligatorios; 403 no aplica en MVP actual salvo policy nueva. |

### Orden de trabajo

1. Definir la ruta protegida del chat y el ítem nuevo del avatar.
2. Exponer `POST /api/v1/chat-assistant/messages` con validador, service y OpenAPI.
3. Implementar la página del chat, composer, contador y vista de respuesta.
4. Implementar el empty state enlazado a la superficie de configuración personal.
5. Añadir tests feature, unit y E2E; cerrar matriz/documentación.

### Archivos previstos

| Capa | Archivos |
|------|----------|
| Backend | `backend/app/Http/Controllers/ChatAssistantMessageController.php`, `backend/app/Http/Requests/SendChatAssistantMessageRequest.php`, `backend/app/Services/ChatAssistant/ChatAssistantMessageService.php`, `backend/app/Services/ChatAssistant/ChatAssistantCorpusResolver.php`, `backend/app/Support/ChatAssistantErrorCodes.php`, `backend/routes/api.php`, `backend/app/OpenApi/OpenApiSchemas.php`, `backend/tests/Feature/ChatAssistantMessageTest.php`, `backend/tests/Feature/OpenApiDocumentationTest.php` |
| Frontend | `frontend/src/features/chatAssistant/pages/ChatAssistantPage.tsx`, `frontend/src/features/chatAssistant/api/sendChatAssistantMessage.ts`, `frontend/src/features/chatAssistant/model/chatAssistantMessage.ts`, `frontend/src/features/chatAssistant/components/ChatAssistantComposer.tsx`, `frontend/src/features/chatAssistant/components/ChatAssistantEmptyState.tsx`, `frontend/src/features/chatAssistant/components/ChatAssistantReferences.tsx`, `frontend/src/features/chatAssistant/components/ChatAssistantResponse.tsx`, `frontend/src/features/avatar/components/AvatarMenu.tsx`, `frontend/src/app/router/protectedRoutes.tsx` |
| E2E | `frontend/tests/e2e/chat-assistant.spec.ts` |
| Docs | `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` |

### Tests a ejecutar

- Backend: `ChatAssistantMessageTest` con 200/401/422, criterio de configuración válida y whitelist de corpus.
- Backend: `OpenApiDocumentationTest`.
- Frontend unit: contador, bloqueo por longitud y render del empty state.
- E2E: apertura desde avatar, coexistencia con ayuda externa, empty state con CTA y consulta textual válida.

### Dudas / bloqueos

- El CTA del empty state depende de la superficie final de configuración definida en `TR-GEN-10-configuracion-asistente-ia`; al ejecutar D debe cerrarse la ruta interna concreta.

### Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**. El plan se limita al chat documental base, su ruta interna, el envío de texto y la respuesta orientativa con referencias, sin introducir histórico, ejecución de acciones ni integraciones externas adicionales.

## 4) Impacto en Datos

### Tablas afectadas
- Sin tabla nueva obligatoria en este slice para Fase 1 del chat documental.
- Lectura indirecta de `PQ_PARTES_ASISTENTE_IA_CREDENCIALES` para resolver si el usuario tiene configuración válida.

### Seed mínimo para tests
- Usuario autenticado con configuración válida del chat.
- Usuario autenticado sin configuración válida.
- Corpus documental mínimo de prueba derivado de `99-manual-usuario` / documentación operativa aprobada para validar respuesta no vacía.
- Fixture/caso de whitelist que permita verificar exclusión de SPEC/HU/TR y docs técnicas del corpus efectivo.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Código, matriz y OpenAPI deben coincidir. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| POST | `/api/v1/chat-assistant/messages` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### POST `/api/v1/chat-assistant/messages`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "message": "Necesito ayuda para entender por qué no puedo guardar un pedido."
}
```

**Validaciones funcionales mínimas:**

- en este slice base, `message` es obligatorio;
- máximo `2.000` caracteres si la interacción es de texto solo;
- la regla de `1.000` caracteres para texto + imágenes se activa cuando el payload se extienda con `images` según `TR-GEN-10-imagenes-asistente-ia`, preservando el mismo endpoint.

**Response 200:** envelope con respuesta orientativa del asistente.

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "reply": "Texto orientativo de la respuesta",
    "references": [
      {
        "title": "Manual de usuario",
        "path": "99-manual-usuario/..."
      }
    ],
    "requiresSupportFollowup": false
  }
}
```

**Response 401:** no autenticado.

**Response 403:** no aplica en el MVP actual mientras la regla siga siendo “usuario autenticado” sin policy adicional.

**Response 422:** consulta vacía o excede límites de longitud.

**OpenAPI (L5-Swagger):**

- [ ] Endpoint documentado en controller/DTO.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401 y 422 documentadas.
- [ ] 403 documentado solo si en una oleada posterior se agrega policy específica.
- [ ] Envelope documentado.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Agregar fila para `POST /api/v1/chat-assistant/messages`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/chatAssistant/pages/ChatAssistantPage.tsx` (nuevo): página del chat en nueva pestaña.
- `frontend/src/features/chatAssistant/api/sendChatAssistantMessage.ts` (nuevo): envío de consulta textual.
- `frontend/src/features/chatAssistant/model/chatAssistantMessage.ts` (nuevo): tipado de request/response.
- `frontend/src/features/chatAssistant/components/ChatAssistantComposer.tsx` (nuevo): caja de texto con contador y bloqueo.
- `frontend/src/features/chatAssistant/components/ChatAssistantEmptyState.tsx` (nuevo): estado vacío por falta de configuración.
- `frontend/src/features/chatAssistant/components/ChatAssistantReferences.tsx` (nuevo o ajuste): referencias documentales.
- `frontend/src/features/avatar/components/AvatarMenu.tsx` (ajuste): ítem `Chat Asistente IA`.

### Reglas de integración

- El chat se implementa como ruta protegida interna del portal, abierta en nueva pestaña desde el avatar con `window.open(..., '_blank', 'noopener,noreferrer')`.
- El estado vacío redirige a la superficie real de preferencias/configuración personal del asistente y no a un `profile` ambiguo.
- El endpoint de chat solo devuelve `reply`, `references` y `requiresSupportFollowup`; el mensaje inicial y el cierre a soporte se resuelven en `TR-GEN-10-mensajes-asistente-ia`.
- La conversación de Fase 1 puede operar como interacción stateless por request, sin histórico persistido del chat.

### data-testid sugeridos
- `avatarMenuItemChatAssistant`
- `chatAssistantPage`
- `chatAssistantComposerInput`
- `chatAssistantCharacterCounter`
- `chatAssistantSubmitButton`
- `chatAssistantEmptyState`
- `chatAssistantEmptyStateConfigurationCta`
- `chatAssistantResponse`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Exponer `POST /api/v1/chat-assistant/messages` con validación de longitud y envelope | OpenAPI + 401/422 |
| T2 | Backend | Resolver lectura de configuración válida y estado vacío cuando no exista | Sin crash; contrato consistente |
| T3 | Backend | Integrar consulta al corpus documental Fase 1 y devolver referencias | Respuesta orientativa con trazabilidad y whitelist operativa cerrada |
| T4 | Frontend | Agregar ítem `Chat Asistente IA` al menú avatar y abrir nueva pestaña del portal | UX completa |
| T5 | Frontend | Implementar página de chat, composer con contador y validación de límites | AC visibles |
| T6 | Frontend | Implementar estado vacío con CTA a preferencias del usuario cuando falte configuración | Mensaje claro y navegación |
| T7 | Tests | Integration API + unit contador/validación + E2E apertura/estado vacío/consulta válida | Casos verdes |
| T8 | Docs | Matriz endpoint ↔ permiso y trazabilidad del corpus Fase 1 | Coherencia documental |

---

## 8) Estrategia de Tests

- **Unit:** contador de caracteres, bloqueo por límite, resolución de estado vacío.
- **Integration:** `POST /api/v1/chat-assistant/messages` con 200/401/422; verificar criterio de configuración válida, whitelist de corpus Fase 1 y shape estable de respuesta (`reply`, `references`, `requiresSupportFollowup`).
- **E2E:**  
  - abrir chat desde avatar en nueva pestaña;  
  - abrir chat sin configuración y ver CTA;  
  - enviar consulta válida y ver respuesta;  
  - exceder límite y ver bloqueo/mensaje;  
  - coexistencia con ayuda externa sin desaparición del ítem del chat.

---

## 9) Riesgos y Edge Cases

- Corpus documental incompleto o mal delimitado que genere respuestas fuera de alcance.
- Respuesta sin referencias cuando la documentación sí existe.
- Diferencias entre límite de texto solo y texto + imágenes que confundan la UX si no se comunica bien.
- Apertura en nueva pestaña afectada por políticas del navegador o interacción no considerada como gesto del usuario.
- Riesgo de desalineación entre el criterio de configuración válida del backend y el empty state resuelto por frontend.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Integración validada con menú avatar y configuración personal
- [ ] Corpus Fase 1 acotado a documentación aprobada

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401 y 422 documentados; 403 solo si aplica policy específica
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 solo si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR










