# Cierre F — CC PQ #2 (05/06/2026) — GEN-03 layouts + export

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


## Alcance

Verificación **F1 + F** sobre correcciones derivadas del Control de Calidad #2:

| TR | HU |
|----|-----|
| [TR-GEN-03-layouts-grilla](TR-GEN-03-layouts-grilla.md) | [HU-GEN-03-layouts-grilla](../../03-historias-usuario/001-Generalidades/HU-GEN-03-layouts-grilla.md) |
| [TR-GEN-03-exportaciones](TR-GEN-03-exportaciones.md) | [HU-GEN-03-exportaciones](../../03-historias-usuario/001-Generalidades/HU-GEN-03-exportaciones.md) |

**Fecha verificación:** 09/06/2026  
**Parte I (unificación HU/TR):** 09/06/2026  
**Rama / build:** `v1.1.0-paq` (working tree local)

---

## F1 — Verificación del agente (evidencia código + tests)

**Resultado F1:** **Aprobado con observaciones**

### TR-GEN-03-layouts-grilla (AC-12 / AC-13)

| AC / RN | Evidencia código | Estado |
|---------|------------------|--------|
| AC-12 sufijo ` (*)` propios | `GridLayoutToolbar.tsx` + i18n `gridLayout.ownerMarker` (5 locales) | OK |
| AC-13 plantilla sistema reset | `DataGridDx.tsx` → `instance.state(null)` sin early-return | OK |
| AC-07 Guardar → Guardar como en plantilla | `useGridLayouts.tsx` `isSystemTemplate` → `setSaveAsOpen(true)` | OK (preexistente) |
| RN-08 / RN-09 | `08-devextreme-grid-standards.md` §1.11, `grillas.md` | OK |

### TR-GEN-03-exportaciones (AC-11)

| AC / RN | Evidencia código | Estado |
|---------|------------------|--------|
| Fechas según locale | `excelExportFormatting.ts` → `resolveExcelDateNumFmt` | OK |
| Enteros sin decimales | `applyFormattedNumberStyle` + `isIntegerColumnFormat` | OK |
| Decimales por `column.format` | `resolveDecimalNumFmt` | OK |
| Booleanos VERDADERO/FALSO | `formatBooleanExportValue` + i18n 5 locales | OK |
| Encabezados gris + negrita | `formattedHeaderFill` + `applyFormattedHeaderStyle` | OK |
| Totales pie | `totalFooter` / `groupFooter` en `customizeFormattedExportCell` | OK |
| RN-04 básica distinta | `customizeBasicExportCell` limpia `numFmt`/negrita; `autoFilterEnabled: false` | OK |

---

## F — Verificación documental (openspec-05 / TR ↔ código)

**Resultado F:** **Aprobado con observaciones**

### Coherencia docs ↔ implementación

| Documento | Alineado |
|-----------|----------|
| TR layouts + export (base, post Parte I) | Sí |
| `grillas.md` § layouts + export formateada | Sí |
| `08-devextreme-grid-standards.md` §1.11 + §1.13 | Sí |
| HU base (ambas) | Sí — Parte I 09/06/2026 |
| CC #2 `00-ControlCalidad-PQ.md` | **Finalizado (Parte I)** |

### Evidencia tests ejecutados (09/06/2026)

| Comando | Resultado |
|---------|-----------|
| `npm run test` — `src/shared/ui/gridExport/` | **8 passed** |
| `npm run build` (frontend) | **OK** |
| `php artisan test --filter=GridLayout` | **6 passed** |
| `npm run test:e2e` — `grid-export.spec.ts`, `grid-layouts.spec.ts` | **6 passed** (E2E en `/demo/abm`, `/demo/export-empty`, `/consultas/historial`) |

### QA manual (PQ)

| Ítem | Estado |
|------|--------|
| Excel básica vs formateada distinguibles | OK (PQ 09/06/2026) |
| Reset plantilla del sistema | OK (PQ 09/06/2026) |

### Observaciones no bloqueantes

| Ítem | Motivo |
|------|--------|
| E2E contenido xlsx (diff básica/formateada) | No existe aún — validación manual suficiente en CC #2 |
| AC-13 reset plantilla — E2E automatizado columna a columna | Cubierto por QA manual; E2E valida sufijo y toolbar |

---

## Parte I — Unificación (09/06/2026)

Updates fusionados en HU/TR base; archivos `*-update.md` eliminados.

| Origen update | Destino unificado |
|---------------|-------------------|
| `HU-GEN-03-layouts-grilla-update` | `HU-GEN-03-layouts-grilla.md` (CA-12, CA-13, RN-10/11) |
| `HU-GEN-03-exportaciones-update` | `HU-GEN-03-exportaciones.md` (CA-11, RN-06/07) |
| `TR-GEN-03-layouts-grilla-update` | `TR-GEN-03-layouts-grilla.md` (AC-12/13, RN-08/09, Historial) |
| `TR-GEN-03-exportaciones-update` | `TR-GEN-03-exportaciones.md` (AC-11, RN-03/04, Historial) |

---

## Veredicto final

| Slice | F1 | F | Nota |
|-------|----|---|------|
| Layouts CC #2 | Aprobado con observaciones | Aprobado con observaciones | Parte I cerrada |
| Export CC #2 | Aprobado con observaciones | Aprobado con observaciones | QA manual PQ OK |

**Estado CC #2:** **Finalizado (Parte I 09/06/2026)**






