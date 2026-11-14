# HU-016 – Edición IA de informes existentes

Jira: [PIAPARTES-59](https://paqsystems.atlassian.net/browse/PIAPARTES-59)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-016 |
| Título | Prompt-to-Report IA sobre un informe ya cargado en el diseñador |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | **Especificado** (Parte C1 — 2026-11-06) |
| SPEC origen | [SPEC-016-prompt-to-report-edicion-informes](../../05-open-spec/100-SistemaPartes/SPEC-016-prompt-to-report-edicion-informes.md) |
| TR relacionada(s) | [TR-016-edicion-ia-informes-existentes-prompt-to-report](../../04-tareas/100-SistemaPartes/TR-016-edicion-ia-informes-existentes-prompt-to-report.md) · [PIAPARTES-60](https://paqsystems.atlassian.net/browse/PIAPARTES-60) |
| Norma GEN | Adoptar **GEN-15** y el diseñador existente; no reimplementar el motor de reportes |

## Narrativa

Como usuario con permiso `emission.design`  
quiero solicitar cambios en lenguaje natural sobre el informe que ya seleccioné y tengo abierto en el diseñador  
para modificar su diseño sin comenzar un informe nuevo y conservar el control antes de guardarlo.

## Contexto funcional

El usuario ya seleccionó un reporte generado y lo visualiza cargado en el diseñador DevExpress. La asistencia IA debe trabajar sobre ese informe existente, por ejemplo agregando un subtotal de horas, sin limitarse al wizard de creación de informes nuevos.

El usuario revisa el cambio dentro del diseñador y decide si lo persiste con Save o Save As. La IA no guarda automáticamente ni reemplaza el flujo GEN-15.

## Actores y precondiciones

| Actor / condición | Comportamiento |
|-------------------|----------------|
| Usuario con `emission.design` | Puede editar un informe existente y solicitar cambios a la IA |
| Usuario sin `emission.design` | No accede al diseñador ni a la asistencia IA |
| Informe existente | Debe estar seleccionado y cargado antes de solicitar el cambio |
| Mobile / native | Fuera de alcance |

## Alcance incluido

- Invocar la asistencia IA con un informe ya cargado.
- Solicitar cambios incrementales sobre el diseño existente.
- Usar el schema autorizado del proceso para interpretar campos.
- Preservar las partes del layout que no fueron solicitadas para cambio.
- Revisar el resultado antes de guardar.
- Guardar mediante Save o Save As del flujo GEN-15.
- Mantener operativo el flujo de creación de informes nuevos.
- Mantener disponible la edición manual si la IA no está disponible.

## Fuera de alcance

- Crear un informe nuevo como sustituto del flujo de edición.
- Reemplazar GEN-15, el diseñador, Emitir, filtros o dataset.
- Guardado automático desde la IA.
- Chat documental mutando informes.
- Smart Capture GEN-03 en esta historia.
- Unificación de credenciales BYOK GEN-16 con el sidecar.
- Soporte mobile.

## Reglas de negocio

| ID | Regla |
|----|-------|
| R-HU016-01 | La asistencia se ofrece sobre el informe existente que el usuario ya cargó, no únicamente sobre un informe en blanco. |
| R-HU016-02 | La IA utiliza el informe cargado y el schema autorizado del proceso; no agrega campos inexistentes. |
| R-HU016-03 | Un cambio incremental conserva el resto del diseño salvo solicitud explícita del usuario. |
| R-HU016-04 | El usuario revisa y confirma mediante Save o Save As; la IA no persiste automáticamente. |
| R-HU016-05 | La modificación real del informe existente es requisito funcional; informar una limitación técnica no constituye cumplimiento. |
| R-HU016-06 | Sin IA disponible, la edición manual del informe continúa funcionando. |
| R-HU016-07 | La capacidad de creación de informes nuevos no se rompe. |

## Criterios de aceptación

- [ ] **CA-016-01** Dado un informe ya seleccionado y cargado en el diseñador, el usuario con `emission.design` puede invocar la asistencia IA sin iniciar un informe nuevo.
- [ ] **CA-016-02** Al solicitar «Agregá subtotal de horas», el informe existente presenta un cambio visible y revisable.
- [ ] **CA-016-03** El cambio no elimina ni altera las partes no relacionadas del layout.
- [ ] **CA-016-04** El usuario puede continuar editando manualmente antes de guardar.
- [ ] **CA-016-05** Save y Save As persisten el resultado mediante el flujo GEN-15.
- [ ] **CA-016-06** Un pedido basado en un campo inexistente es rechazado o no agrega datos inventados.
- [ ] **CA-016-07** Un usuario sin `emission.design` no puede acceder al diseñador ni a la asistencia.
- [ ] **CA-016-08** Si la IA no está disponible, la edición manual sigue operativa y el usuario recibe una indicación clara.
- [ ] **CA-016-09** La creación de informes nuevos continúa funcionando.

## Escenarios Gherkin

```gherkin
Feature: Editar con IA un informe existente
  Como diseñador de informes con permiso emission.design
  Quiero pedir cambios sobre el reporte que ya tengo abierto
  Para modificarlo sin comenzar otro informe

  Scenario: Solicitar un cambio sobre un reporte cargado
    Given un informe existente seleccionado y cargado en el diseñador
    And un usuario con permiso emission.design
    When solicita "Agregá subtotal de horas"
    Then el informe cargado muestra el cambio solicitado
    And el usuario puede revisarlo antes de guardar

  Scenario: Guardar una modificación aprobada
    Given un cambio IA visible y revisado por el usuario
    When el usuario ejecuta Save o Save As
    Then el informe queda persistido mediante el flujo GEN-15

  Scenario: Usuario sin permiso
    Given un usuario sin emission.design
    When intenta abrir el diseñador
    Then no accede al diseñador ni a la asistencia IA
```

## Supuestos explícitos

- El informe `.repx` ya existe, fue seleccionado por el usuario y está cargado en el diseñador antes de invocar la IA.
- La capacidad se incorpora dentro del diseñador y flujo GEN-15 existentes.
- El schema autorizado del proceso está disponible para interpretar el pedido del usuario.
- La versión del frontend y el sidecar son compatibles con DevExpress Reporting 26.1.
- La credencial del sidecar puede ser de servicio; esta HU no exige sincronización automática con las preferencias BYOK del usuario.

## Preguntas abiertas

| # | Pregunta | Tratamiento |
|---|----------|-------------|
| 1 | ¿La modificación se resuelve con la capacidad nativa DX o con una alternativa Framework/GEN? | Definir en TR sin cambiar el resultado funcional exigido |
| 2 | ¿Cuál es la superficie concreta para invocar y revisar la propuesta dentro del diseñador? | Definir en TR respetando el flujo existente |
| 3 | ¿Qué mecanismo preserva las partes no solicitadas del layout? | Definir y probar en TR |

## Riesgos de ambigüedad

- La API o UI web de DevExpress podría estar orientada al wizard de creación y no aceptar directamente un REPX existente.
- Una solución que solo informe la limitación técnica no cumple esta HU: debe existir modificación real del reporte cargado.
- El cambio IA podría afectar elementos del layout no solicitados; la prueba debe verificar su preservación.

## Trazabilidad

| Fuente | Aplicación en esta HU |
|--------|------------------------|
| SPEC-016 §2 | Alcance y exclusiones |
| SPEC-016 §3 | Actores y precondiciones |
| SPEC-016 §4 | Flujo y reglas R-HU016-01…07 |
| SPEC-016 §5 | CA-016-01…09 |
| GEN-15 | Diseñador y persistencia Save / Save As; adoptar, no reimplementar |

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-06 | Parte B: HU-016 creada exclusivamente desde SPEC-016. |
| 2026-11-06 | Parte B1: HU-016 enriquecida desde SPEC-016; preguntas técnicas reservadas para TR. |
| 2026-11-06 | Parte C: [TR-016](../../04/tareas/100-SistemaPartes/TR-016-edicion-ia-informes-existentes-prompt-to-report.md) — Jira [PIAPARTES-60](https://paqsystems.atlassian.net/browse/PIAPARTES-60). |
| 2026-11-06 | Parte C1: TR-016 apta con observaciones → HU **Especificado**. |

