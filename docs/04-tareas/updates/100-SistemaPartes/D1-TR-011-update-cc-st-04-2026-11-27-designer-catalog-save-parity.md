# Plan de implementación — TR-011-update CC-ST-04

Jira: [PIAPARTES-82](https://paqsystems.atlassian.net/browse/PIAPARTES-82)

| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-011-update CC-ST-04](../../../05-open-spec/updates/100-SistemaPartes/SPEC-011-update-cc-st-04-2026-11-27-designer-catalog-save-parity.md) |
| **HU** | [HU-011-update CC-ST-04](../../../03-historias-usuario/updates/100-SistemaPartes/HU-011-reportes-emisiones-update-cc-st-04-2026-11-27-designer-catalog-save-parity.md) |
| **TR** | [TR-011-update CC-ST-04](./TR-011-update-cc-st-04-2026-11-27-designer-catalog-save-parity.md) |
| **Parte SDD** | **D1** — 27/11/2026 |
| **A1** | [A1 CC-ST-4](../../../05-open-spec/updates/100-SistemaPartes/SPEC-011-update-cc-st-04-2026-11-27-designer-catalog-save-parity-A1-ambiguity-review.md) — apto; **D-ST4-A1-01 = opción A** |

---

## Alcance entendido

Cerrar CC ST **#4**: alineación entre **catálogo shell** (`GET …/design/processes/{processCode}/reports`) y **diálogo Save/Save As** de DevExpress, que hoy solo lista la intersección `data/layouts/*.repx` ∩ URLs de `report-storage-urls`.

**Estrategia cerrada (opción A):** el host ejecuta **hydrate idempotente BD → sidecar** al **confirmar proceso** y **hydrate puntual** al cambiar variante si aún no hay `.repx`. **No** modificar `FileReportStorage.GetUrls` en Framework v1.

**Adoptar GEN-15** (`EmissionReportDesignerPage`, APIs existentes). No segundo diseñador ni catálogo paralelo.

**Fuera de alcance:** CC ST #3 (schema/Dataset); migración masiva de `report_code`; flag «forzar desde catálogo»; cambiar semántica global de `is_standard=1`. **D-ST4-A1-02 cerrada:** excepción **R-ST4-06b** solo Consulta detallada.

---

## Fuentes leídas

- SPEC / HU / TR update CC-ST-04 (27/11/2026)
- [mapa-rutas-disenador-emisiones-gen15.md](../../../06-operacion/mapa-rutas-disenador-emisiones-gen15.md)
- [persistencia-layouts-emisiones-dx.md](../../../06-operacion/persistencia-layouts-emisiones-dx.md)
- Código Partes: `EmissionsController::designReports` / `designReportStorageUrls`, `SpEmissionRepository::reportsForDesignCatalog` / `layoutFromRow` / `updateDesignReportLayout`, `EmissionReportDxStorageUrl`, `ReportDesignerHostPage`, `layoutPendingReviewApi` (proxy `/api/layout/*` solo navegador)
- Código Framework: `FileReportStorage` (`GetUrls`, `SetData`, `TryReadXml`, `SaveXml` privado), `LayoutApplyController` (apply exige REPX existente — **no** sirve para hydrate desde cero)
- SDK: `EmissionReportDesignerPage` — confirmación proceso sin callback host; `designerReady` tras catálogo + schema DX

---

## Impacto esperado

### Base de datos

| Acción | Detalle |
|--------|---------|
| **Sin** migración | Lectura de `pq_emission_reports.layout_definition` / flags existentes |
| **Sin** SP nuevo | Reutilizar acceso vía `SpEmissionRepository` (excepción TR documentada: lectura catálogo diseño ya en repository) |
| Opcional D | Método repository `listDesignLayoutsForHydrate(processCode)` que devuelva `reportCode`, `reportUrl` canónico, `isStandard`, XML válido |

### Backend (Partes)

| Crear / modificar | Rol |
|-------------------|-----|
| `POST /api/v1/emissions/design/processes/{processCode}/reports/hydrate-sidecar` | Auth: usuario + `emission.design` + proceso activo. Body opcional `{ "reportCodes": string[] }`. Respuesta GEN-20: `{ hydrated, skipped, failed: [{ code, reason }] }` |
| Servicio `EmissionSidecarLayoutHydrator` (nombre orientativo) | Por fila: `reportUrl` canónico; XML desde BD o, si **R-ST4-06b** (`partes.informes.consultaDetallada` + `is_standard` sin REPX), desde `resources/emissions/partes_consultaDetallada_principal.repx`; **R-ST4-05** skip si ya hay `.repx`; **R-ST4-06** omitir otros `is_standard` sin XML |
| Cliente HTTP sidecar | Extender patrón `PartesDxReportingHttpClient`: base `PAQSUITE_DX_REPORTING_SIDECAR_URL`, header acordado (mismo token que `report-storage-urls` / `X-Paq-Sidecar-Schema-Token` en lab) |
| `routes/api.php` + `EmissionsController` | Ruta + delegación |
| `OpenApiPathsEmissions.php` | Documentar endpoint hydrate |

**Lectura layout por variante:** usar la misma regla que emisión (`layoutFromRow` + fallback consulta detallada solo donde ya aplica hoy). No duplicar lógica de paquete horas en hydrate sin tests.

**Envelope errores:** sidecar caído / timeout → catálogo existente `PaqSuiteEnvelopeCatalog` (p. ej. validación / transport); no inventar 47xx sin registrar en catálogo host.

### Backend / Framework (sidecar — coordinación v1)

| Crear | Rol |
|-------|-----|
| `POST api/layout/put` (nombre TR; alternativa `hydrate`) | Body: `reportUrl`, `layoutXml`. Si archivo `.repx` **ya existe** → `{ ok: true, skipped: true }` sin sobrescribir (**R-ST4-05**). Si no existe → escribir UTF-8 en `data/layouts/{SafeUrl}.repx` vía lógica compartida con `SaveXml` / método público `TryWriteXmlIfMissing` en `FileReportStorage` |
| Auth | Mismo esquema que field-schema / apply interno (token config `PaqSuite:SidecarSchemaToken` o middleware existente) |

**Prohibido en v1:** cambiar `GetUrls`, `FilterUrlsForProcess` o `BackendDesignerReportCatalogClient`.

**Despliegue:** sidecar Framework debe actualizarse en Forge/lab **antes o junto** con Partes que llame `layout/put`; documentar en persistencia-layouts §hydrate.

### Frontend (Partes web)

| Crear / modificar | Rol |
|-------------------|-----|
| `emissionDesignHydrateApi.ts` | `postHydrateSidecar(processCode, reportCodes?)` → nuevo endpoint Laravel |
| `ReportDesignerHostPage.tsx` | Orquestar hydrate sin fork del shell GEN |

**Problema de cableado:** `EmissionReportDesignerPage` no expone hook post-confirmación. **Plan preferido (mínimo drift GEN):**

1. **Framework** (`@paqsuite/react-core`): prop opcional `onProcessConfirmed?: (processCode: string) => void | Promise<void>`. Invocar en el `onClick` de confirmar **antes** de que el `useEffect` de reportes complete; mostrar `LoadPanel` mientras `onProcessConfirmed` + fetch catálogo (combinar flags `loading` existentes).
2. Host Partes: `onProcessConfirmed={(code) => hydrateBatch(code)}`; en cambio de variante (si el SDK no expone evento): **alternativa host** — nueva prop opcional `onReportSelected?: (report: EmissionReportRef) => void` **o** hydrate puntual desde `renderDesigner` cuando `context.reportCode` cambia y `HEAD`/GET sidecar vía Laravel indica missing (preferir una sola llamada `hydrate-sidecar` con `reportCodes: [code]`).

**Sin fork** de toolbar/selects del SDK. Si el pin Partes no puede subir react-core a tiempo: **plan B temporal** (solo lab, no merge prod) — wrapper duplicado **rechazado** por TR; bloquear D hasta prop SDK o bump acordado.

| i18n | Claves producto `partes.emissionDesign.hydrate.*` (cargando, error parcial, sidecar down) en 5 locales |
| Remount | Tras hydrate puntual exitoso: incrementar `designerRemountKey` (patrón SC existente) |
| Mobile | Sin cambios (diseñador excluido) |

### Tests

| Capa | Casos |
|------|--------|
| **PHPUnit Feature** | `POST hydrate-sidecar` 401 sin auth; 4709 sin `emission.design`; 200 con contadores; idempotencia (`hydrated`→0, `skipped`→N en 2ª llamada); skip `is_standard` sin XML; mock HTTP sidecar (Http::fake `layout/put`) |
| **PHPUnit Unit** | Selección filas elegibles; mapeo `EmissionReportDxStorageUrl` estable |
| **Vitest** | `emissionDesignHydrateApi`; host: mock confirm llama hydrate antes de montar panel (con prop SDK mockeada) |
| **E2E** | Opcional humo post-D; CA-ST4-01 manual con capturas (F1) |
| **Framework sidecar** | Test integración `layout/put` no sobrescribe; escribe si missing |

```text
cd backend && php artisan test --filter=HydrateSidecar
cd backend && php artisan test --filter=ApiV1EmissionsPartes
cd frontend && npx vitest run src/features/partes/informes
```

### Documentación

- Actualizar [mapa-rutas](../../../06-operacion/mapa-rutas-disenador-emisiones-gen15.md): fila `POST hydrate-sidecar` + `POST api/layout/put`
- [persistencia-layouts-emisiones-dx.md](../../../06-operacion/persistencia-layouts-emisiones-dx.md): sección «Hydrate batch (CC ST #4)»
- TR-update / D-VERIFICACION: pegar counts checklist post-implementación
- Manual usuario (línea SPEC): catálogo vs disco sidecar
- Si bump SDK: ritual bump regla **43** + pin `package.json` / lock

### DevOps

- Variables: `PAQSUITE_DX_REPORTING_SIDECAR_URL`, token sidecar alineado con ASP.NET
- Forge: desplegar sidecar con endpoint `layout/put` + reinicio
- Sin migrate/seed obligatorio en Partes
- Smoke: confirmar proceso → counts shell = urls = .repx (checklist mapa)

---

## Orden de trabajo

1. **T0 — Sidecar (Framework)** — `TryWriteXmlIfMissing` + `POST api/layout/put` + test + README sidecar; publicar/desplegar sidecar en lab.
2. **T1 — BE Partes** — hydrator service + endpoint + OpenAPI + Feature tests con Http::fake.
3. **T2 — SDK (Framework npm)** — props `onProcessConfirmed` (+ opcional `onReportVariantChange`); test flow existente; publish beta; pin Partes.
4. **T3 — FE Partes** — `emissionDesignHydrateApi` + cableado `ReportDesignerHostPage` + i18n + Vitest.
5. **T4 — Docs / evidencia** — mapa, persistencia, D-VERIFICACION checklist; capturas CA-ST4-01.
6. **T5 — Regresión** — `php artisan test --filter=Emission`; emit remote smoke; Save As runbook PR139.

Alineado a TR-update T2–T5 con T0 sidecar explícito (dependencia crítica).

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| No existe API sidecar para escribir XML sin REPX previo | T0 `layout/put` (no usar `layout/apply`) |
| SDK sin callback → no cumplir R-ST4-02 “tras confirmar” | Prop GEN-15 en react-core; no fork host |
| Desalineación `SafeUrl` C# vs `EmissionReportDxStorageUrl` PHP | Tests unitarios cruzados + checklist counts |
| Hydrate desde FE directo a `/api/layout` | **Prohibido** — token y reglas en Laravel |
| Sobrescribir trabajo local en sidecar | Solo `put` if-not-exists; R-ST4-05 |
| `is_standard=1` sin XML | R-ST4-06 omitir; Consulta detallada usa R-ST4-06b (plantilla host) |
| CC #3 mezclado en PR | Rama/PR solo CC #4 |
| Modo `fake` / sin sidecar | Endpoint responde error claro; diseñador stub sin CA-ST4-01 |
| Bump SDK retrasado | Bloquear merge FE hasta pin con prop |

---

## Tests a ejecutar

Ver tabla § Tests. Mínimo antes de F1:

- Feature hydrate idempotente + authz
- Unit URL canónica
- Vitest API client + integración host (mock)
- Manual lab: CA-ST4-01, CA-ST4-02, CA-ST4-06

---

## Dudas / bloqueos

| ID | Tema | Estado | Propuesta D1 |
|----|------|--------|----------------|
| **D-ST4-A1-02** | `is_standard=1` sin XML en BD | **Cerrada** | **R-ST4-06** general + **R-ST4-06b** solo `partes.informes.consultaDetallada` desde `resources/emissions/partes_consultaDetallada_principal.repx` |
| **B1** | Nombre ruta sidecar | Cerrar en D | `POST api/layout/put` documentado en OpenAPI sidecar |
| **B2** | Prop SDK vs esperar bump | Decisión en D | **Recomendado:** prop en react-core ≥ pin oleada Partes (`2.4.30-beta.21` + patch) |

**Bloqueante único:** merge productivo FE depende de **T0 sidecar** + **T2 prop SDK** (o acuerdo explícito de excepción — no recomendado).

---

## Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR update CC-ST-04: **Sí**
- No toca CC ST #3, pivot, mobile, emisión fuera de regresión: **Sí**
- No modifica `GetUrls` Framework v1: **Sí**
- GEN-15 adoptado; hydrate es dominio host + contrato sidecar mínimo: **Sí**

---

**Estado D1:** listo. Esperar autorización explícita para **parte D** (implementar). Orden sugerido: **T0 → T1 → T2 → T3**.