# Verificación F — CC PQ #12 (02/11/2026)

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#12** |
| **Veredicto F** | **Apto** (Vitest OK; PHPUnit maestros pendiente re-run en entorno con migraciones al día) |
| **Parte I** | 02/11/2026 — fusionado en SPEC/HU/TR-003, 006, 011 base |

## CA CC #12

| CA | Evidencia |
|----|-----------|
| CA-12-01/02 | `MaestroCrudPage` + `CustomStore` + `maestroListPaging.ts` |
| CA-12-03 | `PqMenuSeeder` sin 40200; migración `2026_11_02_120000_remove_consultas_agrupadas_menu` |
| CA-12-04 | `PaqueteHorasPage` + `PartesPaqueteHorasEmissionPort` + seed emisión |
| CA-12-05 | Vitest 6/6 (paging, hostContext, gates) |

## Deploy

- `php artisan migrate --force` (menú 40200).
- Re-seed o `installation:update` para catálogo menú y proceso emisión Paquete de Horas (`PqEmissionSeeder`).
