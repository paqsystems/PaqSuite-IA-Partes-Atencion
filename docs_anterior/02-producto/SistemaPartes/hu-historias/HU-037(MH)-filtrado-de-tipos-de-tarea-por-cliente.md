# HU-037 – Filtrado de tipos de tarea por cliente

## Épica
Épica 7: Registro de Tareas


**Rol:** Empleado / Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero que el selector de tipos de tarea muestre solo los tipos disponibles para el cliente seleccionado para evitar selecciones incorrectas.

**Criterios de aceptación:**
- Al seleccionar un cliente en el formulario, el selector de tipos de tarea se actualiza dinámicamente.
- El selector muestra:
  - Todos los tipos genéricos (`is_generico = true`) activos y no inhabilitados
  - Los tipos NO genéricos asignados al cliente seleccionado (desde `ClienteTipoTarea`) activos y no inhabilitados
- Si no hay tipos disponibles para el cliente seleccionado, se muestra un mensaje informativo.
- Si se cambia el cliente, el tipo de tarea seleccionado se limpia si ya no está disponible para el nuevo cliente.
- El sistema valida al guardar que el tipo de tarea seleccionado sea válido para el cliente.

**Notas de reglas de negocio:**
- Regla de visibilidad: genéricos + asignados al cliente.
- Esta funcionalidad es crítica para cumplir la regla de negocio de tipos de tarea.

**Dependencias:** HU-028, HU-012 (asignación de tipos a clientes).

---

