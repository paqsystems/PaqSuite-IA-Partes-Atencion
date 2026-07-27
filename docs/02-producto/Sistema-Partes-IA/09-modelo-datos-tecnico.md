# Modelo de datos tecnico

## Objetivo

Este documento concentra la definicion tecnica de las tablas del modulo `SistemaPartes`.

Se separa del resto de la carpeta para que la fuente conceptual principal quede en lenguaje humano, mientras que este archivo conserve el detalle tecnico necesario para:

- entender la estructura fisica;
- revisar relaciones;
- alinear migraciones;
- y validar consistencia del esquema.

## Convenciones generales

- prefijo de tablas del modulo: `PQ_PARTES_`;
- excepcion: la identidad autenticable comun vive fuera del modulo;
- nombres de columnas en `snake_case`;
- tablas pensadas para un contexto MONO, reutilizando la identidad comun del sistema.

## Vista general del esquema

El esquema tecnico del modulo se organiza alrededor de estas tablas:

- `PQ_PARTES_USUARIOS`
- `PQ_PARTES_CLIENTES`
- `PQ_PARTES_TIPOS_CLIENTE`
- `PQ_PARTES_TIPOS_TAREA`
- `PQ_PARTES_CLIENTE_TIPO_TAREA`
- `PQ_PARTES_REGISTRO_TAREA`

## Tablas del modulo

## `PQ_PARTES_USUARIOS`

Representa asistentes del modulo y su capacidad eventual de supervision.

### Campos

| Campo | Tipo | Nulo | Descripcion tecnica |
|-------|------|------|---------------------|
| `id` | `bigint` | No | Clave primaria |
| `user_id` | `bigint` | No | Vinculo con identidad autenticable comun |
| `code` | `nvarchar(50)` | No | Codigo funcional del asistente |
| `nombre` | `nvarchar(255)` | No | Nombre visible |
| `email` | `nvarchar(255)` | Si | Email del asistente |
| `supervisor` | `bit` | No | Marca tecnica de supervision |
| `activo` | `bit` | No | Estado activo |
| `inhabilitado` | `bit` | No | Estado de inhabilitacion |
| `created_at` | `datetime` | Si | Auditoria de alta |
| `updated_at` | `datetime` | Si | Auditoria de modificacion |

## `PQ_PARTES_CLIENTES`

Representa clientes del modulo y, cuando aplique, su vinculacion con acceso autenticado.

### Campos

| Campo | Tipo | Nulo | Descripcion tecnica |
|-------|------|------|---------------------|
| `id` | `bigint` | No | Clave primaria |
| `user_id` | `bigint` | Si | Vinculo opcional con identidad autenticable comun |
| `nombre` | `nvarchar(255)` | No | Nombre o razon social |
| `tipo_cliente_id` | `bigint` | No | FK a tipo de cliente |
| `code` | `nvarchar(50)` | No | Codigo funcional del cliente |
| `email` | `nvarchar(255)` | Si | Email del cliente |
| `activo` | `bit` | No | Estado activo |
| `inhabilitado` | `bit` | No | Estado de inhabilitacion |
| `created_at` | `datetime` | Si | Auditoria de alta |
| `updated_at` | `datetime` | Si | Auditoria de modificacion |

## `PQ_PARTES_TIPOS_CLIENTE`

Catalogo tecnico para clasificar clientes.

### Campos

| Campo | Tipo | Nulo | Descripcion tecnica |
|-------|------|------|---------------------|
| `id` | `bigint` | No | Clave primaria |
| `code` | `nvarchar(50)` | No | Codigo obligatorio del tipo |
| `descripcion` | `nvarchar(255)` | No | Descripcion visible |
| `activo` | `bit` | No | Estado activo |
| `inhabilitado` | `bit` | No | Estado de inhabilitacion |
| `created_at` | `datetime` | Si | Auditoria de alta |
| `updated_at` | `datetime` | Si | Auditoria de modificacion |

## `PQ_PARTES_TIPOS_TAREA`

Catalogo tecnico de tipos de tarea.

### Campos

| Campo | Tipo | Nulo | Descripcion tecnica |
|-------|------|------|---------------------|
| `id` | `bigint` | No | Clave primaria |
| `code` | `nvarchar(50)` | No | Codigo obligatorio del tipo |
| `descripcion` | `nvarchar(255)` | No | Descripcion visible |
| `is_generico` | `bit` | No | Marca de disponibilidad general |
| `is_default` | `bit` | No | Marca de tipo por defecto |
| `activo` | `bit` | No | Estado activo |
| `inhabilitado` | `bit` | No | Estado de inhabilitacion |
| `created_at` | `datetime` | Si | Auditoria de alta |
| `updated_at` | `datetime` | Si | Auditoria de modificacion |

## `PQ_PARTES_CLIENTE_TIPO_TAREA`

Tabla de relacion entre clientes y tipos de tarea no genericos.

### Campos

| Campo | Tipo | Nulo | Descripcion tecnica |
|-------|------|------|---------------------|
| `id` | `bigint` | No | Clave primaria |
| `cliente_id` | `bigint` | No | FK a cliente |
| `tipo_tarea_id` | `bigint` | No | FK a tipo de tarea |
| `created_at` | `datetime` | Si | Auditoria de alta |
| `updated_at` | `datetime` | Si | Auditoria de modificacion |

## `PQ_PARTES_REGISTRO_TAREA`

Tabla central del modulo, donde se persiste cada tarea realizada.

### Campos

| Campo | Tipo | Nulo | Descripcion tecnica |
|-------|------|------|---------------------|
| `id` | `bigint` | No | Clave primaria |
| `usuario_id` | `bigint` | No | FK a asistente propietario |
| `cliente_id` | `bigint` | No | FK a cliente |
| `tipo_tarea_id` | `bigint` | No | FK a tipo de tarea |
| `fecha` | `date` | No | Fecha de proceso o fecha funcional de la tarea |
| `duracion_minutos` | `int` | No | Duracion persistida en minutos |
| `sin_cargo` | `bit` | No | Marca funcional |
| `presencial` | `bit` | No | Marca funcional |
| `observacion` | `nvarchar(max)` | No | Descripcion del trabajo |
| `cerrado` | `bit` | No | Estado funcional de cierre |
| `created_at` | `datetime` | Si | Auditoria de alta |
| `updated_at` | `datetime` | Si | Auditoria de modificacion |

## Relaciones tecnicas esperadas

- `PQ_PARTES_CLIENTES.tipo_cliente_id` -> `PQ_PARTES_TIPOS_CLIENTE.id`
- `PQ_PARTES_CLIENTE_TIPO_TAREA.cliente_id` -> `PQ_PARTES_CLIENTES.id`
- `PQ_PARTES_CLIENTE_TIPO_TAREA.tipo_tarea_id` -> `PQ_PARTES_TIPOS_TAREA.id`
- `PQ_PARTES_REGISTRO_TAREA.usuario_id` -> `PQ_PARTES_USUARIOS.id`
- `PQ_PARTES_REGISTRO_TAREA.cliente_id` -> `PQ_PARTES_CLIENTES.id`
- `PQ_PARTES_REGISTRO_TAREA.tipo_tarea_id` -> `PQ_PARTES_TIPOS_TAREA.id`

Los vinculos con la identidad autenticable comun se expresan mediante `user_id` en asistentes y clientes.

## Observaciones tecnicas relevantes

- `PQ_PARTES_REGISTRO_TAREA` es la tabla de mayor sensibilidad funcional.
- `duracion_minutos` se persiste como entero, aunque la experiencia de usuario pueda expresarlo con formatos mas amigables.
- `cerrado` pertenece al ciclo de vida de la tarea y no a una regla transversal de seguridad.
- `is_generico` e `is_default` sostienen la semantica tecnica de seleccion de tipos de tarea.
- La definicion conceptual adoptada exige un unico tipo de tarea por defecto y que ese tipo por defecto sea generico.

## DDL consolidado

```sql
CREATE TABLE [dbo].[PQ_PARTES_CLIENTE_TIPO_TAREA](
    [id] [bigint] IDENTITY(1,1) NOT NULL,
    [cliente_id] [bigint] NOT NULL,
    [tipo_tarea_id] [bigint] NOT NULL,
    [created_at] [datetime] NULL,
    [updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
    [id] ASC
)
) ON [PRIMARY];

CREATE TABLE [dbo].[PQ_PARTES_CLIENTES](
    [id] [bigint] IDENTITY(1,1) NOT NULL,
    [user_id] [bigint] NULL,
    [nombre] [nvarchar](255) NOT NULL,
    [tipo_cliente_id] [bigint] NOT NULL,
    [code] [nvarchar](50) NOT NULL,
    [email] [nvarchar](255) NULL,
    [activo] [bit] NOT NULL,
    [inhabilitado] [bit] NOT NULL,
    [created_at] [datetime] NULL,
    [updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
    [id] ASC
)
) ON [PRIMARY];

CREATE TABLE [dbo].[PQ_PARTES_REGISTRO_TAREA](
    [id] [bigint] IDENTITY(1,1) NOT NULL,
    [usuario_id] [bigint] NOT NULL,
    [cliente_id] [bigint] NOT NULL,
    [tipo_tarea_id] [bigint] NOT NULL,
    [fecha] [date] NOT NULL,
    [duracion_minutos] [int] NOT NULL,
    [sin_cargo] [bit] NOT NULL,
    [presencial] [bit] NOT NULL,
    [observacion] [nvarchar](max) NOT NULL,
    [cerrado] [bit] NOT NULL,
    [created_at] [datetime] NULL,
    [updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
    [id] ASC
)
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE TABLE [dbo].[PQ_PARTES_TIPOS_CLIENTE](
    [id] [bigint] IDENTITY(1,1) NOT NULL,
    [code] [nvarchar](50) NOT NULL,
    [descripcion] [nvarchar](255) NOT NULL,
    [activo] [bit] NOT NULL,
    [inhabilitado] [bit] NOT NULL,
    [created_at] [datetime] NULL,
    [updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
    [id] ASC
)
) ON [PRIMARY];

CREATE TABLE [dbo].[PQ_PARTES_TIPOS_TAREA](
    [id] [bigint] IDENTITY(1,1) NOT NULL,
    [code] [nvarchar](50) NOT NULL,
    [descripcion] [nvarchar](255) NOT NULL,
    [is_generico] [bit] NOT NULL,
    [is_default] [bit] NOT NULL,
    [activo] [bit] NOT NULL,
    [inhabilitado] [bit] NOT NULL,
    [created_at] [datetime] NULL,
    [updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
    [id] ASC
)
) ON [PRIMARY];

CREATE TABLE [dbo].[PQ_PARTES_USUARIOS](
    [id] [bigint] IDENTITY(1,1) NOT NULL,
    [user_id] [bigint] NOT NULL,
    [code] [nvarchar](50) NOT NULL,
    [nombre] [nvarchar](255) NOT NULL,
    [email] [nvarchar](255) NULL,
    [supervisor] [bit] NOT NULL,
    [activo] [bit] NOT NULL,
    [inhabilitado] [bit] NOT NULL,
    [created_at] [datetime] NULL,
    [updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
    [id] ASC
)
) ON [PRIMARY];
```

## Relacion con documentacion operativa

Para reconstruccion, migraciones, seeders y arranque del entorno, complementar este documento con:

- `docs/backend/SistemaPartes/arranque-base-datos-inicial.md`
