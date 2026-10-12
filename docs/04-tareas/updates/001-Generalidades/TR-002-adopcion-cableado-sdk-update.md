# TR-002-update – Adopción de cableado SDK (deuda GEN)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-002-update](../../../03-historias-usuario/updates/001-Generalidades/HU-002-adopcion-cableado-sdk-update.md) |
| **SPEC relacionada** | [SPEC-002-update](../../../05-open-spec/updates/001-Generalidades/SPEC-002-adopcion-cableado-sdk-update.md) |
| **Épica** | 001 — Generalidades (transversal) |
| **Prioridad** | MUST-HAVE |
| **Clasificación** | HU COMPLEJA (varios GEN; orden por prioridad) |
| **Estado** | Finalizado |
| **Última actualización** | 2026-10-12 |
| **F T6** | [F T6](./F-TR-002-adopcion-cableado-sdk-T6.md) |
| **F PA-03/04/14** | [F](./F-TR-002-adopcion-cableado-sdk-PA-03-04-14.md) |

**Origen:** `00-ControlCalidad-PQ` · 11/10/2026 · CC #6 · PA-02…PA-14  
**SoT:** reglas BASE 19, 34, 36, 42, 43, 44 · ficha GEN-06 en regla 44  
**Inventario origen:** [deuda-cableado-sdk-por-gen.md](../../../06-operacion/deuda-cableado-sdk-por-gen.md)  
**PA-01:** [TR-012](../../100-SistemaPartes/TR-012-adopcion-gen-35-cloudsmith.md) (F+I 11/10/2026; no esta TR)

---

## 1) Resumen técnico

Sustituir forks y contratos host por exports del pin `@paqsuite/react-core` **2.4.25-beta.1** / `paqsuite/laravel-core` 1.3.13-beta.1. Confirmados `UsuariosAdminPage`, `PermisosAdminPage` y `register*AdminI18nResources`.

---

## 2) Criterios de aceptación (AC)

Alineados a HU CA-* / SPEC §5.

| AC | Verificación |
|----|----------------|
| AC-ENV-01 | `ApiEnvelope` eliminado o delega en `ApiResponse`; auth/status `error === 0`. Test PHP envelope. |
| AC-ADM-01 | `UsuariosAdminPage` host = wrapper SDK. |
| AC-ADM-02 | `PermisosAdminPage` host = export SDK. |
| AC-ADM-03 | Empresas: `i18nNamespace="common"`; borrar mapper/fallback GEN. |
| AC-ADM-04 | `registerSecurityRolesI18nResources`; quitar `securityRolesSdkI18n` de mapeo GEN. |
| AC-ADM-05 | `registerUsuariosAdminI18nResources` + `registerPermisosAdminI18nResources` en bootstrap. |
| AC-ADM-06 | Regresión SPEC-001: `*FormError` en Popup. |
| AC-I18N-01 | Sidebar `menuSidebarLabelsFromT`; `shellI18n` sin `menu.*` GEN. |
| AC-I18N-02 | Un `syncDevExtremeLocale` con locale real; quitar hardcode `'es'` en `main.tsx`. |
| AC-THM-01 | Helpers SDK; preview sin reload; retirar compat host. |
| AC-TEN-01 | `bootstrapClienteFromWindow` antes del Router. |
| AC-TEN-02 | `buildPlatformHeaders`. |
| AC-AUTH-01 | `ChangePasswordPage` + layouts SDK + i18n. |
| AC-LAY-01 | `ProcessPageLayout` + `resolveMenuCaption` / `t()` en `features/partes/**`. |
| AC-SMK-01 | Smoke ES + EN/FR: login + empresas o usuarios/permisos. |

---

## 3) Reglas de implementación

| ID | Implementación |
|----|----------------|
| RN-TR-SDK-01 | No copiar carpetas GEN del Framework al host. |
| RN-TR-SDK-02 | Namespace i18n único `common` (igual que `i18n.init`). |
| RN-TR-SDK-03 | Wrappers admin: props `accessToken`, `platform`, `i18nNamespace`, `tenancyMode` según ficha 44. |
| RN-TR-SDK-04 | PUT tema: `mapThemeForApiPersistence`; SelectBox: `normalizeThemeFromApi`. No enviar `paqsuite.*` a API. |
| RN-TR-SDK-05 | Conservar `data-testid` estables de SPEC-001 donde el SDK los exponga; documentar si el SDK usa otros ids. |
| RN-TR-SDK-06 | camelCase en código nuevo del host. |

### Inventario (host)

| Archivo / zona | Ítem |
|----------------|------|
| `backend/app/Support/ApiEnvelope.php`, `AuthController.php` | PA-02 |
| `frontend/src/features/admin/security/UsuariosAdminPage.tsx` | PA-03 |
| `…/PermisosAdminPage.tsx` | PA-04 |
| `…/EmpresasAdminPage.tsx`, `empresasAdminHostFallback.ts`, `securityEmpresasSdkI18n.ts`, `i18n.ts` | PA-05 |
| `…/RolesAdminPage.tsx`, `securityRolesSdkI18n.ts` | PA-06 |
| `i18n.ts` register usuarios/permisos | PA-14 |
| `features/auth/shellI18n.ts`, shell `MenuSidebar` labels | PA-07 |
| `main.tsx`, `i18n.ts` | PA-11 |
| `theme/sdkEmpresaThemeCompat.ts`, `devExtremeThemeSwitcher.ts` | PA-08 |
| `platformContext.ts`, `partesCanonicalCliente`, `main.tsx` | PA-09, PA-10 |
| `ChangePasswordPage.tsx` | PA-12 |
| `features/partes/**` páginas de proceso | PA-13 |

---

## 4) Plan de tareas

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Envelope canónico (PA-02) | AC-ENV-01 |
| T2 | Frontend | GEN-06 wrappers + i18n (PA-03, 04, 05, 06, 14) + regresión modal | AC-ADM-01…06 |
| T3 | Frontend | GEN-19 bootstrap, headers, tema (PA-09, 10, 08) | AC-TEN-01/02, AC-THM-01 |
| T4 | Frontend | GEN-07 labels (PA-07) | AC-I18N-01 |
| T5 | Frontend | PA-11 locale DX, PA-12 change password, PA-13 ProcessPageLayout | AC-I18N-02, AC-AUTH-01, AC-LAY-01 |
| T6 | Tests | PHP envelope; Vitest wrappers/i18n; ajustar E2E admin CC#5; smoke ES/EN/FR | AC-SMK-01 **cerrado F T6** |

**Orden:** T1 → T2 → T3 → T4 → T5 → T6. **No ejecutar en esta Parte G.**

---

## 5) i18n y testid

| Clave / id | Uso |
|------------|-----|
| `admin.empresas.*`, `appearance.paqsuite.*` | Solo SDK (`registerEmpresasAdminI18nResources`) |
| `admin.usuarios.*` / permisos / roles | Solo SDK (`register*I18nResources`) |
| `menu.*` chrome | SDK + `menuSidebarLabelsFromT` |
| Claves `partes.*` / `menu.partes.*` | Permanecen en host (`productI18n`) |
| `{prefix}FormError` | Conservar o mapear al id del SDK (documentar en D) |

---

## 6) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-11 | Parte G · CC-PQ #6 · TR-update PA-02…PA-14. |
| 2026-10-11 | Parte D T1 (PA-02): eliminado `app/Support/ApiEnvelope.php`. Auth legacy y `system/status` usan `ApiResponse`. Tests unit + feature `error === 0`. |
| 2026-10-11 | Parte F+I PA-02. T1 cerrado. PA-03: pin `2.4.24-beta.1` **no** exporta `UsuariosAdminPage` (solo `SecurityRolesPage` / `EmpresasAdminPage` en `features/security` y `empresas`). D diferido; no reimplementar. |
| 2026-10-11 | Parte D+E+F+I PA-05/06: JSON host sin `admin.empresas.*` / `admin.roles.*`; register SDK; `t` identity para roles (el export no traduce si falta `t`). |
| 2026-10-11 | Parte D+E+F+I PA-08/09/10: `bootstrapClienteFromWindow`; `buildPlatformHeaders`; `useEmpresaAppearanceBridge`. |
| 2026-10-11 | Parte D+E+F+I PA-07: `menuSidebarLabelsFromT` en host (pin sin export); JSON chrome `menu.*`; sin literales ES en `shellI18n`. |
| 2026-10-11 | Parte D+E+F+I PA-11/12/13: locale DX guest; `AuthCardLayout` en cambio de clave; `ProcessPageLayout` + `useProcessMenuTitle`. T5 cerrado. Cola T2 residual PA-03/04/14. |
| 2026-10-11 | Parte D+E+F T6: PHPUnit envelope; Vitest pin-gap + FormError; E2E empresas ES/EN/FR + CC#5. AC-SMK-01 cerrado. PA-03/04/14 siguen bloqueados por pin. |
| 2026-10-12 | Parte D+E+F+I PA-03/04/14: bump `2.4.25-beta.1`; wrappers SDK; i18n register; aliases API `name`/`userId`. Update Finalizado. |
