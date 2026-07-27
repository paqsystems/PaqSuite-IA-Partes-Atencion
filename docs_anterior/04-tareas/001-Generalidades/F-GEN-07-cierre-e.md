# F-GEN-07 — Cierre paso E (tests automatizados)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-001-07-importar-excel](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **Fecha** | 2026-06-16 |
| **Alcance** | Verificación automatizada epic Importar Excel (D1 + D2 ui-embebida) sin caso Pedidos |
| **Veredicto** | **Apto** — tests listos; feature con SQL Server tenant se omiten si no hay entorno |

## Cobertura por slice

| TR / HU | Tests automatizados |
|---------|---------------------|
| TR-GEN-07-plantilla-excel | `ExcelImportPlantillaFeatureTest`, `ExcelTemplateServiceFileNameTest` |
| TR-GEN-07-carga-staging-excel | `ExcelImportStagingFeatureTest` (lote válido, estructural, fila error) |
| TR-GEN-07-grilla-procesamiento-excel | `ExcelImportStagingFeatureTest` (`soloConError`, `procesar` bloqueado/ok) |
| TR-GEN-07-historial-importaciones | `ExcelImportStagingFeatureTest::testHistorialListsCreatedLot` |
| TR-GEN-07-ui-embebida-host | `ExcelImportStagingFeatureTest` (`filas/validas`, `export-errores`), `excelImportApi.test.ts` (`buildHostResultFromLot`) |
| Unit transversal | `ExcelImportHeaderCommentBuilderTest`, `ExcelImportNormalizerTest`, `ExcelImportErrorsExportServiceTest` |

## Inventario backend

| Suite | Archivo | Casos |
|-------|---------|-------|
| Feature | `tests/Feature/Api/ExcelImport/ExcelImportPlantillaFeatureTest.php` | 5 |
| Feature | `tests/Feature/Api/ExcelImport/ExcelImportStagingFeatureTest.php` | 9 |
| Unit | `tests/Unit/Services/ExcelImport/*` | 8 |
| Support | `ExcelImportFeatureTestCase`, `BuildsExcelImportWorkbooks` | Generación `.xlsx` en tests |

**Comando:** `php artisan test --filter=ExcelImport`

**Nota:** los Feature extienden `ExcelImportFeatureTestCase` y requieren tenant `desarrollo` con SQL Server (mismo criterio que Pivots/SistemaPartes). Sin tenant → `markTestSkipped`.

## Inventario frontend

| Suite | Archivo | Casos |
|-------|---------|-------|
| Unit Vitest | `frontend/src/features/excelImport/api/excelImportApi.test.ts` | 2 |

**Comando:** `npm run test -- excelImportApi`

**Build:** `npm run build` (verificado en D2).

## Escenarios cubiertos (piloto `ARTICULOS_ALTA`)

1. Descarga plantilla: encabezados, comentario `OBLIGATORIO`, nombre `ARTICULOS_ALTA_plantilla.xlsx`.
2. Carga válida → `lista_para_procesar`, contadores.
3. Error estructural (columna faltante) → `con_error_estructura`.
4. Error por fila (fecha inválida) → mezcla válidas/errores.
5. `GET filas?soloConError=true` solo devuelve filas con error.
6. `GET filas/validas` payload host (`onComplete`).
7. `GET export-errores` `.xlsx` con una fila error y columnas plantilla + Errores + NumeroFilaExcel.
8. `POST procesar` bloqueado con `permiteSoloValidar=true` (seed piloto).
9. `POST procesar` ok con flags de procesamiento habilitados en test.
10. Historial lista el lote creado.

## Fuera de alcance paso E

- E2E Playwright modal embebido (pendiente smoke manual conjunto con SPEC Pedidos).
- Matriz permisos MVP documentada (paso F).
- OpenAPI global sincronizado (observación F).
- Handler negocio real (SPEC proceso Pedidos).

## Próximo paso

1. **Usuario:** SPEC carga/edición pedidos + prueba manual integrada.
2. **F formal:** `F-GEN-07-cierre-formal.md` tras smoke manual y matriz permisos.






