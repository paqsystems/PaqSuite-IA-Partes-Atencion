# SPEC-015-update (oleada 3b) — Formato del título (CHK-08-format)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-3b-formato-titulo-chk08 |
| Título | Negrita, subrayado y centrado del título vía Smart Capture |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Implementando** (2026-11-21) |
| Jira | [PIAPARTES-66](https://paqsystems.atlassian.net/browse/PIAPARTES-66) (Epic) |
| SPEC previa | [Oleada 3 apariencia](./SPEC-015-ia-diseno-informes-oleada-3-apariencia-update.md) |
| HU | [HU-015-update-g](../../../03-historias-usuario/100-SistemaPartes/HU-015-update-g-formato-titulo-chk08.md) |
| TR | [TR-015-update-g](../../../04-tareas/100-SistemaPartes/TR-015-update-g-formato-titulo-chk08.md) |
| Matriz | Framework `15-reportes-emisiones-ia-checklist-matriz.json` — **CHK-08-format** |
| Proceso piloto | `partes.informes.consultaDetallada` |

**Plantilla regla 19:** GEN-03 + GEN-15; mutación REPX en sidecar Framework; heurística MUST en host Partes (`setControlAppearance`). No `setReportTitle` en esta corrida.

---

## 1. Resumen

Prompt **`Poné el título en negrita, subrayado y centrado`** → `setControlAppearance` con `target.kind: reportTitle`. El sidecar aplica estilo al **`lblReportTitle` visible** en page header (fila 2 / `pnlReportTitle` post CHK-05) y en **report header** legacy. Re-aplicar con estilos ya cumplidos → noop `ya_formateado` + mensaje Smart Capture.

---

## 2. GEN adoptados

| GEN | Rol |
|-----|-----|
| GEN-03 | Turno Smart Capture |
| GEN-15 | Diseñador + Guardar/Descartar |
| GEN-16 | BYOK opcional; heurística obligatoria para prompt canónico |

---

## 3. Alcance

### 3.1 En alcance (CHK-08-format)

| CHK | Prompt | Acción | assert REPX |
|-----|--------|--------|-------------|
| CHK-08-format | Poné el título en negrita, subrayado y centrado | `setControlAppearance` reportTitle + bold/underline/center | `lblReportTitle`, `style=Bold, Underline`, `MiddleCenter` |

**Layout eval:** `spike-layout-b-1` (backup con título sin formato completo).

**Idempotencia:** `noop:setControlAppearance:ya_formateado`; clave `partes.emissionDesign.appearance.titleAlreadyFormatted`.

### 3.2 Fuera de alcance

- CHK-08 texto (`setReportTitle` / «cambiá el título a …»)
- CHK-27 (apariencia campo cliente)
- CHK-05 en esta corrida (solo frontera: no romper panel)

---

## 4. Criterios de aceptación

| ID | Criterio |
|----|----------|
| CA-B3b-01 | Matriz / heurística: prompt canónico → `setControlAppearance` reportTitle |
| CA-B3b-02 | Apply resuelve título en page header (anidado) y report header legacy |
| CA-B3b-03 | Re-apply: noop `ya_formateado` + mensaje i18n SC |
| CA-B3b-04 | L-B-10 sin regresión tras apply exitoso |
| CA-B3b-05 | Script `eval-emission-ia-chk08-format-apply.ps1` PASS (sidecar) |

---

## 5. Contrato sidecar

Extensión de **`setControlAppearance`** (`target.kind: reportTitle`):

- Targets: todos los `lblReportTitle` en `PageHeaderBand` (recursivo) + `ReportHeaderBand` (sin duplicar instancia).
- Fallback sin label: scaffolding `setReportTitle` (comportamiento oleada 3).
- Noop: ningún control necesita cambio de apariencia según payload.