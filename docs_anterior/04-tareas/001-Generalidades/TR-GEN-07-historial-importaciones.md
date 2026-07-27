# TR-GEN-07-historial-importaciones — Historial de importaciones

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-07-historial-importaciones](../../03-historias-usuario/001-Generalidades/HU-GEN-07-historial-importaciones.md) |
| **SPEC relacionada** | [SPEC-001-07-importar-excel](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **Épica** | 001-Generalidades / Importar Excel |
| **Prioridad** | Could |
| **Dependencias** | TR-GEN-07-carga-staging-excel; TR-GEN-07-grilla-procesamiento-excel (detalle readOnly); TR-GEN-03-grillas-listados |
| **Estado** | Pendiente de programación (2026-06-16) |
| **Última actualización** | 2026-06-16 (D1 historial) |

**Origen:** [HU-GEN-07-historial-importaciones](../../03-historias-usuario/001-Generalidades/HU-GEN-07-historial-importaciones.md)  
**Referencia SPEC:** [SPEC-001-07-importar-excel](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md)  
**MONO:** [PQ_EXCEL_Documento_Conceptual_Funcional_v3.md](../../00-contexto/_mono/importar-excel/PQ_EXCEL_Documento_Conceptual_Funcional_v3.md) §10, [PQ_EXCEL_SQL_Server_Tablas_y_Create.md](../../00-contexto/_mono/importar-excel/PQ_EXCEL_SQL_Server_Tablas_y_Create.md) §6  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Pantalla de historial de importaciones Excel (solo lectura) con filtros y detalle de lote.

### In scope / Out of scope
- **In scope:** vista `PQ_EXCEL_VW_HISTORIAL_IMPORTACIONES`; API paginada; grilla DevExtreme solo lectura; filtros proceso/estado/usuario/fechas; navegación a grilla staging readOnly; i18n estados; menú proceso dedicado o transversal.
- **Out of scope:** reproceso/cancelación retroactiva; purga/archivado; export Excel historial (Should opcional); MVP portal release actual.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Grilla columnas mínimas §10 conceptual (fecha, usuario, proceso, archivo, hoja, estado, contadores).
- **AC-02**: Filtros por `codigoProceso`, `estadoImportacion`, rango `fechaInicio`.
- **AC-03**: Orden default `fechaInicio` descendente.
- **AC-04**: Sin permiso sobre proceso → sin filas / 403 según filtro.
- **AC-05**: Acción ver detalle → ruta grilla staging `readOnly=true`.
- **AC-06**: Estados con i18n `excelImport.status.*`.
- **AC-07**: `data-testid`: `excelHistoryGrid`, `excelHistoryDetail`.

### Escenarios Gherkin

(Heredados de HU-GEN-07-historial-importaciones.)

---

## 3) Reglas de Negocio

1. **RN-01**: Solo lectura; ninguna acción modifica lotes.
2. **RN-02**: Visibilidad por permiso `ProcedimientoHost` del proceso (mismo gate que importar).
3. **RN-03**: Supervisor con permiso amplio puede ver todos los procesos autorizados (misma policy que consultas).
4. **RN-04**: Etiquetas estado i18n, no código crudo.
5. **RN-05**: Contadores deben coincidir con `PQ_EXCEL_IMPORTACIONES`.
6. **RN-06**: Detalle reutiliza `ExcelStagingGridPage` en modo lectura.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-16)

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí**

### Ambigüedades críticas

| ID | Tema | Estado | Resolución (→ D1) |
|----|------|--------|-------------------|
| AMB-C07-07 | **Menú historial** | **Cerrado** (R-C1-13) | Proceso menú `pw_historialimportexcel` (o `excel_historial`) con `Permiso_Repo`; filtro default sin restricción de proceso si AccesoTotal. |
| AMB-C07-08 | **Filtro usuario** | **Cerrado** (R-C1-14) | Query `usuarioEjecucion` opcional; default todos los usuarios visibles según permisos proceso. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M-07-08 | Export historial | **Should** — reutilizar `GridExportButton` si se prioriza post-MVP epic. |
| AMB-M-07-09 | Vista vs query directa | Usar vista SQL §6; repository sobre vista. |

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-16)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-13 | Menú | Entrada `pq_menus` seed epic; `procedimiento = pw_historialimportexcel`. |
| R-C1-14 | Permiso historial | `Permiso_Repo` en procedimiento historial; filas filtradas por procesos donde usuario tiene repo en `ProcedimientoHost`. |
| R-C1-15 | Ruta frontend | `/excel-import/historial` y `/excel-import/lotes/:guid` (detalle). |

---

## 4) Impacto en Datos

### Vista

- `PQ_EXCEL_VW_HISTORIAL_IMPORTACIONES` — DDL §6 SQL MONO.

### Migración

- Incluir `CREATE VIEW` en migración de TR-GEN-07-carga-staging-excel **o** migración dedicada historial.

### Seed menú

- Ítem menú historial (flag epic / `EXCEL_IMPORT_ENABLED`).

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints

| Método | Path | Auth | Permiso | Público |
|--------|------|------|---------|---------|
| GET | `/api/v1/excel-import/historial` | Bearer + tenant | `Permiso_Repo` historial | No |

### 5.2 Detalle

#### GET `/api/v1/excel-import/historial`

**Query:**

| Param | Tipo | Descripción |
|-------|------|-------------|
| `codigoProceso` | string? | Filtro proceso |
| `estadoImportacion` | string? | Filtro estado |
| `usuarioEjecucion` | string? | Filtro usuario |
| `fechaDesde` | date? | ISO date |
| `fechaHasta` | date? | ISO date |
| `page` | int | default 1 |
| `pageSize` | int | default 50, max 200 |

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "",
  "resultado": {
    "items": [
      {
        "guidImportacion": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
        "codigoProceso": "ARTICULOS_ALTA",
        "nombreProceso": "Importacion de Articulos",
        "usuarioEjecucion": "jperez",
        "archivoOriginalNombre": "articulos.xlsx",
        "hojaSeleccionada": "Hoja1",
        "estadoImportacion": "procesada_parcial",
        "fechaInicio": "2026-06-16T14:30:00",
        "fechaFin": "2026-06-16T14:31:05",
        "cantidadFilasLeidas": 100,
        "cantidadFilasValidas": 85,
        "cantidadFilasConError": 15,
        "cantidadFilasProcesadas": 85,
        "cantidadFilasDescartadas": 0
      }
    ],
    "total": 42,
    "page": 1,
    "pageSize": 50
  }
}
```

**403:** sin permiso historial.

---

## 6) Cambios Frontend

### Pantallas
- `ExcelImportHistoryPage`: `DataGridDx` server-side (`excelHistoryGrid`).
- Filtros: `SelectBox` proceso, estado, `DateBox` rango.
- Acción fila: ícono ver detalle (`excelHistoryDetail`) → navigate `/excel-import/lotes/{guid}?readOnly=1`.

### i18n estados
- `excelImport.status.pendiente`, `.validada`, `.con_error_estructura`, `.lista_para_procesar`, `.procesando`, `.procesada`, `.procesada_parcial`, `.cancelada`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Vista + `ExcelImportHistoryRepository` | AC-01 |
| T2 | Backend | Controller historial + policy | AC-02–AC-04 |
| T3 | Frontend | Página historial + filtros | AC-01–AC-07 |
| T4 | Frontend | Navegación detalle readOnly | AC-05 |
| T5 | Backend | Seed menú `pw_historialimportexcel` | R-C1-13 |
| T6 | Tests | Feature filtros + 403 | §8 |

---

## 8) Estrategia de Tests

- **Integration:** listado paginado; filtro proceso; 401/403.
- **E2E:** abrir historial → ver detalle → grilla sin botón Procesar.

---

## 9) Riesgos y Edge Cases

- Muchos lotes → índices `IX_PQ_EXCEL_IMPORTACIONES_Proceso_Fecha` (ya en DDL).
- Lote muy antiguo sin filas (purga futura) → detalle vacío con mensaje i18n.

---

## 10) Checklist final

(Checklist transversal § plantilla TR.)










