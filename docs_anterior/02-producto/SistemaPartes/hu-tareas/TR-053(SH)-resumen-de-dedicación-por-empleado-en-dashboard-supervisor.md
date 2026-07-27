# TR-053(SH) – Resumen de dedicación por empleado en dashboard (supervisor)

| Campo                | Valor                                                                 |
|----------------------|-----------------------------------------------------------------------|
| HU relacionada       | HU-053(SH)-resumen-de-dedicación-por-empleado-en-dashboard-supervisor |
| Épica                | Épica 10: Dashboard                                                  |
| Prioridad            | SHOULD-HAVE                                                          |
| Roles                | Empleado Supervisor                                                  |
| Dependencias         | HU-051 (Dashboard principal); TR-051                                 |
| Clasificación        | HU SIMPLE                                                            |
| Última actualización | 2026-02-07                                                           |
| Estado               | ✅ IMPLEMENTADO                                                       |

---

## 1) HU Refinada

### Título
Resumen de dedicación por empleado en dashboard (supervisor)

### Narrativa
**Como** supervisor  
**Quiero** ver un resumen de dedicación por empleado en el dashboard  
**Para** identificar rápidamente la carga de trabajo de cada empleado

### Contexto/Objetivo
El dashboard del supervisor (TR-051) incluye una sección "Dedicación por Empleado". Muestra lista o tabla con empleados y totales de horas del período (top N, ej. 5 o 10), ordenada por dedicación descendente. Cada ítem: nombre del empleado, total horas (decimal), cantidad de tareas, porcentaje del total (opcional). Total general de horas. El supervisor puede hacer clic en un empleado para ir al detalle (redirección a consulta por empleado). Solo visible para supervisores.

### Suposiciones explícitas
- TR-051 está implementado; el endpoint GET /api/v1/dashboard ya puede incluir `top_empleados` para el rol supervisor.
- Agrupación por `usuario_id`; orden por total horas descendente; límite top N (ej. 5 o 10).
- Horas en formato decimal (minutos / 60).

### In Scope
- Sección "Dedicación por Empleado" visible solo para supervisores en el Dashboard.
- Lista o tabla: empleado (nombre/código), total horas (decimal), cantidad tareas, porcentaje (opcional).
- Orden por total horas descendente; top N (ej. 5 o 10).
- Total general de horas del período.
- Enlace o acción "Ver detalle" por empleado que lleve a Tareas por Empleado (informe agrupado por empleado con filtro).
- data-testid en la sección para E2E.

### Out of Scope
- Paginación de la lista (se limita a top N).
- Edición de tareas desde esta sección.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El dashboard del supervisor muestra la sección "Dedicación por Empleado".
- **AC-02**: Se muestra una lista o tabla con empleados y totales: nombre del empleado, total horas (decimal), cantidad de tareas; porcentaje del total (opcional).
- **AC-03**: Se muestran los top N empleados (ej. 5 o 10), ordenados por total de horas descendente.
- **AC-04**: Se muestra un total general de horas.
- **AC-05**: El supervisor puede hacer clic en un empleado para ver el detalle (redirección a consulta por empleado).
- **AC-06**: La sección no es visible para empleados no supervisores ni para clientes.
- **AC-07**: Estado vacío (sin tareas en el período): mensaje alineado con HU-050.
- **AC-08**: data-testid en la sección y en la lista/tabla para E2E.

### Escenarios Gherkin

```gherkin
Feature: Resumen de dedicación por empleado en dashboard (supervisor)

  Scenario: Supervisor ve dedicación por empleado
    Given el supervisor "MGARCIA" está autenticado
    When accede al dashboard
    Then ve la sección "Dedicación por Empleado"
    And la lista muestra top N empleados por total horas
    And cada empleado muestra nombre, total horas, cantidad tareas
    And ve el total general de horas

  Scenario: Empleado no supervisor no ve la sección
    Given el empleado "JPEREZ" (no supervisor) está autenticado
    When accede al dashboard
    Then no ve la sección "Dedicación por Empleado"

  Scenario: Clic en empleado lleva a consulta por empleado
    Given el supervisor está en el dashboard con al menos un empleado en la lista
    When hace clic en "Ver detalle" de un empleado
    Then navega a la consulta por empleado (Tareas por Empleado) con filtro por ese empleado
```

---

## 3) Reglas de Negocio

1. **RN-01**: Agrupación por `usuario_id` (empleado) sobre las tareas; solo supervisor ve esta sección.
2. **RN-02**: Ordenamiento por total de horas descendente; límite top N (ej. 10).
3. **RN-03**: Solo visible para usuarios con rol supervisor (`es_supervisor` o equivalente).
4. **RN-04**: Total general de horas coherente con el KPI total del dashboard para el supervisor.
5. **RN-05**: Porcentaje (opcional): (total_horas_empleado / total_general_horas) * 100.

### Permisos por Rol
- **Supervisor:** Ve sección "Dedicación por Empleado" con top N empleados.
- **Empleado (no supervisor):** No ve la sección.
- **Cliente:** No ve la sección.

---

## 4) Impacto en Datos

- **Tablas afectadas:** `PQ_PARTES_REGISTRO_TAREA`, `PQ_PARTES_USUARIOS` (o equivalente). Solo lecturas y agregaciones.
- **Cambios en datos:** Ninguno. Reutilización o extensión del endpoint GET /api/v1/dashboard (campo `top_empleados` si no existe o ampliación del mismo).

---

## 5) Contratos de API

- Reutilizar GET /api/v1/dashboard (TR-051). Para el rol supervisor, el resultado debe incluir `top_empleados`: lista de { usuario_id, nombre, code, total_horas, cantidad_tareas, porcentaje (opcional) }, ordenada por total_horas desc, limit N.
- Si el dashboard ya devuelve `top_empleados`, TR-053 se centra en el frontend: mostrar la sección solo a supervisores, tabla/lista, enlace a "Tareas por Empleado".

**Response (fragmento para supervisor):**
```json
"top_empleados": [
  {
    "usuario_id": 1,
    "nombre": "Juan Pérez",
    "code": "JPEREZ",
    "total_horas": 80.0,
    "cantidad_tareas": 30,
    "porcentaje": 45.2
  }
]
```

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **Dashboard:** Sección "Dedicación por Empleado" renderizada solo cuando el usuario es supervisor. Consume `top_empleados` del mismo endpoint del dashboard. Tabla o lista con nombre, total horas, cantidad tareas, porcentaje (opcional). Enlace "Ver detalle" por fila que navegue a /informes/tareas-por-empleado (con query param o filtro por empleado si aplica).

### Estados UI
- Loading/Error: heredados del dashboard (TR-051).
- Empty: mensaje cuando no hay empleados con tareas en el período.

### Accesibilidad Mínima
- data-testid: dashboard.dedicacionEmpleado, dashboard.dedicacionEmpleado.lista, dashboard.dedicacionEmpleado.totalGeneral, dashboard.dedicacionEmpleado.linkDetalle (o por empleado).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | Asegurar top_empleados en GET /dashboard (supervisor) | Si no existe: agregación por usuario_id, limit N, orden desc; incluir nombre, code, total_horas, cantidad_tareas, porcentaje opcional. | TR-051 | M |
| T2 | Backend  | Tests integración dashboard supervisor | Respuesta incluye top_empleados; empleado no supervisor no recibe o recibe vacío según diseño. | T1 | S |
| T3 | Frontend | Sección "Dedicación por Empleado" (solo supervisor) | Condicional por rol; tabla/lista con datos de top_empleados; total general; data-testid. | TR-051 frontend | M |
| T4 | Frontend | Enlace "Ver detalle" por empleado | Navegación a /informes/tareas-por-empleado con filtro por ese empleado. | T3 | S |
| T5 | Tests    | E2E dashboard supervisor: sección empleados | Login supervisor → dashboard → ve sección Dedicación por Empleado; empleado no ve sección. | T3 | M |
| T6 | Docs     | OpenAPI | Actualizar si se modifica contrato dashboard. | T1 | S |

**Total:** 6 tareas.

---

## 8) Estrategia de Tests

- **Unit (backend):** Agregación top_empleados con limit N y orden correcto.
- **Integration:** GET /dashboard como supervisor devuelve top_empleados; como empleado no supervisor, no incluir o vacío.
- **E2E:** Login supervisor → dashboard → sección Dedicación por Empleado visible; login empleado → sección no visible; clic en empleado → navega a tareas por empleado.

---

## 9) Riesgos y Edge Cases

- Pocos empleados con tareas: mostrar los que haya, total general coherente.
- Supervisor sin tareas en período: sección vacía con mensaje (HU-050).

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend devuelve top_empleados para supervisor
- [ ] Frontend muestra sección solo a supervisor con lista y enlace a detalle
- [ ] Tests integration y E2E ok
- [ ] Docs/OpenAPI actualizados si hubo cambio de contrato

---

## Archivos creados/modificados

- **Backend:** `app/Services/TaskService.php` (porcentaje en top_empleados).
- **Frontend:** `src/app/Dashboard.tsx` (sección Dedicación por Empleado, data-testid, link Ver detalle); `src/features/tasks/services/task.service.ts` (DashboardTopEmpleado.porcentaje); `src/features/tasks/components/TareasPorEmpleadoPage.tsx` (lectura de usuario_id, fecha_desde, fecha_hasta desde URL).
- **Tests:** `frontend/tests/e2e/dashboard.spec.ts` (TR-053: dedicacionEmpleado, link a tareas-por-empleado).

## Comandos ejecutados

- `npm run test:e2e` (frontend) para validar dashboard.spec.ts.

## Notas y decisiones

- Se reutiliza GET /api/v1/dashboard; el backend ya devolvía top_empleados; se añadió solo el campo porcentaje.
- Enlace "Ver detalle" navega a `/informes/tareas-por-empleado?usuario_id=X&fecha_desde=...&fecha_hasta=...`; TareasPorEmpleadoPage inicializa filtros desde useSearchParams.

## Pendientes / follow-ups

- Ninguno.
