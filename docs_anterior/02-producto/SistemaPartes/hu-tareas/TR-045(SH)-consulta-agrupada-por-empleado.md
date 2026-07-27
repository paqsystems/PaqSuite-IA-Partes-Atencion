# TR-045(SH) – Consulta agrupada por empleado

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-045(SH)-consulta-agrupada-por-empleado  |
| Épica              | Épica 9: Informes y Consultas              |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-044 (Consulta detallada)                |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-07                               |
| Estado             | Completado                                 |

---

## 1) HU Refinada

### Título
Consulta agrupada por empleado

### Narrativa
**Como** supervisor  
**Quiero** consultar tareas agrupadas por empleado  
**Para** analizar la dedicación de cada empleado.

### Contexto/Objetivo
El supervisor accede a la sección "Tareas por Empleado" (o "Resumen por Empleado") donde ve resultados agrupados por empleado en el período seleccionado. Se usan los mismos filtros que en Consulta Detallada (período, tipo de cliente, cliente, empleado). Cada grupo muestra nombre del empleado, total de horas en formato decimal y cantidad de tareas; es expandible (accordion o similar) para ver el detalle de tareas de ese empleado. El detalle muestra las mismas columnas que la consulta detallada. Se puede colapsar el grupo. Se muestra el total general de horas y tareas.

### Suposiciones explícitas
- El usuario ya está autenticado (HU-001). Solo supervisores acceden a esta funcionalidad.
- HU-044 (Consulta Detallada) está implementada; se reutilizan filtros y criterios de período (validación 1305).
- La tabla `PQ_PARTES_REGISTRO_TAREA` y tablas relacionadas (usuarios, clientes, tipos de tarea) existen.
- Horas en formato decimal (minutos / 60).
- Agrupación por `usuario_id`; los filtros aplican a todas las tareas antes de agrupar.

### In Scope
- Sección "Tareas por Empleado" accesible solo para supervisores.
- Mismos filtros que consulta detallada: período (fecha desde, fecha hasta), tipo de cliente, cliente, empleado (todos o específico).
- Resultados agrupados por empleado: nombre, total horas (decimal), cantidad de tareas.
- Cada grupo expandible (accordion o similar); al expandir, detalle de tareas con mismas columnas que consulta detallada (fecha, cliente, tipo tarea, horas, sin cargo, presencial, descripción).
- Se puede colapsar el grupo.
- Total general de horas y tareas.
- Validación de período: fecha_desde <= fecha_hasta (error 1305 si no).

### Out of Scope
- Acceso de empleado no supervisor (solo supervisor en esta HU).
- Exportación a Excel/PDF (HU-049).
- Gráficos en esta pantalla.
- Edición/eliminación de tareas desde esta vista.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la sección "Tareas por Empleado".
- **AC-02**: Se muestran los mismos filtros que en consulta detallada (período, tipo de cliente, cliente, empleado).
- **AC-03**: Los resultados se agrupan por empleado (usuario_id).
- **AC-04**: Cada grupo muestra: nombre del empleado, total de horas en formato decimal, cantidad de tareas.
- **AC-05**: Cada grupo es expandible (accordion o similar).
- **AC-06**: Al expandir un grupo se muestra el detalle de todas las tareas de ese empleado.
- **AC-07**: El detalle muestra las mismas columnas que la consulta detallada (fecha, cliente, tipo tarea, horas, sin cargo, presencial, descripción).
- **AC-08**: Se puede colapsar el grupo para ocultar el detalle.
- **AC-09**: Se muestra el total general de horas y tareas.
- **AC-10**: Si `fecha_desde > fecha_hasta`, se muestra error 1305 (validación de período).
- **AC-11**: Estados loading, vacío y error manejados (estado vacío alineado con HU-050 si aplica).

### Escenarios Gherkin

```gherkin
Feature: Consulta Agrupada por Empleado

  Scenario: Supervisor consulta resumen por empleado
    Given el supervisor está autenticado
    When accede a "Tareas por Empleado"
    And aplica período "2026-01-01" a "2026-01-31"
    Then se muestran grupos por cada empleado con tareas
    And cada grupo muestra nombre empleado, total horas y cantidad tareas
    And se muestra el total general de horas y tareas

  Scenario: Expandir grupo y ver detalle
    Given el supervisor está en "Tareas por Empleado"
    And hay al menos un grupo con tareas
    When expande el grupo del empleado "Juan Pérez"
    Then se muestra detalle de tareas con mismas columnas que consulta detallada
    When colapsa el grupo
    Then el detalle se oculta

  Scenario: Período inválido
    Given el supervisor está autenticado
    When aplica filtros con fecha_desde > fecha_hasta
    Then se muestra error 1305 (período inválido)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Agrupación por `usuario_id` (empleado) sobre las tareas que el supervisor puede ver (todas, o filtradas por cliente/empleado).
2. **RN-02**: Solo supervisores pueden acceder a "Tareas por Empleado".
3. **RN-03**: Totalización de horas en formato decimal (minutos / 60).
4. **RN-04**: Los filtros (período, tipo_cliente_id, cliente_id, usuario_id) aplican a todas las tareas antes de agrupar.
5. **RN-05**: Validación de período: `fecha_desde <= fecha_hasta`; si no se cumple, error 1305 (regla 8.1).

### Permisos por Rol
- **Supervisor:** Acceso completo a Tareas por Empleado; puede filtrar por empleado (todos o específico), cliente, tipo de cliente, período.
- **Empleado (no supervisor):** Sin acceso (403 o no mostrar enlace).
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: consultas de agregación agrupando por `usuario_id`, con filtros por período, cliente_id, tipo_cliente_id, usuario_id.
- Tablas relacionadas: usuarios (empleados), clientes, tipos de tarea (para nombres y detalle).

### Cambios en Datos
- No se requieren nuevas tablas ni columnas.
- Consultas de agregación (SUM duracion_minutos, COUNT) agrupando por usuario_id; índices existentes son suficientes.

### Seed Mínimo para Tests
- Tareas de varios empleados y clientes en distintos períodos.
- Usuario supervisor para tests de acceso y filtros.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/reports/by-employee`

**Descripción:** Obtener reporte de tareas agrupadas por empleado para el período indicado. Solo supervisores.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor; 403 si no.

**Query Parameters:**
```
?fecha_desde=2026-01-01
&fecha_hasta=2026-01-31
&tipo_cliente_id=1        (opcional)
&cliente_id=2             (opcional)
&usuario_id=3             (opcional: filtrar a un empleado; si no, todos)
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Reporte por empleado obtenido correctamente",
  "resultado": {
    "grupos": [
      {
        "usuario_id": 1,
        "nombre": "Juan Pérez",
        "code": "JPEREZ",
        "total_horas": 45.5,
        "cantidad_tareas": 12,
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

**Response 403:** Usuario no supervisor.

---

## 6) Cambios Frontend

### Pantallas/Componentes
- **TareasPorEmpleadoPage** (o **ResumenPorEmpleadoPage**): nueva pantalla con ruta protegida solo para supervisores.
- **Filtros:** período (fecha desde, fecha hasta), tipo de cliente, cliente, empleado (mismos que consulta detallada); botón "Aplicar Filtros".
- **Lista/Accordion por empleado:** cada ítem muestra nombre empleado, total horas, cantidad tareas; expandible/colapsable.
- **Detalle expandido:** tabla de tareas del empleado (fecha, cliente, tipo tarea, horas, sin cargo, presencial, descripción).
- **Total general:** bloque con total de horas y total de tareas del período.
- **Estado vacío:** mensaje cuando no hay grupos (alineado con HU-050 si aplica).

### Estados UI
- Loading: mientras se cargan datos.
- Empty: sin resultados (mensaje informativo).
- Error: mensaje por 1305 u otro error.

### data-testid sugeridos
- `tareasPorEmpleado.page`, `tareasPorEmpleado.filtros`, `tareasPorEmpleado.aplicarFiltros`, `tareasPorEmpleado.grupo.{usuario_id}`, `tareasPorEmpleado.grupoExpandir.{usuario_id}`, `tareasPorEmpleado.totalGeneral`, `tareasPorEmpleado.empty`, `tareasPorEmpleado.loading`, `tareasPorEmpleado.mensajeError`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | GET /api/v1/reports/by-employee con fecha_desde/hasta, tipo_cliente_id, cliente_id, usuario_id; validación 1305; solo supervisor | 200 con grupos; 422 (1305); 403 | TR-044 / ReportController | M |
| T2 | Frontend | Ruta /informes/tareas-por-empleado (SupervisorRoute); página con filtros y accordion por empleado | Cumple AC | TR-044 | M |
| T3 | Frontend | Accordion expandible/colapsable; detalle con columnas como consulta detallada; total general | Cumple AC | T2 | M |
| T4 | Frontend | Enlace "Tareas por Empleado" en Dashboard o menú (solo supervisores) | Enlace visible solo si supervisor | T2 | S |
| T5 | Tests    | Integration: GET by-employee 200, 422 (1305), 403; E2E supervisor accede, aplica filtros, expande grupo | Tests pasan | T1, T2, T3 | M |
| T6 | Docs     | Spec endpoint by-employee | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit/Integration (backend):** GET by-employee con supervisor retorna grupos; con empleado 403; fecha_desde > fecha_hasta retorna 422 (1305); filtros aplicados correctamente.
- **E2E (Playwright):** Login supervisor → acceder a Tareas por Empleado → aplicar filtros → ver grupos y total general → expandir un grupo → ver detalle → colapsar; sin waits ciegos; data-testid.

---

## 9) Riesgos y Edge Cases

- Muchos empleados: considerar orden de grupos (ej. por total horas descendente) y posible paginación futura.
- Empleado sin tareas en el período: puede no aparecer en grupos o aparecer con 0 horas según diseño.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend: endpoint by-employee + validación 1305 + 403
- [x] Frontend: ruta + filtros + accordion + detalle + total general
- [x] Enlace solo para supervisores
- [x] Tests ok
- [x] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/TaskService.php` — método `listByEmployeeReport`
- `backend/app/Http/Controllers/Api/V1/ReportController.php` — método `byEmployee`
- `backend/routes/api.php` — ruta GET `reports/by-employee`

### Frontend
- `frontend/src/features/tasks/services/task.service.ts` — tipos y `getReportByEmployee`
- `frontend/src/features/tasks/components/TareasPorEmpleadoPage.tsx` — página con filtros y accordion
- `frontend/src/features/tasks/components/TareasPorEmpleadoPage.css` — estilos
- `frontend/src/features/tasks/components/index.ts` — export de `TareasPorEmpleadoPage`
- `frontend/src/app/App.tsx` — ruta `/informes/tareas-por-empleado` con SupervisorRoute
- `frontend/src/app/Dashboard.tsx` — enlace "Tareas por Empleado" (solo supervisores)

### Tests
- `backend/tests/Feature/Api/V1/ReportControllerTest.php` — tests by_employee (200, 403, 422 1305, 401)
- `frontend/tests/e2e/tareas-por-empleado.spec.ts` — E2E Playwright: supervisor accede, aplica filtros, expande/colapsa grupo; empleado no ve enlace y es redirigido

## Comandos ejecutados

## Notas y decisiones

## Pendientes / follow-ups
