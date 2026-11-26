# TR-015-update-i – Total minutos group footer (CHK-20, oleada 7b)

| Campo | Valor |
|-------|--------|
| **HU** | [HU-015-update-i](../../03-historias-usuario/100-SistemaPartes/HU-015-update-i-group-sum-minutos-chk20.md) |
| **SPEC** | [SPEC-015-update oleada 7b](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-7b-group-sum-chk20-update.md) |
| **C1** | [C1-TR-015-update-i-chk20](./C1-TR-015-update-i-chk20.md) — **Apto** |
| **D1** | [D1-TR-015-update-i-chk20](./d1/D1-TR-015-update-i-chk20.md) |
| **TR previa** | [TR-015-update-h](./TR-015-update-h-group-header-cliente-chk19.md) · [TR-015-update-b](./TR-015-update-b-smart-capture-diseno-informes.md) §`addGroupSum` |
| **Matriz** | `PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json` (CHK-20) |
| **Estado** | **Finalizado** (F 2026-11-24) |
| **Jira** | PIAPARTES-72 (Epic) · PIAPARTES-73 (HU) · PIAPARTES-74 (TR) |

---

## 1) Objetivo

Cerrar **CHK-20**: prompt **«Agrupá por cliente y sumá los minutos»** → planner batch (grupo + suma) → sidecar deja subtotal **«Total minutos:»** + `sumSum([duracionMinutos])` en el **pie emparejado al grupo cliente**, sin duplicar bandas footer ni romper CHK-18/19.

---

## 2) Sidecar (Framework)

| Artefacto | Entrega |
|-----------|---------|
| `LayoutGroupSumApplier.cs` | Reutilizar `GroupFooter_{safeNameGrupo}`; expresión ACT-03; noop `ya_totalizado` |
| `LayoutApplyService.cs` | Delega `addGroupSum` → applier |
| `LayoutGroupSumTests.cs` | Batch CHK-20, reuse footer CHK-18, noop re-apply (**3** tests) |
| `eval-emission-ia-chk20-apply.ps1` | CHK-20: restore → apply batch matriz → needles → re-apply noop suma |

**Detalle `ApplyGroupSum` (MUST):**

1. Validar `duracionMinutos` numérico en schema.
2. Si `FooterHasGroupSum(report, duracionMinutos)` → `LayoutApplyStepResult(false, ya_totalizado)`.
3. Si no hay `GroupHeaderBand` → excepción (mensaje existente).
4. Resolver `groupFooter` = pie emparejado al primer campo de grupo del header más interno / último header (piloto: `clienteNombre` → `GroupFooter_clienteNombre`). Si no existe pie, crear uno con nombre emparejado (no nombreado por campo numérico).
5. Agregar `XRLabel` `lblGroupSum_duracionMinutos` (o equivalente) con `Font` Arial 9pt Italic; binding Text = `'Total minutos: ' + sumSum([duracionMinutos])` (alineado matriz; XRSummary solo si no rompe needles `sumSum` en XML guardado — decisión en D1).
6. **Prohibido** en batch CHK-20: crear `GroupFooter_duracionMinutos` adicional cuando ya existe pie de cliente.

---

## 3) Host Partes

| Artefacto | Entrega |
|-----------|---------|
| `DesignLayoutActionPlanner.php` | Sin cambio funcional (heurística ya emite ambas acciones); validar orden estable (grupo antes suma) |
| `DesignLayoutActionPlannerTest.php` | `test_heuristics_group_and_sum`: orden + payload `summary` (**hecho**) |
| `guia-manual-prompts-checklist-chk-emision-ia.md` | Fila CHK-20 + script |

Sin cambios whitelist si planner ya incluye `addGroupSum`.

---

## 4) Tests (parte E / D)

| Prueba | Resultado (2026-11-24) |
|--------|-------------------------|
| `phpunit --filter DesignLayout` | **OK** — 48 |
| `dotnet test --filter LayoutGroup` | **OK** — 8 (5 CHK-18/19 + 3 CHK-20) |
| `eval-emission-ia-chk20-apply.ps1` | **PASS CHK-20** |
| `eval-emission-ia-oleada7-chk19-apply.ps1` | **PASS** (regresión CHK-19) |
| `eval-emission-ia-oleada6-apply.ps1` | **PASS** (regresión CHK-18) |

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test --filter LayoutGroupSum

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-chk20-apply.ps1
```

Smoke manual: preview por grupo muestra total minutos; L-B-10.

---

## 5) Evidencia F1 / cierre F

| Parte | Documento |
|-------|-----------|
| F1 | [D-VERIFICACION-TR-015-update-i-chk20-2026-11-24](./D-VERIFICACION-TR-015-update-i-chk20-2026-11-24.md) |
| **F** | [F-TR-015-update-i-oleada7b-chk20](./F-TR-015-update-i-oleada7b-chk20.md) |

---

## 6) Confirmación alcance

Solo **CHK-20**. Sin CHK-21/08-format. Sin regresión noop CHK-19 ni estructura CHK-18.