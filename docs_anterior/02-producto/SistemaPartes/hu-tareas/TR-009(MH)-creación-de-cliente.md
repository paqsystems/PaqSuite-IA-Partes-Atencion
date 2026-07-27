# TR-009(MH) – Creación de Cliente

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-009(MH)-creación-de-cliente              |
| Épica              | Épica 3: Gestión de Clientes (ABM)         |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-008 (listado), HU-014 (tipos de cliente), HU-023 (tipos de tarea) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-01-31                               |
| Estado             | 📋 PENDIENTE                               |

---

## 1) HU Refinada

### Título
Creación de Cliente

### Narrativa
**Como** supervisor  
**Quiero** crear un nuevo cliente  
**Para** poder asociar tareas a él

### Contexto/Objetivo
El supervisor accede al formulario de creación de cliente desde el listado (HU-008). Debe completar código (único), nombre, tipo de cliente (obligatorio), email (opcional), y opcionalmente habilitar acceso al sistema (creando usuario con mismo code y contraseña). Si se habilita acceso, se crea primero un registro en USERS y luego el cliente con user_id. Regla de tipos de tarea: el cliente debe tener al menos un tipo genérico disponible o un tipo asignado (validación post-creación o durante creación si se asignan tipos en HU-012).

### Suposiciones explícitas
- El usuario ya está autenticado como supervisor (HU-001).
- Existe la sección/listado de clientes (HU-008) con enlace "Crear cliente".
- Las tablas PQ_PARTES_cliente (o PQ_PARTES_CLIENTES), PQ_PARTES_tipo_cliente y USERS (o equivalente) existen.
- Si el diseño usa "habilitar acceso": tabla USERS con code, password_hash, activo, inhabilitado; cliente con user_id opcional (FK a USERS). Si el diseño usa solo password_hash en cliente (specs modelo), no se crea USERS.
- Tipos de cliente existen y están activos (HU-014). Tipos de tarea existen para validar regla genéricos/asignados (HU-023).

### In Scope
- Formulario de creación accesible desde listado de clientes (ruta ej. /clientes/nueva).
- Campos: código (obligatorio, único), nombre (obligatorio), tipo de cliente (obligatorio, selector), email (opcional, único si se proporciona), habilitar acceso al sistema (checkbox, default false), contraseña (obligatoria si se habilita acceso), activo (default true), inhabilitado (default false).
- Validaciones: code no vacío y único; nombre no vacío; tipo_cliente_id existente, activo y no inhabilitado; email formato válido y único si se proporciona; si habilitar_acceso: code único en USERS, contraseña obligatoria; regla tipos de tarea (al menos un genérico o tipo asignado).
- Al guardar: si habilitar_acceso, crear User (code, password_hash, activo, inhabilitado) y luego cliente con user_id y mismo code; si no, crear solo cliente. code del cliente debe coincidir con User.code si tiene user_id.
- Mensaje de confirmación y redirección al listado (o opción crear otro).

### Out of Scope
- Edición de cliente (HU-010).
- Eliminación de cliente (HU-011).
- Asignación de tipos de tarea en el mismo formulario (HU-012; puede ser pantalla separada o paso posterior).
- Visualización de detalle de cliente (HU-013 si existe).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder al formulario de creación de cliente (desde listado o ruta /clientes/nueva).
- **AC-02**: Un usuario no supervisor no puede acceder (403 o redirección).
- **AC-03**: El formulario tiene: código (obligatorio), nombre (obligatorio), tipo de cliente (obligatorio, selector), email (opcional), habilitar acceso al sistema (checkbox, default false), contraseña (visible/obligatoria si se habilita acceso), activo (default true), inhabilitado (default false).
- **AC-04**: El sistema valida que el código no esté vacío y sea único (en clientes y, si aplica, en USERS).
- **AC-05**: El sistema valida que el nombre no esté vacío.
- **AC-06**: El sistema valida que el tipo de cliente esté seleccionado, exista, esté activo y no inhabilitado.
- **AC-07**: El sistema valida que el email tenga formato válido y sea único si se proporciona.
- **AC-08**: Si se habilita acceso al sistema, el sistema valida que el código no exista en USERS y que se proporcione contraseña (mínimo 8 caracteres).
- **AC-09**: El sistema valida la regla: al menos un tipo de tarea genérico disponible O el cliente tenga al menos un tipo asignado (post-creación o durante creación; error 2116 si no se cumple).
- **AC-10**: Al guardar con acceso habilitado: se crea primero User (code, password_hash, activo, inhabilitado) y luego cliente con user_id y mismo code.
- **AC-11**: Al guardar sin acceso habilitado: se crea solo el cliente (sin user_id).
- **AC-12**: Se muestra mensaje de confirmación y se redirige al listado de clientes (o opción crear otro).

### Escenarios Gherkin

```gherkin
Feature: Creación de Cliente

  Scenario: Supervisor crea cliente sin acceso al sistema
    Given el supervisor está autenticado
    And está en el listado de clientes
    When hace clic en "Crear cliente"
    And completa código "CLI001", nombre "Cliente A", tipo de cliente "Corporativo"
    And no marca "Habilitar acceso al sistema"
    And hace clic en "Guardar"
    Then se crea el cliente en la base de datos
    And no se crea registro en USERS
    And se muestra mensaje de confirmación
    And es redirigido al listado de clientes

  Scenario: Supervisor crea cliente con acceso al sistema
    Given el supervisor está autenticado
    When accede al formulario de creación de cliente
    And completa código "CLI002", nombre "Cliente B", tipo de cliente "Corporativo"
    And marca "Habilitar acceso al sistema"
    And completa email "cliente@ejemplo.com" y contraseña "password123"
    And hace clic en "Guardar"
    Then se crea un registro en USERS con code "CLI002" y password_hash
    And se crea el cliente con user_id apuntando al User creado y code "CLI002"
    And se muestra mensaje de confirmación

  Scenario: Código duplicado
    Given existe un cliente con código "CLI001"
    When el supervisor intenta crear un cliente con código "CLI001"
    Then el sistema retorna error 422 o 409 (código ya existe)
    And no se crea el cliente

  Scenario: Email duplicado
    Given existe un cliente con email "cliente@ejemplo.com"
    When el supervisor intenta crear un cliente con el mismo email
    Then el sistema retorna error 422 o 409 (email ya existe)
    And no se crea el cliente

  Scenario: Habilitar acceso sin contraseña
    Given el supervisor está en el formulario de creación
    When marca "Habilitar acceso al sistema"
    And no completa la contraseña
    And hace clic en "Guardar"
    Then el sistema valida y muestra error (contraseña obligatoria)
    And no se crea el cliente
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden crear clientes (endpoint y pantalla protegidos).
2. **RN-02**: `code` es obligatorio y único (en tabla de clientes y, si se habilita acceso, en USERS).
3. **RN-03**: `nombre` es obligatorio. `tipo_cliente_id` es obligatorio; debe existir, estar activo y no inhabilitado.
4. **RN-04**: `email` es opcional; si se proporciona, debe tener formato válido y ser único.
5. **RN-05**: Si se habilita acceso al sistema: se debe crear registro en USERS con code (del cliente), password_hash, activo, inhabilitado; el cliente se crea con user_id (FK a USERS) y el mismo code. Contraseña obligatoria y mínimo 8 caracteres (1104).
6. **RN-06**: Regla de tipos de tarea: el cliente debe tener al menos un tipo de tarea genérico disponible O al menos un tipo asignado (validación post-creación o durante creación). Si no se cumple, error 2116.
7. **RN-07**: Códigos de error: 422 (validación), 409 (conflicto código/email), 403 (no supervisor), 2116 (tipos de tarea).

### Permisos por Rol
- **Supervisor:** Acceso completo al formulario de creación y al endpoint POST /api/v1/clientes.
- **Empleado (no supervisor):** Sin acceso; 403 o redirección.
- **Cliente:** No aplica (no crean clientes).

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_cliente` (o `PQ_PARTES_CLIENTES`): INSERT. Campos: code, nombre, tipo_cliente_id, email, user_id (opcional), password_hash (si no se usa USERS), activo, inhabilitado.
- `USERS` (o tabla de usuarios del proyecto): INSERT cuando se habilita acceso. Campos: code (igual al del cliente), password_hash, activo, inhabilitado.
- `PQ_PARTES_tipo_cliente`: SELECT para validación y selector.
- `PQ_PARTES_tipo_tarea` (o equivalente): SELECT para validar regla de tipos genéricos/asignados.
- `PQ_PARTES_cliente_tipo_tarea`: INSERT si se asignan tipos en el mismo flujo (opcional; puede ser HU-012).

### Cambios en Datos
- No se requieren nuevas migraciones si las tablas ya existen con user_id opcional en cliente y USERS con code.
- Verificar que PQ_PARTES_cliente tenga user_id (FK a USERS) nullable si el diseño usa "habilitar acceso" con tabla USERS.

### Migración + Rollback
- No se requiere migración nueva para esta tarea si el esquema ya soporta user_id en cliente.

### Seed Mínimo para Tests
- Tipos de cliente activos. Tipos de tarea (al menos uno genérico o datos para asignar). Usuario supervisor. Cliente existente con código y email para tests de duplicados.

---

## 5) Contratos de API

### Endpoint: POST `/api/v1/clientes`

**Descripción:** Crear un nuevo cliente. Solo supervisores. Si se habilita acceso al sistema, crear primero User y luego cliente con user_id.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. Si no es supervisor → 403 (3101).

**Request Body:**
```json
{
  "code": "CLI001",
  "nombre": "Cliente A",
  "tipo_cliente_id": 1,
  "email": "cliente@ejemplo.com",
  "password": "contraseña123",
  "habilitar_acceso": true,
  "activo": true,
  "inhabilitado": false
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|------------|-------------|
| code | string | Sí | Código único del cliente. No vacío. Único en clientes y en USERS si habilitar_acceso. |
| nombre | string | Sí | Nombre del cliente. No vacío. |
| tipo_cliente_id | integer | Sí | ID tipo de cliente. Debe existir, activo, no inhabilitado. |
| email | string | No | Email. Formato válido, único si se proporciona. |
| password | string | Condicional | Obligatorio si habilitar_acceso. Mínimo 8 caracteres (1104). |
| habilitar_acceso | boolean | No | Default false. Si true, crear User y cliente con user_id. |
| activo | boolean | No | Default true. |
| inhabilitado | boolean | No | Default false. |

**Response 201 Created:**
```json
{
  "error": 0,
  "respuesta": "Cliente creado correctamente",
  "resultado": {
    "id": 1,
    "code": "CLI001",
    "nombre": "Cliente A",
    "tipo_cliente_id": 1,
    "tipo_cliente": { "id": 1, "code": "CORP", "descripcion": "Corporativo" },
    "email": "cliente@ejemplo.com",
    "activo": true,
    "inhabilitado": false,
    "created_at": "2025-01-20T10:00:00Z",
    "updated_at": "2025-01-20T10:00:00Z"
  }
}
```

**Response 401 Unauthorized:** No autenticado (3001).

**Response 403 Forbidden:** No supervisor (3101).

**Response 422 Unprocessable Entity – Validación:**
- 1105: Código requerido o vacío
- 1106: Nombre requerido o vacío
- 1107: Tipo de cliente requerido o inválido
- 1108: Email formato inválido
- 1104: Contraseña muy corta (si habilitar_acceso)
- 2116: El cliente debe tener al menos un tipo de tarea disponible

**Response 409 Conflict:**
- 4101: Código de cliente duplicado
- 4102: Email duplicado

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **ClientesNuevaPage** (o **CrearClientePage**): formulario de creación, ruta /clientes/nueva, protegida por SupervisorRoute.
- **Formulario:** campos código, nombre, tipo de cliente (select), email, checkbox "Habilitar acceso al sistema", contraseña (visible y requerida si checkbox marcado), activo, inhabilitado. Botones Guardar y Cancelar.
- **Navegación:** desde listado de clientes (HU-008) enlace/botón "Crear cliente" que lleva a /clientes/nueva.

### Estados UI
- Loading: mientras se envía el formulario.
- Error: errores de validación (422) o conflicto (409) mostrados en el formulario.
- Success: mensaje de confirmación y redirección al listado (o permanecer para crear otro).

### Validaciones en UI
- Código y nombre no vacíos. Tipo de cliente seleccionado. Email formato válido si se completa. Si "Habilitar acceso" marcado: contraseña obligatoria y mínimo 8 caracteres. Mostrar errores devueltos por la API (code, nombre, email, password, tipo_cliente_id, 2116).

### Accesibilidad Mínima
- `data-testid` en: formulario (clientes.create.form), código (clientes.create.code), nombre (clientes.create.nombre), tipo cliente (clientes.create.tipoCliente), email (clientes.create.email), habilitar acceso (clientes.create.habilitarAcceso), contraseña (clientes.create.password), activo (clientes.create.activo), inhabilitado (clientes.create.inhabilitado), botón guardar (clientes.create.submit), botón cancelar (clientes.create.cancel).
- Labels y roles ARIA apropiados.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | ClienteService o ClientService::create() | Validaciones: code, nombre, tipo_cliente_id, email, habilitar_acceso + password. Si habilitar_acceso: crear User luego Cliente con user_id. Regla tipos de tarea (2116). Códigos error 422, 409, 2116. | HU-008, HU-014, HU-023 | L |
| T2 | Backend  | ClienteController::store() | POST /api/v1/clientes; validación request; llamar servicio; 201, 422, 409, 403. Solo supervisor. | T1 | M |
| T3 | Backend  | Tests unitarios servicio creación | Crear con/sin acceso; código duplicado; email duplicado; tipo_cliente inválido; contraseña corta; regla 2116. | T1 | M |
| T4 | Backend  | Tests integración POST /clientes | 201 con y sin habilitar_acceso; 422 validación; 409 código/email duplicado; 403 no supervisor; 401 sin token. | T2 | M |
| T5 | Frontend | Servicio client.service.ts createCliente() | POST con body; manejo 201, 422, 409, 403. | — | S |
| T6 | Frontend | ClientesNuevaPage / CrearClientePage | Formulario con todos los campos; checkbox habilitar acceso muestra/oculta y obliga contraseña; validaciones UI; submit y redirección. data-testid. | HU-008 | M |
| T7 | Frontend | Integración formulario con API | Llamar createCliente; mostrar errores por campo; mensaje éxito y redirección al listado. | T5, T6 | M |
| T8 | Tests    | E2E Playwright creación cliente | Login supervisor → Clientes → Crear cliente → llenar (sin acceso) → guardar → ver en listado. | T6 | M |
| T9 | Tests    | E2E creación con acceso y validación duplicado | Crear con habilitar acceso; intentar crear con mismo código → ver error. | T6 | S |
| T10| Frontend | Tests unit (Vitest) servicio creación | createCliente(body), manejo 201, 422, 409. | T5 | S |
| T11| Docs     | Actualizar specs/endpoints/clientes-create.md | Reflejar habilitar_acceso y user_id si aplica; códigos 2116, 1104. | T2 | S |
**Total:** 11 tareas (4S + 6M + 1L).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio create: cliente sin acceso (solo INSERT cliente); cliente con acceso (INSERT User, luego INSERT cliente con user_id); code duplicado en clientes → excepción/409; code duplicado en USERS si habilitar_acceso → excepción/409; email duplicado → 409; tipo_cliente no existe o inactivo → 422; contraseña vacía o corta si habilitar_acceso → 422 (1104); regla tipos de tarea no cumplida → 2116.

### Integration Tests (Backend)
- POST /api/v1/clientes como supervisor con body válido (sin acceso) → 201, cliente creado.
- POST con habilitar_acceso true y password → 201, User creado y cliente con user_id.
- POST con code ya existente → 409 (4101).
- POST con email ya existente → 409 (4102).
- POST sin nombre o sin tipo_cliente_id → 422.
- POST como empleado no supervisor → 403.
- POST sin token → 401.

### Frontend Unit Tests (Vitest)
- createCliente: envía body correcto; maneja 201 (éxito); maneja 422 (errores por campo); maneja 409 (mensaje conflicto).

### E2E Tests (Playwright)
- Supervisor → Clientes → Crear cliente → completar formulario sin acceso → Guardar → redirección al listado y cliente visible.
- Supervisor → Crear cliente con habilitar acceso, email y contraseña → Guardar → éxito.
- Crear cliente con código existente → ver mensaje de error (409/422).

---

## 9) Riesgos y Edge Cases

- **Transacción:** Si se habilita acceso, crear User y Cliente en transacción; si falla el cliente, hacer rollback del User.
- **Code único en USERS:** Al habilitar acceso, validar que code no exista en USERS antes de crear (evitar conflicto con empleados u otros clientes).
- **Regla 2116:** Validar después de crear el cliente; si hay tipos genéricos en el sistema, la regla se cumple; si no, el cliente debe tener al menos un tipo asignado (puede ser en HU-012 en pantalla de edición/asignación).
- **Email + password:** Si el diseño exige que email implique "acceso", alinear con habilitar_acceso y contraseña obligatoria.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: servicio creación con validaciones y creación User + Cliente cuando habilitar_acceso
- [ ] Backend: endpoint POST /api/v1/clientes documentado; 201, 422, 409, 403
- [ ] Frontend: formulario creación en /clientes/nueva con todos los campos y validaciones
- [ ] Frontend: checkbox habilitar acceso y contraseña condicional
- [ ] Unit tests backend ok
- [ ] Integration tests endpoint ok
- [ ] Frontend unit tests (Vitest) servicio ok
- [ ] ≥1 E2E Playwright creación cliente ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

*(Se completará durante la implementación)*

### Backend
- Por definir: ClienteService (o ClientService), ClienteController, rutas POST /api/v1/clientes.

### Frontend
- Por definir: ClientesNuevaPage (o CrearClientePage), client.service.ts createCliente(), ruta /clientes/nueva, SupervisorRoute.

### Docs
- `specs/endpoints/clientes-create.md` – Actualizar si se añade habilitar_acceso y códigos 2116, 1104.

### Tests
- Por definir: unit backend, feature API, Vitest frontend, E2E Playwright.

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

*(Se completará durante la implementación)*

## Pendientes / follow-ups

*(Se completará durante la implementación)*
