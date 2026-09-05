# Plan de implementación - TR-011

## Alcance entendido

Adoptar **GEN-15 Reportes / emisiones** en Partes **solo** en **Consulta detallada** (web):

- DDL/SP `PQ_EMISSION_*` + params `Emission*` + proceso `partes.informes.consultaDetallada`.
- Puerto `PartesConsultaDetalladaEmissionPort` → `pq_sp_partes_tarea_list` / `PartesTareaOperations::list` con **`p_page_size=0`** (todas las filas).
- `hostContext` en preview/jobs; persistencia por `jobId` para `runQueued`.
- FE: filtros de pantalla (SPEC-006) + `EmissionDialog` en grilla y pivot; `EmissionEnabled=S`.
- Diseñador: menú `70100` / `partes_disenador_emisiones` bajo Soporte Técnico (`70000`) → `/emisiones/disenador` (`EmissionReportDesignerPage` GEN + `renderDesigner` DX; **sin** `processCode` fijo — CC Q13; lista+confirmación también N=1).
- Native: deny `/emisiones`; Consulta detallada ya denegada.
- Manual + OpenAPI + tests Feature / Vitest / E2E humo.

**No:** redefinir motor GEN; Emitir en agrupadas / paquete / dashboard / carga; ZIP/segmentado; `MailRecipients` / `resolveSegments`; selector GEN-23; menú `E`; Eloquent CRUD de tareas; historial Partes.

## Fuentes leídas

- SPEC: `docs/05-open-spec/100-SistemaPartes/SPEC-011-reportes-emisiones.md`
- HU: `docs/03-historias-usuario/100-SistemaPartes/HU-011-reportes-emisiones.md`
- TR: `docs/04-tareas/100-SistemaPartes/TR-011-reportes-emisiones.md` (C1 apto con obs.)
- Producto: `docs/02-producto/Sistema-Partes-IA/15-reportes-emisiones.md`
- GEN: `TR-GEN-15-adopcion-habilitacion.md` / `TR-GEN-15-dx-reporting-documental.md` (C1-15-36..39); FE `EmissionDialog` / `useEmission` / `EmissionReportDesignerPage`; PHP `EmissionOrchestrator`, `EmissionDatasetPortRegistry`, envelope `4701–4712`
- Host analogía TR-009: `ExcelImportController`, `SpExcelImportRepository`, `PqExcelImportSeeder`, `AppServiceProvider` binds, `MenuProcedimientoChecker`
- Host UI: `PartesConsultasPages.tsx` (solo periodo hoy), `CargaDiariaPage.tsx` (filtros catálogo a reutilizar), `PqMenuSeeder` (60000/60200), `partesMobilePolicy.ts`, `PartesMenuSidebar.tsx` (`filterMenuForCliente`)
- Pin: `paqsuite/laravel-core ^1.3.3` (lock 1.3.3); `@paqsuite/react-core` **2.2.1** (source Framework exporta `EmissionDialog`)
- Inventario: host **sin** rutas `/emissions/*` ni tablas emisión

## Impacto esperado

### Base de datos

| Acción | Detalle |
|--------|---------|
| Crear | Migración host copiando template `2026_07_25_000010_create_pq_emission_tables.php` (procesos, reports, mail templates, jobs, artifacts, preview sessions) |
| Desplegar | Copiar `laravel-core/database/sp/pq_sp_emission_core.sql` → `backend/database/sp/` |
| Seed params | Insert-if-absent Programa `Emission`: `EmissionEnabled` tipo **L** `N`, MB=5, rows=2000, days=30. **Adopción Partes:** update **solo** `EmissionEnabled` → **`S`**. Fixture tests puede forzar `N` para 4704 |
| Seed catálogo | Proceso `partes.informes.consultaDetallada`; canales pdf/print/excel/csv/mail; zip=0; consolidado=1; segmentado=0; preview=0; `menu_process_code=partes_consulta_detallada`; reporte `partes.consultaDetallada.principal` tabular; plantilla `partes.consultaDetallada.mail` breve |
| Seed menú | `PqMenuSeeder` id **70100**, padre 70000 (Soporte Técnico), código `partes_disenador_emisiones`, ruta `/emisiones/disenador`, `process_type` **igual** a consulta detallada (`A`); nunca `E` |
| Seed permiso | `emission.design` (seeder GEN); **no** asignar a CLIENTE/ASISTENTE. SUPERVISOR demo (`admin`/`PQ`) ya tiene `acceso_total` |
| Modificar | `PartesTareaOperations::list`: sentinel **`p_page_size=0`** = sin `forPage` ni clamp 1…200. GET informe **sigue** default 50 |
| Sin | Tabla de historial Partes; SP paralelo de listado |

**Runtime MONO/sqlite:** `SpEmissionRepository` + ops bridge (mismo patrón Excel). No Eloquent smoke como camino prod.

**`hostContext` persistencia:** clase host `PartesEmissionHostContextStore` (Laravel Cache, clave `paq.emission.hostContext.{jobId}`, TTL ≥ retención artefacto). **No** tabla de dominio nueva. Preview sin `jobId` = body del mismo HTTP.

### Backend

| Crear | Rol |
|-------|-----|
| `SpEmissionRepository` (+ ops sqlite/`pq_sp_emission_*`) | Implementa `EmissionRepository` |
| `PartesConsultaDetalladaEmissionPort` | `resolveDataset`: hostContext + actor profile + list `pageSize=0` + camelCase columnas TR §1 |
| `PartesEmissionHostContextStore` | Get/put por jobId |
| `EmissionsController` thin | Espejo template + **`MenuProcedimientoChecker`** (`partes_consulta_detallada` → 4703). Design → 4709 si falta `emission.design`. Mapear `EmissionException` al envelope |
| Adapters mínimos | `MinimalDxReportingEngine` (template, sin DX real); mail/audit/notif **noop o template**; task dispatcher **sync-noop** (mismo criterio TR-009: Must bajo umbral async) |
| Feature tests | Ver § Tests |

| Modificar | Cambio |
|-----------|--------|
| `routes/api.php` | Grupo auth + `partes.profile`: `/emissions/*` (paths GEN). **No** `partes.notCliente` (el cliente **sí** puede emitir su organización si tiene el menú de consulta; Excel sí lo deniega) |
| `AppServiceProvider` | Binds ports + `EmissionDatasetPortRegistry::register('partes.informes.consultaDetallada', …)` |
| `CapabilityEnvelopeController` | Incluir `EmissionException` en el `match` (o `fromException` en el controller) |
| `DatabaseSeeder` | `PqEmissionSeeder` (nombre host) |
| `PartesTareaOperations::list` | Sentinel 0 |
| `.env.example` | Solo si hace falta doc; capacidad = param BD, no flag .env Must |

**Puerto (resumen):**

1. Resolver snapshot: si `jobId` → store; si no hay, `request()->input('hostContext')` y persistir si hay jobId.
2. Validar fechas → 4701; mapear a `p_fecha_desde` / `p_usuario_id` / etc. (mismo contrato que `PartesInformeController::listTareas`).
3. Actor: `EnsurePartesFunctionalProfile` / mismos `p_actor_*`.
4. `p_page=1`, `p_page_size=0`; camelCase; schema columnas Must; **sin** `diaSemana`.
5. No `SegmentedEmissionDatasetPort` ni `MailRecipients`.

**Authz (no copiar smoke `config grants` que omite 4703):**

- Emitir: `HostMenuProcedimientoChecker` sobre `partes_consulta_detallada`.
- Diseñar: permiso `emission.design`. Fixture 4709 = usuario **sin** `acceso_total` y sin ese slug.
- 401: sin Sanctum.

### Frontend

| Crear | Rol |
|-------|-----|
| `partesEmissionHostContext.ts` | Ref del snapshot de filtros; `set`/`get` |
| `ConsultaDetalladaEmissionBar` (o inline) | Botón Emitir + `EmissionDialog` |
| Página diseñador | Monta **`EmissionReportDesignerPage`** sin `processCode` fijo; opcional `initialProcessCode`; `isNative` → excluded; `renderDesigner` omitido = placeholder GEN. FE actual con hardcode = **gap** hasta bump `react-core` CA-09..12 |
| Vitest | Gates disabled/native; hostContext; no dialog en agrupadas; `filterMenuForCliente` oculta `/emisiones` |

| Modificar | Cambio |
|-----------|--------|
| `installApiAuthFetch.ts` (o sibling) | En POST `/api/v1/emissions/jobs` y `/preview`, fusionar `hostContext` del ref en el JSON (GEN `useEmission` **no** lo envía). No clonar `EmissionDialog` |
| `PartesConsultasPages.tsx` | Estado `total`; filtros cliente/tipo/cerrado + asistente si supervisor (`listCatalogo` como Carga diaria); Emitir en `toolbarLeading` y `leadingSlot`; `permiteConsolidado={false}`; no montar native / si GET process = 4704 |
| `AppRouter.tsx` | Ruta `/emisiones/disenador` dentro de shell + mobile policy |
| `partesMobilePolicy.ts` | Denylist prefix `/emisiones` |
| `PartesMenuSidebar.tsx` | `filterMenuForCliente`: ocultar también `/emisiones` (hoy solo `/parametros/`) |
| i18n `common.json` (es + locales) | `partes.informe.emitir` + claves `emissions.*` mínimas si el host no las hereda del paquete |

| Sin | Dialog en agrupadas/paquete/dashboard; selector grupo; campo `mailTo` Partes (gap GEN si el diálogo no lo trae) |

**`total`:** persistir `resultado.total` del último Buscar; `disabled={loading \|\| total===0}`.

**DX Reporting real:** no en esta TR. Diseñador = placeholder. PDF = `MinimalDxReportingEngine` (stub usable).

### Tests

| Capa | Casos |
|------|-------|
| Feature BE | Seed GET process 200; POST jobs PDF + `hostContext`; 401; 4706 sin puerto; 4703 sin menú; 4709 design; 4704 `EmissionEnabled=N`; 4701 fechas; cliente no ve otras orgs; list `pageSize=0` N>50 y N<2000; 2ª `resolveDataset` mismo jobId mismo recorte |
| Unit FE | Emitir disabled total 0 / loading; no montar native; snapshot = controles actuales; agrupadas sin dialog; cliente sin ítem `/emisiones` |
| E2E | Login admin → Consulta detallada → Buscar → `partesConsultaDetalladaEmit` abre `emissions.dialog`; empty → disabled; ir a `/emisiones/disenador` (placeholder o gated) |
| Manual | PDF stub; convive export grilla; mail API síncrono si GEN trae `mailTo` |

### Documentación

- `docs/99-manual-usuario/Partes-Atencion.md`: Emitir ≠ export grilla; diseñador bajo Soporte Técnico; no mobile
- OpenAPI: `OpenApiPathsEmissions.php` (familia + `hostContext`) + tag en `SwaggerRoot.php`
- Tras D: nota en este D1 / checklist producto

### DevOps

- `migrate` + script SP emisión (SQL Server)
- Capacidad: `EmissionEnabled=S` (seed adopción)
- Verificar pin `react-core` exporta `EmissionDialog` / `EmissionReportDesignerPage` con selección de proceso (CA-09..12); **bump Satis** si falta
- Smoke: Consulta detallada → Emitir PDF stub

## Orden de trabajo

1. **T0 pin** — `composer install` / `npm` si falta `vendor/paqsuite`; confirmar exports GEN-15 en `laravel-core` 1.3.3 y `react-core` 2.2.1 (`EmissionDialog`, registry, migraciones); bump Satis si falta 
2. **T1 DB** — migración + `pq_sp_emission_core.sql` + sentinel `pageSize=0` en `PartesTareaOperations::list`  
3. **T2 Seed** — params + proceso/reporte/plantilla + menú 70100 + `EmissionEnabled=S` + `emission.design`  
4. **T3 BE plumbing** — repository SP + controller + routes + DI + `hostContext` store + 4703 vía menú  
5. **T4 Puerto** — `PartesConsultaDetalladaEmissionPort` + Feature dataset/rol/async snapshot  
6. **T5 FE consulta** — filtros + fetch interceptor `hostContext` + Emitir grilla/pivot + dialog  
7. **T6 FE diseñador** — página + ruta + policy native + filtro menú cliente  
8. **T7 Docs** — OpenAPI + i18n + manual  
9. **T8 Tests** — Vitest + E2E humo + ajuste Feature  

Alineado a TR-011 §7 (T1→T8) con T0 de pin.

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| `useEmission` no manda `hostContext` | Interceptor fetch host (body JSON); no fork del diálogo |
| `runQueued` sin request | Cache por `jobId`; tests de 2ª resolución |
| `pageSize=0` clamp a 1 | Cambiar Operations **antes** del puerto |
| Pin 2.2.1 / 1.3.3 sin GEN-15 | T0 bump; no implementar Dialog a mano |
| Smoke Eloquent vs MUST SP | `SpEmissionRepository` como Excel |
| `acceso_total` salta 4709 | Fixture 4709 **sin** acceso total |
| Cliente ve menú `/emisiones` | Extender `filterMenuForCliente` |
| Async mail pierde `mailTo` | No `MailRecipients`; Feature mail N<2000; dispatcher noop = todo sync en MVP |
| FakeDx vs layout hh:mm | Stub PDF aceptable; formato en layout seed / documentar gap DX real |
| `EmissionEnabled` tipo L vs B de Excel | Seguir GEN **L** `S`/`N`; `EmissionSettings` compara `'S'` |

## Tests a ejecutar

```text
cd backend && php artisan test --filter=Emission
cd backend && php artisan test --filter=PartesInforme
cd backend && php artisan test --filter=PartesTarea
cd frontend && npx vitest run src/features/partes/informes src/features/partes/partesMenuSidebar.test.ts src/features/partes/mobile
cd frontend && npx playwright test tests/e2e/partes-emisiones.spec.ts
```

(Nombres exactos de archivos Feature al crearlos en D.)

## Dudas / bloqueos

Ninguno bloqueante para D.

**Decisiones D1 (absorber al implementar):**

1. Persistencia catálogo/jobs emisión: **SP contract** (`SpEmissionRepository` + ops MONO), no Eloquent smoke.  
2. `hostContext` FE: **inyectar en body** vía interceptor `fetch` (extender `installApiAuthFetch` o sibling); Dialog GEN intacto.  
3. `hostContext` BE async: **Cache** por `jobId`, no tabla nueva.  
4. DX Reporting: **MinimalDx** + diseñador **placeholder**; gap DX real documentado.  
5. Task dispatcher: **sync-noop** (Must bajo umbral; igual TR-009). Jobs `EMISSION_BATCH` seed Should si el script GEN es barato, sin bandeja Partes.  
6. Authz emitir: **`MenuProcedimientoChecker`**, no `config.grants` smoke.  
7. `EmissionEnabled=S` en seed de adopción (prod); tests 4704 fuerzan `N`.  
8. Filtros UI: reutilizar `listCatalogo` de Carga diaria (caption izquierda, código+descripcion, Cargando…).  
9. Orden toolbar: **Emitir a la izquierda del botón Pivot** (libre C1; se fija aquí).  
10. Rutas emisiones: **sin** `partes.notCliente` (a diferencia de Excel).  
11. OpenAPI: registrar tag en `SwaggerRoot.php`.

## Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**  
  (filtros de pantalla y hide `/emisiones` en menú cliente son exigidos por CA-07 / CA-13 / seed no-cliente, no features nuevas)

---

**Estado D1:** listo. Esperar autorización explícita para **parte D** (implementar).
