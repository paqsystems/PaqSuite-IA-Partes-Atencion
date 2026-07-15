# Actores, identidad y acceso

## Idea central

`SistemaPartes` utiliza una identidad autenticable comun del sistema, pero necesita ademas una **identidad funcional propia del modulo** para decidir como opera cada usuario.

En otras palabras:

- el framework autentica;
- el modulo interpreta a que actor de negocio corresponde esa identidad;
- y a partir de ahi habilita la operatoria funcional.

## Actores del modulo

### Asistente

Es el usuario interno que registra tareas y consulta su propia dedicacion.

En esta base conceptual, `asistente` y `empleado` deben entenderse como sinonimos historicos. El termino preferido es **asistente**.

En el MVP:

- carga tareas;
- edita o elimina tareas propias mientras correspondan;
- consulta sus tareas;
- consulta resumentes o indicadores propios;
- visualiza su perfil.

### Supervisor

Es un asistente con capacidad ampliada de supervision.

Ademas de lo que hace un asistente comun:

- ve tareas de otros asistentes;
- crea tareas para terceros;
- edita o elimina tareas de terceros;
- ejecuta el proceso masivo sobre tareas;
- accede a informacion no restringida a su propia actividad;
- administra maestros del modulo segun permisos.

El concepto de supervisor pertenece al dominio del modulo y no a la seguridad transversal del framework.

### Cliente

Es un usuario vinculado a una organizacion cliente que puede consultar informacion relacionada con su propia empresa.

En el MVP su participacion es de lectura:

- ingresa al modulo si tiene acceso habilitado;
- consulta tareas realizadas para su organizacion;
- consulta resumentes o indicadores propios;
- visualiza su perfil.

El cliente no debe operar procesos de carga de tareas ni procesos de supervision.

### Administracion tecnica

La administracion tecnica de usuarios, roles, permisos, menu y seguridad general pertenece al framework comun.

No se la trata aqui como actor funcional del negocio de partes.

## Niveles de identidad

El modulo trabaja con dos niveles:

1. la identidad autenticable comun del sistema;
2. la entidad funcional del modulo que interpreta esa identidad como asistente o cliente.

Esta separacion permite que la autenticacion siga un criterio general, mientras que `SistemaPartes` decide quien puede cargar, consultar o supervisar dentro de su propio dominio.

## Reglas funcionales de reconocimiento

### Asistente

Si la identidad autenticada se vincula con un registro funcional de asistente, el usuario opera como asistente dentro del modulo.

### Cliente

Si la identidad autenticada se vincula con un registro funcional de cliente con acceso habilitado, el usuario opera como cliente dentro del modulo.

### Exclusividad esperada

Una misma identidad funcional no deberia quedar configurada simultaneamente como asistente y cliente para el mismo circuito operativo.

Si esa ambiguedad existiera, el sistema deberia tratarla como una configuracion inconsistente a resolver.

## Resultado esperado del login

Cuando el login finaliza correctamente, el modulo debe haber resuelto al menos:

- si el usuario es asistente o cliente;
- si el asistente actua o no como supervisor;
- que universo funcional de datos puede ver;
- que procesos del modulo le corresponden.

Sin esa resolucion, el usuario no deberia ingresar al circuito operativo de `SistemaPartes`.

## Sesion funcional del modulo

Durante la sesion autenticada, el modulo necesita conservar la informacion minima para:

- filtrar consultas;
- condicionar visibilidad de acciones;
- definir el dashboard correcto;
- restringir o habilitar supervision;
- materializar el menu del modulo con coherencia funcional.

## Perfil visible para el usuario

Todo usuario autenticado del modulo debe poder consultar su propio perfil en modo de lectura.

La funcion del perfil no es administrativa. Su funcion es confirmar:

- quien fue reconocido por el sistema;
- bajo que tipo de usuario opera;
- y, cuando aplique, si tiene condicion de supervisor.

## Clientes con acceso habilitado

No todo cliente del modulo tiene necesariamente acceso autenticado.

Debe distinguirse entre:

- cliente como entidad de negocio;
- cliente con acceso al sistema.

Cuando se habilita acceso:

- el cliente sigue siendo un registro funcional del modulo;
- ademas queda vinculado a una identidad autenticable;
- y debe mantenerse coherencia entre ambos lados.

Si el acceso se revoca, esa decision debe reflejarse sin dejar estados intermedios confusos.

## Filtros automaticos por rol

### Cliente

Solo ve informacion asociada a su propia organizacion.

### Asistente no supervisor

Solo ve su propia actividad, salvo pantallas que por definicion no dependan de ese criterio.

### Supervisor

La condicion funcional del usuario autenticado es la primera capa de delimitacion del universo de datos del modulo.

Eso significa que:

- el cliente queda restringido a su propio universo;
- el asistente comun queda restringido a su propia actividad;
- y el supervisor accede al universo supervisor del modulo.

Los permisos de menu o de proceso pueden limitar accesos a pantallas o acciones concretas, pero no reemplazan esta delimitacion funcional primaria.

## Relacion con la seguridad comun

El modulo no redefine:

- login comun;
- logout;
- menu por permisos;
- roles tecnicos;
- seguridad base;
- shell autenticado.

Lo que si define es la **lectura funcional del usuario ya autenticado** dentro del negocio de partes.
