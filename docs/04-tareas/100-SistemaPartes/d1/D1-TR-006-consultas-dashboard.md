# Plan de implementación - TR-006

## Alcance entendido

Consulta detallada + agrupadas (1 pantalla + eje + día/mes) + Pivot en Informes; dashboard MVP (top N, refresh); post-login → `/partes`; menú `pq_menus` Inicio/Informes; params `PartesDashboardTopN` / `PartesDashboardRefreshSeg`.

## Fuentes leídas
- SPEC-006, HU-006, TR-006 (C1 OK)

## Impacto esperado
### Base de datos
- SP `pq_sp_partes_informe_agrupado`, `pq_sp_partes_dashboard_snapshot` (+ reuso list)
- Seed params + menú Inicio/Informes

### Backend
- `GET /partes/informes/tareas|agrupado`, `GET /partes/dashboard`, `GET /partes/parametros/dashboard`
- Empty = 200

### Frontend
- 3 pantallas; toggle Pivot; timer dashboard; redirect post-login
- i18n `partes.consulta.*` / `partes.dashboard.*`

### Tests
- Feature delimitación; Vitest timer/periodo; E2E dashboard humo

### Documentación
- OpenAPI informes/dashboard

### DevOps
- migrate SP + seed

## Orden de trabajo
1. SP + params + menú
2. API
3. FE pantallas + pivot + post-login
4. Tests

## Riesgos
- Race timer vs periodo → cancel/seq
- Pivot dataset grande → filtros fecha obligatorios

## Tests a ejecutar
- Feature informes/dashboard; E2E login→dashboard

## Dudas / bloqueos
- **Bloqueo D:** TR-002…005 (identidad, menú Archivos, list tareas, ítem masivo)
- Post-login producto = **`/partes`** (override local vs Framework `/dashboard`)
- Gráfico dashboard = Should (no bloquea AC)

## Confirmación de alcance
- Sin cambio fuera SPEC/HU/TR: **Sí** (export Excel fuera)
