# F — Verificación cierre TR-015-update-f (oleada 6 / CHK-18)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-f](./TR-015-update-f-agrupacion-cliente-chk18.md) |
| HU / SPEC | [HU-015-update-f](../../03-historias-usuario/100-SistemaPartes/HU-015-update-f-agrupacion-cliente-chk18.md) · [SPEC oleada 6](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-6-agrupacion-chk18-update.md) |
| F1 | [D-VERIFICACION-TR-015-update-f-chk18-2026-11-21.md](./D-VERIFICACION-TR-015-update-f-chk18-2026-11-21.md) |
| Jira | PIAPARTES-63 · PIAPARTES-64 · PIAPARTES-65 (**Finalizado** 2026-11-21) |
| Commits | Partes `80e54ad` (`v1.3.0-CLOUDSMITH`) · Framework `6f8493e` (`v1.4.5`) |
| Fecha | 2026-11-21 |

## Resultado

**Aprobado** — Oleada 6 **solo CHK-18** cerrada: automatizado + smoke manual (apply, Guardar, re-aplicar con mensaje `alreadyGrouped`). Sin integración a `develop` (decisión producto 2026-11-21).

## Evidencia revisada

- [D1](./d1/D1-TR-015-update-f-chk18.md) y decisiones humanas registradas.
- Matriz: CHK-18 con `applyAssert.reportUrl = partes_consultaDetallada_principal`.
- Sidecar: `LayoutGroupByFieldApplier` (header + footer 28px, noop `ya_agrupado`, reparación pie).
- Partes: i18n + `emissionDesignSmartCaptureTurn` ante `noop:addGroupByField:ya_agrupado`.
- Captura: [`evidence/chk-18-smart-capture-2026-11-21.png`](./evidence/chk-18-smart-capture-2026-11-21.png).

## Tests (Parte E — 2026-11-21)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | OK — 48 tests |
| `php vendor/bin/phpunit --filter DesignLayoutActionPlannerTest` | OK — incl. `test_heuristics_chk18_agrupar_por_cliente` |
| `dotnet test --filter LayoutGroupByField` | OK — 3 tests |
| `eval-emission-ia-oleada6-apply.ps1` | OK — PASS CHK-18 (apply + re-apply) |
| Sidecar `:5055` | Reiniciado; `health=ok` |

## Hallazgos críticos

- Ninguno.

## Advertencias

- Expresión visible en group header sigue formato técnico `clienteNombre:` — mejora en **CHK-19** (mismo prompt canónico, distinto `applyAssert`).
- Lab layouts con grupo previo (`spike-layout-b-1`): para smoke limpio usar variante sin grupo o **Descartar** antes del ítem; eval automatizado usa `partes_consultaDetallada_principal`.

## Smoke manual (confirmación usuario 2026-11-21)

| CHK | Prompt | Resultado |
|-----|--------|-----------|
| CHK-18 | Agrupá por cliente | **OK** — turno 1 apply + Guardar; turno 2 noop con copy i18n completo |

## Fuera de alcance (confirmado)

- CHK-19, CHK-20, CHK-21, LAB-21 en esta oleada.

## Recomendación final

- TR/HU/SPEC/Jira en **Finalizado** en ramas de trabajo; **Desplegado** en Jira cuando exista PR a `develop`.
- **Siguiente hito sugerido (producto):** **CHK-08-format** (`setControlAppearance` / título) — distinta oleada checklist; no mezclar con CHK-19 salvo decisión explícita.