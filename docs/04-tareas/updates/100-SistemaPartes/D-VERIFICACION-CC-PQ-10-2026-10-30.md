# Verificación F — CC PQ #10 (30/10/2026)

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#10** |
| **Updates** | Familia GEN-13 unificada 06/11/2026 → [TR-GEN-13](../../001-Generalidades/TR-GEN-13-adopcion-tareas-programadas.md) · saldo informe CC #8 unificado 06/11/2026 → [TR-006](../../100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) |
| **Veredicto** | GEN-13 **Aprobado** (Parte I 06/11/2026; backup RDS verificado). CA-10-03 (informe) no forma parte de este cierre. |

## Triage (CA-10-04)

Los síntomas se registraron post **beta.14**; el diff del release no tocó UI de tareas ni el SP de informe (checksum estable). Causa probable: adapter sqlsrv multi-rowset + definiciones legacy sin `next_run_at`.

## Evidencia

| CA | Resultado | Notas |
|----|-----------|-------|
| CA-10-01 | OK (código) | `PartesTaskExecutionRepository` drena rowsets |
| CA-10-02 | OK (código + runbook) | `partes:tasks-backfill-next-run-at` |
| CA-10-03 | Cerrado con CC #8 | [D-VERIFICACION-CC-PQ-8](./D-VERIFICACION-CC-PQ-8-2026-10-25.md) · Parte I 06/11/2026 |
| CA-10-04 | OK | Este documento |
| CA-10-05 | OK (usuario 2026-11-06) | Backup MONO resuelve BD tenant |
| CA-10-06 | OK (usuario 2026-11-06) | Destino S3 con prefijo; sin ARN duplicado |
| CA-10-07 | OK (código + pin SDK) | Columna motivo en historial |

## Observaciones

- Deploy **develop** (PR #108, SHA `3e3c16d`): Forge + Vercel aplicados; `installation:update` 10/10.
- Backfill en Forge: 0 definiciones pendientes (OK).
- **2026-11-06:** el usuario verificó backup RDS (éxito operativo, destino S3 y scheduler multi-tenant). Familia GEN-13 **Finalizado** (Parte I).
- Runbook S3: [adopcion-gen-13-tareas-programadas.md](../../../06-operacion/adopcion-gen-13-tareas-programadas.md) § «Backup RDS → S3».

## Deploy

- Redeploy SP: `pq_sp_partes_informe_paquete_horas.sql` vía post-deploy / `deploy-sp.php`.
- Sin migraciones nuevas obligatorias.
- Comando opcional en servidor: `php artisan partes:tasks-backfill-next-run-at`.
