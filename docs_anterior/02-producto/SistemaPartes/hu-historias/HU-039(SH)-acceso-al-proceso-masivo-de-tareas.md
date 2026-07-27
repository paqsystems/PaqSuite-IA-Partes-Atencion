# HU-039 – Acceso al proceso masivo de tareas

## Épica
Épica 8: Proceso Masivo de Tareas


**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero acceder a la funcionalidad de proceso masivo de tareas para gestionar eficientemente el estado de múltiples tareas.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Proceso Masivo" desde el menú principal.
- Solo los usuarios con `supervisor = true` pueden acceder a esta funcionalidad.
- Si un usuario normal intenta acceder, se muestra un error 403 o redirección.
- La página muestra los filtros y la tabla de tareas.
- Se muestra un mensaje claro si el usuario no tiene permisos.

**Notas de reglas de negocio:**
- Validación de permisos: solo supervisores (`supervisor = true`).
- Código de error: 403 si usuario no es supervisor.

**Dependencias:** HU-001 (autenticación).

---

