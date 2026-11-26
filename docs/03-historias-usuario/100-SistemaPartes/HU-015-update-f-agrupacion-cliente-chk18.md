# HU-015-update-f – Agrupación por cliente en diseño de informes (oleada 6 / CHK-18)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-015-update-f |
| Título | Agrupar por cliente con group header y footer vía Smart Capture |
| Épica | `100-SistemaPartes` |
| Clasificación | MUST-HAVE (oleada 6) |
| Estado | **Finalizado** (2026-11-21) |
| Jira | PIAPARTES-64 |
| SPEC | [SPEC-015-update oleada 6](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-6-agrupacion-chk18-update.md) |
| TR | [TR-015-update-f](../../04-tareas/100-SistemaPartes/TR-015-update-f-agrupacion-cliente-chk18.md) |
| HU previa | [HU-015-update-e](./HU-015-update-e-page-header-informes.md) |

---

## Narrativa

Como **diseñador** con `emission.design`, quiero pedir **«Agrupá por cliente»** para que el informe muestre bloques por cliente con bandas de grupo estándar, sin armar Group Header/Footer a mano en DevExpress.

---

## Alcance incluido

- Prompt canónico CHK-18.
- Apply sidecar + mensaje si ya estaba agrupado.
- Reparación de pie de grupo faltante.

## Fuera de alcance

CHK-19, CHK-20, CHK-21.

---

## Criterios de aceptación (resumen)

| AC | Descripción |
|----|-------------|
| AC-01 | Planner devuelve `addGroupByField` / `clienteNombre` |
| AC-02 | Apply crea header + footer + agrupación |
| AC-03 | Re-apply → mensaje «ya agrupado», sin error HTTP |
| AC-04 | Guardar/Descartar operativos tras apply |

## Gherkin (CHK-18)

```gherkin
Escenario: Agrupar por cliente en layout limpio
  Dado un layout sin GroupHeaderBand para clienteNombre
  Cuando envío "Agrupá por cliente" y confirmo apply
  Entonces el REPX contiene GroupHeaderBand, GroupFooter y GroupField clienteNombre
  Y aparece el banner Guardar o Descartar

Escenario: Re-aplicar agrupación existente
  Dado el informe ya agrupado por clienteNombre con header y footer
  Cuando repito el mismo prompt
  Entonces applied es 0 y el asistente informa que ya estaba agrupado
```