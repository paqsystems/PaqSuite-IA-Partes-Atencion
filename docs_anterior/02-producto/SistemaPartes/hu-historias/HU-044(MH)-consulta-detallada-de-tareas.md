# HU-044 – Consulta detallada de tareas

## Épica
Épica 9: Informes y Consultas


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario quiero consultar un listado detallado de tareas con filtros para analizar el trabajo realizado.

**Criterios de aceptación:**
- El usuario puede acceder a la sección "Consulta Detallada" o "Detalle de Tareas".
- Se muestra una tabla con todas las tareas según los permisos del usuario:
  - **Empleado (NO supervisor):** Solo sus propias tareas (donde `usuario_id` coincide con su `usuario_id`).
  - **Supervisor:** Todas las tareas de todos los usuarios.
  - **Cliente:** Solo tareas donde es el cliente (donde `cliente_id` coincide con su `cliente_id`).
- La tabla muestra: empleado (si supervisor), cliente, fecha, tipo de tarea, horas (decimal), sin cargo, presencial, descripción.
- El usuario puede aplicar filtros:
  - Período (fecha desde, fecha hasta)
  - Tipo de cliente (todos o específico, solo para supervisor)
  - Cliente (todos o específico, filtrado automático para cliente)
  - Empleado (todos o específico, solo para supervisor, filtrado automático para empleado normal)
- Los filtros se aplican con botón "Aplicar Filtros".
- La tabla se actualiza con los resultados filtrados.
- Se muestra el total de horas del período filtrado.
- Se puede ordenar por columnas (fecha, cliente, empleado, etc.).
- Se puede paginar si hay muchos resultados.

**Notas de reglas de negocio:**
- Filtros automáticos según tipo de usuario (ver reglas de negocio 8.2).
- Validación de período: `fecha_desde <= fecha_hasta` (1305).
- Formato de horas: decimal (minutos / 60).

**Dependencias:** HU-001 (autenticación), HU-033, HU-034.

---

