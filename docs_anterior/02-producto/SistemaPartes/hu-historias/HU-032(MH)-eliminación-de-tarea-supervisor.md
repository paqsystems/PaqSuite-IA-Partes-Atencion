# HU-032 – Eliminación de tarea (supervisor)

## Épica
Épica 7: Registro de Tareas


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero eliminar cualquier tarea del sistema para mantener la precisión de los registros.

**Criterios de aceptación:**
- El supervisor puede acceder a la opción de eliminar cualquier tarea desde la lista de tareas.
- El sistema valida que la tarea no esté cerrada (`cerrado = false`).
- Si la tarea está cerrada, se muestra un error y no se permite la eliminación.
- Se muestra un diálogo de confirmación.
- El diálogo muestra información de la tarea a eliminar (fecha, cliente, tipo, duración, empleado).
- El usuario debe confirmar la eliminación.
- Al confirmar, el sistema elimina el registro de tarea de la base de datos.
- Se muestra un mensaje de confirmación.
- La tarea desaparece de la lista.

**Notas de reglas de negocio:**
- El supervisor puede eliminar tareas de cualquier usuario.
- Una tarea cerrada no se puede eliminar.
- Código de error: 2111.

**Dependencias:** HU-028.

---

