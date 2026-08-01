# TR-006-update – Filtro `es_tarea` y Paquete de Horas cuenta corriente

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-006-consultas-dashboard-navegacion-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-006-consultas-dashboard-navegacion-update.md) |
| **SPEC relacionada** | [SPEC-006-consultas-dashboard-navegacion-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion-update.md) |
| **TR base** | [TR-006-consultas-dashboard-navegacion](../../100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) |
| **Dependencias** | [TR-001-update](./TR-001-modelo-datos-modulo-update.md) |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-08-01 |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 31/07/2026 |
| Ítems | Informes/Dashboard EsTarea; Paquete de Horas rehacer; cuenta corriente |

---

## 1) Alcance

### A) Filtro lecturas
- SP/endpoints: consulta detallada, agrupado, dashboard snapshot → `es_tarea = 1`.

### B) Paquete de Horas (rehacer)
- Endpoint/SP que:
  - filtre periodo + cliente (según rol);
  - **no** filtre `es_tarea`;
  - calcule saldo inicial (fecha &lt; desde; + tarea / − compra);
  - devuelva movimientos detalle (mismos campos que detalle) + `saldo` running;
  - marque fila sintética saldo inicial (`esSaldoInicial` o convención TR).
- FE: `PaqueteHorasPage` — grilla detalle con Saldo; pivot **sin** campo Saldo; filtros cliente.

## 2) AC

| AC | Verificación |
|----|--------------|
| AC-U01 | Detalle/agrupadas/dashboard sin compras |
| AC-U02 | Paquete incluye tarea y compra |
| AC-U03 | Saldo inicial correcto |
| AC-U04 | Columna Saldo running |
| AC-U05 | Pivot sin Saldo |
| AC-U06 | Filtro cliente (asistente/supervisor) |

## 3) Reglas implementación

| ID | Nota |
|----|------|
| RN-TR-PH-01 | Orden movimientos: fecha ASC, id ASC tras fila saldo inicial. |
| RN-TR-PH-02 | Presentación duración UI `hh:mm` coherente TR-006 vigente. |
| RN-TR-PH-03 | SP sugerido: `pq_sp_partes_informe_paquete_horas` (reemplaza/amplía contrato actual). |

## 4) Plan

| ID | Tipo | Descripción | Est. |
|----|------|-------------|------|
| T1 | BE | Filtro `es_tarea` en detalle/agrupado/dashboard | M |
| T2 | BE | Rehacer SP/API paquete horas + saldo | L |
| T3 | FE | PaqueteHorasPage grilla/pivot/filtros | L |
| T4 | Tests | Feature + unit saldo | L |
| T5 | Docs | Manual SPEC-006 | S |

## 5) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-01 | D/E: filtro `es_tarea` en agrupado/dashboard; paquete cuenta corriente + FE; tests. |
| 2026-07-31 | Parte G: TR-update CC-PQ. |
