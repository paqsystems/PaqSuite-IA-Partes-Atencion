# SPEC-001-update – Atributo `es_tarea` en registro de tareas

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-001-update |
| Título | Agregar atributo booleano `es_tarea` a `PQ_PARTES_REGISTRO_TAREA` |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-07-31 |
| SPEC base | [SPEC-001-modelo-datos-modulo](../../100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) |
| HU relacionada(s) | [HU-001-modelo-datos-modulo-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-001-modelo-datos-modulo-update.md) |
| TR relacionada(s) | [TR-001-modelo-datos-modulo-update](../../../04-tareas/updates/100-SistemaPartes/TR-001-modelo-datos-modulo-update.md) |
| Origen | `00-ControlCalidad-PQ` · fecha **31/07/2026** · Control #1 · ítem «Agregar atributo booleano EsTarea» |

---

## 1. Resumen ejecutivo

- **Cambio:** incorporar en `PQ_PARTES_REGISTRO_TAREA` el bit **`es_tarea`** para distinguir tareas de carga (`true`) de compras/movimientos de paquete de horas (`false`).
- **Resultado esperado:** esquema, docs de modelo y backfill de filas existentes alineados; contrato usable por SPEC-004/005/006.

---

## 2. Alcance

### 2.1 En alcance

- Columna `es_tarea` `bit NOT NULL DEFAULT 1` en `PQ_PARTES_REGISTRO_TAREA`.
- API/DTO: propiedad camelCase `esTarea`.
- Backfill: registros existentes → `es_tarea = 1`.
- Actualizar SPEC-001 §4.4 tabla registro, reglas R-MD si aplica, y docs `09-modelo-datos-tecnico` / diagrama `md-sistema-partes` cuando se unifique.
- Semántica:
  - `es_tarea = 1` → tarea cargada desde Carga de Partes (u operación equivalente de tarea).
  - `es_tarea = 0` → compra / movimiento de paquete de horas (alta en proceso a definir; fuera de este update salvo el campo).

### 2.2 Fuera de alcance

- UI de alta de compras de horas (`es_tarea = false`).
- Cambios de filtrado en carga/masivo/informes (SPEC-004/005/006-update).

---

## 3. Comportamiento / reglas

| ID | Regla |
|----|--------|
| R-MD-ES-01 | `es_tarea` bit NOT NULL, default `1`. |
| R-MD-ES-02 | Filas históricas migradas con `es_tarea = 1`. |
| R-MD-ES-03 | Nombre técnico DB `es_tarea`; API `esTarea`; UI «Es tarea». |

---

## 4. Criterios verificables

- [x] Columna existe con default 1.
- [x] Backfill de existentes = 1.
- [x] Docs de modelo listan el campo y su semántica.

---

## 5. Impacto técnico (visión TR)

- Migración add column + backfill.
- Ajuste seed/factories/tests de registro de tarea si exponen el campo.
- Diagrama / `09` / SPEC-001 base al unificar (Parte I).

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-31 | Parte G: SPEC-update desde CC-PQ 31/07/2026. |
