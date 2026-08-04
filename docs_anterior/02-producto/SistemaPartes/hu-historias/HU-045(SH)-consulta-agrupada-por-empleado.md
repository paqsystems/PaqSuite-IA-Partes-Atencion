# HU-045 – Consulta agrupada por empleado

## Épica
Épica 9: Informes y Consultas


**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero consultar tareas agrupadas por empleado para analizar la dedicación de cada empleado.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Tareas por Empleado".
- Se muestran los mismos filtros que en consulta detallada.
- Los resultados se agrupan por empleado.
- Cada grupo muestra:
  - Nombre del empleado
  - Total de horas en formato decimal
  - Cantidad de tareas
- Cada grupo es expandible (accordion o similar).
- Al expandir un grupo, se muestra el detalle de todas las tareas de ese empleado.
- El detalle muestra las mismas columnas que la consulta detallada.
- Se puede colapsar el grupo para ocultar el detalle.
- Se muestra el total general de horas y tareas.

**Notas de reglas de negocio:**
- Agrupación por `usuario_id`.
- Totalización de horas en formato decimal.
- Los filtros aplican a todas las tareas antes de agrupar.

**Dependencias:** HU-044.

---

