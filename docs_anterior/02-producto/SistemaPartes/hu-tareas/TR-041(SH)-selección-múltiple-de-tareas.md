# TR-041(SH) – Selección múltiple de tareas

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-041(SH)-selección-múltiple-de-tareas   |
| Épica              | Épica 8: Proceso Masivo de Tareas          |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-040 (filtrado proceso masivo)          |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-07                               |
| Estado             | Pendiente                                  |

---

## 1) HU Refinada

### Título
Selección múltiple de tareas

### Narrativa
**Como** supervisor  
**Quiero** seleccionar múltiples tareas de la lista  
**Para** procesarlas en conjunto (cerrar/reabrir).

### Contexto/Objetivo
En la tabla de tareas del Proceso Masivo, cada fila tiene un checkbox. El supervisor puede seleccionar tareas individuales, "Seleccionar todos" (visibles) y "Deseleccionar todos". Contador de tareas seleccionadas en tiempo real. Las tareas cerradas también pueden seleccionarse (el procesamiento tendrá reglas en HU-042).

### Suposiciones explícitas
- La tabla de tareas ya se muestra con datos filtrados (TR-040).
- La selección es en memoria en el frontend (ids de tareas seleccionadas); no se persiste hasta "Procesar" (TR-042).

### In Scope
- Checkbox por fila para selección individual.
- Botón/enlace "Seleccionar todos" (marca todas las tareas visibles en la página actual).
- Botón/enlace "Deseleccionar todos" (desmarca todas).
- Contador visible: "X tareas seleccionadas" (actualizado en tiempo real).
- Checkboxes actualizados correctamente al seleccionar/deseleccionar.
- Tareas cerradas pueden seleccionarse (selección independiente del estado).

### Out of Scope
- Lógica de procesamiento (invertir estado) en HU-042.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Cada fila de la tabla tiene un checkbox para selección.
- **AC-02**: El supervisor puede seleccionar tareas individuales haciendo clic en los checkboxes.
- **AC-03**: El supervisor puede usar "Seleccionar todos" para marcar todas las tareas visibles.
- **AC-04**: El supervisor puede usar "Deseleccionar todos" para desmarcar todas.
- **AC-05**: Se muestra un contador de tareas seleccionadas (ej: "5 tareas seleccionadas").
- **AC-06**: Los checkboxes se actualizan correctamente al seleccionar/deseleccionar.
- **AC-07**: Las tareas cerradas pueden seleccionarse (el procesamiento puede tener reglas específicas en HU-042).

### Escenarios Gherkin

```gherkin
Feature: Selección Múltiple

  Scenario: Seleccionar y deseleccionar
    Given el supervisor está en Proceso Masivo con tareas cargadas
    When hace clic en el checkbox de la fila 1 y de la fila 3
    Then el contador muestra "2 tareas seleccionadas"
    When hace clic en "Seleccionar todos"
    Then todas las filas visibles quedan marcadas
    And el contador se actualiza
    When hace clic en "Deseleccionar todos"
    Then ninguna fila queda marcada
    And el contador muestra "0 tareas seleccionadas"
```

---

## 3) Reglas de Negocio

1. **RN-01**: La selección es independiente del estado de la tarea (cerrado/abierto).
2. **RN-02**: El contador debe actualizarse en tiempo real.
3. **RN-03**: "Seleccionar todos" aplica solo a las tareas visibles en la página actual (si hay paginación).

### Permisos por Rol
- **Supervisor:** Puede seleccionar tareas en Proceso Masivo.
- **Empleado:** Sin acceso (TR-039).

---

## 4) Impacto en Datos

### Tablas Afectadas
- Ninguna; la selección es estado en UI hasta que se ejecute "Procesar" (TR-042).

### Migración + Rollback
- No aplica.

### Seed Mínimo para Tests
- Tareas en lista (filtradas); usuario supervisor.

---

## 5) Contratos de API

- No se añaden endpoints en esta TR. La selección es solo frontend; el envío de ids se hará en TR-042 (POST o PUT para procesamiento masivo).

---

## 6) Cambios Frontend

### Pantallas/Componentes
- En la tabla de Proceso Masivo: columna con checkbox por fila (data-task-id o similar para identificar la tarea).
- Cabecera de columna o barra de acciones: "Seleccionar todos", "Deseleccionar todos".
- Área visible con contador: "X tareas seleccionadas" (actualizado al cambiar selección).
- Estado React (o equivalente): array/set de ids de tareas seleccionadas.

### Estados UI
- Contador actualizado en tiempo real; checkboxes reflejan el estado de selección.

### data-testid sugeridos
- `procesoMasivo.seleccionarTodos`, `procesoMasivo.deseleccionarTodos`, `procesoMasivo.contadorSeleccionadas`, `procesoMasivo.checkboxTarea` (por fila, ej. procesoMasivo.checkboxTarea.123).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Frontend | Checkbox por fila + estado de selección (ids) | Cumple AC | TR-040 | M |
| T2 | Frontend | "Seleccionar todos" / "Deseleccionar todos" + contador en tiempo real | Contador y botones funcionando | T1 | S |
| T3 | Tests    | E2E: seleccionar varias, seleccionar todos, deseleccionar todos, ver contador | ≥1 E2E | T2 | S |
| T4 | Docs     | Actualizar docs si aplica | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **E2E:** Interacciones con checkboxes, seleccionar todos, deseleccionar todos; verificar contador y que el botón "Procesar" (TR-042) se habilite/deshabilite según selección.

---

## 9) Riesgos y Edge Cases

- Paginación: "Seleccionar todos" solo afecta la página actual; si se cambia de página, la selección de la página anterior puede mantenerse o no (definir UX).

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Frontend: checkboxes + seleccionar/deseleccionar todos + contador
- [ ] Tests E2E ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend

### Frontend

### Tests

## Comandos ejecutados

## Notas y decisiones

## Pendientes / follow-ups
