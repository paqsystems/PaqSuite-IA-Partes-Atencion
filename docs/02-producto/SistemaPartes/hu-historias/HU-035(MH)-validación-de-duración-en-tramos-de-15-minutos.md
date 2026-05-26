# HU-035 – Validación de duración en tramos de 15 minutos

## Épica
Épica 7: Registro de Tareas


**Rol:** Empleado / Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero que el sistema valide que la duración de las tareas esté en tramos de 15 minutos para mantener la consistencia en el registro de tiempo.

**Criterios de aceptación:**
- Al ingresar la duración en el formulario, el sistema valida que sea múltiplo de 15.
- Si se ingresa un valor que no es múltiplo de 15, se muestra un mensaje de error claro.
- El mensaje indica: "La duración debe estar en tramos de 15 minutos (15, 30, 45, 60, ...)".
- El sistema no permite guardar la tarea si la duración no es válida.
- El campo de duración puede tener un selector con valores predefinidos (15, 30, 45, 60, 75, 90, ..., 1440) o validar el input manual.
- Si es input manual, se puede redondear automáticamente al tramo más cercano (opcional, según diseño UX).

**Notas de reglas de negocio:**
- Duración válida: `duracion_minutos % 15 === 0` y `0 < duracion_minutos <= 1440`.
- Código de error: 1210.

**Dependencias:** HU-028.

---

