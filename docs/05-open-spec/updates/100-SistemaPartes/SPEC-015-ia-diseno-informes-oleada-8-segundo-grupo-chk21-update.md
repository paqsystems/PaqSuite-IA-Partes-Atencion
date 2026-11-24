# SPEC-015-update (oleada 8) — Segundo nivel de agrupación (CHK-21 / LAB-21)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-8-segundo-grupo-chk21 |
| Título | Segundo nivel de agrupación cliente → fecha (un turno vs dos turnos) |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Finalizado** (2026-11-24) — opción **B** |
| Jira | PIAPARTES-75 (Epic) · PIAPARTES-76 (HU) · PIAPARTES-77 (TR) |
| SPEC previa | [Oleada 7b CHK-20](./SPEC-015-ia-diseno-informes-oleada-7b-group-sum-chk20-update.md) |
| HU | [HU-015-update-j](../../../03-historias-usuario/100-SistemaPartes/HU-015-update-j-segundo-grupo-chk21.md) |
| TR | [TR-015-update-j](../../../04-tareas/100-SistemaPartes/TR-015-update-j-segundo-grupo-chk21.md) |
| Matriz | Framework `15-reportes-emisiones-ia-checklist-matriz.json` — **CHK-21**, **LAB-21** |
| Proceso piloto | `partes.informes.consultaDetallada` |

**Plantilla regla 19:** GEN-03 + GEN-15; mutación REPX sidecar; heurística host. Sin nueva acción de planner salvo que **opción A** lo documente.

---

## 1. Resumen

La matriz distingue dos comportamientos:

| Ítem | Prompt | Expectativa v1 documentada |
|------|--------|---------------------------|
| **CHK-21** | Agregá un segundo nivel de agrupación por fecha dentro de cada cliente | **`plannerExpect: no_mapped`** — un solo turno **no** debe aplicar un segundo nivel incorrecto |
| **LAB-21** | Turno 1: Agrupá por cliente → Turno 2: Agrupá por fecha | Dos `addGroupByField` secuenciales; needles tras turno 2 |

Hoy el sidecar solo soporta **un** `GroupHeader` + pie emparejado por invocación (CHK-18…20). Un segundo `addGroupByField` en layouts ya agrupados debe definirse sin romper pies de grupo ni totales CHK-20.

**Decisión de producto (D1):** implementar **opción A** (un turno → dos acciones ordenadas) u **opción B** (mantener `no_mapped` + endurecer LAB-21 + mensaje Smart Capture). El SPEC adopta la rama elegida en §3.

---

## 2. GEN adoptados

| GEN | Rol |
|-----|-----|
| GEN-03 | Turno Smart Capture + heurística |
| GEN-15 | Diseñador / apply |
| GEN-16 | BYOK opcional |

---

## 3. Alcance (bifurcación A / B)

### 3.1 Opción A — Un turno con dos `addGroupByField` (cliente → fecha)

| Aspecto | Contrato |
|---------|----------|
| Planner | Prompt CHK-21 → `[addGroupByField clienteNombre, addGroupByField fecha]` orden fijo (cliente externo, fecha interna) |
| Sidecar | Insertar **segundo** `GroupHeaderBand` + pie emparejado **entre** header cliente y `DetailBand`; caption fecha conforme schema |
| Matriz | Actualizar CHK-21: `plannerExpect: actions`, `expectedActions` (2), `applyAssert` con needles |
| Idempotencia | Segundo campo: noop análogo `ya_agrupado` por nivel; no duplicar bandas |
| LAB-21 | Sigue válido como secuencia equivalente |

### 3.2 Opción B — Un turno sin mapeo (matriz actual)

| Aspecto | Contrato |
|---------|----------|
| Planner | Prompt CHK-21 → **`actions: []`**, `replyText: partes.emissionDesign.noMappedActions` (o clave dedicada «usá dos turnos») |
| Guardia | Excluir heurística «agrupar por cliente» si el texto indica **segundo nivel** / **dentro de cada** / **fecha** en el mismo mensaje (evitar falso positivo por «agrupación» + «cliente») |
| CHK-21 tests | `phpunit_planner` en matriz o test dedicado `test_chk21_no_mapped` |
| LAB-21 | Sidecar: segundo turno `fecha` con layout ya agrupado por cliente → orden bandas DevExpress correcto; script **PASS** |
| Smart Capture | Mensaje guía: agrupar cliente, luego fecha (i18n 5 locales) |
| Matriz CHK-21 | Sin cambio en `no_mapped` |

### 3.3 Fuera de alcance (ambas opciones)

- CHK-19/20 en la misma corrida
- CHK-28+
- Nuevo `type` de acción (p. ej. `addNestedGroup`) salvo épica futura
- Tercer nivel de agrupación

---

## 4. Criterios de aceptación (por opción)

### Si A

| ID | Criterio |
|----|----------|
| CA-A8-01 | Apply batch CHK-21: REPX con headers cliente + fecha y pies emparejados |
| CA-A8-02 | `DesignLayoutChecklistPlannerTest` / matriz CHK-21 PASS |
| CA-A8-03 | LAB-21 script PASS |
| CA-A8-04 | Sin regresión CHK-18/19/20 scripts |

### Si B

| ID | Criterio |
|----|----------|
| CA-B8-01 | Prompt CHK-21 → cero acciones + mensaje i18n |
| CA-B8-02 | No apply parcial solo cliente en un turno CHK-21 |
| CA-B8-03 | `eval-emission-ia-checklist-lab-extras.ps1` LAB-21 **PASS** |
| CA-B8-04 | PHPUnit CHK-21 (no_mapped) + regresión planner |
| CA-B8-05 | Manual: usuario entiende secuencia dos turnos |

---

## 5. Contrato sidecar (común)

- **`LayoutGroupByFieldApplier`:** al agrupar por un **segundo** campo con header cliente existente, insertar nuevo header **inmediatamente antes** de `DetailBand` (más interno); crear `GroupFooter_{fecha}` sin eliminar `GroupFooter_clienteNombre`.
- **Orden bandas (objetivo):** `GroupHeader_cliente` → `GroupHeader_fecha` → `Detail` → `GroupFooter_fecha` → `GroupFooter_cliente` (validar en D contra DevExpress / script).
- CHK-20 suma permanece en pie del grupo **externo** salvo decisión explícita en TR.

---

## 6. Veredicto alcance (D1 — opción B)

| Campo | Valor |
|-------|--------|
| Opción adoptada | **B** (2026-11-24, usuario) |
| Fecha cierre D1 | 2026-11-24 |
| Comentario Epic Jira | Opción **B** — `no_mapped` + `useTwoTurns` + LAB-21 |