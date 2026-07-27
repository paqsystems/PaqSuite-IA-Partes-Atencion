# HU-047 – Consulta agrupada por tipo de tarea

## Épica
Épica 9: Informes y Consultas


**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero consultar tareas agrupadas por tipo de tarea para analizar la distribución del trabajo por tipo.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Tareas por Tipo".
- Se muestran los mismos filtros que en consulta detallada.
- Los resultados se agrupan por tipo de tarea.
- Cada grupo muestra:
  - Descripción del tipo de tarea
  - Total de horas en formato decimal
  - Cantidad de tareas
- Cada grupo es expandible.
- Al expandir un grupo, se muestra el detalle de todas las tareas de ese tipo.
- El detalle muestra las mismas columnas que la consulta detallada.
- Se puede colapsar el grupo.
- Se muestra el total general.

**Notas de reglas de negocio:**
- Agrupación por `tipo_tarea_id`.
- Totalización de horas en formato decimal.

**Dependencias:** HU-044.

---

