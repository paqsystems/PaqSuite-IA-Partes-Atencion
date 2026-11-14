# SPEC-001-13 — Adopción host GEN-13 en PARTES

Jira: [PIAPARTES-49](https://paqsystems.atlassian.net/browse/PIAPARTES-49)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-001-13-host-adopcion-tareas-programadas |
| Título | Adopción host GEN-13 (tareas programadas / backup RDS) |
| Épica / carpeta | 001 — Generalidades |
| Host | PaqSuite-IA-Partes-Atencion |
| GEN | GEN-13 — Tareas programadas |
| Estado | Finalizado |
| Última actualización | 2026-11-11 (defer manual RDS — doc operativa; SPEC base **Finalizado**) |
| HU relacionada(s) | [HU-GEN-13-adopcion-tareas-programadas](../../03-historias-usuario/001-Generalidades/HU-GEN-13-adopcion-tareas-programadas.md) |
| TR relacionada(s) | [TR-GEN-13-adopcion-tareas-programadas](../../04-tareas/001-Generalidades/TR-GEN-13-adopcion-tareas-programadas.md) |
| SoT Framework | `SPEC-001-13` + `TR-GEN-13-backup-windows-rds-update` + **`docs/06-operacion/adopcion-gen-13-tareas-programadas-host.md`** (cableado Must) |
| Operación host | [adopcion-gen-13-tareas-programadas.md](../../06-operacion/adopcion-gen-13-tareas-programadas.md) |
| F / verificación | [D-VERIFICACION-CC-PQ-10](../../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-10-2026-10-30.md) |

```text
[Proceso] Tareas programadas de PARTES: adoptar GEN-13. UI/motor = TaskProcessesPage / TaskDefinitionsPage / TaskExecutionsPage + TaskProcessRegistry. No reimplementar.
SoT: Framework SPEC-001-13. Host: BACKUP_DATABASE, catálogo, SP overrides, menú, TASKS_BACKUP_S3_ARN_PREFIX, scheduler.
```

## 1. Propósito

Declarar la adopción de GEN-13 en el host PARTES para configurar, ejecutar y
monitorear tareas programadas mediante el SDK Framework, sin copiar ni
reimplementar el motor, la API, el envelope o la UI transversal.

El perfil técnico de PARTES es `tenancy=single` + backend `rds`. La operación
de `BACKUP_DATABASE` usa la empresa vigente, export RDS a S3, polling terminal,
lock global por instalación y logs trazables.

## 2. Alcance host

- Consumir `paqsuite/laravel-core` y `@paqsuite/react-core` en sus versiones
  pinneadas por el ritual SDK.
- Adoptar las tablas, SP, rutas y comandos GEN-13 provistos por el Framework.
- Declarar el proceso único `BACKUP_DATABASE` en el catálogo host.
- Montar las páginas SDK de procesos, definiciones, ejecuciones y monitor.
- Exponer la opción de menú de configuración de tareas programadas.
- Gobernar el acceso exclusivamente por el permiso de esa opción de menú.
- Operar `TASKS_BACKUP_S3_ARN_PREFIX`, `PAQSUITE_CLIENTE`, timeout, polling y lock según el SoT.
  Formato del prefijo S3 y errores RDS: [adopcion-gen-13-tareas-programadas.md](../../06-operacion/adopcion-gen-13-tareas-programadas.md) § «Backup RDS → S3».
- Completar `next_run_at` en definiciones activas (el SP due no lista `NULL`).
- Drenar todos los rowsets sqlsrv en listados de definiciones y ejecuciones.
- Backfill operativo de `next_run_at` para definiciones legacy.
- Resolver BD tenant MONO cuando el catálogo no trae `nombre_bd`.
- Destino S3 RDS con prefijo de carpeta (sin ARN duplicado) y columna motivo en historial.

## 3. Fuera de alcance

- Motor o UI local de tareas.
- Segundo endpoint o proceso de backup.
- Credenciales AWS, SQL libre o secretos en documentación.
- Nuevas reglas de permisos fuera de la opción de menú.
- Saldo del informe **Paquete de horas** (familia TR-006 / CC PQ #8; no es GEN-13).

## 4. Criterios de aceptación

- [x] El catálogo host expone `BACKUP_DATABASE` con `supportsManualExecution` y
  `supportsScheduledExecution` activos y simulación desactivada.
- [x] La empresa vigente se resuelve por el contexto de tenancy; no se muestra
  selector MULTI en PARTES.
- [x] El backup exige key S3 normalizada, `taskId`, polling `running|success|error`
  y liberación del lock ante éxito, error o timeout.
- [x] La opción de menú y sus permisos son la única puerta de acceso funcional.
- [x] Las páginas y cliente son exports del SDK; no hay fork host.
- [x] La documentación de operación incluye smoke MONO/RDS y límites del ambiente.
- [x] **CA-10-01** Historial lista ejecuciones cuando existen filas en BD.
- [x] **CA-10-02** Definiciones activas muestran `nextRunAt` tras backfill o re-guardado.
- [x] **CA-10-04** Triage CC #10 documentado (no regresión beta.14).
- [x] **CA-10-05** Backup MONO resuelve BD tenant sin `nombre_bd`.
- [x] **CA-10-06** Destino S3 RDS con prefijo `TASKS_BACKUP_S3_ARN_PREFIX` y sin ARN duplicado.
- [x] **CA-10-07** Historial expone mensaje de resultado cuando la API lo devuelve.

## 5. Delta CC PQ #10 (incorporado 2026-11-06)

1. **Listados sqlsrv:** los SP `pq_sp_task_definition_list` y `pq_sp_task_execution_list` devuelven dos result sets; el host drena todos los rowsets.
2. **Backfill:** comando `partes:tasks-backfill-next-run-at` para definiciones con `next_run_at` NULL.
3. **Ejecuciones:** snapshot, append log y update status vía `statement` cuando el SP no devuelve rowset; hidratación de `message`.
4. **Backup MONO:** `PartesMonoBackupCompanyTargetResolver` cuando falta `nombre_bd`.
5. **Backup RDS → S3:** `TASKS_BACKUP_S3_ARN_PREFIX` como prefijo; gateway host usa `RdsBackupS3Arn::composeExportDestination`.
6. **UI:** columna motivo en historial (`@paqsuite/react-core` ≥ `2.4.30-beta.2`) + i18n host.
7. **Scheduler multi-tenant:** `partes:tasks-run-due` recorre instalaciones SQL directo del proyecto (`SchedulerClienteResolver`); `PAQSUITE_CLIENTE` es fallback.
8. **Backup manual RDS sin bloqueo HTTP (2026-11-11):** con `PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING=true` y pin `laravel-core` **≥ 1.3.18-beta.32`, el manual deja `EN_EJECUCION` y el host cierra vía `RdsDeferredBackupExecutionPoller` en el cron. SoT Framework: `SPEC-001-13-update-defer-manual-rds-polling-2026-11-11`.

## 6. Trazabilidad

- Framework: `docs/05-open-spec/updates/001-Generalidades/SPEC-001-13-update-backups-windows-rds.md`
- A0 / cierre: [PIAPARTES-49](https://paqsystems.atlassian.net/browse/PIAPARTES-49)
- CC PQ #10: `docs/00-ControlCalidad/00-ControlCalidad-PQ.md`

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-23 | A0 — adopción host GEN-13 (código parcialmente cableado). |
| 2026-10-30 | Parte G — SPEC-update CC PQ #10 (monitor sqlsrv + backup MONO/S3). |
| 2026-11-06 | Parte I — unificación + **Finalizado**. Backup RDS verificado por el usuario. |
| 2026-11-11 | Delta operativo defer manual RDS (adopción + pin beta.32); sin reabrir alcance SPEC base. |
