# TR-009 – Importación de partes desde Excel — adopción GEN-14 en Carga diaria

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-009-importacion-partes-excel](../../03-historias-usuario/100-SistemaPartes/HU-009-importacion-partes-excel.md) |
| **SPEC relacionada** | [SPEC-009-importacion-partes-excel](../../05-open-spec/100-SistemaPartes/SPEC-009-importacion-partes-excel.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Asistente / supervisor (cliente denegado) |
| **Dependencias** | [TR-004](./TR-004-operacion-carga-diaria.md) (carga + `pq_sp_partes_tarea_upsert`); [TR-002](./TR-002-identidad-funcional-y-acceso.md); [TR-003](./TR-003-maestros-y-catalogos.md); paquetes `@paqsuite/react-core` + `paqsuite/laravel-core`; GEN-14 (SPEC-001-14 / TR-GEN-14-*) |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Pendiente |
| **Última actualización** | 2026-08-02 |
| **Revisión C1** | Apto con observaciones (ver §11) |

**Origen:** [HU-009](../../03-historias-usuario/100-SistemaPartes/HU-009-importacion-partes-excel.md)  
**Referencia SPEC:** [SPEC-009](../../05-open-spec/100-SistemaPartes/SPEC-009-importacion-partes-excel.md)  
**Producto:** [13-importacion-partes-excel.md](../../02-producto/Sistema-Partes-IA/13-importacion-partes-excel.md) (D-IMP-01…09)

**Referencia GEN (checkout Framework):**

- `docs/_base/excel-import-adopcion.md`
- `docs/02-producto/14-importaciones-excel.md`
- `docs/05-open-spec/001-Generalidades/SPEC-001-14-importaciones-excel.md`
- `docs/04-tareas/001-Generalidades/TR-GEN-14-adopcion-habilitacion.md`
- `docs/04-tareas/001-Generalidades/TR-GEN-14-ui-embebida.md`
- Smoke: `apps/smoke-frontend` `/demo/clientes-import` + `apps/smoke-backend` ExcelImport*

---

## 1) HU refinada (resumen)

### Narrativa

Como asistente o supervisor quiero importar altas de tareas desde Excel **dentro de Carga diaria** para registrar dedicación masiva con las mismas reglas que la carga manual.

### In scope

- Adoptar motor GEN-14 (DDL/SP Excel, params, rutas `/api/v1/excel-import/*`, UI `ExcelImportToolbar`).
- Proceso Partes **`partes.tareas.import`** con `allow_partial = 1` y columnas D-IMP-05.
- Handler host: `validateRow` + `processBatch` → altas vía **`pq_sp_partes_tarea_upsert`** con `es_tarea = 1`, `cerrado = 0`.
- Reglas propietario D-IMP-04; cliente siempre obligatorio; fecha por locale app; refresco grilla D-IMP-08.
- Capacidad `ExcelImportEnabled`; ocultar toolbar si off o si actor cliente / native.

### Out of scope

- Menú/ruta aparte; compras `es_tarea=0`; edit/delete vía Excel; mobile; redefinir GEN; dialog extra Must; ABM catálogo Excel.

### Decisiones TR (cierran observaciones B1)

| Tema | Decisión |
|------|----------|
| `processCode` | **`partes.tareas.import`** (Must) |
| `menu_process_code` | **`partes_carga_diaria`** (mismo permiso que Carga diaria) |
| Atomicidad Procesar | **`processBatch` en transacción DB**: todas las válidas del batch o ninguna; ante fallo → status `failed`, mensaje i18n, **sin** éxito silencioso |
| Async / `queued` | Respetar umbrales GEN. Si `onComplete.status === 'queued'`: **no** refrescar grilla; toast/mensaje i18n “en cola”; el usuario puede refrescar manualmente al volver. **Bandeja GEN-17 no es Must** de esta TR (Partes no exige adoptar GEN-17 aquí). Si `done`/`partial` con `processedRows > 0` → refrescar filtros vigentes |

---

## 2) Criterios de aceptación (AC)

Mapear HU CA-01…12. Verificación técnica:

| AC | Verificación |
|----|--------------|
| CA-01 | `CargaDiariaPage` monta `ExcelImportToolbar` en fila propia; `processCode=partes.tareas.import`; sin ruta/menú nuevo |
| CA-02 | GET template trae columnas canónicas del seed |
| CA-03/04 | Handler fuerza/valida `asistente` según `esSupervisor` |
| CA-05 | Cliente: no montar toolbar (gate host por identidad funcional). Sin permiso `partes_carga_diaria` en API Excel → **4603**. Capacidad off → **4604** (GEN). |
| CA-06 | `validateRow` rechaza casos listados; errores en GET errors |
| CA-07/07b | Parse fecha por locale del importador (`users.locale`, fallback `es`) + nativa GEN; futura no invalida por sí sola |
| CA-08 | Inserts con `es_tarea=1`, `cerrado=0` vía upsert SP |
| CA-09 | Seed `allow_partial=1`. Mixto: FE habilita Procesar si `validRows>0`. API: con `validRows=0` → **4607**; con errores y `allow_partial=0` → **4607** (no aplica al seed Partes). Mixto + `allow_partial=1` → process OK (`partial` si hubo errorRows de validación). |
| CA-10 | `onComplete` done/partial + processedRows>0 → `load()` filtros actuales; `queued`/`failed` → sin refresh de éxito |
| CA-11 | `isNativeApp()` → no montar toolbar |
| CA-12 | Filas vacías skip; sin dedupe |

---

## 3) Reglas de negocio / implementación

| ID | Implementación host |
|----|---------------------|
| RN-TR-01 | Seed `PQ_EXCEL_PROCESOS`: `codigo=partes.tareas.import`, `allow_partial=1`, `menu_process_code=partes_carga_diaria`, `handler_class` → handler Partes. |
| RN-TR-02 | Seed `PQ_EXCEL_PROCESO_COLUMNAS` en orden: `cliente`, `asistente`, `tipo_tarea`, `fecha`, `duracion`, `sin_cargo`, `presencial`, `descripcion` (tipos string/date/bool según GEN). |
| RN-TR-03 | Migración/deploy: tablas + SP `pq_sp_excel_*` desde `laravel-core/database/sp/pq_sp_excel_core.sql` (o paquete). Seed params `ExcelImport*` vía `ExcelImportParametersSeeder` (insert-if-absent). **Prod:** set `ExcelImportEnabled=S` cuando se active. |
| RN-TR-04 | `AppServiceProvider` (o Capabilities): bind ports GEN + `ExcelImportHandlerRegistry::register('partes.tareas.import', PartesTareasImportHandler::class)`. |
| RN-TR-05 | Rutas `auth:sanctum` + tenancy: espejo smoke `/api/v1/excel-import/*` (controllers thin del paquete o host). |
| RN-TR-06 | `PartesTareasImportHandler::validateRow`: mapear códigos→ids (maestros usables); reglas SPEC-009 §4.4; vacío total → skip (no error); locale fecha = `users.locale` del importador (fallback `es`). |
| RN-TR-07 | `processBatch`: abrir transacción; por cada staging válida llamar **misma lógica** que alta TR-004 (`pq_sp_partes_tarea_upsert` / `PartesTareaOperations`) con `es_tarea=1`, `cerrado=0`, `usuario_id` forzado si !supervisor; commit o rollback completo. |
| RN-TR-08 | FE: fila exclusiva (encima o debajo de filtros, no en la misma fila) en `CargaDiariaPage`. Props Must GEN: `processCode`, `onComplete` (+ opcionales `disabled`, `showTemplateButton`, `sheetName`). **No** inventar prop `capabilityEnabled`: el toolbar GEN se auto-oculta con `ExcelImportEnabled=No` (**4604**); el host además no monta si cliente/native. `onComplete` según §1. |
| RN-TR-09 | No montar toolbar si identidad funcional **cliente** (`session.partes` / flag equivalente del host) o `isNativeApp()`. |
| RN-TR-10 | i18n: reutilizar `excelImport.*` GEN; claves Partes `partes.import.*` para errores de fila de dominio. testids GEN `excelImport.*`. |
| RN-TR-11 | Manual usuario: sección breve “Importar desde Excel” en `docs/99-manual-usuario/Partes-Atencion.md`. |
| RN-TR-12 | Envelope errores familia **4600–4699** GEN; no inventar códigos fuera del catálogo. |

---

## 4) Impacto en datos

| Pieza | Detalle |
|-------|---------|
| Tablas GEN | `PQ_EXCEL_PROCESOS`, `PQ_EXCEL_PROCESO_COLUMNAS`, batches/staging (migración Framework / host) |
| SP GEN | `pq_sp_excel_*` |
| SP negocio | Reutilizar `pq_sp_partes_tarea_upsert` (TR-004); **no** Eloquent CRUD de tareas |
| Seed | Proceso + columnas Partes; params `ExcelImportEnabled` default `N` hasta activación |
| Menú `pq_menus` | **Sin** ítem nuevo |
| Rollback | Desregistrar handler; soft-disable proceso; no borrar tareas ya importadas |

---

## 5) Contratos de API

Base `/api/v1/excel-import` — Bearer + `X-Paq-Cliente`. Authz = permiso menú **`partes_carga_diaria`** → sin permiso **4603** (catálogo GEN; no inventar 3003).

Envelope JSON GEN (`error` / `respuesta` / `resultado`) en endpoints de lotes/errores/process; plantilla = **binario** `.xlsx` (sin envelope). Contratos y códigos = TR-GEN-14-motor (no redefinir en host).

| Método | Path | Notas |
|--------|------|-------|
| GET | `/processes/{codigo}/template` | Binario `.xlsx` |
| POST | `/batches` | multipart `file` + `processCode` (+ `sheetName?`) |
| GET | `/batches/{batchId}` | Resumen (`validRows`, `errorRows`, `allowPartial`, `status`) |
| GET | `/batches/{batchId}/errors` | Errores fila |
| GET | `/batches/{batchId}/errors/export` | Export errores |
| POST | `/batches/{batchId}/process` | Sync **200** / async umbral **202** + `queued` |

Códigos Must a respetar (GEN): **4603** forbidden; **4604** capability off; **4605** formato; **4606** estructural; **4607** no processable por parcial/validRows; **4608** estado lote; **4609** process not found; **4611** handler no registrado.

---

## 6) Frontend

| Pieza | Detalle |
|-------|---------|
| Host | `frontend/src/features/partes/carga/CargaDiariaPage.tsx` |
| Componente | `ExcelImportToolbar` de `@paqsuite/react-core` |
| Props | `processCode="partes.tareas.import"`, `onComplete` (opc. `disabled` / `showTemplateButton` / `sheetName`) — contrato TR-GEN-14-ui |
| Layout | **Fila exclusiva** full-width (norma GEN-14) |
| Post-import | `onComplete`: si `processedRows>0` y status `done`\|`partial` → `void load()`; `queued`/`failed` → sin `load()` de éxito |
| Mobile / cliente | No montar si `isNativeApp()` o identidad cliente |
| i18n | Claves GEN `excelImport.*` (+ `t` del host si el componente lo exige); dominio `partes.import.*` |

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD | Deps | Est. |
|----|------|-------------|-----|------|------|
| T1 | DB | Migración/tablas Excel + deploy SP `pq_sp_excel_*` | migrate OK | — | M |
| T2 | Seed | Params ExcelImport* + proceso/columnas `partes.tareas.import` | seed idempotente | T1 | M |
| T3 | Backend | Registry + routes excel-import + binds | Feature smoke template/upload | T1 | L |
| T4 | Backend | `PartesTareasImportHandler` validate+process (txn + upsert SP) | Feature: OK, parcial, force owner, es_tarea | T2,T3 | L |
| T5 | Frontend | Toolbar en CargaDiariaPage + onComplete refresh | CA-01, CA-10 | T3 | M |
| T6 | Frontend | Gate cliente / native / capability | CA-05, CA-11 | T5 | S |
| T7 | Docs | Manual + `.env.example` si aplica flag | CA manual | T5 | S |
| T8 | Tests | Feature handler + Vitest mount gates + E2E smoke plantilla/toolbar | Suite verde | T4–T6 | M |

**Orden:** T1 → T2 → T3 → T4 → T5 → T6 → T7 → T8.

---

## 8) Estrategia de tests

| Capa | Casos |
|------|-------|
| Feature BE | Template 200; upload valida; process parcial; !supervisor fuerza owner; supervisor requiere asistente; upsert `es_tarea=1`; txn rollback → `failed`/processedRows=0; sin permiso → **4603**; capability off → **4604** |
| Unit FE | No montar toolbar native/cliente; `onComplete` llama load si processedRows>0 |
| E2E | Login asistente → Carga diaria → visible `excelImport.toolbar` / template (sin LLM/red externa) |
| Manual | Activar `ExcelImportEnabled=S`; importar 2 OK + 1 error; Procesar; verificar grilla y filtros |

---

## 9) Riesgos y edge cases

| Riesgo | Mitigación |
|--------|------------|
| Smoke usa Eloquent | Partes Must SP Excel + SP tarea |
| Parser xlsx incompleto en smoke | Usar orchestrator/parser paquete producción |
| Locale fecha ambiguo | Usar locale sesión; documentar en plantilla ejemplo según locale |
| Lote grande async | Umbrales GEN; no fingir refresh en `queued` |
| Permiso menú | Reutilizar `partes_carga_diaria` |

---

## 10) Checklist DoD

- [ ] CA-01…12 HU
- [ ] Proceso seed `partes.tareas.import` + columnas
- [ ] Handler registrado + rutas excel-import
- [ ] Upsert vía `pq_sp_partes_tarea_upsert` / ops TR-004
- [ ] Toolbar solo Carga diaria; fila exclusiva
- [ ] Partial + txn processBatch
- [ ] Refresh filtros; no refresh en queued
- [ ] Sin menú nuevo; sin native/cliente
- [ ] Manual + tests
- [ ] `ExcelImportEnabled` documentado

---

## 11) Revisión C1 (ambigüedad)

**Estado:** Apto con observaciones  
**Puede pasar a D1/D:** Sí (tras leer observaciones)

### Críticas (cerradas en esta TR)

- Props FE alineadas a GEN: **sin** `capabilityEnabled`; toolbar auto-gate `ExcelImportEnabled` + host no monta cliente/native (§3 RN-TR-08, §6).
- Authz API Excel: sin permiso menú → **4603**; capacidad off → **4604** (no “403 genérico”) — CA-05 / §5.
- CA-09: **4607** solo según semántica GEN (`validRows=0` o `!allowPartial` con errores); con seed `allow_partial=1` el mixto Procesa OK.
- Async `queued`: Must = toast + **sin** refresh; GEN-17 bandeja **no** requerida en esta TR (§1).

### Menores

- Posición exacta de la fila toolbar (encima vs debajo de filtros) libre en D1.
- OpenAPI formal del host: Should si ya se publica catálogo; contrato = GEN.
- SPEC-009 §5 aún dice “Menú / entrada bajo Carga…” (redacción residual A); Must real = embebido sin menú (HU/TR). Alinear en Parte I si se desea.
- `users.locale` como proxy del “formato de fecha configurado” (MVP); si aparece param de formato distinto, TR-update.
- Typo/nombres identidad cliente: usar el flag real del session Partes en D1 (no inventar `tipoFuncional` si no existe).

### Contradicciones TR ↔ HU ↔ SPEC

- Ninguna de alcance Must. Solo wording residual SPEC §5 (menú) vs embebido-only (HU/TR/A1).

### Supuestos

- Paquetes Framework vía Satis/Verdaccio (`laravel-core@^1.3.3`, `react-core@2.2.1`) exponen `ExcelImportToolbar`, handlers registry, migraciones/SP Excel y envelope `4601–4611`.
- Rutas `/api/v1/excel-import/*` se registran como en smoke/GEN (paquete o thin host) — D1 elige el patrón ya usado en TR-008.
- Cliente funcional **no** tiene permiso efectivo de `partes_carga_diaria` (o se deniega por gate de identidad además del menú).

### Preguntas humanas

Ninguna bloqueante.

### Recomendaciones

- En D1: clonar smoke GEN-14 **sin** Eloquent de dominio; handler → `PartesTareaOperations` / upsert SP.
- Tests: cubrir **4603/4604** y txn `failed`; E2E con `ExcelImportEnabled=S` en fixture.
- Opcional post-MVP: alinear bullet SPEC §5 “menú” → “embebido en Carga diaria”.

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-02 | Parte C: TR-009 desde SPEC-009 / HU-009; adopción GEN-14; decisiones processCode/atomicidad/async. |
| 2026-08-02 | Parte C1: apto con obs.; cierre props FE, códigos 4603/4604/4607, queued sin GEN-17 Must. |
| 2026-08-02 | Parte D: adopción host (DB Excel, handler, rutas, toolbar Carga diaria, tests). |
| 2026-08-02 | Parte E: suite OK — [E-TR-009](../updates/100-SistemaPartes/E-TR-009-importacion-excel-2026-08-02.md). |
