# SPEC-004-update – Carga diaria y `es_tarea`

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-004-update |
| Título | Carga diaria: filtrar y persistir `es_tarea = true` |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-07-31 |
| SPEC base | [SPEC-004-operacion-carga-diaria](../../100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) |
| HU relacionada(s) | [HU-004-operacion-carga-diaria-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-004-operacion-carga-diaria-update.md) |
| TR relacionada(s) | [TR-004-operacion-carga-diaria-update](../../../04-tareas/updates/100-SistemaPartes/TR-004-operacion-carga-diaria-update.md) |
| Origen | `00-ControlCalidad-PQ` · fecha **31/07/2026** · Control #1 · ítem «Carga de Partes … EsTarea» |
| Depende de | [SPEC-001-update](./SPEC-001-modelo-datos-modulo-update.md) |

---

## 1. Resumen ejecutivo

- **Cambio:** la carga diaria solo opera sobre registros con `esTarea = true` y, en cada alta/edición, fuerza `esTarea = true`.
- **Resultado esperado:** listados y CRUD de carga no mezclan compras de horas.

---

## 2. Alcance

### 2.1 En alcance

- Listado `GET` / SP list de carga: filtro implícito `es_tarea = 1`.
- Upsert: al grabar, asignar siempre `es_tarea = 1` (no editable por el usuario en este proceso).
- Documentar campo en captura como implícito (no es captura de negocio visible obligatoria).

### 2.2 Fuera de alcance

- DDL del campo (SPEC-001-update).
- Proceso masivo (SPEC-005-update).
- Informes / paquete de horas (SPEC-006-update).
- Alta de compras (`es_tarea = 0`).

---

## 3. Reglas

| ID | Regla |
|----|--------|
| R-OP-ES-01 | Carga diaria lista solo `es_tarea = 1`. |
| R-OP-ES-02 | Alta/edición desde carga diaria persiste `es_tarea = 1`. |

---

## 4. Criterios verificables

- [x] Tras crear/editar desde carga, `esTarea` queda `true`.
- [x] Un registro con `es_tarea = 0` no aparece en el listado de carga (mismo filtro de fechas).

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-31 | Parte G: SPEC-update desde CC-PQ 31/07/2026. |
