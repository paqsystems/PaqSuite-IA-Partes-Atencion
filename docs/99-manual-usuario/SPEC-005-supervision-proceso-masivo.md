---
specId: SPEC-005
titulo: Supervisión y proceso masivo
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-07-31
openSpec: docs/05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md
---

# Supervisión y proceso masivo

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

El **proceso masivo** permite a un **supervisor** cerrar o reabrir muchas tareas de una vez, sin editar el resto de los datos. Asistentes y clientes no usan esta pantalla. También podés cerrar o reabrir de a una desde la carga diaria (ver SPEC-004).

## Funcionamiento

### Cerrar o reabrir en lote

1. Menú **Partes** → **Proceso masivo** (un atajo desde carga **no** arrastra los filtros previos).
2. Aplicá filtros: fechas obligatorias; cliente, asistente y estado opcionales.
3. Seleccioná filas, o usá la opción de seleccionar todos los del resultado (si hay varias páginas, confirmá que afectará a N partes).
4. Elegí **Cerrar** o **Reabrir**.
5. Confirmá la acción (cantidad y resumen).
6. El resultado es **todas o ninguna**: si algo falla, no queda un lote a medias.
7. El listado se actualiza; si hubo conflicto, refrescá y volvé a armar la selección.

### Supervisión puntual en carga diaria

1. En carga diaria, filtrá por asistente u otros criterios.
2. Editá tareas no cerradas; cerrá o reabrí individualmente si sos supervisor.

## Particularidades

- Solo cambia el estado **cerrado**; no modifica duración, cliente ni observación en lote.
- Acciones ya aplicadas (cerrar lo cerrado) son inocuas / idempotentes.
- Si se supera el tope configurado o el límite técnico (~5000), debés refinar el filtro.
- **No disponible en la app móvil.**

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
| Acción de proceso masivo no válida | `partes.masivo.accionInvalida` | Acción incorrecta | Elegir Cerrar o Reabrir |
| Ítem de lote inválido | `partes.masivo.itemInvalido` | Selección corrupta | Refrescar y reseleccionar |

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

## Preguntas frecuentes

### ¿Puedo cerrar “todo lo filtrado” sin marcar nada?

No. Hace falta una selección explícita (o “seleccionar todos” del resultado).

### Si una falla, ¿se cierran las demás?

No. No se aplican cambios parciales.

### ¿Desde el celular?

No; solo en la versión web.
