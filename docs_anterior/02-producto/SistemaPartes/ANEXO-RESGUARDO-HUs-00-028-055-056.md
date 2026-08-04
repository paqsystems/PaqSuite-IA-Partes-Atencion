# Anexo de resguardo de HUs 00, 028, 055 y 056

## Objetivo

Conservar en un unico documento toda la informacion pertinente identificada en las HUs:

- `HU-00(MH)-Generacion-base-datos-inicial.md`
- `HU-028(MH)-carga-de-tarea-diaria.md`
- `HU-055(SH)-actualización-automática-del-dashboard.md`
- `HU-056(SH)-menú-lateral-de-navegación.md`

Este anexo no reemplaza a `PROD-Sistema-Partes.md`, `RN-Sistema-Partes.md`, `modelo-datos.md` ni a la documentacion comun de `docs/00-contexto/_mono/`.

Su finalidad es dar resguardo documental mientras se decide la optimizacion final, la depuracion de HUs historicas y la eventual regeneracion futura de artefactos.

## Alcance de este resguardo

Se preserva aqui:

- el contenido funcional u operativo que aparece en esas HUs;
- criterios de aceptacion concretos que todavia pueden ser utiles;
- decisiones de transicion que ayudan a no perder contexto;
- observaciones sobre dependencias, anomalias o tensiones con la documentacion nueva.

No se intenta en este documento normalizar redaccion, eliminar duplicaciones ni decidir la estructura final definitiva.

## Estado actual de referencia

Al momento de crear este anexo, buena parte del contenido de estas HUs ya fue absorbido en:

- `docs/backend/SistemaPartes/arranque-base-datos-inicial.md`
- `docs/02-producto/SistemaPartes/PROD-Sistema-Partes.md`
- `docs/02-producto/SistemaPartes/RN-Sistema-Partes.md`
- `docs/02-producto/SistemaPartes/modelo-datos.md`
- `docs/02-producto/SistemaPartes/INDICE-Sistema-Partes.md`
- `docs/00-contexto/_mono/`

Por eso este archivo debe leerse como un respaldo de seguridad documental y no como fuente primaria ideal.

---

## HU-00 - Generacion de base de datos inicial

### Tipo de informacion preservada

HU tecnica habilitadora de infraestructura. Su valor principal no esta en reglas de negocio sino en dejar explicitado el piso tecnico minimo para habilitar el desarrollo del modulo.

### Contenido pertinente conservado

#### Proposito

- La base inicial debe poder generarse de forma consistente, versionada y reproducible.
- Debe habilitar desarrollo, prueba, validacion del MVP y reconstruccion completa desde repositorio.
- Debe ejecutarse antes de las historias funcionales del sistema.

#### Alcance operativo

- Generacion del esquema completo de base de datos a partir del modelo definido.
- Uso de MCP de SQL Server para ejecucion o verificacion controlada.
- Generacion de migraciones Laravel versionadas con `up()` y `down()`.
- Aplicacion de convenciones del proyecto:
  - prefijo `PQ_PARTES_` para tablas del modulo;
  - excepcion de `USERS`;
  - nombres en `snake_case`;
  - indices con prefijo `idx_`.
- Generacion de seeders minimos para testing y validacion inicial.
- Verificacion de reconstruccion del entorno desde cero.
- Documentacion del proceso de creacion y ejecucion.

#### Seeders minimos explicitados por la HU

- un usuario administrador o supervisor;
- un cliente de prueba;
- un tipo de cliente;
- un tipo de tarea generico con `is_default = true`.

#### Suposiciones tecnicas explicitas

- el modelo ya estaba definido y validado;
- el motor es SQL Server;
- el entorno dispone de MCP `mssql-toolbox` o `mssql`;
- Laravel esta listo para generar migraciones.

#### Criterios de aceptacion preservados

- la base objetivo es `Lidr`;
- la base puede generarse completamente desde cero;
- el esquema implementa tablas, campos, indices y claves foraneas del modelo;
- existen migraciones con rollback;
- la ejecucion puede pasar por Laravel y por MCP;
- existen seeders minimos para tests;
- no se requieren pasos manuales fuera del repositorio y la configuracion del entorno;
- queda documentado el proceso;
- existe evidencia verificable para validacion del MVP.

### Observaciones de resguardo

- La HU referencia `docs/modelo-datos.md`, mientras que la documentacion vigente del modulo hoy esta en `docs/02-producto/SistemaPartes/modelo-datos.md`.
- Este contenido ya fue absorbido operativamente en `docs/backend/SistemaPartes/arranque-base-datos-inicial.md`, pero aqui se preserva la formulacion completa de la HU tecnica.
- Sigue siendo util como respaldo mientras se termina de dejar totalmente explicita la separacion entre esquema propio del modulo y esquema comun MONO del framework.

---

## HU-028 - Carga de tarea diaria

### Tipo de informacion preservada

HU funcional central del MVP. Conserva criterios de aceptacion muy concretos de captura, validacion, UX y seguridad de acceso del proceso de registro diario de tareas.

### Contenido pertinente conservado

#### Proposito funcional

- Un empleado registra una tarea realizada indicando fecha, cliente, tipo de tarea, duracion y descripcion.
- El objetivo es dejar constancia del trabajo efectuado.

#### Usuarios habilitados

- Empleado.
- Empleado supervisor.
- El cliente no debe poder operar este proceso.

#### Acceso y visibilidad

- El boton `Cargar Tarea` solo debe ser visible para empleados.
- La ruta debe estar protegida.
- Si un cliente intenta acceder, debe redirigirse al dashboard.

#### Campos del formulario preservados por la HU

- `fecha`
  - obligatoria;
  - carga en formato `DD/MM/YYYY`;
  - por defecto fecha actual;
  - con autoformato de barras.
- `cliente`
  - obligatorio;
  - selector.
- `tipo de tarea`
  - obligatorio;
  - selector.
- `duracion`
  - obligatoria;
  - entrada amigable en `hh:mm`;
  - ejemplo `02:30 = 150` minutos;
  - con autoformato de dos puntos.
- `sin_cargo`
  - checkbox;
  - default `false`.
- `presencial`
  - checkbox;
  - default `false`.
- `observacion` o descripcion
  - obligatoria;
  - `textarea`.

#### Comportamiento para supervisor

- Si el usuario es supervisor, puede seleccionar el empleado propietario de la tarea.
- Ese selector debe iniciar por defecto con el propio supervisor.
- Si selecciona otro empleado, el sistema debe validar existencia y estado habilitado.

#### Reglas de selectores

- El selector de clientes solo muestra clientes activos y no inhabilitados.
- El selector de tipos de tarea muestra:
  - todos los tipos genericos (`is_generico = true`) activos y no inhabilitados;
  - los tipos no genericos asignados al cliente seleccionado, tambien activos y no inhabilitados.

#### Reglas de validacion preservadas

- La fecha no puede estar vacia.
- En frontend la fecha debe validarse en formato `DD/MM/YYYY`.
- Antes de enviar al backend, la fecha debe convertirse a `YYYY-MM-DD`.
- El backend valida fecha en formato `YMD`.
- Una fecha futura genera advertencia, pero no bloqueo.
- El cliente debe estar seleccionado, existir, estar activo y no inhabilitado.
- El tipo de tarea debe estar seleccionado, existir, estar activo y no inhabilitado.
- El tipo de tarea debe ser generico o estar asignado al cliente seleccionado.
- La duracion debe ser mayor a cero.
- La duracion se ingresa en `hh:mm` y se convierte a minutos.
- La duracion debe estar en tramos de 15 minutos.
- La duracion no puede superar `1440` minutos.
- `observacion` no puede estar vacia.
- `sin_cargo` y `presencial` no deben quedar en `null`.

#### Reglas funcionales sintetizadas por la HU

- La duracion se almacena en minutos.
- La fecha se almacena en formato tecnico distinto al de presentacion.
- Los selectores deben excluir registros inhabilitados.
- La visibilidad de tipos de tarea responde a la regla "genericos + asignados al cliente".
- Solo empleados y supervisores pueden operar la carga.

#### Resultado esperado del guardado

- Se crea el registro en base de datos.
- El registro queda asociado al usuario autenticado o al empleado seleccionado por supervisor.
- Se muestra mensaje de confirmacion.
- El formulario se limpia o el usuario vuelve a la lista.

#### Dependencias preservadas en la HU

- `HU-001` autenticacion.
- `HU-009` clientes.
- `HU-024` tipos de tarea.
- `HU-012` asignacion de tipos de tarea a cliente.

### Observaciones de resguardo

- Gran parte de estas reglas ya vive en `RN-Sistema-Partes.md`, pero la HU seguia siendo valiosa porque las presentaba integradas en un unico flujo de captura.
- Se conserva especialmente la combinacion de reglas de acceso, campos, validaciones y comportamiento post-guardado.

---

## HU-055 - Actualizacion automatica del dashboard

### Tipo de informacion preservada

HU funcional complementaria del dashboard. Su valor esta en fijar un comportamiento de refresco automatico y manual respetando filtros por rol.

### Contenido pertinente conservado

#### Proposito funcional

- El usuario quiere ver informacion actualizada en el dashboard sin depender de recarga completa de pagina.

#### Usuarios alcanzados

- Empleado.
- Empleado supervisor.
- Cliente.

#### Criterios de aceptacion preservados

- El dashboard puede actualizarse automaticamente cada cierto intervalo.
- El intervalo es configurable.
- Se contempla como ejemplo un refresco cada `5` minutos.
- Se muestra un indicador de ultima actualizacion.
- Existe un boton o accion de actualizacion manual.
- Durante la actualizacion se muestra indicador de carga.
- El refresco ocurre sin recargar toda la pagina.

#### Filtros automaticos por rol preservados

- Cliente:
  - solo ve tareas cuyo `cliente_id` coincide con su identidad funcional.
- Empleado no supervisor:
  - solo ve tareas cuyo `usuario_id` coincide con su identidad funcional.
- Supervisor:
  - puede ver todas las tareas.

#### Notas funcionales preservadas

- La actualizacion automatica es opcional.
- Puede deshabilitarse segun criterio UX.
- Los filtros por rol se aplican tambien durante refrescos automaticos y manuales.

#### Dependencia preservada

- `HU-051` dashboard principal.

### Observaciones de resguardo

- La parte util y propia de esta HU llega hasta su seccion funcional inicial.
- En el archivo original aparece luego un anexo masivo de resumen de HUs y tickets tecnicos que no parece pertenecer organicamente a `HU-055`.
- Ese anexo no se replica aqui como contenido funcional de la HU, pero se deja expresamente mencionado para no perder trazabilidad sobre la anomalia documental.
- Las reglas funcionales principales ya fueron absorbidas en `RN-Sistema-Partes.md`, pero este anexo conserva la formulacion puntual original.

---

## HU-056 - Menu lateral de navegacion

### Tipo de informacion preservada

HU de navegacion y transicion de UX. Su valor principal no es solo definir que exista un menu lateral, sino registrar el pasaje desde accesos por botones en dashboard hacia una navegacion lateral persistente.

### Contenido pertinente conservado

#### Proposito funcional

- El usuario debe poder acceder a procesos y pantallas desde un menu lateral izquierdo.
- Se busca una navegacion mas clara y constante que la basada en botones dentro del dashboard.

#### Usuarios alcanzados

- Empleado.
- Empleado supervisor.
- Cliente.

#### Criterios de aceptacion preservados

- Existe un menu lateral fijo en la izquierda, junto al header comun.
- El menu debe estar visible en todas las pantallas autenticadas.
- Las opciones hoy expuestas como botones en dashboard se reubican como items del menu lateral.
- Cada item del menu muestra el nombre del proceso o pantalla destino.
- Las opciones visibles dependen del rol del usuario.
- El menu debe ser colapsable o adaptable en pantallas pequenas.
- Debe existir estado activo o resaltado de la opcion actual.
- El dashboard deja de mostrar el bloque de accesos rapidos tipo `welcome-card-actions`.
- El dashboard queda mas centrado en resumen ejecutivo.
- `Panel` o `Inicio` puede mantenerse como opcion inicial.
- Debe conservarse trazabilidad de `data-testid` o equivalente para E2E.

#### Reglas de negocio o transicion preservadas

- Los destinos y permisos por rol deben mantenerse coherentes con lo que antes exponia el dashboard.
- Si no hay un orden definido, el equipo puede proponer uno logico.

#### Orden de presentacion explicitado por la HU

1. Inicio.
2. Separador.
3. Archivos:
   - clientes;
   - empleados;
   - tipos de clientes;
   - tipos de tareas.
4. Separador.
5. Partes:
   - carga de tareas;
   - mis tareas;
   - procesamiento masivo.
6. Separador.
7. Informes:
   - todos los informes definidos.

#### Dependencia preservada

- `HU-051` dashboard y rutas actuales.

### Observaciones de resguardo

- Parte de esta HU ya fue reinterpretada correctamente en `PROD-Sistema-Partes.md` bajo el criterio de `pq_menus`.
- Sin embargo, esta HU seguia reteniendo detalles de transicion de UI que no estaban completamente documentados en otro lugar:
  - reemplazo explicito de botones del dashboard;
  - conservacion de `data-testid`;
  - idea de menu fijo en todas las pantallas autenticadas;
  - criterio de orden visual de secciones.
- Se preserva por eso como respaldo de requerimiento funcional y no como definicion final de implementacion.

---

## Notas finales de tranquilidad documental

Para el objetivo inmediato de no perder informacion relevante de estas cuatro HUs, este anexo conserva:

- la HU tecnica de arranque de base;
- la HU central de carga diaria;
- la HU de refresco de dashboard;
- la HU de transicion hacia menu lateral.

Con esto queda resguardado en un solo lugar el contenido mas util que todavia podia preocupar antes de seguir optimizando, depurando o eventualmente eliminando las HUs originales.

## Uso recomendado de este anexo

Mientras no se cierre la depuracion final, conviene usar este documento como respaldo de chequeo cuando se quiera verificar:

- si una regla de captura estaba en una HU y no se quiere perder;
- si un criterio de arranque tecnico estaba formulado solo en una HU;
- si una decision de navegacion o dashboard estaba aun en transicion;
- si una HU original puede archivarse con mas tranquilidad.
