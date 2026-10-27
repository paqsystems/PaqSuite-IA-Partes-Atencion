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
- El snapshot de parámetros vive en
  `backend/database/sp/pq_sp_task_execution_save_snapshot.sql`: expande el
  JSON del SDK a filas con `parameter_code` NOT NULL. Se despliega con
  `backend/scripts/deploy-sp.php`.
- El objeto S3 se nombra con la base de la empresa (`BackupObjectName`) y,
  si `includeDateTimeInName=true`, un sufijo de fecha. No reutiliza el slug
  del producto.

## Disparador del scheduler

El host registra `tasks:run-due` cada minuto y `tasks:purge-history`
diariamente en `backend/app/Console/Kernel.php`. Para que la agenda opere en
un ambiente, una tarea externa debe ejecutar:

```text
php artisan schedule:run
```

En Forge, habilitar por sitio la integración **Laravel Scheduler** (Scheduled
Job minutely → `…/current/backend/artisan schedule:run`). Ver skill
`vercel-github-deployment-standard` §3 al dar de alta un host nuevo.

## Smoke documental

1. Validar catálogo con `tasks:validate-catalog`.
2. Crear una definición `BACKUP_DATABASE` para la empresa vigente.
3. Ejecutar manualmente y comprobar `taskId`, polling terminal y logs.
4. Verificar que timeout/error resulte `FALLIDA` y lock ocupado resulte
   `OMITIDA`.
5. Repetir en ambiente autorizado con RDS/S3 e IAM reales.
6. Comprobar que una definición activa tenga `next_run_at` y que
   `php artisan tasks:run-due --dry-run` la liste cuando esa fecha ya venció.
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
El artefacto no incluye una migración de tablas GEN-13: antes del smoke debe
existir la provisión `PQ_TASK_*` y sus SP `pq_sp_task_*` en la base operativa,
según el runbook de instalación.

Complementos de host (2026-10-27): `PAQSUITE_CLIENTE` → `clienteCodigo`;
wrapper `SchedulingTaskDefinitionRepository` para `nextRunAt`; SP de snapshot
con `parameter_code`. En Forge demo el Environment ya tiene las vars GEN-13;
el SP de snapshot se aplicó en la base operativa compartida.
