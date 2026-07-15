# TR-GEN-07-ui-embebida-host — Componente Excel embebido (D2)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-07-ui-embebida-host](../../03-historias-usuario/001-Generalidades/HU-GEN-07-ui-embebida-host.md) |
| **Patrón** | [patron-componente-excel-embebido.md](../../00-contexto/_mono/importar-excel/patron-componente-excel-embebido.md) |
| **SPEC** | [SPEC-001-07-importar-excel](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **Dependencias** | TR-GEN-07-plantilla-excel; TR-GEN-07-carga-staging-excel; TR-GEN-07-grilla-procesamiento-excel |
| **Estado** | D2 implementado; paso E tests (2026-06-16) |
| **Última actualización** | 2026-06-16 |

---

## 1) In scope / Out of scope

**In scope:** `ExcelImportHostToolbar`, `ExcelImportHostModal`, `ExcelImportErrorGrid`, hook `useExcelImportHostFlow`, tipos `ExcelImportHostResult`, APIs nuevas export-errores y filas-validas, i18n, integración piloto en `ExcelImportProcessPage`.

**Out of scope:** SPEC carga pedidos; cambios handler negocio; eliminar rutas legado `/excel-import/lotes`.

---

## 2) Criterios de aceptación

- **AC-01** a **AC-09**: heredados de HU ui-embebida.
- **AC-10**: Rutas legado historial + grilla readOnly siguen operativas.
- **AC-11**: `GET filas?soloConError=true` usado por grilla modal.

---

## 3) Contrato TypeScript

```typescript
export type ExcelImportHostResult = {
  guidImportacion: string;
  codigoProceso: string;
  validRows: Array<Record<string, unknown>>;
  meta: {
    totalFilas: number;
    filasValidas: number;
    filasConError: number;
    permiteProcesamientoParcial: boolean;
    estadoImportacion: string;
    nombreArchivoOriginal: string;
  };
};

export type ExcelImportHostToolbarProps = {
  codigoProceso: string;
  disabled?: boolean;
  onComplete: (result: ExcelImportHostResult) => void;
  onCancel?: () => void;
};
```

---

## 4) Orquestación (post `POST lotes`)

| Condición | Acción |
|-----------|--------|
| `estadoImportacion = con_error_estructura` | Fase mensaje; no `onComplete` hasta Cerrar (vacío opcional) o Reintentar |
| `cantidadFilasConError = 0` y no `permiteSoloValidar` | `POST procesar` → `GET filas/validas` → `onComplete` → cerrar modal |
| `cantidadFilasConError = 0` y `permiteSoloValidar` | `GET filas/validas` (sin procesar) → `onComplete` → cerrar |
| Errores y `permiteProcesamientoParcial = false` | Grilla errores; Cerrar → `onComplete` con `validRows: []` |
| Errores y parcial `true` | `POST procesar` → grilla errores → Continuar → `onComplete` |

---

## 5) APIs nuevas

| Método | Path | Descripción |
|--------|------|-------------|
| GET | `/api/v1/excel-import/lotes/{guid}/filas/validas` | Payload host: filas sin error (`datos` por `NombreCampoInterno`) |
| GET | `/api/v1/excel-import/lotes/{guid}/export-errores` | Stream `.xlsx` solo filas con error |

### Export errores

- Columnas: `NombreColumnaExcel` activos + `Errores` + `NumeroFilaExcel`.
- `Content-Disposition`: `{baseOriginal}_errores_{YmdHis}.xlsx`.

### Plantilla

- Nombre sugerido: `{codigoProceso}_plantilla.xlsx` (sin fecha).

---

## 6) Frontend

| Componente | Rol |
|------------|-----|
| `ExcelImportHostToolbar` | Export + abrir modal |
| `ExcelImportHostModal` | Fases upload / estructural / errores |
| `ExcelImportErrorGrid` | `CustomStore` + `soloConError=true` |
| `ExcelTemplateDownloadButton` | `saveExcelWithPicker` + nombre fijo |

### data-testid

`excelHostToolbar`, `excelHostImport`, `excelHostImportModal`, `excelHostErrorGrid`, `excelHostExportErrors`, `excelHostContinue`, `excelHostRetry`

---

## 7) Tests

- **Unit:** `buildErrorsExportFileName`, `buildSuggestedFileName` sin fecha.
- **Feature:** GET export-errores 200; GET filas/validas tras lote mixto.

---

## 8) Checklist D2

- [x] Docs revisión consistencia
- [x] Backend endpoints
- [x] Componentes FE + piloto process page
- [x] i18n 5 locales
- [x] `npm run build` + PHPUnit excel import










