# TR-015-update-j – Segundo grupo CHK-21 / LAB-21 (oleada 8)

| Campo | Valor |
|-------|--------|
| **HU** | [HU-015-update-j](../../03-historias-usuario/100-SistemaPartes/HU-015-update-j-segundo-grupo-chk21.md) |
| **SPEC** | [SPEC-015-update oleada 8](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-8-segundo-grupo-chk21-update.md) |
| **C1** | [C1-TR-015-update-j-chk21](./C1-TR-015-update-j-chk21.md) — **Apto con bifurcación A/B** |
| **D1** | [D1-TR-015-update-j-chk21](./d1/D1-TR-015-update-j-chk21.md) |
| **Matriz** | CHK-21, LAB-21 |
| **Estado** | **Finalizado** (F 2026-11-24, **opción B**) |
| **Jira** | PIAPARTES-75 (Epic) · PIAPARTES-76 (HU) · PIAPARTES-77 (TR) |

---

## 1) Objetivo

Cerrar **CHK-21** (un turno) y asegurar **LAB-21** (dos turnos), según opción **A** o **B** fijada en D1 y SPEC §6.

---

## 2) Ramas de implementación

### Rama B (matriz vigente — **recomendación técnica D1**)

| Capa | Entrega |
|------|---------|
| `DesignLayoutActionPlanner.php` | Guard: `segundo nivel` / `segundo grupo` / `dentro de cada` → no mapear agrupación simple por cliente |
| `DesignLayoutActionPlannerTest.php` + matriz | `test_chk21` o añadir `phpunit_planner` a CHK-21 |
| i18n | Clave opcional `partes.emissionDesign.group.useTwoTurns` (5 locales) + cableado SC si no basta `noMappedActions` |
| `LayoutGroupByFieldApplier.cs` | Segundo campo con layout ya agrupado: insertar header fecha antes de Detail; pie `GroupFooter_fecha` |
| `LayoutGroupByFieldTests.cs` | Escenario cliente → apply fecha |
| `eval-emission-ia-checklist-lab-extras.ps1` | LAB-21 PASS con needles matriz |

### Rama A

| Capa | Entrega |
|------|---------|
| Planner | Heurística CHK-21 → 2× `addGroupByField` orden cliente, fecha |
| Matriz JSON Framework | CHK-21 → `plannerExpect: actions`, applyAssert |
| Sidecar | Igual extensión multi-header que rama B |
| Script | `eval-emission-ia-oleada8-chk21-apply.ps1` (batch un turno) |

---

## 3) Riesgo conocido (drift actual)

La heurística `/agrup.*cliente/` puede disparar **`addGroupByField` / `clienteNombre`** en el prompt CHK-21 por la palabra «agrupación» + «cliente», incumpliendo **`no_mapped`**. La rama **B** corrige esto en D.

---

## 4) Tests (parte E)

| Prueba | Resultado (2026-11-24, **B**) |
|--------|-------------------------------|
| `phpunit --filter DesignLayout` | **OK** (CHK-21 + checklist matriz) |
| `dotnet test --filter LayoutGroupByField` | **OK** — 6 (+ LAB-21 in-memory) |
| `eval-emission-ia-checklist-lab-extras.ps1` | **PASS** LAB-21 + CHK-26 |

---

## 5) Evidencia F1 / cierre F

| Parte | Documento |
|-------|-----------|
| F1 | [D-VERIFICACION-TR-015-update-j-chk21-2026-11-24](./D-VERIFICACION-TR-015-update-j-chk21-2026-11-24.md) |
| **F** | [F-TR-015-update-j-oleada8-chk21](./F-TR-015-update-j-oleada8-chk21.md) |

---

## 6) Confirmación alcance

Solo CHK-21 + LAB-21. Sin CHK-19/20 en la misma corrida.