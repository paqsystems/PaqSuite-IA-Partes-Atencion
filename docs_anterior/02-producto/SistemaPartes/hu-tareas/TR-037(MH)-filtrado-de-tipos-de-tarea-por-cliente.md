# TR-037(MH) – Filtrado de Tipos de Tarea por Cliente

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-037(MH)-filtrado-de-tipos-de-tarea-por-cliente |
| Épica              | Épica 7: Registro de Tareas                |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado / Empleado Supervisor             |
| Dependencias       | HU-028, HU-012 (Asignación de Tipos a Clientes) |
| Clasificación      | HU SIMPLE                                  |
| Estado             | ✅ PARCIALMENTE IMPLEMENTADO EN TR-028    |
| Última actualización | 2026-01-28                               |

---

## 1) HU Refinada

### Título
Filtrado de Tipos de Tarea por Cliente

### Narrativa
**Como** empleado  
**Quiero** que el selector de tipos de tarea muestre solo los tipos disponibles para el cliente seleccionado  
**Para** evitar selecciones incorrectas

### Contexto/Objetivo
Esta funcionalidad asegura que solo se puedan seleccionar tipos de tarea válidos para el cliente seleccionado, mostrando tipos genéricos y tipos asignados específicamente al cliente.

### Suposiciones explícitas
- El filtrado dinámico ya está parcialmente implementado en TR-028
- El selector de tipos de tarea se actualiza cuando cambia el cliente seleccionado
- La validación también se realiza en backend

### In Scope
- Actualización dinámica del selector de tipos al cambiar cliente
- Mostrar tipos genéricos (`is_generico = true`) activos
- Mostrar tipos asignados al cliente seleccionado (desde `ClienteTipoTarea`) activos
- Limpiar selección de tipo si ya no está disponible para el nuevo cliente
- Mensaje informativo si no hay tipos disponibles
- Validación en backend al guardar

### Out of Scope
- Asignación de tipos a clientes (HU-012)
- Creación de tipos desde el formulario

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Al seleccionar cliente, el selector de tipos de tarea se actualiza dinámicamente
- **AC-02**: El selector muestra todos los tipos genéricos activos y no inhabilitados
- **AC-03**: El selector muestra los tipos NO genéricos asignados al cliente seleccionado
- **AC-04**: Si no hay tipos disponibles, se muestra mensaje informativo
- **AC-05**: Si se cambia cliente y el tipo seleccionado ya no está disponible, se limpia la selección
- **AC-06**: El backend valida al guardar que el tipo seleccionado sea válido para el cliente

### Escenarios Gherkin

```gherkin
Feature: Filtrado de Tipos de Tarea por Cliente

  Scenario: Selector se actualiza al cambiar cliente
    Given el empleado está en el formulario de carga de tarea
    And selecciona cliente "Cliente A"
    Then el selector de tipos muestra tipos genéricos y tipos asignados a "Cliente A"
    When cambia a cliente "Cliente B"
    Then el selector se actualiza mostrando tipos genéricos y tipos asignados a "Cliente B"

  Scenario: Limpiar selección si tipo no disponible
    Given el empleado seleccionó cliente "Cliente A"
    And seleccionó tipo "Tipo Especial A"
    When cambia a cliente "Cliente B"
    And "Tipo Especial A" no está asignado a "Cliente B"
    Then la selección de tipo se limpia
    And debe seleccionar un nuevo tipo
```

---

## 3) Reglas de Negocio

1. **RN-01**: Regla de visibilidad: genéricos + asignados al cliente
2. **RN-02**: Solo se muestran tipos activos y no inhabilitados
3. **RN-03**: Si se cambia cliente, se limpia la selección de tipo si no está disponible
4. **RN-04**: El backend valida que el tipo sea válido para el cliente antes de guardar

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_TIPOS_TAREA`: Consulta con filtro `is_generico = true`
- `PQ_PARTES_CLIENTE_TIPO_TAREA`: Consulta de tipos asignados al cliente

### Cambios en Datos
- No se requieren nuevas columnas ni migraciones

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/tasks/task-types?cliente_id={id}`

**Descripción:** Obtener tipos de tarea disponibles para un cliente.

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tipos de tarea obtenidos correctamente",
  "resultado": [
    {
      "id": 1,
      "nombre": "Desarrollo",
      "is_generico": true
    },
    {
      "id": 3,
      "nombre": "Tipo Especial",
      "is_generico": false
    }
  ]
}
```

---

## 6) Cambios Frontend

### Componentes Afectados
- **TaskTypeSelector**: Filtrado dinámico ya implementado en TR-028
- **TaskForm**: Manejo de cambio de cliente y actualización de tipos

### Estados UI
- **Loading**: Cargando tipos disponibles para el cliente
- **Empty**: No hay tipos disponibles (mostrar mensaje)
- **Success**: Tipos cargados y mostrados

### Validaciones en UI
- Limpiar selección de tipo al cambiar cliente si no está disponible
- Mostrar mensaje si no hay tipos disponibles

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Dependencias | Estimación |
|----|------|-------------|-----|--------------|------------|
| T1 | Backend | Verificar endpoint GET /tasks/task-types | Filtro por cliente_id funciona | TR-028 | S |
| T2 | Backend | Verificar validación en CreateTaskRequest | Validar tipo válido para cliente | TR-028 | S |
| T3 | Backend | Tests integración endpoint | 3+ tests (con cliente, sin cliente, tipos asignados) | T1 | S |
| T4 | Frontend | Verificar TaskTypeSelector | Actualización dinámica funciona | TR-028 | S |
| T5 | Frontend | Verificar limpieza de selección | Limpia tipo al cambiar cliente si no disponible | TR-028 | S |
| T6 | Tests | E2E Playwright filtrado | Verificar que tipos se actualizan al cambiar cliente | T4 | S |
| T7 | Tests | Frontend unit tests (Vitest) | Tests para task.service getTaskTypes(clienteId) (mock API, con/sin cliente) | T4 | S |
| T8 | Docs | Documentar comportamiento | Actualizar docs de API | T1 | S |

**Total:** 9 tareas (9S)

**Nota:** La implementación ya está en TR-028. Este TR asegura cobertura completa y documentación.

---

## 8) Estrategia de Tests

### Integration Tests
- GET /tasks/task-types sin cliente_id retorna solo genéricos
- GET /tasks/task-types con cliente_id retorna genéricos + asignados
- POST /tasks con tipo no válido para cliente retorna 422

### E2E Tests
- Seleccionar cliente → Verificar tipos disponibles → Cambiar cliente → Verificar tipos se actualizan
- Seleccionar tipo especial → Cambiar cliente → Verificar que tipo se limpia si no disponible

---

## 9) Riesgos y Edge Cases

- Cliente sin tipos asignados: mostrar solo genéricos
- Cambio rápido de cliente: manejar estado de carga correctamente
- Validación backend: asegurar que no se pueda bypassear desde frontend

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: Endpoint GET /tasks/task-types verificado
- [ ] Backend: Validación tipo válido para cliente verificada
- [ ] Frontend: TaskTypeSelector actualización dinámica verificada
- [ ] Frontend: Limpieza de selección verificada
- [ ] Integration tests ok
- [ ] Frontend unit tests (Vitest) getTaskTypes ok
- [ ] ≥1 E2E Playwright ok
- [ ] Docs actualizadas
- [ ] IA log actualizado

---

## Archivos creados/modificados

*(Se completará durante la implementación)*

### Tests unitarios frontend (Vitest) (al implementar)
- `frontend/src/features/tasks/services/task.service.test.ts` – Tests para getTaskTypes(clienteId) (mock API, con/sin cliente).

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

- Esta funcionalidad ya está parcialmente implementada en TR-028
- Este TR asegura cobertura completa de tests y documentación
- No requiere implementación desde cero, solo verificación y completitud

## Pendientes / follow-ups

*(Se completará durante la implementación)*
