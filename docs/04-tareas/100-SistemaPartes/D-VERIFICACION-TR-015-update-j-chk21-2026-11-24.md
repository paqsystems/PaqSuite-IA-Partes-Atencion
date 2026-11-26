# D-VERIFICACION — TR-015-update-j (CHK-21 / LAB-21)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-j](./TR-015-update-j-segundo-grupo-chk21.md) |
| HU | [HU-015-update-j](../../03-historias-usuario/100-SistemaPartes/HU-015-update-j-segundo-grupo-chk21.md) |
| SPEC | [SPEC oleada 8](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-8-segundo-grupo-chk21-update.md) |
| Jira | PIAPARTES-75 · PIAPARTES-76 · PIAPARTES-77 |
| Fecha F1 | 2026-11-24 |
| Alcance | **Opción B** |

---

## Verificación agente (F1 — CHK-21)

### Resultado

**Aprobado** — CHK-21 + LAB-21 cerrados (smoke usuario OK).

### Smoke manual (usuario, 2026-11-24)

| Caso | Prompt / secuencia | Resultado |
|------|-------------------|-----------|
| CHK-21 un turno | `Agregá un segundo nivel de agrupación por fecha dentro de cada cliente` | **OK** — sin apply; guía dos turnos |
| LAB-21 turno 1 | `Agrupá por cliente` | **OK** |
| LAB-21 turno 2 | `Agrupá por fecha` | **OK** |

### Tests automatizados (E)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | **OK** — 50 tests |
| `dotnet test --filter LayoutGroupByField` | **OK** — 6 tests |
| `.\eval-emission-ia-checklist-lab-extras.ps1` | **PASS** LAB-21 |

### Criterios CA-B8 (opción B)

| ID | Veredicto |
|----|-----------|
| CA-B8-01 … CA-B8-05 | **OK** |

### Gaps

Ninguno bloqueante.