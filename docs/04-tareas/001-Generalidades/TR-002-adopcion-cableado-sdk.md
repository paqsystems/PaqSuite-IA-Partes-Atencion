# TR-002 — Adopción de cableado SDK (slice PA-02)

| Campo | Valor |
|-------|--------|
| **HU** | [HU-002](../../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) |
| **SPEC** | [SPEC-002](../../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) |
| **Estado** | Finalizado |
| **F** | [F PA-02](../updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-PA-02.md) · [F PA-11/13](../updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-PA-11-13.md) · [F T6](../updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-T6.md) · [F PA-03/04/14](../updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-PA-03-04-14.md) |
| **Update** | [TR-002-update](../updates/001-Generalidades/TR-002-adopcion-cableado-sdk-update.md) (**Finalizado**) |

## AC cerrado

| AC | Verificación |
|----|----------------|
| AC-ENV-01 | `ApiEnvelope` eliminado; auth/status `ApiResponse`; PHPUnit OK. |
| AC-ADM-03 | Empresas: namespace `common`; JSON host sin `admin.empresas.*`. |
| AC-ADM-04 | Roles: `registerSecurityRolesI18nResources`; JSON host sin `admin.roles.*`. |
| AC-THM-01 | `useEmpresaAppearanceBridge`; sin reload en preview/guardar empresas. |
| AC-TEN-01 | Landing + `bootstrapClienteFromWindow` antes de `createRoot`. |
| AC-TEN-02 | `buildPlatformHeaders`; MONO sin `companyId`. |
| AC-I18N-01 | `menuSidebarLabelsFromT`; sin captions ES en `shellI18n`. |
| AC-I18N-02 | `syncDevExtremeLocale(initialLocale)` desde `getGuestLocale`; `main.tsx` sin `'es'` hardcode. |
| AC-AUTH-01 | `ChangePasswordPage` + `AuthCardLayout`. |
| AC-LAY-01 | `ProcessPageLayout` + `useProcessMenuTitle` en procesos Partes. |
| AC-SMK-01 | Playwright empresas SDK ES/EN/FR ([F T6](../updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-T6.md)). |
| AC-ADM-01 | Wrapper `UsuariosAdminPage` SDK (2.4.25-beta.1). |
| AC-ADM-02 | Wrapper `PermisosAdminPage` SDK, MONO. |
| AC-ADM-05 | `registerUsuariosAdminI18nResources` / `registerPermisosAdminI18nResources`. |
| AC-ADM-06 | E2E CC#5: `adminUsuariosFormError` en Popup SDK. |

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-11 | Parte I · PA-02. |
| 2026-10-11 | Parte I · PA-05 / PA-06. |
| 2026-10-11 | Parte I · PA-08 / PA-09 / PA-10. |
| 2026-10-11 | Parte I · PA-07. |
| 2026-10-11 | Parte I · PA-11 / PA-12 / PA-13. |
| 2026-10-12 | Parte I · PA-03 / PA-04 / PA-14. |
