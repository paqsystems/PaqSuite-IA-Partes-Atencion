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
- `TASKS_BACKUP_S3_ARN_PREFIX` debe existir en el entorno del host.
- Timeout y polling se configuran fuera de Git.
- El lock global usa la instalación `{cliente}` y cubre todas las ejecuciones
  RDS concurrentes.
- No se documentan ni almacenan credenciales AWS en este repositorio.

## Smoke documental

1. Validar catálogo con `tasks:validate-catalog`.
2. Crear una definición `BACKUP_DATABASE` para la empresa vigente.
3. Ejecutar manualmente y comprobar `taskId`, polling terminal y logs.
4. Verificar que timeout/error resulte `FALLIDA` y lock ocupado resulte
   `OMITIDA`.
5. Repetir en ambiente autorizado con RDS/S3 e IAM reales.

La implementación de código no forma parte de esta definición; el host adopta
los exports y contratos del SDK.

## Estado de adopción 2026-10-24

El host queda cableado con `@paqsuite/react-core` `2.4.29-beta.1` y
`paqsuite/laravel-core` `1.3.16-beta.1` desde Cloudsmith. Esta versión incluye
`BackupInstallationProfile`, `BackupCompanyTargetResolver` y `RdsBackupLock`.
El artefacto no incluye una migración de tablas GEN-13: antes del smoke debe
existir la provisión `PQ_TASK_*` y sus SP `pq_sp_task_*` en la base operativa,
según el runbook de instalación.
