# Schemas semánticos — sidecar DX Reporting (Consulta detallada)

Copiar estos archivos al sidecar Framework en despliegue:

`PaqSuite-IA-FRAMEWORK/services/dx-reporting/src/PaqSuite.DxReporting/data/schemas/`

Claves alineadas a `processCode` / `reportUrl` del diseñador Partes:

| Archivo | Uso |
|---------|-----|
| `partes.informes.consultaDetallada.json` | `processCode` GEN |
| `partes.consultaDetallada.principal.json` | Variante reportUrl con puntos |
| `partes_consultaDetallada_principal.json` | Reporte principal seed / default Vite |

SoT operativo en el repo Framework (mismo contenido). Prompt-to-Report usa columnas + `label`/`synonyms` vía sidecar con `OPENAI_*` en el proceso .NET.
