# Producto – MVP Sistema de Registro de Tareas

## Visión del Producto

### Descripción General
Sistema web para consultorías y empresas de servicios que permite a los empleados
registrar las tareas realizadas diariamente, asociándolas a clientes y tipos de tarea,
con el objetivo de analizar la dedicación y facilitar la gestión operativa y comercial.

Este producto corresponde al **MVP del proyecto final** y prioriza simplicidad,
claridad y valor completo en un flujo E2E.

---

## Propósito
- Registrar tareas diarias de forma simple y rápida.
- Obtener visibilidad sobre la dedicación a cada cliente.
- Sentar las bases para futuros módulos de análisis o facturación.

---

## Público Objetivo
- **Empleados:** Consultores, empleados administrativos, equipos de servicios profesionales que registran tareas
- **Clientes:** Empresas/clientes que pueden consultar las tareas realizadas para ellos

---

## Actores y Roles Funcionales

### Empleado

Usuario interno que registra sus tareas diarias y consulta su propia dedicacion.

Opera a partir de una identidad autenticable del framework comun y de su vinculacion funcional con `PQ_PARTES_USUARIOS`.

### Supervisor

Empleado con `supervisor = true`. Conserva todas las capacidades del empleado y, ademas:

- ve tareas de todos los empleados;
- puede crear, editar y eliminar tareas de terceros;
- puede ejecutar el proceso masivo de cerrar o reabrir tareas;
- participa de consultas y dashboard sin filtro restringido al usuario autenticado.

### Cliente

Usuario externo o vinculado a un cliente que puede consultar tareas realizadas para su organizacion en modo solo lectura.

Un cliente solo forma parte de la experiencia autenticada del modulo cuando su registro funcional tiene acceso habilitado mediante vinculacion con `users`.

### Administracion tecnica del sistema

Las capacidades de seguridad, autenticacion, menu, usuarios y permisos pertenecen al framework comun y no se consideran un rol funcional propio del MVP de negocio de `SistemaPartes`.

---

## Características Principales (MVP)

### Funcionalidades

**Para Empleados:**
- Autenticación mediante código de usuario y contraseña
- Conservación de sesión autenticada durante la operatoria del módulo
- Registro de tareas diarias:
  - Fecha
  - Cliente
  - Tipo de tarea
  - Duración
- Edición y eliminación de tareas propias
- Visualización de tareas registradas
- Resumen básico de dedicación por cliente
- Visualización de su propio perfil en modo de consulta

**Para Supervisores (empleados con `supervisor = true`):**
- Todas las funcionalidades de empleados normales
- Visualización de tareas de todos los usuarios
- Creación, edición y eliminación de tareas de cualquier usuario
- Al crear una tarea, puede seleccionar el usuario propietario (lista desplegable, por defecto aparece él mismo)
- Acceso a los mantenimientos maestros del módulo, incluyendo clientes

**Para Clientes:**
- Registro y autenticación (si tienen acceso habilitado)
- Consulta de tareas realizadas para ellos (solo lectura)
- Visualización de resumen de dedicación recibida
- Visualización de su propio perfil en modo de consulta

---

## Alcance Funcional del MVP

### En alcance

- autenticacion de usuarios empleados y clientes habilitados;
- mantenimiento de sesion durante el circuito del modulo;
- visualizacion de perfil del usuario autenticado como consulta de identidad operativa;
- catalogos base del modulo: clientes, empleados, tipos de cliente y tipos de tarea;
- asignacion de tipos de tarea por cliente;
- registro diario de tareas;
- edicion y eliminacion de tareas segun rol y estado;
- consultas detalladas y agrupadas;
- proceso masivo supervisor sobre el estado `cerrado`;
- dashboard inicial y menu lateral del modulo;
- exportacion de consultas cuando el modulo o el framework comun la habiliten.

### Fuera de alcance del MVP

- facturacion automatica;
- integracion con ERP u otros sistemas externos;
- aprobacion formal de tareas;
- workflow avanzado de estados mas alla de `cerrado`;
- roles funcionales de negocio adicionales al supervisor;
- analitica avanzada no esencial para el flujo E2E.

### Aclaracion sobre reportes y dashboard

El MVP si contempla:

- consultas operativas y agrupadas;
- resumenes basicos por cliente y por empleado;
- dashboard funcional de inicio;
- visualizaciones basicas si ayudan a lectura rapida del resultado y no introducen complejidad desproporcionada.

Las consultas del modulo deben apoyarse en las capacidades transversales del framework para:

- presentacion en grilla;
- vista pivot cuando el tipo de analisis lo justifique;
- exportacion a Excel;
- gestion de layouts sobre grillas o pivots cuando la vista lo permita.

Queda fuera de alcance:

- BI avanzado;
- reporteria compleja de multiples capas;
- visualizaciones sofisticadas que no aporten valor directo al flujo principal.

---

## Mapa Funcional del Producto

1. **Acceso y sesion**
   - login;
   - determinacion del tipo de usuario autenticado;
   - conservacion de sesion;
   - visualizacion de perfil;
   - recuperacion y cambio de contrasena;
   - logout.
2. **Maestros**
   - clientes;
   - empleados;
   - tipos de cliente;
   - tipos de tarea;
   - asignacion cliente-tipo de tarea.
3. **Operacion**
   - carga diaria de tareas;
   - edicion y eliminacion;
   - manejo del estado `cerrado`.
4. **Supervision**
   - propietario de tarea;
   - proceso masivo;
   - consultas sin filtro de usuario.
5. **Consulta y analisis**
   - listados;
   - informes agrupados;
   - dashboard.

### Criterio funcional de acceso al modulo

La autenticacion del modulo se apoya en `users`, pero su operatoria depende de la vinculacion funcional posterior:

- si el `user` se vincula con `PQ_PARTES_USUARIOS`, opera como empleado;
- si el `user` se vincula con `PQ_PARTES_CLIENTES`, opera como cliente;
- un mismo `code` no deberia quedar asociado simultaneamente a ambas entidades funcionales;
- el login del modulo debe resolver ese perfil funcional antes de habilitar navegacion y permisos de producto.

### Gestión de clientes con acceso habilitado

El mantenimiento de clientes del modulo no solo administra el catalogo comercial, sino tambien la posibilidad de acceso de determinados clientes al sistema.

Esto implica:

- un cliente puede existir sin acceso autenticado;
- si se habilita acceso, debe existir vinculacion controlada con `users`;
- la creacion y edicion del cliente deben contemplar ese estado funcional;
- los cambios de estado del cliente con acceso deben mantenerse coherentes con su identidad autenticable.

---

## Navegacion del Modulo

`SistemaPartes` debe integrarse al framework comun de navegacion definido en `docs/00-contexto/_mono/`.

Esto implica que:

- el menu lateral del modulo no debe definirse como una estructura aislada del producto;
- las opciones funcionales del modulo deben traducirse a entradas de `pq_menus`;
- la jerarquia visible del menu del producto debe alinearse con el sidebar dinamico comun del sistema.

La estructura funcional del modulo puede describirse en terminos de secciones de negocio, pero su materializacion en UI debe respetar el criterio comun de menu basado en `pq_menus`.

### Estructura funcional sugerida del menu

Como criterio de organizacion funcional del modulo, las opciones deberian agruparse asi:

1. **Inicio**
   - panel o dashboard principal.
2. **Archivos**
   - clientes;
   - empleados;
   - tipos de cliente;
   - tipos de tarea.
3. **Partes**
   - carga de tarea;
   - tareas propias;
   - proceso masivo cuando corresponda por rol.
4. **Informes**
   - consultas detalladas;
   - consultas agrupadas;
   - exportaciones cuando esten habilitadas.

Esta estructura no implica un menu hardcodeado. Es la organizacion funcional que luego debe traducirse a `pq_menus` y respetar visibilidad por rol.

---

## Flujo E2E Prioritario
Login → Registro de tarea → Visualización de resumen.

---

## Roadmap Tentativo (Post-MVP)
- Aprobación de tareas por supervisor.
- Reportes avanzados por período.
- Integración con sistemas de facturación o ERP.
- Exportación de datos.
