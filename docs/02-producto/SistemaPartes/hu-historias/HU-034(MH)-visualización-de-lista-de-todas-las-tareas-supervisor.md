# HU-034 – Visualización de lista de todas las tareas (supervisor)

## Épica
Épica 7: Registro de Tareas


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero ver la lista de todas las tareas de todos los usuarios para supervisar el trabajo realizado.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Todas las Tareas" o "Supervisión".
- Se muestra una tabla con todas las tareas de todos los usuarios.
- La tabla muestra: fecha, empleado, cliente, tipo de tarea, duración (en minutos y horas), sin cargo, presencial, observación (truncada), cerrado (sí/no), acciones (editar, eliminar).
- Las tareas se listan paginadas.
- Se puede filtrar por rango de fechas (fecha desde, fecha hasta).
- Se puede filtrar por empleado.
- Se puede filtrar por cliente.
- Se puede filtrar por tipo de tarea.
- Se puede buscar por texto en la observación.
- Se puede ordenar por fecha, empleado, cliente (ascendente/descendente).
- Se muestra el total de tareas y el total de horas del período filtrado.
- Las tareas cerradas se muestran claramente diferenciadas.
- Las acciones de editar/eliminar están deshabilitadas para tareas cerradas.

**Notas de reglas de negocio:**
- Se muestran todas las tareas de todos los usuarios.
- El supervisor puede editar y eliminar cualquier tarea (excepto cerradas).

**Dependencias:** HU-028, HU-031, HU-032.

---

