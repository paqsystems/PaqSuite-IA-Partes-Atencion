# TR-040(SH) – Filtrado de tareas para proceso masivo

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-040(SH)-filtrado-de-tareas-para-proceso-masivo |
| Épica              | Épica 8: Proceso Masivo de Tareas          |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-039 (acceso proceso masivo)             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-07                               |
| Estado             | Pendiente                                  |

---

## 1) HU Refinada

### Título
Filtrado de tareas para proceso masivo

### Narrativa
**Como** supervisor  
**Quiero** aplicar filtros complejos para seleccionar las tareas que deseo procesar masivamente  
**Para** acotar el conjunto de tareas a procesar.

### Contexto/Objetivo
En la página de Proceso Masivo, el supervisor puede filtrar por rango de fechas (fecha desde, fecha hasta), cliente (todos o específico), empleado (todos o específico) y estado (Cerrados / Abiertos). Validación fecha_desde <= fecha_hasta (código 1305). Al aplicar filtros se cargan las tareas que cumplen los criterios. Se muestra el total de tareas filtradas.

### Suposiciones explícitas
- GET /api/v1/tasks/all (TR-034) soporta o se extiende con query params: fecha_desde, fecha_hasta, cliente_id, usuario_id (empleado), cerrado (estado).
- Los filtros se aplican en conjunto (AND).

### In Scope
- Filtro por rango de fechas (fecha desde, fecha hasta); validación fecha_desde <= fecha_hasta (backend 422, código 1305).
- Filtro por cliente (todos o cliente específico).
- Filtro por empleado (todos o empleado específico).
- Filtro por estado (Cerrados / Abiertos / Todos).
- Botón "Aplicar Filtros"; al hacer clic se cargan las tareas que cumplen los criterios.
- Total de tareas filtradas mostrado.
- Opcional: persistir filtros al recargar (UX).

### Out of Scope
- Selección múltiple (HU-041) y procesamiento (HU-042, HU-043).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede filtrar por rango de fechas (fecha desde, fecha hasta).
- **AC-02**: El supervisor puede filtrar por cliente (todos o cliente específico).
- **AC-03**: El supervisor puede filtrar por empleado (todos o empleado específico).
- **AC-04**: El supervisor puede filtrar por estado (Cerrados / Abiertos / Todos).
- **AC-05**: El sistema valida que `fecha_desde <= fecha_hasta` (422, código 1305 si no).
- **AC-06**: Al hacer clic en "Aplicar Filtros", se cargan las tareas que cumplen los criterios.
- **AC-07**: Los filtros se aplican en conjunto (AND lógico).
- **AC-08**: Se muestra el total de tareas filtradas.
- **AC-09**: Opcional: los filtros se mantienen al recargar la página.

### Escenarios Gherkin

```gherkin
Feature: Filtrado para Proceso Masivo

  Scenario: Aplicar filtros
    Given el supervisor está en Proceso Masivo
    When define fecha desde "2026-01-01" y fecha hasta "2026-01-31"
    And selecciona cliente "CLI001" y empleado "JPEREZ"
    And selecciona estado "Abiertos"
    And hace clic en "Aplicar Filtros"
    Then se cargan solo las tareas que cumplen todos los criterios
    And se muestra el total de tareas filtradas

  Scenario: Rango de fechas inválido
    Given el supervisor está en Proceso Masivo
    When define fecha desde "2026-02-01" y fecha hasta "2026-01-15"
    And hace clic en "Aplicar Filtros"
    Then el sistema muestra error (código 1305)
    And no se actualiza la lista
```

---

## 3) Reglas de Negocio

1. **RN-01**: Validación de rango de fechas: `fecha_desde <= fecha_hasta`. Código de error: **1305** (rango inválido).
2. **RN-02**: Los filtros se aplican en conjunto (AND lógico).
3. **RN-03**: Solo supervisores pueden usar los filtros del proceso masivo.

### Permisos por Rol
- **Supervisor:** Puede aplicar todos los filtros.
- **Empleado:** Sin acceso a la página (TR-039).

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: consulta con filtros (fecha, cliente_id, usuario_id, cerrado).
- Sin nuevas columnas.

### Migración + Rollback
- No se requiere migración. GET /api/v1/tasks/all ya acepta filtros (TR-034); verificar o añadir parámetro `cerrado` si no existe.

### Seed Mínimo para Tests
- Tareas con distintos estados (cerrado true/false), clientes, empleados; usuario supervisor.

---

## 5) Contratos de API

### Reutilización / Extensión
- **GET /api/v1/tasks/all** con query params:
  - `fecha_desde`, `fecha_hasta` (obligatorios o validados: fecha_desde <= fecha_hasta; 422 código 1305 si no).
  - `cliente_id` (opcional).
  - `usuario_id` (opcional, empleado).
  - `cerrado` (opcional: true / false; si no se envía = todos).
- Response: lista paginada de tareas + total (del conjunto filtrado).

**Autorización:** Solo supervisor.

---

## 6) Cambios Frontend

### Pantallas/Componentes
- En la página Proceso Masivo: controles de filtro (fecha desde, fecha hasta, selector cliente, selector empleado, selector estado Cerrados/Abiertos/Todos).
- Botón "Aplicar Filtros"; al enviar se llama GET tasks/all con los params y se actualiza la tabla y el total.
- Mensaje de error si backend devuelve 422 (1305) por rango de fechas inválido.

### Estados UI
- Loading al aplicar filtros, Error (1305 u otro), Success con tabla y total.

### data-testid sugeridos
- `procesoMasivo.filtroFechaDesde`, `procesoMasivo.filtroFechaHasta`, `procesoMasivo.filtroCliente`, `procesoMasivo.filtroEmpleado`, `procesoMasivo.filtroEstado`, `procesoMasivo.aplicarFiltros`, `procesoMasivo.total`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | Asegurar GET tasks/all con fecha_desde/hasta, cliente_id, usuario_id, cerrado; validación 1305 | 200 con datos; 422 (1305) si fecha_desde > fecha_hasta | TR-034 | S |
| T2 | Frontend | Controles de filtro + "Aplicar Filtros" + total de tareas filtradas | Cumple AC | TR-039 | M |
| T3 | Tests    | Integration: filtros y 1305; E2E aplicar filtros y ver total | Tests pasan | T1, T2 | S |
| T4 | Docs     | Specs/domain-error-codes 1305 | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit/Integration:** Backend validación fecha_desde <= fecha_hasta; respuesta 422 con 1305; GET con filtros retorna subset correcto.
- **E2E:** Aplicar filtros, ver tabla y total; intentar fecha_desde > fecha_hasta y ver mensaje de error.

---

## 9) Riesgos y Edge Cases

- Fechas nulas o vacías: definir si son obligatorias o si se usa rango por defecto (ej. mes actual).

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: filtros y validación 1305
- [ ] Frontend: filtros + aplicar + total
- [ ] Tests ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Http/Controllers/Api/V1/TaskController.php` – indexAll: validación fecha_desde <= fecha_hasta (422/1305); filtro `cerrado` en query.
- `backend/app/Services/TaskService.php` – listTasks: filtro por `cerrado` en query y totalesQuery.

### Frontend
- `frontend/src/features/tasks/services/task.service.ts` – TaskListParams.cerrado; getAllTasks envía cerrado.
- `frontend/src/features/tasks/components/ProcesoMasivoPage.tsx` – Filtros fecha, cliente, empleado, estado (Todos/Abiertos/Cerrados); Aplicar Filtros; total.

### Tests
- `backend/tests/Feature/Api/V1/TaskControllerTest.php` – indexAll_fecha_desde_mayor_que_fecha_hasta_retorna_422_1305.

## Comandos ejecutados

## Notas y decisiones

## Pendientes / follow-ups
