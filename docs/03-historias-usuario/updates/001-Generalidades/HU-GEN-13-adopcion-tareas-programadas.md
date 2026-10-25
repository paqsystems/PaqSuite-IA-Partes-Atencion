# HU-GEN-13 — Adopción de Tareas Programadas en PARTES

Jira: [PIAPARTES-49](https://paqsystems.atlassian.net/browse/PIAPARTES-49)

| Campo | Valor |
|---|---|
| ID | HU-GEN-13-adopcion-tareas-programadas |
| SPEC | [SPEC-001-13 host](../../../05-open-spec/updates/001-Generalidades/SPEC-001-13-host-adopcion-tareas-programadas.md) |
| TR | [TR-GEN-13 adopción](../../../04-tareas/updates/001-Generalidades/TR-GEN-13-adopcion-tareas-programadas.md) |
| Estado | A0 — Backlog |
| Perfil | MONO/RDS |

## Historia

Como usuario autorizado por el permiso de la opción de menú de configuración
de tareas programadas, quiero definir y monitorear `BACKUP_DATABASE` desde las
pantallas GEN-13 del SDK, para disponer de backups RDS trazables sin un motor
paralelo en PARTES.

## Alcance

- Consultar el catálogo de procesos y sus parámetros.
- Crear y editar una definición programada.
- Ejecutar manualmente o por agenda cuando el proceso lo soporte.
- Consultar ejecuciones, estados y logs.
- Ejecutar el backup de la empresa vigente mediante el contrato RDS del
  Framework.

## Criterios de aceptación

1. Sin permiso sobre la opción de menú, el usuario no accede a Tareas
   Programadas.
2. Con permiso, las pantallas transversales provienen de
   `@paqsuite/react-core`.
3. `BACKUP_DATABASE` no permite seleccionar una empresa ni editar el nombre de
   base en MONO.
4. RDS no expone `backupPolicy`; fuerza overwrite y espera el estado terminal
   de la misma tarea RDS.
5. Un lock ocupado registra `OMITIDA`; error o timeout registra `FALLIDA`.
6. El usuario puede consultar el resultado y los logs de la ejecución.

## Fuera de alcance

- Copiar componentes GEN al host.
- Crear una pantalla de backup específica de PARTES.
- Ejecutar pruebas productivas con credenciales reales en esta HU.
