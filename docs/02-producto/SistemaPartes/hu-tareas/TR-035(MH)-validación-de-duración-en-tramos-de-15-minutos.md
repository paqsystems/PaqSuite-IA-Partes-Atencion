# TR-035(MH) – Validación de Duración en Tramos de 15 Minutos

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-035(MH)-validación-de-duración-en-tramos-de-15-minutos |
| Épica              | Épica 7: Registro de Tareas                |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado / Empleado Supervisor             |
| Dependencias       | HU-028 (Carga de Tarea Diaria)             |
| Clasificación      | HU SIMPLE                                  |
| Estado             | ✅ PARCIALMENTE IMPLEMENTADO EN TR-028    |
| Última actualización | 2026-01-28                               |

---

## 1) HU Refinada

### Título
Validación de Duración en Tramos de 15 Minutos

### Narrativa
**Como** empleado  
**Quiero** que el sistema valide que la duración de las tareas esté en tramos de 15 minutos  
**Para** mantener la consistencia en el registro de tiempo

### Contexto/Objetivo
Esta funcionalidad asegura que todas las duraciones registradas estén en tramos de 15 minutos (15, 30, 45, 60, etc.), facilitando el análisis y reportes de tiempo.

### Suposiciones explícitas
- La validación ya está parcialmente implementada en TR-028
- El campo de duración acepta formato hh:mm y se convierte a minutos
- La validación se realiza tanto en frontend como en backend

### In Scope
- Validación en frontend: mostrar error si duración no es múltiplo de 15
- Validación en backend: rechazar request si duración no es múltiplo de 15
- Mensaje de error claro: "La duración debe estar en tramos de 15 minutos (15, 30, 45, 60, ...)"
- Código de error 1210

### Out of Scope
- Selector con valores predefinidos (opcional según UX)
- Redondeo automático al tramo más cercano (opcional según UX)

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Al ingresar duración en el formulario, el sistema valida que sea múltiplo de 15
- **AC-02**: Si se ingresa valor que no es múltiplo de 15, se muestra mensaje de error claro
- **AC-03**: El mensaje indica: "La duración debe estar en tramos de 15 minutos (15, 30, 45, 60, ...)"
- **AC-04**: El sistema no permite guardar la tarea si la duración no es válida
- **AC-05**: La validación funciona tanto en frontend (UX inmediata) como en backend (seguridad)
- **AC-06**: Código de error 1210 en backend cuando duración no es múltiplo de 15

### Escenarios Gherkin

```gherkin
Feature: Validación de Duración en Tramos de 15 Minutos

  Scenario: Duración válida (múltiplo de 15)
    Given el empleado está en el formulario de carga de tarea
    When ingresa duración "02:00" (120 minutos)
    Then no se muestra error
    And puede guardar la tarea

  Scenario: Duración inválida (no múltiplo de 15)
    Given el empleado está en el formulario de carga de tarea
    When ingresa duración "02:25" (145 minutos)
    Then se muestra mensaje de error
    And el mensaje indica "La duración debe estar en tramos de 15 minutos"
    And no puede guardar la tarea

  Scenario: Backend rechaza duración inválida
    Given el empleado envía request con duracion_minutos=145
    When el backend valida la duración
    Then retorna error 422 con código 1210
    And el mensaje indica "La duración debe estar en tramos de 15 minutos"
```

---

## 3) Reglas de Negocio

1. **RN-01**: Duración válida: `duracion_minutos % 15 === 0` y `0 < duracion_minutos <= 1440`
2. **RN-02**: Código de error 1210: "La duración debe estar en tramos de 15 minutos"
3. **RN-03**: La validación se realiza en frontend (UX) y backend (seguridad)
4. **RN-04**: El formato de entrada es hh:mm, pero la validación se hace sobre minutos

---

## 4) Impacto en Datos

### Tablas Afectadas
- Ninguna (solo validación)

### Cambios en Datos
- No se requieren cambios en BD

---

## 5) Contratos de API

### Endpoint: POST `/api/v1/tasks` y PUT `/api/v1/tasks/{id}`

**Response 422 Unprocessable Entity (duración inválida):**
```json
{
  "error": 1210,
  "respuesta": "La duración debe estar en tramos de 15 minutos (15, 30, 45, 60, ...)",
  "resultado": {
    "duracion_minutos": ["La duración debe ser múltiplo de 15"]
  }
}
```

---

## 6) Cambios Frontend

### Componentes Afectados
- **TaskForm**: Validación ya implementada en TR-028
- Verificar que el mensaje de error use i18n

### Validaciones en UI
- Validar que duración sea múltiplo de 15 antes de enviar al API
- Mostrar mensaje de error usando función `t()` de i18n

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Dependencias | Estimación |
|----|------|-------------|-----|--------------|------------|
| T1 | Backend | Verificar validación en CreateTaskRequest | Validación múltiplo de 15 con código 1210 | TR-028 | S |
| T2 | Backend | Verificar validación en UpdateTaskRequest | Misma validación para edición | TR-029 | S |
| T3 | Backend | Tests unitarios validación | 3+ tests (válido, inválido, límites) | T1 | S |
| T4 | Frontend | Verificar validación en TaskForm | Validación con mensaje i18n | TR-028 | S |
| T5 | Tests | E2E Playwright validación | Verificar mensaje de error al ingresar duración inválida | T4 | S |
| T6 | Tests | Frontend unit tests (Vitest) | Tests para durationUtils (múltiplos 15, formato hh:mm, límites 0/1440) | T4 | S |
| T7 | Docs | Documentar código error 1210 | Actualizar docs de errores | T1 | S |

**Total:** 8 tareas (8S)

**Nota:** La mayoría de la implementación ya está en TR-028. Este TR asegura cobertura completa y documentación.

---

## 8) Estrategia de Tests

### Unit Tests
- Validar que 15, 30, 45, 60, 120, 1440 son válidos
- Validar que 14, 25, 31, 145 son inválidos
- Validar que 0 y >1440 son inválidos

### Integration Tests
- POST /tasks con duración inválida retorna 422 con código 1210
- PUT /tasks/{id} con duración inválida retorna 422 con código 1210

### Frontend unit tests (Vitest)
- Tests para `durationUtils` (o equivalente): validación múltiplo de 15, conversión minutos ↔ hh:mm, límites 0 y 1440.

### E2E Tests
- Ingresar duración inválida → Verificar mensaje de error → Verificar que no se puede guardar

---

## 9) Riesgos y Edge Cases

- Conversión hh:mm a minutos: asegurar que la conversión sea correcta antes de validar
- Límites: validar que 1440 (24:00) es válido pero 1441 es inválido

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: Validación en CreateTaskRequest verificada
- [ ] Backend: Validación en UpdateTaskRequest verificada
- [ ] Backend: Código de error 1210 implementado
- [ ] Frontend: Validación con mensaje i18n verificada
- [ ] Unit tests ok
- [ ] Integration tests ok
- [ ] Frontend unit tests (Vitest) durationUtils ok
- [ ] ≥1 E2E Playwright ok
- [ ] Docs actualizadas
- [ ] IA log actualizado

---

## Archivos creados/modificados

*(Se completará durante la implementación)*

### Tests unitarios frontend (Vitest) (al implementar)
- `frontend/src/shared/utils/durationUtils.test.ts` – Tests para validación múltiplo 15, conversión hh:mm, límites 0/1440.

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

- Esta funcionalidad ya está parcialmente implementada en TR-028
- Este TR asegura cobertura completa de tests y documentación
- No requiere implementación desde cero, solo verificación y completitud

## Pendientes / follow-ups

*(Se completará durante la implementación)*
