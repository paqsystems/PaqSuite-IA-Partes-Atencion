# HU-016 – Edición de tipo de cliente

## Épica
Épica 4: Gestión de Tipos de Cliente (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero editar la información de un tipo de cliente existente para mantener actualizados los datos del catálogo.

**Criterios de aceptación:**
- El supervisor puede acceder a la edición de un tipo de cliente desde el listado.
- Se carga el formulario con los datos actuales.
- El código del tipo de cliente no es modificable (solo lectura).
- El supervisor puede modificar: descripción, estado activo, estado inhabilitado.
- El sistema valida que la descripción no esté vacía.
- Al guardar, el sistema actualiza el tipo de cliente en la base de datos.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan en el listado.

**Notas de reglas de negocio:**
- El `code` no debe ser modificable.

**Dependencias:** HU-015.

---

