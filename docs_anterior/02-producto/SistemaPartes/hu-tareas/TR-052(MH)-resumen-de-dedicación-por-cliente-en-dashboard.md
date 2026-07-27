# TR-052(MH) – Resumen de dedicación por cliente en dashboard

| Campo              | Valor                                                                 |
|--------------------|-----------------------------------------------------------------------|
| HU relacionada     | HU-052(MH)-resumen-de-dedicación-por-cliente-en-dashboard            |
| Épica              | Épica 10: Dashboard                                                  |
| Prioridad          | MUST-HAVE                                                            |
| Roles              | Empleado / Empleado Supervisor / Cliente                             |
| Dependencias       | HU-051 (Dashboard principal); TR-051                                 |
| Clasificación      | HU COMPLEJA                                                          |
| Última actualización | 2026-01-31                                                         |
| Estado             | 📋 PENDIENTE                                                          |

---

## 1) HU Refinada

### Título
Resumen de dedicación por cliente en dashboard

### Narrativa
**Como** usuario (empleado, supervisor o cliente)  
**Quiero** ver un resumen de dedicación por cliente en el dashboard  
**Para** identificar rápidamente los clientes con mayor dedicación

### Contexto/Objetivo
En el dashboard (TR-051) se añade la sección "Dedicación por Cliente". Muestra una lista o tabla con los clientes y sus totales de horas del período (según permisos del usuario), ordenada por dedicación descendente, limitada a top N (ej. 5 o 10). Cada ítem: nombre del cliente, total de horas (decimal), cantidad de tareas, porcentaje del total (opcional). Total general de horas. El usuario puede hacer clic en un cliente para ir al detalle (redirección a "Tareas por Cliente" con filtro o consulta detallada). Filtros automáticos por rol igual que en TR-051.

### Suposiciones explícitas
- TR-051 (Dashboard principal) está implementado: endpoint GET /api/v1/dashboard y pantalla Dashboard con período y KPIs.
- Los datos "top clientes" o "dedicación por cliente" pueden provenir del mismo endpoint de dashboard (campo top_clientes ampliado) o de un sub-endpoint GET /api/v1/dashboard/dedication-by-client. Se opta por reutilizar/ampliar el contrato del dashboard para evitar duplicar lógica.
- Horas en formato decimal (minutos / 60).

### In Scope
- Sección "Dedicación por Cliente" visible en el Dashboard para los tres roles.
- Lista o tabla: cliente (nombre), total horas (decimal), cantidad tareas, porcentaje (opcional).
- Orden por total horas descendente; top N (ej. 5 o 10).
- Total general de horas del período (coherente con KPIs del dashboard).
- Enlace o acción "Ver detalle" por cliente que lleve a Tareas por Cliente (o Consulta Detallada con filtro cliente).
- Filtros automáticos por rol aplicados (empleado: sus tareas; supervisor: todas; cliente: solo su cliente).

### Out of Scope
- Paginación de la lista (se limita a top N).
- Edición de tareas desde esta sección.
- Exportación desde esta sección.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El dashboard muestra la sección "Dedicación por Cliente".
- **AC-02**: Los datos se filtran automáticamente según el rol (empleado: sus tareas; supervisor: todas; cliente: solo donde es el cliente).
- **AC-03**: Se muestra una lista o tabla con clientes y totales: nombre, total horas (decimal), cantidad tareas; porcentaje del total (opcional).
- **AC-04**: Se muestran los top N clientes (ej. 5 o 10), ordenados por total de horas descendente.
- **AC-05**: Se muestra el total general de horas del período (según permisos).
- **AC-06**: El usuario puede hacer clic en un cliente para ver el detalle (redirección a Tareas por Cliente o Consulta Detallada con filtro por ese cliente).
- **AC-07**: Estado vacío (sin tareas en el período): mensaje alineado con HU-050; no tabla vacía.
- **AC-08**: data-testid en la sección y en la lista/tabla para E2E.

### Escenarios Gherkin

```gherkin
Feature: Resumen de dedicación por cliente en dashboard

  Scenario: Empleado ve dedicación por cliente (sus tareas)
    Given el empleado "JPEREZ" está autenticado
    When accede al dashboard
    Then ve la sección "Dedicación por Cliente"
    And la lista muestra solo clientes donde él tiene tareas
    And los clientes están ordenados por total horas descendente
    And ve el total general de horas

  Scenario: Supervisor ve dedicación por cliente (todas las tareas)
    Given el supervisor "MGARCIA" está autenticado
    When accede al dashboard
    Then ve la sección "Dedicación por Cliente"
    And la lista muestra top N clientes por horas (todas las tareas)
    And puede hacer clic en un cliente para ver detalle

  Scenario: Cliente ve su dedicación
    Given el cliente "CLI001" está autenticado
    When accede al dashboard
    Then ve la sección "Dedicación por Cliente"
    And ve como máximo un cliente (él mismo) o mensaje vacío
    And el total general corresponde solo a sus tareas

  Scenario: Clic en cliente lleva a detalle
    Given el usuario está en el dashboard con al menos un cliente en la lista
    When hace clic en "Ver detalle" del cliente "Cliente A"
    Then navega a Tareas por Cliente o Consulta Detallada con filtro por ese cliente
```

---

## 3) Reglas de Negocio

1. **RN-01**: Agrupación por `cliente_id` sobre las tareas visibles según rol (mismas reglas que TR-051).
2. **RN-02**: Ordenamiento por total de horas descendente; límite top N (ej. 10).
3. **RN-03**: Empleado: solo sus tareas. Supervisor: todas. Cliente: solo tareas donde `cliente_id` = su cliente.
4. **RN-04**: Total general de horas coherente con el KPI "total horas" del dashboard.
5. **RN-05**: Porcentaje (opcional): (total_horas_cliente / total_general_horas) * 100.

### Permisos por Rol
- **Empleado:** Sección con sus clientes (top N por horas).
- **Supervisor:** Sección con todos los clientes (top N).
- **Cliente:** Sección con un único cliente (él mismo) o vacío.

---

## 4) Impacto en Datos

- **Tablas afectadas:** Mismas que TR-051 (`PQ_PARTES_REGISTRO_TAREA`, clientes, usuarios). Solo lecturas y agregaciones.
- **Cambios en datos:** Ninguno. Reutilización del endpoint de dashboard o extensión del mismo.

---

## 5) Contratos de API

- **Opción A:** Reutilizar GET /api/v1/dashboard (TR-051). El campo `top_clientes` ya incluye cliente_id, nombre, total_horas, cantidad_tareas. Se puede ampliar con `porcentaje` si se desea.
- **Opción B:** GET /api/v1/dashboard/dedication-by-client con fecha_desde, fecha_hasta, limit (opcional). Respuesta: lista de { cliente_id, nombre, total_horas, cantidad_tareas, porcentaje }, ordenada desc, limit N.

**Recomendación:** Opción A para MVP (un solo request de dashboard con top_clientes y total_general; el frontend calcula porcentaje si se muestra). Si el dashboard ya devuelve top_clientes, TR-052 se centra en el frontend: sección "Dedicación por Cliente", tabla/lista, enlace a detalle.

**Response (dentro de resultado del dashboard):**
```json
"top_clientes": [
  {
    "cliente_id": 1,
    "nombre": "Cliente A",
    "total_horas": 50.0,
    "cantidad_tareas": 20,
    "porcentaje": 41.5
  }
]
```

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **Dashboard:** Nueva sección "Dedicación por Cliente" que consume top_clientes (y total_general) del mismo endpoint del dashboard. Tabla o lista con nombre, total horas, cantidad tareas, porcentaje (opcional). Botón o enlace "Ver detalle" por fila que navegue a /informes/tareas-por-cliente (o consulta detallada con query param cliente_id si se implementa).
- **Estado vacío:** Si top_clientes está vacío, mostrar mensaje tipo "No hay tareas en el período" (HU-050), no tabla vacía.

### Estados UI
- Loading/Error: heredados del dashboard (TR-051).
- Empty: mensaje cuando no hay clientes con tareas en el período.

### Accesibilidad Mínima
- data-testid: dashboard.dedicacionCliente, dashboard.dedicacionCliente.lista, dashboard.dedicacionCliente.totalGeneral, dashboard.dedicacionCliente.linkDetalle.{cliente_id} (o similar).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | Ampliar respuesta dashboard (opcional) | Incluir porcentaje en top_clientes si no está; o asegurar total_general para cálculo en frontend. | TR-051 | S |
| T2 | Frontend | Sección "Dedicación por Cliente" en Dashboard | Bloque con título; consumo de top_clientes del estado del dashboard. data-testid. | TR-051 | M |
| T3 | Frontend | Lista/tabla dedicación por cliente | Columnas: nombre, total horas, cantidad tareas, porcentaje (opcional). Orden ya viene del backend. | T2 | S |
| T4 | Frontend | Total general y enlace a detalle | Mostrar total general; botón/enlace "Ver detalle" por cliente → navegación a Tareas por Cliente (o Consulta Detallada con filtro). | T2 | S |
| T5 | Frontend | Estado vacío sección dedicación | Si top_clientes vacío: mensaje (HU-050), no tabla vacía. | T2 | S |
| T6 | Tests    | E2E dashboard con sección dedicación | Login empleado/supervisor → dashboard → sección "Dedicación por Cliente" visible; al menos un cliente o mensaje vacío. | T2 | M |
| T7 | Docs     | Actualizar TR-051 o docs si se amplía API | Si se añade porcentaje u otro campo. | T1 | S |

**Total:** 8 tareas. Si TR-051 ya expone top_clientes y total_general, T1 puede ser nula.

---

## 8) Estrategia de Tests

- **Unit:** Cálculo de porcentaje en frontend (si se hace en cliente).
- **Integration:** Cubierto por tests del dashboard (TR-051) si no hay endpoint nuevo.
- **E2E:** Dashboard con sección "Dedicación por Cliente"; ver lista o mensaje vacío; clic en "Ver detalle" navega correctamente.

---

## 9) Riesgos y Edge Cases

- Cliente con un solo cliente: la sección muestra una fila o mensaje "Su dedicación: X horas".
- Top N con empates: orden estable (ej. por nombre).

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Sección "Dedicación por Cliente" visible en Dashboard
- [ ] Lista/tabla con nombre, horas, cantidad, porcentaje (opcional)
- [ ] Total general y enlace a detalle por cliente
- [ ] Estado vacío (HU-050)
- [ ] E2E con sección dedicación
- [ ] Docs actualizados

---

## Archivos creados/modificados

- `frontend/src/app/Dashboard.tsx` — Sección "Dedicación por Cliente" con lista, total general, enlace "Ver detalle" por cliente.
- `frontend/src/app/Dashboard.css` — Estilos enlace y total general.
- `frontend/src/features/tasks/components/TareasPorClientePage.tsx` — Lectura de query params cliente_id, fecha_desde, fecha_hasta para prellenar filtros y expandir cliente.
- `frontend/tests/e2e/dashboard.spec.ts` — E2E TR-052: sección dedicación, total general, clic Ver detalle.
- `.cursor/Docs/TR-052(MH)-resumen-de-dedicación-por-cliente-en-dashboard.md` — Documentación de la tarea.

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

*(Se completará durante la implementación)*

## Pendientes / follow-ups

*(Se completará durante la implementación)*
