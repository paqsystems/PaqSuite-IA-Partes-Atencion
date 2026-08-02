# Mobile

## Objetivo

Dejar explicitado que `SistemaPartes` requiere una lectura conceptual especifica para mobile y no solo una traduccion automatica de la experiencia web.

Este documento fija el alcance conceptual principal de la variante mobile del modulo, para que luego puedan derivarse `SPEC`, `HU` y `TR` especificos de esa experiencia.

## Principio general

La version mobile del modulo debe respetar el mismo dominio funcional que la version web:

- misma identidad funcional;
- mismas reglas de negocio;
- mismo modelo conceptual de tareas;
- mismas restricciones por rol;
- y mismo backend.

Lo que puede cambiar es la forma de presentacion, navegacion y priorizacion de procesos.

## Alcance conceptual minimo

Mobile no debe pensarse como un producto distinto. Debe pensarse como otra experiencia de acceso al mismo modulo, con foco en operacion cotidiana, consulta rapida y uso desde dispositivo personal.

Por lo tanto:

- las tareas siguen perteneciendo al mismo dominio;
- el usuario sigue operando como asistente, supervisor o cliente;
- las consultas siguen respetando los mismos filtros funcionales;
- y el dashboard debe conservar el mismo sentido de lectura, aunque su presentacion cambie.

## Pantalla inicial

La aplicacion mobile debe contar con una pantalla inicial previa al login.

Su objetivo es permitir una configuracion basica del entorno de conexion.

### Elementos esperados

- acceso al login;
- boton de configuracion;
- configuracion de la URL del entorno al que se conectara la app.

La definicion detallada de validaciones, persistencia local y recuperacion de esa URL se resolvera despues en especificacion tecnica.

## Login mobile

El login mobile debe solicitar:

- empresa;
- usuario;
- contrasena.

La presencia del campo `empresa` queda adoptada para mobile.

Sigue pendiente aclarar con mayor detalle como aplica exactamente esa empresa dentro del producto y que comportamiento concreto debe tener en el contexto funcional del modulo.

## Prioridad natural en mobile

Por la naturaleza del modulo, la experiencia mobile tiende a tener mayor afinidad con:

- ingreso al sistema;
- consulta rapida del dashboard;
- carga individual de tareas;
- consulta de partes;
- lectura resumida de informacion relevante;
- informe paquete de horas.

## Funciones incluidas en mobile

La experiencia mobile debe contemplar como minimo:

- pantalla inicial con configuracion de URL;
- login con empresa, usuario y contrasena;
- dashboard;
- carga individual de partes, si el usuario no es cliente;
- consulta de partes;
- informe paquete de horas.

## Funciones excluidas en mobile

La version mobile no debe incluir:

- ABMs;
- pivots;
- cargas masivas;
- operaciones con Excel;
- informes impresos.

Estas exclusiones no niegan su existencia en web. Solo delimitan el alcance de la experiencia mobile.

## Presentacion de grillas

Las grillas mobile no deben replicar sin mas la experiencia desktop.

El criterio adoptado es que la presentacion en mobile debe resolverse en forma de **Kardex**.

Esto aplica especialmente a:

- consultas de partes;
- listados operativos;
- y cualquier pantalla donde la lectura secuencial por registro sea mas natural que una tabla ancha tradicional.

## Carga de tareas en mobile

La carga de tareas en mobile debe mantener las mismas reglas del dominio:

- seleccion valida de cliente;
- tipos de tarea segun regla de genericos mas asignados;
- duracion valida;
- observacion obligatoria;
- uso de marcas funcionales;
- respeto del estado `cerrado`.

### Regla de acceso

La carga individual de partes solo corresponde a usuarios que no sean clientes.

### Forma del proceso

En mobile la carga debe entenderse como **carga individual**, no como un proceso administrativo amplio.

### IA en mobile

La carga mobile debe admitir chatbot de IA como ayuda de registracion.

La IA complementa la carga manual:

- propone o completa informacion;
- pero no reemplaza la confirmacion final del usuario.

La UI mobile puede resolver la experiencia de otra manera, pero no alterar el significado funcional del proceso.

## Dashboard y consultas en mobile

El dashboard mobile debe conservar:

- lectura acorde al rol;
- indicador de periodo vigente;
- coherencia con filtros funcionales;
- y acceso a consultas relevantes cuando tenga sentido.

Las consultas mobile pueden requerir una simplificacion de layout respecto de desktop, pero no deben romper la consistencia del dominio.

## Consulta de partes en mobile

La consulta de partes mobile debe permitir:

- seleccionar periodo;
- seleccionar clientes dentro del universo permitido;
- seleccionar asistentes dentro del universo permitido.

### Valores por defecto

- el periodo debe iniciar por defecto en el dia actual.

### Restriccion por universo funcional

Los filtros disponibles deben quedar acotados al universo que corresponda por tipo de usuario:

- cliente;
- asistente;
- supervisor.

### Resultado de la consulta

La consulta muestra una grilla en formato Kardex.

Si el usuario es asistente, sobre esa misma superficie deben poder existir opciones para:

- agregar;
- editar;
- eliminar.

La definicion fina de permisos por caso concreto seguira dependiendo del rol y del estado funcional de cada registro.

## Informe paquete de horas

La experiencia mobile debe incluir el proceso llamado **Informe Paquete de Horas**.

Este informe debe tratarse como una capacidad propia incluida en mobile y no como un simple detalle accesorio del dashboard.

## Navegacion mobile

La navegacion mobile debe seguir el framework comun del producto, adaptando la presentacion al dispositivo sin perder:

- visibilidad por rol;
- continuidad con `pq_menus`;
- y coherencia con el shell autenticado.

## Estado actual

El alcance mobile ya queda mayormente definido en esta base conceptual.

Lo que sigue abierto no es el set principal de pantallas, sino algunos detalles de comportamiento, especialmente:

- la aplicacion exacta del campo `empresa` en login;
- el detalle fino de permisos y acciones por rol dentro de las superficies mobile;
- y la especificacion concreta del informe paquete de horas.
