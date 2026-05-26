# TR-038(MH) – Selección de Empleado Propietario (Supervisor)

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-038(MH)-selección-de-empleado-propietario-supervisor |
| Épica              | Épica 7: Registro de Tareas                |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-028, HU-019 (Gestión de Usuarios)       |
| Clasificación      | HU SIMPLE                                  |
| Estado             | ✅ PARCIALMENTE IMPLEMENTADO EN TR-028    |
| Última actualización | 2026-01-28                               |

---

## 1) HU Refinada

### Título
Selección de Empleado Propietario (Supervisor)

### Narrativa
**Como** supervisor  
**Quiero** seleccionar el empleado propietario de una tarea al crearla  
**Para** poder registrar tareas en nombre de otros empleados

### Contexto/Objetivo
Esta funcionalidad permite a los supervisores registrar tareas asignadas a otros empleados, facilitando la gestión y registro de trabajo realizado por el equipo.

### Suposiciones explícitas
- El selector de empleado ya está parcialmente implementado en TR-028
- Solo los supervisores pueden ver y usar este selector
- El selector muestra empleados activos y no inhabilitados

### In Scope
- Selector de empleado visible solo para supervisores
- Selector muestra todos los empleados activos y no inhabilitados
- Valor por defecto: supervisor mismo
- Validación que empleado seleccionado exista y esté activo
- Tarea queda asociada al empleado seleccionado

### Out of Scope
- Cambio de propietario después de crear (ver TR-031)
- Notificaciones al empleado propietario

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Si el usuario es supervisor, aparece selector de "Empleado" en el formulario
- **AC-02**: El selector muestra todos los empleados activos y no inhabilitados
- **AC-03**: Por defecto, el selector muestra al supervisor mismo
- **AC-04**: El supervisor puede cambiar la selección a otro empleado
- **AC-05**: El sistema valida que el empleado seleccionado exista y esté activo/no inhabilitado
- **AC-06**: Al guardar, la tarea queda asociada al empleado seleccionado (no al supervisor)
- **AC-07**: En la lista de tareas, se muestra el empleado propietario de cada tarea

### Escenarios Gherkin

```gherkin
Feature: Selección de Empleado Propietario (Supervisor)

  Scenario: Supervisor selecciona empleado para tarea
    Given el supervisor "MGARCIA" está autenticado
    And accede al formulario de carga de tarea
    Then aparece selector de "Empleado"
    And el valor por defecto es "MGARCIA"
    When selecciona empleado "JPEREZ"
    And completa el resto del formulario
    And guarda la tarea
    Then la tarea queda asociada a "JPEREZ"
    And no a "MGARCIA"

  Scenario: Empleado normal no ve selector
    Given el empleado "JPEREZ" está autenticado
    And accede al formulario de carga de tarea
    Then no aparece selector de "Empleado"
    And la tarea se asocia automáticamente a "JPEREZ"
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo los supervisores pueden seleccionar otro empleado
2. **RN-02**: Los empleados normales siempre registran tareas como propias
3. **RN-03**: El selector solo muestra empleados activos y no inhabilitados
4. **RN-04**: El backend valida que el empleado seleccionado exista y esté activo

### Permisos por Rol
- **Supervisor**: Puede seleccionar cualquier empleado activo
- **Empleado**: No puede seleccionar otro empleado (siempre propio)

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: Campo `usuario_id` puede ser diferente del usuario autenticado
- `PQ_PARTES_USUARIOS`: Consulta de empleados activos para el selector

### Cambios en Datos
- No se requieren nuevas columnas ni migraciones
- El campo `usuario_id` ya existe y puede ser asignado por supervisor

---

## 5) Contratos de API

### Endpoint: POST `/api/v1/tasks`

**Request Body (supervisor puede incluir usuario_id):**
```json
{
  "fecha": "2026-01-28",
  "cliente_id": 1,
  "tipo_tarea_id": 2,
  "duracion_minutos": 120,
  "sin_cargo": false,
  "presencial": true,
  "observacion": "Tarea asignada",
  "usuario_id": 5  // Opcional: solo para supervisores
}
```

**Response 403 Forbidden (empleado intenta asignar a otro):**
```json
{
  "error": 4030,
  "respuesta": "Solo los supervisores pueden asignar tareas a otros empleados",
  "resultado": {}
}
```

---

## 6) Cambios Frontend

### Componentes Afectados
- **EmployeeSelector**: Ya implementado en TR-028, visible solo para supervisores
- **TaskForm**: Manejo de selector de empleado ya implementado

### Estados UI
- **Visible**: Selector visible solo si usuario es supervisor
- **Hidden**: Selector oculto para empleados normales

### Validaciones en UI
- Validar que empleado seleccionado esté activo antes de enviar

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Dependencias | Estimación |
|----|------|-------------|-----|--------------|------------|
| T1 | Backend | Verificar validación en CreateTaskRequest | Validar permisos supervisor para usuario_id | TR-028 | S |
| T2 | Backend | Verificar TaskService::create() | Asignar tarea al usuario_id seleccionado | TR-028 | S |
| T3 | Backend | Tests unitarios TaskService | 2+ tests (supervisor asigna, empleado no puede) | T2 | S |
| T4 | Backend | Tests integración TaskController | 2+ tests (POST con usuario_id, validación permisos) | T1 | S |
| T5 | Frontend | Verificar EmployeeSelector | Visible solo para supervisores | TR-028 | S |
| T6 | Frontend | Verificar valor por defecto | Muestra supervisor mismo por defecto | TR-028 | S |
| T7 | Tests | E2E Playwright supervisor | Verificar selector visible y funcional | T5 | S |
| T8 | Tests | E2E Playwright empleado | Verificar selector no visible | T5 | S |
| T9 | Tests | Frontend unit tests (Vitest) | Tests para task.service getEmployees() (mock API, lista empleados) si aplica | T5 | S |
| T10 | Docs | Documentar comportamiento | Actualizar docs de API | T1 | S |

**Total:** 11 tareas (11S)

**Nota:** La implementación ya está en TR-028. Este TR asegura cobertura completa y documentación.

---

## 8) Estrategia de Tests

### Unit Tests
- Supervisor puede asignar tarea a otro empleado
- Empleado no puede asignar tarea a otro empleado

### Integration Tests
- POST /tasks con usuario_id por supervisor → Tarea asignada correctamente
- POST /tasks con usuario_id por empleado → Retorna 403

### E2E Tests
- Login supervisor → Ver selector de empleado → Seleccionar otro empleado → Guardar → Verificar asignación
- Login empleado → No ver selector de empleado → Guardar → Verificar asignación propia

---

## 9) Riesgos y Edge Cases

- Permisos: Validar en backend que solo supervisores puedan asignar a otros
- Empleado inactivo: Validar que no se pueda asignar a empleado inactivo
- Valor por defecto: Asegurar que siempre muestre supervisor mismo inicialmente

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: Validación permisos supervisor verificada
- [ ] Backend: Asignación de tarea a usuario_id verificada
- [ ] Frontend: EmployeeSelector visible solo para supervisores verificado
- [ ] Frontend: Valor por defecto verificado
- [ ] Unit tests ok
- [ ] Integration tests ok
- [ ] Frontend unit tests (Vitest) getEmployees ok cuando aplique
- [ ] ≥1 E2E Playwright ok
- [ ] Docs actualizadas
- [ ] IA log actualizado

---

## Archivos creados/modificados

*(Se completará durante la implementación)*

### Tests unitarios frontend (Vitest) (al implementar)
- `frontend/src/features/tasks/services/task.service.test.ts` – Tests para getEmployees() (mock API, lista empleados) si aplica.

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

- Esta funcionalidad ya está parcialmente implementada en TR-028
- Este TR asegura cobertura completa de tests y documentación
- No requiere implementación desde cero, solo verificación y completitud

## Pendientes / follow-ups

*(Se completará durante la implementación)*
