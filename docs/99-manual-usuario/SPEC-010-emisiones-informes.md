---
specId: SPEC-010
titulo: Emisión de reportes y diseñador
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-10-30
openSpec: docs/02-producto/Sistema-Partes-IA/15-reportes-emisiones.md
---

# Emisión de reportes y diseñador

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

En **Informes → Consulta detallada** (solo **web**) podés **Emitir** un reporte formal con las **filas que tildes** en la grilla (PDF, impresión, Excel, CSV o correo, según lo habilitado). Quienes tienen permiso de **diseño** acceden al **Diseñador de emisiones** desde **Soporte Técnico** para crear o ajustar diseños de reporte. **Emitir** no es lo mismo que **exportar la grilla** a Excel ni que la vista pivot.

## Funcionamiento

### Emitir desde consulta detallada

1. Menú **Informes** → consulta detallada.
2. Aplicá filtros (fechas, cliente, asistente si sos supervisor, tipo, estado cerrado).
3. Ejecutá la búsqueda. Si no hay filas, **Emitir** queda visible y deshabilitado.
4. **Tildá** en la grilla las filas que querés incluir (podés usar la casilla del encabezado para seleccionar todas). Sin filas tildadas, **Emitir** sigue deshabilitado.
5. Pulsá **Emitir**.
6. Elegí un **diseño guardado** (no aparece la plantilla original), el canal (PDF, correo, etc.) y confirmá.
7. Según el canal, descargás el archivo, imprimís o completás el envío por correo.

Si el combo de diseños está vacío, alguien con permiso de diseño tiene que guardar una copia desde el diseñador. El reporte usa ese diseño y **solo las filas tildadas**, respetando además los filtros de pantalla y tu perfil. La duración figura en **minutos**, salvo que el diseño la formatee de otra manera.

### Diseñar reportes (administración / soporte)

1. Menú **Soporte Técnico** → **Diseñador de emisiones** (escritorio).
2. Elegí el proceso de **consulta detallada** de Partes si el sistema lo pide.
3. Editá el diseño en el editor visual y guardalo desde la barra del diseñador. Ese diseño es el que usa **Emitir**.
4. Podés marcar un diseño como **principal** para ese proceso.

### Asistencia IA en el diseñador (piloto)

En instalaciones donde el administrador habilitó el **servicio de reportes** con asistencia por inteligencia artificial, el diseñador puede ofrecer un asistente para **generar o ajustar** el diseño a partir de instrucciones en **español** (por ejemplo: listar partes por fecha, agrupar por cliente o totalizar horas).

- La IA solo puede usar **campos previstos** para la consulta detallada (fecha, cliente, asistente, duración, etc.); no inventa datos de negocio que no existan en el sistema.
- Todo cambio propuesto se **revisa en pantalla**; el diseño **no se guarda solo**: tenés que usar **Guardar** o **Guardar como** en la barra del diseñador, igual que sin IA.
- Las credenciales del proveedor de IA las configura **soporte** en el servidor del servicio de reportes, no en tu usuario ni en Preferencias del portal.
- Si no ves el asistente, el diseñador clásico sigue disponible; contactá soporte si necesitás el piloto.

En la banda de detalle, cada control tiene que estar vinculado a un campo. Un control suelto (por ejemplo un medidor sin dato) se repite en cada fila y alarga el PDF.

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

### ¿Por qué no veo diseños al emitir?

La plantilla original no se emite. Tiene que existir un diseño guardado desde el diseñador.

### ¿La duración sale en horas y minutos?

Sale en minutos, como la cargó el parte, salvo que el diseño aplique otro formato.

### ¿Quién puede emitir?

Quien puede abrir la consulta detallada y tiene la emisión habilitada; no hace falta permiso de diseño.
