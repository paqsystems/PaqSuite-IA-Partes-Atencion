# SPEC-005-update – Proceso masivo y `es_tarea`

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-005-update |
| Título | Proceso masivo: solo registros con `es_tarea = true` |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-07-31 |
| SPEC base | [SPEC-005-supervision-proceso-masivo](../../100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) |
| HU relacionada(s) | [HU-005-supervision-proceso-masivo-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-005-supervision-proceso-masivo-update.md) |
| TR relacionada(s) | [TR-005-supervision-proceso-masivo-update](../../../04-tareas/updates/100-SistemaPartes/TR-005-supervision-proceso-masivo-update.md) |
| Origen | `00-ControlCalidad-PQ` · fecha **31/07/2026** · Control #1 · ítem «Proceso Masivo … EsTarea» |
| Depende de | [SPEC-001-update](./SPEC-001-modelo-datos-modulo-update.md) |

---

## 1. Resumen ejecutivo

- **Cambio:** el proceso masivo (listado, select-all, lotes de cerrado y de atributos) opera **únicamente** sobre `es_tarea = true`.
- **Resultado esperado:** las compras de horas no entran en filtros ni acciones masivas de supervisión de tareas.

---

## 2. Alcance

### 2.1 En alcance

- Filtro implícito `es_tarea = 1` en listado masivo y en `list_ids`.
- Acciones masivas (cerrar/reabrir / actualizar atributos) solo sobre ids que sean `es_tarea = 1`; si el lote incluye un id con `es_tarea = 0` → fallo de validación atómico (422).

### 2.2 Fuera de alcance

- DDL (SPEC-001-update).
- Rehacer Paquete de Horas (SPEC-006-update).

---

## 3. Reglas

| ID | Regla |
|----|--------|
| R-SU-ES-01 | Masivo lista/selecciona solo `es_tarea = 1`. |
| R-SU-ES-02 | Lotes rechazan ids con `es_tarea = 0` (atómico). |

---

## 4. Criterios verificables

- [x] Listado masivo no muestra compras (`es_tarea = 0`).
- [x] Select-all / ids solo devuelve `es_tarea = 1`.
- [x] Intentar lote con id `es_tarea = 0` → error; cero cambios.

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-31 | Parte G: SPEC-update desde CC-PQ 31/07/2026. |
