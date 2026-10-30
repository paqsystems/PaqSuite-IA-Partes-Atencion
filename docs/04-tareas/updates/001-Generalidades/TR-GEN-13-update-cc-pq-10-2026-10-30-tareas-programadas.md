# TR-GEN-13-update — Monitor GEN-13 sqlsrv + backfill (CC PQ #10)

| Campo | Valor |
|-------|--------|
| **Estado** | Pendiente |
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
| `PartesTaskExecutionRepository` | Decorador: `listExecutions` drena rowsets sqlsrv |
| `partes:tasks-backfill-next-run-at` | Recalcula `nextRunAt` vía `SchedulingTaskDefinitionRepository` |
| `AppServiceProvider` | Bindings anteriores |

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

## 5) Tests

- Suite Partes informe (saldo): `ApiV1PartesInformeTest` (sqlite / PHP parity).
- Smoke manual: historial + definiciones en sqlsrv.

## 6) Tareas D

| ID | Descripción | DoD |
|----|-------------|-----|
| T1 | Repositorios decoradores | CA-10-01 |
| T2 | Comando backfill | CA-10-02 |
| T3 | SP informe + redeploy | CA-10-03 |
| T4 | D-VERIFICACION CC #10 | CA-10-04 |
