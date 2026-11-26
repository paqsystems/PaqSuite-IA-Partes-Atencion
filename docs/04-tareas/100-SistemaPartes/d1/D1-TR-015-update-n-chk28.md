# Plan de implementación — TR-015-update-n (oleada 12, CHK-28)

> Parte **D1**. Jira **PIAPARTES-80** (HU) · **PIAPARTES-81** (TR) · épica **PIAPARTES-53**. Implementar solo tras «sí, parte D» en hilo SDD.

## Decisiones propuestas (confirmar en «sí, parte D»)

| Tema | Propuesta D1 |
|------|----------------|
| Alcance campo | Solo `observacion` en v1.3; heurística fija sin LLM para prompt canónico |
| Matching control | DetailBand · `XRLabel` con nombre `lblDetail_observacion` o binding `[observacion]` |
| Orden planner | `RemoveDetailFieldHeuristics` **antes** de `RemovalIntentHeuristics` |
| Noop | `noop:removeDetailField:ya_ausente` cuando applier retorna false |
| Fixture eval | Partir de layout lab con observación (addDetailField previo opcional en script) |
| CHK-29 regresión | PHPUnit: prompt agrupación → `noMappedActions` |
| Assets | Ninguno |

## Fuentes

- SPEC oleada 12, matriz CHK-28, `LayoutDetailFieldApplier`, L-B-02

## Orden de tareas D

1. **Framework:** `LayoutDetailFieldRemoveApplier` + dispatch + tests C#
2. **Framework:** actualizar matriz CHK-28
3. **Host:** whitelist + heurística + reorder planner
4. **Host:** i18n (5 locales) + noop suffix en turno SC si aplica patrón oleada 11
5. **Host:** script `eval-emission-ia-oleada12-chk28-apply.ps1`
6. PHPUnit / dotnet / eval en verde
7. Smoke manual + F1/F + guía CHK-28 en `guia-manual-prompts-checklist-chk-emision-ia.md`
8. Actualizar L-B-02 (v1.3 remove parcial)

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Observación solo en header y no en detail | Documentar precondición smoke; eval inserta columna antes |
| `RemovalIntentHeuristics` sigue capturando CHK-28 | Tests planner + reorder obligatorio |
| Eliminar binding compartido | v1 solo Detail; no tocar group/header |

## Capas impactadas

| Capa | Archivos / área |
|------|-----------------|
| Sidecar | `LayoutApplyService`, nuevo applier, tests |
| Backend PHP | Whitelist, heurística, planner, tests |
| FE | i18n mínimo (mensajes noop/applied) |
| Docs | F1, F, guía smoke |

**Sin** migraciones SQL ni menú nuevo.