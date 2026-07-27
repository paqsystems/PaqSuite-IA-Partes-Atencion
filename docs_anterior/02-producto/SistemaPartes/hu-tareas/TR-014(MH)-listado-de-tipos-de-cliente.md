# TR-014(MH) – Listado de tipos de cliente

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-014(MH)-listado-de-tipos-de-cliente     |
| Épica              | Épica 4: Gestión de Tipos de Cliente (ABM) |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-001 (autenticación)                     |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-06                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Listado de tipos de cliente

### Narrativa
**Como** supervisor  
**Quiero** ver el listado de todos los tipos de cliente  
**Para** gestionarlos (crear, editar, eliminar).

### Contexto/Objetivo
Sección "Tipos de Cliente" con tabla de todos los tipos (código, descripción, estado activo/inactivo, inhabilitado). Paginación, búsqueda por código o descripción, filtros por estado e inhabilitado. Total de tipos mostrado. Tipos inhabilitados diferenciados visualmente. Base del ABM de tipos de cliente.

### Suposiciones explícitas
- Usuario autenticado y supervisor (HU-001).
- Tabla `PQ_PARTES_TIPOS_CLIENTE` existe (code, descripcion, activo, inhabilitado, timestamps).
- Endpoint GET listado de tipos de cliente se creará (ej. GET /api/v1/tipos-cliente con query params para listado ABM; el actual GET /api/v1/tipos-cliente puede ser solo para selector en formularios — ver convención del proyecto).

### In Scope
- Sección "Tipos de Cliente" accesible solo para supervisores (ruta ej. /tipos-cliente o /clientes/tipos).
- Tabla: código, descripción, estado (activo/inactivo), inhabilitado (sí/no).
- Paginación.
- Búsqueda por código o descripción.
- Filtros: estado activo/inactivo, inhabilitado sí/no.
- Total de tipos de cliente mostrado.
- Diferenciación visual de tipos inhabilitados.
- Acciones: Crear, Editar, Eliminar (enlaces a HU-015, HU-016, HU-017).

### Out of Scope
- Creación/edición/eliminación (HU-015, HU-016, HU-017).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la sección "Tipos de Cliente".
- **AC-02**: Se muestra una tabla con todos los tipos de cliente (sujeto a paginación/filtros).
- **AC-03**: La tabla muestra: código, descripción, estado (activo/inactivo), inhabilitado (sí/no).
- **AC-04**: Los tipos se listan paginados.
- **AC-05**: Se puede buscar por código o descripción.
- **AC-06**: Se puede filtrar por estado (activo/inactivo).
- **AC-07**: Se puede filtrar por inhabilitado (sí/no).
- **AC-08**: Se muestra el total de tipos de cliente.
- **AC-09**: Los tipos inhabilitados se muestran claramente diferenciados.
- **AC-10**: Usuario no supervisor no puede acceder (403 o redirección).

### Escenarios Gherkin

```gherkin
Feature: Listado de Tipos de Cliente

  Scenario: Supervisor accede al listado
    Given el supervisor está autenticado
    When accede a la sección "Tipos de Cliente"
    Then se muestra la tabla con código, descripción, estado, inhabilitado
    And se muestra el total de tipos
    And hay búsqueda y filtros

  Scenario: Búsqueda y filtros
    Given el supervisor está en Tipos de Cliente
    When escribe "CORP" en búsqueda
    And filtra por activo = true
    Then la tabla muestra solo tipos que coinciden
    And el total se actualiza
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden acceder al listado de tipos de cliente.
2. **RN-02**: Búsqueda aplicada a código y descripción (parcial, case-insensitive según specs).
3. **RN-03**: Filtros activo e inhabilitado opcionales; combinables con búsqueda.
4. **RN-04**: Paginación: page, page_size con rangos válidos.

### Permisos por Rol
- **Supervisor:** Acceso completo al listado y acciones.
- **Empleado (no supervisor):** 403 o redirección.
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_TIPOS_CLIENTE`: SELECT con filtros (code, descripcion, activo, inhabilitado). Sin nuevas columnas.

### Migración + Rollback
- No se requiere migración; tabla ya existe.

### Seed Mínimo para Tests
- Varios tipos de cliente (activo/inactivo, inhabilitado sí/no). Usuario supervisor y no supervisor.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/tipos-cliente` (listado ABM)

**Descripción:** Listado paginado de tipos de cliente con búsqueda y filtros. Solo supervisores.  
**Nota:** Si ya existe GET /api/v1/tipos-cliente para selector (sin paginación), definir ruta alternativa para ABM (ej. GET /api/v1/tipos-cliente con query params) o ampliar el existente con query params opcionales (page, page_size, search, activo, inhabilitado).

**Autenticación:** Requerida (Bearer).  
**Autorización:** Solo supervisor → 403 (3101) si no.

**Query Parameters:**
```
?page=1
&page_size=20
&search=           (opcional; código o descripción)
&activo=true       (opcional)
&inhabilitado=false (opcional)
&sort=descripcion  (opcional)
&sort_dir=asc      (opcional)
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tipos de cliente obtenidos correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "code": "CORP",
        "descripcion": "Corporativo",
        "activo": true,
        "inhabilitado": false,
        "created_at": "...",
        "updated_at": "..."
      }
    ],
    "total": 5,
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
- Nueva sección **Tipos de Cliente** (ruta ej. `/tipos-cliente`).
- Tabla con columnas: código, descripción, estado, inhabilitado.
- Búsqueda, filtros activo/inhabilitado, paginación.
- Botones/enlaces: Crear (→ HU-015), Editar (→ HU-016), Eliminar (→ HU-017).
- Estilo diferenciado para filas inhabilitadas.

### Estados UI
- Loading, Empty (sin resultados), Error (403/500).
- data-testid: `tiposCliente.tabla`, `tiposCliente.busqueda`, `tiposCliente.filtroActivo`, `tiposCliente.crear`, `tiposCliente.editar`, `tiposCliente.eliminar`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | Endpoint GET listado tipos cliente (paginación, search, filtros) | 200 + resultado con items/total/page | — | M |
| T2 | Frontend | Página Listado Tipos de Cliente (ruta + tabla + búsqueda/filtros) | Cumple AC | T1 | M |
| T3 | Frontend | Enlaces Crear / Editar / Eliminar en listado | Navegación correcta | T2 | S |
| T4 | Tests    | Unit + integration backend (listado, 403, paginación) | Tests pasan | T1 | S |
| T5 | Tests    | E2E: acceder listado, búsqueda, filtros | ≥1 E2E Playwright | T2 | M |
| T6 | Docs     | Specs endpoint listado tipos cliente | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit:** Servicio listado (filtros, paginación).
- **Integration:** GET 200 con datos; 401/403; validación query params.
- **E2E:** Login supervisor → Tipos de Cliente → ver tabla, búsqueda, filtros.

---

## 9) Riesgos y Edge Cases

- Conflicto con endpoint existente GET /tipos-cliente (selector): resolver con query params o ruta distinta.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend: listado con paginación, búsqueda, filtros
- [x] Frontend: sección Tipos de Cliente + tabla + acciones
- [x] Unit/integration tests ok
- [x] ≥1 E2E ok
- [x] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `backend/app/Services/TipoClienteService.php` (listado paginado, filtros).
- `backend/app/Http/Controllers/Api/V1/TipoClienteController.php` (index con/sin page).
- `backend/routes/api.php` (GET /tipos-cliente con page → paginado; sin page → selector).

### Frontend
- `frontend/src/features/tipoCliente/components/TiposClientePage.tsx`, `TiposClientePage.css`.
- `frontend/src/features/tipoCliente/services/tipoCliente.service.ts`.
- `frontend/src/features/tipoCliente/components/index.ts`, `frontend/src/features/tipoCliente/index.ts`.
- `frontend/src/app/App.tsx` (rutas tipos-cliente), `frontend/src/app/Dashboard.tsx` (enlace Tipos de Cliente).

### Tests
- `backend/tests/Feature/Api/V1/TipoClienteControllerTest.php`.
- `frontend/tests/e2e/tipos-cliente.spec.ts`.

## Comandos ejecutados

- `php artisan test tests/Feature/Api/V1/TipoClienteControllerTest.php`
- `npm run test:e2e` (frontend, spec tipos-cliente).

## Notas y decisiones

- GET /api/v1/tipos-cliente: sin query `page` devuelve array simple (selector); con `page` devuelve resultado paginado (items, total, page, page_size).
- Ruta única para ambos usos; frontend de listado siempre envía `page` para obtener paginación.

## Pendientes / follow-ups

- Ninguno.
