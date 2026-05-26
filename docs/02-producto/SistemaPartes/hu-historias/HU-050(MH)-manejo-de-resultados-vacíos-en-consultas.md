# HU-050 – Manejo de resultados vacíos en consultas

## Épica
Épica 9: Informes y Consultas


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario quiero recibir un mensaje claro cuando no hay resultados para los filtros aplicados para entender que la consulta funcionó pero no hay datos.

**Criterios de aceptación:**
- Si una consulta no devuelve resultados (después de aplicar filtros automáticos según rol), se muestra un mensaje informativo: "No se encontraron tareas para los filtros seleccionados".
- No se muestra una tabla vacía.
- El botón de exportar a Excel está deshabilitado.
- El mensaje es claro y sugiere ajustar los filtros.
- El mensaje se muestra en lugar de la tabla de resultados.
- Los filtros automáticos según rol se aplican antes de verificar si hay resultados:
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas.

**Notas de reglas de negocio:**
- Validación en backend: si `resultados.isEmpty()` después de aplicar filtros automáticos, retornar mensaje informativo.
- No mostrar lista vacía ni habilitar exportación.
- Los filtros automáticos según rol son obligatorios y se aplican siempre.

**Dependencias:** HU-044.

---

## Épica 10: Dashboard

