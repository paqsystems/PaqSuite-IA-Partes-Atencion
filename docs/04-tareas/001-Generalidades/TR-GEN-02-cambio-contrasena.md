# TR-GEN-02-cambio-contrasena — Cambio de contrasena y primer ingreso

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-cambio-contrasena](../../03-historias-usuario/001-Generalidades/HU-GEN-02-cambio-contrasena.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-modelo-roles-permisos-seed, TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-05-31 (F formal) |

**Origen:** [HU-GEN-02-cambio-contrasena](../../03-historias-usuario/001-Generalidades/HU-GEN-02-cambio-contrasena.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)  
**Cierre F formal:** [F-GEN-01-02-cierre-formal](F-GEN-01-02-cierre-formal.md)

> **Coordinacion login (D1-5):** [TR-GEN-02-login-sesion](TR-GEN-02-login-sesion.md) expone `firstLogin` en `sessionContext` pero **no** implementa gate ni redirect; esta TR es la unica responsable del flujo bloqueante y desbloqueo del shell.

---

## 1) HU Refinada (resumen)

### Titulo
Permitir cambio de contrasena autenticado y forzar primer ingreso cuando corresponda.

### Narrativa
Como usuario autenticado, necesito cambiar mi contrasena desde el avatar y cumplir cambio obligatorio en primer acceso.

### In scope / Out of scope
- In scope: endpoint de cambio autenticado, validacion de contrasena actual y nueva, flujo `firstLogin`.
- In scope: bloqueo de navegacion a shell hasta completar cambio obligatorio.
- Out of scope: flujo forgot/reset y ABM de seguridad.

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Cambio exitoso requiere contrasena actual valida.
- **AC-02**: Contrasena actual incorrecta devuelve error funcional sin modificar hash.
- **AC-03**: Usuario `firstLogin=true` no opera hasta cambiar clave.
- **AC-04**: Sesion expirada durante flujo deriva a login (401).
- **AC-05**: Endpoint documentado en OpenAPI con 401/403.
- **AC-06**: Formulario rechaza envío con campos obligatorios vacíos (validación cliente y/o API).
- **AC-07**: Nueva contraseña y confirmación distintas: rechazo sin modificar hash.
- **AC-08**: Tras cambio exitoso, login con la **nueva** contraseña funciona; con la **anterior** falla.

### Escenarios Gherkin

```gherkin
Feature: Cambio de contrasena autenticado

  Scenario: Cambio exitoso
    Given un usuario autenticado
    When envia password actual correcta y nueva valida
    Then se actualiza la contrasena

  Scenario: Primer ingreso obligatorio
    Given un usuario con firstLogin activo
    When intenta abrir el shell
    Then es redirigido al flujo de cambio de contrasena

  Scenario: Campos obligatorios vacios
    Given un usuario autenticado en el formulario de cambio
    When intenta enviar con uno o mas campos vacios
    Then el envio es bloqueado o rechazado
    And la contrasena no cambia

  Scenario: Confirmacion distinta a la nueva
    Given un usuario autenticado
    When ingresa contrasena actual correcta y nueva distinta de confirmacion
    Then el cambio es rechazado
    And la contrasena no cambia

  Scenario: Contrasena actual incorrecta
    Given un usuario autenticado
    When ingresa contrasena actual incorrecta
    Then el cambio es rechazado con mensaje funcional
    And la contrasena no cambia

  Scenario: Login con nueva contrasena tras cambio exitoso
    Given un usuario que cambio su contrasena exitosamente
    When cierra sesion o la sesion expira segun politica TR
    And inicia sesion con la nueva contrasena
    Then accede al shell
    And el login con la contrasena anterior falla

  Scenario: Sesion expirada durante cambio
    Given un usuario en flujo de cambio de contrasena
    When la sesion expira
    Then es redirigido al login
```

---

## 3) Reglas de Negocio

1. **RN-01**: Cambio requiere autenticacion Bearer Sanctum y `X-Paq-Cliente`.
2. **RN-02**: Nueva contrasena debe cumplir politica minima definida por producto/TR.
3. **RN-03**: No permitir nueva contrasena igual a la actual (recomendado Must para MVP).
4. **RN-04**: `firstLogin` se desactiva solo tras cambio exitoso.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-28)

**Fuentes revisadas:** HU-GEN-02-cambio-contrasena, SPEC-001-02, `login-y-sesion.md` (MONO), TR-GEN-02-login-sesion (§3.3 C1, D1-5 `firstLogin`), TR-GEN-02-modelo-roles-permisos-seed (§4.6), TR-GEN-01-menu-avatar, `matriz-permisos-mvp.md`, `paqsuite_mvp.php`, `SecurityMvpSeeder`, `LoginService`, `SessionContextBuilder`, `AuthController`, `LoginPage`, `AppRoutes`/`RequireAuth`/`ShellHeader`, regla local tablas SQL compartidas.

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1:** **Sí** (resoluciones §3.2 cerradas; sin replan de alcance)

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|-------------------------------|
| AMB-C01 | **Política de complejidad no definida** | Producto §7.4 y MONO no fijan reglas; TR §8 menciona `Password123!` sin norma formal | **D1-1:** política MVP única en `config/paqsuite_password.php` (mín. 8 caracteres, al menos 1 letra y 1 dígito); misma regla en FormRequest backend y validación cliente. |
| AMB-C02 | **`firstLogin` en login UI** | `LoginPage` persiste sesión pero muestra error y **no** navega; `LoginRoute` redirige autenticados a `/dashboard` ignorando `firstLogin` | **D1-2:** tras login OK con `firstLogin=true` → `/change-password` (replace); quitar mensaje bloqueante en login. |
| AMB-C03 | **Sin gate en rutas protegidas** | `RequireAuth` solo valida token; usuario `firstLogin` podría entrar al shell vía F5 o deep link | **D1-3:** layout `RequirePasswordChange` (o ruta dedicada) que redirige a `/change-password` si `sessionContext.firstLogin`; shell solo tras `firstLogin=false`. |
| AMB-C04 | **Entrada desde avatar** | `ShellHeader` solo tiene botón logout con nombre; TR-GEN-01-menu-avatar pendiente | **D1-4:** enlace/botón mínimo «Cambiar contraseña» en header (`/change-password`) hasta menú desplegable avatar; sin duplicar TR menu-avatar. |
| AMB-C05 | **Sync legacy `users`** | Seed opcional escribe `password_bcrypt`/`primer_login`; login MVP usa solo `users.password_hash` | **D1-5:** cambio actualiza **`users.password_hash`** y **`users.first_login`**; si existe fila `users` por `usuario = users.codigo`, actualizar `password_bcrypt` y `primer_login` (best-effort, sin fallar si no hay fila). |
| AMB-C06 | **Sesión tras cambio exitoso** | HU pregunta abierta re-login vs continuar | **D1-6:** **mantener sesión** Sanctum actual; respuesta 200 incluye `sessionContext` actualizado (`firstLogin: false`); persistir en `localStorage`. Sin logout forzado en MVP. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | Código HTTP contraseña actual incorrecta | **422** + `respuesta`: `auth.invalidCurrentPassword` (no 401: evita confundir con expiración de sesión). |
| AMB-M02 | Nueva = actual (RN-03) | **422** + `auth.newPasswordSameAsCurrent`. |
| AMB-M03 | Confirmación distinta | **422** validación Laravel `confirmed` / regla explícita `newPasswordConfirmation`. |
| AMB-M04 | Campos vacíos | **422** integration; cliente deshabilita submit si falta algún campo (AC-06). |
| AMB-M05 | **403** en cambio de clave | Solo si cuenta **`!activo`** o **`inhabilitado`** (misma semántica que login); usuario autenticado operativo siempre puede cambiar su clave. |
| AMB-M06 | Usuario seed E2E `cambioClave.mvp` (§8) | No existe en `paqsuite_mvp.php` | Añadir **`primerIngreso.mvp`** (`firstLogin=true`) y **`cambioClave.mvp`** (`firstLogin=false`) en seed; contraseña inicial `SEED_MVP_PASSWORD`. |
| AMB-M07 | `data-testid` §6 vs §8 | Unificar en **camelCase** §8 (`changePasswordSubmit`, etc.). |
| AMB-M08 | OpenAPI / matriz | Matriz ya tiene fila `password/change`; OpenAPI en oleada D1 (T5). |
| AMB-M09 | Nombres request API | **camelCase** alineado a login: `currentPassword`, `newPassword`, `newPasswordConfirmation`. |
| AMB-M10 | Bitácora seguridad (§4) | **Fuera MVP** — no hay tabla; solo `users` + sync legacy opcional. |
| AMB-M11 | Revocar otros tokens al cambiar clave | **MVP: no** revocar otros dispositivos (edge case §9); token actual sigue válido. |
| AMB-M12 | Re-seed sobrescribe contraseña | `SecurityMvpSeeder` no incluye `password_hash` en `updateColumns` → OK para tests post-cambio; documentar en README seed. |

### Contradicciones TR ↔ código ↔ HU

| Contradicción | Resolución |
|---------------|------------|
| TR §17 login no implementa gate; `LoginPage` muestra error `firstLogin` | Esta TR asume gate completo (D1-2, D1-3); login solo redirige. |
| HU: entrada desde menú avatar; código sin menú avatar | D1-4: enlace mínimo en header; menú avatar completo queda en TR-GEN-01-menu-avatar. |
| TR §8 `Password123!` vs `SEED_MVP_PASSWORD` | Tests usan **`SEED_MVP_PASSWORD`** (phpunit `TestSeedPassword123`); E2E nueva clave fija acordada en config test (`Password123!` solo como ejemplo de política válida). |
| MONO `name` vs código `name_user` | Sin impacto en este slice; auth ya usa `name_user`. |

### Supuestos detectados

- Autenticación y cambio de clave siguen **`users.password_hash`** (TR-GEN-02-login D1-6).
- Ruta pública de cambio: **`/change-password`** bajo `RequireAuth`, **sin** `ShellLayout`.
- Respuesta 200 envelope: `{ sessionContext }` (sin nuevo token salvo que Sanctum requiera rotación — **no** en MVP).
- Tenant header obligatorio en POST change (middleware `paq.tenant` existente).

### Preguntas para decisión humana

- ~~Sesión tras cambio~~ → **Cerrado (2026-05-28):** mantener sesión (R-C1-06).
- ~~Política MVP de clave~~ → **Cerrado (2026-05-28):** 8+ con letra y dígito (R-C1-01).
- ~~Sync legacy login~~ → **Cerrado (2026-05-28):** best-effort si existe fila (R-C1-05).

### Veredicto C1

**Apto para D1.** Sin replan de alcance; resoluciones §3.2 y decisiones §3.3 listas para implementación.

---

## 3.2) Resoluciones C1 — pre-D1 (2026-05-28)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Política contraseña MVP | Mín. **8** caracteres, **≥1 letra** y **≥1 dígito**; config `backend/config/paqsuite_password.php`. |
| R-C1-02 | Login + `firstLogin` | Redirect **`/change-password`**; no bloquear con error en pantalla login. |
| R-C1-03 | Gate shell | Rutas con `ShellLayout` exigen `firstLogin === false`; componente `first-login-gate` / guard dedicado. |
| R-C1-04 | Entrada voluntaria | Botón/enlace en `ShellHeader` → `/change-password` hasta TR menu-avatar. |
| R-C1-05 | Legacy `users` | Actualizar `password_bcrypt` + `primer_login` si existe `usuario = codigo`; no error si ausente. |
| R-C1-06 | Post-cambio sesión | **Mantener** token; actualizar `sessionContext` local y permitir shell. |
| R-C1-07 | Errores funcionales | `auth.invalidCurrentPassword`, `auth.newPasswordSameAsCurrent`; HTTP **422**. |
| R-C1-08 | Seed tests | `primerIngreso.mvp` (`firstLogin=true`), `cambioClave.mvp` (`firstLogin=false`); perfil Cliente + permiso. |
| R-C1-09 | testids | §8 camelCase canónico; `first-login-gate` en pantalla bloqueante. |
| R-C1-10 | Autorización endpoint | Usuario autenticado + cuenta activa; **sin** permiso `Pq_Rol` adicional. |
| R-C1-11 | OpenAPI | Anotar en `AuthController` + revisar `/api/documentation` en mismo slice. |
| R-C1-12 | Coordinación login TR | Ajustar `LoginPage`/`LoginRoute`/`AppRoutes` en este slice (única TR del gate). |

---

## 3.3) Plan D1 — Implementación (2026-05-28)

### Alcance entendido

Endpoint autenticado de cambio de contraseña, desbloqueo de `first_login`, gate frontend sin bypass al shell, formulario con validaciones cliente/servidor, seeds y tests integration + E2E según §8.

### Fuentes leídas

- C1 §3.1–3.2, HU, SPEC-001-02, `login-y-sesion.md`, TR login/seed, código auth actual.

### Decisiones D1 (cerradas en C1)

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Política clave | Ver R-C1-01 (`paqsuite_password.php`). |
| D1-2 | Post-login `firstLogin` | Redirect `/change-password` (R-C1-02). |
| D1-3 | Gate rutas | `RequirePasswordChange` envuelve shell; ruta cambio fuera del shell (R-C1-03). |
| D1-4 | Entrada avatar | Enlace mínimo header (R-C1-04). |
| D1-5 | Legacy sync | Best-effort `users` (R-C1-05). |
| D1-6 | Sesión post-éxito | Mantener token + `sessionContext` (R-C1-06). |
| D1-7 | Códigos error | R-C1-07 + envelope estándar MONO. |
| D1-8 | Seeds | `primerIngreso.mvp`, `cambioClave.mvp` (R-C1-08). |

### Tareas D1 ↔ plan §7

| Ticket | Entregable código |
|--------|-------------------|
| T1 | `ChangePasswordService`, `AuthController::changePassword`, `POST /api/v1/auth/password/change`, `ChangePasswordRequest` |
| T2 | `first_login = false` en éxito; `sessionContext` en respuesta |
| T3 | `ChangePasswordPage`, guard `firstLogin`, fix login redirect, enlace header |
| T4 | `tests/Feature/ChangePasswordTest.php` + `frontend/tests/e2e/change-password.spec.ts` |
| T5 | OpenAPI + checklist matriz §5.3 |

### Archivos previstos

| Capa | Archivos |
|------|----------|
| Backend | `config/paqsuite_password.php`, `app/Services/Auth/ChangePasswordService.php`, `app/Http/Requests/ChangePasswordRequest.php`, `AuthController`, `routes/api.php`, `Support/AuthErrorCodes.php` |
| Frontend | `features/auth/ChangePasswordPage.tsx`, `features/auth/changePasswordApi.ts`, `app/router/passwordChangeRoutes.tsx`, ajustes `LoginPage`, `AppRoutes`, `ShellHeader` |
| Seed | `config/paqsuite_mvp.php`, `SecurityMvpSeeder` (solo nuevos usuarios) |
| Tests | Feature + E2E §8 |

### Contrato API cerrado (D1)

**POST** `/api/v1/auth/password/change`

```json
{
  "currentPassword": "string",
  "newPassword": "string",
  "newPasswordConfirmation": "string"
}
```

**200:** `{ "error": 0, "respuesta": "auth.passwordChanged", "resultado": { /* sessionContext sin token */ } }`

**401:** sin token / expirado — `auth.unauthenticated`

**403:** cuenta inactiva/inhabilitada — `auth.accountDisabled`

**422:** validación / actual incorrecta / nueva igual a actual — claves §3.2 R-C1-07

---

## 3.4) Verificación D (2026-05-30)

| Verificación | Resultado |
|--------------|-----------|
| `POST /api/v1/auth/password/change` (200/401/403/422) | OK — `ChangePasswordTest` 9 casos |
| Política clave (`paqsuite_password.php`) backend + cliente | OK |
| `first_login=false` + `sessionContext` en respuesta 200 | OK |
| Sync legacy `users` (best-effort) | OK — `ChangePasswordService` |
| Gate `firstLogin` sin bypass al shell | OK — `RequirePasswordChange` + E2E |
| Login redirect `/change-password` | OK — `LoginPage` + `LoginRoute` |
| Entrada voluntaria header → `/change-password` | OK — `ShellHeader` |
| i18n `ChangePasswordPage` con locale activo | OK — claves `changePassword.*` / `auth.*` + verificación E2E dedicada |
| Seeds `primerIngreso.mvp`, `cambioClave.mvp` | OK — `paqsuite_mvp.php` |
| Unit frontend validación formulario | OK — 3 tests |
| E2E cambio contraseña (§8 Must) | OK — 7 casos |
| Backend suite completa | **42 passed** (+9 vs baseline 33) |
| Frontend unit | **16 passed** |

### Trazabilidad AC

| AC | Evidencia |
|----|-----------|
| AC-01 | Integration: cambio exitoso con actual correcta |
| AC-02 | Integration + E2E: `auth.invalidCurrentPassword`, hash intacto |
| AC-03 | E2E gate `first-login-gate` + redirect shell bloqueado |
| AC-04 | Integration 401 sin token; frontend redirect login en 401 |
| AC-05 | OpenAPI `@OA\Post` en `AuthController::changePassword` |
| AC-06 | Integration 422 vacío + E2E formulario vacío |
| AC-07 | Integration + E2E confirmación distinta |
| AC-08 | Integration login post-cambio + E2E flujo completo |

### Ajuste D (Handler validación)

- `ValidationException` API: `auth.passwordConfirmationMismatch` solo si **ambas** claves nuevas vienen informadas; campos vacíos → `validation.failed`.

### Confirmación de alcance

Sin forgot/reset, sin ABM seguridad, sin revocación multi-sesión, sin menú avatar completo (enlace mínimo en header hasta TR-GEN-01-menu-avatar).

---

## 4) Impacto en Datos

### Tablas afectadas
- `users` (hash, marca `firstLogin`, metadatos de actualizacion)
- bitacora/eventos de seguridad (si existe)

### Seed minimo para tests
- `primerIngreso.mvp` — `first_login=true`, contraseña `SEED_MVP_PASSWORD`.
- `cambioClave.mvp` — `first_login=false`, para E2E cambio voluntario y login post-cambio.
- Usuarios existentes (`cliente.mvp`, etc.) siguen con `first_login=false`.

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| POST | `/api/v1/auth/password/change` | Bearer Sanctum + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operacion

#### POST `/api/v1/auth/password/change`
**Autorizacion:** usuario autenticado (y politica de cuenta activa).
**Request:** `{ currentPassword, newPassword, newPasswordConfirmation }`.
**Response 200:** envelope con confirmacion de cambio.
**Response 401:** sin token/token invalido/sesion expirada.
**Response 403:** token valido pero operacion no permitida (estado cuenta o politica).
**Response 4xx:** validaciones de formato/complejidad.

### 5.3 Actualizacion matriz permisos

- [x] Fila `POST /api/v1/auth/password/change` en matriz (TR-GEN-02-cambio-contrasena).
- [x] Permiso: usuario autenticado + cuenta activa (C1 R-C1-10).
- [x] Anotaciones OpenAPI en `AuthController::changePassword` (D1 T5).

---

## 6) Cambios Frontend

- Norma base reusable MONO: `docs/00-contexto/_mono/01-experiencia-base/patron-ui-auth-devextreme.md`
- `ChangePasswordPage` queda alineada visualmente con `PaqSuite-IA-TANGO`: ventana/card centrada con jerarquía visual de modal, pero manteniendo el flujo del slice (`/change-password` y gate `firstLogin`).
- Los campos interactivos del formulario deben usar **DevExtreme** (`TextBox`, `Button` y equivalentes), no `<input>` / `<button>` nativos en la UI final.
- Regla reusable MONO: preservar `data-testid` públicos (`changePasswordCurrent`, `changePasswordNew`, `changePasswordConfirm`, `changePasswordSubmit`, `changePasswordError`) aunque el DOM interno de DevExtreme cambie.
- Regla reusable MONO de i18n: todos los textos visibles de `ChangePasswordPage` deben salir del locale activo (`changePassword.*`, `auth.*`), incluyendo título, labels, placeholders, CTA, gate `firstLogin` y errores; no dejar literales hardcodeados en JSX/CSS.
- Criterio de cierre reusable: incorporar al menos un E2E que valide la pantalla en un locale distinto de `es` para detectar regresiones de i18n del formulario.

### Pantallas / componentes
- Entrada desde menu avatar a pantalla de cambio.
- Pantalla bloqueante para usuarios `firstLogin`.
- Manejo de errores de validacion y expiracion de sesion.

### data-testid (canónicos post-C1)

- `change-password-form`
- `changePasswordCurrent`
- `changePasswordNew`
- `changePasswordConfirm`
- `changePasswordSubmit`
- `changePasswordError`
- `first-login-gate`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T1 | Backend | Implementar endpoint `password/change` | **Cumplido** |
| T2 | Backend | Actualizar estado `firstLogin` | **Cumplido** |
| T3 | Frontend | Flujo avatar + gate de primer ingreso | **Cumplido** |
| T4 | Tests | Integration (422/401/200) + E2E validaciones y login post-cambio | **Cumplido** |
| T5 | Docs | OpenAPI y matriz permisos | **Cumplido** |

---

## 8) Estrategia de Tests

### Reparto recomendado

| Caso | Integration (API) | E2E (UI) | Notas |
|------|-------------------|----------|--------|
| Campos vacíos | **Must** 422 | **Must** — botón deshabilitado o error visible | No debe llegar hash al backend si el cliente valida antes |
| Contraseña actual incorrecta | **Must** 4xx | **Must** — mensaje en formulario | AC-02; crítico de seguridad |
| Nueva ≠ confirmación | **Must** 422 | **Must** — error en confirmación | Validación cliente + servidor |
| Cambio exitoso | **Must** 200 + hash distinto | **Must** — toast/redirect éxito | — |
| Login post-cambio (nueva OK, vieja falla) | **Must** | **Must** — flujo completo | Cierra el circuito; detecta bugs de persistencia |
| `firstLogin` gate | Integration flag | **Must** E2E | Ya en AC-03 |
| Sesión expirada (401) | **Must** | Should E2E | Integration suficiente si E2E es costoso |
| Nueva = actual (RN-03) | **Must** | Should E2E | Integration obligatorio |

### Detalle por capa

- **Unit:** validadores de contraseña, reglas de formulario (required, match), transiciones de `firstLogin`.
- **Integration:** `POST /api/v1/auth/password/change` con 200, 401, 403, 422 (vacío, mismatch, actual incorrecta, política); verificar hash en BD; login posterior con nueva/vieja vía endpoint login.
- **E2E (Must):**
  1. Enviar formulario vacío → no éxito, credencial intacta.
  2. Actual incorrecta → error visible, credencial intacta.
  3. Nueva y confirmación distintas → error visible, credencial intacta.
  4. Cambio exitoso desde avatar → mensaje éxito.
  5. **Logout (o sesión nueva) → login con nueva contraseña OK; login con anterior falla.**
  6. Usuario `firstLogin` → gate → cambio → shell accesible.

**Seed E2E:** `cambioClave.mvp` (cambio voluntario) y `primerIngreso.mvp` (gate); contraseña inicial `SEED_MVP_PASSWORD`; nueva clave de prueba acordada en spec E2E (ej. `Password123!` cumple D1-1). No reutilizar `cliente.mvp` en specs de cambio sin reset.

### data-testid para E2E

- `changePasswordCurrent`
- `changePasswordNew`
- `changePasswordConfirm`
- `changePasswordSubmit`
- `changePasswordError` (mensaje global o por campo)

---

## 9) Riesgos y Edge Cases

- Condicion de carrera al cambiar password en multiples sesiones.
- Diferencias de politica de complejidad entre frontend y backend.
- Reintentos fallidos en sesion proxima a expirar.

---

## 10) Checklist final

### Checklist del slice
- [x] AC cumplidos
- [x] Cambio de contrasena operativo
- [x] Gate de primer ingreso validado

### Checklist normas transversales
- [x] Endpoints nuevos/modificados con policy en codigo
- [x] Matriz endpoint ↔ permiso actualizada
- [x] OpenAPI anotado en controller (L5-Swagger pendiente generación `/api/documentation`)
- [x] 401/403 documentados por operacion protegida
- [x] Envelope JSON respetado
- [x] `X-Paq-Cliente` documentado donde aplique
- [x] Tests API incluyen 401 (y 403 si aplica)
- [x] Sin ampliacion de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

### Backend
- `config/paqsuite_password.php`
- `app/Services/Auth/ChangePasswordService.php`
- `app/Http/Requests/ChangePasswordRequest.php`
- `app/Http/Controllers/AuthController.php` (+ OpenAPI)
- `app/Support/AuthErrorCodes.php`
- `app/Exceptions/Handler.php`
- `routes/api.php`
- `config/paqsuite_mvp.php` (usuarios seed)
- `tests/Feature/ChangePasswordTest.php`

### Frontend
- `src/features/auth/ChangePasswordPage.tsx`
- `src/features/auth/changePasswordApi.ts`
- `src/features/auth/changePasswordForm.ts`
- `src/features/auth/changePasswordForm.test.ts`
- `src/features/auth/LoginPage.tsx`
- `src/app/router/RequirePasswordChange.tsx`
- `src/app/router/protectedRoutes.tsx`
- `src/app/router/AppRoutes.tsx`
- `src/app/layout/ShellHeader.tsx`
- `tests/e2e/change-password.spec.ts`
- `tests/e2e/menu-sidebar.spec.ts` (mock logout)

### Docs
- `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`
- `docs/04-tareas/001-Generalidades/TR-GEN-02-cambio-contrasena.md`










