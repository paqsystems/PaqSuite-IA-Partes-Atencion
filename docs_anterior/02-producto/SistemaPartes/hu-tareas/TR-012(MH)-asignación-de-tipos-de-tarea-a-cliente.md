# TR-012(MH) – Asignación de Tipos de Tarea a Cliente

| Campo              | Valor                                                |
|--------------------|------------------------------------------------------|
| HU relacionada     | HU-012(MH)-asignación-de-tipos-de-tarea-a-cliente     |
| Épica              | Épica 3: Gestión de Clientes (ABM)                   |
| Prioridad          | MUST-HAVE                                            |
| Roles              | Empleado Supervisor                                  |
| Dependencias       | HU-010 (edición de cliente); HU-023 (tipos de tarea) |
| Clasificación      | HU SIMPLE                                            |
| Última actualización | 2026-01-31                                         |
| Estado             | ✅ IMPLEMENTADO                                       |

---

## 1) HU Refinada

### Título
Asignación de Tipos de Tarea a Cliente

### Narrativa
**Como** supervisor  
**Quiero** asignar tipos de tarea específicos (no genéricos) a un cliente  
**Para** que ese cliente solo use esos tipos de tareas, además de los genéricos, al registrar tareas

### Contexto/Objetivo
Un tipo de tarea no genérico puede estar asignado a varios clientes. La asignación define qué tipos no genéricos puede usar ese cliente al registrar tareas; los tipos genéricos están disponibles para todos los clientes automáticamente. El supervisor accede a la gestión de tipos de tarea desde la edición o detalle del cliente (HU-010). Ve la lista de tipos no genéricos: cuáles están asignados y cuáles están disponibles. Puede asignar y desasignar; al guardar se persisten las asociaciones en PQ_PARTES_cliente_tipo_tarea. Regla: el cliente debe tener al menos un tipo genérico disponible o al menos un tipo asignado (2116 al desasignar si quedaría sin ninguno).

### Suposiciones explícitas
- El usuario ya está autenticado como supervisor (HU-001).
- Existe la edición o detalle de cliente (HU-010) desde donde se accede a la sección "Tipos de tarea".
- La tabla PQ_PARTES_cliente_tipo_tarea (o equivalente) existe o se crea con migración (cliente_id, tipo_tarea_id; UNIQUE cliente_id + tipo_tarea_id).
- Los tipos de tarea existen (HU-023); los genéricos (is_generico = true) no se asignan; solo tipos con is_generico = false.
- Tipos deben estar activos y no inhabilitados para poder asignarlos.

### In Scope
- Sección "Tipos de tarea" en edición o detalle de cliente (ruta ej. /clientes/:id/editar con pestaña o bloque Tipos de tarea).
- GET para cargar: tipos asignados al cliente y tipos disponibles no genéricos (o solo asignados si "disponibles" se obtiene de catálogo completo de tipos no genéricos).
- UI: lista de tipos no genéricos con checkboxes o multi-select para marcar asignados; botón "Guardar".
- Al guardar: actualizar asignación (PUT con lista de tipo_tarea_ids o POST/DELETE por tipo); validar que los tipos existan, estén activos y no sean genéricos; validar regla 2116 al desasignar (al menos un genérico o un asignado).
- Mensaje de confirmación; cambios reflejados inmediatamente.

### Out of Scope
- Asignación de tipos genéricos (no aplica; están disponibles para todos).
- Gestión del catálogo de tipos de tarea (HU-023, HU-024, etc.).
- Cambiar is_generico de un tipo desde esta pantalla.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la gestión de tipos de tarea de un cliente desde la edición o detalle del cliente.
- **AC-02**: Un usuario no supervisor no puede acceder (403 o sección no visible).
- **AC-03**: Se muestra una lista de tipos de tarea NO genéricos disponibles (catálogo filtrado por is_generico = false, activo, no inhabilitado).
- **AC-04**: Se indica cuáles de esos tipos están asignados al cliente (checkboxes marcados o multi-select con selección actual).
- **AC-05**: El supervisor puede seleccionar múltiples tipos de tarea para asignar al cliente.
- **AC-06**: El supervisor puede desasignar tipos de tarea ya asignados (desmarcar).
- **AC-07**: El sistema valida que los tipos de tarea existan y estén activos/no inhabilitados; no se pueden asignar tipos genéricos (2118 si se intenta).
- **AC-08**: Al guardar, el sistema crea o elimina las asociaciones en la tabla ClienteTipoTarea (PQ_PARTES_cliente_tipo_tarea) según la selección.
- **AC-09**: Si al desasignar el cliente quedaría sin ningún tipo (ni genéricos disponibles ni asignados), el sistema no permite la operación y retorna error 2116.
- **AC-10**: Se muestra un mensaje de confirmación tras guardar.
- **AC-11**: Los cambios se reflejan inmediatamente (lista actualizada o vista refrescada).

### Escenarios Gherkin

```gherkin
Feature: Asignación de Tipos de Tarea a Cliente

  Scenario: Supervisor asigna tipos de tarea a un cliente
    Given el supervisor está autenticado
    And existe un cliente con id 1
    And existen tipos de tarea no genéricos "Tipo A" y "Tipo B"
    When accede a la gestión de tipos de tarea del cliente 1
    Then ve la lista de tipos no genéricos disponibles
    And ve cuáles están asignados al cliente
    When selecciona "Tipo A" y "Tipo B" y guarda
    Then el sistema crea las asociaciones en ClienteTipoTarea
    And se muestra mensaje de confirmación
    And los tipos asignados se reflejan en la lista

  Scenario: Supervisor desasigna un tipo de tarea
    Given el cliente 1 tiene asignado "Tipo A"
    And existe al menos un tipo genérico disponible o otro tipo asignado
    When el supervisor desmarca "Tipo A" y guarda
    Then el sistema elimina la asociación
    And se muestra mensaje de confirmación

  Scenario: No se puede desasignar si quedaría sin tipos
    Given el cliente 1 solo tiene asignado "Tipo A"
    And no hay tipos genéricos disponibles en el sistema
    When el supervisor intenta desasignar "Tipo A" y guardar
    Then el sistema retorna error 2116
    And no se elimina la asociación
    And se muestra mensaje "El cliente debe tener al menos un tipo de tarea disponible"

  Scenario: No se puede asignar tipo genérico
    Given el supervisor está en la gestión de tipos del cliente 1
    When intenta asignar un tipo con is_generico = true
    Then el sistema no lo permite (no se muestra en lista o retorna 2118)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden gestionar la asignación de tipos de tarea a clientes.
2. **RN-02**: Solo se pueden asignar tipos de tarea NO genéricos (`is_generico = false`). Intentar asignar un genérico → error 2118.
3. **RN-03**: Los tipos genéricos están disponibles para todos los clientes automáticamente; no se gestionan en esta pantalla.
4. **RN-04**: Los tipos a asignar deben existir y estar activos/no inhabilitados.
5. **RN-05**: El cliente debe tener al menos un tipo genérico disponible O al menos un tipo asignado. Si al desasignar se incumple esta regla → error 2116 y no se aplica el cambio.
6. **RN-06**: Combinación cliente_id + tipo_tarea_id única en PQ_PARTES_cliente_tipo_tarea (no duplicar asignaciones).
7. **RN-07**: Cliente inexistente → 404 (4003).

### Permisos por Rol
- **Supervisor:** Acceso a la sección Tipos de tarea y a los endpoints GET/PUT (o GET/POST/DELETE) de tipos-tarea por cliente.
- **Empleado (no supervisor):** Sin acceso; 403 o sección no visible.
- **Cliente:** No aplica.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_cliente_tipo_tarea`: SELECT (tipos asignados), INSERT (asignar), DELETE (desasignar). Si no existe, crear migración con cliente_id, tipo_tarea_id, UNIQUE(cliente_id, tipo_tarea_id), FKs a cliente y tipo_tarea.
- `PQ_PARTES_cliente`: SELECT (existencia del cliente).
- `PQ_PARTES_tipo_tarea`: SELECT tipos no genéricos activos/no inhabilitados para listar disponibles y validar.

### Cambios en Datos
- Migración para PQ_PARTES_cliente_tipo_tarea si no existe (tabla pivot cliente_id, tipo_tarea_id, timestamps).

### Migración + Rollback
- Crear tabla PQ_PARTES_cliente_tipo_tarea con FKs y UNIQUE(cliente_id, tipo_tarea_id). Rollback: drop table.

### Seed Mínimo para Tests
- Cliente; tipos de tarea genéricos y no genéricos; algunas asignaciones cliente-tipo_tarea; usuario supervisor.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/clientes/{id}/tipos-tarea`

**Descripción:** Obtener tipos de tarea asignados al cliente (y opcionalmente lista de tipos no genéricos disponibles para el formulario). Solo supervisores.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. 403 (3101) si no. 404 (4003) si cliente no existe.

**Path Parameters:** `id` (integer) – ID del cliente.

**Response 200 OK (según spec actual – solo asignados):**
```json
{
  "error": 0,
  "respuesta": "Tipos de tarea obtenidos correctamente",
  "resultado": [
    {
      "id": 2,
      "code": "TIPO002",
      "descripcion": "Tipo Específico",
      "is_generico": false,
      "activo": true,
      "inhabilitado": false
    }
  ]
}
```

**Para el formulario de asignación** puede ampliarse el contrato para incluir `tipos_asignados` (ids o objetos) y `tipos_disponibles` (tipos no genéricos no asignados), o el frontend puede usar GET tipos-tarea (asignados) + catálogo de tipos no genéricos y calcular disponibles.

---

### Endpoint: PUT `/api/v1/clientes/{id}/tipos-tarea` (actualizar asignación completa)

**Descripción:** Reemplazar la asignación de tipos de tarea del cliente por la lista enviada. Solo supervisores. Validar que todos los ids sean tipos no genéricos, activos y no inhabilitados; validar regla 2116 (si la lista resultante deja al cliente sin ningún tipo cuando no hay genéricos, retornar 2116).

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. 403 (3101). 404 (4003) si cliente no existe.

**Path Parameters:** `id` (integer) – ID del cliente.

**Request Body:**
```json
{
  "tipo_tarea_ids": [2, 3, 5]
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| tipo_tarea_ids | number[] | Sí | Lista de IDs de tipos de tarea no genéricos a tener asignados. Puede ser [] si hay tipos genéricos disponibles. |

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tipos de tarea actualizados correctamente",
  "resultado": [
    { "id": 2, "code": "TIPO002", "descripcion": "Tipo Específico", "is_generico": false }
  ]
}
```

**Response 422 Unprocessable Entity:**
- 2116: El cliente debe tener al menos un tipo de tarea disponible (lista vacía y no hay genéricos).
- 2118: Algún id corresponde a tipo genérico (no se puede asignar).
- 4007: Tipo de tarea no encontrado.
- 4205: Tipo de tarea inactivo o inhabilitado.

**Response 403 Forbidden:** No supervisor (3101).

**Response 404 Not Found:** Cliente no encontrado (4003).

---

**Alternativa según specs existentes:** GET listar asignados; POST `/api/v1/clientes/{id}/tipos-tarea` body `{ "tipo_tarea_id": 2 }` para asignar uno; DELETE `/api/v1/clientes/{id}/tipos-tarea/{tipo_tarea_id}` para desasignar uno. En ese caso el frontend hace varias llamadas o se mantiene PUT para "guardar todo" según diseño del proyecto.

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **Edición o detalle de cliente (HU-010):** Sección o pestaña "Tipos de tarea".
- **Componente de asignación:** Lista de tipos no genéricos (checkboxes o multi-select); cada ítem con código/descripción; marcados los que están asignados al cliente; botón "Guardar" que envía la lista actual (PUT tipo_tarea_ids) o aplica POST/DELETE por cambio.
- Carga inicial: GET /api/v1/clientes/{id}/tipos-tarea (asignados) y catálogo de tipos no genéricos (desde endpoint de tipos de tarea o incluido en GET) para construir la lista con estado asignado/no asignado.

### Estados UI
- Loading: al cargar asignados y al guardar.
- Error: 2116 (mostrar mensaje "El cliente debe tener al menos un tipo de tarea disponible"), 2118, 403, 404.
- Success: mensaje de confirmación; lista actualizada.

### Validaciones en UI
- Al desmarcar el último tipo asignado: advertir si no hay tipos genéricos (o dejar que el backend retorne 2116 y mostrar el mensaje).
- No permitir enviar tipos genéricos (no mostrarlos en la lista de asignables).

### Accesibilidad Mínima
- `data-testid` en: sección (clientes.taskTypes.section), lista o multi-select (clientes.taskTypes.list), checkbox por tipo (clientes.taskTypes.check.{id}), botón guardar (clientes.taskTypes.save).
- Labels y roles ARIA apropiados.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Base de Datos | Migración PQ_PARTES_cliente_tipo_tarea | Tabla cliente_id, tipo_tarea_id, timestamps; UNIQUE(cliente_id, tipo_tarea_id); FKs. Rollback. | HU-023 | S |
| T2 | Backend  | GET /api/v1/clientes/{id}/tipos-tarea | Listar tipos asignados al cliente (no genéricos). Solo supervisor. 404 si cliente no existe. | HU-010 | S |
| T3 | Backend  | ClienteService o ClienteTipoTareaService::updateTiposTarea(id, tipoTareaIds) | Validar cliente existe; validar todos los ids son tipos no genéricos, activos, no inhabilitados (2118, 4007, 4205); validar regla 2116 si lista vacía y no hay genéricos; reemplazar asignaciones (eliminar no presentes, insertar nuevos). | T1 | M |
| T4 | Backend  | PUT /api/v1/clientes/{id}/tipos-tarea | Body tipo_tarea_ids. Llamar servicio; 200, 422 (2116, 2118), 403, 404. Solo supervisor. | T3 | M |
| T5 | Backend  | Tests unitarios servicio asignación | Asignar y desasignar; validar tipos no genéricos; 2116 al dejar sin tipos; 2118 si tipo genérico. | T3 | M |
| T6 | Backend  | Tests integración GET y PUT tipos-tarea | GET 200/403/404; PUT 200 con lista; PUT 422 (2116, 2118); PUT 403/404. | T2, T4 | M |
| T7 | Frontend | Servicio getTiposTareaCliente(id), updateTiposTareaCliente(id, tipoTareaIds) | GET asignados; PUT con array. Manejo 200, 422, 403, 404. | — | S |
| T8 | Frontend | Sección Tipos de tarea en edición/detalle cliente | Lista tipos no genéricos (desde catálogo + asignados); checkboxes o multi-select; botón Guardar. data-testid. | HU-010 | M |
| T9 | Frontend | Integración con API y mensaje 2116 | Cargar asignados; al guardar enviar PUT; mostrar mensaje 2116 si aplica; mensaje éxito. | T7, T8 | M |
| T10| Tests    | E2E Playwright asignar tipos a cliente | Login supervisor → Editar cliente → Tipos de tarea → asignar tipos y guardar → ver cambios. | T8 | M |
| T11| Tests    | E2E desasignar y validación 2116 (opcional) | Desasignar último tipo cuando no hay genéricos → ver mensaje 2116. | T8 | S |
| T12| Frontend | Tests unit (Vitest) servicio | getTiposTareaCliente, updateTiposTareaCliente; 200, 422. | T7 | S |
| T13| Docs     | Actualizar specs clientes-tipos-tarea | Documentar PUT si se implementa; códigos 2116, 2118. | T4 | S |
**Total:** 13 tareas (4S + 8M + 0L).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio: asignar lista de tipos no genéricos → inserciones correctas; desasignar (lista menor) → eliminaciones correctas; lista vacía y sin tipos genéricos → 2116; id de tipo genérico en lista → 2118; tipo inexistente o inactivo → 4007/4205.

### Integration Tests (Backend)
- GET /api/v1/clientes/{id}/tipos-tarea → 200 con array de tipos asignados; 404 si cliente no existe; 403 si no supervisor.
- PUT con tipo_tarea_ids válidos → 200, asignaciones actualizadas; PUT con lista vacía y sin genéricos → 422 (2116); PUT con id de tipo genérico → 422 (2118).

### Frontend Unit Tests (Vitest)
- getTiposTareaCliente(id), updateTiposTareaCliente(id, ids); manejo 200, 422 (2116).

### E2E Tests (Playwright)
- Supervisor → Editar cliente → sección Tipos de tarea → marcar/desmarcar tipos → Guardar → ver lista actualizada y mensaje de éxito.

---

## 9) Riesgos y Edge Cases

- **Regla 2116:** Al guardar con lista vacía, comprobar si existe al menos un tipo genérico activo/no inhabilitado en el sistema; si no, retornar 2116. Al desasignar uno por uno (si se usa POST/DELETE), validar 2116 en cada DELETE.
- **Tipos deshabilitados después de asignar:** Si un tipo se inhabilita después de estar asignado, definir si se sigue mostrando como asignado o se filtra; al guardar, no aceptar ids de tipos inactivos/inhabilitados.
- **Concurrencia:** Dos peticiones PUT simultáneas para el mismo cliente; usar transacción y última escritura gana.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Migración PQ_PARTES_cliente_tipo_tarea si no existía (ya existía)
- [x] Backend: GET y PUT /api/v1/clientes/{id}/tipos-tarea
- [x] Backend: validación tipos no genéricos (2118), regla 2116
- [x] Frontend: sección Tipos de tarea en edición/detalle con checkboxes y Guardar
- [x] Frontend: mensaje 2116 cuando aplica
- [x] Unit tests backend ok (ClienteControllerTest tipos-tarea)
- [x] Integration tests ok
- [x] Frontend unit tests (Vitest) ok (getTiposTareaCliente, updateTiposTareaCliente, getTiposTareaParaAsignacion)
- [ ] ≥1 E2E Playwright asignar tipos ok (pendiente si app en ejecución)
- [x] Docs actualizados

---

## Archivos creados/modificados

### Base de Datos
- Migración ya existía: `backend/database/migrations/2026_01_27_000007_create_cliente_tipo_tarea_table.php`.

### Backend
- `backend/app/Services/ClienteService.php`: getTiposTareaCliente(), updateTiposTareaCliente(), constantes ERROR_TIPO_GENERICO, ERROR_TIPO_TAREA_NOT_FOUND, ERROR_TIPO_TAREA_INACTIVO.
- `backend/app/Http/Controllers/Api/V1/ClienteController.php`: tiposTarea() GET, updateTiposTarea() PUT.
- `backend/routes/api.php`: GET/PUT `/api/v1/clientes/{id}/tipos-tarea`.

### Frontend
- `frontend/src/features/clients/services/client.service.ts`: getTiposTareaCliente(), updateTiposTareaCliente(), getTiposTareaParaAsignacion(), interfaces TipoTareaItem, GetTiposTareaClienteResult, UpdateTiposTareaClienteResult, ERROR_SIN_TIPOS_TAREA, ERROR_TIPO_GENERICO.
- `frontend/src/features/clients/components/ClientesEditarPage.tsx`: sección "Tipos de tarea asignados" con checkboxes y botón "Guardar tipos de tarea"; data-testid clientes.taskTypes.section, clientes.taskTypes.list, clientes.taskTypes.check.{id}, clientes.taskTypes.save.
- `frontend/src/features/clients/components/ClientesNuevaPage.css`: estilos .clientes-task-types-section, .clientes-task-types-list.

### Docs
- `specs/errors/domain-error-codes.md` – 2116 ya documentado; 2118 puede añadirse si no existe.

### Tests
- `backend/tests/Feature/Api/V1/ClienteControllerTest.php`: 9 tests TR-012 (GET tipos-tarea 200/403/404, PUT 200/422 2116/2118/404/403).
- `frontend/src/features/clients/services/client.service.test.ts`: getTiposTareaCliente, updateTiposTareaCliente, getTiposTareaParaAsignacion (TR-012).

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

*(Se completará durante la implementación)*

## Pendientes / follow-ups

*(Se completará durante la implementación)*
