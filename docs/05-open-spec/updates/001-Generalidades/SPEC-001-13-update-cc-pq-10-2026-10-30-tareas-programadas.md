# SPEC-001-13-update — CC PQ #10 (30/10/2026) — Monitor GEN-13 en host

| Campo | Valor |
|-------|--------|
| **Estado** | Pendiente |
| **SPEC base** | [SPEC-001-13-host-adopcion-tareas-programadas](./SPEC-001-13-host-adopcion-tareas-programadas.md) |
| **Origen** | `00-ControlCalidad-PQ` · CC **#10** · 30/10/2026 |
| **Alcance** | Bugfix host (sin cambio de producto Framework): historial de ejecuciones y columna `nextRunAt` en definiciones |

## Delta de alcance

1. **Listados sqlsrv:** Los SP `pq_sp_task_definition_list` y `pq_sp_task_execution_list` devuelven dos result sets (total + ítems). El adapter del SDK puede dejar `items` vacío en sqlsrv; el host debe drenar todos los rowsets antes de responder a la UI.
2. **Backfill operativo:** Definiciones creadas antes del wrapper `SchedulingTaskDefinitionRepository` pueden tener `next_run_at` NULL; el host expone comando de mantenimiento para recalcular sin editar manualmente cada fila.
3. **Saldo informe Paquete de horas:** Sin cambio de regla de negocio; continúa [TR-006-update CC #8](../../100-SistemaPartes/TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) (paridad window SQL + redeploy SP).

## Criterios de aceptación

| ID | Criterio |
|----|----------|
| CA-10-01 | Historial lista ejecuciones cuando existen filas en BD |
| CA-10-02 | Definiciones activas muestran `nextRunAt` tras backfill o re-guardado |
| CA-10-03 | Informe Paquete de horas cumple TR-006-update / doc 18 |
| CA-10-04 | Triage CC #10 documentado (no regresión beta.14) |
