# TR-023(MH) – Listado de tipos de tarea

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-023(MH)-listado-de-tipos-de-tarea      |
| Épica              | Épica 6: Gestión de Tipos de Tarea (ABM)  |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-001 (autenticación)                     |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-06                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Listado de tipos de tarea

### Narrativa
**Como** supervisor  
**Quiero** ver el listado de todos los tipos de tarea  
**Para** gestionarlos (crear, editar, eliminar).

### Contexto/Objetivo
Sección "Tipos de Tarea" con tabla de todos los tipos: código, descripción, genérico (sí/no), por defecto (sí/no), estado (activo/inactivo), inhabilitado. Paginación, búsqueda por código o descripción, filtros por genérico, por defecto, estado e inhabilitado. Total mostrado. Tipos inhabilitados y tipo por defecto diferenciados visualmente. Base del ABM de tipos de tarea.

### Suposiciones explícitas
- Usuario autenticado y supervisor (HU-001).
- Tabla `PQ_PARTES_TIPOS_TAREA` existe (code, descripcion, is_generico, is_default, activo, inhabilitado, timestamps).
- Endpoint GET listado de tipos de tarea: crear GET /api/v1/tipos-tarea con query params; sin `page` puede devolver array para selector; con `page` listado paginado ABM (convención análoga a tipos-cliente).

### In Scope
- Sección "Tipos de Tarea" accesible solo para supervisores (ruta ej. `/tipos-tarea`).
- Tabla: código, descripción, genérico (sí/no), por defecto (sí/no), estado (activo/inactivo), inhabilitado (sí/no).
- Paginación.
- Búsqueda por código o descripción.
- Filtros: genérico, por defecto, estado activo/inactivo, inhabilitado.
- Total de tipos mostrado.
- Diferenciación visual: tipos inhabilitados; tipo por defecto destacado.
- Acciones: Crear, Editar, Eliminar (enlaces a HU-024, HU-025, HU-026).

### Out of Scope
- Creación/edición/eliminación (HU-024, HU-025, HU-026).
- Detalle (HU-027).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la sección "Tipos de Tarea".
- **AC-02**: Se muestra una tabla con todos los tipos de tarea (sujeto a paginación/filtros).
- **AC-03**: La tabla muestra: código, descripción, genérico (sí/no), por defecto (sí/no), estado (activo/inactivo), inhabilitado (sí/no).
- **AC-04**: Los tipos se listan paginados (si hay muchos).
- **AC-05**: Se puede buscar por código o descripción.
- **AC-06**: Se puede filtrar por genérico (sí/no).
- **AC-07**: Se puede filtrar por por defecto (sí/no).
- **AC-08**: Se puede filtrar por estado (activo/inactivo).
- **AC-09**: Se puede filtrar por inhabilitado (sí/no).
- **AC-10**: Se muestra el total de tipos de tarea.
- **AC-11**: Los tipos inhabilitados se muestran claramente diferenciados.
- **AC-12**: El tipo por defecto se muestra claramente destacado.
- **AC-13**: Usuario no supervisor no puede acceder (403 o redirección).

### Escenarios Gherkin

```gherkin
Feature: Listado de Tipos de Tarea

  Scenario: Supervisor accede al listado
    Given el supervisor está autenticado
    When accede a la sección "Tipos de Tarea"
    Then se muestra la tabla con código, descripción, genérico, por defecto, estado, inhabilitado
    And se muestra el total de tipos
    And hay búsqueda y filtros

  Scenario: Búsqueda y filtros
    Given el supervisor está en Tipos de Tarea
    When escribe "DES" en búsqueda
    And filtra por genérico = true
    Then la tabla muestra solo tipos que coinciden
    And el total se actualiza
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden acceder al listado de tipos de tarea.
2. **RN-02**: Búsqueda aplicada a código y descripción (parcial, case-insensitive según specs).
3. **RN-03**: Filtros genérico, por defecto, activo e inhabilitado opcionales; combinables con búsqueda.
4. **RN-04**: Paginación: page, page_size con rangos válidos.

### Permisos por Rol
- **Supervisor:** Acceso completo al listado y acciones.
- **Empleado (no supervisor):** 403 o redirección.
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_TIPOS_TAREA`: SELECT con filtros (code, descripcion, is_generico, is_default, activo, inhabilitado). Sin nuevas columnas.

### Migración + Rollback
- No se requiere migración; tabla ya existe.

### Seed Mínimo para Tests
- Varios tipos de tarea (genérico/no, por defecto sí/no, activo/inactivo, inhabilitado). Usuario supervisor y no supervisor.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/tipos-tarea` (listado ABM)

**Descripción:** Listado paginado de tipos de tarea con búsqueda y filtros. Solo supervisores. Sin query `page` puede devolver array simple (selector); con `page` devuelve resultado paginado (items, total, page, page_size).

**Autenticación:** Requerida (Bearer).  
**Autorización:** Solo supervisor → 403 (3101) si no.

**Query Parameters:**
```
?page=1
&page_size=20
&search=             (opcional; código o descripción)
&is_generico=true    (opcional)
&is_default=false    (opcional)
&activo=true         (opcional)
&inhabilitado=false  (opcional)
&sort=descripcion    (opcional)
&sort_dir=asc        (opcional)
```

**Response 200 OK (paginado, con page):**
```json
{
  "error": 0,
  "respuesta": "Tipos de tarea obtenidos correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "code": "DESARROLLO",
        "descripcion": "Desarrollo de software",
        "is_generico": true,
        "is_default": true,
        "activo": true,
        "inhabilitado": false,
        "created_at": "...",
        "updated_at": "..."
      }
    ],
    "total": 8,
    "page": 1,
    "page_size": 20
  }
}
```

**Response 403:** No supervisor.  
**Response 401:** No autenticado.

---

## 6) Cambios Frontend

### Pantallas/Componentes
- Nueva sección **Tipos de Tarea** (ruta ej. `/tipos-tarea`).
- Tabla con columnas: código, descripción, genérico, por defecto, estado, inhabilitado.
- Búsqueda, filtros genérico / por defecto / activo / inhabilitado, paginación.
- Botones/enlaces: Crear (→ HU-024), Editar (→ HU-025), Eliminar (→ HU-026).
- Estilo diferenciado para filas inhabilitadas y fila del tipo por defecto.

### Estados UI
- Loading, Empty (sin resultados), Error (403/500).
- data-testid: `tiposTarea.tabla`, `tiposTarea.busqueda`, `tiposTarea.filtroGenerico`, `tiposTarea.filtroPorDefecto`, `tiposTarea.filtroActivo`, `tiposTarea.filtroInhabilitado`, `tiposTarea.crear`, `tiposTarea.editar`, `tiposTarea.eliminar`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | Endpoint GET listado tipos tarea (paginación, search, filtros) | 200 + resultado con items/total/page; sin page → array selector | — | M |
| T2 | Frontend | Página Listado Tipos de Tarea (ruta + tabla + búsqueda/filtros) | Cumple AC | T1 | M |
| T3 | Frontend | Enlaces Crear / Editar / Eliminar en listado | Navegación correcta | T2 | S |
| T4 | Tests    | Unit + integration backend (listado, 403, paginación) | Tests pasan | T1 | S |
| T5 | Tests    | E2E: acceder listado, búsqueda, filtros | ≥1 E2E Playwright | T2 | M |
| T6 | Docs     | Specs endpoint listado tipos tarea | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit:** Servicio listado (filtros, paginación).
- **Integration:** GET 200 con datos; 401/403; validación query params.
- **E2E:** Login supervisor → Tipos de Tarea → ver tabla, búsqueda, filtros.

---

## 9) Riesgos y Edge Cases

- Conflicto con uso actual de tipos de tarea (selector en formularios): resolver con query params (sin page = selector, con page = listado ABM) o ruta distinta.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend: listado con paginación, búsqueda, filtros
- [x] Frontend: sección Tipos de Tarea + tabla + acciones
- [x] Unit/integration tests ok
- [x] ≥1 E2E ok
- [x] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/TipoTareaService.php` (listado paginado, filtros).
- `backend/app/Http/Controllers/Api/V1/TipoTareaController.php` (index con/sin page).
- `backend/routes/api.php` (GET/POST/GET/PUT/DELETE /tipos-tarea).

### Frontend
- `frontend/src/features/tipoTarea/components/TiposTareaPage.tsx`, `TiposTareaPage.css`.
- `frontend/src/features/tipoTarea/services/tipoTarea.service.ts`, `services/index.ts`.
- `frontend/src/features/tipoTarea/components/index.ts`, `frontend/src/features/tipoTarea/index.ts`.
- `frontend/src/app/App.tsx` (rutas tipos-tarea), `frontend/src/app/Dashboard.tsx` (enlace Tipos de Tarea).

### Tests
- `backend/tests/Feature/Api/V1/TipoTareaControllerTest.php`.
- `frontend/tests/e2e/tipos-tarea.spec.ts`.

## Comandos ejecutados

- `php artisan test tests/Feature/Api/V1/TipoTareaControllerTest.php`
- `npm run test:e2e` (frontend, spec tipos-tarea).

## Notas y decisiones

- GET /api/v1/tipos-tarea: sin query `page` devuelve array simple (selector); con `page` devuelve resultado paginado (items, total, page, page_size).
- Implementación conjunta con TR-024 a TR-027 (mismo controller/servicio/rutas/frontend).

## Pendientes / follow-ups

- Ninguno.
