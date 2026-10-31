# TR-GEN-13 — Wiring documental de Tareas Programadas en PARTES

Jira: [PIAPARTES-49](https://paqsystems.atlassian.net/browse/PIAPARTES-49)

| Campo | Valor |
|---|---|
| HU | [HU-GEN-13 adopción](../../../03-historias-usuario/updates/001-Generalidades/HU-GEN-13-adopcion-tareas-programadas.md) |
| SPEC | [SPEC-001-13 host](../../../05-open-spec/updates/001-Generalidades/SPEC-001-13-host-adopcion-tareas-programadas.md) |
| SoT Framework | `TR-GEN-13-backup-windows-rds-update` · cableado Must: `PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-gen-13-tareas-programadas-host.md` |
| Estado | A0 — Backlog (código parcialmente cableado; D/E/F pendientes) |
| Prioridad | MUST |

## 1. Objetivo

Definir el contrato de adopción de GEN-13 en PARTES MONO/RDS. El host conserva
solo configuración, permisos de menú, wiring de plataforma y operación; el
motor y la UI pertenecen a los paquetes SDK.

## 2. Contratos host

- Proceso: `BACKUP_DATABASE`.
- Perfil: `tenancy=single`, backend `rds`.
- Objetivo: empresa vigente y base resuelta por el contexto de instalación.
- Destino: `TASKS_BACKUP_S3_ARN_PREFIX` con key normalizada.
- CLI: `PAQSUITE_CLIENTE` (código `EMPRESAS_CONEXION`) mapeado a
  `config('paqsuite.clienteCodigo')` para `tasks:run-due`.
- Agenda: al crear/editar definición el host completa `nextRunAt` si falta
  (`SchedulingTaskDefinitionRepository`). El SP due exige `next_run_at`.
- Repositorios: Must implementar **todos** los métodos de
  `TaskDefinitionRepository` en cada wrapper (p. ej. `claimNextRunAt` desde
  laravel-core 1.3.18-beta.18). Ver Framework adopcion-gen-13-tareas-programadas-host §3.
- SQL: `installation:update` antes de `deploy-sp.php`; overrides en
  `backend/database/sp/` (p. ej. `pq_sp_task_definition_parameter_set.sql`).
- Snapshot: SP host `pq_sp_task_execution_save_snapshot` expande JSON a
  `parameter_code` + `value_json`.
- Polling: intervalo configurado y estados `running|success|error`.
- Lock: `tasks:backup:rds:{cliente}`, adquisición no bloqueante, renovación y
  liberación en `finally`.
- Acceso: permiso de la opción de menú de configuración de tareas programadas.

## 3. Catálogo y operación

La definición debe conservar los metadatos GEN-13 de `companyIds`,
`includeDateTimeInName`, `s3ArnPrefix`, `rdsTimeoutMinutes` y
`rdsPollingIntervalSeconds`, respetando qué valores son derivados y cuáles son
editables. En MONO no se muestra `companyIds`.

La operación debe incluir:

- `tasks:validate-catalog`;
- `tasks:run-due` con frecuencia acordada y sin solapamiento;
- `tasks:purge-history`;
- smoke catálogo → definición → ejecución → logs;
- verificación operativa posterior con RDS/S3 autorizado.

## 4. No hacer

- No copiar `TaskExecutionService`, controllers, cliente HTTP ni componentes
  de `react-core`.
- No agregar SQL de negocio ad-hoc.
- No incluir secretos AWS en Git o documentación.
- No crear otro proceso para backup.

## 5. Evidencia esperada

- SPEC/HU/TR consistentes con el Framework.
- Permiso de menú trazable.
- Configuración de entorno documentada sin valores secretos.
- Smoke MONO/RDS documentado; la prueba productiva queda pendiente de
  credenciales y ambiente autorizado.
