---
specId: SPEC-009
titulo: Importación desde Excel (carga diaria)
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-10-05
openSpec: docs/02-producto/Sistema-Partes-IA/13-importacion-partes-excel.md
---

# Importación desde Excel (carga diaria)

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

En **Partes → Carga diaria** (solo **web**) podés cargar muchas tareas desde una planilla **Excel (.xlsx)**: descargás la **plantilla**, completás filas, **validás** y **procesás** para grabar solo las filas correctas. Cada fila grabada es una **tarea nueva abierta**. No está disponible en la app móvil.

## Funcionamiento

1. Abrí **Carga diaria** y definí el filtro de fechas que usarás después (se mantiene tras importar).
2. En la barra bajo los filtros, elegí **Descargar plantilla**.
3. Completá la planilla con una fila por tarea (cliente, tipo, fecha, duración, descripción, etc., según columnas de la plantilla).
4. **Importar** → seleccioná el archivo `.xlsx`.
5. **Validar**: revisá la grilla de errores por número de fila y columna.
6. Corregí la planilla o eliminá filas erróneas.
7. **Procesar**: se graban las filas válidas; la grilla de carga se actualiza.

### Reglas importantes

| Situación | Regla |
|-----------|--------|
| Asistente (no supervisor) | Las tareas quedan a tu nombre; si el archivo trae otro asistente, esa fila falla. |
| Supervisor | La columna **asistente** es obligatoria por fila. |
| Duración | Formato **hh:mm** o minutos enteros; múltiplo del **tramo** (habitual 15 min). |
| Descripción | Obligatoria en cada fila (equivale a observación en carga manual). |
| Cliente y tipo | Deben existir, estar activos y el tipo debe ser válido para ese cliente. |

Si no ves la barra de plantilla/importar, la función puede estar **deshabilitada** en parámetros de la instalación.

## Particularidades

- Validar no graba; procesar sí.
- Puede no permitirse procesar si quedan errores sin corregir.
- Exportar errores a Excel ayuda a corregir fuera del sistema.
- Tras procesar, revisá el listado como en una carga manual.

## Condiciones de uso

- Perfil asistente o supervisor con permiso de carga diaria.
- Importación habilitada en la instalación.
- Archivo `.xlsx` según plantilla oficial.

## Errores posibles en este proceso

Identificador de catálogo: **P05** (y **P03** al persistir). Ver [CATALOGO-ERRORES.md](./CATALOGO-ERRORES.md).

Incluye mensajes `excelImport.*`, `partes.import.*` y errores transversales **P00**.

## Preguntas frecuentes

### ¿Puedo importar desde el celular?

No. Usá la versión web.

### ¿La importación cierra las tareas?

No. Las tareas importadas quedan **abiertas** hasta que un supervisor las cierre.

### ¿Es lo mismo que exportar la grilla a Excel?

No. **Exportar** descarga lo que ves filtrado; **importar** crea tareas nuevas desde tu archivo.
