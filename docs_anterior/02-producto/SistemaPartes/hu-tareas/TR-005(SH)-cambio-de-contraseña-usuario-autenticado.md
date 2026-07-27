# TR-005(SH) – Cambio de contraseña (usuario autenticado)

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-005(SH)-cambio-de-contraseña-usuario-autenticado |
| Épica              | Épica 1: Autenticación y Acceso            |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado / Empleado Supervisor / Cliente   |
| Dependencias       | HU-001 (login empleado), HU-002 (login cliente), HU-006 (perfil) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-05                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Cambio de contraseña (usuario autenticado)

### Narrativa
**Como** usuario autenticado (empleado, supervisor o cliente)  
**Quiero** cambiar mi contraseña desde mi perfil  
**Para** mantener la seguridad de mi cuenta.

### Contexto/Objetivo
El usuario ya autenticado accede a su perfil (pantalla existente TR-006) y dispone de una opción "Cambiar contraseña". Debe ingresar la contraseña actual para autorizar el cambio, luego la nueva contraseña y su confirmación. El sistema valida la contraseña actual contra USERS, valida la nueva (longitud mínima, coincidencia) y actualiza el `password_hash`. Tras el cambio, se muestra confirmación; según diseño se puede exigir volver a iniciar sesión con la nueva contraseña o mantener la sesión actual.

### Suposiciones explícitas
- La tabla `USERS` existe y contiene `password` (hash).
- La pantalla de perfil ya existe (HU-006 / TR-006); se añade la opción y el flujo de cambio de contraseña.
- El usuario está autenticado (token válido); el backend identifica al usuario por el token.
- La política de contraseña del proyecto (longitud mínima, complejidad) se aplica a la nueva contraseña.
- Opcional: tras cambiar contraseña se invalida el token actual y se redirige a login para reautenticarse (más seguro) o se mantiene la sesión (mejor UX); se debe definir en implementación.

### In Scope
- Opción "Cambiar contraseña" visible en la pantalla de perfil (enlace, botón o sección).
- Formulario o modal con: contraseña actual, nueva contraseña, confirmación de nueva contraseña.
- Validación en frontend: campos no vacíos, nueva contraseña cumple longitud mínima (ej. 8 caracteres), nueva y confirmación coinciden.
- Endpoint backend para cambio de contraseña (requiere autenticación): valida contraseña actual, valida nueva, actualiza `password_hash` en USERS.
- Mensaje de confirmación tras éxito.
- Manejo de errores: contraseña actual incorrecta, validación de nueva contraseña, errores de red.
- Decisión de diseño: re-login obligatorio tras cambio o sesión mantenida (documentar en implementación).

### Out of Scope
- Recuperación de contraseña sin estar autenticado (HU-004).
- Cambio de contraseña por un administrador sobre otro usuario (otra HU si aplica).
- Política de historial de contraseñas (no reutilizar últimas N).
- 2FA o MFA.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario autenticado puede acceder a la opción "Cambiar contraseña" desde la pantalla de perfil.
- **AC-02**: El usuario debe ingresar su contraseña actual antes de poder establecer una nueva.
- **AC-03**: El sistema valida que la contraseña actual sea correcta (comparación con hash en USERS); si no lo es, muestra error claro sin revelar datos sensibles.
- **AC-04**: El usuario ingresa la nueva contraseña y su confirmación; el sistema valida que no estén vacías.
- **AC-05**: El sistema valida que la nueva contraseña y la confirmación coincidan.
- **AC-06**: El sistema valida la nueva contraseña según política del proyecto (longitud mínima; complejidad si está definida).
- **AC-07**: Si todas las validaciones pasan, el sistema actualiza el `password_hash` del usuario en USERS.
- **AC-08**: Se muestra un mensaje de confirmación tras el cambio exitoso.
- **AC-09**: Si se define re-login obligatorio: tras el cambio se invalida el token actual y se redirige al login; el usuario inicia sesión con la nueva contraseña.
- **AC-10**: Si se define mantener sesión: el usuario sigue en perfil (o dashboard) sin tener que volver a iniciar sesión.
- **AC-11**: Un usuario no autenticado no puede acceder al endpoint de cambio de contraseña (401).

### Escenarios Gherkin

```gherkin
Feature: Cambio de contraseña (usuario autenticado)

  Scenario: Usuario cambia contraseña correctamente
    Given el usuario está autenticado
    And está en la pantalla de perfil
    When hace clic en "Cambiar contraseña"
    And ingresa su contraseña actual correcta
    And ingresa nueva contraseña y confirmación válidas (mínimo 8 caracteres, coinciden)
    And envía el formulario
    Then el sistema valida la contraseña actual
    And actualiza el password_hash en USERS
    And muestra mensaje de confirmación
    And según diseño: invalida sesión y redirige a login, o mantiene sesión

  Scenario: Contraseña actual incorrecta
    Given el usuario está autenticado
    And está en el formulario de cambio de contraseña
    When ingresa una contraseña actual incorrecta
    And ingresa nueva contraseña y confirmación válidas
    And envía el formulario
    Then el sistema no actualiza la contraseña
    And muestra error indicando que la contraseña actual es incorrecta

  Scenario: Nueva contraseña no cumple política
    Given el usuario está autenticado
    And está en el formulario de cambio de contraseña
    When ingresa contraseña actual correcta
    And ingresa nueva contraseña con menos de 8 caracteres
    And envía el formulario
    Then el sistema no actualiza la contraseña
    And muestra error de validación (longitud mínima o complejidad)

  Scenario: Usuario no autenticado
    Given el usuario no está autenticado
    When intenta llamar al endpoint de cambio de contraseña
    Then recibe 401 Unauthorized
```

---

## 3) Reglas de Negocio

1. **RN-01**: La contraseña actual debe validarse antes de permitir el cambio; si no coincide con el hash en USERS, se rechaza el cambio.
2. **RN-02**: La nueva contraseña debe cumplir la política del sistema (longitud mínima; complejidad si está definida en el proyecto).
3. **RN-03**: La nueva contraseña y su confirmación deben coincidir.
4. **RN-04**: Solo el usuario autenticado puede cambiar su propia contraseña (identificado por el token).
5. **RN-05**: Tras el cambio exitoso, se debe definir si la sesión actual se invalida (re-login obligatorio) o se mantiene; documentar en implementación.

### Permisos por Rol
- **Empleado / Supervisor / Cliente autenticados:** Pueden cambiar su propia contraseña desde su perfil.
- **No autenticado:** No puede acceder al endpoint (401).

---

## 4) Impacto en Datos

### Tablas Afectadas
- **USERS:** Se actualiza la columna `password` (hash) del usuario que realiza el cambio. No se requieren nuevas columnas ni tablas.

### Migración + Rollback
- No se requiere migración nueva; solo UPDATE sobre USERS existente.

### Seed Mínimo para Tests
- Usuario con contraseña conocida para tests de validación de contraseña actual y de actualización.

---

## 5) Contratos de API

### Endpoint: Cambiar contraseña – POST `/api/v1/auth/change-password` (o PUT `/api/v1/user/password`)

**Descripción:** El usuario autenticado envía contraseña actual, nueva contraseña y confirmación; el sistema valida y actualiza el hash en USERS.

**Autenticación:** Requerida (Bearer token). Solo el usuario identificado por el token puede cambiar su contraseña.

**Request (JSON):**
```json
{
  "current_password": "contraseñaActual123",
  "password": "nuevaContraseña456",
  "password_confirmation": "nuevaContraseña456"
}
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Contraseña actualizada correctamente.",
  "resultado": {}
}
```

**Response 401 Unauthorized:** Token ausente o inválido.

**Response 422 Unprocessable Entity:**
- Contraseña actual incorrecta (código de error de dominio si aplica).
- Validación: nueva contraseña no cumple política, contraseña y confirmación no coinciden.
- Body con `errors` por campo si aplica.

**Response 500:** Error interno.

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **ProfileView (existente):** Añadir opción "Cambiar contraseña" (botón o enlace) con `data-testid="profile.changePasswordLink"`.
- **Nuevo formulario/modal:** "Cambiar contraseña" con campos: contraseña actual, nueva contraseña, confirmación; botón Enviar y Cancelar. Puede ser sección expandible en el mismo perfil o modal/pantalla secundaria.

### Estados UI
- Loading: mientras se envía la solicitud.
- Success: mensaje de confirmación tras cambio exitoso; opcionalmente redirección a login si se invalida sesión.
- Error: contraseña actual incorrecta, validación de nueva contraseña, error de red.
- Empty: formulario inicial.

### Validaciones en UI
- Contraseña actual no vacía.
- Nueva contraseña no vacía, longitud mínima (ej. 8), coincidencia con confirmación.
- Mostrar errores por campo cuando el backend devuelva 422.

### Accesibilidad mínima
- `data-testid`: `profile.changePasswordLink`, `profile.currentPassword`, `profile.newPassword`, `profile.newPasswordConfirm`, `profile.changePasswordSubmit`, `profile.changePasswordCancel`.
- Labels y roles ARIA apropiados; campos tipo password.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | Servicio/regla cambio de contraseña | Validar contraseña actual (Hash::check); validar nueva (longitud, coincidencia); actualizar USERS.password; opcional invalidar otros tokens del usuario. | — | M |
| T2 | Backend  | Endpoint POST /auth/change-password (o PUT /user/password) | Requiere autenticación; body current_password, password, password_confirmation; 200/401/422/500. | T1 | S |
| T3 | Frontend | Opción "Cambiar contraseña" en perfil | Visible en ProfileView; abre formulario o modal. | — | S |
| T4 | Frontend | Formulario cambiar contraseña | Campos contraseña actual, nueva, confirmación; validaciones frontend; llamada API; mensaje éxito/error; opcional redirección a login si sesión invalidada. | T2 | M |
| T5 | Tests    | Unit + integration backend | Servicio: contraseña actual correcta/incorrecta; nueva válida/inválida. Endpoint: 200, 401, 422 (actual incorrecta, nueva no cumple). | T2 | M |
| T6 | Tests    | E2E Playwright cambio contraseña | Login → Perfil → Cambiar contraseña → llenar formulario válido → éxito; contraseña actual incorrecta → error. | T3, T4 | M |
| T7 | Docs     | Actualizar specs/endpoints | Documentar endpoint. | T2 | S |

**Total:** 7 tareas.

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio: contraseña actual correcta → actualiza hash; contraseña actual incorrecta → no actualiza y devuelve error; nueva contraseña corta → validación; confirmación no coincide → validación.

### Integration Tests (Backend)
- POST con token válido y datos válidos → 200.
- POST sin token o token inválido → 401.
- POST con contraseña actual incorrecta → 422.
- POST con nueva contraseña que no cumple política → 422.
- POST con password y password_confirmation distintas → 422.

### E2E (Playwright)
- Flujo: login → ir a perfil → clic "Cambiar contraseña" → llenar contraseña actual correcta, nueva y confirmación válidas → enviar → ver mensaje de éxito.
- Flujo error: contraseña actual incorrecta → ver mensaje de error.

---

## 9) Riesgos y Edge Cases

- **Seguridad:** No revelar en mensajes si la contraseña actual es incorrecta de forma que facilite enumeración; mensaje genérico "Contraseña actual incorrecta" es aceptable.
- **Sesión tras cambio:** Decidir si invalidar todos los tokens del usuario (re-login obligatorio) o solo el actual; documentar en implementación.
- **Política de contraseña:** Alinear longitud mínima y complejidad con el resto del sistema (ej. registro, recuperación HU-004).

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend: endpoint cambio contraseña listo + validaciones + 401/422
- [x] Frontend: opción en perfil + formulario + mensajes éxito/error
- [x] Unit tests backend ok
- [x] Integration tests endpoint ok
- [x] ≥1 E2E Playwright flujo cambio contraseña ok (profile-change-password.spec.ts; requiere backend + frontend y usuario JPEREZ sembrado)
- [x] Docs/specs actualizados

---

## Archivos creados/modificados

### Backend
- `app/Http/Requests/Auth/ChangePasswordRequest.php` (nuevo)
- `app/Services/AuthService.php` (changePassword, ERROR_CURRENT_PASSWORD_INVALID 3204)
- `app/Http/Controllers/Api/V1/AuthController.php` (changePassword)
- `routes/api.php` (POST /api/v1/auth/change-password)
- `tests/Unit/Services/AuthServiceTest.php` (4 tests changePassword)
- `tests/Feature/Api/V1/Auth/ChangePasswordTest.php` (nuevo, 6 tests)

### Frontend
- `src/features/user/services/user.service.ts` (changePassword)
- `src/features/user/components/ProfileView.tsx` (opción Cambiar contraseña, formulario)
- `src/features/user/components/ProfileView.css` (estilos sección cambio contraseña)

### Docs
- `specs/endpoints/auth-change-password.md` (nuevo)

### Tests
- `frontend/tests/e2e/profile-change-password.spec.ts` (nuevo, 3 tests serial)

## Comandos ejecutados

- `php artisan test tests/Feature/Api/V1/Auth/ChangePasswordTest.php` (6 passed)
- `npx playwright test tests/e2e/profile-change-password.spec.ts` (requiere backend + frontend y usuario JPEREZ con password123)

## Notas y decisiones

- **Sesión tras cambio:** Se mantiene la sesión actual (no se invalida el token); el usuario sigue en perfil.
- **E2E:** Los tests E2E de cambio de contraseña usan test.describe.serial; el tercer test restaura la contraseña de JPEREZ a password123 para no afectar a user-profile.spec.ts y auth-login.spec.ts.

## Pendientes / follow-ups

- Ninguno.
