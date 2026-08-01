# Vision y alcance

## Proposito del modulo

`SistemaPartes` es un modulo para registrar el trabajo realizado por asistentes sobre clientes concretos, con el objetivo de dejar trazabilidad operativa y permitir luego su consulta, supervision y analisis.

El corazon del modulo es el **registro diario de tareas**. Todo lo demas existe para habilitar, organizar, controlar o explotar esa informacion.

## Problema que resuelve

El modulo busca resolver una necesidad simple y recurrente:

- saber que trabajo se hizo;
- para que cliente se hizo;
- quien lo hizo;
- cuanto tiempo implico;
- y poder consultar luego esa dedicacion de manera confiable.

Sin este registro, la organizacion pierde visibilidad sobre su dedicacion operativa y limita su capacidad de analisis, supervision o futura facturacion.

## Objetivos del MVP

En su version MVP, `SistemaPartes` debe:

- permitir la carga diaria de tareas desde una grilla de trabajo clara y rapida;
- conservar el historial de lo registrado;
- distinguir entre operatoria propia y operatoria de supervision;
- permitir consultas operativas y agrupadas;
- ofrecer un dashboard inicial util;
- dejar una base ordenada para futuras capacidades de control, costeo o facturacion.

## Flujo E2E prioritario

El flujo minimo que justifica valor completo en el MVP es:

1. el usuario inicia sesion;
2. el sistema resuelve su perfil funcional dentro del modulo;
3. el usuario registra una tarea;
4. el usuario consulta sus tareas o un resumen de dedicacion.

Ese flujo debe mantenerse estable antes de ampliar el alcance con funciones adicionales.

## Que incluye el MVP

### Acceso al modulo

- ingreso de asistentes;
- ingreso de clientes habilitados;
- mantenimiento de sesion durante la operatoria;
- consulta del perfil autenticado.

### Maestros del modulo

- clientes;
- asistentes;
- tipos de cliente;
- tipos de tarea;
- asignacion de tipos de tarea a cliente.

### Operacion

- carga diaria de tareas desde una grilla previamente filtrada;
- edicion y eliminacion de tareas segun rol;
- control del estado `cerrado`;
- seleccion de asistente propietario para supervisores.

### Supervision

- vista sobre tareas de terceros;
- proceso masivo: filtrar/seleccionar tareas y aplicar cambios en lote (atributos permitidos y/o estado `cerrado`), con grilla del framework (totales, filtro por columna, plantillas, exportacion);
- acceso a consultas sin filtro por usuario propio.

### Consulta y analisis

- consulta detallada de tareas;
- consultas agrupadas;
- dashboard inicial con indicadores utiles por rol.

## Que no forma parte del MVP

Quedan fuera del MVP, salvo decision explicita posterior:

- facturacion automatica;
- integracion con ERP;
- aprobacion formal de tareas;
- workflow complejo de estados;
- analitica avanzada de tipo BI;
- automatizaciones sofisticadas de IA;
- exportacion entendida como capacidad obligatoria dentro del MVP base;
- procesos de auditoria o importacion masiva aun no cerrados conceptualmente.

## Relacion con el framework comun

Este modulo no redefine los mecanismos generales del framework. Los reutiliza.

Por lo tanto:

- el sistema de menu debe integrarse con `pq_menus`;
- la experiencia autenticada debe vivir dentro del shell comun;
- las consultas deben apoyarse en grillas, pivots, exportaciones y layouts provistos por el framework cuando corresponda;
- las capacidades de seguridad base siguen siendo transversales y no especificas de `SistemaPartes`.

## Valor esperado del modulo

Cuando el modulo este correctamente implementado, la organizacion deberia poder responder con facilidad:

- que tareas se hicieron en un periodo;
- cuanto tiempo se dedico a cada cliente;
- que trabajo hizo cada asistente;
- que tareas siguen abiertas para correccion;
- y que fotografia resumida muestra el dashboard para cada tipo de usuario.
