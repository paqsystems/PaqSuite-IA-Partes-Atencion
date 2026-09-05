# Fuera de alcance y evolucion

## Objetivo

Este documento separa con claridad:

- lo que pertenece al MVP ya definido;
- de las lineas de evolucion que aparecen en la documentacion heredada, pero aun no forman parte de una definicion cerrada del modulo.

## Criterio general

Toda idea futura es valida como orientacion, pero no debe contaminar la fuente de verdad del MVP si todavia no tiene:

- alcance acordado;
- reglas de negocio consolidadas;
- y frontera clara respecto de lo ya definido.

## Lineas de evolucion identificadas

### Exportacion

La **exportación Excel de grilla** (GEN-11) sigue como capacidad de toolbar y **convive** con la emisión.

La **emisión documental** (PDF, Excel de reporte, mail, impresión) sobre **Consulta detallada** queda definida en [`15-reportes-emisiones.md`](./15-reportes-emisiones.md) y **sí forma parte** de la definición vigente (adopción GEN-15). No se trata como línea postergada.

Otras consultas (agrupadas, Paquete de horas) **no** montan Emitir en esa definición; pueden adoptarlo después.

### Cuenta corriente de horas / Paquete de Horas

La **cuenta corriente de horas** por cliente con paquetes anticipados **ya forma parte** de la definicion vigente del informe **Paquete de Horas** (ver `06-consultas-dashboard-y-navegacion.md`).

Lo que sigue como evolucion / proceso a definir es la **pantalla o flujo de alta de compras de horas** (`es_tarea = false`), no el concepto de saldo en el informe.

### Auditoria de partes

Existe la idea de una pantalla supervisor con **importacion desde Excel** y **envio selectivo de mails**, mas otras capacidades de auditoria ampliada.

Eso **no** se confunde con el proceso masivo ya definido: la **edicion masiva de atributos permitidos** (tipo de tarea, sin cargo, y los factibles documentados) pertenece al proceso masivo en `05-operacion-diaria-y-supervision.md`.

Mientras no se cierre formalmente, la auditoria con importacion Excel y mails sigue siendo evolucion distinta.

### Costeo de horas

Se menciona una futura tabla de costos por asistente, anio y mes, junto con consultas de costo por tarea.

No forma parte del dominio cerrado actual del MVP.

### Informe de facturacion

Se menciona una posible extension para preparar informacion util para facturacion o integracion con ERP.

Eso debe tratarse como linea post-MVP.

### Carga de tareas con IA

**Conceptualizado** en [`14-smart-capture.md`](./14-smart-capture.md): Smart Capture (GEN-03) embebido en el **modal** de alta/edición de tarea en Carga diaria (texto, audio, imagen; BYOK; sin montaje en grilla).

Complementa la carga manual; no la reemplaza. Distinto del Asistente IA documental (`12`) y de la importación Excel (`13`).

Mobile: fuera de alcance v1 (ver `10-mobile.md` y D-SC-10).

### Asistente IA (ayuda / chat documental)

La ayuda orientativa desde el **menú avatar** (corpus Partes + generalidades Framework) **ya tiene definición conceptual** en `12-asistente-ia-ayuda-y-chat-documental.md`.

Esa capacidad **adopta** el canal GEN del Framework (`21` / SPEC-001-21); no se reabre aquí como invención del módulo. Pendiente: SPEC/HU/TR de adopción e implementación en el shell.

### Carga cronometrada

Se plantea un modo de captura con inicio y fin de tarea en tiempo real.

Debe considerarse una evolucion posible del proceso de carga, no una obligacion del MVP actual.

### Carga masiva desde Excel

La **importación de partes (tareas) desde Excel** bajo **Carga de Partes** queda definida en [`13-importacion-partes-excel.md`](./13-importacion-partes-excel.md) (plantilla, obligatoriedad, `es_tarea = true`, commit parcial con confirmación).

No se confunde con:

- la **exportación** Excel de grillas/informes o del proceso masivo;
- la línea de **auditoría** (Excel + mails), que sigue como evolución distinta (§ Auditoria de partes).

### Mobile

La experiencia mobile del modulo debe documentarse conceptualmente de forma separada.

Por eso esta carpeta incorpora un documento especifico `10-mobile.md`, aunque el alcance exacto del MVP mobile todavia no este completamente cerrado.

## Relacion con capacidades generales del framework

Algunas mejoras mencionadas en la documentacion heredada ya pertenecen al framework comun y no deben reabrirse aqui como definicion exclusiva del modulo.

Por ejemplo:

- layouts de grillas;
- layouts de pivots;
- exportaciones;
- menu avatar (catálogo GEN; Partes solo declara adopción del Asistente IA en `12-…`);
- **reportes / emisiones** (GEN-15; Partes declara adopción en `15-reportes-emisiones.md`);
- multilingual;
- seguridad base;
- menu lateral comun.

El modulo solo debe declarar como las usa, no volver a definirlas.

## Regla de adopcion futura

Cuando una de estas lineas pase a formar parte del producto formal, deberia incorporarse mediante el proceso habitual:

1. definicion conceptual clara;
2. impacto en este set de documentos fuente;
3. recien despues generacion de `SPEC`, `HU` y `TR`.

## Resultado esperado de este documento

La carpeta `Sistema-Partes-IA` debe poder leerse sin mezclar:

- requerimientos vigentes;
- ideas tentativas;
- y mejoras futuras aun no acordadas.
