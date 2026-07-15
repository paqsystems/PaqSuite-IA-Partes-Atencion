# TR-GEN-03-exportaciones — Exportación Excel desde grillas

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-03-exportaciones](../../03-historias-usuario/001-Generalidades/HU-GEN-03-exportaciones.md) |
| **SPEC relacionada** | [SPEC-001-03-ui-transversal](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-03-grillas-listados; TR-GEN-03-layouts-grilla |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-09 (Parte I — CC PQ #2) |

**Cierre F formal:** [F-GEN-03-cierre-formal](F-GEN-03-cierre-formal.md)

**Origen:** [HU-GEN-03-exportaciones](../../03-historias-usuario/001-Generalidades/HU-GEN-03-exportaciones.md)  
**Referencia SPEC:** [SPEC-001-03-ui-transversal](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md)  
**Contexto:** [`exportaciones.md`](../../00-contexto/_mono/03-ui-transversal/exportaciones.md), [`grillas.md`](../../00-contexto/_mono/03-ui-transversal/grillas.md) § Exportación

---

## 1) HU Refinada (resumen)

### Título
Exportar a Excel la vista vigente de la grilla (básica / formateada).

### In scope / Out of scope
- **In scope:** botón Exportar en toolbar `DataGridDx`, modalidades básica y formateada (default formateada), **deshabilitado si la grilla está vacía**, respeta filtros/orden/layout activo; guardado con **diálogo del sistema** cuando el navegador lo permita; si no, **descarga silenciosa** según el navegador con **nombre por defecto sugerido**.
- **Out of scope:** PDF (SPEC-001-06), batch, pivot export.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Botón Exportar visible en toolbar superior de grillas exportables.
- **AC-02**: **Grilla vacía** (sin filas en el listado) → botón Exportar **deshabilitado** + mensaje i18n (`gridExport.noData`).
- **AC-03**: Usuario elige básica o formateada (DropDown o submenú DX).
- **AC-04**: Exportación usa columnas/filtros/orden del estado actual + layout activo.
- **AC-05**: Default modalidad formateada.
- **AC-06**: Si el navegador expone diálogo de guardado (`showSaveFilePicker`), el usuario elige **carpeta y nombre**; el nombre por defecto sugerido es `{proceso}_{yyyyMMdd_HHmm}.xlsx`.
- **AC-07**: Si **no** hay intervención posible (sin File System Access API o usuario cancela el picker), **descarga silenciosa** según parámetros del navegador usando el **nombre por defecto sugerido** (mismo patrón que AC-06).
- **AC-08**: Mismos permisos que ver la grilla (`exportEnabled` prop ligada a permiso proceso).
- **AC-09**: `data-testid="gridExportExcel"`.
- **AC-10**: E2E: exportar dashboard/demo completa flujo guardar (o stub del diálogo en entorno de test).
- **AC-11**: Modalidad **formateada** diferenciada de básica: fechas según locale, enteros sin decimales, decimales según `column.format` (fallback 2), booleanos VERDADERO/FALSO (i18n), encabezados negrita + fondo gris (#D9D9D9), totales pie con formato numérico.

---

## 3) Reglas de Negocio

1. **RN-01**: Exportar en misma franja que layouts y Agregar.
2. **RN-02**: Base = vista visible (incluye layout cargado).
3. **RN-03**: **Formateada** aplica estilos Excel por tipo de dato según `exportaciones.md` y `excelExportFormatting.ts` (fechas locale, enteros, decimales, booleanos i18n, encabezados gris, totales pie).
4. **RN-04**: **Básica** exporta valores sin formato avanzado; debe **limpiar** estilos que DevExtreme aplica por defecto (`numFmt`, negrita, `autoFilter`) para no confundirse con formateada.
5. **RN-05**: MVP: alcance **página actual** salvo que TR de proceso documente “dataset completo” con límite.
6. **RN-06** (**nombre y guardado del archivo**):  
   - **Nombre por defecto sugerido:** `{proceso}_{yyyyMMdd_HHmm}.xlsx` (sanitizar `proceso`; zona horaria del cliente o acordada en implementación).  
   - **Con diálogo disponible** (`showSaveFilePicker`): el usuario elige carpeta y nombre; se prellena el sugerido.  
   - **Sin posibilidad de intervención** (API no disponible, error del picker o entorno que no lo soporta): **descarga silenciosa** acorde a la configuración del navegador, con el **nombre por defecto sugerido** (p. ej. `exportDataGrid({ fileName })` o blob + anchor download).  
7. **RN-07**: **Grilla vacía** → botón Exportar **inhabilitado** (misma condición que el estado vacío del `DataGridDx`: sin filas en el listado tras la carga).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-01)

**Fuentes revisadas:** HU-GEN-03-exportaciones, SPEC-001-03, `exportaciones.md`, `grillas.md` § Exportación, TR-GEN-03-grillas-listados, TR-GEN-03-layouts-grilla (layout activo).

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (aplicar resoluciones §3.2)

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|------------------------------|
| AMB-C01 | **AC-06 diálogo vs fallback** | ¿Qué hacer sin `showSaveFilePicker`? | **Cerrado (humano):** sin intervención posible → **descarga silenciosa** del navegador con **nombre por defecto sugerido**. Con picker → usuario elige carpeta/nombre. |
| AMB-C02 | **Cuándo deshabilitar Exportar** | Criterio “sin datos” ambiguo | **Cerrado (humano):** **inhabilitar** el botón si la **grilla está vacía** (sin filas en el listado / estado vacío del `DataGridDx`). |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | E2E §8 “download event” | Coherente con fallback; si se usa picker, **mock** `showSaveFilePicker` y assert `suggestedName`. |
| AMB-M02 | `exportEnabled` vs `Permiso_Repo` | Default: `exportEnabled = permiso consulta del proceso` (mismo gate que ver grilla). |
| AMB-M03 | `gridId` en nombre archivo | Opcional en helper si hay varias grillas exportables en un proceso. |
| AMB-M04 | Alcance página actual (RN-05) | `exportDataGrid` sin `customizeExcelCell` de dataset completo; documentar en JSDoc. |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| `grillas.md` “nombre descriptivo proceso+fecha” vs diálogo usuario | Coherente: **sugerido** en diálogo, no impuesto (RN-06). |
| AC-06 exigía siempre diálogo | RN-06 / R-C1-01: diálogo **si hay** picker; si no, descarga silenciosa con nombre sugerido. |

### Supuestos detectados

- Export **client-side** (sin endpoints) en cierre MVP.
- `exceljs` / módulo export DX disponible en build release.

### Preguntas para decisión humana

(Ninguna bloqueante — cerradas en §3.2.)

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-01)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Guardar archivo (**AMB-C01**) | `showSaveFilePicker` cuando exista; si no hay intervención posible → **descarga silenciosa** del navegador con nombre `{proceso}_{fecha}.xlsx`. |
| R-C1-02 | Grilla vacía (**AMB-C02**) | Botón Exportar **deshabilitado** si la grilla está vacía (sin filas / estado vacío del wrapper). |
| R-C1-03 | E2E | Chromium: mock picker **o** assert `download` con `suggestedFileName` en fallback. |
| R-C1-04 | Permisos | `exportEnabled` ligado a permiso de consulta del proceso. |

---

## 3.3) Plan D1 — Implementación (2026-06-01)

**Depende de:** TR-GEN-03-grillas-listados + TR-GEN-03-layouts-grilla (estado layout en grid para AC-04).

### Alcance entendido

`GridExportButton` + `exportDataGridExcel` / `saveExcelWithPicker`: modalidades básica/formateada, deshabilitado si grilla vacía, guardado con picker o descarga silenciosa con nombre sugerido, integrado en `toolbarEnd` después de layouts.

### Decisiones D1

| ID | Decisión |
|----|----------|
| D1-1 | Dependencia: agregar **`exceljs`** (y verificar peer de export DX 25.x) |
| D1-2 | `buildSuggestedExportFileName(proceso, gridId?)` |
| D1-3 | `isGridEmpty` desde ref/callback `DataGridDx` (`getVisibleRows().length === 0` o prop explícita) |
| D1-4 | Picker → fallback `exportDataGrid({ fileName })` (R-C1-01 humano) |
| D1-5 | E2E: mock `showSaveFilePicker` **o** assert evento `download` + nombre sugerido |

### Orden de trabajo

1. `exportMode.ts`, `buildSuggestedExportFileName.ts`
2. `exportDataGridExcel.ts` + `saveExcelWithPicker.ts`
3. `GridExportButton.tsx` (DropDownButton DX: básica / formateada)
4. Integrar en `DataGridDx` tras `GridLayoutToolbar`
5. i18n `gridExport.*`
6. Unit tests helper nombre
7. E2E export + grilla vacía deshabilitada

### Confirmación de alcance

**Sí** — client-side only; sin PDF ni POST `/grid-export` en MVP.

---

## 4) Impacto en Datos

- Sin tablas nuevas.
- Exportación **client-side** vía DevExtreme `exportDataGrid` (recomendado MVP) o endpoint backend si volumen lo exige — **default: cliente**.

---

## 5) Contratos de API y OpenAPI

### Default MVP (client-side)

**Sin endpoints** — exportación en browser con `exceljs` / mecanismo DX.

### Alternativa (Should si performance lo exige — fuera cierre mínimo)

| Método | Path | Permiso |
|--------|------|---------|
| POST | `/api/v1/grid-export` | `Permiso_Repo` + permiso proceso |

**Body:** `{ proceso, gridId, mode: "basic"|"formatted", queryState }`  
**Response:** archivo binario o URL temporal.

> No implementar en primer cierre salvo bloqueo técnico documentado.

---

## 6) Cambios Frontend

### Componentes

```text
frontend/src/features/gridExport/
  exportDataGridExcel.ts      # wrapper exportDataGrid DX
  components/GridExportButton.tsx
  model/exportMode.ts           # 'basic' | 'formatted'
```

### Integración `DataGridDx`

- Prop `exportEnabled?: boolean` (default true en listados).
- Slot toolbar: `GridExportButton` junto a `GridLayoutToolbar`.
- Generar blob/workbook con `exportDataGrid` (o equivalente DX + `exceljs`).
- **Con picker:** `showSaveFilePicker({ suggestedName, ... })` → escribir blob al handle.
- **Sin intervención:** `exportDataGrid({ fileName: suggestedName })` o descarga programática equivalente (**silenciosa**, nombre sugerido).
- Prop `isGridEmpty` o derivar del `DataGridDx` (`rowCount === 0` tras carga) → `GridExportButton` **disabled**.
- Helper `buildSuggestedExportFileName(proceso, gridId?)` — `gridId` opcional si un proceso tiene varias grillas exportables.

### Modalidad formateada (mínimo)

- Encabezados en negrita + fondo gris (#D9D9D9).
- Fechas con `numFmt` según locale activo (i18n).
- Enteros sin decimales; decimales según `column.format` (fallback 2).
- Booleanos como VERDADERO/FALSO (`gridExport.boolean.*` i18n).
- Totales de pie (`totalFooter` / `groupFooter`) con negrita y formato numérico.
- Implementación: `excelExportFormatting.ts`, `exportDataGridExcel.ts`, `GridExportButton.tsx`.

### Grilla vacía (AMB-C02)

- Si la grilla está **vacía** (sin filas en el listado, alineado al estado vacío de `DataGridDx`): botón Exportar **`disabled`** + `hint` i18n `gridExport.noData`.
- Reevaluar al cambiar `dataSource` / tras refresh de la grilla.

### data-testid

- `gridExportExcel`, `gridExportModeBasic`, `gridExportModeFormatted`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | `exportDataGridExcel` + `saveExcelWithPicker` (sugerido + diálogo Guardar) | AC-04, AC-06, AC-07 |
| T2 | Frontend | `GridExportButton` con selector modalidad | AC-03, AC-05 |
| T3 | Frontend | Integrar en `DataGridDx` toolbar | AC-01 |
| T4 | Frontend | Exportar deshabilitado si grilla vacía | AC-02, RN-07 |
| T5 | i18n | `gridExport.*` | |
| T6 | Tests | E2E export con filas + grilla vacía deshabilitada | AC-10 |

---

## 8) Estrategia de Tests

- **Unit:** `buildSuggestedExportFileName(proceso)` sanitización y formato.
- **E2E:** `grid-export.spec.ts` en `/demo/abm` (export habilitado, descarga formateada) y `/demo/export-empty` (deshabilitado).

---

## 9) Riesgos y Edge Cases

- Export cliente con muchas filas en página grande → mantener MVP en página actual.
- Licencia DX export: verificar módulo `exceljs` incluido en build release.
- Layout activo con columnas ocultas → exportar solo visibles (comportamiento DX esperado).

---

## 10) Verificación F formal (2026-06-01)

- **Tests:** E2E `grid-export.spec.ts` 3 OK; unit `buildSuggestedExportFileName` OK
- **QA manual:** export formateada y grilla vacía con botón deshabilitado

**CC PQ #2 (05/06/2026) — F1/F:** [F-CC-PQ-02-GEN-03-cierre-formal](F-CC-PQ-02-GEN-03-cierre-formal.md) — Aprobado con observaciones (09/06/2026). E2E en `/demo/abm` y `/demo/export-empty`; unit `excelExportFormatting.test.ts`.

---

## 11) Checklist final

- [x] PDF no incluido (remisión SPEC-001-06 documentada en HU)
- [x] Módulo `gridExport` integrado en `DataGridDx` toolbar

---

## Historial CC PQ #2 (05/06/2026) — Parte I 09/06/2026

Corrección export Excel formateada vs básica.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | Helpers formato por tipo de dato | `excelExportFormatting.ts` |
| T2 | `customizeCell` formateada + limpieza básica | `exportDataGridExcel.ts` (`autoFilterEnabled: false` en básica) |
| T3 | Booleanos i18n 5 locales | `gridExport.boolean.true/false` |
| T4 | Reglas estándar | `08-devextreme-grid-standards.md` §1.13, `grillas.md` |
| T5 | Tests unit + E2E | `excelExportFormatting.test.ts`, `grid-export.spec.ts` |

---

## Orden en bloque GEN-03

**4/4** — Requiere `DataGridDx` + layout activo (TR-GEN-03-layouts-grilla) para AC-04 completo.










