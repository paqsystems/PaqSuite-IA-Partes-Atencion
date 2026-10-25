# SPEC-001-13 — Adopción host GEN-13 en PARTES

Jira: [PIAPARTES-49](https://paqsystems.atlassian.net/browse/PIAPARTES-49)

| Campo | Valor |
|---|---|
| ID | SPEC-001-13-host-adopcion-tareas-programadas |
| Host | PaqSuite-IA-Partes-Atencion |
| GEN | GEN-13 — Tareas programadas |
| Estado | A0 — Backlog (código parcialmente cableado; D/E/F pendientes) |
| HU | [HU-GEN-13-adopcion-tareas-programadas](../../../03-historias-usuario/updates/001-Generalidades/HU-GEN-13-adopcion-tareas-programadas.md) |
| TR | [TR-GEN-13-adopcion-tareas-programadas](../../../04-tareas/updates/001-Generalidades/TR-GEN-13-adopcion-tareas-programadas.md) |
| SoT Framework | `SPEC-001-13` + `TR-GEN-13-backup-windows-rds-update` |

## 1. Propósito

Declarar la adopción de GEN-13 en el host PARTES para configurar, ejecutar y
monitorear tareas programadas mediante el SDK Framework, sin copiar ni
reimplementar el motor, la API, el envelope o la UI transversal.

El perfil técnico de PARTES es `tenancy=single` + backend `rds`. La operación
de `BACKUP_DATABASE` usa la empresa vigente, export RDS a S3, polling terminal,
lock global por instalación y logs trazables.

## 2. Alcance host

- Consumir `paqsuite/laravel-core` y `@paqsuite/react-core` en sus versiones
  pinneadas por el ritual SDK.
- Adoptar las tablas, SP, rutas y comandos GEN-13 provistos por el Framework.
- Declarar el proceso único `BACKUP_DATABASE` en el catálogo host.
- Montar las páginas SDK de procesos, definiciones, ejecuciones y monitor.
- Exponer la opción de menú de configuración de tareas programadas.
- Gobernar el acceso exclusivamente por el permiso de esa opción de menú.
- Operar `TASKS_BACKUP_S3_ARN_PREFIX`, timeout, polling y lock según el SoT.

## 3. Fuera de alcance

- Motor o UI local de tareas.
- Segundo endpoint o proceso de backup.
- Credenciales AWS, SQL libre o secretos en documentación.
- Nuevas reglas de permisos fuera de la opción de menú.
- Implementación en este documento: esta SPEC define adopción y contrato host;
  el código queda fuera del alcance solicitado.

## 4. Criterios de aceptación

- El catálogo host expone `BACKUP_DATABASE` con `supportsManualExecution` y
  `supportsScheduledExecution` activos y simulación desactivada.
- La empresa vigente se resuelve por el contexto de tenancy; no se muestra
  selector MULTI en PARTES.
- El backup exige key S3 normalizada, `taskId`, polling `running|success|error`
  y liberación del lock ante éxito, error o timeout.
- La opción de menú y sus permisos son la única puerta de acceso funcional.
- Las páginas y cliente son exports del SDK; no hay fork host.
- La documentación de operación incluye smoke MONO/RDS y límites del ambiente.

## 5. Trazabilidad

- Framework: `docs/05-open-spec/updates/001-Generalidades/SPEC-001-13-update-backups-windows-rds.md`
- A0: [PIAPARTES-49](https://paqsystems.atlassian.net/browse/PIAPARTES-49)
