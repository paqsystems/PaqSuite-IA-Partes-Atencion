# HU-048 – Consulta agrupada por fecha

## Épica
Épica 9: Informes y Consultas


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario quiero consultar tareas agrupadas por fecha para analizar la distribución del trabajo en el tiempo.

**Criterios de aceptación:**
- El usuario puede acceder a la sección "Tareas por Fecha".
- Se muestran filtros de período (fecha desde, fecha hasta).
- Los resultados se filtran automáticamente según los permisos del usuario:
  - **Empleado (NO supervisor):** Solo sus propias tareas (donde `usuario_id` coincide con su `usuario_id`).
  - **Supervisor:** Todas las tareas de todos los usuarios.
  - **Cliente:** Solo tareas donde es el cliente (donde `cliente_id` coincide con su `cliente_id`).
- Los resultados se agrupan por fecha.
- Cada grupo muestra:
  - Fecha (formato legible)
  - Total de horas en formato decimal
  - Cantidad de tareas
- Cada grupo es expandible.
- Al expandir un grupo, se muestra el detalle de todas las tareas de esa fecha (según permisos del usuario).
- El detalle muestra las mismas columnas que la consulta detallada.
- Se puede colapsar el grupo.
- Se muestra el total general.
- Las fechas se ordenan cronológicamente (más reciente primero o más antigua primero, según diseño).

**Notas de reglas de negocio:**
- Agrupación por `fecha`.
- Totalización de horas en formato decimal.
- Ordenamiento cronológico.
- **Filtros automáticos según rol:**
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas.

**Dependencias:** HU-044.

---

