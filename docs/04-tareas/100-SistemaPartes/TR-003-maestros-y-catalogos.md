# TR-003 – Maestros y catálogos del módulo

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-003-maestros-y-catalogos](../../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) |
| **SPEC relacionada** | [SPEC-003-maestros-y-catalogos](../../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Operador con permiso menú Archivos (seed: admin/PQ + rol SUPERVISOR); **no** cliente funcional |
| **Dependencias** | [TR-001](./TR-001-modelo-datos-modulo.md), [TR-002](./TR-002-identidad-funcional-y-acceso.md); lookup GEN `GET /api/v1/admin/usuarios` (o implementar lookup si aún no existe en host) |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Pendiente (D implementado — verificar F1) |
| **Última actualización** | 2026-07-30 (D) |

**Origen:** [HU-003](../../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md)  
**Referencia SPEC:** [SPEC-003](../../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md)  
**Envelope:** [`envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md)

---

## 1) HU refinada (resumen)

### Título
Maestros y catálogos del módulo Sistema Partes

### In scope
- ABM web (grilla + modal) de 5 entidades: asistentes, clientes, tipos cliente, tipos tarea, asignaciones cliente–tipo.
- Habilitar/revocar acceso cliente (grilla + modal).
- Catálogos usables + universo tipos por cliente (§4.7).
- Seed menú Archivos + rutas FE; denegar cliente funcional y mobile.
- SP MUST; reutilizar `pq_sp_partes_assert_user_id_exclusividad` y `pq_sp_partes_tipos_tarea_marcar_default` (TR-001).
- Lookup users PedidosWeb + `soloActivos`.

### Out of scope
- DDL tablas; gate login; carga/consultas; reinventar ABM users/roles (usar menú Seguridad GEN existente/patrón Framework); Excel; mobile ABM.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | CRUD API+UI de las 5 entidades (modal sobre grilla) |
| AC-02 | Asistente sin `userId` → 422; mismo `userId` en asistente y cliente → rechazo (assert/trigger) |
| AC-03 | Habilitar/revocar acceso: `userId` set/NULL; acciones grilla + modal; post-revocar login → `partes.auth.noFunctionalProfile` |
| AC-04 | Asignar tipo `isGenerico=true` → 422 |
| AC-05 | Marcar default → SP atómico; un solo default; fuerza genérico |
| AC-05b | Inhabilitar tipo que es default vigente → 422 + i18n `partes.maestros.tipoDefaultNoInhabilitar` |
| AC-06 | Delete con referencias → 422; sin refs → Eliminar e Inhabilitar en UI |
| AC-07 | Catálogos omiten no usables (`activo=0` o `inhabilitado=1`) |
| AC-08 | `GET .../tipos-tarea/universo?clienteId=` = genéricos usables ∪ asignaciones |
| AC-09 | UI código+nombre; i18n; `data-testid` `partesMaestros*` |
| AC-10 | Sin menú/ruta maestros para `tipoFuncional=cliente` ni native |
| AC-11 | Lookup `GET /api/v1/admin/usuarios?soloActivos=1\|0` (default UI `1`) |
| AC-12 | Cambio `userId` asistente que deja al anterior sin vínculo → confirm UI antes de guardar |

### Gherkin
Heredar HU-003; añadir escenario AC-05b y AC-12.

---

## 3) Reglas de negocio

R-MA-01…12 (SPEC/HU).

| ID | Implementación |
|----|----------------|
| RN-TR-01 | JSON API en **camelCase**; persistencia columnas snake_case vía SP. |
| RN-TR-02 | Antes de set `user_id`: invocar `pq_sp_partes_assert_user_id_exclusividad`. |
| RN-TR-03 | Al marcar `isDefault=true` en upsert tipos: invocar `pq_sp_partes_tipos_tarea_marcar_default` (no UPDATE suelto). |
| RN-TR-04 | Middleware `EnsurePartesFunctionalProfile` + deny si `tipoFuncional=cliente` en rutas maestros (403 `partes.maestros.forbidden`). |
| RN-TR-05 | Permiso menú Framework obligatorio además del perfil Partes. |
| RN-TR-06 | Query param lookup users: **`soloActivos`** = `1` (default FE) \| `0`. |

---

## 4) Impacto en datos — SP (nombres cerrados)

Convención: `pq_sp_partes_<entidad>_<accion>`.

### 4.1 Asistentes (`usuarios`)

| SP | Uso |
|----|-----|
| `pq_sp_partes_usuarios_list` | Filtros/paginación |
| `pq_sp_partes_usuarios_get` | `@p_id` |
| `pq_sp_partes_usuarios_upsert` | Alta/edición; llama assert exclusividad |
| `pq_sp_partes_usuarios_set_estado` | activo/inhabilitado |
| `pq_sp_partes_usuarios_delete` | Físico solo sin refs (tareas) |

### 4.2 Clientes

| SP | Uso |
|----|-----|
| `pq_sp_partes_clientes_list` / `_get` / `_upsert` / `_set_estado` / `_delete` | Análogo |
| `pq_sp_partes_clientes_set_acceso` | `@p_id`, `@p_user_id` NULL=revocar; assert exclusividad si not null |

### 4.3 Tipos cliente / tipos tarea / asignaciones

| SP | Uso |
|----|-----|
| `pq_sp_partes_tipos_cliente_*` | list/get/upsert/set_estado/delete |
| `pq_sp_partes_tipos_tarea_*` | idem; upsert con default → `marcar_default`; set_estado rechaza si es default vigente |
| `pq_sp_partes_cliente_tipo_tarea_list` | |
| `pq_sp_partes_cliente_tipo_tarea_upsert` | Rechaza `is_generico=1` |
| `pq_sp_partes_cliente_tipo_tarea_delete` | Delete relación |

### 4.4 Catálogos (lectura)

| SP | Uso |
|----|-----|
| `pq_sp_partes_catalogo_usuarios_dominio` | Asistentes usables (propietario) |
| `pq_sp_partes_catalogo_clientes` | Clientes usables |
| `pq_sp_partes_catalogo_tipos_cliente` | Tipos cliente usables |
| `pq_sp_partes_catalogo_tipos_tarea_universo` | `@p_cliente_id` → §4.7 |

“Usable” = `activo=1` AND `inhabilitado=0`.

### 4.5 Seed menú

Ítems bajo padre **Archivos** (crear padre si no existe), `enabled=1`, `activo=1`, solo rol SUPERVISOR (y quien tenga permiso):

| codigo | titulo | ruta FE |
|--------|--------|---------|
| `partes_asistentes` | Asistentes | `/archivos/partes/asistentes` |
| `partes_clientes` | Clientes | `/archivos/partes/clientes` |
| `partes_tipos_cliente` | Tipos de cliente | `/archivos/partes/tipos-cliente` |
| `partes_tipos_tarea` | Tipos de tarea | `/archivos/partes/tipos-tarea` |
| `partes_cliente_tipo_tarea` | Asignación tipos por cliente | `/archivos/partes/cliente-tipos-tarea` |

Seguridad GEN (usuarios/roles/permisos): seed/patrón Framework — **no** reimplementar en este TR; verificar que el menú Seguridad esté presente en MVP (SPEC R-MA-11).

---

## 5) Contratos de API

Base: `/api/v1/partes/...` — Auth Bearer + `X-Paq-Cliente` + perfil Partes no-cliente + permiso menú.

### 5.1 Recursos (patrón REST)

| Método | Path | Notas |
|--------|------|-------|
| GET | `/partes/asistentes` | list paginado |
| GET | `/partes/asistentes/{id}` | |
| POST | `/partes/asistentes` | body camelCase |
| PUT | `/partes/asistentes/{id}` | |
| PATCH | `/partes/asistentes/{id}/estado` | `{ activo?, inhabilitado? }` |
| DELETE | `/partes/asistentes/{id}` | 422 si refs |
| GET/POST/PUT/PATCH/DELETE | `/partes/clientes` … | + `POST/DELETE .../clientes/{id}/acceso` body `{ userId }` / revocar |
| … | `/partes/tipos-cliente` | |
| … | `/partes/tipos-tarea` | |
| GET/POST/DELETE | `/partes/cliente-tipos-tarea` | asignación |
| GET | `/partes/catalogos/clientes` | usables |
| GET | `/partes/catalogos/asistentes` | usables |
| GET | `/partes/catalogos/tipos-cliente` | |
| GET | `/partes/catalogos/tipos-tarea` | query `clienteId` (universo §4.7) **obligatorio** para uso en carga; sin `clienteId` → solo genéricos usables **o** 422 (cerrado C1: **422** si falta `clienteId` en este endpoint de universo; genéricos puros = otro path opcional no Must) |

### 5.2 Lookup users (GEN)

| Método | Path | Query |
|--------|------|-------|
| GET | `/api/v1/admin/usuarios` | `soloActivos=1\|0` (default servidor `1` si omitido), búsqueda/paginación según host PedidosWeb |

Si el host aún no expone el endpoint: **T0** de este TR implementa GET lookup mínimo (sin POST/PUT/DELETE users) alineado a GEN.

### 5.3 Errores

| Caso | HTTP | `error` | `respuesta` |
|------|------|---------|-------------|
| Validación negocio | 422 | ≠0 catálogo | `partes.maestros.*` |
| Cliente funcional / sin permiso | 403 | 3003 | `partes.maestros.forbidden` |
| No encontrado | 404 | | `partes.maestros.notFound` |

Ejemplos claves: `partes.maestros.codeDuplicate`, `partes.maestros.userIdRequired`, `partes.maestros.exclusividadUserId`, `partes.maestros.tipoGenericoNoAsignable`, `partes.maestros.tipoDefaultNoInhabilitar`, `partes.maestros.deleteConReferencias`.

Envelope siempre `{ error, respuesta, resultado }` — `resultado` nunca null.

### 5.4 OpenAPI
Actualizar paths anteriores + matriz permisos menú Archivos Partes.

---

## 6) Cambios frontend

| Feature | Ruta | Notas |
|---------|------|-------|
| Asistentes | `/archivos/partes/asistentes` | Grilla+modal; SelectBox users (`soloActivos`); warn confirm cambio `userId` |
| Clientes | `/archivos/partes/clientes` | Acciones Habilitar/Revocar acceso + campo modal |
| Tipos cliente | `/archivos/partes/tipos-cliente` | |
| Tipos tarea | `/archivos/partes/tipos-tarea` | Checkbox default → SP; bloquear inhabilitar default |
| Asignaciones | `/archivos/partes/cliente-tipos-tarea` | Selector tipos no genéricos |
| Mobile | — | No registrar rutas en policy native |

Patrón: caption izquierda; columnas código+nombre; testids `partesMaestrosAsistentesGrid`, `…FormSave`, etc.

Filtrar menú: no mostrar ítems Archivos Partes si `resultado.partes.tipoFuncional === 'cliente'`.

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T0 | Backend | Asegurar `GET /admin/usuarios?soloActivos=` | AC-11 | S/M |
| T1 | DB | Scripts SP list/get/upsert/estado/delete + catálogos + set_acceso | R-MA-12 | L |
| T2 | Backend | Controllers/adapters + middleware deny cliente | AC-01…08, AC-10 | L |
| T3 | Seed | Menú Archivos 5 ítems + permisos SUPERVISOR | Menú visible admin | M |
| T4 | Frontend | 5 pantallas ABM + i18n + testids | AC-01,09,12 | L |
| T5 | Frontend | Acciones acceso cliente; filtro menú cliente | AC-03,10 | M |
| T6 | Tests | Feature API por entidad crítica + exclusividad + default + universo; E2E humo 1 ABM | Suite | L |
| T7 | Docs | OpenAPI + checklist revocación | | S |

**Orden:** T0 → T1 → T2 → T3 → T4/T5 → T6 → T7.

---

## 8) Estrategia de tests

| Capa | Casos |
|------|--------|
| Feature | AC-02…08, AC-05b; 403 cliente; delete con/sin refs |
| Vitest | Confirm warn userId; mapeo catálogo |
| E2E | Humo: listar asistentes + abrir modal alta (admin seed) |

---

## 9) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| `GET /admin/usuarios` ausente | T0 Must |
| Volumen SP alto | Misma familia de firmas; tests por humo + 2–3 SP críticos profundos |
| Inhabilitar asistente con sesión viva | R-ID-11 (TR-002); prueba integrada |
| “Usuario Framework usable” al vincular | Cerrado C1: `activo=1` e `inhabilitado=0` en `users` (+ login allowed GEN si el host lo expone) |

---

## 10) Checklist final

- [ ] AC-01…12  
- [ ] SP + menú + APIs + FE  
- [ ] Lookup users  
- [ ] Tests + OpenAPI  
- [ ] Deploy: migrate SP + seed menú  

---

## 11) Informe C1

# Revisión de ambigüedad - TR-003

## Resultado general
- Estado: **Apto con observaciones** (absorbidas abajo)

## Ambigüedades críticas
- ~~Nombre query lookup users~~ → **`soloActivos=1|0`**, default UI/servidor `1`.
- ~~Universo tipos sin `clienteId`~~ → endpoint universo **exige** `clienteId` (else 422).
- ~~Regla GEN “usuario usable” al habilitar acceso~~ → **`users.activo=1` y `users.inhabilitado=0`** (y `isLoginAllowed` del host si existe).

## Ambigüedades menores
- Sufijo SP `*_set_estado` vs disable/enable separados: un SP con bits.
- Exactitud de `data-testid` por control: prefijo `partesMaestros*` + sufijo entidad en D1.
- Si Seguridad GEN no está seeded en este clone: verificar en D1; no bloquea ABM Partes si Archivos tiene permiso.

## Contradicciones TR ↔ HU ↔ SPEC
- HU “Fuera de alcance: ABM users…” = no reinventar; MVP **sí** usa menú Seguridad GEN (SPEC). TR alineado.

## Supuestos
- TR-001/002 desplegados; trigger + assert exclusividad + marcar_default disponibles.

## Preguntas humanas
- Ninguna bloqueante para D1.

## Veredicto
- **Puede pasar a D1/D: Sí**

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte C + C1: TR creada; `soloActivos`; universo con `clienteId` obligatorio; criterio users usable. |
| 2026-07-30 | Parte D: API maestros (Operations + SpCaller), lookup usuarios, menú Archivos, 5 ABM FE, deny cliente/native, Feature tests OK. Nota: SP T-SQL maestros = follow-up gateway; runtime MONO vía Operations. |

---

**Siguiente:** F1 de TR-003, o D de TR-004 cuando se autorice.
