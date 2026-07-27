# HU-029 – Edición de tarea propia

## Épica
Épica 7: Registro de Tareas


**Rol:** Empleado  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero editar una tarea que registré para corregir errores de carga.

**Criterios de aceptación:**
- El empleado puede acceder a la edición de una tarea desde la lista de sus tareas.
- Solo puede editar tareas propias (donde `usuario_id` coincide con el usuario autenticado).
- El sistema valida que la tarea no esté cerrada (`cerrado = false`).
- Si la tarea está cerrada, se muestra un error y no se permite la edición.
- Se carga el formulario con los datos actuales de la tarea.
- El empleado puede modificar todos los campos: fecha, cliente, tipo de tarea, duración, sin cargo, presencial, observación.
- Se aplican las mismas validaciones que en la creación.
- El `usuario_id` no es modificable (solo lectura, muestra el nombre del empleado).
- Al guardar, el sistema actualiza el registro de tarea en la base de datos.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan en la lista de tareas.

**Notas de reglas de negocio:**
- Solo el autor puede editar su tarea.
- Una tarea cerrada no se puede modificar.
- Código de error: 2110 (no se puede modificar una tarea cerrada).

**Dependencias:** HU-028.

---

