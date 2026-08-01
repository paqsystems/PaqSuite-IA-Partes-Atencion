# SPEC-006-update – Filtro `es_tarea` en consultas/dashboard y rehacer Paquete de Horas

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-006-update |
| Título | Informes/dashboard con `es_tarea` y Paquete de Horas como cuenta corriente |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-07-31 |
| SPEC base | [SPEC-006-consultas-dashboard-navegacion](../../100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |
| HU relacionada(s) | [HU-006-consultas-dashboard-navegacion-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-006-consultas-dashboard-navegacion-update.md) |
| TR relacionada(s) | [TR-006-consultas-dashboard-navegacion-update](../../../04-tareas/updates/100-SistemaPartes/TR-006-consultas-dashboard-navegacion-update.md) |
| Origen | `00-ControlCalidad-PQ` · fecha **31/07/2026** · Control #1 · ítems informes/`EsTarea` + «Paquete de Horas: Rehacer» + hallazgo cuenta corriente |
| Depende de | [SPEC-001-update](./SPEC-001-modelo-datos-modulo-update.md) |

---

## 1. Resumen ejecutivo

- **Problema:** hace falta distinguir tareas de compras de horas en analítica, y el Paquete de Horas debe funcionar como **cuenta corriente** de minutos/horas por cliente.
- **Resultado esperado:** consulta detallada, agrupadas y dashboard filtran `es_tarea = true`; Paquete de Horas se rehace con movimientos detalle + saldo inicial + columna Saldo (sin Saldo en pivot).

---

## 2. Alcance

### 2.1 En alcance

#### A) Filtro `es_tarea` en lecturas operativas

- **Consulta detallada**, **consultas agrupadas** y **Dashboard:** solo registros con `es_tarea = true`.

#### B) Rehacer Informe Paquete de Horas

- Objetivo: cuenta corriente de horas para clientes con paquetes anticipados.
- UI: grilla / pivot (framework).
- Filtros: `fechaDesde`, `fechaHasta`, **cliente** (asistente/supervisor; cliente funcional = su org).
- Columnas: **mismos atributos que Carga / consulta detallada**.
- **No** filtrar por `es_tarea` (incluye tareas y compras).
- Fila sintética **«Saldo inicial»**: suma/resta de `duracion_minutos` con fecha **&lt; fechaDesde** (exclusive).
  - `es_tarea = true` → **suma**
  - `es_tarea = false` → **resta**
- Columna **Saldo**: en saldo inicial = acumulado anterior; en cada fila = saldo previo ± minutos de la fila (misma regla suma/resta).
- En **Pivot:** **no** incluir el campo/atributo Saldo.

### 2.2 Fuera de alcance

- Alta UI de compras de horas (`es_tarea = false`) — proceso a definir (dependencia de datos).
- Cambios de carga/masivo salvo el consumo del campo (SPEC-004/005-update).

---

## 3. Reglas

| ID | Regla |
|----|--------|
| R-CO-ES-01 | Detalle, agrupadas y dashboard: solo `es_tarea = 1`. |
| R-CO-PH-01 | Paquete de Horas no filtra por `es_tarea`. |
| R-CO-PH-02 | Saldo inicial = movimientos con fecha &lt; fechaDesde; + si tarea, − si compra. |
| R-CO-PH-03 | Columna Saldo = running balance en grilla; excluida del pivot. |
| R-CO-PH-04 | Filtros periodo + cliente según rol. |
| R-CO-PH-05 | Mismos atributos de detalle que consulta detallada. |

---

## 4. Criterios verificables

- [x] Detalle/agrupadas/dashboard ignoran filas `es_tarea = 0`.
- [x] Paquete de Horas muestra tareas y compras en el periodo.
- [x] Primera fila lógica «Saldo inicial» con acumulado correcto (exclusive fechaDesde).
- [x] Columna Saldo corre en grilla; pivot no expone Saldo.
- [x] Filtro cliente disponible para asistente/supervisor.

---

## 5. Impacto técnico (visión TR)

- Ajustar SP de listado informe/dashboard/agrupado con filtro `es_tarea`.
- Rehacer endpoint/SP paquete de horas (detalle + saldo).
- FE `PaqueteHorasPage`: grilla detalle + pivot sin campo Saldo; filtros cliente.

---

## 7. Riesgos y supuestos

- Sin proceso de alta de compras, el saldo solo reflejará tareas (+); las restas aparecerán cuando existan movimientos `es_tarea = 0`.
- Orden de filas para Saldo: por fecha (+ id) ascendente tras la fila saldo inicial.

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-31 | Parte G: SPEC-update desde CC-PQ 31/07/2026. |
