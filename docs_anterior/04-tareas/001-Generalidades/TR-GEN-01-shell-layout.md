# TR-GEN-01-shell-layout — Shell principal post-login

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-01-shell-layout](../../03-historias-usuario/001-Generalidades/HU-GEN-01-shell-layout.md) |
| **SPEC relacionada** | [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-login-sesion (post-login); base para TR-GEN-01-menu-general-sidebar, TR-GEN-01-menu-avatar, TR-GEN-01-idioma y TR-GEN-01-apariencia-temas |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-09 (Parte I — CC PQ #3) |

**Origen:** [HU-GEN-01-shell-layout](../../03-historias-usuario/001-Generalidades/HU-GEN-01-shell-layout.md)  
**Referencia SPEC:** [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)  
**Cierre F formal:** [F-GEN-01-02-cierre-formal](F-GEN-01-02-cierre-formal.md)

---

## 1) HU Refinada (resumen)

### Título
Shell principal post-login.

### Narrativa
Como usuario autenticado del portal SistemaPartes, quiero disponer de un shell estable con header, sidebar, área principal y footer para navegar entre procesos sin perder contexto global ni controles de sesión.

### In scope / Out of scope
- **In scope:** composición del shell post-login, cuatro zonas visibles, comportamiento responsive base, guard de sesión inválida.
- **Out of scope:** pixel-perfect final, lógica de negocio de cada proceso, login/logout en sí mismos, contenido del menú lateral y configuración detallada de idioma/tema.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Tras autenticación válida, se renderiza shell completo sin selector de empresa.
- **AC-02**: Existen cuatro zonas identificables en desktop: header, sidebar, área principal y footer.
- **AC-03**: Al cambiar de ruta interna, header/sidebar/footer permanecen montados.
- **AC-04**: En viewport reducido, el sidebar puede colapsarse sin bloquear navegación.
- **AC-05**: Footer muestra versión y referencia de usuario/sesión.
- **AC-06**: Si la sesión es inválida o expirada, se redirige a login y no se renderiza shell.
- **AC-07**: E2E valida login -> shell visible con `data-testid` clave.
- **AC-08**: Header expone slot/`data-testid` de los tres controles de menú (`menuToggleSidebar`, etc.).
- **AC-09**: Error al cargar preferencias: shell visible con fallback `es`, `generic.light`.

### Escenarios Gherkin

```gherkin
Feature: Shell principal post-login

  Scenario: Usuario autenticado accede al shell completo
    Given un usuario con sesión válida en modo MONO
    When completa el login exitosamente
    Then ve header, sidebar, área principal y footer
    And no ve selector de empresa

  Scenario: Navegación entre procesos mantiene el shell
    Given un usuario autenticado en el shell
    When navega a otro proceso desde el sidebar
    Then header, sidebar y footer permanecen visibles
    And el área principal muestra el proceso activo

  Scenario: Sesión inválida no muestra shell
    Given un token de sesión inválido o expirado
    When intenta acceder a una ruta protegida
    Then es redirigido al login
    And no se renderiza el shell

  Scenario: Shell usable en viewport reducido
    Given un usuario autenticado en viewport móvil
    When abre el menú lateral
    Then puede acceder a la navegación sin perder las cuatro zonas funcionales
```

---

## 3) Reglas de Negocio

1. **RN-01**: En modo MONO no existe selector de empresa en ninguna zona del shell.
2. **RN-02**: El shell se renderiza únicamente en rutas post-login (dependiente de TR-GEN-02-login-sesion).
3. **RN-03**: Header y footer son persistentes durante navegación SPA.
4. **RN-04**: Error de carga de preferencias de usuario no bloquea shell; usar fallback definido (`es`, `generic.light`).
5. **RN-05**: La identidad visual del shell no define estilos finales de pantallas de negocio.
6. **RN-06**: Cuando exista selector de apariencia/tema, el shell debe consumir la paleta derivada del tema activo (no solo `light/dark`) y preservar contraste legible en header, sidebar, footer y menú avatar.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-29)

**Fuentes revisadas:** HU-GEN-01-shell-layout, SPEC-001-01, TR-GEN-01-menu-general-sidebar (testids), shell-layout.md (contexto MONO), código existente (login, menu API).

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (tras cerrar resoluciones §3.2)

### Ambigüedades críticas

- Ninguna bloqueante tras cerrar ruta post-login y stubs de header documentados.

### Ambigüedades menores (resueltas en §3.2)

| ID | Tema | Riesgo | Resolución |
|----|------|--------|------------|
| AMB-01 | Ruta landing post-login (HU pregunta abierta) | Dos devs podrían usar `/` vs `/dashboard` | **D1-1:** `/dashboard`; `/` redirige según sesión |
| AMB-02 | `shellSidebarToggle` vs `menuToggleSidebar` | Tests divergentes | Un solo testid: **`menuToggleSidebar`**; alias documentado |
| AMB-03 | Convención `data-testid` shell | HU sugiere kebab; TR camelCase | **camelCase** para zonas shell (`shellHeader`…); auth mantiene kebab legacy |
| AMB-04 | Logout en header vs menú avatar | Duplicar controles | **`avatar-logout` stub** temporal hasta TR-GEN-01-menu-avatar |
| AMB-05 | `GET /users/me/preferences` inexistente | Bloqueo shell vs fallback | Hook intenta fetch; **404 → fallback** `es` / `generic.light` (AC-09) |
| AMB-06 | Toggles expandir/vista operativa | Implementar lógica en shell vs sidebar TR | **Stub disabled** en shell; lógica en TR-GEN-01-menu-general-sidebar |
| AMB-07 | Tests integración router (Vitest+RTL) | Scaffold sin Testing Library | **E2E Playwright** cubre guard + rutas anidadas (§3.3 T6) |
| AMB-08 | Puerto backend dev vs proxy Vite | Smoke falla si puertos no alineados | Documentar smoke en **`:8088`**; proxy Vite configurable (§3.4) |

### Contradicciones TR ↔ HU ↔ SPEC

- Ninguna funcional. HU deja avatar/idioma/menú en TRs hermanas; shell solo expone contenedores/slots — **coherente**.

### Supuestos detectados

- Dashboard MVP (`routePath` `/dashboard`) existe como placeholder hasta procesos reales.
- Versión footer desde `VITE_APP_VERSION` o `1.1.0` por defecto.

### Preguntas para decisión humana

- Ninguna pendiente para este slice.

### Veredicto C1

**Apto para D1/D** con observaciones menores cerradas en §3.2.

---

## 3.2) Resoluciones C1 → decisiones D1

| # | Tema | Decisión |
|---|------|----------|
| D1-1 | Ruta post-login | **`/dashboard`** como landing inmediata tras login exitoso. |
| D1-2 | Router | `react-router-dom` v6 con layout anidado (`RequireAuth` → `ShellLayout` → `<Outlet />`). |
| D1-3 | Preferencias | Intentar `GET /api/v1/users/me/preferences`; si falla (404/401), fallback **`es`** + **`generic.light`** desde sesión (`/auth/me`). |
| D1-4 | Controles menú header | Exponer `menuToggleSidebar`, `menuToggleExpandAll`, `menuToggleDisplayMode`; hamburguesa operativa (colapso sidebar); expandir/vista operativa **stub** hasta TR-GEN-01-menu-general-sidebar. |
| D1-5 | Responsive | Desktop: colapso de columna sidebar; mobile (`<768px`): overlay deslizable con backdrop. |
| D1-6 | Puerto dev frontend | **`3010`** (`strictPort`) para evitar colisión con otras apps PaqSuite en `:3000`. |
| D1-7 | testids shell | camelCase: `shellHeader`, `shellSidebar`, `shellMain`, `shellFooter`. |
| D1-8 | Avatar / logout | Botón `avatar-logout` temporal en header; menú avatar completo → TR-GEN-01-menu-avatar. |

---

## 3.3) Plan D1 — Implementación (2026-05-29)

### Alcance entendido

Shell post-login MONO: 4 zonas, router protegido, guard sesión, responsive base, slots para TRs hermanas. Sin endpoints nuevos.

### Impacto esperado

| Capa | Cambios |
|------|---------|
| DB | Ninguno |
| Backend | Ninguno (consume `/auth/me`, `/user/menu`; `/users/me/preferences` opcional 404) |
| Frontend | Layout, router, AuthProvider, preferencias, CSS responsive |
| Tests | Unit sidebar/preferencias; E2E login/shell/guard/navegación/móvil |
| Docs | TR §3.1–3.4; HU estado |

### Orden de trabajo

| Paso | Tarea | Archivos |
|------|-------|----------|
| T1 | Dependencia router | `package.json` |
| T2 | Auth context + guard | `AuthProvider.tsx`, `RequireAuth.tsx`, `AppRoutes.tsx`, `protectedRoutes.tsx` |
| T3 | Layout 4 zonas | `ShellLayout.tsx`, `ShellHeader.tsx`, `ShellSidebar.tsx`, `ShellFooter.tsx`, `shellLayout.css` |
| T4 | Preferencias fallback | `useUserPreferences.ts`, `userPreferences.ts`, `preferencesApi.ts` |
| T5 | Páginas placeholder | `DashboardPage.tsx`, `ProcessPlaceholderPage.tsx` |
| T6 | Refactor App | `App.tsx`; eliminar `AuthApp.tsx`, `ShellPage.tsx` |
| T7 | Tests unit | `sidebarState.test.ts`, `userPreferences.test.ts` |
| T8 | Tests E2E | `smoke.spec.ts` (login, shell, nav, sesión inválida, **móvil**) |
| T9 | Smoke API manual | curl login → me → menu (SQL Server) |
| T10 | Cierre TR + HU | Este documento |

### Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Parpadeo shell en bootstrap | Estado `auth-bootstrapping` antes de renderizar shell |
| Puerto 3000 ocupado | Frontend `3010` strictPort |
| Preferencias 404 | Fallback sin bloquear render |
| Proxy Vite ≠ backend smoke | Documentar puerto en §3.4 |

### Tests a ejecutar

- `npm run build`
- `npm run test` (unit)
- `npm run test:e2e` (5 casos)
- Smoke curl API (supervisor, vendedor acotado, cliente)

### Confirmación de alcance

Sin ampliación fuera de HU/TR/SPEC. TreeView DevExtreme, toggles completos y avatar → TRs posteriores.

---

## 3.4) Verificación D — Ejecución (2026-05-29)

### Smoke API manual (backend `:8088`, tenant `desarrollo`)

| Paso | Resultado |
|------|-----------|
| `POST /api/v1/auth/login` supervisor.mvp | 200, token OK |
| `POST /api/v1/auth/login` vendedor.acotado.mvp | 200 |
| `GET /api/v1/auth/me` | 200, envelope coherente |
| `GET /api/v1/user/menu` vendedor acotado | 200, **4** ítems raíz |
| `GET /api/v1/users/me/preferences` | **404** (esperado); frontend usa fallback |

### Tests automatizados

| Suite | Resultado |
|-------|-----------|
| `npm run build` | OK |
| `npm run test` | 5 passed |
| `npm run test:e2e` | 5 passed (login, shell 4 zonas, navegación, sesión inválida, viewport móvil) |

### AC trazabilidad

| AC | Evidencia |
|----|-----------|
| AC-01 | E2E login → shell sin selector empresa |
| AC-02 | E2E `shellHeader/Sidebar/Main/Footer` |
| AC-03 | E2E navegación `/dashboard` → `/pedidos/ingresados` |
| AC-04 | E2E móvil 375px + toggle `menuToggleSidebar` |
| AC-05 | E2E footer sesión + versión en código |
| AC-06 | E2E token inválido → `/login` |
| AC-07 | E2E testids clave |
| AC-08 | E2E tres controles menú visibles |
| AC-09 | E2E preferences 404 mock + unit fallback |

### Smoke UI manual (pendiente operador)

1. `php artisan serve --port=8088` (backend)
2. Ajustar proxy Vite a `:8088` si no usa `:8000`
3. `npm run dev` → `http://localhost:3010/login`
4. Login `vendedor.acotado.mvp` / seed password → ver 4 zonas y menú API

---

## 4) Impacto en Datos

### Tablas afectadas
- No se crean tablas nuevas en este slice.
- Lectura indirecta de preferencias de `users.locale` y `users.theme` (definidas en slices de idioma/apariencia).

### Seed mínimo para tests
- No aplica seed específico de datos para este slice.
- Reutilizar seed de autenticación del slice `TR-GEN-02-login-sesion`.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1. Código, matriz y OpenAPI deben coincidir.

Este slice es **principalmente frontend**. No introduce endpoints nuevos; consume endpoints mínimos definidos por slices de autenticación/preferencias para habilitar el shell.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/auth/me` | Bearer + `X-Paq-Cliente` | Sesión válida | No |
| GET | `/api/v1/users/me/preferences` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### GET `/api/v1/auth/me`

**Autorización:** sesión válida (`TR-GEN-02-login-sesion`).

**Request:** sin body.

**Response 200:** envelope con datos mínimos de usuario autenticado.

**Response 401:** no autenticado/token inválido.

**Response 403:** no aplica en operación de auto-consulta de sesión.

**OpenAPI (L5-Swagger):**

- [ ] Anotaciones en controller/DTO (referencia slice de login).
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuesta 401 documentada.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] No aplica fila nueva en este slice (sin endpoints nuevos).

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/app/layout/ShellLayout.tsx`: estructura de 4 zonas + responsive.
- `frontend/src/app/layout/ShellHeader.tsx`: marca, slots idioma/avatar, controles menú (stub expandir/vista).
- `frontend/src/app/layout/ShellSidebar.tsx`: contenedor de `SidebarMenu`.
- `frontend/src/app/layout/ShellFooter.tsx`: versión e identidad de sesión.
- `ShellFooter` debe permanecer visible en el shell y mostrar, como mínimo, **marca**, **usuario/sesión** y **versión** del proyecto con jerarquía visual suficiente; referencia de estilo: `PaqSuite-IA-TANGO`.
- La presencia del footer no sustituye la regla transversal de DevExtreme para controles interactivos, pero sí forma parte del contrato reusable de shell MONO.
- `frontend/src/app/router/protectedRoutes.tsx` + `AppRoutes.tsx` + `RequireAuth.tsx`.
- `frontend/src/features/auth/AuthProvider.tsx`: contexto de sesión + bootstrap `/auth/me`.
- `frontend/src/features/preferences/useUserPreferences.ts`: fallback preferencias.
- `frontend/src/app/App.tsx`: `BrowserRouter` + rutas login/shell.

### data-testid
- `shellHeader`, `shellSidebar`, `shellMain`, `shellFooter`
- `menuToggleSidebar` (= control colapso sidebar; alias conceptual `shellSidebarToggle`)
- `menuToggleExpandAll`, `menuToggleDisplayMode` (stub disabled)
- `shell-language-slot`, `shell-footer-session`

**Contexto:** `docs/00-contexto/_mono/01-experiencia-base/shell-layout.md` y `menu-general.md`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Crear `ShellLayout` con 4 zonas semánticas y responsive básico | **Cumplido** |
| T2 | Frontend | Integrar guard de sesión y redirección a login en rutas protegidas | **Cumplido** |
| T3 | Frontend | Exponer `data-testid` y estructura para menú/avatar/idioma | **Cumplido** (avatar/idioma stub) |
| T4 | Tests | Agregar E2E `login -> shell` y `sesión expirada -> login` | **Cumplido** (`smoke.spec.ts`, **5** casos incl. móvil) |
| T5 | Docs | Referenciar dependencia hacia slices de menú/avatar/idioma/tema | **Cumplido** (§3.1–3.4) |
| T6 | Smoke | Smoke API manual SQL Server | **Cumplido** (§3.4) |

---

## 8) Estrategia de Tests

- **Unit:** tests de utilidades de layout (clases y estado de colapso sidebar).
- **Integration:** rutas protegidas + guard de sesión + render persistente de shell.
- **E2E:**  
  - login exitoso muestra las 4 zonas;  
  - acceso con sesión inválida redirige a login.

---

## 9) Riesgos y Edge Cases

- Inconsistencia entre estado de sesión frontend y backend puede generar parpadeo de shell.
- Cambios futuros en rutas de negocio pueden desmontar shell si no usan layout de rutas protegidas.
- Falla en preferencias de usuario al cargar puede afectar header si no se usa fallback.
- Implementación responsive incompleta puede ocultar sidebar sin alternativa de acceso.

---

## 10) Checklist final

### Checklist del slice
- [x] AC cumplidos
- [x] Backend + frontend + tests según plan (slice frontend-only; reutiliza auth existente)
- [x] Dependencia con `TR-GEN-02-login-sesion` validada
- [x] Base habilitada para slices dependientes de menú/avatar/idioma/tema

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Historial CC PQ #3 (09/06/2026) — Parte I 09/06/2026

Patrón transversal listas DevExtreme: indicador cargando + bloqueo + auto-selección con un solo match.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | Wrapper `SelectBoxDx` + hook `useDxSelectBoxLoadState` + `tryAutoSelectSingleMatch` | `frontend/src/shared/ui/controls/` |
| T2 | i18n `selectBox.loading` (5 locales) + JSDoc contrato pantallas 101 | `es/en/pt/fr/it.json`, `SelectBoxDx.tsx` |
| T3 | Integración smoke en carga (cliente + artículo) | `PedidosCargaPage.tsx` |
| T4 | Vitest loading + auto-match | `*.test.ts` en controls |











