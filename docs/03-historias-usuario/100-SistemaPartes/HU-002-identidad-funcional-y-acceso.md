# HU-002 – Identidad funcional y acceso al módulo

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-002 |
| Título | Identidad funcional y acceso al módulo Sistema Partes |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-07-30 |
| SPEC origen | [SPEC-002-identidad-funcional-y-acceso](../../05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md) |
| TR relacionada(s) | [TR-002-identidad-funcional-y-acceso](../../04-tareas/100-SistemaPartes/TR-002-identidad-funcional-y-acceso.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-002 | Dónde en esta HU |
|--------------------------------|------------------|
| Gate post-credenciales (`PostLoginBusinessGate`) §4.1 | Alcance; CA-01, CA-04, CA-05 |
| Algoritmo resolución por `user_id` §4.2 | Alcance; CA-02, CA-03; R-ID-02/03/04/05/06 |
| Denegación 403 / 3003 / claves `partes.auth.*` §4.3 | CA-01, CA-04, CA-05, CA-09; R-ID-07 |
| Contexto `resultado.partes` en login y `/auth/me` §4.4 | CA-02, CA-03, CA-06; R-ID-08 |
| Perfil solo lectura vía avatar §4.5 | CA-07; R-ID-09 |
| Delimitación primaria universo de datos §4.6 | Alcance; R-ID-10 |
| Revalidación sesión viva §4.8 / R-ID-11 | CA-10 |
| Sin `users.supervisor` como fuente | CA-08; R-ID-06 |
| Fuera de alcance login GEN / ABM maestros | Fuera de alcance |

---

## Narrativa

Como usuario autenticado del Framework  
quiero que el sistema reconozca mi perfil funcional de Partes (asistente, supervisor o cliente) tras validar mis credenciales  
para acceder al circuito operativo del módulo con el contexto correcto o recibir un mensaje claro de denegación si no tengo perfil usable.

---

## Contexto funcional

El login GEN autentica contra `users`, pero el módulo Partes necesita resolver si esa identidad es asistente (con o sin supervisión) o cliente con acceso autenticado, validando registros activos y no inhabilitados en `PQ_PARTES_USUARIOS` o `PQ_PARTES_CLIENTES`. Un gate de negocio post-credenciales debe ejecutarse antes de emitir una sesión operativa usable; el contexto funcional enriquecido (`resultado.partes`) alimenta shell, filtros y APIs posteriores. Precondición: tablas SPEC-001 desplegadas y auth GEN operativo.

---

## Alcance incluido

- Implementación real del gate Partes (`PostLoginBusinessGate`, reemplazando NoOp) tras credenciales OK.
- Resolución funcional por `users.id` → `PQ_PARTES_USUARIOS.user_id` o `PQ_PARTES_CLIENTES.user_id` (R-ID-02).
- Validación de dominio usable: `activo = 1` e `inhabilitado = 0` (R-ID-03).
- Denegación por exclusividad asistente+cliente en el mismo `users.id` (R-ID-04).
- Denegación sin vínculo de dominio activo (R-ID-05).
- Enriquecimiento de login y `GET /api/v1/auth/me` con objeto `resultado.partes` (campos §4.4 del SPEC).
- Delimitación primaria de universo de datos por `tipoFuncional` / `esSupervisor` (contrato para SPEC posteriores).
- Perfil visible de solo lectura accesible desde panel/modal del menú avatar del shell (sin ruta dedicada).
- Contrato de mensajes envelope / i18n de denegación (`partes.auth.noFunctionalProfile`, `partes.auth.inconsistentProfile`; no `auth.invalidCredentials` si la password fue correcta).
- Revalidación del perfil usable en cada request a APIs de dominio Partes y en `/auth/me` (R-ID-11).

---

## Fuera de alcance

- Redefinir login, logout, forgot/reset, `firstLogin`, Sanctum, tenancy headers (Framework GEN).
- ABM de asistentes/clientes y alta de acceso (SPEC-003).
- Carga diaria, supervisión masiva, consultas, dashboard, mobile (SPECs posteriores).
- Roles/permisos Framework (`pq_roles` / menú GEN) salvo coexistencia: el menú puede ocultar pantallas pero no sustituye la delimitación funcional.
- Detalle de revocación de acceso al cliente en **nuevo** login (SPEC-003); efecto en sesión viva cubierto por revalidación R-ID-11.
- Edición de perfil por el usuario final.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-ID-01 | Auth GEN primero; gate Partes después de credenciales OK; luego FE: `firstLogin` → change-password antes del shell (criterio PedidosWeb). |
| R-ID-02 | Resolución por `user_id` = `users.id`, no por igualdad de códigos. |
| R-ID-03 | Dominio usable solo si `activo` y no `inhabilitado`. |
| R-ID-04 | Exclusividad asistente vs cliente; doble vínculo → denegar. |
| R-ID-05 | Sin vínculo de dominio → denegar circuito Partes. |
| R-ID-06 | `esSupervisor` solo desde `PQ_PARTES_USUARIOS.supervisor`. |
| R-ID-07 | Fallo de gate: 403 / 3003 / claves `partes.auth.*` (no 3002). |
| R-ID-08 | Login/`me` exponen contexto §4.4 en `resultado.partes`. |
| R-ID-09 | Perfil MVP de solo lectura, vía panel/modal del avatar; campos: tipo, `code`, nombre, supervisor si aplica, email dominio, `users.usuario`. |
| R-ID-10 | Delimitación §4.6 es capa primaria para APIs de dominio posteriores. |
| R-ID-11 | Revalidar perfil usable en `/auth/me` y en **toda** API de dominio; fallo → 403 y re-login. |

Forma canónica de `resultado.partes`: `tipoFuncional`, `asistenteId`, `clienteId`, `esSupervisor`, `code`, `nombre` (camelCase; ver SPEC-002 §4.4).

---

## Criterios de aceptación

- [ ] **CA-01** Tras credenciales OK, usuario sin fila de dominio activa recibe 403 + `partes.auth.noFunctionalProfile` y sin token de éxito operativo Partes.
- [ ] **CA-02** Usuario solo asistente activo obtiene login 200 con `tipoFuncional=asistente`, `asistenteId` correcto y `esSupervisor` reflejando la columna de dominio.
- [ ] **CA-03** Usuario solo cliente con `user_id` activo obtiene login 200 con `tipoFuncional=cliente`, `clienteId` correcto y `esSupervisor=false`.
- [ ] **CA-04** Usuario presente en ambas tablas de dominio recibe 403 + `partes.auth.inconsistentProfile`.
- [ ] **CA-05** Asistente o cliente inhabilitado o inactivo es denegado como sin perfil usable.
- [ ] **CA-06** `GET /api/v1/auth/me` devuelve el mismo contexto funcional en `resultado.partes` que el login exitoso.
- [ ] **CA-07** Perfil UI: panel/modal desde avatar, solo lectura, con tipo funcional, código dominio, nombre, supervisor si aplica, email de dominio (si hay) y código Framework `users.usuario`.
- [ ] **CA-08** No se lee `users.supervisor` para determinar el flag de supervisión del módulo.
- [ ] **CA-09** i18n: claves `partes.auth.noFunctionalProfile` y `partes.auth.inconsistentProfile` presentes en locales del producto.
- [ ] **CA-10** Tras revocar/inhabilitar con token aún válido: `/auth/me` (y APIs de dominio) responden 403 + `partes.auth.*`; el FE fuerza re-login o limpia sesión.
- [ ] **CA-11** Tras login OK con `firstLogin=true`, el FE redirige a cambio de contraseña antes del Dashboard/shell (criterio PedidosWeb).

---

## Escenarios Gherkin

```gherkin
Feature: Gate de identidad funcional Partes
  Como usuario del producto
  Quiero que el sistema reconozca mi perfil funcional tras login
  Para acceder al módulo Partes o recibir denegación clara

  Scenario: Usuario Framework sin vínculo de dominio
    Given credenciales GEN válidas para un "users.id" sin fila en "PQ_PARTES_USUARIOS" ni "PQ_PARTES_CLIENTES"
    When intento login al circuito Partes
    Then la respuesta es HTTP 403 con error 3003
    And la clave i18n es "partes.auth.noFunctionalProfile"
    And no se devuelve token de sesión operativa Partes

  Scenario: Asistente activo con contexto de sesión
    Given un "users.id" vinculado en "PQ_PARTES_USUARIOS" con "activo" = 1 e "inhabilitado" = 0
    And "supervisor" = 1 en el registro de dominio
    When login con credenciales correctas
    Then la respuesta es HTTP 200
    And "resultado.partes.tipoFuncional" es "asistente"
    And "resultado.partes.asistenteId" coincide con el id de dominio
    And "resultado.partes.esSupervisor" es true

  Scenario: Cliente con acceso autenticado
    Given un "users.id" vinculado en "PQ_PARTES_CLIENTES" con "user_id" no nulo, activo y no inhabilitado
    When login con credenciales correctas
    Then "resultado.partes.tipoFuncional" es "cliente"
    And "resultado.partes.clienteId" coincide con el id de dominio
    And "resultado.partes.esSupervisor" es false

  Scenario: Doble vínculo asistente y cliente
    Given el mismo "users.id" presente en "PQ_PARTES_USUARIOS" y "PQ_PARTES_CLIENTES" activos
    When intento login
    Then la respuesta es HTTP 403 con clave "partes.auth.inconsistentProfile"
    And no se emite sesión operativa Partes

  Scenario: Revalidación mid-session tras inhabilitación
    Given una sesión activa con "resultado.partes" válido
    When el registro de dominio queda inhabilitado o pierde vínculo usable
    And el cliente invoca "GET /api/v1/auth/me" o una API de dominio Partes
    Then la respuesta es HTTP 403 con "partes.auth.noFunctionalProfile" o "partes.auth.inconsistentProfile"
    And el frontend debe forzar re-login o limpiar sesión

  Scenario: Perfil de solo lectura desde avatar
    Given sesión con "resultado.partes" válido
    When abro el panel o modal de perfil desde el menú avatar del shell
    Then veo tipo funcional, código dominio, nombre, supervisor si aplica, email de dominio si existe y código Framework users.usuario
    And no puedo editar los datos del perfil
```

---

## Supuestos explícitos

- Las tablas `PQ_PARTES_*` de SPEC-001 están desplegadas antes de activar el gate.
- El host expone el gancho `PostLoginBusinessGate`; la implementación NoOp actual se sustituye en la TR de esta HU.
- La resolución de identidad funcional se ejecuta vía SP (MUST según norma de acceso a datos); firmas concretas se definen en TR.
- Usuarios Framework de operaciones sin fila de dominio no entran al circuito Partes salvo que tengan vínculo en maestros (SPEC-003).
- Menú GEN y delimitación funcional coexisten; la delimitación §4.6 manda en datos de dominio.

---

## Preguntas abiertas

- Nombres exactos de SP de resolución funcional y revalidación: a fijar en TR (no cambia reglas R-ID-02 / R-ID-11).
- ~~Campos del panel de perfil~~ → **cerrado:** tipo, `code`, nombre, supervisor si aplica, email dominio, `users.usuario`.
- ~~Orden gate vs `firstLogin`~~ → **cerrado (PedidosWeb):** gate Partes en login → token con `firstLogin` → FE change-password antes del shell/dashboard.

---

## Riesgos de ambigüedad

- Garantía de que el login falle **antes** de devolver éxito con token si el gate Partes falla: alineado al gancho actual del host; verificar en TR que no queden tokens emitidos en estados intermedios.
- Consumo de `resultado.partes` en session store del frontend: forma exacta de persistencia y refresco en `/me` a detallar en TR sin alterar contrato API.
- Delimitación supervisor “universo sin filtro por propietario propio”: detalle de procesos concretos llega en SPEC supervisión; esta HU fija solo el contrato de capa 1.

---

## Dependencias

- [HU-001](./HU-001-modelo-datos-modulo.md) / SPEC-001: tablas `PQ_PARTES_USUARIOS` y `PQ_PARTES_CLIENTES` con FK `user_id`.
- Auth Framework GEN operativo (Sanctum, envelope, middleware instalación).

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte B + B1: HU creada y enriquecida desde SPEC-002. |
| 2026-07-30 | Batch: seed `admin`+`PQ` supervisores; resto vía maestros. |
| 2026-07-30 | Batch: perfil = mínimo + email + `users.usuario`. |
| 2026-07-30 | Batch: post-login = criterio PedidosWeb (`firstLogin` → change-password antes del shell). |
| 2026-07-30 | Parte C+C1: enlazada TR-002; restaurado CA-10 en lista. |
