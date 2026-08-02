# TR-049(SH) – Exportación de consultas a Excel

| Campo              | Valor                                           |
|--------------------|-------------------------------------------------|
| HU relacionada     | HU-049(SH)-exportación-de-consultas-a-excel    |
| Épica              | Épica 9: Informes y Consultas                    |
| Prioridad          | SHOULD-HAVE                                     |
| Roles              | Empleado / Empleado Supervisor / Cliente        |
| Dependencias       | HU-044, HU-045, HU-046, HU-047, HU-048          |
| Clasificación      | HU SIMPLE                                       |
| Última actualización | 2026-02-07                                    |
| Estado             | Pendiente                                       |

---

## 1) HU Refinada

### Título
Exportación de consultas a Excel

### Narrativa
**Como** usuario (empleado, supervisor o cliente)  
**Quiero** exportar los resultados de las consultas a Excel  
**Para** analizar los datos fuera del sistema o compartirlos.

### Contexto/Objetivo
En cualquier pantalla de consulta (detallada, por empleado, por cliente, por tipo de tarea, por fecha) el usuario puede hacer clic en un botón "Exportar a Excel". El botón está habilitado solo si hay resultados. Si no hay resultados, se muestra mensaje "No hay datos para exportar" y el botón está deshabilitado. Los datos exportados respetan los mismos permisos que la consulta en pantalla (empleado: solo sus tareas; supervisor: todas; cliente: solo donde es el cliente). Se genera un archivo XLSX que se descarga automáticamente, con nombre descriptivo (ej. "Tareas_2025-01-01_2025-01-31.xlsx"). Para consulta detallada: todas las columnas de la tabla; para consulta agrupada: estructura de agrupación con totales y detalles expandidos. Horas en formato decimal; fechas formateadas correctamente.

### Suposiciones explícitas
- El usuario ya está autenticado (HU-001).
- Las consultas HU-044, HU-045, HU-046, HU-047, HU-048 están implementadas o en curso.
- Se usará una librería estándar para generar XLSX en frontend (ej. xlsx, exceljs) o un endpoint en backend que devuelva el archivo; el diseño puede elegir cliente o servidor.
- El nombre del archivo incluye información del período o filtros aplicados.
- Formato compatible con Excel (XLSX).

### In Scope
- Botón "Exportar a Excel" en cada pantalla de consulta (detallada, por empleado, por cliente, por tipo, por fecha).
- Botón habilitado solo si hay resultados; deshabilitado si no hay datos.
- Mensaje "No hay datos para exportar" cuando no hay resultados.
- Datos exportados respetan permisos del usuario (mismos filtros que la consulta en pantalla).
- Archivo XLSX generado y descarga automática.
- Nombre de archivo descriptivo (ej. "Tareas_2025-01-01_2025-01-31.xlsx").
- Consulta detallada: todas las columnas de la tabla en el Excel.
- Consulta agrupada: estructura de agrupación con totales y detalles expandidos.
- Horas en formato decimal en el Excel.
- Fechas formateadas correctamente en el Excel.

### Out of Scope
- Exportación a PDF (otra HU o iteración).
- Programación de exportaciones automáticas o envío por email.
- Edición del contenido del Excel antes de descargar.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario puede hacer clic en un botón "Exportar a Excel" en cualquier consulta (detallada, por empleado, por cliente, por tipo, por fecha).
- **AC-02**: El botón está habilitado solo si hay resultados para exportar.
- **AC-03**: Si no hay resultados, se muestra el mensaje "No hay datos para exportar" y el botón está deshabilitado.
- **AC-04**: Los datos exportados respetan los permisos del usuario: Empleado solo sus tareas; Supervisor todas; Cliente solo donde es el cliente.
- **AC-05**: Al hacer clic, se genera un archivo Excel con los datos de la consulta (filtrados según permisos).
- **AC-06**: El archivo se descarga automáticamente.
- **AC-07**: El nombre del archivo es descriptivo (ej. "Tareas_2025-01-01_2025-01-31.xlsx" o incluyendo período/filtros).
- **AC-08**: Consulta detallada: el archivo contiene todas las columnas de la tabla.
- **AC-09**: Consulta agrupada: el archivo contiene estructura de agrupación con totales y detalles expandidos.
- **AC-10**: Las horas se muestran en formato decimal en el Excel.
- **AC-11**: Las fechas se formatean correctamente en el Excel.
- **AC-12**: El formato del archivo es compatible con Excel (XLSX).

### Escenarios Gherkin

```gherkin
Feature: Exportación a Excel

  Scenario: Usuario exporta consulta detallada con resultados
    Given el usuario está autenticado
    And está en la consulta detallada con resultados
    When hace clic en "Exportar a Excel"
    Then se descarga un archivo XLSX
    And el archivo contiene todas las columnas de la tabla
    And las horas están en formato decimal
    And las fechas están formateadas correctamente

  Scenario: Botón deshabilitado sin resultados
    Given el usuario está en una consulta sin resultados
    Then el botón "Exportar a Excel" está deshabilitado
    And se muestra el mensaje "No hay datos para exportar"

  Scenario: Exportación respeta permisos del empleado
    Given el empleado está autenticado
    And la consulta muestra solo sus tareas
    When exporta a Excel
    Then el archivo contiene solo sus tareas

  Scenario: Exportación respeta permisos del cliente
    Given el cliente está autenticado
    And la consulta muestra solo tareas donde es el cliente
    When exporta a Excel
    Then el archivo contiene solo esas tareas

  Scenario: Nombre de archivo descriptivo
    Given el usuario aplicó período "2026-01-01" a "2026-01-31"
    When exporta a Excel
    Then el nombre del archivo incluye información del período o es descriptivo
```

---

## 3) Reglas de Negocio

1. **RN-01**: El botón "Exportar a Excel" debe estar deshabilitado si `resultados.length === 0` (o equivalente).
2. **RN-02**: Los datos exportados deben respetar los mismos filtros automáticos que las consultas en pantalla (regla 8.2 por rol).
3. **RN-03**: Formato de horas en el Excel: decimal (minutos / 60 o equivalente).
4. **RN-04**: El nombre del archivo debe incluir información del período o filtros aplicados (ej. fechas).
5. **RN-05**: Formato de archivo: XLSX compatible con Excel.

### Permisos por Rol
- **Empleado (no supervisor):** Exporta solo sus tareas (mismo conjunto que ve en pantalla).
- **Supervisor:** Exporta todas las tareas que ve en la consulta (según filtros aplicados).
- **Cliente:** Exporta solo tareas donde es el cliente (mismo conjunto que ve en pantalla).

---

## 4) Impacto en Datos

### Tablas Afectadas
- No se requieren cambios en base de datos. La exportación usa los mismos datos que ya devuelven los endpoints de reportes (HU-044, HU-045, HU-046, HU-047, HU-048).

### Cambios en Datos
- Ninguno. Solo lectura de datos existentes para generar el archivo.

### Seed Mínimo para Tests
- Datos existentes de las consultas; tests verifican que el contenido exportado coincide con lo mostrado en pantalla y respeta permisos.

---

## 5) Contratos de API (opcional)

Si la exportación se implementa en **backend** (endpoint que devuelve el archivo):

### Endpoint: GET `/api/v1/reports/export?tipo=detallada|by-employee|by-client|by-task-type|by-date&fecha_desde=...&fecha_hasta=...` (y demás filtros)

**Descripción:** Generar y descargar archivo Excel con los datos del reporte indicado, respetando permisos del usuario.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Según rol; datos filtrados igual que el reporte correspondiente.

**Response 200:** `Content-Disposition: attachment; filename="Tareas_2026-01-01_2026-01-31.xlsx"` y cuerpo binario XLSX.

**Response 400/422:** Si no hay datos para exportar o parámetros inválidos (opcional: devolver JSON con mensaje "No hay datos para exportar").

Si la exportación se implementa en **frontend** (generar XLSX en el cliente a partir de los datos ya cargados), no se requiere nuevo endpoint; solo reutilizar los datos de la consulta actual y una librería (xlsx, exceljs, etc.).

---

## 6) Cambios Frontend

### Pantallas/Componentes
- **Todas las páginas de consulta:** Consulta detallada, Tareas por Empleado, Tareas por Cliente, Tareas por Tipo, Tareas por Fecha.
- En cada una: botón "Exportar a Excel" (habilitado solo si hay resultados).
- Mensaje "No hay datos para exportar" y botón deshabilitado cuando no hay resultados.
- Lógica de generación de Excel: ya sea llamada a endpoint de exportación (backend) o generación en cliente con los datos actuales (librería XLSX).
- Nombre de archivo: construir con período/filtros (ej. `Tareas_${fechaDesde}_${fechaHasta}.xlsx`).

### Estados UI
- Botón habilitado/deshabilitado según existencia de resultados.
- Durante la generación/descarga: opcional indicador de carga (evitar doble clic).
- No se requiere estado de error crítico si la descarga falla; se puede mostrar toast o mensaje puntual.

### data-testid sugeridos
- `exportarExcel.boton`, `exportarExcel.deshabilitado`, `exportarExcel.mensajeSinDatos` (en cada página de consulta).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | (Opcional) Endpoint GET export que genere XLSX y devuelva archivo; mismos filtros que reportes; nombre archivo en header | 200 con attachment XLSX; 400 si sin datos | TR-044, reportes | M |
| T2 | Frontend | Botón "Exportar a Excel" en consulta detallada; habilitado solo con resultados; mensaje "No hay datos para exportar" | Cumple AC | TR-044 | M |
| T3 | Frontend | Botón "Exportar a Excel" en Tareas por Empleado, por Cliente, por Tipo, por Fecha; misma lógica habilitado/mensaje | Cumple AC | TR-045, TR-046, TR-047, TR-048 | M |
| T4 | Frontend | Generación XLSX: columnas detallada; agrupaciones con totales y detalle; horas decimal; fechas formateadas; nombre archivo descriptivo | Cumple AC | T2, T3 | M |
| T5 | Tests    | Unit: lógica de nombre de archivo y formato; E2E: usuario con resultados hace clic Exportar y se descarga archivo; sin resultados botón deshabilitado | Tests pasan | T2, T3, T4 | M |
| T6 | Docs     | Documentar opción elegida (backend vs frontend) | Docs actualizados | T1 o T4 | S |

---

## 8) Estrategia de Tests

- **Unit (frontend):** Función que construye nombre de archivo; función que transforma datos de consulta a estructura Excel (si se hace en cliente).
- **Integration (backend):** Si hay endpoint export: 200 con archivo XLSX válido; sin datos retorna 400 o mensaje adecuado; permisos por rol.
- **E2E (Playwright):** Usuario en consulta con resultados → clic Exportar a Excel → verificar que se inicia descarga (o que el archivo tiene contenido esperado); usuario en consulta sin resultados → botón deshabilitado y mensaje "No hay datos para exportar".

---

## 9) Riesgos y Edge Cases

- Volumen grande de datos: exportación puede tardar; considerar límite de filas o mensaje de advertencia en iteración posterior.
- Navegadores: asegurar que la descarga de archivo funcione en los entornos objetivo (blob + link download o endpoint con Content-Disposition).

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Botón en todas las consultas; habilitado/deshabilitado y mensaje sin datos
- [ ] Datos exportados respetan permisos por rol
- [ ] Archivo XLSX con columnas/totales correctos; horas decimal; fechas formateadas
- [ ] Nombre de archivo descriptivo
- [ ] Tests ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Frontend
- `frontend/package.json` — dependencia `xlsx`
- `frontend/src/features/tasks/utils/exportToExcel.ts` — utilidad: buildExportFileName, exportDetailToExcel, exportGroupedToExcel
- `frontend/src/features/tasks/components/ConsultaDetalladaPage.tsx` — botón Exportar a Excel + mensaje sin datos
- `frontend/src/features/tasks/components/ConsultaDetalladaPage.css` — estilos del botón y fila total
- `frontend/src/features/tasks/components/TareasPorClientePage.tsx` — botón Exportar a Excel
- `frontend/src/features/tasks/components/TareasPorClientePage.css` — estilos
- `frontend/src/features/tasks/components/TareasPorEmpleadoPage.tsx` — botón Exportar a Excel
- `frontend/src/features/tasks/components/TareasPorEmpleadoPage.css` — estilos
- `frontend/src/features/tasks/components/TareasPorTipoPage.tsx` — botón Exportar a Excel
- `frontend/src/features/tasks/components/TareasPorTipoPage.css` — estilos
- `frontend/src/features/tasks/components/TareasPorFechaPage.tsx` — botón Exportar a Excel
- `frontend/src/features/tasks/components/TareasPorFechaPage.css` — estilos

## Comandos ejecutados

- `npm install xlsx@^0.18.5 --save` (frontend)

## Notas y decisiones

- Exportación implementada 100% en frontend: se genera el XLSX a partir de los datos ya cargados en cada consulta (detallada o agrupada). Nombre de archivo con período y sufijo (por-cliente, por-empleado, por-tipo, por-fecha). Botón deshabilitado cuando no hay resultados; mensaje "No hay datos para exportar" visible en ese caso.

## Pendientes / follow-ups

- Unit test para exportToExcel (buildExportFileName, estructura del workbook) y E2E para clic en Exportar con resultados (opcional en iteración posterior).
