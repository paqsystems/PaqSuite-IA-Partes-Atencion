# TR-004(SH) – Recuperación de contraseña

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-004(SH)-recuperación-de-contraseña      |
| Épica              | Épica 1: Autenticación y Acceso            |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado / Empleado Supervisor / Cliente   |
| Dependencias       | HU-001 (login empleado), HU-002 (login cliente) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-05                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Recuperación de contraseña

### Narrativa
**Como** usuario (empleado, supervisor o cliente)  
**Quiero** recuperar mi contraseña si la olvidé  
**Para** poder acceder nuevamente al sistema.

### Contexto/Objetivo
El usuario puede solicitar un enlace de recuperación desde la página de login ingresando su código de usuario o email. Si el usuario existe y tiene email configurado, recibe un correo con un enlace que permite establecer una nueva contraseña. El enlace tiene tiempo de expiración y es de un solo uso. Cumplida la recuperación, el usuario puede iniciar sesión con la nueva contraseña.

### Suposiciones explícitas
- La tabla `USERS` existe y contiene `code`, `email`, `password` (hash).
- Los clientes se identifican en `USERS` (o tabla equivalente) con código/email.
- El sistema puede enviar correo (SMTP o driver configurado en Laravel).
- El frontend de login ya existe (HU-001/HU-002); se añade el enlace "¿Olvidaste tu contraseña?".
- Si el usuario no tiene email configurado, no se envía correo y se devuelve un mensaje genérico (sin revelar si el usuario existe).

### In Scope
- Enlace "¿Olvidaste tu contraseña?" en la página de login.
- Pantalla o modal para ingresar código de usuario o email.
- Endpoint backend para solicitar recuperación (valida usuario, genera token, envía email si aplica).
- Tabla o mecanismo para tokens de recuperación (token, expiración, uso único).
- Página para establecer nueva contraseña (accesible mediante enlace del email con token).
- Endpoint backend para restablecer contraseña (valida token, actualiza `password_hash` en USERS).
- Validación de nueva contraseña (longitud mínima, confirmación).
- Usuario puede iniciar sesión con la nueva contraseña tras el flujo.

### Out of Scope
- Cambio de contraseña estando autenticado (otra HU).
- Recuperación por SMS o otros canales.
- 2FA o MFA.
- Rate limiting específico más allá del estándar Laravel.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: En la página de login existe un enlace visible "¿Olvidaste tu contraseña?" (o equivalente).
- **AC-02**: Al hacer clic, el usuario accede a una pantalla donde puede ingresar su código de usuario o su email.
- **AC-03**: El sistema valida que se haya ingresado código o email (no vacío).
- **AC-04**: El sistema busca el usuario en USERS por código o por email; si no existe, responde con mensaje genérico (sin revelar si el usuario existe).
- **AC-05**: Si el usuario existe y tiene email configurado, el sistema genera un token de recuperación con expiración (ej. 1 hora), lo persiste y envía un correo con el enlace de restablecimiento.
- **AC-06**: Si el usuario existe pero no tiene email, el sistema responde con mensaje genérico indicando que no es posible enviar el correo (sin revelar datos del usuario).
- **AC-07**: El enlace del correo lleva a una página donde el usuario puede ingresar la nueva contraseña y su confirmación.
- **AC-08**: La nueva contraseña se valida (longitud mínima según reglas del proyecto, ej. 8 caracteres; coincidencia con confirmación).
- **AC-09**: El token es válido solo una vez; tras restablecer la contraseña, el token se invalida.
- **AC-10**: El sistema actualiza el `password_hash` del usuario en USERS y confirma éxito.
- **AC-11**: Tras restablecer la contraseña, el usuario puede iniciar sesión con la nueva contraseña (flujo HU-001/HU-002).
- **AC-12**: Si el token ha expirado o ya fue usado, se muestra mensaje claro y se ofrece volver a solicitar recuperación.

### Escenarios Gherkin

```gherkin
Feature: Recuperación de contraseña

  Scenario: Usuario solicita recuperación con código existente y con email
    Given existe un usuario con código "JPEREZ" y email "juan@ejemplo.com"
    When el usuario accede a "¿Olvidaste tu contraseña?"
    And ingresa "JPEREZ"
    And envía el formulario
    Then el sistema busca el usuario por código
    And genera un token de recuperación con expiración
    And envía un correo a "juan@ejemplo.com" con enlace de restablecimiento
    And se muestra un mensaje genérico de éxito (sin revelar el email)

  Scenario: Usuario sin email configurado
    Given existe un usuario con código "EMP001" y email null
    When el usuario ingresa "EMP001" en recuperación de contraseña
    And envía el formulario
    Then el sistema no envía correo
    And muestra un mensaje genérico (sin revelar si el usuario existe)

  Scenario: Usuario restablece contraseña con token válido
    Given el usuario recibió un correo con enlace de recuperación
    And el token no ha expirado y no ha sido usado
    When accede al enlace
    And ingresa nueva contraseña y confirmación válidas
    And envía el formulario
    Then el sistema actualiza el password_hash en USERS
    And invalida el token
    And el usuario puede iniciar sesión con la nueva contraseña

  Scenario: Token expirado o ya usado
    Given el usuario accede al enlace de recuperación
    And el token ha expirado o ya fue utilizado
    When intenta restablecer la contraseña
    Then se muestra mensaje de token inválido o expirado
    And se ofrece opción de solicitar nuevamente la recuperación
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo usuarios existentes en USERS pueden solicitar recuperación; la respuesta ante código/email inexistente debe ser genérica (seguridad).
2. **RN-02**: El token de recuperación tiene tiempo de expiración (ej. 1 hora); pasado ese tiempo no es válido.
3. **RN-03**: Cada token es de un solo uso; tras restablecer la contraseña correctamente, el token se invalida.
4. **RN-04**: La nueva contraseña debe cumplir la política del sistema (longitud mínima; complejidad si está definida en el proyecto).
5. **RN-05**: Solo si el usuario tiene email configurado se envía el correo; en caso contrario se responde con mensaje genérico.
6. **RN-06**: Aplica a empleados, supervisores y clientes (todos los roles que usan USERS para login).

### Permisos por Rol
- **Empleado / Supervisor / Cliente:** Pueden solicitar recuperación de su propia contraseña (no autenticados). Pueden restablecer contraseña solo mediante token válido recibido por email.

---

## 4) Impacto en Datos

### Tablas Afectadas
- **USERS:** Se actualiza `password` (hash) al restablecer contraseña. No se añaden columnas nuevas para esta HU si se usa tabla separada de tokens.
- **Nueva tabla (recomendado):** `password_reset_tokens` (o nombre según convención Laravel) para almacenar token, email (o user_id), fecha de expiración y uso. Alternativa: usar el sistema de tokens de Laravel (Password::createToken, etc.) si ya existe configuración.

### Estructura sugerida (password_reset_tokens)
- `email` (string): email del usuario al que se envía el enlace.
- `token` (string): token único (hash).
- `created_at` (timestamp): para calcular expiración (ej. 1 hora).

### Migración + Rollback
- Migración: crear tabla `password_reset_tokens` si no existe (Laravel puede proveerla por defecto en versiones recientes).
- Rollback: eliminar tabla en down.

### Seed Mínimo para Tests
- Usuario con email para tests de envío de correo (mockeado en tests).
- Usuario sin email para tests de mensaje genérico.

---

## 5) Contratos de API

### Endpoint 1: Solicitar recuperación – POST `/api/v1/auth/forgot-password`

**Descripción:** El usuario envía su código o email; si existe y tiene email, se genera token y se envía correo.

**Autenticación:** No requerida.

**Request (JSON):**
```json
{
  "code_or_email": "JPEREZ"
}
```
o `"code_or_email": "juan@ejemplo.com"`

**Response 200 OK (siempre mensaje genérico por seguridad):**
```json
{
  "error": 0,
  "respuesta": "Si el usuario existe y tiene email configurado, recibirá un enlace para restablecer la contraseña.",
  "resultado": {}
}
```

**Response 422:** Validación (campo vacío, formato inválido).
**Response 500:** Error interno (no revelar detalles).

---

### Endpoint 2: Restablecer contraseña – POST `/api/v1/auth/reset-password`

**Descripción:** El usuario envía el token recibido por email y la nueva contraseña con confirmación.

**Autenticación:** No requerida (el token actúa como autorización).

**Request (JSON):**
```json
{
  "token": "abc123...",
  "password": "nuevaContraseña123",
  "password_confirmation": "nuevaContraseña123"
}
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Contraseña restablecida correctamente.",
  "resultado": {}
}
```

**Response 422:** Token inválido o expirado; contraseña no cumple reglas; contraseñas no coinciden.
**Response 500:** Error interno.

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **Login (existente):** Añadir enlace "¿Olvidaste tu contraseña?" con `data-testid="auth.forgotPasswordLink"`.
- **Nueva pantalla/componente:** "Solicitar recuperación" – formulario con campo código o email, botón Enviar. Ruta sugerida: `/forgot-password` o `/recuperar-contrasena`.
- **Nueva pantalla/componente:** "Restablecer contraseña" – formulario con token (en query o hidden), campo contraseña, campo confirmación, botón Guardar. Ruta sugerida: `/reset-password?token=...` o similar.

### Estados UI
- Loading: mientras se envía solicitud o se restablece contraseña.
- Success: mensaje genérico tras solicitud; mensaje de éxito tras restablecer.
- Error: validación, token inválido/expirado, error de red.
- Empty: formularios iniciales.

### Validaciones en UI
- Código o email no vacío en solicitud.
- Contraseña longitud mínima y coincidencia con confirmación en restablecimiento.
- Mostrar mensaje claro si token expirado o inválido.

### Accesibilidad mínima
- `data-testid`: `auth.forgotPasswordLink`, `forgotPassword.codeOrEmail`, `forgotPassword.submit`, `resetPassword.password`, `resetPassword.passwordConfirm`, `resetPassword.submit`.
- Labels y roles ARIA apropiados en formularios.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | DB       | Migración tabla tokens de recuperación | Crear tabla password_reset_tokens (email, token, created_at); rollback definido. | — | S |
| T2 | Backend  | Servicio/regla solicitud recuperación | Buscar usuario por code o email; si existe y tiene email, generar token, guardar, enviar email; respuesta genérica. | T1 | M |
| T3 | Backend  | Endpoint POST /auth/forgot-password | Aceptar code_or_email; validación; llamar servicio; 200/422/500. | T2 | S |
| T4 | Backend  | Servicio restablecer contraseña | Validar token (existencia, no expirado, no usado); validar contraseña; actualizar USERS; invalidar token. | T1 | M |
| T5 | Backend  | Endpoint POST /auth/reset-password | Aceptar token, password, password_confirmation; 200/422/500. | T4 | S |
| T6 | Frontend | Enlace "¿Olvidaste tu contraseña?" en login | Visible en login; navega a pantalla solicitud recuperación. | — | S |
| T7 | Frontend | Pantalla solicitar recuperación | Formulario code/email; llamada API; mensaje genérico éxito/error. | T3 | M |
| T8 | Frontend | Pantalla restablecer contraseña | Ruta con token (query); formulario password + confirmación; llamada API; redirección a login o mensaje éxito. | T5 | M |
| T9 | Tests    | Unit + integration backend | Servicio forgot: usuario existe con email, sin email, no existe. Servicio reset: token válido, expirado, ya usado. Endpoints 200/422. | T3, T5 | M |
| T10| Tests    | E2E Playwright recuperación | Login → "¿Olvidaste tu contraseña?" → ingresar código → mensaje; (opcional con mock email) enlace → nueva contraseña → login. | T6, T7, T8 | M |
| T11| Docs     | Actualizar specs/endpoints | Documentar endpoints. | T5 | S |

**Total:** 11 tareas.

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio forgot: usuario por código, por email; con email → envía; sin email → no envía y respuesta genérica; usuario inexistente → respuesta genérica.
- Servicio reset: token válido → actualiza password e invalida token; token expirado → error; token ya usado → error; contraseña corta → validación.

### Integration Tests (Backend)
- POST /auth/forgot-password: 200 con body válido; 422 sin code_or_email; respuesta genérica en todos los casos.
- POST /auth/reset-password: 200 con token válido y contraseña válida; 422 token inválido/expirado; 422 contraseña no cumple o no coincide.

### E2E (Playwright)
- Flujo: ir a login → clic "¿Olvidaste tu contraseña?" → rellenar código/email → enviar → ver mensaje.
- Flujo con token (mock o test con token pregenerado): abrir URL reset con token → rellenar contraseña y confirmación → enviar → ver éxito → login con nueva contraseña.

---

## 9) Riesgos y Edge Cases

- **Seguridad:** No revelar si el usuario existe en respuestas de forgot-password; mensajes genéricos.
- **Token reutilizable:** Asegurar invalidación tras primer uso.
- **Email no configurado:** Definir mensaje y comportamiento homogéneo (siempre respuesta genérica).
- **Concurrencia:** Múltiples solicitudes para el mismo usuario: invalidar tokens anteriores o permitir solo uno vigente según diseño.
- **Expiración:** Definir ventana (1 h recomendada) y mensaje claro en frontend cuando el token expira.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Migración tabla tokens + rollback (tabla Laravel `password_reset_tokens` ya existía)
- [x] Backend: forgot-password y reset-password listos + respuestas genéricas donde aplique
- [x] Frontend: enlace en login + pantallas solicitud y restablecimiento
- [x] Unit tests backend ok
- [x] Integration tests endpoints ok
- [x] ≥1 E2E Playwright flujo recuperación ok
- [x] Docs/specs actualizados

---

## Archivos creados/modificados

### Backend
- `app/Services/PasswordResetService.php` – requestReset, resetPassword, búsqueda por code/email, token 1h.
- `app/Mail/ResetPasswordMail.php` – Mailable con enlace de restablecimiento.
- `resources/views/emails/reset-password.blade.php` – Vista del correo.
- `app/Http/Requests/Auth/ForgotPasswordRequest.php` – Validación code_or_email.
- `app/Http/Requests/Auth/ResetPasswordRequest.php` – Validación token, password, password_confirmation.
- `app/Http/Controllers/Api/V1/AuthController.php` – forgotPassword, resetPassword; rutas en `routes/api.php`.

### Frontend
- `src/features/auth/components/LoginForm.tsx` – Enlace "¿Olvidaste tu contraseña?" con `data-testid="auth.forgotPasswordLink"`.
- `src/features/auth/components/LoginForm.css` – Estilos `.forgot-password-link`, `.form-group-forgot`.
- `src/features/auth/components/ForgotPasswordPage.tsx` + `ForgotPasswordPage.css` – Pantalla solicitar recuperación.
- `src/features/auth/components/ResetPasswordPage.tsx` + `ResetPasswordPage.css` – Pantalla restablecer (token por query).
- `src/features/auth/services/auth.service.ts` – forgotPassword, resetPassword.
- `src/features/auth/components/index.ts` – Export ForgotPasswordPage, ResetPasswordPage.
- `src/app/App.tsx` – Rutas públicas `/forgot-password`, `/reset-password`.

### Docs
- `specs/endpoints/auth-forgot-password.md` – Contrato POST forgot-password.
- `specs/endpoints/auth-reset-password.md` – Contrato POST reset-password.

### Tests
- Backend: `tests/Feature/Api/V1/Auth/PasswordResetTest.php` (forgot/reset 200/422).
- Backend: `tests/Unit/Services/PasswordResetServiceTest.php` (requestReset, resetPassword, token expirado).
- Frontend: `src/features/auth/services/auth.service.test.ts` (forgotPassword, resetPassword).
- E2E: `frontend/tests/e2e/auth-forgot-password.spec.ts` (enlace, formulario éxito, reset sin token).

## Comandos ejecutados

```bash
# Backend
php artisan test tests/Feature/Api/V1/Auth/PasswordResetTest.php tests/Unit/Services/PasswordResetServiceTest.php

# Frontend unit
cd frontend && npm run test -- --run src/features/auth/services/auth.service.test.ts

# Frontend E2E (opcional)
cd frontend && npm run test:e2e -- tests/e2e/auth-forgot-password.spec.ts
```

## Notas y decisiones

- Se usa la tabla Laravel `password_reset_tokens` (email, token, created_at); expiración 1 h en código.
- URL de restablecimiento: `config('app.frontend_url')` + `/reset-password?token=...`.
- Respuesta de forgot-password siempre 200 con mensaje genérico (incluso si hay excepción en el servicio).

## Pendientes / follow-ups

- Ninguno. TR-004 cerrado.
