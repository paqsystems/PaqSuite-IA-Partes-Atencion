# HU-031 – Edición de tarea (supervisor)

## Épica
Épica 7: Registro de Tareas


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero editar cualquier tarea del sistema para corregir errores o ajustar información.

**Criterios de aceptación:**
- El supervisor puede acceder a la edición de cualquier tarea desde la lista de tareas.
- El sistema valida que la tarea no esté cerrada (`cerrado = false`).
- Si la tarea está cerrada, se muestra un error y no se permite la edición.
- Se carga el formulario con los datos actuales de la tarea.
- El supervisor puede modificar todos los campos: fecha, cliente, tipo de tarea, duración, sin cargo, presencial, observación.
- El supervisor puede cambiar el empleado propietario de la tarea (selector de usuarios).
- Se aplican las mismas validaciones que en la creación.
- Al guardar, el sistema actualiza el registro de tarea en la base de datos.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan en la lista de tareas.

**Notas de reglas de negocio:**
- El supervisor puede editar tareas de cualquier usuario.
- Una tarea cerrada no se puede modificar.
- Código de error: 2110.

**Dependencias:** HU-028, HU-019 (gestión de usuarios para selector).

---

