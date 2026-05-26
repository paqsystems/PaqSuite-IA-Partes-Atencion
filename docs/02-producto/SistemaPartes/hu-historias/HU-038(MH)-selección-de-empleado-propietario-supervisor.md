# HU-038 – Selección de empleado propietario (supervisor)

## Épica
Épica 7: Registro de Tareas


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero seleccionar el empleado propietario de una tarea al crearla para poder registrar tareas en nombre de otros empleados.

**Criterios de aceptación:**
- En el formulario de creación de tarea, si el usuario es supervisor, aparece un selector de "Empleado".
- El selector muestra todos los empleados activos y no inhabilitados.
- Por defecto, el selector muestra al supervisor mismo.
- El supervisor puede cambiar la selección a otro empleado.
- El sistema valida que el empleado seleccionado exista y esté activo/no inhabilitado.
- Al guardar, la tarea queda asociada al empleado seleccionado (no al supervisor).
- En la lista de tareas, se muestra el empleado propietario de cada tarea.

**Notas de reglas de negocio:**
- Solo los supervisores pueden seleccionar otro empleado.
- Los empleados normales siempre registran tareas como propias.

**Dependencias:** HU-028, HU-019 (gestión de usuarios).

---

## Épica 8: Proceso Masivo de Tareas

