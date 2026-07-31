---
specId: SPEC-006
titulo: Consultas, dashboard y navegación
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-07-31
openSpec: docs/05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md
---

# Consultas, dashboard y navegación

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

Desde **Inicio** ves el **Dashboard** de dedicación. En **Informes** consultás el detalle de tareas, totales agrupados y (según canal) el paquete de horas. Los datos siempre respetan tu perfil: el cliente solo ve su organización; el asistente, su actividad; el supervisor, el universo ampliado. Las consultas son de **solo lectura**.

## Funcionamiento

### Dashboard (Inicio)

1. Tras el login llegás al Dashboard (también desde **Inicio → Dashboard**).
2. El periodo inicial es el **mes calendario** actual.
3. Revisá total de minutos, cantidad de tareas y el ranking top N (por defecto 10).
4. Cambiá el mes/periodo y actualizá.
5. En web puede haber auto-refresco (por defecto cada 60 s; 0 = solo manual). Al salir de la pantalla se detiene.

### Consulta detallada

1. Menú **Informes** → consulta detallada.
2. Filtrá por fechas (recomendado), cliente, asistente (si sos supervisor), tipo y estado cerrado.
3. Revisá las filas en solo lectura.
4. En web podés usar vista **grilla** y **pivot**.
5. Sin datos verás un mensaje de vacío (no es un fallo técnico).

### Consultas agrupadas

1. Menú **Informes** → consultas agrupadas.
2. Elegí el eje: cliente, asistente, tipo o fecha.
3. Si el eje es fecha, indicá granularidad **día** o **mes**.
4. Revisá suma de minutos y cantidad de tareas (pivot disponible en web).

### Paquete de horas

1. Desde Informes o el enlace del dashboard.
2. Elegí el periodo (por defecto el mes actual).
3. Consultá totales y desgloses (p. ej. por cliente y por tipo).

## Particularidades

- No se editan tareas desde Informes en el MVP: usá **Carga diaria**.
- Vacío de resultados ≠ error del sistema.
- Exportación Excel: no es Must de esta versión.
- Pivot: en informes web; **nunca** en mobile ni en el dashboard como pivot.

### Límites / cupos visibles al usuario

| Parámetro típico | Default |
|------------------|---------|
| Top N del dashboard | 10 |
| Auto-refresco web | 60 segundos (0 = off) |

### Web vs mobile

| Tema | Web | Mobile |
|------|-----|--------|
| Dashboard | Auto-refresco configurable | Solo manual / pull-to-refresh |
| Informes | Grilla + pivot | Kardex / paquete de horas (ver SPEC-007) |
| Edición desde informes | No | No |

## Condiciones de uso

- Perfil Partes válido.
- Ítems de menú Inicio / Informes según permisos.
- Cliente: sin Archivos, Partes ni Seguridad.

## Errores de validación

| Qué ve el usuario (mensaje o síntoma) | Código / clave i18n (si existe) | Causa habitual | Qué hacer |
|---------------------------------------|--------------------------------|----------------|-----------|
| Debe indicar fecha desde y fecha hasta | `partes.tarea.fechasRequeridas` | Filtro incompleto | Completar fechas |
| Indique granularidad día o mes | `partes.consulta.granularidadRequerida` | Eje fecha sin granularidad | Elegir día o mes |
| Eje de agrupación no válido | `partes.consulta.ejeInvalido` | Eje incorrecto | Elegir un eje permitido |

## Errores de lógica

| Qué ve el usuario | Código / clave | Regla de negocio | Qué hacer |
|-------------------|----------------|------------------|-----------|
| No hay datos para el filtro indicado | `partes.consulta.empty` | Sin filas en el periodo/filtros | Ampliar fechas o quitar filtros |
| Solo veo mis partes / mi organización | — | Delimitación por perfil | Esperado; supervisor ve más |

## Errores técnicos posibles

| Qué ve el usuario | Código / HTTP (si aplica) | Causa posible | Qué hacer / a quién escalar |
|-------------------|---------------------------|---------------|------------------------------|
| Error de conexión | `infra.transport` | Red o servidor | Reintentar; soporte |
| Error inesperado | `infra.unexpected` | Fallo interno | Reportar a soporte |

## Preguntas frecuentes

### ¿Por qué no veo partes de otros asistentes?

Por delimitación de perfil. Solo el supervisor ve el universo ampliado.

### ¿El dashboard se actualiza solo?

En web, según el parámetro de refresco. En mobile, no: usá actualizar o pull-to-refresh.

### ¿Puedo editar desde Informes?

No en el MVP. Andá a Carga diaria.
