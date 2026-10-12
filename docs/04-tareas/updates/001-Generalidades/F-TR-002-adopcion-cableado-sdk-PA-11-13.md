# F — Verificación TR-002 PA-11 / PA-12 / PA-13

Fecha: 2026-10-11

| Campo | Valor |
|-------|--------|
| **Resultado F** | **Aprobado** |
| **F1** | [F1](./F1-TR-002-adopcion-cableado-sdk-PA-11-13.md) |

## Matriz CA

| CA | Veredicto | Evidencia |
|----|-----------|-----------|
| AC-I18N-02 / PA-11 | **OK** | `main.tsx` no llama `syncDevExtremeLocale('es')`. `i18n.ts` usa `getGuestLocale()` → `syncDevExtremeLocale(initialLocale)`. |
| AC-AUTH-01 / PA-12 | **OK** | `ChangePasswordPage` monta `AuthCardLayout` + `authClassNames` + claves `change.*`. Login sigue en `AuthLoginLayout`. |
| AC-LAY-01 / PA-13 | **OK** | Procesos Partes (dashboard, masivo, informes, maestros, kardex mobile, diseñador) usan `ProcessPageLayout` y `useProcessMenuTitle`. Native: kardex/cards, no DataGrid. Admin usuarios/permisos no tocados (PA-03/04). |

## Parte I

PA-11/12/13 unificados en originales SPEC/HU/TR-002. Update: PA-03, PA-04, PA-14.
