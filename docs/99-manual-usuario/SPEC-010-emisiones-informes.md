---
specId: SPEC-010
titulo: Emisión de reportes y diseñador
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-10-05
openSpec: docs/02-producto/Sistema-Partes-IA/15-reportes-emisiones.md
---

# Emisión de reportes y diseñador

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

En **Informes → Consulta detallada** (solo **web**) podés **Emitir** un reporte formal con el mismo universo de datos que la consulta filtrada (PDF, impresión, Excel, CSV o correo, según lo habilitado). Quienes tienen permiso de **diseño** acceden al **Diseñador de emisiones** desde **Soporte Técnico** para crear o ajustar diseños de reporte. **Emitir** no es lo mismo que **exportar la grilla** a Excel ni que la vista pivot.

## Funcionamiento

### Emitir desde consulta detallada

1. Menú **Informes** → consulta detallada.
2. Aplicá filtros (fechas, cliente, asistente si sos supervisor, tipo, estado cerrado).
3. Ejecutá la búsqueda y verificá que hay datos (o que aceptás emitir vacío según el caso).
4. Pulsá **Emitir**.
5. Elegí diseño de reporte, canal de salida (PDF, correo, etc.) y confirmá.
6. Según el canal, descargás el archivo, imprimís o completás el envío por correo.

Los datos del reporte respetan los **filtros de pantalla**, no solo la página visible de la grilla.

### Diseñar reportes (administración / soporte)

1. Menú **Soporte Técnico** → **Diseñador de emisiones** (escritorio).
2. Elegí el proceso de **consulta detallada** de Partes si el sistema lo pide.
3. Editá el diseño en el editor visual (guardar desde la barra del diseñador).
4. Podés marcar un diseño como **principal** para ese proceso.

Diseñar exige permiso específico; **emitir** solo requiere poder entrar a la consulta y tener la función habilitada.

## Particularidades

- **No disponible en móvil** (ni emitir ni diseñador).
- Si el parámetro de emisión está desactivado, no verás **Emitir** ni el diseñador operativo.
- El cliente, asistente o supervisor emiten según el mismo universo de datos que la consulta.
- Las columnas **Erp Cliente** y **Erp Artículo** del maestro de clientes pueden figurar en el dataset del informe.

## Condiciones de uso

- Menú de consulta detallada visible.
- Emisión habilitada en parámetros.
- Para diseñar: permiso de diseño de reportes y acceso al menú de soporte técnico.

## Errores posibles en este proceso

Identificador de catálogo: **P08**. Ver [CATALOGO-ERRORES.md](./CATALOGO-ERRORES.md) y errores de consulta **P07** si fallan filtros previos a emitir. También **P00**.

## Preguntas frecuentes

### ¿Emitir usa el pivot?

No. Usa el conjunto de datos de la consulta detallada filtrada, independiente de la vista grilla o pivot.

### ¿Puedo diseñar desde el celular?

No. Solo en la versión web de escritorio.

### ¿Quién puede emitir?

Quien puede abrir la consulta detallada y tiene la emisión habilitada; no hace falta permiso de diseño.
