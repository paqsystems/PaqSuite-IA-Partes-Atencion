# TR-033(MH) – Visualización de Lista de Tareas Propias

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-033(MH)-visualización-de-lista-de-tareas-propias |
| Épica              | Épica 7: Registro de Tareas                |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado                                   |
| Dependencias       | HU-028 (Carga de Tarea Diaria), HU-029 (Edición), HU-030 (Eliminación) |
| Clasificación      | HU COMPLEJA **[REVISAR_SIMPLICIDAD]**     |
| Última actualización | 2026-01-29                               |
| Estado             | ✅ COMPLETADO (TR-033 + TR-033-update)     |

---

## 1) HU Refinada

### Título
Visualización de Lista de Tareas Propias

### Narrativa
**Como** empleado  
**Quiero** ver la lista de mis tareas registradas  
**Para** controlar lo que cargué y poder editarlas o eliminarlas

### Contexto/Objetivo
Los empleados necesitan visualizar todas sus tareas registradas en una tabla paginada con filtros, búsqueda y ordenamiento. Esta funcionalidad es esencial para gestionar las tareas propias y acceder a las acciones de edición y eliminación.

### Suposiciones explícitas
- El usuario ya está autenticado como empleado
- La tabla `PQ_PARTES_REGISTRO_TAREA` ya existe y tiene datos
- Existen endpoints para obtener lista de tareas con filtros
- Las acciones de edición y eliminación están implementadas (TR-029, TR-030)

### In Scope
- Tabla paginada con todas las tareas del usuario autenticado (o de todos los empleados si es supervisor)
- Columnas: fecha, cliente, tipo de tarea, duración (minutos y horas), sin cargo, presencial, observación (truncada), cerrado, acciones
- Filtros: rango de fechas, cliente (con opción "Todos"), tipo de tarea (con opción "Todos"); si el usuario es supervisor: filtro Empleado (con opción "Todos")
- Cuando cliente = "Todos", el selector de tipo de tarea carga todos los tipos (genéricos y no genéricos)
- Búsqueda por texto en observación
- Ordenamiento por fecha (asc/desc)
- Totales: cantidad de tareas y horas del período filtrado
- Indicador visual para tareas cerradas
- Acciones editar/eliminar deshabilitadas para tareas cerradas
- Paginación
- Layout común con header visible y botón "Volver" (o "Panel") a dashboard en todas las pantallas autenticadas

### Out of Scope
- Exportación a Excel/PDF
- Filtros avanzados (múltiples clientes simultáneos)
- Vista de calendario
- Agrupación por fecha/cliente
- Gráficos

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El empleado puede acceder a la sección "Mis Tareas"
- **AC-02**: Se muestra una tabla con todas las tareas del usuario autenticado
- **AC-03**: La tabla muestra: fecha, cliente, tipo de tarea, duración (minutos y horas), sin cargo, presencial, observación (truncada a 50 caracteres), cerrado (sí/no), acciones (editar, eliminar)
- **AC-04**: Las tareas se listan paginadas (10-20 por página)
- **AC-05**: Se puede filtrar por rango de fechas (fecha desde, fecha hasta)
- **AC-06**: Se puede filtrar por cliente (selector)
- **AC-07**: Se puede filtrar por tipo de tarea (selector)
- **AC-08**: Se puede buscar por texto en la observación (input de texto)
- **AC-09**: Se puede ordenar por fecha (ascendente/descendente)
- **AC-10**: Se muestra el total de tareas del período filtrado
- **AC-11**: Se muestra el total de horas del período filtrado
- **AC-12**: Las tareas cerradas se muestran claramente diferenciadas (indicador visual, estilo diferente)
- **AC-13**: Las acciones de editar/eliminar están deshabilitadas para tareas cerradas
- **AC-14**: Los filtros se pueden combinar (fecha + cliente + tipo + búsqueda)
- **AC-15**: Al cambiar de página, se mantienen los filtros aplicados

### Escenarios Gherkin

```gherkin
Feature: Visualización de Lista de Tareas Propias

  Scenario: Empleado visualiza sus tareas
    Given el empleado "JPEREZ" está autenticado
    And existen 25 tareas del usuario JPEREZ
    When accede a "Mis Tareas"
    Then se muestra tabla con 20 tareas (primera página)
    And se muestra paginación con 2 páginas
    And cada fila muestra: fecha, cliente, tipo, duración, sin_cargo, presencial, observación truncada, cerrado, acciones

  Scenario: Empleado filtra por rango de fechas
    Given el empleado "JPEREZ" está autenticado
    And existen tareas del usuario JPEREZ en diferentes fechas
    When accede a "Mis Tareas"
    And filtra por fecha desde "2026-01-01" hasta "2026-01-31"
    Then se muestran solo las tareas del mes de enero
    And se muestra el total de tareas y horas del período

  Scenario: Empleado busca por texto en observación
    Given el empleado "JPEREZ" está autenticado
    And existen tareas con observaciones variadas
    When accede a "Mis Tareas"
    And busca por texto "desarrollo"
    Then se muestran solo las tareas que contienen "desarrollo" en la observación

  Scenario: Empleado intenta editar tarea cerrada
    Given el empleado "JPEREZ" está autenticado
    And existe una tarea cerrada del usuario JPEREZ
    When accede a "Mis Tareas"
    Then la tarea cerrada se muestra con indicador visual
    And el botón editar está deshabilitado
    And el botón eliminar está deshabilitado
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo se muestran tareas del usuario autenticado (`usuario_id` coincide con usuario autenticado)
2. **RN-02**: Las tareas cerradas no se pueden editar ni eliminar
3. **RN-03**: Los filtros se aplican en el backend (no solo en frontend)
4. **RN-04**: La búsqueda por texto es case-insensitive
5. **RN-05**: La paginación tiene tamaño fijo (15-20 items por página)
6. **RN-06**: Los totales se calculan sobre el conjunto filtrado, no sobre todas las tareas

### Permisos por Rol
- **Empleado**: Solo ve sus propias tareas
- **Supervisor**: Ve todas las tareas (ver TR-034)

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: Consulta con filtros y paginación

### Cambios en Datos
- No se requieren nuevas columnas ni migraciones
- Se requieren índices para optimizar consultas:
  - Índice en `usuario_id` (ya existe probablemente)
  - Índice en `fecha` (para filtros de fecha)
  - Índice en `cliente_id` (para filtros de cliente)
  - Índice en `tipo_tarea_id` (para filtros de tipo)
  - Índice full-text en `observacion` (opcional, para búsqueda)

### Seed Mínimo para Tests
```php
// En TestTasksSeeder o similar:
- 30+ tareas del usuario JPEREZ con diferentes fechas, clientes, tipos
- Algunas tareas cerradas, otras abiertas
- Observaciones variadas para probar búsqueda
```

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/tasks`

**Descripción:** Obtener lista paginada de tareas del usuario autenticado con filtros.

**Autenticación:** Requerida (Bearer token)

**Autorización:** 
- Empleado: Solo ve sus propias tareas
- Supervisor: Ve todas las tareas (ver TR-034)

**Query Parameters:**
```
?page=1
&per_page=15
&fecha_desde=2026-01-01
&fecha_hasta=2026-01-31
&cliente_id=1
&tipo_tarea_id=2
&usuario_id=5          # opcional; solo para supervisores (filtrar por empleado; omitir = todos)
&busqueda=desarrollo
&ordenar_por=fecha
&orden=desc
```
**Nota:** GET `/api/v1/task-types` sin `cliente_id` retorna todos los tipos activos (genéricos y no genéricos).

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tareas obtenidas correctamente",
  "resultado": {
    "data": [
      {
        "id": 1,
        "fecha": "2026-01-28",
        "cliente": {
          "id": 1,
          "nombre": "Cliente A"
        },
        "tipo_tarea": {
          "id": 2,
          "nombre": "Desarrollo"
        },
        "duracion_minutos": 120,
        "duracion_horas": "2:00",
        "sin_cargo": false,
        "presencial": true,
        "observacion": "Desarrollo de feature X...",
        "cerrado": false,
        "created_at": "2026-01-28T10:00:00+00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 25,
      "last_page": 2
    },
    "totales": {
      "cantidad_tareas": 25,
      "total_horas": 50.5
    }
  }
}
```

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **AppLayout**: Layout común con header y botón Volver/Panel (visible en todas las rutas autenticadas)
- **TaskList**: Componente principal de lista
- **TaskTable**: Componente de tabla con columnas
- **TaskFilters**: Filtros (fecha, cliente, tipo, búsqueda; empleado solo para supervisores). Selectores con opción "Todos" y sin duplicar etiquetas (showLabel/allowAll)
- **TaskPagination**: Componente de paginación
- **TaskTotals**: Componente de totales
- **TaskRow**: Componente de fila de tabla con acciones

### Estados UI
- **Loading**: Cargando lista de tareas
- **Empty**: No hay tareas que mostrar
- **Error**: Error al cargar (sin conexión, error de servidor)
- **Success**: Lista cargada exitosamente
- **Filtering**: Aplicando filtros

### Validaciones en UI
- Validar formato de fechas en filtros
- Validar que fecha_desde <= fecha_hasta
- Mostrar mensaje cuando no hay resultados

### Accesibilidad Mínima
- `data-testid="app.volverButton"` en botón Volver/Panel (AppLayout)
- `data-testid="task.list.container"` en contenedor principal
- `data-testid="task.list.table"` en tabla
- `data-testid="task.list.filters"` en filtros
- `data-testid="task.list.pagination"` en paginación
- `data-testid="task.list.row.{id}"` en cada fila
- `data-testid="task.list.edit.{id}"` en botón editar
- `data-testid="task.list.delete.{id}"` en botón eliminar
- Labels y roles ARIA apropiados
- Navegación por teclado

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Dependencias | Estimación |
|----|------|-------------|-----|--------------|------------|
| T1 | Backend | TaskService::list() | Lógica de filtros, paginación, totales | TR-028 | L |
| T2 | Backend | TaskController::index() | Endpoint GET /tasks con query params | T1 | M |
| T3 | Backend | Índices en BD | Crear índices para optimizar consultas | TR-028 | S |
| T4 | Backend | Tests unitarios TaskService::list() | 8+ tests (filtros, paginación, totales) | T1 | M |
| T5 | Backend | Tests integración TaskController | 6+ tests (GET con diferentes filtros) | T2 | M |
| T6 | Frontend | Servicio task.service.ts::getTasks() | Función para obtener lista con filtros | TR-028 | M |
| T7 | Frontend | TaskList componente | Componente principal con estados | TR-028 | L |
| T8 | Frontend | TaskTable componente | Tabla con columnas y filas | T7 | M |
| T9 | Frontend | TaskFilters componente | Filtros (fecha, cliente, tipo, búsqueda) | T7 | M |
| T10 | Frontend | TaskPagination componente | Paginación | T7 | S |
| T11 | Frontend | TaskTotals componente | Totales (cantidad, horas) | T7 | S |
| T12 | Frontend | TaskRow componente | Fila con acciones editar/eliminar | T8 | M |
| T13 | Frontend | Ruta /tareas | Nueva ruta protegida | TR-028 | S |
| T14 | Frontend | Integración con TR-029, TR-030 | Botones editar/eliminar funcionan | TR-029, TR-030 | S |
| T15 | Tests | E2E Playwright lista básica | Verificar carga de lista | T7 | M |
| T16 | Tests | E2E Playwright filtros | Verificar filtros funcionan | T9 | M |
| T17 | Tests | E2E Playwright paginación | Verificar paginación funciona | T10 | S |
| T18 | Tests | E2E Playwright acciones | Verificar botones editar/eliminar | T12 | S |
| T19 | Tests | Frontend unit tests (Vitest) | Tests para task.service.ts getTasks() (params, transformación respuesta, paginación) | T6 | S |
| T20 | Docs | Actualizar docs/backend/tareas.md | Documentar endpoint GET /tasks | T2 | S |

**Total:** 21 tareas (8S + 9M + 4L)

---

## 8) Estrategia de Tests

### Unit Tests (TaskService)
- `list_retorna_tareas_del_usuario`
- `list_filtra_por_rango_fechas`
- `list_filtra_por_cliente`
- `list_filtra_por_tipo_tarea`
- `list_busca_por_texto_en_observacion`
- `list_ordena_por_fecha_ascendente`
- `list_ordena_por_fecha_descendente`
- `list_calcula_totales_correctamente`
- `list_pagina_correctamente`

### Integration Tests (TaskController)
- `index_retorna_lista_paginada`
- `index_aplica_filtro_fecha`
- `index_aplica_filtro_cliente`
- `index_aplica_filtro_tipo`
- `index_aplica_busqueda_texto`
- `index_retorna_totales`

### Frontend unit tests (Vitest)
- Tests para `task.service.ts`: `getTasks(params)` con mock de fetch, construcción de query params, transformación de respuesta (data, pagination, totales).

### E2E Tests (Playwright)
- **Lista básica**: Login → Acceder a "Mis Tareas" → Verificar tabla con datos
- **Filtros**: Login → Aplicar filtros → Verificar resultados filtrados
- **Paginación**: Login → Cambiar de página → Verificar nueva página
- **Acciones**: Login → Click editar/eliminar → Verificar navegación/acción
- **Tareas cerradas**: Login → Verificar que acciones están deshabilitadas

---

## 9) Riesgos y Edge Cases

- **Performance**: Muchas tareas pueden hacer lenta la consulta (usar índices, paginación)
- **Filtros combinados**: Múltiples filtros pueden reducir resultados a cero (mostrar mensaje apropiado)
- **Búsqueda**: Búsqueda por texto puede ser lenta con muchas tareas (considerar índice full-text)
- **Paginación**: Cambiar filtros debe resetear a página 1
- **Permisos**: Validar en backend que solo se muestren tareas propias

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend: TaskService::list() implementado (incl. filtro usuario_id para supervisores)
- [x] Backend: Endpoint GET /tasks implementado (query usuario_id)
- [ ] Backend: Índices en BD creados (opcional)
- [x] Frontend: AppLayout con header y botón Volver
- [x] Frontend: TaskList componente implementado
- [x] Frontend: TaskTable componente implementado
- [x] Frontend: TaskFilters (opciones "Todos", filtro Empleado para supervisores, alineación)
- [x] Frontend: TaskPagination componente implementado
- [x] Frontend: TaskTotals componente implementado
- [x] Frontend: Integración con acciones editar/eliminar
- [x] Unit tests TaskService ok
- [x] Integration tests TaskController ok
- [x] Frontend unit tests (Vitest) task.service getTasks ok
- [x] ≥1 E2E Playwright ok (sin waits ciegos)
- [x] Docs actualizadas
- [x] IA log actualizado

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/TaskService.php` – `listTasks()` con filtros, paginación, totales y filtro `usuario_id` (supervisores).
- `backend/app/Http/Controllers/Api/V1/TaskController.php` – `index()` GET /api/v1/tasks (acepta `usuario_id`); GET task-types sin `cliente_id` retorna todos los tipos activos (genéricos + no genéricos).
- `backend/routes/api.php` – Ruta GET /api/v1/tasks.
- `backend/tests/Unit/Services/TaskServiceTest.php` – Tests unitarios para `listTasks`.
- `backend/tests/Feature/Api/V1/TaskControllerTest.php` – Tests de integración para GET /api/v1/tasks y getTaskTypes.

### Frontend
- `frontend/src/app/AppLayout.tsx` y `AppLayout.css` – Layout común con header y botón Volver/Panel.
- `frontend/src/app/App.tsx` – Rutas anidadas bajo AppLayout (/, /perfil, /tareas, /tareas/nueva).
- `frontend/src/app/Dashboard.tsx` – Contenido sin header propio; enlace "Mis Tareas" (data-testid="app.myTasksLink").
- `frontend/src/features/tasks/services/task.service.ts` – Tipos y `getTasks()` con param `usuario_id`.
- `frontend/src/features/tasks/components/TaskList.tsx` – Lista con filtro `empleadoId` y buildParams con `usuario_id`.
- `frontend/src/features/tasks/components/TaskFilters.tsx` – Filtros con opciones "Todos" (cliente, tipo, empleado); filtro Empleado solo para supervisores; selectores con showLabel/allowAll.
- `frontend/src/features/tasks/components/ClientSelector.tsx`, `TaskTypeSelector.tsx`, `EmployeeSelector.tsx` – Props `showLabel`, `allowAll`; opción "Todos"; TaskTypeSelector sin cliente carga todos los tipos.
- `frontend/src/features/tasks/components/TaskPagination.tsx`, `TaskTotals.tsx` – Sin cambios de contrato.
- `frontend/src/features/tasks/components/TaskList.css` – Estilos y alineación de filtros.
- `frontend/src/features/tasks/components/index.ts` – Exportaciones.

### Tests unitarios frontend (Vitest)

- `frontend/src/features/tasks/services/task.service.test.ts` – Tests para getTasks() (params, transformación respuesta, paginación).

### Tests E2E
- `frontend/tests/e2e/task-list.spec.ts` – E2E: navegación a Mis Tareas, filtros, tabla o vacío.

## Comandos ejecutados

- Backend: `php artisan test --filter="TaskServiceTest::test_list_tasks|TaskControllerTest::index_"` (requiere BD).
- Frontend E2E: `cd frontend && npx playwright test tests/e2e/task-list.spec.ts` (requiere backend y frontend en marcha).

## Notas y decisiones

- Empleado: solo ve sus tareas. Supervisor: ve todas o filtra por empleado (query `usuario_id`); filtro Empleado en UI solo para supervisores, con opción "Todos".
- GET task-types sin `cliente_id`: retorna todos los tipos activos (genéricos y no genéricos) para el selector cuando Cliente = "Todos".
- Observación truncada a 50 caracteres en el listado (backend).
- Editar/eliminar: botones enlazan a `/tareas/:id/editar` y confirmación de eliminación; implementación real de TR-029/TR-030 en sus tickets.
- Totales calculados sobre el conjunto filtrado (mismo criterio que la consulta paginada).
- **TR-033-update aplicado (2026-01-29):** alineación de filtros; eliminación de títulos duplicados (showLabel en selectores); opciones "Todos" en Cliente, Tipo de tarea y Empleado; filtro Empleado para supervisores; AppLayout con header y botón Volver en todas las pantallas autenticadas.

## Pendientes / follow-ups

- Índices en BD (T3 del plan) – opcional según volumen.
- Integración real con TR-029 (editar) y TR-030 (eliminar) cuando estén implementados (rutas y API).
- E2E de filtros y paginación (opcional, ya hay E2E básico de lista).
