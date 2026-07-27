# TR-054(SH) – Gráficos y visualizaciones en dashboard

| Campo                | Valor                                                       |
|----------------------|-------------------------------------------------------------|
| HU relacionada       | HU-054(SH)-gráficos-y-visualizaciones-en-dashboard         |
| Épica                | Épica 10: Dashboard                                        |
| Prioridad            | SHOULD-HAVE                                                |
| Roles                | Empleado / Empleado Supervisor / Cliente                    |
| Dependencias         | HU-051, HU-052 (Dashboard; resumen por cliente); TR-051   |
| Clasificación        | HU SIMPLE                                                  |
| Última actualización | 2026-02-07                                                 |
| Estado               | 📋 PENDIENTE                                                |

---

## 1) HU Refinada

### Título
Gráficos y visualizaciones en dashboard

### Narrativa
**Como** usuario (empleado, supervisor o cliente)  
**Quiero** ver gráficos y visualizaciones en el dashboard  
**Para** entender mejor la distribución de la dedicación

### Contexto/Objetivo
El dashboard muestra gráficos según el rol, generados a partir de los mismos datos que las consultas (filtros automáticos por rol). Empleado: distribución de horas por cliente (barras o pie, solo sus tareas). Supervisor: por cliente y por empleado (todas las tareas). Cliente: distribución por tipo de tarea (solo tareas donde es el cliente). Los gráficos se actualizan al cambiar el período; son responsive y accesibles; se usa una librería estándar (Chart.js, Recharts, etc.).

### Suposiciones explícitas
- TR-051 (y TR-052/053 si aplican) están implementados; el endpoint GET /api/v1/dashboard (o datos equivalentes) permite obtener agregaciones por cliente, por empleado, por tipo de tarea según rol.
- Los datos para gráficos pueden venir del mismo endpoint de dashboard (ampliando la respuesta) o de endpoints de informes existentes (by-client, by-employee, by-task-type) con parámetros de período.
- Librería de gráficos: Recharts o Chart.js (elegir una y documentar).

### In Scope
- Gráfico de distribución de horas por cliente (empleado y supervisor: sus tareas o todas).
- Gráfico de distribución de horas por empleado (solo supervisor).
- Gráfico de distribución de horas por tipo de tarea (solo cliente).
- Datos filtrados automáticamente según rol.
- Gráficos se actualizan al cambiar el período.
- Gráficos responsive y accesibles (textos alternativos, contraste).
- Uso de librería estándar (Chart.js, Recharts, etc.); colores consistentes y accesibles.
- Opcional: tooltips, clics para filtrar.

### Out of Scope
- Gráficos avanzados (solo barras o pie / dona suficientes para MVP).
- Exportación de gráficos como imagen.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El empleado (no supervisor) ve un gráfico de distribución de horas por cliente (solo sus tareas).
- **AC-02**: El supervisor ve gráfico de distribución por cliente y gráfico por empleado (todas las tareas).
- **AC-03**: El cliente ve un gráfico de distribución de horas por tipo de tarea (solo tareas donde es el cliente).
- **AC-04**: Los datos de los gráficos se filtran automáticamente según el rol del usuario.
- **AC-05**: Los gráficos se actualizan al cambiar el período (selector del dashboard).
- **AC-06**: Los gráficos son responsive y se adaptan al tamaño de pantalla.
- **AC-07**: Se usa una librería de gráficos estándar (Chart.js, Recharts, etc.).
- **AC-08**: Colores consistentes y accesibles (contraste).
- **AC-09**: Gráficos con textos alternativos o descripción para accesibilidad.
- **AC-10**: data-testid en contenedores de gráficos para E2E.

### Escenarios Gherkin

```gherkin
Feature: Gráficos en dashboard

  Scenario: Empleado ve gráfico por cliente (sus tareas)
    Given el empleado "JPEREZ" está autenticado
    When accede al dashboard
    Then ve un gráfico de distribución de horas por cliente
    And los datos corresponden solo a sus tareas

  Scenario: Supervisor ve gráficos por cliente y por empleado
    Given el supervisor "MGARCIA" está autenticado
    When accede al dashboard
    Then ve gráfico de distribución por cliente
    And ve gráfico de distribución por empleado
    And los datos incluyen todas las tareas

  Scenario: Cliente ve gráfico por tipo de tarea
    Given el cliente "CLI001" está autenticado
    When accede al dashboard
    Then ve un gráfico de distribución de horas por tipo de tarea
    And los datos corresponden solo a tareas donde es el cliente

  Scenario: Cambio de período actualiza gráficos
    Given el usuario está en el dashboard con gráficos visibles
    When cambia el período (mes o rango)
    Then los gráficos se actualizan con los datos del nuevo período
```

---

## 3) Reglas de Negocio

1. **RN-01**: Filtros automáticos por rol (obligatorios): Cliente solo `cliente_id` = su cliente; Empleado solo `usuario_id` = su usuario; Supervisor todas las tareas.
2. **RN-02**: Los gráficos se generan a partir de los mismos criterios de filtrado que las consultas del dashboard.
3. **RN-03**: Gráficos accesibles: textos alternativos, contraste de colores (WCAG básico).

### Permisos por Rol
- **Empleado (no supervisor):** Gráfico horas por cliente (sus tareas).
- **Supervisor:** Gráfico horas por cliente + gráfico horas por empleado (todas las tareas).
- **Cliente:** Gráfico horas por tipo de tarea (solo su cliente).

---

## 4) Impacto en Datos

- **Tablas afectadas:** Mismas que dashboard (PQ_PARTES_REGISTRO_TAREA y relacionadas). Solo lecturas y agregaciones.
- **Cambios en datos:** Ninguno. Los datos pueden provenir del mismo GET /api/v1/dashboard (ampliando respuesta con estructuras aptas para gráficos) o de endpoints de informes existentes.

---

## 5) Contratos de API

- Reutilizar GET /api/v1/dashboard. La respuesta ya puede incluir o ampliarse con:
  - `top_clientes` / agregación por cliente (para gráfico por cliente).
  - `top_empleados` / agregación por empleado (para gráfico por empleado, supervisor).
  - `distribucion_por_tipo` (para gráfico por tipo, cliente).
- Si hace falta, estructuras adicionales tipo `grafico_por_cliente`, `grafico_por_empleado`, `grafico_por_tipo` con arrays { label, value } o equivalente para la librería de gráficos.
- No es obligatorio crear endpoints nuevos si el dashboard ya devuelve suficientes agregaciones.

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **Dashboard:** Añadir uno o más componentes de gráfico (p. ej. `GraficoDistribucion`) que consuman datos del dashboard según rol. Empleado: un gráfico (por cliente). Supervisor: dos gráficos (por cliente, por empleado). Cliente: un gráfico (por tipo de tarea).
- **Librería:** Instalar y configurar Recharts o Chart.js (o la elegida); tema de colores consistente y accesible.
- **Estados:** Loading/error heredados del dashboard; estado vacío (sin datos para el gráfico) con mensaje.

### Estados UI
- Loading: mientras cargan datos del dashboard (los gráficos se pintan cuando llegan).
- Empty: si no hay datos para el gráfico, mostrar mensaje (HU-050).
- Success: gráfico renderizado con datos.

### Accesibilidad Mínima
- Atributos aria-label o role en contenedores de gráficos; colores con contraste suficiente; opcional: descripción textual del gráfico.
- data-testid: dashboard.graficoPorCliente, dashboard.graficoPorEmpleado, dashboard.graficoPorTipo (según rol).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Frontend | Instalar y configurar librería de gráficos | Recharts o Chart.js; tema de colores accesible. | — | S |
| T2 | Backend  | Ajustar respuesta dashboard para gráficos (si falta) | Estructuras por cliente, por empleado, por tipo aptas para gráficos (label, value). | TR-051 | S |
| T3 | Frontend | Componente GraficoDistribucion (reutilizable) | Acepta datos { label, value }[]; tipo bar/pie; responsive; aria-label. | T1 | M |
| T4 | Frontend | Integrar gráfico por cliente (empleado y supervisor) | Datos de top_clientes o equivalente; solo empleado/supervisor. | T3, TR-051 | M |
| T5 | Frontend | Integrar gráfico por empleado (solo supervisor) | Datos de top_empleados; solo supervisor. | T3, TR-051 | M |
| T6 | Frontend | Integrar gráfico por tipo de tarea (solo cliente) | Datos de distribucion_por_tipo; solo cliente. | T3, TR-051 | M |
| T7 | Frontend | Actualización de gráficos al cambiar período | Al cambiar selector de período, los gráficos se refrescan con los nuevos datos. | T4–T6 | S |
| T8 | Tests    | E2E gráficos por rol | Empleado ve 1 gráfico; supervisor ve 2; cliente ve 1; cambio de período actualiza. | T4–T7 | M |
| T9 | Docs     | Documentar librería y decisiones (accesibilidad) | README o docs/frontend. | T1 | S |

**Total:** 9 tareas.

---

## 8) Estrategia de Tests

- **Unit (frontend):** Componente de gráfico con datos mock; no falla con array vacío.
- **Integration:** Backend devuelve estructuras correctas para cada rol (si se modificó contrato).
- **E2E:** Por rol: número y tipo de gráficos correctos; cambio de período actualiza gráficos.

---

## 9) Riesgos y Edge Cases

- Sin datos en período: gráfico vacío con mensaje (HU-050).
- Muchos ítems en gráfico: limitar a top N o agrupar "Otros" para no saturar.
- Accesibilidad: asegurar contraste y texto alternativo.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Gráficos por rol según especificación (empleado/supervisor/cliente)
- [ ] Librería estándar; responsive; accesible
- [ ] Gráficos se actualizan al cambiar período
- [ ] E2E y docs actualizados

---

## Archivos creados/modificados

- **Frontend:** `package.json` (dependencia recharts); `src/app/GraficoDistribucion.tsx` (componente BarChart Recharts); `src/app/Dashboard.tsx` (integración de gráficos por rol); `src/app/Dashboard.css` (estilos .dashboard-grafico).

## Comandos ejecutados

- `npm install recharts --save` (frontend).

## Notas y decisiones

- Librería elegida: Recharts. Gráfico de barras (BarChart) para las tres variantes (por cliente, por empleado, por tipo).
- Gráficos solo se muestran cuando hay datos (length > 0); sección vacía no se renderiza.
- data-testid: dashboard.graficoPorCliente, dashboard.graficoPorEmpleado, dashboard.graficoPorTipo.

## Pendientes / follow-ups

- Ninguno.
