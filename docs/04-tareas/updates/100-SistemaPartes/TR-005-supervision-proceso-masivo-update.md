# TR-005-update – Masivo y `es_tarea`

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-005-supervision-proceso-masivo-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-005-supervision-proceso-masivo-update.md) |
| **SPEC relacionada** | [SPEC-005-supervision-proceso-masivo-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-005-supervision-proceso-masivo-update.md) |
| **TR base** | [TR-005-supervision-proceso-masivo](../../100-SistemaPartes/TR-005-supervision-proceso-masivo.md) |
| **Dependencias** | [TR-001-update](./TR-001-modelo-datos-modulo-update.md) |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-08-01 |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 31/07/2026 |
| Ítem | Proceso Masivo — EsTarea |

---

## 1) Alcance

- List / list_ids masivo: `es_tarea = 1`.
- `masivo_set_cerrado` y `masivo_actualizar`: rechazar cualquier id con `es_tarea = 0` → 422 atómico (p. ej. `partes.masivo.atributoInvalido` o clave dedicada `partes.masivo.noEsTarea`).

## 2) AC

| AC | Verificación |
|----|--------------|
| AC-U01 | List/ids solo `es_tarea=1` |
| AC-U02 | Lote con compra → error; 0 cambios |

## 3) Plan

| ID | Tipo | Descripción | Est. |
|----|------|-------------|------|
| T1 | BE | Filtros + guardas en lotes | M |
| T2 | Tests | Feature | S |

## 4) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-01 | D/E: guardas `noEsTarea` en lotes; list/ids vía filtro 004; i18n; Feature. |
| 2026-07-31 | Parte G: TR-update CC-PQ. |
