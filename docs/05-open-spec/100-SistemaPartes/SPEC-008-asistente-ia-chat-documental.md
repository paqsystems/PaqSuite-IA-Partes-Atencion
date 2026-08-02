# SPEC-008 – Asistente IA (chat documental) — adopción en Partes

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-008 |
| Título | Asistente IA — chat documental desde menú avatar (adopción GEN-21) |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Especificado |
| Última actualización | 2026-08-01 |
| HU relacionada(s) | [HU-008-asistente-ia-chat-documental](../../03-historias-usuario/100-SistemaPartes/HU-008-asistente-ia-chat-documental.md) |
| TR relacionada(s) | [TR-008-asistente-ia-chat-documental](../../04-tareas/100-SistemaPartes/TR-008-asistente-ia-chat-documental.md) |
| Depende de | [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md) (sesión Partes usable); canal GEN SPEC-001-21; corpus SPEC-001-99; avatar SPEC-001-08; BYOK SPEC-001-16 (docs en checkout Framework `PaqSuite-IA-FRAMEWORK/docs/05-open-spec/001-Generalidades/`) |
| Fuentes | [`12-asistente-ia-ayuda-y-chat-documental.md`](../../02-producto/Sistema-Partes-IA/12-asistente-ia-ayuda-y-chat-documental.md) (D-AI-01…04); Framework producto `21` |

---

## 1. Resumen ejecutivo

- **Problema:** el usuario autenticado de Partes necesita consultar **ayuda orientativa** (cómo usar el módulo y generalidades del Framework) sin abandonar el portal ni operar pantallas de negocio a ciegas.
- **Resultado esperado:** Partes **adopta** el canal GEN de **chat documental (Asistente IA)** con entrada en el **menú avatar**, pantalla in-app, corpus Partes + GEN vía manifest del host, y **BYOK obligatorio**; **sin** ayuda externa por URL en este MVP.

---

## 2. Alcance

### 2.1 En alcance

- Habilitar en el shell de Partes el ítem avatar **«Asistente IA»** (`showChat: true`).
- Navegar a la pantalla de **chat documental in-app** (ruta Must del host: `/chat-assistant`).
- Reutilizar UI/contrato de turno del Framework (SPEC-001-21): cabecera, bienvenida, consulta, envío, Preferencias BYOK en modal, empty sin LLM, i18n `chatAssistant.*`, `data-testid` estables.
- Consumir `POST /api/v1/chat-assistant/turns` (contrato GEN) sin `actions` de mutación de dominio.
- Proveer corpus al runtime mediante **contrato de adopción del host** (manifest/paths):  
  - corpus Partes = `docs/99-manual-usuario/` de este producto;  
  - corpus Framework GEN = paths/paquete GEN (**sin** copiar manuales GEN al repo Partes);  
  - **sin RAG Must**.
- Copy de **bienvenida** propio de Partes (i18n).
- Idioma de `reply` = locale de app del usuario (`users.locale`; vacío/inválido → `es`); no seguir el idioma del prompt (SPEC-001-21 Q13).
- Disponibilidad para todo usuario con **sesión Partes usable** (asistente, supervisor, cliente) — misma precondición que el shell post-login SPEC-002.
- Mobile Capacitor (cuando el producto lo tenga activo): mismo ítem; chat **siempre in-app** (SPEC-001-21 / SPEC-007).
- Actualizar manual de usuario Partes con «cómo abrir el Asistente IA» (`Partes-Atencion.md` y/o índice).

### 2.2 Fuera de alcance

- **Ayuda externa por URL** en el avatar (`showHelp` permanece deshabilitado) — D-AI-01.
- **Smart Capture** / asistente operativo embebido en carga diaria (evolución; `07` / SPEC-001-03).
- Inventar o redefinir el canal GEN (UI canónica, envelope `4300–4399`, shape del turno): se **adopta**, no se bifurca.
- Mutación de datos de negocio vía chat (alta/edición tareas, masivo, ABM).
- Consulta de datos vivos de clientes/tareas como si fuera el ERP.
- Persistencia de historial de conversación en servidor (v1 GEN: hilo solo sesión cliente).
- RAG/embeddings propietarios como Must.
- Duplicar o forkedear corpus GEN dentro de `docs/99-manual-usuario` del producto.
- Ítem propio en menú lateral `pq_menus`.
- Cambiar el perfil Partes del avatar (sigue SPEC-002).
- Dictado/STT en el chat (fuera GEN v1).

---

## 3. Actores y contexto

| Actor | Uso del Asistente IA |
|-------|----------------------|
| Asistente / supervisor / cliente | Puede abrir el chat desde el avatar tras login con identidad Partes usable |
| Usuario sin vínculo Partes usable | No opera el shell Partes (SPEC-002); este SPEC no aplica |
| Sistema | Aplica gate BYOK; sirve turnos documentales; no ejecuta acciones de dominio |

**Precondiciones**

- Sesión autenticada Sanctum + gate Partes OK (SPEC-002).
- Al menos una configuración LLM BYOK válida para **enviar** turnos (D-AI-03). Sin ella, la UI muestra empty canónico + CTA Preferencias; el BE rechaza el turno con `configurationRequired`.

---

## 4. Comportamiento funcional

### 4.1 Acceso (avatar)

| Norma | Detalle |
|-------|---------|
| R-AI-01 | El shell monta `UserAvatarMenu` con **`showChat: true`** (API real Framework; alias documental histórico: `showChatAssistant`). |
| R-AI-02 | **`showHelp` = false** (o no montar ayuda URL) en este MVP. |
| R-AI-03 | Al elegir «Asistente IA», el host navega a **`/chat-assistant`** **en la misma ventana** de la SPA (Must MVP). La preferencia `openInNewTab` del avatar **no** abre el chat en solapa aparte en este MVP (esa preferencia sigue afectando solo la navegación del menú lateral según GEN-08). Mobile: siempre in-app. |
| R-AI-04 | El ítem es visible para todo usuario autenticado en el shell Partes; no hay permiso de menú lateral específico. |
| R-AI-05 | Smart Capture **no** aparece en el avatar. El perfil Partes sigue siendo control/panel distinto. |

### 4.2 Pantalla de chat

Adopción de SPEC-001-21 § UI:

| Tema | Norma Partes |
|------|----------------|
| R-AI-06 | Controles DevExtreme; tema shell A1. |
| R-AI-07 | Con LLM activo: cabecera, SelectBox de configuración activa, bienvenida Partes, TextArea (máx. 2000), envío; adjuntos imagen según cupo GEN (hasta 4 × 2 MB) si `supportsVision`. |
| R-AI-08 | Sin LLM válido: empty canónico (una jerarquía) + CTA Preferencias; **envío bloqueado**. |
| R-AI-09 | Preferencias BYOK: **modal** desde el chat; ruta `/preferences` desde avatar si el producto la expone (GEN-08/16). |
| R-AI-10 | Hilo: solo sesión cliente v1; se pierde al salir/refrescar la pantalla del chat. |

### 4.3 Turno documental

| Norma | Detalle |
|-------|---------|
| R-AI-11 | Endpoint GEN: `POST /api/v1/chat-assistant/turns` con `contractVersion`, `credentialId`, `message` (± `images`). |
| R-AI-12 | Respuesta: `reply` orientativo; `citations` opcionales; **sin** `actions` de dominio. |
| R-AI-13 | Sin config LLM: BE `error ≠ 0` + HTTP 4xx + `configurationRequired`. |
| R-AI-14 | `reply` en locale de app (fallback `es`); corpus puede estar en español canónico. |
| R-AI-15 | Timeouts = default del **producto** Partes (config/env documentada en TR; alineada a SPEC-001-16). |
| R-AI-16 | Códigos de capacidad en rango envelope GEN `4300–4399`. |

### 4.4 Corpus (adopción host)

| Norma | Detalle |
|-------|---------|
| R-AI-17 | El host declara un **manifest** (o paths/endpoint de contexto) con origen **Partes** (`docs/99-manual-usuario/`: índice + SPEC-002…007 publicados) y origen **Framework GEN**. |
| R-AI-18 | No duplicar archivos GEN en el corpus del producto. |
| R-AI-19 | El asistente **orienta** según corpus; si el tema no está documentado, debe indicarlo con honestidad (no inventar pantallas/reglas). |
| R-AI-20 | El asistente **no** opera el sistema ni consulta datos de negocio vivos como fuente de verdad operativa. |

### 4.5 Mensajes / i18n

- Prefijo UI GEN `chatAssistant.*` + claves de bienvenida Partes (p. ej. `partes.chatAssistant.welcome` — catálogo exacto en HU/TR).
- Errores de envelope visibles vía i18n GEN / host.

---

## 5. Criterios verificables

- [ ] Con sesión Partes usable, el menú avatar muestra **Asistente IA** y **no** muestra ayuda externa por URL.
- [ ] Activar el ítem abre `/chat-assistant` in-app en la misma ventana (mobile: in-app; sin `window.open` para este flujo).
- [ ] Sin BYOK válido: empty + CTA Preferencias; no se envía turno; BE responde `configurationRequired` si se fuerza el POST.
- [ ] Con BYOK válido: el usuario envía un mensaje ≤ 2000 chars y recibe `reply` en el locale de la app.
- [ ] El turno no incluye `actions` ni modifica tareas/maestros.
- [ ] El corpus efectivo incluye material Partes **y** GEN (verificable por manifest o por respuesta que cite/generalice ambos ámbitos en smoke documental).
- [ ] Manual `Partes-Atencion.md` (o índice) documenta cómo abrir el Asistente IA.
- [ ] i18n + `data-testid` estables en avatar/chat (prefijos GEN + Partes donde aplique).

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Frontend shell | `ShellPage` / `UserAvatarMenu`: `showChat`, `showHelp=false`; ruta `/chat-assistant`; montar `ChatAssistantPage` de `@paqsuite/react-core` |
| Frontend i18n | Bienvenida Partes; reutilizar `chatAssistant.*` |
| Backend | Adoptar endpoint/turno GEN del paquete Laravel Framework; registrar adopción de corpus (manifest) |
| Corpus | Archivo manifest host + paths a `docs/99-manual-usuario` + GEN |
| Docs | Manual usuario; este SPEC; HU-008 / TR-008 |
| Mobile | Policy: ruta chat permitida; full screen in-app |
| OpenAPI | Si el host publica OpenAPI, documentar adopción del turno GEN (detalle TR) |

**Nota SP:** el turno documental es plumbing GEN (excepción de framework auth/IA); no introduce SP de negocio Partes. El corpus no se sirve con SQL de dominio.

---

## 7. Riesgos y supuestos

| Riesgo / supuesto | Mitigación |
|-------------------|------------|
| Paquete GEN chat/BYOK aún no versionado en el host | TR debe fijar versión mínima de `@paqsuite/react-core` / paquete PHP; smoke con credencial demo |
| Corpus GEN no empaquetado en el install | Manifest falla cerrado: mensaje claro; checklist deploy incluye paths GEN |
| Usuario espera que el chat cargue tareas | Copy de bienvenida + R-AI-20; fuera de alcance Smart Capture |
| Timeout LLM lento | Default de producto en TR; UX de loading DX |

**Supuestos**

- SPEC-001-21 y SPEC-001-16 están disponibles como norma a adoptar (no se reimplementa el canal desde cero en Partes).
- El corpus Partes vigente en `docs/99-manual-usuario` es la fuente Must del producto.

---

## 8. Decisiones de producto absorbidas

| ID | Decisión |
|----|----------|
| D-AI-01 | Solo chat in-app |
| D-AI-02 | Manifest host Partes + GEN, sin RAG Must |
| D-AI-03 | BYOK obligatorio |
| D-AI-04 | Este SPEC-008 |

---

## 9. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-01 | Parte A: versión inicial desde `12-asistente-ia-ayuda-y-chat-documental.md`. |
| 2026-08-01 | A1: R-AI-03 fija navegación chat en misma ventana (sin solapa vía `openInNewTab`). |
| 2026-08-01 | Parte B: enlazada HU-008. |
| 2026-08-01 | Parte C: enlazada TR-008; props avatar alineadas a API real (`showChat` / `showHelp`). |
| 2026-08-01 | D/E/F1: adopción host verificada ([D-VERIFICACION-TR-008](../../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-008-asistente-ia-2026-08-01.md)); HU/TR → Pendiente de Revisión. |

---

**Trazabilidad:** fuente producto `12-…`; GEN-21/08/16/99. HU-008 / TR-008.
