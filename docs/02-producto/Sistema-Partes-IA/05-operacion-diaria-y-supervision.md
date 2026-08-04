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
- marca **`es_tarea`** (siempre `true` en este proceso: es una tarea de carga, no una compra de horas);
- y, cuando corresponda, su estado `cerrado`.

### Forma operativa del proceso

La carga diaria no debe resolverse como un formulario aislado de una sola tarea.

La definicion adoptada es que el proceso se realiza desde una **grilla de trabajo previamente filtrada**, desde la cual se puede:

- insertar registros;
- editar registros;
- eliminar registros.

Como criterio funcional minimo, la grilla se abre sobre un contexto previamente acotado. Ese contexto debe incluir al menos filtros funcionales suficientes para que la carga sea ordenada y util para el usuario.

**Filtro implicito `es_tarea`:** la carga diaria **solo lista y opera** registros con `es_tarea = true`. Al **grabar** (alta o edicion) una tarea desde este proceso, el sistema **asigna siempre `es_tarea = true`**.

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

- la tarea debe tener una **fecha de proceso**, entendida como la fecha funcional a la que corresponde el trabajo registrado;
- esa fecha de proceso es parte central del significado del registro y no un mero dato tecnico de carga;
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
- el tramo es **parametrizable** en `PQ_PARAMETROS_GRAL` (**default 15** minutos);
- en la experiencia de carga web, la duracion se captura con **selector de tramos en formato `hh:mm`** (el valor tecnico persistido sigue siendo minutos enteros);
- en la grilla de carga diaria, las celdas de duracion se presentan en **`hh:mm`** y la sumatoria usa **horas decimales** (`minutos / 60`) para totalizar en el pie DevExtreme;
- la API y la base de datos no cambian: continuan en `duracion_minutos`.

### Observacion

- la descripcion del trabajo es parte esencial del valor del registro;
- no deberia tratarse como un dato secundario o descartable.

### Marcas funcionales

- `sinCargo` define que la tarea no se considera con cargo;
- `presencial` define que la tarea implico presencia fisica;
- ambas marcas forman parte del significado del registro y no simples adornos de UI;
- en la grilla de carga diaria ambas columnas estan **disponibles** (visibles por defecto; ocultables con el selector de columnas).

### Presentacion de cliente y tipo en grilla

- la columna **Cliente** muestra la **descripcion/nombre** del cliente (no el codigo como valor principal);
- la columna **Tipo de tarea** muestra la **descripcion** del tipo (no el codigo como valor principal);
- los codigos permanecen disponibles via column chooser para quien los necesite;
- los selectores de formulario siguen mostrando `codigo — descripcion`.

## Complemento por IA

La asistencia por IA no reemplaza el proceso de carga.

Su funcion esperada es **complementar** la carga manual, por ejemplo completando o proponiendo datos dentro de la misma pantalla o grilla de registracion.

La confirmacion final del registro sigue perteneciendo al circuito normal del usuario.

## Importación de partes desde Excel

Además de la carga interactiva en grilla, el módulo define un proceso de **alta masiva desde Excel** ubicado en la rama **Carga de Partes**.

Reglas canónicas (plantilla de columnas, forzar `es_tarea = true`, pregunta al usuario si hay filas válidas y con error): ver [`13-importacion-partes-excel.md`](./13-importacion-partes-excel.md).

Ese proceso **no** sustituye al proceso masivo de supervisión (edición/cierre en lote sobre tareas ya existentes).

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

El proceso masivo es una herramienta de **supervision** sobre tareas **ya existentes**.

No es un alta de tareas ni un reemplazo de la carga diaria: es un circuito para **localizar un conjunto**, **seleccionarlo** y **aplicar cambios en lote** (atributos y/o estado `cerrado`).

### Que ya forma parte del proceso (descubrimiento y seleccion)

El supervisor debe poder:

1. **Filtrar** el universo de tareas con:
   - **periodo** (obligatorio);
   - **cliente** (opcional);
   - **asistente** (opcional);
   - **estado** / cerrado (opcional).
2. **Ver** el resultado en una grilla de trabajo.
3. **Tildar** los registros sobre los que actuara (seleccion explicita), incluyendo la opcion de seleccionar **todos** los visibles / del conjunto filtrado, segun el patron de grilla del framework.
4. Ejecutar la accion solo sobre esa seleccion (nunca sobre una seleccion vacia o invalida).

Esta etapa de filtro + listado + seleccion es el nucleo ya esperado del proceso.

**Filtro implicito `es_tarea`:** el proceso masivo **solo lista y opera** registros con `es_tarea = true` (tareas de carga). No incluye compras de horas (`es_tarea = false`).

### Grilla del proceso (capacidades del framework)

La grilla del proceso masivo debe reutilizar las capacidades comunes de grilla del framework (`ProcessDataGrid` / plantillas GEN), no una grilla “a medida” incompleta.

En particular debe incorporar:

- **fila de filtrado** bajo los titulos de columna;
- **totalizacion** (sumatorias / agregados que aporten valor en el pie, tipicamente sobre duracion cuando la columna este presente);
- **seleccion de campos** (column chooser): mostrar u ocultar columnas;
- **plantillas** de layout de grilla (guardar / aplicar / ultimo usado, segun el contrato comun);
- **exportacion a Excel** del conjunto presentado en la grilla.

Estas capacidades se declaran aqui como **requerimiento de producto del proceso**; la implementacion concreta sigue las normas del framework, sin redefinirlas en este modulo.

### Acciones sobre la seleccion

Sobre los registros tildados, el supervisor debe poder aplicar cambios **masivos**.

#### A) Actualizacion masiva de atributos

El proceso debe permitir modificar en lote **uno o mas atributos permitidos**, aplicando el mismo valor elegido a todos los registros seleccionados.

**Prioridad inmediata (must del proceso):**

| Atributo | Notas |
|----------|--------|
| **Tipo de tarea** | Debe respetar reglas de tipos validos / habilitados y la relacion con el cliente de cada tarea (no forzar un tipo invalido para el cliente de un registro). |
| **Sin cargo** | Marca booleana del dominio. |

**Atributos factibles en el mismo circuito (should / siguientes):**

| Atributo | Notas |
|----------|--------|
| **Presencial** | Marca booleana del dominio. |
| **Asistente** | Cambio de propietario funcional; solo supervisor. |
| **Fecha** | Fecha de proceso de la tarea. |

**Atributos excluidos de la edicion masiva** (no entran en este proceso):

| Atributo | Motivo funcional |
|----------|------------------|
| **Cliente** | Cambio de cliente altera el significado del registro y la validez del tipo; queda fuera del lote. |
| **Minutos / duracion** | Ajuste individual de tiempo; no forma parte del masivo. |
| **Descripcion / observacion** | Texto libre por tarea; no se homogeniza en lote. |

La UI debe dejar claro **que atributo(s)** se van a modificar y con **que valor**, antes de confirmar.

#### B) Estado `cerrado` (cerrar / reabrir)

Sigue siendo una accion valida del proceso masivo: marcar o desmarcar `cerrado` sobre la seleccion, con las mismas reglas de supervision y consistencia del dominio.

### Rasgos transversales

- solo corresponde a **supervisores**;
- trabaja sobre una **seleccion explicita** de registros;
- requiere **filtros previos** (periodo obligatorio) para acotar el conjunto;
- debe mostrar con claridad **que se va a procesar** (cantidad, accion, valores);
- no debe ejecutarse sobre seleccion vacia o invalida;
- el resultado debe quedar **inmediatamente reflejado** en la grilla / para el usuario;
- las validaciones de negocio de cada atributo (tipos habilitados, propiedad, cerrado, etc.) siguen vigentes en el lote: un registro que no pueda recibir el cambio no debe “pasar en silencio” de forma confusa.

### Criterio de atomicidad

Cuando el proceso masivo afecte varios registros, la expectativa funcional es que no deje resultados parciales confusos si el conjunto no puede procesarse correctamente.

Si en una corrida algunos registros fallan validacion (por ejemplo tipo incompatible con el cliente de esa fila), el contrato vigente ([SPEC-005](../../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md)) es **bloqueo total del lote** (cero cambios; mensaje claro), evitando un exito aparente parcial.

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
