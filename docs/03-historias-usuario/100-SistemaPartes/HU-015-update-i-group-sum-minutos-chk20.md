# HU-015-update-i – Total minutos en pie de grupo (oleada 7b / CHK-20)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-015-update-i |
| Título | Subtotal de minutos por cliente en diseño de informes vía Smart Capture |
| Épica | `100-SistemaPartes` |
| Clasificación | MUST-HAVE (oleada 7b) |
| Estado | **Finalizado** (2026-11-24) |
| Jira | PIAPARTES-73 |
| SPEC | [SPEC-015-update oleada 7b](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-7b-group-sum-chk20-update.md) |
| TR | [TR-015-update-i](../../04-tareas/100-SistemaPartes/TR-015-update-i-group-sum-minutos-chk20.md) |
| HU previa | [HU-015-update-h](./HU-015-update-h-group-header-cliente-chk19.md) |

---

## Narrativa

Como **diseñador** con `emission.design`, quiero pedir en un solo mensaje **«Agrupá por cliente y sumá los minutos»** para que el informe muestre cada grupo por cliente y, al pie del grupo, el texto **«Total minutos:»** con la suma de `duracionMinutos`, alineado al laboratorio y a la vista previa del diseñador.

---

## Alcance incluido

- Planner heurístico: dos acciones ordenadas (grupo cliente, suma minutos).
- Apply batch sidecar sin segundo pie de grupo duplicado.
- Re-aplicar sin duplicar subtotal cuando ya existe.

## Fuera de alcance

CHK-21, CHK-08-format, totales a nivel informe, nuevo campo de agrupación distinto de cliente en esta HU.

---

## Criterios de aceptación (resumen)

| AC | Descripción |
|----|-------------|
| AC-01 | Turno planifica `addGroupByField` + `addGroupSum` con payloads matriz |
| AC-02 | Apply deja REPX con «Total minutos», `duracionMinutos`, `sumSum` en pie del grupo cliente |
| AC-03 | No aparece pie extra `GroupFooter_duracionMinutos` si ya hay `GroupFooter_clienteNombre` |
| AC-04 | Re-apply con subtotal OK → sin duplicar; Guardar/Descartar operativos |

## Gherkin (CHK-20)

```gherkin
Escenario: Agrupar por cliente y sumar minutos en un turno
  Dado un layout sin agrupación ni subtotales de grupo
  Cuando envío "Agrupá por cliente y sumá los minutos" y confirmo apply
  Entonces el planner devuelve grupo por clienteNombre y suma duracionMinutos
  Y el REPX del pie de grupo contiene "Total minutos" y sumSum([duracionMinutos])

Escenario: Reutilizar pie vacío de CHK-18
  Dado un informe ya agrupado por cliente con GroupFooter vacío
  Cuando apply solo addGroupSum para duracionMinutos
  Entonces el subtotal se agrega en GroupFooter_clienteNombre
  Y no se crea un segundo GroupFooter para duracionMinutos

Escenario: Re-aplicar con subtotal existente
  Dado el pie de grupo ya muestra Total minutos con suma de duracionMinutos
  Cuando repito el prompt canónico y apply
  Entonces la acción de suma no duplica controles
```