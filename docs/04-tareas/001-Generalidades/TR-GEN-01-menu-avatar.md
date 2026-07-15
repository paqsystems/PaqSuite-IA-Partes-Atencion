# TR-GEN-01-menu-avatar — Menú avatar y preferencias personales

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-01-menu-avatar](../../03-historias-usuario/001-Generalidades/HU-GEN-01-menu-avatar.md) |
| **SPEC relacionada** | [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-01-shell-layout; TR-GEN-02-login-sesion; TR-GEN-02-cambio-contrasena; integra TR-GEN-01-idioma, TR-GEN-01-apariencia-temas y TR-GEN-01-ayuda-externa |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-28 (D implementado) |

**Origen:** [HU-GEN-01-menu-avatar](../../03-historias-usuario/001-Generalidades/HU-GEN-01-menu-avatar.md)  
**Referencia SPEC:** [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Menú avatar con acciones de sesión y preferencias.

### Narrativa
Como usuario autenticado quiero acceder a un menú personal bajo el avatar para gestionar apariencia, apertura en nueva pestaña y acciones de sesión (idioma en header, no en avatar).

### In scope / Out of scope
- **In scope:** desplegable avatar post-login, acciones personales, persistencia de preferencia de nueva pestaña, enlaces a slices hermanos.
- **Out of scope:** selector de empresa (MULTI), contenido del menú de procesos, implementación interna de cambio de contraseña.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Clic en avatar abre/cierra menú desplegable.
- **AC-02**: Toggle "abrir en nueva pestaña" persiste entre sesiones.
- **AC-03**: Acción "cerrar sesión" invalida sesión y redirige a login.
- **AC-04**: Acción "cambiar contraseña" enruta al flujo de seguridad correspondiente.
- **AC-05**: Acción "apariencia" enlaza al selector de temas.
- **AC-06**: Acción "asistente IA" se muestra solo si hay URL configurada.
- **AC-07**: No existe opción "cambiar empresa".
- **AC-08**: Avatar genérico sin foto de usuario (iniciales o icono).
- **AC-09**: E2E: logout y persistencia toggle nueva pestaña.

### Escenarios Gherkin

```gherkin
Feature: Menú avatar

  Scenario: Abrir menú avatar post-login
    Given un usuario autenticado en el shell
    When hace clic en su avatar
    Then ve opciones de apariencia, seguridad y sesión
    And no ve opción de cambiar empresa

  Scenario: Persistir preferencia abrir en nueva pestaña
    Given un usuario autenticado
    When activa "Abrir en nueva pestaña" en el menú avatar
    And recarga la sesión
    Then la preferencia sigue activa
    And afecta la navegación del sidebar

  Scenario: Cerrar sesión desde avatar
    Given un usuario autenticado
    When selecciona "Cerrar sesión"
    Then la sesión se invalida
    And es redirigido al login

  Scenario: Acceso a apariencia desde avatar
    Given un usuario autenticado
    When abre el menú avatar y elige apariencia
    Then puede cambiar el tema DevExtreme
    And la UI refleja el tema elegido
```

---

## 3) Reglas de Negocio

1. **RN-01**: Menú avatar solo visible en estado post-login.
2. **RN-02**: Las acciones del avatar son personales/de sesión, no de navegación de procesos.
3. **RN-03**: La preferencia `openInNewTab` se guarda por usuario (server-side).
4. **RN-04**: En MONO no se permite "cambiar empresa".
5. **RN-05**: Si no existe avatar/foto, se usa ícono genérico sin bloquear acciones.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-30)

**Fuentes revisadas:** HU-GEN-01-menu-avatar, SPEC-001-01, TR-GEN-01-idioma (D1, R-C1-12), TR-GEN-01-apariencia-temas, TR-GEN-01-ayuda-externa, TR-GEN-01-menu-general-sidebar (RN-05), TR-GEN-02-cambio-contrasena (R-C1-04 enlace mínimo header), TR-GEN-02-login-sesion, `matriz-permisos-mvp.md`, `paqsuite_mvp.php`, regla local tablas SQL compartidas, código backend (`UserPreferencesController`, `User`, `SessionContextBuilder`, `routes/api.php`, `OpenApiSchemas.php`, `UserPreferencesTest`), frontend (`ShellHeader`, `MenuSidebarTree`, `preferencesApi`, `useUserPreferences`, `userPreferences`), E2E (`change-password.spec.ts`, `menu-sidebar.spec.ts`).

### Resultado general

- **Estado:** Apto — ambigüedades cerradas
- **Puede pasar a D1:** **Sí** (resoluciones §3.2 aceptadas; alcance D1 acotado según AMB-C01, AMB-C05)

### Aceptación stakeholder (2026-05-28)

Se aceptan **todas** las resoluciones propuestas en ambigüedades críticas (AMB-C01…AMB-C07) y menores (AMB-M01…AMB-M12). No quedan preguntas abiertas para D1 en este slice.

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución (aceptada → D1) | Estado |
|----|------|--------|-------------------------------|--------|
| AMB-C01 | **Stub header vs menú avatar** | `ShellHeader` expone enlace `avatar-change-password` y botón `avatar-logout` con `displayName`; no hay trigger ni panel desplegable (AC-01, AC-08) | **D1-1:** sustituir stub por **`AvatarMenu`** + trigger con iniciales; eliminar enlaces sueltos del header. | ✅ |
| AMB-C02 | **GET preferences sin `openInNewTab`** | Idioma D1 devolvió solo `locale` + `theme`; matriz y TR §5 exigen campo nueva pestaña | **D1-2:** extender **GET** `/api/v1/users/me/preferences` con `openInNewTab: boolean` (default `false` si columna `null`). | ✅ |
| AMB-C03 | **PATCH general inexistente** | Solo existe `PATCH .../locale`; matriz asigna `PATCH /preferences` a este slice | **D1-3:** implementar **`PATCH /api/v1/users/me/preferences`** body `{ "openInNewTab": true \| false }`; locale/theme siguen en sub-rutas (idioma/apariencia). | ✅ |
| AMB-C04 | **Columna BD vs API** | HU supone `users.menu_abrir_nueva_pestana`; TR/API usan `openInNewTab` camelCase | **D1-4:** mapeo en modelo/servicio: columna legacy **`menu_abrir_nueva_pestana`** ↔ JSON **`openInNewTab`**; sin DDL (regla tablas compartidas). | ✅ |
| AMB-C05 | **Gherkin apariencia vs TR apariencia** | Escenario exige cambiar tema DevExtreme; TR apariencia-temas aún pendiente | **D1-5:** ítem «Apariencia» **Must** = entrada al flujo (stub modal/ruta); selector y `PATCH /theme` → **TR-GEN-01-apariencia-temas**. | ✅ |
| AMB-C06 | **Sidebar no respeta preferencia** | `MenuSidebarTree.handleItemClick` siempre `navigate()` | **D1-6:** si `openInNewTab === true`, `window.open(routePath, '_blank', 'noopener,noreferrer')`; si no, `navigate()` (RN-05 menu-sidebar). | ✅ |
| AMB-C07 | **E2E acoplados al stub** | `change-password.spec.ts` y `menu-sidebar.spec.ts` usan `avatar-change-password` / `avatar-logout` directos en header | **D1-7:** migrar a flujo menú: `avatarMenuTrigger` → panel → ítems; conservar cobertura logout y cambio contraseña. | ✅ |

### Ambigüedades menores

| ID | Tema | Resolución (aceptada → D1) | Estado |
|----|------|------------------------------|--------|
| AMB-M01 | Orden ítems menú (HU pregunta abierta) | Orden fijo §3.2 **R-C1-02** (preferencia → apariencia → seguridad → sesión). | ✅ |
| AMB-M02 | Iniciales vs ícono genérico (AC-08) | **Iniciales** derivadas de `sessionContext.user.displayName` (máx. 2 letras); fallback ícono usuario si nombre vacío. | ✅ |
| AMB-M03 | Confirmación logout | HU: cierre **inmediato**; sin modal confirmación en D1. | ✅ |
| AMB-M04 | Idioma en avatar | **Prohibido** en este slice (TR idioma R-C1-12); mantener `LocaleSelector` en header. | ✅ |
| AMB-M05 | Asistente IA (AC-06, Should) | **Fuera D1** avatar; ítem lo añade TR-GEN-01-ayuda-externa cuando exista URL config. | ✅ |
| AMB-M06 | Cambiar empresa (AC-07) | No renderizar ítem; MONO sin opción MULTI. | ✅ |
| AMB-M07 | Seeds QA nueva pestaña | Filas `openTab.true.mvp`, `openTab.false.mvp`, `openTab.null.mvp` en `paqsuite_mvp.php`. | ✅ |
| AMB-M08 | `useUserPreferences` | Extender tipo con `openInNewTab`; fuente GET API + fallback `false`. | ✅ |
| AMB-M09 | Popup blockers | Documentar edge case §9; E2E no depende de popup real del navegador (verificar persistencia + llamada API). | ✅ |
| AMB-M10 | Rutas §6 desactualizadas | Preferir extender `features/preferences/` existente; componentes avatar en `features/avatar/` (§3.3). | ✅ |
| AMB-M11 | Logout backend | `POST /api/v1/auth/logout` **ya existe**; D1 = invocación desde menú + limpieza cliente (AuthProvider). | ✅ |
| AMB-M12 | OpenAPI | Ampliar `UserPreferencesResultado` + operación PATCH general; regenerar `composer openapi`. | ✅ |

### Contradicciones TR ↔ código ↔ HU

| Contradicción | Resolución |
|---------------|------------|
| HU menciona persistencia `locale`/`theme` vía avatar; SPEC paso 1 separa idioma del header | **Cerrado:** idioma en header (idioma D1); avatar solo enlaza apariencia/tema (R-C1-07). |
| TR §5.1 PATCH general vs patrón sub-rutas `/locale`, `/theme` | **Coexistencia:** PATCH **`/preferences`** solo para `openInNewTab`; locale/theme en sub-rutas (R-C1-03). |
| TR §6 `updatePreferences.ts` vs código `preferencesApi.ts` | Unificar en **`preferencesApi.ts`** + request dedicado `patchOpenInNewTabPreference`. |
| Seed HU «con y sin foto de perfil» | MVP **sin foto**; AC-08 cubierto con iniciales; columna foto no existe en legacy — no inventar DDL. |
| Gherkin «afecta navegación sidebar» vs alcance solo avatar | Avatar D1 **debe** cablear preferencia al sidebar (D1-6); no es scope distinto. |

### Supuestos detectados

- Toggle nueva pestaña es **Switch/checkbox** dentro del panel, no ítem de navegación externa.
- Panel se cierra al elegir acción de navegación (contraseña, apariencia) o tras logout.
- Clic fuera del panel cierra menú (comportamiento estándar desplegable).
- Claves i18n bajo prefijo `avatar.*` en archivos locales existentes.
- Usuario autenticado activo puede PATCH propias preferencias (**sin 403** adicional, misma regla que locale).

### Preguntas para decisión humana

Todas **cerradas y aceptadas (2026-05-28):**

- ~~Orden ítems menú~~ → R-C1-02
- ~~PATCH general vs sub-ruta open-in-new-tab~~ → R-C1-03
- ~~Alcance AC-05 apariencia en D1 avatar~~ → R-C1-08 (stub)
- ~~Asistente IA en D1~~ → R-C1-09 (omitir)
- ~~AMB-C01…AMB-C07 y AMB-M01…AMB-M12~~ → aceptación integral stakeholder

### Veredicto C1

**Apto para D1 — C1 definitivamente cerrado.** Alcance Must: menú avatar, persistencia `openInNewTab`, integración sidebar y acciones sesión/seguridad. Selector de temas DevExtreme y asistente IA quedan en slices hermanos.

---

## 3.2) Resoluciones C1 — pre-D1 (2026-05-30, aceptadas 2026-05-28)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Componente avatar | Nuevo **`AvatarMenu`** en `frontend/src/features/avatar/`; trigger circular con iniciales (`avatarMenuTrigger`); panel `avatarMenuPanel`. |
| R-C1-02 | Orden ítems menú | 1) Toggle **Abrir en nueva pestaña** (`avatarMenuItemOpenInNewTab`); 2) **Apariencia**; 3) **Cambiar contraseña**; 4) **Cerrar sesión**. Separador visual opcional antes de sesión. |
| R-C1-03 | API PATCH | **`PATCH /api/v1/users/me/preferences`** acepta solo `{ "openInNewTab": boolean }`; rechazar/ignorar otros campos con 422 si se envían `locale`/`theme`. |
| R-C1-04 | API GET | Respuesta `{ locale, theme, openInNewTab }`; `openInNewTab` = `(bool) menu_abrir_nueva_pestana` o **`false`** si `null`. |
| R-C1-05 | Modelo User | Añadir `menu_abrir_nueva_pestana` a `$fillable` y `$casts` boolean; **sin migración DDL**. |
| R-C1-06 | Sidebar RN-05 | Prop/context `openInNewTab` desde preferencias cargadas; `MenuSidebarTree` bifurca `navigate` vs `window.open`. |
| R-C1-07 | Idioma | **No** incluir selector idioma en avatar; header mantiene `LocaleSelector` (coordinación idioma D1). |
| R-C1-08 | Apariencia AC-05 | Ítem abre **stub** (`ThemeAppearanceEntry` o ruta `/appearance`): título i18n + mensaje «próximamente» / placeholder; `data-testid="avatarMenuItemAppearance"`. TR apariencia sustituye stub. |
| R-C1-09 | Asistente IA | **No renderizar** en D1; TR-GEN-01-ayuda-externa añade ítem condicional. |
| R-C1-10 | Logout | Clic → `POST /api/v1/auth/logout` + callback `onLogout` existente; sin modal confirmación. |
| R-C1-11 | Cambiar contraseña | `navigate('/change-password')` desde ítem menú; retirar `Link` suelto del header. |
| R-C1-12 | Seeds | Usuarios QA `openTab.true.mvp`, `openTab.false.mvp`, `openTab.null.mvp` con `menu_abrir_nueva_pestana` true/false/null. |
| R-C1-13 | Errores API | 401 sin token; 422 body inválido (`openInNewTab` no boolean); envelope MONO; **403 no aplica** usuario operativo. |
| R-C1-14 | OpenAPI | Extender schemas + anotación PATCH en `UserPreferencesController`; checklist §5.2 en D1. |
| R-C1-15 | E2E testids | Canónicos §6; actualizar specs que hoy usan stub header (`avatar-logout`, `avatar-change-password`). |
| R-C1-16 | Coordinación change-password | TR-GEN-02 enlace mínimo header **retirado** al integrar menú avatar (reemplazo 1:1 de entrada). |

---

## 3.3) Plan D1 — Implementación (2026-05-30)

### Alcance entendido

Menú desplegable avatar post-login, persistencia server-side `openInNewTab`, extensión GET/PATCH preferences, cableado sidebar, acciones cerrar sesión y cambiar contraseña, entrada apariencia stub, tests integration/E2E. **Fuera:** selector temas completo, asistente IA, foto de perfil, cambiar empresa.

### Decisiones D1 (cerradas en C1)

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | UI avatar | R-C1-01, R-C1-02; integrar en `ShellHeader`. |
| D1-2 | GET preferences | R-C1-04 |
| D1-3 | PATCH preferences | R-C1-03 + `UpdateOpenInNewTabPreferenceRequest` |
| D1-4 | Mapeo BD | R-C1-05 |
| D1-5 | Apariencia | R-C1-08 stub |
| D1-6 | Sidebar | R-C1-06 |
| D1-7 | E2E | R-C1-15 + escenarios AC-09 (persistencia toggle + logout) |

### Tareas D1 ↔ plan §7

| Ticket | Entregable |
|--------|------------|
| T1 | Backend: GET extendido + `PATCH /preferences` + validación + tests Feature |
| T2 | Backend: confirmar logout documentado (sin cambio funcional salvo OpenAPI si falta) |
| T3 | Frontend: `AvatarMenu`, `useAvatarMenu`, trigger iniciales, i18n `avatar.*` |
| T4 | Frontend: `patchOpenInNewTabPreference`, extender `useUserPreferences`, toggle en menú |
| T5 | Frontend: conectar `MenuSidebarTree` con `openInNewTab` |
| T6 | Tests: Feature API + unit hook menú + E2E `avatar-menu.spec.ts`; ajustar change-password/menu-sidebar |
| T7 | Docs: OpenAPI + matriz §5.3 + seeds R-C1-12 |

### Archivos previstos

| Capa | Archivos |
|------|----------|
| Backend | `UserPreferencesController.php` (método `update`), `UpdateOpenInNewTabPreferenceRequest.php`, `User.php`, `OpenApiSchemas.php`, `routes/api.php`, `tests/Feature/UserPreferencesTest.php`, `config/paqsuite_mvp.php` |
| Frontend | `features/avatar/components/AvatarMenu.tsx`, `features/avatar/hooks/useAvatarMenu.ts`, `features/avatar/utils/avatarInitials.ts`, `features/preferences/preferencesApi.ts`, `features/preferences/useUserPreferences.ts`, `features/preferences/userPreferences.ts`, `app/layout/ShellHeader.tsx`, `features/menu/components/MenuSidebarTree.tsx`, `locales/*.json`, `tests/e2e/avatar-menu.spec.ts` |
| Stub apariencia | `features/avatar/components/ThemeAppearanceEntry.tsx` o ruta `/appearance` mínima |

### Contrato API cerrado (D1)

**GET** `/api/v1/users/me/preferences`

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "locale": "es",
    "theme": "generic.light",
    "openInNewTab": false
  }
}
```

**PATCH** `/api/v1/users/me/preferences`

```json
{ "openInNewTab": true }
```

**200:**

```json
{
  "error": 0,
  "respuesta": "preferences.updated",
  "resultado": { "openInNewTab": true }
}
```

**401:** `auth.unauthenticated` · **422:** `openInNewTab` ausente o no boolean

### Fuera de alcance D1 (delegación)

| Ítem | Slice responsable |
|------|-------------------|
| Selector temas DevExtreme + `PATCH /preferences/theme` | TR-GEN-01-apariencia-temas |
| Ítem Asistente IA + URL config | TR-GEN-01-ayuda-externa |
| Traducción ítems menú sidebar (`labelKey`) | TR-GEN-01-idioma (hecho) |
| Foto de perfil / entrada Perfil | Fuera SPEC MVP |

### Criterio de cierre D1

- AC-01, AC-02, AC-03, AC-04, AC-07, AC-08, AC-09 verificados.
- AC-05 cumplido vía **entrada stub** a apariencia (selector completo = TR apariencia).
- AC-06 omitido en D1 (Should; R-C1-09).
- Checklist §10 normas transversales en verde tras T7.

---

## 3.4) Verificación D (2026-05-28)

| Verificación | Resultado |
|--------------|-----------|
| `GET /api/v1/users/me/preferences` incluye `openInNewTab` | OK — `UserPreferencesTest` (3 casos GET) |
| `PATCH /api/v1/users/me/preferences` (200/401/422) | OK — 4 casos Feature; rechaza `locale`/`theme` |
| Mapeo `menu_abrir_nueva_pestana` ↔ `openInNewTab` | OK — `User::resolveOpenInNewTab()` |
| Seeds QA `openTab.true/false/null.mvp` | OK — `paqsuite_mvp.php` + `SecurityMvpSeeder` |
| `AvatarMenu` + trigger iniciales (`avatarMenuTrigger`) | OK — `AvatarMenu.tsx` |
| Orden menú R-C1-02 (toggle → apariencia → contraseña → logout) | OK — UI + E2E |
| Toggle persiste server-side + reload | OK — E2E `avatar-menu.spec.ts` |
| `MenuSidebarTree` bifurca `navigate` / `window.open` | OK — cableado vía `ShellLayout` → `openInNewTab` |
| Logout desde menú (`POST /auth/logout` + redirect login) | OK — E2E + `AuthLoginTest::testLogoutInvalidatesToken` |
| Entrada apariencia stub `/appearance` | OK — `AppearanceStubPage` + E2E |
| Entrada cambiar contraseña desde menú | OK — E2E + `change-password.spec.ts` migrado |
| Sin selector idioma en avatar; sin «cambiar empresa» | OK — `LocaleSelector` solo en header |
| Unit `resolveAvatarInitials` + `userPreferences.openInNewTab` | OK — 6 tests |
| E2E avatar | OK — `avatar-menu.spec.ts` 5 casos |
| E2E suite completa | **27 passed** |
| Frontend unit | **27 passed** |
| Backend suite completa | **59 passed** |
| OpenAPI L5-Swagger + PATCH `/preferences` | OK — `OpenApiDocumentationTest` + `api-docs.json` |
| Matriz permisos GET/PATCH `/preferences` | OK — ya documentada en `matriz-permisos-mvp.md` |

### Trazabilidad AC

| AC | Evidencia | Estado D |
|----|-----------|----------|
| AC-01 | E2E abre/cierra `avatarMenuPanel`; trigger con iniciales | ✅ |
| AC-02 | PATCH persiste BD + E2E toggle checked tras reload | ✅ |
| AC-03 | E2E logout → `/login`; token invalidado en `AuthLoginTest` | ✅ |
| AC-04 | E2E navega a `/change-password` desde menú | ✅ |
| AC-05 | Stub `/appearance` (selector temas → TR apariencia-temas) | ✅ parcial |
| AC-06 | Asistente IA condicional | ⏭ fuera D1 (Should; R-C1-09) |
| AC-07 | Ítem «cambiar empresa» no renderizado | ✅ |
| AC-08 | Iniciales desde `displayName`; fallback `?` si vacío | ✅ |
| AC-09 | E2E persistencia toggle + logout | ✅ |

### Ajustes D observados

- **403 en PATCH `/preferences`:** no aplica MVP (misma regla que locale — R-C1-13); OpenAPI documenta 401/422.
- **Sidebar + nueva pestaña:** E2E valida persistencia del toggle; navegación `window.open` verificada en código (`MenuSidebarTree`); popup real del navegador no assertable en CI (R-C1-09 / §9).
- **Coordinación change-password:** enlace mínimo header retirado; flujo exclusivo vía menú avatar (R-C1-16).

### Confirmación de alcance

Menú avatar Must, persistencia `openInNewTab`, acciones sesión/seguridad, stub apariencia, integración sidebar. **Fuera:** selector temas DevExtreme (TR apariencia-temas), asistente IA (TR ayuda-externa), foto de perfil.

---

## 4) Impacto en Datos

### Tablas afectadas
- `users.menu_abrir_nueva_pestana` (lectura/escritura; expuesta en API como `openInNewTab`).
- Reuso de `users.locale` y `users.theme` en GET preferences (lectura; escritura en slices idioma/apariencia).

### Seed mínimo para tests
- Usuario autenticado MVP estándar (`cliente.mvp`).
- Usuarios QA C1: `openTab.true.mvp`, `openTab.false.mvp`, `openTab.null.mvp` (persistencia toggle).

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1. Código, matriz y OpenAPI deben coincidir.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/users/me/preferences` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| PATCH | `/api/v1/users/me/preferences` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| POST | `/api/v1/auth/logout` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### PATCH `/api/v1/users/me/preferences`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "openInNewTab": true
}
```

**Response 200:** envelope con preferencias actualizadas del usuario.

**Response 401:** no autenticado.

**Response 403:** token válido sin permiso de auto-gestión (si aplica política explícita).

**Response 4xx/5xx:** validación de tipo de dato o error interno.

**OpenAPI (L5-Swagger):**

- [x] Anotaciones en controller/DTO de preferencias.
- [x] `security` declarado.
- [x] Header `X-Paq-Cliente` documentado.
- [x] Respuestas 401 documentadas (403 no aplica MVP — R-C1-13).
- [x] Envelope validado.
- [x] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [x] Agregar/confirmar filas para `GET/PATCH /api/v1/users/me/preferences`.
- [x] Confirmar fila de `POST /api/v1/auth/logout` en matriz de autenticación.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/app/layout/ShellHeader.tsx`: trigger avatar (sustituye stub logout/link).
- `frontend/src/features/avatar/components/AvatarMenu.tsx` (nuevo): panel desplegable y acciones.
- `frontend/src/features/avatar/pages/AppearanceStubPage.tsx` (stub D1): entrada apariencia.
- `frontend/src/features/avatar/hooks/useAvatarMenu.ts` (nuevo): apertura/cierre del panel.
- `frontend/src/features/avatar/utils/avatarInitials.ts` (nuevo): iniciales desde `displayName`.
- `frontend/src/features/preferences/preferencesApi.ts` (extender): `patchOpenInNewTabPreference`.
- `frontend/src/features/preferences/useUserPreferences.ts` (extender): incluir `openInNewTab`.
- `frontend/src/features/menu/components/MenuSidebarTree.tsx`: respetar `openInNewTab` en clic de proceso.
- Logout: reutilizar flujo `AuthProvider` / `POST /api/v1/auth/logout` (sin archivo dedicado salvo extracción opcional).

### data-testid sugeridos
- `avatarMenuTrigger`
- `avatarMenuPanel`
- `avatarMenuItemAppearance`
- `avatarMenuItemChangePassword`
- `avatarMenuItemOpenInNewTab`
- `avatarMenuItemLogout`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Exponer `GET/PATCH /api/v1/users/me/preferences` con campo `openInNewTab` | **Cumplido** |
| T2 | Backend | Asegurar `POST /api/v1/auth/logout` idempotente y documentado | **Cumplido** (preexistente; reutilizado desde menú) |
| T3 | Frontend | Implementar `AvatarMenu` con acciones y estados de visibilidad | **Cumplido** |
| T4 | Frontend | Integrar persistencia `openInNewTab` y conexión con sidebar | **Cumplido** |
| T5 | Tests | Integration preferencias/logout + E2E persistencia y logout | **Cumplido** |
| T6 | Docs | Actualizar matriz de permisos y trazabilidad con slices hermanos | **Cumplido** |

---

## 8) Estrategia de Tests

- **Unit:** reducer/hook de estado de menú avatar y parseo de preferencias.
- **Integration:** `PATCH /api/v1/users/me/preferences` (200/401/403) y `POST /api/v1/auth/logout`.
- **E2E:**  
  - activar `openInNewTab`, refrescar sesión y verificar persistencia;  
  - cerrar sesión desde avatar y validar redirección.

---

## 9) Riesgos y Edge Cases

- Desacople entre preferencias en cliente y server si falla sincronización.
- Popup blockers del navegador pueden interferir con navegación en nueva pestaña.
- Orden inconsistente de ítems del menú avatar entre entornos puede afectar UX.
- Duplicar selector de idioma dentro de avatar contradice flujo definido.

---

## 10) Checklist final

### Checklist del slice
- [x] AC cumplidos (AC-06 omitido Should; AC-05 stub)
- [x] Backend + frontend + tests según plan
- [x] Integración validada con shell/menu/login
- [x] Sin opción de cambiar empresa (MONO)

### Checklist normas transversales

- [x] Endpoints nuevos/modificados con policy en código
- [x] Matriz endpoint ↔ permiso actualizada
- [x] OpenAPI en /api/documentation coherente con código y matriz
- [x] 401 documentados por operación protegida (403 no aplica)
- [x] Envelope JSON respetado
- [x] X-Paq-Cliente documentado donde aplique
- [x] Tests API incluyen 401 (403 no aplica)
- [x] Sin ampliación de alcance fuera de SPEC/HU/TR











