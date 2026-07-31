# Modelo de Datos – Sistema Partes (MVP)

> **Canónico OpenSpec:** [SPEC-001-modelo-datos-modulo](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md)  
> Complemento conceptual: [09-modelo-datos-tecnico](../02-producto/Sistema-Partes-IA/09-modelo-datos-tecnico.md)

## Diagrama de Relaciones

```mermaid
erDiagram
    users {
        bigint id PK
        string usuario UK
        string email
        string password
        bit activo
        bit inhabilitado
    }

    PQ_PARTES_USUARIOS {
        bigint id PK
        bigint user_id FK_UK
        string code UK
        string nombre
        string email
        bit supervisor
        bit activo
        bit inhabilitado
    }

    PQ_PARTES_CLIENTES {
        bigint id PK
        bigint user_id FK_UK_nullable
        string nombre
        bigint tipo_cliente_id FK
        string code UK
        string email
        bit activo
        bit inhabilitado
    }

    PQ_PARTES_TIPOS_CLIENTE {
        bigint id PK
        string code UK
        string descripcion
        bit activo
        bit inhabilitado
    }

    PQ_PARTES_TIPOS_TAREA {
        bigint id PK
        string code UK
        string descripcion
        bit is_generico
        bit is_default
        bit activo
        bit inhabilitado
    }

    PQ_PARTES_REGISTRO_TAREA {
        bigint id PK
        bigint usuario_id FK
        bigint cliente_id FK
        bigint tipo_tarea_id FK
        date fecha
        int duracion_minutos
        bit sin_cargo
        bit presencial
        string observacion
        bit cerrado
        rowversion row_version
    }

    PQ_PARTES_CLIENTE_TIPO_TAREA {
        bigint id PK
        bigint cliente_id FK
        bigint tipo_tarea_id FK
    }

    users ||--o| PQ_PARTES_USUARIOS : "user_id"
    users ||--o| PQ_PARTES_CLIENTES : "user_id optional"
    PQ_PARTES_USUARIOS ||--o{ PQ_PARTES_REGISTRO_TAREA : "usuario_id"
    PQ_PARTES_CLIENTES ||--o{ PQ_PARTES_REGISTRO_TAREA : "cliente_id"
    PQ_PARTES_CLIENTES }o--|| PQ_PARTES_TIPOS_CLIENTE : "tipo_cliente_id"
    PQ_PARTES_TIPOS_TAREA ||--o{ PQ_PARTES_REGISTRO_TAREA : "tipo_tarea_id"
    PQ_PARTES_CLIENTES ||--o{ PQ_PARTES_CLIENTE_TIPO_TAREA : "cliente_id"
    PQ_PARTES_TIPOS_TAREA ||--o{ PQ_PARTES_CLIENTE_TIPO_TAREA : "tipo_tarea_id"
```

**Leyenda:** `users` es la identidad autenticable Framework (sin prefijo `PQ_PARTES_`). El vínculo de dominio es por `user_id` → `users.id` (SPEC-001 R-MD-09).

---

## Entidades

### `users` (Framework — fuera del módulo)

Tabla Sanctum / autenticación común. Columnas de negocio del módulo **no** viven aquí.

- Login Framework: `usuario` / email + password (GEN).
- **`users.supervisor` (si existe en el host): legado — no usar.** Fuente de verdad: `PQ_PARTES_USUARIOS.supervisor`.

### `PQ_PARTES_USUARIOS` (asistente)

| Campo | Notas |
|-------|--------|
| `user_id` | NOT NULL, UNIQUE → `users.id` |
| `code` | UNIQUE — código funcional de negocio |
| `supervisor` | Capacidad de dominio (no rol Framework) |
| `activo` / `inhabilitado` | Defaults 1 / 0 |

### `PQ_PARTES_CLIENTES`

| Campo | Notas |
|-------|--------|
| `user_id` | NULL = sin acceso autenticado; si no NULL, UNIQUE |
| `tipo_cliente_id` | NOT NULL → tipos cliente |
| `code` | UNIQUE |

### `PQ_PARTES_TIPOS_CLIENTE` / `PQ_PARTES_TIPOS_TAREA`

- `code` UNIQUE.
- Tipos tarea: `is_generico`, `is_default` (a lo sumo un default; implica genérico).

### `PQ_PARTES_CLIENTE_TIPO_TAREA`

- UNIQUE (`cliente_id`, `tipo_tarea_id`).
- Solo tipos **no** genéricos.

### `PQ_PARTES_REGISTRO_TAREA`

- FKs a asistente (`usuario_id`), cliente, tipo tarea.
- `duracion_minutos` entero; múltiplo de 15 / máx. 1440 en capa de negocio.
- `cerrado = 1` → sin edición/eliminación normal.
- `row_version` (SQL Server `rowversion`) → optimistic lock (SPEC-004/005); conflicto → HTTP 409.

---

## Relaciones

- `users` 1 → 0..1 `PQ_PARTES_USUARIOS`
- `users` 1 → 0..1 `PQ_PARTES_CLIENTES` (opcional)
- Exclusividad: un `users.id` no puede estar en ambas tablas a la vez (R-MD-04)
- Asistente / Cliente / TipoTarea → N registros de tarea
- Cliente N ↔ M TipoTarea (vía `PQ_PARTES_CLIENTE_TIPO_TAREA`)

---

## Restricciones (resumen alineado a SPEC-001)

| ID | Regla |
|----|--------|
| R-MD-01 | Prefijo `PQ_PARTES_*`; `users` fuera |
| R-MD-02 | USUARIOS.user_id NOT NULL + UNIQUE |
| R-MD-03 | CLIENTES.user_id nullable + UNIQUE si no null |
| R-MD-04 | Exclusividad asistente vs cliente por `users.id` |
| R-MD-05 | `code` UNIQUE en maestros/catálogos |
| R-MD-06 | Un solo `is_default`; implica `is_generico` |
| R-MD-07 | UNIQUE asignación cliente–tipo |
| R-MD-08 | `supervisor` solo en USUARIOS |
| R-MD-09 | Vínculo auth por `user_id`, no por igualdad de códigos |
| R-MD-10 | Timestamps `datetime2(3)` en SQL Server |

**Negocio (hereda a SPEC operación / maestros; no todos son CHECK SQL):**

- `duracion_minutos` > 0, múltiplo de 15, ≤ 1440
- `observacion` no vacía
- Tarea `cerrado` inmutable en operación normal
- Uso operativo requiere `activo = 1` y `inhabilitado = 0`
- Integridad: no borrar físicamente maestros referenciados (inhabilitar)

---

## Identidad funcional (puntero)

El detalle de gate post-login, payload de sesión y exclusividad operativa se especifica en **SPEC-002** (siguiente). A nivel dato: resolución por `users.id` → `user_id` en tablas de dominio.

---

## Decisiones de diseño

- Sin facturación / costeo en MVP.
- Normalización 3NF salvo desnormalización documentada.
- Autenticación centralizada Framework; dominio interpreta vía `user_id`.
- Término preferido de actor interno: **asistente** (sinónimo histórico: empleado).

---

## Historial documental

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Alineado a SPEC-001: nombres `PQ_PARTES_*`, vínculo por `user_id`, `supervisor` de dominio, `datetime2`, deprecado login por igualdad de `code`. |
