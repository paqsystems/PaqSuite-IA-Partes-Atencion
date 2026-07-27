# HU-019 – Creación de empleado

## Épica
Épica 5: Gestión de Empleados (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero crear un nuevo empleado para que pueda acceder al sistema y registrar tareas.

**Criterios de aceptación:**
- El supervisor puede acceder al formulario de creación de empleado.
- El formulario tiene los siguientes campos:
  - Código (obligatorio, único)
  - Nombre (obligatorio)
  - Email (opcional, único si se proporciona)
  - Contraseña (obligatorio)
  - Confirmar contraseña (obligatorio)
  - Supervisor (checkbox, por defecto: false)
  - Activo (checkbox, por defecto: true)
  - Inhabilitado (checkbox, por defecto: false)
- El sistema valida que el código no esté vacío.
- El sistema valida que el código sea único.
- El sistema valida que el código no exista en `USERS`.
- El sistema valida que el nombre no esté vacío.
- El sistema valida que el email tenga formato válido (si se proporciona).
- El sistema valida que el email sea único (si se proporciona).
- El sistema valida que la contraseña no esté vacía.
- El sistema valida que la contraseña y confirmación coincidan.
- El sistema valida la complejidad de la contraseña (si aplica).
- Al guardar, el sistema crea primero un registro en `USERS` con: `code` (del empleado), `password_hash` (de la contraseña proporcionada), `activo` (del empleado), `inhabilitado` (del empleado).
- Al guardar, el sistema crea el empleado en `PQ_PARTES_USUARIOS` con: `user_id` (FK al `USERS` creado), `code` (debe coincidir con `User.code`), y los demás campos.
- El sistema valida que el `code` del empleado coincida con el `code` del `User` creado.
- Se muestra un mensaje de confirmación.
- El usuario es redirigido al listado de empleados o puede crear otro.

**Notas de reglas de negocio:**
- `code` es obligatorio y único.
- `nombre` es obligatorio.
- La creación de un empleado requiere la creación simultánea de un registro en `USERS`.
- El `user_id` en `PQ_PARTES_USUARIOS` es obligatorio y debe referenciar a `USERS.id`.
- El `code` en `PQ_PARTES_USUARIOS` debe coincidir exactamente con el `code` en `USERS`.
- `password_hash` se genera a partir de la contraseña en texto plano y se almacena en `USERS`.

**Dependencias:** HU-018.

---

