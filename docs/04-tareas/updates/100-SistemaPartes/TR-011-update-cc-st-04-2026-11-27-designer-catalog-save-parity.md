# TR-011-update — Paridad catálogo Save DX + hydrate layout

Jira: [PIAPARTES-82](https://paqsystems.atlassian.net/browse/PIAPARTES-82)

| Campo | Valor |
|-------|--------|
| **HU** | [HU-011-update CC-ST-04](../../../03-historias-usuario/updates/100-SistemaPartes/HU-011-reportes-emisiones-update-cc-st-04-2026-11-27-designer-catalog-save-parity.md) |
| **SPEC** | [SPEC-011-update CC-ST-04](../../../05-open-spec/updates/100-SistemaPartes/SPEC-011-update-cc-st-04-2026-11-27-designer-catalog-save-parity.md) |
| **Origen** | `00-ControlCalidad-ST` · CC #4 · **27/11/2026** |
| **Estado** | Pendiente de Revisión (parte D 27/11/2026) |
| **TR base** | [TR-011](../../100-SistemaPartes/TR-011-reportes-emisiones.md) |
| **D1** | [D1-TR-011-update CC-ST-04](./D1-TR-011-update-cc-st-04-2026-11-27-designer-catalog-save-parity.md) (27/11/2026) |
| **Mapa rutas** | [mapa-rutas-disenador-emisiones-gen15.md](../../../06-operacion/mapa-rutas-disenador-emisiones-gen15.md) |

## Objetivo técnico

Cerrar la brecha **catálogo Laravel (shell)** ↔ **FileReportStorage (Save DX)** mediante **hydrate batch (D-ST4-A1-01 opción A)** al confirmar proceso + hydrate puntual al cambiar variante, sin cambiar `GetUrls` en Framework en v1.

**Decisión cerrada:** D-ST4-A1-01 = **A** (27/11/2026).

## Tareas

### T1 — Auditoría / diagnóstico (cerrado en G)

- [x] Documentar mapa FE → Laravel → sidecar → DXXRD ([mapa-rutas](../../../06-operacion/mapa-rutas-disenador-emisiones-gen15.md)).
- [ ] Ejecutar checklist counts en lab DEV y pegar resultado en D-VERIFICACION.

### T2 — Backend Partes (hydrate)

- [x] Contrato **`POST /api/v1/emissions/design/processes/{processCode}/reports/hydrate-sidecar`**: body opcional `reportCodes[]`; contadores `hydrated`, `skipped`, `failed[]`.
- [x] `EmissionSidecarLayoutHydrator` + `SpEmissionRepository::resolveDesignLayoutXmlForSidecarHydrate` (R-ST4-06 / R-ST4-06b).
- [x] Config token `paqsuite.emissions.designerSidecarToken` → header sidecar.
- [x] Feature tests `ApiV1EmissionsHydrateSidecarTest`.

### T3 — Frontend Partes

- [x] `emissionDesignHydrateApi` + `EmissionDesignHydrateGate` (batch al montar lienzo + puntual por `reportCode`).
- [x] Cableado en `ReportDesignerHostPage` (sin fork SDK; hydrate al abrir diseñador DX).
- [x] i18n `partes.emissionDesign.hydrate.*` (5 locales) + Vitest API.

### T4 — Framework sidecar (coordinación v1)

- [x] `POST api/layout/put` + `FileReportStorage.TryWriteXmlIfMissing` (**sin** cambio `GetUrls`).
- [ ] Alinear `EmissionReportUrlNormalizer` con `EmissionReportDxStorageUrl` (una canonical por `report_code`).
- [ ] Regresión Save As multi-proceso ([runbook PR139](../../../06-operacion/runbook-post-deploy-emission-designer-save-as-pr139.md)).

### T5 — Documentación y operación

- [ ] Actualizar [persistencia-layouts](../../../06-operacion/persistencia-layouts-emisiones-dx.md) §hydrate si cambia flujo.
- [ ] Manual SPEC-010 (diseñador): una línea sobre catálogo vs disco sidecar.

## Dependencias

- `PAQSUITE_EMISSION_DX_MODE=remote`, sidecar `:5055`, `PaqSuite:BackendBaseUrl`, token sidecar en lab/Forge.
- Pin SDK GEN-15 vigente en Partes.

## DoD

- CA-ST4-01 … CA-ST4-06 (SPEC-update).
- Tests Feature Partes verdes para cardinalidad + hydrate (mock o integración lab).
- Smoke manual: capturas shell + Save con K>2 variantes.
- Sin regresión emisión Consulta detallada.

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| GetUrls lista urls sin archivo | Preferir hydrate host; no Save a archivo fantasma |
| Duplicados punto/underscore | Tabla canonical en TR-011 §19; migración opcional |
| CC #3 schema | No mezclar PR; enlace trazabilidad |