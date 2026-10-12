# F — Verificación TR-002 PA-08 / PA-09 / PA-10

Fecha: 2026-10-11

| Campo | Valor |
|-------|--------|
| **Resultado F** | **Aprobado con observaciones** |
| **F1** | [F1](./F1-TR-002-adopcion-cableado-sdk-PA-08-10.md) |

## Matriz CA

| CA | Veredicto | Evidencia |
|----|-----------|-----------|
| AC-TEN-01 / PA-09 | **OK** | `bootstrapPlatformCliente` persiste landing (SoT Vercel/canónico) y **siempre** llama `bootstrapClienteFromWindow` antes de `createRoot`. Override explícito no pasa por force-DEMO del SDK. |
| AC-TEN-02 / PA-10 | **OK** | `buildAuthPlatformHeaders` arma input; HTTP vía `buildPlatformHeaders`. MONO sin `companyId`. Fetch interceptor usa esos headers. |
| AC-THM-01 / PA-08 | **OK** (obs.) | ABM empresas: `useEmpresaAppearanceBridge` (preview/restore/guardar **sin** reload). Selector de empresa: `reloadOnGroupChange: false`. |

## Observaciones no bloqueantes

1. `devExtremeThemeSwitcher.ts` sigue para bootstrap inicial (`<link rel="dx-theme">` + Vite). No hay `sdkEmpresaThemeCompat.ts`.
2. Landing wrap (hostname/referrer) se conserva: el SDK no lee subdominio `{cliente}.{proyecto}`.

## Parte I

PA-08/09/10 unificados en originales SPEC/HU/TR-002. Update: PA-03, 04, 07, 11…14.
