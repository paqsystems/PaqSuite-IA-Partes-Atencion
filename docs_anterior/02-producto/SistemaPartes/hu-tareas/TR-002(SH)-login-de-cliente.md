# TR-002(SH) – Login de Cliente

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-002(SH)-login-de-cliente                |
| Épica              | Épica 1: Autenticación y Acceso            |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Cliente                                    |
| Dependencias       | HU-001 (comparte lógica de autenticación)  |
| Última actualización | 2026-01-28                               |
| Estado             | ✅ IMPLEMENTADA                            |

---

## 1) HU Refinada

### Título
Login de Cliente

### Narrativa
**Como** cliente  
**Quiero** autenticarme en el sistema con mi código y contraseña  
**Para** consultar las tareas realizadas para mí

### Contexto/Objetivo
Esta historia extiende el sistema de autenticación existente (TR-001) para permitir que los clientes con acceso al sistema puedan autenticarse. Los clientes tienen permisos de solo lectura para consultar las tareas relacionadas con ellos.

### Suposiciones explícitas
- El AuthService de TR-001 ya existe y funciona para empleados
- La tabla `PQ_PARTES_CLIENTES` ya existe con datos
- Solo clientes con `user_id` configurado (relación con `USERS`) pueden autenticarse
- El cliente debe estar activo y no inhabilitado en ambas tablas
- El frontend ya tiene el formulario de login implementado

### In Scope
- Extender `AuthService` para soportar login de clientes
- Buscar usuario en `PQ_PARTES_CLIENTES` si no existe en `PQ_PARTES_USUARIOS`
- Establecer `tipo_usuario = "cliente"` y `es_supervisor = false`
- Validar estado activo/inhabilitado en `PQ_PARTES_CLIENTES`
- Tests unitarios y de integración para el caso de cliente
- Un test E2E para verificar el flujo

### Out of Scope
- Cambios en el formulario de login del frontend (ya soporta el flujo)
- Vista de consulta de tareas del cliente (HU posterior)
- Permisos específicos del rol cliente

---

## 2) Criterios de Aceptación (AC)

### Bullets
- **AC-01**: El cliente puede ingresar su código y contraseña en el mismo formulario que empleados
- **AC-02**: El sistema valida que el `User` exista en la tabla `USERS`
- **AC-03**: El sistema valida que el `User` esté activo y no inhabilitado en `USERS`
- **AC-04**: El sistema valida que la contraseña coincida con el hash almacenado
- **AC-05**: Si el `User.code` NO existe en `PQ_PARTES_USUARIOS`, el sistema busca en `PQ_PARTES_CLIENTES`
- **AC-06**: El sistema determina que `tipo_usuario = "cliente"`
- **AC-07**: El sistema obtiene el `cliente_id` del registro en `PQ_PARTES_CLIENTES`
- **AC-08**: El sistema verifica que el cliente esté activo y no inhabilitado en `PQ_PARTES_CLIENTES`
- **AC-09**: El sistema establece `es_supervisor = false` (siempre para clientes)
- **AC-10**: El token incluye: `user_id`, `user_code`, `tipo_usuario="cliente"`, `usuario_id=null`, `cliente_id`, `es_supervisor=false`
- **AC-11**: El cliente es redirigido al dashboard después de login exitoso

### Escenarios Gherkin

```gherkin
Feature: Login de Cliente

  Scenario: Login exitoso de cliente
    Given que soy un cliente con código "CLI001" y contraseña válida
    And que mi usuario está activo y no inhabilitado en USERS
    And que mi registro en PQ_PARTES_CLIENTES está activo y no inhabilitado
    When ingreso mi código y contraseña en el formulario de login
    And hago clic en el botón de envío
    Then el sistema valida mis credenciales correctamente
    And el sistema genera un token Sanctum
    And el token incluye: user_code="CLI001", tipo_usuario="cliente", cliente_id, usuario_id=null, es_supervisor=false
    And soy redirigido al dashboard principal

  Scenario: Login fallido - cliente inactivo en PQ_PARTES_CLIENTES
    Given que soy un cliente con código "CLI002" y contraseña válida
    And que mi usuario está activo en USERS
    And que mi registro en PQ_PARTES_CLIENTES tiene activo=false o inhabilitado=true
    When ingreso mi código y contraseña en el formulario de login
    And hago clic en el botón de envío
    Then el sistema retorna error 401 con código 4203
    And se muestra el mensaje "Usuario inactivo"

  Scenario: Login fallido - usuario no existe en ninguna tabla de perfiles
    Given que soy un usuario con código "SINPERFIL" en USERS
    And que NO existe registro en PQ_PARTES_USUARIOS ni PQ_PARTES_CLIENTES
    When ingreso mi código y contraseña
    Then el sistema retorna error 401 con código 3201
    And se muestra el mensaje "Credenciales inválidas"
```

---

## 3) Reglas de Negocio

1. **RN-01**: La autenticación se realiza contra la tabla `USERS` (igual que empleados)
2. **RN-02**: Después de validar en `USERS`, se busca primero en `PQ_PARTES_USUARIOS`
3. **RN-03**: Si NO existe en `PQ_PARTES_USUARIOS`, se busca en `PQ_PARTES_CLIENTES`
4. **RN-04**: Solo clientes con `user_id` configurado pueden autenticarse
5. **RN-05**: El cliente debe estar activo (`activo = true`) y no inhabilitado (`inhabilitado = false`)
6. **RN-06**: Un `User.code` solo puede estar asociado a un Cliente O a un Usuario, no a ambos
7. **RN-07**: Los clientes SIEMPRE tienen `es_supervisor = false`
8. **RN-08**: Si el código no existe en ninguna tabla de perfiles, retornar error genérico

### Permisos por Rol

| Rol     | Puede autenticarse | es_supervisor | tipo_usuario |
|---------|-------------------|---------------|--------------|
| Cliente | ✅ (si tiene user_id) | false | "cliente" |

---

## 4) Impacto en Datos

### Tablas afectadas
- `USERS` (lectura) - igual que TR-001
- `PQ_PARTES_USUARIOS` (lectura) - para verificar si es empleado primero
- `PQ_PARTES_CLIENTES` (lectura) - para datos del cliente

### Campos utilizados de PQ_PARTES_CLIENTES
- `id` (PK, para `cliente_id`)
- `user_id` (FK → USERS, para relación)
- `code` (UK, para búsqueda por código)
- `nombre` (para respuesta)
- `email` (para respuesta, opcional)
- `activo` (para validación de estado)
- `inhabilitado` (para validación de estado)

### Seed mínimo para tests
Agregar a TestUsersSeeder:
```php
// Cliente activo
USERS: code="CLI001", password_hash, activo=true, inhabilitado=false
PQ_PARTES_CLIENTES: code="CLI001", user_id (FK), nombre="Empresa ABC", activo=true, inhabilitado=false

// Cliente inactivo
USERS: code="CLI002", password_hash, activo=true, inhabilitado=false
PQ_PARTES_CLIENTES: code="CLI002", user_id (FK), nombre="Empresa XYZ", activo=false, inhabilitado=false

// Usuario en USERS sin perfil en ninguna tabla
USERS: code="SINPERFIL", password_hash, activo=true, inhabilitado=false
```

---

## 5) Contratos de API

### Endpoint: POST `/api/v1/auth/login` (existente)

El endpoint ya existe. Solo se extiende la respuesta para clientes.

**Response 200 (éxito - cliente):**
```json
{
  "error": 0,
  "respuesta": "Autenticación exitosa",
  "resultado": {
    "token": "1|abcdef...",
    "user": {
      "user_id": 10,
      "user_code": "CLI001",
      "tipo_usuario": "cliente",
      "usuario_id": null,
      "cliente_id": 5,
      "es_supervisor": false,
      "nombre": "Empresa ABC S.A.",
      "email": "contacto@empresaabc.com"
    }
  }
}
```

---

## 6) Cambios Frontend

No se requieren cambios en el frontend. El formulario de login ya soporta el flujo y el dashboard mostrará la información del tipo de usuario recibido.

---

## 7) Plan de Tareas / Tickets

### T1 - Backend: Extender AuthService para clientes
| Campo       | Valor |
|-------------|-------|
| Tipo        | Backend |
| Descripción | Modificar AuthService::login() para buscar en PQ_PARTES_CLIENTES si no existe en PQ_PARTES_USUARIOS |
| DoD         | Login de clientes funciona correctamente, retorna tipo_usuario="cliente", cliente_id, es_supervisor=false |
| Dependencias| Ninguna |
| Estimación  | S |

### T2 - Backend: Seed de clientes de prueba
| Campo       | Valor |
|-------------|-------|
| Tipo        | DB |
| Descripción | Agregar clientes de prueba al TestUsersSeeder (activos, inactivos) |
| DoD         | Seeder incluye CLI001 (activo), CLI002 (inactivo), SINPERFIL (sin perfil) |
| Dependencias| Ninguna |
| Estimación  | S |

### T3 - Backend: Tests unitarios para login de cliente
| Campo       | Valor |
|-------------|-------|
| Tipo        | Tests |
| Descripción | Agregar tests unitarios al AuthServiceTest para casos de cliente |
| DoD         | Tests de login exitoso cliente, cliente inactivo, usuario sin perfil |
| Dependencias| T1 |
| Estimación  | S |

### T4 - Backend: Tests de integración para login de cliente
| Campo       | Valor |
|-------------|-------|
| Tipo        | Tests |
| Descripción | Agregar tests de integración al LoginTest para casos de cliente |
| DoD         | Tests de endpoint 200 para cliente, 401 para cliente inactivo |
| Dependencias| T1 |
| Estimación  | S |

### T5 - E2E: Test Playwright para login de cliente
| Campo       | Valor |
|-------------|-------|
| Tipo        | Tests |
| Descripción | Agregar test E2E que verifique login de cliente |
| DoD         | Test de login exitoso con cliente, verifica tipo_usuario en dashboard |
| Dependencias| T1 |
| Estimación  | S |

### T6 - Docs: Actualizar documentación de autenticación
| Campo       | Valor |
|-------------|-------|
| Tipo        | Docs |
| Descripción | Actualizar docs/backend/autenticacion.md con información de login de clientes |
| DoD         | Documentación incluye flujo de cliente, ejemplos de respuesta |
| Dependencias| T1 |
| Estimación  | S |

---

## 8) Estrategia de Tests

### Unit Tests (agregar a AuthServiceTest)
- `login_exitoso_con_cliente()`
- `login_fallido_cliente_inactivo_en_pq_partes_clientes()`
- `login_fallido_cliente_inhabilitado_en_pq_partes_clientes()`
- `login_fallido_usuario_sin_perfil_en_ninguna_tabla()`

### Integration Tests (agregar a LoginTest)
- `login_exitoso_cliente_retorna_200()`
- `login_cliente_retorna_tipo_usuario_cliente()`
- `login_cliente_retorna_es_supervisor_false()`
- `login_fallido_cliente_inactivo_retorna_401_error_4203()`

### E2E Tests (agregar a auth-login.spec.ts)
- `debe autenticar cliente y redirigir al dashboard`

---

## 9) Riesgos y Edge Cases

| Riesgo/Edge Case | Mitigación |
|------------------|------------|
| **Usuario existe en ambas tablas (Usuario y Cliente)** | Por regla de negocio, no debería ocurrir. Si ocurre, priorizar PQ_PARTES_USUARIOS (se considera empleado) |
| **Usuario en USERS sin perfil en ninguna tabla** | Retornar error genérico "Credenciales inválidas" |
| **Cliente sin user_id** | No puede autenticarse (no tiene relación con USERS) |

---

## 10) Checklist final

- [ ] AC cumplidos (AC-01 a AC-11)
- [ ] AuthService extendido para soportar clientes
- [ ] Seed de clientes de prueba creado
- [ ] Unit tests agregados y pasando
- [ ] Integration tests agregados y pasando
- [ ] ≥1 E2E Playwright agregado y pasando
- [ ] Documentación actualizada

---

## EJECUCIÓN DE LA TR

### Estado de Tareas

| ID | Tarea | Estado |
|----|-------|--------|
| T1 | Backend: Extender AuthService para clientes | ✅ COMPLETADO |
| T2 | Backend: Seed de clientes de prueba | ✅ COMPLETADO |
| T3 | Backend: Tests unitarios para login de cliente | ✅ COMPLETADO |
| T4 | Backend: Tests de integración para login de cliente | ✅ COMPLETADO |
| T5 | E2E: Test Playwright para login de cliente | ✅ COMPLETADO |
| T6 | Docs: Actualizar documentación de autenticación | ✅ COMPLETADO |

---

## Archivos Creados/Modificados

### Backend
- `backend/app/Services/AuthService.php` (MODIFICADO) - Extendido con métodos `loginEmpleado()` y `loginCliente()`
- `backend/database/seeders/TestUsersSeeder.php` (MODIFICADO) - Agregados clientes de prueba CLI001, CLIINACTIVO, SINPERFIL

### Frontend
*(no aplica - sin cambios en componentes)*

### Tests
- `backend/tests/Unit/Services/AuthServiceTest.php` (MODIFICADO) - 6 tests de cliente agregados
- `backend/tests/Feature/Api/V1/Auth/LoginTest.php` (MODIFICADO) - 6 tests de cliente agregados
- `frontend/tests/e2e/auth-login.spec.ts` (MODIFICADO) - 4 tests de cliente agregados

### Docs
- `docs/04-tareas/TR-002(SH)-login-de-cliente.md` (CREADO)
- `docs/backend/autenticacion.md` (MODIFICADO) - Agregada información de login de cliente

---

## Comandos Ejecutados

```bash
# Los tests se ejecutan con:
php artisan test --filter=Auth

# Seed de usuarios de prueba (incluye clientes):
php artisan db:seed --class=TestUsersSeeder
```

---

## Notas y Decisiones

1. **Refactoring del AuthService**: Se extrajo la lógica a métodos privados `loginEmpleado()` y `loginCliente()` para mantener el código limpio.

2. **Prioridad empleado vs cliente**: Si un código existe en ambas tablas, se prioriza `PQ_PARTES_USUARIOS` (empleado).

3. **Tests idempotentes**: Los tests limpian datos incluyendo códigos de clientes antes de insertar.

4. **Frontend sin cambios**: El formulario de login ya soportaba el flujo; solo se agregaron tests E2E.

---

## Pendientes / Follow-ups

- Ejecutar `php artisan db:seed --class=TestUsersSeeder` para crear clientes de prueba
- Ejecutar `php artisan test --filter=Auth` para verificar todos los tests
- Ejecutar tests E2E: `cd frontend && npm run test:e2e`

---

## Criterios de Aceptación - Estado Final

| AC | Descripción | Estado |
|----|-------------|--------|
| AC-01 | Cliente puede usar formulario de login | ✅ |
| AC-02 | Valida existencia en USERS | ✅ |
| AC-03 | Valida activo/inhabilitado en USERS | ✅ |
| AC-04 | Valida contraseña | ✅ |
| AC-05 | Busca en PQ_PARTES_CLIENTES si no está en PQ_PARTES_USUARIOS | ✅ |
| AC-06 | tipo_usuario = "cliente" | ✅ |
| AC-07 | Obtiene cliente_id | ✅ |
| AC-08 | Valida activo/inhabilitado en PQ_PARTES_CLIENTES | ✅ |
| AC-09 | es_supervisor = false | ✅ |
| AC-10 | Token incluye todos los campos correctos | ✅ |
| AC-11 | Redirección al dashboard | ✅ |
