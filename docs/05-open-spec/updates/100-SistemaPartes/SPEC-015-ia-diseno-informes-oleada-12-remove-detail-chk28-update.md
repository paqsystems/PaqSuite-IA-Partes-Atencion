# SPEC-015-update (oleada 12) — Eliminar columna del detalle (CHK-28)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-12-remove-detail-chk28 |
| Título | Quitar columna «observación» del detalle vía Smart Capture |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Finalizado** (2026-11-26) |
| Jira | [PIAPARTES-53](https://paqsystems.atlassian.net/browse/PIAPARTES-53) · [PIAPARTES-80](https://paqsystems.atlassian.net/browse/PIAPARTES-80) (HU) · [PIAPARTES-81](https://paqsystems.atlassian.net/browse/PIAPARTES-81) (TR) |
| SPEC previa | [Oleada 11 imágenes CHK-15..17](./SPEC-015-ia-diseno-informes-oleada-11-imagenes-chk15-17-update.md) |
| HU | [HU-015-update-n](../../../03-historias-usuario/100-SistemaPartes/HU-015-update-n-remove-detalle-chk28.md) |
| TR | [TR-015-update-n](../../../04-tareas/100-SistemaPartes/TR-015-update-n-remove-detalle-chk28.md) |
| Matriz | Framework `15-reportes-emisiones-ia-checklist-matriz.json` — **CHK-28** |
| Proceso piloto | `partes.informes.consultaDetallada` |
| Veredicto A1 | **Apto** para HU/TR — alcance acotado a una acción `removeDetailField` y un campo piloto |

**Plantilla regla 19:** GEN-03 + GEN-15; mutación REPX en **sidecar Framework** (`LayoutDetailFieldRemoveApplier`); host: whitelist + heurística específica **antes** del bloqueo genérico `DesignLayoutRemovalIntentHeuristics`. No appliers PHP.

---

## 1. Resumen

El usuario pide en castellano **«Quitá la columna observación del detalle»**. El planner debe devolver **`removeDetailField`** con `{ "field": "observacion" }` (campo autorizado en schema consulta detallada). El sidecar elimina del **DetailBand** el control vinculado a `[observacion]` y el título de columna en **PageHeader** `lblPageHdr_observacion` (misma convención que CHK-05/`addPageHeaderColumnLabels`).

Si el control **no existe** → noop **`noop:removeDetailField:ya_ausente`** con mensaje i18n (sin error HTTP). Si existe → apply muta REPX y el usuario **Guarda / Descarta** en el diseñador (L-B-10).

Esta oleada **inaugura la familia remove* en whitelist v1.3** solo para **un** caso (observación en detalle). CHK-29..32 permanecen fuera de alcance.

---

## 2. GEN adoptados

| GEN | Rol |
|-----|-----|
| GEN-03 | Turno Smart Capture bajo diseñador |
| GEN-15 | Diseñador + apply sidecar |
| GEN-16 | LLM opcional; heurística obligatoria para prompt canónico CHK-28 |

---

## 3. Alcance

### 3.1 En alcance (CHK-28)

| CHK | Prompt canónico | Acción | Assert |
|-----|-----------------|--------|--------|
| CHK-28 | Quitá la columna observación del detalle | `removeDetailField` `{ field: observacion }` | REPX sin `lblDetail_observacion` ni binding `[observacion]` en Detail |

**Sinónimos heurística (v1):** columna/campo observación, observaciones, nota/comentario en detalle (alineado a `synonyms` del schema).

**Precondición smoke:** layout con columna observación visible (informe base o apply previo CHK-01 / diseño emisor con el campo).

**Planner (orden obligatorio):**

1. `DesignLayoutRemoveDetailFieldHeuristics::tryMatch` (nuevo) — resuelve campo por texto + schema.
2. Solo si no hay match → `DesignLayoutRemovalIntentHeuristics` sigue devolviendo `noMappedActions` para otros pedidos de eliminación (CHK-29..32).

### 3.2 Fuera de alcance

| Ítem | Motivo |
|------|--------|
| CHK-29..32 | Oleadas futuras (`removeGroupByField`, etc.) |
| Eliminar otros campos detalle distintos de `observacion` en v1.3 | Una fila matriz; ampliar en TR-update posteriores |
| Eliminar controles fuera de Detail (page header, grupos, QR CHK-17) | No es CHK-28 |
| Eliminar etiquetas de encabezado de columna sin binding detalle | Diferido |
| Upload / assets | N/A |

---

## 4. Criterios de aceptación

| ID | Criterio |
|----|----------|
| CA-B12-01 | Prompt canónico CHK-28 → planner devuelve exactamente una acción `removeDetailField` / `observacion` |
| CA-B12-02 | Apply en layout con `lblDetail_observacion` (o binding equivalente) → control ausente en Detail; sin excepción sidecar |
| CA-B12-03 | Re-apply en layout ya sin observación → noop `ya_ausente` + i18n |
| CA-B12-04 | «Sacá la agrupación por cliente» (CHK-29) → sigue `noMappedActions` (regresión planner) |
| CA-B12-05 | Tras apply exitoso, banner Guardar/Descartar usable (L-B-10) |

---

## 5. Contrato sidecar (Framework)

- **Applier:** `LayoutDetailFieldRemoveApplier` — simétrico a `LayoutDetailFieldApplier`; valida campo en schema; busca label en DetailBand; `Controls.Remove`; retorna `true` si eliminó al menos un control.
- **Dispatch:** `LayoutApplyService` case `removedetailfield`.
- **Tests:** `LayoutDetailFieldRemoveTests` (layout fixture con observación → remove → assert ausente; segundo apply → noop false o contador según convención existente).

---

## 6. Contrato host (Partes)

- Extender `DesignLayoutWhitelist` con `removeDetailField`.
- Nueva clase heurística + tests en `DesignLayoutChecklistPlannerTest` fila CHK-28.
- Reordenar `DesignLayoutActionPlanner::fromHeuristics` (remove detail **antes** del bloqueo genérico).
- i18n: `partes.emissionDesign.removeDetailField.applied`, `partes.emissionDesign.removeDetailField.noop.ya_ausente` (5 locales).
- `eval-emission-ia-oleada12-chk28-apply.ps1` + matriz CHK-28 `mvp` con `expectedActions` y `applyAssert`.

---

## 7. Matriz (delta previsto en D)

CHK-28 pasa de `futuro_producto` / `no_mapped` a:

- `estado`: `mvp`
- `plannerExpect`: `mapped`
- `expectedActions`: `[{ "action": "removeDetailField", "payload": { "field": "observacion" } }]`
- `applyAssert`: `repxNotContains`: `["lblDetail_observacion", "[observacion]"]` (en Detail; criterio TR)
- `testLayers`: `phpunit_planner`, `dotnet_apply`, `ps_apply`

---

## 8. Limitaciones (actualizar L-B-02)

Mover de «futuro» a **whitelist v1.3 parcial**: `removeDetailField` solo CHK-28; demás eliminaciones siguen excluidas.

---

## 9. Trazabilidad

| Artefacto | Repo |
|-----------|------|
| SPEC/HU/TR/D1 | Partes-Atencion |
| Applier + matriz | Framework |

**OpenSpec producto condicional:** [19-ia-diseno-informes.md](../../../02-producto/Sistema-Partes-IA/19-ia-diseno-informes.md) — checklist CHK-28.