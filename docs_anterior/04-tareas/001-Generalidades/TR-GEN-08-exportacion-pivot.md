# TR-GEN-08-exportacion-pivot — Exportación Excel desde pivot

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-08-exportacion-pivot](../../03-historias-usuario/001-Generalidades/HU-GEN-08-exportacion-pivot.md) |
| **SPEC relacionada** | [SPEC-001-08-pivots](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Could |
| **Dependencias** | TR-GEN-08-pivotgrid-visualizacion; TR-GEN-08-layouts-pivot; TR-GEN-03-exportaciones |
| **Estado** | Pendiente de programación (2026-06-11) |
| **Última actualización** | 2026-06-11 (D1 exportacion-pivot) |

**Origen:** [HU-GEN-08-exportacion-pivot](../../03-historias-usuario/001-Generalidades/HU-GEN-08-exportacion-pivot.md)  
**Referencia SPEC:** [SPEC-001-08-pivots](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md)  
**Contexto:** [pivots.md](../../00-contexto/_mono/03-ui-transversal/pivots.md) § Exportación, [TR-GEN-03-exportaciones](TR-GEN-03-exportaciones.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Exportar vista pivot a Excel (básica y tabla dinámica).

### In scope / Out of scope
- **In scope:** botón Exportar en toolbar pivot, modalidades básica y tabla dinámica según metadata, deshabilitado sin datos, permisos = consulta, nombre sugerido, metadatos opcionales.
- **Out of scope:** export grilla tabular (TR-GEN-03), PDF, batch server-side.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Exportar visible en toolbar pivot cuando modo pivot activo.
- **AC-02**: Modalidad **básica** exporta matriz visible + totales (`excelBasicoHabilitado`).
- **AC-03**: Modalidad **tabla dinámica** solo en modo pivot y si `excelFormateadoHabilitado`.
- **AC-04**: Sin `excelFormateadoHabilitado` no se ofrece tabla dinámica.
- **AC-05**: Sin datos exportables → botón deshabilitado + `pivotExport.noData` i18n.
- **AC-06**: Export respeta filtros generales vigentes (misma base que refresh).
- **AC-07**: Con `incluirMetadatos`, hoja/sección con consulta, usuario, diseño activo.
- **AC-08**: Mismo permiso que ver consulta (`exportEnabled` / `Permiso_Repo`).
- **AC-09**: `data-testid="pivotExport"` (o `{prefix}Export`).
- **AC-10**: Nombre sugerido `{consultaId}_{yyyyMMdd_HHmm}.xlsx` (paridad RN-06 TR-GEN-03).
- **AC-11**: E2E: export básica descarga archivo en consulta piloto.

### Escenarios Gherkin

(Heredados de HU.)

---

## 3) Reglas de Negocio

1. **RN-01**: Tabla dinámica solo con vista pivot activa.
2. **RN-02**: Básica prioriza datos sobre estructura interactiva completa.
3. **RN-03**: Flags metadata deshabilitan modalidades.
4. **RN-04**: Permisos = consulta visible (paridad TR-GEN-03-exportaciones).
5. **RN-05**: Sin datos → botón deshabilitado.
6. **RN-06**: Nombre archivo sugerido + `showSaveFilePicker` o descarga silenciosa (heredar TR-GEN-03).
7. **RN-07**: `incluirFiltrosAplicados` agrega sección filtros si metadata true.
8. **RN-08**: PDF (`pdfHabilitado`) **no** implementar en v1.
9. **RN-09**: Toolbar: después de Actualizar y diseños guardados.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-11)

**Fuentes revisadas:** HU-GEN-08-exportacion-pivot, SPEC-001-08, `pivots.md` § Exportación, TR-GEN-03-exportaciones §3.1–3.2, TR-GEN-08-pivotgrid-visualizacion, metadata `exportacion` §16 especificación técnica.

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (última TR del epic; depende de pivotgrid + layouts montados)

### Ambigüedades críticas

| ID | Tema | Riesgo | Estado | Qué hacer (D1 / código) |
|----|------|--------|--------|-------------------------|
| AMB-C01 | **Client vs server export** | Complejidad backend innecesaria | **Cerrado** (R-C1-01) | Export **client-side** (paridad TR-GEN-03); sin endpoint v1. |
| AMB-C02 | **Tabla dinámica DX** | Expectativa Excel pivot completo | **Cerrado** (R-C1-02) | Usar `exportToExcel` pivot DX; si jerarquía incompleta → aviso i18n `pivotExport.pivotTableLimited` + manual. |
| AMB-C03 | **Convivencia grilla/pivot** | Dos botones export activos | **Cerrado** (R-C1-03) | Modo grilla: solo `gridExportExcel` (GEN-03); modo pivot: solo `pivotExport`. |
| AMB-C04 | **Pivot vacío** | Export de matriz sin celdas | **Cerrado** (R-C1-04) | Deshabilitar + `pivotExport.noData` (paridad `gridExport.noData`). |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M01 | Hoja metadatos | Pestaña `_meta` si `incluirMetadatos`; columnas consulta, usuario, diseño, fecha. |
| AMB-M02 | Alcance filas | Vista pivot visible / dataset cargado (no paginación servidor extra). |
| AMB-M03 | Nombre archivo | `{consultaId}_{yyyyMMdd_HHmm}.xlsx` sanitizado. |
| AMB-M04 | Modalidad default UI | `DropDownButton` sin auto-export; usuario elige básica o tabla dinámica. |
| AMB-M05 | `excelFormateadoHabilitado` | Mapea a ítem UI «Tabla dinámica» (nombre i18n `pivotExport.pivotTable`). |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| SPEC metadata `excelFormateadoHabilitado` vs texto HU «tabla dinámica» | Mismo flag; etiqueta UI vía i18n. |
| TR-GEN-03 default formateada vs pivot sin default | Pivot: usuario elige modalidad en menú (sin default automático al clic). |
| Básica disponible en modo grilla | **No** — básica pivot solo con vista pivot activa (RN-01). |

### Supuestos detectados

- DevExtreme export pivot disponible en versión licenciada del proyecto.
- `exportEnabled` hereda permiso consulta (`Permiso_Repo` host).
- Helpers `showSaveFilePicker` / fallback descarga reutilizables de TR-GEN-03.

### Preguntas para decisión humana

(Ninguna bloqueante.)

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-11)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Implementación | Client-side; sin endpoint v1 |
| R-C1-02 | Tabla dinámica | Export DX nativo; documentar limitación si aplica |
| R-C1-03 | Convivencia | Export pivot oculto en modo grilla |
| R-C1-04 | Vacío | Botón deshabilitado + `pivotExport.noData` |
| R-C1-05 | Guardado archivo | Paridad TR-GEN-03 (`showSaveFilePicker` / descarga silenciosa) |
| R-C1-06 | Permisos | `exportEnabled` = permiso ver consulta |
| R-C1-07 | Toolbar | Slot después de layouts; antes de extras proceso |

---

## 4) Impacto en Datos

Sin tablas nuevas. Lee flags `exportacion` de metadata API.

---

## 5) Contratos de API y OpenAPI

**Sin endpoints nuevos en v1** (export client-side).

Opcional futuro (fuera D1): `POST /api/v1/pivots/consultas/{id}/export` para volúmenes grandes.

---

## 6) Cambios Frontend

### Componentes

```text
frontend/src/features/pivotExport/
  components/PivotExportButton.tsx
  utils/pivotExportExcel.ts
```

- `DropDownButton` DevExtreme con ítems Básica / Tabla dinámica según metadata.
- Montar en `PivotGridBlock` toolbar después de `PivotLayoutToolbar`.

### data-testid

- `pivotExport` — botón principal
- `pivotExportBasic` / `pivotExportPivotTable` — ítems menú si aplica

### i18n

- `pivotExport.*` (títulos modalidades, noData, metadata sheet)

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | `PivotExportButton` + menú modalidades | AC-01–AC-04 |
| T2 | Frontend | `pivotExportExcel.ts` básica | AC-02 |
| T3 | Frontend | Export tabla dinámica DX | AC-03 |
| T4 | Frontend | Gate vacío + permisos | AC-05, AC-08 |
| T5 | Frontend | Hoja metadatos opcional | AC-07 |
| T6 | Tests | E2E download básica | AC-11 |

---

## 8) Estrategia de Tests

- **Unit:** helpers nombre archivo, detección pivot vacío.
- **E2E:** mock download / `showSaveFilePicker` (patrón TR-GEN-03).

---

## 9) Riesgos y Edge Cases

- Excel tabla dinámica incompleta según versión DevExtreme → documentar en manual.
- Export con dataset truncado por volumen → incluir aviso en metadatos exportados.

---

## 10) Checklist final

- [x] AC cumplidos (toolbar, modalidades, vacío, permisos, testids, nombre archivo)
- [x] Paridad guardado archivo con TR-GEN-03 (`saveExcelWithPicker`)
- [x] Solo visible en modo pivot
- [x] E2E descarga básica (AC-11)

---

## Archivos creados/modificados (D1)

### Frontend
- `frontend/src/features/pivotExport/components/PivotExportButton.tsx`
- `frontend/src/features/pivotExport/utils/pivotExportExcel.ts`
- `frontend/src/features/pivotExport/utils/resolvePivotExportFlags.ts`
- `frontend/src/features/pivotExport/utils/buildPivotExportFileName.ts`
- `frontend/src/features/pivotExport/utils/isPivotExportEmpty.ts`
- `frontend/src/shared/pivot/components/ConsultaGrillaPivotShell.tsx` (slot export)
- `frontend/src/shared/pivot/types/pivotGridBlockHandle.ts` (`getPivotGridInstance`)
- `frontend/tests/e2e/pivot-export.spec.ts`
- `frontend/tests/unit/pivotExportUtils.test.ts`
- i18n `pivotExport.*` (es, en, pt, fr, it)










