# TR-042(SH) – Procesamiento masivo de tareas (cerrar/reabrir)

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-042(SH)-procesamiento-masivo-de-tareas-cerrarreabrir |
| Épica              | Épica 8: Proceso Masivo de Tareas          |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-041 (selección múltiple)                |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-07                               |
| Estado             | Pendiente                                  |

---

## 1) HU Refinada

### Título
Procesamiento masivo de tareas (cerrar/reabrir)

### Narrativa
**Como** supervisor  
**Quiero** procesar masivamente las tareas seleccionadas para cambiar su estado (cerrar o reabrir)  
**Para** gestionar eficientemente múltiples tareas.

### Contexto/Objetivo
El supervisor selecciona tareas (TR-041) y hace clic en "Procesar". El sistema invierte el estado `cerrado` de cada tarea seleccionada: si cerrado=true → cerrado=false; si cerrado=false → cerrado=true. Opcional: diálogo de confirmación con cantidad de tareas. Indicador de carga durante el procesamiento. Mensaje de éxito "Se procesaron X registros" y actualización de la lista. Si hay error, mensaje de error y estado anterior mantenido. Procesamiento atómico (todas o ninguna, según diseño).

### Suposiciones explícitas
- Backend: nuevo endpoint ej. POST /api/v1/tasks/bulk-toggle-close con body { "task_ids": [1,2,3] }. Solo supervisor. Valida que haya al menos un id; actualiza cerrado = NOT cerrado por cada tarea.
- Transacción: todas las actualizaciones en una transacción (atómico) o procesamiento por lote con rollback en caso de error (definir).

### In Scope
- Botón "Procesar" deshabilitado si no hay tareas seleccionadas; habilitado si hay al menos una.
- Al hacer clic en "Procesar": opcional diálogo de confirmación (cantidad de tareas).
- Llamada al backend con los ids seleccionados; el backend invierte `cerrado` en cada tarea.
- Indicador de carga durante el procesamiento.
- Mensaje de éxito: "Se procesaron X registros".
- Lista se actualiza automáticamente con los nuevos estados.
- Si hay error: mensaje de error y tareas mantienen estado anterior (backend atómico o rollback).

### Out of Scope
- Otros tipos de procesamiento masivo (solo invertir cerrado en esta HU).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El botón "Procesar" está deshabilitado si no hay tareas seleccionadas.
- **AC-02**: El botón "Procesar" se habilita cuando hay al menos una tarea seleccionada.
- **AC-03**: Al hacer clic en "Procesar", el sistema invierte el estado de las tareas seleccionadas (cerrado → abierto, abierto → cerrado).
- **AC-04**: Opcional: se muestra un diálogo de confirmación antes de procesar con la cantidad de tareas.
- **AC-05**: Durante el procesamiento se muestra un indicador de carga.
- **AC-06**: El sistema procesa todas las tareas seleccionadas en una sola operación.
- **AC-07**: Al finalizar, se muestra mensaje de éxito: "Se procesaron X registros".
- **AC-08**: La lista se actualiza automáticamente con los nuevos estados.
- **AC-09**: Si hay un error, se muestra mensaje de error y las tareas mantienen su estado anterior.

### Escenarios Gherkin

```gherkin
Feature: Procesamiento Masivo Cerrar/Reabrir

  Scenario: Procesar tareas seleccionadas
    Given el supervisor tiene 3 tareas seleccionadas en Proceso Masivo
    When hace clic en "Procesar"
    And confirma en el diálogo (si aplica)
    Then se muestra indicador de carga
    And el backend invierte el estado cerrado de las 3 tareas
    And se muestra "Se procesaron 3 registros"
    And la lista se actualiza con los nuevos estados
```

---

## 3) Reglas de Negocio

1. **RN-01**: El procesamiento invierte el estado `cerrado` de cada tarea (true→false, false→true).
2. **RN-02**: El botón "Procesar" solo está habilitado si hay al menos una tarea seleccionada (validación frontend y backend).
3. **RN-03**: El procesamiento debe ser atómico (todas o ninguna; transacción en backend).
4. **RN-04**: Solo supervisores pueden ejecutar el procesamiento masivo.

### Permisos por Rol
- **Supervisor:** Puede procesar tareas seleccionadas.
- **Empleado:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: UPDATE cerrado = NOT cerrado WHERE id IN (...).

### Migración + Rollback
- No se requiere migración; la columna `cerrado` ya existe.

### Seed Mínimo para Tests
- Varias tareas (cerrado true/false); usuario supervisor; tests de integración con ids válidos y 403 para no supervisor.

---

## 5) Contratos de API

### Endpoint: POST `/api/v1/tasks/bulk-toggle-close` (o nombre equivalente)

**Descripción:** Invertir el estado `cerrado` de las tareas indicadas. Solo supervisores. Atómico.

**Autenticación:** Requerida.  
**Autorización:** Solo supervisor → 403 si no.

**Request Body:**
```json
{
  "task_ids": [1, 2, 3]
}
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Se procesaron 3 registros",
  "resultado": {
    "processed": 3,
    "task_ids": [1, 2, 3]
  }
}
```

**Response 422:** Si `task_ids` está vacío o no es un array de ids válidos (ej. "Debe seleccionar al menos una tarea").  
**Response 403:** No supervisor.

---

## 6) Cambios Frontend

### Pantallas/Componentes
- Botón "Procesar" (habilitado solo si hay selección; data-testid `procesoMasivo.procesar`).
- Opcional: diálogo de confirmación (cantidad de tareas, botón Confirmar/Cancelar).
- Llamada POST al endpoint con los ids seleccionados.
- Indicador de carga durante la petición.
- Mensaje de éxito ("Se procesaron X registros") y recarga de la lista (o actualización de estado local).
- Mensaje de error si el backend falla.

### Estados UI
- Loading durante procesamiento, Success (mensaje + lista actualizada), Error (mensaje).

### data-testid sugeridos
- `procesoMasivo.procesar`, `procesoMasivo.confirmarProcesar`, `procesoMasivo.procesando`, `procesoMasivo.mensajeExito`, `procesoMasivo.mensajeError`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | POST /api/v1/tasks/bulk-toggle-close; validar task_ids no vacío; invertir cerrado en transacción | 200 con processed; 422 si vacío; 403 | — | M |
| T2 | Frontend | Botón Procesar (disabled sin selección); llamada POST; loading; mensaje éxito/error; actualizar lista | Cumple AC | TR-041 | M |
| T3 | Frontend | Opcional: diálogo de confirmación con cantidad | Diálogo visible al clic en Procesar | T2 | S |
| T4 | Tests    | Integration: POST 200, 422 vacío, 403; E2E seleccionar y procesar | Tests pasan | T1, T2 | M |
| T5 | Docs     | Specs endpoint bulk-toggle-close | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit/Integration:** Backend: POST con ids válidos invierte cerrado; POST con array vacío 422; empleado 403.
- **E2E:** Seleccionar tareas, clic Procesar, confirmar (si hay diálogo), ver mensaje de éxito y lista actualizada.

---

## 9) Riesgos y Edge Cases

- Ids de tareas que no existen o no pertenecen al contexto: backend puede ignorar o devolver 404/422 según diseño.
- Concurrencia: dos supervisores procesando las mismas tareas; transacción y estado final consistente.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: endpoint + atómico + validación
- [ ] Frontend: Procesar + loading + mensajes + actualización lista
- [ ] Tests ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Http/Controllers/Api/V1/TaskController.php` – bulkToggleClose(); solo supervisor, 403.
- `backend/app/Http/Requests/Api/V1/BulkToggleCloseRequest.php` – Validación task_ids required, array, min:1; 422 con 1212.
- `backend/app/Services/TaskService.php` – bulkToggleClose($taskIds) en transacción.
- `backend/routes/api.php` – POST /api/v1/tasks/bulk-toggle-close.

### Frontend
- `frontend/src/features/tasks/services/task.service.ts` – bulkToggleClose(taskIds), BulkToggleCloseResult.
- `frontend/src/features/tasks/components/ProcesoMasivoPage.tsx` – Botón Procesar, confirmación, loading, mensaje éxito/error, recarga lista.

### Tests
- `backend/tests/Feature/Api/V1/TaskControllerTest.php` – bulk_toggle_close_supervisor_invierte_cerrado, bulk_toggle_close_empleado_retorna_403.

## Comandos ejecutados

## Notas y decisiones

## Pendientes / follow-ups
