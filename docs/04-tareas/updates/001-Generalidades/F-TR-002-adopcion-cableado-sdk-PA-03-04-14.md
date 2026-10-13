# F — Verificación TR-002 PA-03 / PA-04 / PA-14

Fecha: 2026-10-12

| Campo | Valor |
|-------|--------|
| **Resultado F** | **Aprobado** |
| **F1** | [F1 PA-03/04/14](./F1-TR-002-adopcion-cableado-sdk-PA-03-04-14.md) |
| **Pin** | `@paqsuite/react-core` **2.4.25-beta.1** (Cloudsmith). `laravel-core` **1.3.13-beta.1**. |
| **SoT Framework** | Wrappers + `register*` + FormError en Popup cubiertos en `2.4.25-beta.1`. Bulk permisos y SP GEN-06 laravel-core **fuera de esta TR**. |

## Matriz CA

| CA | Veredicto | Evidencia |
|----|-----------|-----------|
| AC-ADM-01 / PA-03 | **OK** | `UsuariosAdminPage.tsx`: `accessToken`, `platform`, `i18nNamespace="common"`. Sin `ProcessDataGrid` propio. |
| AC-ADM-02 / PA-04 | **OK** | `PermisosAdminPage.tsx`: `tenancyMode="single"`, `defaultEmpresaId`. Sin batch host. |
| AC-ADM-05 / PA-14 | **OK** | `registerUsuariosAdminI18nResources` / `registerPermisosAdminI18nResources` en `i18n.ts` (`common`). JSON host sin `admin.usuarios.*` / `admin.permisos.*`. |
| AC-ADM-06 | **OK** | Playwright: `adminUsuariosFormError` en Popup SDK. |

## Parte I

Unificar PA-03/04/14 en originales SPEC/HU/TR-002 y **eliminar** los `-update`. Familia 002 **Finalizado**.
