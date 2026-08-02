# HU-043 – Validación de selección para procesamiento

## Épica
Épica 8: Proceso Masivo de Tareas


**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero que el sistema valide que haya tareas seleccionadas antes de procesar para evitar errores.

**Criterios de aceptación:**
- Si no hay tareas seleccionadas y el supervisor intenta procesar, se muestra un mensaje: "Debe seleccionar al menos una tarea".
- El botón "Procesar" está visualmente deshabilitado cuando no hay selección.
- El mensaje de error es claro y visible.
- El sistema no realiza ninguna operación si no hay selección.

**Notas de reglas de negocio:**
- Validación en frontend (UX) y backend (seguridad).
- El botón debe estar deshabilitado cuando `selectedTasks.length === 0`.

**Dependencias:** HU-042.

---

## Épica 9: Informes y Consultas

