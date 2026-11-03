# Verificación F — CC PQ #12 (02/11/2026)

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#12** |
| **Veredicto F** | **Apto** (Vitest 6/6; smoke preview develop OK) |
| **F1/F preview** | **Aprobado** 02/11/2026 — smoke API tenant DEV |
| **Parte I** | 02/11/2026 — fusionado en SPEC/HU/TR-003, 006, 011 base |

## CA CC #12

| CA | Evidencia |
|----|-----------|
| CA-12-01/02 | `MaestroCrudPage` + `CustomStore` + `maestroListPaging.ts` |
| CA-12-03 | `PqMenuSeeder` sin 40200; migración `2026_11_02_120000_remove_consultas_agrupadas_menu` (+ FK `pq_rol_atributos`, PR #124) |
| CA-12-04 | `PaqueteHorasPage` + `PartesPaqueteHorasEmissionPort` + migración seed `2026_11_03_100000_seed_paquete_horas_emission_catalog` (PR #125) |
| CA-12-05 | Vitest 6/6 (paging, hostContext, gates) |

## Deploy

- `php artisan migrate --force` (menú 40200 + catálogo emisión Paquete de Horas).
- Forge post-deploy: `installation:update` + `deploy-sp` por tenant (GEN-34).

## Smoke preview develop

Entorno: `https://partesatencion-paqsystems-dev.on-forge.com/api/v1` · tenant **DEV** · usuario **PQ** · SHA final **`eafa6fe`** (PR #125).

| CA | Resultado | Evidencia |
|----|-----------|-----------|
| CA-12-01 | **OK** | `GET /partes/clientes?page=1&pageSize=50` → `total=189`, `items=50`; `page=2` → `items=50` |
| CA-12-02 | **OK** (muestra clientes) | Mismo contrato paginado en maestros; asistentes sin volumen en DEV |
| CA-12-03 | **OK** | `GET /user/menu` sin `40200` / `consultas-agrupadas` |
| CA-12-04 | **OK** | `GET /emissions/processes` incluye `partes.informes.paqueteHoras`; `GET .../partes.informes.paqueteHoras` → 200 |
| UI manual | **No verificado** | Vercel preview exige SSO |

### Historial smoke

| Fecha | SHA / PR | Notas |
|-------|----------|--------|
| 02/11/2026 | `24c8b28` / #122 | FAIL menú 40200 y emisión: migrate FK en Forge |
| 02/11/2026 | `858b7c0` / #124 | Menú OK tras borrar `pq_rol_atributos`; emisión aún sin seed |
| 02/11/2026 | `eafa6fe` / #125 | Emisión Paquete de Horas OK en API |
| 02/11/2026 | `57f2438` / #123 → `main` | Prod API tenant **PAQ**: menú sin 40200, clientes paginados, `partes.informes.paqueteHoras` |
