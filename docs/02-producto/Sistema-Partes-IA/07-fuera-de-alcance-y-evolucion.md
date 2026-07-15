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

La exportacion debe leerse como una evolucion inmediata del modulo, apoyada en las capacidades comunes del framework.

No se considera parte del nucleo minimo imprescindible del MVP base, pero si una extension cercana y esperable de las consultas.

### Cuenta corriente de horas

Existe la idea de una vista que muestre movimientos por fecha y un acumulado de horas por cliente en un periodo.

Hoy debe leerse como un proceso funcional distinto y futuro, no como parte cerrada del MVP.

### Auditoria de partes

Existe la idea de una pantalla supervisor con edicion masiva ampliada, importacion desde Excel y envio selectivo de mails.

Mientras no se cierre formalmente, debe considerarse una evolucion distinta del proceso masivo MVP y no un detalle ya definido del mismo.

### Costeo de horas

Se menciona una futura tabla de costos por asistente, anio y mes, junto con consultas de costo por tarea.

No forma parte del dominio cerrado actual del MVP.

### Informe de facturacion

Se menciona una posible extension para preparar informacion util para facturacion o integracion con ERP.

Eso debe tratarse como linea post-MVP.

### Carga de tareas con IA

Se plantea la posibilidad de registrar tareas mediante chat, audio o imagen.

Su rol esperado no es reemplazar la carga manual, sino complementarla completando o proponiendo datos dentro del mismo proceso de registracion.

Aun asi, sigue fuera de la definicion funcional cerrada actual del MVP base.

### Carga cronometrada

Se plantea un modo de captura con inicio y fin de tarea en tiempo real.

Debe considerarse una evolucion posible del proceso de carga, no una obligacion del MVP actual.

### Carga masiva desde Excel

Se menciona una posible importacion con plantilla, validacion y grabacion masiva.

Debe mantenerse fuera de la definicion canonicamente cerrada hasta que tenga alcance propio.

### Mobile

La experiencia mobile del modulo debe documentarse conceptualmente de forma separada.

Por eso esta carpeta incorpora un documento especifico `10-mobile.md`, aunque el alcance exacto del MVP mobile todavia no este completamente cerrado.

## Relacion con capacidades generales del framework

Algunas mejoras mencionadas en la documentacion heredada ya pertenecen al framework comun y no deben reabrirse aqui como definicion exclusiva del modulo.

Por ejemplo:

- layouts de grillas;
- layouts de pivots;
- exportaciones;
- menu avatar;
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
