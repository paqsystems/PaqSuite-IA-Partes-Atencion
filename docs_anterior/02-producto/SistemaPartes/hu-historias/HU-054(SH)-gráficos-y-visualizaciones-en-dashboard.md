# HU-054 – Gráficos y visualizaciones en dashboard

## Épica
Épica 10: Dashboard


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario quiero ver gráficos y visualizaciones en el dashboard para entender mejor la distribución de la dedicación.

**Criterios de aceptación:**
- El dashboard muestra gráficos según el rol:
  - **Empleado (NO supervisor):** Gráfico de distribución de horas por cliente (barras o pie) - solo de sus propias tareas (donde `usuario_id` coincide con su `usuario_id`).
  - **Supervisor:** Gráfico de distribución de horas por cliente y por empleado - todas las tareas de todos los empleados.
  - **Cliente:** Gráfico de distribución de horas por tipo de tarea - solo tareas donde es el cliente (donde `cliente_id` coincide con su `cliente_id`).
- Los datos de los gráficos se filtran automáticamente según los permisos del usuario.
- Los gráficos se actualizan al cambiar el período.
- Los gráficos son interactivos (opcional: tooltips, clics para filtrar).
- Los gráficos son responsive y se adaptan al tamaño de pantalla.
- Se usa una librería de gráficos estándar (Chart.js, Recharts, etc.).
- Los colores son consistentes y accesibles.

**Notas de reglas de negocio:**
- Los gráficos se generan a partir de los mismos datos que las consultas (con filtros automáticos según rol).
- **Filtros automáticos según rol (obligatorios):**
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas.
- Los gráficos deben ser accesibles (textos alternativos, contraste).

**Dependencias:** HU-051, HU-052.

---

