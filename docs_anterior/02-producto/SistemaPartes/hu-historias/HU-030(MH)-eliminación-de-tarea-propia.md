# HU-030 – Eliminación de tarea propia

## Épica
Épica 7: Registro de Tareas


**Rol:** Empleado  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero eliminar una tarea que registré incorrectamente para mantener la precisión de los registros.

**Criterios de aceptación:**
- El empleado puede acceder a la opción de eliminar una tarea desde la lista de sus tareas.
- Solo puede eliminar tareas propias (donde `usuario_id` coincide con el usuario autenticado).
- El sistema valida que la tarea no esté cerrada (`cerrado = false`).
- Si la tarea está cerrada, se muestra un error y no se permite la eliminación.
- Se muestra un diálogo de confirmación.
- El diálogo muestra información de la tarea a eliminar (fecha, cliente, tipo, duración).
- El usuario debe confirmar la eliminación.
- Al confirmar, el sistema elimina el registro de tarea de la base de datos.
- Se muestra un mensaje de confirmación.
- La tarea desaparece de la lista.

**Notas de reglas de negocio:**
- Solo el autor puede eliminar su tarea.
- Una tarea cerrada no se puede eliminar.
- Código de error: 2111 (no se puede eliminar una tarea cerrada).

**Dependencias:** HU-028.

---

