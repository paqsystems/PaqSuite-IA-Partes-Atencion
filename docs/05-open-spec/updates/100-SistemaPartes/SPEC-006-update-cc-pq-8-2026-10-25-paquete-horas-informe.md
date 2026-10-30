# SPEC-006-update – CC PQ #8 (25/10/2026) — Informe Paquete de Horas (saldo y destaque)

## Metadatos

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe |
| **Título** | Informe Paquete de Horas — saldo por cliente/renglón y destaque de compras |
| **Épica / carpeta** | `100-SistemaPartes` |
| **Estado** | Pendiente (**Parte A** cerrada 2026-10-30; pendiente **A1**) |
| **Última actualización** | 2026-10-30 |
| **SPEC base** | [SPEC-006-consultas-dashboard-navegacion](../../100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) §4.3b |
| **HU-update** | [HU-006-update-cc-pq-8-2026-10-25-paquete-horas-informe](../../../03-historias-usuario/updates/100-SistemaPartes/HU-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **TR-update** | [TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe](../../../04-tareas/updates/100-SistemaPartes/TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **Origen** | CC **#8** · [`18-obtencion-saldo-por-renglon.md`](../../../02-producto/Sistema-Partes-IA/18-obtencion-saldo-por-renglon.md) · [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52) |

---

## 1. Resumen ejecutivo

- **Problema:** en prueba manual del informe **Paquete de Horas**, la columna **Saldo** no refleja la cuenta corriente acordada; las filas de **compra de paquete** (`es_tarea = false`) no se distinguen visualmente en **Duración**.
- **Resultado esperado:** saldo inicial y saldo acumulado **por cliente**, orden estable por fecha/id; duración de compras destacada (negativo y/o rojo) en grilla web y export si aplica.

---

## 2. Alcance

### 2.1 En alcance

- Corrección del **cálculo de saldo** en `GET /api/v1/partes/informes/paquete-horas` (contrato TR-006 / envelope items + `saldoInicial`).
- **Partición por cliente:** cada `cliente_id` tiene su propio saldo previo al periodo y su propia serie running balance (CC #8 CA-05…CA-07).
- **Fila sintética «Saldo inicial»:** una por **cliente presente** en el resultado del informe (supervisor sin filtro cliente → N filas saldo inicial, una por cliente con movimientos en periodo o saldo previo ≠ 0); si el filtro fija un solo cliente → una fila saldo inicial (comportamiento actual simplificado).
- **Orden de filas:** por `cliente_id` ASC (estable), dentro de cada cliente: fila saldo inicial, luego movimientos `fecha ASC`, `id ASC`.
- **Signo:** `es_tarea = true` → suma minutos al saldo; `es_tarea = false` → resta minutos (sin cambio normativo SPEC-006 §4.3b).
- **Presentación FE web:** columna Duración — para `esTarea === false`, mostrar magnitud en **`hh:mm` con prefijo `-`** y clase CSS de destaque rojo (`data-testid` estable en celda opcional).
- **Columna Saldo:** formato `hh:mm` con signo algebraico coherente (saldo negativo = deuda de horas).
- Filtro **tipo de cliente** sigue acotando universo de movimientos **y** saldo inicial (SPEC-006 §4.3b).
- i18n etiqueta fila saldo inicial (`partes.informe.saldoInicialLabel` / fila `observacion` traducible).
- Pivot: campo **Saldo** permanece **excluido** del catálogo pivot (sin cambio).

### 2.2 Fuera de alcance

- Cambiar filtros obligatorios del informe (fecha desde/hasta; cliente según rol).
- Mobile kardex paquete: saldo inicial en KPI puede mantenerse agregado simple salvo que TR-007 indique paridad (observación A1).
- Migrar implementación PHP ad-hoc a SP en este delta **solo si** TR confirma deuda regla 74; el SPEC exige **comportamiento**, no obliga SP en v1 del fix si el host ya invoca `pq_sp_partes_informe_paquete_horas` como contrato documentado.

---

## 3. Actores y contexto

| Actor | Universo |
|-------|----------|
| Supervisor | Varios clientes; filtros opcionales |
| Asistente | Mismo patrón delimitado SPEC-002 |
| Cliente funcional | Solo su organización |

**Diagnóstico referencia (Parte A):** la implementación actual en `PartesInformeOperations::paqueteHoras` acumula saldo inicial **global** (suma todos los clientes) y emite **una** fila «Saldo inicial» — incumple CA-07 cuando hay más de un cliente en el resultado.

---

## 4. Comportamiento funcional

### 4.1 Cálculo de saldo (norma cerrada Parte A)

Para cada **cliente C** del universo filtrado:

1. **Saldo inicial S₀(C):**  
   `SUM( signed(es_tarea, duracion_minutos) )` sobre `PQ_PARTES_REGISTRO_TAREA` con `cliente_id = C`, `fecha < fechaDesde`, aplicando filtros de actor y **tipo de cliente** (mismo universo que SPEC-006 §4.3b).

2. **Movimientos del periodo:** filas con `fechaDesde ≤ fecha ≤ fechaHasta`, mismo universo, **sin** filtrar `es_tarea`.

3. **Running balance:** iniciar `saldo = S₀(C)`; por cada movimiento en orden `(fecha, id)`:  
   `saldo += signed(es_tarea, duracion_minutos)`; exponer en columna **Saldo** de esa fila.

4. **Fila sintética:** primera fila por cliente con `esSaldoInicial = true`, `duracionMinutos` = |S₀| presentación según convención (ver §4.3), `saldo` = S₀, demás columnas de detalle vacías salvo cliente identificable (código + nombre).

Equivalencia SQL de producto [`18-obtencion-saldo-por-renglon.md`](../../../02-producto/Sistema-Partes-IA/18-obtencion-saldo-por-renglon.md) con partición explícita por `cliente_id`.

### 4.2 Destaque visual duración (compras)

| Condición | Presentación Must (web grilla) |
|-----------|----------------------------------|
| `esTarea === true` | Duración positiva `hh:mm`, estilo normal |
| `esTarea === false` | Duración con **signo negativo** visible (`-hh:mm`) **y** color rojo (clase tema DevExtreme / CSS host) |
| Fila saldo inicial | Sin destaque rojo en duración salvo que TR unifique con convención de signo |

**Export Excel/PDF (si habilitado):** mismo signo textual; color opcional.

### 4.3 Convención de signos en columnas

| Columna | Tarea (consume) | Compra (repone) |
|---------|-----------------|-----------------|
| Duración mostrada | `hh:mm` positivo | `-hh:mm` (destaque) |
| Efecto en saldo | +minutos | −minutos |

Persistencia y API siguen en minutos **positivos** en `duracionMinutos`; el signo es **presentacional** en duración para compras, **algebraico** en saldo.

### 4.4 Reglas numeradas

| ID | Regla |
|----|--------|
| R-INF-PH-01 | Saldo inicial y running **por cliente_id**; prohibido mezclar clientes (CA-07). |
| R-INF-PH-02 | Orden `(fecha ASC, id ASC)` dentro de cada cliente tras saldo inicial (CA-06). |
| R-INF-PH-03 | Filtro tipo cliente aplica a S₀ y movimientos (SPEC-006 base). |
| R-INF-PH-04 | Compras destacadas en duración (CA-03); tareas normales (CA-04). |
| R-INF-PH-05 | Pivot sin campo Saldo (sin cambio). |

---

## 5. Criterios verificables

- [ ] **CA-03** Filas `es_tarea = 0` con duración destacada (negativo/rojo).
- [ ] **CA-04** Filas `es_tarea = 1` presentación normal.
- [ ] **CA-05** Saldo inicial por cliente desde movimientos con `fecha < fechaDesde`.
- [ ] **CA-06** Saldo de cada renglón continúa acumulado del **mismo cliente**.
- [ ] **CA-07** Dos clientes en mismo informe → dos series de saldo independientes.
- [ ] Fixture regression: cliente A solo tareas, cliente B tarea+paquete → saldos no cruzados.

---

## 6. Impacto técnico (visión TR)

| Pieza | Acción |
|-------|--------|
| `PartesInformeOperations::paqueteHoras` o SP `pq_sp_partes_informe_paquete_horas` | Reimplementar partición por cliente + N filas saldo inicial |
| `PartesInformeController` | Sin cambio de contrato query; validar shape JSON camelCase |
| `PaqueteHorasPage.tsx` | `cellPrepared` / `customizeText` duración; opcional ocultar columna `esTarea` en producción |
| `partesInformePivotCatalog` | Confirmar exclusión Saldo |
| Tests | Integración API multi-cliente; Vitest formatter duración negativa |

---

## 7. Riesgos y supuestos

- **Supuesto:** supervisor puede consultar varios clientes en una sola grilla (sin filtro cliente); si producto exige cliente obligatorio en UI, CA-07 sigue aplicando en backend por integridad.
- **Riesgo:** filas saldo inicial múltiples cambian densidad de grilla — aceptado por CC.
- **Deuda:** TR-006 documenta SP; implementación actual usa PHP + Query Builder en excepción — A1/C1 deben fijar si el fix migra a SP único.

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-30 | Parte G volcado |
| 2026-10-30 | **Parte A** — SPEC-update completo (saldo + destaque) |
