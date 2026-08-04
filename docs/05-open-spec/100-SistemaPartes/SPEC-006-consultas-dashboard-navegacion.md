# SPEC-006 – Consultas, dashboard y navegación

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-006 |
| Título | Consultas, dashboard y navegación del módulo |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Finalizado |
| Última actualización | 2026-08-01 |
| HU relacionada(s) | [HU-006-consultas-dashboard-navegacion](../../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) |
| TR relacionada(s) | [TR-006-consultas-dashboard-navegacion](../../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) |
| Depende de | [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md), [SPEC-004](./SPEC-004-operacion-carga-diaria.md); menú seed alineado a SPEC-003/005 |
| Fuentes | [`06-consultas-dashboard-y-navegacion.md`](../../02-producto/Sistema-Partes-IA/06-consultas-dashboard-y-navegacion.md), [`01-vision-y-alcance.md`](../../02-producto/Sistema-Partes-IA/01-vision-y-alcance.md) |

---

## 1. Resumen ejecutivo

- **Problema:** tras registrar tareas, el usuario necesita leer dedicación (detalle y agregados), un dashboard por rol y una navegación coherente con el shell Framework — sin reescribir grillas/layouts GEN.
- **Resultado esperado:** contrato de **consulta detallada**, **consultas agrupadas**, **dashboard MVP** y **agrupación funcional de menú** (`pq_menus`), con la misma delimitación de datos que SPEC-002.

---

## 2. Alcance

### 2.1 En alcance

- Consulta detallada de tareas (fila = registro) con filtros y columnas mínimas §4.2; incluye referencias ERP del cliente (`erpCliente`, `erpArticulo`).
- Consultas agrupadas: **una pantalla** + selector de eje (cliente, asistente, tipo, fecha); incluye referencias ERP en grilla y pivot.
- **Informe Paquete de Horas** (cuenta corriente de horas por cliente): grilla/pivot con saldo inicial y saldo corriente (§4.3b).
- Delimitación por perfil (cliente / asistente / supervisor) en todas las lecturas.
- Delimitación implícita por `es_tarea = true` en consulta detallada, consultas agrupadas y dashboard (no en Paquete de Horas, ver §4.3b).
- Experiencia de **resultado vacío** clara (sin confundir con error técnico).
- Dashboard de entrada analítica: indicadores mínimos, periodo inicial = mes calendario del sistema, refresco automático + manual.
- Navegación del módulo vía `pq_menus` (seed); agrupación funcional sugerida §4.7 (no menú hardcodeado en FE).
- Reutilizar patrones Framework: DataGrid, layouts persistentes cuando apliquen; **PivotGrid ofrecido en todos los procesos de tipo Informes web que muestran grilla** (consulta detallada y consultas agrupadas). Mobile: sin pivot (SPEC-007).
- i18n + `data-testid`.

### 2.2 Fuera de alcance

- Alta/edición/baja de tareas (SPEC-004) y proceso masivo (SPEC-005), salvo deep-links de menú.
- ABM maestros (SPEC-003).
- **Exportación Excel como Must** del MVP base (evolución Framework cuando se habilite formalmente).
- BI avanzado, facturación, costeo.
- Mobile kardex / exclusiones pivot (detalle en SPEC-007); este SPEC prioriza **web**.
- Frecuencia de refresco parametrizable avanzada / excepciones por rol más allá del default MVP §4.5.
- UI de alta de compras de horas (`es_tarea = false`); proceso a definir (SPEC-001).
- Referencias ERP en Dashboard (no requerido) ni integración activa con ERP.

---

## 3. Actores y contexto

| Actor | Universo de lectura |
|-------|---------------------|
| Cliente | Solo `cliente_id = clienteId` de sesión |
| Asistente no supervisor | Solo `usuario_id = asistenteId` |
| Supervisor | Universo supervisor (sin filtro por propietario propio) |

Menú/permisos Framework pueden ocultar pantallas; **no** sustituyen esta capa.

---

## 4. Comportamiento funcional

### 4.1 Principios comunes de consulta

- Toda API de lectura aplica delimitación §3 en servidor (SP MUST).
- Filtros de UI acotan dentro del universo permitido; no amplían.
- Resultado vacío: mensaje i18n explícito (`partes.consulta.empty`); no se presentan acciones que requieran filas (p. ej. drill-down) como si hubiera datos.
- Si en el futuro hay export: botón puede permanecer visible pero **disabled** sin filas exportables; el export respeta filtros + universo (cuando se habilite).
- Layouts de grilla/pivot: criterio GEN de layouts persistentes cuando la pantalla lo soporte.

### 4.2 Consulta detallada

**Objetivo:** ver cada tarea individual con trazabilidad.

**Columnas / campos mínimos visibles (según rol; ocultar lo que no aporte):**

| Campo | Notas |
|-------|--------|
| Fecha | Fecha de negocio |
| Cliente | Código + nombre |
| Asistente | Código + nombre (cliente puede verlo; asistente propio puede ocultarse o mostrarse fijo) |
| Tipo de tarea | Código + descripción |
| Duración | Presentación **`hh:mm`** (API/persistencia en minutos) |
| Sin cargo / Presencial | Marcas |
| Cerrado | Estado |
| Observación | Texto (truncar en grilla + detalle/tooltip) |
| Erp Cliente / Erp Articulo | `erpCliente` / `erpArticulo` del cliente de la fila (grilla y pivot); vacíos si el cliente no los tiene cargados |

**Filtros mínimos:** rango de fechas (recomendado obligatorio para no volcar histórico completo); cliente; asistente (solo supervisor); tipo de tarea; cerrado (opcional).

**Delimitación de datos:** solo registros con `es_tarea = true` (SPEC-001 R-MD-12); las compras/movimientos de paquete de horas no aparecen aquí (ver Paquete de Horas, §4.3b).

**Modo:** solo lectura (no editar desde esta pantalla en MVP; la edición vive en carga diaria).

### 4.3 Consultas agrupadas

**Objetivo:** patrones de dedicación, no solo listado.

**Composición UI (cerrado):** **una sola pantalla** de consultas agrupadas con **selector de eje** (cliente / asistente / tipo de tarea / fecha). Un ítem de menú Informes; no una ruta por eje.

Al menos esa superficie debe permitir agregar tiempo / cantidad de tareas por:

| Eje | Métrica mínima |
|-----|----------------|
| Cliente | Suma `duracion_minutos`, conteo tareas — UI suma/celda en **`hh:mm`** |
| Asistente | Idem (cliente: eje asistente permitido dentro de su org) |
| Tipo de tarea | Idem |
| Fecha | Por día o por mes según **selector de granularidad** explícito (día / mes) del usuario |

- Filtro de periodo obligatorio o con default = mes calendario actual.
- **Delimitación de datos:** solo registros con `es_tarea = true` (SPEC-001 R-MD-12); igual que consulta detallada.
- **Referencias ERP:** `erpCliente` / `erpArticulo` disponibles en grilla y pivot, en especial con eje cliente; en otros ejes se exponen como campos del cliente de la fila/agregación (implementación TR).
- **Presentación duración (cerrado 2026-07-31):** en consulta detallada, agrupadas, paquete de horas y dashboard, celdas/totales/tooltips de tiempo en **`hh:mm`** (coherente SPEC-004); API sigue en minutos.
- **Web — Pivot:** en **todos** los procesos de menú tipo **Informes** que muestran grilla (consulta detallada, consultas agrupadas y Paquete de Horas), la UI **ofrece PivotGrid** (modo/vista pivot disponible junto al patrón grilla Framework). No es “opcional por ruta”: si hay grilla de informe, hay pivot (salvo exclusión explícita de un campo, ver §4.3b).
- **Grilla de proceso:** toda grilla de listado/consulta web usa **`ProcessDataGrid`** (regla BASE 29): column chooser, plantillas/layouts, filtros, agrupación y totalizadores vienen **por el componente**, no se reimplementan por menú. `proceso` + `gridId` identifican la plantilla; no usar `DataGrid` crudo en pantallas de proceso.
- **Dashboard** (Inicio): no es proceso Informe con grilla de listado; no exige PivotGrid (sí usa `ProcessDataGrid` en el top).
- Mobile: sin pivot (SPEC-007).

### 4.3b Informe Paquete de Horas (cuenta corriente)

**Objetivo:** cuenta corriente de horas para clientes con paquetes de horas anticipados (contratados por adelantado); permite ver consumo (tareas) y reposición (compras) con saldo corriente.

- UI: grilla / pivot (framework), mismo patrón `ProcessDataGrid` + PivotGrid que el resto de Informes.
- Filtros: `fechaDesde`, `fechaHasta`, **cliente** (obligatorio acotar; asistente/supervisor eligen cliente, cliente funcional ve su propia org).
- Columnas: **mismos atributos que consulta detallada / carga diaria** (fecha, cliente, asistente, tipo de tarea, duración, sin cargo, presencial, cerrado, observación).
- **No filtra por `es_tarea`**: incluye tanto tareas (`es_tarea = true`) como compras/movimientos de paquete (`es_tarea = false`), a diferencia de consulta detallada/agrupadas/dashboard.
- **Fila sintética «Saldo inicial»:** representa el acumulado de movimientos con fecha **anterior** a `fechaDesde` (exclusive):
  - `es_tarea = true` (tarea) → **suma** `duracion_minutos` (consume del paquete).
  - `es_tarea = false` (compra) → **resta** `duracion_minutos` (repone el paquete).
- **Columna Saldo** (running balance): en la fila «Saldo inicial» = acumulado previo a `fechaDesde`; en cada fila siguiente = saldo previo ± minutos de esa fila (misma regla suma/resta), ordenadas por fecha (+ id) ascendente tras la fila de saldo inicial.
- **En Pivot:** el campo/atributo **Saldo no se incluye** (no tiene sentido en una tabla pivote); el resto de atributos de detalle sí está disponible para el pivot.
- Presentación de duración en **`hh:mm`** (API/persistencia en minutos), igual que el resto de Informes.
- **Fuera de alcance:** UI de alta de compras de horas (`es_tarea = false`); sin ese proceso, el saldo solo reflejará sumas (+) hasta que existan movimientos de compra.

### 4.4 Resultados vacíos

- Texto claro: no hay datos para el contexto/filtros.
- No mostrar error técnico genérico por vacío.
- Gráficos/indicadores en cero o estado empty coherente.

### 4.5 Dashboard MVP

- **Propósito:** puerta de entrada analítica; **destino post-login** web = esta pantalla (Inicio / Dashboard).

#### Indicadores mínimos

| Indicador | Descripción |
|-----------|-------------|
| Total tiempo del periodo | Suma `duracion_minutos` del universo del rol, solo `es_tarea = true`; **UI:** presentación en **`hh:mm`** (API sigue en minutos) |
| Cantidad de tareas | Conteo en el periodo |
| Resumen principal | Asistente/supervisor: top por **cliente**; Cliente: top por **asistente** (default). Cantidad de ítems = parámetro `PQ_PARAMETROS_GRAL` clave **`PartesDashboardTopN`** (programa `Partes`; fijada en TR-006); **default 10**. Columna de tiempo del top en **`hh:mm`**. |

Gráficos simples opcionales si no complican el MVP.

**Presentación de duración (cerrado 2026-07-31):** coherente con carga diaria (SPEC-004): totales y columnas de tiempo del Dashboard en **`hh:mm`**; persistencia/API en minutos.

#### Periodo

- Apertura inicial: **mes calendario** de la fecha del sistema (día 1 → último día del mes).
- Usuario puede cambiar el periodo (mes o rango; TR fija control UI).
- Toda métrica respeta periodo + delimitación §3.

#### Refresco

- **Automático:** controlado por parámetro `PQ_PARAMETROS_GRAL` clave **`PartesDashboardRefreshSeg`** (programa `Partes`; fijada en TR-006); valor en **segundos**.
  - **Default: 60**
  - **`0`:** auto desactivado (solo refresco manual)
  - Aplica a **todos** los roles con dashboard web (sin excepción por rol en MVP)
- **Manual:** botón refrescar inmediato (siempre disponible).
- Auto y manual aplican los mismos filtros/rol/periodo.
- Al ocultar la vista (navegación fuera): se detiene el timer (no poll en background de otras pantallas).
- Mobile: sin timer; solo manual/pull (SPEC-007).

### 4.6 Perfil

- Acceso al perfil de solo lectura (SPEC-002) desde el **menú del avatar** (panel/modal); sin ruta dedicada Must.

### 4.7 Navegación y menú (`pq_menus`)

- Las opciones del módulo se materializan como entradas `pq_menus` + permisos; el FE **no** hardcodea el árbol de negocio como fuente de verdad.
- Agrupación funcional **sugerida** para seed:

| Grupo | Ítems orientativos | Quién tipicamente |
|-------|--------------------|-------------------|
| Inicio | Dashboard | Todos los perfiles Partes |
| Archivos | Clientes, asistentes, tipos cliente, tipos tarea, asignaciones | Quienes tengan permiso menú ABM (seed: rol supervisor → `admin`/`PQ`) |
| Partes | Carga diaria; proceso masivo (pantalla + atajo desde carga) | Asistente/supervisor; masivo solo supervisor de dominio |
| Informes | Consulta detallada; consultas agrupadas (1 pantalla + eje); Paquete de Horas | Todos según delimitación |
| Seguridad | Usuarios, roles, permisos (GEN) | Rol supervisor / permisos seguridad (MVP incluido) |

- Perfil vía avatar (SPEC-002); no requiere ítem de menú dedicado Must.
- Cliente: sin Archivos ABM, sin carga, sin masivo, sin Seguridad; sí dashboard + informes de su org (+ perfil vía avatar).
- Mobile: filtrar exclusiones (pivot, ABM, masivo, seguridad admin) en SPEC-007 / policy.
- Seed: `admin` y `PQ` con rol Framework **supervisor** (permisos ABM Partes + seguridad GEN) y `PQ_PARTES_USUARIOS.supervisor = 1`.

### 4.8 Reglas numeradas

| ID | Regla |
|----|--------|
| R-CO-01 | Lecturas delimitadas por SPEC-002 en servidor. |
| R-CO-02 | Consulta detallada = tareas individuales, solo lectura. |
| R-CO-03 | Agrupadas: **una pantalla** + selector de eje (cliente, asistente, tipo, fecha); métricas suma duración y conteo. Eje fecha: selector **día / mes**. |
| R-CO-04 | Vacío ≠ error técnico; UX explícita. |
| R-CO-05 | Dashboard: tiempo total, cantidad, resumen top N por rol; N = parámetro GRAL (default 10); periodo inicial = mes sistema. |
| R-CO-05b | Tras login OK (web), navegar al Dashboard Partes (Inicio). |
| R-CO-06 | Refresco dashboard web: parámetro GRAL en segundos (default 60; 0 = off) + manual; solo con dashboard visible. Mobile: SPEC-007. |
| R-CO-07 | Menú vía `pq_menus`; incluye Seguridad GEN en MVP; agrupación §4.7 orienta seed. |
| R-CO-08 | Export Must fuera de MVP; si se habilita, respeta universo+filtros. |
| R-CO-09 | Web: PivotGrid **ofrecido** en todo proceso Informes con grilla (detalle + agrupadas). Dashboard sin pivot obligatorio. Mobile: sin pivot. |
| R-CO-10 | Acceso datos vía SP (MUST). |
| R-CO-11 | Consulta detallada, agrupadas y dashboard: solo `es_tarea = 1`. |
| R-CO-12 | Paquete de Horas **no** filtra por `es_tarea` (incluye tareas y compras); fila «Saldo inicial» = movimientos con fecha &lt; `fechaDesde` (tarea suma, compra resta); columna Saldo = running balance en grilla, excluida del pivot; mismos atributos de detalle que consulta detallada; filtros periodo + cliente según rol. |
| R-CO-13 | Consulta detallada y agrupada exponen `erpCliente` / `erpArticulo` en grilla y pivot; valores vacíos/NULL no rompen la consulta. |

---

## 5. Criterios verificables

- [ ] Cliente solo ve tareas/métricas de su organización en detalle, agrupadas y dashboard.
- [ ] Asistente no supervisor solo ve su actividad en las mismas superficies.
- [ ] Supervisor ve universo ampliado.
- [ ] Consulta detallada muestra campos mínimos §4.2 y filtros de fecha.
- [ ] Existe agregación por cliente, asistente, tipo y fecha en **una** pantalla con selector de eje (suma duración + conteo).
- [ ] Consulta sin datos muestra empty state claro (no 500 / no mensaje de falla genérica).
- [ ] Tras login OK web, el usuario aterriza en el Dashboard Partes.
- [ ] Dashboard abre en mes calendario actual con total tiempo, cantidad y resumen.
- [ ] Cambio de periodo actualiza indicadores; refresco manual también.
- [ ] Con dashboard visible y parámetro > 0, datos se actualizan según ese intervalo sin reload de app; si parámetro = 0, solo manual.
- [ ] Ítems de menú Partes provienen de API menú / `pq_menus` (seed documentado).
- [ ] i18n + `data-testid` en dashboard y consultas.
- [ ] Informes web con grilla (detalle + agrupadas) ofrecen PivotGrid; dashboard no lo exige; mobile sin pivot.
- [x] Detalle, agrupadas y dashboard ignoran filas `es_tarea = 0`.
- [x] Paquete de Horas muestra tareas y compras del periodo; fila «Saldo inicial» con acumulado correcto (exclusive `fechaDesde`); columna Saldo corre en grilla; pivot no expone Saldo; filtro cliente disponible para asistente/supervisor.
- [x] Detallada: grilla y pivot muestran Erp Cliente y Erp Articulo; agrupada los expone en grilla y pivot.

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | SP de listado detallado, agregaciones y snapshot dashboard (filtro `es_tarea = 1`); SP de Paquete de Horas (detalle + saldo, sin filtro `es_tarea`); ambos exponen `erpCliente` / `erpArticulo`; leer top N desde `PQ_PARAMETROS_GRAL` (default 10) |
| Frontend | Páginas informes + dashboard + Paquete de Horas; DataGrid + **PivotGrid en todo Informe con grilla** (Paquete de Horas sin campo Saldo en pivot); timer según parámetro refresh |
| Seed | `pq_menus` (Inicio/Archivos/Partes/Informes/Seguridad); parámetros top N (10) y refresh (60); rol supervisor → `admin`/`PQ` |
| Tests | Feature delimitación por rol; Vitest periodo mes; E2E empty + dashboard humo |

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| Frecuencia auto web | **Cerrado:** parámetro GRAL segundos (default 60; 0 = off); sin excepción por rol. |
| Resumen top N | **Cerrado:** parámetro GRAL (default 10). |
| Export | Fuera Must; no bloquea |
| Pivot en Informes web | **Cerrado:** ofrecido en todo proceso Informes con grilla; no en dashboard; no en mobile |
| Consultas agrupadas UI | **Cerrado:** una pantalla + selector de eje; eje fecha = selector día/mes |
| Home post-login = dashboard | **Cerrado:** tras login OK → **Dashboard** Partes (Inicio), como en el resto de productos PaqSuite. |
| Sin proceso de alta de compras | El saldo del Paquete de Horas solo reflejará tareas (+) hasta que exista alta de compras (`es_tarea = 0`); dependencia SPEC-001. |
| Orden de filas Paquete de Horas | Por fecha (+ id) ascendente tras la fila «Saldo inicial». |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Versión inicial (Parte A) — consultas, dashboard, menú. |
| 2026-07-30 | A1: apto con observaciones (top N del resumen; export diferido). Frecuencia auto = 60 s. |
| 2026-07-30 | A1 cierre: top N dashboard = parámetro GRAL default 10. |
| 2026-07-30 | A1 cierre: refresh dashboard web = parámetro GRAL segundos (default 60; 0 = off). |
| 2026-07-30 | A1 cierre: post-login → Dashboard Partes (Inicio). |
| 2026-07-30 | Batch HU: menú Seguridad GEN en MVP; seed rol supervisor `admin`/`PQ`. |
| 2026-07-30 | Batch HU: PivotGrid ofrecido en todo Informe web con grilla. |
| 2026-07-30 | Batch HU: consultas agrupadas = una pantalla + selector de eje. |
| 2026-07-30 | Batch HU: eje fecha = selector granularidad día/mes. |
| 2026-07-30 | Enlace TR-006 (Parte C+C1); claves param `PartesDashboardTopN` / `PartesDashboardRefreshSeg`. |
| 2026-07-31 | Dashboard: totales y top en presentación **`hh:mm`** (coherente SPEC-004; API minutos). |
| 2026-07-31 | Informes (detalle, agrupadas, paquete horas): duración en **`hh:mm`** en grilla/pivot/totales/tooltip. |
| 2026-07-31 | Grillas de Informes/Dashboard = **`ProcessDataGrid`** (plantillas y column chooser inherentes). |
| 2026-07-31 | CC-PQ #1 (31/07/2026): consulta detallada, agrupadas y dashboard filtran `es_tarea = 1` (R-CO-11); nuevo Informe **Paquete de Horas** como cuenta corriente (saldo inicial + columna Saldo running, sin Saldo en pivot) (R-CO-12; §4.3b). |
| 2026-08-01 | CC-PQ #2 (01/08/2026): consulta detallada y agrupada incorporan `erpCliente` / `erpArticulo` en grilla y pivot (R-CO-13). |
| 2026-08-01 | Parte I: fusionados SPEC-006-update (CC-PQ #1, 31/07) y SPEC-006-update-01 (CC-PQ #2, 01/08) en este original; updates eliminados. Estado → Finalizado. |

---

**Trazabilidad:** [HU-006](../../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md). Siguiente previsto: **SPEC-007 mobile**.
