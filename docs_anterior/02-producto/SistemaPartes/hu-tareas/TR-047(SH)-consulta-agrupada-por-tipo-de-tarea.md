# TR-047(SH) – Consulta agrupada por tipo de tarea

| Campo              | Valor                                                |
|--------------------|------------------------------------------------------|
| HU relacionada     | HU-047(SH)-consulta-agrupada-por-tipo-de-tarea       |
| Épica              | Épica 9: Informes y Consultas                        |
| Prioridad          | SHOULD-HAVE                                          |
| Roles              | Empleado Supervisor                                  |
| Dependencias       | HU-044 (Consulta detallada)                          |
| Clasificación      | HU SIMPLE                                            |
| Última actualización | 2026-02-07                                         |
| Estado             | Pendiente                                            |

---

## 1) HU Refinada

### Título
Consulta agrupada por tipo de tarea

### Narrativa
**Como** supervisor  
**Quiero** consultar tareas agrupadas por tipo de tarea  
**Para** analizar la distribución del trabajo por tipo.

### Contexto/Objetivo
El supervisor accede a la sección "Tareas por Tipo" donde ve resultados agrupados por tipo de tarea en el período seleccionado. Se usan los mismos filtros que en Consulta Detallada. Cada grupo muestra descripción del tipo de tarea, total de horas en formato decimal y cantidad de tareas; es expandible (accordion o similar) para ver el detalle de tareas de ese tipo. El detalle muestra las mismas columnas que la consulta detallada. Se puede colapsar el grupo. Se muestra el total general de horas y tareas.

### Suposiciones explícitas
- El usuario ya está autenticado (HU-001). Solo supervisores acceden a esta funcionalidad.
- HU-044 (Consulta Detallada) está implementada; se reutilizan filtros y criterios de período (validación 1305).
- La tabla `PQ_PARTES_REGISTRO_TAREA` y tablas relacionadas (tipos de tarea, clientes, usuarios) existen.
- Horas en formato decimal (minutos / 60).
- Agrupación por `tipo_tarea_id`; los filtros aplican a todas las tareas antes de agrupar.

### In Scope
- Sección "Tareas por Tipo" accesible solo para supervisores.
- Mismos filtros que consulta detallada: período (fecha desde, fecha hasta), tipo de cliente, cliente, empleado.
- Resultados agrupados por tipo de tarea: descripción del tipo, total horas (decimal), cantidad de tareas.
- Cada grupo expandible (accordion o similar); al expandir, detalle de tareas con mismas columnas que consulta detallada.
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

- **AC-01**: El supervisor puede acceder a la sección "Tareas por Tipo".
- **AC-02**: Se muestran los mismos filtros que en consulta detallada (período, tipo de cliente, cliente, empleado).
- **AC-03**: Los resultados se agrupan por tipo de tarea (tipo_tarea_id).
- **AC-04**: Cada grupo muestra: descripción del tipo de tarea, total de horas en formato decimal, cantidad de tareas.
- **AC-05**: Cada grupo es expandible (accordion o similar).
- **AC-06**: Al expandir un grupo se muestra el detalle de todas las tareas de ese tipo.
- **AC-07**: El detalle muestra las mismas columnas que la consulta detallada (fecha, cliente, tipo tarea, horas, sin cargo, presencial, descripción).
- **AC-08**: Se puede colapsar el grupo para ocultar el detalle.
- **AC-09**: Se muestra el total general de horas y tareas.
- **AC-10**: Si `fecha_desde > fecha_hasta`, se muestra error 1305 (validación de período).
- **AC-11**: Estados loading, vacío y error manejados (estado vacío alineado con HU-050 si aplica).

### Escenarios Gherkin

```gherkin
Feature: Consulta Agrupada por Tipo de Tarea

  Scenario: Supervisor consulta resumen por tipo de tarea
    Given el supervisor está autenticado
    When accede a "Tareas por Tipo"
    And aplica período "2026-01-01" a "2026-01-31"
    Then se muestran grupos por cada tipo de tarea con tareas
    And cada grupo muestra descripción tipo, total horas y cantidad tareas
    And se muestra el total general de horas y tareas

  Scenario: Expandir grupo y ver detalle
    Given el supervisor está en "Tareas por Tipo"
    And hay al menos un grupo con tareas
    When expande el grupo del tipo "Desarrollo"
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

1. **RN-01**: Agrupación por `tipo_tarea_id` sobre las tareas que el supervisor puede ver (todas, o filtradas por cliente/empleado).
2. **RN-02**: Solo supervisores pueden acceder a "Tareas por Tipo".
3. **RN-03**: Totalización de horas en formato decimal (minutos / 60).
4. **RN-04**: Los filtros (período, tipo_cliente_id, cliente_id, usuario_id) aplican a todas las tareas antes de agrupar.
5. **RN-05**: Validación de período: `fecha_desde <= fecha_hasta`; si no se cumple, error 1305 (regla 8.1).

### Permisos por Rol
- **Supervisor:** Acceso completo a Tareas por Tipo; puede filtrar por empleado, cliente, tipo de cliente, período.
- **Empleado (no supervisor):** Sin acceso (403 o no mostrar enlace).
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: consultas de agregación agrupando por `tipo_tarea_id`, con filtros por período, cliente_id, tipo_cliente_id, usuario_id.
- Tablas relacionadas: tipos de tarea, clientes, usuarios (para nombres y detalle).

### Cambios en Datos
- No se requieren nuevas tablas ni columnas.
- Consultas de agregación (SUM duracion_minutos, COUNT) agrupando por tipo_tarea_id; índices existentes son suficientes.

### Seed Mínimo para Tests
- Tareas de varios tipos de tarea, empleados y clientes en distintos períodos.
- Usuario supervisor para tests de acceso y filtros.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/reports/by-task-type`

**Descripción:** Obtener reporte de tareas agrupadas por tipo de tarea para el período indicado. Solo supervisores.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor; 403 si no.

**Query Parameters:**
```
?fecha_desde=2026-01-01
&fecha_hasta=2026-01-31
&tipo_cliente_id=1        (opcional)
&cliente_id=2             (opcional)
&usuario_id=3             (opcional)
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Reporte por tipo de tarea obtenido correctamente",
  "resultado": {
    "grupos": [
      {
        "tipo_tarea_id": 1,
        "descripcion": "Desarrollo",
        "total_horas": 45.5,
        "cantidad_tareas": 12,
        "tareas": [
          {
            "id": 1,
            "fecha": "2026-01-15",
            "cliente": { "id": 1, "nombre": "Cliente A" },
            "tipo_tarea": { "id": 1, "descripcion": "Desarrollo" },
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
- **TareasPorTipoPage** (o **ResumenPorTipoPage**): nueva pantalla con ruta protegida solo para supervisores.
- **Filtros:** período (fecha desde, fecha hasta), tipo de cliente, cliente, empleado (mismos que consulta detallada); botón "Aplicar Filtros".
- **Lista/Accordion por tipo de tarea:** cada ítem muestra descripción del tipo, total horas, cantidad tareas; expandible/colapsable.
- **Detalle expandido:** tabla de tareas del tipo (fecha, cliente, tipo tarea, horas, sin cargo, presencial, descripción).
- **Total general:** bloque con total de horas y total de tareas del período.
- **Estado vacío:** mensaje cuando no hay grupos (alineado con HU-050 si aplica).

### Estados UI
- Loading: mientras se cargan datos.
- Empty: sin resultados (mensaje informativo).
- Error: mensaje por 1305 u otro error.

### data-testid sugeridos
- `tareasPorTipo.page`, `tareasPorTipo.filtros`, `tareasPorTipo.aplicarFiltros`, `tareasPorTipo.grupo.{tipo_tarea_id}`, `tareasPorTipo.grupoExpandir.{tipo_tarea_id}`, `tareasPorTipo.totalGeneral`, `tareasPorTipo.empty`, `tareasPorTipo.loading`, `tareasPorTipo.mensajeError`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | GET /api/v1/reports/by-task-type con fecha_desde/hasta, tipo_cliente_id, cliente_id, usuario_id; validación 1305; solo supervisor | 200 con grupos; 422 (1305); 403 | TR-044 / ReportController | M |
| T2 | Frontend | Ruta /informes/tareas-por-tipo (SupervisorRoute); página con filtros y accordion por tipo de tarea | Cumple AC | TR-044 | M |
| T3 | Frontend | Accordion expandible/colapsable; detalle con columnas como consulta detallada; total general | Cumple AC | T2 | M |
| T4 | Frontend | Enlace "Tareas por Tipo" en Dashboard o menú (solo supervisores) | Enlace visible solo si supervisor | T2 | S |
| T5 | Tests    | Integration: GET by-task-type 200, 422 (1305), 403; E2E supervisor accede, aplica filtros, expande grupo | Tests pasan | T1, T2, T3 | M |
| T6 | Docs     | Spec endpoint by-task-type | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit/Integration (backend):** GET by-task-type con supervisor retorna grupos; con empleado 403; fecha_desde > fecha_hasta retorna 422 (1305); filtros aplicados correctamente.
- **E2E (Playwright):** Login supervisor → acceder a Tareas por Tipo → aplicar filtros → ver grupos y total general → expandir un grupo → ver detalle → colapsar; sin waits ciegos; data-testid.

---

## 9) Riesgos y Edge Cases

- Muchos tipos de tarea: considerar orden de grupos (ej. por total horas descendente).
- Tipo de tarea sin tareas en el período: puede no aparecer en grupos o aparecer con 0 horas según diseño.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: endpoint by-task-type + validación 1305 + 403
- [ ] Frontend: ruta + filtros + accordion + detalle + total general
- [ ] Enlace solo para supervisores
- [ ] Tests ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/TaskService.php` — método `listByTaskTypeReport`
- `backend/app/Http/Controllers/Api/V1/ReportController.php` — método `byTaskType`
- `backend/routes/api.php` — ruta GET `reports/by-task-type`

### Frontend
- `frontend/src/features/tasks/services/task.service.ts` — tipos y `getReportByTaskType`
- `frontend/src/features/tasks/components/TareasPorTipoPage.tsx` — página con filtros y accordion
- `frontend/src/features/tasks/components/TareasPorTipoPage.css` — estilos
- `frontend/src/features/tasks/components/index.ts` — export de `TareasPorTipoPage`
- `frontend/src/app/App.tsx` — ruta `/informes/tareas-por-tipo` con SupervisorRoute
- `frontend/src/app/Dashboard.tsx` — enlace "Tareas por Tipo" (solo supervisores)

### Tests
- `backend/tests/Feature/Api/V1/ReportControllerTest.php` — tests by_task_type (200, 403, 422 1305, 401)
- `frontend/tests/e2e/tareas-por-tipo.spec.ts` — E2E Playwright: supervisor accede, aplica filtros, grupos/vacío

## Comandos ejecutados

- `npm install xlsx@^0.18.5 --save` (frontend, para TR-049)

## Notas y decisiones

- TR-047, TR-048 y TR-049 implementados en la misma sesión; exportación Excel (TR-049) en frontend con librería xlsx.

## Pendientes / follow-ups

- Ninguno.
