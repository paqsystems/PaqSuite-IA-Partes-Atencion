# TR-048(SH) – Consulta agrupada por fecha

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-048(SH)-consulta-agrupada-por-fecha     |
| Épica              | Épica 9: Informes y Consultas              |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado / Empleado Supervisor / Cliente  |
| Dependencias       | HU-044 (Consulta detallada)                |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-07                               |
| Estado             | Pendiente                                  |

---

## 1) HU Refinada

### Título
Consulta agrupada por fecha

### Narrativa
**Como** usuario (empleado, supervisor o cliente)  
**Quiero** consultar tareas agrupadas por fecha  
**Para** analizar la distribución del trabajo en el tiempo.

### Contexto/Objetivo
El usuario accede a la sección "Tareas por Fecha" donde ve resultados agrupados por fecha en el período seleccionado. Se muestran filtros de período (fecha desde, fecha hasta). Los resultados se filtran automáticamente según permisos del usuario (empleado: solo sus tareas; supervisor: todas; cliente: solo donde es el cliente). Cada grupo muestra fecha (formato legible), total de horas en formato decimal y cantidad de tareas; es expandible para ver el detalle de tareas de esa fecha. El detalle muestra las mismas columnas que la consulta detallada. Se puede colapsar el grupo. Se muestra el total general. Las fechas se ordenan cronológicamente.

### Suposiciones explícitas
- El usuario ya está autenticado (HU-001).
- HU-044 (Consulta Detallada) está implementada; la lógica de filtros por rol (empleado/supervisor/cliente) se reutiliza.
- La tabla `PQ_PARTES_REGISTRO_TAREA` y tablas relacionadas existen.
- Horas en formato decimal (minutos / 60).
- Agrupación por `fecha`; filtros automáticos por rol (regla 8.2) aplican antes de agrupar.
- Ordenamiento cronológico (más reciente primero o más antigua primero, según diseño).

### In Scope
- Sección "Tareas por Fecha" accesible según rol (empleado, supervisor, cliente).
- Filtros de período (fecha desde, fecha hasta) con botón aplicar.
- Resultados filtrados por rol: empleado solo sus tareas; supervisor todas; cliente solo donde es el cliente.
- Resultados agrupados por fecha: fecha (legible), total horas (decimal), cantidad de tareas.
- Cada grupo expandible (accordion o similar); al expandir, detalle de tareas con mismas columnas que consulta detallada.
- Se puede colapsar el grupo.
- Total general de horas y tareas.
- Fechas ordenadas cronológicamente.
- Validación de período: fecha_desde <= fecha_hasta (error 1305 si no).

### Out of Scope
- Exportación a Excel/PDF (HU-049).
- Gráficos en esta pantalla.
- Edición/eliminación de tareas desde esta vista.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario puede acceder a la sección "Tareas por Fecha".
- **AC-02**: Se muestran filtros de período (fecha desde, fecha hasta) y botón para aplicar.
- **AC-03**: Los resultados se filtran según permisos del usuario: Empleado (no supervisor) solo sus tareas; Supervisor todas; Cliente solo tareas donde es el cliente.
- **AC-04**: Los resultados se agrupan por fecha.
- **AC-05**: Cada grupo muestra: fecha (formato legible), total de horas en formato decimal, cantidad de tareas.
- **AC-06**: Cada grupo es expandible (accordion o similar).
- **AC-07**: Al expandir un grupo se muestra el detalle de todas las tareas de esa fecha (según permisos).
- **AC-08**: El detalle muestra las mismas columnas que la consulta detallada.
- **AC-09**: Se puede colapsar el grupo para ocultar el detalle.
- **AC-10**: Se muestra el total general de horas y tareas.
- **AC-11**: Las fechas se ordenan cronológicamente (más reciente primero o más antigua primero, según diseño).
- **AC-12**: Si `fecha_desde > fecha_hasta`, se muestra error 1305 (validación de período).
- **AC-13**: Estados loading, vacío y error manejados (estado vacío alineado con HU-050 si aplica).

### Escenarios Gherkin

```gherkin
Feature: Consulta Agrupada por Fecha

  Scenario: Supervisor consulta resumen por fecha
    Given el supervisor está autenticado
    When accede a "Tareas por Fecha"
    And aplica período "2026-01-01" a "2026-01-31"
    Then se muestran grupos por cada fecha con tareas
    And cada grupo muestra fecha, total horas y cantidad tareas
    And las fechas están ordenadas cronológicamente
    And se muestra el total general de horas y tareas

  Scenario: Expandir grupo y ver detalle
    Given el usuario está en "Tareas por Fecha"
    And hay al menos un grupo con tareas
    When expande el grupo de la fecha "2026-01-15"
    Then se muestra detalle de tareas con mismas columnas que consulta detallada
    When colapsa el grupo
    Then el detalle se oculta

  Scenario: Empleado ve solo sus tareas agrupadas por fecha
    Given el empleado está autenticado
    When accede a "Tareas por Fecha"
    Then se muestran solo grupos de fechas donde él tiene tareas
    And el total general corresponde solo a sus tareas

  Scenario: Cliente ve solo su dedicación por fecha
    Given el cliente está autenticado
    When accede a "Tareas por Fecha"
    Then se muestran solo grupos de fechas donde es el cliente
    And el total general corresponde solo a tareas donde es el cliente

  Scenario: Período inválido
    Given el usuario está autenticado
    When aplica filtros con fecha_desde > fecha_hasta
    Then se muestra error 1305 (período inválido)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Agrupación por `fecha` sobre las tareas que el usuario puede ver según su rol.
2. **RN-02**: Empleado (no supervisor) solo ve tareas donde `usuario_id` = su `usuario_id` (regla 8.2.2).
3. **RN-03**: Supervisor ve todas las tareas de todos los usuarios.
4. **RN-04**: Cliente solo ve tareas donde `cliente_id` = su `cliente_id` (regla 8.2.1).
5. **RN-05**: Totalización de horas en formato decimal (minutos / 60).
6. **RN-06**: Ordenamiento cronológico de grupos (fecha descendente o ascendente según diseño).
7. **RN-07**: Validación de período: `fecha_desde <= fecha_hasta`; si no se cumple, error 1305 (regla 8.1).

### Permisos por Rol
- **Empleado (no supervisor):** Acceso a Tareas por Fecha; solo sus tareas agrupadas por fecha.
- **Supervisor:** Acceso a Tareas por Fecha; todas las tareas agrupadas por fecha.
- **Cliente:** Acceso a Tareas por Fecha; solo tareas donde es el cliente.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: consultas de agregación agrupando por `fecha`, con filtros por `usuario_id` (empleado), `cliente_id` (cliente), rango de fechas.
- Tablas relacionadas: usuarios, clientes, tipos de tarea (para detalle).

### Cambios en Datos
- No se requieren nuevas tablas ni columnas.
- Consultas de agregación (SUM duracion_minutos, COUNT) agrupando por fecha; índices existentes son suficientes.

### Seed Mínimo para Tests
- Tareas de varios usuarios, clientes y fechas en distintos períodos.
- Usuario empleado, supervisor y cliente para tests por rol.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/reports/by-date`

**Descripción:** Obtener reporte de tareas agrupadas por fecha para el período indicado, con filtros automáticos por rol.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Empleado, Supervisor o Cliente; datos filtrados según rol (regla 8.2).

**Query Parameters:**
```
?fecha_desde=2026-01-01
&fecha_hasta=2026-01-31
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Reporte por fecha obtenido correctamente",
  "resultado": {
    "grupos": [
      {
        "fecha": "2026-01-15",
        "total_horas": 8.5,
        "cantidad_tareas": 4,
        "tareas": [
          {
            "id": 1,
            "fecha": "2026-01-15",
            "cliente": { "id": 1, "nombre": "Cliente A" },
            "tipo_tarea": { "id": 2, "descripcion": "Desarrollo" },
            "horas": 2.5,
            "sin_cargo": false,
            "presencial": true,
            "descripcion": "Desarrollo de feature X..."
          }
        ]
      }
    ],
    "total_general_horas": 120.25,
    "total_general_tareas": 48
  }
}
```

**Response 422 (período inválido):**
```json
{
  "error": 1305,
  "respuesta": "El período es inválido: fecha_desde debe ser menor o igual a fecha_hasta",
  "resultado": {}
}
```

---

## 6) Cambios Frontend

### Pantallas/Componentes
- **TareasPorFechaPage** (o **ResumenPorFechaPage**): nueva pantalla con ruta accesible según rol (empleado, supervisor, cliente).
- **Filtros:** período (fecha desde, fecha hasta); botón "Aplicar Filtros".
- **Lista/Accordion por fecha:** cada ítem muestra fecha (legible), total horas, cantidad tareas; expandible/colapsable.
- **Detalle expandido:** tabla de tareas de esa fecha (fecha, cliente, tipo tarea, horas, sin cargo, presencial, descripción).
- **Total general:** bloque con total de horas y total de tareas del período.
- **Estado vacío:** mensaje cuando no hay grupos (alineado con HU-050 si aplica).

### Estados UI
- Loading: mientras se cargan datos.
- Empty: sin resultados (mensaje informativo).
- Error: mensaje por 1305 u otro error.

### data-testid sugeridos
- `tareasPorFecha.page`, `tareasPorFecha.filtros`, `tareasPorFecha.aplicarFiltros`, `tareasPorFecha.grupo.{fecha}`, `tareasPorFecha.grupoExpandir.{fecha}`, `tareasPorFecha.totalGeneral`, `tareasPorFecha.empty`, `tareasPorFecha.loading`, `tareasPorFecha.mensajeError`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | GET /api/v1/reports/by-date con fecha_desde/hasta; filtros por rol (empleado/supervisor/cliente); validación 1305 | 200 con grupos; 422 (1305) | TR-044 / ReportController | M |
| T2 | Frontend | Ruta /informes/tareas-por-fecha; página con filtros y accordion por fecha; acceso según rol | Cumple AC | TR-044 | M |
| T3 | Frontend | Accordion expandible/colapsable; detalle con columnas como consulta detallada; total general; orden cronológico | Cumple AC | T2 | M |
| T4 | Frontend | Enlace "Tareas por Fecha" en Dashboard o menú (visible para empleado, supervisor, cliente) | Enlace visible según rol | T2 | S |
| T5 | Tests    | Integration: GET by-date 200, 422 (1305); filtros por rol; E2E usuario accede, aplica filtros, expande grupo | Tests pasan | T1, T2, T3 | M |
| T6 | Docs     | Spec endpoint by-date | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit/Integration (backend):** GET by-date con empleado retorna solo sus tareas; con supervisor todas; con cliente solo donde es cliente; fecha_desde > fecha_hasta retorna 422 (1305); grupos ordenados cronológicamente.
- **E2E (Playwright):** Login según rol → acceder a Tareas por Fecha → aplicar filtros → ver grupos y total general → expandir un grupo → ver detalle → colapsar; sin waits ciegos; data-testid.

---

## 9) Riesgos y Edge Cases

- Período muy amplio: muchos grupos por fecha; considerar paginación o límite en iteración posterior.
- Fecha sin tareas: puede no aparecer en grupos según diseño (solo fechas con al menos una tarea).

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: endpoint by-date + validación 1305 + filtros por rol
- [ ] Frontend: ruta + filtros + accordion + detalle + total general + orden cronológico
- [ ] Enlace visible según rol
- [ ] Tests ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/TaskService.php` — método `listByDateReport`
- `backend/app/Http/Controllers/Api/V1/ReportController.php` — método `byDate`
- `backend/routes/api.php` — ruta GET `reports/by-date`

### Frontend
- `frontend/src/features/tasks/services/task.service.ts` — tipos y `getReportByDate`
- `frontend/src/features/tasks/components/TareasPorFechaPage.tsx` — página con filtros y accordion
- `frontend/src/features/tasks/components/TareasPorFechaPage.css` — estilos
- `frontend/src/features/tasks/components/index.ts` — export de `TareasPorFechaPage`
- `frontend/src/app/App.tsx` — ruta `/informes/tareas-por-fecha` (acceso empleado/supervisor/cliente)
- `frontend/src/app/Dashboard.tsx` — enlace "Tareas por Fecha"

### Tests
- `backend/tests/Feature/Api/V1/ReportControllerTest.php` — tests by_date (200, 422 1305, 401)
- `frontend/tests/e2e/tareas-por-fecha.spec.ts` — E2E Playwright: empleado accede, aplica filtros

## Comandos ejecutados

- (Véase TR-047)

## Notas y decisiones

- Filtros por rol (empleado/supervisor/cliente) reutilizados de listByClientReport.

## Pendientes / follow-ups

- Ninguno.
