---
specId: SPEC-004
titulo: Carga diaria de tareas
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-08-01
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
4. Dá de alta una fila: fecha, cliente, tipo, duración en **hh:mm**, observación; marcas opcionales (sin cargo, presencial).
5. Si sos supervisor, elegí el asistente propietario cuando corresponda.
6. Guardá. Editá o eliminá solo si la tarea **no** está cerrada.
7. Si la fecha es futura, el sistema pide **confirmación** (no bloquea del todo).
8. Supervisor: cerrá o reabrí **una** fila con la acción explícita.

En la grilla ves el **nombre del cliente** y la **descripción del tipo de tarea** (no el código como valor principal). Las columnas **Sin cargo** y **Presencial** están disponibles. La duración se muestra en **hh:mm** y podés **sumar** el total en horas (decimal) desde el pie / menú de la grilla.

## Particularidades

- La **observación es obligatoria**.
- La duración se elige en formato **hh:mm** (tramos según parámetro; por defecto cada **15** minutos), mayor que 0 y hasta **24:00** (1440 minutos).
- Los tipos disponibles dependen del cliente: genéricos + asignados a ese cliente.
- Al cambiar de cliente, si el tipo ya no aplica se limpia y debés elegir otro.
- Una tarea **cerrada** queda de solo lectura en el flujo ordinario.
- Sin fechas en el filtro no se lista el histórico completo a ciegas.
- Esta pantalla solo muestra y registra **tareas** (no incluye compras de horas del Paquete de Horas, que se consultan en Informes).

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

### Importar desde Excel (web)

En la misma pantalla de **Carga diaria**, debajo de los filtros, podés:

1. **Descargar plantilla** — archivo Excel con las columnas esperadas.
2. **Importar** — subir el `.xlsx`, revisar errores por fila y pulsar **Procesar** para grabar solo las filas válidas.

Reglas útiles:

- Cada fila grabada es una **tarea** nueva (abierta).
- Si no sos supervisor, las tareas quedan a tu nombre (aunque el archivo traiga otra columna de asistente, esa fila falla).
- Si sos supervisor, la columna **asistente** es obligatoria por fila.
- Tras procesar con altas, la grilla se actualiza **sin perder** los filtros que tenías.
- En **mobile** esta importación no está disponible.

La capacidad puede estar deshabilitada en la instalación (parámetro de importación Excel); en ese caso no verás la barra de plantilla/importar.

### Captura inteligente (Smart Capture) en el formulario

Al dar de alta o editar una tarea (modal), debajo del formulario podés usar la **captura inteligente**:

1. Escribí, dictá o adjuntá una imagen (si tu proveedor LLM lo permite) describiendo la tarea.
2. El asistente **propone** valores en el formulario (cliente, tipo, duración, etc.).
3. Revisá y, si corresponde, pedí **guardar**; se usan las mismas validaciones que el botón Guardar.

No confundir con el **Asistente IA** del menú del avatar (ese responde preguntas de ayuda; no completa el parte). En **mobile** la captura inteligente no está disponible. Si la tarea está **cerrada**, el panel queda deshabilitado. Sin credencial LLM configurada en Preferencias, el panel te pedirá configurarla.

## Condiciones de uso

- Perfil asistente o supervisor.
- Menú **Carga diaria** visible.
- El asistente no puede grabar tareas de otro propietario.

## Errores de validación

| Qué ve el usuario (mensaje o síntoma) | Código / clave i18n (si existe) | Causa habitual | Qué hacer |
|---------------------------------------|--------------------------------|----------------|-----------|
| Debe indicar fecha desde y fecha hasta | `partes.tarea.fechasRequeridas` | Filtro incompleto | Completar ambas fechas |
| Duración inválida (tramo / 0 / >1440) | `partes.tarea.duracionInvalida` | Valor no múltiplo del tramo | Elegir un tramo válido en hh:mm (p. ej. 00:15, 00:30…) |
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
