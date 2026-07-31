---
specId: SPEC-004
titulo: Carga diaria de tareas
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-07-31
openSpec: docs/05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md
---

# Carga diaria de tareas

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

En **Partes → Carga diaria** registrás el trabajo del día (o de un rango de fechas): cliente, tipo de tarea, duración y observación. El asistente solo opera sus propias tareas; el supervisor puede cargar o revisar las de otros y cerrar/reabrir de a una. El perfil cliente **no** usa esta pantalla.

## Funcionamiento

### Registrar o editar tareas (web)

1. Menú **Partes** → **Carga diaria**.
2. Confirmá el filtro de fechas (por defecto el **día de hoy**; podés ampliar el rango).
3. Actualizá el listado.
4. Dá de alta una fila: fecha, cliente, tipo, duración, observación; marcas opcionales (sin cargo, presencial).
5. Si sos supervisor, elegí el asistente propietario cuando corresponda.
6. Guardá. Editá o eliminá solo si la tarea **no** está cerrada.
7. Si la fecha es futura, el sistema pide **confirmación** (no bloquea del todo).
8. Supervisor: cerrá o reabrí **una** fila con la acción explícita.

## Particularidades

- La **observación es obligatoria**.
- La duración debe ser múltiplo del **tramo** configurado (por defecto **15** minutos), mayor que 0 y hasta **1440** (24 h).
- Los tipos disponibles dependen del cliente: genéricos + asignados a ese cliente.
- Al cambiar de cliente, si el tipo ya no aplica se limpia y debés elegir otro.
- Una tarea **cerrada** queda de solo lectura en el flujo ordinario.
- Sin fechas en el filtro no se lista el histórico completo a ciegas.

### Límites / cupos visibles al usuario

| Límite | Valor habitual |
|--------|----------------|
| Tramo de duración | Parámetro (default 15 min) |
| Duración máxima | 1440 minutos |
| Fechas | Desde y hasta obligatorias para listar |

### Web vs mobile

| Tema | Web | Mobile |
|------|-----|--------|
| Carga | Grilla / formulario de carga diaria | Flujo kardex + una tarea (ver SPEC-007) |
| Cierre individual | Supervisor en la misma pantalla | Según app; masivo no existe en mobile |

## Condiciones de uso

- Perfil asistente o supervisor.
- Menú **Carga diaria** visible.
- El asistente no puede grabar tareas de otro propietario.

## Errores de validación

| Qué ve el usuario (mensaje o síntoma) | Código / clave i18n (si existe) | Causa habitual | Qué hacer |
|---------------------------------------|--------------------------------|----------------|-----------|
| Debe indicar fecha desde y fecha hasta | `partes.tarea.fechasRequeridas` | Filtro incompleto | Completar ambas fechas |
| Duración inválida (tramo / 0 / >1440) | `partes.tarea.duracionInvalida` | Minutos no múltiplo del tramo | Usar 15, 30, 45… (según tramo) |
| Observación obligatoria | `partes.tarea.observacionRequerida` | Texto vacío | Completar observación |
| Complete los campos obligatorios | `partes.tarea.camposObligatorios` | Faltan datos | Completar el formulario |

## Errores de lógica

| Qué ve el usuario | Código / clave | Regla de negocio | Qué hacer |
|-------------------|----------------|------------------|-----------|
| Confirme para registrar una fecha futura | `partes.tarea.fechaFuturaConfirmacion` | Fecha > hoy | Confirmar o cambiar la fecha |
| Tipo no pertenece al universo del cliente | `partes.tarea.tipoFueraUniverso` | Tipo no asignado / no genérico | Elegir otro tipo |
| Tipo / cliente / asistente no usable | `partes.tarea.tipoNoUsable` / `clienteNoUsable` / `asistenteNoUsable` | Inhabilitado o inactivo | Elegir otro registro usable |
| No se puede editar/eliminar una tarea cerrada | `partes.tarea.cerradaNoEditable` / `cerradaNoEliminable` | Estado cerrado | Pedir reapertura (supervisor) o proceso masivo |
| Solo un supervisor puede cerrar o reabrir | `partes.tarea.soloSupervisor` | Rol insuficiente | Usar usuario supervisor |
| No puede operar sobre tareas de otro asistente | `partes.tarea.forbiddenOwner` | No sos el dueño ni supervisor | Cargar solo las propias |
| Sin permiso para operar carga | `partes.tarea.forbidden` | Perfil cliente u otro | Usar el perfil correcto |
| Fue modificada por otro usuario | `partes.tarea.conflictoVersion` | Cambio concurrente | Refrescar e intentar de nuevo |
| Tarea no encontrada | `partes.tarea.notFound` | Ya no existe | Refrescar el listado |

## Errores técnicos posibles

| Qué ve el usuario | Código / HTTP (si aplica) | Causa posible | Qué hacer / a quién escalar |
|-------------------|---------------------------|---------------|------------------------------|
| Error de conexión | `infra.transport` | Red o servidor | Reintentar; soporte |
| Error inesperado | `infra.unexpected` | Fallo interno | Reportar a soporte |

## Preguntas frecuentes

### ¿Puedo cargar 10 minutos?

Solo si el tramo configurado lo permite. Con tramo 15, las duraciones válidas son 15, 30, 45, …

### ¿El cliente puede cargar partes?

No.

### ¿Fecha de mañana?

Sí, con confirmación explícita.
