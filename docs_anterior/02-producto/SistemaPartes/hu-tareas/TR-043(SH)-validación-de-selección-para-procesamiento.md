# TR-043(SH) – Validación de selección para procesamiento

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-043(SH)-validación-de-selección-para-procesamiento |
| Épica              | Épica 8: Proceso Masivo de Tareas          |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-042 (procesamiento masivo)             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-07                               |
| Estado             | Pendiente                                  |

---

## 1) HU Refinada

### Título
Validación de selección para procesamiento

### Narrativa
**Como** supervisor  
**Quiero** que el sistema valide que haya tareas seleccionadas antes de procesar  
**Para** evitar errores y mejorar la UX.

### Contexto/Objetivo
Doble validación: frontend (UX) y backend (seguridad). Si no hay tareas seleccionadas: el botón "Procesar" está deshabilitado y, si igual se intenta enviar (ej. por manipulación), el backend rechaza y no realiza ninguna operación. Mensaje claro: "Debe seleccionar al menos una tarea".

### Suposiciones explícitas
- TR-041 (selección) y TR-042 (botón Procesar y endpoint) ya implementados.
- Esta TR refuerza y documenta la validación explícita en ambos lados.

### In Scope
- Si no hay tareas seleccionadas y el supervisor intenta procesar (ej. por bypass de UI): mensaje "Debe seleccionar al menos una tarea".
- El botón "Procesar" está visualmente deshabilitado cuando no hay selección (selectedTasks.length === 0).
- Mensaje de error claro y visible cuando se intenta procesar sin selección (backend devuelve 422).
- El backend no realiza ninguna operación si task_ids está vacío o ausente.

### Out of Scope
- Otras validaciones del procesamiento (ej. permisos sobre tareas concretas), cubiertas en TR-042.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Si no hay tareas seleccionadas y el supervisor intenta procesar, se muestra mensaje: "Debe seleccionar al menos una tarea".
- **AC-02**: El botón "Procesar" está visualmente deshabilitado cuando no hay selección.
- **AC-03**: El mensaje de error es claro y visible (frontend y/o respuesta backend).
- **AC-04**: El sistema no realiza ninguna operación si no hay selección (backend rechaza con 422).

### Escenarios Gherkin

```gherkin
Feature: Validación de Selección

  Scenario: Botón deshabilitado sin selección
    Given el supervisor está en Proceso Masivo con tareas cargadas
    And no hay ninguna tarea seleccionada
    Then el botón "Procesar" está deshabilitado

  Scenario: Backend rechaza sin selección
    Given el supervisor envía una petición de procesamiento con task_ids vacío
    Then el backend responde 422
    And el mensaje indica "Debe seleccionar al menos una tarea"
    And no se modifica ninguna tarea en la base de datos
```

---

## 3) Reglas de Negocio

1. **RN-01**: Validación en frontend (UX): botón deshabilitado cuando `selectedTasks.length === 0`.
2. **RN-02**: Validación en backend (seguridad): si `task_ids` está vacío o ausente, responder 422 y no ejecutar actualizaciones.
3. **RN-03**: Mensaje consistente: "Debe seleccionar al menos una tarea".

### Permisos por Rol
- **Supervisor:** Sujeto a la validación (no puede procesar sin selección).
- **Empleado:** Sin acceso a la funcionalidad (TR-039).

---

## 4) Impacto en Datos

### Tablas Afectadas
- Ninguna cuando la validación rechaza (no se ejecuta UPDATE).

### Migración + Rollback
- No aplica.

### Seed Mínimo para Tests
- Usuario supervisor; petición POST con body vacío o task_ids: [].

---

## 5) Contratos de API

### Extensión de POST `/api/v1/tasks/bulk-toggle-close` (TR-042)

- Si `task_ids` está ausente, es null o es un array vacío: **Response 422** con mensaje "Debe seleccionar al menos una tarea" (o código de error acordado). No se modifica ninguna fila.

**Request inválido (ejemplo):**
```json
{
  "task_ids": []
}
```

**Response 422:**
```json
{
  "error": 1209,
  "respuesta": "Debe seleccionar al menos una tarea",
  "resultado": {}
}
```
*(Código a definir en domain-error-codes para "selección vacía", ej. 1210; 1209 está usado para observación. Alternativa: 1000 validación general.)*

---

## 6) Cambios Frontend

### Pantallas/Componentes
- El botón "Procesar" debe estar `disabled` cuando no hay ids en el estado de selección.
- Si por algún motivo se envía la petición sin selección (ej. condición de carrera), mostrar el mensaje de error devuelto por el backend ("Debe seleccionar al menos una tarea") de forma visible.

### Estados UI
- Botón deshabilitado (sin selección); mensaje de error visible si backend responde 422 por selección vacía.

### data-testid sugeridos
- `procesoMasivo.procesar` (debe tener atributo disabled cuando corresponda), `procesoMasivo.mensajeError` (para mostrar mensaje 422).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | Validar task_ids no vacío en bulk-toggle-close; 422 con mensaje claro si vacío | No ejecutar UPDATE; 422 | TR-042 | S |
| T2 | Frontend | Asegurar disabled cuando selectedTasks.length === 0; mostrar mensaje 422 | Cumple AC | TR-042 | S |
| T3 | Tests    | Integration: POST con task_ids [] → 422; E2E botón deshabilitado sin selección | Tests pasan | T1, T2 | S |
| T4 | Docs     | Código error selección vacía en domain-error-codes si se añade | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Integration:** POST con body `{ "task_ids": [] }` → 422; POST sin task_ids → 422.
- **E2E:** Verificar que el botón "Procesar" está deshabilitado cuando no hay filas seleccionadas.

---

## 9) Riesgos y Edge Cases

- Ninguno significativo; validación sencilla en ambos lados.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: 422 cuando task_ids vacío
- [ ] Frontend: botón deshabilitado + mensaje error visible
- [ ] Tests ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Http/Requests/Api/V1/BulkToggleCloseRequest.php` – failedValidation devuelve 422 con error 1212.
- `specs/errors/domain-error-codes.md` – Código 1212 "Debe seleccionar al menos una tarea".

### Frontend
- ProcesoMasivoPage: botón Procesar disabled cuando selectedIds.size === 0; mensaje de error si backend 422.

### Tests
- `backend/tests/Feature/Api/V1/TaskControllerTest.php` – bulk_toggle_close_task_ids_vacio_retorna_422_1212.

## Comandos ejecutados

## Notas y decisiones

## Pendientes / follow-ups
