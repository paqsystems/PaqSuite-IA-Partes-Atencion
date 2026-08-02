# TR-027(SH) – Visualización de detalle de tipo de tarea

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-027(SH)-visualización-de-detalle-de-tipo-de-tarea |
| Épica              | Épica 6: Gestión de Tipos de Tarea (ABM)  |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-025 (edición / listado)                 |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-06                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Visualización de detalle de tipo de tarea

### Narrativa
**Como** supervisor  
**Quiero** ver el detalle completo de un tipo de tarea incluyendo clientes asociados y estadísticas básicas  
**Para** tener contexto completo antes de editar o eliminar.

### Contexto/Objetivo
Pantalla de detalle accesible desde el listado de tipos de tarea. Muestra: código, descripción, genérico, por defecto, estado (activo/inactivo), inhabilitado. Si el tipo NO es genérico: lista de clientes asociados (desde ClienteTipoTarea). Opcional: cantidad total de tareas registradas con este tipo; created_at, updated_at. Acciones: Editar (→ HU-025), Eliminar (→ HU-026; si no tiene referencias).

### Suposiciones explícitas
- El usuario está autenticado y es supervisor (HU-001).
- Existe GET /api/v1/tipos-tarea/{id} (TR-025) que devuelve el tipo; puede extenderse o reutilizarse para el detalle.
- Los clientes asociados se obtienen de una relación o endpoint (ej. tipos-tarea/{id}/clientes o incluidos en GET tipo).
- La cantidad de tareas registradas es opcional (agregado en backend o endpoint de reportes).

### In Scope
- Ruta /tipos-tarea/:id (detalle) accesible solo para supervisores.
- Mostrar: código, descripción, genérico, por defecto, estado, inhabilitado.
- Si tipo NO genérico: mostrar lista de clientes asociados (ClienteTipoTarea).
- Opcional: total de tareas registradas con este tipo; created_at, updated_at.
- Botones/enlaces: Editar (→ HU-025), Eliminar (→ HU-026, si no tiene referencias).

### Out of Scope
- Edición/eliminación en sí (cubiertas por HU-025, HU-026).
- Asignación de tipos de tarea a clientes desde esta pantalla (HU-012).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder al detalle de un tipo de tarea desde el listado (ej. clic en fila o enlace "Ver").
- **AC-02**: Se muestra toda la información del tipo: código, descripción, genérico, por defecto, estado (activo/inactivo), inhabilitado.
- **AC-03**: Si el tipo NO es genérico, se muestra la lista de clientes asociados (desde ClienteTipoTarea).
- **AC-04**: Opcional: se muestra la cantidad total de tareas registradas con este tipo.
- **AC-05**: Opcional: se muestran fecha de creación y última actualización.
- **AC-06**: El supervisor puede navegar a editar el tipo desde el detalle.
- **AC-07**: El supervisor puede eliminar el tipo desde el detalle si no tiene referencias; si tiene, no se permite o se muestra mensaje acorde (2114).

### Escenarios Gherkin

```gherkin
Feature: Detalle de Tipo de Tarea

  Scenario: Supervisor accede al detalle desde el listado
    Given el supervisor está en el listado de tipos de tarea
    When hace clic en "Ver" o en la fila del tipo "DESARROLLO"
    Then se muestra la pantalla de detalle del tipo
    And se muestran código, descripción, genérico, por defecto, estado
    And si no es genérico se muestra la lista de clientes asociados

  Scenario: Detalle con acciones Editar y Eliminar
    Given el supervisor está en el detalle de un tipo sin referencias
    Then ve el botón/enlace "Editar"
    And ve el botón/enlace "Eliminar"
    When hace clic en "Editar"
    Then navega al formulario de edición del tipo
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden acceder al detalle de tipo de tarea.
2. **RN-02**: La información mostrada es de solo lectura en esta pantalla; la edición se hace en la pantalla de edición (HU-025).
3. **RN-03**: Eliminar solo permitido si el tipo no tiene tareas ni clientes asociados (igual que HU-026; código 2114 si tiene referencias).

### Permisos por Rol
- **Supervisor:** Acceso completo al detalle; acciones Editar y Eliminar (Eliminar según regla de referencias).
- **Empleado (no supervisor):** Sin acceso; 403 o redirección.
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_TIPOS_TAREA`: lectura por id.
- Tabla de relación cliente–tipo tarea: lectura para clientes asociados (si tipo no genérico).
- `PQ_PARTES_REGISTRO_TAREA` (o equivalente) si se muestra total de tareas (opcional).

### Migración + Rollback
- No se requiere migración nueva si los endpoints y modelos ya existen.

### Seed Mínimo para Tests
- Tipo genérico y tipo no genérico con clientes asignados; tipo con y sin tareas registradas; usuario supervisor.

---

## 5) Contratos de API

### Reutilización / Extensión
- **GET /api/v1/tipos-tarea/{id}** (existente en TR-025): devuelve tipo. Verificar que incluya o pueda incluir clientes asociados cuando no es genérico.
- Si no incluye clientes: usar **GET /api/v1/tipos-tarea/{id}/clientes** (o equivalente) o ampliar respuesta de GET tipo.
- Opcional: campo o endpoint que devuelva total de tareas del tipo (atributo calculado o endpoint de reportes).

**Autorización:** Solo supervisor (403 si no supervisor).

---

## 6) Cambios Frontend

### Pantallas/Componentes
- Nueva pantalla **Detalle de Tipo de Tarea** (ruta ej. `/tipos-tarea/:id`).
- Desde listado: enlace "Ver" o fila clicable que navegue a `/tipos-tarea/:id`.
- Mostrar datos en formato lectura; botones/enlaces "Editar" (→ `/tipos-tarea/:id/editar`) y "Eliminar" (→ modal o flujo HU-026; deshabilitado u oculto si tiene referencias).
- Si no genérico: lista de clientes asociados.

### Estados UI
- Loading mientras se carga el tipo.
- Empty/Error si el tipo no existe o 403.
- Success con datos y (si aplica) lista de clientes asociados.

### data-testid sugeridos
- `tipoTareaDetalle.container`, `tipoTareaDetalle.editar`, `tipoTareaDetalle.eliminar`, `tipoTareaDetalle.clientesAsociados`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | Asegurar GET tipo + clientes asociados (o combo) para detalle | Respuesta incluye datos para detalle y clientes asociados si no genérico | HU-025, TR-025 | S |
| T2 | Frontend | Pantalla Detalle Tipo de Tarea (ruta /tipos-tarea/:id) | Muestra datos tipo + clientes asociados si aplica; enlaces Editar/Eliminar | T1 | M |
| T3 | Frontend | Enlace desde listado a detalle | Desde listado, "Ver" o fila lleva a detalle | T2 | S |
| T4 | Tests    | Unit + integration backend (get tipo con clientes si aplica) | Tests pasan | T1 | S |
| T5 | Tests    | E2E: listado → detalle → editar; detalle → eliminar (sin referencias) | ≥1 E2E Playwright | T2, T3 | M |
| T6 | Docs     | Actualizar specs si se añade o cambia contrato | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit:** Servicio/controller que arma respuesta de detalle (tipo + clientes asociados si aplica).
- **Integration:** GET /api/v1/tipos-tarea/{id} 200 con datos esperados; 403 no supervisor; 404 no existe.
- **E2E:** Navegar a tipos-tarea → clic en Ver → ver detalle; clic Editar → formulario edición; (opcional) clic Eliminar cuando no tiene referencias.

---

## 9) Riesgos y Edge Cases

- Tipo eliminado entre listado y detalle: mostrar 404.
- Supervisor sin permisos: 403 consistente con listado.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend devuelve datos necesarios para detalle (GET tipos-tarea/:id?clientes=1 + clientes si no genérico)
- [x] Frontend: pantalla detalle + enlaces desde listado
- [x] Unit/integration tests ok
- [x] ≥1 E2E Playwright ok
- [x] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `TipoTareaService::getByIdConClientes`; `TipoTareaController::show` con query `clientes=1` para incluir clientes asociados.

### Frontend
- `frontend/src/features/tipoTarea/components/TiposTareaDetallePage.tsx`, estilos en `TiposTareaPage.css`.
- Servicio `getTipoTareaConClientes`. Ruta `/tipos-tarea/:id`. Botón "Ver" en listado.

### Tests
- E2E en `tipos-tarea.spec.ts`: "supervisor puede abrir detalle de tipo de tarea (TR-027)".

## Comandos ejecutados

- `php artisan test tests/Feature/Api/V1/TipoTareaControllerTest.php`
- `npm run test:e2e` (tipos-tarea).

## Notas y decisiones

- Detalle con ?clientes=1 devuelve array clientes cuando el tipo no es genérico. Frontend muestra sección "Clientes asociados" solo en ese caso.

## Pendientes / follow-ups

- Ninguno.
