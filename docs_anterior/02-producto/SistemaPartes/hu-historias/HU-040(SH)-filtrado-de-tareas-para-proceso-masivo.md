# HU-040 – Filtrado de tareas para proceso masivo

## Épica
Épica 8: Proceso Masivo de Tareas


**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero aplicar filtros complejos para seleccionar las tareas que deseo procesar masivamente.

**Criterios de aceptación:**
- El supervisor puede filtrar por rango de fechas (fecha desde, fecha hasta).
- El supervisor puede filtrar por cliente (todos o cliente específico).
- El supervisor puede filtrar por empleado (todos o empleado específico).
- El supervisor puede filtrar por estado (Cerrados / Abiertos).
- El sistema valida que `fecha_desde <= fecha_hasta`.
- Al hacer clic en "Aplicar Filtros", se cargan las tareas que cumplen los criterios.
- Los filtros se mantienen al recargar la página (opcional, según UX).
- Se muestra el total de tareas filtradas.

**Notas de reglas de negocio:**
- Validación de rango de fechas: `fecha_desde <= fecha_hasta` (código de error: 1305).
- Los filtros se aplican en conjunto (AND lógico).

**Dependencias:** HU-039.

---

