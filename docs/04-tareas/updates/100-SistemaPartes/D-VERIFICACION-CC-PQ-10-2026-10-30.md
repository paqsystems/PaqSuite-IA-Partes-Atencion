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

- Smoke **sqlsrv** en Forge (historial con filas reales) pendiente de ejecutar por operador tras deploy.
- Tras deploy: backfill + comprobar columna Próxima ejecución en UI.

## Deploy

- Redeploy SP: `pq_sp_partes_informe_paquete_horas.sql` vía post-deploy / `deploy-sp.php`.
- Sin migraciones nuevas obligatorias.
- Comando opcional en servidor: `php artisan partes:tasks-backfill-next-run-at`.
