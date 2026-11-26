# Plan de implementación — TR-015-update-i (oleada 7b, **CHK-20**)

> Parte **D1** (2026-11-24). Parte **D** ejecutada 2026-11-24 (OK usuario).

## Alcance entendido

Cerrar **CHK-20** con prompt **«Agrupá por cliente y sumá los minutos»**. El planner Partes ya devuelve `addGroupByField` + `addGroupSum` (orden grupo → suma). El sidecar debe corregir la deuda de **ACT-03** en producción:

1. **Un solo pie de grupo** por nivel: reutilizar `GroupFooter_clienteNombre` (CHK-18) al aplicar suma; no insertar `GroupFooter_duracionMinutos`.
2. Copy y expresión REPX como spike: `'Total minutos: ' + sumSum([duracionMinutos])` (needles matriz).
3. **Noop** si el subtotal de `duracionMinutos` ya existe (`FooterHasGroupSum` ampliado + sufijo `ya_totalizado` en `details`).
4. Batch secuencial en `LayoutApplyService` sin cambiar contrato HTTP.

Estado actual (`ApplyGroupSum` ~L181–227): crea banda nueva `GroupFooter_{safeName}` del campo numérico, texto `Total {field}:`, binding `[field]` + `XRSummary` — **no** cumple matriz ni frontera CHK-18.

---

## Fuentes leídas

- SPEC: [SPEC-015-update oleada 7b](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-7b-group-sum-chk20-update.md)
- HU: [HU-015-update-i](../../03-historias-usuario/100-SistemaPartes/HU-015-update-i-group-sum-minutos-chk20.md)
- TR: [TR-015-update-i](../TR-015-update-i-group-sum-minutos-chk20.md)
- C1: [C1-TR-015-update-i-chk20](../C1-TR-015-update-i-chk20.md) — Apto
- Código: `LayoutApplyService.ApplyGroupSum`, `LayoutGroupByFieldApplier` (`FindPairedGroupFooter`), `DesignLayoutActionPlanner.php` L91–101, spike `SpikeApplyAct03/Program.cs`, `spike-layout-b-1.repx`
- Matriz CHK-20: `Total minutos`, `duracionMinutos`, `sumSum`
- TR-015-update-b §`addGroupSum`

---

## Impacto esperado

### Base de datos

Ninguno.

### Backend (host Partes)

PHPUnit: reforzar `test_heuristics_group_and_sum` (orden de acciones, `summary: sum`, campos). Sin API nueva.

### Frontend (host Partes)

Opcional: si se introduce `noop:addGroupSum:ya_totalizado`, extender `emissionDesignSmartCaptureTurn.ts` solo si hace falta mensaje i18n (evaluar en D: puede bastar `noop:addGroupSum` genérico).

### Sidecar (Framework `dx-reporting`)

| Archivo | Cambio |
|---------|--------|
| `LayoutApplyService.cs` | Refactor `ApplyGroupSum` → `LayoutApplyStepResult` con sufijo noop; resolver footer emparejado |
| `LayoutGroupFooterResolver.cs` (sugerido) o métodos internos compartidos | Extraer lógica de `FindPairedGroupFooter` + `ResolvePrimaryGroupField` |
| `LayoutGroupSumTests.cs` | Nuevo: apply batch, footer reuse, noop, asserts XML |
| `eval-emission-ia-chk20-apply.ps1` | Clonar oleada 7; `targetIds = CHK-20`; batch `expectedActions` |

### Tests

- .NET: filtro `LayoutGroupSum` (≥ 3 casos).
- PowerShell: apply1 mutación + needles; apply2 noop en suma (y grupo noop o repair según estado layout).
- Regresión: oleada 6 + 7 scripts.

### Documentación

TR §4 resultados tras E; guía checklist CHK-20; F1/F pendientes.

### DevOps

Reiniciar sidecar tras build local.

---

## Orden de trabajo

1. **Resolver footer destino** — Dado `GroupHeaderBand`(s), obtener `GroupField` → `safeName` → `FindPairedGroupFooter`; si footer null, crear `GroupFooter_{safeNameGrupo}` (no del campo suma).
2. **Alinear expresión y copy** — Binding `'Total minutos: ' + sumSum([duracionMinutos])`; fuente italic 9pt como spike; verificar serialización contiene `sumSum` (si XRSummary lo oculta, preferir expresión spike documentada en TR-015-update-b / deuda XRE090).
3. **Noop y `LayoutApplyStepResult`** — Cambiar `ApplyGroupSum` a retornar step result con `ya_totalizado` cuando `FooterHasGroupSum`.
4. **Eliminar insert duplicado** — No llamar `InsertBandAfterDetail` con nuevo footer si ya existe pie emparejado.
5. **Tests unitarios .NET** — Escenario: apply `addGroupByField` luego `addGroupSum` en memoria; contar `GroupFooterBand` = 1; XML needles.
6. **Test reuse** — Layout preagrupado CHK-18 (footer vacío) + solo `addGroupSum` → misma banda `GroupFooter_clienteNombre`.
7. **PHPUnit orden** — Assert índices: `actions[0]` group, `actions[1]` sum.
8. **Script eval CHK-20** — Restore, apply batch matriz, re-apply; validar `noop:addGroupSum:ya_totalizado` o `applied=0` en paso 2.
9. **Guía manual** — Enlace script en fila CHK-20.

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Validación bindings XRE090 sin XRSummary | Conservar patrón spike; `LayoutStubCleanup` post-apply |
| Múltiples `GroupHeader` futuros (CHK-21) | Piloto: último header o primer grupo cliente; documentar límite |
| Regresión apply suma en layouts legacy con pie distinto | Test con `spike-layout-b-1` / layout principal |
| CHK-19 repair muta mientras suma noop | Batch orden fijo; tests integrados |

---

## Tests a ejecutar (parte E)

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test --filter LayoutGroupSum

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-chk20-apply.ps1
.\eval-emission-ia-oleada7-chk19-apply.ps1
.\eval-emission-ia-oleada6-apply.ps1
```

---

## Dudas / bloqueos

Ninguno bloqueante para D.

**Decisión técnica D1:** pie de subtotal siempre en el footer emparejado al **campo de agrupación**, no al campo numérico sumado.

**Decisión copy:** literal **Total minutos** (matriz), no nombre técnico del campo.

---

## Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí** (solo `addGroupSum` + tests/eval).
- Sin CHK-19/21/08-format en esta corrida: **Sí** (solo regresión scripts).
- Parte D: **ejecutada** 2026-11-24 (`LayoutGroupSumApplier`, tests, script eval).