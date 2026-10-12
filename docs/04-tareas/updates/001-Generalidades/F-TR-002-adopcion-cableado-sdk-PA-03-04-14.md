# F — Verificación TR-002 PA-03 / PA-04 / PA-14

Fecha: 2026-10-12

| Campo | Valor |
|-------|--------|
| **Resultado F** | **Aprobado** |
| **F1** | [F1 PA-03/04/14](./F1-TR-002-adopcion-cableado-sdk-PA-03-04-14.md) |
| **Pin** | `@paqsuite/react-core` **2.4.25-beta.1** (Cloudsmith). `laravel-core` sigue **1.3.13-beta.1**; el host alinea aliases `name`/`userId`. |

## Matriz CA

| CA | Veredicto | Evidencia |
|----|-----------|-----------|
| AC-ADM-01 / PA-03 | **OK** | `UsuariosAdminPage.tsx` wrapper de `UsuariosAdminPage` SDK; sin `ProcessDataGrid` propio. |
| AC-ADM-02 / PA-04 | **OK** | `PermisosAdminPage.tsx` wrapper SDK `tenancyMode="single"`; sin tres DataGrid ni batch host. |
| AC-ADM-05 / PA-14 | **OK** | `registerUsuariosAdminI18nResources` / `registerPermisosAdminI18nResources` en `i18n.ts` namespace `common`. JSON host sin `admin.usuarios.*` / `admin.permisos.*`. |
| AC-ADM-06 | **OK** | SDK popup: `adminUsuariosFormError`; E2E CC#5 verde. |

## Parte I

Unificar PA-03/04/14 en originales SPEC/HU/TR-002. Update **Finalizado**. CC #6 residual de GEN-06 cerrado en código.
