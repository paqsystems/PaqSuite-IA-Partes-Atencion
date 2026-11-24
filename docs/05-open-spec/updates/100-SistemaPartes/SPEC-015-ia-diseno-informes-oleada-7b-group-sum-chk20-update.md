# SPEC-015-update (oleada 7b) — Total minutos en pie de grupo (CHK-20)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-7b-group-sum-chk20 |
| Título | Sumar duración en group footer con copy «Total minutos» vía Smart Capture |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Finalizado** (2026-11-24) |
| Jira | PIAPARTES-72 (Epic) |
| SPEC previa | [Oleada 7 group header CHK-19](./SPEC-015-ia-diseno-informes-oleada-7-group-header-chk19-update.md) |
| HU | [HU-015-update-i](../../../03-historias-usuario/100-SistemaPartes/HU-015-update-i-group-sum-minutos-chk20.md) |
| TR | [TR-015-update-i](../../../04-tareas/100-SistemaPartes/TR-015-update-i-group-sum-minutos-chk20.md) |
| Matriz | Framework `15-reportes-emisiones-ia-checklist-matriz.json` — **CHK-20** |
| Proceso piloto | `partes.informes.consultaDetallada` |

**Plantilla regla 19:** GEN-03 + GEN-15; mutación REPX en sidecar Framework; heurística MUST en host Partes (`addGroupByField` + `addGroupSum` en el mismo turno). No apply PHP nuevo.

---

## 1. Resumen

El usuario pide **«Agrupá por cliente y sumá los minutos»**. El planner devuelve **dos acciones en orden**: `addGroupByField` / `clienteNombre` y `addGroupSum` / `duracionMinutos` / `summary: sum`. El sidecar debe:

1. Agrupar (CHK-18/19) si aún no está agrupado — header «Cliente:» + footer vacío `GroupFooter_clienteNombre`.
2. En el **mismo pie de grupo** (no crear un segundo `GroupFooter_*`), añadir subtotal con expresión tipo spike **ACT-03**: copy **«Total minutos:»** y agregado `sumSum([duracionMinutos])`.
3. Si el subtotal ya existe en algún `GroupFooterBand` → noop sin duplicar labels ni bandas.

Preferir apply **secuencial** en un solo POST apply (mismo contrato que TR-015-update-b).

---

## 2. GEN adoptados

| GEN | Rol |
|-----|-----|
| GEN-03 | Turno Smart Capture (planner + apply batch) |
| GEN-15 | Diseñador + Guardar/Descartar |
| GEN-16 | BYOK opcional; heurística obligatoria para prompt canónico |

---

## 3. Alcance

### 3.1 En alcance (CHK-20)

| CHK | Prompt | Acciones (orden) | assert REPX |
|-----|--------|-------------------|-------------|
| CHK-20 | Agrupá por cliente y sumá los minutos | `addGroupByField` `{ field: clienteNombre }` → `addGroupSum` `{ field: duracionMinutos, summary: sum }` | `Total minutos`, `duracionMinutos`, `sumSum` |

**Contrato `addGroupSum` (extensión oleada 7b):**

- Requiere al menos un `GroupHeaderBand` (tras `addGroupByField` en el mismo batch o layout previo).
- **Destino del subtotal:** el `GroupFooterBand` emparejado al grupo activo (`GroupFooter_{safeName}` del `GroupField`, típicamente `clienteNombre` → `GroupFooter_clienteNombre`), **no** `GroupFooter_duracionMinutos`.
- Si CHK-18 dejó el footer vacío (28px), **reutilizarlo** y solo agregar `lblGroupSum_*`.
- Expresión canónica (spike / matriz): `'Total minutos: ' + sumSum([duracionMinutos])` (no `Total duracionMinutos:`).
- **Idempotencia:** si ya hay suma de `duracionMinutos` en un group footer → `applied` no incrementa para esa acción; `details` puede incluir `noop:addGroupSum:ya_totalizado`.

**Layout eval:** layout limpio o `partes_consultaDetallada_principal` (misma convención oleadas 6–7); script `eval-emission-ia-chk20-apply.ps1`.

### 3.2 Fuera de alcance

- CHK-21 (segundo nivel de agrupación)
- CHK-08-format y oleadas de apariencia en esta corrida
- Nuevo tipo de acción planner; cambios a expectedActions de CHK-18/19
- Report Footer / totales globales (matriz: `addGroupSum` actúa a nivel grupo)

---

## 4. Criterios de aceptación

| ID | Criterio |
|----|----------|
| CA-B7b-01 | Heurística / PHPUnit: prompt canónico → `addGroupByField` + `addGroupSum` con campos matriz, **grupo antes que suma** |
| CA-B7b-02 | Apply batch: un solo `GroupFooter_*` de cliente; REPX contiene needles matriz |
| CA-B7b-03 | No duplica `GroupFooter_duracionMinutos` cuando ya existe `GroupFooter_clienteNombre` (CHK-18) |
| CA-B7b-04 | Re-apply con suma ya presente → noop suma (grupo puede noop CHK-19 si ya conforme) |
| CA-B7b-05 | Sin regresión CHK-18/19 (scripts oleada 6 y 7) |
| CA-B7b-06 | `eval-emission-ia-chk20-apply.ps1` PASS (apply + re-apply) |
| CA-B7b-07 | L-B-10 sin regresión (banner Guardar/Descartar) |

---

## 5. Contrato sidecar

| Acción | Comportamiento |
|--------|----------------|
| `addGroupByField` | Sin cambio oleada 7 (CHK-19) |
| `addGroupSum` | Resolver pie emparejado al último grupo; insertar label subtotal; noop si `FooterHasGroupSum` |

Implementación preferida: extraer o reutilizar resolución de footer emparejado (`LayoutGroupByFieldApplier` / helper compartido); ajustar `ApplyGroupSum` en `LayoutApplyService.cs` o applier dedicado.

Referencia deseada: `spike-layout-b-1.repx`, `SpikeApplyAct03/Program.cs`.

---

## 6. Frontera CHK-18 / CHK-19

| Aspecto | CHK-18/19 | CHK-20 |
|---------|-----------|--------|
| Planner | Una acción | Dos acciones (grupo + suma) |
| Group footer | Vacío 28px | Mismo footer + label total minutos |
| Group header | Cliente: negrita | Sin cambio |
| Noop grupo | `ya_agrupado` | Independiente del noop suma |

Un turno CHK-20 no debe dejar dos pies de grupo para el mismo nivel ni vaciar el subtotal al reparar caption CHK-19.