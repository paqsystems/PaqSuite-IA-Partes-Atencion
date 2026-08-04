# HU-010 – Smart Capture en el modal de carga de tarea

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-010 |
| Título | Completar y grabar tareas de Carga diaria con Smart Capture (texto, audio, imagen) |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-08-03 |
| SPEC origen | [SPEC-010-smart-capture-carga-diaria](../../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) |
| TR relacionada(s) | [TR-010-smart-capture-carga-diaria](../../04-tareas/100-SistemaPartes/TR-010-smart-capture-carga-diaria.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-010 | Dónde en esta HU |
|--------------------------------|------------------|
| Panel solo en modal alta/edición (R-SC-01…03, D-SC-01…03) | Alcance; CA-01 |
| No en grilla / otras pantallas (R-SC-02) | Fuera de alcance; CA-01 |
| Hint i18n Partes (R-SC-05, D-SC-15) | CA-01 |
| Tarea cerrada → SC disabled (R-SC-04, D-SC-13) | CA-02 |
| Sin LLM → Preferencias (R-SC-07, D-SC-11) | CA-03 |
| Modalidades texto/audio/imagen (R-SC-20, D-SC-09) | Alcance; CA-04 |
| Timeout = chat (R-SC-22, D-SC-14) | Supuestos; CA-14 |
| Obligatorios + defaults §4.3 (R-SC-08…10) | CA-05, CA-06; R-SC-08…10 |
| Sin orden obligatorio (D-SC-04) | R-SC-08; CA-05 |
| Lookup 0/1/N (R-SC-11, R-SC-30) | CA-07 |
| pendingChoice conserva draft (R-SC-12) | CA-07 |
| Fecha futura al aplicar draft (R-SC-14…16) | CA-08 |
| LLM→save; no substring (R-SC-17, D-SC-17) | CA-09 |
| FE→API Guardar (R-SC-18, D-SC-18) | CA-09, CA-10 |
| Validación draft completo al grabar (R-SC-19) | CA-10 |
| Sin confirm overwrite (R-SC-28, D-SC-19) | CA-11 |
| Edición parcial (R-SC-29, D-SC-20) | CA-12 |
| No supervisor no carga como otro (R-SC-24 / §4.3) | CA-13 |
| Mobile excluido (R-SC-06, D-SC-10) | Fuera de alcance; CA-15 |
| Cliente no usa SC (R-SC-24) | Actores; CA-16 |
| Adopta GEN-03 (R-SC-23) | Alcance; Fuera de alcance |
| Criterios verificables SPEC §5 | CA-01…16 |

---

## Narrativa

Como asistente o supervisor de Partes  
quiero usar Smart Capture dentro del modal de alta o edición de una tarea en Carga diaria  
para dictar o describir el trabajo en lenguaje natural (o con imagen) y que el sistema proponga los datos en el formulario, de modo que solo revise y confirme la grabación sin abandonar el proceso manual.

---

## Contexto funcional

La carga campo a campo es lenta cuando la información ya está en una frase, un audio o una imagen. Esta historia adopta el **asistente operativo Smart Capture (GEN-03)** embebido **debajo del formulario** del modal de Carga diaria: interpreta, propone al draft, resuelve ambigüedades con elección numerada, pide confirmación antes de aplicar fechas futuras y graba solo con intención explícita (action `save` vía turno o botón Guardar), con las **mismas reglas** que la carga manual (SPEC-004).

Distinto del Asistente IA documental del avatar (SPEC-008 / HU-008) y de la importación Excel (SPEC-009 / HU-009).

### Precondiciones (SPEC §3)

- Sesión Partes usable (asistente o supervisor).
- Maestros usables (clientes, tipos, asistentes).
- Modal de Carga diaria operativo.
- Credencial LLM BYOK válida para **enviar** turnos; sin ella, gate Preferencias.
- Paquete / panel Smart Capture GEN disponible en el host.

### Actores

| Actor | Puede usar SC en carga |
|-------|------------------------|
| Asistente (`esSupervisor = false`) | Sí — propietario = sesión |
| Supervisor (`esSupervisor = true`) | Sí — puede indicar asistente |
| Cliente | No |

---

## Alcance incluido

- Panel Smart Capture GEN **solo** dentro del Popup de **Nueva tarea** / **Editar** (tarea abierta), debajo del formulario.
- Hint inicial con **texto fijo Partes** (i18n).
- Modalidades: **texto**, **audio** (Web Speech), **imagen** (límites GEN).
- Turno operativo host: propuesta de campos al draft; lookups por código y descripción; ambigüedad numerada; confirmación de fecha futura **antes de aplicar al draft**.
- Grabación: intención vía modelo → action `save`; el FE llama al **mismo API** que Guardar; validación de obligatorios sobre el draft resultante.
- **Alta:** defaults (tipo default si no se indica; sin cargo / presencial = falso; asistente = sesión si aplica).
- **Edición:** solo se envían/aplican los cambios deseados; el resto del draft permanece; al grabar se valida todo.
- Overwrite de campos (incl. cliente) **sin** confirmación destructiva extra.
- Solo **web**; i18n + testids estables (detalle TR).

---

## Fuera de alcance

- Montar SC en la grilla, toolbar Excel, informes, maestros o proceso masivo.
- Redefinir el panel o el contrato genérico GEN-03.
- Grabar tareas desde el chat documental (avatar).
- Rellenar Excel / importación con IA.
- Mobile / Capacitor v1.
- Matcher literal de substring para auto-grabar.
- Persistencia distinta al API de Guardar (vía “oculta” del turno).
- Confirmación tipo `confirmChangeRoot` al cambiar cliente.
- Timeout LLM específico Partes-SC (se reutiliza el del chat).
- RAG / historial SC en servidor / motor de voz distinto de Web Speech.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-SC-01 | SC vive solo en el modal de tarea de Carga diaria, debajo del form. |
| R-SC-02 | No aparece en grilla ni otras pantallas Must. |
| R-SC-03 | Alta y edición (abierta) comparten el mismo panel sobre el draft. |
| R-SC-04 | Tarea cerrada → panel deshabilitado. |
| R-SC-05 | Hint = texto fijo Partes (i18n). |
| R-SC-06 | No disponible en mobile v1. |
| R-SC-07 | Sin LLM válido → Preferencias; no se envían turnos. |
| R-SC-08 | Sin orden obligatorio de campos; se aplica lo resoluble. |
| R-SC-09 | No inventar datos faltantes. |
| R-SC-10 | Lookups por código y/o descripción. |
| R-SC-11 | >1 coincidencia → lista numerada; el usuario elige. |
| R-SC-12 | Durante elección se conserva el resto del draft. |
| R-SC-14 | Fecha > hoy → confirmar en el hilo **antes** de aplicar al draft. |
| R-SC-15 | Sin confirmar, la fecha futura no queda en el form. |
| R-SC-17 | Keywords guían al modelo; **prohibido** auto-save por substring. |
| R-SC-18 | `save` en FE → mismo API que botón Guardar. |
| R-SC-19 | Al grabar se valida el draft completo; sin grabación parcial. |
| R-SC-28 | Overwrite de campos al draft sin confirmación destructiva extra. |
| R-SC-29 | En edición, omitir un campo no lo borra ni lo resetea. |
| R-SC-30 | Lookup: 0 → refinar; 1 → aplicar; >1 → elegir. |
| R-SC-23 | Adopta GEN-03; no reimplementa el motor del panel. |
| R-SC-24 | Cliente funcional no usa SC de carga. |
| R-SC-26 | Tras grabar OK: misma UX post-Guardar (modal + refresco grilla con filtros vigentes). |

---

## Criterios de aceptación

- [ ] **CA-01** En Carga diaria (web), al abrir Nueva tarea o Editar (abierta), el usuario ve el panel SC debajo del formulario con hint i18n Partes; el panel **no** aparece en la grilla ni fuera de ese modal.
- [ ] **CA-02** Si la tarea está **cerrada**, el panel SC está deshabilitado (sin envío de turnos / dictado / imágenes).
- [ ] **CA-03** Sin credencial LLM válida: se muestra el gate Preferencias y no se envían turnos.
- [ ] **CA-04** El usuario puede aportar entrada por texto, dictado (Web Speech) e imagen dentro de los límites GEN.
- [ ] **CA-05** Un turno con datos resolubles aplica al menos un campo al draft (p. ej. descripción o duración) sin exigir un orden fijo de campos.
- [ ] **CA-06** En **alta**, si no se indica tipo → queda el tipo default de maestros; si no se indican sin cargo/presencial → **falso**. Asistente no supervisor queda con propietario de sesión.
- [ ] **CA-07** Con >1 coincidencia de cliente/tipo/asistente → lista numerada; al elegir, se actualiza el draft y se conserva lo ya aplicado. Con 0 coincidencias → se pide refinar (sin inventar).
- [ ] **CA-08** Fecha estrictamente futura: no se aplica al draft hasta confirmar en el hilo (`sí` / `confirmo` o equivalentes GEN); tras confirmar, sí.
- [ ] **CA-09** La intención de grabar llega como action `save` del turno (no por detectar substring en el mensaje); el FE persiste con el **mismo API** que el botón Guardar.
- [ ] **CA-10** Con obligatorios incompletos en el draft, la grabación no persiste e indica qué falta; con draft completo válido, persiste y aplica la UX post-Guardar (cierre/refresco según carga diaria).
- [ ] **CA-11** Cambiar cliente (u otro campo) vía SC **actualiza el draft sin** diálogo de confirmación destructiva extra.
- [ ] **CA-12** En **edición**, un turno que solo modifica un campo deja el resto intacto; al grabar se validan todos los obligatorios del draft.
- [ ] **CA-13** Asistente no supervisor no puede dejar grabada una tarea de otro asistente vía SC.
- [ ] **CA-14** El timeout del turno SC es el mismo configurado para el chat documental del host.
- [ ] **CA-15** En mobile el panel SC de carga no se monta / no se ofrece.
- [ ] **CA-16** Usuario cliente no dispone de SC de carga (ni UI ni API de turno de este proceso).

### Escenarios Gherkin

```gherkin
Feature: Smart Capture en modal de Carga diaria
  Como asistente o supervisor
  Quiero completar el formulario de tarea con Smart Capture
  Para registrar dedicación más rápido sin salir del modal

  Scenario: Alta parcial por texto y grabación
    Given un asistente no supervisor en Carga diaria con LLM configurado
    And abre Nueva tarea
    When envía un mensaje con cliente único, fecha de hoy, duración válida y descripción
    And solicita guardar vía intención del asistente
    Then los campos aparecen en el formulario
    And se persiste la tarea con el mismo resultado que Guardar
    And el propietario es el asistente de sesión

  Scenario: Ambigüedad de cliente
    Given dos clientes cuyo nombre contiene "Acme"
    When el usuario dice "tarea para Acme"
    Then el hilo lista opciones numeradas
    And no se elige un cliente en silencio
    When el usuario elige "1"
    Then el draft toma ese cliente y conserva el resto ya aplicado

  Scenario: Fecha futura requiere confirmación al draft
    Given el día de sistema es 2026-08-03
    When el asistente propone fecha 2026-08-10
    Then la fecha no queda en el formulario hasta confirmar
    When el usuario confirma en el hilo
    Then la fecha 2026-08-10 queda en el draft

  Scenario: Edición solo cambia un campo
    Given una tarea abierta con todos los obligatorios cargados
    When el usuario edita y pide cambiar solo la duración a 45 minutos
    Then el resto de campos del draft permanece
    When guarda
    Then la tarea se actualiza con la nueva duración

  Scenario: No auto-guarda por substring
    Given el draft aún incompleto
    When el usuario escribe "confirmar reunión con el cliente mañana"
    Then no se dispara una grabación solo por la palabra confirmar
    And no se crea ni actualiza la tarea por ese solo hecho

  Scenario: Sin LLM
    Given no hay credencial LLM válida
    When abre el modal de tarea
    Then Smart Capture muestra el acceso a Preferencias
    And no se pueden enviar turnos

  Scenario: Cliente no usa SC de carga
    Given un usuario con identidad funcional cliente
    When opera el sistema en web
    Then no dispone del panel Smart Capture de Carga diaria

  Scenario: Mobile sin SC
    Given la app en modo native/mobile
    When abre carga de partes
    Then el panel Smart Capture no está montado
```

---

## Supuestos explícitos

- El panel y helpers GEN-03 (`SmartCapturePanel`, turno, `pendingChoice`, dictado, imágenes) están disponibles vía `@paqsuite/react-core` / contrato host.
- La UI actual puede ocultar Editar en tareas cerradas; CA-02 sigue Must como defensa.
- Path exacto del endpoint de turno y nombres finales de `actions` se fijan en TR.
- Scoring fino de lookup (fuzzy vs exacto) se detalla en TR sin cambiar 0/1/N.

---

## Preguntas abiertas

| # | Pregunta | Destino | Resolución |
|---|----------|---------|------------|
| 1 | Path del endpoint de turno | TR | **`POST /api/v1/partes/tareas/asistente/turn`** (TR-010) |
| 2 | Clave i18n exacta del hint | TR | **`partes.smartCapture.hint`** (TR-010) |
| 3 | Prefijo `data-testid` | TR | GEN `smartCapture.*` (+ host opcional) |

Cerradas en Parte C (TR-010).

---

## Riesgos de ambigüedad

| Riesgo | Mitigación en SPEC/HU |
|--------|------------------------|
| Confundir con chat documental | Narrativa + fuera de alcance; CA-16 vs avatar |
| Auto-save por “confirmar” en frase | CA-09 / R-SC-17 |
| Edición que pisa defaults | CA-06 vs CA-12 / R-SC-29 |
| Persistencia “en el turno” distinta | CA-09 / R-SC-18 |
| SC en grilla | CA-01 / R-SC-02 |

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-03 | Parte B: HU-010 desde SPEC-010 (post A1). |
| 2026-08-03 | Parte B1: trazabilidad, Gherkin y CA alineados a cierres A1 (save LLM/FE, edición parcial, sin confirm overwrite). |
| 2026-08-03 | Parte C: enlace TR-010; preguntas abiertas cerradas. |
