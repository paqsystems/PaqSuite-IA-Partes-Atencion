# Adopción GEN-13 — Tareas Programadas en PARTES

Jira: [PIAPARTES-49](https://paqsystems.atlassian.net/browse/PIAPARTES-49)

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
- `PAQSUITE_CLIENTE` es el código de `EMPRESAS_CONEXION` (el mismo que
  `X-Paq-Cliente`). Obligatorio en Forge para `tasks:run-due`, porque el CLI
  no envía header HTTP. En **Forge dev** del producto: `DEV` (preview Vercel
  suele usar el mismo código en query `?cliente=`). En plantilla local:
  `DEMO`.
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
- `PAQSUITE_CLIENTE` debe mapearse en `config/paqsuite.php` (`clienteCodigo`).
  Tras `config:cache`, `env()` queda vacío: el resolver de perfil CLI lee
  `config()`, no `env()`.
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

### CC PQ #10 — seguimiento deploy (30/10/2026)

Además del drenado de listados y backfill:

- `PartesTaskExecutionRepository`: `statement` en escrituras sin rowset; hidratación de `message` en listado.
- `PartesTaskDefinitionRepository`: `listDefinitions` (rowsets sqlsrv); delegación **`claimNextRunAt`** + fallback SQL optimista si falta SP `pq_sp_task_definition_claim_next_run`; `clearNextRunAt` y fallback SP schedule de 4 parámetros si el SP en RDS aún no tiene `@reset_next_run_at`.
- `SchedulingTaskDefinitionRepository`: Must delegar **todos** los métodos de la interfaz (incl. `claimNextRunAt`); completa `nextRunAt` en create/update.
- Frontend: i18n columna **Motivo / mensaje** (`ejecuciones.message`).
- Pin UI recomendado post-fix: `@paqsuite/react-core` **2.4.30-beta.2** o superior en el deploy Vercel.

Tras merge a `develop`: Forge `installation:update`, redeploy SP tenant, backfill opcional, smoke backup manual una vez.

## Disparador del scheduler

El host registra **`partes:tasks-run-due`** cada minuto (resuelve tenant vía
`PAQSUITE_CLIENTE` + `EMPRESAS_CONEXION`, recupera ejecuciones
`EN_EJECUCION` expiradas y luego ejecuta due) y `tasks:purge-history`
diariamente en `backend/app/Console/Kernel.php`. Para que la agenda opere en
un ambiente, una tarea externa debe ejecutar:

```text
php artisan schedule:run
```

En Forge, habilitar por sitio la integración **Laravel Scheduler** (Scheduled
Job minutely → `…/current/backend/artisan schedule:run`). Ver skill
`vercel-github-deployment-standard` §3 al dar de alta un host nuevo.

Cada sitio (p. ej. preview **dev**) debe tener **`PAQSUITE_CLIENTE`** igual al
código de `EMPRESAS_CONEXION` que usa la UI (`dev`, `paq`, …). Sin eso,
`partes:tasks-run-due` no conmuta la BD tenant y la agenda no corre sobre
`paqsystems_partesatencion_dev`.

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
3. Ejecutar manualmente y comprobar `taskId`, polling terminal y logs.
4. Verificar que timeout/error resulte `FALLIDA` y lock ocupado resulte
   `OMITIDA`.
5. Repetir en ambiente autorizado con RDS/S3 e IAM reales.
6. Comprobar que una definición activa tenga `next_run_at` y que
   `php artisan partes:tasks-run-due --dry-run` la liste cuando esa fecha ya
   venció (mismo tenant que `PAQSUITE_CLIENTE` del sitio).
7. En CLI, `BackupInstallationProfileResolver` debe resolver `PAQSUITE_CLIENTE`
   (sin header HTTP).

La implementación de código no forma parte de esta definición; el host adopta
los exports y contratos del SDK.

## Estado de adopción 2026-10-27 (actualizado 2026-10-30)

El host queda cableado con `@paqsuite/react-core` **`2.4.30-beta.2`** (UI tareas +
columna motivo) y `paqsuite/laravel-core` **`1.3.18-beta.18`** (pin en
`backend/composer.json`; verificar con `installation:status` tras sync local o
Forge) desde Cloudsmith.
La línea beta.1 incluía
`BackupInstallationProfile`, `BackupCompanyTargetResolver` y `RdsBackupLock`.
El SDK publica `RdsBackupLock` como contrato; el host aporta el adapter
`App\Tasks\Backup\RdsBackupCacheLock` sobre el cache configurado de Laravel.
El host incluye la migración `2026_07_25_000006_create_pq_task_tables` y el
runbook § «Provisionamiento por base tenant». Antes del smoke deben existir
`PQ_TASK_*`, el seed `BACKUP_DATABASE` y todos los SP `pq_sp_task_*` en la
base del tenant.

Complementos de host (2026-10-27, ampliado 2026-10-31): `PAQSUITE_CLIENTE` →
`clienteCodigo`; cadena de repositorios § «Inventario cableado»; SP snapshot
host con `parameter_code`; override `pq_sp_task_definition_parameter_set` para
RDS con `process_parameter_id`. Incidente RDS 2026-10-27: faltaban SP tras
timeout → migrate + seed + `installation:update` + `deploy-sp.php`.

---

## Inventario cableado (repo Partes)

| Pieza | Ruta |
|-------|------|
| Binding repositorios | `backend/app/Providers/AppServiceProvider.php` (`TaskDefinitionRepository`, `TaskExecutionRepository`) |
| Decorador listado sqlsrv | `backend/app/Tasks/PartesTaskDefinitionRepository.php` |
| Agenda `nextRunAt` | `backend/app/Tasks/SchedulingTaskDefinitionRepository.php` |
| Ejecuciones sqlsrv | `backend/app/Tasks/PartesTaskExecutionRepository.php` |
| Scheduler CLI | `backend/app/Console/Commands/PartesTasksRunDueCommand.php`, `PartesTasksBackfillNextRunAtCommand.php` |
| Backup RDS | `backend/app/Tasks/Backup/*` |
| Override SP parámetros definición | `backend/database/sp/pq_sp_task_definition_parameter_set.sql` |
| Otros SP host tenant | `backend/database/sp/*.sql`, `backend/scripts/sql/partes/` |
| Deploy SP post-manifiesto | `backend/scripts/deploy-sp.php` |
| UI rutas SDK | `frontend/src/features/tareasProgramadas/TareasProgramadasSdkRoutes.tsx` |
| i18n host (it/fr/…) | `frontend/src/features/tareasProgramadas/tareasProgramadasI18n.ts` |
| Tests wrappers | `backend/tests/Unit/SchedulingTaskDefinitionRepositoryTest.php` |

### Checklist al bump `paqsuite/laravel-core` (Must)

Seguir Framework `adopcion-gen-13-tareas-programadas-host.md` §3.2 y §6, más:

1. Diff `TaskDefinitionRepository` en vendor vs versión anterior.
2. Actualizar `SchedulingTaskDefinitionRepository` y `PartesTaskDefinitionRepository` + fake de test.
3. `php artisan test tests/Unit/SchedulingTaskDefinitionRepositoryTest.php`
4. GEN-34 sync local o Forge: `installation:update` + `deploy-sp.php`.
5. Smoke: listado + guardar definición backup con parámetros en UI lab DEV.
