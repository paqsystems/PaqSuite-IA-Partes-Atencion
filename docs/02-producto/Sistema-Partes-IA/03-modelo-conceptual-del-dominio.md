# Modelo conceptual del dominio

## Idea rectora

El dominio de `SistemaPartes` gira alrededor del **registro de tarea**.

Cada registro representa una unidad concreta de trabajo realizado **o** un movimiento de paquete de horas:

- por una persona (cuando es tarea);
- para un cliente;
- en una fecha determinada;
- con una clasificacion funcional (cuando es tarea);
- con una duracion medible;
- y con la marca **`es_tarea`**: `true` = tarea de carga; `false` = compra / movimiento de paquete de horas.

Todo el modelo del modulo existe para hacer posible ese registro y su posterior consulta.

## Entidades funcionales principales

### Asistente

Representa a la persona interna que realiza tareas dentro del modulo.

Un asistente:

- puede cargar tareas;
- puede quedar inhabilitado;
- puede estar vinculado a una identidad autenticable;
- y puede tener o no condicion de supervisor.

### Cliente

Representa a la organizacion para la cual se realizan tareas.

Un cliente:

- puede recibir tareas;
- se clasifica por tipo de cliente;
- puede existir sin acceso autenticado;
- y puede, si se habilita, consultar informacion propia dentro del sistema.

### Tipo de cliente

Es un catalogo de clasificacion para los clientes del modulo.

No es un perfil de seguridad. Es una categoria funcional del cliente.

### Tipo de tarea

Es el catalogo que clasifica la naturaleza del trabajo realizado.

Su semantica es especialmente importante porque define:

- que tipos pueden usarse para cualquier cliente;
- cuales requieren asignacion especifica;
- y cual es el tipo por defecto del sistema.

### Asignacion cliente - tipo de tarea

Es la relacion que habilita tipos de tarea no genericos para clientes concretos.

Su razon de ser es evitar que cualquier cliente pueda seleccionar tipos que no le corresponden.

### Registro de tarea

Es la entidad central del modulo.

Cada tarea conserva, como minimo:

- quien la realizo;
- para que cliente fue;
- que tipo de tarea fue;
- cuando ocurrio;
- cuanto duro;
- y una descripcion funcional del trabajo realizado.

Ademas puede contener marcas operativas, como si fue presencial, sin cargo o cerrada.

## Relaciones funcionales

### Asistente y tareas

Un asistente puede tener muchas tareas registradas.

Cada tarea pertenece a un unico asistente propietario.

### Cliente y tareas

Un cliente puede tener muchas tareas asociadas.

Cada tarea pertenece a un unico cliente.

### Tipo de tarea y tareas

Un tipo de tarea puede utilizarse en muchas tareas.

Cada tarea utiliza un unico tipo de tarea.

### Cliente y tipo de tarea

Los tipos de tarea genericos son de disponibilidad general.

Los tipos no genericos requieren asignacion explicita al cliente.

## Semantica de atributos relevantes

### `supervisor`

No indica un rol tecnico del framework. Indica una capacidad funcional del modulo.

Le permite al asistente actuar sobre informacion y procesos ampliados de supervision.

### `isGenerico`

Indica que un tipo de tarea esta disponible para todos los clientes, sin necesidad de asignacion particular.

### `isDefault`

Indica el tipo de tarea por defecto del sistema.

Como definicion conceptual adoptada:

- solo debe existir un unico tipo por defecto;
- y ese tipo por defecto debe ser tambien generico.

### `sinCargo`

Indica que la tarea forma parte de la dedicacion operativa, pero no deberia tratarse como dedicacion con cargo.

### `presencial`

Indica que la tarea implico presencia fisica o prestacion presencial.

### `cerrado`

Indica que la tarea ya no puede seguir editandose o eliminandose dentro del circuito normal.

No significa que deje de existir ni que pierda su valor historico.

## Integridad funcional esperada

El modulo debe preservar ciertas coherencias minimas:

- una tarea siempre debe referenciar un asistente, un cliente y un tipo de tarea validos;
- un cliente inhabilitado no deberia seguir apareciendo como opcion de carga;
- un asistente inhabilitado no deberia seguir operando normalmente;
- un tipo de tarea inhabilitado no deberia seguir ofreciendose para nuevas tareas;
- una tarea cerrada conserva historia, pero deja de ser editable en el flujo normal;
- un cliente con acceso debe conservar coherencia entre su entidad funcional y su identidad autenticable.

## Glosario funcional minimo

### Tarea

Registro unitario de trabajo realizado.

### Dedicacion

Tiempo invertido en tareas, ya sea para lectura operativa, analitica o futura valorizacion.

### Carga diaria

Proceso principal mediante el cual un asistente o supervisor trabaja sobre una grilla previamente filtrada para insertar, editar o eliminar registros de tareas.

### Proceso masivo

Proceso supervisor que, sobre un conjunto de tareas ya existentes acotado por filtros (periodo obligatorio; cliente, asistente y estado opcionales), permite seleccionar registros y aplicar cambios en lote: actualizacion masiva de atributos permitidos (prioridad: tipo de tarea y sin cargo; factibles: presencial, asistente, fecha; excluidos: cliente, duracion, descripcion) y/o cerrar-reabrir, sobre una grilla con capacidades del framework (filtro por columna, totales, column chooser, plantillas, exportacion Excel).

### Consulta

Vista destinada a leer y analizar la informacion ya registrada.

### Dashboard

Puerta de entrada analitica del modulo, con indicadores y accesos resumidos segun perfil.
