# TR-GEN-01-apariencia-temas — Apariencia y temas por usuario

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-01-apariencia-temas](../../03-historias-usuario/001-Generalidades/HU-GEN-01-apariencia-temas.md) |
| **SPEC relacionada** | [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-01-shell-layout; TR-GEN-01-menu-avatar; TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-31 (F formal) |

**Origen:** [HU-GEN-01-apariencia-temas](../../03-historias-usuario/001-Generalidades/HU-GEN-01-apariencia-temas.md)  
**Referencia SPEC:** [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)  
**Cierre F formal:** [F-GEN-01-02-cierre-formal](F-GEN-01-02-cierre-formal.md)

---

## 1) HU Refinada (resumen)

### Título
Apariencia DevExtreme por usuario con fallback seguro.

### Narrativa
Como usuario autenticado quiero seleccionar apariencia global desde el menú avatar para trabajar con un tema persistente entre sesiones.

### In scope / Out of scope
- **In scope:** selector de tema desde avatar, aplicación inmediata, persistencia en `users.theme`, fallback `generic.light`.
- **Out of scope:** temas por empresa, theme builder custom, ajustes visuales pixel-perfect.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Menú avatar expone acción de apariencia.
- **AC-02**: Seleccionar tema aplica cambio sin recarga completa.
- **AC-03**: Tema se persiste en `users.theme` y se recupera al próximo login.
- **AC-04**: Si `users.theme` es nulo/inválido, se usa `generic.light`.
- **AC-05**: Error al guardar preferencia revierte al último tema válido y notifica.
- **AC-06**: E2E valida cambio de tema y persistencia.

### Escenarios Gherkin

```gherkin
Feature: Apariencia y temas

  Scenario: Tema por defecto generic.light
    Given un usuario sin users.theme
    When accede al shell post-login
    Then la interfaz usa el tema generic.light

  Scenario: Cambiar y persistir tema
    Given un usuario autenticado
    When selecciona un tema desde Apariencia en el menú avatar
    Then la UI aplica el tema inmediatamente
    And users.theme se persiste
    And en el próximo login ve el mismo tema

  Scenario: Tema inválido en base de datos
    Given un usuario con users.theme inválido
    When accede al portal
    Then se aplica generic.light sin error fatal
```

---

## 3) Reglas de Negocio

1. **RN-01**: Tema por defecto del producto es `generic.light`.
2. **RN-02**: La preferencia de tema es individual por usuario en MONO.
3. **RN-03**: Solo se aceptan temas del catálogo cerrado MVP.
4. **RN-04**: Debe existir un único tema activo por sesión.
5. **RN-05**: No se presenta configuración de tema por empresa.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-28)

**Fuentes revisadas:** HU-GEN-01-apariencia-temas, SPEC-001-01 §8.1, TR-GEN-01-menu-avatar (D cerrado, stub `/appearance`), TR-GEN-01-idioma (patrón `LocaleProvider` / `PATCH locale`), TR-GEN-01-shell-layout, TR-GEN-02-login-sesion, `matriz-permisos-mvp.md`, `paqsuite_mvp.php`, regla local tablas SQL compartidas, código backend (`UserPreferencesController`, `SessionContextBuilder`, `User`, `routes/api.php`, `OpenApiSchemas.php`, `UserPreferencesTest`), frontend (`main.tsx`, `App.tsx`, `AvatarMenu`, `AppearanceStubPage`, `userPreferences`, `useUserPreferences`, `preferencesApi`, `LocaleProvider`, `shellLayout.css`), E2E (`avatar-menu.spec.ts`).

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1:** **Sí** (resoluciones §3.2; catálogo MVP cerrado en R-C1-01)

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|-------------------------------|
| AMB-C01 | **PATCH `/preferences/theme` inexistente** | Matriz documenta ruta; solo existe `PATCH locale` y `PATCH /preferences` (`openInNewTab`) | **D1-1:** implementar **`PATCH /api/v1/users/me/preferences/theme`** con catálogo cerrado y tests Feature. |
| AMB-C02 | **Tema DevExtreme estático** | `main.tsx` importa fijo `dx.light.css`; no hay `themes.current()` ni cambio runtime | **D1-2:** `syncDevExtremeTheme(themeKey)` + carga CSS dinámica / swap stylesheet para temas MVP; invocar al iniciar y al cambiar. |
| AMB-C03 | **Stub `/appearance` vs selector** | Menu-avatar D1 dejó `AppearanceStubPage`; TR §6 pide `ThemeSelectorModal` | **D1-3:** sustituir stub por **`ThemeSelectorModal`** abierto desde `AvatarMenu`; retirar ruta `/appearance` (o redirigir a dashboard). |
| AMB-C04 | **Valor legacy `light` en seeds/BD** | Seeds MVP usan `theme: 'light'`; API GET devuelve crudo; frontend `normalizeTheme` mapea `light` → `generic.light` solo en cliente | **D1-4:** **`ThemeNormalizer`** backend (lectura/escritura/persistencia **código catálogo**); GET/`sessionContext` siempre normalizado; aceptar `light`/`dark` legacy en entrada. |
| AMB-C05 | **Tema no aplicado en runtime** | `useUserPreferences` carga `theme` pero ningún provider lo aplica; shell CSS propio independiente de DevExtreme | **D1-5:** **`ThemeProvider`** (análogo `LocaleProvider`) + atributo **`data-theme`** en `#root` para shell; sync DevExtreme en mismo tick. |
| AMB-C06 | **Catálogo MVP no cerrado** | HU pregunta abierta: ¿solo generic.light/dark u otras familias (material, fluent)? | **D1-6:** catálogo MVP **cerrado:** `generic.light`, `generic.dark` (R-C1-01). Material/fluent → oleada posterior. |
| AMB-C07 | **AC-05 sin implementar** | Idioma tiene `saveErrorKey` + revert; tema no tiene flujo de error | **D1-7:** al fallar PATCH theme: mantener tema activo, `themeSaveFailed` i18n (no bloqueante), revert optimista. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | Selector en login | **No** en login (HU: post-login vía avatar); login usa `generic.light` hasta sesión. |
| AMB-M02 | GET preferences compartido | Esta TR **no duplica GET**; extiende normalización de `theme` en `show()` + OpenAPI enum. |
| AMB-M03 | Rutas §6 `updateThemePreference.ts` | Extender **`preferencesApi.ts`** con `patchThemePreference` (convención idioma/avatar). |
| AMB-M04 | `ThemeSelectorModal` vs página | Modal sobre shell (no navegación full-page); `data-testid="themeSelectorModal"`. |
| AMB-M05 | testids §6 | Canónicos: `themeSelectorModal`, `themeOption-{themeKey}`, `themeApplyButton`, `themeCurrentValue`. |
| AMB-M06 | Seeds QA tema | `theme.null.mvp`, `theme.light.mvp`, `theme.dark.mvp`, `theme.invalid.mvp` en `paqsuite_mvp.php`. |
| AMB-M07 | E2E stub avatar | Actualizar `avatar-menu.spec.ts` (apariencia abre modal) + nuevo **`theme.spec.ts`**. |
| AMB-M08 | 403 PATCH theme | **No aplica MVP** (misma regla locale/avatar — usuario autenticado operativo). |
| AMB-M09 | Shell + tema oscuro | CSS mínimo en `shellLayout.css` bajo `[data-theme="generic.dark"]` (contraste legible header/sidebar). |
| AMB-M10 | Coordinación SPEC-001-03 | D1 limitado a shell + login + controles DevExtreme existentes (demo grid); pantallas futuras heredan provider. |
| AMB-M11 | Persistencia login siguiente | PATCH + actualizar `sessionContext` local (`updateStoredSessionContext`) como locale. |
| AMB-M12 | OpenAPI enum | Schema `supportedThemes` en `OpenApiSchemas.php` + `config/paqsuite_themes.php`. |

### Contradicciones TR ↔ código ↔ HU

| Contradicción | Resolución |
|---------------|------------|
| TR §6 `App.tsx` aplica tema; hoy solo `LocaleProvider` | **`ThemeProvider`** envuelve rutas en `App.tsx` (R-C1-02). |
| Menu-avatar navega a `/appearance`; TR apariencia pide modal | Modal desde avatar; **retirar stub** (R-C1-03). |
| GET test espera `theme: light` para `locale.en.mvp` | Tras normalización D1-4, test esperará `generic.light`; seed legacy sigue válido en BD. |
| `normalizeTheme` solo frontend vs RN-03 catálogo cerrado | Backend es fuente de verdad; frontend delega en normalizer compartido / respuesta API. |
| Gherkin «sin recarga completa» | Cambio vía provider + `themes.current()` sin `location.reload()`. |

### Supuestos detectados

- Catálogo DevExtreme MVP = variante **generic** claro/oscuro (CSS `dx.light.css` / `dx.dark.css` o equivalente v25).
- Modal lista 2 opciones con etiqueta i18n (`theme.name.generic.light`, …).
- Aplicar tema no requiere remount de React tree completo.
- Claves i18n bajo prefijo `theme.*`.

### Preguntas para decisión humana

- ~~Catálogo temas MVP~~ → **Cerrado:** `generic.light`, `generic.dark` (R-C1-01).
- ~~Modal vs ruta `/appearance`~~ → **Cerrado:** modal desde avatar (R-C1-03).
- ~~Normalización `light` legacy~~ → **Cerrado:** ThemeNormalizer (R-C1-04).
- ~~Claves i18n tema~~ → **Cerrado (2026-05-28):** tabla §3.3.1.

### Aceptación stakeholder (2026-05-28)

Se aceptan resoluciones §3.2 (AMB-C/M) y catálogo i18n §3.3.1.

### Veredicto C1

**Apto para D1** con catálogo generic claro/oscuro, PATCH theme, provider runtime y sustitución del stub menu-avatar.

---

## 3.2) Resoluciones C1 — pre-D1 (2026-05-28)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Catálogo MVP | **`generic.light`**, **`generic.dark`**; default **`generic.light`**. Config `backend/config/paqsuite_themes.php` + mirror frontend `supportedThemes.ts`. |
| R-C1-02 | Provider | **`ThemeProvider`** + hook **`useCurrentTheme`** en `frontend/src/features/theme/`; integrar en `App.tsx` (junto a `LocaleProvider`). |
| R-C1-03 | UI selector | **`ThemeSelectorModal`** invocado desde `AvatarMenu` (reemplaza `navigate('/appearance')`); eliminar **`AppearanceStubPage`** y ruta `/appearance`. |
| R-C1-04 | Normalización | **`ThemeNormalizer`** backend; alias legacy `light`→`generic.light`, `dark`→`generic.dark`; inválido→default; persistir solo códigos catálogo. |
| R-C1-05 | API PATCH | **`PATCH /api/v1/users/me/preferences/theme`** body `{ "theme": "generic.dark" }`; prohibir otros campos; 422 fuera de catálogo. |
| R-C1-06 | API GET | Normalizar `theme` en `UserPreferencesController::show` y `SessionContextBuilder`. |
| R-C1-07 | DevExtreme runtime | **`syncDevExtremeTheme(themeKey)`** — `themes.current()` + swap CSS bundle del tema. |
| R-C1-08 | Contenedor raíz | Atributo **`data-theme="{themeKey}"`** en `#root` para E2E y estilos shell. |
| R-C1-09 | Errores AC-05 | Clave `preferences.themeSaveFailed`; revert optimista; sin bloquear UI. |
| R-C1-10 | Seeds QA | `theme.null.mvp`, `theme.light.mvp` (legacy `light`), `theme.dark.mvp`, `theme.invalid.mvp` (`xx`). |
| R-C1-11 | Login pre-auth | Tema **`generic.light`** fijo en pantallas login/change-password (sin selector). |
| R-C1-12 | OpenAPI | Schema enum temas + operación PATCH; checklist §5.2 en D1. |
| R-C1-13 | Coordinación avatar | Entrada «Apariencia» abre modal; E2E avatar actualizado. |
| R-C1-14 | 403 | **No aplica** usuario operativo autenticado. |

---

## 3.3) Plan D1 — Implementación (2026-05-28)

### Alcance entendido

PATCH theme backend con catálogo cerrado, normalización legacy, ThemeProvider + sync DevExtreme, modal selector desde avatar, fallback `generic.light`, tests unit/integration/E2E. **Fuera:** material/fluent, tema por empresa, theme builder, rediseño pixel-perfect shell.

### Decisiones D1 (cerradas en C1)

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Backend PATCH | R-C1-05 + `UpdateThemePreferenceRequest` |
| D1-2 | Normalización | R-C1-04 + R-C1-06 |
| D1-3 | DevExtreme | R-C1-07 |
| D1-4 | Provider UI | R-C1-02 + R-C1-08 |
| D1-5 | Modal avatar | R-C1-03 + R-C1-13 |
| D1-6 | Errores | R-C1-09 |
| D1-7 | Tests/E2E | R-C1-10 + `theme.spec.ts` |

### Tareas D1 ↔ plan §7

| Ticket | Entregable |
|--------|------------|
| T1 | Backend: `ThemeNormalizer`, `PATCH /preferences/theme`, config catálogo, tests Feature |
| T2 | Frontend: `ThemeProvider`, `useCurrentTheme`, `syncDevExtremeTheme`, `data-theme` en root |
| T3 | Frontend: `ThemeSelectorModal`, integración `AvatarMenu`, i18n `theme.*` |
| T4 | Frontend: `patchThemePreference`, sync `sessionContext` post-cambio |
| T5 | CSS shell mínimo dark + retirar stub `/appearance` |
| T6 | Tests: unit normalizer + E2E cambio/persistencia + ajuste `avatar-menu.spec.ts` |
| T7 | Docs: OpenAPI + matriz §5.3 + seeds R-C1-10 |

### Archivos previstos

| Capa | Archivos |
|------|----------|
| Backend | `app/Support/ThemeNormalizer.php`, `config/paqsuite_themes.php`, `UpdateThemePreferenceRequest.php`, `UserPreferencesController.php` (método `updateTheme`), `SessionContextBuilder.php`, `PreferencesErrorCodes.php`, `OpenApiSchemas.php`, `routes/api.php`, `tests/Feature/UserPreferencesTest.php`, `config/paqsuite_mvp.php` |
| Frontend | `features/theme/**`, `features/preferences/preferencesApi.ts`, `features/avatar/components/AvatarMenu.tsx`, `app/App.tsx`, `main.tsx`, `app/layout/shellLayout.css`, `locales/*.json`, `tests/e2e/theme.spec.ts` |
| Retirar | `features/avatar/pages/AppearanceStubPage.tsx`, ruta `/appearance` en `protectedRoutes.tsx` |

### Contrato API cerrado (D1)

**PATCH** `/api/v1/users/me/preferences/theme`

```json
{ "theme": "generic.dark" }
```

**200:**

```json
{
  "error": 0,
  "respuesta": "preferences.themeUpdated",
  "resultado": { "theme": "generic.dark" }
}
```

**401:** `auth.unauthenticated` · **422:** `preferences.invalidTheme` o `validation.failed`

**GET** `/api/v1/users/me/preferences` — campo `theme` siempre normalizado (`generic.light` | `generic.dark`).

### 3.3.1) Catálogo i18n cerrado (D1)

Separación de capas (análoga a `locale.name.{code}` / `supportedLocales.ts`):

| Capa | Contenido | Ejemplo |
|------|-----------|---------|
| Catálogo técnico | Códigos persistidos BD/API | `generic.light`, `generic.dark` |
| i18n | Etiquetas visibles | `theme.name.generic.light` → «Claro» |

#### Nombres de temas (selector)

| Clave i18n | `themeKey` | es | en | it | pt | fr |
|------------|------------|----|----|----|----|-----|
| `theme.name.generic.light` | `generic.light` | Claro | Light | Chiaro | Claro | Clair |
| `theme.name.generic.dark` | `generic.dark` | Oscuro | Dark | Scuro | Escuro | Sombre |

Uso en UI: `t(\`theme.name.${themeKey}\`)` (p. ej. `theme.name.generic.light`).

#### Textos del modal

| Clave i18n | Uso | es |
|------------|-----|-----|
| `theme.selector.title` | Título modal | Apariencia |
| `theme.selector.current` | Tema activo | Tema actual |
| `theme.selector.apply` | Botón aplicar | Aplicar |
| `theme.selector.cancel` | Cerrar | Cancelar |

#### Errores

| Clave i18n | Uso | es |
|------------|-----|-----|
| `preferences.themeSaveFailed` | AC-05 fallo PATCH | No se pudo guardar el tema. Se mantiene el tema actual. |

#### Claves existentes (coordinación)

| Clave | Rol D1 |
|-------|--------|
| `avatar.appearance` | Ítem menú avatar (se mantiene) |
| `avatar.appearance.title` | **No usar** en modal (usar `theme.selector.title`) |
| `avatar.appearance.stubMessage` | **Retirar** al eliminar stub `/appearance` |

Paridad obligatoria en `es.json`, `en.json`, `it.json`, `pt.json`, `fr.json` (`localeCatalogParity.test.ts`).

### Fuera de alcance D1

| Ítem | Destino |
|------|---------|
| Temas material / fluent / contrast | Oleada posterior |
| Tema por empresa MULTI | SPEC-001-05 |
| Selector tema en login | Fuera HU |
| Rediseño completo shell dark | CSS mínimo MVP |

### Criterio de cierre D1

- AC-01…AC-06 verificados (AC-01 vía modal desde avatar).
- Stub menu-avatar retirado.
- Checklist §10 en verde tras T7.

---

## 3.4) Verificación D (2026-05-30)

| Verificación | Resultado |
|--------------|-----------|
| `ThemeNormalizer` + alias legacy `light`/`dark` | OK — `ThemeNormalizerTest` (5 casos) |
| GET `/preferences` normaliza `theme` | OK — `UserPreferencesTest` (legacy + inválido) |
| `PATCH /preferences/theme` (200/401/422) | OK — 4 casos Feature; rechaza `locale` |
| `SessionContextBuilder` normaliza tema en login | OK — vía `ThemeNormalizer` |
| Seeds QA `theme.*.mvp` | OK — `paqsuite_mvp.php`; `theme.null.mvp` usa `''` (columna NOT NULL) |
| `ThemeProvider` + `data-theme` en `<html>` | OK — `ThemeProvider.tsx` + `syncDevExtremeTheme` |
| `syncDevExtremeTheme` bootstrap `link[rel=dx-theme]` | OK — corrige E0021 DevExtreme en arranque |
| `ThemeSelectorModal` desde avatar | OK — `AvatarMenu.tsx`; stub `/appearance` retirado |
| i18n `theme.*` + `preferences.themeSaveFailed` | OK — 5 locales + `localeCatalogParity.test.ts` |
| Unit frontend normalizer + preferences | OK — 7 tests |
| E2E `theme.spec.ts` | OK — 3 casos (modal, cambio/persistencia, inválido) |
| E2E `avatar-menu.spec.ts` apariencia | OK — abre modal (no navega a `/appearance`) |
| E2E suite completa | **30 passed** |
| Frontend unit | **31 passed** |
| Backend suite completa | **70 passed** |
| OpenAPI PATCH `/preferences/theme` + enum | OK — `composer openapi` + `OpenApiDocumentationTest` |
| Matriz permisos PATCH `/preferences/theme` | OK — `matriz-permisos-mvp.md` |

### Trazabilidad AC

| AC | Evidencia | Estado D |
|----|-----------|----------|
| AC-01 | E2E abre `themeSelectorModal` desde `avatarMenuItemAppearance` | ✅ |
| AC-02 | E2E cambia tema sin reload; `data-theme` actualizado al instante | ✅ |
| AC-03 | E2E persistencia tras reload; PATCH persiste en BD | ✅ |
| AC-04 | GET normaliza null/legacy/inválido → `generic.light` | ✅ |
| AC-05 | `preferences.themeSaveFailed` + revert optimista en `ThemeProvider` | ✅ |
| AC-06 | E2E `theme.spec.ts` cambio y persistencia | ✅ |

### Ajustes D observados

- **`theme.null.mvp`:** columna `users.theme` NOT NULL en BD compartida; seed usa cadena vacía (normalizada como ausencia de preferencia).
- **DevExtreme runtime:** requiere registrar `link[rel=dx-theme]` antes de `themes.current()`; enlace `stylesheet` manual provocaba E0021 y bloqueaba el render de login.
- **403 PATCH theme:** no aplica MVP (R-C1-14); OpenAPI documenta 401/422.

### Confirmación de alcance

Selector modal desde avatar, catálogo `generic.light`/`generic.dark`, PATCH theme, normalización legacy, provider runtime, fallback seguro. **Fuera:** material/fluent, tema por empresa, selector en login, rediseño shell dark completo.

---

## 4) Impacto en Datos

### Tablas afectadas
- `users.theme` (lectura/escritura de preferencia de apariencia).

### Seed mínimo para tests
- Usuario estándar MVP (`cliente.mvp`, normalización desde `light` legacy).
- Usuarios QA C1: `theme.null.mvp` (cadena vacía en BD), `theme.light.mvp`, `theme.dark.mvp`, `theme.invalid.mvp`.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1. Código, matriz y OpenAPI deben coincidir.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/users/me/preferences` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| PATCH | `/api/v1/users/me/preferences/theme` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### PATCH `/api/v1/users/me/preferences/theme`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "theme": "generic.dark"
}
```

**Response 200:** envelope con `theme` persistido y aplicado.

**Response 401:** no autenticado.

**Response 403:** sin permiso para actualizar preferencias (si aplica policy explícita).

**Response 422:** tema fuera del catálogo permitido.

**OpenAPI (L5-Swagger):**

- [x] Anotaciones en controller/DTO del endpoint de tema.
- [x] `security` declarado.
- [x] Header `X-Paq-Cliente` documentado.
- [x] Respuestas 401/403/422 documentadas.
- [x] Enumeración de temas permitidos en schema.
- [x] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [x] Agregar/confirmar fila para `PATCH /api/v1/users/me/preferences/theme`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- Norma base reusable MONO: `docs/00-contexto/_mono/01-experiencia-base/patron-ui-auth-devextreme.md`
- `frontend/src/features/theme/model/supportedThemes.ts` (nuevo): catálogo MVP (`generic.light`, `generic.dark`).
- `frontend/src/features/theme/syncDevExtremeTheme.ts` (nuevo): aplicación runtime DevExtreme.
- `frontend/src/features/theme/ThemeProvider.tsx` + `hooks/useCurrentTheme.ts` (nuevo).
- `frontend/src/features/theme/components/ThemeSelectorModal.tsx` (nuevo): selector invocado desde avatar.
- `ThemeSelectorModal` debe construirse con controles **DevExtreme** (`Popup`/contenedor DX-compatible + `List`/botones DX), no con radios o botones HTML nativos.
- El catálogo visible del selector debe seguir el patrón de `PaqSuite-IA-TANGO`: lista DX de temas, desacoplada del transporte/persistencia. Se adopta el **catálogo completo** soportado por `PQ_Empresa.Themes` en TANGO (generic, compact, carmine, darkmoon, darkviolet, greenmist, softblue, contrast, material y fluent), manteniendo compatibilidad legacy con `light` / `dark` / `default`.
- Regla reusable MONO de i18n: tanto la chrome del modal (`title`, `current`, `cancel`, `apply`, `confirm`) como los nombres del catálogo deben resolverse según el locale activo del portal; no dejar labels hardcodeados en un único idioma.
- Regla reusable MONO de UX para cambio de apariencia:
  - `Aplicar`: previsualiza el tema seleccionado **sin cerrar** el modal;
  - `Confirmar`: persiste el mismo tema y **cierra** el modal;
  - `Cancelar` o cerrar por `X`: revierte cualquier preview no confirmada y restaura el tema persistido;
  - el área de acciones del modal (`Cancelar`, `Aplicar`, `Confirmar`) debe permanecer visible y operativa después de cada preview, sin requerir una nueva selección para repintarse.
- Regla reusable MONO de paleta aplicada al shell:
  - el tema DevExtreme activo debe impactar no solo el catálogo/modal de apariencia, sino también superficies propias del shell (`header`, `sidebar`, `footer`, avatar-menu, selected/hover y overlays);
  - en temas oscuros no alcanza con un único oscuro genérico: la UI debe conservar la familia cromática del tema elegido (`blue`, `orange`, `teal`, `lime`, etc.) y mantener contraste legible;
  - textos e íconos del menú lateral no pueden quedar con contraste bajo respecto del fondo del tema seleccionado.
- Regla reusable MONO de responsive/i18n del modal:
  - el título del `Popup` no debe truncarse cuando el idioma activo use labels más largas;
  - la lista debe conservar la selección y la posición útil de scroll aunque el usuario elija opciones fuera del primer bloque visible;
  - el contenedor de acciones debe permanecer completamente dentro de la ventana y admitir wrap/responsive si el idioma ensancha los textos.
- El bootstrap de licencia y runtime DX queda soportado por `VITE_DEVEXTREME_LICENSE` + `src/init-devextreme-license.ts`; cualquier proyecto MONO que reutilice este slice debe configurar esa variable antes del build productivo.
- Contrato operativo MONO para licencia DevExtreme:
  - la clave debe existir en un archivo **real cargado por Vite** (`frontend/.env` o `frontend/.env.local`); `frontend/.env.example` sirve solo como plantilla y no elimina el watermark por sí solo;
  - cada cambio de `VITE_DEVEXTREME_LICENSE` requiere **reiniciar** el servidor Vite para que `import.meta.env` relea la configuración;
  - criterio de verificación mínimo: abrir una pantalla pública (`/login`, `/forgot-password`) y confirmar ausencia del banner `For evaluation purposes only`.
- Para shells MONO con overrides propios, además de `data-theme` debe exponerse un atributo derivado (`data-color-scheme=light|dark`) para aplicar estilos coherentes cuando se seleccionan temas DX oscuros distintos de `generic.dark`.
- `frontend/src/features/preferences/preferencesApi.ts` (extender): `patchThemePreference`.
- `frontend/src/features/avatar/components/AvatarMenu.tsx`: abrir modal apariencia (sin ruta `/appearance`).
- `frontend/src/app/App.tsx`: `ThemeProvider` + contenedor con `data-theme`.
- `frontend/src/app/layout/shellLayout.css`: shell y overrides propios deben consumir variables derivadas del tema activo; evitar hardcodes limitados a un único `generic.dark`.

### data-testid sugeridos
- `themeSelectorModal`
- `themeOption-{themeKey}`
- `themeApplyButton`
- `themeConfirmButton`
- `themeCurrentValue`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Exponer endpoint `PATCH /api/v1/users/me/preferences/theme` con catálogo cerrado | ✅ OpenAPI + validaciones |
| T2 | Frontend | Implementar selector de tema desde avatar y aplicación en runtime | ✅ Cambio inmediato de UI |
| T3 | Frontend | Implementar fallback `generic.light` para tema nulo/inválido | ✅ Arranque robusto |
| T4 | Frontend | Sincronizar tema persistido al iniciar sesión | ✅ Persistencia validada |
| T5 | Tests | Integration API + E2E de cambio y persistencia | ✅ Casos verdes |
| T6 | Docs | Actualizar matriz endpoint ↔ permiso | ✅ Trazabilidad completa |

---

## 8) Estrategia de Tests

- **Unit:** validación de catálogo y lógica fallback de tema.
- **Integration:** endpoint de tema con 200/401/403/422.
- **E2E:**  
  - abrir selector desde avatar y validar labels en locale activo;
  - usar `Aplicar` y verificar cambio visual sin cierre del modal;
  - usar `Confirmar` y validar persistencia tras reload / nueva sesión;
  - cancelar preview y verificar rollback al tema persistido.

---

## 9) Riesgos y Edge Cases

- Catálogo de temas no consolidado puede romper compatibilidad entre frontend/backend.
- Tema inválido cargado desde BD puede dejar UI inconsistente si no hay fallback central.
- Cambios de tema simultáneos (multitab) pueden dejar estado desfasado.
- Diferencias de nombre de tema entre DevExtreme y persistencia interna.

---

## 10) Checklist final

### Checklist del slice
- [x] AC cumplidos
- [x] Backend + frontend + tests según plan
- [x] Fallback `generic.light` validado
- [x] Integración operativa con menú avatar

### Checklist normas transversales

- [x] Endpoints nuevos/modificados con policy en código
- [x] Matriz endpoint ↔ permiso actualizada
- [x] OpenAPI en /api/documentation coherente con código y matriz
- [x] 401/403 documentados por operación protegida
- [x] Envelope JSON respetado
- [x] X-Paq-Cliente documentado donde aplique
- [x] Tests API incluyen 401 (y 403 si aplica)
- [x] Sin ampliación de alcance fuera de SPEC/HU/TR











