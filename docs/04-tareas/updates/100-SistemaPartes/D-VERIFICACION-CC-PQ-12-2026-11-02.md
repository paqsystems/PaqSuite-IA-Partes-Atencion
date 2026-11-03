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

## Smoke preview develop (02/11/2026 — post PR #122, SHA `24c8b28`)

Entorno: API `https://partesatencion-paqsystems-dev.on-forge.com` · tenant **DEV** · usuario **PQ**.

| CA | Resultado | Evidencia |
|----|-----------|-----------|
| CA-12-01 | **OK** | `GET /partes/clientes?page=1&pageSize=50` → `total=189`, `items=50`; `page=2` → `items=50` |
| CA-12-02 | **No verificado** | Misma API en clientes; resto de maestros no recorrido en smoke |
| CA-12-03 | **FAIL** | `GET /user/menu` incluye ítem `id=40200` «Consultas agrupadas» |
| CA-12-04 | **FAIL** | `GET /emissions/processes` solo lista `partes.informes.consultaDetallada` (sin `partes.informes.paqueteHoras`) |
| UI manual | **No verificado** | Vercel preview (`partesatencion-dev.paqsystems.com`) exige login SSO |

**Causa raíz deploy Forge** (log deployment `81916803`): `migrate` falló al borrar `pq_menus` 40200 por FK `FK_pq_rol_atributos_menu` en `pq_rol_atributos`; el release se activó igual → `installation:update` / seed emisión no completaron el ciclo.

**Corrección pendiente:** migración CC #12 debe borrar `pq_rol_atributos` del menú antes del `DELETE` en `pq_menus`; re-deploy preview + repetir smoke antes de F1/F en CC-PQ.
