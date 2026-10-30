# Adopción GEN-13 — Tareas Programadas en PARTES

Jira: [PIAPARTES-49](https://paqsystems.atlassian.net/browse/PIAPARTES-49)

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
  no envía header HTTP. En este host de demo: `DEMO`.
- `TASKS_BACKUP_S3_ARN_PREFIX` debe existir en el entorno del host.
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
- Los SP `pq_sp_task_*` del SDK (`vendor/paqsuite/laravel-core/database/sp/`)
  y los del host se aplican con `backend/scripts/deploy-sp.php` (el host gana
  si comparten nombre de archivo).
- El objeto S3 se nombra con la base de la empresa (`BackupObjectName`) y,
  si `includeDateTimeInName=true`, un sufijo de fecha. No reutiliza el slug
  del producto.

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

## Estado de adopción 2026-10-27

El host queda cableado con `@paqsuite/react-core` `2.4.29-beta.1` y
`paqsuite/laravel-core` `1.3.17-beta.1` desde Cloudsmith. Esta versión incluye
`BackupInstallationProfile`, `BackupCompanyTargetResolver` y `RdsBackupLock`.
El SDK publica `RdsBackupLock` como contrato; el host aporta el adapter
`App\Tasks\Backup\RdsBackupCacheLock` sobre el cache configurado de Laravel.
El host incluye la migración `2026_07_25_000006_create_pq_task_tables` y el
runbook § «Provisionamiento por base tenant». Antes del smoke deben existir
`PQ_TASK_*`, el seed `BACKUP_DATABASE` y todos los SP `pq_sp_task_*` en la
base del tenant.

Complementos de host (2026-10-27): `PAQSUITE_CLIENTE` → `clienteCodigo`;
wrapper `SchedulingTaskDefinitionRepository` para `nextRunAt`; SP de snapshot
con `parameter_code`; `deploy-sp.php` despliega SP GEN-13 del SDK. Incidente
RDS 2026-10-27: en `paqsystems_partesatencion_paq` faltaban SP tras timeout;
corregido con migrate + seed + `deploy-sp.php`.
