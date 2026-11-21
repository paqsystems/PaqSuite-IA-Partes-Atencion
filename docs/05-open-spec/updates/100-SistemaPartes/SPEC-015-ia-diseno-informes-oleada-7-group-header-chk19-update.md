# SPEC-015-update (oleada 7) — Expresión y estilo en group header (CHK-19)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-7-group-header-chk19 |
| Título | Etiqueta «Cliente:» y negrita en encabezado de grupo vía Smart Capture |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Especificado** (2026-11-21) |
| Jira | PIAPARTES-69 (Epic) |
| SPEC previa | [Oleada 6 agrupación CHK-18](./SPEC-015-ia-diseno-informes-oleada-6-agrupacion-chk18-update.md) |
| HU | [HU-015-update-h](../../../03-historias-usuario/100-SistemaPartes/HU-015-update-h-group-header-cliente-chk19.md) |
| TR | [TR-015-update-h](../../../04-tareas/100-SistemaPartes/TR-015-update-h-group-header-cliente-chk19.md) |
| Matriz | Framework `15-reportes-emisiones-ia-checklist-matriz.json` — **CHK-19** |
| Proceso piloto | `partes.informes.consultaDetallada` |

**Plantilla regla 19:** GEN-03 + GEN-15; mutación REPX en sidecar Framework; mismo planner/heurística que CHK-18 (`addGroupByField` / `clienteNombre`). No apply PHP nuevo.

---

## 1. Resumen

El usuario pide **«Agrupá por cliente»** (mismo prompt canónico que CHK-18). El planner no cambia. El sidecar debe presentar el **group header** con expresión legible **`'Cliente: ' + [clienteNombre]`**, fuente **negrita** (`Font=… style=Bold` en REPX), y campo de datos **`clienteNombre`** en la expresión — **no** el prefijo técnico `clienteNombre:`.

Si el informe ya está agrupado (CHK-18) pero el label del header conserva la expresión antigua → **reparar** caption/estilo sin romper `GroupField` ni footer. Si caption y estilo ya cumplen matriz → noop **`ya_agrupado`** (misma semántica CHK-18).

---

## 2. GEN adoptados

| GEN | Rol |
|-----|-----|
| GEN-03 | Turno Smart Capture (mismas acciones que CHK-18) |
| GEN-15 | Diseñador + Guardar/Descartar |
| GEN-16 | BYOK opcional; heurística obligatoria para prompt canónico |

---

## 3. Alcance

### 3.1 En alcance (CHK-19)

| CHK | Prompt | Acción | assert REPX |
|-----|--------|--------|-------------|
| CHK-19 | Agrupá por cliente | `addGroupByField` `{ field: clienteNombre }` | `Cliente:`, `clienteNombre`, `Font=` |

**Presentación header (nuevo/ reparado):**

- Expresión BeforePrint: `'Cliente: ' + [clienteNombre]` (literal «Cliente:» + valor del campo).
- `XRLabel` en group header: **Arial 10pt Bold** (subrayado **no** requerido MVP — notas matriz).
- Prefijo legible derivado del `label` de schema (`Cliente (nombre)` → prefijo `Cliente:`); para `clienteNombre` el resultado canónico es **Cliente:**.

**Layout eval:** layout con agrupación existente post CHK-18 (p. ej. `partes_consultaDetallada_principal` tras apply CHK-18) o apply inicial desde layout sin grupo (debe crear header **ya** con copy CHK-19).

**Idempotencia:**

- `details` incluye `noop:addGroupByField:ya_agrupado` solo si header+footer emparejados **y** caption/estilo CHK-19 ya OK.
- Si agrupación existe pero falta label o expresión/estilo incorrectos → **apply muta** (`applied >= 1`), **sin** noop.

**Reparación CHK-18 → CHK-19:** informes agrupados en oleada 6 con `'{field}: ' + [field]` se corrigen en el mismo `addGroupByField` sin duplicar bandas.

### 3.2 Fuera de alcance

- CHK-20 (`addGroupSum` / «Total minutos»)
- CHK-21 (segundo nivel de agrupación)
- CHK-08-format, CHK-27 en esta corrida
- Nuevo tipo de acción planner; cambios matriz CHK-18 expectedActions
- Subrayado obligatorio en group header (diferido; matriz lo marca opcional MVP)

---

## 4. Criterios de aceptación

| ID | Criterio |
|----|----------|
| CA-B7-01 | Apply inicial o reparación: REPX contiene `Cliente:`, `clienteNombre`, `Font=` (negrita) |
| CA-B7-02 | Apply en layout limpio: header CHK-19 + footer CHK-18 (sin regresión bandas) |
| CA-B7-03 | Re-apply con caption/estilo ya correctos → noop `ya_agrupado` + mensaje i18n existente |
| CA-B7-04 | Re-apply tras layout CHK-18 legacy (expresión técnica) → repara sin error HTTP |
| CA-B7-05 | L-B-10 sin regresión (banner Guardar/Descartar) |
| CA-B7-06 | Script `eval-emission-ia-oleada7-chk19-apply.ps1` PASS |
| CA-B7-07 | `dotnet test --filter LayoutGroupByField` y `phpunit --filter DesignLayout` sin regresión CHK-18 |

---

## 5. Contrato sidecar (extensión oleada 7)

Semántica de **`addGroupByField`** (mismo `type` que CHK-18):

- Al **crear** group header: expresión y estilo según §3.1 (alineado a spike `spike-layout-b-1.repx`).
- Al **encontrar** `GroupHeader` con `GroupField` = campo y footer emparejado:
  - Si label principal del header no cumple expresión/estilo → actualizar bindings y `Font`.
  - Si cumple → `LayoutApplyStepResult(false, ya_agrupado)`.
- Pie de grupo sin header: sin cambio respecto CHK-18 (reparación footer).

Implementación: `LayoutGroupByFieldApplier.cs` (+ tests).

---

## 6. Frontera con CHK-18

| Aspecto | CHK-18 | CHK-19 |
|---------|--------|--------|
| Planner | `addGroupByField` / `clienteNombre` | Igual |
| Bandas | Header + footer 28px | Sin cambio estructural |
| Copy header | Técnico `clienteNombre:` (estado previo) | **Cliente:** legible |
| Noop | header+footer | header+footer **+ caption OK** |

Noop CHK-18 en layouts ya correctos CHK-19 debe seguir siendo noop (regresión prohibida).