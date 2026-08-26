# TR-011 – Reportes / emisiones — adopción GEN-15 en Consulta detallada

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-011-reportes-emisiones](../../03-historias-usuario/100-SistemaPartes/HU-011-reportes-emisiones.md) |
| **SPEC relacionada** | [SPEC-011-reportes-emisiones](../../05-open-spec/100-SistemaPartes/SPEC-011-reportes-emisiones.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Cliente / asistente / supervisor (emitir según menú); diseñar = `emission.design` + desktop |
| **Dependencias** | [TR-002](./TR-002-identidad-funcional-y-acceso.md); [TR-006](./TR-006-consultas-dashboard-navegacion.md) (`GET /partes/informes/tareas`, `pq_sp_partes_tarea_list`); [TR-007](./TR-007-mobile-capacitor.md) (policy native); paquetes `@paqsuite/react-core` + `paqsuite/laravel-core`; GEN-15 (SPEC-001-15 / TR-GEN-15-*) |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Pendiente de Revisión |
| **Revisión C1** | Apto con observaciones (ver §11) |
| **Última actualización** | 2026-08-25 (CC Q13 RN-TR-12 / CA-15) |

**Origen:** [HU-011](../../03-historias-usuario/100-SistemaPartes/HU-011-reportes-emisiones.md)  
**Referencia SPEC:** [SPEC-011](../../05-open-spec/100-SistemaPartes/SPEC-011-reportes-emisiones.md)  
**Producto:** [15-reportes-emisiones.md](../../02-producto/Sistema-Partes-IA/15-reportes-emisiones.md) (D-EM-01…10)

**Referencia GEN (checkout Framework):**

- `docs/02-producto/15-reportes-emisiones.md`
- `docs/05-open-spec/001-Generalidades/SPEC-001-15-reportes-emisiones.md`
- `docs/04-tareas/001-Generalidades/TR-GEN-15-*.md` (adopción, motor, ventana, diseñador, async, trazabilidad)
- FE: `packages/js/react-core/src/features/emissions/*` (`EmissionDialog`, `useEmission`, `EmissionReportDesignerPage`, `ReportDesignerHost`)
- BE: `EmissionDatasetPort`, `EmissionDatasetPortRegistry`, familia `/api/v1/emissions/`
- Smoke: `apps/smoke-frontend` `/demo/emisiones` + `EmissionsController` template

---

## 1) HU refinada (resumen)

### Narrativa

Como usuario con acceso a Consulta detallada quiero emitir PDF / impresión / Excel o CSV de reporte / mail+PDF del universo filtrado. Como usuario con permiso de diseño quiero ajustar el layout en desktop.

### In scope

- Adoptar motor GEN-15 (DDL/SP/params `Emission*` si el host aún no los tiene; rutas `/api/v1/emissions/*`; UI `EmissionDialog` + `EmissionReportDesignerPage`).
- Seed proceso **`partes.informes.consultaDetallada`**, `menu_process_code=partes_consulta_detallada`, canales Must, flags A1, reporte + plantilla principales.
- Puerto `PartesConsultaDetalladaEmissionPort::resolveDataset` → **`pq_sp_partes_tarea_list`** (todas las filas).
- `hostContext` en preview/job; FE snapshot de filtros de pantalla.
- `EmissionDialog` en Consulta detallada (grilla y pivot); `EmissionEnabled=S` al adoptar.
- Menú diseñador `partes_disenador_emisiones` → `/emisiones/disenador`.
- Policy native: deny diseñador; Consulta detallada ya denegada.
- Manual + tests (Feature, Vitest, E2E humo).

### Out of scope

- Redefinir GEN-15; Emitir en agrupadas / paquete / dashboard / carga / kardex; ZIP/segmentado; `resolveSegments` / `resolveMailRecipients`; ABM catálogo; matriz por reporte; recurrentes `13`; selector GEN-23; tipo menú `E`.

### Decisiones TR (cierran preguntas HU B1)

| Tema | Decisión |
|------|----------|
| `processCode` | **`partes.informes.consultaDetallada`** |
| Authz emitir | Permiso menú **`partes_consulta_detallada`** → GEN **4703** |
| Authz diseñar | **`emission.design`** → GEN **4709** |
| Snapshot filtros | Body **`hostContext`** (camelCase §5) en preview/jobs. **Prohibido** sesión de usuario como única fuente. `request()` solo en el HTTP que crea preview/job. Si hay `jobId`, persistir el snapshot atado a ese id y reutilizarlo en `runQueued` (el worker **no** tiene el body original). Preview síncrono (sin `jobId`) puede leer el body del mismo request. |
| SP / listado dataset | Reutilizar **`pq_sp_partes_tarea_list`** + mismos `p_actor_*`. Runtime host actual = `PartesTareaOperations::list` (clampa `pageSize` a 1…200). **Must de esta TR:** `p_page=1` y **`p_page_size=0` = sin paginar** (no aplicar `max(1,…)` ni `min(200,…)` a ese sentinel). Si existe T-SQL, la misma semántica. **No** SP paralelo ni `pageSize=9999`. |
| Columnas dataset | camelCase alineadas a la grilla TR-006: `fecha`, `clienteCode`, `clienteNombre`, `usuarioCode`, `usuarioNombre`, `tipoTareaCode`, `tipoTareaDescripcion`, `duracionMinutos`, `sinCargo`, `presencial`, `cerrado`, `observacion`, `erpCliente`, `erpArticulo`. **Sin** `diaSemana` (es enriquecimiento solo FE). |
| `EmissionEnabled` | Seeder Partes de adopción **pone `S`**. Umbrales 5 / 2000 / 30: insert-if-absent GEN; **no** pisar numéricos ya editados. |
| Selector grupo | `EmissionDialog` con **`permiteConsolidado={false}`** (prop GEN-23). El proceso sí declara modo consolidado (un documento). |
| Menú diseñador | id **`60300`**, padre `60000`, código **`partes_disenador_emisiones`**, ruta **`/emisiones/disenador`**. `process_type` **igual** a `partes_consulta_detallada` (hoy `A` en `PqMenuSeeder`); **nunca `E`**. |
| Roles `emission.design` | **No** CLIENTE ni ASISTENTE. Seed Must: permiso + menú `60300` al rol **`SUPERVISOR`** (usuarios demo `admin`/`PQ` ya van por ese rol). Otros roles → ABM seguridad, no esta TR. |
| mailTo / DX | Canal mail Must en camino **síncrono** (`mailTo` del body GEN). **No** implementar `MailRecipientsEmissionDatasetPort`. Si GEN async re-resuelve destinatarios y pierde `mailTo`, D1 documenta el gap; el Feature Must de mail usa N filas **<** `EmissionAsyncMaxRows`. Sin modal Partes paralelo. Runtime DX: paquete; FakeDx = smoke stub. |
| Filtros UI faltantes | Consulta detallada hoy solo tiene periodo. Esta TR **monta** filtros de pantalla SPEC-006 que ya expone el API (`clienteId`, `usuarioId` solo supervisor, `tipoTareaId`, `estadoCerrado`) para que el snapshot sea completo. |

---

## 2) Criterios de aceptación (AC)

Mapear HU CA-01…21:

| AC | Verificación técnica |
|----|----------------------|
| CA-01 | Seed `PQ_EMISSION_PROCESSES` + canales pdf/print/excel/csv/mail; zip=0; segmentado=0; `requiere_vista_previa=0`; `menu_process_code=partes_consulta_detallada`; 1 reporte + 1 plantilla principales |
| CA-02 | `GET /api/v1/emissions/processes/partes.informes.consultaDetallada`; sin puerto → **4706** |
| CA-03 | `ConsultaDetalladaPage` abre `EmissionDialog` (`data-testid` GEN `emissions.dialog`); preview no bloquea (`requiresPreview=false`); visible en `mode=grid` y `mode=pivot` |
| CA-04 | `disabled` si `loading` o **`total===0`** del último Buscar (universo API, no filter-row ni página vacía con total>0); visible |
| CA-05 | `EmissionEnabled=N` → no montar diálogo ni página diseñador; API → **4704** (`emission.capabilityDisabled`) |
| CA-06 | Usuario menú consulta sin `emission.design` emite PDF; design → **4709** |
| CA-07 | Dataset = SP list + `hostContext`; todas las filas; rol en servidor; columnas §1; duración `hh:mm` en layout DX (campo minutos + formato) |
| CA-08 | Canales seed; UI no ofrece zip ni segmented |
| CA-09 | `permiteConsolidado={false}` en el dialog (sin selector grupo) |
| CA-10 | Export grilla/pivot GEN-11/12 intacto |
| CA-11 | Mail: plantilla breve + PDF; `mailTo` GEN; sin `MailRecipientsEmissionDatasetPort` |
| CA-12 | Writer GEN `source=emission`; sin tabla Partes; async umbral OR GEN |
| CA-13 | Menú 60300 + ruta; native deny `/emisiones` |
| CA-14 | Design 4709; emitir sin menú 4703 |
| CA-15 | `EmissionReportDesignerPage` **sin** `processCode` fijo; lista GEN + confirmación (C1-15-36; no skip N=1); seed tabular §1; opcional `initialProcessCode` / `?processCode=` solo preselecciona |
| CA-16 | Ruta diseñador no exige menú consulta (solo `emission.design` + ítem menú) |
| CA-17 | `partesMobilePolicy`: denylist `/emisiones`; consulta detallada ya deny |
| CA-18 | No montar `EmissionDialog` en agrupadas / paquete / dashboard |
| CA-19 | Seed menú ≠ `E` |
| CA-20 | Párrafo en `docs/99-manual-usuario/Partes-Atencion.md` |
| CA-21 | Feature puerto/seed + Vitest gates + E2E humo Emitir / diseñador gated |

### Escenarios Gherkin

Los 6 de HU-011 (PDF universo, vacío/capacidad off, pivot no recorta, permisos, mail/impresión, diseñador/mobile). No se reescriben aquí.

---

## 3) Reglas de negocio / implementación

| ID | Implementación host |
|----|---------------------|
| RN-TR-01 | Si el host no tiene migraciones `PQ_EMISSION_*` / SP emisiones / seed params / `emission.design` / jobs `EMISSION_BATCH`+`PURGE`: adoptarlos del paquete `laravel-core` (mismo patrón TR-009 Excel). |
| RN-TR-02 | Seeder proceso Partes: `process_code=partes.informes.consultaDetallada`, `permite_consolidado=1`, `permite_segmentado=0`, `requiere_vista_previa=0`, canales pdf/print/excel/csv/mail=1, zip=0, `menu_process_code=partes_consulta_detallada`, `is_active=1`. |
| RN-TR-03 | Seed reporte `partes.consultaDetallada.principal` (`is_principal=1`) layout tabular columnas §1; plantilla mail `partes.consultaDetallada.mail` cuerpo breve + variables mínimas (sin tabla de partes). |
| RN-TR-04 | `EmissionEnabled=S` en seeder de **adopción Partes** (update de esa clave). Numéricos: solo insert-if-absent. |
| RN-TR-05 | `AppServiceProvider`: `EmissionDatasetPortRegistry::register('partes.informes.consultaDetallada', PartesConsultaDetalladaEmissionPort)`. |
| RN-TR-06 | Puerto: `resolveDataset` obtiene `hostContext` (§1 persistencia jobId), aplica actor `EnsurePartesFunctionalProfile`, llama `pq_sp_partes_tarea_list` vía SpCaller / **`PartesTareaOperations`** (mismo contrato que `PartesInformeController::listTareas`), camelCase, **sin** paginar. No implementa `SegmentedEmissionDatasetPort` ni `MailRecipientsEmissionDatasetPort`. |
| RN-TR-07 | `hostContext` Must: `{ fechaDesde, fechaHasta, clienteId, usuarioId, tipoTareaId, estadoCerrado }`. Fechas ISO `yyyy-MM-dd` **requeridas**; faltantes/inválidas → **4701**. Ids `number \| null`. `estadoCerrado`: `'todas' \| 'abiertas' \| 'cerradas'` (default `'todas'`). `usuarioId` ignorado si `!esSupervisor`. Filtros **no amplían** el universo de rol (misma `filteredQuery` que el informe). Snapshot al confirmar Emitir = **controles de pantalla actuales**, no el último Buscar ni el estado visual de grilla/pivot. |
| RN-TR-08 | Rutas `/api/v1/emissions/*` como template GEN (`EmissionsController` thin del host o del paquete) + `auth:sanctum` + `paqsuite.instalacion` + `partes.profile`. Emitir: gate permiso **`partes_consulta_detallada`**. Design: **`emission.design`**. |
| RN-TR-09 | FE `ConsultaDetalladaPage`: filtros de pantalla (DateBox existentes + SelectBox cliente / tipo / cerrado; asistente solo supervisor). `Buscar` → `fetchInformeTareas` con esos query params (ya soportados por TR-006). |
| RN-TR-10 | Botón **Emitir** en `toolbarLeading` de `ProcessDataGrid` **y** en `leadingSlot` de `PivotLayoutsBar` (Must visible en ambos modos). `disabled={loading \|\| total===0}` (`total` del envelope del último Buscar). No montar si `isNativeApp()` o `EmissionEnabled=No`. |
| RN-TR-11 | `EmissionDialog` `processCode="partes.informes.consultaDetallada"` `permiteConsolidado={false}` `isNative={false}`. Al emitir/preview, el host envía `hostContext` del estado de filtros **en ese momento** (extender `createEmissionJob` / `createEmissionPreview` si `useEmission` no lo acepta). |
| RN-TR-12 | Página host en `/emisiones/disenador` monta **`EmissionReportDesignerPage`** (export canónico GEN). **Prohibido** hardcodear `processCode` como única entrada. Opcional: mapear query `?processCode=` → `initialProcessCode` (preselección; no auto-confirma). `isNative` → excluded GEN. `renderDesigner` = inyección DX del host o placeholder GEN si el paquete aún es stub. Tras bump `react-core` con CA-09..12 GEN. |
| RN-TR-13 | `PqMenuSeeder`: ítem 60300. `partesMobilePolicy`: prefijo deny **`/emisiones`**. |
| RN-TR-14 | Envelope `respuesta`: claves GEN **`emission.*`** (catálogo PHP, p. ej. `emission.forbidden`). UI/testids smoke: **`emissions.*`**. Copy Partes `partes.informe.emitir` solo en el botón toolbar. Host testid `partesConsultaDetalladaEmit`. |
| RN-TR-15 | Controles DevExtreme; caption filtros a la izquierda (regla formularios). Selectores de catálogo: código + descripción + «Cargando…» (regla 29). |
| RN-TR-16 | OpenAPI: documentar adopción familia `/emissions/*` + campo `hostContext` en jobs/preview (tag Partes Informes / Emisiones). |
| RN-TR-17 | Manual usuario: Emitir vs export grilla; diseñador bajo Parámetros; no mobile. |
| RN-TR-18 | Acceso datos Must SP. Sin Eloquent CRUD de tareas. |

---

## 4) Impacto en datos

| Pieza | Detalle |
|-------|---------|
| Tablas GEN | `PQ_EMISSION_PROCESSES` / `REPORTS` / `MAIL_TEMPLATES` (+ jobs GEN si aplica) — migrate paquete |
| SP GEN | `pq_sp_emission_*` del paquete |
| SP / operations | **`pq_sp_partes_tarea_list`** + **`PartesTareaOperations::list`**: `p_page_size=0` = todas las filas (sin clamp 1…200). El GET informe **sigue** paginando (pageSize default 50). |
| Seed | Proceso + reporte + plantilla Partes; `EmissionEnabled=S`; menú 60300; permiso `emission.design` (GEN) sin rol cliente |
| Params | Programa `Emission`; no umbrales hardcode |
| Rollback | Desregistrar puerto; `EmissionEnabled=N` o inactivar proceso; no borrar bitácora GEN |

---

## 5) Contratos de API

Familia GEN **`/api/v1/emissions/`** — Bearer + `X-Paq-Cliente`. Envelope MONO (`error` entero, `respuesta` i18n, `resultado` **nunca** `null`). No redefinir códigos `4701–4712`. Sin token → **401** (no 4703). Capacidad off → **4704**. Download de artefacto: binario GEN (o **4711**); no inventar wrapper Partes. Job async: HTTP según GEN (típicamente **202** + `status=queued`).

| Método | Path | Authz |
|--------|------|-------|
| GET | `/emissions/processes/{codigo}` | Menú `partes_consulta_detallada` |
| POST | `/emissions/preview` | Idem + desktop |
| POST | `/emissions/jobs` | Idem |
| GET | `/emissions/jobs/{jobId}` | Idem |
| GET | `/emissions/jobs/{jobId}/download` | Idem (binario) |
| GET/PUT/POST | `/emissions/design/...` | `emission.design` |

**Extensión Must `hostContext`** en POST preview y POST jobs (además del DTO GEN):

```ts
type ConsultaDetalladaHostContext = {
  fechaDesde: string
  fechaHasta: string
  clienteId: number | null
  usuarioId: number | null
  tipoTareaId: number | null
  estadoCerrado: 'todas' | 'abiertas' | 'cerradas'
}
```

Códigos a respetar (GEN): **4701** validación (`hostContext` / `mailTo` vacío en canal mail); **4703** forbidden menú host; **4704** capacidad off; **4706** sin puerto; **4709** design forbidden.

`GET /api/v1/partes/informes/tareas` (TR-006) **no cambia de contrato**; el puerto reutiliza el mismo SP.

---

## 6) Frontend

| Pieza | Detalle |
|-------|---------|
| Host emitir | `frontend/src/features/partes/informes/PartesConsultasPages.tsx` (`ConsultaDetalladaPage`) |
| Dialog | `EmissionDialog` + `useEmission` `@paqsuite/react-core` |
| Diseñador | Nueva página + ruta en `AppRouter.tsx` |
| Filtros | Periodo + cliente + tipo + cerrado + asistente (supervisor) → mismo `load()` |
| Emitir | Toolbar grilla y pivot; `data-testid="partesConsultaDetalladaEmit"` |
| Gates | Native / `EmissionEnabled` / 0 filas |
| i18n | `emissions.*` + `partes.informe.emitir` |
| Mobile | Deny `/emisiones` en `partesMobilePolicy.ts` |

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD | Deps | Est. |
|----|------|-------------|-----|------|------|
| T1 | DB | Migraciones/SP GEN-15 en host + sentinel `p_page_size=0` en `PartesTareaOperations::list` (y T-SQL si aplica) | migrate + list all rows sin clamp 200 | — | L |
| T2 | Seed | Params Emission* + proceso/reporte/plantilla + menú 60300 + `EmissionEnabled=S` + permiso diseño (no cliente) | seed idempotente; GET process 200 | T1 | M |
| T3 | Backend | Registry puerto + rutas `/emissions/*` + `hostContext` | Feature: 4706 sin puerto; dataset respeta filtros/rol | T2 | L |
| T4 | Backend | `PartesConsultaDetalladaEmissionPort` → SP list sin paginar | Feature: todas las filas; cliente no ve otros | T3 | L |
| T5 | Frontend | Filtros pantalla + botón Emitir grilla/pivot + `EmissionDialog` + `hostContext` | CA-03,04,07–11 | T3 | L |
| T6 | Frontend | Página diseñador (`EmissionReportDesignerPage`) + ruta + policy native; sin hardcode proceso | CA-13…17 | T2 + pin react-core con selección proceso | M |
| T7 | Docs | OpenAPI `hostContext` + i18n + manual | CA-20 | T5,T6 | S |
| T8 | Tests | Feature + Vitest + E2E humo PDF/disabled/export/gated design | CA-21 | T4–T6 | M |

**Orden:** T1 → T2 → T3 → T4 → T5 → T6 → T7 → T8 (T6 puede ir en paralelo a T5 tras T2).

---

## 8) Estrategia de tests

| Capa | Casos |
|------|-------|
| Feature BE | Seed process; GET process; POST jobs PDF con `hostContext`; 401 sin token; sin puerto **4706**; sin menú **4703**; design **4709**; `EmissionEnabled=N` → **4704**; fechas vacías → **4701**; cliente no ve otras orgs; `p_page_size=0` N>pageSize grilla y N<async max; mismo snapshot en 2ª resolución (jobId) |
| Unit FE | Emitir disabled si 0 filas/loading; no montar native; `hostContext` toma filtros actuales; no montar dialog en agrupadas |
| E2E | Login con menú consulta → Consulta detallada → Buscar con datos → `partesConsultaDetalladaEmit` abre `emissions.dialog`; vacío → disabled; usuario sin diseño no entra `/emisiones/disenador`; con diseño: lista proceso visible (`emission.design.process`) → confirm → DX/stub |
| Manual | PDF real o stub DX; mail si GEN trae `mailTo`; diseñador desktop con `emission.design` + confirmación de proceso (N=1) |

---

## 9) Riesgos y edge cases

| Riesgo | Mitigación |
|--------|------------|
| `laravel-core@^1.3.3` / `react-core@2.2.1` sin GEN-15 exportado | D1 verifica exports (`EmissionDialog`, registry, migraciones); **bump pin** Satis/Verdaccio si falta |
| `EmissionContext` / job GEN sin `hostContext` | Persistencia host por `jobId` (§1); `request()` solo en el HTTP de alta |
| `useEmission` no reenvía extra fields | Wrapper host de `createEmissionJob` / preview |
| FakeDx / designer stub | Smoke PDF aceptable; documentar gap DX real |
| `EmissionEnabled` GEN insert-if-absent = N | Seeder Partes **update a S** (solo esa clave) |
| Confundir Excel grilla vs emisión | Botón i18n distinto; conviven |
| `process_type` SPEC `C` vs seeder `A` | Misma columna que Consulta detallada; nunca `E` |
| Filtros UI nuevos vs TR-006 incompleto en pantalla | Esta TR los monta (decisión §1) |
| Lote > umbral async | Comportamiento GEN (202 + bandeja); no inbox Partes |

---

## 10) Checklist DoD

- [x] CA-01…21 HU (Must de esta TR; E2E Playwright y `npm run test:all` pendientes de entorno Node en Parte E)
- [x] Proceso seed + puerto + `hostContext`
- [x] Rutas `/api/v1/emissions/*` + 401/4701/4703/4704/4706/4709
- [x] Emitir en Consulta detallada (grilla y pivot); empty disabled
- [x] Dataset = SP list todas las filas + rol
- [x] Diseñador `/emisiones/disenador` + menú 60300 + native deny
- [x] `EmissionEnabled=S`; sin ZIP/segmentado/GEN-23
- [x] Manual + OpenAPI + tests Feature (Vitest/E2E: Parte E)
- [x] Sin historial Partes; sin menú `E`

---

## Discrepancias SPEC / HU

| Tema | Resolución TR |
|------|----------------|
| SPEC i18n `emission.*` vs smoke `emissions.*` | Envelope `respuesta` = **`emission.*`** (catálogo PHP); UI/testid = **`emissions.*`** (C1) |
| SPEC menú `tipo_proceso=C` vs `PqMenuSeeder` `A` | Físico = mismo tipo que Consulta detallada; **nunca `E`** (CA-19) |
| `POST /jobs` GEN sin filtros | Extensión `hostContext` (§5); puerto no usa sesión |

Ninguna cambia el alcance Must.

---

## 11) Revisión C1 (ambigüedad)

**Estado:** Apto con observaciones  
**Puede pasar a D1/D:** Sí (tras leer observaciones cerradas abajo)

### Críticas (cerradas en esta TR)

- **`hostContext` y jobs async:** `EmissionOrchestrator::runQueued` vuelve a llamar `resolveDataset` sin el HTTP original. Usar solo `request()` filtraría mal (universo de rol sin filtros) o ampliaría datos. Cierre §1 / RN-TR-06: persistir snapshot por `jobId`; preview síncrono puede leer el body.
- **Todas las filas:** `PartesTareaOperations::list` hoy hace `min(200, max(1, pageSize))`; `p_page_size=0` se volvería **1**. Cierre T1: sentinel 0 = sin paginar; el GET de pantalla sigue paginado.
- **Capacidad off:** código concreto **4704**, no “470x”.
- **`mailTo` async GEN:** el worker puede ignorar `mailTo` del request si no hay puerto de destinatarios. Cierre: no implementar `MailRecipients`; mail Must = síncrono; gap async documentado en D1 si el paquete no persiste `mailTo`.
- **Roles diseño:** SUPERVISOR sí; CLIENTE/ASISTENTE no (deja de ser “D1 inventa códigos”).
- **Emitir disabled:** `total` del último Buscar, no `rows.length` de la página ni filter-row.
- **i18n:** `respuesta` = `emission.*` (PHP); UI/testid = `emissions.*` (smoke).

### Menores

- Rutas thin host (`EmissionsController` template) vs paquete: mismo patrón TR-008/009; D1 elige.
- Pin `laravel-core` / `react-core`: verificar exports GEN-15; bump Satis si 1.3.3 / 2.2.1 no los traen.
- Layout DX (márgenes, totales): Must tabular §1; estética → diseñador.
- OpenAPI: documentar `hostContext`; el resto de la familia es contrato GEN.
- Colocación relativa Emitir vs botón Pivot: ambos en `toolbarLeading` / `leadingSlot`; orden visual libre en D1.
- GET `/emissions/processes` (listado): **Must** (contrato GEN Q13 / C1-15-37; dual-uso Emitir + diseñador). Seed Partes aporta ≥1 proceso activo.

### Contradicciones TR ↔ HU ↔ SPEC

- SPEC/HU `tipo_proceso = C` vs seeder físico `A`: **sin cambio de alcance**; TR ya unifica “igual que Consulta detallada, nunca `E`”. Alinear wording SPEC en Parte I si se desea.
- Montar filtros de pantalla (cliente/tipo/cerrado/asistente) en esta TR: no es creep; SPEC-006/011 y CA-07 lo exigen y hoy la página solo tiene periodo.

### Supuestos

- Paquetes Framework exponen orquestador, `EmissionDialog`, registry, migraciones `PQ_EMISSION_*`, envelope `4701–4712` y `emission.design`.
- Runtime listado de informe = `PartesTareaOperations` vía SpCaller (Query Builder MONO), no un T-SQL distinto con otra paginación.
- Jobs `EMISSION_BATCH` / purga GEN se adoptan con el paquete; Partes no arma un inbox propio.
- Cliente funcional con menú de consulta emite solo su `cliente_id` (misma `filteredQuery`).

### Preguntas humanas

Ninguna bloqueante.

### Recomendaciones

- En D1: persistencia `hostContext` por `jobId` (cache o tabla host) con purga alineada al job; no sesión.
- Tests Feature: 4704, 4701 fechas, dos `resolveDataset` del mismo `jobId` con el mismo recorte, N filas > pageSize y < umbral async.
- E2E: `EmissionEnabled=S` en fixture; no exigir DX Reporting real si el smoke es FakeDx.

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-25 | Parte C: TR-011 desde SPEC-011 + HU-011; cierres hostContext, SP list sin página, menú 60300, EmissionEnabled=S. |
| 2026-08-25 | Parte C1: apto con obs.; persistencia hostContext por jobId; sentinel pageSize 0 en Operations; 4704; mail sync; roles SUPERVISOR. |
| 2026-08-25 | Parte D1: plan [D1-TR-011](./d1/D1-TR-011-reportes-emisiones.md). |
| 2026-08-25 | Parte D: adopción GEN-15 en host (DB/SP, puerto Consulta detallada, `EmissionDialog`, diseñador placeholder). |
| 2026-08-25 | CC Q13: RN-TR-12 / CA-15 → `EmissionReportDesignerPage` sin hardcode; listado processes Must; T6/E2E con lista+confirmación (código FE pendiente de bump `react-core`). |

---

## 13) Archivos creados / modificados (Parte D)

### Creados

- `backend/database/migrations/2026_08_25_100000_create_pq_emission_tables.php`
- `backend/database/sp/pq_sp_emission_core.sql`
- `backend/database/seeders/PqEmissionSeeder.php`
- `backend/app/Repositories/Sp/Emissions/SpEmissionRepository.php`
- `backend/app/Services/Emissions/*` (repo adapters, puerto, MinimalDx, hostContext cache, noops)
- `backend/app/Http/Controllers/Api/V1/Emissions/EmissionsController.php`
- `backend/app/OpenApi/OpenApiPathsEmissions.php`
- `backend/tests/Feature/Emissions/ApiV1EmissionsPartesTest.php`
- `frontend/src/features/partes/informes/consultaDetalladaHostContext.ts` (+ test)
- `frontend/src/features/partes/informes/emissionHostContextBridge.ts` (+ test)
- `frontend/src/features/partes/informes/ReportDesignerHostPage.tsx`
- `frontend/src/features/partes/informes/partesConsultasEmissionsGate.test.ts`
- `frontend/tests/e2e/partes-emisiones.spec.ts`

### Modificados

- `PartesTareaOperations::list` sentinel `p_page_size=0`
- `SpParametroRepository` encode tipo `L` (params Emission usan `S` + `'S'`/`'N'` porque el codec 1.3.3 no incluye `L`)
- `PqMenuSeeder` ítem 60300; `DatabaseSeeder`; `AppServiceProvider`; `routes/api.php`; `CapabilityEnvelopeController`
- `ConsultaDetalladaPage` filtros + Emitir + `EmissionDialog`; interceptor `installApiAuthFetch`; router; mobile policy; menú cliente
- i18n `es/en/pt/fr/it`; manual usuario; `SwaggerRoot`

---

## 14) Comandos ejecutados (Parte D)

| Comando | Resultado |
|---------|-----------|
| `backend`: `php artisan test --filter=ApiV1EmissionsPartesTest` | **10 passed** (108 assertions) |
| `backend`: `php artisan test --filter=PartesInforme` | **4 passed** |
| `backend`: `php artisan test --filter=PartesTarea` | **6 passed** |
| `frontend`: Vitest / Playwright | **No ejecutados** en este entorno (`node`/`npx` no están en PATH; helper Cursor no arranca workers Vitest) |

---

## 15) Notas y decisiones (Parte D)

- Persistencia `hostContext`: Laravel Cache `paq.emission.hostContext.{jobId}`. Preview síncrono lee `request()->input('hostContext')`.
- Authz emitir: `MenuProcedimientoChecker` (menú `partes_consulta_detallada` → 4703). Authz diseñar: `AccesoTotal` (SUPERVISOR demo) → 4709 si no.
- `EmissionEnabled` se seed/actualiza a **`S`** como tipo **S** (string `'S'`/`'N'`). GEN documenta tipo L; `ParametroValorCodec` 1.3.3 solo S/T/I/D/B/F.
- Runtime DX: `MinimalDxReportingEngine` (PDF mínimo). Diseñador: `ReportDesignerHost` placeholder.
- Dispatcher async: `SyncNoopEmissionTaskDispatcher` (Must bajo umbral, igual TR-009).
- FE `hostContext`: interceptor `fetch` (no se clona `EmissionDialog`).
- Toolbar: **Emitir a la izquierda del botón Pivot**.
- Cliente **sí emite** (rutas sin `partes.notCliente`).
- Mail Must = síncrono con `mailTo` en el body; `NullEmissionMailPort`.

---

## 16) Pendientes (hacia E / F)

- Correr en máquina con Node: `npx vitest run src/features/partes/informes src/features/partes/partesMenuSidebar.test.ts src/features/partes/mobile` y `npx playwright test tests/e2e/partes-emisiones.spec.ts`.
- Smoke manual: PDF/mail real si hay SMTP; diseñador DX cuando el host inyecte `renderDesigner`.
- SQL Server: desplegar `pq_sp_emission_core.sql` (runtime tests = Query Builder MONO / sqlite).

