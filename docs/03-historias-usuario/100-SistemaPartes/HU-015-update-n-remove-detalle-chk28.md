# HU-015-update-n – Eliminar columna observación del detalle (CHK-28)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-015-update-n |
| Estado | **Finalizado** |
| Jira | [PIAPARTES-80](https://paqsystems.atlassian.net/browse/PIAPARTES-80) (parent [PIAPARTES-53](https://paqsystems.atlassian.net/browse/PIAPARTES-53)) |
| SPEC | [SPEC oleada 12](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-12-remove-detail-chk28-update.md) |
| TR | [TR-015-update-n](../../04-tareas/100-SistemaPartes/TR-015-update-n-remove-detalle-chk28.md) |
| Prompts agente | [prompts-nuevo-hilo-oleada-12-chk28](../../06-operacion/prompts-nuevo-hilo-oleada-12-chk28-sdd.md) |

## Narrativa

Como diseñador del informe **Consulta detallada**, quiero pedir que **saquen la columna de observación del detalle**, para simplificar la vista sin editar el REPX manualmente ni romper el resto del layout.

## Criterios de aceptación

| AC | Descripción |
|----|-------------|
| AC-01 | Prompt canónico CHK-28 → acción `removeDetailField` / `observacion` en la respuesta del planner. |
| AC-02 | Apply elimina el control de observación del DetailBand; el informe sigue emitible en preview. |
| AC-03 | Si la columna ya no está → noop amigable (`ya_ausente`), sin error HTTP. |
| AC-04 | Otros pedidos de eliminación no cubiertos (p. ej. agrupación) siguen `noMappedActions`. |
| AC-05 | Tras apply exitoso, debo poder **Guardar** o **Descartar** (L-B-10). |

## Reglas de negocio

- Solo campo **`observacion`** del schema `partes.informes.consultaDetallada` en esta entrega.
- Eliminación **física** del control en detalle; no ocultar con `Visible=false`.
- No eliminar en esta HU: agrupaciones, calculados, títulos, QR, logo, watermark.

## Gherkin (resumen)

```gherkin
Escenario: Quitar observación del detalle
  Dado el diseñador abierto con una columna observación en el detalle
  Cuando envío "Quitá la columna observación del detalle"
  Entonces el sidecar aplica removeDetailField y la columna ya no aparece en el detalle
  Y puedo Guardar o Descartar

Escenario: Idempotencia
  Dado un informe sin columna observación en el detalle
  Cuando repito el mismo prompt
  Entonces recibo un mensaje noop sin error
```

## Fuera de alcance

CHK-29 a CHK-32, eliminación de otros campos detalle, y borrado de controles en bandas que no sean Detail.