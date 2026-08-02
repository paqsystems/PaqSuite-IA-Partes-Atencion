# Plan de implementación - TR-009

## Alcance entendido

Adoptar **GEN-14 Importaciones Excel** en Partes solo embebido en **Carga diaria** (web):

- DDL + params `ExcelImport*` + proceso `partes.tareas.import` (`allow_partial=1`, `menu_process_code=partes_carga_diaria`, columnas D-IMP-05).
- Rutas `/api/v1/excel-import/*` + registry handler `PartesTareasImportHandler`.
- `validateRow` / `processBatch` (txn atómica) → altas vía contrato `pq_sp_partes_tarea_upsert` / `PartesTareaOperations` con `es_tarea=1`, `cerrado=0`.
- FE: `ExcelImportToolbar` en fila propia de `CargaDiariaPage`; `onComplete` refresh filtros; no native/cliente; `queued` sin refresh.
- Manual + tests Feature/Vitest/E2E.

**No:** menú/ruta aparte; compras; mobile Excel; ABM catálogo Excel; GEN-17 bandeja Must; redefinir motor GEN; Eloquent CRUD de **tareas**.

## Fuentes leídas

- SPEC: `docs/05-open-spec/100-SistemaPartes/SPEC-009-importacion-partes-excel.md`
- HU: `docs/03-historias-usuario/100-SistemaPartes/HU-009-importacion-partes-excel.md`
- TR: `docs/04-tareas/100-SistemaPartes/TR-009-importacion-partes-excel.md` (C1 apto con obs.)
- Producto: `docs/02-producto/Sistema-Partes-IA/13-importacion-partes-excel.md`
- Adopción GEN: `PaqSuite-IA-FRAMEWORK/docs/_base/excel-import-adopcion.md` + TR-GEN-14-*
- Código Partes: `CargaDiariaPage.tsx`, `PartesTareaOperations.php`, `AppServiceProvider.php`, `routes/api.php`, `EnsurePartesNotCliente`, `RequirePartesMaestrosAccess`, `PqMenuSeeder` (`partes_carga_diaria`), patrón TR-008 (SP + thin controllers)
- Framework: `ExcelImportHandler` / `ExcelImportRepository` / orchestrators; smoke `ExcelImportController` + `EloquentExcelImportRepository` + `SmokeClientesImportHandler`; FE `ExcelImportToolbar` (props: `processCode`, `onComplete`, `capabilityEnabled?`, `disabled?`, …)
- Inventario explore: [Explore Excel import](de05e7bd-c239-4335-aa38-387a33e0487f)

## Impacto esperado

### Base de datos

| Acción | Detalle |
|--------|---------|
| Crear | Migración tablas GEN Excel (adaptar smoke `2026_07_25_000009_create_pq_excel_import_tables.php`): procesos, columnas, batches, rows, errors |
| Desplegar | Copiar `laravel-core/database/sp/pq_sp_excel_core.sql` → `backend/database/sp/` (SQL Server prod) |
| Seed params | `ExcelImportParametersSeeder` del paquete o host insert-if-absent: `ExcelImportEnabled=N`, AsyncMaxMB/Rows, StagingRetentionDays |
| Seed catálogo | Proceso `partes.tareas.import` + 8 columnas D-IMP-05; `allow_partial=1`; `menu_process_code=partes_carga_diaria`; `handler_class` FQCN host |
| Sin | Ítem `pq_menus` nuevo; tablas dominio nuevas |

**Runtime MONO/sqlite:** igual que tareas/LLM — bridge PHP (`ExcelImportOperations` vía `SpCaller`) que cumple contrato `pq_sp_excel_*`; **no** usar Eloquent smoke como camino prod. SQL Server: mismos nombres SP del script.

### Backend

| Crear | Rol |
|-------|-----|
| `SpExcelImportRepository` (+ ops bridge si hace falta) | Implementa `ExcelImportRepository` MUST |
| Stubs/ports mínimos | `ExcelImportBinaryExporter` → **`MinimalXlsxExcelImportBinaryExporter`** (GEN laravel-core); `ExcelWorkbookParser` host (`ZipXml…`); audit/notif/task dispatcher thin/no-op si GEN-17 no Must |
| `PartesTareasImportHandler` | `validateRow` + `processBatch` (DB::transaction) + upsert tarea |
| `ExcelImportController` | Thin espejo smoke → orchestrators GEN |
| Feature tests | Template, upload, process parcial, owner, 4603/4604, txn failed |

| Modificar | Cambio |
|-----------|--------|
| `routes/api.php` | Grupo auth: `/excel-import/*` |
| `AppServiceProvider` | Binds ports + `ExcelImportHandlerRegistry::register('partes.tareas.import', …)` |
| `DatabaseSeeder` | Llamar seeders Excel params + proceso Partes |
| `.env.example` | Solo si hace falta doc de activación (`ExcelImportEnabled=S` vía param BD, no flag .env Must GEN) |

**Handler validateRow (resumen):**

- Mapear códigos cliente/asistente/tipo → ids usables (reutilizar lookups maestros / lógica TR-004).
- Duración `hh:mm` → minutos (helpers FE ya existen; portar/compartir criterio BE).
- Bool: `verdadero`/`falso` case-insensitive.
- Fecha: parse por locale importador (`users.locale`, fallback `es`) o valor tipado GEN.
- Asistente: !supervisor → vacío OK / ≠ sesión → error fila; supervisor → obligatorio usable.
- Fila totalmente vacía → skip (no error).
- No grabar en validate (solo staging).

**processBatch:**

- Transacción; por cada staging válida → `pq_sp_partes_tarea_upsert` (`p_id` null) con actor context; `es_tarea`/`cerrado` ya forzados en ops.
- Fallo → rollback → `BatchProcessResult` `failed`, processedRows=0.
- Éxito mixto de validación previa → status `partial` o `done` según GEN (errorRows de validación no se procesan).

### Frontend

| Modificar | Cambio |
|-----------|--------|
| `CargaDiariaPage.tsx` | Fila exclusiva `ExcelImportToolbar` (entre filtros y grid preferido); `processCode="partes.tareas.import"`; `onComplete` → `load()` si done/partial && processedRows>0; opcional `capabilityEnabled` desde param/sesión si el host ya lee capabilities (prop **existe** en paquete; default `true` + API 4604) |
| Gates | No montar si `isNativeApp()`; cliente ya no entra a carga (`RequirePartesMaestrosAccess` / menú) — refuerzo explícito si hace falta |
| i18n | Namespace `excelImport.*` si el host debe traer JSON (como chat); `partes.import.*` para mensajes dominio si el handler expone messageKeys Partes |

| Sin | Ruta `/excel-import`; ítem menú; allowlist mobile del proceso |

### Tests

| Capa | Casos |
|------|-------|
| Feature BE | Template 200; validate OK/error; process parcial; force owner; supervisor requiere asistente; `es_tarea=1`; txn rollback → failed; **4603** sin permiso; **4604** capability off |
| Unit FE | Helper mount gates (native/cliente) y/o `onComplete` no llama load en `queued` |
| E2E | Login asistente → Carga diaria → toolbar visible con fixture `ExcelImportEnabled=S` (o seed test); smoke plantilla si viable sin xlsx frágil |
| Manual | 2 OK + 1 error → Procesar → grilla conserva filtros |

### Documentación

- `docs/99-manual-usuario/Partes-Atencion.md`: sección breve Importar desde Excel
- Actualizar checklist producto / README TR tras D (no en D1)

### DevOps

- Deploy: `migrate` + scripts SP excel (SQL Server)
- Activar capacidad: set param `ExcelImportEnabled=S` (ABM params / SQL)
- Smoke post-deploy: Carga diaria → Descargar plantilla

## Orden de trabajo

1. **T1 DB** — migración `pq_excel_*` + copiar `pq_sp_excel_core.sql` + bridge ops sqlite si aplica  
2. **T2 Seed** — params ExcelImport* + proceso/columnas `partes.tareas.import`  
3. **T3 BE plumbing** — repository SP + ports + controller + routes + DI registry  
4. **T4 Handler** — `PartesTareasImportHandler` validate + process (txn + upsert) + Feature  
5. **T5 FE** — toolbar en `CargaDiariaPage` + onComplete + i18n mínimas  
6. **T6 Gates** — native / capability / cliente refuerzo + Vitest  
7. **T7 Docs** — manual usuario  
8. **T8 E2E** — Playwright smoke toolbar  

Alineado a TR-009 §7 (T1→T8).

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Smoke Eloquent vs MUST SP | Implementar `SpExcelImportRepository` (+ ops MONO); no copiar Eloquent como prod |
| Superficie grande de `ExcelImportRepository` | Seguir métodos del contract + smoke como checklist; reutilizar orchestrators del paquete |
| Ports async/notif/audit incompletos en paquete | No-ops seguros; sync primero; umbral async respeta GEN sin GEN-17 |
| Fecha locale ambigua | Locale usuario + tests con `es` (`dd/MM/yyyy`) |
| `capabilityEnabled` C1 vs prop real del paquete | Usar prop opcional del paquete **o** default true; no inventar otra API |
| E2E frágil con xlsx | E2E mínimo: toolbar visible + GET template; process profundo en Feature |

## Tests a ejecutar

```text
cd backend && php artisan test --filter=ExcelImport
cd backend && php artisan test --filter=PartesTarea
cd frontend && npx vitest run src/features/partes/carga
cd frontend && npx playwright test tests/e2e/...  # spec nuevo o extendido carga
```

(Exact filter names al crear archivos en D.)

## Dudas / bloqueos

Ninguno bloqueante para D.

**Decisiones D1 (absorber al implementar):**

1. Persistencia staging Excel: **SP contract** (`SpExcelImportRepository` + ops MONO/sqlite), no Eloquent smoke.  
2. Toolbar: fila **entre filtros y grid**.  
3. `capabilityEnabled`: pasar si hay lectura fácil del param; si no, default `true` y gate API/GEN.  
4. GEN-17 / task dispatcher: **no-op** o mínimo para sync; async 202 sin bandeja Must.  
5. Seed E2E/Feature: `ExcelImportEnabled=S` en fixture de test (prod default `N`).

## Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**

---

**Estado D1:** listo. Esperar autorización explícita para **parte D** (implementar).
