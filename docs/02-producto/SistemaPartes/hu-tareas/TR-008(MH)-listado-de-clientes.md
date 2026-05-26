# TR-008(MH) – Listado de Clientes

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-008(MH)-listado-de-clientes              |
| Épica              | Épica 3: Gestión de Clientes (ABM)         |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-001 (autenticación)                     |
| Clasificación      | HU COMPLEJA **[REVISAR_SIMPLICIDAD]**     |
| Última actualización | 2026-01-31                               |
| Estado             | 📋 PENDIENTE                                |

---

## 1) HU Refinada

### Título
Listado de Clientes

### Narrativa
**Como** supervisor  
**Quiero** ver el listado de todos los clientes  
**Para** gestionarlos (crear, editar, eliminar, asignar tipos de tarea)

### Contexto/Objetivo
El supervisor necesita una sección "Clientes" con una tabla paginada que muestre todos los clientes (código, nombre, tipo de cliente, estado activo/inactivo, inhabilitado). Debe poder buscar por código o nombre, filtrar por tipo de cliente, estado e inhabilitado, ver el total de clientes y distinguir visualmente los clientes inhabilitados. Es la pantalla base del ABM de clientes.

### Suposiciones explícitas
- El usuario ya está autenticado (HU-001) y es supervisor.
- Las tablas `PQ_PARTES_cliente` y `PQ_PARTES_tipo_cliente` existen (prefijo según convención del proyecto).
- Existe endpoint o se creará GET listado de clientes según specs (ej. GET /api/v1/clientes).
- Los tipos de cliente se usan para el filtro y para mostrar la columna "tipo de cliente".
- Se listan todos los clientes independientemente de estado; los filtros permiten acotar.

### In Scope
- Sección "Clientes" accesible solo para supervisores (ruta /clientes protegida).
- Tabla con columnas: código, nombre, tipo de cliente, estado (activo/inactivo), inhabilitado (sí/no).
- Total de clientes mostrado (del resultado filtrado/paginado).
- Búsqueda por código o nombre (campo único).
- Filtros: tipo de cliente, estado (activo/inactivo), inhabilitado (sí/no).
- Paginación.
- Indicador visual para clientes inhabilitados (opcional pero recomendado).
- Acciones en listado: crear, editar, eliminar (enlaces/botones que llevan a HU-009, HU-010, HU-011).

### Out of Scope
- Creación/edición/eliminación de clientes (HU-009, HU-010, HU-011).
- Asignación de tipos de tarea a cliente (HU-012).
- Visualización de detalle de cliente (HU-013 si existe).
- Exportación a Excel desde esta pantalla.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la sección "Clientes" (ruta /clientes).
- **AC-02**: Un usuario no supervisor no puede acceder (403 o redirección según diseño).
- **AC-03**: Se muestra una tabla con todos los clientes (sujeto a filtros y paginación).
- **AC-04**: La tabla muestra: código, nombre, tipo de cliente, estado (activo/inactivo), inhabilitado (sí/no).
- **AC-05**: Los clientes se listan paginados (tamaño de página configurable, ej. 10–20).
- **AC-06**: Se puede buscar clientes por código o nombre (un solo campo de búsqueda).
- **AC-07**: Se puede filtrar por tipo de cliente (selector).
- **AC-08**: Se puede filtrar por estado (activo/inactivo).
- **AC-09**: Se puede filtrar por inhabilitado (sí/no).
- **AC-10**: Se muestra el total de clientes (del resultado actual: filtrado y/o total de registros según diseño).
- **AC-11**: Los clientes inhabilitados se muestran claramente diferenciados (indicador visual o estilo).
- **AC-12**: Los filtros y la búsqueda se pueden combinar; al cambiar de página se mantienen.

### Escenarios Gherkin

```gherkin
Feature: Listado de Clientes

  Scenario: Supervisor accede al listado de clientes
    Given el supervisor "MGARCIA" está autenticado
    When accede a la sección "Clientes"
    Then se muestra la tabla de clientes
    And las columnas son: código, nombre, tipo de cliente, estado, inhabilitado
    And se muestra el total de clientes
    And hay controles de búsqueda y filtros

  Scenario: Empleado no supervisor intenta acceder
    Given el empleado "JPEREZ" está autenticado
    And "JPEREZ" no es supervisor
    When intenta acceder a la sección "Clientes"
    Then recibe 403 o es redirigido
    And no ve la tabla de clientes

  Scenario: Búsqueda y filtros
    Given el supervisor está en la sección "Clientes"
    When escribe "CORP" en el campo de búsqueda
    And aplica filtro tipo de cliente "Corporativo"
    Then la tabla muestra solo clientes que coinciden en código o nombre con "CORP"
    And que son del tipo Corporativo
    And se actualiza el total mostrado

  Scenario: Clientes inhabilitados diferenciados
    Given el supervisor está en la sección "Clientes"
    And existen clientes inhabilitados
    Then los clientes con inhabilitado = true se muestran con indicador visual
    And es posible filtrar por inhabilitado (sí/no)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo los supervisores pueden acceder a la sección "Clientes" (y al endpoint de listado).
2. **RN-02**: Se deben listar todos los clientes según filtros aplicados; no se ocultan por defecto los inactivos o inhabilitados (el usuario puede filtrar).
3. **RN-03**: Búsqueda: se aplica a código y nombre (parcial, case-insensitive según specs).
4. **RN-04**: Filtros tipo_cliente_id, activo, inhabilitado son opcionales; si se envían, se aplican en AND con la búsqueda.
5. **RN-05**: Paginación: page >= 1, page_size dentro de rango permitido (ej. 1–100); validaciones 1301/1302 si se documentan en specs.
6. **RN-06**: Ordenamiento: según specs (whitelist de campos; ej. nombre, code, created_at) y dirección asc/desc; validación 1303/1304 si aplica.

### Permisos por Rol
- **Supervisor:** Acceso completo a "Clientes": listado, búsqueda, filtros, paginación, acciones crear/editar/eliminar.
- **Empleado (no supervisor):** Sin acceso; 403 o redirección.
- **Cliente:** Sin acceso a esta sección (no aplica rol cliente aquí).

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_cliente`: SELECT con filtros (búsqueda en code, nombre; tipo_cliente_id, activo, inhabilitado).
- `PQ_PARTES_tipo_cliente`: JOIN para mostrar tipo de cliente y para filtro de tipos.

### Cambios en Datos
- No se requieren nuevas tablas ni columnas para el listado.
- Verificar índices para rendimiento: `code`, `nombre` (búsqueda), `tipo_cliente_id`, `activo`, `inhabilitado` (filtros). Índices existentes según specs/models deben ser suficientes.

### Migración + Rollback
- No se requiere migración nueva para esta tarea si las tablas e índices ya existen.

### Seed Mínimo para Tests
- Varios clientes con distintos tipos de cliente, estados activo/inactivo e inhabilitado sí/no.
- Usuario supervisor y usuario empleado (no supervisor) para tests de autorización.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/clientes`

**Descripción:** Obtener listado paginado de clientes con búsqueda y filtros. Solo supervisores.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. Si el usuario no es supervisor → 403 (3101).

**Query Parameters:**
```
?page=1
&page_size=20
&search=                    (opcional; código o nombre)
&tipo_cliente_id=1          (opcional)
&activo=true                (opcional; true/false)
&inhabilitado=false         (opcional; true/false)
&sort=nombre                (opcional; whitelist: code, nombre, created_at, updated_at)
&sort_dir=asc               (opcional; asc|desc)
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Clientes obtenidos correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "code": "CLI001",
        "nombre": "Cliente A",
        "tipo_cliente": { "id": 1, "code": "CORP", "descripcion": "Corporativo" },
        "email": "cliente@ejemplo.com",
        "activo": true,
        "inhabilitado": false,
        "created_at": "2025-01-15T10:00:00Z",
        "updated_at": "2025-01-15T10:00:00Z"
      }
    ],
    "page": 1,
    "page_size": 20,
    "total": 1,
    "total_pages": 1
  }
}
```

**Response 401 Unauthorized:** Usuario no autenticado (3001).

**Response 403 Forbidden:** Usuario no supervisor (3101).
```json
{
  "error": 3101,
  "respuesta": "No tiene permiso para acceder a esta funcionalidad",
  "resultado": {}
}
```

**Response 422 Unprocessable Entity:** Validación de params (ej. page &lt; 1, page_size fuera de rango, sort no permitido) — códigos 1301, 1302, 1303, 1304 según specs.

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **ClientesPage** (o **ListadoClientesPage**): nueva pantalla, ruta `/clientes`, protegida por SupervisorRoute.
- **Tabla:** columnas código, nombre, tipo de cliente, estado (activo/inactivo), inhabilitado (sí/no); cabeceras ordenables si se implementa sort; filas con indicador visual para inhabilitado.
- **Filtros:** búsqueda (input texto), tipo de cliente (select), estado activo/inactivo (select o radio), inhabilitado sí/no (select o radio); botón "Aplicar" o aplicación en tiempo real según diseño.
- **Paginación:** controles de página y tamaño de página; total de clientes visible.
- **Acciones:** botones o enlaces "Crear cliente", "Editar", "Eliminar" por fila (editar/eliminar llevan a HU-010 / HU-011).

### Estados UI
- Loading: mientras se cargan datos.
- Empty: sin resultados (mensaje "No se encontraron clientes" o similar).
- Error: error de red o 403.
- Success: tabla con datos, total y paginación.

### Validaciones en UI
- Búsqueda: longitud máxima según API (ej. 100 caracteres).
- Filtros: valores coherentes (activo/inhabilitado boolean o sí/no).

### Accesibilidad Mínima
- `data-testid` en: contenedor listado (clientes.list), tabla (clientes.table), búsqueda (clientes.search), filtros (clientes.filters), paginación (clientes.pagination), total (clientes.total), botón crear (clientes.create).
- Labels y roles ARIA apropiados en formularios y tabla.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Base de Datos | Verificar índices en PQ_PARTES_cliente (code, nombre, tipo_cliente_id, activo, inhabilitado) | Índices adecuados para búsqueda y filtros; sin migración si ya existen. | — | S |
| T2 | Backend  | ClientService o ClienteService::list() | Lógica: búsqueda (code, nombre), filtros tipo_cliente_id, activo, inhabilitado; ordenamiento (whitelist); paginación; total. Solo ejecutar si usuario es supervisor. | HU-001 | M |
| T3 | Backend  | ClienteController::index() o ClientController::list() | GET /api/v1/clientes con query params; validación page, page_size, sort, sort_dir; respuesta paginada + total; 403 si no supervisor. | T2 | M |
| T4 | Backend  | Tests unitarios servicio listado | Búsqueda, filtros, paginación, total; usuario no supervisor no debe obtener datos. | T2 | M |
| T5 | Backend  | Tests integración GET /clientes | 200 como supervisor con/sin filtros; 403 como empleado; 401 sin token. | T3 | M |
| T6 | Frontend | Servicio client.service.ts getClientes() | Llamada GET con params; transformar respuesta (items, pagination, total). | — | S |
| T7 | Frontend | ClientesPage (listado) | Contenedor: búsqueda, filtros, tabla, total, paginación. Ruta /clientes protegida por SupervisorRoute. | — | M |
| T8 | Frontend | Tabla y columnas | Columnas: código, nombre, tipo cliente, estado, inhabilitado. Indicador visual para inhabilitado. data-testid. | T7 | M |
| T9 | Frontend | Búsqueda y filtros | Campo búsqueda; filtros tipo cliente, estado, inhabilitado. Aplicar y mantener en paginación. data-testid. | T7 | M |
| T10| Frontend | Paginación y total | Controles de página; mostrar total de clientes. | T7 | S |
| T11| Frontend | Acciones crear/editar/eliminar | Botón "Crear cliente" (navegación a HU-009); enlaces editar/eliminar por fila (HU-010, HU-011). | T7 | S |
| T12| Tests    | E2E Playwright listado clientes supervisor | Login supervisor → Clientes → ver tabla → filtrar/buscar → ver total y diferenciación inhabilitados. | T7 | M |
| T13| Tests    | E2E empleado no accede | Login empleado → intentar acceder a /clientes → 403 o redirección. | T7 | S |
| T14| Frontend | Tests unit (Vitest) servicio listado | getClientes(params), transformación, manejo 403. | T6 | S |
| T15| Docs     | Actualizar docs/specs si se añade endpoint | Especificación GET /api/v1/clientes (o referenciar specs/endpoints/clientes-list.md). | T3 | S |
**Total:** 15 tareas (6S + 8M + 2L implícitos en M).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio listado: búsqueda en code y nombre; filtros tipo_cliente_id, activo, inhabilitado; paginación y total; no devolver datos si no supervisor (o capa superior retorna 403).

### Integration Tests (Backend)
- GET /api/v1/clientes con token supervisor → 200, estructura items + pagination + total.
- GET con query params (search, tipo_cliente_id, activo, inhabilitado, page, page_size) → resultados coherentes.
- GET con token empleado no supervisor → 403.
- GET sin token → 401.
- Validación de params (page &lt; 1, page_size inválido) → 422 si está implementado.

### Frontend Unit Tests (Vitest)
- Servicio getClientes: construcción de params, transformación de respuesta, manejo de error 403.

### E2E Tests (Playwright)
- **Supervisor:** login → navegar a Clientes → ver tabla con columnas y total → aplicar búsqueda y filtros → ver resultados y total actualizado; ver indicador en clientes inhabilitados.
- **Empleado:** login → intentar acceder a /clientes → ver 403 o redirección; no ver tabla de clientes.

---

## 9) Riesgos y Edge Cases

- **Permisos:** Comprobar siempre en backend que el usuario es supervisor; no confiar solo en ocultar la ruta en frontend.
- **Performance:** Muchos clientes; asegurar índices y paginación; no cargar todos los registros en una sola respuesta.
- **Búsqueda vacía:** Comportamiento cuando search está vacío (no aplicar filtro LIKE o aplicar y devolver todos si no hay otros filtros).
- **Resultados vacíos:** Mostrar mensaje claro "No se encontraron clientes" en lugar de tabla vacía (estado empty).

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Índices verificados (sin migración nueva si ya existen)
- [ ] Backend: servicio listado + endpoint GET /api/v1/clientes con filtros, búsqueda, paginación, total
- [ ] Backend: 403 para no supervisor
- [ ] Frontend: pantalla Clientes en /clientes protegida por supervisor
- [ ] Frontend: tabla, búsqueda, filtros, paginación, total, indicador inhabilitados
- [ ] Frontend: estados loading, empty, error
- [ ] Unit tests backend ok
- [ ] Integration tests endpoint ok
- [ ] Frontend unit tests (Vitest) servicio ok
- [ ] ≥1 E2E Playwright ok (supervisor listado + empleado 403)
- [ ] Docs/specs actualizados

---

## Archivos creados/modificados

*(Se completará durante la implementación)*

### Backend
- Por definir: Service, Controller, rutas.

### Frontend
- Por definir: ClientesPage, servicio client.service.ts, rutas, SupervisorRoute.

### Docs
- Referencia o actualización de `specs/endpoints/clientes-list.md` si aplica.

### Tests
- Por definir: unit backend, feature API, Vitest frontend, E2E Playwright.

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

*(Se completará durante la implementación)*

## Pendientes / follow-ups

*(Se completará durante la implementación)*
