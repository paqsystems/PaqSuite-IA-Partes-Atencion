# Handoff GEN-15 — Save As → catálogo Framework

**Origen:** chat Partes (2026-08-26).  
**Destino:** SoT en repo **PaqSuite-IA-FRAMEWORK**; Partes solo adopta.

## Decisión

| Capa | Responsabilidad |
|------|-----------------|
| **Framework** | `POST .../design/processes/{processCode}/reports`, `PUT .../layout`, `set-principal`; client `createEmissionDesignReport` / `updateEmissionReportLayout`; `EmissionReportDesignerPage` + `onDxReportSaved` (alta + refresh SelectBox) |
| **Host (Partes / smoke)** | Seed; menú/ruta; `renderDesigner` con widget DX + `Callbacks.ReportSaved` → `context.onDxReportSaved`; opcional normalización de `reportCode` por producto (p. ej. prefijo `partes.consultaDetallada.*`) |
| **Sidecar DX** | Solo `.repx` en storage local; **no** conoce `pq_emission_reports` |

## Referencias Framework (ya alineadas)

- Producto: `docs/02-producto/15-reportes-emisiones-update.md` (decisión **22**, tabla host/Framework)
- SPEC: `SPEC-001-15` §10–§11 (alta Save As)
- TR: `TR-GEN-15-dx-reporting-documental.md` / `TR-GEN-15-dx-reporting-disenador.md`
- Smoke API + test: `designCreateReport`
- Template host: mismas rutas design
- Ejemplo puente DX: `services/dx-reporting/host-examples/DxReportDesignerHost.tsx`
- Smoke FE: `apps/smoke-frontend/.../DxReportDesignerHost.tsx`
- README sidecar: flujo Save As → POST/PUT Laravel

## Partes (adopción)

Mantener en Partes:

- Endpoints Laravel de design (create/layout/set-principal) — copia de contrato GEN
- `DxReportDesignerApp` alineado con `host-examples/DxReportDesignerHost.tsx`: `parseDxReportSavedArgs` + `isUnknownEmissionReportCode` desde `@paqsuite/react-core` (≥ 2.4.2); `normalizeSaveAsReportCode` en `ReportSaving` (prefijo producto)
- Seed del proceso/reporte principal

No inventar ABM de reportes fuera de la superficie GEN.

## Publicación

Partes adopta `@paqsuite/react-core` **^2.4.2** (Verdaccio). Instalar con `--legacy-peer-deps` por convivencia DX 25.2 / reporting 26.1.
