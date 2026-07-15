# Maestros y catalogos

## Objetivo

Los maestros del modulo existen para sostener una carga de tareas consistente.

No son un fin en si mismos. Su valor esta en permitir que la operatoria diaria cuente con datos validos, comprensibles y administrables.

## Maestros principales

### Clientes

El maestro de clientes administra las organizaciones para las que se registran tareas.

Su responsabilidad conceptual incluye:

- identificar al cliente;
- describirlo de manera clara;
- clasificarlo por tipo de cliente;
- reflejar si esta habilitado o inhabilitado;
- y, cuando corresponda, determinar si tiene acceso autenticado al modulo.

El cliente puede existir aunque nunca ingrese al sistema. El acceso es una capacidad adicional, no la definicion del cliente.

### Asistentes

El maestro de asistentes administra las personas internas que operan en el modulo.

Debe permitir distinguir:

- identidad funcional;
- estado operativo;
- condicion de supervisor;
- y vinculacion con la identidad autenticable comun.

### Tipos de cliente

Es un catalogo de clasificacion de clientes.

Su utilidad principal es ordenar la informacion del negocio y permitir segmentaciones o consultas posteriores.

### Tipos de tarea

Es un catalogo central del modulo porque condiciona directamente la carga diaria.

Debe permitir distinguir:

- tipos genericos, disponibles para todos los clientes;
- tipos especificos, disponibles solo por asignacion;
- y un unico tipo por defecto, que ademas debe ser generico.

## Regla de habilitacion general

En todos los maestros del modulo, un registro inhabilitado no deberia seguir ofreciendose como opcion valida para nuevas operaciones donde su uso ya no tenga sentido.

Eso vale, por ejemplo, para:

- clientes en la carga de tareas;
- asistentes seleccionables por un supervisor;
- tipos de tarea en selectores;
- y tipos de cliente para nuevos clientes.

## Asignacion de tipos de tarea a cliente

La relacion entre cliente y tipo de tarea cumple una funcion concreta:

- permitir que ciertos tipos no genericos solo se usen en clientes definidos;
- mantener una oferta de seleccion coherente durante la carga de tareas;
- y evitar combinaciones funcionalmente invalidas.

### Regla conceptual

Para un cliente determinado, el universo de tipos disponibles surge de:

- todos los tipos genericos habilitados;
- mas los tipos especificos que ese cliente tenga asignados.

### Restriccion de sentido

No tiene sentido asignar manualmente a un cliente un tipo que ya es generico para todo el sistema.

La asignacion manual debe reservarse a tipos no genericos.

## Clientes con acceso

El maestro de clientes no solo administra datos comerciales. Tambien puede administrar el hecho de que ciertos clientes ingresen al modulo.

Por eso debe quedar conceptualmente claro:

- un cliente puede no tener acceso;
- un cliente con acceso necesita vinculacion coherente con la identidad comun;
- habilitar o revocar acceso no deberia dejar estados ambiguos;
- y el cliente con acceso sigue siendo, antes que nada, una entidad del negocio.

## Integridad en eliminaciones o bajas

Los maestros del modulo no deberian permitir operaciones que rompan la trazabilidad historica.

Eso significa, como criterio general:

- si un registro ya esta fuertemente referenciado por tareas o relaciones activas, deberia privilegiarse su inhabilitacion antes que una eliminacion destructiva;
- las reglas concretas de bloqueo o baja logica se derivaran luego en `SPEC`, `HU` y `TR`.

## Resultado esperado de esta capa

Si los maestros estan bien definidos:

- la carga de tareas se vuelve mas simple;
- la supervision se apoya en catalogos confiables;
- las consultas reflejan clasificaciones consistentes;
- y el sistema evita gran parte de los errores operativos antes de que lleguen al registro diario.
