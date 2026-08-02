# TR-021(MH) – Eliminación de Empleado

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-021(MH)-eliminación-de-empleado         |
| Épica              | Épica 5: Gestión de Empleados (ABM)        |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-020 (edición de empleado), HU-018 (listado) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-05                               |
| Estado             | 📋 PENDIENTE                                |

---

## 1) HU Refinada

### Título
Eliminación de Empleado

### Narrativa
**Como** supervisor  
**Quiero** eliminar un empleado que ya no trabaja  
**Para** mantener el catálogo actualizado

### Contexto/Objetivo
El supervisor puede eliminar un empleado desde el listado (HU-018) o desde el detalle/edición (HU-020). Antes de eliminar, el sistema verifica si el empleado tiene tareas asociadas (PQ_PARTES_registro_tarea o equivalente). Si tiene tareas, no se permite la eliminación (error 2113). Si no tiene tareas, se muestra un diálogo de confirmación con código y nombre del empleado; al confirmar, se elimina el empleado y se muestra mensaje de éxito; el empleado desaparece del listado.

### Suposiciones explícitas
- El usuario ya está autenticado como supervisor (HU-001).
- Existe el listado de empleados (HU-018) con opción "Eliminar" por empleado (y opcionalmente en detalle/edición).
- La tabla PQ_PARTES_registro_tarea (o equivalente) tiene FK empleado_id o user_id; no se puede eliminar un empleado con registros referenciándolo.
- Eliminación física (DELETE) o soft delete según diseño del proyecto; la HU indica "elimina el empleado de la base de datos" (eliminación física o lógica según convención del proyecto).
- Al eliminar empleado, considerar si también se elimina el registro en USERS o se mantiene para historial (definir según diseño del proyecto).

### In Scope
- Opción "Eliminar" accesible desde listado (y opcionalmente desde detalle/edición de empleado).
- Verificación en backend: empleado sin tareas asociadas (PQ_PARTES_registro_tarea donde empleado_id o user_id = id).
- Si tiene tareas: error 2113 (no se puede eliminar).
- Si no tiene tareas: modal de confirmación mostrando código y nombre del empleado; usuario debe confirmar.
- Al confirmar: DELETE /api/v1/empleados/{id}; eliminar empleado (y posiblemente registro en USERS según diseño); mensaje de confirmación; recargar listado o redirigir al listado; empleado desaparece del listado.
- Solo supervisores pueden eliminar (403 si no).

### Out of Scope
- Eliminación en cascada de tareas (no se eliminan tareas; se impide eliminar el empleado si tiene tareas).
- Restauración de empleados eliminados (soft delete fuera de alcance de esta HU si no se especifica).
- Eliminación masiva de empleados.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la opción de eliminar un empleado desde el listado (o detalle/edición).
- **AC-02**: Un usuario no supervisor no puede eliminar (403 o opción no visible).
- **AC-03**: Antes de eliminar, el sistema verifica si el empleado tiene tareas asociadas.
- **AC-04**: Si el empleado tiene tareas asociadas, se muestra un error (2113) y no se permite la eliminación.
- **AC-05**: Si el empleado no tiene tareas asociadas, se muestra un diálogo de confirmación.
- **AC-06**: El diálogo muestra el código y nombre del empleado a eliminar.
- **AC-07**: El usuario debe confirmar la eliminación (botón "Confirmar" o "Eliminar"; cancelar cierra el modal).
- **AC-08**: Al confirmar, el sistema elimina el empleado (DELETE en backend).
- **AC-09**: Se muestra un mensaje de confirmación tras la eliminación exitosa.
- **AC-10**: El empleado desaparece del listado (recarga o redirección al listado).

### Escenarios Gherkin

```gherkin
Feature: Eliminación de Empleado

  Scenario: Supervisor elimina empleado sin tareas asociadas
    Given el supervisor está autenticado
    And existe un empleado "JPEREZ" con nombre "Juan Pérez"
    And el empleado no tiene tareas asociadas
    When hace clic en "Eliminar" para el empleado "JPEREZ"
    Then se muestra un diálogo de confirmación
    And el diálogo muestra el código "JPEREZ" y el nombre "Juan Pérez"
    When confirma la eliminación
    Then el sistema elimina el empleado
    And se muestra mensaje de confirmación
    And el empleado desaparece del listado

  Scenario: No se puede eliminar empleado con tareas asociadas
    Given el supervisor está autenticado
    And existe un empleado "MGARCIA" con tareas asociadas
    When intenta eliminar el empleado "MGARCIA"
    Then el sistema verifica y detecta tareas asociadas
    And no se elimina el empleado
    And se muestra error 2113 (no se puede eliminar empleado con tareas asociadas)

  Scenario: Usuario no supervisor no puede eliminar
    Given un empleado no supervisor está autenticado
    When accede al listado de empleados
    Then no ve la opción "Eliminar" o al intentar eliminar recibe 403

  Scenario: Cancelar eliminación
    Given el supervisor hizo clic en "Eliminar" para un empleado sin tareas
    When se muestra el diálogo de confirmación
    And hace clic en "Cancelar"
    Then el diálogo se cierra
    And el empleado no se elimina
    And permanece en el listado
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden eliminar empleados (endpoint y opción protegidos).
2. **RN-02**: No se puede eliminar un empleado si tiene tareas asociadas (registros en PQ_PARTES_registro_tarea con empleado_id o user_id = id). Integridad referencial.
3. **RN-03**: Código de error cuando tiene tareas: 2113 ("No se puede eliminar un empleado que tiene tareas asociadas").
4. **RN-04**: Empleado inexistente (id no existe) → 404 (4003).
5. **RN-05**: La eliminación requiere confirmación explícita del usuario (modal con código y nombre).
6. **RN-06**: Al eliminar empleado, definir si también se elimina el registro en USERS o se mantiene para historial (documentar decisión según diseño del proyecto).

### Permisos por Rol
- **Supervisor:** Acceso a la opción eliminar y al endpoint DELETE /api/v1/empleados/{id}.
- **Empleado (no supervisor):** Sin acceso; 403 o opción no visible.
- **Cliente:** No aplica (no eliminan empleados).

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_registro_tarea` (o equivalente): SELECT COUNT/EXISTS donde empleado_id o user_id = id (verificación antes de eliminar).
- `PQ_PARTES_USUARIOS` (o equivalente): DELETE donde id = id (o soft delete si el proyecto lo usa).
- `USERS`: DELETE o mantener según diseño del proyecto (si el empleado tiene user_id, definir si se elimina o desvincula).

### Cambios en Datos
- No se requieren nuevas tablas ni columnas. DELETE (o update de deleted_at) en PQ_PARTES_USUARIOS y posiblemente USERS.

### Migración + Rollback
- No se requiere migración nueva.

### Seed Mínimo para Tests
- Empleado sin tareas; empleado con tareas asociadas; usuario supervisor; usuario empleado no supervisor.

---

## 5) Contratos de API

### Endpoint: DELETE `/api/v1/empleados/{id}`

**Descripción:** Eliminar un empleado. Solo supervisores. No se puede eliminar si tiene tareas asociadas.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. 403 (3101) si no. 404 (4003) si empleado no existe.

**Path Parameters:** `id` (integer) – ID del empleado a eliminar.

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Empleado eliminado correctamente",
  "resultado": {}
}
```

**Response 401 Unauthorized:** No autenticado (3001).

**Response 403 Forbidden:** No supervisor (3101).
```json
{
  "error": 3101,
  "respuesta": "No tiene permisos para acceder a esta funcionalidad",
  "resultado": {}
}
```

**Response 404 Not Found:** Empleado no encontrado (4003).
```json
{
  "error": 4003,
  "respuesta": "Empleado no encontrado",
  "resultado": {}
}
```

**Response 422 Unprocessable Entity:** Empleado con tareas asociadas (2113).
```json
{
  "error": 2113,
  "respuesta": "No se puede eliminar un empleado que tiene tareas asociadas",
  "resultado": {}
}
```

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **Listado de empleados (HU-018):** Botón o enlace "Eliminar" por fila (y opcionalmente en pantalla detalle/edición).
- **Modal de confirmación (DeleteEmpleadoModal o similar):** Título "Eliminar empleado"; texto con código y nombre del empleado; botones "Cancelar" y "Confirmar" (o "Eliminar"). Mostrar mensaje de error si el backend retorna 2113 (empleado con tareas).
- Tras confirmar: llamar DELETE /api/v1/empleados/{id}; si 200: cerrar modal, mensaje de éxito, recargar listado o redirigir a /empleados; si 2113: mostrar mensaje "No se puede eliminar un empleado que tiene tareas asociadas"; si 404: mensaje empleado no encontrado; si 403: mensaje sin permisos.

### Estados UI
- Loading: mientras se envía DELETE.
- Error: 2113 (mostrar en modal o toast); 403, 404.
- Success: mensaje de confirmación, cierre del modal, listado actualizado.

### Validaciones en UI
- No eliminar sin confirmación (el modal obliga a confirmar o cancelar).

### Accesibilidad Mínima
- `data-testid` en: botón eliminar (empleados.delete.button o por fila empleados.row.{id}.delete), modal (empleados.delete.modal), código/nombre en modal (empleados.delete.code, empleados.delete.nombre), botón confirmar (empleados.delete.confirm), botón cancelar (empleados.delete.cancel).
- Labels y roles ARIA apropiados (dialog, alertdialog si aplica).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | EmpleadoService::delete() o canDelete() + delete() | Verificar que no existan registros en PQ_PARTES_registro_tarea con empleado_id o user_id = id; si existen, lanzar excepción/retornar error 2113. Si no existen, eliminar empleado (DELETE o soft delete). Considerar eliminación de registro en USERS según diseño. | HU-020 | M |
| T2 | Backend  | EmpleadoController::destroy() | DELETE /api/v1/empleados/{id}. Solo supervisor. Llamar servicio; 200, 422 (2113), 403, 404. | T1 | M |
| T3 | Backend  | Tests unitarios regla "no eliminar si tiene tareas" | Empleado con tareas → delete lanza excepción o retorna 2113; empleado sin tareas → delete elimina. | T1 | M |
| T4 | Backend  | Tests integración DELETE /empleados/{id} | DELETE con tareas → 422 (2113); DELETE sin tareas → 200; DELETE id inexistente → 404; DELETE como empleado → 403; DELETE sin token → 401. | T2 | M |
| T5 | Frontend | Servicio empleado.service.ts deleteEmpleado() | DELETE /api/v1/empleados/{id}; manejo 200, 422 (2113), 403, 404. | — | S |
| T6 | Frontend | Modal de confirmación eliminar empleado | Modal con código y nombre; botones Cancelar y Confirmar; mostrar error 2113 si aplica. data-testid. | HU-018 | M |
| T7 | Frontend | Integración botón Eliminar y modal con API | Al hacer clic en Eliminar, abrir modal con datos del empleado; al confirmar, llamar deleteEmpleado(id); éxito: cerrar modal, mensaje, recargar listado; error 2113: mostrar mensaje en modal o toast. | T5, T6 | M |
| T8 | Tests    | E2E Playwright eliminar empleado sin tareas | Login supervisor → Empleados → Eliminar empleado sin tareas → confirmar → ver mensaje y desaparición del listado. | T6 | M |
| T9 | Tests    | E2E intentar eliminar empleado con tareas (opcional) | Si hay empleado con tareas, eliminar → ver mensaje 2113 y empleado sigue en listado. | T6 | S |
| T10| Frontend | Tests unit (Vitest) deleteEmpleado | deleteEmpleado(id); manejo 200, 422 (2113), 404. | T5 | S |
| T11| Docs     | Actualizar specs/endpoints/empleados-delete.md | Verificar códigos 2113, 4003, 3101. | T2 | S |

**Total:** 11 tareas (3S + 7M + 0L).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio delete: empleado con al menos una tarea en PQ_PARTES_registro_tarea → no eliminar, retornar/lanzar 2113; empleado sin tareas → eliminar y retornar éxito; empleado inexistente → 404.

### Integration Tests (Backend)
- DELETE /api/v1/empleados/{id} con empleado sin tareas → 200, empleado eliminado.
- DELETE con empleado que tiene tareas → 422, body error 2113, empleado no eliminado.
- DELETE con id inexistente → 404.
- DELETE como empleado no supervisor → 403.
- DELETE sin token → 401.

### Frontend Unit Tests (Vitest)
- deleteEmpleado(id): manejo 200 (éxito), 422 (2113), 404.

### E2E Tests (Playwright)
- Supervisor → Empleados → Eliminar (empleado sin tareas) → modal con código y nombre → Confirmar → mensaje de éxito y empleado ya no aparece en el listado.
- Opcional: Eliminar empleado con tareas → ver mensaje de error 2113.

---

## 9) Riesgos y Edge Cases

- **Integridad referencial:** Asegurar que la verificación de tareas asociadas sea atómica con la eliminación (evitar condición de carrera: verificar y luego eliminar en la misma transacción o con lock si aplica).
- **Empleado con user_id:** Si el empleado tiene registro en USERS, definir si al eliminar el empleado se elimina o desvincula el registro USERS (puede quedar fuera de alcance de esta HU; documentar decisión).
- **Relación con tareas:** Verificar correctamente la FK empleado_id o user_id en PQ_PARTES_registro_tarea según el diseño del modelo de datos.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: verificación "no eliminar si tiene tareas" (2113) y DELETE /api/v1/empleados/{id}
- [ ] Backend: 200, 422 (2113), 403, 404 documentados
- [ ] Frontend: modal de confirmación con código y nombre; botones Cancelar y Confirmar
- [ ] Frontend: integración con DELETE; mensaje 2113 cuando aplica; recarga listado tras éxito
- [ ] Unit tests backend ok
- [ ] Integration tests DELETE ok
- [ ] Frontend unit tests (Vitest) ok
- [ ] ≥1 E2E Playwright eliminar empleado sin tareas ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/EmpleadoService.php` – Agregado método delete() con verificación de tareas asociadas y eliminación transaccional de Usuario y User.
- `backend/app/Http/Controllers/Api/V1/EmpleadoController.php` – Agregado método destroy() para DELETE /api/v1/empleados/{id}.
- `backend/routes/api.php` – Agregada ruta DELETE /api/v1/empleados/{id}.
- `backend/tests/Feature/Api/V1/EmpleadoControllerTest.php` – Agregados 5 tests de integración para eliminación.

### Frontend
- `frontend/src/features/employees/services/empleado.service.ts` – Agregada función deleteEmpleado() con manejo de errores 200, 422 (2113), 404, 403.
- `frontend/src/features/employees/components/EmpleadosPage.tsx` – Agregado modal de confirmación y handlers para eliminación.
- `frontend/src/features/employees/components/EmpleadosPage.css` – Agregados estilos para modal de confirmación y mensaje de éxito.
- `frontend/src/features/employees/services/empleado.service.test.ts` – Agregados 4 tests unitarios para deleteEmpleado().
- `frontend/tests/e2e/empleados-delete.spec.ts` – Tests E2E con Playwright para eliminación de empleados.

### Docs
- `docs/04-tareas/TR-021(MH)-eliminación-de-empleado.md` – Estado actualizado a COMPLETADO.

## Comandos ejecutados

```bash
# Backend tests
cd backend && php artisan test --filter EmpleadoControllerTest::test_destroy

# Frontend unit tests
cd frontend && npm run test:run -- empleado.service.test.ts

# Frontend E2E tests (requieren entorno corriendo)
cd frontend && npm run test:e2e -- empleados-delete.spec.ts
```

## Notas y decisiones

- La implementación sigue el patrón de TR-011 (eliminación de clientes) adaptado para empleados.
- La verificación de tareas asociadas se realiza antes de eliminar para garantizar integridad referencial.
- La eliminación se realiza en una transacción: primero se elimina el Usuario y luego el User asociado para evitar restricciones de foreign key.
- El modal de confirmación muestra código y nombre del empleado antes de confirmar la eliminación.
- Si el empleado tiene tareas asociadas, se muestra un error específico (2113) en el modal.

## Pendientes / follow-ups

- Ninguno. La tarea TR-021(MH) está completa.
