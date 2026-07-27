# HU-017 – Eliminación de tipo de cliente

## Épica
Épica 4: Gestión de Tipos de Cliente (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero eliminar un tipo de cliente que ya no se utiliza para mantener el catálogo limpio.

**Criterios de aceptación:**
- El supervisor puede acceder a la opción de eliminar un tipo de cliente desde el listado o detalle.
- Antes de eliminar, el sistema verifica si el tipo de cliente tiene clientes asociados.
- Si el tipo tiene clientes asociados, se muestra un error y no se permite la eliminación.
- Si el tipo no tiene clientes asociados, se muestra un diálogo de confirmación.
- El diálogo muestra el código y descripción del tipo a eliminar.
- El usuario debe confirmar la eliminación.
- Al confirmar, el sistema elimina el tipo de cliente de la base de datos.
- Se muestra un mensaje de confirmación.
- El tipo desaparece del listado.

**Notas de reglas de negocio:**
- No se puede eliminar un tipo de cliente si tiene clientes asociados (integridad referencial).
- Código de error: 2115.

**Dependencias:** HU-016.

---

## Épica 5: Gestión de Empleados (ABM)

