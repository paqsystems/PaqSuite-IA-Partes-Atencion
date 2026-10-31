# HU-GEN-13-update — Monitor tareas programadas (CC PQ #10)

| Campo | Valor |
|-------|--------|
| **Estado** | En Control Calidad |
| **HU base** | [HU-GEN-13-adopcion-tareas-programadas](./HU-GEN-13-adopcion-tareas-programadas.md) |
| **SPEC-update** | [SPEC-001-13-update-cc-pq-10-2026-10-30-tareas-programadas](../../../05-open-spec/updates/001-Generalidades/SPEC-001-13-update-cc-pq-10-2026-10-30-tareas-programadas.md) |
| **TR-update** | [TR-GEN-13-update-cc-pq-10-2026-10-30-tareas-programadas](../../../04-tareas/updates/001-Generalidades/TR-GEN-13-update-cc-pq-10-2026-10-30-tareas-programadas.md) |
| **Origen** | `00-ControlCalidad-PQ` · CC **#10** · 30/10/2026 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | En Control Calidad |

## Narrativa

Como administrador del host PARTES, quiero ver el **historial de ejecuciones** y la **próxima corrida** de las tareas programadas, para operar backups RDS y otras definiciones GEN-13 sin pantallas vacías por fallos de adapter sqlsrv.

## Criterios de aceptación

- **CA-10-01:** Dado al menos una fila en `pq_task_execution`, cuando abro Historial, entonces la grilla lista filas con total coherente.
- **CA-10-02:** Dada una definición activa con cron válido y `next_run_at` NULL legacy, cuando ejecuto el backfill documentado o re-guardo la definición, entonces la columna Próxima ejecución muestra fecha/hora.
- **CA-10-04:** La evidencia de verificación referencia el triage CC #10 (síntoma previo a beta.14).
- **CA-10-05:** Dado un backup `BACKUP_DATABASE` en MONO sin `nombre_bd` en catálogo, cuando ejecuto la tarea, entonces el proceso resuelve la BD operativa del tenant (no «Backup company target not found»).
- **CA-10-06:** Dado `TASKS_BACKUP_S3_ARN_PREFIX` configurado como prefijo bucket/carpeta, cuando RDS exporta a S3, entonces `@s3_arn_to_backup_to` apunta a un único ARN objeto `{prefijo}/{nombreBd}.bak` (sin ARN duplicado en la ruta).
- **CA-10-07:** Cuando una ejecución termina en FALLIDA u OMITIDA, entonces el historial muestra el motivo en columna dedicada (SDK + i18n host).
