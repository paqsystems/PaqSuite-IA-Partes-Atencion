# TR-019(MH) – Creación de Empleado

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-019(MH)-creación-de-empleado            |
| Épica              | Épica 5: Gestión de Empleados (ABM)        |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-018 (listado), HU-001 (autenticación)   |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-05                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Creación de Empleado

### Narrativa
**Como** supervisor  
**Quiero** crear un nuevo empleado  
**Para** que pueda acceder al sistema y registrar tareas

### Contexto/Objetivo
El supervisor accede al formulario de creación de empleado desde el listado (HU-018). Debe completar código (único), nombre, email (opcional), contraseña (obligatoria), confirmación de contraseña, supervisor (checkbox, default false), activo (default true), inhabilitado (default false). La creación de empleado siempre requiere crear primero un registro en USERS y luego el empleado en PQ_PARTES_USUARIOS con user_id. El código del empleado debe coincidir exactamente con el código del User creado.

### Suposiciones explícitas
- El usuario ya está autenticado como supervisor (HU-001).
- Existe la sección/listado de empleados (HU-018) con enlace "Crear empleado".
- Las tablas `USERS` y `PQ_PARTES_USUARIOS` (o equivalente según convención del proyecto) existen.
- La creación de empleado siempre requiere crear registro en USERS (a diferencia de clientes donde es opcional).
- El campo `code` en `PQ_PARTES_USUARIOS` debe coincidir exactamente con `code` en `USERS`.

### In Scope
- Formulario de creación accesible desde listado de empleados (ruta ej. /empleados/nuevo).
- Campos: código (obligatorio, único), nombre (obligatorio), email (opcional, único si se proporciona), contraseña (obligatorio), confirmar contraseña (obligatorio), supervisor (checkbox, default false), activo (checkbox, default true), inhabilitado (checkbox, default false).
- Validaciones: code no vacío y único en USERS; nombre no vacío; email formato válido y único si se proporciona; contraseña no vacía y coincide con confirmación; validación de complejidad de contraseña si aplica (mínimo 8 caracteres).
- Al guardar: crear primero User en USERS (code, password_hash, activo, inhabilitado) y luego empleado en PQ_PARTES_USUARIOS con user_id (FK a USERS) y mismo code. Validar que code del empleado coincida con User.code.
- Mensaje de confirmación y redirección al listado (o opción crear otro).

### Out of Scope
- Edición de empleado (otras HU del ABM).
- Eliminación de empleado (otras HU del ABM).
- Visualización de detalle de empleado.
- Cambio de contraseña desde el formulario de creación.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder al formulario de creación de empleado (desde listado o ruta /empleados/nuevo).
- **AC-02**: Un usuario no supervisor no puede acceder (403 o redirección).
- **AC-03**: El formulario tiene: código (obligatorio), nombre (obligatorio), email (opcional), contraseña (obligatorio), confirmar contraseña (obligatorio), supervisor (checkbox, default false), activo (checkbox, default true), inhabilitado (checkbox, default false).
- **AC-04**: El sistema valida que el código no esté vacío y sea único en USERS.
- **AC-05**: El sistema valida que el nombre no esté vacío.
- **AC-06**: El sistema valida que el email tenga formato válido y sea único si se proporciona.
- **AC-07**: El sistema valida que la contraseña no esté vacía.
- **AC-08**: El sistema valida que la contraseña y confirmación coincidan.
- **AC-09**: El sistema valida la complejidad de la contraseña (mínimo 8 caracteres si aplica).
- **AC-10**: Al guardar, el sistema crea primero un registro en USERS con: code (del empleado), password_hash (de la contraseña proporcionada), activo (del empleado), inhabilitado (del empleado).
- **AC-11**: Al guardar, el sistema crea el empleado en PQ_PARTES_USUARIOS con: user_id (FK al USERS creado), code (debe coincidir con User.code), nombre, email, supervisor, y demás campos.
- **AC-12**: El sistema valida que el code del empleado coincida con el code del User creado.
- **AC-13**: Se muestra mensaje de confirmación y se redirige al listado de empleados (o opción crear otro).

### Escenarios Gherkin

```gherkin
Feature: Creación de Empleado

  Scenario: Supervisor crea empleado correctamente
    Given el supervisor está autenticado
    And está en el listado de empleados
    When hace clic en "Crear empleado"
    And completa código "JPEREZ", nombre "Juan Pérez", email "juan@ejemplo.com"
    And completa contraseña "password123" y confirmación "password123"
    And marca supervisor "false", activo "true", inhabilitado "false"
    And hace clic en "Guardar"
    Then se crea un registro en USERS con code "JPEREZ" y password_hash
    And se crea el empleado en PQ_PARTES_USUARIOS con user_id apuntando al User creado
    And el code del empleado coincide con el code del User
    And se muestra mensaje de confirmación
    And es redirigido al listado de empleados

  Scenario: Código duplicado
    Given existe un usuario con código "JPEREZ" en USERS
    When el supervisor intenta crear un empleado con código "JPEREZ"
    Then el sistema retorna error 422 o 409 (código ya existe)
    And no se crea el empleado
    And no se crea registro en USERS

  Scenario: Email duplicado
    Given existe un empleado con email "juan@ejemplo.com"
    When el supervisor intenta crear un empleado con el mismo email
    Then el sistema retorna error 422 o 409 (email ya existe)
    And no se crea el empleado

  Scenario: Contraseñas no coinciden
    Given el supervisor está en el formulario de creación
    When completa contraseña "password123"
    And completa confirmación "password456"
    And hace clic en "Guardar"
    Then el sistema valida y muestra error (las contraseñas no coinciden)
    And no se crea el empleado

  Scenario: Contraseña muy corta
    Given el supervisor está en el formulario de creación
    When completa contraseña "123"
    And hace clic en "Guardar"
    Then el sistema valida y muestra error (contraseña debe tener al menos 8 caracteres)
    And no se crea el empleado
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden crear empleados (endpoint y pantalla protegidos).
2. **RN-02**: `code` es obligatorio y único en USERS (no puede existir previamente).
3. **RN-03**: `nombre` es obligatorio.
4. **RN-04**: `email` es opcional; si se proporciona, debe tener formato válido y ser único.
5. **RN-05**: La creación de empleado siempre requiere crear primero registro en USERS (code, password_hash, activo, inhabilitado) y luego empleado en PQ_PARTES_USUARIOS con user_id (FK a USERS) y mismo code.
6. **RN-06**: El `code` en `PQ_PARTES_USUARIOS` debe coincidir exactamente con el `code` en `USERS`.
7. **RN-07**: `password_hash` se genera a partir de la contraseña en texto plano y se almacena en USERS.
8. **RN-08**: Contraseña obligatoria, mínimo 8 caracteres (1104). Contraseña y confirmación deben coincidir.
9. **RN-09**: Códigos de error: 422 (validación), 409 (conflicto código/email), 403 (no supervisor), 1104 (contraseña muy corta), 1105 (código requerido), 1106 (nombre requerido), 1108 (email formato inválido).

### Permisos por Rol
- **Supervisor:** Acceso completo al formulario de creación y al endpoint POST /api/v1/empleados.
- **Empleado (no supervisor):** Sin acceso; 403 o redirección.
- **Cliente:** No aplica (no crean empleados).

---

## 4) Impacto en Datos

### Tablas Afectadas
- `USERS`: INSERT. Campos: code, password_hash, activo, inhabilitado.
- `PQ_PARTES_USUARIOS` (o equivalente según convención): INSERT. Campos: user_id (FK a USERS), code (debe coincidir con User.code), nombre, email, supervisor, activo, inhabilitado.

### Cambios en Datos
- No se requieren nuevas migraciones si las tablas ya existen con la estructura adecuada.
- Verificar que PQ_PARTES_USUARIOS tenga user_id (FK a USERS) y code que coincida con User.code.

### Migración + Rollback
- No se requiere migración nueva para esta tarea si el esquema ya soporta user_id en PQ_PARTES_USUARIOS y USERS con code.

### Seed Mínimo para Tests
- Usuario supervisor. Usuario existente con código y email para tests de duplicados.

---

## 5) Contratos de API

### Endpoint: POST `/api/v1/empleados`

**Descripción:** Crear un nuevo empleado. Solo supervisores. Siempre crea primero User en USERS y luego empleado en PQ_PARTES_USUARIOS con user_id.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. Si no es supervisor → 403 (3101).

**Request Body:**
```json
{
  "code": "JPEREZ",
  "nombre": "Juan Pérez",
  "email": "juan@ejemplo.com",
  "password": "contraseña123",
  "supervisor": false,
  "activo": true,
  "inhabilitado": false
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| code | string | Sí | Código único del empleado. No vacío. Único en USERS (1105, 4101). |
| nombre | string | Sí | Nombre completo del empleado. No vacío (1106). |
| email | string | No | Email del empleado. Formato válido, único si se proporciona (1108, 4102). |
| password | string | Sí | Contraseña. Mínimo 8 caracteres (1104). |
| supervisor | boolean | No | Indica si es supervisor. Default: false. |
| activo | boolean | No | Estado activo. Default: true. |
| inhabilitado | boolean | No | Estado inhabilitado. Default: false. |

**Response 201 Created:**
```json
{
  "error": 0,
  "respuesta": "Empleado creado correctamente",
  "resultado": {
    "id": 1,
    "code": "JPEREZ",
    "nombre": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "supervisor": false,
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
```json
{
  "error": 422,
  "respuesta": "Error de validación",
  "resultado": {
    "errors": {
      "code": ["El código es obligatorio."],
      "password": ["La contraseña debe tener al menos 8 caracteres."]
    }
  }
}
```
- 1104: Contraseña muy corta (mínimo 8 caracteres)
- 1105: Código requerido o vacío
- 1106: Nombre requerido o vacío
- 1108: Email formato inválido

**Response 409 Conflict:**
```json
{
  "error": 409,
  "respuesta": "El código del empleado ya existe",
  "resultado": {}
}
```
- 4101: Código de empleado duplicado en USERS
- 4102: Email duplicado

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **EmpleadosNuevoPage** (o **CrearEmpleadoPage**): formulario de creación, ruta /empleados/nuevo, protegida por SupervisorRoute.
- **Formulario:** campos código, nombre, email, contraseña, confirmar contraseña, supervisor (checkbox), activo (checkbox), inhabilitado (checkbox). Botones Guardar y Cancelar.
- **Navegación:** desde listado de empleados (HU-018) enlace/botón "Crear empleado" que lleva a /empleados/nuevo.

### Estados UI
- Loading: mientras se envía el formulario.
- Error: errores de validación (422) o conflicto (409) mostrados en el formulario.
- Success: mensaje de confirmación y redirección al listado (o permanecer para crear otro).

### Validaciones en UI
- Código y nombre no vacíos. Email formato válido si se completa. Contraseña no vacía y mínimo 8 caracteres. Contraseña y confirmación deben coincidir. Mostrar errores devueltos por la API (code, nombre, email, password).

### Accesibilidad Mínima
- `data-testid` en: formulario (empleados.create.form), código (empleados.create.code), nombre (empleados.create.nombre), email (empleados.create.email), contraseña (empleados.create.password), confirmar contraseña (empleados.create.passwordConfirm), supervisor (empleados.create.supervisor), activo (empleados.create.activo), inhabilitado (empleados.create.inhabilitado), botón guardar (empleados.create.submit), botón cancelar (empleados.create.cancel).
- Labels y roles ARIA apropiados.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | EmpleadoService o UsuarioService::create() | Validaciones: code, nombre, email, password. Crear primero User en USERS (code, password_hash, activo, inhabilitado) y luego empleado en PQ_PARTES_USUARIOS con user_id y mismo code. Validar que code coincida. Códigos error 422, 409, 403. | HU-018, HU-001 | L |
| T2 | Backend  | EmpleadoController::store() | POST /api/v1/empleados; validación request; llamar servicio; 201, 422, 409, 403. Solo supervisor. | T1 | M |
| T3 | Backend  | Tests unitarios servicio creación | Crear empleado correctamente; código duplicado en USERS; email duplicado; contraseña corta; nombre vacío; validación code coincidente. | T1 | M |
| T4 | Backend  | Tests integración POST /empleados | 201 creación correcta; 422 validación; 409 código/email duplicado; 403 no supervisor; 401 sin token. | T2 | M |
| T5 | Frontend | Servicio empleado.service.ts createEmpleado() | POST con body; manejo 201, 422, 409, 403. | — | S |
| T6 | Frontend | EmpleadosNuevoPage / CrearEmpleadoPage | Formulario con todos los campos; validaciones UI (código, nombre, email, contraseña, confirmación); submit y redirección. data-testid. | HU-018 | M |
| T7 | Frontend | Integración formulario con API | Llamar createEmpleado; mostrar errores por campo; mensaje éxito y redirección al listado. | T5, T6 | M |
| T8 | Tests    | E2E Playwright creación empleado | Login supervisor → Empleados → Crear empleado → llenar formulario → guardar → ver en listado. | T6 | M |
| T9 | Tests    | E2E validación duplicado y contraseñas | Crear empleado; intentar crear con mismo código → ver error; probar contraseñas no coinciden → ver error. | T6 | S |
| T10| Frontend | Tests unit (Vitest) servicio creación | createEmpleado(body), manejo 201, 422, 409. | T5 | S |
| T11| Docs     | Actualizar specs/endpoints/empleados-create.md | Verificar que refleje creación en USERS y PQ_PARTES_USUARIOS; códigos 1104, 1105, 1106, 1108, 4101, 4102. | T2 | S |

**Total:** 11 tareas (4S + 6M + 1L).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio create: crear empleado correctamente (INSERT User, luego INSERT empleado con user_id y code coincidente); code duplicado en USERS → excepción/409; email duplicado → 409; contraseña vacía o corta → 422 (1104); nombre vacío → 422 (1106); validar que code del empleado coincida con User.code.

### Integration Tests (Backend)
- POST /api/v1/empleados como supervisor con body válido → 201, User creado y empleado creado con user_id.
- POST con code ya existente en USERS → 409 (4101).
- POST con email ya existente → 409 (4102).
- POST sin nombre o sin code → 422.
- POST con contraseña corta → 422 (1104).
- POST como empleado no supervisor → 403.
- POST sin token → 401.

### Frontend Unit Tests (Vitest)
- createEmpleado: envía body correcto; maneja 201 (éxito); maneja 422 (errores por campo); maneja 409 (mensaje conflicto).

### E2E Tests (Playwright)
- Supervisor → Empleados → Crear empleado → completar formulario → Guardar → redirección al listado y empleado visible.
- Crear empleado con código existente → ver mensaje de error (409/422).
- Crear empleado con contraseñas que no coinciden → ver error de validación.
- Crear empleado con contraseña corta → ver error de validación.

---

## 9) Riesgos y Edge Cases

- **Transacción:** Crear User y Empleado en transacción; si falla el empleado, hacer rollback del User.
- **Code único en USERS:** Validar que code no exista en USERS antes de crear (evitar conflicto con otros usuarios).
- **Code coincidente:** Asegurar que el code en PQ_PARTES_USUARIOS coincida exactamente con el code en USERS (validación explícita).
- **Password hash:** Generar password_hash correctamente antes de guardar en USERS.
- **Confirmación de contraseña:** Validar en frontend que contraseña y confirmación coincidan antes de enviar al backend.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: servicio creación con validaciones y creación User + Empleado en transacción
- [ ] Backend: endpoint POST /api/v1/empleados documentado; 201, 422, 409, 403
- [ ] Frontend: formulario creación en /empleados/nuevo con todos los campos y validaciones
- [ ] Frontend: validación de coincidencia de contraseñas en UI
- [ ] Unit tests backend ok
- [ ] Integration tests endpoint ok
- [ ] Frontend unit tests (Vitest) servicio ok
- [ ] ≥1 E2E Playwright creación empleado ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/EmpleadoService.php` - Agregado método create() con validaciones y creación User + Empleado en transacción
- `backend/app/Http/Controllers/Api/V1/EmpleadoController.php` - Agregado método store() para POST /api/v1/empleados
- `backend/routes/api.php` - Agregada ruta POST /api/v1/empleados
- `backend/tests/Feature/Api/V1/EmpleadoControllerTest.php` - Agregados tests de integración para creación (10 tests nuevos)

### Frontend
- `frontend/src/features/employees/services/empleado.service.ts` - Agregada función createEmpleado() con interfaces CreateEmpleadoBody, EmpleadoCreadoItem, CreateEmpleadoResult
- `frontend/src/features/employees/components/EmpleadosNuevoPage.tsx` - Componente de formulario de creación con todos los campos y validaciones
- `frontend/src/features/employees/components/EmpleadosNuevoPage.css` - Estilos del formulario
- `frontend/src/features/employees/components/index.ts` - Exportación de EmpleadosNuevoPage
- `frontend/src/app/App.tsx` - Agregada ruta /empleados/nuevo protegida por SupervisorRoute
- `frontend/src/features/employees/services/empleado.service.test.ts` - Agregados tests unitarios para createEmpleado() (5 tests nuevos)

### Tests
- `frontend/tests/e2e/empleados-create.spec.ts` - Tests E2E con Playwright para creación de empleados (5 tests)

### Docs

## Comandos ejecutados

```bash
# Backend - Ejecutar tests de integración
cd backend
php artisan test --filter EmpleadoControllerTest

# Frontend - Ejecutar tests unitarios
cd frontend
npm run test:run -- empleado.service.test.ts

# Frontend - Ejecutar tests E2E
cd frontend
npm run test:e2e -- empleados-create.spec.ts
```

## Notas y decisiones

- Se siguió el mismo patrón de implementación que TR-009 (creación de clientes) para mantener consistencia.
- La diferencia clave es que para empleados SIEMPRE se crea el User en USERS (no es opcional como en clientes).
- El código del empleado debe coincidir exactamente con el código del User creado (validación explícita).
- Se implementó validación en frontend para que contraseña y confirmación coincidan antes de enviar al backend.
- La contraseña siempre es obligatoria y debe tener mínimo 8 caracteres.
- El campo supervisor tiene valor por defecto false, activo true, inhabilitado false.
- Los tests E2E cubren el flujo completo de creación y validaciones de contraseñas.

## Pendientes / follow-ups

- Implementar funcionalidad de edición de empleado (TR-020) para que el botón "Editar" funcione.
- Implementar funcionalidad de eliminación de empleado (TR-021) para que el botón "Eliminar" funcione.
