---
specId: SPEC-005
titulo: Supervisión y proceso masivo
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-11-09
procesoErrores: P06
openSpec: docs/05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md
---

# Supervisión y proceso masivo

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

El **proceso masivo** permite a un **supervisor** localizar tareas (por periodo y filtros opcionales), seleccionarlas y aplicar cambios en lote: **cerrar/reabrir**, o actualizar atributos permitidos (**tipo de tarea**, **sin cargo**; y en evoluciones cercanas presencial, asistente o fecha). También puede **informar novedades por correo** a los clientes del periodo filtrado. Asistentes y clientes no usan esta pantalla. El cierre individual sigue disponible en carga diaria (SPEC-004).

## Funcionamiento

### Localizar y seleccionar

1. Menú **Partes** → **Proceso masivo** (un atajo desde carga **no** arrastra los filtros previos).
2. Aplicá filtros: fechas obligatorias; cliente, asistente y estado opcionales.
3. Revisá el listado en la grilla (podés filtrar por columna, elegir qué columnas ver, usar plantillas y exportar a Excel). Clic en el **título de una columna** (por ejemplo Cliente) ordena **todo** el resultado filtrado, no solo la página que estás viendo. El **embudo** del encabezado lista los valores distintos de esa columna para elegir uno o varios. Si el filtro devuelve más filas que las que entran en una hoja, usá el **paginador** al pie para pasar de página; el **total filtro** indica cuántos registros hay en el universo filtrado.
4. Seleccioná filas, o usá la opción de seleccionar todos los del resultado (si hay varias páginas, confirmá que afectará a N partes).

### Cerrar o reabrir en lote

1. Con selección hecha, elegí **Cerrar** o **Reabrir**.
2. Confirmá la acción (cantidad y resumen).
3. El resultado es **todas o ninguna**: si algo falla, no queda un lote a medias.
4. El listado se actualiza; si hubo conflicto, refrescá y volvé a armar la selección.

### Actualizar atributos en lote

1. Con selección hecha, indicá el o los atributos a cambiar:
   - prioridad: **tipo de tarea** y/o **sin cargo**;
   - también: **presencial**, **asistente** y/o **fecha**.
2. Confirmá valor(es) y cantidad.
3. Se aplica el mismo valor a todas las filas seleccionadas (también si están cerradas).
4. Si el tipo de tarea no es válido para el cliente de alguna fila, **no se cambia ninguna**.

No se puede cambiar en lote: **cliente**, **duración** ni **descripción**.

### Informar novedades a clientes

1. Con el **filtro de periodo** aplicado (y los filtros opcionales que uses), pulsá el ícono de correo **Informar a clientes**. No hace falta tildar filas: se avisa a todos los clientes del resultado filtrado que tienen usuario y correo.
2. Se abre una confirmación con la **cantidad de avisos** y el **periodo** (día/mes/año). Cada destinatario aparece en una **tarjeta** con nombre y correo.
3. Si hay más de **cinco** destinatarios, recorré el resto con **scroll** dentro de la lista. El título y los botones **Cancelar** / **Enviar correos** siguen visibles.
4. Revisá el listado y confirmá **Enviar correos**. El mail incluye el acceso al portal y el código de usuario; **nunca** la contraseña.
5. Si salís del proceso con cambios pendientes, el sistema pregunta si querés enviar los correos, salir sin enviar o cancelar.

### Supervisión puntual en carga diaria

1. En carga diaria, filtrá por asistente u otros criterios.
2. Editá tareas no cerradas; cerrá o reabrí individualmente si sos supervisor.

## Particularidades

- La grilla del masivo incluye filtro bajo títulos, embudo de valores únicos, orden por columna sobre todo el filtro, totales, selector de columnas, **plantillas** (Original / Guardar / Guardar como / Eliminar) y exportación a Excel.
- Exportar a Excel **no** es importar desde Excel (eso no forma parte de este proceso).
- Acciones ya aplicadas (cerrar lo cerrado, o el mismo valor de atributo) son inocuas / idempotentes.
- Si se supera el tope configurado o el límite técnico (~5000), debés refinar el filtro.
- El proceso masivo solo localiza y opera sobre **tareas** (no incluye compras de horas del Paquete de Horas).
- **No disponible en la app móvil.**
- **Informar a clientes** usa el universo **filtrado** (todas las páginas). La lista de confirmación muestra **todos** los destinatarios; como máximo se ven **cinco tarjetas** a la vez y el resto se recorre con scroll.

### Límites / cupos visibles al usuario

| Límite | Valor |
|--------|--------|
| Tope de negocio | Parámetro (puede estar sin tope) |
| Límite técnico de lote | Hasta ~5000 registros |

### Web vs mobile

| Tema | Web | Mobile |
|------|-----|--------|
| Proceso masivo | Sí | No |

## Condiciones de uso

- Debés ser **supervisor**.
- Menú **Proceso masivo** visible.
- Selección no vacía y fechas de filtro completas.

## Errores de validación

| Qué ve el usuario (mensaje o síntoma) | Código / clave i18n (si existe) | Causa habitual | Qué hacer |
|---------------------------------------|--------------------------------|----------------|-----------|
| Debe indicar fecha desde y fecha hasta | `partes.tarea.fechasRequeridas` | Filtro incompleto | Completar fechas |
| Seleccione al menos una tarea | `partes.masivo.emptySelection` | Sin selección | Marcar filas o “todos” |
| Acción de proceso masivo no válida | `partes.masivo.accionInvalida` | Acción incorrecta | Elegir una acción válida |
| Atributo o valor no válido para el lote | `partes.masivo.atributoInvalido` | p. ej. tipo incompatible con un cliente | Elegir otro tipo o reducir selección |
| Ítem de lote inválido | `partes.masivo.itemInvalido` | Selección corrupta | Refrescar y reseleccionar |
| El lote incluye un registro que no es tarea | `partes.masivo.noEsTarea` | Alguno de los IDs corresponde a una compra de horas | Reducir la selección a tareas |
| No hay destinatarios para informar | `partes.masivo.sinDestinatarios` | Ningún cliente del filtro tiene usuario con correo | Ampliar el periodo o vincular usuario al cliente |
| No hay destinatarios para el filtro | `partes.masivo.mailInformarClientes.empty` | Preview vacío | Completar fechas y comprobar clientes con usuario |

## Errores de lógica

| Qué ve el usuario | Código / clave | Regla de negocio | Qué hacer |
|-------------------|----------------|------------------|-----------|
| Solo un supervisor puede ejecutar el proceso masivo | `partes.masivo.forbidden` | No sos supervisor | Usar usuario supervisor |
| La selección supera el tope configurado | `partes.masivo.topeExcedido` | Tope de negocio | Reducir selección o pedir ajuste del parámetro |
| El lote supera el límite técnico de 5000 | `partes.masivo.loteDemasiadoGrande` | Lote demasiado grande | Refinar filtros |
| Alguna tarea fue modificada por otro usuario | `partes.masivo.conflictoVersion` | Cambio concurrente | Refrescar y rearmar el lote |
| Algún identificador no existe; no hubo cambios | `partes.masivo.idInexistente` | Ítem fantasma en la selección | Refrescar listado |

## Errores técnicos posibles

| Qué ve el usuario | Código / HTTP (si aplica) | Causa posible | Qué hacer / a quién escalar |
|-------------------|---------------------------|---------------|------------------------------|
| Error de conexión | `infra.transport` | Red o servidor | Reintentar; soporte |
| Error inesperado | `infra.unexpected` | Fallo interno | Reportar a soporte |

**Catálogo completo:** proceso **P06** y transversal **P00** en [CATALOGO-ERRORES.md](./CATALOGO-ERRORES.md). Errores de carga (`partes.tarea.*`) no suelen aplicarse aquí salvo fechas de filtro.

## Preguntas frecuentes

### ¿Puedo cerrar “todo lo filtrado” sin marcar nada?

No. Hace falta una selección explícita (o “seleccionar todos” del resultado).

### Si una falla, ¿se aplican las demás?

No. No se aplican cambios parciales.

### ¿Puedo cambiar el cliente o la duración en lote?

No. Esos campos quedan fuera del proceso masivo.

### ¿Desde el celular?

No; solo en la versión web.

### ¿Tengo que tildar filas para informar a clientes?

No. El aviso cubre a los clientes del **filtro vigente** que tienen usuario y correo.

### Si hay muchos destinatarios, ¿se recortan?

No. Se listan todos. En la confirmación ves hasta **cinco tarjetas** y el resto se recorre con **scroll**.

### ¿El orden o el embudo solo afectan la página actual?

No. Ordenar por un título y elegir un valor en el embudo aplican a **todas las páginas** del filtro vigente. El agrupamiento por arrastre de columnas sí opera sobre la página que está en pantalla.
