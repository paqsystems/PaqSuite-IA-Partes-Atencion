# HU-021 – Eliminación de empleado

## Épica
Épica 5: Gestión de Empleados (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero eliminar un empleado que ya no trabaja para mantener el catálogo actualizado.

**Criterios de aceptación:**
- El supervisor puede acceder a la opción de eliminar un empleado desde el listado o detalle.
- Antes de eliminar, el sistema verifica si el empleado tiene tareas asociadas.
- Si el empleado tiene tareas asociadas, se muestra un error y no se permite la eliminación.
- Si el empleado no tiene tareas asociadas, se muestra un diálogo de confirmación.
- El diálogo muestra el código y nombre del empleado a eliminar.
- El usuario debe confirmar la eliminación.
- Al confirmar, el sistema elimina el empleado de la base de datos.
- Se muestra un mensaje de confirmación.
- El empleado desaparece del listado.

**Notas de reglas de negocio:**
- No se puede eliminar un empleado si tiene tareas asociadas (integridad referencial).
- Código de error: 2113.

**Dependencias:** HU-020.

---

