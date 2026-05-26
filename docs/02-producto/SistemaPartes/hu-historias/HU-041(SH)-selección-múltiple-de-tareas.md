# HU-041 – Selección múltiple de tareas

## Épica
Épica 8: Proceso Masivo de Tareas


**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero seleccionar múltiples tareas de la lista para procesarlas en conjunto.

**Criterios de aceptación:**
- Cada fila de la tabla tiene un checkbox para selección.
- El supervisor puede seleccionar tareas individuales haciendo clic en los checkboxes.
- El supervisor puede usar "Seleccionar todos" para marcar todas las tareas visibles.
- El supervisor puede usar "Deseleccionar todos" para desmarcar todas las tareas.
- Se muestra un contador de tareas seleccionadas (ej: "5 tareas seleccionadas").
- Los checkboxes se actualizan correctamente al seleccionar/deseleccionar.
- Las tareas cerradas pueden seleccionarse (pero el procesamiento puede tener reglas específicas).

**Notas de reglas de negocio:**
- La selección es independiente del estado de la tarea (cerrado/abierto).
- El contador debe actualizarse en tiempo real.

**Dependencias:** HU-040.

---

