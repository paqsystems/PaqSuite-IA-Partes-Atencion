# Smoke manual — oleada 5 T1 (CHK-04)

> Batería completa CHK-04…07: [smoke-oleada5-page-header-chk04-07.md](./smoke-oleada5-page-header-chk04-07.md).

## Precondiciones

- Sidecar `:5055` con build actual (acción `addPageHeaderColumnLabels`).
- Partes lab `http://127.0.0.1:3102/emisiones/disenador?cliente=DEV`.
- Variante **spike-layout-b-1** (detalle con bindings).

## Paso

1. Prompt: `Poné un título encima de cada columna en el encabezado de página`
2. Confirmar apply.
3. **Esperado:** labels `lblPageHdr_*` en page header (fila 3), banner Guardar/Descartar.
4. Variante sin campos en detalle: mismo prompt → mensaje *«Agregá campos en el detalle…»*; sin error HTTP.
5. Variante que ya tiene `lblPageHdr_*` alineados: mensaje *«El encabezado de página ya tiene los títulos…»*; apply `noop:…:ya_colocados`; sin refresh del lienzo.

## Automatizado

```powershell
cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-oleada5-apply.ps1
```