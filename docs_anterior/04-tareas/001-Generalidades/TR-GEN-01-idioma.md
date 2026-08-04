# TR-GEN-01-idioma — Selector de idioma e i18n base

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-01-idioma](../../03-historias-usuario/001-Generalidades/HU-GEN-01-idioma.md) |
| **SPEC relacionada** | [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-01-shell-layout (header/login); TR-GEN-02-login-sesion (estado autenticado); coordina con TR-GEN-01-menu-avatar |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-18 (F formal) |

**Origen:** [HU-GEN-01-idioma](../../03-historias-usuario/001-Generalidades/HU-GEN-01-idioma.md)  
**Referencia SPEC:** [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)  
**Cierre F formal:** [F-GEN-01-02-cierre-formal](F-GEN-01-02-cierre-formal.md)

---

## 1) HU Refinada (resumen)

### Título
Selector de idioma en login y header post-login.

### Narrativa
Como usuario quiero elegir idioma de interfaz en login y header para leer labels/mensajes en mi preferencia y conservarla entre sesiones.

### In scope / Out of scope
- **In scope:** selector en login/header, resolución de idioma inicial (`users.locale` -> `navigator.language` -> `es`), persistencia por usuario autenticado.
- **Out of scope:** traducción de datos de negocio, internacionalización avanzada fuera de shell base.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Selector visible en login y en header del shell.
- **AC-02**: Cambio de idioma actualiza textos de shell/login sin recarga completa.
- **AC-03**: Usuario autenticado persiste idioma en `users.locale`.
- **AC-04**: Si `users.locale` está vacío, se usa `navigator.language`; si no soportado, fallback `es`.
- **AC-05**: Error al guardar preferencia no rompe UI y mantiene idioma actual en cliente.
- **AC-06**: E2E valida cambio de idioma y persistencia.

### Escenarios Gherkin

```gherkin
Feature: Selector de idioma

  Scenario: Idioma por defecto es español
    Given un usuario nuevo sin users.locale
    And navigator.language no está soportado
    When accede al portal
    Then la interfaz se muestra en español (es)

  Scenario: Persistir idioma tras login
    Given un usuario autenticado
    When cambia el idioma desde el header
    Then users.locale se actualiza
    And en el próximo login ve el mismo idioma

  Scenario: Selector en login
    Given un visitante en pantalla de login
    Then ve selector de idioma
    And puede cambiar idioma antes de autenticarse

  Scenario: Interfaz en italiano
    Given el visitante selecciona italiano (it) en login
    When visualiza la pantalla de login y una grilla de prueba
    Then los labels de login y al menos un caption de grilla están en italiano
    And los textos de filtro DevExtreme del grid están en italiano
```

---

## 3) Reglas de Negocio

1. **RN-01**: Idioma por defecto del producto es `es`.
2. **RN-02**: Precedencia de resolución: `users.locale` > `navigator.language` soportado > `es`.
3. **RN-03**: Selector de idioma post-login vive en header (no en menú avatar).
4. **RN-04**: Preferencia de idioma es por usuario, no por tenant.
5. **RN-05**: Catálogo MVP cerrado: `es`, `en`, `pt`, `fr`, `it` (archivos de recursos completos por idioma antes de habilitar en selector).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-30)

**Fuentes revisadas:** HU-GEN-01-idioma, SPEC-001-01, `idioma-multilingual.md` (MONO), TR-GEN-01-shell-layout (stub idioma), TR-GEN-01-menu-general-sidebar (R-C1-09 i18n menú), TR-GEN-01-menu-avatar (GET/PATCH preferences), TR-GEN-02-login-sesion (locale en sessionContext), TR-GEN-02-cambio-contrasena (textos auth), `matriz-permisos-mvp.md`, `paqsuite_mvp.php`, código frontend (`ShellHeader`, `LoginPage`, `ChangePasswordPage`, `userPreferences`, `useUserPreferences`, `preferencesApi`), `backend/routes/api.php`, `SessionContextBuilder`, `frontend/package.json`.

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1:** **Sí** (resoluciones §3.2; **alcance D1 acotado** al shell base — ver AMB-C01)

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|-------------------------------|
| AMB-C01 | **Checklist §4 demasiado amplio** | 20 ámbitos incluyen mail, pivot, parámetros globales sin UI | **D1-1:** MVP idioma = **shell + login + auth + change-password + toolbar menú + footer + selector** + **1 grilla DevExtreme demo** para E2E italiano. Ítems §4.13–15 y datos de negocio → TRs hermanas / oleadas posteriores. |
| AMB-C02 | **Sin stack i18n instalado** | `package.json` no incluye `i18next` / `react-i18next` (solo documentado en README) | **D1-2:** instalar y configurar `i18next` + `react-i18next`; provider en `App.tsx`. |
| AMB-C03 | **API preferencias inexistente** | Matriz documenta `GET/PATCH preferences` y `PATCH locale`; **no hay rutas** en `backend/routes/api.php`. Frontend `preferencesRequest()` → 404 en E2E. | **D1-3:** esta TR implementa **`GET /api/v1/users/me/preferences`** (lectura `users.locale` + `users.theme`) y **`PATCH /api/v1/users/me/preferences/locale`**. `PATCH` general (`openInNewTab`) queda en TR-GEN-01-menu-avatar. |
| AMB-C04 | **`users.locale` seed `es-AR` vs catálogo `es`** | `SessionContextBuilder` devuelve valor crudo; `userPreferences` ya hace `split('-')[0]` en cliente | **D1-4:** normalizar **siempre a código catálogo** (`es`, `en`, …) en backend al persistir y al armar `sessionContext`; aceptar BCP47 en lectura (`es-AR` → `es`). |
| AMB-C05 | **Menú: `labelKey` vs `text` API** | TR menú-sidebar difirió AC-12: hoy se muestra `text` del API | **D1-5:** sidebar usa `t(labelKey)` con **fallback a `text`** si falta clave; no traducir datos ERP. |
| AMB-C06 | **Textos hardcoded en login/auth** | `LoginPage`, `ChangePasswordPage`, errores auth en español fijo | **D1-6:** migrar labels y mensajes a claves i18n; errores envelope vía `t(respuestaKey)` (tabla §4 claves auth). |
| AMB-C07 | **E2E grilla italiano sin UI** | Gherkin exige caption/filtro DevExtreme en italiano; no hay DataGrid en pantallas actuales | **D1-7:** componente **`LocaleDemoGrid`** (mínimo) en dashboard o ruta demo; solo para validar `dx.messages` + captions i18n. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | Ruta §6 `app/auth/LoginPage` | Corregir a `frontend/src/features/auth/LoginPage.tsx`. |
| AMB-M02 | Stub header `shell-language-slot` | Reemplazar por **`LocaleSelector`** (`localeSelectorHeader`); eliminar texto fijo «Idioma: es». |
| AMB-M03 | Visitante no autenticado | Persistir en **`localStorage`** `SistemaPartes.locale.guest`; no llamar API. |
| AMB-M04 | Post-login cambio idioma | `PATCH locale` → actualizar `users.locale`, respuesta envelope, **`sessionContext` local** y `updateStoredSessionContext`. |
| AMB-M05 | Fallo PATCH (AC-05) | Mantener locale cliente; toast/error no bloqueante (`localeSaveFailed`). |
| AMB-M06 | **403** en PATCH locale | **No aplica MVP** salvo cuenta inhabilitada (misma regla que auth); usuario autenticado operativo siempre puede cambiar su locale. |
| AMB-M07 | Seeds usuarios locale | Añadir en `paqsuite_mvp.php`: `locale.en.mvp`, `locale.it.mvp`, `locale.null.mvp`, `locale.invalid.mvp` (§4 seed). |
| AMB-M08 | Paridad archivos JSON | Test unitario: mismas claves en `es.json`, `en.json`, `it.json`; `pt`/`fr` completos pero smoke opcional en CI. |
| AMB-M09 | DevExtreme locale | Al cambiar idioma app: `locale(code)` + `loadMessages` del paquete DevExtreme para los 5 códigos. |
| AMB-M10 | `useUserPreferences` vs i18n | **`useCurrentLocale`** (i18n) es fuente de verdad UI; preferences API complementa theme (apariencia TR futura). |
| AMB-M11 | Login hereda locale guest | Tras login OK: **`users.locale` normalizado** prevalece sobre guest; si vacío/null → regla RN-02. |
| AMB-M12 | OpenAPI / matriz | Matriz ya tiene fila `PATCH locale`; implementar anotaciones en D1 (T6). |

### Contradicciones TR ↔ código ↔ HU

| Contradicción | Resolución |
|---------------|------------|
| TR §5.1 lista `GET preferences` y TR menu-avatar comparte endpoint | **GET único** en esta TR (locale+theme); menu-avatar extiende PATCH general sin duplicar GET. |
| HU pregunta abierta catálogo vs TR RN-05 | **Cerrado:** `es`, `en`, `pt`, `fr`, `it` (MONO + TR). |
| Seed MVP `es-AR` en todos los usuarios vs AC fallback | Normalización D1-4; tests usan códigos cortos en usuarios nuevos seed. |
| AC-02 «sin recarga completa» vs cambio DevExtreme | Cambio vía `i18n.changeLanguage` + `config({ locale })` DevExtreme en mismo tick React. |

### Supuestos detectados

- Claves i18n en **inglés técnico** (`auth.invalidCredentials`, `shell.menu.toggleSidebar`, …).
- Idiomas en selector: **autodenominación** traducida (`locale.name.es` = «Español», etc.).
- `Intl` / `date-fns` no requerido en D1; formato fechas = oleada posterior si aplica.
- Recuperación contraseña (mail `it`) → **fuera D1** ([TR-GEN-02-recuperacion-contrasena](TR-GEN-02-recuperacion-contrasena.md)).

### Preguntas para decisión humana

- ~~Alcance checklist §4 en D1~~ → **Cerrado (2026-05-30):** shell base + demo grid (R-C1-01).
- ~~Endpoint PATCH vs PATCH general~~ → **Cerrado:** sub-ruta `/locale` (R-C1-03).
- ~~Formato almacenamiento `users.locale`~~ → **Cerrado:** código catálogo 2 letras (R-C1-04).

### Veredicto C1

**Apto para D1** con alcance acotado al shell/login/auth/menú fijo + grilla demo. Sin replan de HU/SPEC; checklist §4 completo es **meta de producto**, no bloqueante único de este slice.

---

## 3.2) Resoluciones C1 — pre-D1 (2026-05-30)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Alcance D1 checklist | Ítems §4 **1–12, 17–19** para `es`/`en`/`it`; omitir 13–16 hasta TR/UI correspondiente. |
| R-C1-02 | Stack | `i18next` + `react-i18next`; archivos `frontend/src/locales/{locale}.json`. |
| R-C1-03 | API | `GET /api/v1/users/me/preferences` + `PATCH /api/v1/users/me/preferences/locale` en este slice. |
| R-C1-04 | Normalización locale | Persistir y exponer **código catálogo** (`es`…`it`); BCP47 solo en entrada/cliente. |
| R-C1-05 | Menú sidebar | `t(item.labelKey) \|\| item.text`. |
| R-C1-06 | Auth UI | Claves §4 (auth.*, tenant.invalid) en todos los locales MVP activos. |
| R-C1-07 | Grilla demo E2E | `LocaleDemoGrid` mínimo con caption i18n + filtro DevExtreme. |
| R-C1-08 | Guest | `localStorage` `SistemaPartes.locale.guest`; selector en login (`localeSelectorLogin`). |
| R-C1-09 | Seeds | Usuarios QA locale según §4 (nuevas filas en `paqsuite_mvp.php`). |
| R-C1-10 | DevExtreme | Sincronizar locale/messages al cambiar idioma (T7). |
| R-C1-11 | Errores PATCH | 422 locale inválido; 401 sin token; envelope MONO. |
| R-C1-12 | Coordinación avatar | No implementar `openInNewTab` ni menú desplegable avatar en este slice. |

---

## 3.3) Plan D1 — Implementación (2026-05-30)

### Alcance entendido

Infra i18n, selector login/header, persistencia `users.locale`, normalización, traducción del shell base y mensajes auth, integración DevExtreme, grilla demo para E2E italiano, tests unit/integration/E2E.

### Decisiones D1 (cerradas en C1)

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Alcance UI | R-C1-01 |
| D1-2 | i18next | R-C1-02 |
| D1-3 | Backend | R-C1-03 + `UserPreferencesController` |
| D1-4 | Normalización | R-C1-04 (`LocaleNormalizer` backend + frontend) |
| D1-5 | Menú | R-C1-05 |
| D1-6 | Login/auth | R-C1-06 |
| D1-7 | Demo grid | R-C1-07 |
| D1-8 | Guest storage | R-C1-08 |

### Tareas D1 ↔ plan §7

| Ticket | Entregable |
|--------|------------|
| T1 | Backend `GET preferences` + `PATCH locale`, validación catálogo, tests Feature |
| T2 | `supportedLocales.ts`, `useCurrentLocale`, resolución RN-02 |
| T3 | `LocaleSelector` en login y header |
| T4 | Persistencia guest + autenticado; sync sessionContext |
| T5 | Tests: paridad claves, API, E2E es/en/it + demo grid |
| T6 | OpenAPI + matriz §5.3 |
| T7 | DevExtreme `loadMessages` / `locale()` |
| T8 | JSON `es`/`en`/`it` completos para alcance D1-1 |

### Archivos previstos

| Capa | Archivos |
|------|----------|
| Backend | `app/Support/LocaleNormalizer.php`, `app/Http/Controllers/UserPreferencesController.php`, `config/paqsuite_locales.php`, `routes/api.php`, tests Feature |
| Frontend | `src/locales/*.json`, `src/features/i18n/**`, ajustes `LoginPage`, `ShellHeader`, `ChangePasswordPage`, `MenuSidebarTree`, `LocaleDemoGrid`, `App.tsx` |
| Seed | `paqsuite_mvp.php` usuarios locale QA |

### Contrato API cerrado (D1)

**GET** `/api/v1/users/me/preferences` → `{ "locale": "es", "theme": "generic.light" }`

**PATCH** `/api/v1/users/me/preferences/locale`

```json
{ "locale": "it" }
```

**200:** `{ "error": 0, "respuesta": "preferences.localeUpdated", "resultado": { "locale": "it" } }`

**401/422:** envelope estándar; catálogo inválido → `preferences.invalidLocale`

---

## 3.4) Verificación D (2026-05-30)

| Verificación | Resultado |
|--------------|-----------|
| `GET /api/v1/users/me/preferences` | OK — `UserPreferencesTest` |
| `PATCH /api/v1/users/me/preferences/locale` (200/401/422) | OK — 6 casos Feature |
| `LocaleNormalizer` backend + seeds QA locale | OK |
| Infra `i18next` + JSON `es`/`en`/`pt`/`fr`/`it` | OK |
| `LocaleSelector` login (`localeSelectorLogin`) + header (`localeSelectorHeader`) | OK |
| Persistencia guest (`SistemaPartes.locale.guest`) + autenticado (PATCH) | OK — E2E |
| Menú sidebar `t(labelKey) \|\| text` | OK — `MenuSidebarTree` |
| DevExtreme `loadMessages` + `locale()` sync | OK — `syncDevExtremeLocale.ts` |
| `LocaleDemoGrid` en dashboard | OK — caption + FilterRow |
| Unit paridad claves `es`/`en`/`it` | OK — 3 tests |
| Unit resolución locale (BCP47, fallback) | OK — 5 tests |
| E2E italiano + persistencia header | OK — `locale.spec.ts` 3 casos |
| E2E suite completa | **22 passed** |
| Frontend unit | **24 passed** |
| OpenAPI L5-Swagger + UI `/api/documentation` | OK — `OpenApiDocumentationTest` 3 casos |

### Trazabilidad AC

| AC | Evidencia |
|----|-----------|
| AC-01 | `LocaleSelector` en `LoginPage` y `ShellHeader` |
| AC-02 | Cambio en vivo sin recarga; `i18n.changeLanguage` |
| AC-03 | PATCH locale + E2E persistencia tras reload |
| AC-04 | `resolveInitialLocale` + `LocaleNormalizer` + seeds `locale.null.mvp` |
| AC-05 | `saveErrorKey` en header; idioma actual se mantiene |
| AC-06 | `locale.spec.ts` cambio/persistencia + smoke actualizado |

### Criterio italiano (§4)

- [x] Paridad de claves respecto a `es.json`
- [x] E2E: login con selector en `it` → label «Accedi»
- [x] E2E: grilla demo caption «Nome»
- [x] DevExtreme locale `it` cargado al cambiar idioma
- [ ] Mail recuperación con locale `it` — fuera D1 (TR-GEN-02-recuperacion-contrasena)

### Confirmación de alcance

Shell base + login + auth + change-password + toolbar menú + footer + selector + grilla demo. Sin mail recuperación, pivot, parámetros globales ni menú avatar desplegable.

---

## 4) Impacto en Datos

### Tablas afectadas
- `users.locale` (lectura y escritura de preferencia de idioma).

### Catálogo de idiomas MVP

| Código | Uso en tests / producto |
|--------|------------------------|
| `es` | Idioma por defecto (SPEC §8.1) |
| `en` | Segundo idioma obligatorio en suite |
| `pt` | Catálogo cerrado MVP |
| `fr` | Catálogo cerrado MVP |
| `it` | Catálogo cerrado MVP — **incluir en seed y E2E de cambio de idioma** |

Un idioma solo se habilita en el selector cuando su archivo de recursos (`frontend/src/locales/{locale}.json` o convención del proyecto) está **completo** para los ítems del checklist de cobertura (sección siguiente).

### Seed mínimo para tests

**Usuarios (`users.locale`):**

| Caso | `locale` | Objetivo del test |
|------|----------|-------------------|
| Sin preferencia | `null` | Fallback `navigator.language` → `es` |
| Español | `es` | Idioma por defecto |
| Inglés | `en` | Persistencia y cambio en header |
| Italiano | `it` | Validar catálogo completo y E2E con textos en italiano |
| Portugués | `pt` | Smoke de catálogo (opcional en CI si recursos listos) |
| Francés | `fr` | Smoke de catálogo (opcional en CI si recursos listos) |
| Inválido en BD | `xx` | Fallback a `es` sin error fatal |

**Recursos i18n (frontend):**

- Archivos base por idioma: mínimo `es.json`, `en.json`, `it.json` con **mismas claves** (paridad de claves validada en test unitario).
- Claves de prueba representativas para italiano: login, un ítem de menú, caption de grilla, mensaje de carga, tooltip de filtro (ver checklist).
- **Claves de autenticación** (coord. [TR-GEN-02-login-sesion](TR-GEN-02-login-sesion.md) D-01) — obligatorias en todos los locales MVP:

| Clave | Uso | Tono |
|-------|-----|------|
| `auth.invalidCredentials` | Login 401 — credenciales incorrectas | **Genérico** (no revelar si existe la cuenta) |
| `auth.noPermission` | Login 403 — sin `Pq_Permiso` válido | Explícito |
| `auth.noCommercialProfile` | Login 403 — sin `cod_login` comercial | Explícito (contacte administración) |
| `tenant.invalid` | Request sin `X-Paq-Cliente` válido (400) | Explícito |

**Convención envelope:** el backend devuelve la **clave** en `respuesta`; la UI traduce con i18n. Ver [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md) §4.

### Cobertura mínima de textos traducibles (checklist)

Lista de **ámbitos** que deben usar claves i18n (no texto fijo en código) en el MVP. Marcar por idioma al cerrar traducciones; usar como guía de QA y de revisión de PRs que toquen UI.

| # | Ámbito | Ejemplos / notas | ¿Obligatorio MVP? |
|---|--------|------------------|-------------------|
| 1 | **Labels y títulos de pantalla** | Títulos de páginas, subtítulos, textos de formulario estáticos | Sí |
| 2 | **Botones y acciones** | Guardar, Cancelar, Buscar, Aplicar filtros, Exportar | Sí |
| 3 | **Controles DevExtreme genéricos** | Textos de botones integrados, placeholders por defecto del tema | Sí |
| 4 | **Tooltips** | Iconos de ayuda, acciones de toolbar, botones solo icono | Sí |
| 5 | **Mensajes del sistema** | Toasts, confirmaciones, alertas de error/éxito, validaciones de formulario | Sí |
| 6 | **Estados de carga** | “Cargando…”, “Procesando…”, overlay al cargar datos o guardar | Sí |
| 7 | **Estado vacío / sin datos** | “No hay registros”, mensajes de grilla vacía | Sí |
| 8 | **Captions de columnas (DataGrid / TreeList)** | Cabeceras de grillas de consultas y ABM | Sí |
| 9 | **Opciones de filtro en grilla DevExtreme** | Textos del filtro de encabezado, operadores (contiene, empieza con, etc.) vía `dx.messages` / locale DevExtreme | Sí |
| 10 | **Paginación y selector de página** | “Página”, “de”, tamaños de página si se personalizan | Sí |
| 11 | **Menú lateral y menú avatar** | Textos de ítems de `pq_menus` si son claves; etiquetas fijas del shell (Perfil, Cerrar sesión, Apariencia) | Sí |
| 12 | **Pantalla de login y recuperación** | Labels de usuario/contraseña, enlaces; `auth.invalidCredentials` (401 genérico), `auth.noPermission` y `auth.noCommercialProfile` (403 explícitos) | Sí |
| 13 | **Mail “Olvidé mi contraseña”** | Asunto y cuerpo según locale activo al solicitar | Sí — implementación: [TR-GEN-02-recuperacion-contrasena](TR-GEN-02-recuperacion-contrasena.md) |
| 14 | **Wizard / UI de pivot** | Pasos, botones y mensajes del asistente pivot (referencia documental SPEC-001-08; si hay pivot en pantalla, mismas reglas) | Sí si hay pivot visible |
| 15 | **Consulta de parámetros (SistemaPartes)** | `parametros.SistemaPartes.{Clave}.caption|tooltip`; archivos `locales/parametros/SistemaPartes.*.json`; remapeo al cambiar idioma | Sí — CC PQ #7 |
| 16 | **Footer y versión** | Leyendas institucionales configurables vía i18n si no vienen de backend | Sí |
| 17 | **Selector de idioma** | Nombres de idiomas en autodenominación (Español, English, Italiano, …) | Sí |
| 18 | **Fechas y números** | Formato vía `Intl` / locale activo (no traducción literal, pero coherencia regional) | Sí |
| 19 | **Mensajes HTTP / envelope** | Claves i18n en `respuesta` del envelope; UI traduce (`t(respuesta)`). Ver contexto MONO `envelope-respuestas.md` §4 | Sí |
| 20 | **Datos de negocio** | Nombres de clientes, artículos, descripciones ERP | **No** (fuera de alcance HU) |
| 21 | **Panel de agrupación (DataGrid)** | Texto vacío «arrastre columna…» (`grid.dx.groupPanelEmpty` + `dxDataGrid-groupPanelEmptyText`) | Sí |
| 22 | **Selector de columnas (Column Chooser)** | Título y panel vacío (`grid.dx.columnChooserTitle`, `grid.dx.columnChooserEmpty`) | Sí |
| 23 | **Operadores de fila de filtro** | Menú de operaciones (contiene, empieza con, etc.) vía `FilterRow.operationDescriptions` + overrides `grid.dx.filter.*` | Sí |
| 24 | **Totalizadores de pie de grilla** | Menú contextual (clic derecho en celda de footer): opciones según tipo de dato; textos `grid.summary.*` | Sí |
| 25 | **Columna de acciones sin caption** | Cabecera i18n `grid.column.actions` (evita ítem vacío en Column Chooser) | Sí |
| 26 | **Menú contextual de encabezado de columna** | Ordenar, agrupar, mover columna: overrides `grid.dx.sort.*`, `grid.dx.group.*`, `grid.dx.column.move*` → `dxDataGrid-*`; requiere `loadMessages` sin doble anidación | Sí |
| 27 | **Pie de grilla — totalizadores por columna** | Clic derecho en celda de pie; `grid.summary.*`; fila visible con placeholder; separadores CSS | Sí |
| 28 | **Remount grilla al cambiar idioma** | `key={gridId-locale}` en `DataGridDx` + `syncDevExtremeLocale` en `LocaleProvider` | Sí |
| 29 | **Captions pivot por consulta** | `pivot.consulta.{consultaId}.{dataField}` + `resolveConsultaColumnCaption` | Sí si hay pivot |
| 30 | **Menú pivot — expandir/contraer filas** | `pivot.dx.expandAll` / `collapseAll` en encabezados de **fila** (paridad con columnas) | Sí — CC PQ #7 |

**QA manual ítems 21–28 (GEN-03, 2026-06-01):** validados en dashboard y `/demo/abm` (locale `es`). Evidencia: [F-GEN-03-cierre-formal](F-GEN-03-cierre-formal.md) § QA manual.

**QA CC PQ #7 (2026-06-18):** `/general/parametros` en `it`; pivot informes — clic derecho encabezado fila → Expandir/Contraer todo; pantalla carga layout §4.1 producto.

**Patrón técnico completo (obligatorio en proyectos MONO):** [`patron-i18n-grilla-devextreme.md`](../../00-contexto/_mono/03-ui-transversal/patron-i18n-grilla-devextreme.md).

**Criterio de cierre por idioma (ej. italiano):**

- [x] Paridad de claves respecto a `es.json`
- [x] E2E: login con selector en `it` → al menos un label de login en italiano
- [x] E2E o smoke: grilla con caption y mensaje de carga en italiano
- [x] DevExtreme: locale cargado (`it`) para textos de filtro/paginación del grid
- [ ] Mail de recuperación probado con locale `it` (mock o entorno test) — ver [TR-GEN-02-recuperacion-contrasena](TR-GEN-02-recuperacion-contrasena.md)

**Implementación DevExtreme (recordatorio técnico):**

- Cargar mensajes del paquete DevExtreme con `loadMessages(esMessages)` (el JSON ya es `{ es: { … } }`); **no** `loadMessages({ es: esMessages })`.
- Al cambiar `locale` de la app, sincronizar `locale()` de DevExtreme en el mismo ciclo (sin recarga completa de página).
- En `DataGridDx`: props explícitas (`GroupPanel`, `ColumnChooser`, `FilterRow.operationDescriptions`) + `getGridDevExtremeMessageOverrides()` (claves `grid.dx.*` → `dxDataGrid-*`).
- **No asumir** que el paquete DX traduce todo en runtime: ítems **21–28** validados en QA GEN-03 (2026-06-01); repetir al añadir idioma o superficie DX nueva.
- Detalle normativo, inventario de superficies y anti-patrones: **[`patron-i18n-grilla-devextreme.md`](../../00-contexto/_mono/03-ui-transversal/patron-i18n-grilla-devextreme.md)**.

**Corrección post-GEN-03 (2026-06-01):** ver [TR-GEN-03-grillas-listados](TR-GEN-03-grillas-listados.md) §10 y el patrón MONO anterior.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1. Código, matriz y OpenAPI deben coincidir.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/users/me/preferences` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| PATCH | `/api/v1/users/me/preferences/locale` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### PATCH `/api/v1/users/me/preferences/locale`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "locale": "es"
}
```

**Response 200:** envelope con locale persistido.

**Response 401:** no autenticado.

**Response 403:** sin permiso de auto-gestión (si se define policy explícita).

**Response 422:** locale fuera del catálogo permitido.

**OpenAPI (L5-Swagger):**

- [x] Anotaciones en controller/DTO de preferencias.
- [x] `security` declarado.
- [x] Header `X-Paq-Cliente` documentado.
- [x] Respuestas 401/403/422 documentadas.
- [x] Permisos/restricciones de locale en descripción.
- [x] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [x] Fila `PATCH /api/v1/users/me/preferences/locale` en matriz (TR-GEN-01-idioma).
- [x] `GET /api/v1/users/me/preferences` compartido con TR-GEN-01-menu-avatar (implementación en este slice).
- [x] OpenAPI verificado en `/api/documentation`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/i18n/model/supportedLocales.ts` (nuevo): catálogo cerrado.
- `frontend/src/features/i18n/api/updateLocalePreference.ts` (nuevo): persistencia server-side.
- `frontend/src/features/i18n/hooks/useCurrentLocale.ts` (nuevo): resolución de idioma inicial.
- `frontend/src/features/i18n/components/LocaleSelector.tsx` (nuevo): selector reutilizable.
- `LocaleSelector` debe implementarse con **DevExtreme `SelectBox`** tanto en login como en header; no usar `<select>` nativo en superficies de usuario finales.
- Contrato reusable MONO: wrapper con `data-testid` estable (`localeSelectorLogin`, `localeSelectorHeader`) + items renderizados con `localeOption-{code}` para no acoplar E2E al DOM interno de DevExtreme.
- Estilo visual alineado con `PaqSuite-IA-TANGO`: mismo patrón de selector DX en login público y shell autenticado, incluyendo **bandera representativa por idioma** en el valor seleccionado y en el desplegable.
- `frontend/src/app/layout/ShellHeader.tsx`: ubicar selector post-login.
- `frontend/src/features/auth/LoginPage.tsx`: selector visible pre-login.
- `frontend/src/features/i18n/components/LocaleDemoGrid.tsx` (nuevo): grilla demo E2E italiano.

### data-testid sugeridos
- `localeSelectorLogin`
- `localeSelectorHeader`
- `localeOption-{localeCode}`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Crear endpoint `PATCH /api/v1/users/me/preferences/locale` con validación de catálogo | OpenAPI + 401/403/422 |
| T2 | Frontend | Implementar resolución inicial de idioma y fallback (`users.locale`, `navigator.language`, `es`) | Reglas aplicadas en arranque |
| T3 | Frontend | Implementar selector reutilizable en login y header | Cambio de idioma en vivo |
| T4 | Frontend | Persistir locale para usuario autenticado y fallback cliente en no autenticado | Preferencia consistente |
| T5 | Tests | Unit i18n (paridad claves `es`/`en`/`it`) + Integration API + E2E cambio/persistencia + smoke italiano | Suite en verde |
| T6 | Docs | Actualizar matriz endpoint ↔ permiso para locale | Trazabilidad completa |
| T7 | Frontend | Integrar `dx.messages` / locale DevExtreme para `es`, `en`, `pt`, `fr`, `it` | Filtros y paginación de grillas traducidos |
| T8 | i18n | Completar checklist de cobertura por idioma (prioridad `it` para validación E2E) | Checklist §4 marcado |

---

## 8) Estrategia de Tests

- **Unit:** algoritmo de resolución de idioma y catálogo soportado.
- **Integration:** `PATCH /api/v1/users/me/preferences/locale` con 200/401/403/422.
- **E2E:**  
  - cambiar idioma en header y verificar textos;  
  - iniciar sesión nuevamente y validar persistencia;  
  - **italiano (`it`):** selector en login → textos clave (login + caption grilla o menú) en italiano;  
  - validar que filtro de grilla DevExtreme muestra operadores en idioma activo.

---

## 9) Riesgos y Edge Cases

- Claves i18n faltantes pueden dejar textos sin traducir.
- Catálogo de idiomas no consensuado puede generar cambios de contrato tardíos.
- Diferencias entre `navigator.language` (ej. `es-AR`) y código interno (`es`) requieren normalización.
- Guardado fallido de locale puede dejar estado divergente entre cliente y backend.

---

## 10) Checklist final

### Checklist del slice
- [x] AC cumplidos
- [x] Backend + frontend + tests según plan
- [x] Resolución de fallback sin ambigüedad validada
- [x] Selector en login y header operativo
- [x] Catálogo `it` con paridad de claves y smoke/E2E en italiano
- [ ] Checklist de cobertura §4 revisado en PRs de UI (muestreo por ámbito)

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [x] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR











