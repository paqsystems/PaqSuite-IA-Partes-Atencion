# HU-010-update – Smart Capture pinta el formulario del modal

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-010-update |
| Título | Smart Capture — los datos del turno se ven en los controles |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente de Revisión |
| Última actualización | 2026-09-15 |
| SPEC origen | [SPEC-010-smart-capture-carga-diaria-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria-update.md) (base: [SPEC-010](../../../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md)) |
| TR relacionada(s) | [TR-010-smart-capture-carga-diaria-update](../../../04-tareas/updates/100-SistemaPartes/TR-010-smart-capture-carga-diaria-update.md) |
| HU base | [HU-010-smart-capture-carga-diaria](../../100-SistemaPartes/HU-010-smart-capture-carga-diaria.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Fuente | `00-ControlCalidad-PQ` |
| Fecha | 09/08/2026 |
| Control | #3 |
| Ítem | Cargas de Partes Diarios — Implementar IA |
| Evidencia | `CC33-PQ-2026-09-15.png` (hilo: «asistente PQ cliente LACAPOL duracion 1:25 hrs»; controles en «Seleccionar…») |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente de Revisión |

---

## Narrativa (delta)

Como asistente o supervisor  
quiero que lo que Smart Capture entiende **aparezca en los campos del modal**  
para revisar y guardar sin reingresar cliente, asistente ni duración a mano.

---

## Alcance

- Completar CA-05 de HU-010: apply visible, no solo `replyText`.
- Apply parcial: campos resolubles aunque la duración no sea múltiplo del tramo (caso 85 min / `1:25`).

## Fuera de alcance

- Redefinir GEN-03, grabación por keyword, mobile, SC fuera del modal.

---

## Criterios de aceptación

- [ ] **CA-CC3-07** Tras un turno con cliente y asistente únicos, esos SelectBox muestran las opciones (código + descripción); no quedan en placeholder.
- [ ] **CA-CC3-08** Si la duración no es múltiplo del tramo, el hilo lo informa y **no** borra ni impide los campos ya aplicados.
- [ ] **CA-CC3-09** Observación / fecha / tipo resolubles se ven en sus controles DX del mismo modal.

### Gherkin

```gherkin
Scenario: El hilo no basta sin controles
  Given modal Nueva tarea y LLM configurado
  When envío "asistente PQ cliente LACAPOL duracion 1:25 hrs"
  Then el SelectBox de cliente muestra LACAPOL si el lookup es único
  And el SelectBox de asistente muestra PQ si el lookup es único y soy supervisor
  And la duración inválida no deja esos campos vacíos
```

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-09-15 | Parte G CC-PQ #3 (09/08/2026). |
