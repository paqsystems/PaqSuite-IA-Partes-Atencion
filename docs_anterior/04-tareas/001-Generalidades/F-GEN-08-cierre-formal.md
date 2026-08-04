# Cierre F Formal — Bloque GEN-08 (SPEC-001-08 Pivots)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


## Alcance del cierre

Cubre los cuatro slices planificados y verificados (D1 + tests automatizados + build frontend):

| TR | HU |
|----|-----|
| [TR-GEN-08-motor-metadata-pivots](TR-GEN-08-motor-metadata-pivots.md) | [HU-GEN-08-motor-metadata-pivots](../../03-historias-usuario/001-Generalidades/HU-GEN-08-motor-metadata-pivots.md) |
| [TR-GEN-08-pivotgrid-visualizacion](TR-GEN-08-pivotgrid-visualizacion.md) | [HU-GEN-08-pivotgrid-visualizacion](../../03-historias-usuario/001-Generalidades/HU-GEN-08-pivotgrid-visualizacion.md) |
| [TR-GEN-08-layouts-pivot](TR-GEN-08-layouts-pivot.md) | [HU-GEN-08-layouts-pivot](../../03-historias-usuario/001-Generalidades/HU-GEN-08-layouts-pivot.md) |
| [TR-GEN-08-exportacion-pivot](TR-GEN-08-exportacion-pivot.md) | [HU-GEN-08-exportacion-pivot](../../03-historias-usuario/001-Generalidades/HU-GEN-08-exportacion-pivot.md) |

**SPEC:** [SPEC-001-08-pivots](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md)

**Revisión C1 previa:** [F-GEN-08-cierre-c1](F-GEN-08-cierre-c1.md)

## Resultado global

- **Aprobado con observaciones**

Epic **implementado en código** (D1 completo). **Fuera del release MVP portal** hasta activar flags en deploy (`PIVOTS_ENABLED`, `PIVOT_LAYOUTS_ENABLED`) y migraciones/seeder en tenant objetivo.

## Resumen por slice

| Slice | Resultado D1 | Observación principal |
|-------|--------------|------------------------|
| TR-GEN-08-motor-metadata-pivots | Aprobado | Catálogo `pq_pivots_*`, API metadata/data/validate; consulta piloto `CONSULTA_PILOTO_PIVOT` |
| TR-GEN-08-pivotgrid-visualizacion | Aprobado | `ConsultaGrillaPivotShell`, toggle grilla/pivot, i18n 5 idiomas; piloto historial ventas |
| TR-GEN-08-layouts-pivot | Aprobado | `pq_pivots_config` + API CRUD; toolbar diseños; E2E persistencia |
| TR-GEN-08-exportacion-pivot | Aprobado | Export client-side Excel básico/tabla dinámica; E2E descarga |

## Verificación automatizada (2026-06-11)

| Área | Evidencia |
|------|-----------|
| Backend Feature | `PivotMetadataFeatureTest`, `PivotConfigFeatureTest` (requieren tenant SQL Server en CI/local) |
| Backend Unit | `PivotMetadataResolverTest` |
| Frontend Unit | `resolvePivotCoexistence`, `applyPivotBaseToFields`, `pivotExportUtils` |
| Frontend E2E | `pivot-historial`, `pivot-layout-persistencia`, `pivot-export` |
| Build | `npm run build` OK |

## Activación en entorno

1. Migraciones:
   - `2026_06_11_100000_create_pq_pivots_catalog_tables` → `pq_pivots_consultas`, `pq_pivots_campos`, `pq_pivots_plantillas`, `pq_pivots_plantillas_det`, `pq_pivots_validaciones`
   - `2026_06_11_110000_create_pq_pivots_config_tables` → `pq_pivots_config`, `pq_pivots_config_last_used`
2. Seeder: `PivotCatalogPilotSeeder` (consulta piloto + diseño supervisor «Vista resumen»).
3. `.env`: `PIVOTS_ENABLED=true`, `PIVOT_LAYOUTS_ENABLED=true` (opcional layouts).
4. Pantalla piloto: historial ventas (`CONSULTA_PILOTO_PIVOT`, `tipoProceso=informe`).

Inventario completo y pasos deploy: [`docs/_base/00-runbook-actualizacion-version.md`](../../_base/00-runbook-actualizacion-version.md) §10.1 Pivots.

## Matriz permisos

Actualizada en [`matriz-permisos-mvp.md`](matriz-permisos-mvp.md) § Pivots (SPEC-001-08).

Regla: `procedimiento_host` de `pq_pivots_consultas` → `Permiso_Repo` vía `VisibilityPermissionGuard`.

## Observaciones (no bloqueantes)

| ID | Tema | Notas |
|----|------|-------|
| OBS-01 | `pq_pivots_aud` | Fuera D1 v1 |
| OBS-02 | Export server-side | Fuera D1 v1 |
| OBS-03 | Dictionary DB | Catálogo en BD tenant (desviación MONO documentada en C1) |
| OBS-04 | Tabla dinámica Excel | Limitaciones DX; toast `pivotExport.pivotTableLimited` |
| OBS-05 | OpenAPI anotado | Contratos en TR; anexo OpenAPI global pendiente de sincronización |
| OBS-06 | PHPUnit tenant | Tests pivot omitidos si no hay SQL Server tenant `desarrollo` |

## Fuera de alcance confirmado

- MVP portal release actual (feature flags default `false`).
- PDF pivot (`pdfHabilitado`).
- ABM web de catálogos pivot.
- Bootstrap destructivo de tablas ERP.

## Veredicto

**F formal cerrado** — SPEC-001-08 listo como epic implementado; pendiente solo **deploy/activación** productiva y QA manual en tenant con datos reales.






