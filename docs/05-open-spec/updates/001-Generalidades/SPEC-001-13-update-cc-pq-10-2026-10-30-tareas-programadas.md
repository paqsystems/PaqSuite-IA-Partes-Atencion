# SPEC-001-13-update — CC PQ #10 (30/10/2026) — Monitor GEN-13 en host

| Campo | Valor |
|-------|--------|
| **Estado** | En Control Calidad |
| **SPEC base** | [SPEC-001-13-host-adopcion-tareas-programadas](./SPEC-001-13-host-adopcion-tareas-programadas.md) |
| **Origen** | `00-ControlCalidad-PQ` · CC **#10** · 30/10/2026 |
| **Alcance** | Bugfix host: monitor GEN-13 (listados, mensajes, backup MONO, runbook S3) + saldo informe (TR-006) |

## Delta de alcance

1. **Listados sqlsrv:** Los SP `pq_sp_task_definition_list` y `pq_sp_task_execution_list` devuelven dos result sets (total + ítems). El adapter del SDK puede dejar `items` vacío en sqlsrv; el host debe drenar todos los rowsets antes de responder a la UI.
2. **Backfill operativo:** Definiciones creadas antes del wrapper `SchedulingTaskDefinitionRepository` pueden tener `next_run_at` NULL; el host expone comando de mantenimiento para recalcular sin editar manualmente cada fila.
3. **Saldo informe Paquete de horas:** Sin cambio de regla de negocio; continúa [TR-006-update CC #8](../../100-SistemaPartes/TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) (paridad window SQL + redeploy SP).
4. **Ejecuciones — escritura sqlsrv:** Snapshot, append log y update status vía `statement` cuando el SP no devuelve rowset; hidratación de `message` en listado/historial.
5. **Backup MONO:** `PartesMonoBackupCompanyTargetResolver` cuando `nombre_bd` falta en catálogo (BD operativa del tenant).
6. **Backup RDS → S3:** Documentar `TASKS_BACKUP_S3_ARN_PREFIX`; gateway host evita ARN S3 duplicado si el SDK pasa `s3Key` completo como `filePrefix`.
7. **UI:** Columna motivo en historial (`@paqsuite/react-core` ≥ `2.4.30-beta.2`) + claves i18n host.

## Criterios de aceptación

| ID | Criterio |
|----|----------|
| CA-10-01 | Historial lista ejecuciones cuando existen filas en BD |
| CA-10-02 | Definiciones activas muestran `nextRunAt` tras backfill o re-guardado |
| CA-10-03 | Informe Paquete de horas cumple TR-006-update / doc 18 |
| CA-10-04 | Triage CC #10 documentado (no regresión beta.14) |
| CA-10-05 | Backup MONO resuelve BD tenant sin `nombre_bd` |
| CA-10-06 | Destino S3 RDS con prefijo `TASKS_BACKUP_S3_ARN_PREFIX` documentado y sin ARN duplicado |
| CA-10-07 | Historial expone mensaje de resultado cuando la API lo devuelve |
