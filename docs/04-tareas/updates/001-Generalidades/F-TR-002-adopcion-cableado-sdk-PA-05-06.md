# F — Verificación TR-002 PA-05 / PA-06

Fecha: 2026-10-11

| Campo | Valor |
|-------|--------|
| **Resultado F** | **Aprobado** |
| **F1** | [F1 PA-05/06](./F1-TR-002-adopcion-cableado-sdk-PA-05-06.md) |

## Matriz CA

| CA | Veredicto | Evidencia |
|----|-----------|-----------|
| AC-ADM-03 / PA-05 | **OK** | `EmpresasAdminPage` `i18nNamespace="common"`; `registerEmpresasAdminI18nResources(i18n, 'common')`; JSON host sin `admin.empresas.*` / `appearance.paqsuite.*`; sin mapper/fallback. |
| AC-ADM-04 / PA-06 | **OK** | `registerSecurityRolesI18nResources(i18n, 'common')`; `SecurityRolesPage` / `RolAtributosPage` reciben `t` de i18n (claves `security.roles.*`); JSON host sin `admin.roles.*`. El SDK **exige** `t` (si falta, muestra la key). |

## Observaciones

1. `admin.usuarios.*` / `admin.permisos.*` quedan en JSON: PA-03/04 siguen en fork.
2. PA-03/04/14 no cierran con este F.

## Parte I

PA-05 y PA-06 unificados en originales SPEC/HU/TR-002. Update 002 queda PA-03, 04, 07…14.
