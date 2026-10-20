# Deuda de cableado SDK — Partes-Atención (por GEN)

**Origen:** relevamiento Framework 2026-10-11 (`PaqSuite-IA-FRAMEWORK` / `docs/06-operacion/relevamiento-cableado-hosts-tango-partes.md`).
**Pin de referencia:** `@paqsuite/react-core` **2.4.29-beta.1** · `paqsuite/laravel-core` **1.3.15-beta.1**.
**Criterio:** mala invocación de un export GEN listo, fork de una pantalla/motor que el SDK ya exporta, o contrato que el SDK ya cubre (headers, cliente, envelope, tema, i18n GEN).
**Fuera de alcance:** pantallas de dominio (carga diaria, maestros, informes). Gaps de producto no ofrecidos (GEN-13, GEN-17, GEN-23, GEN-32).

**Qué ya está bien (no pedir trabajo):** `registerGridI18nResources`; `MenuSidebar` con `t`/`locale`/`labels`/`openInNewTab`/`onItemsLoaded`; `UserAvatarMenu`; `DashboardContainer`; `EmpresaSelectorPage`; `ParametrosGeneralesPage`; `ProcessDataGrid` (salvo permisos); `ConsultaGrillaPivotShell`; `ExcelImportToolbar`; `SmartCapturePanel`; `ChatAssistantPage`; `LlmPreferencesPanel`; `ShellLayout` + CSS SDK; `EmpresasAdminPage` con `tenancyMode="single"` y preview sin reload.

---

## GEN-35 — Distribución SDK

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-01 | Lab / ops — **Cerrado F+I** 2026-10-11 | ~~`composer.lock` path al Framework~~ Cerrado en código: `composer.json` solo Cloudsmith; lock dist `dl.cloudsmith.io/basic/…`; Forge rechaza path/Satis. Lab path solo local. | `backend/composer.json`, `backend/composer.lock`, `backend/scripts/forge-composer-install.sh` | Cerrado. Residual: smoke Forge post-deploy de este lock. |

---

## GEN-20 — Envelope API

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-02 | **P0** — **Cerrado F+I** 11/10/2026 | ~~`ApiEnvelope` host~~ Eliminado. Auth/status = `ApiResponse`. | `AuthController.php`, `SystemStatusController.php` | Cerrado. |

---

## GEN-06 — Seguridad admin

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-03 | **P1** — **Cerrado F+I** 12/10/2026 | ~~Fork usuarios~~ Wrapper `UsuariosAdminPage` SDK (pin **2.4.25-beta.1**). | `UsuariosAdminPage.tsx` | Cerrado. |
| PA-04 | **P1** — **Cerrado F+I** 12/10/2026 | ~~Tres DataGrid permisos~~ Wrapper `PermisosAdminPage` SDK, `tenancyMode="single"`. | `PermisosAdminPage.tsx` | Cerrado. |
| PA-05 | **P1** — **Cerrado F+I** 11/10/2026 | ~~Triple fuente empresas i18n~~ Wrapper `i18nNamespace="common"`; JSON host sin `admin.empresas.*`. | `EmpresasAdminPage.tsx`, `locales/*/common.json` | Cerrado. |
| PA-06 | **P1** — **Cerrado F+I** 11/10/2026 | ~~Mapper roles / sin register~~ `registerSecurityRolesI18nResources`; `t` de i18n; JSON sin `admin.roles.*`. | `RolesAdminPage.tsx`, `RoleAtributosPage.tsx` | Cerrado. El SDK exige `t` (sin `t` muestra la key). |
| PA-14 | **P2** — **Cerrado F+I** 12/10/2026 | ~~Sin register usuarios/permisos~~ `registerUsuariosAdminI18nResources` / `registerPermisosAdminI18nResources` en `i18n.ts`. | `i18n.ts` | Cerrado. |

**SoT cableado:** regla BASE `44-host-framework-gen-cableado-componentes` (ficha GEN-06). Errores de guardado en modal: regla 36.

---

## GEN-07 + GEN-02 — Menú / i18n GEN

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-07 | **P1** — **Cerrado F+I** 11/10/2026 | Labels vía `menuSidebarLabelsFromT` + `t('menu.*')`. Residual: JSON chrome (pin sin `registerMenuI18nResources`). | `shellI18n.ts`, `ShellPage.tsx` | Cerrado con obs. |
| PA-11 | **P2** — **Cerrado F+I** 11/10/2026 | ~~`syncDevExtremeLocale('es')` en main~~ Un solo sync con `getGuestLocale()`. | `main.tsx`, `i18n.ts` | Cerrado. |

El `MenuSidebar` en sí está bien cableado (`t`, `locale`, `onItemsLoaded`). No rehacer el árbol.

---

## GEN-19 — Shell / tenancy / tema / `{cliente}`

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-08 | **P1** — **Cerrado F+I** 11/10/2026 | ABM empresas usa `useEmpresaAppearanceBridge` (sin reload). Residual: switcher DX para bootstrap Vite (`dx-theme` links). | `EmpresasAdminPage.tsx` | Cerrado con obs. |
| PA-09 | **P1** — **Cerrado F+I** 11/10/2026 | Landing SoT + `bootstrapClienteFromWindow` antes del Router. | `platformContext.ts`, `main.tsx` | Cerrado. |
| PA-10 | **P1** — **Cerrado F+I** 11/10/2026 | `buildPlatformHeaders`; MONO sin `companyId`. | `platformContext.ts`, `installApiAuthFetch.ts` | Cerrado. |

SoT: regla 34 (`bootstrapClienteFromWindow` antes del Router); override Framework tenancy/headers.

---

## GEN-04 — Auth (cambio de clave)

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-12 | **P2** — **Cerrado F+I** 11/10/2026 | `AuthCardLayout` + `authClassNames` + `change.*`. | `ChangePasswordPage.tsx` | Cerrado. Login sigue `AuthLoginLayout`. |

---

## GEN-01 — Layouts de proceso

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-13 | **P2** — **Cerrado F+I** 11/10/2026 | `ProcessPageLayout` + `useProcessMenuTitle`. Native kardex. | `features/partes/**` | Cerrado. |

---

## Orden sugerido de resolución

1. **P0:** PA-02 — F+I 11/10/2026.
2. **P1 GEN-06 PA-03/04/14:** F+I 12/10/2026 (pin 2.4.25-beta.1).
3. **P1 GEN-06 i18n:** PA-05 / PA-06 F+I 11/10/2026.
4. **P1 GEN-19:** PA-08/09/10 F+I 11/10/2026.
5. **P1 GEN-07:** PA-07 F+I 11/10/2026.
6. **P2:** PA-11, PA-12, PA-13 F+I 11/10/2026.
7. **GEN-35 PA-01:** cerrado F+I 2026-10-11.

## Checklist de aceptación (por ítem)

- [x] Se invoca el **export** del SDK; no hay pantalla/motor GEN duplicado.
- [x] i18n GEN: `register*I18nResources` + mismo namespace; sin JSON/mapper host de las mismas claves.
- [x] Envelope `{ error: 0, respuesta, resultado }` (PA-02).
- [x] `bootstrapClienteFromWindow` antes del Router; headers con `buildPlatformHeaders` (PA-09/10).
- [x] Smoke: login + una pantalla GEN tocada (empresas o usuarios/permisos) en ES y EN/FR.
