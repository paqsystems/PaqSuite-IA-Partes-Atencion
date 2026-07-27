# HU-018 – Listado de empleados

## Épica
Épica 5: Gestión de Empleados (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero ver el listado de todos los empleados para gestionarlos.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Empleados".
- Se muestra una tabla con todos los usuarios/empleados.
- La tabla muestra: código, nombre, email, supervisor (sí/no), estado (activo/inactivo), inhabilitado (sí/no).
- Los usuarios se listan paginados (si hay muchos).
- Se puede buscar usuarios por código, nombre o email.
- Se puede filtrar por rol supervisor (sí/no).
- Se puede filtrar por estado (activo/inactivo).
- Se puede filtrar por inhabilitado (sí/no).
- Se muestra el total de usuarios.
- Los usuarios inhabilitados se muestran claramente diferenciados.

**Notas de reglas de negocio:**
- Solo los supervisores pueden acceder a esta funcionalidad.
- Se deben listar todos los usuarios, independientemente de su estado.

**Dependencias:** HU-001.

---

