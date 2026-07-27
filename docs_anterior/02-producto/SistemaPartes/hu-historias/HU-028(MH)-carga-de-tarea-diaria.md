# HU-028 – Carga de tarea diaria

## Épica
Épica 7: Registro de Tareas


**Rol:** Empleado / Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero registrar una tarea realizada indicando fecha, cliente, tipo de tarea, duración y descripción para dejar constancia del trabajo efectuado.

**Criterios de aceptación:**
- El empleado puede acceder al formulario de registro de tarea. El botón "Cargar Tarea" solo es visible para empleados (no para clientes). La ruta está protegida y redirige al dashboard si un cliente intenta acceder.
- El formulario tiene los siguientes campos:
  - Fecha (obligatorio, input de texto con formato DD/MM/YYYY, por defecto: fecha actual en formato DMY, con autoformato de barras)
  - Cliente (obligatorio, selector)
  - Tipo de tarea (obligatorio, selector)
  - Duración (obligatorio, input de texto con formato hh:mm, ej: "02:30" = 150 minutos, con validación de tramos de 15 minutos y autoformato de dos puntos)
  - Sin cargo (checkbox, por defecto: false)
  - Presencial (checkbox, por defecto: false)
  - Observación/Descripción (obligatorio, textarea)
- Si el usuario es supervisor, puede seleccionar el empleado propietario de la tarea (selector, por defecto: él mismo).
- El selector de clientes solo muestra clientes activos y no inhabilitados.
- El selector de tipos de tarea muestra:
  - Todos los tipos genéricos (`is_generico = true`) activos y no inhabilitados
  - Los tipos NO genéricos asignados al cliente seleccionado (desde `ClienteTipoTarea`) activos y no inhabilitados
- El sistema valida que la fecha no esté vacía.
- El sistema valida que la fecha tenga formato DMY válido (DD/MM/YYYY) en el frontend y se convierte a YMD (YYYY-MM-DD) antes de enviar al backend.
- El backend valida que la fecha tenga formato YMD válido (YYYY-MM-DD).
- El sistema muestra una advertencia si la fecha es futura (no bloquea, solo advierte).
- El sistema valida que el cliente esté seleccionado y exista.
- El sistema valida que el cliente esté activo y no inhabilitado.
- El sistema valida que el tipo de tarea esté seleccionado y exista.
- El sistema valida que el tipo de tarea esté activo y no inhabilitado.
- El sistema valida que el tipo de tarea sea genérico o esté asignado al cliente seleccionado.
- El sistema valida que la duración sea mayor a cero.
- El campo acepta formato horario hh:mm (ej: "02:30" = 150 minutos) para facilitar la entrada del usuario.
- El sistema convierte el formato hh:mm a minutos antes de almacenar y enviar al API.
- El sistema valida que la duración esté en tramos de 15 minutos (15, 30, 45, 60, ..., 1440).
- El sistema valida que la duración no exceda 1440 minutos (24 horas = "24:00").
- El sistema valida que la observación no esté vacía.
- El sistema valida que `sin_cargo` y `presencial` no sean null (valores por defecto: false).
- Si el usuario es supervisor y selecciona otro empleado, el sistema valida que el empleado exista y esté activo/no inhabilitado.
- Al guardar, el sistema crea el registro de tarea en la base de datos.
- El registro queda asociado al usuario autenticado (o al seleccionado si es supervisor).
- Se muestra un mensaje de confirmación.
- El formulario se limpia o el usuario es redirigido a la lista de tareas.

**Notas de reglas de negocio:**
- La duración debe ser múltiplo de 15 minutos.
- La duración se ingresa en formato horario hh:mm pero se almacena en minutos.
- La fecha se ingresa en formato DD/MM/YYYY pero se almacena en formato YYYY-MM-DD.
- La fecha futura genera advertencia pero no bloquea.
- `observacion` es obligatorio (no opcional).
- Los selectores solo muestran registros activos y no inhabilitados.
- Regla de visibilidad de tipos de tarea: genéricos + asignados al cliente.
- Solo los empleados pueden acceder al formulario de carga de tarea. Los clientes no pueden ver el botón ni acceder a la ruta.

**Dependencias:** HU-001 (autenticación), HU-009 (clientes), HU-024 (tipos de tarea), HU-012 (asignación de tipos a clientes).

---

