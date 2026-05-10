# BASE DE DATOS : PQ_DICCIONARIO

> **Diagramas Mermaid:** Ver `md-seguridad-diagramas.md` en esta misma carpeta (módulos SEGURIDAD, GRUPOS EMPRESARIOS — referencia MULTI —, REPORTES, etc.).

## Instalación **MONO** (este producto)

**PaqSuite-IA-Partes-Atencion** se despliega en modo **mono-empresa / mono-contexto**: **no** hay tabla operativa **`PQ_Empresa`**, **no** hay cabecera **`X-Company-Id`** ni “empresa activa”. La asignación de permisos es **usuario ↔ rol** (`Pq_Permiso`) **sin** dimensión empresa en reglas de negocio ni UI.

- **`users`** y el resto del esquema de seguridad viven en la **base DICCIONARIO** (esquema único de la instalación).
- Cualquier DDL o texto que mencione **Company DB por empresa**, **`PQ_Empresa`**, **`IDEmpresa`** en permisos o **grupos empresarios** se conserva como **referencia MULTI / legado ERP** o para migraciones, **no** como modelo operativo obligatorio de MONO.

---

## Tablas no aplicadas (por el momento)

| Tabla | Propósito | Motivo |
|-------|-----------|--------|
| `users_identities` | Identidades externas (Google, Microsoft, etc.) vinculadas a usuarios | No se implementa login social/externo en este proyecto; solo autenticación local (código + contraseña). |

---

CREATE TABLE [dbo].[users](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[codigo] [nvarchar](20) NOT NULL UNIQUE,			-- còdigo unico identificador usuario
	[name_user] [nvarchar](255) NOT NULL,				-- nombre y apellido del usuario
	[email] [nvarchar](255) NOT NULL UNIQUE,			-- email del usuario
	[password_hash] [nvarchar](255) NULL,				-- contraseña (encriptada)
	[first_login] [bit] NOT NULL,						-- 1er login (debe cambiarlo obligatoriamente si es verdad)
	[supervisor] [bit] NOT NULL,						-- si es de tipo SUPERVISOR (atributos especiales)
	[activo] [bit] NOT NULL,							-- si està activo (indispensable para poder acceder)
	[inhabilitado] [bit] NOT NULL,						-- si està inhabilitado (impide acceso al sistema)
	[token] [nvarchar](255) NULL,						-- validaciòn acceso durante el uso de APIs del sistema
	[menu_abrir_nueva_pestana] [bit] NOT NULL DEFAULT 0,	-- 0 = misma pestaña, 1 = nueva pestaña (HU-003, solo frontend web)
	[locale] [varchar](10) NULL,						-- idioma preferido: 'es', 'en', etc. NULL = usar idioma del navegador (HU-004)
	[created_at] [datetime] NULL,						-- fecha de creaciòn del usuario
	[updated_at] [datetime] NULL,						-- fecha ultima modificaciòn del usuario
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

-- NOTA: users_identities NO se aplica por el momento. Ver sección "Tablas no aplicadas" al inicio.
CREATE TABLE [dbo].[users_identities](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id] [bigint] NOT NULL,                        -- id de usuario al que pertenece
    provider [varchar](20) NOT NULL,                    -- (google, microsoft, local)
    provider_subject [varchar](100) NOT NULL,           -- codigo del provider
    created_at [datetime] NULL

CREATE TABLE [dbo].[pq_menus](
	[id] [int] NOT NULL,
	[text] [nvarchar](150) NOT NULL,					-- descripciòn que se visualiza en pantalla
	[expanded] [bit] NOT NULL,							
	[Idparent] [int] NOT NULL,							-- quièn es el id padre (0 -> es de la rama principal)
	[order] [smallint] NOT NULL,						-- orden de ubicaciòn dentro de un mismo padre
	[tipo] [char](3) NOT NULL,							-- ABM / INF / ...
	[procedimiento] [nvarchar](150) NOT NULL,			-- nombre para vincular procesos internos (APIs, Reportes, etc)
	[enabled] [bit] NOT NULL,							-- habilitado 
	[routeName] [varchar](50) NULL,						
	[estructura] [int] NULL,
 CONSTRAINT [PK_pq_menus] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UQ_pq_menus_parent_order] UNIQUE ([Idparent], [order])
) ON [PRIMARY]
GO

/* -----------------------------------------------------------------------------
   PQ_Empresa — REFERENCIA MULTI / LEGADO (no operativo en MONO)
   En este producto NO se usa para login, menú ni permisos. Tema visual por
   usuario: preferencia en `users` (ver HUs de apariencia), no `theme` por empresa.
   ----------------------------------------------------------------------------- */
CREATE TABLE [dbo].[PQ_Empresa](
	[IDEmpresa] [int] IDENTITY(1,1) NOT NULL,
	[NombreEmpresa] [varchar](100) NOT NULL,			-- Nombre de la empresa visual al usuario (MULTI)
	[NombreBD] [varchar](100) NOT NULL,					-- nombre técnico de la base de datos (MULTI: Company DB)
	[Habilita] [int] NULL,								-- si está habilitada para usar
	[imagen] [varchar](100) NULL,						-- imagen o icono en menú principal
	[theme] [varchar](100) NOT NULL,					-- MULTI: tema por empresa; MONO: no usar — tema por usuario en `users`
 CONSTRAINT [PK_PQ_EMPRESA] PRIMARY KEY CLUSTERED 
(
	[IDEmpresa] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[Pq_Rol](
	[IDRol] [int] IDENTITY(1,1) NOT NULL,
	[NombreRol] [varchar](100) NULL,					-- Còdigo de rol
	[DescripcionRol] [varchar](100) NULL,				-- Nombre descriptivo del rol
	[AccesoTotal] [bit] NULL,							-- si accede a todas las opciones del sistema (Supervisor)
	/*[BuscarMaximo] [bit] NULL,
	[FiltroVertical] [bit] NULL,
	[CantRegistroBuscar] [int] NULL,
	[CantRegistroMultidimensional] [int] NULL,
	[TamanioReporte] [int] NULL,
	[Exportable] [bit] NULL,*/
 CONSTRAINT [PK_PQ_ROL] PRIMARY KEY CLUSTERED 
(
	[IDRol] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[PQ_RolAtributo](
	[IDRol] [int] NOT NULL,								-- idem PQ_ROL.IdRol
	[IDOpcionMenu] [int] NOT NULL,						-- idem PQ_MENU.Id
	[IDAtributo] [int] NOT NULL,
	[Permiso_Alta] [bit] NOT NULL DEFAULT 0,			-- si tiene acceso a agregar en caso que Pq_menu.Tipo = ABM
	[Permiso_Baja] [bit] NOT NULL DEFAULT 0,			-- si tiene acceso a eliminar (màs allà de restricciones para ello) en caso que Pq_menu.Tipo = ABM
	[Permiso_Modi] [bit] NOT NULL DEFAULT 0,			-- si tiene acceso a modificar en caso que Pq_menu.Tipo = ABM
	[Permiso_Repo] [bit] NOT NULL DEFAULT 0,			-- si tiene acceso a emitir informes o exportar archivos en caso que Pq_menu.Tipo = ABM / INFO
 CONSTRAINT [PK_ROLATRIBUTO] PRIMARY KEY CLUSTERED 
(
	[IDRol] ASC,
	[IDOpcionMenu] ASC,
	[IDAtributo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[Pq_Permiso](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[IDRol] [int] NOT NULL,								-- Idem PQ_Rol.IdRol
	[IDEmpresa] [int] NOT NULL,							-- MULTI: FK lógica a PQ_Empresa. MONO: columna legada; valor fijo único de instalación (ej. 1); no participa en API/UI ni en validación de menú
	[IDUsuario] [int] NOT NULL,							-- Idem users.id
 CONSTRAINT [PK_PQ_PERMISO] PRIMARY KEY CLUSTERED 
(
	[IDRol] ASC,
	[IDEmpresa] ASC,
	[IDUsuario] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[pq_grid_layouts](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id] [bigint] NOT NULL,							-- Usuario que creó el layout (solo él puede modificarlo/eliminarlo)
	[proceso] [varchar](150) NOT NULL,					-- Identificador del proceso/pantalla (ej. pq_menus.procedimiento: "Clientes", "Empleados")
	[grid_id] [varchar](50) NOT NULL DEFAULT 'default',	-- Identificador del grid cuando hay varios en la misma pantalla ("default", "master", "detalle")
	[layout_name] [varchar](100) NOT NULL,				-- Nombre del layout (puede repetirse entre usuarios)
	[layout_data] [nvarchar](max) NULL,					-- JSON: columnas visibles, orden, filtros, agrupaciones, ordenamiento, totalizadores
	[is_default] [bit] NOT NULL DEFAULT 0,				-- 1 = layout por defecto para ese proceso+grid_id (por usuario o global según diseño)
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
 CONSTRAINT [PK_pq_grid_layouts] PRIMARY KEY CLUSTERED ([id] ASC),
 CONSTRAINT [FK_pq_grid_layouts_users] FOREIGN KEY ([user_id]) REFERENCES [dbo].[users]([id])
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_pq_grid_layouts_proceso_grid] ON [dbo].[pq_grid_layouts]([proceso], [layout_name], [grid_id])
GO

/* PQ_GrupoEmpresario* — REFERENCIA MULTI / legado; no aplica en MONO */
CREATE TABLE [dbo].[PQ_GrupoEmpresario](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[descripcion] [varchar](100) NOT NULL,
 CONSTRAINT [PK_PQ_GrupoEmpresario] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[PQ_GrupoEmpresario_Empresas](
	[id_grupo] [bigint] NOT NULL,							-- Idem PQ_GrupoEmpresario.id
	[id_empresa] [bigint] NOT NULL							-- MULTI: referencia a empresa legado; MONO: sin uso operativo
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[PQ_REPORTE_IA](
	[Id] [int] IDENTITY(1,1) NOT NULL,
	[procedimiento] [nvarchar](150) NOT NULL,					-- Idem Pq_menu.procedimiento
	[Name] [nvarchar](max) NULL,
	[DisplayName] [text] NULL,
	[LayoutData] [varbinary](max) NULL,
	[Usuario] [text] NULL,										-- Idem users.id (null -> todos los usuarios)
	[Empresa] [text] NULL,										-- MULTI: restricción por empresa / ID legado. MONO: sin uso operativo; NULL o ignorar
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[Proceso] [text] NULL,
	[Empresas] [text] NULL								-- MULTI: lista/restricción; MONO: sin uso operativo
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO


CREATE TABLE [dbo].[PQ_SistemaAlarmas_Cabecera](
	[idAlarma] [int] IDENTITY(1,1) NOT NULL,
	[idUsuario] [varchar](50) NOT NULL,
	[archivo] [varchar](100) NOT NULL,
	[clase] [varchar](100) NOT NULL,
	[nombre] [varchar](50) NOT NULL,
	[descripcion] [text] NOT NULL,
	[activa] [bit] NOT NULL,
 CONSTRAINT [PK_PQ_SistemaAlarmas_Cabecera] PRIMARY KEY CLUSTERED 
(
	[idAlarma] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO


CREATE TABLE [dbo].[PQ_SistemaAlarmas_Detalle](
	[idAlarma] [int] NOT NULL,
	[clave] [varchar](50) NOT NULL,
	[valor_string] [varchar](100) NULL,
	[valor_int] [int] NULL,
	[valor_datetime] [datetime] NULL,
	[valor_float] [numeric](18, 2) NULL,
	[valor_bool] [bit] NULL,
 CONSTRAINT [PK_PQ_SistemaAlarmas_Detalle] PRIMARY KEY CLUSTERED 
(
	[idAlarma] ASC,
	[clave] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO


CREATE TABLE [dbo].[PQ_TareasProgramadas_Cabecera](
	[idTarea] [int] IDENTITY(1,1) NOT NULL,
	[archivo] [varchar](100) NOT NULL,
	[clase] [varchar](100) NOT NULL,
	[nombre] [varchar](50) NOT NULL,
	[descripcion] [text] NOT NULL,
	[periodicidad] [varchar](50) NOT NULL,
	[horario] [char](5) NOT NULL,
	[fechaPasada] [char](1) NOT NULL,
	[usaLog] [bit] NOT NULL,
	[logFile] [varchar](150) NOT NULL,
	[ultimaEjecucion] [datetime] NOT NULL,
	[ultimoEstado] [varchar](100) NOT NULL,
	[activa] [bit] NOT NULL,
 CONSTRAINT [PK_PQ_TareasProgramadas_Cabecera] PRIMARY KEY CLUSTERED 
(
	[idTarea] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[PQ_TareasProgramadas_Parametros](
	[idTarea] [int] NOT NULL,
	[clave] [varchar](30) NOT NULL,
	[valor_string] [varchar](100) NULL,
	[valor_int] [int] NULL,
	[valor_double] [numeric](15, 0) NULL,
	[valor_datetime] [datetime] NULL,
	[valor_float] [numeric](18, 2) NULL,
	[valor_bool] [bit] NULL,
	[valor_text] [text] NULL,
 CONSTRAINT [PK_PQ_TareasProgramadas_Parametros] PRIMARY KEY CLUSTERED 
(
	[idTarea] ASC,
	[clave] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO



# Definiciones de cada tema

---

## SEGURIDAD

### 1) Objetivo
- **MONO (este producto):** Definir **autenticación** y **autorización** por **usuario y rol**, y permisos granulares por opción de menú, **sin** contexto de empresa.
- **MULTI (referencia legado):** En un ERP multi-empresa, el mismo esquema puede expresar acceso **por empresa** (`PQ_Empresa`, `IDEmpresa` en `Pq_Permiso`, cabecera de tenant). Ese modo **no** es alcance operativo de esta instalación.

### 2) Relaciones
- **MONO:** 1 fila de `Pq_Permiso` → **1 usuario + 1 rol** (la columna `IDEmpresa`, si existe en DDL, es **legado**; unicidad lógica operativa: **(IDUsuario, IDRol)**).
- **MULTI:** 1 permiso → 1 usuario + **1 empresa** + 1 rol (tripleta en PK o equivalente).
- 1 rol → varios `PQ_RolAtributo` (uno por opción de menú, según modelo).
- 1 rol atributo → 1 opción de menú.

### 3) Reglas de negocio
- **Usuario:** Un solo registro por persona física en `users`. El campo `codigo` es el identificador de login (único).
- **Permiso (MONO):** Asignación **usuario–rol**; varias filas = varios roles. **No** se valida pertenencia a sociedad.
- **Permiso (MULTI):** Un usuario puede tener varias filas (combinación empresa + rol).
- **Rol:** Define qué puede hacer el usuario. Si `AccesoTotal = 1`, el rol tiene acceso total (supervisor); en ese caso, los `PQ_RolAtributo` pueden ser ignorados o complementarios.
- **RolAtributo:** Permisos granulares por opción de menú (Alta, Baja, Modif, Repo). Solo aplica cuando `AccesoTotal = 0`.
- **Menú:** Jerarquía `Idparent` (0 = raíz). `tipo` puede ser ABM (mantenimiento), INF (informe), etc. `procedimiento` vincula con APIs/reportes.

### 4) Flujo de autorización (para codificación)
**MONO**
1. Usuario se autentica → `users` (validar `activo`, `inhabilitado`, `password_hash`).
2. Si `first_login = 1`: tras validar contraseña, redirigir obligatoriamente al proceso de cambio de contraseña (no bloquea el ingreso).
3. **No** hay `X-Company-Id`. Resolver roles vía `Pq_Permiso` por **IDUsuario** (ignorar `IDEmpresa` en lógica de negocio o contrastar solo valor fijo de instalación).
4. Obtener el o los **IDRol** asignados. Si algún rol tiene `AccesoTotal = 1`, aplicar política de menú completo según producto. Si no, consultar `PQ_RolAtributo` para la opción de menú y validar permiso (Alta/Baja/Modi/Repo).

**MULTI (referencia)**
1. Igual autenticación en `users`.
2. Request incluye **empresa activa** (p. ej. `X-Company-Id`).
3. Buscar en `Pq_Permiso` la combinación **(IDUsuario, IDEmpresa, IDRol)**.
4. Igual resolución de rol y `PQ_RolAtributo` que arriba.

### 5) Definiciones acordadas
- **users.token:** Se usa con Laravel Sanctum.
- **Campo contraseña:** Nombre definitivo `password_hash` (hashear con bcrypt). Si el CREATE actual usa `password`, migrar a `password_hash`.
- **first_login = 1:** No bloquea. Tras validar contraseña, el usuario debe ir obligatoriamente al proceso de cambio de contraseña.
- **pq_menus.tipo:** Por el momento solo `ABM` e `INF`. Podrán agregarse otros a futuro.

### 6) Pendiente de definir
- **PQ_RolAtributo.IDAtributo:** ¿Qué representa?

---

## LAYOUTS DE GRILLA

### 1) Objetivo
Almacenar formatos personalizados de grillas (DataGrid DevExtreme) para que los usuarios puedan guardar y recuperar configuraciones de columnas, filtros, agrupaciones, ordenamiento y totalizadores.

### 2) Relaciones
- 1 usuario → varios layouts creados (user_id = creador)
- Los layouts son compartidos: todos los usuarios pueden usar cualquier layout.
- `proceso` vincula con `pq_menus.procedimiento` (identificador de la pantalla).
- `grid_id` distingue cuando una pantalla tiene varias grillas (ej. "default", "master", "detalle").

### 3) Reglas de negocio
- Solo el creador (user_id) puede modificar o eliminar un layout.
- No existe UNIQUE que incluya user_id: los nombres pueden repetirse entre usuarios.
- `layout_data` almacena JSON con la configuración serializada de la grilla.

### 4) Definiciones acordadas
- **proceso:** Valor de `pq_menus.procedimiento` (ej. "Clientes", "Empleados").
- **grid_id:** "default" cuando hay una sola grilla; identificador específico cuando hay varias.

---

## GRUPOS EMPRESARIOS

### 0) Alcance **MONO**
**No aplica** al despliegue actual: no hay múltiples sociedades ni `PQ_Empresa` operativa. Lo siguiente es **referencia MULTI / legado** para informes consolidados en ERP.

### 1) Objetivo (MULTI)
Definir agrupaciones de empresas para informes y procesos que integran información de **varias** bases de datos (reporte consolidado, etc.).

### 2) Relaciones
- 1 grupo empresario → varios `PQ_GrupoEmpresario_Empresas`
- 1 `PQ_GrupoEmpresario_Empresas` → 1 identificador de empresa en modelo multi-empresa (N:M entre grupos y empresas)

### 3) Reglas de negocio
- Un grupo puede contener varias empresas (MULTI).
- Una empresa puede pertenecer a varios grupos.
- La tabla `PQ_GrupoEmpresario_Empresas` es la tabla de unión (N:M).
- **Un grupo NO puede existir sin empresas** (debe tener al menos una) en un despliegue donde exista catálogo de empresas.

### 4) Definiciones acordadas (si se implementa MULTI)
- **Tipos:** Alinear `id_empresa` con el identificador de empresa del modelo (p. ej. `PQ_Empresa.IDEmpresa` como `int`).
- **PK y FKs:** Añadir PK compuesta y FKs a `PQ_GrupoEmpresario_Empresas`.

---

## REPORTES

### 1) Objetivo
Almacenar formatos, grillas, reportes y gráficos definidos por usuarios en informes o procesos con información masiva.

### 2) Relaciones
- 1 `pq_menus.procedimiento` → varios reportes (vinculación lógica por valor, no FK física)

### 3) Reglas de negocio
- Cada reporte se asocia a una opción de menú mediante el campo `procedimiento` (mismo valor en ambos).
- **`Usuario`:** si es `NULL`, el reporte aplica a todos los usuarios (respecto de este campo); si tiene valor, restringe.
- **`Empresa` / `Empresas`:** en **MULTI**, restricción por empresa(s). En **MONO**, **sin uso operativo**; dejar `NULL` u omitir en lógica de producto.

### 4) Definiciones acordadas
- **LayoutData:** Por el momento tipo `string` (texto).
- **Proceso y Empresas:** Por el momento no se usan en la práctica actual; campos de empresa reservados a escenarios MULTI.

### 5) Pendiente de definir
- **Usuario** y, si aplica MULTI, **Empresa:** formato (un solo ID vs lista separada por comas).

---

## SISTEMA ALARMAS

### 1) Objetivo
Almacenar procesos que se disparan al activarse determinados eventos.

### 2) Relaciones
- 1 cabecera → varios detalles (parámetros clave-valor)
- Cabecera: define la alarma (archivo, clase, nombre, descripción).
- Detalle: parámetros configurables (valor_string, valor_int, valor_datetime, etc.).

### 3) Reglas de negocio
- `idUsuario` en cabecera: FK a `users.id` (tipo `int`/`bigint`).
- `activa`: si es 0, la alarma no se ejecuta.
- Detalle: modelo clave-valor flexible (una clave por fila).
- **Ejecución:** Se ejecutan en Laravel (backend).

### 4) Definiciones acordadas
- **idUsuario:** Debe ser FK a `users.id` (cambiar tipo a int/bigint).
- **Ejecución:** Laravel.

### 5) Pendiente de definir
- **Eventos:** ¿Qué tipos de eventos disparan alarmas?

---

## TAREAS PROGRAMADAS

### 1) Objetivo
Almacenar procesos a ejecutar en frecuencias definidas por usuarios, con valores predefinidos y opción de proceso manual análogo.

### 2) Relaciones
- 1 cabecera → varios parámetros (clave-valor)
- Cabecera: define la tarea (archivo, clase, periodicidad, horario).
- Parámetros: valores configurables por tarea.

### 3) Reglas de negocio
- `periodicidad`: frecuencia de ejecución (ver valores abajo).
- `horario`: hora de ejecución (formato `HH:MM`).
- `fechaPasada`: a definir.
- `usaLog`, `logFile`: registro de ejecución.
- `ultimaEjecucion`, `ultimoEstado`: estado de la última ejecución.

### 4) Definiciones acordadas
- **Ejecución:** Laravel Scheduler (o lo más similar al scheduler de Windows si Laravel no cubre todo).
- **periodicidad:** Valores tipo: diaria, semanal, por día de semana, un día del mes, con frecuencia de horas/minutos/segundos. Usar lo que provea Laravel por defecto; si no cubre, implementar lo más similar a Windows Task Scheduler.

### 5) Pendiente de definir
- **fechaPasada:** Valores y significado.

---

# Preguntas pendientes de definir

Solo quedan por definir las siguientes, antes o durante la codificación:

| # | Módulo | Pregunta |
|---|--------|----------|
| 1 | SEGURIDAD | **PQ_RolAtributo.IDAtributo:** ¿Qué representa? |
| 2 | REPORTES | **Usuario** y, en MULTI, **Empresa:** ¿Formato? (un ID vs lista separada por comas). En MONO, empresa N/A. |
| 3 | SISTEMA ALARMAS | **Eventos:** ¿Qué tipos de eventos disparan alarmas? |
| 4 | TAREAS PROGRAMADAS | **fechaPasada:** ¿Valores y significado? |
