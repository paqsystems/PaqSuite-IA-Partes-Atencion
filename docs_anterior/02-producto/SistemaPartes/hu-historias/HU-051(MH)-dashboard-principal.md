# HU-051 – Dashboard principal

## Épica
Épica 10: Dashboard


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario quiero ver un dashboard con resumen ejecutivo del sistema para tener una visión general rápida de mi actividad.

**Criterios de aceptación:**
- El usuario puede acceder al dashboard desde el menú principal o como página de inicio post-login.
- El dashboard muestra información según el rol del usuario:
  - **Empleado (NO supervisor):** Resumen de sus propias tareas (donde `usuario_id` coincide con su `usuario_id`) - total de horas del mes, cantidad de tareas, top clientes.
  - **Supervisor:** Resumen de todas las tareas de todos los empleados - total de horas del mes, cantidad de tareas, top clientes, top empleados.
  - **Cliente:** Resumen de tareas recibidas (donde `cliente_id` coincide con su `cliente_id`) - total de horas del mes, cantidad de tareas, distribución por tipo.
- Se muestra un período por defecto (mes actual o último mes).
- El usuario puede cambiar el período (selector de mes o rango de fechas).
- Los datos se actualizan automáticamente al cambiar el período.
- Se muestran indicadores clave (KPIs):
  - Total de horas del período
  - Cantidad de tareas del período
  - Promedio de horas por día (opcional)
- Se muestran gráficos o visualizaciones básicas (opcional, según diseño):
  - Distribución de horas por cliente (gráfico de barras o pie)
  - Evolución de horas en el tiempo (gráfico de línea, opcional)
- El dashboard es responsive y se adapta a diferentes tamaños de pantalla.

**Notas de reglas de negocio:**
- **Filtros automáticos según rol (obligatorios):**
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas (sin filtro automático).
- El período por defecto es el mes actual.
- Los KPIs se calculan en tiempo real desde la base de datos aplicando los filtros automáticos según rol.

**Dependencias:** HU-001 (autenticación), HU-044 (consultas base).

---

