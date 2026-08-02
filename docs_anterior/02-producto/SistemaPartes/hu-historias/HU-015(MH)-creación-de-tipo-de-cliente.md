# HU-015 – Creación de tipo de cliente

## Épica
Épica 4: Gestión de Tipos de Cliente (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero crear un nuevo tipo de cliente para clasificar los clientes del sistema.

**Criterios de aceptación:**
- El supervisor puede acceder al formulario de creación de tipo de cliente.
- El formulario tiene los siguientes campos:
  - Código (obligatorio, único)
  - Descripción (obligatorio)
  - Activo (checkbox, por defecto: true)
  - Inhabilitado (checkbox, por defecto: false)
- El sistema valida que el código no esté vacío.
- El sistema valida que el código sea único.
- El sistema valida que la descripción no esté vacía.
- Al guardar, el sistema crea el tipo de cliente en la base de datos.
- Se muestra un mensaje de confirmación.
- El usuario es redirigido al listado de tipos de cliente o puede crear otro.

**Notas de reglas de negocio:**
- `code` es obligatorio y único.
- `descripcion` es obligatorio.

**Dependencias:** HU-014.

---

