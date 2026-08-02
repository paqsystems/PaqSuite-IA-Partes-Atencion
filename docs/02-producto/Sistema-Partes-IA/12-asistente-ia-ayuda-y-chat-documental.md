# Asistente IA — ayuda y chat documental

## Objetivo

Definir, para **Partes de Atención**, el acceso desde el **menú avatar** a un **Asistente IA de orientación** que permita al usuario consultar:

1. la **ayuda general del sistema** (cómo usar Partes: pantallas, perfiles, informes, errores visibles);
2. las **generalidades del Framework** PaqSuite (shell, login, menú, grillas, preferencias, patrones comunes), sin redefinirlas en este módulo.

Este documento es la **definición conceptual de producto** del módulo. No es SPEC/HU/TR; alimenta la posterior generación Open-Spec (Parte J / A→C).

---

## 1. Relación con el Framework (no reinventar)

La capacidad es **transversal** del Framework. Partes **adopta** y **configura**; no redefine el canal.

| Norma Framework | Rol |
|-----------------|-----|
| [`02-producto/21-ayuda-externa-y-chat-documental.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/21-ayuda-externa-y-chat-documental.md) | Distinción chat documental vs Smart Capture; corpus; acceso avatar |
| [`SPEC-001-21`](../../../PaqSuite-IA-FRAMEWORK/docs/05-open-spec/001-Generalidades/SPEC-001-21-ayuda-externa-y-chat-documental.md) | Contrato OpenSpec del canal (UI, turno, flags avatar, i18n) |
| [`02-producto/08-menu-avatar.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/08-menu-avatar.md) / SPEC-001-08 | Catálogo del menú avatar (`showChatAssistant`, copy, orden) |
| [`SPEC-001-99`](../../../PaqSuite-IA-FRAMEWORK/docs/05-open-spec/001-Generalidades/SPEC-001-99-manual-usuario-corpus.md) | Contrato del corpus de manuales |
| [`02-producto/16-provider-ia.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/16-provider-ia.md) | BYOK / Preferencias LLM (compartido) |
| Contexto mono | `docs/00-contexto/_mono/01-experiencia-base/ayuda-externa-asistente.md` (si está enlazado en el host) |

**Regla:** el menú avatar y el chat son piezas GEN; Partes declara **si las habilita**, **qué corpus aporta** y **qué copy/bienvenida** usa.

---

## 2. Distinción obligatoria (Partes)

| Capacidad | Propósito en Partes | ¿MVP de este documento? |
|-----------|---------------------|-------------------------|
| **Chat documental / Asistente IA** (este doc) | Explicar y orientar: «¿cómo cargo un parte?», «¿qué ve un cliente?», «¿qué es Paquete de Horas?» | **Sí** |
| **Ayuda externa (URL)** | Enlace a wiki/Zendesk/PDF fuera del portal | **No** en este MVP (fuera de alcance) |
| **Smart Capture / asistente operativo** | Completar o proponer datos en un formulario abierto (carga diaria) | **No** — evolución (`07-fuera-de-alcance-y-evolucion.md`, `10-mobile.md`) |
| **Perfil Partes** | Solo lectura desde avatar (SPEC-002) | Ya definido; **ítem distinto** del Asistente IA |

Anti-patrón: usar el Asistente IA para grabar tareas, cerrar lotes o consultar datos vivos de clientes/tareas como si fuera el ERP.

---

## 3. Decisión de producto para Partes (MVP)

### 3.1 Canal principal

**Chat documental in-app** (Asistente IA), accesible desde el **menú avatar**.

- Etiqueta visible: **«Asistente IA»** (i18n Framework / host, p. ej. `userMenu.chatAssistant`).
- Navegación a pantalla/ruta dedicada del chat (norma Framework: Should `/chat-assistant`).
- Web: puede respetar la preferencia «abrir en nueva solapa» del avatar cuando el producto abra el chat en pestaña aparte.
- Mobile Capacitor: **siempre in-app** (full screen); sin `window.open` para este flujo.

### 3.2 Ayuda externa (URL)

**Fuera de alcance del MVP.** Partes habilita **solo** chat documental in-app. No hay ítem de ayuda por URL en el avatar en esta versión (`showExternalHelp: false` / no montar).

Puede retomarse como Should post-MVP sin cambiar el canal Must del chat.

### 3.3 Quién lo ve

Disponible para **todo usuario autenticado** con sesión Partes usable (asistente, supervisor, cliente), desde cualquier pantalla post-login del shell.

No requiere ítem en el menú lateral `pq_menus`.

### 3.4 Decisiones cerradas (2026-08-01)

| ID | Decisión |
|----|----------|
| **D-AI-01** | MVP = **solo chat in-app**. Sin ayuda externa por URL. |
| **D-AI-02** | Corpus Framework vía **contrato de adopción del host** (manifest/paths): une corpus Partes + corpus GEN del Framework **sin duplicar** manuales GEN en el repo producto y **sin RAG Must**. Ver §4.2. |
| **D-AI-03** | **BYOK obligatorio** para usar el chat: sin credencial LLM válida → gate Preferencias (no se envían turnos). En demos locales se usa credencial de prueba. |
| **D-AI-04** | OpenSpec del producto = **SPEC-008** (nuevo), adopción GEN-21 + corpus Partes; no absorber en SPEC-006. |

---

## 4. Qué puede consultar el usuario

### 4.1 Corpus Partes (ayuda del sistema)

Fuente canónica ya existente:

`docs/99-manual-usuario/`

| Documento | Contenido orientativo |
|-----------|------------------------|
| `Partes-Atencion.md` | Visión del módulo, menú, perfiles, mapa de pantallas |
| `SPEC-002-…` | Login, perfil, acceso |
| `SPEC-003-…` | Maestros / Archivos |
| `SPEC-004-…` | Carga diaria |
| `SPEC-005-…` | Proceso masivo |
| `SPEC-006-…` | Dashboard e informes (incl. Paquete de Horas) |
| `SPEC-007-…` | App móvil Capacitor |

Reglas del corpus: lenguaje de uso; errores visibles; **sin** detalle de implementación (código, DDL, SP, APIs).

### 4.2 Corpus Framework (generalidades) — decisión D-AI-02

**Sugerencia adoptada:** el host Partes provee el corpus al runtime del Asistente mediante el **contrato de adopción** del Framework (SPEC-001-21 Q7 / SPEC-001-99): un **manifest** (o paths/endpoint de contexto) que declara **dos orígenes**:

| Origen | Dónde vive | Quién lo mantiene |
|--------|------------|-------------------|
| **Partes** | `docs/99-manual-usuario/` de este repo | Producto Partes |
| **Framework GEN** | Corpus GEN del Framework (paquete / paths versionados del runtime GEN; no copiar a mano al producto) | Framework |

Reglas:

- Partes **no** duplica los manuales GEN dentro de su `99-manual-usuario`.
- Framework **no** impone RAG/embeddings Must: el binding concreto (manifest, `ChatCorpusProvider`, install) lo detalla SPEC-008 / TR.
- El GEN **siempre** se incluye junto al corpus de negocio (SPEC-001-99 Q7).
- El diálogo (`reply`) explica en el locale de app; el corpus puede estar en español canónico.

En Partes, el asistente debe poder responder también sobre:

- cómo funciona el **shell** (menú, avatar, idioma, cerrar sesión);
- **preferencias** / configuración LLM (BYOK);
- patrones comunes de **grillas / pivot / plantillas** que el módulo reutiliza;
- límites de lo que el chat **no** hace (no opera el sistema).

### 4.3 Fuera del alcance de respuesta

El asistente **no** debe:

- inventar pantallas o reglas no documentadas en el corpus;
- ejecutar acciones de negocio (alta/edición de tareas, masivo, ABM);
- exponer secretos, tokens o detalle técnico interno;
- sustituir al soporte humano cuando el corpus no alcanza.

---

## 5. Experiencia de uso (resumen)

| Tema | Norma para Partes |
|------|-------------------|
| Entrada | Ítem **Asistente IA** en `UserAvatarMenu` (`showChatAssistant: true`) |
| Pantalla | Chat documental (cabecera, bienvenida, hilo, envío) según Framework `21` / SPEC-001-21 |
| Bienvenida | Copy propio de Partes (producto); tono operativo y breve |
| Idioma de respuesta | Locale de la app del usuario (`users.locale`; fallback `es`); **no** el idioma del prompt |
| Sin LLM configurado | **BYOK obligatorio (D-AI-03):** bloquear envío; empty canónico + CTA a Preferencias |
| Preferencias LLM | Modal desde el chat / avatar según Framework `08` + `16` |
| Ayuda externa URL | **No** en MVP (`showExternalHelp` deshabilitado) |
| i18n / testids | Prefijos Framework `chatAssistant.*` + testids estables |
| Perfil Partes | Botón/panel ya existente; **no** mezclar con el ítem Asistente IA |

Estado actual del host (referencia): el shell de Partes ya usa `UserAvatarMenu` para contraseña, logout y (web) nueva solapa; **aún no** habilita `showChatAssistant` ni ruta de chat. Esta definición cierra el **qué** de producto para implementarlo.

---

## 6. Alcance MVP vs evolución

### En alcance (MVP de esta capacidad)

- Ítem avatar **Asistente IA** → **solo** chat documental in-app (D-AI-01).
- Corpus = manuales Partes + GEN Framework vía **manifest de adopción del host** (D-AI-02).
- **BYOK obligatorio**; gate Preferencias sin credencial (D-AI-03).
- OpenSpec: **SPEC-008** (D-AI-04).
- Degradación elegante si el chat no puede responder (mensaje claro; sin romper el shell).
- Mobile: mismo ítem; chat in-app (cuando mobile esté en el alcance activo del producto).

### Fuera de alcance (este documento)

- Ayuda externa por URL en el avatar.
- Smart Capture embebido en carga diaria / audio / imagen para **registrar** tareas.
- RAG propietario avanzado / embeddings PaqSystems como Must (salvo lo que ya entregue el Framework).
- Historial de conversaciones en servidor (si Framework lo deja diferido).
- Sustituir el botón de perfil Partes por el asistente.
- Redefinir el catálogo completo del menú avatar (sigue siendo norma GEN).
- Copiar/forkear manuales GEN dentro de `docs/99-manual-usuario` del producto.

---

## 7. Impacto esperado en artefactos

Cuando se autorice Open-Spec / implementación:

| Artefacto | Acción esperada |
|-----------|-----------------|
| **SPEC-008** (+ HU/TR) | Adopción GEN-21: avatar chat, ruta in-app, corpus Partes+GEN, BYOK, i18n |
| Shell FE | `showChatAssistant: true`; **sin** `showExternalHelp`; ruta `/chat-assistant` (o la que fije el SPEC) |
| Corpus | Manifest host: `docs/99-manual-usuario` Partes + paths GEN Framework |
| Manuales | Nota en `Partes-Atencion.md`: cómo abrir el Asistente IA |
| Mobile | Chat in-app; evaluar policy si aplica |

---

## 8. Decisiones cerradas (referencia)

| ID | Pregunta | Decisión (2026-08-01) |
|----|----------|------------------------|
| D-AI-01 | ¿Solo chat in-app o también URL? | **Solo chat in-app** |
| D-AI-02 | ¿Cómo se aporta el corpus Framework? | **Manifest/contrato de adopción del host**: Partes + GEN, sin duplicar GEN, sin RAG Must |
| D-AI-03 | ¿BYOK obligatorio? | **Sí** — gate Preferencias sin clave |
| D-AI-04 | ¿Qué SPEC? | **SPEC-008** (nuevo) |

---

## 9. Checklist de definición (producto)

- [x] Distinción chat documental vs Smart Capture vs perfil avatar
- [x] Acceso desde menú avatar
- [x] Corpus Partes = `docs/99-manual-usuario`
- [x] Inclusión de generalidades Framework por adopción (sin duplicar)
- [x] Fronteras: no opera el sistema; no inventa fuera del corpus
- [x] Mobile: in-app
- [x] Cierre D-AI-01…04
- [x] Generación SPEC-008 (Parte A) — [`SPEC-008-asistente-ia-chat-documental.md`](../../05-open-spec/100-SistemaPartes/SPEC-008-asistente-ia-chat-documental.md)
- [x] Generación HU-008 (Parte B + B1) — [`HU-008-asistente-ia-chat-documental.md`](../../03-historias-usuario/100-SistemaPartes/HU-008-asistente-ia-chat-documental.md)
- [ ] TR-008 / implementación

---

## 10. Resultado esperado

Con este documento, Partes deja explícito que el **Asistente IA del avatar** es la puerta de **ayuda orientativa** (solo chat in-app) del producto + Framework, con BYOK obligatorio y SPEC-008 como vehículo Open-Spec, y que la **carga con IA** sigue siendo evolución separada.
