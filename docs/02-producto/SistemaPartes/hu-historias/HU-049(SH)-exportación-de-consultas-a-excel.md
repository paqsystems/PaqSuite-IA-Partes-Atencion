# HU-049 – Exportación de consultas a Excel

## Épica
Épica 9: Informes y Consultas


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario quiero exportar los resultados de las consultas a Excel para analizar los datos fuera del sistema o compartirlos.

**Criterios de aceptación:**
- El usuario puede hacer clic en un botón "Exportar a Excel" en cualquier consulta.
- El botón está habilitado solo si hay resultados para exportar.
- Si no hay resultados, se muestra un mensaje: "No hay datos para exportar" y el botón está deshabilitado.
- Los datos exportados respetan los permisos del usuario:
  - **Empleado (NO supervisor):** Solo sus propias tareas (donde `usuario_id` coincide con su `usuario_id`).
  - **Supervisor:** Todas las tareas de todos los usuarios.
  - **Cliente:** Solo tareas donde es el cliente (donde `cliente_id` coincide con su `cliente_id`).
- Al hacer clic, se genera un archivo Excel con los datos de la consulta (filtrados según permisos).
- El archivo se descarga automáticamente.
- El nombre del archivo es descriptivo (ej: "Tareas_2025-01-01_2025-01-31.xlsx").
- El archivo contiene:
  - Para consulta detallada: todas las columnas de la tabla
  - Para consulta agrupada: estructura de agrupación con totales y detalles expandidos
- Las horas se muestran en formato decimal en el Excel.
- Las fechas se formatean correctamente en el Excel.
- El formato del archivo es compatible con Excel (XLSX).

**Notas de reglas de negocio:**
- El botón debe estar deshabilitado si `resultados.length === 0`.
- El formato de horas debe ser decimal en el Excel.
- El nombre del archivo debe incluir información del período o filtros aplicados.
- **Los datos exportados deben respetar los mismos filtros automáticos que las consultas en pantalla.**

**Dependencias:** HU-044, HU-045, HU-046, HU-047, HU-048.

---

