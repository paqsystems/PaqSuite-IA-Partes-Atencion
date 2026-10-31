# TR-GEN-13-update — Monitor GEN-13 sqlsrv + backfill (CC PQ #10)

| Campo | Valor |
|-------|--------|
| **Estado** | En Control Calidad |
| **Prioridad** | P1 |
| **HU-update** | [HU-GEN-13-update-cc-pq-10](../../../03-historias-usuario/updates/001-Generalidades/HU-GEN-13-update-cc-pq-10-2026-10-30-tareas-programadas.md) |
| **SPEC-update** | [SPEC-001-13-update-cc-pq-10](../../../05-open-spec/updates/001-Generalidades/SPEC-001-13-update-cc-pq-10-2026-10-30-tareas-programadas.md) |
| **Origen** | `00-ControlCalidad-PQ` · CC **#10** · 30/10/2026 |

## 1) Resumen

Corregir listados GEN-13 en sqlsrv (definiciones + ejecuciones) y comando de backfill `next_run_at`. Ítem saldo informe: ejecutar / verificar [TR-006-update CC #8](../100-SistemaPartes/TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md).

## 2) Backend

| Pieza | Acción |
|-------|--------|
| `PartesTaskDefinitionRepository` | Decorador: `listDefinitions` drena rowsets sqlsrv |
| `PartesTaskExecutionRepository` | Drena rowsets en listado; `statement` en escrituras; hidrata `message` |
| `PartesTaskDefinitionRepository` | `clearNextRunAt`; fallback SP schedule 4 params si RDS sin `@reset_next_run_at` |
| `PartesMonoBackupCompanyTargetResolver` | Fallback BD tenant MONO |
| `RdsSqlServerBackupGateway` | `RdsBackupS3Arn::composeExportDestination` (SoT SDK; sin concat manual) |
| `partes:tasks-backfill-next-run-at` | Recalcula `nextRunAt` vía `SchedulingTaskDefinitionRepository` |
| Doc cableado Must (2026-10-31) | Framework `adopcion-gen-13-tareas-programadas-host.md`; Partes adopcion § Inventario + override `pq_sp_task_definition_parameter_set.sql` |
| `AppServiceProvider` | Bindings resolver backup + gateway RDS |
| `frontend` | i18n motivo ejecución; pin `@paqsuite/react-core` 2.4.30-beta.2 |

## 3) SQL / deploy

| Artefacto | Acción |
|-----------|--------|
| `pq_sp_partes_informe_paquete_horas.sql` | `PARTITION BY cliente_id` en window (paridad doc 18) |
| Forge | `installation:update` + post-deploy domain SP |

## 4) Operación post-deploy

```powershell
cd backend
php artisan partes:tasks-backfill-next-run-at --dry-run
php artisan partes:tasks-backfill-next-run-at
```

Verificar scheduler Forge (`schedule:run`) y `PAQSUITE_CLIENTE` según [adopcion-gen-13-tareas-programadas.md](../../../06-operacion/adopcion-gen-13-tareas-programadas.md).

Comprobar en Forge:

- `TASKS_BACKUP_S3_ARN_PREFIX` = prefijo bucket/carpeta (sin `.bak`).
- Smoke backup manual: estado terminal SUCCESS y objeto en S3 bajo el prefijo.
- Historial UI con columna motivo tras redeploy Vercel.

## 5) Tests

- Suite Partes informe (saldo): `ApiV1PartesInformeTest` (sqlite / PHP parity).
- `RdsSqlServerBackupGatewayTest` (destino S3 con y sin clave ARN completa).
- `PartesMonoBackupCompanyTargetResolverTest`.
- Smoke manual: historial + definiciones + backup en sqlsrv Forge.

## 6) Tareas D

| ID | Descripción | DoD |
|----|-------------|-----|
| T1 | Repositorios decoradores | CA-10-01 |
| T2 | Comando backfill | CA-10-02 |
| T3 | SP informe + redeploy | CA-10-03 |
| T4 | D-VERIFICACION CC #10 | CA-10-04 |
| T5 | Runbook S3 + gateway + resolver MONO | CA-10-05…CA-10-07 |
