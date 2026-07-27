# TR-GEN-02-login-sesion — Login, bootstrap de sesion y logout

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-login-sesion](../../03-historias-usuario/001-Generalidades/HU-GEN-02-login-sesion.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | **TR-GEN-02-modelo-roles-permisos-seed** (implementada; ver §3.2 D1-1) |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-05-31 (F formal) |

**Origen:** [HU-GEN-02-login-sesion](../../03-historias-usuario/001-Generalidades/HU-GEN-02-login-sesion.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)  
**Cierre F formal:** [F-GEN-01-02-cierre-formal](F-GEN-01-02-cierre-formal.md)

---

## 1) HU Refinada (resumen)

### Titulo
Implementar autenticacion base del MVP: login, bootstrap de sesion y logout seguro.

### Narrativa
Como usuario del portal, quiero iniciar/cerrar sesion para operar solo con permisos sembrados y sin selector de empresa en contexto MONO.

### In scope / Out of scope
- In scope: login API, entrega de token Sanctum, bootstrap de preferencias y menu autorizado.
- In scope: logout con invalidacion de token/sesion.
- Out of scope: 2FA, login social, recuperacion/cambio de contrasena.

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Login exitoso devuelve **token + contexto de sesión completo** en un solo envelope (D-01).
- **AC-02**: Credenciales invalidas responden 401 con mensaje generico.
- **AC-03**: Usuario sin `Pq_Permiso` vigente: 403 con mensaje de falta de acceso.
- **AC-04**: Post-login redirige al shell sin seleccion de empresa (sin llamada extra obligatoria a `/me`).
- **AC-05**: Logout invalida token; uso posterior devuelve 401.
- **AC-06**: Tenant/`X-Paq-Cliente` invalido: error controlado (4xx), sin token.
- **AC-07**: Login incluye `locale`, `theme`, `functionalProfile`, `security` y codigos comerciales.
- **AC-08**: `GET /auth/me` devuelve el **mismo objeto de sesion** (sin `token`) para F5/recarga.
- **AC-09**: E2E: login valido → shell con contexto; logout; usuario sin permisos rechazado.
- **AC-10**: Sin `cod_login` comercial: **403 con motivo explicito** (clave i18n `auth.noCommercialProfile`).

### Escenarios Gherkin

```gherkin
Feature: Login y ciclo de sesion

  Scenario: Login valido en MONO
    Given un usuario con Pq_Permiso vigente y vinculo comercial por user_id
    And header X-Paq-Cliente valido
    When envia credenciales correctas a /api/v1/auth/login
    Then recibe token y contexto de sesion completo en una sola respuesta
    And es redirigido al shell sin selector de empresa

  Scenario: Login invalido
    Given credenciales incorrectas
    When intenta autenticarse
    Then recibe HTTP 401 y mensaje generico

  Scenario: Usuario sin permisos
    Given un usuario sin fila valida en Pq_Permiso
    When intenta login con credenciales correctas
    Then no accede al shell
    And recibe HTTP 403 con mensaje de falta de acceso

  Scenario: Usuario sin vinculo comercial
    Given un usuario con Pq_Permiso pero sin fila en PQ_PARTES_CLIENTES ni PQ_PARTES_USUARIOS
    When intenta login con credenciales correctas
    Then recibe HTTP 403
    And ve mensaje explicito de usuario sin perfil comercial configurado

  Scenario: Logout invalida token
    Given un usuario autenticado
    When ejecuta POST /api/v1/auth/logout
    Then el token queda invalidado
    And cualquier request posterior responde 401
```

---

## 3) Reglas de Negocio

1. **RN-01**: Toda request autenticada usa Bearer Sanctum y header `X-Paq-Cliente`.
2. **RN-02**: Usuario sin permiso seed activo no ingresa al shell.
3. **RN-03**: Mensaje **generico** solo ante credenciales invalidas (401). Los 403 por permiso o perfil comercial pueden ser **explicitos** (claves i18n).
4. **RN-04**: En MONO se omite completamente selector de empresa.
5. **RN-05**: Tras login, el menú lateral se carga vía `GET /api/v1/user/menu` (endpoint aparte); no va embebido en login.
6. **RN-06**: Backend reutiliza un mismo servicio `buildSessionContext(user)` en login y `/me` para evitar divergencia.
7. **RN-07**: La pantalla pública de login debe poder reutilizarse entre proyectos MONO con el mismo patrón visual base (hero + card de autenticación), dejando proyecto/textos en i18n y el look & feel encapsulado en CSS del slice.

### 3.1) Resolucion de perfil y bootstrap (decision D-01 — producto)

Al autenticar, el backend construye el **contexto de sesion** del usuario:

| Capa | Fuente | Uso |
|------|--------|-----|
| **Seguridad (menu/endpoints)** | `Pq_Permiso` → `Pq_Rol` (+ `AccesoTotal`, `PQ_RolAtributo`) | Menu API, policies |
| **Perfil funcional datos** | `PQ_PARTES_CLIENTES.user_id` **o** `PQ_PARTES_USUARIOS.user_id` (= `users.id`) | `functionalProfile`, `codCliente` / `codVendedor` |

**Nota post-seed (2026-05-29):** el seed MVP vincula por **`user_id`**, no por `users.codigo = cod_login`. Los valores comerciales son literales (`CLI-MVP-001`, `VEN-ACOT-MVP`, etc.) distintos del `codigo` de login.

**Reglas de clasificacion (producto §7.2–7.3):**

| Condicion | `functionalProfile` |
|-----------|---------------------|
| Fila en `PQ_PARTES_CLIENTES` con `user_id` = usuario autenticado | `cliente` (+ `codCliente` = `cod_login`) |
| Fila en `PQ_PARTES_USUARIOS` con `user_id` = usuario y `supervisor = false` | `vendedor` (+ `codVendedor` = `cod_login`) |
| Misma fila vendedor con `supervisor = true` | `supervisor` (+ `codVendedor` = `cod_login`) |
| Rol `VendedorAcotado` en `Pq_Rol` | `functionalProfile` = **`vendedor`** (mapeo login; rol en `security.roles`) |
| Sin fila comercial (`user_id`) pero con `Pq_Permiso` | **403** + `auth.noCommercialProfile` |

Un login **nunca** vincula cliente y vendedor a la vez.

**Decisión D-01 (cerrada — confirmacion producto):**

| # | Pregunta | Decisión |
|---|----------|----------|
| 1 | ¿Donde vive el perfil? | **`POST /api/v1/auth/login`** — respuesta 200 incluye contexto completo |
| 2 | ¿Segunda llamada tras login? | **No obligatoria.** El frontend persiste la respuesta de login y entra al shell (o gate `firstLogin`) |
| 3 | ¿Menu y preferencias? | **`locale`/`theme` en login.** **Menu NO** — siempre `GET /api/v1/user/menu` en paralelo al montar shell |
| 4 | ¿Sin `cod_login`? | **403** con clave i18n **`auth.noCommercialProfile`** (mensaje explicito, no generico) |

**`/auth/me`:** mismo objeto de sesion **sin** `token`; solo para **recarga F5**, pestaña nueva con token persistido, o refresh opcional. No reemplaza el flujo login→shell.

#### Objeto `sessionContext` (compartido login 200 y `/me` 200)

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "token": "1|...",
    "user": {
      "id": 1,
      "displayName": "Vendedor Acotado MVP",
      "login": "vendedor.acotado.mvp"
    },
    "functionalProfile": "vendedor",
    "codCliente": null,
    "codVendedor": "VEN-ACOT-MVP",
    "locale": "es",
    "theme": "generic.light",
    "firstLogin": false,
    "security": {
      "roles": ["VendedorAcotado"],
      "accesoTotal": false
    }
  }
}
```

| Campo | Login 200 | `/me` 200 | Notas |
|-------|-----------|-----------|-------|
| `token` | Sí | **No** | Sanctum solo en login |
| `user`, `functionalProfile`, codigos, `locale`, `theme`, `firstLogin`, `security` | Sí | Sí | Identicos |

#### Errores 403 diferenciados

| Caso | HTTP | `respuesta` (clave i18n sugerida) | UI |
|------|------|-----------------------------------|-----|
| Sin `Pq_Permiso` | 403 | `auth.noPermission` | Falta de acceso al portal |
| Sin `cod_login` comercial | 403 | **`auth.noCommercialProfile`** | Usuario sin cliente/vendedor asociado — contacte administracion |
| Credenciales invalidas | **401** | `auth.invalidCredentials` | **Generico** (RN-03) |

Ejemplo 403 sin vinculo comercial:

```json
{
  "error": 3001,
  "respuesta": "auth.noCommercialProfile",
  "resultado": {}
}
```

**Flujo frontend:**

```text
POST /auth/login  (body: codigo, password; header X-Paq-Cliente)
  → guardar token (localStorage) + sessionContext
  → firstLogin === true?  → NO entrar al shell (gate en TR-GEN-02-cambio-contrasena)
  → else → shell (sessionContext ya en store)
       → en paralelo: GET /user/menu
F5 / recarga con token valido:
  → GET /auth/me → refrescar sessionContext (sin token)
```

Coordinacion: seed en [TR-GEN-02-modelo-roles-permisos-seed](TR-GEN-02-modelo-roles-permisos-seed.md); filtros en [TR-GEN-02-visibilidad-datos-SistemaPartes](TR-GEN-02-visibilidad-datos-SistemaPartes.md).

### 3.2) Decisiones D1 — planificacion (cerradas 2026-05-28)

| # | Tema | Decision |
|---|------|----------|
| D1-1 | Orden vs seed | **Cumplido** (2026-05-29): seed ejecutado; usuarios §4.6 TR seed disponibles. |
| D1-2 | Tenancy (`X-Paq-Cliente`) | **Stub MVP:** tenant valido `desarrollo` (y los que defina env); ausente o invalido → **HTTP 400** + envelope (`tenant.invalid`). |
| D1-3 | Campo login (request) | **`codigo`** (alineado a `users.codigo`). |
| D1-4 | Persistencia token (frontend) | **`localStorage`** (clave `SistemaPartes.auth.token`). |
| D1-5 | `firstLogin` | Incluir en `sessionContext`; **sin** gate UI (TR cambio clave). Seed: `first_login = false`. |
| D1-6 | Auth password + perfil comercial | Autenticar con **`users.password_hash`** (`User::getAuthPassword()`). Vinculo comercial por **`user_id`** en tablas `pq_SistemaPartes_*` (no `codigo = cod_login`). |
| D1-7 | Legacy `Pq_*` | Columnas snake_case: `nombre_rol`, `acceso_total`, `id_usuario`, `id_rol`, `id_empresa`. `AccesoTotal` desde **`Pq_Rol.acceso_total`**. |
| D1-8 | Rol `VendedorAcotado` | `functionalProfile` → `vendedor`; `security.roles` incluye `VendedorAcotado`. |

**Middleware tenancy (stub):** validar header en login, logout y `/me`; ejemplo 400:

```json
{
  "error": 1001,
  "respuesta": "tenant.invalid",
  "resultado": {}
}
```

(clave i18n sugerida; coordinar TR-GEN-01-idioma.)

### 3.3) Revision C1 — post-seed (2026-05-29)

| # | Hallazgo | Accion |
|---|----------|--------|
| C1-1 | Vinculo comercial por **`user_id`**, no `codigo = cod_login` | Corregido §3.1 + D1-6 |
| C1-2 | Auth legacy: `password_hash`, `name_user`, columnas `Pq_*` snake_case | D1-6, D1-7 |
| C1-3 | Rol `VendedorAcotado` → `functionalProfile: vendedor` | D1-8 |
| C1-4 | `AccesoTotal` en `Pq_Rol.acceso_total` | D1-7 |
| C1-5 | Seed prerequisito cumplido | D1-1 |

**Veredicto:** **Apto para D** — sin bloqueantes; decisiones D1-6…D1-8 cerradas en esta revision.

---

## 4) Impacto en Datos

### Tablas afectadas
- `users`
- `personal_access_tokens` (o equivalente Sanctum)
- tablas de seguridad (`Pq_Rol`, `Pq_Permiso`) para validacion de acceso

### Seed minimo para tests
- Usuario cliente, vendedor y supervisor creados por `TR-GEN-02-modelo-roles-permisos-seed`.
- Tenant `desarrollo` disponible para `X-Paq-Cliente`.

---

## 5) Contratos de API y OpenAPI

> Endpoints de autenticacion documentados en OpenAPI y coherentes con middleware/policies.

### 5.1 Endpoints del slice

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| POST | `/api/v1/auth/login` | No | N/A | Si |
| POST | `/api/v1/auth/logout` | Bearer Sanctum + `X-Paq-Cliente` | Usuario autenticado | No |
| GET | `/api/v1/auth/me` | Bearer Sanctum + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operacion

#### POST `/api/v1/auth/login`
**Autorizacion:** publica.
**Request:** `{ "codigo": string, "password": string }` + header **`X-Paq-Cliente`** (tenant stub; invalido → 400 envelope).
**Response 200:** envelope con `sessionContext` completo incl. `token` (ver §3.1).
**Response 400:** `X-Paq-Cliente` ausente o no permitido (`tenant.invalid` u homologo).
**Response 401:** credenciales invalidas — `respuesta`: `auth.invalidCredentials` (generico).
**Response 403:** sin `Pq_Permiso` (`auth.noPermission`) **o** sin `cod_login` (`auth.noCommercialProfile`).

#### POST `/api/v1/auth/logout`
**Autorizacion:** usuario autenticado.
**Response 200:** envelope confirmando cierre de sesion.
**Response 401:** token ausente/invalido.
**Response 403:** token valido sin permiso operativo (si aplica politica).

#### GET `/api/v1/auth/me`
**Autorizacion:** usuario autenticado.
**Response 200:** mismo `sessionContext` que login **sin** campo `token`.
**Response 401:** no autenticado.
**Response 403:** autenticado sin permiso para contexto solicitado.

### 5.3 Actualizacion matriz permisos

- [x] Registrar filas para `/api/v1/auth/logout` y `/api/v1/auth/me`.
- [x] Marcar `/api/v1/auth/login` como ruta publica.
- [ ] Validar 401/403 en spec generado `/api/documentation` — **oleada F** (L5-Swagger batch final).

---

## 6) Cambios Frontend

### Pantallas / componentes
- `LoginPage`: formulario `codigo` + `password`; submit → persistir **token** (`localStorage`) + **sessionContext**; redirect al shell solo si `firstLogin === false`.
- `LoginPage` debe usar controles **DevExtreme** para la UI final (`TextBox`, `Button`, `SelectBox` para idioma), manteniendo los `data-testid` públicos del slice.
- `AuthBootstrap`: en **recarga F5** con token → `GET /auth/me`; en flujo post-login **no** llamar `/me` si ya hay contexto de login.
- `sessionContext` store: fuente en login; refresh opcional via `/me`; token leido de `localStorage`.
- `firstLogin`: **sin** pantalla de cambio de clave ni redirect — ver [TR-GEN-02-cambio-contrasena](TR-GEN-02-cambio-contrasena.md).
- `AvatarMenu` / logout minimo: invalidar token y limpiar `localStorage`.
- Claves i18n login: `auth.invalidCredentials`, `auth.noPermission`, `auth.noCommercialProfile`, `tenant.invalid` (coord. TR-GEN-01-idioma).

### Refinamiento UI login MONO (2026-05-31)
- Se adopta un patrón visual **reusable** alineado con `PaqSuite-IA-TANGO`: layout a 2 columnas con **hero de marca** + **card de autenticación**.
- La estructura visual queda encapsulada en `src/features/auth/LoginPage.css`; la lógica de autenticación permanece en `LoginPage.tsx`.
- Norma base reusable MONO: `docs/00-contexto/_mono/01-experiencia-base/patron-ui-auth-devextreme.md`
- El contenido adaptable por proyecto MONO queda en i18n para evitar hardcodes visuales de negocio:
  - `login.title`
  - `login.subtitle`
  - `login.welcome`
  - `login.hint`
  - `login.loading`
- Para reutilización cross-project, conservar este contrato del componente:
  - mismos `data-testid` públicos (`login-form`, `login-submit`, `login-forgot-password`, `auth-error-*`, `auth-notice-password-reset-success`);
  - mismo flujo (`codigo` + `password` → persist token/contexto → redirect/gate `firstLogin`);
  - branding/textos solo vía locales, no dentro del JSX.
- Criterio de portabilidad MONO: cambiar **textos**, **paleta**, **branding** y eventualmente assets del hero, sin reescribir el flujo auth ni los selectores usados por tests E2E.
- Bootstrap DX obligatorio para proyectos reutilizadores: configurar `VITE_DEVEXTREME_LICENSE` en `frontend/.env` o `frontend/.env.local`; `src/init-devextreme-license.ts` registra la licencia antes de montar la app y falla en build productivo si falta la clave.

### data-testid sugeridos
- `login-form`
- `login-submit`
- `auth-error-generic`
- `auth-error-no-commercial-profile`
- `avatar-logout`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T1 | Backend | `POST /login` + `buildSessionContext` + errores 403 diferenciados | **Cumplido** (OpenAPI → oleada F) |
| T2 | Backend | Implementar `POST /api/v1/auth/logout` | **Cumplido** |
| T3 | Backend | `GET /auth/me` reutiliza `buildSessionContext` (sin token) | **Cumplido** |
| T4 | Backend | Vinculos comerciales seed (**TR seed**, prerequisito D1-1) | **Cumplido** — lookup por `user_id` en login |
| T5 | Frontend | Login (`codigo`) + bootstrap + logout + localStorage | **Cumplido** |
| T6 | Tests | Integration + E2E; tenant 400; usuarios seed `firstLogin=false` | **Cumplido** (Feature + E2E mock) |
| T7 | Docs | Actualizar matriz/OpenAPI | Matriz **OK**; OpenAPI anotaciones → **oleada F** |

---

## 8) Estrategia de Tests

- **Unit:** servicio de autenticacion y validaciones de credenciales.
- **Integration:** login 200 contexto completo; `/me` paridad sin token; 403 `auth.noPermission` y `auth.noCommercialProfile`; 401 generico; **400** tenant invalido (`X-Paq-Cliente`).
- **E2E:** login → shell sin `/me` intermedio (usuario `firstLogin=false`); F5 refresca via `/me`; mensaje explicito sin vinculo comercial.

---

## 9) Riesgos y Edge Cases

- Encabezado `X-Paq-Cliente` ausente o invalido → **400** envelope (stub `desarrollo`; ver §3.2 D1-2).
- Token valido con usuario deshabilitado entre requests.
- Desalineacion entre seed de permisos y politicas de login.
- Usuario con `firstLogin=true` intenta usar shell antes de TR cambio clave → bloqueo en frontend, no redirect.

---

## 10) Checklist final

> **Cierre post-D (2026-05-29):** Parte **F** (verificacion formal + OpenAPI en `/api/documentation`) **diferida** a oleada conjunta al final de Generalidades, segun acuerdo de equipo.

### Evidencia smoke (SQL Server `Diccionario_000205_012`)

- `php artisan test` — 23 passed (9 auth).
- Smoke manual `:8088`: login cliente/vendedor acotado 200; 403 sin permiso/vinculo; 400 tenant; `/me` + logout OK.
- Frontend: `npm run build` verde; scaffold Vite completado.
- Refinamiento UI login MONO (2026-05-31): `npm test` frontend **41 passed** tras aplicar nuevo layout del login.

### Checklist del slice
- [x] AC cumplidos (AC-08 `/me` paridad verificada)
- [x] Login/logout planificados con envelope
- [x] Bootstrap post-login sin selector de empresa

### Checklist normas transversales
- [x] Endpoints nuevos/modificados con policy en codigo (`auth:sanctum` + tenant stub)
- [x] Matriz endpoint ↔ permiso actualizada (`matriz-permisos-mvp.md` §Autenticacion)
- [ ] ~~OpenAPI en `/api/documentation` coherente~~ **Diferido oleada F**
- [x] 401/403 planificados en codigo (documentacion OpenAPI → oleada F)
- [x] Envelope JSON respetado
- [x] `X-Paq-Cliente` documentado donde aplique (§3.2, middleware `paq.tenant`)
- [x] Tests API incluyen 401 y 403 (`AuthLoginTest`)
- [x] Sin ampliacion de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

### Backend
- `app/Http/Controllers/AuthController.php`
- `app/Services/Auth/LoginService.php`, `SessionContextBuilder.php`
- `app/Http/Middleware/ValidatePaqTenant.php`
- `app/Exceptions/AuthFlowException.php`, `app/Support/AuthErrorCodes.php`
- `config/paqsuite_tenant.php`, `config/paqsuite_seed.php` (`mvpPassword`)
- `routes/api.php`
- `app/Http/Middleware/Authenticate.php` (API sin redirect login)
- `app/Exceptions/Handler.php` (401 envelope)
- `tests/Feature/AuthLoginTest.php`

### Frontend
- `src/features/auth/` — `AuthApp`, `LoginPage`, `LoginPage.css`, `ShellPage`, storage, API
- `src/app/App.tsx`, `src/shared/http/client.ts`
- `src/locales/*.json` — claves reutilizables `login.subtitle`, `login.welcome`, `login.hint`, `login.loading`
- Scaffold: `index.html`, `tsconfig*.json`, `vite-env.d.ts`, `playwright.config.ts`, `.env.example`

### OpenAPI
- Pendiente oleada F (anotaciones L5-Swagger).

### Docs
- `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` (auth ya registrado)










