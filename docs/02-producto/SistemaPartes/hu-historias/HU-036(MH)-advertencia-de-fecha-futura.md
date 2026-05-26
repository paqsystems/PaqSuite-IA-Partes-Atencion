# HU-036 – Advertencia de fecha futura

## Épica
Épica 7: Registro de Tareas


**Rol:** Empleado / Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero que el sistema me advierta si registro una tarea con fecha futura para evitar errores, pero sin bloquear la acción si es intencional.

**Criterios de aceptación:**
- Al seleccionar una fecha futura en el formulario, el sistema muestra una advertencia visual.
- La advertencia indica: "La fecha seleccionada es futura. ¿Está seguro de que desea continuar?"
- El usuario puede continuar con la acción (no se bloquea).
- La advertencia es clara pero no impide el guardado.
- Opcionalmente, se puede mostrar un checkbox de confirmación "Confirmo que la fecha es correcta".

**Notas de reglas de negocio:**
- La fecha futura genera advertencia pero no bloquea la creación/edición.
- Esta es una validación de advertencia, no de bloqueo.

**Dependencias:** HU-028.

---

