# HU-026 – Eliminación de tipo de tarea

## Épica
Épica 6: Gestión de Tipos de Tarea (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero eliminar un tipo de tarea que ya no se utiliza para mantener el catálogo limpio.

**Criterios de aceptación:**
- El supervisor puede acceder a la opción de eliminar un tipo de tarea desde el listado o detalle.
- Antes de eliminar, el sistema verifica si el tipo de tarea tiene tareas asociadas o clientes asociados (en `ClienteTipoTarea`).
- Si el tipo tiene tareas asociadas o clientes asociados, se muestra un error y no se permite la eliminación.
- Si el tipo no tiene referencias, se muestra un diálogo de confirmación.
- El diálogo muestra el código y descripción del tipo a eliminar.
- El usuario debe confirmar la eliminación.
- Al confirmar, el sistema elimina el tipo de tarea de la base de datos.
- Se muestra un mensaje de confirmación.
- El tipo desaparece del listado.

**Notas de reglas de negocio:**
- No se puede eliminar un tipo de tarea si tiene tareas asociadas (en `RegistroTarea`).
- No se puede eliminar un tipo de tarea si tiene clientes asociados (en `ClienteTipoTarea`).
- Código de error: 2114.

**Dependencias:** HU-025.

---

