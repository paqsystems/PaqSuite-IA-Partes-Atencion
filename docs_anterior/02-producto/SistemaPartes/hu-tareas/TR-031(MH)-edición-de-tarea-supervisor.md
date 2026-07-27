# TR-031(MH) – Edición de Tarea (Supervisor)

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-031(MH)-edición-de-tarea-supervisor     |
| Épica              | Épica 7: Registro de Tareas                |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-028 (Carga de Tarea Diaria), HU-019 (Gestión de Usuarios) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-01-28                               |
| Estado             | ✅ IMPLEMENTADO                            |

---

## 1) HU Refinada

### Título
Edición de Tarea (Supervisor)

### Narrativa
**Como** supervisor  
**Quiero** editar cualquier tarea del sistema  
**Para** corregir errores o ajustar información

### Contexto/Objetivo
Los supervisores necesitan poder corregir errores en cualquier tarea del sistema. Esta funcionalidad permite editar cualquier tarea no cerrada, incluyendo cambio de empleado propietario, reutilizando el formulario de creación con carga de datos existentes.

### Suposiciones explícitas
- El usuario ya está autenticado como supervisor (`es_supervisor = true`)
- La tarea existe
- La tabla `PQ_PARTES_REGISTRO_TAREA` ya existe
- El formulario de creación (TR-028) ya está implementado y puede reutilizarse
- Existe selector de empleados para cambio de propietario

### In Scope
- Acceso a edición desde lista de tareas (cualquier tarea)
- Carga de datos existentes en formulario
- Validación de estado (no cerrada)
- Cambio de empleado propietario (selector de usuarios)
- Actualización de registro en BD
- Mensaje de confirmación
- Mismas validaciones que creación

### Out of Scope
- Edición de tareas cerradas
- Historial de cambios
- Notificaciones al empleado propietario

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la edición de cualquier tarea desde la lista de tareas
- **AC-02**: El sistema valida que la tarea no esté cerrada (`cerrado = false`)
- **AC-03**: Si la tarea está cerrada, se muestra error 2110 y no se permite edición
- **AC-04**: Se carga el formulario con los datos actuales de la tarea
- **AC-05**: El supervisor puede modificar: fecha, cliente, tipo de tarea, duración, sin cargo, presencial, observación
- **AC-06**: El supervisor puede cambiar el empleado propietario (selector de usuarios activos)
- **AC-07**: Se aplican las mismas validaciones que en la creación (TR-028)
- **AC-08**: Al guardar, se actualiza el registro en la base de datos
- **AC-09**: Se muestra mensaje de confirmación
- **AC-10**: Los cambios se reflejan en la lista de tareas

### Escenarios Gherkin

```gherkin
Feature: Edición de Tarea (Supervisor)

  Scenario: Supervisor edita tarea exitosamente
    Given el supervisor "MGARCIA" está autenticado
    And existe una tarea con id=1, usuario_id=JPEREZ, cerrado=false
    When accede a editar la tarea id=1
    Then se carga el formulario con los datos de la tarea
    And puede modificar todos los campos incluyendo empleado propietario
    When guarda los cambios
    Then se actualiza el registro en la BD
    And se muestra mensaje de confirmación

  Scenario: Supervisor cambia empleado propietario
    Given el supervisor "MGARCIA" está autenticado
    And existe una tarea con id=1, usuario_id=JPEREZ, cerrado=false
    When accede a editar la tarea id=1
    And cambia el empleado propietario a "OTROEMPLEADO"
    When guarda los cambios
    Then la tarea queda asociada a "OTROEMPLEADO"
    And el usuario_id se actualiza en la BD

  Scenario: Supervisor no puede editar tarea cerrada
    Given el supervisor "MGARCIA" está autenticado
    And existe una tarea con id=1, cerrado=true
    When intenta acceder a editar la tarea id=1
    Then se muestra error 2110
    And no se permite la edición
```

---

## 3) Reglas de Negocio

1. **RN-01**: El supervisor puede editar cualquier tarea del sistema
2. **RN-02**: Una tarea cerrada (`cerrado = true`) no se puede modificar
3. **RN-03**: Código de error 2110: "No se puede modificar una tarea cerrada"
4. **RN-04**: El supervisor puede cambiar el `usuario_id` de la tarea
5. **RN-05**: Se aplican las mismas validaciones que en la creación (fecha YMD, duración múltiplo de 15, cliente activo, tipo válido, empleado activo, etc.)

### Permisos por Rol
- **Supervisor**: Puede editar cualquier tarea no cerrada, incluyendo cambio de propietario
- **Empleado**: Solo puede editar sus propias tareas (ver TR-029)

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: Actualización de registro existente, posible cambio de `usuario_id`

### Cambios en Datos
- No se requieren nuevas columnas ni migraciones
- Se actualizan campos: `fecha`, `cliente_id`, `tipo_tarea_id`, `duracion_minutos`, `sin_cargo`, `presencial`, `observacion`, `usuario_id` (puede cambiar), `updated_at`

### Seed Mínimo para Tests
```php
// En TestTasksSeeder o similar:
- Tarea editable: id=1, usuario_id=JPEREZ, cerrado=false
- Tarea cerrada: id=2, cerrado=true
- Supervisor: MGARCIA (es_supervisor=true)
- Empleado alternativo: OTROEMPLEADO (activo, no inhabilitado)
```

---

## 5) Contratos de API

### Endpoint: PUT `/api/v1/tasks/{id}`

**Descripción:** Actualizar una tarea existente (supervisor puede cambiar propietario).

**Autenticación:** Requerida (Bearer token)

**Autorización:** 
- Supervisor: Puede actualizar cualquier tarea no cerrada, incluyendo cambio de `usuario_id`
- Empleado: Solo puede actualizar sus propias tareas (ver TR-029)

**Request Body:**
```json
{
  "fecha": "2026-01-29",
  "cliente_id": 2,
  "tipo_tarea_id": 3,
  "duracion_minutos": 180,
  "sin_cargo": true,
  "presencial": false,
  "observacion": "Corrección: Desarrollo de feature Y",
  "usuario_id": 5  // Opcional: solo para supervisores, cambia el propietario
}
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tarea actualizada correctamente",
  "resultado": {
    "id": 1,
    "usuario_id": 5,
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
    "usuario_id": ["El empleado seleccionado no existe o no está activo"]
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

**Response 403 Forbidden (Empleado intenta cambiar propietario):**
```json
{
  "error": 4030,
  "respuesta": "Solo los supervisores pueden cambiar el propietario de una tarea",
  "resultado": {}
}
```

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **TaskForm**: Reutilizar componente de TR-028, agregar modo edición con selector de empleado visible para supervisores
- **TaskList**: Agregar botón "Editar" por cada tarea (visible para supervisores)
- **EmployeeSelector**: Reutilizar componente de TR-028, habilitado en modo edición para supervisores

### Estados UI
- **Loading**: Cargando datos de la tarea
- **Error**: Error al cargar o guardar (tarea no encontrada, cerrada)
- **Success**: Tarea actualizada exitosamente
- **Form**: Formulario con datos precargados, selector de empleado habilitado

### Validaciones en UI
- Mismas validaciones que creación (TR-028)
- Validación adicional: verificar que tarea no esté cerrada antes de mostrar formulario
- Validación adicional: verificar que empleado seleccionado esté activo

### Accesibilidad Mínima
- `data-testid="task.edit.button"` en botón editar
- `data-testid="task.edit.form"` en formulario
- `data-testid="task.edit.employeeSelector"` en selector de empleado
- Labels y roles ARIA apropiados

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Dependencias | Estimación |
|----|------|-------------|-----|--------------|------------|
| T1 | Backend | TaskService::update() extender | Validar permisos supervisor, permitir cambio usuario_id | TR-028, TR-029 | M |
| T2 | Backend | UpdateTaskRequest extender | Validar usuario_id opcional para supervisores | TR-028, TR-029 | S |
| T3 | Backend | TaskController::update() | Validar permisos supervisor en endpoint | TR-029 | S |
| T4 | Backend | Tests unitarios TaskService::update() supervisor | 3+ tests (cambio propietario, validación empleado) | T1 | M |
| T5 | Backend | Tests integración TaskController supervisor | 3+ tests (PUT con cambio propietario) | T3 | M |
| T6 | Frontend | TaskForm modo edición supervisor | Habilitar selector de empleado para supervisores | TR-028, TR-029 | M |
| T7 | Frontend | TaskList botón editar supervisor | Mostrar botón para todas las tareas si es supervisor | TR-033 | S |
| T8 | Frontend | Manejo de errores | Mostrar errores 2110, 403, 422 | T6 | S |
| T9 | Tests | E2E Playwright edición supervisor | Flujo completo con cambio de propietario | T6 | M |
| T10 | Tests | E2E Playwright tarea cerrada | Verificar error 2110 | T6 | S |
| T11 | Tests | Frontend unit tests (Vitest) | Tests para task.service.ts updateTask con usuario_id (supervisor) si aplica | T6 | S |
| T12 | Docs | Actualizar docs/backend/tareas.md | Documentar cambio de propietario | T3 | S |

**Total:** 13 tareas (7S + 5M + 1L implícito)

---

## 8) Estrategia de Tests

### Unit Tests (TaskService)
- `update_supervisor_puede_cambiar_propietario`
- `update_supervisor_valida_empleado_activo`
- `update_falla_tarea_cerrada_retorna_error_2110`

### Integration Tests (TaskController)
- `update_supervisor_exitoso_con_cambio_propietario`
- `update_supervisor_falla_empleado_inactivo_retorna_422`
- `update_falla_tarea_cerrada_retorna_2110`

### Frontend unit tests (Vitest)
- Tests para `task.service.ts` updateTask con payload que incluye `usuario_id` (solo supervisor), mock API.

### E2E Tests (Playwright)
- **Flujo completo**: Login supervisor → Lista tareas → Editar tarea de otro → Cambiar propietario → Guardar → Verificar cambios
- **Tarea cerrada**: Login supervisor → Intentar editar tarea cerrada → Verificar error 2110

---

## 9) Riesgos y Edge Cases

- **Permisos**: Empleado intenta cambiar propietario (validar en backend)
- **Estado**: Tarea se cierra mientras se está editando (validar antes de guardar)
- **Empleado inactivo**: Supervisor intenta asignar a empleado inactivo (validar en backend)
- **Concurrencia**: Dos supervisores editando la misma tarea simultáneamente (usar `updated_at`)

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend: TaskService::update() extendido para supervisores
- [x] Backend: Validación de cambio de propietario implementada
- [x] Backend: Endpoint PUT con permisos supervisor implementado
- [x] Frontend: TaskForm modo edición supervisor implementado
- [x] Frontend: Selector de empleado habilitado para supervisores
- [x] Frontend: Manejo de errores implementado
- [x] Unit tests TaskService ok
- [x] Integration tests TaskController ok
- [x] Frontend unit tests (Vitest) ok
- [x] ≥1 E2E Playwright ok (task-edit-supervisor.spec.ts)
- [x] Docs actualizadas
- [ ] IA log actualizado (pendiente registro manual)

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/TaskService.php` – updateTask(): usuario_id opcional; solo supervisor puede cambiar propietario; validación empleado activo.
- `backend/app/Http/Requests/Api/V1/UpdateTaskRequest.php` – usuario_id opcional; withValidator empleado activo.
- `backend/app/Http/Controllers/Api/V1/TaskController.php` – handleTaskException 422 con resultado.errors.usuario_id.
- `backend/tests/Unit/Services/TaskServiceTest.php` – tests supervisor cambio propietario, empleado inactivo, empleado no puede enviar usuario_id.
- `backend/tests/Feature/Api/V1/TaskControllerTest.php` – update_supervisor_exitoso_con_cambio_propietario, update_supervisor_falla_empleado_inactivo_retorna_422.

### Frontend
- `frontend/src/features/tasks/services/task.service.ts` – UpdateTaskData.usuario_id; updateTask envía usuario_id.
- `frontend/src/features/tasks/components/TaskForm.tsx` – isSupervisor en edición; EmployeeSelector para supervisor; payload usuario_id.
- `frontend/src/features/tasks/services/task.service.test.ts` – tests updateTask con usuario_id.
- `frontend/tests/e2e/task-edit-supervisor.spec.ts` – E2E edición supervisor.

## Comandos ejecutados

- `php artisan test --filter="update_supervisor|update_empleado_no_puede|update_falla_tarea_cerrada"`
- `npm run test:run -- src/features/tasks/services/task.service.test.ts`
- `npx playwright test task-edit-supervisor.spec.ts --project=chromium`

## Notas y decisiones

- Solo supervisores pueden enviar usuario_id en PUT; empleado recibe 4030.
- En edición, supervisor ve EmployeeSelector; empleado ve campo solo lectura.

## Pendientes / follow-ups

- TR-032: eliminación por supervisor (ya soportado en DELETE).
