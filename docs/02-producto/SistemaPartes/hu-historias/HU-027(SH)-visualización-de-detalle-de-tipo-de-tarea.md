# HU-027 – Visualización de detalle de tipo de tarea

## Épica
Épica 6: Gestión de Tipos de Tarea (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero ver el detalle completo de un tipo de tarea incluyendo clientes asociados y estadísticas básicas.

**Criterios de aceptación:**
- El supervisor puede acceder al detalle de un tipo de tarea desde el listado.
- Se muestra toda la información del tipo: código, descripción, genérico, por defecto, estado.
- Si el tipo NO es genérico, se muestra la lista de clientes asociados (desde `ClienteTipoTarea`).
- Se muestra la cantidad total de tareas registradas con este tipo (opcional).
- Se muestra la fecha de creación y última actualización (opcional).
- El supervisor puede editar el tipo desde el detalle.
- El supervisor puede eliminar el tipo desde el detalle (si no tiene referencias).

**Notas de reglas de negocio:**
- Mostrar información completa y contextual del tipo de tarea.

**Dependencias:** HU-025.

---

## Épica 7: Registro de Tareas

