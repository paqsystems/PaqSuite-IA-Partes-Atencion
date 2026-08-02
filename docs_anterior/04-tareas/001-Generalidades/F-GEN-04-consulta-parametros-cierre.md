# Cierre F Formal — TR-GEN-04 Consulta de parámetros

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **TR** | [TR-GEN-04-consulta-parametros](TR-GEN-04-consulta-parametros.md) |
| **HU** | [HU-GEN-04-consulta-parametros](../../03-historias-usuario/001-Generalidades/HU-GEN-04-consulta-parametros.md) |
| **Fecha** | 2026-06-03 |

## Resultado global

- **Aprobado**

## Verificación F1

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=ParametrosConsulta` | 3 passed |
| `npx playwright test consultas-d1.spec.ts` (caso parámetros) | OK |
| QA manual usuario | OK |

Fix F1: claves menú en `fr.json` / `pt.json` para build TypeScript.

## Verificación F (docs vs código)

| Regla producto | Implementación | OK |
|----------------|----------------|-----|
| Solo lectura | Sin PUT/POST parámetros | ✓ |
| Menú General último ítem | `grp_general`, `pw_consultaparametros` | ✓ |
| Sin columna Clave en grilla | `ParametrosConsultaPage` | ✓ |
| Orden por descripción | `ParametrosConsultaService` ORDER BY CAPTION | ✓ |
| Booleanos Sí/No i18n | cellRender tipo `B` | ✓ |

## Estado

- TR y HU en **Pendiente**.






