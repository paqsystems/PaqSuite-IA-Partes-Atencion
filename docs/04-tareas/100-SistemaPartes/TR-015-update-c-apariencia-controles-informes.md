# TR-015-update-c – `setControlAppearance` (oleada 3)

| Campo | Valor |
|-------|--------|
| **HU** | [HU-015-update-c](../../03-historias-usuario/100-SistemaPartes/HU-015-update-c-apariencia-controles-informes.md) |
| **SPEC** | [SPEC-015-update oleada 3](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-3-apariencia-update.md) |
| **TR previa** | [TR-015-update-b](./TR-015-update-b-smart-capture-diseno-informes.md) |
| **Estado** | **Finalizado** |
| **Cierre** | [D-CIERRE oleada3](../updates/100-SistemaPartes/D-CIERRE-oleada3-apariencia-2026-11-18.md) |
| **Norma GEN** | GEN-03, GEN-15, GEN-37 |

**Plantilla regla 19:** adoptar contrato sidecar Framework; host amplía whitelist, planner y catálogo color. No reimplementar apply en PHP.

---

## 1) Whitelist v1.1 (añade oleada 3)

| `action` | Payload | Apply |
|----------|---------|--------|
| `setControlAppearance` | `target` + propiedades opcionales (ver SPEC §3.1) | Sidecar `LayoutApplyService` |
| *(alias apply)* `patchControlStyle` | Igual | Misma ruta |

Tipos oleada 2 sin cambio: `setReportTitle`, `addGroupByField`, `addGroupSum`, `setPageOrientation`.

### 1.1 `target`

```json
{ "kind": "field", "fieldName": "clienteCode" }
{ "kind": "reportTitle" }
{ "kind": "controlName", "name": "label1" }
```

---

## 2) Capas

| Capa | Archivos |
|------|----------|
| Framework sidecar | `LayoutApplyService.cs`, `LayoutControlAppearanceApplier.cs` |
| Partes BE | `DesignLayoutWhitelist.php`, `DesignLayoutActionPlanner.php`, `DesignLayoutColorCatalog.php` |
| Matriz | `15-reportes-emisiones-ia-checklist-matriz.json` |
| Tests | `DesignLayoutChecklistPlannerTest.php`, `DesignLayoutActionPlannerTest.php`; .NET `PaqSuite.DxReporting.Tests` |

---

## 3) Tareas

| # | Tarea | Estado |
|---|--------|--------|
| T1 | Contrato + apply sidecar | Hecho |
| T2 | Whitelist + sanitize Partes | Hecho |
| T3 | Heurísticas + LLM prompt | Hecho |
| T4 | Matriz CHK + guía manual | Hecho |
| T5 | PHPUnit + tests .NET | Hecho |

---

## 4) Pruebas

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test tests\PaqSuite.DxReporting.Tests\PaqSuite.DxReporting.Tests.csproj --filter "FullyQualifiedName~LayoutControlAppearance"
```

## 5) Cierre oleada 3 (SDD)

| Paso | Artefacto |
|------|-----------|
| F1 automático | [D-VERIFICACION-TR-015-update-c-2026-11-18.md](../updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-c-2026-11-18.md) |
| Smoke manual (2 CHK) | [smoke-oleada3-apariencia-controles.md](../../06-operacion/smoke-oleada3-apariencia-controles.md) |
| Apply script acotado | Framework `services/dx-reporting/scripts/eval-emission-ia-oleada3-apply.ps1` |
| Parte I | TR/HU/SPEC → **Finalizado** cuando smoke §3 registre ambos **OK** |