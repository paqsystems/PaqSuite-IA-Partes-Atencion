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
| **Estado** | Finalizado |
| **Última actualización** | 2026-08-01 |

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
- **(CC-PQ #1, 31/07)** Consulta detallada, agrupado y dashboard filtran `es_tarea = 1`; nuevo Informe **Paquete de Horas** (cuenta corriente): sin filtro `es_tarea`, saldo inicial + columna Saldo running (pivot sin Saldo).
- **(CC-PQ #2, 01/08)** Consulta detallada y agrupada incorporan `erpCliente` / `erpArticulo` en grilla y pivot.

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
| AC-14 | Detalle/agrupadas/dashboard excluyen filas `es_tarea = 0` |
| AC-15 | Paquete de Horas incluye tareas y compras del periodo (sin filtrar `es_tarea`) |
| AC-16 | Paquete de Horas: fila «Saldo inicial» con acumulado correcto (exclusive `fechaDesde`); columna Saldo running en grilla; pivot sin campo Saldo; filtro cliente disponible para asistente/supervisor |
| AC-17 | Consulta detallada muestra `erpCliente`/`erpArticulo` en grilla y pivot; agrupada los expone en grilla y pivot |

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
| RN-TR-09 | Consulta detallada, agrupado y dashboard: filtro implícito `es_tarea = 1`. |
| RN-TR-10 | Paquete de Horas: **no** filtra `es_tarea`; saldo inicial = movimientos con fecha &lt; `fechaDesde` (tarea suma, compra resta); orden de filas: fecha ASC, id ASC tras la fila «Saldo inicial»; columna Saldo running en grilla, excluida del pivot; SP `pq_sp_partes_informe_paquete_horas`. |
| RN-TR-11 | Detalle y agrupado incluyen `erpCliente` / `erpArticulo` (join a cliente) en la salida; grilla y pivot los exponen. |

---

## 4) Datos

### 4.1 Stored procedures

| SP | Uso |
|----|-----|
| `pq_sp_partes_tarea_list` | Consulta detallada (reuso TR-004; delimitación actor) |
| `pq_sp_partes_informe_agrupado` | Agregación: `@p_eje` ∈ {`cliente`,`asistente`,`tipo`,`fecha`}, `@p_granularidad_fecha` ∈ {`dia`,`mes`} (obligatorio si eje=fecha), filtros periodo + opcionales, delimitación |
| `pq_sp_partes_dashboard_snapshot` | Totales + top N: `@p_fecha_desde`, `@p_fecha_hasta`, `@p_top_n`, actor; resultsets: summary + rows top |
| `pq_sp_partes_informe_paquete_horas` | Cuenta corriente: `@p_fecha_desde`, `@p_fecha_hasta`, `@p_cliente_id`, actor; **no** filtra `es_tarea`; devuelve fila sintética «Saldo inicial» (`esSaldoInicial=true`) + movimientos con columna `saldo` running (fecha ASC, id ASC) |

**Salida agrupado (conceptual):** `{ ejeKey, ejeCodigo?, ejeDescripcion?, totalMinutos, cantidadTareas }[]` (+ total periodo opcional).

**Salida dashboard:** `{ totalMinutos, cantidadTareas, top: [{ clave, codigo?, descripcion?, totalMinutos, cantidadTareas }] }`.

**Salida Paquete de Horas (conceptual):** lista de filas con los mismos campos que consulta detallada (fecha, cliente, asistente, tipo, duración, marcas, `erpCliente`/`erpArticulo`) más `saldo` (minutos, running) y `esSaldoInicial` (bool) en la primera fila. Filtro `es_tarea` **no** aplica; `erpCliente`/`erpArticulo` disponibles igual que en detalle/agrupado.

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
| Informes | `partes_paquete_horas` | `/partes/informes/paquete-horas` | Todos (datos delimitados) |
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
| GET | `/partes/informes/paquete-horas` | Query: `fechaDesde`, `fechaHasta` **req**; `clienteId` **req** (cliente funcional usa el propio); sin filtro `es_tarea` → SP `pq_sp_partes_informe_paquete_horas` |

Errores: 422 fechas faltantes / eje inválido / granularidad faltante / `clienteId` faltante en Paquete de Horas; empty = **200** + `data: []` / ceros (no 404).

---

## 6) Frontend

| Pieza | Detalle |
|-------|---------|
| Consulta detallada | `/partes/informes/consulta-detallada`; **`ProcessDataGrid`** (plantillas + column chooser inherentes) + toggle Pivot; filtros; empty i18n; duración en **`hh:mm`**; columnas `erpCliente`/`erpArticulo`; **solo lectura** |
| Consultas agrupadas | `/partes/informes/consultas-agrupadas`; selector eje + granularidad fecha; **`ProcessDataGrid`** + Pivot; `totalMinutos` en **`hh:mm`**; `erpCliente`/`erpArticulo` en grilla y pivot |
| Paquete de horas | `/partes/informes/paquete-horas`; filtros `fechaDesde`/`fechaHasta`/cliente; **`ProcessDataGrid`** con fila «Saldo inicial» + columna Saldo running (**`hh:mm`**); toggle Pivot **sin** el campo Saldo; mismos atributos que detalle (incl. ERP) |
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
| T7 | Backend | Filtro `es_tarea=1` en detalle/agrupado/dashboard (CC-PQ #1) | AC-14 | M |
| T8 | Backend | SP/endpoint `pq_sp_partes_informe_paquete_horas` + saldo (CC-PQ #1) | AC-15/16 | L |
| T9 | Frontend | `PaqueteHorasPage`: grilla + Saldo running + pivot sin Saldo + filtros (CC-PQ #1) | AC-15/16 | L |
| T10 | Backend | Select/map `erpCliente`/`erpArticulo` en detalle y agrupado (CC-PQ #2) | AC-17 | M |
| T11 | Frontend | Columnas + pivot fields ERP en `PartesConsultasPages` (CC-PQ #2) | AC-17 | M |
| T12 | Tests | Feature/unit: `es_tarea` en informes, saldo Paquete de Horas, ERP en pivot | AC-14…17 | L |

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

- [ ] AC-01…17  
- [ ] SP + params + menú  
- [ ] FE 4 pantallas (detalle, agrupadas, Paquete de Horas, dashboard) + post-login + timer + pivot en Informes  
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
| 2026-07-31 | CC-PQ #1 (31/07/2026): detalle/agrupado/dashboard filtran `es_tarea=1` (AC-14, RN-TR-09, T7); nuevo Informe Paquete de Horas cuenta corriente vía `pq_sp_partes_informe_paquete_horas` (AC-15/16, RN-TR-10, T8/T9); [D-VERIFICACION-CC-PQ-01](../updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-01-2026-07-31.md). |
| 2026-08-01 | CC-PQ #2 (01/08/2026): detalle y agrupada incorporan `erpCliente`/`erpArticulo` en grilla y pivot (AC-17, RN-TR-11, T10/T11); [D-VERIFICACION-CC-PQ-02](../updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-02-2026-08-01.md). |
| 2026-08-01 | Parte I: fusionados TR-006-update (CC-PQ #1, 31/07) y TR-006-update-01 (CC-PQ #2, 01/08) en esta TR; updates eliminados. Estado → Finalizado. |

---

**Siguiente:** F1 de TR-006, o continuar TR-007.
