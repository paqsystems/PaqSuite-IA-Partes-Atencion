# HU-025 – Edición de tipo de tarea

## Épica
Épica 6: Gestión de Tipos de Tarea (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero editar la información de un tipo de tarea existente para mantener actualizados los datos del catálogo.

**Criterios de aceptación:**
- El supervisor puede acceder a la edición de un tipo de tarea desde el listado.
- Se carga el formulario con los datos actuales.
- El código del tipo de tarea no es modificable (solo lectura).
- El supervisor puede modificar: descripción, genérico, por defecto, estado activo, estado inhabilitado.
- El sistema valida que la descripción no esté vacía.
- El sistema valida la regla: si `por defecto = true`, entonces `genérico = true` (forzado automáticamente).
- El sistema valida la regla: solo puede haber un tipo de tarea con `por defecto = true` (verificar que no haya otro distinto al actual).
- Si se marca "por defecto" y ya existe otro tipo por defecto (distinto al actual), se muestra un error.
- Si se marca "por defecto", el checkbox "genérico" se marca automáticamente y se deshabilita.
- Si el tipo actual es "por defecto" y se desmarca, se permite (pero debe quedar al menos un tipo genérico disponible).
- Al guardar, el sistema actualiza el tipo de tarea en la base de datos.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan en el listado.

**Notas de reglas de negocio:**
- El `code` no debe ser modificable.
- Las mismas validaciones que en creación aplican.

**Dependencias:** HU-024.

---

