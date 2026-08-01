# HU-001-update – Atributo `es_tarea` en modelo de datos

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-001-update |
| Título | Agregar `es_tarea` a `PQ_PARTES_REGISTRO_TAREA` |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-07-31 |
| SPEC origen | [SPEC-001-modelo-datos-modulo-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-001-modelo-datos-modulo-update.md) |
| TR relacionada(s) | [TR-001-modelo-datos-modulo-update](../../../04-tareas/updates/100-SistemaPartes/TR-001-modelo-datos-modulo-update.md) |
| HU base | [HU-001-modelo-datos-modulo](../../100-SistemaPartes/HU-001-modelo-datos-modulo.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 31/07/2026 |
| Control # | 1 |
| Ítem | Agregar atributo booleano «EsTarea» |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

---

## Narrativa

Como equipo de implementación  
quiero el campo `es_tarea` en el registro de tareas  
para distinguir tareas de carga de compras de paquete de horas en todo el módulo.

## Alcance incluido

- Columna `es_tarea` bit NOT NULL default 1; API `esTarea`.
- Backfill existentes = 1.
- Docs de modelo alineados (vía TR / unificación).

## Fuera de alcance

- UI de compras; filtros de pantallas (HU-004/005/006-update).

## Criterios de aceptación

- [x] **CA-U01** Existe `es_tarea` con default 1.
- [x] **CA-U02** Registros previos migrados con `es_tarea = 1`.
- [x] **CA-U03** Documentación de modelo describe semántica true/false.

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-31 | Parte G: HU-update CC-PQ 31/07/2026. |
