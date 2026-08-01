# TR-001-update – Columna `es_tarea`

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-001-modelo-datos-modulo-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-001-modelo-datos-modulo-update.md) |
| **SPEC relacionada** | [SPEC-001-modelo-datos-modulo-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-001-modelo-datos-modulo-update.md) |
| **TR base** | [TR-001-modelo-datos-modulo](../../100-SistemaPartes/TR-001-modelo-datos-modulo.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-08-01 |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 31/07/2026 |
| Ítem | Agregar atributo booleano «EsTarea» |

---

## 1) Alcance

- Migración: `es_tarea` bit NOT NULL DEFAULT 1 en `PQ_PARTES_REGISTRO_TAREA`.
- Backfill `UPDATE … SET es_tarea = 1` (o default al add).
- Exponer en mapeos DTO/`mapRow` como `esTarea` cuando list/get lo requieran.
- Actualizar docs modelo en unificación.

## 2) AC

| AC | Verificación |
|----|--------------|
| AC-U01 | Columna + default 1 |
| AC-U02 | Existentes = 1 |
| AC-U03 | Docs modelo |

## 3) Plan

| ID | Tipo | Descripción | Est. |
|----|------|-------------|------|
| T1 | DB | Migración `es_tarea` | S |
| T2 | BE | mapRow / tests factories | S |
| T3 | Docs | 09 / diagrama al cerrar I | S |

## 4) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-01 | D: migración `es_tarea`, mapRow/API, docs diagrama, tests. |
| 2026-07-31 | Parte G: TR-update CC-PQ. |
