# TR-GEN-13 — Wiring de Tareas Programadas en PARTES

Jira: [PIAPARTES-49](https://paqsystems.atlassian.net/browse/PIAPARTES-49)

| Campo | Valor |
|-------|--------|
| HU | [HU-GEN-13 adopción](../../03-historias-usuario/001-Generalidades/HU-GEN-13-adopcion-tareas-programadas.md) |
| SPEC | [SPEC-001-13 host](../../05-open-spec/001-Generalidades/SPEC-001-13-host-adopcion-tareas-programadas.md) |
| SoT Framework | `TR-GEN-13-backup-windows-rds-update` · cableado Must: `PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-gen-13-tareas-programadas-host.md` |
| Estado | Finalizado |
| Prioridad | MUST |
| F / verificación | [D-VERIFICACION-CC-PQ-10](../updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-10-2026-10-30.md) |
| Operación | [adopcion-gen-13-tareas-programadas.md](../../06-operacion/adopcion-gen-13-tareas-programadas.md) |

## 1. Objetivo

Definir el contrato de adopción de GEN-13 en PARTES MONO/RDS. El host conserva
solo configuración, permisos de menú, wiring de plataforma y operación; el
motor y la UI pertenecen a los paquetes SDK.

## 2. Contratos host

- Proceso: `BACKUP_DATABASE`.
- Perfil: `tenancy=single`, backend `rds`.
- Objetivo: empresa vigente y base resuelta por el contexto de instalación.
- Destino: `TASKS_BACKUP_S3_ARN_PREFIX` con key normalizada.
- CLI: `partes:tasks-run-due` recorre instalaciones SQL directo del proyecto
  (`SchedulerClienteResolver`). `PAQSUITE_CLIENTE` es fallback; `--cliente=`
  o `PAQSUITE_TASKS_CLIENTES` acotan. Tras `applyForCliente` se actualiza
  `paqsuite.clienteCodigo` (lock RDS).
- Agenda: al crear/editar definición el host completa `nextRunAt` si falta
  (`SchedulingTaskDefinitionRepository`). El SP due exige `next_run_at`.
- Repositorios: Must implementar **todos** los métodos de
  `TaskDefinitionRepository` en cada wrapper (p. ej. `claimNextRunAt` desde
  laravel-core ≥ 1.3.18-beta.32). Ver Framework adopcion-gen-13-tareas-programadas-host §3.
- SQL: `installation:update` antes de `deploy-sp.php`; overrides en
  `backend/database/sp/` (p. ej. `pq_sp_task_definition_parameter_set.sql`).
- Snapshot: SP host `pq_sp_task_execution_save_snapshot` expande JSON a
  `parameter_code` + `value_json`.
- Polling: intervalo configurado y estados `running|success|error`.
- Lock: `tasks:backup:rds:{cliente}`, adquisición no bloqueante, renovación y
  liberación en `finally`.
- **Backup manual RDS diferido:** `PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING=true`;
  `partes:tasks-run-due` y `partes:tasks-recover-stale` invocan
  `RdsDeferredBackupExecutionPoller::pollOpenRdsBackups()` **antes** de
  `StaleTaskExecutionRecovery`. Pin `laravel-core` **≥ 1.3.18-beta.32**.
- Acceso: permiso de la opción de menú de configuración de tareas programadas.

## 3. Piezas de implementación (host)

| Pieza | Acción |
|-------|--------|
| `PartesTaskDefinitionRepository` | Decorador: `listDefinitions` drena rowsets sqlsrv; `clearNextRunAt`; fallback SP schedule 4 params; `claimNextRunAt` |
| `PartesTaskExecutionRepository` | Drena rowsets en listado; `statement` en escrituras; hidrata `message` |
| `PartesMonoBackupCompanyTargetResolver` | Fallback BD tenant MONO |
| `RdsSqlServerBackupGateway` | `RdsBackupS3Arn::composeExportDestination` (SoT SDK; sin concat manual) |
| `partes:tasks-backfill-next-run-at` | Recalcula `nextRunAt` vía `SchedulingTaskDefinitionRepository` |
| `AppServiceProvider` | `RdsDeferredBackupExecutionPoller`, `RdsBackupExecutionLogSupport`, resolver backup + gateway RDS |
| `StaleTaskExecutionRecovery` | Overlap/full recovery; reconcilia RDS OK antes de FALLIDA |
| `frontend` | i18n motivo ejecución; pin `@paqsuite/react-core` ≥ 2.4.30-beta.2 |

## 4. Catálogo y operación

La definición debe conservar los metadatos GEN-13 de `companyIds`,
`includeDateTimeInName`, `s3ArnPrefix`, `rdsTimeoutMinutes` y
`rdsPollingIntervalSeconds`, respetando qué valores son derivados y cuáles son
editables. En MONO no se muestra `companyIds`.

La operación debe incluir:

- `tasks:validate-catalog`;
- `tasks:run-due` con frecuencia acordada y sin solapamiento;
- `tasks:purge-history`;
- smoke catálogo → definición → ejecución → logs;
- verificación operativa posterior con RDS/S3 autorizado.

Post-deploy:

```powershell
cd backend
php artisan partes:tasks-backfill-next-run-at --dry-run
php artisan partes:tasks-backfill-next-run-at
```

Comprobar en Forge: `TASKS_BACKUP_S3_ARN_PREFIX` = prefijo bucket/carpeta (sin `.bak`);
scheduler `schedule:run`; smoke backup manual con estado terminal SUCCESS y objeto en S3.

## 5. No hacer

- No copiar `TaskExecutionService`, controllers, cliente HTTP ni componentes
  de `react-core`.
- No agregar SQL de negocio ad-hoc.
- No incluir secretos AWS en Git o documentación.
- No crear otro proceso para backup.

## 6. AC cerrado

| AC | Verificación |
|----|----------------|
| Catálogo `BACKUP_DATABASE` | Proceso único; pantallas SDK. |
| MONO sin selector empresa | UI SDK; resolver tenant. |
| Backup RDS overwrite + polling | Gateway + lock `tasks:backup:rds:{cliente}`. |
| Agenda `next_run_at` | Wrapper schedule + backfill. |
| CA-10-01 Historial | `PartesTaskExecutionRepository` drena rowsets. |
| CA-10-02 Próxima ejecución | `partes:tasks-backfill-next-run-at`. |
| CA-10-04 Triage CC #10 | [D-VERIFICACION-CC-PQ-10](../updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-10-2026-10-30.md). |
| CA-10-05 BD tenant MONO | `PartesMonoBackupCompanyTargetResolver`. |
| CA-10-06 S3 sin ARN duplicado | `RdsBackupS3Arn::composeExportDestination`; smoke 2026-11-06. |
| CA-10-07 Motivo en historial | i18n host + pin react-core ≥ 2.4.30-beta.2. |
| CA-DEF Manual defer RDS | POST manual `EN_EJECUCION`; poller cierra en scheduler; pin ≥ 1.3.18-beta.32; ver adopción GEN-13 § smoke #9. |

**Fuera de esta TR:** saldo informe Paquete de horas → [TR-006](../100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) (CC #8 unificado 06/11/2026).

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-23 | A0 — wiring documental (código parcialmente cableado). |
| 2026-10-30 | Parte G — TR-update CC PQ #10 (sqlsrv, backfill, backup MONO/S3). |
| 2026-11-06 | Parte I — unificación + **Finalizado**. Backup RDS verificado por el usuario. |
| 2026-11-11 | Cableado host defer manual RDS polling + pin `laravel-core` 1.3.18-beta.32 (Framework update 11/11). |
