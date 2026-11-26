# TR-015-update-e – Page header rico (oleada 5, CHK-04…07)

| Campo | Valor |
|-------|--------|
| **HU** | [HU-015-update-e](../../03-historias-usuario/100-SistemaPartes/HU-015-update-e-page-header-informes.md) |
| **SPEC** | [SPEC-015-update oleada 5](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) |
| **TR previa** | [TR-015-update-d3](./TR-015-update-d3-titulo-page-bands-chk03.md) (CHK-03) |
| **Contrato Framework** | [15-reportes-emisiones-update-4-ia-diseno.md](../../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-update-4-ia-diseno.md) §oleada 5 |
| **Matriz** | `PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json` |
| **Estado** | **Finalizado** (2026-11-20 — T1–T4 F1 + E/F + smoke manual; [F](./F-TR-015-update-e-oleada5-page-header.md)) |
| **Norma GEN** | GEN-03, GEN-15, GEN-16 (heurísticas MUST), GEN-37 (CHK-04 explícito) |

**Plantilla regla 19:** cuatro acciones nuevas en sidecar + whitelist/heurísticas Partes. Implementación **por hitos** T1→T4; no mezclar CA sin F1 del hito anterior.

---

## 1) Modelo de filas (sidecar — constantes v1)

Compartido por todos los appliers oleada 5 (`LayoutPageHeaderLayout.cs` o equivalente):

| Constante | Valor (float) | Uso |
|-----------|---------------|-----|
| `PageHeaderRow1Y` | `0` | Empresa, `pageInfoFecha` |
| `PageHeaderRow2Y` | `24` | `lblReportTitle` / `pnlReportTitle` |
| `PageHeaderRow3Y` | `48` | `lblPageHdr_*` columnas |
| `PageHeaderMinHeightF` | `72` | Altura mínima `PageHeaderBand` |

- Ampliar altura de banda si controles exceden fila 3 (solo hacia abajo).
- **Legacy:** si `lblReportTitle` está en `ReportHeaderBand`, copiar `Text` a fila 2 en page header; si report header queda sin controles, `HeightF` mínimo (p. ej. 1f) — no borrar banda.

---

## 2) Whitelist v1.2 (añade oleada 5)

| `action` | Payload mínimo | Sidecar |
|----------|----------------|---------|
| `addPageHeaderColumnLabels` | `fields?[]`, `style?` ignorado | `LayoutPageHeaderColumnLabelsApplier.cs` |
| `setPageHeaderEmpresa` | `text` 1..200 | `LayoutPageHeaderEmpresaApplier.cs` |
| `setPageHeaderFecha` | `format?` default `dd, MMMM 'de' yyyy` | `LayoutPageHeaderFechaApplier.cs` (`XRPageInfo` `pageInfoFecha`) |
| `wrapReportTitleInPanel` | `{}` | `LayoutPageHeaderTitlePanelApplier.cs` |

Registrar en `LayoutApplyService.AllowedTypes` y dispatcher `switch`.

Tipos oleadas 2–4 sin cambio semántico.

---

## 3) Host Partes (backend)

| Artefacto | Responsabilidad |
|-----------|-----------------|
| `DesignLayoutWhitelist.php` | Añadir los cuatro `action` |
| `DesignLayoutPageHeaderHeuristics.php` | CHK-04/05/06/07; sinónimos ES; extracción empresa del prompt |
| `DesignLayoutActionPlanner.php` | Orden heurísticas; post-proceso noop CHK-04 → clave mensaje |
| `DesignLayoutSemanticFieldResolver` / `DesignSemanticCatalog` | CHK-04 con columnas nombradas → `fields[]` |
| `locales/*/common.json` (o claves producto) | `partes.emissionDesign.pageHeader.*` (sin columnas, acción planeada, etc.) |

**CHK-06 planner:** regex nombre entre comillas o tras “empresa …”; si no hay texto → `"Empresa demo"` **solo** si `APP_ENV=local` o tenant header `DEV` documentado en runbook (misma convención lab que diseñador `?cliente=DEV`).

**CHK-04 post-apply:** si sidecar `applied: 0` y detail contiene `sin_columnas_detalle`, `replyText` MUST usar i18n AC-08.

---

## 4) Hitos de implementación

### T1 — CHK-04 (`addPageHeaderColumnLabels`) — **implementado**

| Capa | Entregable |
|------|------------|
| FW | Applier + tests `LayoutPageHeaderColumnLabelsTests.cs`; noop detail `details` estándar |
| FW | `eval-emission-ia-oleada5-apply.ps1` (solo CHK-04 inicialmente) |
| Partes | Whitelist + heurística CHK-04 + PHPUnit matriz |
| Matriz | CHK-04 → `estado: mvp`, `expectedActions`, `applyAssert` (`lblPageHdr_`, fila 3) |
| Doc | Guía manual §CHK-04; smoke `smoke-oleada5-page-header-chk04.md` |

**CA T1:** CA-B5-01, CA-B5-02, AC-08.

**Cierre T1 (2026-11-20):** payload apply `{}`; noop `ya_colocados` + i18n `partes.emissionDesign.pageHeader.columnLabelsAlreadyPlaced`; evidencia [D-VERIFICACION T1](./D-VERIFICACION-TR-015-update-e-t1-chk04-2026-11-20.md).

### T2 — CHK-06 (`setPageHeaderEmpresa`) — **implementado**

| Capa | Entregable |
|------|------------|
| FW | `LayoutPageHeaderEmpresaApplier` + tests; noop `ya_colocados`; búsqueda recursiva label |
| Partes | `tryMatchEmpresa`, `DesignLayoutPageHeaderLab`, FE `empresaAlreadyPlaced` |
| Matriz | CHK-06 `mvp`; eval script T1+T2 |

**CA T2:** CA-B5-03, AC-03, AC-07 (fila 1 izq).

**Cierre T2 (2026-11-20):** D1 [oleada5-t2-chk06](./d1/D1-TR-015-update-e-oleada5-t2-chk06.md); F1 [D-VERIFICACION T2](./D-VERIFICACION-TR-015-update-e-t2-chk06-2026-11-20.md). **Entrada T3:** gate OK.

### T3 — CHK-07 (`setPageHeaderFecha`)

| Capa | Entregable |
|------|------------|
| FW | `XRPageInfo` `pageInfoFecha`, `FormatString`, alineación derecha fila 1 |
| Partes | Heurística fecha |
| Matriz | CHK-07 `mvp` |

**CA T3:** CA-B5-04, AC-04, AC-07 (fila 1 der).

| Capa | Implementado |
|------|--------------|
| FW | `LayoutPageHeaderFechaApplier`, tests, eval CHK-07 |
| Partes | `tryMatchFecha`, FE `fechaAlreadyPlaced` |
| Matriz | CHK-07 `mvp`; eval script T1+T2+T3 |

**Cierre T3 (2026-11-20):** D1 [oleada5-t3-chk07](./d1/D1-TR-015-update-e-oleada5-t3-chk07.md); F1 [D-VERIFICACION T3](./D-VERIFICACION-TR-015-update-e-t3-chk07-2026-11-20.md). **Entrada T4:** gate OK.

### T4 — CHK-05 (`wrapReportTitleInPanel`)

| Capa | Entregable |
|------|------------|
| FW | Panel fila 2 + sync título legacy |
| Partes | Heurística “panel/recuadro” |
| Matriz | CHK-05 `mvp` |

**CA T4:** CA-B5-05, AC-05, AC-06 smoke completo cuatro CHK.

| Capa | Implementado |
|------|--------------|
| FW | `LayoutPageHeaderTitlePanelApplier`, tests, eval CHK-05 |
| Partes | `tryMatchTitlePanel`, FE `titlePanelAlreadyPlaced` |
| Matriz | CHK-05 `mvp`; eval script CHK-04..07 |

**Cierre T4 (2026-11-20):** D1 [oleada5-t4-chk05](./d1/D1-TR-015-update-e-oleada5-t4-chk05.md); F1 [D-VERIFICACION T4](./D-VERIFICACION-TR-015-update-e-t4-chk05-2026-11-20.md). **Oleada 5 implementada** (revisión humana E/F pendiente).

**Gate:** F1 + [D-VERIFICACION] por hito antes de abrir el siguiente en la misma rama o PR acumulativo.

---

## 5) Capas y archivos (resumen)

| Capa | Archivos nuevos/modificados |
|------|-----------------------------|
| Framework sidecar | `Reporting/LayoutPageHeader*.cs`, `LayoutApplyService.cs`, tests, script eval oleada 5 |
| Partes BE | `DesignLayoutPageHeaderHeuristics.php`, whitelist, planner, i18n |
| Partes FE | Sin cambios salvo claves i18n si el turno muestra mensajes nuevos |
| Framework docs | Matriz CHK + UPDATE-4 ya referencian contrato; actualizar `expectedActions` por hito |

---

## 6) Pruebas

```powershell
# Planner + matriz (Partes)
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayoutChecklistPlannerTest
php vendor/bin/phpunit --filter DesignLayoutActionPlannerTest

# Sidecar (.NET) — ampliar filtro por applier
cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test tests\PaqSuite.DxReporting.Tests\PaqSuite.DxReporting.Tests.csproj --filter "FullyQualifiedName~PageHeader"

# Apply integrado (sidecar :5055)
.\scripts\eval-emission-ia-oleada5-apply.ps1
```

**Layout lab:** `spike-layout-b-1` (con detalle poblado para CHK-04).

**Smoke UI oleada completa:** [smoke-oleada5-page-header-chk04-07.md](../../06-operacion/smoke-oleada5-page-header-chk04-07.md) (CHK-04…07 + Guardar/Descartar).

---

## 10) Parte E — evidencia de tests (2026-11-20)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | OK — 47 tests, 148 assertions |
| `php vendor/bin/phpunit --filter DesignLayoutChecklistPlannerTest` | OK — 29 tests |
| `php vendor/bin/phpunit --filter DesignLayoutActionPlannerTest` | OK — 18 tests |
| `dotnet test … -c Release --filter PageHeader` | OK — 13 tests |
| `eval-emission-ia-oleada5-apply.ps1` (sidecar `:5055` Release) | OK — `passed=4 failed=0` |

Sidecar reiniciado: `stop-sidecar-port-5055.ps1` → `dotnet build -c Release` → `dotnet run -c Release --no-build --urls http://127.0.0.1:5055`; `GET /health` → `ok`.

---

## 7) Criterios de cierre TR (Definition of Done)

| # | Criterio |
|---|----------|
| D1 | Los cuatro CHK en matriz `mvp` con `plannerExpect: actions` |
| D2 | Scripts eval + PHPUnit + .NET verdes en CI/local |
| D3 | Smoke manual guía: empresa, fecha, columnas, panel, Guardar/Descartar — **OK usuario 2026-11-20** |
| D4 | SPEC/HU/TR → **Finalizado** tras F1 oleada completa (no solo T1) |

---

## 8) Riesgos técnicos

| Riesgo | Mitigación |
|--------|------------|
| Título duplicado ReportHeader + PageHeader | Appliers T4/T2 sincronizan y reducen report header vacío |
| Page header corto (24f oleada 3) | `EnsurePageHeaderThreeRows()` eleva `HeightF` |
| Locale fecha español en servidor | `FormatString` + cultura diseño documentada en test |

---

## 9) SDD

| Parte | Estado |
|-------|--------|
| C (este doc) | **Especificado** 2026-11-19 |
| C1 | Apto 2026-11-19 |
| D1 T1 | [D1-TR-015-update-e-oleada5-t1-chk04](../d1/D1-TR-015-update-e-oleada5-t1-chk04.md) |
| D1 T2 | [D1-TR-015-update-e-oleada5-t2-chk06](../d1/D1-TR-015-update-e-oleada5-t2-chk06.md) |
| D / F1 T1 | Cerrado 2026-11-20 |
| D / F1 T2 | Cerrado 2026-11-20 — [D-VERIFICACION T2](./D-VERIFICACION-TR-015-update-e-t2-chk06-2026-11-20.md) |
| D1 T3 | [D1-TR-015-update-e-oleada5-t3-chk07](../d1/D1-TR-015-update-e-oleada5-t3-chk07.md) |
| D / F1 T3 | Cerrado 2026-11-20 — [D-VERIFICACION T3](./D-VERIFICACION-TR-015-update-e-t3-chk07-2026-11-20.md) |
| D1 T4 | [D1-TR-015-update-e-oleada5-t4-chk05](../d1/D1-TR-015-update-e-oleada5-t4-chk05.md) |
| D / F1 T4 | Cerrado 2026-11-20 — [D-VERIFICACION T4](./D-VERIFICACION-TR-015-update-e-t4-chk05-2026-11-20.md) |
| E | Cerrado 2026-11-20 — §10 Parte E (tests automatizados) |
| F | [F-TR-015-update-e-oleada5](./F-TR-015-update-e-oleada5-page-header.md) — **Aprobado** (D3 smoke manual cerrado 2026-11-20) |