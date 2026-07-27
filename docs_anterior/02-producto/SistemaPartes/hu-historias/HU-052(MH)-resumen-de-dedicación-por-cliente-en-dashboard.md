# HU-052 – Resumen de dedicación por cliente en dashboard

## Épica
Épica 10: Dashboard


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario quiero ver un resumen de dedicación por cliente en el dashboard para identificar rápidamente los clientes con mayor dedicación.

**Criterios de aceptación:**
- El dashboard muestra una sección "Dedicación por Cliente".
- Los datos se filtran automáticamente según los permisos del usuario:
  - **Empleado (NO supervisor):** Solo sus propias tareas (donde `usuario_id` coincide con su `usuario_id`).
  - **Supervisor:** Todas las tareas de todos los usuarios.
  - **Cliente:** Solo tareas donde es el cliente (donde `cliente_id` coincide con su `cliente_id`).
- Se muestra una lista o tabla con los clientes y sus totales de horas del período (según permisos).
- Se muestran los top N clientes (ej: top 5 o top 10, según diseño).
- Cada cliente muestra:
  - Nombre del cliente
  - Total de horas en formato decimal
  - Cantidad de tareas
  - Porcentaje del total (opcional)
- Los clientes se ordenan por total de horas (mayor a menor).
- El usuario puede hacer clic en un cliente para ver el detalle (redirección a consulta por cliente).
- Se muestra un total general de horas (calculado según permisos del usuario).

**Notas de reglas de negocio:**
- Agrupación por `cliente_id`.
- Ordenamiento por dedicación descendente.
- Límite de top N para mantener el dashboard simple.
- **Filtros automáticos según rol (obligatorios):**
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas.

**Dependencias:** HU-051.

---

