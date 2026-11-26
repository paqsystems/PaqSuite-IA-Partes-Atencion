# Plan de implementación — TR-015-update-m (oleada 11, CHK-15..17)

> Parte **D1**. Jira: **PIAPARTES-78** (HU) · **PIAPARTES-79** (TR, si existe) bajo épica **PIAPARTES-53**. Orden smoke: 16 → 15 → 17.

## Decisiones propuestas (confirmar en «sí, parte D»)

| Tema | Propuesta D1 |
|------|----------------|
| Watermark CHK-15 | `Report.Watermark` DX con imagen embebida base64 o ruta relativa asset sidecar; opacidad ~0.15 v1 |
| Logo CHK-16 | `PictureBox` `imgPageHdr_Logo` fila 1; si `lblPageHdr_Empresa` existe, desplazar logo o empresa según TR (logo izq. extrema, texto empresa +80px) |
| QR CHK-17 | `XRBarCode` en Detail; `Binding` a `clienteCode`; symbology QRCode |
| Noop | `noop:setReportWatermarkImage:ya_existe`, `noop:setPageHeaderLogo:ya_colocados`, `noop:addDetailBarCode:ya_existe` |
| Eval | Script dedicado oleada 11; restore entre ítems |
| Framework bump | laravel-core no; publicar dx-reporting + pin si aplica hosts |
| PHPUnit | Matriz ya exige acciones — implementar heurísticas **antes** de commit verde |

## Fuentes

- SPEC oleada 11, matriz v4, TR-015-update-e (page header), L-B-02

## Orden de tareas D

1. Sidecar: tres appliers + `LayoutApplyService` dispatch + unit tests C#
2. Assets pilot PNG en sidecar
3. Host: whitelist, heurísticas, i18n noop/éxito (5 locales mín. claves nuevas)
4. Script `eval-emission-ia-oleada11-chk15-17-apply.ps1`
5. FE: sufijos noop en `emissionDesignSmartCaptureTurn` si aplica
6. Documentación F1/F tras smoke

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Logo vs CHK-06 solapamiento | Fixture con ambos; offset en applier |
| QR sin columna clienteCode | Mensaje `partes.emissionDesign.barCode.fieldMissing` |
| Watermark opaco | Param TR / constante documentada |