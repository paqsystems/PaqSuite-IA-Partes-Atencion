# HU-015-update-j – Segundo nivel de agrupación (CHK-21 / LAB-21)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-015-update-j |
| Título | Agrupación cliente y fecha sin comportamiento ambiguo en Smart Capture |
| Épica | `100-SistemaPartes` |
| Clasificación | MUST-HAVE (oleada 8 / mvp_parcial) |
| Estado | **Finalizado** (2026-11-24) |
| Jira | PIAPARTES-76 |
| SPEC | [SPEC-015-update oleada 8](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-8-segundo-grupo-chk21-update.md) |
| TR | [TR-015-update-j](../../04-tareas/100-SistemaPartes/TR-015-update-j-segundo-grupo-chk21.md) |

---

## Narrativa

Como **diseñador**, quiero que al pedir un **segundo nivel de agrupación por fecha dentro de cada cliente** el sistema **no** aplique un único grupo incorrecto, y que el camino documentado para lograr cliente + fecha sea **claro** (dos turnos **LAB-21** o un turno, si producto elige opción A).

---

## Alcance incluido (según A o B en SPEC §6)

- Contrato planner para prompt canónico CHK-21.
- Comportamiento sidecar para segundo `addGroupByField` (fecha tras cliente).
- Script LAB-21 y guía manual alineados.

## Fuera de alcance

CHK-20, CHK-28+, multi-grupo en un mensaje distinto al canónico sin actualizar matriz.

---

## Criterios de aceptación (resumen)

| AC | Opción B (matriz default) | Opción A |
|----|---------------------------|----------|
| AC-01 | CHK-21 → sin acciones apply | CHK-21 → 2 acciones ordenadas |
| AC-02 | Mensaje SC guía dos turnos | Apply deja cliente + fecha en REPX |
| AC-03 | LAB-21 script PASS | LAB-21 sigue PASS |
| AC-04 | Sin regresión CHK-18/19/20 | Idem |

```gherkin
# Opción B (referencia)
Escenario: Un turno pidiendo segundo nivel no aplica
  Cuando envío el prompt canónico CHK-21 en un solo turno
  Entonces no hay acciones de layout
  Y el asistente no invita a aplicar un solo grupo por cliente

Escenario: Dos turnos LAB-21
  Dado un layout limpio
  Cuando agrupo por cliente y aplico
  Y agrupo por fecha y aplico
  Entonces el REPX contiene agrupación cliente y fecha
```