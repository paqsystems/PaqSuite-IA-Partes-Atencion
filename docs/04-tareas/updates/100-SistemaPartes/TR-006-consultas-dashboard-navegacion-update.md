# TR-006-update – Tipo de cliente en Paquete de Horas

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-006-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-006-consultas-dashboard-navegacion-update.md) |
| **SPEC relacionada** | [SPEC-006-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion-update.md) |
| **TR base** | [TR-006](../../100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-10-05 |

**Origen:** `00-ControlCalidad-PQ` · 04/10/2026 · CC #4 · Paquete de Horas

---

## 1) HU refinada (resumen)

### In scope
- `pq_sp_partes_informe_paquete_horas` acepta `p_tipo_cliente_id`.
- Join `PQ_PARTES_TIPOS_CLIENTE` por `PQ_PARTES_CLIENTES.tipo_cliente_id`.
- Salida por movimiento: `tipo_cliente_code`, `tipo_cliente_descripcion` (camel en API).
- El mismo predicado aplica al cálculo de saldo inicial.
- UI web: SelectBox `data-testid="partesPaqueteTipoCliente"` y dos columnas. Pivot: los dos campos, sin Saldo.
- UI mobile: el mismo filtro en la barra del informe (la consulta está en el menú native).

### Out of scope
- Consulta detallada, agrupadas y dashboard.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|----------------|
| AC-U05 | Cada ítem de movimiento trae `tipoClienteCode` y `tipoClienteDescripcion`. La fila saldo inicial los trae vacíos. |
| AC-U06 | `tipoClienteId` excluye movimientos de otro tipo y recalcula `saldoInicial` solo con el tipo. |
| AC-U07 | `buildPaqueteHorasPivotFields` incluye ambos dataField y no incluye `saldo`. |

---

## 3) Reglas

| ID | Implementación |
|----|----------------|
| RN-TR-U04 | Filtro por igualdad `c.tipo_cliente_id = @p` (un valor). No usar `IN`. |
| RN-TR-U05 | El join del tipo no se agrega a `baseScoped` compartido con detallada/agrupado/dashboard: solo en la query de paquete. |

---

## 4) API

`GET /api/v1/partes/informes/paquete-horas` suma query opcional `tipoClienteId`.

---

## 5) Plan de tareas

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Join + filtro + campos en saldo inicial y movimientos | AC-U05/U06 |
| T2 | Frontend | Filtro web/mobile, columnas, pivot, i18n 5 locales | AC-U07 |
| T3 | Tests | Feature del filtro; Vitest del pivot | Suites verdes |

**Orden:** T1 → T2 → T3.

---

## 6) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-05 | Volcado Parte G desde CC-PQ #4. |
