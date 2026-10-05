# SPEC-006-update – Tipo de cliente en Paquete de Horas

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-006-update |
| Título | Filtro y columnas de tipo de cliente en Paquete de Horas |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-10-05 |
| SPEC base | [SPEC-006-consultas-dashboard-navegacion](../../100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |
| HU relacionada(s) | [HU-006-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-006-consultas-dashboard-navegacion-update.md) |
| TR relacionada(s) | [TR-006-update](../../../04-tareas/updates/100-SistemaPartes/TR-006-consultas-dashboard-navegacion-update.md) |
| Origen | `00-ControlCalidad-PQ` · fecha 04/10/2026 · Control de Calidad #4 · Paquete de Horas |

---

## 1. Resumen ejecutivo

- **Problema:** el informe Paquete de Horas no permite acotar ni ver el tipo de cliente.
- **Resultado esperado:** filtro opcional «Tipo de cliente» y columnas de código y descripción del tipo, en grilla y en pivot.

---

## 2. Alcance

### 2.1 En alcance

- Filtro opcional `tipoClienteId` en Paquete de Horas (web y mobile). Vacío = todos los tipos del universo ya delimitado por rol y por el filtro de cliente.
- El filtro acota **movimientos del periodo y saldo inicial** (mismo universo).
- Columnas `tipoClienteCode` y `tipoClienteDescripcion` (código y descripción de `PQ_PARTES_TIPOS_CLIENTE` del cliente de la fila).
- La fila sintética «Saldo inicial» deja esas columnas vacías.
- Pivot: los dos campos están disponibles. El campo Saldo sigue excluido del pivot.
- Selector de catálogo: tipos de cliente usables (código + descripción), con «Cargando…» mientras llega el catálogo.

### 2.2 Fuera de alcance

- El mismo filtro o columnas en consulta detallada, agrupadas o dashboard.
- Alta de compras de horas.

---

## 3. Actores y contexto

Los mismos perfiles de SPEC-006. El filtro no amplía la delimitación de rol (R-CO-01).

---

## 4. Comportamiento funcional

### 4.3b Paquete de Horas (delta)

- Filtros: los ya vigentes (`fechaDesde`, `fechaHasta`, cliente según rol) **más** tipo de cliente opcional.
- Columnas: las ya vigentes **más** tipo de cliente (código y descripción).
- Presentación del tipo: código y descripción por separado, no el id.

### 4.8 Reglas numeradas (delta)

| ID | Regla |
|----|--------|
| R-CO-14 | Paquete de Horas acepta filtro opcional `tipoClienteId`. Si viene informado, saldo inicial y movimientos se limitan a clientes de ese tipo. |
| R-CO-15 | Cada movimiento expone `tipoClienteCode` y `tipoClienteDescripcion`. La fila «Saldo inicial» los deja vacíos. Pivot los ofrece; no ofrece Saldo. |

---

## 5. Criterios verificables

- [ ] Sin filtro de tipo, el informe se comporta como hasta ahora y además trae código y descripción de tipo en cada movimiento.
- [ ] Con `tipoClienteId`, solo entran movimientos de clientes de ese tipo, y el saldo inicial usa el mismo recorte.
- [ ] La grilla muestra las dos columnas; el pivot las incluye y sigue sin Saldo.
- [ ] Otro tipo no devuelve los movimientos del tipo filtrado.

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | `pq_sp_partes_informe_paquete_horas`: join a `PQ_PARTES_TIPOS_CLIENTE` y parámetro `p_tipo_cliente_id` |
| Frontend | SelectBox + dos columnas en `PaqueteHorasPage`; campos de pivot; filtro también en la barra mobile |
| i18n | Claves de filtro y columnas en 5 locales |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-05 | CC-PQ #4 (04/10/2026): filtro y columnas de tipo de cliente en Paquete de Horas. |
