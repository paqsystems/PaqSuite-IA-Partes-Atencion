# HU-006 – Exportación a Excel desde grillas

## Épica
000 – Generalidades

## Clasificación
SHOULD-HAVE

## Rol
Usuario que opera con grillas (listados o ABMs)

## Narrativa

Como usuario que trabajo con grillas quiero exportar los datos visibles a Excel para analizarlos fuera del sistema, compartirlos o archivarlos, con la opción de elegir el nivel de formato según mi necesidad.

## Contexto / Objetivo

Toda grilla del sistema (según estándar de HU-001 y `.cursor/rules/24-devextreme-grid-standards.md`) debe ofrecer un botón o acción para exportar a Excel. Se ofrecen tres modalidades de exportación según el tipo de uso: planilla básica, planilla formateada (con totales y formatos) o, en caso de pivots, tabla dinámica.

## Suposiciones explícitas

- Las grillas del proyecto usan DevExtreme DataGrid (o equivalente).
- Las grillas se identifican por `proceso` + `grid_id` (ver HU-001).
- El proyecto ya usa `xlsx` en algunas consultas; se puede reutilizar o integrar con ExcelJS/DevExtreme export según la arquitectura.
- Los datos exportados respetan los mismos filtros, ordenamiento y permisos que la grilla en pantalla.

## In scope

- Botón "Exportar a Excel" en la toolbar de cada grilla.
- Tres modalidades de exportación según el tipo de grilla y preferencia del usuario.
- Exportación de los datos actualmente visibles (filtros aplicados, paginación: exportar página actual o total según configuración).
- Respeto de permisos: solo se exportan datos a los que el usuario tiene acceso.

## Out of scope

- Exportación programática o batch.
- Exportación a PDF (otra HU si aplica).
- Personalización de columnas solo para exportación (se usa la configuración actual de la grilla).

---

## Criterios de aceptación

### AC1 – Disponibilidad del botón

- El botón "Exportar a Excel" está disponible en la toolbar de cada grilla.
- El botón está habilitado solo si hay datos para exportar.
- Si no hay datos, se muestra mensaje "No hay datos para exportar" y el botón está deshabilitado.
- El botón tiene `data-testid="grid.{proceso}.{grid_id}.exportExcel"`. Ejemplo: `grid.clientes.default.exportExcel`.

### AC2 – Modalidad 1: Planilla básica

- Exporta los datos en formato plano: encabezados + filas en una hoja.
- Sin formato de celdas (colores, tipos de dato Excel).
- Sin totales en pie.
- Datos crudos (fechas como texto, números sin formato).
- Útil para importaciones masivas o procesamiento externo.

### AC3 – Modalidad 2: Planilla formateada

- Títulos resaltados (encabezados con negrita, fondo).
- Datos formateados: fechas en formato legible (dd/mm/yyyy o según locale), números con separadores de miles y decimales.
- Totales en pie: sumas en columnas de importes y cantidades (según definición de columnas numéricas en la grilla).
- Conteo de filas.
- Ancho de columnas ajustado para legibilidad.

### AC4 – Modalidad 3: Tabla dinámica (para pivots)

- Cuando la grilla es un pivot (PivotGrid o vista pivot), se exporta como tabla dinámica de Excel.
- Permite al usuario interactuar con la tabla dinámica en Excel (expandir/colapsar, filtrar).
- Estructura de filas/columnas/valores preservada.

### AC5 – Selección de modalidad

- El usuario puede elegir la modalidad antes de exportar (dropdown, menú, o diálogo).
- Por defecto: planilla formateada (modalidad 2).
- Opción de "exportar solo filas seleccionadas" si la grilla tiene selección múltiple (según estándar de grillas).

### AC6 – Datos exportados

- Se exportan los datos que corresponden a la vista actual: filtros, ordenamiento y agrupación aplicados.
- Si hay paginación: opción de exportar "página actual" o "todas las filas" (puede requerir límite razonable, ej. 10.000).

### AC7 – Escenarios Gherkin

```gherkin
Scenario: Usuario exporta grilla plana básica
  Given estoy en la grilla de clientes con datos
  When selecciono "Planilla básica" en el menú de exportación
  And hago clic en "Exportar a Excel"
  Then se descarga un archivo XLSX con encabezados y datos sin formato
  And el nombre del archivo incluye el proceso y fecha (ej. clientes_2025-02-27.xlsx)

Scenario: Usuario exporta grilla formateada con totales
  Given estoy en la grilla de tareas con columnas de importes y cantidades
  When selecciono "Planilla formateada" en el menú de exportación
  And hago clic en "Exportar a Excel"
  Then se descarga un archivo XLSX con títulos resaltados
  And las fechas y números están formateados
  And hay fila de totales con sumas en columnas numéricas

Scenario: Usuario exporta pivot como tabla dinámica
  Given estoy en una vista pivot (ej. consulta agrupada por cliente y empleado)
  When selecciono "Tabla dinámica" en el menú de exportación
  And hago clic en "Exportar a Excel"
  Then se descarga un archivo XLSX con tabla dinámica de Excel
  And puedo expandir/colapsar en Excel

Scenario: Sin datos no se puede exportar
  Given estoy en una grilla sin datos (o filtros que no devuelven resultados)
  When observo el botón "Exportar a Excel"
  Then el botón está deshabilitado
  And se muestra "No hay datos para exportar"
```

---

## Reglas de negocio

1. Los datos exportados respetan los permisos del usuario (misma lógica que la grilla en pantalla).
2. Los filtros aplicados en la grilla se aplican al exportar.
3. El nombre del archivo debe ser descriptivo: `{proceso}_{fecha}.xlsx` o `{proceso}_{filtros_relevantes}.xlsx`.
4. Límite razonable de filas para exportación total (ej. 10.000) para evitar timeouts o archivos excesivos; si se supera, informar al usuario.
5. Formato de archivo: XLSX (compatible con Excel y LibreOffice).

---

## Impacto en datos

- No se crean tablas nuevas.
- Los datos se leen de las mismas fuentes que la grilla (API, store).
- No se persiste preferencia de modalidad por usuario (opcional a futuro).

---

## Contratos de API

- No se requieren endpoints nuevos; la exportación se realiza en el cliente con los datos ya cargados en la grilla.
- Si la grilla usa paginación del servidor y no tiene todos los datos en memoria, puede requerirse un endpoint de exportación que aplique filtros y devuelva el dataset completo (o paginado). En ese caso, el contrato dependerá del proceso específico.

---

## Cambios Frontend

### Componentes afectados

- **DataGridDX / wrapper de grillas:** Agregar botón de exportación en toolbar.
- **Cada pantalla con grilla:** Integrar botón de exportación según el estándar.
- **Utilidades de exportación:** Extender o crear módulo compartido para las tres modalidades.
- **PivotGrid (si existe):** Configurar exportación como tabla dinámica.

### Integración con DevExtreme

- DevExtreme DataGrid ofrece `export` con `enabled: true` y `ExcelJS` / `excelExporter`.
- Opción alternativa: usar `xlsx` (ya en el proyecto) para control fino sobre formato.
- Para tabla dinámica: ExcelJS o DevExtreme PivotGrid export.

### data-testid

- `grid.{proceso}.{grid_id}.exportExcel` – botón exportar.
- `grid.{proceso}.{grid_id}.exportExcel.menu` – menú de modalidades (si aplica).
- `grid.{proceso}.{grid_id}.exportExcel.option.basic` – opción planilla básica.
- `grid.{proceso}.{grid_id}.exportExcel.option.formatted` – opción planilla formateada.
- `grid.{proceso}.{grid_id}.exportExcel.option.pivot` – opción tabla dinámica.

### Accesibilidad

- `aria-label` con `t("grid.export.aria", "Exportar a Excel")`.

---

## Plan de tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Frontend | Utilidad de exportación: planilla básica (xlsx o ExcelJS) | Función que genera XLSX sin formato, descarga | - |
| T2 | Frontend | Utilidad de exportación: planilla formateada (títulos, formatos, totales) | Función que genera XLSX formateado, con totales | T1 |
| T3 | Frontend | Utilidad de exportación: tabla dinámica para pivots | Función que genera XLSX con pivot table | - |
| T4 | Frontend | Añadir botón Exportar en toolbar de DataGrid (estándar grillas) | Botón visible, deshabilitado si sin datos, data-testid | HU-001 |
| T5 | Frontend | Menú/dropdown de modalidades (básica, formateada, pivot) | Usuario elige modalidad antes de exportar | T4 |
| T6 | Frontend | Integrar exportación en grillas existentes (DataGridDX, pantallas) | Cada grilla con proceso+grid_id tiene export | T4, T1, T2 |
| T7 | Frontend | Lógica para exportar página actual vs todas las filas (si paginación) | Opción según configuración de grilla | T6 |
| T8 | Tests | Unit: utilidades de exportación (planilla básica, formateada) | Tests pasan | T1, T2 |
| T9 | Tests | E2E: exportar desde grilla, verificar descarga y contenido | Playwright cubre al menos 1 grilla | T6 |
| T10 | Docs | Actualizar 24-devextreme-grid-standards con exportación | Regla incluye export como característica obligatoria | T6 |

---

## Estrategia de tests

### Unit

- Utilidades de exportación: dado un dataset, el XLSX generado contiene los datos esperados.
- Planilla formateada: totales correctos, formatos de fecha/número según locale.

### Integration

- No aplica (exportación es cliente-side con datos ya cargados).

### E2E (Playwright)

- Navegar a pantalla con grilla → clic en Exportar → verificar que se descarga archivo.
- Verificar nombre de archivo y que contiene datos (opcional: leer XLSX con librería).

---

## Riesgos y edge cases

- **Grillas con muchos datos:** Límite de filas; informar si se supera.
- **Exportación con agrupación:** Preservar estructura de grupos en la planilla formateada.
- **Pivot:** No todas las grillas son pivot; la opción "tabla dinámica" solo debe mostrarse en vistas pivot.

---

## Dependencias

- **HU-001 (Layouts persistentes de grillas)** – estándar de grillas, identificación proceso+grid_id.
- `.cursor/rules/24-devextreme-grid-standards.md` – características obligatorias de grillas.
- DevExtreme DataGrid (o equivalente) con export.
- Librería `xlsx` (ya en proyecto) o `exceljs` / `devextreme-exceljs-fork` para exportación avanzada.

---

## Especificación (Open-Spec)

- [SPEC-006 – Exportación Excel desde grillas](../../05-open-spec/000-Generalidades/SPEC-006-exportacion-excel-grillas.md)

## Referencias

- `docs/03-historias-usuario/000-Generalidades/HU-001-layouts-grilla.md` – Layouts y identificación de grillas
- `.cursor/rules/24-devextreme-grid-standards.md` – Estándar de grillas DevExtreme
- `frontend/src/features/tasks/utils/exportToExcel.ts` – Utilidad existente de exportación
- [DevExtreme DataGrid - Export](https://js.devexpress.com/React/Documentation/ApiReference/UI_Components/dxDataGrid/Configuration/export/)
- [DevExtreme excelExporter](https://js.devexpress.com/React/Documentation/ApiReference/Common/Utils/excelExporter/)
