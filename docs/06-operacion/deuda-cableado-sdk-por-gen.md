# Deuda de cableado SDK — Partes-Atención (por GEN)

**Origen:** relevamiento Framework 2026-10-11 (`PaqSuite-IA-FRAMEWORK` / `docs/06-operacion/relevamiento-cableado-hosts-tango-partes.md`).
**Pin de referencia:** `@paqsuite/react-core` **2.4.24-beta.1** · `paqsuite/laravel-core` **1.3.13-beta.1**.
**Criterio:** mala invocación de un export GEN listo, fork de una pantalla/motor que el SDK ya exporta, o contrato que el SDK ya cubre (headers, cliente, envelope, tema, i18n GEN).
**Fuera de alcance:** pantallas de dominio (carga diaria, maestros, informes). Gaps de producto no ofrecidos (GEN-13, GEN-17, GEN-23, GEN-32).

**Qué ya está bien (no pedir trabajo):** `registerGridI18nResources`; `MenuSidebar` con `t`/`locale`/`labels`/`openInNewTab`/`onItemsLoaded`; `UserAvatarMenu`; `DashboardContainer`; `EmpresaSelectorPage`; `ParametrosGeneralesPage`; `ProcessDataGrid` (salvo permisos); `ConsultaGrillaPivotShell`; `ExcelImportToolbar`; `SmartCapturePanel`; `ChatAssistantPage`; `LlmPreferencesPanel`; `ShellLayout` + CSS SDK; `EmpresasAdminPage` con `tenancyMode="single"` y preview sin reload.

---

## GEN-35 — Distribución SDK

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-01 | Lab / ops (no P0 de cableado GEN) | `composer.lock` resuelve `laravel-core` por **path** al checkout Framework. En `composer.json` el repo `path` está **primero** que Cloudsmith. El FE ya va por Cloudsmith; el BE quedó en modo labo. | `backend/composer.json`, `backend/composer.lock` | **No tratarlo como bug de invocación** si el host está en consumo “repo labo”. Antes de CI/Forge/prod: quitar el path (o no commitearlo) y regenerar lock **solo** Cloudsmith con `CLOUDSMITH_READ_TOKEN`. |

---

## GEN-20 — Envelope API

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-02 | **P0** | `ApiEnvelope::ok` serializa `'error' => null`. Contrato SDK: `'error' => 0` y `resultado` objeto, no `null`. Rompe clientes que usan `isEnvelope` / `error === 0`. | `backend/app/Support/ApiEnvelope.php`, `AuthController.php` | Eliminar `ApiEnvelope` o alinearlo a `PaqSuite\LaravelCore` `ApiResponse`. Auth (y status) deben usar el envelope canónico. |

---

## GEN-06 — Seguridad admin

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-03 | **P1** | El SDK exporta `UsuariosAdminPage`. El host reimplementa listado, popup, alta/edición e inhabilitación. | `frontend/src/features/admin/security/UsuariosAdminPage.tsx` | Wrapper fino del export SDK. Dominio solo: token, `platform`, permisos, menú. |
| PA-04 | **P1** | El SDK exporta `PermisosAdminPage`. El host monta tres `DataGrid` DX y lógica batch propia. | `frontend/src/features/admin/security/PermisosAdminPage.tsx` | Montar `PermisosAdminPage` del SDK. No DataGrid crudo de proceso. |
| PA-05 | **P1** | Wrapper de empresas **no pasa** `i18nNamespace="common"` (init usa `defaultNS: 'common'`). Pasa `t={translateSdk}` (`securityEmpresasSdkI18n`) **y** `registerEmpresasAdminI18nResources` **y** `empresasAdminHostFallback` con overwrite. Triple fuente de `admin.empresas.*` / `appearance.paqsuite.*`. | `EmpresasAdminPage.tsx`, `i18n/i18n.ts`, `empresasAdminHostFallback.ts`, `securityEmpresasSdkI18n.ts` | `registerEmpresasAdminI18nResources(i18n, 'common')` + `i18nNamespace="common"`. Quitar mapper y fallback host de claves GEN. |
| PA-06 | **P1** | `SecurityRolesPage` recibe `t` que mapea a claves host. **No** se llama `registerSecurityRolesI18nResources`. | `RolesAdminPage.tsx`, `securityRolesSdkI18n.ts` | Registrar recursos SDK y no mapear claves GEN. |
| PA-14 | **P2** | Existen `registerUsuariosAdminI18nResources` / `registerPermisosAdminI18nResources`; el host no los registra (coherente con el fork de pantallas). | `i18n.ts` | Cerrar junto con PA-03 / PA-04. |

**SoT cableado:** regla BASE `44-host-framework-gen-cableado-componentes` (ficha GEN-06). Errores de guardado en modal: regla 36.

---

## GEN-07 + GEN-02 — Menú / i18n GEN

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-07 | **P1** | `shellI18n.ts` duplica chrome `menu.*`. Regla 43: no fork de catálogo GEN. | `frontend/src/features/auth/shellI18n.ts` | Labels del sidebar vía `t('menu.*')` / helper SDK (`menuSidebarLabelsFromT`). Catálogo host solo claves **producto**. |
| PA-11 | **P2** | `main.tsx` hace `syncDevExtremeLocale('es')` y `i18n.ts` sincroniza `getGuestLocale()`. Arranque EN/FR puede pintar DX en ES un tick. | `main.tsx`, `i18n.ts` | Un solo `syncDevExtremeLocale` con el locale real (guest/sesión), no forzar `'es'` al boot. |

El `MenuSidebar` en sí está bien cableado (`t`, `locale`, `onItemsLoaded`). No rehacer el árbol.

---

## GEN-19 — Shell / tenancy / tema / `{cliente}`

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-08 | **P1** | Con pin ≥ 2.4.20 el SDK exporta `normalizeThemeFromApi`, `mapThemeForApiPersistence`, `paqsuiteDxStockBridge`, `useEmpresaAppearanceBridge`. El host mantiene `sdkEmpresaThemeCompat.ts` y switcher DX propio. | `frontend/src/theme/sdkEmpresaThemeCompat.ts`, `devExtremeThemeSwitcher.ts` | Un solo puente SDK. Preview/restore sin reload de página. |
| PA-09 | **P1** | `main.tsx` llama `bootstrapPlatformCliente()` **antes** del Router (orden OK) pero **no** `bootstrapClienteFromWindow`. Reimplementa query/cookie/session/Vercel/landing. | `platformContext.ts`, `partesCanonicalCliente` | Delegar a `bootstrapClienteFromWindow`. Conservar wrap de landing Vercel **solo** si el SoT de resolución host lo admite. |
| PA-10 | **P1** | `buildAuthPlatformHeaders()` arma `{ cliente, companyId, tenancy }` a mano. | `platformContext.ts` | Usar `buildPlatformHeaders`. MONO: `X-Paq-Cliente` ≠ id de empresa. |

SoT: regla 34 (`bootstrapClienteFromWindow` antes del Router); override Framework tenancy/headers.

---

## GEN-04 — Auth (cambio de clave)

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-12 | **P2** | `ChangePasswordPage` sin `AuthCardLayout` / `AuthPageLayout`, literales en español. | `ChangePasswordPage.tsx` | Layouts SDK + i18n (mismo patrón que login/forgot/reset, que ya están bien). |

---

## GEN-01 — Layouts de proceso

| ID | Prioridad | Qué está mal | Dónde | Qué pedir |
|----|-----------|--------------|-------|-----------|
| PA-13 | **P2** | Procesos de dominio no usan `ProcessPageLayout` (div/h2 propios). | Varias páginas `features/partes/**` | Adoptar `ProcessPageLayout`. Título desde menú localizado (`resolveMenuCaption`) o `t()` de producto — no literal ES. |

---

## Orden sugerido de resolución

1. **P0:** PA-02 (envelope).
2. **P1 GEN-06:** PA-03, PA-04, PA-05, PA-06 (y PA-14).
3. **P1 GEN-19:** PA-09, PA-10, PA-08.
4. **P1 GEN-07:** PA-07.
5. **P2:** PA-11, PA-12, PA-13.
6. **GEN-35 PA-01:** solo al pasar de labo a contrato Cloudsmith en CI/Forge (regenerar lock sin path).

## Checklist de aceptación (por ítem)

- [ ] Se invoca el **export** del SDK; no hay pantalla/motor GEN duplicado.
- [ ] i18n GEN: `register*I18nResources` + mismo namespace; sin JSON/mapper host de las mismas claves.
- [ ] Envelope `{ error: 0, respuesta, resultado }`.
- [ ] `bootstrapClienteFromWindow` antes del Router; headers con `buildPlatformHeaders`.
- [ ] Smoke: login + una pantalla GEN tocada (empresas o usuarios/permisos) en ES y EN/FR.
