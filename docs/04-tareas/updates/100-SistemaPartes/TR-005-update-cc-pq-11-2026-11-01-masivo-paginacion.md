# TR-005-update — Proceso masivo: paginación remota grilla — CC PQ #11

| Campo | Valor |
|-------|--------|
| **ID** | TR-005-update-cc-pq-11-2026-11-01-masivo-paginacion |
| **Estado** | Pendiente |
| **Prioridad** | P0 (regresión UX supervisión) |
| **TR base** | [TR-005-supervision-proceso-masivo](../../100-SistemaPartes/TR-005-supervision-proceso-masivo.md) |
| **HU-update** | [HU-005-update-cc-pq-11-2026-11-01-masivo-paginacion](../../../03-historias-usuario/updates/100-SistemaPartes/HU-005-update-cc-pq-11-2026-11-01-masivo-paginacion.md) |
| **SPEC-update** | [SPEC-005-update-cc-pq-11-2026-11-01-masivo-paginacion](../../../05-open-spec/updates/100-SistemaPartes/SPEC-005-update-cc-pq-11-2026-11-01-masivo-paginacion.md) |
| **Origen** | CC PQ **#11** · 01/11/2026 · `00-ControlCalidad-PQ` |
| **Última actualización** | 01/11/2026 |

---

## 1) Resumen

Corregir **Proceso masivo**: la grilla debe usar **paginación remota** DevExtreme alineada a `GET /api/v1/partes/tareas` (`page`, `pageSize`, `total`). Sin cambios de backend salvo verificación de contrato existente.

---

## 2) Criterios de aceptación (TR)

| ID | Criterio | HU |
|----|----------|-----|
| AC-11-01 | Pager N > 1 cuando `total > pageSize`; ids distintos por página | CA-11-01 |
| AC-11-02 | `totalFiltro` = `total` envelope | CA-11-02 |
| AC-11-03 | Requests con `pageSize` 10/20/40 | CA-11-03 |
| AC-11-04 | Group panel sin error silencioso | CA-11-04 |
| AC-11-05 | Selección + masivo API sin regresión | CA-11-05 |

---

## 3) Implementación frontend

| ID | Detalle |
|----|---------|
| RN-11-01 | Archivo principal: `frontend/src/features/partes/masivo/ProcesoMasivoPage.tsx`. |
| RN-11-02 | `dataSource`: `CustomStore` con `load(loadOptions)` → `listTareas({ ...filtros, page, pageSize })` donde `page = floor(skip/take)+1`, `pageSize = take` (default 20). |
| RN-11-03 | `ProcessDataGrid`: `remoteOperations` (bool); `<Pager displayMode="full" showNavigationButtons showInfo />`; `ref` para `refresh()` tras acciones masivas y cambio de filtros. |
| RN-11-04 | Helper puro `masivoTareasPaging.ts` + test Vitest (mapeo skip/take). |
| RN-11-05 | Al cambiar filtros (fechas, cliente, asistente, estado): `clearSelection()` + `grid.refresh()`; no fijar `page: 1` en estado local sin recargar store. |
| RN-11-06 | `setTotal` en callback `load` del store para leyenda bajo grilla. |
| RN-11-07 | Mantener `listTareaIds` para «seleccionar todos» (sin cambio). |

---

## 4) Backend / API

Sin cambio. Reutilizar listado existente documentado en TR-005 base.

Verificación manual: con filtro amplio, Network muestra `page=2` al avanzar pager.

---

## 5) Tests

| Tipo | Alcance |
|------|---------|
| Vitest | `masivoTareasPaging.test.ts` — mapeo página desde `loadOptions` |
| E2E | `partes-masivo-paging.spec.ts` — pager > 1, `page=2` en red; `partes-masivo-selection.spec.ts` — regresión tilde |
| Helper E2E | `tests/e2e/helpers/partesMasivoE2e.ts` — mocks auth/menú/layouts + handler listado |

### Parte E — ejecución (01/11/2026)

| Comando | Resultado |
|---------|-----------|
| `npm run test` (`frontend/`) | **191** tests OK (61 archivos), incl. `masivoTareasPaging.test.ts` |
| `npx playwright test tests/e2e/partes-masivo-paging.spec.ts` | **1** OK — 20 filas pág. 1, «Página siguiente», request `page=2`, filas `obs-21`…`obs-25` |
| `npx playwright test tests/e2e/partes-masivo-selection.spec.ts` | **1** OK — selección por fila estable |

**Cobertura automática vs AC:** CA-11-01 (E2E paging), CA-11-05 (E2E selection). CA-11-02 parcial (E2E no assert leyenda; lógica en `load` del store). CA-11-03 / CA-11-04 pendientes de smoke manual o Parte F.

---

## 6) DoD técnico

- [ ] AC-11-01…05 verificados en manual o E2E (01–05 parcial: ver §5 Parte E).
- [x] `npm run test` en `frontend/` OK (01/11/2026).
- [x] E2E masivo paging + selection OK (01/11/2026).
- [ ] Sin commit/push salvo autorización.
