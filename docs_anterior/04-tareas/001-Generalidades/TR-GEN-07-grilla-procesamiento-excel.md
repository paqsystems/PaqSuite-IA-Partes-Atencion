# TR-GEN-07-grilla-procesamiento-excel — Grilla de staging y procesamiento

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-07-grilla-procesamiento-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-grilla-procesamiento-excel.md) |
| **SPEC relacionada** | [SPEC-001-07-importar-excel](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **Épica** | 001-Generalidades / Importar Excel |
| **Prioridad** | Could |
| **Dependencias** | TR-GEN-07-carga-staging-excel; TR-GEN-03-grillas-listados |
| **Estado** | Pendiente de programación (2026-06-16) |
| **Última actualización** | 2026-06-16 (D1 grilla + procesar) |

**Origen:** [HU-GEN-07-grilla-procesamiento-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-grilla-procesamiento-excel.md)  
**Referencia SPEC:** [SPEC-001-07-importar-excel](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md)  
**MONO:** [PQ_EXCEL_Documento_Conceptual_Funcional_v3.md](../../00-contexto/_mono/importar-excel/PQ_EXCEL_Documento_Conceptual_Funcional_v3.md) §6.1, §8  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Grilla DevExtreme de filas en staging, política `PermiteProcesamientoParcial` y confirmación de procesamiento.

### In scope / Out of scope
- **In scope:** API paginada de filas; UI `DataGridDx` con columna **Errores** fija; acciones Procesar/Cancelar; lógica `PermiteProcesamientoParcial`; invocación `HandlerBackend::processRow`; actualización contadores y estados; modo lectura para historial; notificaciones async; **modo modal embebido** (solo filas con error — TR ui-embebida-host).
- **Out of scope:** upload/parser; historial listado; columna `FilaAjustadaAutomaticamente` en UI; ABM procesos; MVP portal release actual.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Grilla muestra columnas del proceso + columna **Errores** concatenada.
- **AC-02**: Fila con error: `rowAlternation`/`onRowPrepared` fondo suave + tooltip detalle.
- **AC-03**: `permiteProcesamientoParcial = false` y ≥ 1 error → Procesar deshabilitado + i18n `excelImport.processBlockedByErrors`.
- **AC-04**: `permiteProcesamientoParcial = true`, mezcla → Procesar habilitado + mensaje filas omitidas.
- **AC-05**: Todas las filas con error → Procesar deshabilitado.
- **AC-06**: Cero errores → procesamiento total; estado `procesada`.
- **AC-07**: Tras procesar, contadores BD coherentes con API.
- **AC-08**: Async: toast/bandeja al finalizar (`PQ_EXCEL_IMPORTACIONES_NOTIFICACIONES`).
- **AC-09**: `data-testid`: `excelStagingGrid`, `excelProcessConfirm`, `excelImportCancel`.
- **AC-10**: Modo `readOnly` oculta Procesar/Cancelar (detalle desde historial).

### Escenarios Gherkin

(Heredados de HU-GEN-07-grilla-procesamiento-excel.)

---

## 3) Reglas de Negocio

1. **RN-01**: `PermiteProcesamientoParcial = false` + ≥ 1 error → no procesar (SPEC §6.1).
2. **RN-02**: Parcial true + mezcla → solo válidas; estado `procesada_parcial`.
3. **RN-03**: Todas con error → Procesar deshabilitado.
4. **RN-04**: Cero errores → `procesada` (no parcial).
5. **RN-05**: Errores estructurales no llegan a esta pantalla.
6. **RN-06**: `PermiteSoloValidar = 1` → ocultar Procesar.
7. **RN-07**: Tras procesar, filas aplicadas → `EstadoFila = procesada`.
8. **RN-08**: Cancelar solo antes de `procesando` / estados finales.
9. **RN-09**: Sin columna ni ícono `FilaAjustadaAutomaticamente` en grilla.
10. **RN-10**: Reproceso mismo lote tras corregir Excel → **no** en v1 (nueva importación).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-16)

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí**

### Ambigüedades críticas

| ID | Tema | Estado | Resolución (→ D1) |
|----|------|--------|-------------------|
| AMB-C07-05 | **Transacción procesamiento** | **Cerrado** (R-C1-09) | Por fila válida en transacción corta; fallo handler → fila `rechazada` + error sistema; lote puede quedar `procesada_parcial`. |
| AMB-C07-06 | **Confirmación UI** | **Cerrado** (R-C1-10) | `Popup` DevExtreme confirmación antes de `POST procesar`; resume válidas/omitidas. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M-07-04 | Paginación | `GET filas?page=&pageSize=` default 50, max 200; server-side `DataGridDx`. |
| AMB-M-07-07 | Columnas dinámicas | Metadata columnas desde `PQ_EXCEL_PROCESOS_CAMPOS` + columna fija `errores`. |

### Preguntas para decisión humana

| ID | Tema | Decisión C1 |
|----|------|-------------|
| AMB-Q-07-03 | Reproceso mismo lote | **Cerrada:** nueva importación en v1. |

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-16)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-09 | Transacción | Unit of work por fila; rollback fila individual en error handler. |
| R-C1-10 | Confirmación | Popup DX con resumen; `excelProcessConfirm` en botón primario. |
| R-C1-11 | Refresh grilla | Ícono Actualizar en `toolbarEnd` (`gridRefresh` / `excelStagingRefresh`) re-fetch servidor. |
| R-C1-12 | Errores concatenados | Campo `errorImportacion` en fila API; detalle en tooltip desde `FILAS_ERRORES` si hace falta. |

---

## 4) Impacto en Datos

Sin tablas nuevas (usa staging de TR-GEN-07-carga-staging-excel).

### Actualizaciones en procesamiento

- `PQ_EXCEL_IMPORTACIONES`: estado, contadores, `FechaFin`, `MensajeResultado`.
- `PQ_EXCEL_IMPORTACIONES_FILAS`: `EstadoFila`, post-proceso.

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints

| Método | Path | Auth | Permiso | Público |
|--------|------|------|---------|---------|
| GET | `/api/v1/excel-import/lotes/{guidImportacion}/filas` | Bearer + tenant | `Permiso_Alta` host | No |
| GET | `/api/v1/excel-import/lotes/{guidImportacion}/columnas` | Bearer + tenant | Idem | No |
| POST | `/api/v1/excel-import/lotes/{guidImportacion}/procesar` | Bearer + tenant | Idem + dueño | No |

### 5.2 Detalle

#### GET `.../filas`

**Query:** `page` (default 1), `pageSize` (default 50, max 200), `soloConError` (opcional bool).

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "",
  "resultado": {
    "items": [
      {
        "idImportacionFila": 1001,
        "numeroFilaExcel": 2,
        "tieneError": true,
        "errorImportacion": "Precio: valor decimal invalido",
        "estadoFila": "con_error",
        "datos": { "codigo": "A01", "descripcion": "Item", "precio": null }
      }
    ],
    "total": 120,
    "page": 1,
    "pageSize": 50
  }
}
```

> `FilaAjustadaAutomaticamente` **no** se expone en API v1.

#### GET `.../columnas`

Devuelve definición columnas grilla (`dataField`, `caption`, `tipoDato`, `format`) + metadata acciones (`puedeProcesar`, `permiteProcesamientoParcial`, `permiteSoloValidar`, contadores).

#### POST `.../procesar`

**Precondiciones:** estado `lista_para_procesar` o `validada`; reglas RN-01–RN-05.

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "excelImport.processSuccess",
  "resultado": {
    "estadoImportacion": "procesada_parcial",
    "cantidadFilasProcesadas": 80,
    "cantidadFilasOmitidas": 17
  }
}
```

**422:** `excelImport.processNotAllowed` (parcial false con errores, todas con error, etc.).

---

## 6) Cambios Frontend

### Componentes
- `ExcelStagingGridPage`: `DataGridDx` server-side (`excelStagingGrid`).
- `ExcelImportProcessToolbar`: Procesar (`excelProcessConfirm`), Cancelar (`excelImportCancel`), Actualizar (`excelStagingRefresh`).
- `useExcelProcessPolicy(lote)` → habilita/deshabilita Procesar según RN.
- `ExcelImportConfirmPopup` (DevExtreme `Popup`).
- Prop `readOnly` para detalle historial.

### Estilos fila error
- `onRowPrepared`: clase `excel-import-row-error` (fondo suave).
- Tooltip: errores detallados.

### i18n
- `excelImport.process`, `excelImport.processBlockedByErrors`, `excelImport.processPartialSummary`, `excelImport.cancel`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | `ExcelStagingQueryService` paginado | AC-01, paginación |
| T2 | Backend | `ExcelImportProcessService` + handler | AC-06, AC-07 |
| T3 | Backend | Validación pre-proceso política parcial | AC-03–AC-05 |
| T4 | Frontend | Grilla + toolbar + popup | AC-01–AC-09 |
| T5 | Frontend | Modo readOnly historial | AC-10 |
| T6 | Tests | Feature procesar parcial/total/bloqueado | §8 |

---

## 8) Estrategia de Tests

- **Unit:** `canProcessLot(permiteParcial, contadores)`.
- **Integration:** filas paginadas; procesar total; parcial; bloqueado; 422.
- **E2E:** grilla con filas error → botón deshabilitado; parcial confirmación.

---

## 9) Riesgos y Edge Cases

- Procesamiento largo → job async + estado `procesando`.
- Handler lanza excepción → fila `rechazada`, continuar según política.
- Usuario abandona pantalla durante async → notificación bandeja.

---

## 10) Checklist final

(Checklist transversal § plantilla TR.)










