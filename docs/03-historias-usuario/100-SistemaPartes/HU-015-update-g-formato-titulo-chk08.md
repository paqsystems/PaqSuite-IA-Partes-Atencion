# HU-015-update-g – Formato del título en diseño de informes (oleada 3b / CHK-08-format)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-015-update-g |
| Título | Título en negrita, subrayado y centrado vía Smart Capture |
| Épica | `100-SistemaPartes` |
| Clasificación | MUST-HAVE (oleada 3b) |
| Estado | **Implementando** (2026-11-21) |
| Jira | [PIAPARTES-67](https://paqsystems.atlassian.net/browse/PIAPARTES-67) |
| SPEC | [SPEC-015-update oleada 3b](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-3b-formato-titulo-chk08-update.md) |
| TR | [TR-015-update-g](../../04-tareas/100-SistemaPartes/TR-015-update-g-formato-titulo-chk08.md) |
| HU previa | [HU-015-update-c](./HU-015-update-c-apariencia-controles-informes.md) |

---

## Narrativa

Como **diseñador** con `emission.design`, quiero pedir formato del **título del informe** (negrita, subrayado, centrado) sin abrir el diseñador de bandas, incluso cuando el título está en el encabezado de página (oleada 5).

---

## Alcance incluido

- Prompt canónico CHK-08-format.
- Apply sidecar multi-ubicación (page header + report header).
- Mensaje si el formato ya estaba aplicado.

## Fuera de alcance

CHK-08 texto, CHK-27, CHK-05 (solo coexistencia).

---

## Criterios de aceptación (resumen)

| AC | Descripción |
|----|-------------|
| AC-01 | Planner → `setControlAppearance` / `reportTitle` + bold/underline/center |
| AC-02 | Apply muta `lblReportTitle` visible (page y/o report header) |
| AC-03 | Re-apply → `ya_formateado`, mensaje i18n, sin error HTTP |
| AC-04 | Guardar/Descartar operativos tras apply con cambio |

## Gherkin (CHK-08-format)

```gherkin
Escenario: Formatear título del informe
  Dado un layout con lblReportTitle sin subrayado centrado
  Cuando envío "Poné el título en negrita, subrayado y centrado" y confirmo apply
  Entonces el REPX del título incluye Bold, Underline y MiddleCenter
  Y aparece el banner Guardar o Descartar

Escenario: Re-aplicar formato de título
  Dado el título ya con negrita subrayado y centrado
  Cuando repito el mismo prompt
  Entonces applied es 0 y el asistente informa que el título ya tenía ese formato
```