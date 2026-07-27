# HU-046 – Consulta agrupada por cliente

## Épica
Épica 9: Informes y Consultas


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario quiero consultar tareas agrupadas por cliente para analizar la dedicación a cada cliente.

**Criterios de aceptación:**
- El usuario puede acceder a la sección "Tareas por Cliente" o "Resumen por Cliente".
- Se muestran filtros de período (fecha desde, fecha hasta).
- Los resultados se agrupan por cliente.
- Cada grupo muestra:
  - Nombre del cliente
  - Tipo de cliente (opcional)
  - Total de horas en formato decimal
  - Cantidad de tareas
- Cada grupo es expandible (accordion o similar).
- Al expandir un grupo, se muestra el detalle de todas las tareas de ese cliente.
- El detalle muestra: fecha, tipo de tarea, horas, empleado (si supervisor), descripción.
- Se puede colapsar el grupo para ocultar el detalle.
- Se muestra el total general de horas y tareas.
- Los grupos se ordenan por total de horas (mayor a menor).

**Notas de reglas de negocio:**
- Agrupación por `cliente_id`.
- Totalización de horas en formato decimal.
- Ordenamiento por dedicación total descendente.
- **Filtros automáticos según rol:**
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id` (filtro automático).
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id` (filtro automático).
  - **Supervisor:** Todas las tareas (sin filtro automático).

**Dependencias:** HU-044.

---

