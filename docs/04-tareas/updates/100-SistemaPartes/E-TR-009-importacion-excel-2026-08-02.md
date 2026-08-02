# Evidencia Parte E — TR-009 Importación Excel (2026-08-02)

| Campo | Valor |
|-------|--------|
| TR | [TR-009](../../100-SistemaPartes/TR-009-importacion-partes-excel.md) |
| Fecha | 2026-08-02 |
| Resultado E | **OK** (suite TR + E2E smoke) |

## Comandos / resultados

| Capa | Comando | Resultado |
|------|---------|-----------|
| Feature BE | `php artisan test --filter=ApiV1ExcelImportPartesTest` | **4 passed** (25 assertions) |
| Regresión carga | `php artisan test --filter=ApiV1PartesTareaTest` | **6 passed** |
| Vitest | `npx vitest run src/features/partes/carga` | **9 passed** (2 files) |
| E2E | `npx playwright test tests/e2e/partes-excel-import.spec.ts` (+ login dashboard) | **1 + 1 passed** |

## Casos Feature cubiertos

- Template 200 + capability off → **4604**
- Upload mixto → `validRows=1` / `errorRows=1` → process **partial** + `es_tarea=1` / `cerrado=0`
- Proceso sin menú → **4603**
- Asistente no supervisor con `asistente` ≠ sesión → error fila `partes.import.asistenteDistintoSesion`

## E2E

- Login admin → `/partes/carga-diaria` → visible `excelImport.toolbar` + `excelImport.template`
- Backend local: sqlite `e2e.sqlite` en `:8010`; `ExcelImportEnabled=1` para smoke UI

## Observaciones

- Tras manipular env `DB_*` en shell, **limpiar** antes de PHPUnit (si no, apunta a e2e.sqlite y puede fallar capability).
- Prod/local: activar `ExcelImportEnabled` (param BD) para plantilla/import reales.
- F1 pendiente (verificación formal + Estado Pendiente de Revisión).

## Smoke manual sugerido (F1)

1. Activar `ExcelImportEnabled`
2. Carga diaria → Descargar plantilla
3. Importar 2 OK + 1 error → Procesar → grilla conserva filtros
4. Mobile / cliente: sin toolbar
