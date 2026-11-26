# D-VERIFICACION — TR-015-update-e T3 (CHK-07)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-e](./TR-015-update-e-page-header-informes.md) § T3 |
| SPEC | [SPEC-015-update oleada 5](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) |
| D1 | [D1-TR-015-update-e-oleada5-t3-chk07](./d1/D1-TR-015-update-e-oleada5-t3-chk07.md) |
| T2 base | [D-VERIFICACION T2](./D-VERIFICACION-TR-015-update-e-t2-chk06-2026-11-20.md) |
| Fecha cierre F1 | 2026-11-20 |

---

## Verificación agente (F1 hito T3)

### Resultado

**Aprobado** — T3 CHK-07 cerrado para abrir **T4 CHK-05** (gate TR §4).

### Alcance

Solo **CHK-07** / `setPageHeaderFecha`. Sin T4 ni `wrapReportTitleInPanel`.

### Entregables

| Capa | Artefacto |
|------|-----------|
| FW | `LayoutPageHeaderFechaApplier.cs`, `LayoutPageHeaderFechaTests.cs`, `LayoutApplyService` |
| Partes BE | `tryMatchFecha`, whitelist, planner + sanitize, prompt LLM |
| Partes FE | `pageHeader.fechaAlreadyPlaced` (×5), turno ante `setPageHeaderFecha:ya_colocados` |
| Matriz | CHK-07 `mvp` + `applyAssert` (`pageInfoFecha`, `PageInfo="DateTime"`, `MMMM`) |
| Doc | Guía manual §CHK-07; checklist FW |

### Evidencia manual

- Apply OK + **re-aplicar** con `pageInfoFecha` ya colocado → mensaje `fechaAlreadyPlaced` (usuario, 2026-11-20).

### Tests (cierre F1 — 2026-11-20)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | **OK** — suite DesignLayout |
| `php vendor/bin/phpunit --filter DesignLayoutActionPlannerTest` | **OK** — 17 tests (incl. CHK-07 + no confundir con agrupar por fecha) |
| `dotnet test … -c Release --filter PageHeader` | **OK** — 11 tests |
| `.\eval-emission-ia-oleada5-apply.ps1` | **OK** — PASS CHK-04 + CHK-06 + CHK-07 (`passed=3 failed=0`) |

### Criterios T3

| ID | Descripción | Veredicto |
|----|-------------|-----------|
| CA-B5-04 | Planner CHK-07 → `setPageHeaderFecha` | **OK** |
| AC-04 | `pageInfoFecha` DateTime, formato mes en letras sin hora | **OK** (.NET + eval) |
| AC-07 | Fila 1 der. page header | **OK** |
| D1-G07 | Heurística excluye «agrupá por fecha» | **OK** (PHPUnit) |

### Siguiente hito (SDD)

1. **D1 T4** — CHK-05 `wrapReportTitleInPanel`.
2. Parte **D** T4.
3. **F1** + `D-VERIFICACION` T4; luego cierre oleada 5 completa en TR/SPEC.