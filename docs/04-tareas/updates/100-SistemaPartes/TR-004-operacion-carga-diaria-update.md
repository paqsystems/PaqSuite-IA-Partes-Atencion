# TR-004-update – Carga diaria y `es_tarea`

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-004-operacion-carga-diaria-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-004-operacion-carga-diaria-update.md) |
| **SPEC relacionada** | [SPEC-004-operacion-carga-diaria-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-004-operacion-carga-diaria-update.md) |
| **TR base** | [TR-004-operacion-carga-diaria](../../100-SistemaPartes/TR-004-operacion-carga-diaria.md) |
| **Dependencias** | [TR-001-update](./TR-001-modelo-datos-modulo-update.md) |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-08-01 |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 31/07/2026 |
| Ítem | Carga de Partes — EsTarea |

---

## 1) Alcance

- `pq_sp_partes_tarea_list` (y get si aplica listado carga): filtro `es_tarea = 1`.
- `pq_sp_partes_tarea_upsert`: set `es_tarea = 1` siempre.
- Tests Feature carga: no listan compras; grabación deja `esTarea=true`.

## 2) AC

| AC | Verificación |
|----|--------------|
| AC-U01 | List carga sin `es_tarea=0` |
| AC-U02 | Upsert fuerza `es_tarea=1` |

## 3) Plan

| ID | Tipo | Descripción | Est. |
|----|------|-------------|------|
| T1 | BE | Filtro list + force upsert | M |
| T2 | Tests | Feature | S |

## 4) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-01 | D/E: filtro list `es_tarea=1`; upsert fuerza `es_tarea=1`; Feature test. |
| 2026-07-31 | Parte G: TR-update CC-PQ. |
