# SPEC-006 – Consultas, dashboard y navegación

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-006 |
| Título | Consultas, dashboard y navegación del módulo |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-07-30 |
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

- Consulta detallada de tareas (fila = registro) con filtros y columnas mínimas §4.2.
- Consultas agrupadas: **una pantalla** + selector de eje (cliente, asistente, tipo, fecha).
- Delimitación por perfil (cliente / asistente / supervisor) en todas las lecturas.
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

**Filtros mínimos:** rango de fechas (recomendado obligatorio para no volcar histórico completo); cliente; asistente (solo supervisor); tipo de tarea; cerrado (opcional).

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
- **Presentación duración (cerrado 2026-07-31):** en consulta detallada, agrupadas, paquete de horas y dashboard, celdas/totales/tooltips de tiempo en **`hh:mm`** (coherente SPEC-004); API sigue en minutos.
- **Web — Pivot:** en **todos** los procesos de menú tipo **Informes** que muestran grilla (consulta detallada y consultas agrupadas), la UI **ofrece PivotGrid** (modo/vista pivot disponible junto al patrón grilla Framework). No es “opcional por ruta”: si hay grilla de informe, hay pivot.
- **Grilla de proceso:** toda grilla de listado/consulta web usa **`ProcessDataGrid`** (regla BASE 29): column chooser, plantillas/layouts, filtros, agrupación y totalizadores vienen **por el componente**, no se reimplementan por menú. `proceso` + `gridId` identifican la plantilla; no usar `DataGrid` crudo en pantallas de proceso.
- **Dashboard** (Inicio): no es proceso Informe con grilla de listado; no exige PivotGrid (sí usa `ProcessDataGrid` en el top).
- Mobile: sin pivot (SPEC-007).

### 4.4 Resultados vacíos

- Texto claro: no hay datos para el contexto/filtros.
- No mostrar error técnico genérico por vacío.
- Gráficos/indicadores en cero o estado empty coherente.

### 4.5 Dashboard MVP

- **Propósito:** puerta de entrada analítica; **destino post-login** web = esta pantalla (Inicio / Dashboard).

#### Indicadores mínimos

| Indicador | Descripción |
|-----------|-------------|
| Total tiempo del periodo | Suma `duracion_minutos` del universo del rol; **UI:** presentación en **`hh:mm`** (API sigue en minutos) |
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
| Informes | Consulta detallada; consultas agrupadas (1 pantalla + eje) | Todos según delimitación |
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

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | SP de listado detallado, agregaciones y snapshot dashboard; leer top N desde `PQ_PARAMETROS_GRAL` (default 10) |
| Frontend | Páginas informes + dashboard; DataGrid + **PivotGrid en todo Informe con grilla**; timer según parámetro refresh |
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

---

**Trazabilidad:** [HU-006](../../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md). Siguiente previsto: **SPEC-007 mobile**.
