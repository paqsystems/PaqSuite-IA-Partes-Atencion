# SPEC-015-update (oleada 3) – Apariencia de controles vía Smart Capture

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-3-apariencia |
| Título | Estilos de controles en diseñador de emisiones (whitelist `setControlAppearance`) |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Finalizado** |
| SPEC base / oleada 2 | [SPEC-015 oleada 2 B](./SPEC-015-ia-diseno-informes-oleada-2-b-update.md) |
| HU | [HU-015-update-c](../../03-historias-usuario/100-SistemaPartes/HU-015-update-c-apariencia-controles-informes.md) |
| TR | [TR-015-update-c](../../04-tareas/100-SistemaPartes/TR-015-update-c-apariencia-controles-informes.md) |
| Contrato Framework | [15-reportes-emisiones-update-4-ia-diseno.md](../../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-update-4-ia-diseno.md) §acciones layout |
| Limitaciones | [20-ia-diseno-informes-limitaciones-mvp-b](../../../02-producto/Sistema-Partes-IA/20-ia-diseno-informes-limitaciones-mvp-b.md) (L-B-02 parcial cerrado para apariencia) |

**Plantilla regla 19:**  
Diseño informes Partes oleada 3: adoptar **GEN-03** + **GEN-15**; nueva acción whitelist **`setControlAppearance`** (apply sidecar Framework); planner y sinónimos de color en host. SoT: este SPEC + matriz CHK. Host: `partes.informes.consultaDetallada`.

---

## 1. Resumen

- **Problema:** el usuario puede cambiar título, agrupación y orientación, pero **no** pide formato visual (fuente, color, alineación) sobre controles existentes (CHK-08 parcial).
- **Resultado:** turno Smart Capture traduce pedidos de estilo a **`setControlAppearance`**; el sidecar muta XRLabel (u objetivo resuelto) en REPX; el usuario revisa y guarda con GEN-15 (L-B-10).

---

## 2. GEN adoptados (sin GEN nuevo)

| GEN | Rol |
|-----|-----|
| GEN-03 | Turno + panel |
| GEN-15 | Diseñador + Save |
| GEN-16 | BYOK |
| GEN-37 | Resolución campo semántico → `fieldName` técnico |

---

## 3. Alcance oleada 3

### 3.1 En alcance (v1 apariencia)

- Acción **`setControlAppearance`** (alias `patchControlStyle` en apply).
- **Target:** `reportTitle` (`lblReportTitle`), `field` (binding `[campo]`), `controlName`.
- Propiedades: `fontFamily`, `fontSizePt`, `bold`, `italic`, `underline`, `textAlign`, `verticalAlign`, `foreColor`, `backColor`, `textAngle` (si el control lo admite).
- Colores por **nombre natural** resueltos en **host** (hex en payload); sidecar acepta `#RRGGBB` y `rgb(r,g,b)`.
- Proceso piloto: `partes.informes.consultaDetallada`.
- CHK: **CHK-08-format**, **CHK-27** (matriz Framework).

### 3.2 Fuera de alcance (oleada 3b+)

- padding, bordes, visible, wordWrap, `styleName` global.
- Agregar controles/bandas (CHK-01…07 siguen diferidos).
- Mobile, REPX crudo, auto-save.

---

## 4. Reglas

| ID | Regla |
|----|--------|
| R-B3-01 | Solo acción whitelist; rechazar payload sin al menos una propiedad de apariencia. |
| R-B3-02 | `target.fieldName` ∈ schema del `processCode`. |
| R-B3-03 | Sin controles resueltos → apply `noop` o error claro; no inventar label. |
| R-B3-04 | Estilo **inline** en control (no reasignar `StyleName` salvo TR futura). |
| R-B3-05 | Persistencia solo vía Save GEN-15 (L-B-10). |

---

## 5. Criterios verificables

- [x] **CA-B3-01** Planner heurístico mapea CHK-27 y CHK-08-format (PHPUnit matriz).
- [x] **CA-B3-02** Apply sidecar muta REPX (`Times New Roman`, alineación, flags fuente) — evidencia .NET + script `eval-emission-ia-oleada3-apply.ps1` con sidecar.
- [x] **CA-B3-03** Cambio visible en diseñador tras apply + banner Guardar/Descartar — [D-CIERRE oleada3](../../../04-tareas/updates/100-SistemaPartes/D-CIERRE-oleada3-apariencia-2026-11-18.md).
- [x] **CA-B3-04** Campo fuera de schema → acción omitida en sanitize.

---

## 6. SDD

| Parte | Estado |
|-------|--------|
| A (este doc) | Hecho |
| B/C HU/TR | Hecho (oleada 3) |
| D/E | Hecho (2026-11-18) |
| F1 auto | Hecho — [D-VERIFICACION-TR-015-update-c-2026-11-18.md](../../../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-c-2026-11-18.md) |
| F1 smoke UI | **Hecho** (2026-11-18) |
| I Finalizado | **Finalizado** |

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-18 | Oleada 3 apariencia — SPEC inicial |