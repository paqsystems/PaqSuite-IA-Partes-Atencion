# TR-013(SH) – Visualización de detalle de cliente

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-013(SH)-visualización-de-detalle-de-cliente |
| Épica              | Épica 3: Gestión de Clientes (ABM)         |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-008 (listado), HU-010 (edición), HU-012 (tipos tarea) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-06                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Visualización de detalle de cliente

### Narrativa
**Como** supervisor  
**Quiero** ver el detalle completo de un cliente incluyendo sus tipos de tarea asignados y estadísticas básicas  
**Para** tener contexto completo antes de editar o eliminar.

### Contexto/Objetivo
Pantalla de detalle accesible desde el listado de clientes. Muestra datos del cliente (código, nombre, tipo de cliente, email, estado), lista de tipos de tarea asignados (no genéricos), opcionalmente cantidad de tareas registradas y fechas created_at/updated_at. Incluye acciones para editar y eliminar (eliminar solo si no tiene tareas).

### Suposiciones explícitas
- El usuario está autenticado y es supervisor (HU-001).
- Existe GET /api/v1/clientes/:id (TR-008/ClienteController) que devuelve el cliente; puede extenderse o reutilizarse para el detalle.
- Los tipos de tarea asignados al cliente se obtienen del endpoint existente (ej. clientes-tipos-tarea o incluidos en get cliente).
- La cantidad de tareas registradas para el cliente es opcional (endpoint de reportes o agregado en backend).

### In Scope
- Ruta /clientes/:id (detalle) accesible solo para supervisores.
- Mostrar: código, nombre, tipo de cliente, email, estado (activo/inactivo), inhabilitado.
- Mostrar lista de tipos de tarea asignados al cliente (no genéricos).
- Opcional: total de tareas registradas para el cliente; created_at, updated_at.
- Botones/enlaces: Editar (→ HU-010), Eliminar (→ HU-011, si no tiene tareas).

### Out of Scope
- Edición/eliminación en sí (ya cubiertas por HU-010, HU-011).
- Asignación de tipos de tarea desde esta pantalla (HU-012; puede enlazar a flujo existente).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder al detalle de un cliente desde el listado (ej. clic en fila o enlace "Ver").
- **AC-02**: Se muestra toda la información del cliente: código, nombre, tipo de cliente, email, estado (activo/inactivo), inhabilitado.
- **AC-03**: Se muestra la lista de tipos de tarea asignados al cliente (no genéricos).
- **AC-04**: Opcional: se muestra la cantidad total de tareas registradas para el cliente.
- **AC-05**: Opcional: se muestran fecha de creación y última actualización.
- **AC-06**: El supervisor puede navegar a editar el cliente desde el detalle.
- **AC-07**: El supervisor puede eliminar el cliente desde el detalle si no tiene tareas; si tiene tareas, no se permite o se muestra mensaje acorde.

### Escenarios Gherkin

```gherkin
Feature: Detalle de Cliente

  Scenario: Supervisor accede al detalle desde el listado
    Given el supervisor está en el listado de clientes
    When hace clic en "Ver" o en la fila del cliente "CLI001"
    Then se muestra la pantalla de detalle del cliente
    And se muestran código, nombre, tipo de cliente, email, estado
    And se muestra la lista de tipos de tarea asignados

  Scenario: Detalle con acciones Editar y Eliminar
    Given el supervisor está en el detalle de un cliente sin tareas
    Then ve el botón/enlace "Editar"
    And ve el botón/enlace "Eliminar"
    When hace clic en "Editar"
    Then navega al formulario de edición del cliente
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden acceder al detalle de cliente.
2. **RN-02**: La información mostrada debe ser de solo lectura en esta pantalla; la edición se hace en la pantalla de edición (HU-010).
3. **RN-03**: Eliminar solo permitido si el cliente no tiene tareas registradas (igual que HU-011).

### Permisos por Rol
- **Supervisor:** Acceso completo al detalle; acciones Editar y Eliminar (Eliminar según regla de tareas).
- **Empleado (no supervisor):** Sin acceso; 403 o redirección.
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_CLIENTES`: lectura por id.
- `PQ_PARTES_TIPOS_CLIENTE`: lectura para mostrar tipo del cliente.
- Tabla de relación cliente–tipos de tarea y `PQ_PARTES_REGISTRO_TAREA` (o equivalente) si se muestra total de tareas.

### Migración + Rollback
- No se requiere migración nueva si los endpoints y modelos ya existen.

### Seed Mínimo para Tests
- Cliente con tipos de tarea asignados; cliente con y sin tareas registradas; usuario supervisor.

---

## 5) Contratos de API

### Reutilización
- **GET /api/v1/clientes/{id}** (existente): devuelve cliente. Verificar que incluya o pueda incluir tipos de tarea asignados.
- Si no incluye tipos de tarea: usar **GET /api/v1/clientes/{id}/tipos-tarea** (o equivalente según HU-012).
- Opcional: endpoint o campo que devuelva total de tareas del cliente (puede ser atributo calculado en GET cliente o endpoint de reportes).

**Autorización:** Solo supervisor (403 si no supervisor).

---

## 6) Cambios Frontend

### Pantallas/Componentes
- Nueva pantalla o página **Detalle de Cliente** (ruta ej. `/clientes/:id`).
- Desde listado de clientes: enlace "Ver" o fila clicable que navegue a `/clientes/:id`.
- Mostrar datos en formato lectura; botones/enlaces "Editar" (→ `/clientes/:id/editar`) y "Eliminar" (→ modal o flujo HU-011; deshabilitado o oculto si tiene tareas).

### Estados UI
- Loading mientras se carga el cliente.
- Empty/Error si el cliente no existe o 403.
- Success con datos y lista de tipos de tarea.

### data-testid sugeridos
- `clienteDetalle.container`, `clienteDetalle.editar`, `clienteDetalle.eliminar`, `clienteDetalle.tiposTarea`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | Asegurar GET cliente + tipos tarea (o combo) para detalle | Respuesta incluye datos para detalle y tipos de tarea asignados | HU-008, HU-012 | S |
| T2 | Frontend | Pantalla Detalle Cliente (ruta /clientes/:id) | Muestra datos cliente + tipos tarea; enlaces Editar/Eliminar | T1 | M |
| T3 | Frontend | Enlace desde listado a detalle | Desde listado, "Ver" o fila lleva a detalle | T2 | S |
| T4 | Tests    | Unit + integration backend (get cliente con tipos) | Tests pasan | T1 | S |
| T5 | Tests    | E2E: listado → detalle → editar; detalle → eliminar (sin tareas) | ≥1 E2E Playwright | T2, T3 | M |
| T6 | Docs     | Actualizar specs si se añade o cambia contrato | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit:** Servicio/controller que arma respuesta de detalle (cliente + tipos tarea).
- **Integration:** GET /api/v1/clientes/{id} 200 con datos esperados; 403 no supervisor; 404 no existe.
- **E2E:** Navegar a clientes → clic en Ver → ver detalle; clic Editar → formulario edición; (opcional) clic Eliminar cuando no tiene tareas.

---

## 9) Riesgos y Edge Cases

- Cliente eliminado entre listado y detalle: mostrar 404.
- Supervisor sin permisos: 403 consistente con listado.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend devuelve datos necesarios para detalle (GET clientes/:id + GET clientes/:id/tipos-tarea existentes)
- [x] Frontend: pantalla detalle + enlaces desde listado
- [x] Unit/integration tests ok (existentes en ClienteControllerTest)
- [x] ≥1 E2E Playwright ok (clientes-list.spec.ts)
- [x] Docs actualizados

---

## Archivos creados/modificados

### Backend
- Ninguno (se reutilizan GET /api/v1/clientes/{id} y GET /api/v1/clientes/{id}/tipos-tarea).

### Frontend
- `frontend/src/features/clients/components/ClienteDetallePage.tsx` – Pantalla detalle (datos, tipos tarea, Editar/Eliminar).
- `frontend/src/features/clients/components/ClienteDetallePage.css` – Estilos.
- `frontend/src/features/clients/components/ClientesPage.tsx` – Botón "Ver" que navega a /clientes/:id.
- `frontend/src/features/clients/components/ClientesPage.css` – Estilo .clientes-page-btn-ver.
- `frontend/src/features/clients/components/index.ts` – Export ClienteDetallePage.
- `frontend/src/features/clients/index.ts` – Export ClienteDetallePage.
- `frontend/src/app/App.tsx` – Ruta clientes/:id con ClienteDetallePage (SupervisorRoute).

### Tests
- `frontend/tests/e2e/clientes-list.spec.ts` – Test E2E "supervisor puede abrir detalle de cliente desde listado (TR-013)".

## Comandos ejecutados

```bash
# E2E (con backend y frontend levantados)
cd frontend && npm run test:e2e -- tests/e2e/clientes-list.spec.ts
```

## Notas y decisiones

- Detalle obtiene datos con dos llamadas: getCliente(id) y getTiposTareaCliente(id).
- Eliminar desde detalle abre modal de confirmación; si el cliente tiene tareas, el API devuelve 422 (2112) y se muestra mensaje.

## Pendientes / follow-ups

- Ninguno.
