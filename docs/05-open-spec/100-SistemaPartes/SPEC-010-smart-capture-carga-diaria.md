# SPEC-010 – Smart Capture en carga diaria (modal de tarea)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-010 |
| Título | Smart Capture (asistente operativo GEN-03) en el modal de alta/edición de tarea — Carga diaria |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-08-03 |
| HU relacionada(s) | [HU-010-smart-capture-carga-diaria](../../03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md) |
| TR relacionada(s) | [TR-010-smart-capture-carga-diaria](../../04-tareas/100-SistemaPartes/TR-010-smart-capture-carga-diaria.md) |
| Depende de | [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md), [SPEC-003](./SPEC-003-maestros-y-catalogos.md), [SPEC-004](./SPEC-004-operacion-carga-diaria.md), [SPEC-008](./SPEC-008-asistente-ia-chat-documental.md) (BYOK / timeout LLM host); Framework GEN-03 / SPEC-001-03 (docs en `PaqSuite-IA-FRAMEWORK`); provider GEN-16 |
| Fuentes | [`14-smart-capture.md`](../../02-producto/Sistema-Partes-IA/14-smart-capture.md) (D-SC-01…20); [`05-operacion-diaria-y-supervision.md`](../../02-producto/Sistema-Partes-IA/05-operacion-diaria-y-supervision.md); Framework [`03-asistente-inteligente-smart-capture.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/03-asistente-inteligente-smart-capture.md) |

---

## 1. Resumen ejecutivo

- **Problema:** cargar una tarea escribiendo campo a campo es lento cuando el usuario ya tiene la información en lenguaje natural, dictado o imagen.
- **Resultado esperado:** en el **modal** de alta/edición de **Carga diaria** (web), el asistente/supervisor puede usar **Smart Capture** (texto, audio, imagen) para **proponer** datos sobre el formulario; el sistema valida, resuelve ambigüedades con elección numerada, confirma fechas futuras **antes de aplicarlas al draft**, y graba solo con intención explícita (keywords o botón Guardar), con las **mismas reglas de dominio** que SPEC-004. Adopta GEN-03; no redefine el panel ni el contrato genérico de turno.

---

## 2. Alcance

### 2.1 En alcance

- Montar el **panel Smart Capture GEN** (`SmartCapturePanel` / helpers `@paqsuite/react-core`) **dentro del Popup** de alta/edición de tarea en Carga diaria, **debajo** del formulario (D-SC-01, D-SC-03).
- **No** montar SC en la grilla, toolbar Excel, informes, maestros ni proceso masivo (D-SC-02).
- Modalidades **texto**, **audio** (Web Speech) e **imagen** (límites GEN: hasta 4 × 2 MB) en web (D-SC-09).
- Endpoint de **turno operativo** del host Partes (shape GEN: `contractVersion`, `message`, `modality`, `credentialId`, `draftContext`, `pendingChoice`, `images`) con envelope estándar; `actions` tipadas que el FE aplica al draft del modal.
- Lookups de **cliente**, **asistente** (si aplica) y **tipo de tarea** por **código y descripción** (D-SC-12); ambigüedad → lista numerada + `pendingChoice` (D-SC-05, D-SC-06).
- Defaults: asistente = sesión (no supervisor) / seleccionable (supervisor); tipo = default de maestros si no se indica; `sinCargo` / `presencial` = **falso** si no se indica (§4.3).
- Sin orden obligatorio de campos (D-SC-04).
- Confirmación de **fecha > hoy** **antes de aplicar al draft** (D-SC-07, D-SC-16).
- Keywords de grabación canónicas + sinónimos (§4.6); misma validación/persistencia que Guardar UI (SPEC-004).
- Tarea **cerrada** → panel SC **deshabilitado** (D-SC-13).
- BYOK obligatorio; misma credencial / Preferencias que chat documental; **timeout LLM = el del host chat** (D-SC-11, D-SC-14).
- Hint inicial del panel: **texto fijo Partes vía i18n** (D-SC-15).
- Solo **web**; exclusión mobile v1 (D-SC-10 / SPEC-007).
- i18n + `data-testid` estables (prefijo acordado en TR, p. ej. `smartCapture.*` GEN + claves Partes).

### 2.2 Fuera de alcance

- Redefinir UI GEN del panel, contrato genérico de turno o paquete Framework.
- Smart Capture en grilla, listados, informes, dashboard, maestros o proceso masivo (SPEC-005).
- Usar el **chat documental** (SPEC-008) para grabar o mutar tareas.
- Rellenar Excel / importación (SPEC-009) vía IA.
- Mobile / Capacitor v1.
- RAG, historial de hilo SC en servidor, motor de voz distinto de Web Speech.
- Inventar campos de tarea fuera del modelo SPEC-004.
- Ayuda externa por URL; ítem de menú lateral nuevo.
- Timeout LLM específico Partes-SC (queda el del chat).

---

## 3. Actores y contexto

| Actor | Smart Capture en carga |
|-------|------------------------|
| Asistente (`esSupervisor = false`) | Sí — propietario forzado a sesión |
| Supervisor (`esSupervisor = true`) | Sí — puede indicar asistente |
| Cliente | **No** (no opera carga; API/UI deniegan) |

**Precondiciones**

- Sesión Partes usable (SPEC-002); maestros usables (SPEC-003); modal de carga diaria operativo (SPEC-004).
- Credencial LLM BYOK válida para enviar turnos (mismo gate que SPEC-008). Sin ella: UI GEN `configurationRequired` / «Ir a Preferencias»; BE no invoca modelo.
- Capacidad / paquetes Smart Capture del Framework disponibles en el host (supuesto de adopción).

---

## 4. Comportamiento funcional

### 4.1 Ubicación UX (Must — D-SC-01…03, D-SC-10, D-SC-13, D-SC-15)

| ID | Norma |
|----|--------|
| R-SC-01 | Panel SC **solo** dentro del modal de alta/edición de tarea (Carga diaria), **debajo** del formulario. |
| R-SC-02 | **No** aparece en la grilla ni en otras pantallas de este MVP. |
| R-SC-03 | Alta y edición comparten el mismo panel sobre el **draft** del formulario abierto. |
| R-SC-04 | Si `cerrado = true` en la tarea en edición → SC **deshabilitado** (sin turnos, dictado ni imágenes). |
| R-SC-05 | Hint inicial: clave i18n Partes (texto fijo); no parámetro `PQ_PARAMETROS` en este alcance. |
| R-SC-06 | Solo web; en native/mobile **no** montar el panel. |
| R-SC-07 | Sin LLM válido: gate Preferencias; resto no usable (norma GEN). |

### 4.2 Flujo principal (Must)

1. Usuario abre **Nueva tarea** o **Editar** (tarea abierta) en Carga diaria.
2. Expande el panel SC (colapsable GEN); ve hint Partes.
3. Envía texto, dicta o adjunta imagen(es) (parcial o completo).
4. Host llama al endpoint de turno con `draftContext` (snapshot del form) y, si aplica, `pendingChoice` previo.
5. Respuesta: `replyText` + `actions` + opcional `pendingChoice`.
6. FE aplica `actions` al draft del modal (sin reinterpretar negocio).
7. Usuario corrige a mano si quiere; puede seguir turnos.
8. Grabación: keyword SC (§4.6) **o** botón Guardar → mismas validaciones SPEC-004 → persistencia → cierre modal / refresco grilla con filtros vigentes (como hoy tras Guardar).

### 4.3 Campos, defaults y orden (Must — D-SC-04, D-SC-12)

**Obligatorios para grabar** (SC o UI):

| Campo form | Semántica |
|------------|-----------|
| Cliente | Lookup código **y** descripción/nombre |
| Fecha | Fecha de trabajo del parte |
| Duración | Múltiplo del tramo parametrizable (SPEC-004) |
| Descripción (`observacion`) | Texto no vacío |

**Opcionales / defaults:**

| Campo | Regla |
|-------|--------|
| Asistente | No supervisor: **siempre** sesión (si el mensaje pide otro → no aplicar / error de permiso). Supervisor: si no se indica → sesión; si se indica → lookup; visible en UI. |
| Tipo de tarea | Si no se indica → **tipo default** de maestros (genérico). Debe ∈ universo del cliente (SPEC-003/004). Lookup código **y** descripción. |
| Sin cargo | Default **falso** |
| Presencial | Default **falso** |

| ID | Norma |
|----|--------|
| R-SC-08 | **Sin orden obligatorio** de campos: en un turno se aplica lo resoluble; lo ambiguo o faltante queda pendiente hasta completar al guardar. No se bloquea el resto del turno solo por falta de cliente (especialización Partes vs P7 GEN genérico de “raíz primero”). |
| R-SC-09 | No inventar datos faltantes. |
| R-SC-10 | Lookups por código y/o descripción (texto / STT / visión). |

### 4.4 Ambigüedad (Must — D-SC-05, D-SC-06)

| ID | Norma |
|----|--------|
| R-SC-11 | >1 coincidencia en cliente, asistente o tipo → **no** elegir en silencio; lista numerada en el hilo; usuario elige por número (u opción clara). |
| R-SC-12 | Durante `pendingChoice`, conservar draft aplicado y diferidos GEN; reenviar `pendingChoice` en el turno siguiente. |
| R-SC-13 | Tope orientativo de opciones visibles: norma GEN (~10). |
| R-SC-30 | **0** coincidencias → pedir refinar; **1** segura → aplicar; **>1** → `needsChoice`. Scoring detallado en TR. |

### 4.5 Fecha futura (Must — D-SC-07, D-SC-16)

| ID | Norma |
|----|--------|
| R-SC-14 | Si la fecha propuesta es **estrictamente posterior** a la fecha del día del sistema/instalación → pedir confirmación en el hilo **antes de aplicarla al draft**. |
| R-SC-15 | Sin confirmar → la fecha futura **no** queda en el formulario. |
| R-SC-16 | Fecha = hoy o pasada: se puede aplicar sin esa confirmación extra (sujeto a demás reglas SPEC-004). |

### 4.6 Grabación por keyword (Must — D-SC-08)

Intención de persistir vía SC (case-insensitive; espacios normalizados como guía de prompt):

**Canónicas:** `guardar`, `confirmar`, `procesar`, `grabar`.

**Sinónimos Must:** `registrar`, `aceptar`, `ok`, `dale`, `guardar tarea`, `confirmar tarea`.

| ID | Norma |
|----|--------|
| R-SC-17 | Las keywords son **señales de intención para el modelo / contrato** (el turno debe emitir `action` de grabación, p. ej. `save`/`submit`). **Prohibido** auto-grabar por *matcher* literal de substring sobre el mensaje del usuario (evita falsos positivos tipo «confirmar reunión…»). |
| R-SC-18 | Persistencia Must: el **FE aplica** la action de grabación y llama al **mismo API** de alta/edición que el botón **Guardar** del modal (SPEC-004). El BE del turno **no** sustituye ese POST/PUT como vía distinta de dominio. |
| R-SC-19 | Ante grabación: validar **obligatorios + reglas SPEC-004** sobre el **draft resultante** (valores ya en el form + lo aplicado por SC). Si OK → persistir; si faltan datos → indicar qué falta; **no** grabar parcial. |
| R-SC-27 | Botón Guardar del modal permanece válido y equivalente en validación/persistencia. |
| R-SC-28 | **Conflictos / overwrite** de campos ya cargados (incl. cambio de cliente con draft poblado): en este MVP Partes se **aplican al draft sin** confirmación destructiva extra tipo GEN `confirmChangeRoot` (decisión A1). El usuario ve el cambio en el formulario y puede corregir a mano. |

### 4.6.1 Alta vs edición (Must — A1)

| Modo | Norma |
|------|--------|
| **Alta** | El draft parte de defaults de UI/SPEC-004; SC puede completar parcial o total. Al grabar, deben estar todos los obligatorios §4.3. |
| **Edición** | El draft **ya trae** los datos de la tarea abierta. SC solo necesita aportar lo que el usuario **desea modificar**; **no** es obligatorio re-dictar/reenviar todos los campos. Al grabar (keyword/action save o Guardar), se valida el draft completo: no debe faltar ningún obligatorio. |

| ID | Norma |
|----|--------|
| R-SC-29 | Edición: cambios parciales sobre draft preexistente; omitir un campo en el mensaje **no** lo borra ni lo resetea a default. |

Confirmación de **fecha futura** (R-SC-14…16): palabras de aceptación del hilo según norma GEN (`sí`, `confirmo`, equivalentes del panel); distintas de las keywords de grabación §4.6.

### 4.7 Modalidades y BYOK (Must — D-SC-09, D-SC-11, D-SC-14)

| ID | Norma |
|----|--------|
| R-SC-20 | Texto, audio (Web Speech) e imagen según cupos GEN. |
| R-SC-21 | `credentialId` = config LLM activa del SelectBox del panel (misma BYOK que chat). |
| R-SC-22 | Timeout HTTP/LLM del turno SC = **el configurado para el host del chat documental** (sin clave/env aparte Partes-SC). |

### 4.8 Reglas numeradas (resumen)

| ID | Regla |
|----|--------|
| R-SC-01…07 | Ubicación UX, cerrado, hint, mobile, BYOK gate |
| R-SC-08…10 | Campos, defaults, sin orden, lookups |
| R-SC-11…13 | Ambigüedad / pendingChoice |
| R-SC-14…16 | Fecha futura al aplicar draft |
| R-SC-17…19, 27–28 | Grabación: LLM→save; FE→API Guardar; sin confirm overwrite; alta vs edición parcial |
| R-SC-20…22 | Modalidades + BYOK + timeout chat |
| R-SC-23 | Adopta GEN-03; no reimplementa el motor del panel. |
| R-SC-24 | Cliente funcional no usa SC de carga; API deniega. |
| R-SC-25 | Persistencia de negocio vía mismos mecanismos que SPEC-004 (SP / bridge Must BASE). |
| R-SC-26 | Tras grabar OK: misma UX post-Guardar (cerrar modal según diseño actual + refrescar grilla con filtros vigentes). |
| R-SC-29 | Edición: SC aporta solo cambios deseados; draft preexistente; validación integral al grabar. |
| R-SC-30 | Lookup catálogo: **0** coincidencias → pedir refinar (`needsRefine` / mensaje); **1** segura → aplicar; **>1** → `needsChoice` (lista numerada). Detalle de scoring en TR. |

---

## 5. Criterios verificables

- [ ] Panel SC visible solo dentro del modal de tarea en Carga diaria (web); no en grilla ni otras pantallas Must.
- [ ] Alta y edición (tarea abierta) comparten panel; hint i18n Partes presente.
- [ ] Tarea cerrada: SC deshabilitado (si el modal pudiera abrirse).
- [ ] Sin LLM: gate Preferencias; no se envían turnos.
- [ ] Turno texto aplica al menos un campo resoluble al draft (p. ej. descripción o duración).
- [ ] Cliente/tipo/asistente ambiguos → lista numerada; elección actualiza draft; resto de datos se conserva.
- [ ] Fecha futura no se aplica al draft sin confirmación en el hilo; tras confirmar, sí.
- [ ] Keyword / intención de grabación produce `save` vía turno (no auto-save por substring); FE persiste con el mismo API que Guardar.
- [ ] Keyword de grabación con faltantes en el draft → mensaje; 0 persistencia.
- [ ] En **edición**, un turno que solo cambia un campo (p. ej. duración) deja el resto del draft intacto; al grabar valida obligatorios completos.
- [ ] Cambio de cliente (u otro campo) vía SC se aplica al draft **sin** diálogo de confirmación destructiva extra.
- [ ] Asistente no supervisor no puede dejar grabada una tarea de otro asistente vía SC.
- [ ] Tipo omitido en **alta** → default de maestros; `sinCargo`/`presencial` omitidos → falso. En **edición**, omitir tipo/flags no pisa los valores ya cargados salvo que el turno los cambie explícitamente.
- [ ] Lookup 0 / 1 / N: refinar / aplicar / lista numerada.
- [ ] Mobile: panel no montado / policy excluye.
- [ ] Timeout del turno SC alineado al del chat (misma config).
- [ ] Tests (Feature turno y/o unit FE apply-actions + E2E smoke modal) cubren al menos: apply campo, needsChoice, fecha futura, save vía FE API, edición parcial, cerrado disabled.

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | Endpoint turno Partes (path definitivo en TR; sugerido orientativo `POST /api/v1/partes/tareas/asistente/turn`); orquestación LLM + resolución catálogos + emisión `actions` / `pendingChoice`; authz carga diaria; reutilizar timeout chat |
| Frontend | Montar `SmartCapturePanel` en Popup de `CargaDiariaPage`; mapear `actions` → state del form; `disabled` si `cerrado`; i18n hint; no montar en `isNativeApp()` |
| DB / SP | Reutilizar upsert/cerrar de tareas SPEC-004; sin tablas nuevas Must salvo auditoría GEN si el Framework la exige |
| Config | BYOK existente; timeout = chat; sin flag SC aparte salvo gate capacidad GEN si aplica |
| OpenAPI | Documentar endpoint de turno (tag acorde, p. ej. Partes Tareas / Smart Capture) |
| Docs | Manual usuario: cómo usar SC en carga; distinguir de Asistente IA avatar |

`draftContext` (opaco GEN; shape Partes en TR): snapshot de ids/códigos/textos del form (cliente, asistente, tipo, fecha, duración, flags, observación, `cerrado`, modo alta|edición, `id` si edición).

Familias de `action` orientativas (nombres finales en TR): `setField`, lookups/`needsChoice`/`needsRefine`, `confirmFutureDate`, `save`/`submit` (**FE → API Guardar**), `noop`/`help`.

**Cierre A1 (2026-08-03):** keywords = guía LLM → action save; persistencia = FE + API SPEC-004; sin confirm overwrite de cliente; edición = cambios parciales sobre draft existente.

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| Paquete SC en `@paqsuite/react-core` / laravel-core | Supuesto: API panel + helpers disponibles (GEN-03 cerrado conceptual; smoke Framework). Si falta pieza, TR declara gap y mínimos. |
| UI actual oculta Editar en tareas cerradas | D-SC-13 sigue Must como defensa; E2E puede mockear `cerrado` o unit del `disabled`. |
| Calidad LLM / STT | No inventar datos; ambigüedad → choice; errores visibles en hilo. |
| Especialización vs P7 GEN (orden raíz) | Explicitada en R-SC-08; no bloquear turno por falta de cliente. |
| Conflicto SPEC-004 “fecha futura sin bloqueo duro” | SC **añade** confirmación al **aplicar draft** (D-SC-07); no contradice el alta manual sin SC. |
| Endpoint path exacto | TR; OpenAPI al implementar. |
| Overwrite de cliente / campos | A1: **sin** confirmación destructiva extra; aplicar al draft. |
| Keyword vs substring | A1: intención vía LLM/action; no matcher literal. |
| Persistencia save | A1: FE aplica save → mismo API que Guardar UI. |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-03 | Parte A: SPEC-010 desde `14-smart-capture.md` (D-SC-01…16) + adopción GEN-03. |
| 2026-08-03 | A1: LLM→save (no substring); FE→API Guardar; sin confirm overwrite; edición parcial + validación integral al grabar; lookup 0/1/N. |
| 2026-08-03 | Parte B/B1: enlace HU-010. |
| 2026-08-03 | Parte C: enlace TR-010. |
