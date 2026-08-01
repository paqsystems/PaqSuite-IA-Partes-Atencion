# TR-006 – Consultas, dashboard y navegación

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-006-consultas-dashboard-navegacion](../../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) |
| **SPEC relacionada** | [SPEC-006-consultas-dashboard-navegacion](../../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Cliente / asistente / supervisor (delimitación SPEC-002) |
| **Dependencias** | [TR-002](./TR-002-identidad-funcional-y-acceso.md), [TR-003](./TR-003-maestros-y-catalogos.md) (menú Archivos), [TR-004](./TR-004-operacion-carga-diaria.md) (tareas), [TR-005](./TR-005-supervision-proceso-masivo.md) (ítem masivo menú) |
| **Clasificación** | HU COMPLEJA |
| **Estado** | En Control Calidad |
| **Última actualización** | 2026-07-31 (Parte G / CC-PQ) |

**Origen:** [HU-006](../../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md)  
**Referencia SPEC:** [SPEC-006](../../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md)

---

## 1) HU refinada (resumen)

### In scope
- Consulta detallada (solo lectura) + PivotGrid ofrecido.
- Consultas agrupadas: **una** pantalla + selector de eje (+ granularidad día/mes si eje fecha) + PivotGrid ofrecido.
- Dashboard MVP (totales, top N, periodo mes, refresh auto/manual); post-login → Dashboard.
- Menú `pq_menus` agrupado (Inicio / Archivos / Partes / Informes / Seguridad); FE no hardcodea árbol.
- Params `PartesDashboardTopN`, `PartesDashboardRefreshSeg`.
- i18n `partes.consulta.*` / `partes.dashboard.*` + testids.
- Delimitación servidor en todos los SP de lectura.

### Out of scope
- Export Excel Must; edición desde informes; BI avanzado; mobile kardex/pivot (TR-007); excepciones de refresh por rol.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | Cliente: solo su org en detalle, agrupadas, dashboard |
| AC-02 | Asistente no supervisor: solo su actividad |
| AC-03 | Supervisor: universo ampliado |
| AC-04 | Detallada: columnas mínimas + fechas obligatorias |
| AC-05 | Una pantalla agrupadas + ejes + métricas; fecha → día/mes |
| AC-06 | Empty → `partes.consulta.empty` (no 500) |
| AC-07 | Login OK web → `/partes` (Dashboard) |
| AC-08 | Dashboard mes actual: total tiempo, cantidad, top N (default 10) |
| AC-09 | Cambio periodo + refresh manual actualizan indicadores |
| AC-10 | Auto-refresh según param; `0` = off; timer solo con vista visible |
| AC-11 | Menú desde API/`pq_menus` (seed §4.7) |
| AC-12 | i18n + testids |
| AC-13 | Informes web (detalle + agrupadas) ofrecen Pivot; dashboard no; mobile no |

---

## 3) Reglas

R-CO-01…10.

| ID | Implementación |
|----|----------------|
| RN-TR-01 | Params programa **`Partes`**: **`PartesDashboardTopN`** default **`10`**; **`PartesDashboardRefreshSeg`** default **`60`** (`0` = auto off). |
| RN-TR-02 | Consulta detallada: **`fechaDesde`/`fechaHasta` obligatorios** (UI + API 422 si faltan); default UI = mes calendario actual (día 1 → último día). |
| RN-TR-03 | Columna **Asistente** en detallada: **siempre visible** (código + nombre) para todos los roles, incluida sesión asistente (fila propia). |
| RN-TR-04 | Dashboard periodo UI: control **mes** (default mes sistema) + opción **rango custom** (DateBox desde/hasta); métricas usan el rango efectivo. |
| RN-TR-05 | Pivot: toggle/vista **Grilla \| Pivot** en detalle y agrupadas (mismo dataset); dashboard sin pivot. |
| RN-TR-06 | Post-login web: destino home = ruta Dashboard **`/partes`**. |
| RN-TR-07 | Timer: `setInterval` solo montado en Dashboard; cleanup unmount; no poll en background. |
| RN-TR-08 | Lectura detallada reutiliza familia list TR-004 **o** endpoint informe dedicado con mismos filtros + delimitación (C1: endpoint informe `GET /partes/informes/tareas` → mismo SP `pq_sp_partes_tarea_list` con flag solo lectura; evita confundir con CRUD carga). |

---

## 4) Datos

### 4.1 Stored procedures

| SP | Uso |
|----|-----|
| `pq_sp_partes_tarea_list` | Consulta detallada (reuso TR-004; delimitación actor) |
| `pq_sp_partes_informe_agrupado` | Agregación: `@p_eje` ∈ {`cliente`,`asistente`,`tipo`,`fecha`}, `@p_granularidad_fecha` ∈ {`dia`,`mes`} (obligatorio si eje=fecha), filtros periodo + opcionales, delimitación |
| `pq_sp_partes_dashboard_snapshot` | Totales + top N: `@p_fecha_desde`, `@p_fecha_hasta`, `@p_top_n`, actor; resultsets: summary + rows top |

**Salida agrupado (conceptual):** `{ ejeKey, ejeCodigo?, ejeDescripcion?, totalMinutos, cantidadTareas }[]` (+ total periodo opcional).

**Salida dashboard:** `{ totalMinutos, cantidadTareas, top: [{ clave, codigo?, descripcion?, totalMinutos, cantidadTareas }] }`.

### 4.2 Seed parámetros

| Clave | Default | Notas |
|-------|---------|--------|
| `PartesDashboardTopN` | `10` | Entero ≥ 1; si seed inválido, backend clamp a 10 |
| `PartesDashboardRefreshSeg` | `60` | Entero ≥ 0 |

### 4.3 Seed menú (`pq_menus`) — agrupación

| Grupo | Código ítem (orientativo) | Ruta FE | Visibilidad tipica |
|-------|---------------------------|---------|-------------------|
| Inicio | `partes_dashboard` | `/partes` | Todos perfiles Partes |
| Archivos | (TR-003) | `/archivos/partes/...` | No cliente |
| Partes | `partes_carga_diaria` | `/partes/carga-diaria` | Asistente/supervisor |
| Partes | `partes_proceso_masivo` | `/partes/proceso-masivo` | Solo supervisor dominio + permiso |
| Informes | `partes_consulta_detallada` | `/partes/informes/consulta-detallada` | Todos (datos delimitados) |
| Informes | `partes_consultas_agrupadas` | `/partes/informes/consultas-agrupadas` | Todos |
| Seguridad | GEN usuarios/roles/permisos | rutas GEN | Rol supervisor Framework (`admin`/`PQ`) |

Cliente: sin Archivos, carga, masivo, Seguridad; sí Inicio + Informes + perfil avatar.

---

## 5) API

Base `/api/v1` — Bearer + tenant + perfil Partes.

| Método | Path | Notas |
|--------|------|-------|
| GET | `/partes/informes/tareas` | Query: `fechaDesde`, `fechaHasta` **req**; `clienteId?`, `usuarioId?` (solo efecto supervisor), `tipoTareaId?`, `estadoCerrado?`, `page`, `pageSize` → SP list |
| GET | `/partes/informes/agrupado` | Query: `eje` **req**, `granularidadFecha?` (req si eje=fecha), `fechaDesde`/`fechaHasta` **req**, filtros opcionales |
| GET | `/partes/dashboard` | Query: `fechaDesde`, `fechaHasta` **req** (o `mes=YYYY-MM` que el backend expande); lee top N y refresh no van en query — top N desde param en server; FE lee refresh vía bootstrap/param |
| GET | `/partes/parametros/dashboard` | `{ topN, refreshSeg }` desde GRAL (evita hardcode FE) |

Errores: 422 fechas faltantes / eje inválido / granularidad faltante; empty = **200** + `data: []` / ceros (no 404).

---

## 6) Frontend

| Pieza | Detalle |
|-------|---------|
| Consulta detallada | `/partes/informes/consulta-detallada`; **`ProcessDataGrid`** (plantillas + column chooser inherentes) + toggle Pivot; filtros; empty i18n; duración en **`hh:mm`**; **solo lectura** |
| Consultas agrupadas | `/partes/informes/consultas-agrupadas`; selector eje + granularidad fecha; **`ProcessDataGrid`** + Pivot; `totalMinutos` en **`hh:mm`** |
| Paquete de horas | Totales, **`ProcessDataGrid`** por cliente/tipo y tooltip/eje del chart en **`hh:mm`** |
| Dashboard | Ruta `/partes`; indicadores; top N en **`ProcessDataGrid`**; DateBox mes; botón refresh; timer; totales en **`hh:mm`**; **sin** Pivot |
| Post-login | Redirect a `/partes` |
| Layouts | Via **`ProcessDataGrid`** (`proceso` + `gridId`); no redefinir chooser/plantillas por menú |
| testids | `partesDashboardRoot`, `partesDashboardRefresh`, `partesConsultaDetalladaGrid`, `partesConsultaAgrupadaEje`, `partesInformePivotToggle`, … |
| i18n | `partes.consulta.*`, `partes.dashboard.*`, `partes.consulta.empty` |
| Mobile | Rutas informes/dashboard: comportamiento native en TR-007 (sin pivot; sin auto-timer) |

**Gráfico dashboard:** Must = números + lista top; gráfico DX simple **Should** (si no complica; no bloquea AC).

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1 | DB | SP agrupado + dashboard_snapshot | AC-01…05,08 | M |
| T2 | Seed | Params topN/refresh + menú Informes/Inicio | AC-10,11 | S |
| T3 | Backend | Endpoints informes + dashboard + params | AC-01…06 | M |
| T4 | Frontend | 3 pantallas + pivot toggle + post-login + timer | AC-07…13 | L |
| T5 | Tests | Feature delimitación/empty; Vitest periodo; E2E dashboard humo | Suite | L |
| T6 | Docs | OpenAPI | | S |

---

## 8) Tests

- Feature: cliente/asistente/supervisor delimitación en las 3 APIs; empty 200; 422 sin fechas; eje fecha sin granularidad → 422.
- Feature: dashboard top N respeta param; refreshSeg expuesto.
- Vitest: cálculo mes calendario default; cleanup timer unmount.
- E2E: login → `/partes`; empty state; toggle pivot visible en informe web.

---

## 9) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Race timer vs cambio periodo | Abort/ignore response stale (seq id o cancel token) |
| Pivot + dataset grande | Mismos filtros obligatorios de fecha; paginación en grilla; pivot sobre página o dataset acotado (patrón Framework) |
| Duplicar list CRUD vs informe | Path `/informes/tareas` separado; mismo SP |

---

## 10) Checklist

- [ ] AC-01…13  
- [ ] SP + params + menú  
- [ ] FE 3 pantallas + post-login + timer + pivot en Informes  
- [ ] Tests + OpenAPI  

---

## 11) Informe C1

# Revisión de ambigüedad - TR-006

## Resultado general
- **Apto con observaciones** (absorbidas)

## Críticas cerradas
- Params → **`PartesDashboardTopN`** (10), **`PartesDashboardRefreshSeg`** (60)
- SP → `pq_sp_partes_tarea_list` (detalle), **`pq_sp_partes_informe_agrupado`**, **`pq_sp_partes_dashboard_snapshot`**
- Fechas detallada → **obligatorias** (default UI = mes actual)
- Columna asistente → **siempre visible**
- Periodo dashboard UI → **mes + rango custom**
- Rutas → `/partes`, `/partes/informes/consulta-detallada`, `/partes/informes/consultas-agrupadas`
- Pivot → toggle Grilla\|Pivot en ambos Informes
- Empty → HTTP 200 + empty state (no error)

## Menores
- Gráfico dashboard: Should, no bloquea AC
- Bootstrap param dashboard vs endpoint dedicado: ambos OK; C1 fija **`GET /partes/parametros/dashboard`**

## Veredicto
- **Puede pasar a D1/D: Sí**

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte C + C1: TR consultas/dashboard/navegación; params/SP/rutas/pivot/fechas cerrados. |
| 2026-07-30 | Parte D: informes/dashboard API, menú Inicio/Informes, FE 3 pantallas + pivot + post-login `/partes`, Feature OK. |
| 2026-07-31 | Dashboard FE: total y columna top en `hh:mm` (`formatMinutosAsHhMm`). |
| 2026-07-31 | Informes FE: detalle/agrupadas/paquete horas — duración en `hh:mm`. |
| 2026-07-31 | Informes/Dashboard: grillas migradas a `ProcessDataGrid` (plantillas + column chooser inherentes; sin DataGrid ad hoc). |

---

**Siguiente:** F1 de TR-006, o continuar TR-007.
