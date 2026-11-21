# TR-015-update-h – Group header Cliente: (CHK-19, oleada 7)

| Campo | Valor |
|-------|--------|
| **HU** | [HU-015-update-h](../../03-historias-usuario/100-SistemaPartes/HU-015-update-h-group-header-cliente-chk19.md) |
| **SPEC** | [SPEC-015-update oleada 7](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-7-group-header-chk19-update.md) |
| **C1** | [C1-TR-015-update-h-chk19](./C1-TR-015-update-h-chk19.md) — **Apto** |
| **D1** | [D1-TR-015-update-h-chk19](./d1/D1-TR-015-update-h-chk19.md) |
| **TR previa** | [TR-015-update-f](./TR-015-update-f-agrupacion-cliente-chk18.md) |
| **Matriz** | `PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json` (CHK-19) |
| **Estado** | **Pendiente de Revisión** (parte D 2026-11-21) |
| **Jira** | PIAPARTES-69 (Epic) · PIAPARTES-70 (HU) · PIAPARTES-71 (TR) |

---

## 1) Objetivo

Cerrar **CHK-19** sobre el mismo planner que CHK-18: expresión **`'Cliente: ' + [clienteNombre]`**, negrita en label de group header, reparación de layouts CHK-18, noop solo si caption/estilo ya cumplen matriz.

---

## 2) Sidecar (Framework)

| Artefacto | Entrega |
|-----------|---------|
| `LayoutGroupByFieldApplier.cs` | Expresión `Cliente:`; `ResolveGroupCaptionPrefix(schema, field)`; reparación caption si `ya_agrupado` previo no aplica; noop ampliado |
| `LayoutGroupByFieldTests.cs` | +tests: caption nuevo, reparación CHK-18, noop con caption OK |
| `eval-emission-ia-oleada7-chk19-apply.ps1` | CHK-19: apply + assert needles + re-apply noop |

**Detalle applier (MUST):**

1. **Crear header:** `ExpressionBinding` Text = `'Cliente: ' + [clienteNombre]`; `Font` Bold 10pt Arial (como spike).
2. **Localizar label:** `lblGroup_{safeName}` en `GroupHeader_{safeName}`; si falta y hay header, crear label.
3. **¿Caption CHK-19 OK?** Expresión contiene `Cliente:` y `[clienteNombre]`; `Font.Bold == true` (subrayado ignorado para noop).
4. **Rama grouped + footer:**
   - Caption no OK → mutar → `Changed = true`.
   - Caption OK → `NoopYaAgrupado`.
5. **Regresión CHK-18:** tests existentes siguen pasando; footer repair sin cambios.

---

## 3) Host Partes

| Artefacto | Entrega |
|-----------|---------|
| `emissionDesignSmartCaptureTurn.ts` | Sin cambio salvo que reaparezca nuevo sufijo noop (no esperado) |
| `common.json` (×5) | Reutilizar `partes.emissionDesign.group.alreadyGrouped` |
| `DesignLayoutActionPlannerTest.php` | Sin nuevo test planner (misma heurística CHK-18); confirmar no regresión |
| `guia-manual-prompts-checklist-chk-emision-ia.md` | Fila CHK-19 + script oleada 7 |

Sin cambios whitelist.

---

## 4) Tests (parte E / D)

| Prueba | Resultado (2026-11-21) |
|--------|-------------------------|
| `phpunit --filter DesignLayout` | **OK** — 48 |
| `dotnet test --filter LayoutGroupByField` | **OK** — 5 (2026-11-21) |
| `eval-emission-ia-oleada7-chk19-apply.ps1` | **PASS CHK-19** (apply + re-apply `ya_agrupado`) |
| `eval-emission-ia-oleada6-apply.ps1` (regresión CHK-18) | **PASS CHK-18** |

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test --filter LayoutGroupByField

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-oleada7-chk19-apply.ps1
```

Smoke manual: diseñador muestra **Cliente:** en preview de grupo; re-aplicar sin error.

---

## 5) Evidencia F1 / cierre F (pendiente)

| Parte | Documento |
|-------|-----------|
| F1 | `D-VERIFICACION-TR-015-update-h-chk19-*.md` (tras E + smoke) |
| **F** | `F-TR-015-update-h-oleada7-chk19.md` |

---

## 6) Confirmación alcance

Solo **CHK-19**. Sin CHK-20/21/08-format. Sin regresión CHK-18 noop en layouts ya conformes CHK-19.