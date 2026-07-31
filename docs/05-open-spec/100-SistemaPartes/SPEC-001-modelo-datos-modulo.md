# SPEC-001 – Modelo de datos del módulo Sistema Partes

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-001 |
| Título | Modelo de datos del módulo Sistema Partes |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-07-30 |
| HU relacionada(s) | [HU-001-modelo-datos-modulo](../../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) |
| TR relacionada(s) | [TR-001-modelo-datos-modulo](../../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md) |
| Fuentes | [`docs/02-producto/Sistema-Partes-IA/`](../../02-producto/Sistema-Partes-IA/) — en especial `09-modelo-datos-tecnico.md`, `02-actores-identidad-y-acceso.md`, `03-modelo-conceptual-del-dominio.md`, `04-maestros-y-catalogos.md`; diagrama operativo [`docs/modelo-datos/md-sistema-partes.md`](../../modelo-datos/md-sistema-partes.md) |

---

## 1. Resumen ejecutivo

- **Problema:** el módulo necesita un esquema físico estable (`PQ_PARTES_*`) para identidad funcional, maestros y registro de tareas, separado de la autenticación Framework (`users`).
- **Resultado esperado:** un contrato de datos canónico (tablas, PK/FK, unicidades, defaults y reglas de integridad) listo para migraciones SQL Server, seed y posteriores SPEC de identidad/maestros/operación.

---

## 2. Alcance

### 2.1 En alcance

- Las seis tablas de dominio:
  - `PQ_PARTES_USUARIOS`
  - `PQ_PARTES_CLIENTES`
  - `PQ_PARTES_TIPOS_CLIENTE`
  - `PQ_PARTES_TIPOS_TAREA`
  - `PQ_PARTES_CLIENTE_TIPO_TAREA`
  - `PQ_PARTES_REGISTRO_TAREA`
- Convenciones: prefijo `PQ_PARTES_`, columnas `snake_case`, motor de referencia SQL Server (MySQL adaptable).
- Vínculo lógico con identidad autenticable Framework: `user_id` → `users.id`.
- Constraints de integridad (PK, FK, UNIQUE, defaults de `bit`).
- Reglas de datos que deben poder imponerse en esquema y/o capa de negocio documentada aquí:
  - un único `is_default = 1` en `PQ_PARTES_TIPOS_TAREA` y ese registro **debe** tener `is_generico = 1`;
  - `user_id` único en asistentes; `user_id` único cuando no nulo en clientes;
  - exclusividad: el mismo `users.id` **no** puede figurar a la vez en `PQ_PARTES_USUARIOS` y `PQ_PARTES_CLIENTES`;
  - par único `(cliente_id, tipo_tarea_id)` en asignación;
  - códigos funcionales `code` únicos por tabla de maestro/catálogo.
- Impacto esperado en TR: migraciones, índices, seed mínimo de catálogo (tipo de tarea default genérico), despliegue de SP futuros (sin definir firma aquí).

### 2.2 Fuera de alcance

- UI ABM de maestros, carga diaria, consultas, dashboard, mobile.
- Login, Sanctum, menú GEN, roles Framework (`pq_roles` / `pq_permisos`).
- Gate post-login y payload de sesión funcional del módulo (**SPEC-002**).
- Reglas de UI de duración (múltiplos de 15), mensajes de advertencia de fecha futura, políticas de baja lógica vs física en pantallas (se reafirman como reglas de negocio a heredar; el DDL no implementa checks de múltiplo de 15 en esta versión salvo documentación).
- Facturación, costeo, auditoría avanzada, cuenta corriente de horas.

---

## 3. Actores y contexto

| Actor | Relevancia para este SPEC |
|-------|---------------------------|
| Asistente | Persistido en `PQ_PARTES_USUARIOS`; `supervisor` es capacidad de dominio en **esta** tabla. |
| Cliente (organización) | Persistido en `PQ_PARTES_CLIENTES`; `user_id` opcional = acceso autenticado. |
| Identidad Framework | Tabla `users` (Sanctum); no es tabla `PQ_PARTES_*`. |
| Administración técnica Framework | Fuera de este SPEC. |

**Precondiciones de instalación:** BD diccionario/operativa del producto (`PAQSUITE_DB=unified`) ya con esquema Framework mínimo (`users`, etc.). Este SPEC añade solo el esquema de dominio.

---

## 4. Comportamiento funcional (contrato de datos)

### 4.1 Convenciones

| Norma | Valor |
|-------|--------|
| Prefijo tablas módulo | `PQ_PARTES_` |
| Identidad autenticable | `users` (Framework); vínculo por `user_id` |
| Naming columnas | `snake_case` |
| PK | `id` `bigint` IDENTITY |
| Timestamps | `created_at` / `updated_at` nullable; en SQL Server preferir `datetime2(3)` |
| Bits de estado | `activo` default `1`; `inhabilitado` default `0` |

### 4.2 Fuente de verdad de `supervisor`

- La capacidad **supervisor** del módulo vive en **`PQ_PARTES_USUARIOS.supervisor`**.
- La columna `users.supervisor` eventualmente presente por integración previa del host es **legado**: no es fuente de verdad del dominio; no debe usarse en gates/resolución funcional. TR de limpieza podrá retirarla o dejarla sin uso.

### 4.3 Resolución de vínculo autenticable (dato)

- Asistente: `PQ_PARTES_USUARIOS.user_id` **NOT NULL**, **UNIQUE** → un `users.id` mapea a lo sumo un asistente.
- Cliente con acceso: `PQ_PARTES_CLIENTES.user_id` **NULL** permitido; si no nulo, **UNIQUE**.
- El campo `code` de asistente/cliente es **código funcional de negocio**, no el login Framework (`users.usuario`). No se exige igualdad `code` = `users.usuario` a nivel DDL (si el producto desea alineación operativa, será regla de maestros / seed, no FK).
- Lookup post-auth (detalle en SPEC-002): por **`users.id`** contra `user_id`, no por igualdad de códigos.

### 4.4 Tablas y campos

#### `PQ_PARTES_USUARIOS`

| Campo | Tipo | Nulo | Default | Notas |
|-------|------|------|---------|--------|
| `id` | bigint IDENTITY | No | — | PK |
| `user_id` | bigint | No | — | UNIQUE; FK lógica → `users.id` |
| `code` | nvarchar(50) | No | — | UNIQUE |
| `nombre` | nvarchar(255) | No | — | |
| `email` | nvarchar(255) | Sí | NULL | |
| `supervisor` | bit | No | 0 | Capacidad de dominio |
| `activo` | bit | No | 1 | |
| `inhabilitado` | bit | No | 0 | |
| `created_at` | datetime2(3) | Sí | NULL | |
| `updated_at` | datetime2(3) | Sí | NULL | |

#### `PQ_PARTES_CLIENTES`

| Campo | Tipo | Nulo | Default | Notas |
|-------|------|------|---------|--------|
| `id` | bigint IDENTITY | No | — | PK |
| `user_id` | bigint | Sí | NULL | UNIQUE filtrado (cuando no NULL); FK lógica → `users.id` |
| `nombre` | nvarchar(255) | No | — | |
| `tipo_cliente_id` | bigint | No | — | FK → `PQ_PARTES_TIPOS_CLIENTE.id` |
| `code` | nvarchar(50) | No | — | UNIQUE |
| `email` | nvarchar(255) | Sí | NULL | |
| `activo` | bit | No | 1 | |
| `inhabilitado` | bit | No | 0 | |
| `created_at` | datetime2(3) | Sí | NULL | |
| `updated_at` | datetime2(3) | Sí | NULL | |

#### `PQ_PARTES_TIPOS_CLIENTE`

| Campo | Tipo | Nulo | Default | Notas |
|-------|------|------|---------|--------|
| `id` | bigint IDENTITY | No | — | PK |
| `code` | nvarchar(50) | No | — | UNIQUE |
| `descripcion` | nvarchar(255) | No | — | |
| `activo` | bit | No | 1 | |
| `inhabilitado` | bit | No | 0 | |
| `created_at` / `updated_at` | datetime2(3) | Sí | NULL | |

#### `PQ_PARTES_TIPOS_TAREA`

| Campo | Tipo | Nulo | Default | Notas |
|-------|------|------|---------|--------|
| `id` | bigint IDENTITY | No | — | PK |
| `code` | nvarchar(50) | No | — | UNIQUE |
| `descripcion` | nvarchar(255) | No | — | |
| `is_generico` | bit | No | 0 | Disponible para todos los clientes si 1 |
| `is_default` | bit | No | 0 | A lo sumo un registro con 1; implica `is_generico = 1` |
| `activo` | bit | No | 1 | |
| `inhabilitado` | bit | No | 0 | |
| `created_at` / `updated_at` | datetime2(3) | Sí | NULL | |

#### `PQ_PARTES_CLIENTE_TIPO_TAREA`

| Campo | Tipo | Nulo | Default | Notas |
|-------|------|------|---------|--------|
| `id` | bigint IDENTITY | No | — | PK |
| `cliente_id` | bigint | No | — | FK → clientes |
| `tipo_tarea_id` | bigint | No | — | FK → tipos tarea |
| `created_at` / `updated_at` | datetime2(3) | Sí | NULL | |
| — | — | — | — | UNIQUE (`cliente_id`, `tipo_tarea_id`) |

Semántica: solo para tipos **no** genéricos. Asignar un tipo genérico a un cliente no tiene sentido funcional (validación de negocio / maestros).

#### `PQ_PARTES_REGISTRO_TAREA`

| Campo | Tipo | Nulo | Default | Notas |
|-------|------|------|---------|--------|
| `id` | bigint IDENTITY | No | — | PK |
| `usuario_id` | bigint | No | — | FK → `PQ_PARTES_USUARIOS.id` (propietario) |
| `cliente_id` | bigint | No | — | FK → clientes |
| `tipo_tarea_id` | bigint | No | — | FK → tipos tarea |
| `fecha` | date | No | — | Fecha funcional de la tarea |
| `duracion_minutos` | int | No | — | Entero; múltiplo del **tramo** de negocio (`PQ_PARAMETROS_GRAL`, default 15) / máx. 1440 en capa de negocio |
| `sin_cargo` | bit | No | 0 | |
| `presencial` | bit | No | 0 | |
| `observacion` | nvarchar(max) | No | — | No vacía en negocio |
| `cerrado` | bit | No | 0 | Histórico; sin edición/eliminación normal si 1 |
| `row_version` | rowversion | No | — | Optimistic lock (SQL Server); expuesto a API como token opaco |
| `created_at` / `updated_at` | datetime2(3) | Sí | NULL | |

### 4.5 Relaciones (FK)

```text
PQ_PARTES_CLIENTES.tipo_cliente_id     → PQ_PARTES_TIPOS_CLIENTE.id
PQ_PARTES_CLIENTE_TIPO_TAREA.cliente_id → PQ_PARTES_CLIENTES.id
PQ_PARTES_CLIENTE_TIPO_TAREA.tipo_tarea_id → PQ_PARTES_TIPOS_TAREA.id
PQ_PARTES_REGISTRO_TAREA.usuario_id    → PQ_PARTES_USUARIOS.id
PQ_PARTES_REGISTRO_TAREA.cliente_id    → PQ_PARTES_CLIENTES.id
PQ_PARTES_REGISTRO_TAREA.tipo_tarea_id → PQ_PARTES_TIPOS_TAREA.id
PQ_PARTES_USUARIOS.user_id             → users.id   (FK formal NOT NULL)
PQ_PARTES_CLIENTES.user_id             → users.id   (FK formal; NULL permitido = sin acceso)
```

**FK hacia `users`:** sí en ambas tablas. En clientes, `user_id` nullable: en SQL Server la FK **no exige** match cuando el valor es NULL (solo valida filas con `user_id` no nulo). Sin `ON DELETE CASCADE` destructivo hacia dominio (preferir `NO ACTION` / restringir baja de `users` referenciados).

**Bajas:** no eliminar físicamente maestros referenciados por tareas u otras FKs (inhabilitar). Detalle operativo en SPEC de maestros.

### 4.6 Reglas de integridad numeradas

| ID | Regla |
|----|--------|
| R-MD-01 | Prefijo `PQ_PARTES_*` para tablas de dominio; `users` fuera del prefijo. |
| R-MD-02 | `PQ_PARTES_USUARIOS.user_id` NOT NULL + UNIQUE. |
| R-MD-03 | `PQ_PARTES_CLIENTES.user_id` nullable; UNIQUE cuando no NULL. |
| R-MD-04 | Exclusividad: un `users.id` no puede estar en ambas tablas de dominio a la vez. **Decisión:** validación en SP/backend **y** trigger SQL Server que bloquee insert/update inconsistente (cinturón y tirantes). |
| R-MD-05 | `code` UNIQUE en USUARIOS, CLIENTES, TIPOS_CLIENTE, TIPOS_TAREA. |
| R-MD-06 | A lo sumo un `is_default = 1` en TIPOS_TAREA; si `is_default = 1` entonces `is_generico = 1`. Imponer en SP/backend (sin índice filtrado). **Al marcar un nuevo default:** desmarcar el anterior en la misma operación (atómico). |
| R-MD-07 | UNIQUE (`cliente_id`, `tipo_tarea_id`) en CLIENTE_TIPO_TAREA. |
| R-MD-08 | `supervisor` de negocio solo en `PQ_PARTES_USUARIOS`. |
| R-MD-09 | Vínculo autenticable por `user_id` → `users.id`, no por igualdad de códigos. **Decisión:** FK formal en USUARIOS y CLIENTES; en CLIENTES NULL = sin acceso (FK no aplica al NULL). |
| R-MD-10 | Timestamps de auditoría en `datetime2(3)` (SQL Server de referencia). |
| R-MD-11 | `PQ_PARTES_REGISTRO_TAREA.row_version` para optimistic lock (SPEC-004 / SPEC-005); conflicto → HTTP 409. |

---

## 5. Criterios verificables

- [ ] Existen las seis tablas `PQ_PARTES_*` con columnas de §4.4.
- [ ] PK `id` IDENTITY en todas; UNIQUE en `code` de maestros/catálogos.
- [ ] UNIQUE `user_id` en USUARIOS; UNIQUE filtrado/equivalente en CLIENTES.user_id.
- [ ] FK formal `user_id` → `users.id` en USUARIOS y CLIENTES (NULL permitido en clientes).
- [ ] FK entre tablas de dominio según §4.5.
- [ ] Defaults de bits documentados aplicados en migración.
- [ ] Un solo `is_default = 1` garantizado por validación SP/backend (sin índice filtrado); seed + tests de escritura.
- [ ] Seed mínimo incluye un tipo de tarea genérico y default.
- [ ] Documentación [`md-sistema-partes.md`](../../modelo-datos/md-sistema-partes.md) y `09-modelo-datos-tecnico.md` alineadas a este SPEC.
- [ ] No se usa `users.supervisor` como fuente de verdad en documentación de dominio.

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Migraciones | Crear tablas + índices UNIQUE + FK; SQL Server: evitar cascadas problemáticas; `datetime2(3)`. |
| Seed | Tipo tarea default genérico; filas `PQ_PARTES_USUARIOS` para **`admin`** y **`PQ`** con `supervisor = 1` (SPEC-002). |
| SP | Runtime de negocio MUST SP (regla BASE); listado de SP de dominio en TR posteriores, no en este SPEC. |
| Host PHP | Modelos/adapters solo tras TR; gate en SPEC-002. |
| Docs | Sync `md-sistema-partes.md` + `09-modelo-datos-tecnico.md`. |

---

## 7. Riesgos y supuestos

| Riesgo / supuesto | Tratamiento |
|-------------------|-------------|
| Columna `users.supervisor` residual | Legado; no usar; limpieza en TR host. |
| Exclusividad asistente/cliente sin constraint SQL portable | **Cerrado:** SP/backend + trigger SQL Server (R-MD-04). |
| `md-sistema-partes.md` histórico resolvía login por `code` | Obsoleto frente a `user_id`; corregido en sync documental. |
| Checks de `duracion_minutos` (múltiplo del tramo param, default 15) | Capa negocio / SPEC-004; no CHECK obligatorio en este SPEC. |
| Preguntas abiertas de producto (revocar acceso cliente, etc.) | No bloquean DDL; ver `08-dudas-y-ambiguedades.md`. |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Versión inicial (Parte A OpenSpec) — canónico desde `Sistema-Partes-IA` + ajuste `user_id` / `supervisor` / `datetime2`. |
| 2026-07-30 | A1 cierre: exclusividad R-MD-04 = SP/backend + trigger SQL Server. |
| 2026-07-30 | A1 cierre: R-MD-06 `is_default` solo validación SP/backend (sin índice filtrado). |
| 2026-07-30 | A1 cierre: FK formal `user_id`→`users` en ambas tablas (NULL en clientes OK). |
| 2026-07-30 | Parte B: enlazada [HU-001](../../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md). |
| 2026-07-30 | Batch HU: R-MD-06 al marcar default desmarca el anterior (atómico). |
| 2026-07-30 | Batch HU: `row_version` en `PQ_PARTES_REGISTRO_TAREA` (optimistic lock). |
| 2026-07-30 | Parte C: enlazada [TR-001](../../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md). |

---

**Trazabilidad:** HU/TR se enlazarán en Parte B/C. Siguiente SPEC de dominio previsto: **SPEC-002 identidad funcional y acceso** (gate post-login).
