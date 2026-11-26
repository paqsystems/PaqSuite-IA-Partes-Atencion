# F — Verificación cierre TR-015-update-e (oleada 5)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-e](./TR-015-update-e-page-header-informes.md) |
| HU / SPEC | [HU-015-update-e](../../03-historias-usuario/100-SistemaPartes/HU-015-update-e-page-header-informes.md) · [SPEC oleada 5](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) |
| Fecha | 2026-11-20 |

## Resultado

**Aprobado** — Automatizado + **smoke manual oleada 5 cerrado** (confirmación usuario 2026-11-20): CHK-04…07 en diseñador, incl. **CHK-05/07** con re-aplicar (`ya_colocados`) y flujo coherente con Guardar/Descartar (D3 OK).

## Evidencia revisada

- F1 por hito T1–T4 (`D-VERIFICACION-TR-015-update-e-t*-chk*.md`).
- Matriz CHK-04…07 en `mvp` con `applyAssert`.
- Parte E (tests) 2026-11-20 abajo.

## Tests (Parte E — 2026-11-20)

| Comando | Resultado |
|---------|-----------|
| `phpunit --filter DesignLayout` | OK — 47 tests, 148 assertions |
| `phpunit --filter DesignLayoutChecklistPlannerTest` | OK — 29 tests (matriz oleada 5) |
| `phpunit --filter DesignLayoutActionPlannerTest` | OK — 18 tests |
| `dotnet test -c Release --filter PageHeader` | OK — 13 tests |
| `eval-emission-ia-oleada5-apply.ps1` | OK — `passed=4 failed=0` (CHK-04…07) |
| Sidecar `:5055` | Reiniciado Release; `health=ok` |

## Hallazgos críticos

- Ninguno en capa automatizada.

## Advertencias

- Título puede coexistir en `ReportHeaderBand` y en panel fila 2 (v1 SPEC).
- Smoke UI 4 prompts + Guardar/Descartar: ver [`smoke-oleada5-page-header-chk04-07.md`](../../06-operacion/smoke-oleada5-page-header-chk04-07.md).

## Smoke manual (D3)

| CHK | Resultado usuario 2026-11-20 |
|-----|------------------------------|
| 04…07 | OK (batería oleada 5) |
| 05, 07 | OK + idempotencia documentada |

## Recomendación final

- TR listo para **Finalizado** / PR acumulativo FW + Partes cuando lo indiques (D1–D3 cubiertos).