# TR-018(MH) – Listado de Empleados

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-018(MH)-listado-de-empleados            |
| Épica              | Épica 5: Gestión de Empleados (ABM)        |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-001 (autenticación)                     |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-05                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Listado de Empleados

### Narrativa
**Como** supervisor  
**Quiero** ver el listado de todos los empleados  
**Para** gestionarlos

### Contexto/Objetivo
El supervisor necesita una sección "Empleados" con una tabla paginada que muestre todos los usuarios/empleados (código, nombre, email, supervisor sí/no, estado activo/inactivo, inhabilitado sí/no). Debe poder buscar por código, nombre o email, filtrar por rol supervisor, estado e inhabilitado, ver el total de usuarios y distinguir visualmente los usuarios inhabilitados. Es la pantalla base del ABM de empleados.

### Suposiciones explícitas
- El usuario ya está autenticado (HU-001) y es supervisor.
- La tabla `PQ_PARTES_usuario` existe (prefijo según convención del proyecto).
- Existe endpoint GET /api/v1/empleados según specs (specs/endpoints/empleados-list.md).
- Se listan todos los usuarios independientemente de su estado; los filtros permiten acotar.

### In Scope
- Sección "Empleados" accesible solo para supervisores (ruta /empleados protegida).
- Tabla con columnas: código, nombre, email, supervisor (sí/no), estado (activo/inactivo), inhabilitado (sí/no).
- Total de usuarios mostrado (del resultado filtrado/paginado).
- Búsqueda por código, nombre o email (campo único).
- Filtros: supervisor (sí/no), estado (activo/inactivo), inhabilitado (sí/no).
- Paginación.
- Indicador visual para usuarios inhabilitados (diferenciación clara).
- Acciones en listado: crear, editar, eliminar (enlaces/botones que llevan a otras HU del ABM de empleados).

### Out of Scope
- Creación/edición/eliminación de empleados (otras HU del ABM).
- Visualización de detalle de empleado.
- Exportación a Excel desde esta pantalla.
- Cambio de contraseña desde el listado.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la sección "Empleados" (ruta /empleados).
- **AC-02**: Un usuario no supervisor no puede acceder (403 o redirección según diseño).
- **AC-03**: Se muestra una tabla con todos los usuarios/empleados (sujeto a filtros y paginación).
- **AC-04**: La tabla muestra: código, nombre, email, supervisor (sí/no), estado (activo/inactivo), inhabilitado (sí/no).
- **AC-05**: Los usuarios se listan paginados (tamaño de página configurable, ej. 10–20).
- **AC-06**: Se puede buscar usuarios por código, nombre o email (un solo campo de búsqueda).
- **AC-07**: Se puede filtrar por rol supervisor (sí/no).
- **AC-08**: Se puede filtrar por estado (activo/inactivo).
- **AC-09**: Se puede filtrar por inhabilitado (sí/no).
- **AC-10**: Se muestra el total de usuarios (del resultado actual: filtrado y/o total de registros según diseño).
- **AC-11**: Los usuarios inhabilitados se muestran claramente diferenciados (indicador visual o estilo).
- **AC-12**: Los filtros y la búsqueda se pueden combinar; al cambiar de página se mantienen.

### Escenarios Gherkin

```gherkin
Feature: Listado de Empleados

  Scenario: Supervisor accede al listado de empleados
    Given el supervisor "MGARCIA" está autenticado
    When accede a la sección "Empleados"
    Then se muestra la tabla de empleados
    And las columnas son: código, nombre, email, supervisor, estado, inhabilitado
    And se muestra el total de usuarios
    And hay controles de búsqueda y filtros

  Scenario: Empleado no supervisor intenta acceder
    Given el empleado "JPEREZ" está autenticado
    And "JPEREZ" no es supervisor
    When intenta acceder a la sección "Empleados"
    Then recibe 403 o es redirigido
    And no ve la tabla de empleados

  Scenario: Búsqueda y filtros
    Given el supervisor está en la sección "Empleados"
    When escribe "PEREZ" en el campo de búsqueda
    And aplica filtro supervisor "No"
    And aplica filtro estado "Activo"
    Then la tabla muestra solo empleados que coinciden en código, nombre o email con "PEREZ"
    And que no son supervisores
    And que están activos
    And se actualiza el total mostrado

  Scenario: Usuarios inhabilitados diferenciados
    Given el supervisor está en la sección "Empleados"
    And existen usuarios inhabilitados
    Then los usuarios con inhabilitado = true se muestran con indicador visual
    And es posible filtrar por inhabilitado (sí/no)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo los supervisores pueden acceder a la sección "Empleados" (y al endpoint de listado).
2. **RN-02**: Se deben listar todos los usuarios según filtros aplicados; no se ocultan por defecto los inactivos o inhabilitados (el usuario puede filtrar).
3. **RN-03**: Búsqueda: se aplica a código, nombre y email (parcial, case-insensitive según specs).
4. **RN-04**: Filtros supervisor, activo, inhabilitado son opcionales; si se envían, se aplican en AND con la búsqueda.
5. **RN-05**: Paginación: page >= 1, page_size dentro de rango permitido (ej. 1–100); validaciones 1301/1302 si se documentan en specs.
6. **RN-06**: Ordenamiento: según specs (whitelist de campos; ej. code, nombre, email, created_at) y dirección asc/desc; validación 1303/1304 si aplica.

### Permisos por Rol
- **Supervisor:** Acceso completo a "Empleados": listado, búsqueda, filtros, paginación, acciones crear/editar/eliminar.
- **Empleado (no supervisor):** Sin acceso; 403 o redirección.
- **Cliente:** Sin acceso a esta sección (no aplica rol cliente aquí).

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_usuario`: SELECT con filtros (búsqueda en code, nombre, email; supervisor, activo, inhabilitado).

### Cambios en Datos
- No se requieren nuevas tablas ni columnas para el listado.
- Verificar índices para rendimiento: `code`, `nombre`, `email` (búsqueda), `supervisor`, `activo`, `inhabilitado` (filtros). Índices existentes según specs/models deben ser suficientes.

### Migración + Rollback
- No se requiere migración nueva para esta tarea si las tablas e índices ya existen.

### Seed Mínimo para Tests
- Varios usuarios con distintos roles supervisor/no supervisor, estados activo/inactivo e inhabilitado sí/no.
- Usuario supervisor y usuario empleado (no supervisor) para tests de autorización.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/empleados`

**Descripción:** Obtener listado paginado de empleados con búsqueda y filtros. Solo supervisores.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Solo supervisor. Si el usuario no es supervisor → 403 (3101).

**Query Parameters:**
```
?page=1
&page_size=20
&search=                    (opcional; código, nombre o email)
&supervisor=true            (opcional; true/false)
&activo=true                (opcional; true/false)
&inhabilitado=false         (opcional; true/false)
&sort=nombre                (opcional; whitelist: code, nombre, email, created_at, updated_at)
&sort_dir=asc               (opcional; asc|desc)
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Empleados obtenidos correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "code": "JPEREZ",
        "nombre": "Juan Pérez",
        "email": "juan@ejemplo.com",
        "supervisor": false,
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

**Response 422 Unprocessable Entity:** Validación de params (ej. page < 1, page_size fuera de rango, sort no permitido) — códigos 1301, 1302, 1303, 1304 según specs.

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **EmpleadosPage** (o **ListadoEmpleadosPage**): nueva pantalla, ruta `/empleados`, protegida por SupervisorRoute.
- **Tabla:** columnas código, nombre, email, supervisor (sí/no), estado (activo/inactivo), inhabilitado (sí/no); cabeceras ordenables si se implementa sort; filas con indicador visual para inhabilitado.
- **Filtros:** búsqueda (input texto), supervisor sí/no (select o radio), estado activo/inactivo (select o radio), inhabilitado sí/no (select o radio); botón "Aplicar" o aplicación en tiempo real según diseño.
- **Paginación:** controles de página y tamaño de página; total de usuarios visible.
- **Acciones:** botones o enlaces "Crear empleado", "Editar", "Eliminar" por fila (editar/eliminar llevan a otras HU del ABM).

### Estados UI
- Loading: mientras se cargan datos.
- Empty: sin resultados (mensaje "No se encontraron empleados" o similar).
- Error: error de red o 403.
- Success: tabla con datos, total y paginación.

### Validaciones en UI
- Búsqueda: longitud máxima según API (ej. 100 caracteres).
- Filtros: valores coherentes (supervisor/activo/inhabilitado boolean o sí/no).

### Accesibilidad Mínima
- `data-testid` en: contenedor listado (empleados.list), tabla (empleados.table), búsqueda (empleados.search), filtros (empleados.filters), paginación (empleados.pagination), total (empleados.total), botón crear (empleados.create).
- Labels y roles ARIA apropiados en formularios y tabla.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Base de Datos | Verificar índices en PQ_PARTES_usuario (code, nombre, email, supervisor, activo, inhabilitado) | Índices adecuados para búsqueda y filtros; sin migración si ya existen. | — | S |
| T2 | Backend  | EmpleadoService o UsuarioService::list() | Lógica: búsqueda (code, nombre, email), filtros supervisor, activo, inhabilitado; ordenamiento (whitelist); paginación; total. Solo ejecutar si usuario es supervisor. | HU-001 | M |
| T3 | Backend  | EmpleadoController::index() o EmpleadoController::list() | GET /api/v1/empleados con query params; validación page, page_size, sort, sort_dir; respuesta paginada + total; 403 si no supervisor. | T2 | M |
| T4 | Backend  | Tests unitarios servicio listado | Búsqueda, filtros, paginación, total; usuario no supervisor no debe obtener datos. | T2 | M |
| T5 | Backend  | Tests integración GET /empleados | 200 como supervisor con/sin filtros; 403 como empleado; 401 sin token. | T3 | M |
| T6 | Frontend | Servicio empleado.service.ts getEmpleados() | Llamada GET con params; transformar respuesta (items, pagination, total). | — | S |
| T7 | Frontend | EmpleadosPage (listado) | Contenedor: búsqueda, filtros, tabla, total, paginación. Ruta /empleados protegida por SupervisorRoute. | — | M |
| T8 | Frontend | Tabla y columnas | Columnas: código, nombre, email, supervisor, estado, inhabilitado. Indicador visual para inhabilitado. data-testid. | T7 | M |
| T9 | Frontend | Búsqueda y filtros | Campo búsqueda; filtros supervisor, estado, inhabilitado. Aplicar y mantener en paginación. data-testid. | T7 | M |
| T10| Frontend | Paginación y total | Controles de página; mostrar total de usuarios. | T7 | S |
| T11| Frontend | Acciones crear/editar/eliminar | Botón "Crear empleado" (navegación a HU de creación); enlaces editar/eliminar por fila (otras HU del ABM). | T7 | S |
| T12| Tests    | E2E Playwright listado empleados supervisor | Login supervisor → Empleados → ver tabla → filtrar/buscar → ver total y diferenciación inhabilitados. | T7 | M |
| T13| Tests    | E2E empleado no accede | Login empleado → intentar acceder a /empleados → 403 o redirección. | T7 | S |
| T14| Frontend | Tests unit (Vitest) servicio listado | getEmpleados(params), transformación, manejo 403. | T6 | S |
| T15| Docs     | Actualizar docs/specs si se añade endpoint | Especificación GET /api/v1/empleados (o referenciar specs/endpoints/empleados-list.md). | T3 | S |

**Total:** 16 tareas (6S + 8M + 2L implícitos en M).

---

## 8) Estrategia de Tests

### Unit Tests (Backend)
- Servicio listado: búsqueda en code, nombre y email; filtros supervisor, activo, inhabilitado; paginación y total; no devolver datos si no supervisor (o capa superior retorna 403).

### Integration Tests (Backend)
- GET /api/v1/empleados con token supervisor → 200, estructura items + pagination + total.
- GET con query params (search, supervisor, activo, inhabilitado, page, page_size) → resultados coherentes.
- GET con token empleado no supervisor → 403.
- GET sin token → 401.
- Validación de params (page < 1, page_size inválido) → 422 si está implementado.

### Frontend Unit Tests (Vitest)
- Servicio getEmpleados: construcción de params, transformación de respuesta, manejo de error 403.

### E2E Tests (Playwright)
- **Supervisor:** login → navegar a Empleados → ver tabla con columnas y total → aplicar búsqueda y filtros → ver resultados y total actualizado; ver indicador en usuarios inhabilitados.
- **Empleado:** login → intentar acceder a /empleados → ver 403 o redirección; no ver tabla de empleados.

---

## 9) Riesgos y Edge Cases

- **Permisos:** Comprobar siempre en backend que el usuario es supervisor; no confiar solo en ocultar la ruta en frontend.
- **Performance:** Muchos usuarios; asegurar índices y paginación; no cargar todos los registros en una sola respuesta.
- **Búsqueda vacía:** Comportamiento cuando search está vacío (no aplicar filtro LIKE o aplicar y devolver todos si no hay otros filtros).
- **Resultados vacíos:** Mostrar mensaje claro "No se encontraron empleados" en lugar de tabla vacía (estado empty).
- **Confidencialidad:** No exponer password_hash ni información sensible en la respuesta del listado.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Índices verificados (sin migración nueva si ya existen)
- [ ] Backend: servicio listado + endpoint GET /api/v1/empleados con filtros, búsqueda, paginación, total
- [ ] Backend: 403 para no supervisor
- [ ] Frontend: pantalla Empleados en /empleados protegida por supervisor
- [ ] Frontend: tabla, búsqueda, filtros, paginación, total, indicador inhabilitados
- [ ] Frontend: estados loading, empty, error
- [ ] Unit tests backend ok
- [ ] Integration tests endpoint ok
- [ ] Frontend unit tests (Vitest) servicio ok
- [ ] ≥1 E2E Playwright ok (supervisor listado + empleado 403)
- [ ] Docs/specs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/EmpleadoService.php` - Servicio con método list() para búsqueda, filtros y paginación
- `backend/app/Http/Controllers/Api/V1/EmpleadoController.php` - Controller con método index() para GET /api/v1/empleados
- `backend/routes/api.php` - Agregada ruta GET /api/v1/empleados
- `backend/tests/Feature/Api/V1/EmpleadoControllerTest.php` - Tests de integración del endpoint

### Frontend
- `frontend/src/features/employees/services/empleado.service.ts` - Servicio con función getEmpleados()
- `frontend/src/features/employees/services/index.ts` - Exportaciones del servicio
- `frontend/src/features/employees/components/EmpleadosPage.tsx` - Componente de listado con tabla, filtros y búsqueda
- `frontend/src/features/employees/components/EmpleadosPage.css` - Estilos del componente
- `frontend/src/features/employees/components/index.ts` - Exportaciones de componentes
- `frontend/src/features/employees/index.ts` - Exportaciones del módulo
- `frontend/src/app/App.tsx` - Agregada ruta /empleados protegida por SupervisorRoute
- `frontend/src/features/employees/services/empleado.service.test.ts` - Tests unitarios con Vitest del servicio

### Tests
- `frontend/tests/e2e/empleados-list.spec.ts` - Tests E2E con Playwright para listado de empleados

### Docs

## Comandos ejecutados

```bash
# Backend - Ejecutar tests de integración
cd backend
php artisan test --filter EmpleadoControllerTest

# Frontend - Ejecutar tests unitarios
cd frontend
npm run test:run -- empleado.service.test.ts

# Frontend - Ejecutar tests E2E
cd frontend
npm run test:e2e -- empleados-list.spec.ts
```

## Notas y decisiones

- Se siguió el mismo patrón de implementación que TR-008 (listado de clientes) para mantener consistencia.
- El servicio EmpleadoService usa el modelo Usuario que representa empleados en la tabla PQ_PARTES_USUARIOS.
- Los filtros implementados son: supervisor (sí/no), activo (sí/no), inhabilitado (sí/no).
- La búsqueda se aplica a código, nombre y email según la especificación.
- Los usuarios inhabilitados se muestran con clase CSS `empleados-table-row-inhabilitado` para diferenciación visual.
- Las acciones "Editar" y "Eliminar" navegan a rutas que se implementarán en otras HU (TR-020 y TR-021).
- El botón "Crear empleado" navega a /empleados/nuevo que se implementará en TR-019.

## Pendientes / follow-ups

- Implementar funcionalidad de creación de empleado (TR-019) para que el botón "Crear empleado" funcione.
- Implementar funcionalidad de edición de empleado (TR-020) para que el botón "Editar" funcione.
- Implementar funcionalidad de eliminación de empleado (TR-021) para que el botón "Eliminar" funcione.
- Agregar enlace a "Empleados" en el menú de navegación si existe (AppLayout o Dashboard).
