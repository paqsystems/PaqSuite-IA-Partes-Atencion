# TR-GEN-07-plantilla-excel — Plantilla modelo por proceso

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-07-plantilla-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-plantilla-excel.md) |
| **SPEC relacionada** | [SPEC-001-07-importar-excel](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **Épica** | 001-Generalidades / Importar Excel |
| **Prioridad** | Could |
| **Dependencias** | TR-GEN-02-login-sesion; TR-GEN-02-autorizacion-menu-api; TR-GEN-03-exportaciones (patrones Excel) |
| **Estado** | Pendiente de programación (2026-06-16) |
| **Última actualización** | 2026-06-16 (C1 cerrado; D1 plantilla) |

**Origen:** [HU-GEN-07-plantilla-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-plantilla-excel.md)  
**Referencia SPEC:** [SPEC-001-07-importar-excel](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md)  
**MONO:** [PQ_EXCEL_Documento_Conceptual_Funcional_v3.md](../../00-contexto/_mono/importar-excel/PQ_EXCEL_Documento_Conceptual_Funcional_v3.md) §12, [PQ_EXCEL_SQL_Server_Tablas_y_Create.md](../../00-contexto/_mono/importar-excel/PQ_EXCEL_SQL_Server_Tablas_y_Create.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Descarga de plantilla Excel oficial por proceso de importación.

### Narrativa
Como usuario autorizado en un proceso de importación, quiero descargar la plantilla `.xlsx` con la estructura exacta del proceso para completar el archivo sin mapeo manual.

### In scope / Out of scope
- **In scope:** tablas catálogo `PQ_EXCEL_PROCESOS`, `PQ_EXCEL_PROCESOS_CAMPOS`; endpoint descarga plantilla; botón UI **Descargar plantilla modelo** (toolbar permanente si `GeneraPlantilla = 1`); generación `.xlsx` según conceptual §12 (encabezados, comentarios `OBLIGATORIO` + `Observaciones`, formato por `TipoDato`, validaciones celda); seed proceso piloto `ARTICULOS_ALTA`; `ProcedimientoHost`.
- **Out of scope:** carga de archivo, staging, grilla, historial, ABM web de catálogos, MVP portal release actual.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Botón **Descargar plantilla modelo** visible en toolbar del proceso si `GeneraPlantilla = 1`.
- **AC-02**: `GET plantilla` devuelve `.xlsx` con fila 1 = todos los `NombreColumnaExcel` activos en orden `OrdenCampo`.
- **AC-03**: Encabezados coinciden carácter a carácter con catálogo.
- **AC-04**: Columnas con `Activo = 0` no aparecen.
- **AC-05**: Comentario con `Observaciones` cuando tiene valor.
- **AC-06**: `EsColumnaObligatoriaEstructural = 1` → comentario incluye línea `OBLIGATORIO` (sin modificar texto del encabezado).
- **AC-07**: Formato de columna (filas ≥ 2) según `TipoDato` — ver §3.3 tabla formatos.
- **AC-08**: Validación Excel: `LargoMaximo`, `CantidadDecimales`, lista `booleano`.
- **AC-09**: Proceso con `GeneraPlantilla = 0` → 404 o metadata sin botón.
- **AC-10**: Nombre archivo `{CodigoProceso}_plantilla.xlsx` (sin fecha).
- **AC-11**: i18n; `data-testid="excelTemplateDownload"`.
- **AC-12**: Tests Feature: 200, 401, 403, 404; unit comentarios y formatos.

### Escenarios Gherkin

(Heredados de HU-GEN-07-plantilla-excel.)

---

## 3) Reglas de Negocio

1. **RN-01**: Plantilla refleja exactamente `NombreColumnaExcel` activos; sin alias ni corrección tipográfica.
2. **RN-02**: Encabezados solo letras sin tildes, números y espacios (conceptual §4).
3. **RN-03**: `GeneraPlantilla = 0` → no exponer descarga.
4. **RN-04**: Permiso = mismo que proceso host (`ProcedimientoHost` → `Permiso_Alta` o el que defina la pantalla host; default `Permiso_Alta` en seed piloto).
5. **RN-05**: Formato columna según `TipoDato`; validación Excel según `LargoMaximo` / `CantidadDecimales`.
6. **RN-06**: Booleano según `FormatoBooleanoPlantilla` (`0_1` | `N_S` | `VERDADERO_FALSO`; default `0_1`).
7. **RN-07**: Estilo encabezado fila 1: fondo `#4472C4`, texto blanco (§12.2).
8. **RN-08**: Comentario encabezado = `buildHeaderComment(obligatorio, observaciones)` — ver §3.3.
9. **RN-09**: `OBLIGATORIO` **nunca** se concatena al `NombreColumnaExcel` visible.
10. **RN-10**: Plantilla exporta solo fila 1 con datos; filas 2+ vacías con formato/validación de columna aplicados.

---

## 3.3) Generación plantilla — especificación técnica (2026-06-16)

Fuente normativa: `PQ_EXCEL_Documento_Conceptual_Funcional_v3.md` §12.

### Comentario de encabezado (`buildHeaderComment`)

```text
Si EsColumnaObligatoriaEstructural: línea "OBLIGATORIO"
Si Observaciones no vacío: agregar (nueva línea si ya hay OBLIGATORIO)
Si ambos vacíos: sin comentario
```

### Tabla de formatos PhpSpreadsheet (columna completa, desde fila 2)

| `TipoDato` | `numFmt` / estilo | Validación datos (filas 2:1048576) |
|------------|-------------------|-------------------------------------|
| `texto` | `@` | `LargoMaximo` si definido |
| `codigo` | `@` + celda texto forzado | `LargoMaximo` |
| `entero` | `0` | entero |
| `decimal` | `0.` + `0` × `CantidadDecimales` (min 2 si null → `0.00`) | decimal |
| `fecha` | `dd/mm/yyyy` | fecha |
| `booleano` | texto + lista | lista según `FormatoBooleanoPlantilla` |

### Lista booleano

| `FormatoBooleanoPlantilla` | Valores lista |
|----------------------------|---------------|
| `0_1` | `0,1` |
| `N_S` | `N,S` |
| `VERDADERO_FALSO` | `VERDADERO,FALSO` |

### Servicio backend

- Clase: `ExcelTemplateService::generate(string $codigoProceso): StreamedResponse`
- Método auxiliar: `buildHeaderComment(bool $obligatorio, ?string $observaciones): ?string`
- Método auxiliar: `applyColumnFormat(Worksheet $sheet, int $colIndex, CampoDef $campo, ?string $formatoBooleanoProceso): void`

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-16)

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (aplicar resoluciones §3.2)

### Ambigüedades críticas

| ID | Tema | Riesgo | Estado | Resolución (→ D1) |
|----|------|--------|--------|-------------------|
| AMB-C07-01 | **Permiso API plantilla** | Gate desconocido | **Cerrado** (R-C1-01) | Columna `ProcedimientoHost` en `PQ_EXCEL_PROCESOS`; policy usa permiso de **alta** del host (configurable por seed). |
| AMB-C07-02 | **Respuesta binaria vs envelope** | Cliente no sabe parsear | **Cerrado** (R-C1-02) | `Accept: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` → **stream** `200` + `Content-Disposition`; JSON envelope solo en errores 4xx. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M-07-02 | Formato booleano | Columna `FormatoBooleanoPlantilla` en `PQ_EXCEL_PROCESOS`; default `0_1`. |
| AMB-M-07-05 | Prefijo tablas | Canónico `PQ_EXCEL_*` en migraciones Laravel (SQL Server). |
| AMB-M-07-06 | Librería Excel | **PhpSpreadsheet** en backend (paridad capacidad con `exceljs` frontend). |

### Preguntas para decisión humana

| ID | Tema | Decisión C1 |
|----|------|-------------|
| AMB-Q-07-01 | Permiso dedicado vs host | **Cerrada:** mismo permiso que pantalla host vía `ProcedimientoHost`. |

### Veredicto C1

**Apto con observaciones para D1** — revisión reabierta 2026-06-16 tras enriquecimiento plantilla §12.

---

## 3.4) D1 — Implementación plantilla (2026-06-16)

| ID | Entregable | Estado |
|----|------------|--------|
| D1-1 | `phpoffice/phpspreadsheet` ^5.8 | Hecho |
| D1-2 | Migración `pq_excel_procesos*` + seed `ARTICULOS_ALTA` | Hecho |
| D1-3 | `ExcelTemplateService` + `ExcelImportHeaderCommentBuilder` | Hecho |
| D1-4 | API `GET .../procesos/{codigo}` y `.../plantilla` | Hecho |
| D1-5 | Flag `EXCEL_IMPORT_ENABLED` / `excelImportEnabled` | Hecho |
| D1-6 | Tests unit comentarios | Hecho |
| D1-7 | Tests feature plantilla | Hecho (skip sin SQL Server tenant) |
| D1-8 | Botón frontend `excelTemplateDownload` | Hecho |
| D1-9 | Matriz permisos + OpenAPI completo | Pendiente revisión conjunta |


## 3.2) Resoluciones C1 — pre-D1 (2026-06-16)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Permiso (AMB-Q-07-01) | `ProcedimientoHost` en catálogo; policy `Permiso_Alta` del procedimiento host (ajustable por proceso en seed). |
| R-C1-02 | Descarga plantilla | Stream binario `.xlsx` en 200; errores con envelope MONO. |
| R-C1-03 | Flag infra | `EXCEL_IMPORT_ENABLED` en `config/public` default **false** hasta activar epic. |
| R-C1-04 | Metadata proceso | `GET .../procesos/{codigoProceso}` devuelve flags UI (`generaPlantilla`, `permiteProcesamientoParcial`, etc.) sin datos sensibles de handler. |

---

## 4) Impacto en Datos

### Tablas nuevas (BD tenant)

| Tabla | Rol |
|-------|-----|
| `PQ_EXCEL_PROCESOS` | Catálogo procesos (+ `ProcedimientoHost`, `FormatoBooleanoPlantilla`) |
| `PQ_EXCEL_PROCESOS_CAMPOS` | Columnas por proceso |

DDL base: `PQ_EXCEL_SQL_Server_Tablas_y_Create.md` §1 + columnas extra:

```sql
ALTER TABLE dbo.PQ_EXCEL_PROCESOS ADD ProcedimientoHost VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE dbo.PQ_EXCEL_PROCESOS ADD FormatoBooleanoPlantilla VARCHAR(20) NOT NULL
    CONSTRAINT DF_PQ_EXCEL_PROCESOS_FormatoBool DEFAULT ('0_1');
```

### Seed mínimo para tests

- Proceso `ARTICULOS_ALTA` según SQL §7 (`HandlerBackend`, 5 campos).
- `ProcedimientoHost` = procedimiento de pantalla host acordada (placeholder `pw_articulos` o proceso ABM futuro).

### Migración

- `backend/database/migrations/YYYY_MM_DD_create_pq_excel_catalog_tables.php`

---

## 5) Contratos de API y OpenAPI

> Bearer + `X-Paq-Cliente` · envelope MONO en errores · permiso vía `ProcedimientoHost`.

### 5.1 Endpoints

| Método | Path | Auth | Permiso | Público |
|--------|------|------|---------|---------|
| GET | `/api/v1/excel-import/procesos/{codigoProceso}` | Bearer + tenant | `Permiso_Alta` host | No |
| GET | `/api/v1/excel-import/procesos/{codigoProceso}/plantilla` | Bearer + tenant | `Permiso_Alta` host | No |

**Path param:** `codigoProceso` = `PQ_EXCEL_PROCESOS.CodigoProceso`.

### 5.2 Detalle

#### GET `/api/v1/excel-import/procesos/{codigoProceso}`

**Response 200 (envelope):**

```json
{
  "error": 0,
  "respuesta": "",
  "resultado": {
    "codigoProceso": "ARTICULOS_ALTA",
    "nombreProceso": "Importacion de Articulos",
    "generaPlantilla": true,
    "permiteProcesamientoParcial": false,
    "permiteSoloValidar": true,
    "mantenerEspaciosEnBlancoDefault": false,
    "mantenerCaracteresEspecialesDefault": false,
    "procedimientoHost": "pw_articulos"
  }
}
```

**404:** proceso inexistente o `Activo = 0` → `error: 4007`, `excelImport.procesoNotFound`.

#### GET `/api/v1/excel-import/procesos/{codigoProceso}/plantilla`

**Headers response 200:** `Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`  
`Content-Disposition: attachment; filename="ARTICULOS_ALTA_plantilla_20260616.xlsx"`

**403:** sin permiso · **404:** `GeneraPlantilla = 0` o proceso inactivo.

### 5.3 Matriz permisos

- [ ] Filas en `matriz-permisos-mvp.md` § Importar Excel (epic)

---

## 6) Cambios Frontend

### Componentes
- `ExcelImportToolbar`: botón DevExtreme **Descargar plantilla modelo** (`excelTemplateDownload`) **siempre** en toolbar superior del proceso (junto a carga/grilla), visible si `generaPlantilla === true`.
- Hook `useExcelImportProcess(codigoProceso)` → metadata + descarga blob.
- Ocultar acción si `generaPlantilla === false` o `EXCEL_IMPORT_ENABLED === false`.

### data-testid
- `excelTemplateDownload`

### i18n (5 idiomas)
- `excelImport.downloadTemplate`
- `excelImport.plantillaNoDisponible`
- `excelImport.procesoNotFound`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Migración catálogo + seed piloto | Tablas + seed PHPUnit |
| T2 | Backend | `ExcelTemplateService` + controller plantilla | AC-02–AC-07 |
| T3 | Backend | Policy `ExcelImportProcessPolicy` | 401/403 tests |
| T4 | Frontend | Botón descarga + metadata | AC-01, AC-08 |
| T5 | Tests | Feature + unit generador | §8 |
| T6 | Docs | OpenAPI + matriz permisos | Checklist §10 |

---

## 8) Estrategia de Tests

- **Unit:** `buildHeaderComment` (obligatorio, observaciones, ambos, ninguno); formatos por `TipoDato`; orden columnas.
- **Integration:** GET plantilla — parse xlsx: encabezados, comentario `OBLIGATORIO` en Codigo seed, formato columna Precio.
- **E2E:** mock descarga; botón visible con flag y permiso.

---

## 9) Riesgos y Edge Cases

- Proceso sin campos activos → 422 `excelImport.sinColumnasActivas`.
- `CodigoProceso` con caracteres no seguros en nombre archivo → sanitizar.
- PhpSpreadsheet memoria en procesos con muchas columnas (límite razonable 200 columnas).

---

## 10) Checklist final

- [ ] AC cumplidos
- [ ] Endpoints con policy
- [ ] Matriz permisos actualizada
- [ ] OpenAPI coherente
- [ ] Envelope en errores JSON
- [ ] Sin ampliación fuera de SPEC/HU/TR

---

## Archivos previstos (post-D1)

### Backend
- `app/Modules/ExcelImport/...`
- `ExcelTemplateService.php`, `ExcelImportProcessController.php`
- `Policies/ExcelImportProcessPolicy.php`

### Frontend
- `features/excelImport/ExcelTemplateDownloadButton.tsx`

### Docs
- `matriz-permisos-mvp.md` § Importar Excel










