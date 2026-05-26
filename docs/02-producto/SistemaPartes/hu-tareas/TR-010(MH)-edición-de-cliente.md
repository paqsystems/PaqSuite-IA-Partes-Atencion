# TR-010(MH) – Edición de Cliente

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-010(MH)-edición-de-cliente              |
| Épica              | Épica 3: Gestión de Clientes (ABM)         |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-009 (creación de cliente); HU-008 (listado) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-01-31                               |
| Estado             | 📋 PENDIENTE                               |

---

## 1) HU Refinada

### Título
Edición de Cliente

### Narrativa
**Como** supervisor  
**Quiero** editar la información de un cliente existente  
**Para** mantener actualizados sus datos

### Contexto/Objetivo
El supervisor accede a la edición de un cliente desde el listado (HU-008). Se carga el formulario con los datos actuales del cliente. El código no es modificable. Se pueden editar nombre, tipo de cliente, email, estado activo e inhabilitado. Si el cliente tiene acceso al sistema (user_id configurado), se puede cambiar la contraseña y habilitar o deshabilitar el acceso; si se deshabilita el acceso, se elimina la relación con USERS. Los cambios de estado (activo/inhabilitado) se sincronizan entre USERS y PQ_PARTES_cliente cuando el cliente tiene user_id. Regla de tipos de tarea igual que en creación (2116).

### Suposiciones explícitas
- El usuario ya está autenticado como supervisor (HU-001).
- Existe el listado de clientes (HU-008) con enlace "Editar" por cliente.
- Las tablas PQ_PARTES_cliente, USERS (o equivalente) y PQ_PARTES_tipo_cliente existen.
- Si el cliente tiene user_id, el registro en USERS existe y el code coincide con el del cliente.
- Tipos de cliente y tipos de tarea existen para validaciones (HU-014, HU-023).

### In Scope
- Formulario de edición accesible desde listado (ruta ej. /clientes/:id/editar).
- GET /api/v1/clientes/{id} para cargar datos actuales del cliente (incl. tipo_cliente, si tiene user_id para mostrar opción contraseña/acceso).
- Código en solo lectura (no modificable).
- Campos editables: nombre (obligatorio), tipo de cliente (obligatorio, existente y activo/no inhabilitado), email (opcional, formato válido, único si cambió), activo, inhabilitado.
- Si el cliente tiene acceso al sistema: opción "Cambiar contraseña" (password opcional; si se proporciona, actualizar USERS.password_hash); opción "Habilitar/Deshabilitar acceso" (si se deshabilita, eliminar relación con USERS, ej. user_id = null o lógica equivalente).
- Si se cambia activo o inhabilitado: actualizar PQ_PARTES_cliente y, si tiene user_id, USERS.
- Validación regla tipos de tarea (2116) igual que en creación.
- Mensaje de confirmación y redirección al listado; cambios visibles en el listado.

### Out of Scope
- Creación de cliente (HU-009).
- Eliminación de cliente (HU-011).
- Asignación de tipos de tarea en esta pantalla (HU-012; puede ser misma pantalla o sección; si se hace en HU-012, la regla 2116 se valida allí o aquí).
- Modificación del código del cliente.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la edición de un cliente desde el listado (ej. enlace "Editar" o ruta /clientes/:id/editar).
- **AC-02**: Un usuario no supervisor no puede acceder (403 o redirección).
- **AC-03**: Se carga el formulario con los datos actuales del cliente (nombre, tipo cliente, email, activo, inhabilitado; código en solo lectura).
- **AC-04**: El código del cliente no es modificable (solo lectura).
- **AC-05**: El supervisor puede modificar: nombre, tipo de cliente, email, estado activo, estado inhabilitado.
- **AC-06**: Si el cliente tiene acceso al sistema (user_id), se muestra opción para cambiar contraseña y para habilitar/deshabilitar acceso.
- **AC-07**: El sistema valida que el nombre no esté vacío.
- **AC-08**: El sistema valida que el tipo de cliente exista y esté activo/no inhabilitado.
- **AC-09**: El sistema valida que el email tenga formato válido (si se proporciona) y sea único si cambió.
- **AC-10**: Si se cambia la contraseña, el sistema actualiza USERS.password_hash (no en PQ_PARTES_cliente).
- **AC-11**: Si se cambia activo o inhabilitado, el sistema actualiza PQ_PARTES_cliente y USERS (si tiene user_id).
- **AC-12**: Si se deshabilita el acceso al sistema, se elimina la relación con USERS (user_id = null o lógica equivalente).
- **AC-13**: El sistema valida la regla de tipos de tarea (2116) igual que en creación.
- **AC-14**: Al guardar se muestra mensaje de confirmación y los cambios se reflejan en el listado.

### Escenarios Gherkin

```gherkin
Feature: Edición de Cliente

  Scenario: Supervisor edita datos básicos de un cliente
    Given el supervisor está autenticado
    And existe un cliente con id 1
    When accede a editar el cliente 1
    Then se carga el formulario con los datos actuales
    And el código está en solo lectura
    When modifica el nombre a "Cliente A Actualizado"
    And modifica el tipo de cliente
    And hace clic en "Guardar"
    Then el cliente se actualiza en la base de datos
    And se muestra mensaje de confirmación
    And los cambios se reflejan en el listado

  Scenario: Supervisor cambia contraseña de cliente con acceso
    Given el supervisor está autenticado
    And existe un cliente con user_id configurado
    When accede a editar el cliente
    Then ve la opción "Cambiar contraseña"
    When introduce nueva contraseña y guarda
    Then se actualiza USERS.password_hash para ese usuario
    And el cliente se actualiza correctamente

  Scenario: Supervisor deshabilita acceso al sistema
    Given el supervisor está autenticado
    And existe un cliente con user_id configurado
    When accede a editar el cliente
    And deshabilita "Acceso al sistema"
    And hace clic en "Guardar"
    Then se elimina la relación con USERS (user_id = null o equivalente)
    And el cliente sigue existiendo con el resto de datos actualizados

  Scenario: Cambio de estado activo/inhabilitado sincroniza USERS
    Given un cliente con user_id configurado
    When el supervisor edita y cambia activo a false
    And guarda
    Then se actualiza activo en PQ_PARTES_cliente
    And se actualiza activo en USERS para el user_id del cliente

  Scenario: Cliente no encontrado
    Given el supervisor está autenticado
    When intenta acceder a editar cliente con id inexistente
    Then recibe 404 (cliente no encontrado)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden editar clientes (endpoint y pantalla protegidos).
2. **RN-02**: El código (`code`) no es modificable; se ignora si se envía en el body del PUT.
3. **RN-03**: `nombre` es obligatorio. `tipo_cliente_id` es obligatorio; debe existir, estar activo y no inhabilitado.
4. **RN-04**: `email` es opcional; si se proporciona, formato válido y único (excluyendo el propio cliente).
5. **RN-05**: Si el cliente tiene user_id: la contraseña se almacena en USERS; al cambiar contraseña se actualiza USERS.password_hash. Si se deshabilita acceso, se elimina la relación (user_id = null en cliente; no se elimina el registro USERS si el proyecto lo mantiene para historial, o se desvincula según diseño).
6. **RN-06**: Cambios de activo e inhabilitado se sincronizan: actualizar PQ_PARTES_cliente y, si tiene user_id, USERS.
7. **RN-07**: Regla de tipos de tarea (2116): igual que en creación; si no se cumple tras la actualización, error 2116.
8. **RN-08**: Cliente inexistente → 404 (4003).

### Permisos por Rol
- **Supervisor:** Acceso completo al formulario de edición y a GET/PUT /api/v1/clientes/{id}.
- **Empleado (no supervisor):** Sin acceso; 403 o redirección.
- **Cliente:** No aplica (no editan clientes).

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_cliente`: SELECT (GET por id), UPDATE (nombre, tipo_cliente_id, email, activo, inhabilitado, user_id si se deshabilita acceso).
- `USERS`: SELECT (para saber si tiene user_id), UPDATE (password_hash si cambia contraseña; activo, inhabilitado si cambian).
- `PQ_PARTES_tipo_cliente`: SELECT para validación y selector.
- `PQ_PARTES_tipo_tarea` / `PQ_PARTES_cliente_tipo_tarea`: SELECT para validar regla 2116.

### Cambios en Datos
- No se requieren nuevas tablas ni columnas. UPDATE en PQ_PARTES_cliente y, cuando aplica, en USERS.

### Migración + Rollback
- No se requiere migración nueva.

### Seed Mínimo para Tests
- Cliente con y sin user_id. Usuario supervisor. Tipos de cliente activos. Cliente con email para test de unicidad.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/clientes/{id}`

**Descripción:** Obtener detalle del cliente para cargar el formulario de edición. Solo supervisores.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. 403 (3101) si no. 404 (4003) si cliente no existe.

**Path Parameters:** `id` (integer) – ID del cliente.

**Response 200 OK:** Objeto cliente con id, code, nombre, tipo_cliente (o tipo_cliente_id), email, activo, inhabilitado, created_at, updated_at; opcionalmente indicador de si tiene acceso (user_id) para mostrar u ocultar opción contraseña/habilitar acceso.

**Response 403 Forbidden:** No supervisor (3101).

**Response 404 Not Found:** Cliente no encontrado (4003).

---

### Endpoint: PUT `/api/v1/clientes/{id}`

**Descripción:** Actualizar cliente. Código no modificable. Solo supervisores.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. 403 (3101) si no. 404 (4003) si cliente no existe.

**Path Parameters:** `id` (integer) – ID del cliente.

**Request Body:**
```json
{
  "nombre": "Cliente A Actualizado",
  "tipo_cliente_id": 2,
  "email": "nuevo@ejemplo.com",
  "password": "nueva_contraseña123",
  "activo": true,
  "inhabilitado": false,
  "habilitar_acceso": true
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| nombre | string | Sí | Nombre. No vacío. |
| tipo_cliente_id | integer | Sí | ID tipo de cliente. Existente, activo, no inhabilitado. |
| email | string | No | Email. Formato válido, único si cambió (excl. propio cliente). |
| password | string | No | Nueva contraseña. Mínimo 8 caracteres si se proporciona (1104). Solo si cliente tiene user_id o se habilita acceso. |
| activo | boolean | No | Estado activo. Default: mantener actual. |
| inhabilitado | boolean | No | Estado inhabilitado. Default: mantener actual. |
| habilitar_acceso | boolean | No | Si true y cliente no tenía user_id: crear User y vincular. Si false y tenía user_id: desvincular (user_id = null). |

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Cliente actualizado correctamente",
  "resultado": {
    "id": 1,
    "code": "CLI001",
    "nombre": "Cliente A Actualizado",
    "tipo_cliente_id": 2,
    "tipo_cliente": { "id": 2, "code": "OTRO", "descripcion": "Otro" },
    "email": "nuevo@ejemplo.com",
    "activo": true,
    "inhabilitado": false,
    "updated_at": "2025-01-20T11:00:00Z"
  }
}
```

**Response 401 Unauthorized:** No autenticado (3001).

**Response 403 Forbidden:** No supervisor (3101).

**Response 404 Not Found:** Cliente no encontrado (4003).

**Response 422 Unprocessable Entity:** Validación (1106, 1107, 1108, 1104, 2116).

**Response 409 Conflict:** Email duplicado (4102) si aplica.

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **ClientesEditarPage** (o **EditarClientePage**): formulario de edición, ruta /clientes/:id/editar, protegida por SupervisorRoute.
- **Formulario:** código en solo lectura; campos editables: nombre, tipo de cliente (select), email, activo, inhabilitado. Si cliente tiene user_id: opción "Cambiar contraseña" (input password opcional), opción "Habilitar/Deshabilitar acceso al sistema". Botones Guardar y Cancelar.
- **Navegación:** desde listado (HU-008) enlace "Editar" por fila que lleva a /clientes/:id/editar.

### Estados UI
- Loading: mientras se cargan datos (GET) o se envía el formulario (PUT).
- Error: 404 (cliente no encontrado), 403, 422 (errores por campo), 409.
- Success: mensaje de confirmación y redirección al listado.

### Validaciones en UI
- Nombre no vacío. Tipo de cliente seleccionado. Email formato válido si se completa. Si se muestra "Cambiar contraseña" y se completa, mínimo 8 caracteres. Mostrar errores devueltos por la API.

### Accesibilidad Mínima
- `data-testid` en: formulario (clientes.edit.form), código (clientes.edit.code), nombre (clientes.edit.nombre), tipo cliente (clientes.edit.tipoCliente), email (clientes.edit.email), password (clientes.edit.password), activo (clientes.edit.activo), inhabilitado (clientes.edit.inhabilitado), habilitar acceso (clientes.edit.habilitarAcceso), botón guardar (clientes.edit.submit), botón cancelar (clientes.edit.cancel).
- Labels y roles ARIA apropiados.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | GET /api/v1/clientes/{id} | ClienteController::show(). Solo supervisor. Incluir tipo_cliente; indicar si tiene user_id si aplica. 404 si no existe. | HU-009 | S |
| T2 | Backend  | ClienteService::update() | Validaciones: nombre, tipo_cliente_id, email (único si cambió). Actualizar PQ_PARTES_cliente. Si tiene user_id: actualizar USERS (password_hash si password; activo, inhabilitado). Si deshabilitar acceso: user_id = null. Si habilitar acceso (cliente sin user_id): crear User y vincular. Regla 2116. | T1 | L |
| T3 | Backend  | PUT /api/v1/clientes/{id} | ClienteController::update(). Validación request; llamar servicio; 200, 422, 409, 403, 404. Código ignorado si se envía. | T2 | M |
| T4 | Backend  | Tests unitarios servicio update | Actualización datos básicos; cambio contraseña (USERS); cambio activo/inhabilitado (sincronizar USERS); deshabilitar acceso (user_id null); regla 2116; cliente no encontrado. | T2 | M |
| T5 | Backend  | Tests integración GET y PUT /clientes/{id} | GET 200/403/404; PUT 200 con cambios; PUT 422/409/403/404. | T1, T3 | M |
| T6 | Frontend | Servicio getCliente(id), updateCliente(id, body) | GET para cargar; PUT para guardar; manejo 200, 404, 403, 422, 409. | — | S |
| T7 | Frontend | ClientesEditarPage | Formulario con datos actuales; código solo lectura; campos editables; opción contraseña y habilitar/deshabilitar acceso si tiene user_id. data-testid. | HU-008 | M |
| T8 | Frontend | Integración formulario con API | Cargar con getCliente(id); submit con updateCliente(id, body); mensaje éxito y redirección al listado. | T6, T7 | M |
| T9 | Tests    | E2E Playwright editar cliente | Login supervisor → Clientes → Editar cliente → modificar nombre y guardar → ver cambios en listado. | T7 | M |
| T10| Tests    | E2E editar con cambio contraseña y estado | Editar cliente con acceso → cambiar contraseña; editar y cambiar activo → guardar. | T7 | S |
| T11| Frontend | Tests unit (Vitest) servicio | getCliente, updateCliente; manejo 200, 404, 422. | T6 | S |
| T12| Docs     | Actualizar specs clientes-get y clientes-update | Reflejar habilitar_acceso, user_id, códigos 2116, 1104 si aplica. | T3 | S |
**Total:** 12 tareas (4S + 7M + 1L).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio update: actualizar nombre, tipo_cliente_id, email; cambio contraseña actualiza USERS.password_hash; cambio activo/inhabilitado sincroniza USERS; deshabilitar acceso pone user_id = null; habilitar acceso crea User y vincula; regla 2116; cliente no encontrado → excepción 404.

### Integration Tests (Backend)
- GET /api/v1/clientes/{id} como supervisor → 200 con datos; GET con id inexistente → 404; GET como empleado → 403.
- PUT /api/v1/clientes/{id} con body válido → 200, cliente actualizado; PUT con nombre vacío → 422; PUT con tipo_cliente_id inválido → 422; PUT con email duplicado → 409; PUT como empleado → 403; PUT id inexistente → 404.

### Frontend Unit Tests (Vitest)
- getCliente(id), updateCliente(id, body); manejo 200, 404, 422, 409.

### E2E Tests (Playwright)
- Supervisor → Clientes → Editar → modificar datos → Guardar → redirección al listado y cambios visibles.
- Editar cliente con acceso → cambiar contraseña y guardar.
- Editar y cambiar activo/inhabilitado → guardar.

---

## 9) Riesgos y Edge Cases

- **Deshabilitar acceso:** Definir si al desvincular (user_id = null) se mantiene el registro en USERS (solo se desvincula) o se elimina/suaviza; alinear con reglas de negocio y auditoría.
- **Habilitar acceso en edición:** Si el cliente no tenía user_id y se marca "Habilitar acceso", crear User (code = cliente.code, password obligatoria) y actualizar cliente con user_id; validar code único en USERS.
- **Transacción:** Actualizar cliente y USERS en transacción cuando apliquen ambos.
- **Regla 2116:** Validar después de actualizar; si el cliente ya tiene tipos asignados (HU-012) o hay tipos genéricos, la regla se cumple.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: GET /api/v1/clientes/{id} y PUT /api/v1/clientes/{id} documentados
- [ ] Backend: actualización y sincronización USERS cuando aplica (contraseña, activo, inhabilitado, desvincular)
- [ ] Frontend: formulario edición en /clientes/:id/editar con código solo lectura y campos editables
- [ ] Frontend: opción contraseña y habilitar/deshabilitar acceso cuando tiene user_id
- [ ] Unit tests backend ok
- [ ] Integration tests GET y PUT ok
- [ ] Frontend unit tests (Vitest) ok
- [ ] ≥1 E2E Playwright editar cliente ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

*(Se completará durante la implementación)*

### Backend
- Por definir: ClienteController::show(), ClienteController::update(), ClienteService::update(), rutas GET y PUT /api/v1/clientes/{id}.

### Frontend
- Por definir: ClientesEditarPage, client.service.ts getCliente/updateCliente, ruta /clientes/:id/editar, SupervisorRoute.

### Docs
- `specs/endpoints/clientes-get.md`, `specs/endpoints/clientes-update.md` – Actualizar si se añaden campos o códigos.

### Tests
- Por definir: unit backend, feature API, Vitest frontend, E2E Playwright.

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

*(Se completará durante la implementación)*

## Pendientes / follow-ups

*(Se completará durante la implementación)*
