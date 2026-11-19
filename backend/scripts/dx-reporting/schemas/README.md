# Schemas semánticos — sidecar DX Reporting (generados)

**No editar a mano.** Se generan desde el artefacto canónico GEN-15/GEN-37:

```bash
cd backend
php scripts/dev/export-emission-designer-schemas-for-dx-sidecar.php
```

Copiar al sidecar Framework en despliegue lab:

`PaqSuite-IA-FRAMEWORK/services/dx-reporting/src/PaqSuite.DxReporting/data/schemas/`

(o `scripts/dev/sync-dx-reporting-schemas.ps1` para `resources/emissions/schemas` + copia manual de esta carpeta).

| Archivo | Clave sidecar |
|---------|----------------|
| `partes.informes.consultaDetallada.json` | `processCode` GEN |
| `partes.consultaDetallada.principal.json` | Variante `reportUrl` con puntos |
| `partes_consultaDetallada_principal.json` | Reporte principal seed / Vite |

SoT contenido: `database/emission-designer-schemas/partes.informes.consultaDetallada.json`.