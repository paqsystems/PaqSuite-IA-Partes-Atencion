# HU-003 – Maestros y catálogos del módulo

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-003 |
| Título | Maestros y catálogos del módulo Sistema Partes |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-07-30 |
| SPEC origen | [SPEC-003-maestros-y-catalogos](../../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) |
| TR relacionada(s) | [TR-003-maestros-y-catalogos](../../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-003 | Dónde en esta HU |
|--------------------------------|------------------|
| ABM cinco maestros/relaciones §2.1 | Alcance; CA-01 |
| Patrón UI grilla + modal §4.1 | Alcance; CA-09 |
| Asistentes: `user_id` obligatorio, exclusividad §4.2 | CA-02; R-MA-04/05 |
| Clientes: acceso opcional, habilitar/revocar §4.3 | CA-03; R-MA-06 |
| Tipos tarea: `is_default` / `is_generico` §4.5 | CA-05; R-MA-08 |
| Asignación cliente–tipo §4.6 | CA-04; R-MA-07 |
| Universo tipos por cliente §4.7 | CA-08; R-MA-09 |
| Inhabilitación vs eliminación §4.8 | CA-06; R-MA-02/03 |
| Selectores catálogo usables §4.8 | CA-07 |
| Acceso por menú; cliente no administra §2.1 / R-MA-11 | CA-10 |
| Acceso vía SP MUST R-MA-12 | Alcance; Supuestos |
| Fuera de alcance mobile / DDL / gate | Fuera de alcance |

---

## Narrativa

Como operador del módulo Partes con permiso de menú (típicamente supervisor)  
quiero administrar asistentes, clientes, tipos y asignaciones desde pantallas web de ABM  
para mantener catálogos usables que alimenten el gate de identidad y la carga diaria sin romper trazabilidad ni reglas de dominio.

---

## Contexto funcional

La carga diaria y el gate post-login dependen de maestros administrables: asistentes vinculados a `users`, clientes con acceso opcional, tipos de cliente/tarea y asignaciones cliente–tipo. El ABM debe respetar integridad SPEC-001 (unicidad `code`, exclusividad `user_id`, un solo `is_default`) y exponer APIs de catálogo para selectores de operaciones futuras. Precondiciones: tablas SPEC-001; identidad SPEC-002 para quien opera el shell.

---

## Alcance incluido

- ABM web (listado grilla + alta/edición modal) de:
  - Asistentes → `PQ_PARTES_USUARIOS`
  - Clientes → `PQ_PARTES_CLIENTES` (vínculo opcional de acceso)
  - Tipos de cliente → `PQ_PARTES_TIPOS_CLIENTE`
  - Tipos de tarea → `PQ_PARTES_TIPOS_TAREA`
  - Asignación cliente–tipo de tarea → `PQ_PARTES_CLIENTE_TIPO_TAREA`
- Estados `activo` / `inhabilitado` y política de baja (preferir inhabilitar).
- Habilitar acceso cliente: asignar `user_id` a `users.id` existente con validaciones de exclusividad.
- Revocar acceso cliente (MVP): `user_id = NULL`, conservando entidad y datos.
- APIs de consulta de catálogo para selectores (solo registros usables en nuevas operaciones).
- Validaciones de negocio: unicidad `code`, exclusividad `user_id`, `is_default`/`is_generico`, prohibición de asignar tipos genéricos.
- UI web: caption a la izquierda; código + descripción/nombre en listados y selectores; i18n + `data-testid` (`partesMaestros*`).
- Acceso condicionado por permiso de menú Framework; cliente funcional no administra maestros.
- Persistencia de negocio vía SP (MUST; firmas en TR).

---

## Fuera de alcance

- DDL / migraciones de tablas (SPEC-001).
- Gate post-login y payload `resultado.partes` (SPEC-002), salvo efectos de revocar acceso en nuevo login y revalidación mid-session (R-ID-11).
- Carga diaria, supervisión masiva, consultas/dashboard (SPEC-004+).
- ABM de `users`, roles, permisos, menú GEN (Framework).
- Creación automática de usuarios Sanctum desde el maestro Partes (vínculo a `users.id` **ya existente**).
- Mobile: ABMs de maestros excluidos.
- Importación masiva Excel de maestros.
- Costos / auditoría avanzada.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-MA-01 | ABM web de los cinco maestros/relaciones de §2.1; mobile excluido. |
| R-MA-02 | Inhabilitado / no activo → no usable en selectores de nuevas operaciones. |
| R-MA-03 | Preferir inhabilitar frente a delete si hay referencias históricas. Sin referencias: UI ofrece **Eliminar** e **Inhabilitar**. |
| R-MA-04 | `user_id` de asistente obligatorio; de cliente opcional. |
| R-MA-05 | Exclusividad asistente/cliente sobre el mismo `users.id` (SPEC-001 R-MD-04). |
| R-MA-06 | Habilitar acceso cliente = set `user_id`; revocar = `user_id = NULL`. UX: acciones de grilla **y** campo en modal. |
| R-MA-07 | No asignar tipos genéricos a `PQ_PARTES_CLIENTE_TIPO_TAREA`. |
| R-MA-08 | Un solo `is_default`; implica `is_generico`; cambiar default es atómico (desmarca el anterior). |
| R-MA-09 | Universo de tipos por cliente = genéricos usables ∪ asignaciones específicas usables. |
| R-MA-10 | Vínculo a `users` existentes; no alta de `users` desde este ABM. |
| R-MA-11 | Cliente funcional no administra maestros; pantallas condicionadas por menú/permisos Framework. |
| R-MA-12 | Acceso a datos de negocio vía SP (MUST); firmas en TR. |

Criterio “usable” en selectores: `activo = 1` **y** `inhabilitado = 0`.

---

## Criterios de aceptación

- [ ] **CA-01** CRUD/listado de asistentes, clientes, tipos cliente, tipos tarea y asignaciones vía API + UI modal sobre grilla.
- [ ] **CA-02** No se puede crear asistente sin `user_id`; no se puede vincular el mismo `users.id` a asistente y cliente.
- [ ] **CA-03** Habilitar/revocar acceso cliente deja `user_id` set/NULL; disponible por acciones de grilla y por el campo en el modal; tras revocar, nuevo login falla gate Partes.
- [ ] **CA-04** No se puede asignar tipo con `is_generico = 1` a un cliente en `PQ_PARTES_CLIENTE_TIPO_TAREA`.
- [ ] **CA-05** Marcar un tipo como default desmarca el anterior, fuerza `is_generico = 1` y deja exactamente un default usable en el catálogo.
- [ ] **CA-05b** Inhabilitar el tipo que es `is_default` vigente se rechaza con mensaje claro hasta designar otro default.
- [ ] **CA-06** No se elimina físicamente un maestro referenciado; se inhabilita o se deniega delete. Sin referencias, la UI ofrece Eliminar e Inhabilitar.
- [ ] **CA-07** Selectores de catálogo (API) omiten registros inhabilitados o no activos.
- [ ] **CA-08** El universo de tipos por cliente cumple §4.7: genéricos usables más asignaciones específicas usables.
- [ ] **CA-09** UI muestra código + nombre/descripción; i18n + `data-testid` estables por pantalla.
- [ ] **CA-10** Menú/ruta de maestros no expuesta a perfil cliente funcional ni en mobile.
- [ ] **CA-11** El selector de `users` usa lookup tipo PedidosWeb (`/admin/usuarios` o equivalente) con parámetro para listar solo habilitados o todos.
- [ ] **CA-12** Al cambiar `user_id` de un asistente de modo que el usuario Framework anterior quede sin vínculo Partes usable, la UI muestra advertencia confirmable antes de guardar.

---

## Escenarios Gherkin

```gherkin
Feature: ABM maestros y catálogos Partes
  Como supervisor con permiso de menú
  Quiero administrar maestros del módulo
  Para mantener catálogos usables en operación

  Scenario: Alta de asistente con user_id obligatorio
    Given un "users.id" existente no vinculado en clientes
    When creo un asistente con "code", "nombre" y ese "user_id"
    Then el alta es aceptada
    When intento crear otro asistente sin "user_id"
    Then la operación es rechazada con validación de negocio

  Scenario: Exclusividad user_id asistente vs cliente
    Given un "users.id" ya vinculado como asistente activo
    When intento habilitar acceso de un cliente con el mismo "user_id"
    Then la operación es rechazada

  Scenario: Revocar acceso de cliente
    Given un cliente con "user_id" asignado y usuario activo en Framework
    When revoco acceso poniendo "user_id" en NULL
    Then la entidad cliente se conserva con el resto de datos
    When ese usuario intenta un nuevo login al circuito Partes
    Then el gate responde "partes.auth.noFunctionalProfile"

  Scenario: Marcar tipo de tarea como default
    Given un tipo A con "is_default" = 1 e "is_generico" = 1
    When marco otro tipo B como default vía ABM
    Then tipo B queda con "is_default" = 1 e "is_generico" = 1
    And tipo A queda con "is_default" = 0
    And no existen dos tipos con "is_default" = 1

  Scenario: Asignación cliente-tipo prohibida para genéricos
    Given un tipo de tarea con "is_generico" = 1 usable
    When intento crear asignación en "PQ_PARTES_CLIENTE_TIPO_TAREA" para ese tipo
    Then la operación es rechazada

  Scenario: Universo de tipos por cliente en catálogo
    Given un cliente usable con asignaciones específicas y tipos genéricos activos
    When consulto el catálogo de tipos para ese "clienteId"
    Then recibo la unión de genéricos usables y tipos no genéricos asignados usables
    And no aparecen tipos inhabilitados ni no activos
```

---

## Supuestos explícitos

- Tablas y reglas de integridad de SPEC-001 ya desplegadas (trigger exclusividad, validación `is_default`).
- Operadores con sesión Partes válida (SPEC-002); admin Framework sin perfil Partes no accede a estas pantallas.
- Sesión viva tras revocar acceso cliente: revalidación SPEC-002 R-ID-11 en `/auth/me` y APIs de dominio.
- Seed de menú e ítems ABM maestros con permisos rol supervisor/admin producto: detalle en TR.
- Delete físico de asignación cliente–tipo permitido cuando no hay regla de historial sobre la relación; tareas históricas conservan `tipo_tarea_id`.

---

## Preguntas abiertas

- Selector de `users` Framework: ~~pendiente~~ → **cerrado:** patrón PedidosWeb lookup; query param habilitados vs todos (default UI: solo habilitados); nombre param en TR.
- Asignación exacta de ítems de menú y permisos por rol: ~~pendiente~~ → **cerrado:** seed asigna rol Framework **supervisor** a `admin`/`PQ`; MVP incluye menú Seguridad (usuarios, roles, permisos); Archivos visible por permiso menú (no solo flag dominio).
- Advertencia UI al cambiar `user_id` de asistente dejando identidad sin vínculo: ~~pendiente~~ → **cerrado:** advertencia confirmable, luego guardar.
- Nombres de SP (`pq_sp_partes_*` list/get/upsert/disable/delete): ~~pendiente~~ → **cerrado en [TR-003](../../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)**.
- Query param lookup users: ~~pendiente~~ → **`soloActivos`**.

---

## Riesgos de ambigüedad

- Inhabilitar asistente con sesiones abiertas: nuevo login falla gate; sesión viva depende de revalidación R-ID-11 — coherente pero requiere prueba integrada HU-002 + HU-003.
- Edición de `user_id` en asistente: si el cambio deja al usuario sin vínculo usable → **advertencia confirmable** (cerrado batch); próximo login falla gate.
- “Usuario Framework activo/usable según reglas GEN” al habilitar acceso cliente (§4.3.1): reglas GEN concretas aplicables al vínculo no están enumeradas en SPEC-003.

---

## Dependencias

- [HU-001](./HU-001-modelo-datos-modulo.md) / SPEC-001: esquema y reglas R-MD-04/06.
- [HU-002](./HU-002-identidad-funcional-y-acceso.md) / SPEC-002: gate, revalidación R-ID-11, quién opera el shell.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte B + B1: HU creada y enriquecida desde SPEC-003. |
| 2026-07-30 | Batch: acceso cliente = acciones grilla + campo modal. |
| 2026-07-30 | Batch: sin refs → Eliminar + Inhabilitar; con refs → no delete. |
| 2026-07-30 | Batch: inhabilitar tipo default = bloqueo + mensaje (sin wizard). |
| 2026-07-30 | Batch: lookup users = PedidosWeb + filtro activos/todos. |
| 2026-07-30 | Batch: cambio `user_id` asistente = advertencia confirmable. |
| 2026-07-30 | Batch: seed rol supervisor `admin`/`PQ`; MVP menú Seguridad GEN. |
| 2026-07-30 | Parte C+C1: enlazada TR-003. |
