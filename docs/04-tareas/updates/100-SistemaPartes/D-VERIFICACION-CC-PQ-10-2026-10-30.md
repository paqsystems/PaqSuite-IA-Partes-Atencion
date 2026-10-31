# Verificación F — CC PQ #10 (30/10/2026)

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#10** |
| **Updates** | TR-GEN-13-update-cc-pq-10 · TR-006-update CC #8 (saldo) |
| **Veredicto** | Aprobado con observaciones |

## Triage (CA-10-04)

Los síntomas se registraron post **beta.14**; el diff del release no tocó UI de tareas ni el SP de informe (checksum estable). Causa probable: adapter sqlsrv multi-rowset + definiciones legacy sin `next_run_at`.

## Evidencia

| CA | Resultado | Notas |
|----|-----------|-------|
| CA-10-01 | OK (código) | `PartesTaskExecutionRepository` drena rowsets |
| CA-10-02 | OK (código + runbook) | `partes:tasks-backfill-next-run-at` |
| CA-10-03 | OK (tests CI) | `ApiV1PartesInformeTest` saldo [-140,-110]; SP window `PARTITION BY cliente_id` |
| CA-10-04 | OK | Este documento |

## Observaciones

- Deploy **develop** (PR #108, SHA `3e3c16d`): Forge + Vercel aplicados; `installation:update` 10/10.
- Backfill en Forge: 0 definiciones pendientes (OK).
- Backup manual post-fix: revisar ejecución más reciente — destino S3 debe ser
  `{TASKS_BACKUP_S3_ARN_PREFIX}/{nombreBd}.bak` (gateway host corrige doble ARN).
- Smoke UI: historial con **Motivo / mensaje** requiere preview con
  `@paqsuite/react-core` ≥ `2.4.30-beta.2`.
- Runbook S3: [adopcion-gen-13-tareas-programadas.md](../../../06-operacion/adopcion-gen-13-tareas-programadas.md) § «Backup RDS → S3».

## Deploy

- Redeploy SP: `pq_sp_partes_informe_paquete_horas.sql` vía post-deploy / `deploy-sp.php`.
- Sin migraciones nuevas obligatorias.
- Comando opcional en servidor: `php artisan partes:tasks-backfill-next-run-at`.
