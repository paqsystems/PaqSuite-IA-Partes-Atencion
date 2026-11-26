# HU-015-update-h – Group header «Cliente:» en diseño de informes (oleada 7 / CHK-19)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-015-update-h |
| Título | Encabezado de grupo con etiqueta Cliente: y negrita vía Smart Capture |
| Épica | `100-SistemaPartes` |
| Clasificación | MUST-HAVE (oleada 7) |
| Estado | **Finalizado** (2026-11-24) |
| Jira | PIAPARTES-70 |
| SPEC | [SPEC-015-update oleada 7](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-7-group-header-chk19-update.md) |
| TR | [TR-015-update-h](../../04-tareas/100-SistemaPartes/TR-015-update-h-group-header-cliente-chk19.md) |
| HU previa | [HU-015-update-f](./HU-015-update-f-agrupacion-cliente-chk18.md) |

---

## Narrativa

Como **diseñador** con `emission.design`, quiero que al pedir **«Agrupá por cliente»** el encabezado de cada grupo muestre **«Cliente:»** seguido del nombre del cliente en **negrita**, igual que en los layouts de referencia del laboratorio, sin ver el nombre técnico del campo en el prefijo.

---

## Alcance incluido

- Mismo prompt y acción planner que CHK-18.
- Apply/reparación de expresión y fuente en el label del group header.
- Re-aplicar sin cambios cuando el header ya cumple CHK-19 (mensaje ya agrupado).

## Fuera de alcance

CHK-20, CHK-21, CHK-08-format, subrayado obligatorio.

---

## Criterios de aceptación (resumen)

| AC | Descripción |
|----|-------------|
| AC-01 | Apply deja REPX con `Cliente:`, `clienteNombre`, negrita (`Font=`) |
| AC-02 | Layout creado en CHK-18 con copy técnico se repara al re-aplicar |
| AC-03 | Re-apply con header correcto → `ya_agrupado`, sin error |
| AC-04 | Guardar/Descartar operativos tras apply/reparación |

## Gherkin (CHK-19)

```gherkin
Escenario: Agrupar con etiqueta legible en header
  Dado un layout sin agrupación por clienteNombre
  Cuando envío "Agrupá por cliente" y confirmo apply
  Entonces el REPX del group header contiene la expresión con "Cliente:" y [clienteNombre]
  Y el label usa fuente en negrita

Escenario: Reparar header heredado de CHK-18
  Dado un informe ya agrupado con expresión "clienteNombre:" en el group header
  Cuando repito "Agrupá por cliente"
  Entonces el sidecar actualiza el caption a "Cliente:" sin duplicar bandas
  Y puedo Guardar los cambios

Escenario: Re-aplicar con header ya conforme CHK-19
  Dado el group header ya muestra "Cliente:" en negrita con footer emparejado
  Cuando repito el mismo prompt
  Entonces applied es 0 y el asistente informa que ya estaba agrupado
```