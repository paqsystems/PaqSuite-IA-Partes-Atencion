# HU-008 – Asistente IA (chat documental) desde menú avatar

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-008 |
| Título | Consultar ayuda del sistema y generalidades Framework vía Asistente IA |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente de Revisión |
| Última actualización | 2026-08-01 |
| SPEC origen | [SPEC-008-asistente-ia-chat-documental](../../05-open-spec/100-SistemaPartes/SPEC-008-asistente-ia-chat-documental.md) |
| TR relacionada(s) | [TR-008-asistente-ia-chat-documental](../../04-tareas/100-SistemaPartes/TR-008-asistente-ia-chat-documental.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-008 | Dónde en esta HU |
|--------------------------------|------------------|
| Avatar `showChat` / sin ayuda URL (R-AI-01/02, D-AI-01) | Alcance; CA-01; R-AI-01/02 |
| Navegación `/chat-assistant` misma ventana (R-AI-03) | Alcance; CA-02; R-AI-03 |
| Visible a asistente/supervisor/cliente (R-AI-04) | Actores; CA-01 |
| Distinción perfil / Smart Capture (R-AI-05) | Fuera de alcance; R-AI-05 |
| UI chat + empty BYOK (R-AI-06…10) | Alcance; CA-03, CA-04; R-AI-06…10 |
| Turno documental sin mutación (R-AI-11…16) | Alcance; CA-05, CA-06, CA-07; R-AI-11…16 |
| Corpus Partes + GEN (R-AI-17…20, D-AI-02) | Alcance; CA-08; R-AI-17…20 |
| Manual usuario «cómo abrir» | CA-09 |
| i18n / testids | CA-10 |
| Mobile in-app | Alcance; CA-02; R-AI-03 |
| Fuera de alcance URL / Smart Capture / RAG Must | Fuera de alcance |

---

## Narrativa

Como usuario autenticado de Partes de Atención (asistente, supervisor o cliente)  
quiero abrir un **Asistente IA** desde el menú del avatar  
para consultar **cómo usar el sistema** y las **generalidades del Framework** sin operar pantallas de negocio ni abandonar el portal.

---

## Contexto funcional

Tras el login y el gate de identidad Partes (SPEC-002), el usuario trabaja en el shell. Necesita ayuda orientativa sobre pantallas del módulo (carga, masivo, informes, maestros, mobile) y sobre piezas comunes del Framework (shell, avatar, grillas, preferencias LLM). El Framework ya define el canal de **chat documental** (SPEC-001-21); Partes lo **adopta** con corpus propio + GEN, BYOK obligatorio y **sin** enlace de ayuda externa por URL en este MVP.

---

## Alcance incluido

- Ítem **«Asistente IA»** en el menú avatar del shell Partes.
- Apertura de la pantalla de chat documental en **`/chat-assistant`** (misma ventana; mobile in-app).
- Experiencia de chat según canal GEN: bienvenida Partes, consulta, envío, Preferencias BYOK en modal, empty si no hay LLM.
- Envío de turnos documentales (texto hasta 2000 caracteres; imágenes solo si la config LLM soporta visión, con cupos GEN).
- Respuestas orientativas basadas en corpus Partes (`docs/99-manual-usuario`) **y** generalidades Framework GEN, vía manifest de adopción del host.
- Idioma de las respuestas = idioma de la aplicación del usuario (fallback español).
- Actualización del manual de usuario Partes indicando cómo abrir el Asistente IA.
- i18n y selectores estables para automatización.

---

## Fuera de alcance

- Ayuda externa por URL en el avatar.
- Smart Capture / carga de tareas por chat, audio o imagen.
- Que el asistente cree, edite o cierre tareas, o consulte datos vivos de clientes/tareas como ERP.
- Historial de conversación guardado en servidor.
- RAG/embeddings propietarios como requisito Must.
- Copiar manuales GEN dentro del corpus del producto.
- Ítem en el menú lateral.
- Cambiar el perfil Partes (sigue HU-002).
- Dictado por voz en el chat.
- Redefinir el contrato GEN del turno o del menú avatar.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-AI-01 | El menú avatar habilita **Asistente IA**. |
| R-AI-02 | No se ofrece ayuda por URL externa en este MVP. |
| R-AI-03 | «Asistente IA» abre `/chat-assistant` en la misma ventana; en mobile, siempre dentro de la app. |
| R-AI-04 | Todo usuario con sesión Partes usable ve el ítem; no depende de un permiso de menú lateral. |
| R-AI-05 | El Asistente IA no reemplaza el perfil Partes ni es Smart Capture. |
| R-AI-06 | La pantalla usa controles DevExtreme y la estética del shell. |
| R-AI-07 | Con LLM configurado: el usuario ve bienvenida Partes, puede escribir (máx. 2000) y enviar; puede adjuntar imágenes solo si la configuración lo permite (límites GEN). |
| R-AI-08 | Sin LLM válido: se muestra empty con llamada a Preferencias; no se puede enviar. |
| R-AI-09 | Preferencias de LLM se abren en modal desde el chat. |
| R-AI-10 | El hilo de conversación vive solo mientras la pantalla está abierta; no se guarda en servidor. |
| R-AI-11 | Cada consulta se envía como turno documental al canal GEN del Framework. |
| R-AI-12 | La respuesta es solo orientación (`reply`); no trae acciones que modifiquen el negocio. |
| R-AI-13 | Sin LLM, el servidor rechaza el turno indicando que falta configuración. |
| R-AI-14 | La respuesta se genera en el idioma de la app del usuario, no en el idioma del mensaje escrito. |
| R-AI-15 | Los tiempos de espera del modelo son los del producto Partes (detalle en TR). |
| R-AI-16 | Errores del canal usan el envelope GEN acordado para esta capacidad. |
| R-AI-17 | El conocimiento combina manuales Partes y generalidades Framework. |
| R-AI-18 | Partes no duplica los manuales Framework en su carpeta de manuales. |
| R-AI-19 | Si el tema no está en el corpus, el asistente lo indica; no inventa pantallas ni reglas. |
| R-AI-20 | El asistente no opera el sistema ni usa datos vivos de negocio como fuente de verdad. |

---

## Criterios de aceptación

- [ ] **CA-01** Con sesión Partes usable, el menú avatar muestra **Asistente IA** y **no** muestra un ítem de ayuda por URL externa.
- [ ] **CA-02** Al elegir Asistente IA, se abre `/chat-assistant` en la misma ventana (en mobile, dentro de la app; sin abrir navegador externo solo por este ítem).
- [ ] **CA-03** Sin configuración LLM válida: veo empty con CTA a Preferencias y no puedo enviar consulta.
- [ ] **CA-04** Con LLM válido: veo bienvenida de Partes, puedo escribir y enviar una consulta de hasta 2000 caracteres.
- [ ] **CA-05** Tras enviar, recibo una respuesta orientativa en el idioma de la aplicación.
- [ ] **CA-06** La respuesta del turno no ejecuta ni propone mutaciones de tareas/maestros (no “acciones de negocio”).
- [ ] **CA-07** Si fuerzo un envío sin LLM, el servidor responde error indicando configuración requerida.
- [ ] **CA-08** El conocimiento disponible incluye ayuda de Partes y generalidades Framework (manifest o smoke documental).
- [ ] **CA-09** El manual de usuario Partes explica cómo abrir el Asistente IA desde el avatar.
- [ ] **CA-10** Textos e identificadores de prueba estables para avatar/chat (i18n GEN + bienvenida Partes).

---

## Escenarios Gherkin

```gherkin
Feature: Asistente IA documental en Partes
  Como usuario autenticado de Partes
  Quiero consultar ayuda desde el avatar
  Para orientarme sin operar el sistema a ciegas

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

  Scenario: Consulta orientativa con LLM
    Given tengo una configuración LLM válida
    And estoy en "/chat-assistant"
    When envío una pregunta sobre cómo cargar un parte
    Then recibo una respuesta orientativa en el idioma de la aplicación
    And no se crea ni modifica ninguna tarea
```

---

## Supuestos

- El canal GEN de chat documental y BYOK están disponibles para adopción en la versión del Framework que use el host.
- El corpus Partes en `docs/99-manual-usuario` está publicado y alineado a los SPEC del módulo.
- El timeout numérico del LLM y el path exacto del manifest se fijan en TR-008.

---

## Preguntas abiertas

Ninguna bloqueante a nivel funcional. Detalle de empaquetado GEN / versión de paquetes → TR.

---

## Riesgos de ambigüedad

| Riesgo | Notas |
|--------|--------|
| Usuario confunde Asistente IA con carga por IA | Bienvenida + fuera de alcance Smart Capture |
| Corpus GEN ausente en install | Mensaje claro; checklist deploy en TR |

---

## Notas para TR (no son alcance funcional nuevo)

- Montar props del avatar y ruta FE.
- Adoptar endpoint/turno GEN y manifest corpus.
- Versión mínima de paquetes Framework.
- Policy mobile para `/chat-assistant`.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-01 | Parte B + B1: HU desde SPEC-008. |
| 2026-08-01 | Parte C: enlazada TR-008; Estado Especificado. |
| 2026-08-01 | D/E/F1: implementación verificada; Estado → Pendiente de Revisión. Ver [D-VERIFICACION-TR-008](../../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-008-asistente-ia-2026-08-01.md). |
