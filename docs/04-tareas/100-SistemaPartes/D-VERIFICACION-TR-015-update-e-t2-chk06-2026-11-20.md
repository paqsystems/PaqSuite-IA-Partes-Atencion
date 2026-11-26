# D-VERIFICACION — TR-015-update-e T2 (CHK-06)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-e](./TR-015-update-e-page-header-informes.md) § T2 |
| SPEC | [SPEC-015-update oleada 5](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) |
| D1 | [D1-TR-015-update-e-oleada5-t2-chk06](./d1/D1-TR-015-update-e-oleada5-t2-chk06.md) |
| T1 base | [D-VERIFICACION T1](./D-VERIFICACION-TR-015-update-e-t1-chk04-2026-11-20.md) |
| Fecha cierre F1 | 2026-11-20 |

---

## Verificación agente (F1 hito T2)

### Resultado

**Aprobado** — T2 CHK-06 cerrado para abrir **T3 CHK-07** (gate TR §4).

### Alcance

Solo **CHK-06** / `setPageHeaderEmpresa`. Sin T3/T4 ni CHK-05.

### Entregables

| Capa | Artefacto |
|------|-----------|
| FW | `LayoutPageHeaderEmpresaApplier.cs` (`ya_colocados`, búsqueda recursiva), tests, `LayoutApplyService` |
| Partes BE | `tryMatchEmpresa`, `DesignLayoutPageHeaderLab`, whitelist, planner sanitize |
| Partes FE | `pageHeader.empresaAlreadyPlaced` (×5), turno ante `setPageHeaderEmpresa:ya_colocados` |
| Matriz | CHK-06 `mvp` + `applyAssert` |
| Doc | Guía manual §CHK-06; checklist FW |

### Evidencia manual

- Usuario confirmó apply OK con nombre de empresa en diseñador.
- Re-aplicación: mensaje **ya colocado** (patrón CHK-04), sin excepción HTTP.

### Tests (cierre F1 — 2026-11-20)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | **OK** — 44 tests, 138 assertions |
| `dotnet test … -c Release --filter PageHeader` | **OK** — 8 tests |
| `.\eval-emission-ia-oleada5-apply.ps1` | **OK** — PASS CHK-04 + CHK-06 (`passed=2 failed=0`) |

### Criterios T2

| ID | Descripción | Veredicto |
|----|-------------|-----------|
| CA-B5-03 | Planner CHK-06 → `setPageHeaderEmpresa` | **OK** |
| AC-03 | Texto extraído o `Empresa demo` en lab | **OK** |
| AC-07 | Fila 1 izq. `lblPageHdr_Empresa` | **OK** (.NET + eval + manual) |

### Siguiente hito (SDD)

1. **D1 T3** — CHK-07 `setPageHeaderFecha`.
2. Parte **D** T3.
3. **F1** + `D-VERIFICACION` T3 antes de T4.