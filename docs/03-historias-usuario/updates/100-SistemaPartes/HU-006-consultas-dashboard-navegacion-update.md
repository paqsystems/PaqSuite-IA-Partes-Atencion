# HU-006-update – Tipo de cliente en Paquete de Horas

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-006-update |
| Título | Filtro y columnas de tipo de cliente en Paquete de Horas |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente de Revisión |
| Última actualización | 2026-10-05 |
| SPEC origen | [SPEC-006-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion-update.md) |
| HU base | [HU-006-consultas-dashboard-navegacion](../../100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) |
| TR relacionada(s) | [TR-006-update](../../../04-tareas/updates/100-SistemaPartes/TR-006-consultas-dashboard-navegacion-update.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 04/10/2026 |
| Ítem | Control de Calidad #4 · Paquete de Horas |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente de Revisión |

---

## Narrativa

Como usuario del informe Paquete de Horas  
quiero filtrar por tipo de cliente y ver su código y descripción  
para leer la cuenta corriente de un segmento de clientes.

---

## Alcance incluido

- Filtro opcional «Tipo de cliente» (web y mobile).
- Columnas código y descripción del tipo en la grilla y en el pivot.
- El filtro recorta saldo inicial y movimientos.
- La fila «Saldo inicial» no lleva tipo. El pivot sigue sin la columna Saldo.

## Fuera de alcance

- El mismo corte en consulta detallada, agrupadas o dashboard.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-CO-14 | `tipoClienteId` opcional acota saldo inicial y movimientos. |
| R-CO-15 | Movimientos exponen código y descripción de tipo; pivot los incluye y excluye Saldo. |

---

## Criterios de aceptación

- [ ] **CA-U05** Paquete de Horas muestra `tipoClienteCode` y `tipoClienteDescripcion` en cada movimiento.
- [ ] **CA-U06** El filtro de tipo de cliente limita saldo inicial y filas al tipo elegido.
- [ ] **CA-U07** El pivot ofrece las dos columnas de tipo y no ofrece Saldo.

---

## Escenarios Gherkin

```gherkin
Scenario: Filtrar paquete de horas por tipo de cliente
  Given clientes de dos tipos con movimientos en el periodo
  When consulto Paquete de Horas filtrando un tipo
  Then solo aparecen movimientos de ese tipo
  And el saldo inicial considera solo ese tipo
  And cada movimiento muestra código y descripción del tipo
```

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-05 | Volcado Parte G desde CC-PQ #4 (04/10/2026). |
