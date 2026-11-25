# HU-015-update-m – Imágenes, logo y QR (CHK-15..17)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-015-update-m |
| Estado | **Especificado** |
| Jira | [PIAPARTES-78](https://paqsystems.atlassian.net/browse/PIAPARTES-78) (parent [PIAPARTES-53](https://paqsystems.atlassian.net/browse/PIAPARTES-53)) |
| SPEC | [SPEC oleada 11](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-11-imagenes-chk15-17-update.md) |
| TR | [TR-015-update-m](../../04-tareas/100-SistemaPartes/TR-015-update-m-imagenes-chk15-17.md) (Jira [PIAPARTES-79](https://paqsystems.atlassian.net/browse/PIAPARTES-79)) |
| Prompts agente | [prompts-nuevo-hilo-oleada-11](../../06-operacion/prompts-nuevo-hilo-oleada-11-chk15-17-sdd.md) |

## Narrativa

Como diseñador del informe **Consulta detallada**, quiero pedir en lenguaje natural una **imagen de fondo**, el **logo de la empresa** en el encabezado y un **código QR** del código de cliente, para que el asistente aplique cambios permitidos en el lienzo sin editar el REPX a mano.

## Criterios de aceptación

| AC | Descripción |
|----|-------------|
| AC-01 | Prompt CHK-15 → `setReportWatermarkImage` + watermark visible en preview. |
| AC-02 | Prompt CHK-16 → `setPageHeaderLogo` en page header sin romper CHK-06/07. |
| AC-03 | Prompt CHK-17 → `addDetailBarCode` sobre `clienteCode` escaneable. |
| AC-04 | Re-aplicar mismo prompt → noop i18n (sin error HTTP). |
| AC-05 | Banner Guardar/Descartar (L-B-10) tras cada apply exitoso. |

## Reglas de negocio

- Solo assets v1 empaquetados / `source: empresa` (sin upload arbitrario desde SC).
- Campo QR limitado a `clienteCode` (GEN-37).
- Tres CHK en **una** oleada; mismo proceso piloto.

## Gherkin (resumen)

```gherkin
Escenario: Marca de agua
  Dado el diseñador abierto con Smart Capture
  Cuando envío "Agregá una imagen de fondo al informe"
  Entonces el sidecar aplica watermark y ofrezco Guardar o Descartar

Escenario: Logo
  Cuando envío "Insertá el logo de la empresa"
  Entonces hay un PictureBox de logo en el page header

Escenario: QR cliente
  Dado el detalle incluye clienteCode
  Cuando envío "Mostrá clienteCode como código QR"
  Entonces hay un XRBarCode enlazado al campo
```