# TR-046(MH) – Consulta Agrupada por Cliente

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-046(MH)-consulta-agrupada-por-cliente   |
| Épica              | Épica 9: Informes y Consultas             |
| Prioridad          | MUST-HAVE                                 |
| Roles              | Empleado / Empleado Supervisor / Cliente  |
| Dependencias       | HU-044                                    |
| Clasificación      | HU COMPLEJA **[REVISAR_SIMPLICIDAD]**     |
| Última actualización | 2026-01-30                               |
| Estado             | 📋 PENDIENTE                               |

---

## 1) HU Refinada

### Título
Consulta Agrupada por Cliente

### Narrativa
**Como** usuario (empleado, supervisor o cliente)  
**Quiero** consultar tareas agrupadas por cliente  
**Para** analizar la dedicación a cada cliente

### Contexto/Objetivo
Los usuarios necesitan una sección "Tareas por Cliente" o "Resumen por Cliente" donde ver resultados agrupados por cliente en el período seleccionado. Cada grupo muestra nombre del cliente, tipo de cliente (opcional), total de horas (decimal) y cantidad de tareas; es expandible para ver el detalle de tareas (fecha, tipo, horas, empleado si supervisor, descripción). Se muestra el total general de horas y tareas. Los grupos se ordenan por dedicación total (mayor a menor). Los filtros automáticos por rol (reglas 8.2) aplican igual que en HU-044.

### Suposiciones explícitas
- El usuario ya está autenticado (HU-001).
- HU-044 (Consulta Detallada) está implementada o en curso; la lógica de filtros por rol (empleado/supervisor/cliente) se reutiliza.
- La tabla `PQ_PARTES_REGISTRO_TAREA` y tablas relacionadas (clientes, tipos de cliente, usuarios) existen.
- Horas en formato decimal (minutos / 60).
- Validación de período 1305 (regla 8.1) y filtros automáticos por tipo de usuario (regla 8.2) aplican.

### In Scope
- Sección "Tareas por Cliente" o "Resumen por Cliente" accesible según rol.
- Filtros de período (fecha desde, fecha hasta) con botón aplicar.
- Resultados agrupados por cliente: nombre, tipo de cliente (opcional), total horas (decimal), cantidad de tareas.
- Cada grupo expandible (accordion o similar); al expandir, detalle de tareas: fecha, tipo de tarea, horas, empleado (si supervisor), descripción.
- Se puede colapsar el grupo para ocultar el detalle.
- Total general de horas y tareas.
- Grupos ordenados por total de horas (mayor a menor).

### Out of Scope
- Exportación a Excel/PDF (HU-049).
- Paginación de grupos (si hay muchos clientes, se puede añadir en iteración posterior).
- Gráficos en esta pantalla.
- Edición/eliminación de tareas desde esta vista.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario puede acceder a la sección "Tareas por Cliente" o "Resumen por Cliente".
- **AC-02**: Se muestran filtros de período (fecha desde, fecha hasta) y botón para aplicar.
- **AC-03**: Los resultados se agrupan por cliente según permisos del usuario (empleado: solo sus tareas; supervisor: todas; cliente: solo donde es el cliente).
- **AC-04**: Cada grupo muestra: nombre del cliente, tipo de cliente (opcional), total de horas en formato decimal, cantidad de tareas.
- **AC-05**: Cada grupo es expandible (accordion o similar).
- **AC-06**: Al expandir un grupo se muestra el detalle de todas las tareas de ese cliente.
- **AC-07**: El detalle muestra: fecha, tipo de tarea, horas, empleado (si supervisor), descripción.
- **AC-08**: Se puede colapsar el grupo para ocultar el detalle.
- **AC-09**: Se muestra el total general de horas y tareas del período filtrado.
- **AC-10**: Los grupos se ordenan por total de horas (mayor a menor).
- **AC-11**: Si `fecha_desde > fecha_hasta`, se muestra error 1305 (validación de período).
- **AC-12**: Estados loading, vacío y error manejados (estado vacío alineado con HU-050).

### Escenarios Gherkin

```gherkin
Feature: Consulta Agrupada por Cliente

  Scenario: Supervisor consulta resumen por cliente
    Given el supervisor "MGARCIA" está autenticado
    And existen tareas de múltiples clientes
    When accede a "Tareas por Cliente"
    And aplica período "2026-01-01" a "2026-01-31"
    Then se muestran grupos por cada cliente con tareas
    And cada grupo muestra nombre, tipo cliente, total horas y cantidad tareas
    And los grupos están ordenados por total horas descendente
    And se muestra el total general de horas y tareas

  Scenario: Expandir grupo y ver detalle
    Given el supervisor está en "Tareas por Cliente"
    And hay al menos un grupo con tareas
    When expande el grupo del cliente "Cliente A"
    Then se muestra tabla de tareas: fecha, tipo, horas, empleado, descripción
    When colapsa el grupo
    Then el detalle se oculta

  Scenario: Empleado ve solo sus tareas agrupadas por cliente
    Given el empleado "JPEREZ" está autenticado
    When accede a "Tareas por Cliente"
    Then se muestran solo grupos de clientes donde él tiene tareas
    And el total general corresponde solo a sus tareas

  Scenario: Cliente ve solo su dedicación
    Given el cliente "CLI001" está autenticado
    When accede a "Tareas por Cliente"
    Then se muestra un único grupo (su cliente) o vacío si no hay tareas
    And el total general corresponde solo a tareas donde es el cliente

  Scenario: Período inválido
    Given el usuario está autenticado
    When aplica filtros con fecha_desde > fecha_hasta
    Then se muestra error 1305 (período inválido)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Agrupación por `cliente_id` sobre las tareas que el usuario puede ver según su rol.
2. **RN-02**: Empleado (no supervisor) solo ve tareas donde `usuario_id` = su `usuario_id` (regla 8.2.2).
3. **RN-03**: Supervisor ve todas las tareas de todos los usuarios.
4. **RN-04**: Cliente solo ve tareas donde `cliente_id` = su `cliente_id` (regla 8.2.1).
5. **RN-05**: Totalización de horas en formato decimal (minutos / 60).
6. **RN-06**: Ordenamiento de grupos por total de horas descendente.
7. **RN-07**: Validación de período: `fecha_desde <= fecha_hasta`; si no se cumple, error 1305 (regla 8.1).

### Permisos por Rol
- **Empleado (no supervisor):** Acceso a Tareas por Cliente; solo sus tareas agrupadas por cliente.
- **Supervisor:** Acceso a Tareas por Cliente; todas las tareas agrupadas por cliente.
- **Cliente:** Acceso a Tareas por Cliente; solo tareas donde es el cliente (un grupo o vacío).

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: consultas de agregación agrupando por `cliente_id`, con filtros por `usuario_id` (empleado), `cliente_id` (cliente), rango de fechas.
- Tablas relacionadas: clientes, tipos de cliente, usuarios (para nombre empleado en detalle).

### Cambios en Datos
- No se requieren nuevas tablas ni columnas.
- Consultas de agregación (SUM duracion_minutos, COUNT) agrupando por cliente_id; índices existentes para fecha, usuario_id, cliente_id son suficientes.

### Seed Mínimo para Tests
- Tareas de varios usuarios y varios clientes en distintos períodos.
- Usuario empleado, supervisor y cliente para tests por rol.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/reports/by-client`

**Descripción:** Obtener reporte de tareas agrupadas por cliente para el período indicado, con filtros automáticos por rol.

**Autenticación:** Requerida (Bearer token).

**Autorización:**
- Empleado, Supervisor, Cliente: pueden acceder; el backend aplica filtros automáticos por rol (8.2).

**Query Parameters:**
```
?fecha_desde=2026-01-01
&fecha_hasta=2026-01-31
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Reporte por cliente obtenido correctamente",
  "resultado": {
    "grupos": [
      {
        "cliente_id": 1,
        "nombre": "Cliente A",
        "tipo_cliente": { "id": 1, "descripcion": "Tipo X" },
        "total_horas": 45.5,
        "cantidad_tareas": 12,
        "tareas": [
          {
            "id": 1,
            "fecha": "2026-01-15",
            "tipo_tarea": { "id": 2, "descripcion": "Desarrollo" },
            "horas": 2.5,
            "empleado": { "id": 1, "nombre": "Juan Pérez", "code": "JPEREZ" },
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

**Nota:** El array `tareas` dentro de cada grupo puede incluirse siempre o solo al expandir (según diseño; si el backend devuelve todo, el frontend puede mostrar/ocultar al expandir).

**Response 422 Unprocessable Entity (período inválido):**
```json
{
  "error": 1305,
  "respuesta": "El período es inválido: fecha_desde debe ser menor o igual a fecha_hasta",
  "resultado": {}
}
```

**Response 403 Forbidden:** Si el endpoint restringe por rol y el usuario no tiene permiso (si aplica).

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **TareasPorClientePage** (o **ResumenPorClientePage**): nueva pantalla con ruta protegida para empleado, supervisor y cliente.
- **Filtros:** período (fecha desde, fecha hasta) y botón "Aplicar Filtros".
- **Lista/Accordion por cliente:** cada ítem muestra nombre cliente, tipo cliente (opcional), total horas, cantidad tareas; expandible/colapsable.
- **Detalle expandido:** tabla de tareas del cliente (fecha, tipo, horas, empleado si supervisor, descripción).
- **Total general:** bloque visible con total de horas y total de tareas del período.
- **Estado vacío:** mensaje alineado con HU-050 cuando no hay grupos.

### Estados UI
- Loading: mientras se cargan datos.
- Empty: sin resultados (mensaje informativo, no lista vacía).
- Error: error de red o 1305/403.
- Success: lista de grupos con total general.

### Validaciones en UI
- Fecha desde <= Fecha hasta antes de enviar (opcional; backend siempre valida 1305).

### Accesibilidad Mínima
- `data-testid` en: filtros (report.byClient.filters), lista/accordion (report.byClient.groups), total general (report.byClient.totalGeneral), botón aplicar (report.byClient.applyFilters).
- Labels y roles ARIA apropiados para accordion (expandir/colapsar).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | ReportService::reportByClient() o similar | Agregación por cliente_id con filtros por rol (empleado/supervisor/cliente) y período; orden por total_horas desc; total_general_horas y total_general_tareas. Validación 1305. Incluir tareas[] por grupo. | HU-044 | L |
| T2 | Backend  | ReportController::byClient() | Endpoint GET /api/v1/reports/by-client con params fecha_desde, fecha_hasta. Respuesta con grupos y totales. | T1 | M |
| T3 | Backend  | Tests unitarios servicio reportByClient | Filtros por rol; agregación correcta; orden; totales; período inválido 1305. | T1 | M |
| T4 | Backend  | Tests integración endpoint by-client | Por rol (empleado/supervisor/cliente); período inválido 1305. | T2 | M |
| T5 | Frontend | Servicio report.service.ts getReportByClient() | Llamada GET con fecha_desde, fecha_hasta; transformar respuesta (grupos, totalGeneralHoras, totalGeneralTareas). | — | S |
| T6 | Frontend | TareasPorClientePage / ResumenPorClientePage | Contenedor: filtros período + lista/accordion + total general. Ruta protegida tres roles. | — | M |
| T7 | Frontend | Filtros período y botón aplicar | Fecha desde, fecha hasta, botón "Aplicar Filtros". data-testid. | T6 | S |
| T8 | Frontend | Componente accordion por cliente | Cada ítem: nombre, tipo cliente, total horas, cantidad tareas; expandir/colapsar; al expandir tabla de tareas (fecha, tipo, horas, empleado si supervisor, descripción). data-testid. | T6 | M |
| T9 | Frontend | Total general | Bloque con total general de horas y tareas. | T6 | S |
| T10| Frontend | Estado vacío y error | Mensaje "No se encontraron tareas..." cuando no hay grupos; manejo error 1305. | T6 | S |
| T11| Tests    | E2E Playwright supervisor → filtrar → expandir | Login supervisor → Tareas por Cliente → aplicar período → expandir un cliente y ver detalle. | T6 | M |
| T12| Tests    | E2E Playwright empleado/cliente | Login empleado → solo sus grupos; login cliente → un grupo o vacío. | T6 | M |
| T13| Tests    | Frontend unit (Vitest) getReportByClient | Params, transformación, manejo error 1305. | T5 | S |
| T14| Docs     | Actualizar docs/backend o specs | Documentar GET /reports/by-client y código 1305. | T2 | S |

**Total:** 15 tareas (6S + 7M + 1L).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Agregación por cliente_id con filtro empleado (solo sus tareas).
- Agregación con filtro cliente (solo su cliente_id).
- Supervisor sin filtro usuario/cliente; todos los clientes con tareas.
- Orden por total_horas desc.
- Cálculo total_general_horas y total_general_tareas.
- Período inválido → excepción/lógica 1305.

### Integration Tests (Backend)
- GET /reports/by-client como empleado: solo grupos de clientes donde tiene tareas.
- GET /reports/by-client como supervisor: todos los grupos.
- GET /reports/by-client como cliente: un grupo (su cliente) o vacío.
- Params fecha_desde > fecha_hasta → 422 con error 1305.

### Frontend Unit Tests (Vitest)
- getReportByClient: params correctos, transformación de grupos y totales, manejo error 1305.

### E2E Tests (Playwright)
- **Supervisor:** login → Tareas por Cliente → aplicar período → ver grupos ordenados → expandir un cliente → ver detalle de tareas → total general.
- **Empleado:** login → Tareas por Cliente → solo grupos con sus tareas.
- **Cliente:** login → Tareas por Cliente → un grupo o mensaje vacío.

---

## 9) Riesgos y Edge Cases

- **Performance:** Muchos clientes o muchas tareas por cliente; asegurar agregación eficiente en backend; si hay muchos grupos, valorar paginación en iteración posterior.
- **Permisos:** Aplicar siempre filtros por rol en backend.
- **Resultados vacíos:** Comportamiento alineado con HU-050 (mensaje, no lista vacía).
- **Detalle en respuesta:** Decidir si el backend devuelve `tareas[]` siempre en cada grupo o en un segundo request al expandir; el TR asume un solo request con todo para simplificar.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: servicio reportByClient con filtros por rol y validación 1305
- [ ] Backend: endpoint GET /reports/by-client documentado
- [ ] Frontend: pantalla Tareas por Cliente con filtros período
- [ ] Frontend: accordion por cliente con totales y detalle expandible
- [ ] Frontend: total general y estado vacío (HU-050)
- [ ] Unit tests backend ok
- [ ] Integration tests endpoint ok
- [ ] Frontend unit tests (Vitest) servicio ok
- [ ] ≥1 E2E Playwright ok (supervisor filtrar y expandir; empleado/cliente)
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/TaskService.php` – Método `listByClientReport()` (TR-046); filtros por rol, agregación por cliente_id, orden por total_horas desc, tareas[] por grupo; validación 1305.
- `backend/app/Http/Controllers/Api/V1/ReportController.php` – Método `byClient()`; GET /api/v1/reports/by-client.
- `backend/routes/api.php` – Ruta GET /api/v1/reports/by-client.
- `backend/tests/Unit/Services/TaskServiceTest.php` – Tests listByClientReport (empleado, supervisor, cliente, período inválido 1305).
- `backend/tests/Feature/Api/V1/ReportControllerTest.php` – Tests GET /reports/by-client por rol, 422 1305, 401.

### Frontend
- `frontend/src/features/tasks/services/task.service.ts` – ByClientReportParams, ByClientGroup, ByClientTaskItem, GetByClientReportResult; función `getReportByClient()`.
- `frontend/src/features/tasks/components/TareasPorClientePage.tsx` – Pantalla con filtros período, accordion por cliente, total general.
- `frontend/src/features/tasks/components/TareasPorClientePage.css` – Estilos.
- `frontend/src/features/tasks/components/index.ts` – Export TareasPorClientePage.
- `frontend/src/app/App.tsx` – Ruta /informes/tareas-por-cliente.
- `frontend/src/app/Dashboard.tsx` – Botón "Tareas por Cliente".
- `frontend/src/features/tasks/services/task.service.test.ts` – Tests getReportByClient (200, 422 1305, sin token).
- `frontend/tests/e2e/tareas-por-cliente.spec.ts` – E2E supervisor y empleado en Tareas por Cliente.

### Docs
- `docs/backend/tareas.md` – Sección GET /api/v1/reports/by-client (TR-046).
- `.cursor/Docs/TareasPorClientePage.tsx.md` – Documentación del componente.

## Comandos ejecutados

- `cd backend && php artisan test --filter=TaskServiceTest::test_list_by_client_report_*`
- `cd backend && php artisan test --filter=ReportControllerTest::by_client_*`
- `cd frontend && npm run test -- --run src/features/tasks/services/task.service.test.ts`
- `cd frontend && npx playwright test tests/e2e/tareas-por-cliente.spec.ts`

## Notas y decisiones

*(Se completará durante la implementación)*

## Pendientes / follow-ups

*(Se completará durante la implementación)*
