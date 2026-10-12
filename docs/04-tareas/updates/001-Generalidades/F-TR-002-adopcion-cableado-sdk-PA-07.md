# F — Verificación TR-002 PA-07

Fecha: 2026-10-11

| Campo | Valor |
|-------|--------|
| **Resultado F** | **Aprobado con observaciones** |
| **F1** | [F1](./F1-TR-002-adopcion-cableado-sdk-PA-07.md) |

## Matriz CA

| CA | Veredicto | Evidencia |
|----|-----------|-----------|
| AC-I18N-01 / PA-07 | **OK** | `ShellPage` usa `menuSidebarLabelsFromT`. `shellI18n.ts` no tiene captions ES ni `defaultMenuSidebarLabels`. Chrome `menu.controls`… via `t('menu.*')`. `productI18n` sigue con claves de **producto** (`menu.partes_*`). |

## Observaciones

El pin `@paqsuite/react-core` **2.4.24-beta.1** no exporta `menuSidebarLabelsFromT` ni `registerMenuI18nResources`. El helper equivalente vive en el host. Las traducciones chrome GEN siguen en `locales/*/common.json` (el SDK no registra ese catálogo).

## Parte I

PA-07 unificado en originales SPEC/HU/TR-002. Update: PA-03, 04, 11…14.
