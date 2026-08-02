# HU-020 – Edición de empleado

## Épica
Épica 5: Gestión de Empleados (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero editar la información de un empleado existente para mantener actualizados sus datos.

**Criterios de aceptación:**
- El supervisor puede acceder a la edición de un empleado desde el listado.
- Se carga el formulario con los datos actuales.
- El código del empleado no es modificable (solo lectura).
- El supervisor puede modificar: nombre, email, supervisor, estado activo, estado inhabilitado.
- El supervisor puede cambiar la contraseña (opcional, con campos separados).
- El sistema valida que el nombre no esté vacío.
- El sistema valida que el email tenga formato válido (si se proporciona).
- El sistema valida que el email sea único (si se proporciona y cambió).
- Si se cambia la contraseña, se validan las mismas reglas que en creación.
- Si se cambia la contraseña, el sistema actualiza el `password_hash` en `USERS` (no en `PQ_PARTES_USUARIOS`).
- Si se cambia el estado `activo` o `inhabilitado`, el sistema actualiza ambos: `USERS` y `PQ_PARTES_USUARIOS`.
- El sistema valida que el `code` no se pueda modificar (es identificador único y debe coincidir con `User.code`).
- Al guardar, el sistema actualiza el usuario en la base de datos.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan en el listado.

**Notas de reglas de negocio:**
- El `code` no debe ser modificable.
- La contraseña se almacena en `USERS`, no en `PQ_PARTES_USUARIOS`.
- Los cambios de estado deben sincronizarse entre `USERS` y `PQ_PARTES_USUARIOS`.
- La contraseña solo se actualiza si se proporciona una nueva.

**Dependencias:** HU-019.

---

