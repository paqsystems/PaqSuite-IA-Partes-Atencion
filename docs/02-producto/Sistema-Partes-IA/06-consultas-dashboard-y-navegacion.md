# Consultas, dashboard y navegacion

## Objetivo

Una vez registradas las tareas, el modulo debe permitir leerlas y analizarlas de forma consistente con el perfil del usuario y con las capacidades del framework comun.

Esta capa concentra:

- consultas operativas;
- consultas agrupadas;
- dashboard;
- y navegacion funcional del modulo.

## Consultas del modulo

## Consulta detallada

La consulta detallada representa cada tarea individual con suficiente nivel de detalle para analisis operativo.

Segun el rol del usuario, puede incluir elementos como:

- asistente;
- cliente;
- fecha;
- tipo de tarea;
- duracion (presentacion en **`hh:mm`**; el valor tecnico permanece en minutos);
- marcas funcionales;
- descripcion.

Su finalidad es permitir lectura directa del trabajo realizado, sin perder trazabilidad del registro original.

**Filtro `es_tarea`:** la consulta detallada, las consultas agrupadas y el dashboard **solo consideran** registros con `es_tarea = true` (tareas de carga). Las compras de horas quedan fuera de estas vistas.

## Consultas agrupadas

El modulo debe ofrecer lecturas agrupadas de la dedicacion, al menos sobre ejes como:

- cliente;
- asistente;
- tipo de tarea;
- fecha.

Las metricas de tiempo se presentan en **`hh:mm`**, coherente con carga diaria y dashboard.

La finalidad de estas consultas no es solo listar tareas, sino ayudar a comprender patrones de dedicacion.

## Restricciones por perfil

### Cliente

Solo debe ver informacion de su propia organizacion.

### Asistente no supervisor

Solo debe ver su propia actividad, salvo decision funcional explicita distinta.

### Supervisor

Puede consultar el universo supervisor del modulo.

La condicion funcional del usuario es la primera capa que delimita el universo de datos visible.

Los permisos de menu o de proceso pueden restringir accesos a pantallas concretas, pero no reemplazan la logica funcional principal del modulo.

## Resultados vacios

Una consulta sin resultados no debe producir una experiencia ambigua.

El sistema debe dejar claro que:

- no se encontraron datos para ese contexto;
- no hay una falla tecnica por defecto;
- y no corresponde habilitar acciones que no tienen sentido sin datos.

Cuando existan acciones como exportar, deben seguir visibles para conservar coherencia de interfaz, pero quedar deshabilitadas mientras no exista contenido exportable.

## Delegacion al framework comun

Las consultas del modulo deben reutilizar el comportamiento comun ya definido para:

- grillas;
- pivots;
- exportaciones, cuando el producto las habilite;
- layouts.

Por lo tanto, `SistemaPartes` debe concentrarse en definir **que informacion** ofrece cada consulta y **que restricciones funcionales** aplica, sin reescribir reglas transversales ya resueltas por el framework.

## Pivots

Cuando el tipo de analisis lo justifique, una consulta del modulo puede ofrecer vista pivot.

La decision de ofrecer o no pivot depende del proceso, no de una obligacion universal para toda pantalla.

## Exportacion

La exportacion no forma parte del nucleo cerrado del MVP base.

Debe leerse como una evolucion inmediata soportada por el framework comun y aplicable a las consultas del modulo cuando se la habilite formalmente.

Cuando una consulta habilite exportacion:

- debe respetar exactamente el conjunto de datos visible para el usuario;
- debe mantener coherencia con los filtros aplicados;
- y debe apoyarse en las modalidades comunes del framework.

## Layouts

Cuando la consulta se apoye en grillas o pivots compatibles, debe poder convivir con el criterio comun de layouts persistentes del framework.

## Dashboard

El dashboard es la puerta de entrada analitica del modulo.

Su proposito es ofrecer una lectura rapida de la actividad relevante para el usuario autenticado.

### Lectura esperada por rol

- el asistente comun ve su propia dedicacion;
- el supervisor ve una lectura global o ampliada;
- el cliente ve informacion correspondiente a su propia organizacion.

### Contenido minimo del MVP

Como piso funcional, el dashboard deberia mostrar indicadores comprensibles como:

- total de tiempo del periodo (presentacion en **`hh:mm`**; el dato tecnico sigue en minutos);
- cantidad de tareas;
- algun resumen principal por cliente o por asistente, segun el rol (columna de tiempo tambien en **`hh:mm`**).

Puede incorporar graficos simples si aportan valor inmediato y no agregan complejidad desproporcionada.

## Actualizacion del dashboard

El dashboard debe refrescarse automaticamente sin recarga completa de pagina.

Ademas debe permitir refresco manual.

Como criterio funcional ya resuelto:

- el refresco automatico se considera siempre activo;
- el dashboard debe abrir inicialmente sobre el mes calendario de la fecha del sistema;
- el usuario puede modificar el periodo de analisis;
- y los filtros por rol deben seguir aplicando tanto en el refresco automatico como en el manual.

La frecuencia tecnica exacta del refresco automatico sigue siendo un detalle a cerrar en especificacion posterior.

## Navegacion del modulo

`SistemaPartes` debe integrarse al esquema de navegacion comun del sistema segun las definiciones vigentes del framework PaqSuite.

Eso implica que las opciones del modulo no se definen como un menu aislado o excepcional, sino como entradas funcionales que luego se materializan mediante `pq_menus`.

## Agrupacion funcional sugerida

Como organizacion del negocio, el modulo puede agruparse asi:

### Inicio

- dashboard o panel principal.

### Archivos

- clientes;
- asistentes;
- tipos de cliente;
- tipos de tarea.

### Partes

- carga de tareas;
- tareas propias;
- proceso masivo cuando aplique.

### Informes

- consultas detalladas;
- consultas agrupadas;
- **paquete de horas** (cuenta corriente de horas por cliente);
- exportaciones cuando correspondan.

## Informe Paquete de Horas (cuenta corriente)

El informe **Paquete de Horas** permite llevar una **cuenta corriente de horas** para clientes que contratan paquetes de horas anticipadas.

### Rasgos funcionales

- Presentacion tipo **grilla / pivot** (capacidades del framework).
- **Filtros:** fecha desde, fecha hasta, y **cliente** (cuando el usuario es asistente o supervisor). El cliente funcional solo ve su organizacion.
- **Mismas columnas / atributos** que la consulta detallada (Carga detallada).
- **No filtra por `es_tarea`:** incluye tareas (`es_tarea = true`) y compras/movimientos de paquete (`es_tarea = false`).
- **Fila «Saldo inicial»:** primer registro sintetico con la suma/resta de minutos **estrictamente anteriores** a la fecha desde del filtro (fecha desde exclusive).
  - Si `es_tarea = true` → **suma** minutos.
  - Si `es_tarea = false` → **resta** minutos.
- **Columna Saldo:** en «Saldo inicial» = el acumulado anterior; en cada registro siguiente = saldo previo ± minutos del registro (suma si tarea, resta si compra).
- **Pivot:** la columna **Saldo** **no** se incluye como campo del pivot (solo en la vista grilla / detalle de movimientos).

### Dependencia

La alta de registros con `es_tarea = false` (compra de horas) es un **proceso a definir** aparte; este informe debe comportarse correctamente cuando existan esos movimientos.

## Criterio final

La estructura funcional anterior orienta la experiencia del modulo, pero no debe interpretarse como un menu hardcodeado.

Su materializacion final debe seguir el criterio comun de navegacion del framework y no una reinterpretacion local aislada del modulo.
