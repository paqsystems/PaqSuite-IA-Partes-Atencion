# D-VERIFICACION — TR-015-update-i (CHK-20)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-i](./TR-015-update-i-group-sum-minutos-chk20.md) |
| HU | [HU-015-update-i](../../03-historias-usuario/100-SistemaPartes/HU-015-update-i-group-sum-minutos-chk20.md) |
| SPEC | [SPEC oleada 7b](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-7b-group-sum-chk20-update.md) |
| Jira | PIAPARTES-72 · PIAPARTES-73 · PIAPARTES-74 |
| Fecha F1 | 2026-11-24 |

---

## Verificación agente (F1 — CHK-20)

### Resultado

**Aprobado** — CHK-20 cerrado (batch grupo + suma, pie único, smoke usuario OK).

### Smoke manual (usuario, 2026-11-24)

| Prompt | Resultado |
|--------|-----------|
| `Agrupá por cliente y sumá los minutos` | **OK** |

### Tests automatizados (E)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | **OK** — 48+ tests |
| `dotnet test --filter LayoutGroup` | **OK** — 8 tests |
| `.\eval-emission-ia-chk20-apply.ps1` | **PASS** |
| Regresión oleada 6/7 | **PASS** |

### Criterios CA-B7b

| ID | Veredicto |
|----|-----------|
| CA-B7b-01 … CA-B7b-07 | **OK** (smoke + scripts) |

### Gaps

Ninguno bloqueante.