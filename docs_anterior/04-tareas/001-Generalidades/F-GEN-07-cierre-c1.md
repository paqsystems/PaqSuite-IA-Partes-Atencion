# F-GEN-07 — Cierre revisión C1 (epic Importar Excel)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-001-07-importar-excel](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **Fecha** | 2026-06-16 (revisión ampliada plantilla §12) |
| **Alcance** | Revisión C1 de las 4 TR-GEN-07-* + enriquecimiento plantilla modelo |
| **Veredicto** | **Apto con observaciones** — **autorizado D1** en orden 1→4 |

## Resultado por TR

| TR | Estado C1 | Bloqueantes D1 |
|----|-----------|----------------|
| [TR-GEN-07-plantilla-excel](TR-GEN-07-plantilla-excel.md) | Apto con observaciones | Ninguno |
| [TR-GEN-07-carga-staging-excel](TR-GEN-07-carga-staging-excel.md) | Apto con observaciones | Ninguno |
| [TR-GEN-07-grilla-procesamiento-excel](TR-GEN-07-grilla-procesamiento-excel.md) | Apto con observaciones | Ninguno |
| [TR-GEN-07-historial-importaciones](TR-GEN-07-historial-importaciones.md) | Apto con observaciones | Ninguno |

## Decisiones transversales cerradas en C1

| Tema | Decisión |
|------|----------|
| Permisos (AMB-Q-07-01) | `ProcedimientoHost` en `PQ_EXCEL_PROCESOS`; mismo gate que pantalla host (`Permiso_Alta` importar / `Permiso_Repo` historial). |
| Override normalización (AMB-Q-07-02) | Defaults del proceso al crear lote; sin override UI v1. |
| Reproceso mismo lote (AMB-Q-07-03) | Nueva importación en v1. |
| Descarga plantilla | Stream binario `.xlsx` en 200; errores envelope MONO. |
| Tablas | Prefijo canónico `PQ_EXCEL_*` en BD tenant. |
| Handler negocio | Interface plug-in; columna `HandlerBackend`. |
| Async | Job cola + notificaciones; umbral ~5 MB / 2000 filas. |
| Flag infra | `EXCEL_IMPORT_ENABLED` default **false** hasta deploy epic. |
| Fila ajustada | No expuesta en API ni UI v1. |
| **Plantilla modelo (§12)** | Botón permanente si `GeneraPlantilla=1`; comentario `OBLIGATORIO` + `Observaciones`; formato columna por `TipoDato`; §3.3 TR plantilla = contrato D1 PhpSpreadsheet. |
| Excel sin filas útiles | Lote creado con 0 filas; Procesar deshabilitado; i18n `excelImport.sinFilasParaProcesar` (TR carga). |
| Columna estructural faltante | `con_error_estructura`; sin crash (TR carga). |
| Columnas extra en Excel | Ignoradas (TR carga RN-02). |

## C1 — plantilla (post §12, 2026-06-16)

| ID | Tema | Decisión D1 |
|----|------|-------------|
| AMB-C07-P01 | `OBLIGATORIO` en título vs comentario | Solo en **comentario** Excel; título = `NombreColumnaExcel` exacto |
| AMB-C07-P02 | Fila 2+ en plantilla | Vacías; formato/validación aplicados a columna completa |
| AMB-C07-P03 | Librería generación | **PhpSpreadsheet** backend (`composer require phpoffice/phpspreadsheet`) |

Sin bloqueantes D1 en TR-GEN-07-plantilla-excel.

## Orden D1 recomendado

```text
1. TR-GEN-07-plantilla-excel       (catálogo + plantilla)
2. TR-GEN-07-carga-staging-excel   (upload + staging + handler stub)
3. TR-GEN-07-grilla-procesamiento-excel (grilla + procesar)
4. TR-GEN-07-historial-importaciones    (historial + detalle readOnly)
```

## Matriz permisos — filas previstas (aplicar en D1)

| Método | Path | Permiso |
|--------|------|---------|
| GET | `/api/v1/excel-import/procesos/{codigoProceso}` | `Permiso_Alta` en `ProcedimientoHost` |
| GET | `/api/v1/excel-import/procesos/{codigoProceso}/plantilla` | Idem |
| POST | `/api/v1/excel-import/procesos/{codigoProceso}/archivo/hojas` | Idem |
| POST | `/api/v1/excel-import/procesos/{codigoProceso}/lotes` | Idem |
| GET | `/api/v1/excel-import/lotes/{guidImportacion}` | Idem (+ dueño o supervisor según policy) |
| POST | `/api/v1/excel-import/lotes/{guidImportacion}/cancelar` | Dueño del lote |
| GET | `/api/v1/excel-import/lotes/{guidImportacion}/filas` | Idem |
| GET | `/api/v1/excel-import/lotes/{guidImportacion}/columnas` | Idem |
| POST | `/api/v1/excel-import/lotes/{guidImportacion}/procesar` | Idem |
| GET | `/api/v1/excel-import/historial` | `Permiso_Repo` en `pw_historialimportexcel` |

**Estado:** pendiente incorporar en [matriz-permisos-mvp.md](matriz-permisos-mvp.md) al iniciar D1.

## Fuera de alcance confirmado

- MVP portal release actual.
- Persistencia binaria de archivos subidos.
- Reproceso en mismo lote.
- ABM web de `PQ_EXCEL_PROCESOS*`.
- Override flags normalización por lote en UI.

## Próximo paso

**Paso E cerrado** — ver [F-GEN-07-cierre-e.md](F-GEN-07-cierre-e.md).

Siguiente: SPEC carga pedidos + smoke manual conjunto → **F formal** ([plantilla F-GEN-08](F-GEN-08-cierre-formal.md)).

**Activación en tenant** (ver runbook [`docs/_base/00-runbook-actualizacion-version.md`](../../_base/00-runbook-actualizacion-version.md) §10.1 Importar Excel):

1. `php artisan migrate --force` — `2026_06_16_100000_create_pq_excel_catalog_tables`, `2026_06_16_110000_create_pq_excel_import_tables`.
2. `php artisan db:seed --class=Database\\Seeders\\ExcelImport\\ExcelImportCatalogPilotSeeder`.
3. `.env`: `EXCEL_IMPORT_ENABLED=true`.
4. `php artisan paqsuite:seed-menus-mvp` (ítem `pw_historialimportexcel`).
5. Smoke: plantilla + carga + grilla + historial.






