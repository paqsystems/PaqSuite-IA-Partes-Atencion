# TR-051(MH) – Dashboard principal

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-051(MH)-dashboard-principal            |
| Épica              | Épica 10: Dashboard                       |
| Prioridad          | MUST-HAVE                                 |
| Roles              | Empleado / Empleado Supervisor / Cliente  |
| Dependencias       | HU-001, HU-044                            |
| Clasificación      | HU COMPLEJA                               |
| Última actualización | 2026-01-31                             |
| Estado             | ✅ IMPLEMENTADO                             |

---

## 1) HU Refinada

### Título
Dashboard principal

### Narrativa
**Como** usuario (empleado, supervisor o cliente)  
**Quiero** ver un dashboard con resumen ejecutivo del sistema  
**Para** tener una visión general rápida de mi actividad

### Contexto/Objetivo
El dashboard es la página de inicio post-login. Muestra KPIs (total horas del período, cantidad de tareas, promedio por día opcional) y bloques según rol: empleado (sus tareas, top clientes); supervisor (todas las tareas, top clientes, top empleados); cliente (tareas donde es el cliente, distribución por tipo). Período por defecto: mes actual; el usuario puede cambiar el período (selector de mes o rango). Los datos se actualizan al cambiar el período. Opcional: gráficos (distribución por cliente, evolución en el tiempo). Diseño responsive.

### Suposiciones explícitas
- El usuario ya está autenticado (HU-001).
- HU-044 (Consulta Detallada) aporta la lógica de filtros por rol; se reutiliza para agregaciones del dashboard.
- La tabla `PQ_PARTES_REGISTRO_TAREA` y tablas relacionadas existen.
- Horas en formato decimal (minutos / 60).
- Período por defecto: primer y último día del mes actual (servidor o frontend).

### In Scope
- Dashboard accesible como página de inicio (ruta `/`) y desde menú.
- Contenido según rol: empleado (solo sus tareas), supervisor (todas), cliente (solo donde es el cliente).
- Período por defecto: mes actual; selector para cambiar período (mes o rango fecha_desde / fecha_hasta).
- KPIs: total horas del período, cantidad de tareas, promedio horas por día (opcional).
- Bloque "Top clientes" (lista top N por horas) para empleado y supervisor.
- Bloque "Top empleados" (solo supervisor): lista top N por horas.
- Bloque "Distribución por tipo" (solo cliente): horas por tipo de tarea.
- Estados loading y error; actualización al cambiar período.
- Diseño responsive; data-testid en elementos clave.

### Out of Scope
- Gráficos avanzados (si se incluyen, serán básicos: barras o pie).
- Exportación desde dashboard (HU-049).
- Edición de tareas desde el dashboard.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario accede al dashboard desde la página de inicio post-login (ruta `/`) o desde el menú.
- **AC-02**: El dashboard muestra información según el rol: empleado solo sus tareas; supervisor todas; cliente solo tareas donde es el cliente.
- **AC-03**: Se muestra un período por defecto (mes actual).
- **AC-04**: El usuario puede cambiar el período (selector de mes o rango fecha desde / fecha hasta).
- **AC-05**: Los datos se actualizan al cambiar el período (sin recargar toda la página).
- **AC-06**: Se muestran KPIs: total horas del período, cantidad de tareas del período; promedio horas por día (opcional).
- **AC-07**: Empleado y supervisor ven bloque "Top clientes" (top N por horas).
- **AC-08**: Solo el supervisor ve bloque "Top empleados" (top N por horas).
- **AC-09**: Solo el cliente ve bloque "Distribución por tipo" (horas por tipo de tarea).
- **AC-10**: Estados loading y error manejados; mensaje claro en error.
- **AC-11**: El dashboard es responsive.
- **AC-12**: Elementos clave tienen data-testid para tests E2E.

### Escenarios Gherkin

```gherkin
Feature: Dashboard principal

  Scenario: Empleado ve su resumen en dashboard
    Given el empleado "JPEREZ" está autenticado
    When accede al dashboard (página de inicio)
    Then ve total de horas del mes actual
    And ve cantidad de tareas del período
    And ve bloque "Top clientes" con sus clientes
    And no ve bloque "Top empleados"

  Scenario: Supervisor ve resumen global
    Given el supervisor "MGARCIA" está autenticado
    When accede al dashboard
    Then ve total de horas del mes (todas las tareas)
    And ve bloque "Top clientes"
    And ve bloque "Top empleados"

  Scenario: Cliente ve su dedicación
    Given el cliente "CLI001" está autenticado
    When accede al dashboard
    Then ve total de horas donde es el cliente
    And ve distribución por tipo de tarea
    And no ve "Top empleados"

  Scenario: Cambio de período
    Given el usuario está autenticado
    When cambia el período (mes o rango)
    Then los KPIs y bloques se actualizan
    And no se muestra tabla vacía sin datos (HU-050)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Filtros automáticos por rol (obligatorios): Cliente solo `cliente_id` = su cliente; Empleado solo `usuario_id` = su usuario; Supervisor todas las tareas.
2. **RN-02**: Período por defecto: mes actual (primer y último día del mes).
3. **RN-03**: KPIs y listas (top clientes, top empleados, distribución) se calculan aplicando filtros por rol y rango de fechas.
4. **RN-04**: Top N: límite configurable (ej. 5 o 10) para mantener el dashboard simple.
5. **RN-05**: Horas en formato decimal (minutos / 60). Validación de período: fecha_desde <= fecha_hasta (error 1305 si se expone selector libre).

### Permisos por Rol
- **Empleado (no supervisor):** Dashboard con sus tareas; top clientes (sus clientes); sin top empleados.
- **Supervisor:** Dashboard con todas las tareas; top clientes; top empleados.
- **Cliente:** Dashboard con tareas donde es el cliente; distribución por tipo; sin top empleados.

---

## 4) Impacto en Datos

- **Tablas afectadas:** `PQ_PARTES_REGISTRO_TAREA` y tablas relacionadas (clientes, usuarios, tipos de tarea). Solo lecturas y agregaciones.
- **Cambios en datos:** Ninguna migración. Índices existentes (fecha, usuario_id, cliente_id) suficientes para consultas de agregación.

### Seed Mínimo para Tests
- Tareas de varios empleados y clientes en el mes actual y meses anteriores.
- Usuario empleado, supervisor y cliente para tests por rol.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/dashboard`

**Descripción:** Obtener datos del dashboard para el período indicado, con filtros automáticos por rol.

**Autenticación:** Requerida (Bearer token).

**Autorización:** Empleado, Supervisor, Cliente. El backend aplica filtros por rol.

**Query Parameters:**
```
?fecha_desde=2026-01-01
&fecha_hasta=2026-01-31
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Dashboard obtenido correctamente",
  "resultado": {
    "total_horas": 120.5,
    "cantidad_tareas": 45,
    "promedio_horas_por_dia": 4.02,
    "top_clientes": [
      { "cliente_id": 1, "nombre": "Cliente A", "total_horas": 50.0, "cantidad_tareas": 20 }
    ],
    "top_empleados": [
      { "usuario_id": 1, "nombre": "Juan Pérez", "code": "JPEREZ", "total_horas": 80.0, "cantidad_tareas": 30 }
    ],
    "distribucion_por_tipo": [
      { "tipo_tarea_id": 1, "descripcion": "Desarrollo", "total_horas": 60.0, "cantidad_tareas": 25 }
    ]
  }
}
```

**Notas:** `top_empleados` solo se incluye para supervisor. `distribucion_por_tipo` solo se incluye para cliente. Si el rol no aplica, el campo puede venir vacío o omitirse.

**Response 422 (período inválido):** Si se valida en backend: error 1305 cuando fecha_desde > fecha_hasta.

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **Dashboard (existente):** Sustituir o complementar el placeholder. Añadir selector de período (mes o rango), tarjetas de KPIs (total horas, cantidad tareas, promedio opcional), bloque Top clientes, bloque Top empleados (solo supervisor), bloque Distribución por tipo (solo cliente). Opcional: gráficos (barras/pie).
- **Servicio dashboard:** Llamada GET /api/v1/dashboard con fecha_desde, fecha_hasta; transformar respuesta.

### Estados UI
- Loading: mientras se cargan datos del dashboard.
- Error: mensaje claro; reintentar o ajustar período.
- Success: KPIs y bloques visibles. Estado vacío (sin tareas en período) alineado con HU-050.

### Validaciones en UI
- Período: fecha_desde <= fecha_hasta si el selector lo permite.

### Accesibilidad Mínima
- data-testid: dashboard.container, dashboard.periodSelector, dashboard.kpis, dashboard.topClientes, dashboard.topEmpleados, dashboard.distribucionTipo, dashboard.loading, dashboard.error.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | Servicio/use-case dashboard | Agregaciones por rol (empleado/supervisor/cliente), período; total_horas, cantidad_tareas, promedio_horas_por_dia; top_clientes (limit N), top_empleados (solo supervisor, limit N), distribucion_por_tipo (solo cliente). | HU-044 | L |
| T2 | Backend  | DashboardController + ruta | GET /api/v1/dashboard con fecha_desde, fecha_hasta. Respuesta según rol. Validación 1305 opcional. | T1 | M |
| T3 | Backend  | Tests unitarios servicio dashboard | Filtros por rol; KPIs correctos; top N; período. | T1 | M |
| T4 | Backend  | Tests integración GET /dashboard | Por rol (empleado/supervisor/cliente); 401 sin token. | T2 | M |
| T5 | Frontend | Servicio getDashboard(fecha_desde, fecha_hasta) | Llamada GET, transformar respuesta. | — | S |
| T6 | Frontend | Dashboard: selector de período | Mes actual por defecto; selector mes o rango; data-testid. | — | M |
| T7 | Frontend | Dashboard: tarjetas KPIs | Total horas, cantidad tareas, promedio (opcional). data-testid. | T5, T6 | S |
| T8 | Frontend | Dashboard: bloque Top clientes | Lista top N; solo empleado y supervisor. | T5 | S |
| T9 | Frontend | Dashboard: bloque Top empleados | Lista top N; solo supervisor. | T5 | S |
| T10| Frontend | Dashboard: bloque Distribución por tipo | Solo cliente; horas por tipo. | T5 | S |
| T11| Frontend | Estados loading/error/vacío | HU-050 para sin datos. | T6 | S |
| T12| Tests    | E2E dashboard empleado y supervisor | Login → dashboard → KPIs y bloques visibles según rol. | T6–T10 | M |
| T13| Tests    | Frontend unit getDashboard | Params, transformación, errores. | T5 | S |
| T14| Docs     | Documentar GET /api/v1/dashboard | docs/backend o specs. | T2 | S |

**Total:** 15 tareas.

---

## 8) Estrategia de Tests

- **Unit (backend):** Cálculo de KPIs y top N con filtros por rol; período por defecto.
- **Integration:** GET /dashboard como empleado, supervisor, cliente; 401 sin token.
- **E2E:** Login empleado → dashboard con sus KPIs y top clientes; login supervisor → dashboard con top empleados y top clientes; login cliente → distribución por tipo.

---

## 9) Riesgos y Edge Cases

- Período sin datos: mostrar KPIs en cero y mensaje o estado vacío (HU-050).
- Top N con pocos datos: mostrar los que haya.
- Responsive: bloques apilados en móvil.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Backend GET /dashboard con filtros por rol
- [ ] Frontend Dashboard con período, KPIs, top clientes/empleados, distribución tipo
- [ ] Unit e integration tests ok
- [ ] ≥1 E2E dashboard por rol
- [ ] Docs actualizados

---

## Archivos creados/modificados

- `frontend/src/features/tasks/services/task.service.ts` — getDashboard, interfaces DashboardData, DashboardParams, GetDashboardResult, etc.
- `frontend/src/app/Dashboard.tsx` — selector período, KPIs, Top clientes, Top empleados, Distribución por tipo, loading/error/empty.
- `frontend/src/app/Dashboard.css` — estilos TR-051.
- `frontend/tests/e2e/dashboard.spec.ts` — E2E por rol (empleado, supervisor, cliente), período, Mes actual.
- `.cursor/Docs/Dashboard.tsx.md` — documentación del componente.

## Comandos ejecutados

*(Se completará durante la implementación)*

## Notas y decisiones

*(Se completará durante la implementación)*

## Pendientes / follow-ups

*(Se completará durante la implementación)*
