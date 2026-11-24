# D-VERIFICACION — TR-015-update-h (CHK-19)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-h](./TR-015-update-h-group-header-cliente-chk19.md) |
| HU | [HU-015-update-h](../../03-historias-usuario/100-SistemaPartes/HU-015-update-h-group-header-cliente-chk19.md) |
| SPEC | [SPEC oleada 7](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-7-group-header-chk19-update.md) |
| Jira | PIAPARTES-69 · PIAPARTES-70 · PIAPARTES-71 |
| Fecha F1 | 2026-11-24 |

---

## Verificación agente (F1 — CHK-19)

### Resultado

**Aprobado** — planner, apply sidecar, regresión CHK-18 y smoke manual OK (2026-11-24).

### Smoke manual (operador)

Guía: [`guia-manual-prompts-checklist-chk-emision-ia.md`](../../06-operacion/guia-manual-prompts-checklist-chk-emision-ia.md)

| Prompt | Resultado |
|--------|-----------|
| `Agrupá por cliente` | **OK** (usuario 2026-11-24) |

### Tests automatizados (E)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | **OK** — 50 tests |
| `dotnet test --filter LayoutGroupByField` | **OK** — 5 tests (suite total 9 con LayoutControlAppearance en corrida conjunta) |
| `.\eval-emission-ia-oleada7-chk19-apply.ps1` | **PASS CHK-19** |
| `.\eval-emission-ia-oleada6-apply.ps1` (CHK-18) | Referencia TR — regresión documentada 2026-11-21 |

### Criterios (HU oleada 7)

| ID | Veredicto auto |
|----|----------------|
| CA group header `Cliente:` + expresión | **OK** (apply script) |
| Reparación layout CHK-18 legacy | **OK** (tests .NET) |
| Noop `ya_agrupado` caption conforme | **OK** (script re-apply) |
| Smoke diseñador + banner L-B-10 | **OK** (usuario) |

### Gaps

Ninguno bloqueante.