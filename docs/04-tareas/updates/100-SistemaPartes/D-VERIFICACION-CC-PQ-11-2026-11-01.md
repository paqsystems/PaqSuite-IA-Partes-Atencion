# Verificación F1 + F — CC PQ #11 (01/11/2026)

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#11** |
| **Updates (pre-I)** | TR-005-update-cc-pq-11 · HU-005-update-cc-pq-11 · SPEC-005-update-cc-pq-11 |
| **Prueba manual** | Aceptada por PQ (01/11/2026) — preview develop post PR #114 |
| **Veredicto F1/F** | **Aprobado** |
| **Parte I** | 01/11/2026 — fusionado en SPEC/HU/TR-005 base; updates eliminados |
| **Parte I acotada (prod)** | 01/11/2026 — RN-CC11-03 ampliado en SPEC/HU/TR-005 base (sin recrear updates) |

## Evidencia

| AC | Resultado | Notas |
|----|-----------|-------|
| CA-CC11-01 | OK | Manual preview: pager >1; página 2 distinta de página 1 |
| CA-CC11-02 | OK | Manual: «Total filtro» coherente con total API |
| CA-CC11-03 | OK | Manual: tamaños 20 / 40 / 80 |
| CA-CC11-04 | OK | Manual: agrupación por tipo de actividad sin error |
| CA-CC11-05 | OK | Manual: selección y flujos masivos sin regresión |
| CA-CC11-01 (auto) | OK | E2E `partes-masivo-paging.spec.ts` |
| CA-CC11-05 (auto) | OK | E2E `partes-masivo-selection.spec.ts` |
| Mapeo página | OK | Vitest `masivoTareasPaging.test.ts` |

## F1 — revisión código ↔ TR

| RN / pieza | Evidencia | Estado |
|------------|-----------|--------|
| RN-CC11-01 `ProcesoMasivoPage.tsx` | `CustomStore` + `DataSource` paginado | OK |
| RN-CC11-02 `masivoGridLoadOptionsToQuery` | `masivoTareasPaging.ts` | OK |
| RN-CC11-03 `remoteOperations` + `Pager` | `ProcessDataGrid` en masivo | OK (preview #114) |
| RN-CC11-05 refresh filtros | `clearSelection` + `refreshGrid` en `useEffect` filtros | OK |
| Backend | Sin cambio (reuso `GET /partes/tareas`) | OK |

## Follow-up producción (01/11/2026)

| Campo | Valor |
|-------|--------|
| **Síntoma prod** | Tras promote #116, `paq.partesatencion…` seguía con pager en 1 página y «Total filtro» alto |
| **Causa** | `remoteOperations` global activaba **summary** remoto sin soporte en `CustomStore` (totalizadores de duración en pie) |
| **Fix** | PR [#117](https://github.com/paqsystems/PaqSuite-IA-Partes-Atencion/pull/117) — `<RemoteOperations paging summary={false} … />` + `pageSizes` 20/40/80 |
| **Preview** | PQ OK en `partesatencion-dev.paqsystems.com` |
| **Promote** | PR [#118](https://github.com/paqsystems/PaqSuite-IA-Partes-Atencion/pull/118) → `main` (`325edab`); Vercel prod bundle `index-X8rcuJn4.js` |
| **Veredicto prod** | **Aprobado** — PQ confirma OK en producción |

| RN / pieza (post-fix) | Evidencia | Estado |
|-----------------------|-----------|--------|
| RN-CC11-03 ampliado | `RemoteOperations` solo paging; summary/filter/sort/group en cliente | OK |

## Hallazgos críticos

- Ninguno (cerrado con follow-up prod).

## Observaciones

- Agrupación con paging remoto opera sobre **página actual** (limitación DevExtreme; documentada en SPEC-005 §4.3b).
- Deploy preview inicial: PR [#114](https://github.com/paqsystems/PaqSuite-IA-Partes-Atencion/pull/114) → `develop`; frontend Vercel; API Forge dev sin cambios de contrato.

## Deploy

- Solo frontend; sin migrate/seed ni SP nuevos por CC #11.
