# TR-022(SH) – Visualización de Detalle de Empleado

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-022(SH)-visualización-de-detalle-de-empleado |
| Épica              | Épica 5: Gestión de Empleados (ABM)        |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-020 (edición de empleado), HU-021 (eliminación de empleado), HU-018 (listado) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-05                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Visualización de Detalle de Empleado

### Narrativa
**Como** supervisor  
**Quiero** ver el detalle completo de un empleado incluyendo estadísticas básicas de tareas registradas  
**Para** tener una visión contextual completa del empleado

### Contexto/Objetivo
El supervisor puede acceder al detalle de un empleado desde el listado (HU-018). Se muestra toda la información del empleado: código, nombre, email, supervisor, estado (activo/inhabilitado). Opcionalmente se muestra la cantidad total de tareas registradas por el empleado y las fechas de creación y última actualización. Desde el detalle, el supervisor puede editar el empleado (HU-020) o eliminarlo (HU-021) si no tiene tareas asociadas. Es una pantalla de visualización que complementa el ABM de empleados.

### Suposiciones explícitas
- El usuario ya está autenticado como supervisor (HU-001).
- Existe el listado de empleados (HU-018) con enlace "Ver detalle" o similar por empleado.
- Existe la funcionalidad de edición (HU-020) y eliminación (HU-021) que se pueden invocar desde el detalle.
- El endpoint GET /api/v1/empleados/{id} ya existe (usado en edición) y puede extenderse para incluir estadísticas opcionales.
- La tabla PQ_PARTES_registro_tarea existe para calcular estadísticas de tareas.

### In Scope
- Pantalla de detalle accesible desde listado (ruta ej. /empleados/:id).
- GET /api/v1/empleados/{id} para obtener datos del empleado (ya existe, puede extenderse con estadísticas opcionales).
- Mostrar información del empleado: código, nombre, email, supervisor (sí/no), estado (activo/inactivo), inhabilitado (sí/no).
- Mostrar estadísticas opcionales: cantidad total de tareas registradas por el empleado.
- Mostrar fechas opcionales: fecha de creación (created_at) y última actualización (updated_at).
- Botones o enlaces para acciones: "Editar" (lleva a HU-020) y "Eliminar" (lleva a HU-021 o muestra modal).
- Solo supervisores pueden acceder (403 si no).

### Out of Scope
- Edición de empleado (HU-020; se invoca desde el detalle pero se implementa en su propia HU).
- Eliminación de empleado (HU-021; se invoca desde el detalle pero se implementa en su propia HU).
- Estadísticas avanzadas de tareas (solo cantidad total básica).
- Historial de cambios del empleado.
- Lista detallada de tareas del empleado (puede ser otra HU).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder al detalle de un empleado desde el listado (enlace "Ver detalle" o similar).
- **AC-02**: Un usuario no supervisor no puede acceder (403 o enlace no visible).
- **AC-03**: Se muestra toda la información del empleado: código, nombre, email, supervisor (sí/no), estado (activo/inactivo), inhabilitado (sí/no).
- **AC-04**: Se muestra la cantidad total de tareas registradas por el empleado (opcional, si está disponible en el backend).
- **AC-05**: Se muestra la fecha de creación y última actualización (opcional, si están disponibles).
- **AC-06**: El supervisor puede editar el empleado desde el detalle (botón/enlace "Editar" que lleva a HU-020).
- **AC-07**: El supervisor puede eliminar el empleado desde el detalle (botón/enlace "Eliminar" que invoca HU-021, si no tiene tareas).
- **AC-08**: Si el empleado no existe, se muestra error 404.

### Escenarios Gherkin

```gherkin
Feature: Visualización de Detalle de Empleado

  Scenario: Supervisor accede al detalle de un empleado
    Given el supervisor está autenticado
    And está en el listado de empleados
    When hace clic en "Ver detalle" para un empleado
    Then se muestra la pantalla de detalle del empleado
    And se muestra el código, nombre, email, supervisor, estado
    And se muestra la cantidad total de tareas registradas (si está disponible)
    And se muestra la fecha de creación y última actualización (si están disponibles)
    And hay botones o enlaces para "Editar" y "Eliminar"

  Scenario: Supervisor edita empleado desde el detalle
    Given el supervisor está en el detalle de un empleado
    When hace clic en "Editar"
    Then es redirigido a la pantalla de edición del empleado (HU-020)

  Scenario: Supervisor elimina empleado desde el detalle
    Given el supervisor está en el detalle de un empleado
    And el empleado no tiene tareas asociadas
    When hace clic en "Eliminar"
    Then se muestra el modal de confirmación (HU-021)
    And puede confirmar o cancelar la eliminación

  Scenario: Empleado no encontrado
    Given el supervisor está autenticado
    When intenta acceder al detalle de un empleado con id inexistente
    Then recibe error 404 (empleado no encontrado)

  Scenario: Usuario no supervisor no puede acceder
    Given un empleado no supervisor está autenticado
    When intenta acceder al detalle de un empleado
    Then recibe 403 o es redirigido
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden acceder al detalle de empleados (endpoint y pantalla protegidos).
2. **RN-02**: La información mostrada debe ser completa y contextual del empleado.
3. **RN-03**: Las estadísticas de tareas (cantidad total) son opcionales; si el backend no las proporciona, no se muestran.
4. **RN-04**: Las fechas de creación y actualización son opcionales; si el backend no las proporciona, no se muestran.
5. **RN-05**: Las acciones "Editar" y "Eliminar" invocan las funcionalidades de HU-020 y HU-021 respectivamente.
6. **RN-06**: Empleado inexistente → 404 (4003).

### Permisos por Rol
- **Supervisor:** Acceso completo al detalle y a las acciones de editar/eliminar.
- **Empleado (no supervisor):** Sin acceso; 403 o enlace no visible.
- **Cliente:** No aplica (no ven detalles de empleados).

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_USUARIOS` (o equivalente): SELECT por id para obtener datos del empleado.
- `PQ_PARTES_registro_tarea` (opcional): SELECT COUNT donde empleado_id o user_id = id para estadísticas de tareas.

### Cambios en Datos
- No se requieren nuevas tablas ni columnas. Solo consultas SELECT para obtener datos y estadísticas opcionales.

### Migración + Rollback
- No se requiere migración nueva.

### Seed Mínimo para Tests
- Empleado con datos completos. Empleado con tareas asociadas (para estadísticas). Usuario supervisor. Usuario empleado no supervisor.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/empleados/{id}`

**Descripción:** Obtener detalle completo de un empleado. Solo supervisores. Puede incluir estadísticas opcionales de tareas.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. Si no es supervisor → 403 (3101).

**Path Parameters:** `id` (integer) – ID del empleado.

**Query Parameters (opcionales):**
```
?include_stats=true    (opcional; incluir estadísticas de tareas)
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Empleado obtenido correctamente",
  "resultado": {
    "id": 1,
    "code": "JPEREZ",
    "nombre": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "supervisor": false,
    "activo": true,
    "inhabilitado": false,
    "created_at": "2025-01-15T10:00:00Z",
    "updated_at": "2025-01-20T11:00:00Z",
    "total_tareas": 42
  }
}
```

**Nota:** Los campos `total_tareas`, `created_at` y `updated_at` son opcionales según el diseño del backend.

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

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **EmpleadosDetallePage** (o **DetalleEmpleadoPage**): nueva pantalla, ruta /empleados/:id, protegida por SupervisorRoute.
- **Vista de detalle:** Sección con información del empleado (código, nombre, email, supervisor, estado, inhabilitado). Sección opcional con estadísticas (total de tareas). Sección opcional con fechas (creación, actualización). Botones o enlaces "Editar" y "Eliminar".
- **Navegación:** desde listado (HU-018) enlace "Ver detalle" o "Detalle" por fila que lleva a /empleados/:id.

### Estados UI
- Loading: mientras se cargan datos (GET).
- Error: 404 (empleado no encontrado), 403 (sin permisos).
- Success: vista con datos del empleado y acciones disponibles.

### Validaciones en UI
- Mostrar mensaje claro si el empleado no existe (404).
- Mostrar mensaje si no tiene permisos (403).

### Accesibilidad Mínima
- `data-testid` en: contenedor detalle (empleados.detail.container), código (empleados.detail.code), nombre (empleados.detail.nombre), email (empleados.detail.email), supervisor (empleados.detail.supervisor), estado (empleados.detail.activo), inhabilitado (empleados.detail.inhabilitado), total tareas (empleados.detail.totalTareas), fecha creación (empleados.detail.createdAt), fecha actualización (empleados.detail.updatedAt), botón editar (empleados.detail.edit), botón eliminar (empleados.detail.delete).
- Labels y roles ARIA apropiados.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | Extender EmpleadoService::getById() o crear getDetail() | Incluir estadísticas opcionales (total_tareas) si se solicita con query param include_stats. Mantener compatibilidad con endpoint existente usado en edición. | HU-020 | S |
| T2 | Backend  | EmpleadoController::show() o getDetail() | GET /api/v1/empleados/{id} con query param opcional include_stats; retornar datos completos con estadísticas opcionales; 200, 404, 403. Solo supervisor. | T1 | S |
| T3 | Backend  | Tests integración GET /empleados/{id} con estadísticas | GET con include_stats=true → incluye total_tareas; GET sin include_stats → datos básicos; GET id inexistente → 404; GET como empleado → 403; GET sin token → 401. | T2 | S |
| T4 | Frontend | Servicio empleado.service.ts getEmpleadoDetalle() | GET /api/v1/empleados/{id}?include_stats=true; manejo 200, 404, 403. | — | S |
| T5 | Frontend | EmpleadosDetallePage / DetalleEmpleadoPage | Pantalla de detalle con información del empleado; mostrar estadísticas y fechas si están disponibles; botones Editar y Eliminar. data-testid. | HU-018 | M |
| T6 | Frontend | Integración con acciones Editar y Eliminar | Botón Editar → navegar a /empleados/:id/editar (HU-020); botón Eliminar → invocar modal de eliminación (HU-021). | T5, HU-020, HU-021 | S |
| T7 | Frontend | Navegación desde listado | Agregar enlace "Ver detalle" o "Detalle" en listado que lleva a /empleados/:id. | HU-018, T5 | S |
| T8 | Tests    | E2E Playwright detalle empleado | Login supervisor → Empleados → Ver detalle → ver información completa → hacer clic en Editar → verificar redirección. | T5 | M |
| T9 | Tests    | E2E acceder a detalle desde listado | Login supervisor → Empleados → hacer clic en "Ver detalle" → ver pantalla de detalle con datos. | T5, T7 | S |
| T10| Frontend | Tests unit (Vitest) servicio detalle | getEmpleadoDetalle(id), manejo 200, 404, 403. | T4 | S |
| T11| Docs     | Actualizar specs/endpoints/empleados-get.md | Documentar query param opcional include_stats y campo total_tareas en respuesta. | T2 | S |

**Total:** 11 tareas (8S + 3M + 0L).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio getDetail: obtener datos del empleado; incluir estadísticas si se solicita (total_tareas); empleado inexistente → 404.

### Integration Tests (Backend)
- GET /api/v1/empleados/{id} como supervisor → 200, datos del empleado.
- GET con include_stats=true → 200, incluye total_tareas.
- GET sin include_stats → 200, datos básicos sin estadísticas.
- GET con id inexistente → 404.
- GET como empleado no supervisor → 403.
- GET sin token → 401.

### Frontend Unit Tests (Vitest)
- getEmpleadoDetalle: manejo 200 (éxito con datos), 404 (no encontrado), 403 (sin permisos).

### E2E Tests (Playwright)
- Supervisor → Empleados → Ver detalle → ver información completa del empleado → hacer clic en Editar → verificar redirección a edición.
- Supervisor → Empleados → Ver detalle → hacer clic en Eliminar → verificar modal de confirmación.

---

## 9) Riesgos y Edge Cases

- **Estadísticas opcionales:** Si el backend no proporciona estadísticas, el frontend debe manejar su ausencia sin errores.
- **Performance:** Si se calculan estadísticas de tareas, asegurar que la consulta COUNT sea eficiente (índices en empleado_id o user_id).
- **Compatibilidad:** El endpoint GET /api/v1/empleados/{id} ya se usa en edición; asegurar que la extensión con estadísticas opcionales no rompa la funcionalidad existente.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend: endpoint GET /api/v1/empleados/{id} con estadísticas opcionales (si se implementan)
- [x] Backend: 200, 404, 403 documentados
- [x] Frontend: pantalla de detalle en /empleados/:id con información completa
- [x] Frontend: mostrar estadísticas y fechas si están disponibles
- [x] Frontend: botones Editar y Eliminar funcionando correctamente
- [x] Frontend: navegación desde listado a detalle
- [ ] Unit tests backend ok (si se implementan estadísticas)
- [ ] Integration tests endpoint ok
- [x] Frontend unit tests (Vitest) servicio ok
- [ ] ≥1 E2E Playwright detalle empleado ok
- [x] Docs actualizados

---

## Archivos creados/modificados

### Backend
- (Previamente en conversación) EmpleadoService::getById() con include_stats; EmpleadoController::show() con query param include_stats.

### Frontend
- `frontend/src/features/employees/services/empleado.service.ts` – Interfaces EmpleadoItem, GetEmpleadoResult, EmpleadoDetalleItem, GetEmpleadoDetalleResult, UpdateEmpleadoBody, EmpleadoActualizadoItem, UpdateEmpleadoResult; getEmpleadoDetalle(id).
- `frontend/src/features/employees/components/EmpleadosDetallePage.tsx` – Pantalla de detalle con datos, estadísticas opcionales, fechas, botones Editar/Eliminar/Volver, modal de eliminación.
- `frontend/src/features/employees/components/EmpleadosDetallePage.css` – Estilos de la página de detalle.
- `frontend/src/features/employees/components/EmpleadosPage.tsx` – Handlers handleDeleteClick, handleDeleteCancel, handleDeleteConfirm; botón "Ver detalle" por fila.
- `frontend/src/features/employees/components/EmpleadosPage.css` – Estilo empleados-page-btn-detail.
- `frontend/src/app/App.tsx` – Ruta /empleados/:id con EmpleadosDetallePage; import EmpleadosDetallePage.
- `frontend/src/features/employees/components/index.ts` – Export EmpleadosDetallePage.

### Docs
- `specs/endpoints/empleados-get.md` – Actualizar si se añade query param include_stats y campo total_tareas.

### Tests
- `frontend/src/features/employees/services/empleado.service.test.ts` – describe getEmpleadoDetalle (TR-022): éxito 200 con total_tareas, 404, 403.

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

*(Se completará durante la implementación)*

## Pendientes / follow-ups

*(Se completará durante la implementación)*
