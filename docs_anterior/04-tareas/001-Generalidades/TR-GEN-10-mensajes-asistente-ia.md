# TR-GEN-10-mensajes-asistente-ia — Mensajes editables del Chat Asistente IA

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-10-mensajes-asistente-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-mensajes-asistente-ia.md) |
| **SPEC relacionada** | [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-10-chat-documental; TR-GEN-10-configuracion-asistente-ia |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-30 |

**Origen:** [HU-GEN-10-mensajes-asistente-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-mensajes-asistente-ia.md)  
**Referencia SPEC:** [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Mensajes editables del Chat Asistente IA.

### Narrativa
Como usuario del Chat Asistente IA quiero ver un mensaje inicial claro y un mensaje final de derivación a soporte cuando la IA no tenga suficiente confianza para entender qué esperar del chat y cómo continuar si la respuesta no alcanza.

### In scope / Out of scope
- **In scope:** mensaje inicial editable, mensaje final editable de derivación a soporte, lectura desde archivos Markdown, placeholder de contacto, regla de visualización del cierre solo ante baja confianza.
- **Out of scope:** personalización por usuario, edición de mensajes desde UI, envío automático de correo o ticket a soporte.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Al abrir una conversación nueva se muestra el mensaje inicial.
- **AC-02**: El mensaje inicial proviene del archivo Markdown definido.
- **AC-03**: El mensaje de cierre a soporte proviene del archivo Markdown definido.
- **AC-04**: El cierre a soporte aparece solo en respuestas con baja confianza o insuficiencia de orientación.
- **AC-05**: El contenido admite un placeholder para el dato real de soporte.
- **AC-06**: Si la respuesta es suficientemente confiable, el cierre a soporte no se muestra.

### Escenarios Gherkin

```gherkin
Feature: Mensajes editables del Chat Asistente IA

  Scenario: Mostrar mensaje inicial
    Given un usuario abre una conversación nueva del chat
    Then ve el mensaje inicial definido para la experiencia

  Scenario: Mostrar cierre por baja confianza
    Given un usuario realiza una consulta
    And la IA no tiene confianza suficiente en la respuesta
    Then la respuesta incluye el mensaje final de derivación a soporte

  Scenario: No mostrar cierre cuando la respuesta es suficiente
    Given un usuario realiza una consulta
    And la IA responde con confianza suficiente
    Then la respuesta no incluye el mensaje final de derivación a soporte
```

---

## 3) Reglas de Negocio

1. **RN-01**: El mensaje inicial se resuelve desde archivo Markdown editable del proyecto.
2. **RN-02**: El mensaje de cierre a soporte se resuelve desde archivo Markdown editable del proyecto.
3. **RN-03**: El mensaje final no debe aparecer en todas las respuestas; solo aplica cuando la orientación resulte insuficiente o la IA exprese baja confianza.
4. **RN-04**: El dato de contacto a soporte debe poder completarse sin hardcodear el valor final en la lógica.
5. **RN-05**: La regla concreta para detectar baja confianza debe implementarse como criterio técnico controlado dentro del slice, sin alterar el alcance funcional.
6. **RN-06**: Los placeholders soportados en primera fase son `{{Proyecto}}` y `{{supportEmail}}`.
7. **RN-07**: El mensaje inicial se muestra cuando la página del chat carga sin mensajes previos en la conversación actual.
8. **RN-08**: La heurística para definir `requiresSupportFollowup` pertenece al slice de chat; esta TR solo consume el boolean y resuelve el render del cierre.
9. **RN-09**: No se crea endpoint exclusivo para servir estos mensajes; el contenido se resuelve como activo versionado/adaptador interno consumible por frontend.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-30)

**Fuentes revisadas:** HU-GEN-10-mensajes-asistente-ia, SPEC-001-10-chat-asistente-ia, TR-GEN-10-chat-documental, TR-GEN-10-configuracion-asistente-ia, `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-mensaje-inicial.md`, `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-mensaje-cierre-soporte.md`, `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`, frontend actual (slices `preferences`, `theme`, `avatar`) y contrato `requiresSupportFollowup` definido en el slice de chat.

### Resultado general

- **Estado:** Apto
- **Puede pasar a D1:** **Sí**, con resoluciones C1 aceptadas para cerrar la estrategia de carga de Markdown, la resolución de placeholders y la responsabilidad exacta del flag `requiresSupportFollowup`.

### Aceptación stakeholder (2026-05-30)

Se aceptan las recomendaciones de ajuste y sugerencias de esta revisión. El slice queda cerrado con:

- placeholders explícitos `{{Proyecto}}` y `{{supportEmail}}`;
- aclaración de que no se crea endpoint nuevo solo para servir estos contenidos;
- delimitación del momento exacto del saludo inicial;
- frontera clara con `TR-GEN-10-chat-documental` para el uso de `requiresSupportFollowup`.

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|------------------------------|
| AMB-C01 | **Carga de Markdown desde `docs/...` no definida para runtime frontend** | El navegador no puede leer arbitrariamente la carpeta `docs`; dos implementaciones podrían copiar archivos a mano, hardcodear strings o abrir un endpoint nuevo no documentado. | **D1-1:** resolver estos Markdown como assets/versionados del frontend en build time o mediante un adaptador explícito de contenido, sin crear endpoint nuevo en este slice. |
| AMB-C02 | **Placeholders no completamente identificados en la TR** | El cierre menciona placeholder de soporte, pero el mensaje inicial también contiene `{{Proyecto}}`; si no se cierra la fuente, quedarán literales visibles o reemplazos distintos entre entornos. | **D1-2:** soportar al menos `{{Proyecto}}` y `{{supportEmail}}`; ambos se resuelven desde configuración conocida del producto y nunca quedan hardcodeados dentro del componente renderizador. |
| AMB-C03 | **Responsabilidad de `requiresSupportFollowup` puede mezclarse entre slices** | Esta TR depende del flag técnico, pero si también intenta decidir la heurística de baja confianza, duplica lógica con `TR-GEN-10-chat-documental`. | **D1-3:** el backend/chat decide `requiresSupportFollowup`; esta TR solo consume el boolean y renderiza el cierre correspondiente. |
| AMB-C04 | **Momento exacto del mensaje inicial no está cerrado** | AC-01 dice “al abrir conversación nueva”, pero sin histórico ni botón “nueva conversación” definido, distintos programadores podrían mostrarlo siempre, una sola vez por sesión o por cada respuesta. | **D1-4:** en Fase 1 el mensaje inicial se muestra al cargar la página del chat cuando todavía no hay mensajes en la conversación actual. |
| AMB-C05 | **Formato/render de Markdown no acotado** | Contenido editable puede incluir encabezados, backticks o listas; sin una regla, la UI puede renderizar HTML inseguro o inconsistente. | **D1-5:** admitir un subconjunto controlado de Markdown y renderizarlo de forma segura/sanitizada; no interpretar HTML arbitrario embebido. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | Fallback por falta de archivo | Si falla la carga del asset, mostrar fallback textual controlado y registrar el error sin romper el chat. |
| AMB-M02 | Placeholder faltante | Si una variable no puede resolverse, usar fallback configurado y evitar mostrar el token crudo al usuario. |
| AMB-M03 | Tests integrados | Validar el render del cierre usando el mismo contrato `requiresSupportFollowup` del slice de chat, sin mockear una API nueva. |
| AMB-M04 | data-testid oculto | `chatAssistantSupportFollowupHidden` puede resolverse como ausencia del nodo o bandera explícita, pero debe quedar consistente en tests. |

### Contradicciones TR ↔ HU ↔ contenido fuente

| Contradicción | Resolución |
|---------------|------------|
| La TR menciona un placeholder de soporte, pero los archivos reales incluyen también `{{Proyecto}}` en el mensaje inicial | **Cerrado:** el slice reconoce ambos placeholders mínimos y su fuente queda definida en D1. |
| La HU deja abierta la heurística de baja confianza; la TR podría absorberla aunque depende del contrato del chat | **Cerrado:** la heurística vive en `TR-GEN-10-chat-documental`; esta TR solo renderiza según `requiresSupportFollowup`. |
| La TR no crea endpoint, pero su checklist transversal puede inducir a pensar que debe abrir uno para cargar Markdown | **Cerrado:** no se agrega endpoint nuevo; la carga de contenido se resuelve del lado frontend/build o adaptador interno. |

### Supuestos detectados

- Los archivos Markdown se versionan con el proyecto y cambian por despliegue, no por edición desde la UI.
- El dato de soporte y el nombre del proyecto ya existen o podrán exponerse desde configuración del producto sin ampliar el alcance funcional.
- En Fase 1 no hay histórico persistido de conversaciones; “conversación nueva” equivale a estado inicial de la página.

### Preguntas para decisión humana

- Ninguna adicional. Las decisiones bloqueantes quedan cerradas con la aceptación stakeholder de esta revisión.

### Recomendaciones de ajuste de la TR

- **Aplicadas en esta revisión.**

### Veredicto C1

**Apta para D1 — C1 definitivamente cerrado.** El slice queda implementable con carga de contenido cerrada, placeholders definidos y frontera explícita con el slice del chat.

---

## 3.2) Resoluciones C1 — pre-D1 (2026-05-30)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Carga de contenido | Los mensajes se resuelven como contenido versionado del proyecto consumible por frontend sin abrir endpoint nuevo en este slice. |
| R-C1-02 | Placeholders mínimos | Se soportan `{{Proyecto}}` y `{{supportEmail}}` como placeholders canónicos de primera fase. |
| R-C1-03 | Fuente del contacto | `supportEmail` se resuelve con valor general compartido `ayuda@paqsystems.com.ar`; el componente solo reemplaza, no decide el valor. |
| R-C1-04 | Fuente del nombre del proyecto | `Proyecto` se resuelve desde configuración/entorno propio de cada proyecto y no desde el documento `_MONO` ni desde string hardcodeado en el render. |
| R-C1-05 | Regla de render del cierre | `requiresSupportFollowup=true` muestra el mensaje final; `false` no lo renderiza. |
| R-C1-06 | Momento del saludo inicial | El mensaje inicial se muestra cuando la página del chat carga sin mensajes previos en la conversación actual. |
| R-C1-07 | Seguridad de render | El Markdown se interpreta en modo seguro/sanitizado y sin HTML arbitrario. |

---

## 3.3) Plan D1 — Implementación (2026-05-30)

### Alcance entendido

Implementar el mensaje inicial y el mensaje de derivación a soporte como contenido editable versionado del proyecto, consumido por frontend y renderizado de forma segura según el contrato del chat. El slice cubre carga de contenido, reemplazo de placeholders, render inicial y render condicional de cierre. **Fuera:** edición desde UI, endpoint exclusivo para servir mensajes, heurística de baja confianza y automatización de contacto a soporte.

### Fuentes leídas

- SPEC: `docs/05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md`
- HU: `docs/03-historias-usuario/001-Generalidades/HU-GEN-10-mensajes-asistente-ia.md`
- TR: `docs/04-tareas/001-Generalidades/TR-GEN-10-mensajes-asistente-ia.md`
- Contexto: `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-mensaje-inicial.md`, `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-mensaje-cierre-soporte.md`
- TR hermana: `docs/04-tareas/001-Generalidades/TR-GEN-10-chat-documental.md`
- Código: `frontend/src/app/router/protectedRoutes.tsx`, `frontend/src/shared/http/client.ts`, `frontend/src/locales/es.json`, `frontend/tests/e2e/avatar-menu.spec.ts`

### Impacto esperado

#### Base de datos

- Sin impacto en base de datos.

#### Backend

- Sin endpoint nuevo ni contrato adicional fuera de `requiresSupportFollowup` ya definido por el slice de chat.
- Coordinación únicamente a nivel de contrato del response del chat.

#### Frontend

- Adaptador/cargador de contenido versionado para ambos Markdown.
- Utilidad de reemplazo de placeholders `{{Proyecto}}` y `{{supportEmail}}`.
- Componentes de render del mensaje inicial y del cierre condicional.
- Render Markdown seguro/sanitizado y fallback si falla la carga.

#### Tests

- Unit para carga de contenido, placeholders y render condicional.
- Validación integrada del flag `requiresSupportFollowup`.
- E2E para conversación nueva y respuesta con/sin cierre.

#### Documentación

- Mantener trazabilidad explícita con los dos Markdown fuente y el contrato `requiresSupportFollowup`.

#### DevOps

- Sin cambios de infraestructura.
- Si la carga desde `docs/...` requiriera adaptación de build, resolverla como detalle técnico del frontend sin abrir endpoint nuevo.

### Decisiones D1 (cerradas en C1)

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Carga | Resolver ambos mensajes como contenido versionado consumido por frontend, sin endpoint exclusivo. |
| D1-2 | Placeholders | Soportar `{{Proyecto}}` y `{{supportEmail}}` como reemplazos mínimos. |
| D1-3 | Fuentes | `Proyecto` sale de configuración/env del proyecto; `supportEmail` usa `ayuda@paqsystems.com.ar`. |
| D1-4 | Render inicial | Mostrar saludo al cargar la página del chat cuando aún no existan mensajes en la conversación actual. |
| D1-5 | Render cierre | Mostrar cierre solo cuando `requiresSupportFollowup=true`. |
| D1-6 | Seguridad | Interpretar Markdown en modo seguro/sanitizado y con fallback controlado ante fallas de carga. |

### Orden de trabajo

1. Implementar adaptadores de carga del contenido versionado y el reemplazo de placeholders.
2. Integrar los componentes de render en la página del chat.
3. Conectar el cierre condicional al flag `requiresSupportFollowup` del slice de chat.
4. Añadir tests unit/E2E y verificar fallback controlado.

### Archivos previstos

| Capa | Archivos |
|------|----------|
| Frontend | `frontend/src/features/chatAssistant/content/loadInitialMessage.ts`, `frontend/src/features/chatAssistant/content/loadSupportFollowupMessage.ts`, `frontend/src/features/chatAssistant/components/ChatAssistantInitialMessage.tsx`, `frontend/src/features/chatAssistant/components/ChatAssistantSupportFollowup.tsx`, `frontend/src/features/chatAssistant/utils/replaceSupportPlaceholders.ts`, `frontend/src/features/chatAssistant/model/chatAssistantMessage.ts` |
| Contenido | Reutilización de `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-mensaje-inicial.md` y `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-mensaje-cierre-soporte.md` mediante adaptador de frontend/build |
| E2E | `frontend/tests/e2e/chat-assistant.spec.ts` o spec específico del chat |

### Tests a ejecutar

- Frontend unit: carga de contenido, reemplazo de placeholders, fallback y render condicional.
- E2E: carga inicial del saludo, respuesta con `requiresSupportFollowup=true` y ausencia del cierre cuando `false`.
- Verificación integrada del contrato del campo `requiresSupportFollowup` dentro del slice del chat.

### Dudas / bloqueos

- Confirmar durante D la estrategia técnica exacta de importación del Markdown versionado en frontend/build sin introducir endpoint nuevo; el criterio funcional ya queda cerrado.

### Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**. El plan se limita a contenido versionado, placeholders y render seguro dentro del chat, sin agregar nuevas APIs ni lógica de decisión de baja confianza.

## 4) Impacto en Datos

### Tablas afectadas
- Sin tablas nuevas obligatorias en este slice.

### Seed mínimo para tests
- Archivo Markdown de mensaje inicial con contenido válido.
- Archivo Markdown de mensaje de cierre con placeholder de contacto.
- Archivo Markdown de mensaje inicial con placeholder `{{Proyecto}}`.
- Caso de respuesta con `requiresSupportFollowup = true`.
- Caso de respuesta con `requiresSupportFollowup = false`.
- Caso de carga fallida del asset para validar fallback controlado sin romper la UI.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Código, matriz y OpenAPI deben coincidir. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

No introduce endpoint exclusivo si el comportamiento se resuelve como parte de `POST /api/v1/chat-assistant/messages`.

Los mensajes se resuelven desde contenido versionado del proyecto consumible por frontend sin abrir endpoint nuevo en este slice.

Si la implementación necesitara un endpoint dedicado de assets o contenido, deberá abrirse actualización explícita del slice sin ampliar alcance tácitamente.

### 5.2 Integración con respuesta del chat

El slice requiere que la respuesta del chat pueda transportar una señal técnica equivalente a:

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "reply": "Texto orientativo",
    "references": [],
    "requiresSupportFollowup": true
  }
}
```

Regla:

- `requiresSupportFollowup = true` → UI agrega mensaje final de soporte;
- `requiresSupportFollowup = false` → UI no agrega el mensaje final.
- la decisión de setear `requiresSupportFollowup` pertenece al slice del chat y no se redefine en esta TR.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/chatAssistant/content/loadInitialMessage.ts` (nuevo o ajuste): carga del mensaje inicial desde el asset/documento definido.
- `frontend/src/features/chatAssistant/content/loadSupportFollowupMessage.ts` (nuevo o ajuste): carga del mensaje final desde el asset/documento definido.
- `frontend/src/features/chatAssistant/components/ChatAssistantInitialMessage.tsx` (nuevo o ajuste): render del mensaje inicial.
- `frontend/src/features/chatAssistant/components/ChatAssistantSupportFollowup.tsx` (nuevo o ajuste): render condicional del cierre de soporte.
- `frontend/src/features/chatAssistant/utils/replaceSupportPlaceholders.ts` (nuevo o ajuste): reemplazo de `{{Proyecto}}` y `{{supportEmail}}`.
- `frontend/src/features/chatAssistant/model/chatAssistantMessage.ts` (ajuste): incluir `requiresSupportFollowup`.

### Reglas de integración frontend

- El mensaje inicial se renderiza al cargar la página del chat cuando aún no hay mensajes en la conversación actual.
- Si falla la carga del contenido versionado, la UI usa un fallback textual controlado y registra el error sin romper la experiencia.
- El Markdown se renderiza en modo seguro/sanitizado, sin interpretar HTML arbitrario embebido.
- `{{Proyecto}}` se reemplaza con una variable/configuración propia del proyecto activo; en este proyecto el valor esperado es `Pedidos Web`.
- `{{supportEmail}}` se reemplaza con el valor general `ayuda@paqsystems.com.ar`.

### data-testid sugeridos
- `chatAssistantInitialMessage`
- `chatAssistantSupportFollowup`
- `chatAssistantSupportFollowupHidden`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Cargar mensaje inicial desde contenido versionado del proyecto | Visible al iniciar conversación |
| T2 | Frontend | Cargar mensaje final de soporte desde contenido versionado del proyecto | Render correcto |
| T3 | Frontend | Implementar reemplazo de placeholders `{{Proyecto}}` y `{{supportEmail}}` | Valores visibles sin hardcode |
| T4 | Frontend | Renderizar cierre solo cuando `requiresSupportFollowup = true` | Sin ruido en respuestas confiables |
| T5 | Tests | Unit de carga/render + E2E con y sin cierre de soporte | AC verdes y fallback controlado cubierto |
| T6 | Docs | Mantener referencia explícita a archivos Markdown y contrato de señal técnica | Trazabilidad consistente |

---

## 8) Estrategia de Tests

- **Unit:** carga de contenido versionado, reemplazo de placeholders, render condicional según `requiresSupportFollowup` y fallback controlado ante falla de carga.
- **Integration:** no aplica endpoint exclusivo en esta TR; validar contrato integrado del campo `requiresSupportFollowup` en el slice del chat.
- **E2E:**  
  - abrir conversación nueva y ver mensaje inicial;  
  - respuesta con baja confianza muestra cierre;  
  - respuesta suficiente no muestra cierre;  
  - placeholders visibles resueltos y no renderizados en crudo.

---

## 9) Riesgos y Edge Cases

- Placeholder sin reemplazar visible en producción.
- Diferencias de formato entre Markdown y render esperado en UI.
- Regla de baja confianza demasiado sensible o demasiado laxa.
- Contenido de mensajes editado con estructura inesperada que degrade la presentación.
- Falla de carga del contenido versionado que deje la pantalla vacía si no existe fallback controlado.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Frontend + tests según plan
- [ ] Mensajes cargados desde archivos editables y no hardcodeados
- [ ] Render condicional del cierre validado

### Checklist normas transversales

- [ ] Sin endpoint exclusivo nuevo en esta TR; contrato `requiresSupportFollowup` validado dentro del slice de chat
- [ ] Matriz endpoint ↔ permiso sin cambios en esta TR, salvo actualización si el slice de chat la requiere
- [ ] OpenAPI en /api/documentation coherente con el slice de chat que expone `requiresSupportFollowup`
- [ ] 401/403 no aplican como obligación propia de esta TR sin endpoint exclusivo
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API se cubren en el slice de chat que expone `requiresSupportFollowup`
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR










