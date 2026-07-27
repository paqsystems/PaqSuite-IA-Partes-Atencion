## Modelo conceptual del modulo

El modulo `SistemaPartes` se organiza alrededor de una entidad central: el **registro de tarea**. Todo el resto del modelo existe para habilitar, clasificar o consultar esos registros.

### Entidades principales

| Entidad | Rol funcional |
|---------|---------------|
| `PQ_PARTES_USUARIOS` | Empleados del modulo que cargan o supervisan tareas |
| `PQ_PARTES_CLIENTES` | Clientes para los que se registran tareas y, eventualmente, consultan resultados |
| `PQ_PARTES_TIPOS_CLIENTE` | Catalogo para clasificar clientes |
| `PQ_PARTES_TIPOS_TAREA` | Catalogo de tipos de tarea, con reglas de genericidad y default |
| `PQ_PARTES_CLIENTE_TIPO_TAREA` | Asignacion de tipos de tarea especificos a clientes |
| `PQ_PARTES_REGISTRO_TAREA` | Registro operativo diario del trabajo realizado |

### Centro del modelo

Cada fila de `PQ_PARTES_REGISTRO_TAREA` representa:

- quien realizo la tarea;
- para que cliente se hizo;
- que tipo de tarea fue;
- cuando ocurrio;
- cuanto duro;
- si fue sin cargo, presencial o cerrada;
- y una descripcion funcional del trabajo realizado.

## Relaciones funcionales relevantes

- un **empleado** puede tener muchas tareas;
- un **cliente** puede recibir muchas tareas;
- un **tipo de tarea** puede ser generico o quedar restringido a clientes especificos;
- la tabla `PQ_PARTES_CLIENTE_TIPO_TAREA` resuelve la relacion muchos a muchos entre cliente y tipo de tarea;
- una tarea siempre referencia exactamente un empleado, un cliente y un tipo de tarea.

## Relacion con la identidad comun del sistema

El modulo distingue entre:

- identidad autenticable del framework (`users`);
- entidad funcional del modulo (`PQ_PARTES_USUARIOS` y `PQ_PARTES_CLIENTES`).

Por eso:

- `PQ_PARTES_USUARIOS.user_id` vincula al empleado con su identidad autenticable;
- `PQ_PARTES_CLIENTES.user_id` permite habilitar acceso de consulta a un cliente;
- el modelo de producto debe leerse siempre junto con `RN-Sistema-Partes.md` y el contexto comun de seguridad.

## Observaciones de diseño

- La separacion entre identidad comun y entidades del modulo evita mezclar autenticacion general con reglas de negocio especificas de partes.
- El flag `supervisor` pertenece al dominio del modulo, no al framework comun.
- El estado `cerrado` pertenece al ciclo de vida de la tarea y no a una regla de seguridad del framework.
- `is_generico` e `is_default` pertenecen a la semantica del catalogo de tipos de tarea y condicionan la registracion operativa.

## Integridad funcional esperada

- no deberia eliminarse un cliente, empleado o tipo referenciado por tareas;
- un cliente con acceso debe poder asociarse a una identidad autenticable sin perder su rol de entidad de negocio;
- un empleado inhabilitado no deberia seguir operando dentro del modulo;
- una tarea cerrada conserva su valor historico, aunque deje de ser editable.

CREATE TABLE [dbo].[PQ_PARTES_CLIENTE_TIPO_TAREA](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[cliente_id] [bigint] NOT NULL,
	[tipo_tarea_id] [bigint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

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
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

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
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

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
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

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
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]


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
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
