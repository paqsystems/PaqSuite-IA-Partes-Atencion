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
- excepcion: la identidad autenticable comun vive fuera del modulo (`users`);
- nombres de columnas en `snake_case`;
- tablas pensadas para un contexto MONO / `PAQSUITE_DB=unified`, reutilizando la identidad comun del sistema;
- timestamps de auditoria: preferir `datetime2(3)` en SQL Server;
- **Canónico OpenSpec:** [`docs/05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md`](../../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md);
- diagrama operativo sincronizado: [`docs/modelo-datos/md-sistema-partes.md`](../../modelo-datos/md-sistema-partes.md).

## Vista general del esquema

El esquema tecnico del modulo se organiza alrededor de estas tablas:

- `PQ_PARTES_USUARIOS`
- `PQ_PARTES_CLIENTES`
- `PQ_PARTES_TIPOS_CLIENTE`
- `PQ_PARTES_TIPOS_TAREA`
- `PQ_PARTES_CLIENTE_TIPO_TAREA`
- `PQ_PARTES_REGISTRO_TAREA`

**`supervisor`:** vive en `PQ_PARTES_USUARIOS` (capacidad de dominio). No usar `users.supervisor` como fuente de verdad.

**Vinculo autenticable:** `user_id` → `users.id` (no por igualdad de `code` con login Framework).

## Tablas del modulo

## `PQ_PARTES_USUARIOS`

Representa asistentes del modulo y su capacidad eventual de supervision.

### Campos

| Campo | Tipo | Nulo | Descripcion tecnica |
|-------|------|------|---------------------|
| `id` | `bigint` | No | Clave primaria |
| `user_id` | `bigint` | No | Vinculo UNIQUE con `users.id` |
| `code` | `nvarchar(50)` | No | Codigo funcional del asistente (UNIQUE) |
| `nombre` | `nvarchar(255)` | No | Nombre visible |
| `email` | `nvarchar(255)` | Si | Email del asistente |
| `supervisor` | `bit` | No | Marca de supervision de dominio (default 0) |
| `activo` | `bit` | No | Estado activo (default 1) |
| `inhabilitado` | `bit` | No | Estado de inhabilitacion (default 0) |
| `created_at` | `datetime2(3)` | Si | Auditoria de alta |
| `updated_at` | `datetime2(3)` | Si | Auditoria de modificacion |

## `PQ_PARTES_CLIENTES`

Representa clientes del modulo y, cuando aplique, su vinculacion con acceso autenticado.

### Campos

| Campo | Tipo | Nulo | Descripcion tecnica |
|-------|------|------|---------------------|
| `id` | `bigint` | No | Clave primaria |
| `user_id` | `bigint` | Si | Vinculo opcional UNIQUE con `users.id` |
| `nombre` | `nvarchar(255)` | No | Nombre o razon social |
| `tipo_cliente_id` | `bigint` | No | FK a tipo de cliente |
| `code` | `nvarchar(50)` | No | Codigo funcional del cliente (UNIQUE) |
| `email` | `nvarchar(255)` | Si | Email del cliente |
| `activo` | `bit` | No | Estado activo (default 1) |
| `inhabilitado` | `bit` | No | Estado de inhabilitacion (default 0) |
| `created_at` | `datetime2(3)` | Si | Auditoria de alta |
| `updated_at` | `datetime2(3)` | Si | Auditoria de modificacion |

## `PQ_PARTES_TIPOS_CLIENTE`

Catalogo tecnico para clasificar clientes.

### Campos

| Campo | Tipo | Nulo | Descripcion tecnica |
|-------|------|------|---------------------|
| `id` | `bigint` | No | Clave primaria |
| `code` | `nvarchar(50)` | No | Codigo obligatorio del tipo (UNIQUE) |
| `descripcion` | `nvarchar(255)` | No | Descripcion visible |
| `activo` | `bit` | No | Estado activo (default 1) |
| `inhabilitado` | `bit` | No | Estado de inhabilitacion (default 0) |
| `created_at` | `datetime2(3)` | Si | Auditoria de alta |
| `updated_at` | `datetime2(3)` | Si | Auditoria de modificacion |

## `PQ_PARTES_TIPOS_TAREA`

Catalogo tecnico de tipos de tarea.

### Campos

| Campo | Tipo | Nulo | Descripcion tecnica |
|-------|------|------|---------------------|
| `id` | `bigint` | No | Clave primaria |
| `code` | `nvarchar(50)` | No | Codigo obligatorio del tipo (UNIQUE) |
| `descripcion` | `nvarchar(255)` | No | Descripcion visible |
| `is_generico` | `bit` | No | Marca de disponibilidad general |
| `is_default` | `bit` | No | Marca de tipo por defecto (unico; implica generico) |
| `activo` | `bit` | No | Estado activo (default 1) |
| `inhabilitado` | `bit` | No | Estado de inhabilitacion (default 0) |
| `created_at` | `datetime2(3)` | Si | Auditoria de alta |
| `updated_at` | `datetime2(3)` | Si | Auditoria de modificacion |

## `PQ_PARTES_CLIENTE_TIPO_TAREA`

Tabla de relacion entre clientes y tipos de tarea no genericos.

### Campos

| Campo | Tipo | Nulo | Descripcion tecnica |
|-------|------|------|---------------------|
| `id` | `bigint` | No | Clave primaria |
| `cliente_id` | `bigint` | No | FK a cliente |
| `tipo_tarea_id` | `bigint` | No | FK a tipo de tarea |
| `created_at` | `datetime2(3)` | Si | Auditoria de alta |
| `updated_at` | `datetime2(3)` | Si | Auditoria de modificacion |

UNIQUE (`cliente_id`, `tipo_tarea_id`).

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
| `sin_cargo` | `bit` | No | Marca funcional (default 0) |
| `presencial` | `bit` | No | Marca funcional (default 0) |
| `observacion` | `nvarchar(max)` | No | Descripcion del trabajo |
| `cerrado` | `bit` | No | Estado funcional de cierre (default 0) |
| `row_version` | `rowversion` | No | Optimistic lock (SQL Server); token opaco en API (TR-004) |
| `created_at` | `datetime2(3)` | Si | Auditoria de alta |
| `updated_at` | `datetime2(3)` | Si | Auditoria de modificacion |

## Relaciones tecnicas esperadas

- `PQ_PARTES_CLIENTES.tipo_cliente_id` -> `PQ_PARTES_TIPOS_CLIENTE.id`
- `PQ_PARTES_CLIENTE_TIPO_TAREA.cliente_id` -> `PQ_PARTES_CLIENTES.id`
- `PQ_PARTES_CLIENTE_TIPO_TAREA.tipo_tarea_id` -> `PQ_PARTES_TIPOS_TAREA.id`
- `PQ_PARTES_REGISTRO_TAREA.usuario_id` -> `PQ_PARTES_USUARIOS.id`
- `PQ_PARTES_REGISTRO_TAREA.cliente_id` -> `PQ_PARTES_CLIENTES.id`
- `PQ_PARTES_REGISTRO_TAREA.tipo_tarea_id` -> `PQ_PARTES_TIPOS_TAREA.id`
- `PQ_PARTES_USUARIOS.user_id` -> `users.id` (UNIQUE)
- `PQ_PARTES_CLIENTES.user_id` -> `users.id` (opcional, UNIQUE si no null)

Los vinculos con la identidad autenticable comun se expresan mediante `user_id` (no por igualdad de `code` con el login).

## Observaciones tecnicas relevantes

- `PQ_PARTES_REGISTRO_TAREA` es la tabla de mayor sensibilidad funcional.
- `duracion_minutos` se persiste como entero, aunque la experiencia de usuario pueda expresarlo con formatos mas amigables.
- `cerrado` pertenece al ciclo de vida de la tarea y no a una regla transversal de seguridad.
- `is_generico` e `is_default` sostienen la semantica tecnica de seleccion de tipos de tarea.
- La definicion conceptual adoptada exige un unico tipo de tarea por defecto y que ese tipo por defecto sea generico.
- Un mismo `users.id` no debe figurar a la vez como asistente y como cliente (exclusividad).
- `row_version` en `PQ_PARTES_REGISTRO_TAREA` habilita optimistic lock; no usar `users.supervisor` como fuente de verdad del modulo.

## DDL consolidado

El DDL canónico (tipos, defaults, UNIQUE) esta en [SPEC-001 §4](../../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md). Las migraciones de implementacion deben seguir ese contrato (`datetime2(3)`, constraints R-MD-*).

## Relacion con documentacion operativa

Para reconstruccion, migraciones, seeders y arranque del entorno, complementar este documento con:

- `docs/backend/SistemaPartes/arranque-base-datos-inicial.md`
- `docs/modelo-datos/md-sistema-partes.md`
- `docs/05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md`
