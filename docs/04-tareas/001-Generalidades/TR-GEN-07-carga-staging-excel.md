# TR-GEN-07-carga-staging-excel — Carga de archivo y staging

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-07-carga-staging-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-carga-staging-excel.md) |
| **SPEC relacionada** | [SPEC-001-07-importar-excel](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **Épica** | 001-Generalidades / Importar Excel |
| **Prioridad** | Could |
| **Dependencias** | TR-GEN-07-plantilla-excel |
| **Estado** | Pendiente de programación (2026-06-16) |
| **Última actualización** | 2026-06-16 (D1 carga + staging) |

**Origen:** [HU-GEN-07-carga-staging-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-carga-staging-excel.md)  
**Referencia SPEC:** [SPEC-001-07-importar-excel](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md)  
**MONO:** [PQ_EXCEL_Documento_Conceptual_Funcional_v3.md](../../00-contexto/_mono/importar-excel/PQ_EXCEL_Documento_Conceptual_Funcional_v3.md) §5–§7  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Subida de `.xlsx`, selección de hoja, validación estructural y volcado a staging persistente.

### In scope / Out of scope
- **In scope:** tablas lote/staging/errores/notificaciones; parser `.xlsx`; validación estructural; normalización §7; validación formato (BE); invocación `HandlerBackend` para negocio; jobs asíncronos; cancelación; UI `FileUploader` + selector hoja.
- **Out of scope:** grilla procesamiento (TR hermana), historial, reproceso mismo lote, MVP portal release actual.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: `POST hojas` con archivo `.xlsx` devuelve nombres de hojas.
- **AC-02**: `POST lotes` crea registro en `PQ_EXCEL_IMPORTACIONES` con metadatos.
- **AC-03**: Encabezado inválido → `EstadoImportacion = con_error_estructura` sin filas procesables.
- **AC-04**: Filas vacías descartadas; `CantidadFilasDescartadas` actualizado.
- **AC-05**: Errores por fila en `PQ_EXCEL_IMPORTACIONES_FILAS` + `PQ_EXCEL_IMPORTACIONES_FILAS_ERRORES`.
- **AC-06**: Normalización trim/caracteres según flags del lote; `FilaAjustadaAutomaticamente` en BD sin UI.
- **AC-07**: Archivo grande → `EsAsincronica = 1`, job en cola, notificación al finalizar.
- **AC-08**: `POST cancelar` antes de procesamiento → `cancelada`.
- **AC-09**: `data-testid`: `excelFileUpload`, `excelSheetSelect`.
- **AC-10**: Solo `.xlsx` válido; otro formato → 422 `excelImport.formatoInvalido`.

### Escenarios Gherkin

(Heredados de HU-GEN-07-carga-staging-excel.)

---

## 3) Reglas de Negocio

1. **RN-01**: Solo extensión `.xlsx` válida (magic bytes + PhpSpreadsheet).
2. **RN-02**: Encabezado fila 1; coincidencia exacta con catálogo; columnas extra ignoradas.
3. **RN-03**: Columnas obligatorias estructurales faltantes → error estructural de lote.
4. **RN-04**: Duplicados, encabezados vacíos, celdas combinadas → error estructural.
5. **RN-05**: Tipo incorrecto o largo excedido → error **por fila** (`TieneError = 1`).
6. **RN-06**: Fórmulas → valor calculado; ocultas → procesar igual.
7. **RN-07**: `FilaAjustadaAutomaticamente` por trim (§7.1) o limpieza no imprimibles (§7.2); sin UI.
8. **RN-08**: Flags normalización del lote = defaults del proceso en v1 (sin override UI).
9. **RN-09**: Estados lote: `pendiente` → `validando` → `validada` / `con_error_estructura` → `lista_para_procesar`.
10. **RN-10**: `HandlerBackend` resuelve validación negocio vía contrato plug-in (AMB-M-07-01).

### Contrato HandlerBackend (v1)

```php
interface ExcelImportHandlerInterface {
    /** @return ExcelRowError[] vacío si válida */
    public function validateBusinessRow(array $normalizedRow, ExcelImportLotContext $ctx): array;

    public function processRow(array $normalizedRow, ExcelImportLotContext $ctx): void;
}
```

Resolución por `HandlerBackend` (container tag o mapa clase).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-16)

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí**

### Ambigüedades críticas

| ID | Tema | Estado | Resolución (→ D1) |
|----|------|--------|-------------------|
| AMB-C07-03 | **Umbral archivo asíncrono** | **Cerrado** (R-C1-05) | `> 5 MB` o `> 2000` filas estimadas → asíncrono; configurable `config/excel_import.php`. |
| AMB-C07-04 | **Almacenamiento archivo subido** | **Cerrado** (R-C1-06) | No persistir binario en BD v1; procesar en memoria/temp y guardar solo metadatos nombre/hoja. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M-07-03 | Override flags normalización | **Sin override** en v1; copiar defaults proceso al crear lote. |
| AMB-M-07-01 | HandlerBackend | Interface + registro por nombre en `HandlerBackend` columna. |
| AMB-M-07-04 | Paginación | Definida en TR grilla; carga persiste todo el lote. |

### Preguntas para decisión humana

| ID | Tema | Decisión C1 |
|----|------|-------------|
| AMB-Q-07-02 | Override flags por lote | **Cerrada:** defaults del proceso; sin UI override v1. |

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-16)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-05 | Async | Job `ProcessExcelImportLotJob`; notificación `toast` + registro `PQ_EXCEL_IMPORTACIONES_NOTIFICACIONES`. |
| R-C1-06 | Binario | Sin tabla de archivos en v1. |
| R-C1-07 | Permiso carga | Mismo `ProcedimientoHost` / `Permiso_Alta` que plantilla. |
| R-C1-08 | JSON staging | `DatosNormalizadosJson` keyed por `NombreCampoInterno`; `DatosOriginalesJson` opcional Should. |

---

## 4) Impacto en Datos

### Tablas nuevas

| Tabla | Rol |
|-------|-----|
| `PQ_EXCEL_IMPORTACIONES` | Cabecera lote |
| `PQ_EXCEL_IMPORTACIONES_FILAS` | Staging por fila |
| `PQ_EXCEL_IMPORTACIONES_FILAS_ERRORES` | Errores detallados |
| `PQ_EXCEL_IMPORTACIONES_NOTIFICACIONES` | Toast/bandeja |

DDL: `PQ_EXCEL_SQL_Server_Tablas_y_Create.md` §2–§5.

### Migración

- `backend/database/migrations/YYYY_MM_DD_create_pq_excel_import_tables.php`

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints

| Método | Path | Auth | Permiso | Público |
|--------|------|------|---------|---------|
| POST | `/api/v1/excel-import/procesos/{codigoProceso}/archivo/hojas` | Bearer + tenant | `Permiso_Alta` host | No |
| POST | `/api/v1/excel-import/procesos/{codigoProceso}/lotes` | Bearer + tenant | `Permiso_Alta` host | No |
| GET | `/api/v1/excel-import/lotes/{guidImportacion}` | Bearer + tenant | `Permiso_Alta` host + dueño o supervisor | No |
| POST | `/api/v1/excel-import/lotes/{guidImportacion}/cancelar` | Bearer + tenant | Dueño del lote | No |

### 5.2 Detalle

#### POST `.../archivo/hojas`

**Request:** `multipart/form-data`, campo `archivo` (`.xlsx`).

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "",
  "resultado": {
    "hojas": ["Hoja1", "Datos", "Resumen"]
  }
}
```

#### POST `.../lotes`

**Request:** `multipart` — `archivo`, `hojaSeleccionada` (string).

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "",
  "resultado": {
    "guidImportacion": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
    "estadoImportacion": "lista_para_procesar",
    "esAsincronica": false,
    "cantidadFilasLeidas": 120,
    "cantidadFilasDescartadas": 3,
    "cantidadFilasValidas": 100,
    "cantidadFilasConError": 17
  }
}
```

**422:** error estructural con `estadoImportacion: con_error_estructura` y `respuesta` i18n detallada.

#### GET `/api/v1/excel-import/lotes/{guidImportacion}`

Devuelve cabecera lote + contadores + flags proceso (`permiteProcesamientoParcial`, `permiteSoloValidar`).

#### POST `.../cancelar`

Solo si `PuedeCancelar = 1` y estado no terminal → `cancelada`.

---

## 6) Cambios Frontend

### Componentes
- `ExcelImportUploadPanel`: DevExtreme `FileUploader` (`excelFileUpload`) + `SelectBox` hojas (`excelSheetSelect`).
- Flujo: subir → listar hojas → confirmar carga → redirect a grilla staging (TR hermana) o polling si async.
- Estados loading/error i18n.

### data-testid
- `excelFileUpload`, `excelSheetSelect`, `excelImportSubmit`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Migración tablas import | DDL aplicado |
| T2 | Backend | `ExcelParserService` estructural + formato | AC-03–AC-05 |
| T3 | Backend | `ExcelImportLotService` + jobs async | AC-07 |
| T4 | Backend | Handler registry + stub `ArticulosAltaHandler` tests | RN-10 |
| T5 | Frontend | Upload + hoja | AC-09 |
| T6 | Tests | Feature multipart + structural errors | §8 |

---

## 8) Estrategia de Tests

- **Unit:** parser encabezados, trim, caracteres no imprimibles, filas vacías.
- **Integration:** hojas 200; lote válido; columna faltante; cancelar.
- **E2E:** flujo upload mock xlsx pequeño.

---

## 9) Riesgos y Edge Cases

- Archivo corrupto → 422 sin crear lote.
- Lote concurrente mismo usuario: permitido (lotes aislados).
- Handler inexistente → error sistema por fila o bloqueo post-formato según criticidad (log + `TipoError = sistema`).

---

## 10) Checklist final

(Checklist transversal § plantilla TR.)










