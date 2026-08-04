# HU-042 – Procesamiento masivo de tareas (cerrar/reabrir)

## Épica
Épica 8: Proceso Masivo de Tareas


**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero procesar masivamente las tareas seleccionadas para cambiar su estado (cerrar o reabrir) de forma eficiente.

**Criterios de aceptación:**
- El botón "Procesar" está deshabilitado si no hay tareas seleccionadas.
- El botón "Procesar" se habilita cuando hay al menos una tarea seleccionada.
- Al hacer clic en "Procesar", el sistema invierte el estado de las tareas seleccionadas:
  - Si la tarea está cerrada (`cerrado = true`), se cambia a abierta (`cerrado = false`).
  - Si la tarea está abierta (`cerrado = false`), se cambia a cerrada (`cerrado = true`).
- Se muestra un diálogo de confirmación antes de procesar (opcional, según UX).
- El diálogo muestra la cantidad de tareas a procesar.
- Durante el procesamiento, se muestra un indicador de carga.
- El sistema procesa todas las tareas seleccionadas en una sola operación.
- Al finalizar, se muestra un mensaje de éxito: "Se procesaron X registros".
- La lista se actualiza automáticamente con los nuevos estados.
- Si hay un error, se muestra un mensaje de error y las tareas mantienen su estado anterior.

**Notas de reglas de negocio:**
- El procesamiento invierte el estado `cerrado` de las tareas.
- El botón debe validar que haya al menos una tarea seleccionada.
- El procesamiento debe ser atómico (todas o ninguna).

**Dependencias:** HU-041.

---

