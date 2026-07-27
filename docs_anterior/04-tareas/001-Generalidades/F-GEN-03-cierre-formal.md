# Cierre F Formal — Bloque GEN-03 (SPEC-001-03 UI transversal)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


## Alcance del cierre

Cubre los cuatro slices planificados y verificados (D + F1 + QA manual + tests automatizados):

| TR | HU |
|----|-----|
| [TR-GEN-03-grillas-listados](TR-GEN-03-grillas-listados.md) | [HU-GEN-03-grillas-listados](../../03-historias-usuario/001-Generalidades/HU-GEN-03-grillas-listados.md) |
| [TR-GEN-03-layouts-grilla](TR-GEN-03-layouts-grilla.md) | [HU-GEN-03-layouts-grilla](../../03-historias-usuario/001-Generalidades/HU-GEN-03-layouts-grilla.md) |
| [TR-GEN-03-patron-abm](TR-GEN-03-patron-abm.md) | [HU-GEN-03-patron-abm](../../03-historias-usuario/001-Generalidades/HU-GEN-03-patron-abm.md) |
| [TR-GEN-03-exportaciones](TR-GEN-03-exportaciones.md) | [HU-GEN-03-exportaciones](../../03-historias-usuario/001-Generalidades/HU-GEN-03-exportaciones.md) |

**SPEC:** [SPEC-001-03-ui-transversal](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md)

## Resultado global

- **Aprobado con observaciones**

## Resumen por slice

| Slice | Resultado F | Observación principal |
|-------|-------------|------------------------|
| TR-GEN-03-grillas-listados | Aprobado con observaciones | `DataGridDx` en dashboard; i18n DX y pie por columna corregidos; falta unit `DataGridDx.test.tsx` del plan |
| TR-GEN-03-layouts-grilla | Aprobado | API + migración + toolbar + E2E; matriz actualizada |
| TR-GEN-03-patron-abm | Aprobado | Demo `/demo/abm` + E2E alta/edición/baja |
| TR-GEN-03-exportaciones | Aprobado | Excel básica/formateada; vacío deshabilitado; E2E |

## Verificación F1 (2026-06-01)

- Resultado F1: **Aprobado con observaciones**
- Fix aplicado en F1: error TypeScript `dataGridSummaryFooter.ts` (`command` en Column DX)

## QA manual (2026-06-01)

Validado por el usuario en dashboard y demos (`es`):

- [x] Filtros `FilterRow` en idioma activo
- [x] Panel de agrupación y Column Chooser traducidos
- [x] Menú contextual de encabezado (ordenar, agrupar, mover columna)
- [x] Totalizadores por columna (clic derecho en pie; dos columnas distintas)
- [x] Separación visual de columnas en fila de totales
- [x] Botón **+** ABM en `/demo/abm` (no en dashboard consulta — esperado)
- [x] Cambio de idioma sin F5 (remount grilla)

Trazabilidad checklist i18n ítems **21–28**: [TR-GEN-01-idioma](TR-GEN-01-idioma.md) §4.

## Evidencia de tests (ejecutados en F / F1)

| Comando | Resultado |
|---------|-----------|
| `npm run test` (frontend Vitest) | **60 passed** |
| `npm run build` (frontend) | **OK** |
| `php artisan test --filter=GridLayout` | **6 passed** |
| `npm run test:e2e` — grid-transversal, grid-layouts, grid-export, abm-transversal | **9 passed** |

**No re-ejecutado en esta pasada F:** suite E2E completa del frontend; suite backend integral.

## Documentación añadida en el bloque

- [patron-i18n-grilla-devextreme.md](../../00-contexto/_mono/03-ui-transversal/patron-i18n-grilla-devextreme.md)
- Checklist ampliado en [41-i18n-and-testid.md](../../.cursor/rules/base/40-i18n/41-i18n-and-testid.md) (subsección `DataGridDx`)

## Hallazgos críticos

- Ninguno abierto tras QA manual y tests.

## Advertencias

- ~~Unit render dedicado `DataGridDx.test.tsx` no creado~~ → añadido post-F (`DataGridDx.test.tsx`, smoke render + estados).
- Cambios sin commit en Git (pendiente autorización del equipo).
- Chunk JS DevExtreme > 500 kB (advertencia Vite preexistente).

## Recomendación final

- Bloque **GEN-03** listo para PR con informe F + F1.
- HU GEN-03 en estado **Pendiente** (cierre manual 2026-06-01).






