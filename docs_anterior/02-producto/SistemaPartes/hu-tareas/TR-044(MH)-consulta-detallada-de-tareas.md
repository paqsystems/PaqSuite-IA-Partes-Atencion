# TR-044(MH) – Consulta Detallada de Tareas

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-044(MH)-consulta-detallada-de-tareas    |
| Épica              | Épica 9: Informes y Consultas             |
| Prioridad          | MUST-HAVE                                 |
| Roles              | Empleado / Empleado Supervisor / Cliente  |
| Dependencias       | HU-001, HU-033, HU-034                    |
| Clasificación      | HU COMPLEJA **[REVISAR_SIMPLICIDAD]**     |
| Última actualización | 2026-01-30                               |
| Estado             | 📋 PENDIENTE                               |

---

## 1) HU Refinada

### Título
Consulta Detallada de Tareas

### Narrativa
**Como** usuario (empleado, supervisor o cliente)  
**Quiero** consultar un listado detallado de tareas con filtros  
**Para** analizar el trabajo realizado

### Contexto/Objetivo
Los usuarios necesitan una sección "Consulta Detallada" o "Detalle de Tareas" donde ver una tabla de tareas según su rol: empleado (solo las propias), supervisor (todas), cliente (solo donde es el cliente). La tabla debe permitir filtrar por período, tipo de cliente, cliente y empleado (según rol), ordenar por columnas, paginar y ver el total de horas del período filtrado.

### Suposiciones explícitas
- El usuario ya está autenticado (HU-001).
- Existen listas base de clientes, empleados y tipos (HU-033, HU-034) para los selectores de filtros.
- La tabla `PQ_PARTES_REGISTRO_TAREA` y tablas relacionadas (clientes, usuarios, tipos) existen.
- Horas se expresan en decimal (minutos / 60).
- Reglas de negocio 8.1 y 8.2 (docs/reglas-negocio.md) aplican: validación de período (1305) y filtros automáticos por tipo de usuario.

### In Scope
- Sección "Consulta Detallada" o "Detalle de Tareas" accesible según rol.
- Tabla con columnas: empleado (solo si supervisor), cliente, fecha, tipo de tarea, horas (decimal), sin cargo, presencial, descripción.
- Filtros: período (fecha desde, fecha hasta), tipo de cliente (solo supervisor), cliente (todos o específico; automático para cliente), empleado (solo supervisor; automático para empleado).
- Botón "Aplicar Filtros"; tabla actualizada con resultados filtrados.
- Total de horas del período filtrado.
- Ordenamiento por columnas (fecha, cliente, empleado, etc.).
- Paginación.

### Out of Scope
- Exportación a Excel/PDF (HU-049).
- Vista de calendario.
- Gráficos en esta pantalla.
- Edición/eliminación de tareas desde esta vista (eso corresponde a TR-029/030/031/032).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario puede acceder a la sección "Consulta Detallada" o "Detalle de Tareas".
- **AC-02**: Se muestra una tabla con tareas según permisos: empleado (no supervisor) solo sus tareas; supervisor todas; cliente solo tareas donde es el cliente.
- **AC-03**: La tabla muestra: empleado (si supervisor), cliente, fecha, tipo de tarea, horas (decimal), sin cargo, presencial, descripción.
- **AC-04**: Filtro de período (fecha desde, fecha hasta) disponible para todos.
- **AC-05**: Filtro tipo de cliente (todos o específico) solo para supervisor.
- **AC-06**: Filtro cliente (todos o específico); para usuario cliente está filtrado automáticamente y no se muestra selector de cliente.
- **AC-07**: Filtro empleado (todos o específico) solo para supervisor; para empleado normal está filtrado automáticamente y no se muestra selector de empleado.
- **AC-08**: Los filtros se aplican con botón "Aplicar Filtros".
- **AC-09**: La tabla se actualiza con los resultados filtrados.
- **AC-10**: Se muestra el total de horas del período filtrado.
- **AC-11**: Se puede ordenar por columnas (fecha, cliente, empleado, etc.).
- **AC-12**: Se puede paginar si hay muchos resultados.
- **AC-13**: Si `fecha_desde > fecha_hasta`, se muestra error 1305 (validación de período).

### Escenarios Gherkin

```gherkin
Feature: Consulta Detallada de Tareas

  Scenario: Empleado consulta sus tareas
    Given el empleado "JPEREZ" está autenticado
    And tiene tareas registradas
    When accede a "Consulta Detallada"
    Then se muestra solo sus tareas
    And no se muestra filtro "Empleado"
    And se muestra total de horas del período

  Scenario: Supervisor consulta todas las tareas
    Given el supervisor "MGARCIA" está autenticado
    When accede a "Consulta Detallada"
    Then se muestra tabla con todas las tareas
    And la columna "Empleado" está visible
    And puede filtrar por empleado, cliente y tipo de cliente
    And se muestra total de horas del período

  Scenario: Cliente consulta tareas donde es cliente
    Given el cliente "CLI001" está autenticado
    When accede a "Consulta Detallada"
    Then se muestran solo tareas donde cliente_id = su cliente_id
    And no se muestra filtro "Cliente"
    And se muestra total de horas del período

  Scenario: Período inválido
    Given el usuario está autenticado
    When aplica filtros con fecha_desde > fecha_hasta
    Then se muestra error 1305 (período inválido)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Empleado (no supervisor) solo ve tareas donde `usuario_id` = su `usuario_id` (regla 8.2.2).
2. **RN-02**: Supervisor ve todas las tareas de todos los usuarios.
3. **RN-03**: Cliente solo ve tareas donde `cliente_id` = su `cliente_id` (regla 8.2.1).
4. **RN-04**: Validación de período: `fecha_desde <= fecha_hasta`; si no se cumple, error 1305 (regla 8.1).
5. **RN-05**: Horas en formato decimal (minutos / 60).
6. **RN-06**: Filtro tipo cliente y filtro empleado solo visibles/aplicables para supervisor; filtro cliente visible para empleado y supervisor (oculto para usuario cliente).
7. **RN-07**: Filtro cliente oculto o fijo para usuario cliente; filtro empleado oculto o fijo para empleado no supervisor.
8. **RN-08**: Los filtros se aplican en el backend; el botón "Aplicar Filtros" dispara la petición.

### Permisos por Rol
- **Empleado (no supervisor):** Acceso a Consulta Detallada; solo sus tareas; puede filtrar por cliente (todos o específico); sin filtro empleado.
- **Supervisor:** Acceso a Consulta Detallada; todas las tareas; filtros empleado, cliente, tipo de cliente.
- **Cliente:** Acceso a Consulta Detallada; solo tareas donde es el cliente; sin filtro cliente.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: consultas con filtros por `usuario_id`, `cliente_id`, rango de fechas.
- Tablas relacionadas: clientes, usuarios (PQ_PARTES_USUARIOS), tipos de tarea, tipos de cliente (para filtros y columnas).

### Cambios en Datos
- No se requieren nuevas tablas ni columnas.
- Verificar índices para: `fecha`, `usuario_id`, `cliente_id`, `tipo_tarea_id`, `tipo_cliente_id` (vía cliente) para rendimiento de filtros y ordenamiento.

### Seed Mínimo para Tests
- Tareas de varios usuarios y varios clientes.
- Diferentes fechas, tipos de tarea, sin_cargo, presencial.
- Usuario empleado, usuario supervisor, usuario cliente para tests por rol.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/reports/detail` (o GET `/api/v1/tasks/report`)

**Descripción:** Obtener listado paginado de tareas para consulta detallada, con filtros según rol.

**Autenticación:** Requerida (Bearer token).

**Autorización:**
- Empleado, Supervisor, Cliente: pueden acceder; el backend aplica filtros automáticos por rol (8.2).

**Query Parameters:**
```
?page=1
&per_page=15
&fecha_desde=2026-01-01
&fecha_hasta=2026-01-31
&tipo_cliente_id=1        (opcional; solo supervisor)
&cliente_id=2             (opcional; automático para cliente)
&usuario_id=3             (opcional; solo supervisor; automático para empleado)
&ordenar_por=fecha        (fecha|cliente|empleado|tipo_tarea|horas)
&orden=desc               (asc|desc)
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Consulta obtenida correctamente",
  "resultado": {
    "data": [
      {
        "id": 1,
        "empleado": { "id": 1, "nombre": "Juan Pérez", "code": "JPEREZ" },
        "cliente": { "id": 1, "nombre": "Cliente A", "tipo_cliente": "Tipo X" },
        "fecha": "2026-01-28",
        "tipo_tarea": { "id": 2, "descripcion": "Desarrollo" },
        "horas": 2.5,
        "sin_cargo": false,
        "presencial": true,
        "descripcion": "Desarrollo de feature X..."
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 100,
      "last_page": 7
    },
    "total_horas": 125.75
  }
}
```

**Nota:** La clave `empleado` solo se incluye cuando el usuario es supervisor; para empleado/cliente puede omitirse o enviarse igual para consistencia del contrato.

**Response 422 Unprocessable Entity (período inválido):**
```json
{
  "error": 1305,
  "respuesta": "El período es inválido: fecha_desde debe ser menor o igual a fecha_hasta",
  "resultado": {}
}
```

**Response 403 Forbidden:** Si el endpoint restringe por rol y el usuario no tiene permiso (si aplica).
```json
{
  "error": 4030,
  "respuesta": "No tiene permiso para acceder a esta consulta",
  "resultado": {}
}
```

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **ConsultaDetalladaPage** (o **DetalleTareasPage**): nueva pantalla con ruta protegida accesible por empleado, supervisor y cliente.
- **Filtros:** período (fecha desde, fecha hasta), tipo de cliente (solo supervisor), cliente (oculto o fijo para cliente), empleado (solo supervisor; oculto para empleado).
- **Tabla:** columnas empleado (condicional), cliente, fecha, tipo tarea, horas (decimal), sin cargo, presencial, descripción; cabeceras ordenables; paginación.
- **Total horas:** bloque visible con el total del período filtrado.
- **Estado vacío:** mensaje alineado con HU-050 cuando no hay resultados.

### Estados UI
- Loading: mientras se cargan datos.
- Empty: sin resultados (mensaje informativo, no tabla vacía).
- Error: error de red o 1305/403.
- Success: tabla con datos y total de horas.

### Validaciones en UI
- Fecha desde <= Fecha hasta antes de enviar (opcional; el backend siempre valida y retorna 1305).
- Deshabilitar "Aplicar Filtros" si fechas incompletas o inválidas (opcional).

### Accesibilidad Mínima
- `data-testid` en: filtros (report.detail.filters), tabla (report.detail.table), paginación (report.detail.pagination), total horas (report.detail.totalHours), botón aplicar (report.detail.applyFilters).
- Labels y roles ARIA apropiados.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | ReportService o TaskService::listDetailReport() | Lógica filtros por rol (empleado/supervisor/cliente), período, tipo_cliente_id, cliente_id, usuario_id; orden; paginación; total_horas (decimal). Validación período 1305. | HU-033/034 | L |
| T2 | Backend  | ReportController::detail() o TaskController::report() | Endpoint GET /api/v1/reports/detail con query params y validación período. Respuesta paginada + total_horas. | T1 | M |
| T3 | Backend  | Tests unitarios servicio consulta detallada | Empleado solo sus tareas; supervisor todas; cliente solo su cliente_id; período inválido 1305; total_horas. | T1 | M |
| T4 | Backend  | Tests integración endpoint | Por rol (empleado/supervisor/cliente), filtros, paginación, 1305. | T2 | M |
| T5 | Frontend | Servicio report.service.ts o task.service.ts getDetailReport() | Llamada GET con params; transformar respuesta (data, pagination, totalHoras). | — | S |
| T6 | Frontend | ConsultaDetalladaPage / DetalleTareasPage | Contenedor: filtros + tabla + total horas + paginación. Ruta protegida para los tres roles. | — | M |
| T7 | Frontend | Filtros según rol | Período siempre; tipo cliente y empleado solo supervisor; cliente/empleado ocultos o fijos según rol. Botón "Aplicar Filtros". data-testid. | T6 | M |
| T8 | Frontend | Tabla con columnas y ordenamiento | Columnas indicadas; empleado solo si supervisor; horas en decimal; ordenar por cabeceras. data-testid. | T6 | M |
| T9 | Frontend | Paginación y total de horas | Componentes reutilizables; total horas visible. | T6 | S |
| T10| Frontend | Estado vacío (HU-050) | Mensaje "No se encontraron tareas..." cuando data.length === 0. | T6 | S |
| T11| Tests    | E2E Playwright consulta detallada supervisor | Login supervisor → Consulta Detallada → aplicar filtros → ver tabla y total. | T6 | M |
| T12| Tests    | E2E Playwright consulta empleado/cliente | Login empleado → solo sus tareas; login cliente → solo su cliente. | T6 | M |
| T13| Tests    | Frontend unit (Vitest) servicio consulta | getDetailReport(params), transformación, manejo error 1305. | T5 | S |
| T14| Docs     | Actualizar docs/backend o specs | Documentar GET /reports/detail y códigos 1305, 403. | T2 | S |

**Total:** 15 tareas (6S + 7M + 1L).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Filtro por rol: empleado solo usuario_id; supervisor sin filtro usuario; cliente solo cliente_id.
- Validación período: fecha_desde > fecha_hasta → excepción/lógica 1305.
- Cálculo total_horas en decimal (minutos/60).
- Ordenamiento y paginación.

### Integration Tests (Backend)
- GET /reports/detail como empleado: solo sus tareas.
- GET /reports/detail como supervisor: todas las tareas; filtros tipo_cliente_id, usuario_id.
- GET /reports/detail como cliente: solo su cliente_id.
- Params fecha_desde > fecha_hasta → 422 con error 1305.
- Respuesta incluye total_horas y pagination.

### Frontend Unit Tests (Vitest)
- Servicio getDetailReport: params correctos, transformación de data y totalHoras, manejo de error 1305.

### E2E Tests (Playwright)
- **Supervisor:** login → Consulta Detallada → aplicar filtros (período, empleado, cliente) → ver tabla y total de horas.
- **Empleado:** login → Consulta Detallada → ver solo sus tareas; sin selector empleado.
- **Cliente:** login → Consulta Detallada → ver solo tareas de su cliente; sin selector cliente
- **Período inválido:** aplicar fechas inválidas → ver mensaje de error 1305 (o validación en UI).

---

## 9) Riesgos y Edge Cases

- **Performance:** Muchas filas; asegurar índices y paginación en backend.
- **Permisos:** Aplicar siempre filtros por rol en backend; no confiar solo en ocultar controles en frontend.
- **Cliente vs Empleado:** Un mismo User puede ser cliente o empleado según tabla de origen; definir bien criterio de “rol” para esta pantalla (ej. si tiene registro en PQ_PARTES_CLIENTES es cliente para esta consulta).
- **Resultados vacíos:** Comportamiento alineado con HU-050 (mensaje, no tabla vacía).

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: servicio consulta detallada con filtros por rol y validación 1305
- [ ] Backend: endpoint GET /reports/detail (o /tasks/report) documentado
- [ ] Frontend: pantalla Consulta Detallada con filtros según rol
- [ ] Frontend: tabla con columnas indicadas, ordenamiento, paginación, total horas
- [ ] Frontend: estado vacío según HU-050
- [ ] Unit tests backend ok
- [ ] Integration tests endpoint ok
- [ ] Frontend unit tests (Vitest) servicio ok
- [ ] ≥1 E2E Playwright ok (supervisor y al menos empleado o cliente)
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/TaskService.php` – Añadido listDetailReport(), constante ERROR_PERIODO_INVALIDO (1305).
- `backend/app/Http/Controllers/Api/V1/ReportController.php` – Nuevo; detail().
- `backend/routes/api.php` – Ruta GET /api/v1/reports/detail.

### Frontend
- `frontend/src/features/tasks/services/task.service.ts` – getDetailReport(), DetailReportItem, DetailReportParams, GetDetailReportResult.
- `frontend/src/features/tasks/components/ConsultaDetalladaPage.tsx` – Nuevo.
- `frontend/src/features/tasks/components/ConsultaDetalladaPage.css` – Nuevo.
- `frontend/src/features/tasks/components/index.ts` – Export ConsultaDetalladaPage.
- `frontend/src/app/App.tsx` – Ruta /informes/consulta-detallada, import ConsultaDetalladaPage.
- `frontend/src/app/Dashboard.tsx` – Botón “Consulta Detallada”.

### Docs
- `docs/backend/tareas.md` – Sección GET /api/v1/reports/detail y código 1305.

### Tests
- `backend/tests/Unit/Services/TaskServiceTest.php` – Tests listDetailReport (empleado, supervisor, cliente, 1305, total_horas decimal).
- `backend/tests/Feature/Api/V1/ReportControllerTest.php` – Nuevo; tests GET /reports/detail por rol, 422 1305, 401.
- `frontend/src/features/tasks/services/task.service.test.ts` – Tests getDetailReport (200, 422 1305, sin token).
- `frontend/tests/e2e/consulta-detallada.spec.ts` – Nuevo; E2E supervisor y empleado en Consulta Detallada.

## Comandos ejecutados

- `php artisan test tests/Unit/Services/TaskServiceTest.php --filter=list_detail_report`
- `php artisan test tests/Feature/Api/V1/ReportControllerTest.php`
- `npm run test -- --run src/features/tasks/services/task.service.test.ts`

## Notas y decisiones

*(Se completará durante la implementación)*

## Pendientes / follow-ups

*(Se completará durante la implementación)*
