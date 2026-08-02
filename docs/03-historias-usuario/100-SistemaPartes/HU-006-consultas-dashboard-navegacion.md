# HU-006 – Consultas, dashboard y navegación

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-006 |
| Título | Consultas, dashboard y navegación del módulo |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Finalizado |
| Última actualización | 2026-08-01 |
| SPEC origen | [SPEC-006-consultas-dashboard-navegacion](../../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |
| TR relacionada(s) | [TR-006-consultas-dashboard-navegacion](../../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-006 | Dónde en esta HU |
|--------------------------------|------------------|
| Delimitación por rol §3, R-CO-01 | CA-01, CA-02, CA-03 |
| Consulta detallada §4.2, R-CO-02 | Alcance; CA-04 |
| Consultas agrupadas §4.3, R-CO-03 | Alcance; CA-05 |
| Empty state §4.4, R-CO-04 | CA-06 |
| Dashboard MVP §4.5, R-CO-05 | Alcance; CA-08, CA-09 |
| Post-login → Dashboard §4.5 A1, R-CO-05b | CA-07 |
| Refresco auto + manual §4.5, R-CO-06 | CA-10 |
| Menú vía `pq_menus` §4.7, R-CO-07 | CA-11 |
| Pivot en Informes web con grilla §4.3, R-CO-09 | Alcance; CA-13 |
| Export fuera Must §2.2, R-CO-08 | Fuera de alcance |
| Acceso vía SP §4.8 R-CO-10 | Alcance backend |
| i18n + `data-testid` §2.1 | CA-12 |
| Parámetros top N y refresh A1 | CA-08, CA-10; R-CO-05, R-CO-06 |

---

## Narrativa

Como usuario del módulo Partes (cliente, asistente o supervisor)  
quiero consultar dedicación en detalle y agregados, un dashboard de entrada y navegación coherente con el shell Framework  
para analizar mi actividad o la de mi organización sin reescribir grillas ni layouts GEN, con datos delimitados por mi perfil.

---

## Contexto funcional

Tras registrar tareas (SPEC-004), el usuario necesita **lectura** de dedicación: consulta detallada (fila = registro), consultas agrupadas por ejes de negocio, un **dashboard MVP** como puerta analítica y **navegación** del módulo vía `pq_menus` (seed), sin hardcodear el árbol en frontend. La delimitación de datos replica SPEC-002 en servidor (SP MUST). Este SPEC prioriza **web**; mobile kardex y exclusiones pivot quedan en SPEC-007. Decisión A1: post-login web → Dashboard Partes (Inicio); top N del resumen y refresh automático vía `PQ_PARAMETROS_GRAL`.

---

## Alcance incluido

- **Consulta detallada** de tareas (solo lectura): columnas mínimas §4.2 (fecha, cliente, asistente, tipo, duración, marcas sin cargo/presencial, cerrado, observación, **Erp Cliente / Erp Articulo**); filtros mínimos (rango de fechas recomendado/obligatorio, cliente, asistente solo supervisor, tipo, cerrado). Solo registros con `esTarea = true`.
- **Consultas agrupadas:** **una sola pantalla** con selector de eje (cliente / asistente / tipo de tarea / fecha); métricas mínimas: suma `duracion_minutos` y conteo de tareas (**UI en `hh:mm`**); filtro de periodo obligatorio o default = mes calendario actual; PivotGrid ofrecido (Informe con grilla); incluye **Erp Cliente / Erp Articulo** en grilla y pivot. Solo registros con `esTarea = true`.
- **Informe Paquete de Horas** (cuenta corriente de horas por cliente): grilla/pivot con filtros `fechaDesde`/`fechaHasta`/cliente; **no** filtra por `esTarea` (incluye tareas y compras); mismos atributos que consulta detallada; fila sintética «Saldo inicial» (acumulado previo a `fechaDesde`: tarea suma, compra resta) y columna **Saldo** (running balance en grilla, excluida del pivot).
- **Presentación duración:** Dashboard, consulta detallada, agrupadas y paquete de horas muestran tiempo en **`hh:mm`** (API en minutos).
- **Delimitación por perfil** en todas las lecturas: cliente → solo su `cliente_id`; asistente no supervisor → solo su `usuario_id`; supervisor → universo ampliado.
- **Experiencia de resultado vacío** clara (`partes.consulta.empty`); no confundir con error técnico.
- **Dashboard MVP** web (solo registros con `esTarea = true`):
  - indicadores: total tiempo del periodo, cantidad de tareas, resumen principal (asistente/supervisor: top por cliente; cliente: top por asistente);
  - cantidad de ítems del resumen = parámetro `PQ_PARAMETROS_GRAL` (default **10**);
  - periodo inicial = mes calendario del sistema;
  - refresco automático según parámetro en segundos (default **60**; `0` = desactivado) + botón manual; timer solo con dashboard visible;
  - destino post-login web = Dashboard Partes (Inicio).
- **Navegación** vía entradas `pq_menus` + permisos; agrupación funcional sugerida §4.7 (Inicio, Archivos, Partes, Informes con Paquete de Horas); perfil de solo lectura vía menú avatar (SPEC-002), sin ruta dedicada Must.
- Reutilizar patrones Framework: DataGrid, layouts persistentes cuando apliquen; **PivotGrid ofrecido en todos los Informes web que muestran grilla** (detalle + agrupadas + Paquete de Horas, este último sin el campo Saldo en el pivot). Dashboard sin pivot obligatorio. Mobile sin pivot (SPEC-007).
- Acceso a datos vía stored procedures (R-CO-10).
- i18n + `data-testid` en dashboard y consultas.

---

## Fuera de alcance

- Alta/edición/baja de tareas (SPEC-004) y proceso masivo (SPEC-005), salvo deep-links de menú.
- ABM maestros (SPEC-003).
- **Exportación Excel como Must** del MVP base (evolución Framework cuando se habilite formalmente).
- BI avanzado, facturación, costeo.
- Mobile kardex / exclusiones pivot (SPEC-007); este SPEC prioriza web.
- Frecuencia de refresco parametrizable avanzada / excepciones por rol más allá del default MVP.
- UI de alta de compras de horas (`esTarea = false`); proceso a definir.
- Referencias ERP en Dashboard (no requerido); integración activa con ERP.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-CO-01 | Toda lectura delimitada por SPEC-002 en servidor (SP); filtros UI no amplían universo. |
| R-CO-02 | Consulta detallada = tareas individuales, solo lectura (edición en carga diaria). |
| R-CO-03 | Consultas agrupadas: **una pantalla** + selector de eje (cliente, asistente, tipo, fecha); suma duración y conteo; eje fecha con selector **día / mes**. |
| R-CO-04 | Resultado vacío ≠ error técnico; UX explícita en grillas, gráficos e indicadores. |
| R-CO-05 | Dashboard: total tiempo, cantidad de tareas, resumen top N por rol; N = parámetro GRAL (default 10); periodo inicial = mes calendario del sistema. |
| R-CO-05b | Tras login OK (web), navegar al Dashboard Partes (Inicio). |
| R-CO-06 | Refresco dashboard web: parámetro GRAL en segundos (default 60; `0` = off) + manual; timer solo con dashboard visible; al salir de la vista se detiene. Mobile: SPEC-007. |
| R-CO-07 | Menú vía `pq_menus`; agrupación §4.7 orienta seed; FE no hardcodea árbol de negocio. |
| R-CO-08 | Export Must fuera de MVP; si se habilite en el futuro, respeta universo + filtros. |
| R-CO-09 | Web: PivotGrid **ofrecido** en todo proceso Informes con grilla (detalle + agrupadas). Dashboard sin pivot obligatorio. Mobile: sin pivot. |
| R-CO-10 | Acceso a datos vía SP (MUST). |
| R-CO-11 | Consulta detallada, agrupadas y dashboard: solo `esTarea = true`. |
| R-CO-12 | Paquete de Horas **no** filtra por `esTarea` (incluye tareas y compras); fila «Saldo inicial» = movimientos con fecha &lt; `fechaDesde` (tarea suma, compra resta); columna Saldo = running balance en grilla, excluida del pivot; mismos atributos de detalle que consulta detallada; filtros periodo + cliente según rol. |
| R-CO-13 | Consulta detallada y agrupada exponen `erpCliente` / `erpArticulo` en grilla y pivot; valores vacíos/NULL no rompen la consulta. |

Visibilidad por rol en menú seed (orientativo): cliente sin Archivos ABM, sin carga, sin masivo; sí dashboard + informes de su org. Perfil vía avatar; no ítem de menú dedicado Must.

---

## Criterios de aceptación

- [ ] **CA-01** Cliente solo ve tareas y métricas de su organización en consulta detallada, agrupadas y dashboard.
- [ ] **CA-02** Asistente no supervisor solo ve su actividad en las mismas superficies.
- [ ] **CA-03** Supervisor ve universo ampliado (sin filtro por propietario propio) en detalle, agrupadas y dashboard.
- [ ] **CA-04** Consulta detallada muestra campos mínimos §4.2 y filtros de fecha (y filtros opcionales según rol).
- [ ] **CA-05** Existe **una** pantalla de consultas agrupadas con selector de eje (cliente, asistente, tipo, fecha) y métricas suma de duración + conteo; con eje fecha, selector de granularidad **día / mes**.
- [ ] **CA-06** Consulta sin datos muestra empty state claro (no 500 ni mensaje genérico de falla).
- [ ] **CA-07** Tras login OK web, el usuario aterriza en el Dashboard Partes (Inicio).
- [ ] **CA-08** Dashboard abre en mes calendario actual con total tiempo, cantidad de tareas y resumen principal (top N, default 10).
- [ ] **CA-09** Cambio de periodo actualiza indicadores; refresco manual también.
- [ ] **CA-10** Con dashboard visible y parámetro de refresh > 0, datos se actualizan según ese intervalo sin reload de app; si parámetro = 0, solo refresco manual.
- [ ] **CA-11** Ítems de menú Partes provienen de API menú / `pq_menus` (seed documentado); FE no hardcodea árbol.
- [ ] **CA-12** i18n + `data-testid` estables en dashboard y consultas.
- [ ] **CA-13** Todo proceso web de tipo Informes que muestra grilla (consulta detallada y agrupadas) ofrece PivotGrid; el Dashboard no lo exige; en mobile no hay pivot.
- [x] **CA-14** Detalle, agrupadas y dashboard excluyen registros con `esTarea = false`.
- [x] **CA-15** Paquete de Horas incluye tareas y compras del periodo (sin filtrar por `esTarea`).
- [x] **CA-16** Paquete de Horas muestra fila «Saldo inicial» con acumulado correcto (exclusive `fechaDesde`); columna Saldo corre en grilla; pivot no expone Saldo; filtro cliente disponible para asistente/supervisor.
- [x] **CA-17** Consulta detallada muestra Erp Cliente y Erp Articulo en grilla y pivot; consulta agrupada los expone en grilla y pivot.

---

## Escenarios Gherkin

```gherkin
Feature: Consultas, dashboard y navegación Partes
  Como usuario autenticado del módulo Partes
  Quiero consultar dedicación y un dashboard de entrada
  Para analizar actividad dentro de mi universo permitido

  Scenario: Cliente solo ve datos de su organización
    Given un cliente autenticado con "clienteId" de sesión
    When accede a consulta detallada, agrupadas o dashboard
    Then solo ve tareas y métricas de su organización
    And no ve tareas de otras organizaciones

  Scenario: Asistente ve solo su actividad
    Given un asistente no supervisor autenticado
    When consulta detalle o dashboard con periodo del mes actual
    Then solo ve registros donde él es propietario
    And no ve tareas de otros asistentes

  Scenario: Consulta sin datos muestra empty state
    Given un usuario con universo válido pero sin tareas en el periodo filtrado
    When carga consulta detallada o agrupada
    Then ve mensaje explícito de sin datos
    And no ve error técnico genérico ni código 500 por vacío

  Scenario: Post-login web aterriza en Dashboard
    Given un usuario Partes que completa login OK en web
    When el shell redirige al destino post-login
    Then aterriza en el Dashboard Partes (Inicio)

  Scenario: Dashboard refresco automático según parámetro
    Given el parámetro de refresh en segundos = 60
    And el usuario tiene el dashboard visible
    When transcurre el intervalo configurado
    Then los indicadores se actualizan sin recargar toda la app
    When navega fuera del dashboard
    Then el timer de refresco automático se detiene

  Scenario: Menú Partes desde API sin hardcode
    Given un usuario autenticado con permisos Partes
    When el shell carga el menú
    Then los ítems del módulo provienen de la API / "pq_menus"
    And la agrupación funcional sigue el seed documentado (Inicio, Archivos, Partes, Informes)

  Scenario: Informes y dashboard excluyen compras (es_tarea)
    Given existen registros con "esTarea" = true y con "esTarea" = false en el periodo consultado
    When un usuario consulta detalle, agrupadas o dashboard
    Then solo se consideran los registros con "esTarea" = true

  Scenario: Paquete de Horas como cuenta corriente
    Given un cliente con tareas (consumo) y compras de horas (reposición) en distintas fechas
    When un usuario consulta el Paquete de Horas con "fechaDesde" y "fechaHasta"
    Then la primera fila es "Saldo inicial" con el acumulado de movimientos anteriores a "fechaDesde"
    And cada fila siguiente muestra columna "Saldo" como saldo previo más o menos su duración según sea tarea o compra
    And el pivot del Paquete de Horas no incluye el campo "Saldo"

  Scenario: Referencias ERP visibles en informes
    Given un cliente con "erpCliente" y "erpArticulo" cargados
    When un usuario consulta detalle o agrupada
    Then ve "Erp Cliente" y "Erp Articulo" disponibles en grilla y en pivot
```

---

## Supuestos explícitos

- SPEC-002 y SPEC-004 desplegados; menú seed alineado con SPEC-003/005 para ítems de Archivos, Partes e Informes.
- Menú/permisos Framework pueden ocultar pantallas, pero **no** sustituyen la delimitación de datos en servidor.
- Gráficos simples en dashboard son opcionales si no complican el MVP; indicadores numéricos y resumen top N son Must.
- Layouts persistentes de grilla/pivot siguen criterio GEN cuando la pantalla lo soporte.
- Mobile hereda delimitación pero presentación y exclusiones en SPEC-007 (sin timer auto en dashboard native).

---

## Preguntas abiertas

- Uso concreto de PivotGrid: ~~cerrado~~ (ofrecido en todo Informe web con grilla).
- ¿Una o varias rutas/pantallas para consultas agrupadas por eje?: ~~pendiente~~ → **cerrado:** una sola pantalla + selector de eje.
- Granularidad de fecha en consultas agrupadas: ~~pendiente~~ → **cerrado:** selector explícito **día / mes**.
- Params / SP / fechas detallada / columna asistente / rutas: ~~pendiente~~ → **cerrado en [TR-006](../../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md):** `PartesDashboardTopN` (10), `PartesDashboardRefreshSeg` (60); SP list + `pq_sp_partes_informe_agrupado` + `pq_sp_partes_dashboard_snapshot`; fechas **obligatorias** (default mes); columna asistente **siempre visible**; rutas `/partes` + informes.

---

## Riesgos de ambigüedad

- Fechas detallada y columna asistente: ~~pendiente~~ → **cerrado en TR-006** (obligatorias + columna siempre visible).
- Edición desde consulta detallada: prohibida (solo lectura); deep-links menú a carga/masivo no habilitan edición en informes.
- Timer vs cambio de periodo: TR-006 exige cancel/seq para respuestas stale.

---

## Dependencias

- [SPEC-002](../../05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md) — delimitación por perfil.
- [SPEC-004](../../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) — fuente de datos de tareas.
- Menú seed alineado a SPEC-003 (ABM) y SPEC-005 (proceso masivo) para agrupación §4.7.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte B + B1: HU creada y enriquecida desde SPEC-006. |
| 2026-07-30 | Batch: PivotGrid ofrecido en todo Informe web con grilla. |
| 2026-07-30 | Batch: consultas agrupadas = una pantalla + selector de eje. |
| 2026-07-30 | Batch: eje fecha = selector granularidad día/mes. |
| 2026-07-30 | Enlace TR-006 (Parte C+C1); preguntas param/SP/UI cerradas. |
| 2026-07-31 | CC-PQ #1 (31/07/2026): detalle, agrupadas y dashboard filtran `esTarea = true` (CA-14, R-CO-11); nuevo Informe Paquete de Horas como cuenta corriente (CA-15/16, R-CO-12). |
| 2026-08-01 | CC-PQ #2 (01/08/2026): consulta detallada y agrupada incorporan Erp Cliente / Erp Articulo en grilla y pivot (CA-17, R-CO-13). |
| 2026-08-01 | Parte I: fusionados HU-006-update (CC-PQ #1, 31/07) y HU-006-update-01 (CC-PQ #2, 01/08) en esta HU; updates eliminados. Estado → Finalizado. |
