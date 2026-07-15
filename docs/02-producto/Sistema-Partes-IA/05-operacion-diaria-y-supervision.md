# Operacion diaria y supervision

## Proceso central del modulo

El proceso central de `SistemaPartes` es la **carga diaria de tareas**.

La expectativa funcional del modulo es que un asistente pueda registrar de forma simple lo que hizo, sin perder control sobre la calidad minima del dato.

## Carga diaria de tareas

### Que registra una tarea

Una tarea registra:

- fecha;
- cliente;
- tipo de tarea;
- duracion;
- descripcion u observacion;
- marca de presencialidad;
- marca de sin cargo;
- y, cuando corresponda, su estado `cerrado`.

### Forma operativa del proceso

La carga diaria no debe resolverse como un formulario aislado de una sola tarea.

La definicion adoptada es que el proceso se realiza desde una **grilla de trabajo previamente filtrada**, desde la cual se puede:

- insertar registros;
- editar registros;
- eliminar registros.

Como criterio funcional minimo, la grilla se abre sobre un contexto previamente acotado. Ese contexto debe incluir al menos filtros funcionales suficientes para que la carga sea ordenada y util para el usuario.

### Quien puede cargar

La carga diaria corresponde a:

- asistentes;
- supervisores.

El cliente no debe participar de este proceso.

### Regla de propiedad

Por defecto, una tarea pertenece al asistente que la registra.

Si quien opera es un supervisor, el sistema puede permitirle seleccionar al asistente propietario de la tarea.

## Reglas funcionales de captura

### Fecha

- la tarea debe tener fecha;
- una fecha futura puede merecer advertencia, pero no necesariamente bloqueo;
- el criterio de presentacion para la fecha debe ser amigable para el usuario, aunque la persistencia use otro formato.

### Cliente

- debe elegirse un cliente valido;
- el selector no deberia ofrecer clientes inhabilitados;
- la seleccion del cliente condiciona la disponibilidad de tipos de tarea.

### Tipo de tarea

- debe elegirse un tipo valido;
- el selector no deberia ofrecer tipos inhabilitados;
- la disponibilidad responde a la regla de tipos genericos mas tipos asignados al cliente.

### Duracion

- debe existir una duracion positiva;
- debe expresarse en tramos validos para el modulo;
- en la base conceptual vigente, ese tramo es de 15 minutos;
- la experiencia de carga puede ofrecer una representacion mas amigable que el valor tecnico persistido.

### Observacion

- la descripcion del trabajo es parte esencial del valor del registro;
- no deberia tratarse como un dato secundario o descartable.

### Marcas funcionales

- `sinCargo` define que la tarea no se considera con cargo;
- `presencial` define que la tarea implico presencia fisica;
- ambas marcas forman parte del significado del registro y no simples adornos de UI.

## Complemento por IA

La asistencia por IA no reemplaza el proceso de carga.

Su funcion esperada es **complementar** la carga manual, por ejemplo completando o proponiendo datos dentro de la misma pantalla o grilla de registracion.

La confirmacion final del registro sigue perteneciendo al circuito normal del usuario.

## Estado cerrado

El estado `cerrado` expresa que la tarea sale del circuito normal de modificacion individual.

En consecuencia:

- una tarea cerrada conserva su valor historico;
- pero deja de poder editarse o eliminarse en el flujo ordinario;
- su reapertura o recierre puede depender de capacidades supervisoras y de procesos masivos.

## Edicion y eliminacion

### Asistente

Puede operar normalmente sobre sus propias tareas mientras el estado de la tarea lo permita y las reglas del modulo no indiquen lo contrario.

### Supervisor

Puede operar tambien sobre tareas de terceros, siempre dentro del universo funcional que el modulo le reconoce como supervision.

## Proceso masivo

El proceso masivo es una herramienta de supervision.

Su objetivo principal en el MVP es actuar sobre el estado `cerrado` de un conjunto de tareas ya existentes.

### Rasgos esperados del proceso

- solo corresponde a supervisores;
- trabaja sobre una seleccion explicita de registros;
- requiere filtros previos para acotar el conjunto;
- debe mostrar con claridad que se va a procesar;
- no deberia ejecutarse sobre una seleccion vacia o invalida;
- y su resultado debe quedar inmediatamente reflejado para el usuario.

### Criterio de atomicidad

Cuando el proceso masivo afecte varios registros, la expectativa funcional es que no deje resultados parciales confusos si el conjunto no puede procesarse correctamente.

## Relacion entre operacion y supervision

La supervision no constituye un modulo aislado. Es una ampliacion funcional del mismo dominio de tareas.

Por eso debe conservar coherencia con la carga diaria en temas como:

- validaciones de negocio;
- lectura del estado cerrado;
- propiedad de las tareas;
- y consistencia del historial registrado.

## Resultado esperado de esta capa

Si la operacion diaria y la supervision estan bien definidas, el sistema permite:

- registrar trabajo real con baja friccion;
- controlar que lo cargado tenga sentido;
- restringir cambios cuando una tarea ya fue cerrada;
- y dar herramientas concretas al supervisor sin duplicar el dominio.
