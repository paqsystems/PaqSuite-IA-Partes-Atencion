# Adopción GEN-13 — Tareas Programadas en PARTES

Jira: [PIAPARTES-49](https://paqsystems.atlassian.net/browse/PIAPARTES-49) · Estado OpenSpec: **Finalizado** (Parte I 2026-11-06)

Contrato: [SPEC-001-13](../05-open-spec/001-Generalidades/SPEC-001-13-host-adopcion-tareas-programadas.md) · [HU-GEN-13](../03-historias-usuario/001-Generalidades/HU-GEN-13-adopcion-tareas-programadas.md) · [TR-GEN-13](../04-tareas/001-Generalidades/TR-GEN-13-adopcion-tareas-programadas.md)

## SoT transversal (Framework)

La norma **Must** de cableado host, orden SQL GEN-34, esquema `pq_task_definition_parameter` y checklist al bump de `laravel-core` vive en el Framework (no duplicar aquí salvo deltas Partes):

**`PaqSuite-IA-FRAMEWORK`** → `docs/06-operacion/adopcion-gen-13-tareas-programadas-host.md`

Este archivo documenta **perfil Partes** (MONO/RDS, backup, env, scheduler) e **inventario concreto** de archivos del repo.

---

## Perfil declarado

| Eje | Valor |
|---|---|
| Tenancy | MONO |
| Backend de backup | RDS |
| Proceso | `BACKUP_DATABASE` |
| Acceso | Permiso sobre la opción de menú de configuración de tareas programadas |
| SoT | Framework `SPEC-001-13` + `TR-GEN-13-backup-windows-rds-update` |

## Configuración operativa

- `PAQSUITE_TASKS_BACKUP_BACKEND=rds`
- `PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING=true` — backup RDS **manual** vía HTTP: el POST solo dispara `startExport` y deja `EN_EJECUCION`; el cierre lo hace el scheduler (`RdsDeferredBackupExecutionPoller`) antes de recuperar overlaps. Obligatorio en Partes (perfil RDS). Pin SDK **≥ 1.3.18-beta.32** (no usar beta.31: sin poller publicado).
- `PAQSUITE_CLIENTE` es el código de `EMPRESAS_CONEXION` (el mismo que
  `X-Paq-Cliente`). Sigue siendo fallback CLI y post-deploy. **El scheduler
  `partes:tasks-run-due` no se limita a ese valor:** recorre todas las
  instalaciones SQL directo del proyecto (incidente PAQ 2026-11-06: Forge
  prod tenía `PAQSUITE_CLIENTE=DEMO` y el backup de PAQ nunca corría).
  En **Forge dev**: `DEV`. En plantilla local: `DEMO`.
- `PAQSUITE_TASKS_CLIENTES` — CSV opcional (`PAQ,DEMO`) para acotar el
  scheduler. Vacío = catálogo `EMPRESAS_CONEXION`. Un tenant puntual:
  `php artisan partes:tasks-run-due --cliente=PAQ`.
- `TASKS_BACKUP_S3_ARN_PREFIX` — prefijo S3 autorizado para backups RDS (ver
  § «Backup RDS → S3»). Obligatorio cuando `PAQSUITE_TASKS_BACKUP_BACKEND=rds`.
- `TASKS_BACKUP_POLL_SECONDS` controla el intervalo de consulta al estado RDS.
- `TASKS_BACKUP_LOCK_LEASE_SECONDS` documenta el lease del lock de instalación.
- `TASKS_BACKUP_WINDOWS_SERVICE` queda reservado para el perfil Windows y no se
  usa en este host RDS.
- Timeout y valores efectivos de ambiente se configuran fuera de Git.
- El lock global usa la instalación `{cliente}` y cubre todas las ejecuciones
  RDS concurrentes.
- No se documentan ni almacenan credenciales AWS en este repositorio.
- `PAQSUITE_CLIENTE` vive en `config/paqsuite.php` (`clienteCodigo`) para
  `config:cache`. El scheduler, al aplicar cada tenant, pisa ese valor con el
  código de la instalación activa (lock RDS `tasks:backup:rds:{cliente}`).
- El SP `pq_sp_task_definition_list_due` solo toma definiciones con
  `is_active = 1`, `next_run_at IS NOT NULL` y `next_run_at <= @as_of`. El
  host completa `nextRunAt` al crear/editar vía
  `App\Tasks\SchedulingTaskDefinitionRepository`.
- Definiciones **legacy** (alta previa al wrapper) pueden tener
  `next_run_at` NULL: tras deploy ejecutar
  `php artisan partes:tasks-backfill-next-run-at` (opción `--dry-run` primero).
  CC PQ **#10** (30/10/2026).
- El snapshot de parámetros vive en
  `backend/database/sp/pq_sp_task_execution_save_snapshot.sql`: expande el
  JSON del SDK a filas con `parameter_code` NOT NULL.
- **Orden SQL (Must):** (1) `paqsuite:installation:update` → SP del pin
  **`paqsuite/laravel-core`**; (2) **`php scripts/deploy-sp.php`** → overrides
  en `backend/database/sp/` y SP dominio `backend/scripts/sql/partes/`.
  `deploy-sp.php` **no** sustituye al manifiesto: **corre después** y puede
  pisar un `CREATE OR ALTER` del SDK con el mismo nombre de procedimiento.
- Desarrollo local: [runbook-desarrollo-local-gen34.md](./runbook-desarrollo-local-gen34.md)
  (`sync-installation-sql.ps1` / `start-dev.ps1 -All`).
- El objeto S3 se nombra con la base de la empresa (`BackupObjectName`) y,
  si `includeDateTimeInName=true`, un sufijo de fecha. No reutiliza el slug
  del producto.

## Backup RDS → S3 (`TASKS_BACKUP_S3_ARN_PREFIX`)

### Formato del valor

| Regla | Detalle |
|-------|---------|
| Qué es | Prefijo **bucket + carpeta** en notación ARN S3. **Sin** nombre de archivo `.bak`. |
| Ejemplo DEV (Forge) | `arn:aws:s3:::sql-amazon-basico/backups` o `arn:aws:s3:::sql-amazon-basico/backups/` |
| Destino efectivo | `{prefijo}/{nombreBd}.bak` (p. ej. `…/backups/paqsystems_partesatencion_dev.bak`) |
| Con fecha en nombre | Si el parámetro `includeDateTimeInName=true`, el SDK añade sufijo antes de `.bak` |
| IAM | El rol de la instancia RDS debe poder escribir en ese bucket/prefijo (`rds_backup_database`) |
| Secretos | No commitear bucket ni claves; solo documentar el **nombre** de la variable en Forge |

El proceso `BACKUP_DATABASE` (SDK) resuelve el prefijo desde `config('paqsuite.tasks.backup.s3ArnPrefix')` /
`TASKS_BACKUP_S3_ARN_PREFIX` y el nombre con `BackupObjectName::forDatabase`.

**SoT (Framework — no reimplementar en Partes):** `RdsBackupS3Arn::composeExportDestination`
en `paqsuite/laravel-core` (invocada en `MonoRdsBackupService` y **Must** en el gateway host).

### Gateway host (`App\Tasks\Backup\RdsSqlServerBackupGateway`)

RDS recibe un único parámetro `@s3_arn_to_backup_to` (ARN completo del objeto `.bak`).

| Regla | Detalle |
|-------|---------|
| **Must** | `RdsBackupS3Arn::composeExportDestination($s3Arn, $filePrefix)` antes del `EXEC rds_backup_database` |
| **Prohibido** | Concatenar a mano `rtrim($s3Arn).'/'.$filePrefix` |
| **Env** | `TASKS_BACKUP_S3_ARN_PREFIX` solo prefijo carpeta; **sin** `.bak` ni ARN de objeto embebido |

Pin mínimo post CC #10 (ARN + scheduler): `paqsuite/laravel-core` **≥ 1.3.18-beta.19**.

### Resolución de empresa (MONO)

Si el catálogo no trae `nombre_bd`, el host usa
`PartesMonoBackupCompanyTargetResolver`: fallback a la BD operativa del tenant
(`DB_DATABASE` / conexión resuelta por `PAQSUITE_CLIENTE`). Sin esto, backup
manual o programado puede fallar con **Backup company target not found**.

### Errores frecuentes (operación)

| Síntoma / mensaje | Causa probable | Acción |
|-------------------|----------------|--------|
| Historial vacío en UI con filas en BD | Adapter sqlsrv no drena segundo rowset del SP list | Deploy host CC #10; `@paqsuite/react-core` ≥ `2.4.30-beta.2` para columna motivo |
| `next_run_at` NULL → no entra en `list_due` | Definición legacy sin próxima corrida | `php artisan partes:tasks-backfill-next-run-at` |
| Misma tarea **FALLIDA** cada ~1 min (cron) | Claim no aplicó: SP `claim_next_run` ausente, SDK viejo, wrapper sin `claimNextRunAt`, o `@due_slot` ≠ `@as_of` | Framework `docs/06-operacion/adopcion-gen-13-tareas-programadas-host.md` §3.3 · `installation:update` · pin ≥ beta.19 |
| **Backup company target not found** | Empresa sin `nombre_bd` en MONO | Resolver host + verificar tenant |
| SP schedule *too many arguments* | `pq_sp_task_definition_update_schedule` viejo (sin `@reset_next_run_at`) | `paqsuite:installation:update` + SP del pin `laravel-core` |
| Grilla definiciones vacía / save genérico | Wrapper host sin método nuevo de `TaskDefinitionRepository` (p. ej. `claimNextRunAt`) → API **500** | Actualizar **todos** los wrappers; ver checklist Framework §3.2 |
| *Cannot insert NULL* en `process_parameter_id` al guardar | SP `parameter_set` del manifiesto vs esquema RDS legacy | Override `backend/database/sp/pq_sp_task_definition_parameter_set.sql` + `deploy-sp.php`; SoT SDK en laravel-core |
| *Could not parse the amazon resource name* con ARN repetido | Destino S3 mal formado (doble prefijo) | Verificar `TASKS_BACKUP_S3_ARN_PREFIX` (solo carpeta) y gateway host actualizado |
| Backup omitido / ventana RDS | Mantenimiento o backup concurrente en RDS | Reintentar fuera de ventana; revisar `TASKS_BACKUP_LOCK_LEASE_SECONDS` |
| Ejecución FALLIDA sin texto en UI | Snapshot/log sqlsrv o mensaje no hidratado | Deploy repositorio ejecuciones CC #10; SP `append_log` / `get` en tenant |
| Backup de un tenant (p. ej. PAQ) no corre; cron `schedule:run` sí | Scheduler limitado a `PAQSUITE_CLIENTE` (otro tenant, p. ej. DEMO) | Pin con `SchedulerClienteResolver`; dry-run sin `--cliente` debe listar due de **todos** los SQL directo; no cambiar el `.env` a un solo cliente |
| Manual backup **FALLIDA** ~1–2 min; RDS/S3 OK | nginx cortó el POST (~60s) o overlap recovery cerró antes que el poller | `PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING=true` + pin **≥ beta.32** + poller en `partes:tasks-run-due` / `partes:tasks-recover-stale`. Opcional residual: `scripts/forge/nginx-php-fastcgi-backup-run.timeout.snippet` |
| UI «En curso» mucho tiempo tras manual | Poller inactivo o SDK &lt; beta.32 | Verificar Forge `schedule:run`; smoke `pollOpenRdsBackups` vía comando due; incidente [d-nota GEN-13 backup zombie](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/main/docs/08-control/d-nota-gen-13-backup-zombie-paq-2026-11-03.md) §2026-11-11 |

### CC PQ #10 — seguimiento deploy (30/10/2026)

Además del drenado de listados y backfill:

- `PartesTaskExecutionRepository`: `statement` en escrituras sin rowset; hidratación de `message` en listado.
- `PartesTaskDefinitionRepository`: `listDefinitions` (rowsets sqlsrv); delegación **`claimNextRunAt`** + fallback SQL optimista si falta SP `pq_sp_task_definition_claim_next_run`; `clearNextRunAt` y fallback SP schedule de 4 parámetros si el SP en RDS aún no tiene `@reset_next_run_at`.
- `SchedulingTaskDefinitionRepository`: Must delegar **todos** los métodos de la interfaz (incl. `claimNextRunAt`); completa `nextRunAt` en create/update.
- Frontend: i18n columna **Motivo / mensaje** (`ejecuciones.message`).
- Pin UI recomendado post-fix: `@paqsuite/react-core` **2.4.30-beta.2** o superior en el deploy Vercel.

Tras merge a `develop`: Forge `installation:update`, redeploy SP tenant, backfill opcional, smoke backup manual una vez.

## Disparador del scheduler

El host registra **`partes:tasks-run-due`** cada minuto y `tasks:purge-history`
diariamente en `backend/app/Console/Kernel.php`. El comando due:

1. Resuelve la lista de tenants (`SchedulerClienteResolver`: `--cliente` →
   `PAQSUITE_TASKS_CLIENTES` → `EMPRESAS_CONEXION` SQL directo del proyecto →
   fallback `PAQSUITE_CLIENTE`).
2. Por cada tenant aplica la BD (`CliInstalacionContext`, y actualiza
   `paqsuite.clienteCodigo` para el lock RDS).
3. Si `deferManualRdsPolling` está activo: **`RdsDeferredBackupExecutionPoller::pollOpenRdsBackups()`** sobre ejecuciones `EN_EJECUCION` con `taskId` en logs (renueva lock mientras RDS corre; cierra `EXITOSA` / `FALLIDA`).
4. Recupera ejecuciones `EN_EJECUCION` expiradas (`StaleTaskExecutionRecovery`) y ejecuta due.

El comando **`partes:tasks-recover-stale`** usa el **mismo orden** (poller → recover).

Un Forge que atiende **varios** `{cliente}` del mismo proyecto (PAQ, DEMO,
DEV) **Must** recorrerlos todos. No alcanza con `PAQSUITE_CLIENTE=DEMO` en el
`.env` del sitio: la UI de `paq.…` usa `X-Paq-Cliente` y el cron no.

Mutex `withoutOverlapping(15)`: con polling **diferido**, el POST manual no bloquea
minutos en HTTP; el tick del cron puede cerrar varios backups en curso consultando AWS (~1–3 min típico hasta `EXITOSA`).

Para que la agenda opere en un ambiente, una tarea externa debe ejecutar:

```text
php artisan schedule:run
```

En Forge, habilitar por sitio la integración **Laravel Scheduler** (Scheduled
Job minutely → `…/current/backend/artisan schedule:run`). Ver skill
`vercel-github-deployment-standard` §3 al dar de alta un host nuevo.

`PAQSUITE_CLIENTE` sigue siendo el fallback y el default de post-deploy /
lab. Un sitio **un-tenant** (preview `dev`) funciona igual: el catálogo
devuelve un solo código o cae al `.env`. Para forzar un tenant en ops:
`php artisan partes:tasks-run-due --cliente=PAQ`.

Tras desplegar SP del host, ejecutar `php scripts/deploy-sp.php` (o al menos
`database/sp/pq_sp_task_execution_append_log.sql`) en **cada** base tenant con
esquema GEN-13 template (`log_timestamp`), para que los logs de backup no fallen.

## Desarrollo local (Cursor — PARTE O)

SoT: **[runbook-desarrollo-local-gen34.md](./runbook-desarrollo-local-gen34.md)**.

Resumen:

- Prompt «Iniciá el entorno de desarrollo» → sync GEN-34 sobre el tenant
  `PAQSUITE_CLIENTE` / `PAQSUITE_PROYECTO` (lab: `DEV` + RDS `_dev`).
- **`start-dev.ps1 -All`**: sync + worker + backend `:8010` + frontend `:3000`.
- Instalación **gateway**: se **omite** SQL local (exit 2); el stack **sí**
  se levanta; actualización SQL vía agente/Forge.

## Provisionamiento por base tenant (RDS / MONO)

Cada base operativa `paqsystems_partesatencion_{cliente}` (salvo entornos
`*_dev` de laboratorio) debe recibir **migraciones**, **seed de catálogo** y
**SP GEN-13** en la conexión que use ese tenant (`DB_DATABASE` / resolución
por `X-Paq-Cliente`). Si falta alguno, el menú puede verse pero la pantalla
**Procesos programables** queda vacía (típico: falta `pq_sp_task_process_list`).

Checklist por base (desde `backend/`, con credenciales del host apuntando a
esa base):

```powershell
cd backend
# Opcional: $env:DB_DATABASE = 'paqsystems_partesatencion_paq'

php artisan migrate --force
php artisan db:seed --class=Database\Seeders\PqTaskProcessFrameworkSeeder --force
php scripts/deploy-sp.php
```

En **Forge**, `scripts/forge/post-deploy.sh` → `run-post-deploy-backend.php`
ejecuta el mismo `PqTaskProcessFrameworkSeeder` por tenant tras
`installation:update` y `deploy-sp` (idempotente; alinea parámetros
`BACKUP_DATABASE` al SDK vigente, p. ej. `includeWeekdayInName` / CC PQ #17).

Verificación mínima en SQL:

```sql
SELECT process_code, is_active FROM PQ_TASK_PROCESS;
EXEC dbo.pq_sp_task_process_list;
```

Debe existir `BACKUP_DATABASE` con `is_active = 1` y el SP debe devolver al
menos una fila.

Migración de tablas en el host:
`database/migrations/2026_07_25_000006_create_pq_task_tables.php` (alineada al
template Framework). Las migraciones de menú
`2026_10_25_110000_*` / `2026_10_25_110001_*` ubican **Tareas programadas**
bajo **Parámetros → Soporte técnico**.

Si el seed falla por columnas extra respecto al template (p. ej.
`process_description`, `parameters_json`, `log_level`), comparar con la base
referencia `paqsystems_partesatencion_demo` y añadir columnas faltantes antes
de reintentar el seed.

## Smoke documental

1. Validar catálogo con `tasks:validate-catalog`.
2. Crear una definición `BACKUP_DATABASE` para la empresa vigente.
3. Ejecutar manualmente: respuesta rápida con `EN_EJECUCION`, log `RDS export started` + `taskId`; tras 1–3 ticks de `partes:tasks-run-due`, estado terminal y objeto en S3.
4. Verificar que timeout/error resulte `FALLIDA` y lock ocupado resulte
   `OMITIDA`.
5. Repetir en ambiente autorizado con RDS/S3 e IAM reales.
6. Comprobar que una definición activa tenga `next_run_at` y que
   `php artisan partes:tasks-run-due --dry-run --cliente={tenant}` la liste
   cuando esa fecha ya venció. Sin `--cliente`, el dry-run debe visitar
   **todos** los SQL directo (no solo `PAQSUITE_CLIENTE`).
7. En CLI, `BackupInstallationProfileResolver` debe resolver el tenant
   **aplicado** (`paqsuite.clienteCodigo` tras `applyForCliente`), no un
   header HTTP.
8. **Ejecución colgada (backup RDS OK, UI «En curso»):** usar botón **Liberar colgadas**
   en Monitoreo/Historial (SDK) o `php artisan partes:tasks-recover-stale --cliente={PAQSUITE_CLIENTE}`.
   Modo `--full` si la corrida supera ~2 h (timeout RDS). Nota incidente PAQ 2026-11-03:
   Framework `docs/08-control/d-nota-gen-13-backup-zombie-paq-2026-11-03.md`.
9. **Backup RDS manual (GEN-13, 2026-11):** SDK `BackupDatabaseProcess` + config
   `paqsuite.tasks.backup.deferManualRdsPolling` (`PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING=true` en
   Partes). El POST solo dispara RDS (`MonoRdsBackupService::beginBackup`) y deja `EN_EJECUCION`. El
   cierre lo hace el comando scheduler del host llamando `RdsDeferredBackupExecutionPoller::pollOpenRdsBackups()`
   antes de la recuperación de overlap (`partes:tasks-run-due` cada minuto). Pin mínimo
   `paqsuite/laravel-core` **≥ 1.3.18-beta.32**.

La implementación de código no forma parte de esta definición; el host adopta
los exports y contratos del SDK.

## Estado de adopción (pin vigente 2026-11-11)

| Paquete | Pin repo | Notas |
|---------|----------|--------|
| `paqsuite/laravel-core` | **1.3.18-beta.32** | Motor defer manual RDS (`RdsDeferredBackupExecutionPoller`, `beginBackup`, `TaskResult::enEjecucion`). Manifiesto `partesatencion-1.3.18-beta.32.json`. |
| `@paqsuite/react-core` | **2.4.30-beta.13** (ver `frontend/package.json`) | UI tareas + columna motivo; chrome pestañas **TaskScheduledLayout** ≥ **2.4.30-beta.14** pendiente de migración en Partes (ver Framework SPEC-update chrome 2026-11-11). |

El SDK publica `RdsBackupLock` como contrato; el host usa `App\Tasks\Backup\RdsBackupCacheLock`.
Migración host `2026_07_25_000006_create_pq_task_tables`; seed `BACKUP_DATABASE` en post-deploy Forge.
**Backup manual diferido (11/11/2026):** cableado en `AppServiceProvider` (`RdsDeferredBackupExecutionPoller` + `listOpenExecutions` vía `PartesTaskExecutionRepository::listStaleExecutionsForRecovery`), `PartesTasksRunDueCommand`, `PartesTasksRecoverStaleCommand`, `StaleTaskExecutionRecovery` (reconcilia RDS ya terminado antes de marcar FALLIDA).

SoT Framework: [SPEC-001-13-update-defer-manual-rds-polling-2026-11-11](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/main/docs/05-open-spec/updates/001-Generalidades/SPEC-001-13-update-defer-manual-rds-polling-2026-11-11.md) · `adopcion-gen-13-tareas-programadas-host.md` §3.4.1.

---

## Inventario cableado (repo Partes)

| Pieza | Ruta |
|-------|------|
| Binding repositorios | `backend/app/Providers/AppServiceProvider.php` (`TaskDefinitionRepository`, `TaskExecutionRepository`) |
| Decorador listado sqlsrv | `backend/app/Tasks/PartesTaskDefinitionRepository.php` |
| Agenda `nextRunAt` | `backend/app/Tasks/SchedulingTaskDefinitionRepository.php` |
| Ejecuciones sqlsrv | `backend/app/Tasks/PartesTaskExecutionRepository.php` |
| Poller RDS + stale recovery | `AppServiceProvider` (`RdsDeferredBackupExecutionPoller`, `RdsBackupExecutionLogSupport`), `StaleTaskExecutionRecovery.php` |
| Scheduler CLI | `PartesTasksRunDueCommand.php`, `PartesTasksRecoverStaleCommand.php`, `PartesTasksBackfillNextRunAtCommand.php` (poller **antes** de overlap recovery) |
| Tenant CLI | `backend/app/Tenancy/CliInstalacionContext.php` (aplica BD + `clienteCodigo` para lock RDS) |
| Tenants scheduler | `backend/app/Tenancy/SchedulerClienteResolver.php`, `EmpresasConexionSqlDirectClienteCatalog.php` |
| Recuperación UI/API | `POST /api/v1/task-executions/recover-stale` · `PartesTaskExecutionStaleRecovery` |
| Backup RDS | `backend/app/Tasks/Backup/*` |
| Override SP parámetros definición | `backend/database/sp/pq_sp_task_definition_parameter_set.sql` |
| Otros SP host tenant | `backend/database/sp/*.sql`, `backend/scripts/sql/partes/` |
| Deploy SP post-manifiesto | `backend/scripts/deploy-sp.php` |
| UI rutas SDK | `frontend/src/features/tareasProgramadas/TareasProgramadasSdkRoutes.tsx` — **debe** usar `TaskScheduledLayout` + pin `@paqsuite/react-core` ≥ **2.4.30-beta.14** (Framework: `docs/05-open-spec/updates/001-Generalidades/SPEC-001-13-update-chrome-pestanas-ejecuciones-2026-11-11.md`); eliminar solapas NavLink locales al migrar. |
| i18n host (it/fr/…) | `frontend/src/features/tareasProgramadas/tareasProgramadasI18n.ts` — **no** duplicar `tareasProgramadas.tabs.*` (SDK); revisar overrides de `monitoreo.title` vs caption **Ejecuciones**. |
| Tests wrappers / GEN-13 | `SchedulingTaskDefinitionRepositoryTest.php`, `StaleTaskExecutionRecoveryTest.php`, `BackupRdsExecutionPollerTest.php`, `RdsBackupTestDoubles.php` |
| Nginx timeout (opcional) | `scripts/forge/nginx-php-fastcgi-backup-run.timeout.snippet` — residual si no se usa defer polling |

### Checklist al bump `paqsuite/laravel-core` (Must)

Seguir Framework `adopcion-gen-13-tareas-programadas-host.md` §3.2 y §6, más:

1. Diff `TaskDefinitionRepository` en vendor vs versión anterior.
2. Actualizar `SchedulingTaskDefinitionRepository` y `PartesTaskDefinitionRepository` + fake de test.
3. `php artisan test tests/Unit/SchedulingTaskDefinitionRepositoryTest.php`
4. GEN-34 sync local o Forge: `installation:update` + `deploy-sp.php`.
5. Smoke: listado + guardar definición backup con parámetros en UI lab DEV.
