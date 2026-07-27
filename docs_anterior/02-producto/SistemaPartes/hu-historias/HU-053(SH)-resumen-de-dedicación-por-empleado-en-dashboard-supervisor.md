# HU-053 – Resumen de dedicación por empleado en dashboard (supervisor)

## Épica
Épica 10: Dashboard


**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero ver un resumen de dedicación por empleado en el dashboard para identificar rápidamente la carga de trabajo de cada empleado.

**Criterios de aceptación:**
- El dashboard del supervisor muestra una sección "Dedicación por Empleado".
- Se muestra una lista o tabla con los empleados y sus totales de horas del período.
- Se muestran los top N empleados (ej: top 5 o top 10).
- Cada empleado muestra:
  - Nombre del empleado
  - Total de horas en formato decimal
  - Cantidad de tareas
  - Porcentaje del total (opcional)
- Los empleados se ordenan por total de horas (mayor a menor).
- El supervisor puede hacer clic en un empleado para ver el detalle (redirección a consulta por empleado).
- Se muestra un total general de horas.

**Notas de reglas de negocio:**
- Agrupación por `usuario_id`.
- Ordenamiento por dedicación descendente.
- Solo visible para supervisores.

**Dependencias:** HU-051.

---

