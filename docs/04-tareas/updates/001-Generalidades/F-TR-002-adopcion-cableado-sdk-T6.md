# F — Verificación TR-002 T6

Fecha: 2026-10-11

| Campo | Valor |
|-------|--------|
| **Resultado F** | **Aprobado con observaciones** |
| **F1** | [F1 T6](./F1-TR-002-adopcion-cableado-sdk-T6.md) |

## Matriz CA

| CA | Veredicto | Evidencia |
|----|-----------|-----------|
| AC-ENV-01 | **OK** | PHPUnit envelope `error === 0`; sin `ApiEnvelope` host. |
| AC-ADM-01 / 02 / 05 | **Bloqueado pin** | `@paqsuite/react-core` 2.4.24-beta.1 no exporta `UsuariosAdminPage` / `PermisosAdminPage` ni `registerUsuariosAdminI18nResources` / `registerPermisosAdminI18nResources`. Host no reimplementa GEN copiado. Vitest `gen06HostWiring` documenta el gap. |
| AC-ADM-03 / 04 | **OK** (previo) | Wrapper empresas `i18nNamespace="common"`; roles con register SDK. |
| AC-ADM-06 | **OK residual** | Forks host conservan `adminUsuariosFormError` / `adminPermisosFormError`. E2E CC #5 verde. |
| AC-SMK-01 | **OK** | Playwright empresas SDK: heading `Empresas` / `Companies` / `Sociétés`. |
| PA-14 | **Parcial** | `i18n.ts` llama los register con optional chaining; no-op hasta que el pin los exporte. |

## Parte I

En esta fecha T6: cola **PA-03 / PA-04 / PA-14** por pin `2.4.24-beta.1`. **Cierre posterior 2026-10-12:** [F PA-03/04/14](./F-TR-002-adopcion-cableado-sdk-PA-03-04-14.md) (pin `2.4.25-beta.1`). T1–T6 de esta corrida se mantienen; el pin-gap ya no aplica.
