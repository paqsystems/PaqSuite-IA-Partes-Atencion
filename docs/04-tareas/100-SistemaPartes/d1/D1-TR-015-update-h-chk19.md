# Plan de implementación — TR-015-update-h (oleada 7, **CHK-19**)

> Parte **D1** (2026-11-21). Parte **D** ejecutada 2026-11-21 (OK usuario).

## Alcance entendido

Cerrar **CHK-19** sobre el **mismo** turno que CHK-18 (`addGroupByField` / `clienteNombre`, prompt «Agrupá por cliente»). El planner y el host **no** cambian. El sidecar debe:

1. Crear (o reparar) el label del group header con expresión **`'Cliente: ' + [clienteNombre]`** y **negrita** (subrayado fuera de MVP).
2. Mantener estructura CHK-18 (header 28px, footer emparejado, `GroupField`).
3. Ampliar **noop `ya_agrupado`**: solo si header+footer **y** caption/estilo CHK-19 ya conformes.
4. **Reparar** layouts oleada 6 con `'{field}: ' + [field]` sin duplicar bandas (mismo apply, `applied >= 1`).

Estado actual (`LayoutGroupByFieldApplier.cs` línea 52–53): `'{field}: ' + [{field}]` → incumple matriz (`Cliente:`, `Font=`).

Referencia deseada: `spike-layout-b-1.repx` / `SpikeApplyAct02` — `'Cliente: ' + [clienteNombre]`.

---

## Fuentes leídas

- SPEC: [SPEC-015-update oleada 7](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-7-group-header-chk19-update.md)
- HU: [HU-015-update-h](../../03-historias-usuario/100-SistemaPartes/HU-015-update-h-group-header-cliente-chk19.md)
- TR: [TR-015-update-h](../TR-015-update-h-group-header-cliente-chk19.md)
- C1: [C1-TR-015-update-h-chk19](../C1-TR-015-update-h-chk19.md) — Apto
- Código: `LayoutGroupByFieldApplier.cs`, `LayoutGroupByFieldTests.cs`, `LayoutControlAppearanceApplier` (patrón noop condicional)
- Matriz: CHK-19 `repxContains`: `Cliente:`, `clienteNombre`, `Font=`

---

## Impacto esperado

### Base de datos

Ninguno.

### Backend (host Partes)

Sin cambios de API. PHPUnit solo smoke regresión `DesignLayout`. Guía manual: fila CHK-19 + script oleada 7.

### Frontend (host Partes)

Sin cambios esperados en `emissionDesignSmartCaptureTurn.ts` (mismo sufijo `ya_agrupado`). i18n existente `partes.emissionDesign.group.alreadyGrouped` sigue válido tras reparación silenciosa vs noop.

### Sidecar (Framework `dx-reporting`)

| Archivo | Cambio |
|---------|--------|
| `LayoutGroupByFieldApplier.cs` | Prefijo legible; helpers `ResolveGroupCaptionPrefix`, `FindGroupHeaderLabel`, `IsChk19CaptionSatisfied`; rama repair antes de noop |
| `LayoutGroupByFieldTests.cs` | +2–3 tests: expresión REPX/xml, repair CHK-18, noop caption OK |
| `eval-emission-ia-oleada7-chk19-apply.ps1` | Clonar oleada6; `targetIds = CHK-19`; `reportUrl = partes_consultaDetallada_principal` (matriz CHK-19 sin URL — fijar en script como CHK-18) |

### Tests

- .NET: ampliar filtro `LayoutGroupByField` (objetivo ≥ 6 tests).
- PowerShell: apply1 mutación + needles; apply2 `ya_agrupado`.
- Manual F1: caption «Cliente:» visible en diseñador.

### Documentación

TR §4 resultados tras E; `D-VERIFICACION` / `F-TR` en F1/F; guía checklist CHK-19.

### DevOps

Reiniciar sidecar tras build local (mismo ritual CHK-18/08-format).

---

## Orden de trabajo

1. **Helpers y expresión en applier** — `ResolveGroupCaptionPrefix(schema, field)`: para `clienteNombre` / label `Cliente (nombre)` → prefijo **`Cliente:`**; fallback: segmento antes de ` (` + `:`.
2. **Creación header** — sustituir binding por `'{prefix} ' + [{field}]` con `prefix = Cliente` (sin corchetes en prefijo).
3. **Detección conformidad** — `IsChk19CaptionSatisfied(label, field)`: expresión contiene `Cliente:` y `[clienteNombre]`; `Font.Bold`.
4. **Refactor flujo `Apply`**:
   - Si `grouped && footer != null`:
     - Si caption OK → `ya_agrupado`.
     - Si no → `EnsureGroupCaption` → `changed = true` (no crear bandas duplicadas).
   - Si `!grouped` → crear bandas como hoy con caption CHK-19.
   - Si footer faltante → repair footer (sin cambio CHK-18).
5. **Tests unitarios** — assert expresión en `ExpressionBinding` y/o XML guardado; caso repair desde expresión `'clienteNombre: ' + [clienteNombre]`; actualizar noop test con label CHK-19 válido.
6. **Script eval oleada 7** — CHK-19 needles; restore/apply/re-apply.
7. **PHPUnit smoke** — sin nuevos casos planner.
8. **Guía manual** — una fila CHK-19.

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Noop CHK-18 en layout ya con `Cliente:` pero sin bold | Incluir bold en check noop; test dedicado |
| Label ausente en header legacy | `EnsureGroupCaption` crea `lblGroup_{safeName}` si falta |
| Serialización REPX distinta (`10pt` vs `10, 10pt`) | Assert needles `Cliente:` y `Font=` en script, no string exacto completo |
| Regresión test `Apply_noop_when_header_and_footer_already_present` | Preparar header con binding CHK-19 en fixture |
| Matriz CHK-19 sin `reportUrl` | Script fija URL igual oleada 6 |

---

## Tests a ejecutar (parte E)

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test --filter LayoutGroupByField

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-oleada7-chk19-apply.ps1
```

---

## Dudas / bloqueos

Ninguno bloqueante para D.

**Decisión técnica D1:** prefijo desde label de schema (no hardcode solo `clienteNombre`); para el piloto el resultado canónico es `Cliente:`.

**Frontera `ya_agrupado`:** agrupación “completa” CHK-18 **insuficiente** para noop si caption ≠ CHK-19; el segundo apply tras CHK-18 en lab debe **mutar** (repair), no mostrar “ya agrupado” hasta que caption esté OK.

---

## Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí** (solo presentación header + noop ampliado + eval).
- Sin CHK-20/21/08-format: **Sí**.
- Sin regresión estructural CHK-18: **Sí** (obligatorio en tests).
- Parte D: **pendiente** OK explícito del usuario.