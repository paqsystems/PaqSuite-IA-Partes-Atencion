# SPEC-003 – Maestros y catálogos del módulo

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-003 |
| Título | Maestros y catálogos del módulo Sistema Partes |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Finalizado |
| Última actualización | 2026-08-01 |
| HU relacionada(s) | [HU-003-maestros-y-catalogos](../../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) |
| TR relacionada(s) | [TR-003-maestros-y-catalogos](../../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md) |
| Depende de | [SPEC-001](./SPEC-001-modelo-datos-modulo.md), [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md) (quién opera; exclusividad `user_id`) |
| Fuentes | [`04-maestros-y-catalogos.md`](../../02-producto/Sistema-Partes-IA/04-maestros-y-catalogos.md), [`03-modelo-conceptual-del-dominio.md`](../../02-producto/Sistema-Partes-IA/03-modelo-conceptual-del-dominio.md), [`09-modelo-datos-tecnico.md`](../../02-producto/Sistema-Partes-IA/09-modelo-datos-tecnico.md) |

---

## 1. Resumen ejecutivo

- **Problema:** la carga diaria y el gate de identidad necesitan catálogos administrables (asistentes, clientes, tipos y asignaciones) sin romper trazabilidad ni reglas de `is_generico` / `is_default` / acceso autenticado.
- **Resultado esperado:** ABM web de maestros del módulo + reglas de inhabilitación, acceso cliente y selectores de catálogo, listos para alimentar SPEC-004 (operación) sin redefinir el DDL de SPEC-001.

---

## 2. Alcance

### 2.1 En alcance

- ABM (listado + alta/edición) de:
  - Asistentes → `PQ_PARTES_USUARIOS`
  - Clientes → `PQ_PARTES_CLIENTES` (incluye vínculo opcional de acceso)
  - Tipos de cliente → `PQ_PARTES_TIPOS_CLIENTE`
  - Tipos de tarea → `PQ_PARTES_TIPOS_TAREA`
  - Asignación cliente–tipo de tarea → `PQ_PARTES_CLIENTE_TIPO_TAREA`
- Estados `activo` / `inhabilitado` y política de baja (preferir inhabilitar).
- Habilitar / revocar acceso autenticado de un cliente (`user_id`).
- APIs de **consulta de catálogo** para selectores (solo registros usables en nuevas operaciones).
- Validaciones de negocio sobre los campos de SPEC-001 (unicidad `code`, exclusividad `user_id`, `is_default`/`is_generico`, no asignar tipos genéricos).
- UI web: patrón ABM grilla + modal; selectores código + descripción; i18n + `data-testid`.
- Acceso a pantallas: permiso de menú Framework; **cliente funcional no administra maestros**.

### 2.2 Fuera de alcance

- DDL / migraciones de tablas (SPEC-001).
- Gate post-login y payload `resultado.partes` (SPEC-002), salvo efectos de revocar acceso en **nuevo** login.
- Carga diaria, supervisión masiva, consultas/dashboard (SPEC-004+).
- ~~ABM de `users`, roles, permisos, menú GEN~~ → **en MVP el producto sí expone menú seguridad GEN** (usuarios, roles, permisos) para limitar accesos; la implementación reutiliza el patrón Framework (no reinventar).
- Creación automática de usuarios Sanctum desde el maestro Partes (el vínculo es a un `users.id` **ya existente**; el alta de `users` es por menú seguridad GEN).
- Mobile: ABMs de maestros **excluidos** (norma mobile del producto).
- Importación masiva Excel de maestros.
- Costos / auditoría avanzada.

---

## 3. Actores y contexto

| Actor | Puede administrar maestros Partes |
|-------|-----------------------------------|
| Usuario con **permiso de menú Framework** a ítems Archivos (ABM) | Sí |
| Seed MVP | `admin` y `PQ` reciben **rol Framework supervisor** (con permisos de ABM Partes) **y** `PQ_PARTES_USUARIOS.supervisor = 1` |
| Cliente funcional | No |
| Sin permiso de menú ABM | No |

**MVP incluye menú de seguridad GEN** (usuarios, roles, permisos) para que se pueda limitar el acceso a pantallas/usuarios sin redeploy. La visibilidad de Archivos no depende solo del flag de dominio `supervisor`: se gobierna por **permisos/`pq_menus`**; el seed deja a `admin`/`PQ` operativos.

---

## 4. Comportamiento funcional

### 4.1 Patrón UI común (web)

- Listado inicial en grilla (DataGrid DevExtreme).
- Alta y edición en **modal** sobre el listado (norma ABM del repo).
- Campos de formulario: caption a la izquierda del control.
- En listados y selectores de catálogo: mostrar **código + descripción/nombre**, no IDs al usuario.
- Acciones típicas por fila: Editar; Inhabilitar / Rehabilitar (o toggle de estado); Eliminar solo si aplica §4.8.
- i18n obligatorio; `data-testid` estables por pantalla (`partesMaestros*`).

### 4.2 Asistentes (`PQ_PARTES_USUARIOS`)

| Campo UI | Regla |
|----------|--------|
| `code` | Obligatorio; único |
| `nombre` | Obligatorio |
| `email` | Opcional |
| `user_id` | Obligatorio; selector de usuario Framework existente; único; no puede estar en clientes |
| `supervisor` | Checkbox; capacidad de dominio |
| `activo` / `inhabilitado` | Estado operativo |

- Alta: exige `user_id` válido (R-MD-02).
- Edición de `user_id`: permitida solo si el nuevo id cumple exclusividad y unicidad; si el cambio deja al usuario Framework anterior sin vínculo usable Partes → **advertencia confirmable** en UI (“ese usuario ya no podrá ingresar a Partes”) y, tras confirmar, persistir (decisión batch). El próximo login de esa identidad falla el gate (SPEC-002).
- Inhabilitado / no activo: no aparece en selectores de “asistente propietario” para nuevas tareas (SPEC-004).

### 4.3 Clientes (`PQ_PARTES_CLIENTES`)

| Campo UI | Regla |
|----------|--------|
| `code` | Obligatorio; único |
| `nombre` | Obligatorio |
| `tipo_cliente_id` | Obligatorio; selector de tipo de cliente **usable** |
| `email` | Opcional |
| `erp_cliente` | Opcional; texto libre; máx. 15 caracteres; rechazo (422) si excede |
| `erp_articulo` | Opcional; texto libre; máx. 15 caracteres; rechazo (422) si excede |
| `user_id` | Opcional; si informado → acceso autenticado |
| `activo` / `inhabilitado` | Estado operativo |

- Campos `erp_cliente` / `erp_articulo` (API `erpCliente` / `erpArticulo`): visibles como columnas en el listado y como campos del formulario modal (caption a la izquierda, sin cambios en el patrón de catálogo código+descripción). Referencia externa a ERP; sin integración activa ni sincronización.

- Cliente **sin** `user_id`: entidad de negocio válida; no ingresa al módulo.
- Cliente **con** `user_id`: acceso según SPEC-002 (activo y no inhabilitado).

#### 4.3.1 Habilitar acceso

1. Operador elige un `users.id` existente (selector).
2. Validar: no está en `PQ_PARTES_USUARIOS`; no está en otro cliente; usuario Framework activo/usable según reglas GEN aplicables al vínculo.
3. Persistir `user_id` en el cliente.

**UX MVP (decisión batch):** **ambos** caminos:
- acciones de grilla **Habilitar acceso** / **Revocar acceso**;
- y el campo `user_id` en el modal de alta/edición del cliente.

#### 4.3.2 Revocar acceso (decisión MVP de este SPEC)

Al revocar:

1. Poner `PQ_PARTES_CLIENTES.user_id = NULL` (desasociar identidad).
2. **Conservar** la entidad cliente y el resto de datos.
3. **No** exigir baja del registro en `users` (eso es GEN).
4. Efecto en **nuevo** login: gate SPEC-002 deniega (`partes.auth.noFunctionalProfile`).
5. Sesión ya abierta: **revalidación** en `/auth/me` y APIs de dominio (SPEC-002 R-ID-11) → 403 si el perfil ya no es usable.

Revocar también disponible por acción de grilla y limpiando `user_id` en el modal.

### 4.4 Tipos de cliente

- Campos: `code`, `descripcion`, `activo`, `inhabilitado`.
- Inhabilitado: no ofrecible al crear/editar clientes nuevos (o al cambiar tipo).

### 4.5 Tipos de tarea

| Campo | Regla |
|-------|--------|
| `code` / `descripcion` | Obligatorios; `code` único |
| `is_generico` | Si 1 → disponible para todos los clientes sin asignación |
| `is_default` | A lo sumo uno en todo el catálogo; si se marca 1 → forzar `is_generico = 1` y desmarcar el default anterior |
| `activo` / `inhabilitado` | Estado |

- No se puede dejar el sistema **sin** ningún tipo default usable tras operaciones de edición (seed garantiza uno; al cambiar default debe quedar exactamente uno).
- Inhabilitar el tipo default vigente: **bloqueado** hasta designar otro default (mensaje i18n claro exigiendo reasignar default antes). **Sin** flujo guiado en el mismo paso (decisión batch: solo bloqueo + mensaje).

### 4.6 Asignación cliente – tipo de tarea

- Alta: elegir cliente usable + tipo de tarea con `is_generico = 0`, activo y no inhabilitado.
- **Prohibido** asignar tipo con `is_generico = 1` (R-MD / producto).
- Par `(cliente_id, tipo_tarea_id)` único.
- Baja de asignación: eliminación física de la fila de relación **permitida** si no hay regla de historial sobre la asignación misma (las tareas históricas siguen apuntando al `tipo_tarea_id`; la asignación solo afecta el universo de selección futura).
- Si el tipo o el cliente quedan inhabilitados, la asignación deja de aportar al selector de nuevas tareas.

### 4.7 Universo de tipos disponibles para un cliente (contrato para SPEC-004)

Para un `clienteId` dado, tipos seleccionables en **nuevas** tareas =

- todos los tipos con `is_generico = 1` **y** usables (`activo` y no `inhabilitado`);
- **más** tipos no genéricos usables asignados en `PQ_PARTES_CLIENTE_TIPO_TAREA` para ese cliente.

Endpoint de catálogo (lectura) debe exponer este universo; la UI de carga lo consume.

### 4.8 Inhabilitación vs eliminación

| Situación | Comportamiento |
|-----------|----------------|
| Maestro referenciado por `PQ_PARTES_REGISTRO_TAREA` (u otras FK de dominio) | **No** eliminación física; solo inhabilitar (o denegar delete con mensaje i18n) |
| Tipo de cliente referenciado por clientes | No delete físico; inhabilitar |
| Asignación cliente–tipo | Delete de la relación permitido (§4.6) |
| Registro **sin** referencias | UI MVP ofrece **Eliminar** (físico) **e** **Inhabilitar** (decisión batch) |

Criterio “usable” en selectores de nuevas operaciones: `activo = 1` **y** `inhabilitado = 0`.

### 4.9 Reglas numeradas

| ID | Regla |
|----|--------|
| R-MA-01 | ABM web de los cinco maestros/relaciones de §2.1; mobile excluido. |
| R-MA-02 | Inhabilitado / no activo → no usable en selectores de nuevas operaciones. |
| R-MA-03 | Preferir inhabilitar frente a delete si hay referencias históricas. Sin referencias: UI ofrece **Eliminar** e **Inhabilitar**. |
| R-MA-04 | `user_id` de asistente obligatorio; de cliente opcional. |
| R-MA-05 | Exclusividad asistente/cliente sobre el mismo `users.id` (SPEC-001 R-MD-04). |
| R-MA-06 | Habilitar acceso cliente = set `user_id`; revocar = `user_id = NULL` (MVP §4.3.2). UX: acciones de grilla **y** campo en modal. |
| R-MA-07 | No asignar tipos genéricos a `PQ_PARTES_CLIENTE_TIPO_TAREA`. |
| R-MA-08 | Un solo `is_default`; implica `is_generico`; cambiar default es atómico (desmarca el anterior). |
| R-MA-09 | Universo de tipos por cliente = genéricos usables ∪ asignaciones específicas usables. |
| R-MA-10 | Vínculo a `users` existentes; no alta de `users` desde este ABM. |
| R-MA-11 | Cliente funcional no administra maestros. Visibilidad ABM = permisos/`pq_menus` Framework. Seed: `admin`/`PQ` con rol **supervisor** + dominio `supervisor=1`. MVP incluye menú seguridad GEN (usuarios, roles, permisos). |
| R-MA-12 | Acceso a datos de negocio vía SP (MUST); firmas en TR. |
| R-MA-13 | ABM clientes: `erp_cliente` / `erp_articulo` opcionales en listado y formulario; se persisten en get/list/upsert. |
| R-MA-14 | Rechazar (422) valores de `erp_cliente` / `erp_articulo` con longitud > 15. |

---

## 5. Criterios verificables

- [ ] CRUD/listado de asistentes, clientes, tipos cliente, tipos tarea y asignaciones vía API + UI modal.
- [ ] No se puede crear asistente sin `user_id`; no se puede vincular el mismo `users.id` a asistente y cliente.
- [ ] Habilitar/revocar acceso cliente deja `user_id` set/NULL; tras revocar, nuevo login del usuario falla gate Partes.
- [ ] No se puede asignar tipo genérico a un cliente.
- [ ] Marcar un tipo como default desmarca el anterior y fuerza `is_generico`.
- [ ] No se elimina físicamente un maestro con tareas referenciadas; se inhabilita.
- [ ] Selectores de catálogo (API) omiten inhabilitados / no activos.
- [ ] Universo de tipos por cliente cumple §4.7.
- [ ] UI muestra código + nombre/descripción; i18n + `data-testid`.
- [ ] Menú/ruta de maestros no expuesta a perfil cliente ni en mobile.
- [x] List/get/create/update de clientes exponen y persisten `erpCliente` / `erpArticulo`; UI de maestro los captura y muestra en grilla; valores > 15 caracteres se rechazan (422).

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | Controllers/services + adapters SP (`pq_sp_partes_*` list/get/upsert/disable/delete según entidad); envelope estándar |
| Seed menú | Ítems ABM maestros Partes; permisos rol supervisor/admin producto |
| Frontend | Features ABM bajo `features/partes/` (o equivalente); SelectBox usuarios vía lookup admin (patrón PedidosWeb + filtro activos/todos) |
| Tests | Feature API + Vitest/E2E humo de un ABM representativo |
| Docs | Cierra en checklist el ítem de revocación de acceso cliente (MVP §4.3.2) |

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| Perfil editable | Sigue fuera (SPEC-002: solo lectura) |
| Sesión viva tras revocar acceso | **Cerrado:** SPEC-002 R-ID-11. |
| Quién exactamente tiene cada ítem de menú | Seed/TR; regla de negocio: no clientes |
| Selector de `users` | **Cerrado:** `GET /api/v1/admin/usuarios?soloActivos=1\|0` (default `1`). Usuario vinculable: `activo=1` e `inhabilitado=0` en `users`. |
| Inhabilitar asistente con sesiones abiertas | Nuevo login falla; sesión viva → mismo criterio que revocación cliente |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Versión inicial (Parte A) — ABM maestros + acceso cliente + universo tipos. |
| 2026-07-30 | A1: apto con observaciones (sesión viva post-revocación; detalle seed menú en TR). |
| 2026-07-30 | A1 cierre: sesión viva alineada a SPEC-002 R-ID-11. |
| 2026-07-30 | Batch HU: acceso cliente = acciones grilla + campo modal. |
| 2026-07-30 | Batch HU: sin refs → Eliminar + Inhabilitar en UI. |
| 2026-07-30 | Batch HU: inhabilitar tipo default = bloqueo + mensaje. |
| 2026-07-30 | Batch HU: lookup users = patrón PedidosWeb + filtro activos/todos. |
| 2026-07-30 | Batch HU: cambio `user_id` asistente = advertencia confirmable. |
| 2026-07-30 | Batch HU: seed rol supervisor + menú Seguridad en MVP. |
| 2026-07-30 | Parte C+C1: enlazada [TR-003](../../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md); param `soloActivos`. |
| 2026-08-01 | CC-PQ #2 (01/08/2026): ABM clientes captura/muestra `erpCliente` / `erpArticulo` (R-MA-13/14). |
| 2026-08-01 | Parte I: fusionado SPEC-003-update (CC-PQ #2, 01/08) en este original; update eliminado. Estado → Finalizado. |

---

**Trazabilidad:** HU/TR en Partes B/C. Siguiente previsto: **SPEC-004 operación / carga diaria**.
