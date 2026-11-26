# D-VERIFICACION — TR-015-update-g (CHK-08-format)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-g](./TR-015-update-g-formato-titulo-chk08.md) |
| HU | [HU-015-update-g](../../03-historias-usuario/100-SistemaPartes/HU-015-update-g-formato-titulo-chk08.md) |
| SPEC | [SPEC oleada 3b](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-3b-formato-titulo-chk08-update.md) |
| Jira | PIAPARTES-66 · PIAPARTES-67 · PIAPARTES-68 |
| Fecha F1 | 2026-11-24 |

---

## Verificación agente (F1 — CHK-08-format)

### Resultado

**Aprobado** — planner, apply, noop re-apply y smoke manual OK (2026-11-24).

### Smoke manual (operador)

| Prompt | Resultado |
|--------|-----------|
| `Poné el título en negrita, subrayado y centrado` | **OK** (usuario 2026-11-24) |

### Tests automatizados (E)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | **OK** — 50 tests |
| `dotnet test --filter LayoutControlAppearance` | **OK** |
| `.\eval-emission-ia-chk08-format-apply.ps1` | **PASS CHK-08-format** |

### Criterios (HU oleada 3b)

| Veredicto auto |
|----------------|
| `setControlAppearance` → `reportTitle` page header / legacy **OK** |
| Noop `ya_formateado` + i18n **OK** (script re-apply) |
| Smoke BYOK + Guardar/Descartar | **OK** (usuario) |

### Gaps

Ninguno bloqueante.