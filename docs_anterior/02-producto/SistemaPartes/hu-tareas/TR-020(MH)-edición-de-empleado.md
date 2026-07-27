# TR-020(MH) – Edición de Empleado

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-020(MH)-edición-de-empleado             |
| Épica              | Épica 5: Gestión de Empleados (ABM)        |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-019 (creación de empleado), HU-018 (listado) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-05                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Edición de Empleado

### Narrativa
**Como** supervisor  
**Quiero** editar la información de un empleado existente  
**Para** mantener actualizados sus datos

### Contexto/Objetivo
El supervisor accede a la edición de un empleado desde el listado (HU-018). Se carga el formulario con los datos actuales del empleado. El código no es modificable (solo lectura). Se pueden editar nombre, email, supervisor, estado activo e inhabilitado. Se puede cambiar la contraseña de forma opcional (con campos separados para contraseña y confirmación). Los cambios de estado (activo/inhabilitado) se sincronizan entre USERS y PQ_PARTES_USUARIOS. La contraseña solo se actualiza si se proporciona una nueva.

### Suposiciones explícitas
- El usuario ya está autenticado como supervisor (HU-001).
- Existe el listado de empleados (HU-018) con enlace "Editar" por empleado.
- Las tablas `USERS` y `PQ_PARTES_USUARIOS` existen.
- El empleado siempre tiene un registro en USERS (user_id obligatorio).
- El code en PQ_PARTES_USUARIOS coincide con el code en USERS.

### In Scope
- Formulario de edición accesible desde listado (ruta ej. /empleados/:id/editar).
- GET /api/v1/empleados/{id} para cargar datos actuales del empleado.
- Código en solo lectura (no modificable).
- Campos editables: nombre (obligatorio), email (opcional, formato válido, único si cambió), supervisor (checkbox), activo (checkbox), inhabilitado (checkbox).
- Opción "Cambiar contraseña" (password opcional; si se proporciona, actualizar USERS.password_hash; validar confirmación de contraseña en frontend).
- Si se cambia activo o inhabilitado: actualizar PQ_PARTES_USUARIOS y USERS.
- Validación: code no modificable (ignorar si se envía en body).
- Mensaje de confirmación y redirección al listado; cambios visibles en el listado.

### Out of Scope
- Creación de empleado (HU-019).
- Eliminación de empleado (otras HU del ABM).
- Modificación del código del empleado.
- Visualización de detalle de empleado.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la edición de un empleado desde el listado (ej. enlace "Editar" o ruta /empleados/:id/editar).
- **AC-02**: Un usuario no supervisor no puede acceder (403 o redirección).
- **AC-03**: Se carga el formulario con los datos actuales del empleado (nombre, email, supervisor, activo, inhabilitado; código en solo lectura).
- **AC-04**: El código del empleado no es modificable (solo lectura).
- **AC-05**: El supervisor puede modificar: nombre, email, supervisor, estado activo, estado inhabilitado.
- **AC-06**: El supervisor puede cambiar la contraseña (opcional, con campos separados para contraseña y confirmación).
- **AC-07**: El sistema valida que el nombre no esté vacío.
- **AC-08**: El sistema valida que el email tenga formato válido (si se proporciona) y sea único si cambió.
- **AC-09**: Si se cambia la contraseña, el sistema valida las mismas reglas que en creación (mínimo 8 caracteres, contraseña y confirmación coinciden).
- **AC-10**: Si se cambia la contraseña, el sistema actualiza el password_hash en USERS (no en PQ_PARTES_USUARIOS).
- **AC-11**: Si se cambia el estado activo o inhabilitado, el sistema actualiza ambos: USERS y PQ_PARTES_USUARIOS.
- **AC-12**: El sistema valida que el code no se pueda modificar (es identificador único y debe coincidir con User.code).
- **AC-13**: Al guardar, el sistema actualiza el empleado en la base de datos.
- **AC-14**: Se muestra mensaje de confirmación y los cambios se reflejan en el listado.

### Escenarios Gherkin

```gherkin
Feature: Edición de Empleado

  Scenario: Supervisor edita datos básicos de un empleado
    Given el supervisor está autenticado
    And existe un empleado con id 1
    When accede a editar el empleado 1
    Then se carga el formulario con los datos actuales
    And el código está en solo lectura
    When modifica el nombre a "Juan Pérez Actualizado"
    And modifica el email a "nuevo@ejemplo.com"
    And modifica supervisor a true
    And hace clic en "Guardar"
    Then el empleado se actualiza en PQ_PARTES_USUARIOS
    And se muestra mensaje de confirmación
    And los cambios se reflejan en el listado

  Scenario: Supervisor cambia contraseña de empleado
    Given el supervisor está autenticado
    And existe un empleado con id 1
    When accede a editar el empleado 1
    Then ve la opción "Cambiar contraseña"
    When introduce nueva contraseña "nuevaPass123" y confirmación "nuevaPass123"
    And hace clic en "Guardar"
    Then se actualiza USERS.password_hash para ese usuario
    And el empleado se actualiza correctamente

  Scenario: Contraseñas no coinciden
    Given el supervisor está en el formulario de edición
    When introduce contraseña "password123"
    And introduce confirmación "password456"
    And hace clic en "Guardar"
    Then el sistema valida y muestra error (las contraseñas no coinciden)
    And no se actualiza el empleado

  Scenario: Cambio de estado activo/inhabilitado sincroniza USERS
    Given un empleado con id 1
    When el supervisor edita y cambia activo a false
    And cambia inhabilitado a true
    And guarda
    Then se actualiza activo e inhabilitado en PQ_PARTES_USUARIOS
    And se actualiza activo e inhabilitado en USERS para el user_id del empleado

  Scenario: Email duplicado
    Given existe un empleado con email "juan@ejemplo.com"
    When el supervisor intenta editar otro empleado y cambiar su email a "juan@ejemplo.com"
    Then el sistema retorna error 422 o 409 (email ya existe)
    And no se actualiza el empleado

  Scenario: Empleado no encontrado
    Given el supervisor está autenticado
    When intenta acceder a editar empleado con id inexistente
    Then recibe 404 (empleado no encontrado)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden editar empleados (endpoint y pantalla protegidos).
2. **RN-02**: El código (`code`) no es modificable; se ignora si se envía en el body del PUT.
3. **RN-03**: `nombre` es obligatorio.
4. **RN-04**: `email` es opcional; si se proporciona, formato válido y único (excluyendo el propio empleado).
5. **RN-05**: La contraseña se almacena en USERS, no en PQ_PARTES_USUARIOS. Al cambiar contraseña se actualiza USERS.password_hash. La contraseña solo se actualiza si se proporciona una nueva.
6. **RN-06**: Cambios de activo e inhabilitado se sincronizan: actualizar PQ_PARTES_USUARIOS y USERS.
7. **RN-07**: Empleado inexistente → 404 (4003).
8. **RN-08**: Códigos de error: 422 (validación), 409 (conflicto email), 403 (no supervisor), 404 (no encontrado), 1104 (contraseña muy corta), 1106 (nombre requerido), 1108 (email formato inválido).

### Permisos por Rol
- **Supervisor:** Acceso completo al formulario de edición y a GET/PUT /api/v1/empleados/{id}.
- **Empleado (no supervisor):** Sin acceso; 403 o redirección.
- **Cliente:** No aplica (no editan empleados).

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_USUARIOS` (o equivalente): SELECT (GET por id), UPDATE (nombre, email, supervisor, activo, inhabilitado).
- `USERS`: SELECT (para obtener datos del empleado), UPDATE (password_hash si cambia contraseña; activo, inhabilitado si cambian).

### Cambios en Datos
- No se requieren nuevas tablas ni columnas. UPDATE en PQ_PARTES_USUARIOS y USERS.

### Migración + Rollback
- No se requiere migración nueva para esta tarea.

### Seed Mínimo para Tests
- Empleado existente con datos completos. Usuario supervisor. Empleado con email para tests de duplicados.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/empleados/{id}`

**Descripción:** Obtener datos de un empleado para edición. Solo supervisores.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. Si no es supervisor → 403 (3101).

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Empleado obtenido correctamente",
  "resultado": {
    "id": 1,
    "code": "JPEREZ",
    "nombre": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "supervisor": false,
    "activo": true,
    "inhabilitado": false,
    "created_at": "2025-01-15T10:00:00Z",
    "updated_at": "2025-01-15T10:00:00Z"
  }
}
```

**Response 404 Not Found:** Empleado no encontrado (4003).

**Response 403 Forbidden:** No supervisor (3101).

---

### Endpoint: PUT `/api/v1/empleados/{id}`

**Descripción:** Actualizar un empleado existente. Solo supervisores. El código no es modificable.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. Si no es supervisor → 403 (3101).

**Request Body:**
```json
{
  "nombre": "Juan Pérez Actualizado",
  "email": "nuevo@ejemplo.com",
  "password": "nueva_contraseña123",
  "supervisor": true,
  "activo": true,
  "inhabilitado": false
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| nombre | string | Sí | Nombre completo del empleado. No vacío (1106). |
| email | string | No | Email del empleado. Formato válido, único si cambió (1108, 4102). |
| password | string | No | Nueva contraseña. Solo si se quiere cambiar. Mínimo 8 caracteres si se proporciona (1104). |
| supervisor | boolean | No | Indica si es supervisor. |
| activo | boolean | No | Estado activo. |
| inhabilitado | boolean | No | Estado inhabilitado. |

**Nota:** El campo `code` no es modificable y se ignora si se envía en el body.

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Empleado actualizado correctamente",
  "resultado": {
    "id": 1,
    "code": "JPEREZ",
    "nombre": "Juan Pérez Actualizado",
    "email": "nuevo@ejemplo.com",
    "supervisor": true,
    "activo": true,
    "inhabilitado": false,
    "updated_at": "2025-01-20T11:00:00Z"
  }
}
```

**Response 401 Unauthorized:** No autenticado (3001).

**Response 403 Forbidden:** No supervisor (3101).

**Response 404 Not Found:** Empleado no encontrado (4003).

**Response 422 Unprocessable Entity – Validación:**
```json
{
  "error": 422,
  "respuesta": "Error de validación",
  "resultado": {
    "errors": {
      "nombre": ["El nombre es obligatorio."],
      "password": ["La contraseña debe tener al menos 8 caracteres."]
    }
  }
}
```
- 1104: Contraseña muy corta (mínimo 8 caracteres)
- 1106: Nombre requerido o vacío
- 1108: Email formato inválido

**Response 409 Conflict:**
```json
{
  "error": 409,
  "respuesta": "El email ya está registrado",
  "resultado": {}
}
```
- 4102: Email duplicado

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **EmpleadosEditarPage** (o **EditarEmpleadoPage**): formulario de edición, ruta /empleados/:id/editar, protegida por SupervisorRoute.
- **Formulario:** código en solo lectura; campos editables: nombre, email, supervisor (checkbox), activo (checkbox), inhabilitado (checkbox). Opción "Cambiar contraseña" (inputs password y confirmación opcionales). Botones Guardar y Cancelar.
- **Navegación:** desde listado (HU-018) enlace "Editar" por fila que lleva a /empleados/:id/editar.

### Estados UI
- Loading: mientras se cargan datos (GET) o se envía el formulario (PUT).
- Error: 404 (empleado no encontrado), 403, 422 (errores por campo), 409.
- Success: mensaje de confirmación y redirección al listado.

### Validaciones en UI
- Nombre no vacío. Email formato válido si se completa. Si se muestra "Cambiar contraseña" y se completa, mínimo 8 caracteres y contraseña y confirmación deben coincidir. Mostrar errores devueltos por la API.

### Accesibilidad Mínima
- `data-testid` en: formulario (empleados.edit.form), código (empleados.edit.code), nombre (empleados.edit.nombre), email (empleados.edit.email), password (empleados.edit.password), passwordConfirm (empleados.edit.passwordConfirm), supervisor (empleados.edit.supervisor), activo (empleados.edit.activo), inhabilitado (empleados.edit.inhabilitado), botón guardar (empleados.edit.submit), botón cancelar (empleados.edit.cancel).
- Labels y roles ARIA apropiados.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | EmpleadoService o UsuarioService::update() | Validaciones: nombre, email, password (opcional). Actualizar PQ_PARTES_USUARIOS y USERS (password_hash si cambia contraseña; activo/inhabilitado sincronizados). Validar code no modificable. Códigos error 422, 409, 404, 403. | HU-019, HU-018 | L |
| T2 | Backend  | EmpleadoController::show() | GET /api/v1/empleados/{id}; validar supervisor; retornar datos empleado; 200, 404, 403. | — | S |
| T3 | Backend  | EmpleadoController::update() | PUT /api/v1/empleados/{id}; validación request; llamar servicio; 200, 422, 409, 404, 403. Solo supervisor. | T1 | M |
| T4 | Backend  | Tests unitarios servicio edición | Actualizar nombre, email, supervisor; cambiar contraseña; email duplicado; nombre vacío; contraseña corta; sincronización activo/inhabilitado entre tablas. | T1 | M |
| T5 | Backend  | Tests integración GET /empleados/{id} | 200 como supervisor; 404 empleado inexistente; 403 no supervisor; 401 sin token. | T2 | S |
| T6 | Backend  | Tests integración PUT /empleados/{id} | 200 actualización correcta; 422 validación; 409 email duplicado; 404 no encontrado; 403 no supervisor; 401 sin token. | T3 | M |
| T7 | Frontend | Servicio empleado.service.ts getEmpleado() | GET con id; manejo 200, 404, 403. | — | S |
| T8 | Frontend | Servicio empleado.service.ts updateEmpleado() | PUT con id y body; manejo 200, 422, 409, 404, 403. | — | S |
| T9 | Frontend | EmpleadosEditarPage / EditarEmpleadoPage | Formulario con código en solo lectura; campos editables; opción cambiar contraseña; validaciones UI; submit y redirección. data-testid. | HU-018 | M |
| T10| Frontend | Integración formulario con API | Cargar datos con getEmpleado; llamar updateEmpleado; mostrar errores por campo; mensaje éxito y redirección al listado. | T7, T8, T9 | M |
| T11| Tests    | E2E Playwright edición empleado | Login supervisor → Empleados → Editar empleado → modificar datos → guardar → ver cambios en listado. | T9 | M |
| T12| Tests    | E2E cambio contraseña y validación | Cambiar contraseña; probar contraseñas no coinciden → ver error; probar email duplicado → ver error. | T9 | S |
| T13| Frontend | Tests unit (Vitest) servicios | getEmpleado(id), updateEmpleado(id, body), manejo 200, 404, 422, 409. | T7, T8 | S |
| T14| Docs     | Actualizar specs/endpoints/empleados-update.md | Verificar que refleje code no modificable; códigos 1104, 1106, 1108, 4102, 4003. | T3 | S |

**Total:** 14 tareas (5S + 8M + 1L).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio update: actualizar nombre, email, supervisor; cambiar contraseña (actualizar USERS.password_hash); email duplicado → excepción/409; nombre vacío → 422 (1106); contraseña corta si se proporciona → 422 (1104); sincronización activo/inhabilitado entre PQ_PARTES_USUARIOS y USERS; validar code no modificable.

### Integration Tests (Backend)
- GET /api/v1/empleados/{id} como supervisor → 200, datos del empleado.
- GET con id inexistente → 404.
- GET como empleado no supervisor → 403.
- PUT /api/v1/empleados/{id} como supervisor con body válido → 200, empleado actualizado.
- PUT con email ya existente (otro empleado) → 409 (4102).
- PUT sin nombre → 422 (1106).
- PUT con contraseña corta → 422 (1104).
- PUT con id inexistente → 404.
- PUT como empleado no supervisor → 403.
- PUT sin token → 401.

### Frontend Unit Tests (Vitest)
- getEmpleado: maneja 200 (éxito); maneja 404 (no encontrado); maneja 403.
- updateEmpleado: envía body correcto; maneja 200 (éxito); maneja 422 (errores por campo); maneja 409 (mensaje conflicto); maneja 404.

### E2E Tests (Playwright)
- Supervisor → Empleados → Editar empleado → modificar nombre, email, supervisor → Guardar → redirección al listado y cambios visibles.
- Supervisor → Editar empleado → cambiar contraseña → Guardar → éxito.
- Editar empleado con email existente → ver mensaje de error (409/422).
- Editar empleado con contraseñas que no coinciden → ver error de validación en UI.

---

## 9) Riesgos y Edge Cases

- **Sincronización de estados:** Asegurar que cambios de activo e inhabilitado se reflejen en ambas tablas (PQ_PARTES_USUARIOS y USERS) en la misma transacción.
- **Code no modificable:** Validar explícitamente que el code no se pueda modificar; ignorar si se envía en el body del PUT.
- **Password hash:** Solo actualizar password_hash en USERS si se proporciona una nueva contraseña; no actualizar si el campo password está vacío o no se envía.
- **Email único:** Al validar email único, excluir el propio empleado (WHERE email = ? AND id != ?).
- **Confirmación de contraseña:** Validar en frontend que contraseña y confirmación coincidan antes de enviar al backend.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: servicio edición con validaciones y actualización en PQ_PARTES_USUARIOS y USERS
- [ ] Backend: endpoints GET y PUT /api/v1/empleados/{id} documentados; 200, 422, 409, 404, 403
- [ ] Frontend: formulario edición en /empleados/:id/editar con código en solo lectura y campos editables
- [ ] Frontend: opción cambiar contraseña con validación de confirmación
- [ ] Frontend: sincronización de estados entre tablas
- [ ] Unit tests backend ok
- [ ] Integration tests endpoints ok
- [ ] Frontend unit tests (Vitest) servicios ok
- [ ] ≥1 E2E Playwright edición empleado ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/EmpleadoService.php` – Agregados métodos getById() y update().
- `backend/app/Http/Controllers/Api/V1/EmpleadoController.php` – Agregados métodos show() y update().
- `backend/routes/api.php` – Agregadas rutas GET y PUT /api/v1/empleados/{id}.
- `backend/tests/Feature/Api/V1/EmpleadoControllerTest.php` – Agregados 12 tests de integración.

### Frontend
- `frontend/src/features/employees/services/empleado.service.ts` – Agregadas funciones getEmpleado() y updateEmpleado().
- `frontend/src/features/employees/components/EmpleadosEditarPage.tsx` – Componente nuevo.
- `frontend/src/features/employees/components/EmpleadosNuevoPage.css` – Agregado estilo para input readonly.
- `frontend/src/features/employees/components/index.ts` – Exportación de EmpleadosEditarPage.
- `frontend/src/app/App.tsx` – Ruta /empleados/:id/editar protegida por SupervisorRoute.
- `frontend/src/features/employees/services/empleado.service.test.ts` – Agregados 8 tests unitarios.
- `frontend/tests/e2e/empleados-edit.spec.ts` – Tests E2E nuevos.

### Docs
- `docs/04-tareas/TR-020(MH)-edición-de-empleado.md` – Estado actualizado a COMPLETADO.

## Comandos ejecutados

```bash
# Backend tests
cd backend && php artisan test --filter EmpleadoControllerTest

# Frontend unit tests
cd frontend && npm run test:run -- empleado.service.test.ts

# Frontend E2E tests
cd frontend && npm run test:e2e -- empleados-edit.spec.ts
```

## Notas y decisiones

- La implementación sigue el patrón de TR-010 (edición de clientes) adaptado para empleados.
- La sincronización de estados (activo, inhabilitado) entre USERS y PQ_PARTES_USUARIOS se realiza en una transacción para garantizar consistencia.
- El campo code se ignora explícitamente en el método update() del controller para evitar modificaciones accidentales.
- La contraseña solo se actualiza si se proporciona una nueva en el body del PUT.
- Los tests E2E verifican el flujo completo desde el listado hasta la edición y confirmación.

## Pendientes / follow-ups

- Ninguno. La tarea TR-020(MH) está completa.
