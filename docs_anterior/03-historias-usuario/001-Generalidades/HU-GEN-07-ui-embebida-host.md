# HU-GEN-07-ui-embebida-host — Componente Excel embebido en pantalla host

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-07-ui-embebida-host |
| **SPEC origen** | [SPEC-001-07-importar-excel.md](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **Patrón** | [patron-componente-excel-embebido.md](../../00-contexto/_mono/importar-excel/patron-componente-excel-embebido.md) |
| **TR** | [TR-GEN-07-ui-embebida-host.md](../../04-tareas/001-Generalidades/TR-GEN-07-ui-embebida-host.md) |
| **Épica** | 001 — Generalidades / Importar Excel |
| **Prioridad** | Could |
| **Estado** | Pendiente |
| **Dependencias** | HU-GEN-07-plantilla-excel; HU-GEN-07-carga-staging-excel; HU-GEN-07-grilla-procesamiento-excel |

## Narrativa

Como **desarrollador de una pantalla host** (cualquier módulo),  
quiero **incrustar un componente cerrado de importación Excel** con exportar plantilla e importar en modal,  
para **recibir un conjunto de datos válidos vía callback** y decidir en mi proceso qué hacer con ellos.

## Alcance incluido

- `ExcelImportHostToolbar`: botones **Exportar planilla** e **Importar planilla**.
- Modal DevExtreme: archivo `.xlsx`, hoja, validar.
- Error estructural: mensaje sin grilla; Cerrar / Reintentar.
- Sin errores por fila: sin grilla; procesar (si aplica) y `onComplete` con todas las válidas.
- Con errores: grilla **solo filas con error** (paginación server-side); export errores `.xlsx`.
- Parcial false + errores: no procesar; `onComplete` con `validRows` vacío al cerrar.
- Parcial true + errores: procesar válidas; mostrar errores; **Continuar** → `onComplete` con válidas.
- `PermiteSoloValidar`: sin POST procesar; `validRows` = filas sin error de validación.

## Fuera de alcance

- Lógica de negocio del host (pedidos, ABM, etc.).
- Rutas dedicadas de proceso (opcionales legado).
- Reproceso mismo lote.

## Criterios de aceptación

- [ ] **CA-01:** Toolbar muestra export/import según `generaPlantilla` y flag epic.
- [ ] **CA-02:** Export usa nombre `{codigoProceso}_plantilla.xlsx` y `saveExcelWithPicker` / descarga.
- [ ] **CA-03:** Import modal: FileUploader + SelectBox hoja + validar.
- [ ] **CA-04:** Error estructural → mensaje; sin grilla; Cerrar/Reintentar.
- [ ] **CA-05:** Cero errores fila → sin grilla; `onComplete` con datos.
- [ ] **CA-06:** Errores + parcial false → grilla solo errores; `onComplete` vacío al cerrar.
- [ ] **CA-07:** Errores + parcial true → procesar válidas; grilla errores; Continuar → `onComplete`.
- [ ] **CA-08:** Export errores: columnas plantilla + Errores + nº fila; solo filas error.
- [ ] **CA-09:** `data-testid`: `excelHostToolbar`, `excelHostImportModal`, `excelHostErrorGrid`.

## Veredicto

**Lista para TR D2** — ver [F-GEN-07-revision-consistencia-ui-embebida.md](../../04-tareas/001-Generalidades/F-GEN-07-revision-consistencia-ui-embebida.md).










