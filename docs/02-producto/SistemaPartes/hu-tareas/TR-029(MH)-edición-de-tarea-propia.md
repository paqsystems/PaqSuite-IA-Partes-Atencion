# TR-029(MH) – Edición de Tarea Propia

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-029(MH)-edición-de-tarea-propia          |
| Épica              | Épica 7: Registro de Tareas                |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado                                   |
| Dependencias       | HU-028 (Carga de Tarea Diaria)             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-01-29                               |
| Estado             | ✅ COMPLETADO (implementación TR-029)     |

---

## 1) HU Refinada

### Título
Edición de Tarea Propia

### Narrativa
**Como** empleado  
**Quiero** editar una tarea que registré  
**Para** corregir errores de carga

### Contexto/Objetivo
Los empleados necesitan poder corregir errores en las tareas que registraron. Esta funcionalidad permite editar tareas propias no cerradas, reutilizando el formulario de creación con carga de datos existentes.

### Suposiciones explícitas
- El usuario ya está autenticado como empleado
- La tarea existe y pertenece al usuario autenticado
- La tabla `PQ_PARTES_REGISTRO_TAREA` ya existe
- El formulario de creación (TR-028) ya está implementado y puede reutilizarse

### In Scope
- Acceso a edición desde lista de tareas
- Carga de datos existentes en formulario
- Validación de permisos (solo tareas propias)
- Validación de estado (no cerrada)
- Actualización de registro en BD
- Mensaje de confirmación
- Mismas validaciones que creación

### Out of Scope
- Edición de tareas cerradas
- Edición de tareas de otros usuarios
- Cambio de usuario propietario (solo supervisor puede hacerlo)
- Historial de cambios
- Notificaciones

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El empleado puede acceder a la edición desde la lista de sus tareas
- **AC-02**: Solo puede editar tareas propias (`usuario_id` coincide con usuario autenticado)
- **AC-03**: El sistema valida que la tarea no esté cerrada (`cerrado = false`)
- **AC-04**: Si la tarea está cerrada, se muestra error 2110 y no se permite edición
- **AC-05**: Se carga el formulario con los datos actuales de la tarea
- **AC-06**: El empleado puede modificar: fecha, cliente, tipo de tarea, duración, sin cargo, presencial, observación
- **AC-07**: Se aplican las mismas validaciones que en la creación (TR-028)
- **AC-08**: El `usuario_id` no es modificable (solo lectura, muestra nombre del empleado)
- **AC-09**: Al guardar, se actualiza el registro en la base de datos
- **AC-10**: Se muestra mensaje de confirmación
- **AC-11**: Los cambios se reflejan en la lista de tareas

### Escenarios Gherkin

```gherkin
Feature: Edición de Tarea Propia

  Scenario: Empleado edita tarea propia exitosamente
    Given el empleado "JPEREZ" está autenticado
    And existe una tarea con id=1, usuario_id=JPEREZ, cerrado=false
    When accede a editar la tarea id=1
    Then se carga el formulario con los datos de la tarea
    And puede modificar los campos: fecha, cliente, tipo, duración, sin_cargo, presencial, observación
    And el campo usuario_id es solo lectura
    When guarda los cambios
    Then se actualiza el registro en la BD
    And se muestra mensaje de confirmación

  Scenario: Empleado no puede editar tarea cerrada
    Given el empleado "JPEREZ" está autenticado
    And existe una tarea con id=1, usuario_id=JPEREZ, cerrado=true
    When intenta acceder a editar la tarea id=1
    Then se muestra error 2110
    And no se permite la edición

  Scenario: Empleado no puede editar tarea de otro usuario
    Given el empleado "JPEREZ" está autenticado
    And existe una tarea con id=1, usuario_id=MGARCIA, cerrado=false
    When intenta acceder a editar la tarea id=1
    Then se muestra error 403
    And no se permite la edición
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo el autor puede editar su tarea (`usuario_id` debe coincidir con usuario autenticado)
2. **RN-02**: Una tarea cerrada (`cerrado = true`) no se puede modificar
3. **RN-03**: Código de error 2110: "No se puede modificar una tarea cerrada"
4. **RN-04**: Código de error 403: "No tiene permisos para editar esta tarea"
5. **RN-05**: Se aplican las mismas validaciones que en la creación (fecha YMD, duración múltiplo de 15, cliente activo, tipo válido, etc.)
6. **RN-06**: El `usuario_id` no puede modificarse (solo lectura)

### Permisos por Rol
- **Empleado**: Solo puede editar sus propias tareas no cerradas
- **Supervisor**: Puede editar cualquier tarea (ver TR-031)

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: Actualización de registro existente

### Cambios en Datos
- No se requieren nuevas columnas ni migraciones
- Se actualizan campos: `fecha`, `cliente_id`, `tipo_tarea_id`, `duracion_minutos`, `sin_cargo`, `presencial`, `observacion`, `updated_at`

### Seed Mínimo para Tests
```php
// En TestTasksSeeder o similar:
- Tarea editable: id=1, usuario_id=JPEREZ, cerrado=false, fecha="2026-01-28"
- Tarea cerrada: id=2, usuario_id=JPEREZ, cerrado=true
- Tarea de otro usuario: id=3, usuario_id=MGARCIA, cerrado=false
```

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/tasks/{id}`

**Descripción:** Obtener datos de una tarea para edición.

**Autenticación:** Requerida (Bearer token)

**Autorización:** 
- Empleado: Solo puede obtener sus propias tareas
- Supervisor: Puede obtener cualquier tarea

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tarea obtenida correctamente",
  "resultado": {
    "id": 1,
    "usuario_id": 1,
    "cliente_id": 1,
    "tipo_tarea_id": 2,
    "fecha": "2026-01-28",
    "duracion_minutos": 120,
    "sin_cargo": false,
    "presencial": true,
    "observacion": "Desarrollo de feature X",
    "cerrado": false
  }
}
```

**Response 404 Not Found:**
```json
{
  "error": 4040,
  "respuesta": "Tarea no encontrada",
  "resultado": {}
}
```

**Response 403 Forbidden:**
```json
{
  "error": 4030,
  "respuesta": "No tiene permisos para acceder a esta tarea",
  "resultado": {}
}
```

### Endpoint: PUT `/api/v1/tasks/{id}`

**Descripción:** Actualizar una tarea existente.

**Autenticación:** Requerida (Bearer token)

**Autorización:** 
- Empleado: Solo puede actualizar sus propias tareas no cerradas
- Supervisor: Puede actualizar cualquier tarea no cerrada

**Request Body:**
```json
{
  "fecha": "2026-01-29",
  "cliente_id": 2,
  "tipo_tarea_id": 3,
  "duracion_minutos": 180,
  "sin_cargo": true,
  "presencial": false,
  "observacion": "Corrección: Desarrollo de feature Y"
}
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tarea actualizada correctamente",
  "resultado": {
    "id": 1,
    "usuario_id": 1,
    "cliente_id": 2,
    "tipo_tarea_id": 3,
    "fecha": "2026-01-29",
    "duracion_minutos": 180,
    "sin_cargo": true,
    "presencial": false,
    "observacion": "Corrección: Desarrollo de feature Y",
    "cerrado": false,
    "updated_at": "2026-01-28T15:30:00+00:00"
  }
}
```

**Response 422 Unprocessable Entity:**
```json
{
  "error": 4220,
  "respuesta": "Errores de validación",
  "resultado": {
    "fecha": ["La fecha es obligatoria"],
    "duracion_minutos": ["La duración debe ser múltiplo de 15"]
  }
}
```

**Response 2110 Bad Request (Tarea cerrada):**
```json
{
  "error": 2110,
  "respuesta": "No se puede modificar una tarea cerrada",
  "resultado": {}
}
```

**Response 403 Forbidden:**
```json
{
  "error": 4030,
  "respuesta": "No tiene permisos para editar esta tarea",
  "resultado": {}
}
```

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **TaskForm**: Reutilizar componente de TR-028, agregar modo edición
- **TaskList**: Agregar botón "Editar" por cada tarea
- **TaskEditPage**: Nueva página/ruta `/tareas/{id}/editar`

### Estados UI
- **Loading**: Cargando datos de la tarea
- **Error**: Error al cargar o guardar (tarea no encontrada, cerrada, sin permisos)
- **Success**: Tarea actualizada exitosamente
- **Form**: Formulario con datos precargados

### Validaciones en UI
- Mismas validaciones que creación (TR-028)
- Validación adicional: verificar que tarea no esté cerrada antes de mostrar formulario
- Validación adicional: verificar permisos antes de mostrar formulario

### Accesibilidad Mínima
- `data-testid="task.edit.button"` en botón editar
- `data-testid="task.edit.form"` en formulario
- `data-testid="task.edit.usuarioId"` en campo usuario_id (solo lectura)
- Labels y roles ARIA apropiados

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Dependencias | Estimación |
|----|------|-------------|-----|--------------|------------|
| T1 | Backend | FormRequest UpdateTaskRequest | Validaciones completas, código error 2110 | TR-028 | S |
| T2 | Backend | TaskService::update() | Lógica de negocio, validación permisos y estado | TR-028 | M |
| T3 | Backend | TaskController::show() | Endpoint GET /tasks/{id} | TR-028 | S |
| T4 | Backend | TaskController::update() | Endpoint PUT /tasks/{id} | T1, T2 | M |
| T5 | Backend | Tests unitarios TaskService::update() | 5+ tests (éxito, cerrada, sin permisos, validaciones) | T2 | M |
| T6 | Backend | Tests integración TaskController | 6+ tests (GET y PUT) | T3, T4 | M |
| T7 | Frontend | Servicio task.service.ts::getTask() | Función para obtener tarea | TR-028 | S |
| T8 | Frontend | Servicio task.service.ts::updateTask() | Función para actualizar tarea | TR-028 | S |
| T9 | Frontend | TaskForm modo edición | Reutilizar componente, agregar prop `taskId` | TR-028 | M |
| T10 | Frontend | TaskList botón editar | Agregar botón por tarea, navegar a /tareas/{id}/editar | TR-033 | S |
| T11 | Frontend | Ruta /tareas/{id}/editar | Nueva ruta protegida | TR-028 | S |
| T12 | Frontend | Manejo de errores | Mostrar errores 2110, 403, 404 | T9 | S |
| T13 | Tests | E2E Playwright edición exitosa | Flujo completo: lista → editar → guardar → verificar | T9, T10 | M |
| T14 | Tests | E2E Playwright tarea cerrada | Verificar error 2110 | T9 | S |
| T15 | Tests | E2E Playwright sin permisos | Verificar error 403 | T9 | S |
| T16 | Tests | Frontend unit tests (Vitest) | Tests para task.service.ts getTask/updateTask (mock API, manejo errores 2110/403) | T7, T8 | S |
| T17 | Docs | Actualizar docs/backend/tareas.md | Documentar endpoints GET y PUT | T3, T4 | S |

**Total:** 18 tareas (11S + 6M + 1L implícito)

---

## 8) Estrategia de Tests

### Unit Tests (TaskService)
- `update_exitoso_actualiza_tarea`
- `update_falla_tarea_cerrada_retorna_error_2110`
- `update_falla_sin_permisos_retorna_error_403`
- `update_valida_campos_igual_que_creacion`
- `update_no_modifica_usuario_id`

### Integration Tests (TaskController)
- `show_retorna_tarea_existente`
- `show_falla_tarea_no_encontrada_retorna_404`
- `show_falla_sin_permisos_retorna_403`
- `update_exitoso_actualiza_tarea`
- `update_falla_tarea_cerrada_retorna_2110`
- `update_falla_sin_permisos_retorna_403`
- `update_valida_campos_igual_que_creacion`

### Frontend unit tests (Vitest)
- Tests para `task.service.ts`: `getTask(id)`, `updateTask(id, payload)` con mock de fetch, manejo de respuestas 200/2110/403/404.

### E2E Tests (Playwright)
- **Flujo completo**: Login → Lista tareas → Click editar → Modificar campos → Guardar → Verificar cambios
- **Tarea cerrada**: Login → Lista tareas → Click editar tarea cerrada → Verificar error 2110
- **Sin permisos**: Login → Intentar editar tarea de otro usuario → Verificar error 403

---

## 9) Riesgos y Edge Cases

- **Concurrencia**: Dos usuarios editando la misma tarea simultáneamente (usar `updated_at` para detectar cambios)
- **Permisos**: Usuario intenta editar tarea de otro usuario (validar en backend)
- **Estado**: Tarea se cierra mientras se está editando (validar antes de guardar)
- **Datos incompletos**: Tarea eliminada mientras se está editando (manejar 404)

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend: UpdateTaskRequest con validaciones
- [x] Backend: TaskService::getTask() y updateTask() implementados
- [x] Backend: Endpoints GET /tasks/{id} y PUT /tasks/{id} implementados
- [x] Backend: Códigos de error 2110 y 4030 implementados
- [x] Frontend: TaskForm modo edición (prop taskId) implementado
- [x] Frontend: TaskList botón editar (ya existía, navega a /tareas/{id}/editar)
- [x] Frontend: Ruta /tareas/:id/editar con TaskEditPage implementada
- [x] Frontend: Manejo de errores 2110, 4030, 404
- [x] Unit tests TaskService ok (getTask, updateTask)
- [x] Integration tests TaskController ok (show, update)
- [x] Frontend unit tests (Vitest) getTask/updateTask ok
- [x] ≥1 E2E Playwright ok (task-edit.spec.ts)
- [x] Docs actualizadas
- [x] IA log actualizado

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/TaskService.php` – Constantes ERROR_CLOSED (2110), ERROR_FORBIDDEN_EDIT (4030); getTask(id, user); updateTask(id, datos, user).
- `backend/app/Http/Requests/Api/V1/UpdateTaskRequest.php` – Nuevo. Validaciones igual que CreateTaskRequest sin usuario_id.
- `backend/app/Http/Controllers/Api/V1/TaskController.php` – show(id), update(UpdateTaskRequest, id); handleTaskException() para mapear códigos.
- `backend/routes/api.php` – GET /tasks/{id}, PUT /tasks/{id}.
- `backend/tests/Unit/Services/TaskServiceTest.php` – Tests getTask (éxito, 404, cerrada, sin permisos); updateTask (éxito, cerrada, sin permisos).
- `backend/tests/Feature/Api/V1/TaskControllerTest.php` – Tests show (200, 404, 403, 400/2110); update (200, 400/2110, 403, 422).

### Frontend
- `frontend/src/features/tasks/services/task.service.ts` – Tipos TaskForEdit, GetTaskResult, UpdateTaskData, UpdateTaskResult; getTask(id); updateTask(id, payload).
- `frontend/src/features/tasks/components/TaskForm.tsx` – Prop taskId opcional; modo edición: carga con getTask, campo Empleado solo lectura, submit updateTask, redirección a /tareas.
- `frontend/src/features/tasks/components/TaskEditPage.tsx` – Nuevo. useParams id → TaskForm taskId.
- `frontend/src/features/tasks/components/TaskForm.css` – .form-input-readonly.
- `frontend/src/app/App.tsx` – Ruta /tareas/:id/editar con TaskEditPage.

### Tests unitarios frontend (Vitest)
- `frontend/src/features/tasks/services/task.service.test.ts` – Tests getTask (200, 404, 2110, 4030); updateTask (200, 2110, 422).

### Tests E2E
- `frontend/tests/e2e/task-edit.spec.ts` – E2E: navegar a editar desde lista, título "Editar Tarea".

## Comandos ejecutados

- Backend: `php artisan test --filter="TaskServiceTest::test_get_task|TaskServiceTest::test_update_task|TaskControllerTest::show_|TaskControllerTest::update_"`
- Frontend Vitest: `npm run test:run -- src/features/tasks/services/task.service.test.ts`
- Frontend E2E: `npx playwright test tests/e2e/task-edit.spec.ts` (requiere backend y frontend en marcha).

## Notas y decisiones

- GET /tasks/{id} retorna usuario_nombre para mostrar empleado en solo lectura en el formulario de edición.
- Empleado: solo puede editar sus tareas no cerradas. Supervisor: puede editar cualquier tarea no cerrada (TR-031 no implementado en este ticket).
- Error 2110 → HTTP 400; 4030 → HTTP 403; 4040 → HTTP 404.
- **Conservación de filtros al volver:** Al retornar de la edición (guardar o “Volver a la lista”), se conservan en la lista los valores de filtros (fechas, cliente, tipo de tarea, búsqueda, ordenamiento) y la página, mediante `location.state` (returnFilters, returnPage) y restauración en TaskList (estado inicial + useEffect).

## Pendientes / follow-ups

- TR-031: Edición de tarea por supervisor (mismo endpoint, permisos ya soportados en backend).
