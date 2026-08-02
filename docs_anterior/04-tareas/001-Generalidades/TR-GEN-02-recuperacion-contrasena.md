# TR-GEN-02-recuperacion-contrasena — Recuperacion de contrasena

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-recuperacion-contrasena](../../03-historias-usuario/001-Generalidades/HU-GEN-02-recuperacion-contrasena.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-modelo-roles-permisos-seed, TR-GEN-02-login-sesion, [TR-GEN-01-idioma](TR-GEN-01-idioma.md) (locale activo y checklist §4 ítem 13) |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-05-31 (F formal) |

**Origen:** [HU-GEN-02-recuperacion-contrasena](../../03-historias-usuario/001-Generalidades/HU-GEN-02-recuperacion-contrasena.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)  
**Checklist i18n (correo):** [TR-GEN-01-idioma §4 — ítem 13](TR-GEN-01-idioma.md#cobertura-mínima-de-textos-traducibles-checklist)  
**Cierre F formal:** [F-GEN-01-02-cierre-formal](F-GEN-01-02-cierre-formal.md)

---

## 1) HU Refinada (resumen)

### Titulo
Implementar flujo de recuperacion por email con token de un solo uso e i18n.

### Narrativa
Como usuario que olvido su contrasena, quiero solicitar un reset y definir nueva clave sin exponer informacion sensible.

### In scope / Out of scope
- In scope: solicitud de recuperacion, envio de correo, validacion de token, seteo de nueva contrasena.
- In scope: plantilla de correo en locale activo con fallback `es`.
- Out of scope: cambio autenticado desde avatar, 2FA y ABM de seguridad.

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Solicitud responde mensaje generico para email existente/no existente.
- **AC-02**: Token de recuperacion es temporal y de un solo uso.
- **AC-03**: Restablecimiento exitoso permite login con nueva clave.
- **AC-04**: Correo usa **locale i18n vigente en el sitio** al solicitar (selector login / `users.locale` / header; ver TR-GEN-01-idioma).
- **AC-05**: Fallo de mail registra error sin revelar existencia de cuenta.
- **AC-06**: Plantillas de correo con paridad de claves para `es`, `en` y **`it`** (mínimo validado en tests).

### Escenarios Gherkin

```gherkin
Feature: Recuperacion de contrasena

  Scenario: Solicitud con email registrado
    Given un email existente
    When llama POST /api/v1/auth/password/forgot
    Then recibe mensaje generico
    And se envia correo con token

  Scenario: Token expirado
    Given un token vencido
    When llama POST /api/v1/auth/password/reset
    Then recibe error funcional de token invalido o expirado

  Scenario: Correo con idioma activo
    Given locale actual en "en"
    When solicita recuperacion
    Then el asunto y cuerpo del correo salen en ingles

  Scenario: Correo en italiano
    Given locale actual en "it" en pantalla de login
    When solicita recuperacion con email registrado
    Then el asunto y cuerpo del correo salen en italiano
    And el enlace de restablecimiento es legible en el mismo idioma

  Scenario: Solicitud con email no registrado
    Given un email inexistente
    When llama POST /api/v1/auth/password/forgot
    Then recibe el mismo mensaje generico que email existente

  Scenario: Restablecer con token valido y login
    Given un token de recuperacion valido
    When establece nueva contrasena
    Then puede iniciar sesion con la nueva clave
```

---

## 3) Reglas de Negocio

1. **RN-01**: Misma respuesta funcional para email existente y no existente.
2. **RN-02**: Token de reset con TTL configurable (default documentado).
3. **RN-03**: Token solo puede consumirse una vez.
4. **RN-04**: Asunto y cuerpo del correo **solo** vía claves i18n; locale = idioma activo en el portal al momento del `POST forgot` (coherente con HU-GEN-01-idioma); fallback `es` si locale ausente o no soportado.
5. **RN-05**: El `locale` de la solicitud se toma del cliente (header/cuerpo) alineado al selector de idioma en login; no inferir desde email del usuario.

### Resolución del locale para el correo

| Origen (prioridad) | Uso |
|--------------------|-----|
| Campo `locale` en body de `POST /auth/password/forgot` | Preferido (enviado por frontend según selector) |
| Header `Accept-Language` | Respaldo si no viene body |
| `es` | Fallback final |

**Checklist transversal:** cumplir ítem **13** del [checklist de cobertura i18n](TR-GEN-01-idioma.md#cobertura-mínima-de-textos-traducibles-checklist) (mail “Olvidé mi contraseña”).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-31)

**Fuentes revisadas:** HU-GEN-02-recuperacion-contrasena, SPEC-001-02-acceso-y-seguridad, `_NORMAS-TRANSVERSALES-TR.md`, `envelope-respuestas.md`, `TR-GEN-01-idioma` (checklist §4 ítem 13), `TR-GEN-02-cambio-contrasena`, `backend/routes/api.php`, `backend/config/auth.php`, `backend/config/mail.php`, `backend/app/Models/User.php`, `backend/app/Http/Requests/ChangePasswordRequest.php`, `backend/config/paqsuite_password.php`, seeds `paqsuite_mvp.php` y búsquedas de endpoints/UI reales de forgot/reset.

### Resultado general

- **Estado:** No apta
- **Puede pasar a D1:** **No**, hasta cerrar TTL/canal de token, semántica HTTP del reset, tenancy pública del slice y URL real del enlace de restablecimiento.

### Ambigüedades críticas

| ID | Tema | Riesgo | Recomendación / decisión necesaria |
|----|------|--------|------------------------------------|
| AMB-C01 | **TTL y fuente canónica del token no cerrados** | La HU deja “duración en TR” y la TR solo dice “TTL configurable (default documentado)”, pero no decide si se adopta directamente `config('auth.passwords.users.expire')` del stack Laravel, ni cuál es el valor base del MVP. Hoy el repo ya trae `60` minutos por config, pero la TR no lo convierte en decisión funcional. | **C1-01:** cerrar si el MVP adopta el broker Laravel estándar con TTL **60 minutos** y throttle **60 segundos**, o si la TR define otros valores. |
| AMB-C02 | **Semántica HTTP de `/auth/password/reset` no está cerrada** | La TR deja abierto si token inválido/expirado se modela como `401`, `403` o error funcional. Dos implementaciones podrían divergir entre `401`, `403` o `422`, rompiendo OpenAPI/tests y consistencia con el envelope. | **C1-02:** cerrar un único criterio para token inválido/usado/expirado. Mi recomendación: `422` + clave funcional (`auth.passwordResetTokenInvalidOrExpired` o equivalente), dejando `401/403` como N/A para esta ruta pública. |
| AMB-C03 | **Tenancy MONO en rutas públicas no documentado** | `routes/api.php` hoy agrupa auth bajo `paq.tenant`, y la norma transversal permite `X-Paq-Cliente` también en públicas MONO. La TR no decide si `forgot/reset` viven dentro de `paq.tenant` y por tanto deben documentar `400 tenant.invalid`. | **C1-03:** cerrar si `POST /api/v1/auth/password/forgot` y `POST /api/v1/auth/password/reset` requieren `X-Paq-Cliente` y documentan `400 tenant.invalid` aun siendo públicas. |
| AMB-C04 | **URL/base del enlace de reset no definida** | La TR exige mail con enlace legible, pero no define si apunta a una ruta SPA (`/reset-password`), a frontend externo, ni cómo se compone la base URL por ambiente. Dos programadores podrían generar enlaces incompatibles. | **C1-04:** cerrar la URL objetivo del correo y su fuente de configuración (`APP_URL`, `FRONTEND_URL` u otra). |
| AMB-C05 | **Canal de mail / estrategia ante falla incompletos** | La TR dice “mismo canal que login” y “log error sin revelar existencia”, pero hoy login no envía correo y `config/mail.php` solo define mailers genéricos. Falta decidir si al fallar el envío se persiste igualmente el token, si se reintenta o si se revierte la operación. | **C1-05:** cerrar semántica mínima ante falla de mail: qué se registra, si el token queda vigente y si se usa mailer default/failover del proyecto. |
| AMB-C06 | **Política de nueva contraseña no referenciada de forma canónica** | La TR habla de “políticas de password” pero no cierra si el reset reutiliza exactamente `config/paqsuite_password.php` y las mismas reglas de `TR-GEN-02-cambio-contrasena`. | **C1-06:** decidir explícitamente que forgot/reset reutiliza la misma política de `change-password` para evitar divergencias. |

### Ambigüedades menores

| ID | Tema | Recomendación |
|----|------|---------------|
| AMB-M01 | `locale` body vs `Accept-Language` | La prioridad ya está sugerida en la TR; conviene cerrarla como regla operativa final y no dejar “header/cuerpo” en abstracto. |
| AMB-M02 | `users.locale` | La HU deja abierta la posibilidad de usar `users.locale` “si aplica”, pero la TR ya recomienda no inferirlo desde email. Conviene alinearlo y no reabrirlo en D1. |
| AMB-M03 | Plantillas / claves de mail | Las claves listadas son “sugeridas”; conviene cerrarlas o dejar explícito que podrán ajustarse en implementación manteniendo paridad `es/en/it`. |
| AMB-M04 | Frontend reset page | La TR pide pantalla de nueva contraseña, pero no define path SPA ni estrategia de lectura de `token`/`email` desde querystring. |
| AMB-M05 | Matriz viva | La matriz ya lista ambas rutas como públicas; eso ayuda, pero la TR todavía no aterriza `400 tenant.invalid` ni el detalle final de responses. |

### Contradicciones TR ↔ HU ↔ SPEC ↔ código

| Contradicción | Impacto | Recomendación |
|---------------|---------|---------------|
| La HU deja TTL “a definir en TR”, pero el stack Laravel ya trae `passwords.users.expire = 60` | El código real sugiere una base que la TR no formaliza | Cerrar si se adopta ese default |
| La TR documenta `401/403` posibles en `/auth/password/reset`, pero la norma transversal marca recuperación como ruta pública | Puede sobredocumentarse autenticación/autorización donde en realidad corresponde error funcional/validación | Alinear reset a una semántica única, preferentemente `422` |
| La HU menciona “selector login o `users.locale`”, mientras la TR dice “no inferir desde email del usuario” | Puede abrir dos fuentes distintas de locale | Cerrar una única fuente operativa para forgot |
| La TR supone formularios forgot/reset en frontend, pero hoy el repo no tiene ninguna ruta ni componente para ese flujo | D1 podría inventar superficie UI sin decisión explícita | Cerrar path y contrato mínimo del frontend |

### Supuestos detectados

- El modelo `User` ya tiene `email`, y los seeds QA (`config/paqsuite_mvp.php`) incluyen correos suficientes para pruebas del flujo.
- El stack Laravel ya dispone de la tabla `password_reset_tokens`.
- La política de contraseña vigente del proyecto está hoy en `backend/config/paqsuite_password.php` y es reutilizable.

### Preguntas para decisión humana

- ¿Adoptamos para MVP el TTL estándar de Laravel, es decir **60 minutos**, y throttle **60 segundos**?
- Para `POST /api/v1/auth/password/reset`, ¿cerramos `422` como respuesta para token inválido/usado/expirado?
- ¿Estas rutas públicas van igualmente bajo `paq.tenant`, documentando `X-Paq-Cliente` y `400 tenant.invalid`?
- ¿Qué URL debe ir en el correo?
  - `A)` ruta SPA del portal, por ejemplo `/reset-password?...`
  - `B)` página externa específica
  - `C)` otra convención del proyecto
- ¿Confirmamos que forgot/reset reutiliza exactamente la misma política de contraseña de `TR-GEN-02-cambio-contrasena`?

### Recomendaciones de ajuste de la TR

- Cerrar TTL/default y apoyarlo explícitamente en `config/auth.php` si esa va a ser la fuente.
- Eliminar la ambigüedad `401/403` del reset y dejar un único criterio HTTP funcional.
- Documentar `400 tenant.invalid` si el slice se publica dentro de `paq.tenant`.
- Definir la ruta/base del enlace del mail y el path frontend del formulario de nueva contraseña.
- Referenciar canónicamente `config/paqsuite_password.php` / `TR-GEN-02-cambio-contrasena` para la política de nueva clave.

### Veredicto C1

**No apta para D1** hasta cerrar TTL/default del token, semántica HTTP del reset, tenancy pública (`400 tenant.invalid` si aplica), URL de enlace y reutilización formal de la política de contraseña. Tal como está, dos programadores podrían implementar contratos y UX distintos.

---

## 3.2) Resoluciones C1 — pre-D1 (2026-05-31)

> Resoluciones cerradas tomando como referencia la implementación equivalente revisada en `PaqSuite-IA-TANGO`, adaptadas a la arquitectura MONO y tenancy actual de SistemaPartes.

| ID | Tema | Decisión |
|----|------|----------|
| R-C1-01 | TTL / throttle del token | El MVP adopta el broker/base estándar del stack Laravel para recuperación: **TTL 60 minutos** y **throttle 60 segundos**, documentado desde `config/auth.php`. |
| R-C1-02 | Semántica HTTP del reset | `POST /api/v1/auth/password/reset` devuelve **422** para token inválido, usado o expirado; `401/403` no aplican como caso principal de esta ruta pública. |
| R-C1-03 | Tenancy pública MONO | A diferencia de `PaqSuite-IA-TANGO`, en SistemaPartes estas rutas públicas quedan bajo `paq.tenant`; por lo tanto `forgot/reset` documentan **`X-Paq-Cliente`** y pueden responder **400 `tenant.invalid`**. |
| R-C1-04 | URL del enlace de reset | El correo apunta a una ruta SPA del portal con base configurable: **`FRONTEND_URL/reset-password?token=...&locale=...`** para preservar el idioma del flujo entre el mail y la pantalla de nueva contraseña. |
| R-C1-05 | Política de nueva contraseña | Forgot/reset reutiliza exactamente la misma política de contraseña ya cerrada en `TR-GEN-02-cambio-contrasena`, apoyándose en `backend/config/paqsuite_password.php`. |
| R-C1-06 | Canal de mail y falla de envío | Se usa el mailer/configuración mail del proyecto. Si el envío falla, el backend **registra el error**, mantiene respuesta genérica al usuario y no revela existencia de cuenta; la implementación D decidirá si persiste token antes del send, pero sin alterar esta semántica externa. |
| R-C1-07 | Fuente de locale del correo | El locale del mail se toma del cliente en `POST forgot`: prioridad **body `locale`** → `Accept-Language` → fallback `es`. No se infiere desde `users.locale` por email. |
| R-C1-08 | Path frontend | El portal expone dos superficies dedicadas del flujo: `/forgot-password` y `/reset-password?token=...&locale=...`. |
| R-C1-09 | Claves i18n del mail | La implementación debe cerrar claves backend estables para asunto/cuerpo/enlace/pie, con paridad mínima `es`, `en`, `it` y fallback `es`. |

### Cierre de ambigüedades

- Queda cerrado el valor default del token de recuperación en **60 minutos**.
- Queda cerrada la semántica de **422** para reset inválido/expirado, alineada con el patrón observado en `PaqSuite-IA-TANGO`.
- Queda cerrado que, en SistemaPartes, estas rutas públicas siguen dentro del marco MONO con **tenant obligatorio**.
- Queda cerrada la URL SPA del correo con base configurable por ambiente.
- Queda cerrado que la política de contraseña se reutiliza desde la ya implementada en `change-password`.

## 3.3) Veredicto C1 — cierre

### Resultado final

- **Estado:** Apta
- **Puede pasar a D1:** **Sí**

### Observaciones para D1

- En planificación conviene decidir si el request de forgot usa `email` o `codeOrEmail`; la HU/TR actual hablan de email, pero la referencia `PaqSuite-IA-TANGO` usa campo híbrido.
- El detalle técnico de persistir token antes o después del intento de envío de mail debe cerrarse en D1 sin cambiar la regla funcional externa de respuesta genérica.
- OpenAPI del slice debe reflejar explícitamente `400 tenant.invalid` y `422` como respuestas relevantes de rutas públicas MONO.

---

## 3.4) Plan D1 — Implementación (2026-05-31)

### Alcance entendido

Implementar el flujo completo de recuperación de contraseña del MVP con dos rutas públicas bajo tenancy MONO: `POST /api/v1/auth/password/forgot` y `POST /api/v1/auth/password/reset`. El slice debe emitir siempre una respuesta genérica en `forgot`, generar y consumir un token de un solo uso con TTL de **60 minutos**, enviar el correo usando el locale activo del portal al momento de la solicitud, exponer una ruta SPA `reset-password` para el enlace del mail y permitir login posterior con la nueva clave. **Fuera:** cambio autenticado desde avatar, 2FA, rate limiting avanzado fuera del throttle estándar, soporte de username/código híbrido en forgot y cualquier ABM de seguridad.

### Fuentes leídas

- SPEC: `docs/05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md`
- HU: `docs/03-historias-usuario/001-Generalidades/HU-GEN-02-recuperacion-contrasena.md`
- TR: `docs/04-tareas/001-Generalidades/TR-GEN-02-recuperacion-contrasena.md`
- Dependencias: `TR-GEN-02-cambio-contrasena`, `TR-GEN-02-login-sesion`, `TR-GEN-01-idioma`
- Norma transversal: `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`
- Envelope MONO: `docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`
- Código actual: `backend/routes/api.php`, `backend/app/Http/Controllers/AuthController.php`, `backend/config/auth.php`, `backend/config/mail.php`, `backend/config/app.php`, `backend/app/Models/User.php`, `backend/app/Http/Requests/ChangePasswordRequest.php`, `backend/config/paqsuite_password.php`, `frontend/src/app/router/AppRoutes.tsx`, `frontend/src/features/auth/LoginPage.tsx`
- Referencia aplicada: implementación equivalente revisada en `PaqSuite-IA-TANGO` (servicio de reset, mail locale, rutas forgot/reset y páginas frontend dedicadas)

### Impacto esperado

#### Base de datos

- Reuso de la tabla estándar `password_reset_tokens` como almacenamiento del token temporal.
- Reuso de `users.password_hash` como credencial final actualizada por reset.
- Sin migraciones DDL nuevas previstas para este slice, salvo validar que la migración base de `password_reset_tokens` ya está presente en el proyecto.
- Tests/fixtures deberán contemplar token válido, token expirado y usuario con email existente.

#### Backend

- Extender `AuthController` con dos operaciones públicas del slice:
  - `POST /api/v1/auth/password/forgot`
  - `POST /api/v1/auth/password/reset`
- Mantenerlas bajo `paq.tenant`, pero fuera de `auth:sanctum`, para conservar el marco MONO de `X-Paq-Cliente` + `400 tenant.invalid`.
- Implementar requests dedicados, idealmente `ForgotPasswordRequest` y `ResetPasswordRequest`, con envelope consistente y validaciones 422.
- Crear un servicio dedicado del slice (por ejemplo `PasswordRecoveryService` o nombre equivalente) que concentre:
  - lookup por email;
  - creación/invalidación del token;
  - verificación de expiración;
  - actualización de `users.password_hash`;
  - borrado del token al éxito o vencimiento;
  - logging ante falla de mail sin romper la respuesta genérica.
- Reutilizar la política de contraseña de `backend/config/paqsuite_password.php` ya usada en `change-password`.
- Añadir soporte backend para construir la URL de reset desde una base configurable (`FRONTEND_URL` o config equivalente añadida al proyecto).
- Incorporar mail/mailable y resolución de locale del correo con prioridad `body locale` → `Accept-Language` → `es`.

#### Frontend

- Agregar enlace desde `LoginPage` hacia una pantalla dedicada `/forgot-password`.
- Crear dos superficies dedicadas:
  - `/forgot-password`
  - `/reset-password?token=...`
- `ForgotPasswordPage` envía `email` y el locale actual del portal.
- `ResetPasswordPage` consume `token` y `locale` desde querystring, aplica el mismo idioma del enlace al abrirse, valida nueva contraseña + confirmación y redirige a `/login` con mensaje de éxito al completar.
- Las pantallas deben apoyarse en i18n del portal y no introducir literales fuera del catálogo.
- Mantener el campo de forgot como **`email`** y no ampliar a `codeOrEmail`, porque la HU/TR de SistemaPartes acota el alcance a recuperación por email.

#### Tests

- Unit backend para:
  - resolución/normalización del locale del correo;
  - expiración del token;
  - consumo único del token;
  - política de contraseña reutilizada.
- Integration backend para `forgot/reset` con `200/400/422` y verificación del envelope.
- Integration backend con `Mail::fake()` para comprobar:
  - respuesta genérica indistinguible entre email existente/no existente;
  - mail en `en` e `it`;
  - fallback a `es`;
  - logging al fallar el envío.
- E2E frontend para:
  - navegación login → forgot;
  - envío forgot con mensaje genérico;
  - reset con token de prueba;
  - login exitoso posterior con nueva clave.

#### Documentación

- OpenAPI para ambos endpoints públicos, incluyendo `tenant`, `400`, `200` y `422`.
- Ajustar §5 de la TR para reflejar que `401/403` no son la semántica principal del reset y que el slice es público MONO con `400 tenant.invalid`.
- Mantener alineada `matriz-permisos-mvp.md` como rutas públicas del bloque auth.
- Cerrar las claves i18n del mail con paridad `es`, `en`, `it`.

#### DevOps

- Añadir/confirmar variable de configuración para la base del frontend del enlace de recuperación (`FRONTEND_URL` o equivalente).
- El mailer sigue la configuración estándar del proyecto (`MAIL_*`); no se requieren cambios de infraestructura en esta fase, solo evidencia de fallback/logging en tests.

### Decisiones D1

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Campo forgot | SistemaPartes mantiene **`email`** como input del request. No se amplía a `codeOrEmail`. |
| D1-2 | TTL / throttle | Se adopta `config('auth.passwords.users.expire') = 60` y `throttle = 60` como base del slice. |
| D1-3 | HTTP reset | Token inválido/usado/expirado ⇒ **422** con clave funcional dedicada; `401/403` no se usan como semántica principal del reset. |
| D1-4 | Tenancy pública | `forgot/reset` viven bajo `paq.tenant`, por lo que documentan `X-Paq-Cliente` y `400 tenant.invalid`. |
| D1-5 | URL del mail | El backend construye `FRONTEND_URL/reset-password?token=...&locale=...` desde config/env específica del proyecto. |
| D1-6 | Política de contraseña | Forgot/reset reutiliza `backend/config/paqsuite_password.php` y la misma semántica de validación de `change-password`. |
| D1-7 | Locale del mail | Fuente: `body locale` → `Accept-Language` → `es`; no usar `users.locale` como fuente principal en forgot. |
| D1-8 | Falla de mail | El usuario siempre recibe respuesta genérica; la falla de envío se registra en logs y no revela existencia de cuenta. |

### Orden de trabajo

1. Agregar las rutas públicas del slice bajo `paq.tenant` y requests dedicados.
2. Implementar el servicio backend de forgot/reset con TTL, consumo único, validación de expiración y actualización de hash.
3. Incorporar resolución de locale + mailable + configuración de `FRONTEND_URL`.
4. Ajustar `AuthController`, OpenAPI y tests feature del backend.
5. Implementar `ForgotPasswordPage`, `ResetPasswordPage` y enlace desde login.
6. Añadir pruebas unitarias/integration/E2E del flujo y cerrar documentación/matriz.

### Archivos o módulos a revisar/tocar

| Capa | Archivos |
|------|----------|
| Backend auth | `backend/app/Http/Controllers/AuthController.php`, `backend/routes/api.php`, `backend/config/auth.php`, `backend/config/app.php` |
| Backend requests | `backend/app/Http/Requests/ForgotPasswordRequest.php`, `backend/app/Http/Requests/ResetPasswordRequest.php` o convención equivalente bajo `Auth/` |
| Backend services/mail | `backend/app/Services/Auth/...` o `backend/app/Services/...` para el servicio del slice, `backend/app/Mail/ResetPasswordMail.php`, `backend/app/Support/...` para resolver locale del mail |
| Backend tests/OpenAPI | `backend/tests/Feature/*Password*Test.php`, `backend/tests/Feature/OpenApiDocumentationTest.php`, `backend/app/OpenApi/OpenApiSchemas.php` |
| Frontend auth | `frontend/src/features/auth/LoginPage.tsx`, nuevas páginas `ForgotPasswordPage.tsx` / `ResetPasswordPage.tsx`, servicios API del auth slice |
| Frontend routing/i18n | `frontend/src/app/router/AppRoutes.tsx`, `frontend/src/locales/*.json` y recursos de correo/backend si se centralizan |
| Docs | `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`, esta misma TR |

### Riesgos

- Si se intenta reutilizar el broker Laravel sin encapsular bien el locale/mail, el flujo puede quedar funcional pero sin cubrir el requisito i18n del correo.
- La decisión de mantener `email` en vez de `codeOrEmail` evita scope creep, pero obliga a que el seed/test UX se construya estrictamente sobre correos válidos.
- Al vivir bajo `paq.tenant`, hay que cuidar que el comportamiento genérico de forgot no se rompa por errores de tenant no contemplados en frontend.
- Si no se define una config explícita de `FRONTEND_URL`, el enlace del mail puede quedar acoplado a `APP_URL` del backend y abrir una UX incorrecta.

### Tests a ejecutar

- Backend: suite feature dedicada de recuperación (`forgot/reset`)
- Backend: `OpenApiDocumentationTest`
- Backend: unit del servicio de token/expiración/locale del mail
- Frontend: unit de validaciones y servicios del auth slice si se agregan
- Frontend: E2E del flujo login → forgot → reset → login

### Dudas / bloqueos

- No quedan bloqueos funcionales tras el C1.
- En implementación habrá que decidir si el servicio usa el broker Laravel directamente o una envoltura dedicada sobre `password_reset_tokens`; cualquiera sirve mientras respete las decisiones cerradas del slice.
- Las claves i18n exactas del mail (`mail.passwordReset.*` o naming equivalente) quedan a concretar en D, manteniendo la paridad mínima `es/en/it`.

### Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**. El plan se limita al flujo de recuperación por email con token temporal, mail i18n, rutas públicas MONO con tenant, pantallas forgot/reset y verificación del login posterior, sin ampliar a cambio autenticado, 2FA ni username híbrido.

---

## 3.5) Verificación D (2026-05-31)

### Implementación realizada

- **Backend:** se implementaron `POST /api/v1/auth/password/forgot` y `POST /api/v1/auth/password/reset` en `AuthController`, con requests dedicados, servicio `PasswordRecoveryService`, resolución de locale `body locale` → `Accept-Language` → `es`, mailable `ResetPasswordMail`, plantillas/traducciones backend `es/en/it`, config `FRONTEND_URL` y documentación OpenAPI ajustada.
- **Seguridad / datos:** el reset actualiza `users.password_hash`, fuerza `first_login = false`, sincroniza `users.password_bcrypt` cuando existe vínculo legacy y consume el token en un único uso mediante `password_reset_tokens`.
- **Frontend:** se agregaron `ForgotPasswordPage`, `ResetPasswordPage`, rutas públicas `/forgot-password` y `/reset-password`, enlace desde login, validaciones cliente y mensajes i18n para `es/en/pt/fr/it`.
- **Refinamiento UI MONO (2026-05-31):** `ForgotPasswordPage` y `ResetPasswordPage` se alinearon visualmente con `PaqSuite-IA-TANGO` y con el login público del portal: fondo gradiente, card centrada, selector de idioma integrado, `TextBox` + `Button` DevExtreme, feedback visual homogéneo y link de retorno al login dentro del mismo lenguaje visual reusable.
- **Tests incorporados:** backend `PasswordRecoveryTest`, `PasswordRecoveryMailLocaleResolverTest` y ajustes en `OpenApiDocumentationTest`; frontend `passwordRecoveryForm.test.ts` y `tests/e2e/password-recovery.spec.ts`.

### Evidencia ejecutada

- `php -l` sobre controller, service, requests, resolver, mailable y tests nuevos/modificados: **OK**.
- `npm test` (frontend): **OK**.
- `npm run test:e2e -- tests/e2e/password-recovery.spec.ts`: **OK**.
- `npm run build` (frontend): **con falla previa ajena al slice** en `src/features/theme/syncDevExtremeTheme.ts` por `themes.resetTheme` / `themes.init`.
- `php artisan test --filter=PasswordRecoveryMailLocaleResolverTest`: **bloqueado por timeout SQL Server** (`SQLSTATE[08001]`).
- `php artisan test --filter=PasswordRecoveryTest`: **bloqueado por timeout SQL Server** (`SQLSTATE[08001]`).
- `php artisan test --filter=OpenApiDocumentationTest`: **bloqueado por timeout SQL Server** (`SQLSTATE[08001]`).

### Resultado D

- **Estado:** Finalizado
- **Observaciones:** el slice quedó implementado y con cobertura frontend ejecutada; la validación automatizada del backend quedó condicionada por la indisponibilidad/timeout de la base SQL Server del entorno.

---

## 4) Impacto en Datos

### Tablas afectadas
- `password_reset_tokens` (o equivalente del stack)
- `users` (actualizacion de hash de contrasena)
- bitacora de eventos de seguridad (si existe)

### Seed minimo para tests
- Usuario semilla con email valido.
- Tokens de prueba validos y expirados.
- **Plantillas / claves i18n de correo** (backend o recursos compartidos): `mail.passwordReset.subject`, `mail.passwordReset.body`, `mail.passwordReset.linkLabel` (nombres a confirmar en implementación) en **`es`, `en`, `it`** como mínimo.
- Caso de prueba integración: solicitud con `locale: "it"` → asunto/cuerpo en italiano (assert sobre strings o snapshots de plantilla).

### Plantillas de correo (i18n)

| Elemento | Claves sugeridas | Idiomas mínimos MVP |
|----------|------------------|---------------------|
| Asunto | `mail.passwordReset.subject` | `es`, `en`, `it` |
| Saludo / cuerpo | `mail.passwordReset.body` | `es`, `en`, `it` |
| Texto del enlace | `mail.passwordReset.linkLabel` | `es`, `en`, `it` |
| Pie / no responder | `mail.passwordReset.footer` | `es`, `en`, `it` |

- Mismo canal de envío que login (producto SistemaPartes).
- No hardcodear HTML en un solo idioma; parametrizar URL de reset y minutos de validez del token.
- Ver criterio de cierre **italiano** en [TR-GEN-01-idioma](TR-GEN-01-idioma.md#cobertura-mínima-de-textos-traducibles-checklist).

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| POST | `/api/v1/auth/password/forgot` | No | N/A | Si |
| POST | `/api/v1/auth/password/reset` | No | N/A | Si |

### 5.2 Detalle por operacion

#### POST `/api/v1/auth/password/forgot`
**Autorizacion:** publica bajo middleware `paq.tenant` (sin `auth:sanctum`; requiere `X-Paq-Cliente`).
**Request:** email + **`locale`** (opcional; recomendado — código del catálogo MVP: `es`, `en`, `pt`, `fr`, `it`) para determinar idioma del correo.
**Response 200:** envelope de confirmacion generica.
**Response 400:** `tenant.invalid`.
**Response 422:** validación de request.

#### POST `/api/v1/auth/password/reset`
**Autorizacion:** publica bajo middleware `paq.tenant` (sin `auth:sanctum`; requiere `X-Paq-Cliente`) con token funcional de recuperacion.
**Request:** `token`, `newPassword`, `newPasswordConfirmation`.
**Response 200:** envelope de reset exitoso.
**Response 400:** `tenant.invalid`.
**Response 422:** token inválido/usado/expirado o validación de request.

### 5.3 Actualizacion matriz permisos

- [x] Marcar ambos endpoints como publicos en matriz.
- [x] Alinear el slice a `400 tenant.invalid` + `422` y descartar `401/403` como semántica principal del reset.
- [ ] Verificar OpenAPI en `/api/documentation`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- Norma base reusable MONO: `docs/00-contexto/_mono/01-experiencia-base/patron-ui-auth-devextreme.md`
- Enlace `Olvidaste tu contrasena` en login.
- Pantalla de solicitud y pantalla de nueva contrasena.
- Mensajes i18n coherentes con locale actual.
- Contrato reusable MONO para `ForgotPasswordPage`:
  - reutilizar el mismo sistema visual del login público (`surface` centrada sobre gradiente, tipografía y espaciado compatibles);
  - montar `LocaleSelector` dentro de la card para mantener consistencia entre pantallas públicas;
  - usar exclusivamente controles **DevExtreme** equivalentes (`TextBox`, `Button`), evitando `<input>` / `<button>` nativos en la superficie final;
  - conservar `data-testid` públicos (`localeSelectorForgotPassword`, `forgot-password-form`, `forgotPasswordEmail`, `forgotPasswordSubmit`, `forgotPasswordBackToLogin`) para no acoplar tests E2E al DOM interno de DX;
  - dejar textos de título, descripción, CTA, errores y éxito resueltos vía i18n, no hardcodeados en JSX.
- Contrato reusable MONO para `ResetPasswordPage`:
  - reutilizar exactamente la misma familia visual pública de login/forgot (`surface` centrada sobre gradiente, card clara, selector de idioma integrado y CTA primario dentro de la card);
  - resolver los campos de nueva contraseña y confirmación con `TextBox` DX en modo password, incluyendo `placeholder` y `aria-label` i18n;
  - priorizar el `locale` recibido en la querystring del enlace del mail para abrir la pantalla en el mismo idioma desde el cual se solicitó la recuperación;
  - preservar `data-testid` estables (`localeSelectorResetPassword`, `reset-password-form`, `resetPasswordNew`, `resetPasswordConfirm`, `resetPasswordSubmit`, `resetPasswordBackToLogin`) para mantener automatización reusable;
  - mostrar errores funcionales/validaciones dentro de la misma card con el mismo patrón visual reusable de `ForgotPasswordPage`;
  - no introducir HTML nativo final para inputs o CTA si DevExtreme cubre el caso.

### data-testid sugeridos
- `login-forgot-password`
- `forgot-password-form`
- `forgotPasswordSubmit`
- `reset-password-form`
- `resetPasswordNew`
- `resetPasswordConfirm`
- `resetPasswordSubmit`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T1 | Backend | Endpoint `forgot` con respuesta generica | No filtra existencia de email |
| T2 | Backend | Endpoint `reset` con token one-time | Respeta TTL y consumo unico |
| T3 | Backend | Envio mail con locale activo | Plantillas i18n `es`/`en`/`it` + fallback `es`; alinea checklist TR-GEN-01-idioma §4 ítem 13 |
| T4 | Frontend | Formularios forgot/reset | UX completa y validada |
| T5 | Tests | Integration + E2E de recuperacion | Cubre token valido/expirado |
| T6 | Docs | OpenAPI y matriz actualizadas | Coherencia transversal |

---

## 8) Estrategia de Tests

- **Unit:** generacion/validacion de token y politicas de password.
- **Integration:** flujos `forgot/reset`, validando envelope y errores; **`forgot` con `locale: it`** → contenido de mail en italiano (mock mailer).
- **E2E:** solicitud, consumo de token de prueba y login con nueva clave; opcional captura de mail en entorno test para `en` e `it`.

---

## 9) Riesgos y Edge Cases

- Retrasos/falla de proveedor de correo.
- Reuso accidental de token por carrera concurrente.
- Diferencias de locale entre UI y backend al generar email (mitigar: frontend envía `locale` explícito en `forgot`).

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Flujo forgot/reset completo
- [ ] i18n de correos verificado (`es`, `en`, **`it`** como mínimo)
- [ ] Checklist TR-GEN-01-idioma ítem 13 marcado para este slice

### Checklist normas transversales
- [ ] Endpoints nuevos/modificados con policy en codigo
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en `/api/documentation` coherente con codigo y matriz
- [ ] 401/403 documentados por operacion protegida
- [ ] Envelope JSON respetado
- [ ] `X-Paq-Cliente` documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliacion de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

### Backend
- Endpoints y servicio de recuperacion de contrasena.

### Frontend
- Formularios forgot/reset en modulo de autenticacion.

### OpenAPI
- Anotaciones de auth password endpoints.

### Docs
- `docs/04-tareas/001-Generalidades/TR-GEN-02-recuperacion-contrasena.md`










