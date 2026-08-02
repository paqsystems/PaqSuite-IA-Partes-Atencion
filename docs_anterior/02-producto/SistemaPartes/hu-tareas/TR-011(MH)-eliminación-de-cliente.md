# TR-011(MH) – Eliminación de Cliente

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-011(MH)-eliminación-de-cliente          |
| Épica              | Épica 3: Gestión de Clientes (ABM)         |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-010 (edición de cliente); HU-008 (listado) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-01-31                               |
| Estado             | 📋 PENDIENTE                               |

---

## 1) HU Refinada

### Título
Eliminación de Cliente

### Narrativa
**Como** supervisor  
**Quiero** eliminar un cliente que ya no se utiliza  
**Para** mantener el catálogo limpio

### Contexto/Objetivo
El supervisor puede eliminar un cliente desde el listado (HU-008) o desde el detalle/edición (HU-010). Antes de eliminar, el sistema verifica si el cliente tiene tareas asociadas (PQ_PARTES_registro_tarea o equivalente). Si tiene tareas, no se permite la eliminación (error 2112). Si no tiene tareas, se muestra un diálogo de confirmación con código y nombre del cliente; al confirmar, se elimina el cliente y se muestra mensaje de éxito; el cliente desaparece del listado.

### Suposiciones explícitas
- El usuario ya está autenticado como supervisor (HU-001).
- Existe el listado de clientes (HU-008) con opción "Eliminar" por cliente (y opcionalmente en detalle/edición).
- La tabla PQ_PARTES_registro_tarea (o equivalente) tiene FK cliente_id; no se puede eliminar un cliente con registros referenciándolo.
- Eliminación física (DELETE) o soft delete según diseño del proyecto; la HU indica "elimina el cliente de la base de datos" (eliminación física o lógica según convención del proyecto).

### In Scope
- Opción "Eliminar" accesible desde listado (y opcionalmente desde detalle/edición de cliente).
- Verificación en backend: cliente sin tareas asociadas (PQ_PARTES_registro_tarea donde cliente_id = id).
- Si tiene tareas: error 2112 (no se puede eliminar).
- Si no tiene tareas: modal de confirmación mostrando código y nombre del cliente; usuario debe confirmar.
- Al confirmar: DELETE /api/v1/clientes/{id}; eliminar cliente; mensaje de confirmación; recargar listado o redirigir al listado; cliente desaparece del listado.
- Solo supervisores pueden eliminar (403 si no).

### Out of Scope
- Eliminación en cascada de tareas (no se eliminan tareas; se impide eliminar el cliente si tiene tareas).
- Restauración de clientes eliminados (soft delete fuera de alcance de esta HU si no se especifica).
- Eliminación masiva de clientes.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la opción de eliminar un cliente desde el listado (o detalle/edición).
- **AC-02**: Un usuario no supervisor no puede eliminar (403 o opción no visible).
- **AC-03**: Antes de eliminar, el sistema verifica si el cliente tiene tareas asociadas.
- **AC-04**: Si el cliente tiene tareas asociadas, se muestra un error (2112) y no se permite la eliminación.
- **AC-05**: Si el cliente no tiene tareas asociadas, se muestra un diálogo de confirmación.
- **AC-06**: El diálogo muestra el código y nombre del cliente a eliminar.
- **AC-07**: El usuario debe confirmar la eliminación (botón "Confirmar" o "Eliminar"; cancelar cierra el modal).
- **AC-08**: Al confirmar, el sistema elimina el cliente (DELETE en backend).
- **AC-09**: Se muestra un mensaje de confirmación tras la eliminación exitosa.
- **AC-10**: El cliente desaparece del listado (recarga o redirección al listado).

### Escenarios Gherkin

```gherkin
Feature: Eliminación de Cliente

  Scenario: Supervisor elimina cliente sin tareas asociadas
    Given el supervisor está autenticado
    And existe un cliente "CLI001" con nombre "Cliente A"
    And el cliente no tiene tareas asociadas
    When hace clic en "Eliminar" para el cliente "CLI001"
    Then se muestra un diálogo de confirmación
    And el diálogo muestra el código "CLI001" y el nombre "Cliente A"
    When confirma la eliminación
    Then el sistema elimina el cliente
    And se muestra mensaje de confirmación
    And el cliente desaparece del listado

  Scenario: No se puede eliminar cliente con tareas asociadas
    Given el supervisor está autenticado
    And existe un cliente "CLI002" con tareas asociadas
    When intenta eliminar el cliente "CLI002"
    Then el sistema verifica y detecta tareas asociadas
    And no se elimina el cliente
    And se muestra error 2112 (no se puede eliminar cliente con tareas asociadas)

  Scenario: Usuario no supervisor no puede eliminar
    Given un empleado no supervisor está autenticado
    When accede al listado de clientes
    Then no ve la opción "Eliminar" o al intentar eliminar recibe 403

  Scenario: Cancelar eliminación
    Given el supervisor hizo clic en "Eliminar" para un cliente sin tareas
    When se muestra el diálogo de confirmación
    And hace clic en "Cancelar"
    Then el diálogo se cierra
    And el cliente no se elimina
    And permanece en el listado
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden eliminar clientes (endpoint y opción protegidos).
2. **RN-02**: No se puede eliminar un cliente si tiene tareas asociadas (registros en PQ_PARTES_registro_tarea con cliente_id = id). Integridad referencial.
3. **RN-03**: Código de error cuando tiene tareas: 2112 ("No se puede eliminar un cliente que tiene tareas asociadas").
4. **RN-04**: Cliente inexistente (id no existe) → 404 (4003).
5. **RN-05**: La eliminación requiere confirmación explícita del usuario (modal con código y nombre).

### Permisos por Rol
- **Supervisor:** Acceso a la opción eliminar y al endpoint DELETE /api/v1/clientes/{id}.
- **Empleado (no supervisor):** Sin acceso; 403 o opción no visible.
- **Cliente:** No aplica (no eliminan clientes).

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_registro_tarea` (o equivalente): SELECT COUNT/EXISTS donde cliente_id = id (verificación antes de eliminar).
- `PQ_PARTES_cliente`: DELETE donde id = id (o soft delete si el proyecto lo usa).
- Opcional: tabla de relación cliente-tipo_tarea (PQ_PARTES_cliente_tipo_tarea): eliminar filas del cliente o dejar que la FK en cascada lo maneje según diseño.
- Opcional: USERS: si el cliente tenía user_id, definir si se elimina/desvincula el usuario (fuera de alcance si no se especifica en la HU).

### Cambios en Datos
- No se requieren nuevas tablas ni columnas. DELETE (o update de deleted_at) en PQ_PARTES_cliente.

### Migración + Rollback
- No se requiere migración nueva.

### Seed Mínimo para Tests
- Cliente sin tareas; cliente con tareas asociadas; usuario supervisor; usuario empleado no supervisor.

---

## 5) Contratos de API

### Endpoint: DELETE `/api/v1/clientes/{id}`

**Descripción:** Eliminar un cliente. Solo supervisores. No se puede eliminar si tiene tareas asociadas.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. 403 (3101) si no. 404 (4003) si cliente no existe.

**Path Parameters:** `id` (integer) – ID del cliente a eliminar.

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Cliente eliminado correctamente",
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

**Response 404 Not Found:** Cliente no encontrado (4003).
```json
{
  "error": 4003,
  "respuesta": "Cliente no encontrado",
  "resultado": {}
}
```

**Response 422 Unprocessable Entity:** Cliente con tareas asociadas (2112).
```json
{
  "error": 2112,
  "respuesta": "No se puede eliminar un cliente que tiene tareas asociadas",
  "resultado": {}
}
```

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **Listado de clientes (HU-008):** Botón o enlace "Eliminar" por fila (y opcionalmente en pantalla detalle/edición).
- **Modal de confirmación (DeleteClienteModal o similar):** Título "Eliminar cliente"; texto con código y nombre del cliente; botones "Cancelar" y "Confirmar" (o "Eliminar"). Mostrar mensaje de error si el backend retorna 2112 (cliente con tareas).
- Tras confirmar: llamar DELETE /api/v1/clientes/{id}; si 200: cerrar modal, mensaje de éxito, recargar listado o redirigir a /clientes; si 2112: mostrar mensaje "No se puede eliminar un cliente que tiene tareas asociadas"; si 404: mensaje cliente no encontrado; si 403: mensaje sin permisos.

### Estados UI
- Loading: mientras se envía DELETE.
- Error: 2112 (mostrar en modal o toast); 403, 404.
- Success: mensaje de confirmación, cierre del modal, listado actualizado.

### Validaciones en UI
- No eliminar sin confirmación (el modal obliga a confirmar o cancelar).

### Accesibilidad Mínima
- `data-testid` en: botón eliminar (clientes.delete.button o por fila clientes.row.{id}.delete), modal (clientes.delete.modal), código/nombre en modal (clientes.delete.code, clientes.delete.nombre), botón confirmar (clientes.delete.confirm), botón cancelar (clientes.delete.cancel).
- Labels y roles ARIA apropiados (dialog, alertdialog si aplica).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | ClienteService::delete() o canDelete() + delete() | Verificar que no existan registros en PQ_PARTES_registro_tarea con cliente_id = id; si existen, lanzar excepción/retornar error 2112. Si no existen, eliminar cliente (DELETE o soft delete). | HU-010 | M |
| T2 | Backend  | ClienteController::destroy() | DELETE /api/v1/clientes/{id}. Solo supervisor. Llamar servicio; 200, 422 (2112), 403, 404. | T1 | M |
| T3 | Backend  | Tests unitarios regla "no eliminar si tiene tareas" | Cliente con tareas → delete lanza excepción o retorna 2112; cliente sin tareas → delete elimina. | T1 | M |
| T4 | Backend  | Tests integración DELETE /clientes/{id} | DELETE con tareas → 422 (2112); DELETE sin tareas → 200; DELETE id inexistente → 404; DELETE como empleado → 403; DELETE sin token → 401. | T2 | M |
| T5 | Frontend | Servicio deleteCliente(id) | DELETE /api/v1/clientes/{id}; manejo 200, 422 (2112), 403, 404. | — | S |
| T6 | Frontend | Modal de confirmación eliminar cliente | Modal con código y nombre; botones Cancelar y Confirmar; mostrar error 2112 si aplica. data-testid. | HU-008 | M |
| T7 | Frontend | Integración botón Eliminar y modal con API | Al hacer clic en Eliminar, abrir modal con datos del cliente; al confirmar, llamar deleteCliente(id); éxito: cerrar modal, mensaje, recargar listado; error 2112: mostrar mensaje en modal o toast. | T5, T6 | M |
| T8 | Tests    | E2E Playwright eliminar cliente sin tareas | Login supervisor → Clientes → Eliminar cliente sin tareas → confirmar → ver mensaje y desaparición del listado. | T6 | M |
| T9 | Tests    | E2E intentar eliminar cliente con tareas (opcional) | Si hay cliente con tareas, eliminar → ver mensaje 2112 y cliente sigue en listado. | T6 | S |
| T10| Frontend | Tests unit (Vitest) deleteCliente | deleteCliente(id); manejo 200, 422 (2112), 404. | T5 | S |
| T11| Docs     | Actualizar specs/endpoints/clientes-delete.md | Verificar códigos 2112, 4003, 3101. | T2 | S |
**Total:** 11 tareas (3S + 7M + 0L).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio delete: cliente con al menos una tarea en PQ_PARTES_registro_tarea → no eliminar, retornar/lanzar 2112; cliente sin tareas → eliminar y retornar éxito; cliente inexistente → 404.

### Integration Tests (Backend)
- DELETE /api/v1/clientes/{id} con cliente sin tareas → 200, cliente eliminado.
- DELETE con cliente que tiene tareas → 422, body error 2112, cliente no eliminado.
- DELETE con id inexistente → 404.
- DELETE como empleado no supervisor → 403.
- DELETE sin token → 401.

### Frontend Unit Tests (Vitest)
- deleteCliente(id): manejo 200 (éxito), 422 (2112), 404.

### E2E Tests (Playwright)
- Supervisor → Clientes → Eliminar (cliente sin tareas) → modal con código y nombre → Confirmar → mensaje de éxito y cliente ya no aparece en el listado.
- Opcional: Eliminar cliente con tareas → ver mensaje de error 2112.

---

## 9) Riesgos y Edge Cases

- **Integridad referencial:** Asegurar que la verificación de tareas asociadas sea atómica con la eliminación (evitar condición de carrera: verificar y luego eliminar en la misma transacción o con lock si aplica).
- **Cliente con user_id:** Si el cliente tiene user_id (acceso al sistema), definir si al eliminar el cliente se elimina o desvincula el registro USERS (puede quedar fuera de alcance de esta HU; documentar decisión).
- **Relación cliente_tipo_tarea:** Al eliminar cliente, las filas en PQ_PARTES_cliente_tipo_tarea pueden eliminarse en cascada o por aplicación; alinear con diseño de FKs.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: verificación "no eliminar si tiene tareas" (2112) y DELETE /api/v1/clientes/{id}
- [ ] Backend: 200, 422 (2112), 403, 404 documentados
- [ ] Frontend: modal de confirmación con código y nombre; botones Cancelar y Confirmar
- [ ] Frontend: integración con DELETE; mensaje 2112 cuando aplica; recarga listado tras éxito
- [ ] Unit tests backend ok
- [ ] Integration tests DELETE ok
- [ ] Frontend unit tests (Vitest) ok
- [ ] ≥1 E2E Playwright eliminar cliente sin tareas ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

*(Se completará durante la implementación)*

### Backend
- Por definir: ClienteService::delete() (o canDelete + delete), ClienteController::destroy(), ruta DELETE /api/v1/clientes/{id}.

### Frontend
- Por definir: Modal de confirmación (DeleteClienteModal o similar), botón Eliminar en listado/detalle, client.service.ts deleteCliente(), recarga listado.

### Docs
- `specs/endpoints/clientes-delete.md` – Verificar códigos 2112, 4003, 3101.

### Tests
- Por definir: unit backend, feature API, Vitest frontend, E2E Playwright.

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

*(Se completará durante la implementación)*

## Pendientes / follow-ups

*(Se completará durante la implementación)*
