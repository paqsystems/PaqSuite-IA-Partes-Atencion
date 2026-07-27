# TR-036(MH) – Advertencia de Fecha Futura

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-036(MH)-advertencia-de-fecha-futura     |
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
Advertencia de Fecha Futura

### Narrativa
**Como** empleado  
**Quiero** que el sistema me advierta si registro una tarea con fecha futura  
**Para** evitar errores, pero sin bloquear la acción si es intencional

### Contexto/Objetivo
Esta funcionalidad muestra una advertencia visual cuando se selecciona una fecha futura, permitiendo al usuario continuar si es intencional, pero alertándolo de posibles errores.

### Suposiciones explícitas
- La advertencia ya está parcialmente implementada en TR-028
- La fecha se ingresa en formato DMY (DD/MM/YYYY)
- La advertencia no bloquea la creación/edición

### In Scope
- Detectar cuando la fecha seleccionada es futura
- Mostrar advertencia visual clara
- Permitir continuar con la acción (no bloquear)
- Mensaje: "La fecha seleccionada es futura. ¿Está seguro de que desea continuar?"

### Out of Scope
- Bloqueo de fecha futura
- Checkbox de confirmación obligatorio
- Validación en backend (solo advertencia en frontend)

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Al seleccionar fecha futura en el formulario, se muestra advertencia visual
- **AC-02**: La advertencia indica: "La fecha seleccionada es futura. ¿Está seguro de que desea continuar?"
- **AC-03**: El usuario puede continuar con la acción (no se bloquea)
- **AC-04**: La advertencia es clara pero no impide el guardado
- **AC-05**: La advertencia desaparece si se cambia a fecha no futura

### Escenarios Gherkin

```gherkin
Feature: Advertencia de Fecha Futura

  Scenario: Advertencia al seleccionar fecha futura
    Given el empleado está en el formulario de carga de tarea
    When selecciona una fecha futura (mañana)
    Then se muestra advertencia visual
    And el mensaje indica "La fecha seleccionada es futura"
    And puede continuar y guardar la tarea

  Scenario: Advertencia desaparece con fecha no futura
    Given el empleado seleccionó fecha futura
    And se muestra la advertencia
    When cambia a fecha de hoy o pasada
    Then la advertencia desaparece
```

---

## 3) Reglas de Negocio

1. **RN-01**: La fecha futura genera advertencia pero no bloquea la creación/edición
2. **RN-02**: Esta es una validación de advertencia, no de bloqueo
3. **RN-03**: La comparación se hace con la fecha actual (sin hora)

---

## 4) Impacto en Datos

### Tablas Afectadas
- Ninguna (solo advertencia visual)

### Cambios en Datos
- No se requieren cambios en BD

---

## 5) Contratos de API

### Endpoint: POST `/api/v1/tasks` y PUT `/api/v1/tasks/{id}`

**Nota:** El backend acepta fechas futuras sin error. La advertencia es solo en frontend.

---

## 6) Cambios Frontend

### Componentes Afectados
- **TaskForm**: Advertencia ya implementada en TR-028
- Verificar que el mensaje use i18n
- Verificar que la advertencia sea visible pero no bloqueante

### Estados UI
- **Warning**: Advertencia visible cuando fecha es futura
- **Normal**: Sin advertencia cuando fecha no es futura

### Validaciones en UI
- Comparar fecha seleccionada con fecha actual
- Mostrar advertencia si fecha > fecha actual
- Usar función `t()` de i18n para mensaje

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Dependencias | Estimación |
|----|------|-------------|-----|--------------|------------|
| T1 | Frontend | Verificar advertencia en TaskForm | Advertencia visible, mensaje i18n | TR-028 | S |
| T2 | Frontend | Verificar que no bloquea guardado | Puede guardar con fecha futura | TR-028 | S |
| T3 | Tests | E2E Playwright advertencia | Verificar que se muestra advertencia con fecha futura | T1 | S |
| T4 | Tests | E2E Playwright no bloquea | Verificar que se puede guardar con fecha futura | T2 | S |
| T5 | Tests | Frontend unit tests (Vitest) | Tests para dateUtils (isFutureDate o equivalente, comparación con hoy) | T1 | S |
| T6 | Docs | Documentar comportamiento | Actualizar docs de validaciones | T1 | S |

**Total:** 7 tareas (7S)

**Nota:** La implementación ya está en TR-028. Este TR asegura cobertura completa y documentación.

---

## 8) Estrategia de Tests

### Frontend unit tests (Vitest)
- Tests para `dateUtils` (o equivalente): función que determina si una fecha es futura respecto a hoy; edge cases (hoy, mañana, zona local).

### E2E Tests
- Seleccionar fecha futura → Verificar que se muestra advertencia → Verificar que se puede guardar
- Seleccionar fecha futura → Cambiar a fecha pasada → Verificar que advertencia desaparece

---

## 9) Riesgos y Edge Cases

- Zona horaria: asegurar que la comparación use la fecha local del usuario
- Fecha límite: considerar si "hoy" debe mostrar advertencia o no (probablemente no)

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Frontend: Advertencia verificada en TaskForm
- [ ] Frontend: Mensaje i18n verificado
- [ ] Frontend: No bloquea guardado verificado
- [ ] Frontend unit tests (Vitest) dateUtils ok
- [ ] ≥1 E2E Playwright ok
- [ ] Docs actualizadas
- [ ] IA log actualizado

---

## Archivos creados/modificados

*(Se completará durante la implementación)*

### Tests unitarios frontend (Vitest) (al implementar)
- `frontend/src/shared/utils/dateUtils.test.ts` – Tests para isFutureDate (o equivalente) y comparación con hoy.

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

- Esta funcionalidad ya está parcialmente implementada en TR-028
- Este TR asegura cobertura completa de tests y documentación
- No requiere implementación desde cero, solo verificación y completitud

## Pendientes / follow-ups

*(Se completará durante la implementación)*
