# TR-034(MH) – Visualización de Lista de Todas las Tareas (Supervisor)

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-034(MH)-visualización-de-lista-de-todas-las-tareas-supervisor |
| Épica              | Épica 7: Registro de Tareas                |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-028, HU-031, HU-032, HU-033            |
| Clasificación      | HU COMPLEJA **[REVISAR_SIMPLICIDAD]**     |
| Última actualización | 2026-01-28                               |
| Estado             | 📋 PENDIENTE                               |

---

## 1) HU Refinada

### Título
Visualización de Lista de Todas las Tareas (Supervisor)

### Narrativa
**Como** supervisor  
**Quiero** ver la lista de todas las tareas de todos los usuarios  
**Para** supervisar el trabajo realizado

### Contexto/Objetivo
Los supervisores necesitan visualizar todas las tareas del sistema en una tabla paginada con filtros adicionales (por empleado). Esta funcionalidad es esencial para supervisión y gestión global de tareas.

### Suposiciones explícitas
- El usuario ya está autenticado como supervisor (`es_supervisor = true`)
- La tabla `PQ_PARTES_REGISTRO_TAREA` ya existe y tiene datos
- Existen endpoints para obtener lista de tareas con filtros
- Las acciones de edición y eliminación están implementadas (TR-031, TR-032)
- TR-033 está implementado y puede reutilizarse parcialmente

### In Scope
- Tabla paginada con todas las tareas de todos los usuarios
- Columnas: fecha, empleado, cliente, tipo de tarea, duración (minutos y horas), sin cargo, presencial, observación (truncada), cerrado, acciones
- Filtros: rango de fechas, empleado, cliente, tipo de tarea
- Búsqueda por texto en observación
- Ordenamiento por fecha, empleado, cliente (asc/desc)
- Totales: cantidad de tareas y horas del período filtrado
- Indicador visual para tareas cerradas
- Acciones editar/eliminar deshabilitadas para tareas cerradas
- Paginación

### Out of Scope
- Exportación a Excel/PDF
- Vista de calendario
- Agrupación por empleado/cliente
- Gráficos

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la sección "Todas las Tareas" o "Supervisión"
- **AC-02**: Se muestra una tabla con todas las tareas de todos los usuarios
- **AC-03**: La tabla muestra: fecha, empleado, cliente, tipo de tarea, duración (minutos y horas), sin cargo, presencial, observación (truncada), cerrado (sí/no), acciones (editar, eliminar)
- **AC-04**: Las tareas se listan paginadas (10-20 por página)
- **AC-05**: Se puede filtrar por rango de fechas (fecha desde, fecha hasta)
- **AC-06**: Se puede filtrar por empleado (selector)
- **AC-07**: Se puede filtrar por cliente (selector)
- **AC-08**: Se puede filtrar por tipo de tarea (selector)
- **AC-09**: Se puede buscar por texto en la observación
- **AC-10**: Se puede ordenar por fecha, empleado, cliente (ascendente/descendente)
- **AC-11**: Se muestra el total de tareas del período filtrado
- **AC-12**: Se muestra el total de horas del período filtrado
- **AC-13**: Las tareas cerradas se muestran claramente diferenciadas
- **AC-14**: Las acciones de editar/eliminar están deshabilitadas para tareas cerradas
- **AC-15**: Los filtros se pueden combinar

### Escenarios Gherkin

```gherkin
Feature: Visualización de Lista de Todas las Tareas (Supervisor)

  Scenario: Supervisor visualiza todas las tareas
    Given el supervisor "MGARCIA" está autenticado
    And existen tareas de múltiples usuarios
    When accede a "Todas las Tareas"
    Then se muestra tabla con todas las tareas
    And cada fila muestra: fecha, empleado, cliente, tipo, duración, sin_cargo, presencial, observación truncada, cerrado, acciones

  Scenario: Supervisor filtra por empleado
    Given el supervisor "MGARCIA" está autenticado
    And existen tareas de múltiples usuarios
    When accede a "Todas las Tareas"
    And filtra por empleado "JPEREZ"
    Then se muestran solo las tareas del empleado JPEREZ
    And se muestra el total de tareas y horas del empleado
```

---

## 3) Reglas de Negocio

1. **RN-01**: Se muestran todas las tareas de todos los usuarios
2. **RN-02**: Las tareas cerradas no se pueden editar ni eliminar
3. **RN-03**: Los filtros se aplican en el backend
4. **RN-04**: La búsqueda por texto es case-insensitive
5. **RN-05**: La paginación tiene tamaño fijo (15-20 items por página)
6. **RN-06**: Los totales se calculan sobre el conjunto filtrado

### Permisos por Rol
- **Supervisor**: Ve todas las tareas de todos los usuarios
- **Empleado**: Solo ve sus propias tareas (ver TR-033)

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_REGISTRO_TAREA`: Consulta sin filtro de usuario, con filtros adicionales
- `PQ_PARTES_USUARIOS`: Para mostrar nombre del empleado y filtro por empleado

### Cambios en Datos
- No se requieren nuevas columnas ni migraciones
- Se requieren los mismos índices que TR-033

### Seed Mínimo para Tests
```php
// En TestTasksSeeder o similar:
- Tareas de múltiples usuarios (JPEREZ, MGARCIA, OTROEMPLEADO)
- Diferentes fechas, clientes, tipos
- Algunas tareas cerradas
```

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/tasks/all`

**Descripción:** Obtener lista paginada de todas las tareas (solo supervisores).

**Autenticación:** Requerida (Bearer token)

**Autorización:** 
- Supervisor: Puede acceder
- Empleado: Retorna 403

**Query Parameters:**
```
?page=1
&per_page=15
&fecha_desde=2026-01-01
&fecha_hasta=2026-01-31
&usuario_id=1
&cliente_id=1
&tipo_tarea_id=2
&busqueda=desarrollo
&ordenar_por=fecha
&orden=desc
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tareas obtenidas correctamente",
  "resultado": {
    "data": [
      {
        "id": 1,
        "fecha": "2026-01-28",
        "empleado": {
          "id": 1,
          "nombre": "Juan Pérez",
          "code": "JPEREZ"
        },
        "cliente": {
          "id": 1,
          "nombre": "Cliente A"
        },
        "tipo_tarea": {
          "id": 2,
          "nombre": "Desarrollo"
        },
        "duracion_minutos": 120,
        "duracion_horas": "2:00",
        "sin_cargo": false,
        "presencial": true,
        "observacion": "Desarrollo de feature X...",
        "cerrado": false,
        "created_at": "2026-01-28T10:00:00+00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 100,
      "last_page": 7
    },
    "totales": {
      "cantidad_tareas": 100,
      "total_horas": 250.5
    }
  }
}
```

**Response 403 Forbidden:**
```json
{
  "error": 4030,
  "respuesta": "Solo los supervisores pueden acceder a todas las tareas",
  "resultado": {}
}
```

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **TaskListAll**: Nuevo componente para supervisores (similar a TaskList pero sin filtro de usuario)
- **TaskTable**: Reutilizar componente de TR-033, agregar columna "Empleado"
- **TaskFilters**: Reutilizar componente de TR-033, agregar filtro por empleado
- **TaskPagination**: Reutilizar componente de TR-033
- **TaskTotals**: Reutilizar componente de TR-033

### Estados UI
- Mismos estados que TR-033

### Validaciones en UI
- Validar que usuario sea supervisor antes de mostrar componente
- Mismas validaciones que TR-033

### Accesibilidad Mínima
- Mismos data-testid que TR-033 con prefijo "task.all."
- Labels y roles ARIA apropiados

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Dependencias | Estimación |
|----|------|-------------|-----|--------------|------------|
| T1 | Backend | TaskService::listAll() | Lógica sin filtro usuario, filtro por empleado | TR-033 | M |
| T2 | Backend | TaskController::indexAll() | Endpoint GET /tasks/all con validación supervisor | T1 | M |
| T3 | Backend | Tests unitarios TaskService::listAll() | 6+ tests (filtro empleado, sin filtro usuario) | T1 | M |
| T4 | Backend | Tests integración TaskController | 4+ tests (GET /tasks/all, validación supervisor) | T2 | M |
| T5 | Frontend | Servicio task.service.ts::getAllTasks() | Función para obtener todas las tareas | TR-033 | S |
| T6 | Frontend | TaskListAll componente | Componente reutilizando TaskList, agregar columna empleado | TR-033 | M |
| T7 | Frontend | TaskFilters agregar empleado | Agregar filtro por empleado en TaskFilters | TR-033 | S |
| T8 | Frontend | Ruta /tareas/todas | Nueva ruta protegida solo para supervisores | TR-033 | S |
| T9 | Frontend | Integración con TR-031, TR-032 | Botones editar/eliminar funcionan | TR-031, TR-032 | S |
| T10 | Tests | E2E Playwright lista supervisor | Verificar carga de todas las tareas | T6 | M |
| T11 | Tests | E2E Playwright filtro empleado | Verificar filtro por empleado funciona | T7 | S |
| T12 | Tests | E2E Playwright acceso empleado | Verificar que empleado no puede acceder | T8 | S |
| T13 | Tests | Frontend unit tests (Vitest) | Tests para task.service.ts getAllTasks() (params, filtro empleado, transformación) | T5 | S |
| T14 | Docs | Actualizar docs/backend/tareas.md | Documentar endpoint GET /tasks/all | T2 | S |

**Total:** 15 tareas (8S + 6M + 1L implícito)

---

## 8) Estrategia de Tests

### Unit Tests (TaskService)
- `listAll_retorna_todas_las_tareas`
- `listAll_filtra_por_empleado`
- `listAll_filtra_por_fecha`
- `listAll_ordena_por_empleado`
- `listAll_calcula_totales_correctamente`

### Integration Tests (TaskController)
- `indexAll_supervisor_retorna_todas_las_tareas`
- `indexAll_empleado_retorna_403`
- `indexAll_aplica_filtro_empleado`
- `indexAll_retorna_totales`

### Frontend unit tests (Vitest)
- Tests para `task.service.ts`: `getAllTasks(params)` con mock de fetch, filtro por empleado, transformación de respuesta.

### E2E Tests (Playwright)
- **Lista supervisor**: Login supervisor → Acceder a "Todas las Tareas" → Verificar tabla con todas las tareas
- **Filtro empleado**: Login supervisor → Filtrar por empleado → Verificar resultados
- **Acceso empleado**: Login empleado → Intentar acceder a /tareas/todas → Verificar 403 o redirección

---

## 9) Riesgos y Edge Cases

- **Performance**: Muchas tareas pueden hacer lenta la consulta (usar índices, paginación)
- **Permisos**: Validar en backend que solo supervisores puedan acceder
- **Filtros**: Múltiples filtros pueden reducir resultados a cero

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend: TaskService::listAll() implementado
- [ ] Backend: Endpoint GET /tasks/all implementado
- [ ] Backend: Validación de permisos supervisor implementada
- [ ] Frontend: TaskListAll componente implementado
- [ ] Frontend: Filtro por empleado implementado
- [ ] Frontend: Ruta protegida solo para supervisores
- [ ] Unit tests TaskService ok
- [ ] Integration tests TaskController ok
- [ ] Frontend unit tests (Vitest) task.service getAllTasks ok
- [ ] ≥1 E2E Playwright ok (sin waits ciegos)
- [ ] Docs actualizadas
- [ ] IA log actualizado

---

## Archivos creados/modificados

*(Se completará durante la implementación)*

### Tests unitarios frontend (Vitest) (al implementar)
- `frontend/src/features/tasks/services/task.service.test.ts` – Tests para getAllTasks() (params, filtro empleado, transformación).

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

*(Se completará durante la implementación)*

## Pendientes / follow-ups

*(Se completará durante la implementación)*
