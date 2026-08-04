# HU-033 – Visualización de lista de tareas propias

## Épica
Épica 7: Registro de Tareas


**Rol:** Empleado  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero ver la lista de mis tareas registradas para controlar lo que cargué y poder editarlas o eliminarlas.

**Criterios de aceptación:**
- El empleado puede acceder a la sección "Mis Tareas".
- Se muestra una tabla con todas las tareas del usuario autenticado.
- La tabla muestra: fecha, cliente, tipo de tarea, duración (en minutos y horas), sin cargo, presencial, observación (truncada), cerrado (sí/no), acciones (editar, eliminar).
- Las tareas se listan paginadas.
- Se puede filtrar por rango de fechas (fecha desde, fecha hasta).
- Se puede filtrar por cliente.
- Se puede filtrar por tipo de tarea.
- Se puede buscar por texto en la observación.
- Se puede ordenar por fecha (ascendente/descendente).
- Se muestra el total de tareas y el total de horas del período filtrado.
- Las tareas cerradas se muestran claramente diferenciadas (opcional: con indicador visual).
- Las acciones de editar/eliminar están deshabilitadas para tareas cerradas.

**Notas de reglas de negocio:**
- Solo se muestran tareas del usuario autenticado.
- Las tareas cerradas no se pueden editar ni eliminar.

**Dependencias:** HU-028.

---

