# HU-GEN-13 — Adopción de Tareas Programadas en PARTES

Jira: [PIAPARTES-49](https://paqsystems.atlassian.net/browse/PIAPARTES-49)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-GEN-13-adopcion-tareas-programadas |
| Título | Definir y monitorear BACKUP_DATABASE desde GEN-13 |
| Épica / carpeta | 001 — Generalidades |
| Clasificación | MUST-HAVE |
| Estado | Finalizado |
| Última actualización | 2026-11-06 |
| SPEC origen | [SPEC-001-13-host-adopcion-tareas-programadas](../../05-open-spec/001-Generalidades/SPEC-001-13-host-adopcion-tareas-programadas.md) |
| TR relacionada(s) | [TR-GEN-13-adopcion-tareas-programadas](../../04-tareas/001-Generalidades/TR-GEN-13-adopcion-tareas-programadas.md) |
| Perfil | MONO/RDS |

## Historia

Como usuario autorizado por el permiso de la opción de menú de configuración
de tareas programadas, quiero definir y monitorear `BACKUP_DATABASE` desde las
pantallas GEN-13 del SDK, para disponer de backups RDS trazables sin un motor
paralelo en PARTES.

Como administrador del host PARTES, quiero ver el **historial de ejecuciones**
y la **próxima corrida** de las tareas programadas, para operar backups RDS
sin pantallas vacías por fallos de adapter sqlsrv.

## Alcance

- Consultar el catálogo de procesos y sus parámetros.
- Crear y editar una definición programada.
- Ejecutar manualmente o por agenda cuando el proceso lo soporte.
- Consultar ejecuciones, estados, logs y motivo de resultado.
- Ejecutar el backup de la empresa vigente mediante el contrato RDS del
  Framework.
- Completar o backfillear `next_run_at` para que la agenda tome la definición.

## Criterios de aceptación

- [x] Sin permiso sobre la opción de menú, el usuario no accede a Tareas Programadas.
- [x] Con permiso, las pantallas transversales provienen de `@paqsuite/react-core`.
- [x] `BACKUP_DATABASE` no permite seleccionar una empresa ni editar el nombre de base en MONO.
- [x] RDS no expone `backupPolicy`; fuerza overwrite y espera el estado terminal de la misma tarea RDS.
- [x] Un lock ocupado registra `OMITIDA`; error o timeout registra `FALLIDA`.
- [x] El usuario puede consultar el resultado y los logs de la ejecución.
- [x] Una definición activa con frecuencia y `next_run_at` se ejecuta sola vía `tasks:run-due` (sin disparo manual).
- [x] **CA-10-01** Dado al menos una fila en `pq_task_execution`, cuando abro Historial, entonces la grilla lista filas con total coherente.
- [x] **CA-10-02** Dada una definición activa con cron válido y `next_run_at` NULL legacy, cuando ejecuto el backfill o re-guardo, entonces la columna Próxima ejecución muestra fecha/hora.
- [x] **CA-10-04** La evidencia de verificación referencia el triage CC #10 (síntoma previo a beta.14).
- [x] **CA-10-05** Dado un backup `BACKUP_DATABASE` en MONO sin `nombre_bd` en catálogo, cuando ejecuto la tarea, entonces el proceso resuelve la BD operativa del tenant.
- [x] **CA-10-06** Dado `TASKS_BACKUP_S3_ARN_PREFIX` como prefijo bucket/carpeta, cuando RDS exporta a S3, entonces `@s3_arn_to_backup_to` apunta a un único ARN objeto `{prefijo}/{nombreBd}.bak`.
- [x] **CA-10-07** Cuando una ejecución termina en FALLIDA u OMITIDA, entonces el historial muestra el motivo en columna dedicada.

## Fuera de alcance

- Copiar componentes GEN al host.
- Crear una pantalla de backup específica de PARTES.
- Saldo del informe Paquete de horas (TR-006 / CC PQ #8).

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-23 | A0 — adopción host. |
| 2026-10-30 | Parte G — HU-update CC PQ #10. |
| 2026-11-06 | Parte I — unificación + **Finalizado**. Backup RDS verificado por el usuario. |
