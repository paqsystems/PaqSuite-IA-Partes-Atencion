# HU-024 – Creación de tipo de tarea

## Épica
Épica 6: Gestión de Tipos de Tarea (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero crear un nuevo tipo de tarea para clasificar las tareas registradas en el sistema.

**Criterios de aceptación:**
- El supervisor puede acceder al formulario de creación de tipo de tarea.
- El formulario tiene los siguientes campos:
  - Código (obligatorio, único)
  - Descripción (obligatorio)
  - Genérico (checkbox, por defecto: false)
  - Por defecto (checkbox, por defecto: false)
  - Activo (checkbox, por defecto: true)
  - Inhabilitado (checkbox, por defecto: false)
- El sistema valida que el código no esté vacío.
- El sistema valida que el código sea único.
- El sistema valida que la descripción no esté vacía.
- El sistema valida la regla: si `por defecto = true`, entonces `genérico = true` (forzado automáticamente).
- El sistema valida la regla: solo puede haber un tipo de tarea con `por defecto = true` en todo el sistema.
- Si se marca "por defecto" y ya existe otro tipo por defecto, se muestra un error.
- Si se marca "por defecto", el checkbox "genérico" se marca automáticamente y se deshabilita.
- Al guardar, el sistema crea el tipo de tarea en la base de datos.
- Se muestra un mensaje de confirmación.
- El usuario es redirigido al listado de tipos de tarea o puede crear otro.

**Notas de reglas de negocio:**
- `code` es obligatorio y único.
- `descripcion` es obligatorio.
- Regla crítica: solo un tipo puede tener `is_default = true`.
- Si `is_default = true`, entonces `is_generico = true` (forzado).
- Código de error: 2117 (solo puede haber un tipo por defecto).

**Dependencias:** HU-023.

---

