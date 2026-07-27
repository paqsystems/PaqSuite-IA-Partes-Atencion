# HU-012 – Asignación de tipos de tarea a cliente

## Épica
Épica 3: Gestión de Clientes (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero asignar tipos de tarea específicos a un cliente para que ese cliente solo use esos tipos de tareas, además de los genéricos, al registrar tareas.

**Criterios de aceptación:**
- El supervisor puede acceder a la gestión de tipos de tarea de un cliente (desde la edición o detalle del cliente).
- Se muestra una lista de tipos de tarea NO genéricos disponibles.
- El supervisor puede seleccionar múltiples tipos de tarea para asignar al cliente.
- El supervisor puede desasignar tipos de tarea ya asignados.
- El sistema valida que los tipos de tarea existan y estén activos/no inhabilitados.
- Al guardar, el sistema crea o elimina las asociaciones en la tabla `ClienteTipoTarea`.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan inmediatamente.

**Notas de reglas de negocio:**
- Solo se pueden asignar tipos de tarea NO genéricos (`is_generico = false`).
- Los tipos genéricos están disponibles para todos los clientes automáticamente.
- Esta funcionalidad es necesaria para cumplir la regla: el cliente debe tener al menos un tipo genérico disponible o un tipo asignado.

**Dependencias:** HU-010, HU-020 (tipos de tarea).

---

