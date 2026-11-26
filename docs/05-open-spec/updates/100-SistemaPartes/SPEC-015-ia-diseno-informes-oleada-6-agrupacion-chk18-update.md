# SPEC-015-update (oleada 6) — Agrupación por cliente (CHK-18)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-6-agrupacion-chk18 |
| Título | Agrupar informe por cliente vía Smart Capture |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Finalizado** (2026-11-21 — CHK-18 F1) |
| Jira | PIAPARTES-63 (Epic) |
| SPEC previa | [Oleada 5 page header](./SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) |
| HU | [HU-015-update-f](../../../03-historias-usuario/100-SistemaPartes/HU-015-update-f-agrupacion-cliente-chk18.md) |
| TR | [TR-015-update-f](../../../04-tareas/100-SistemaPartes/TR-015-update-f-agrupacion-cliente-chk18.md) |
| Matriz | Framework `15-reportes-emisiones-ia-checklist-matriz.json` — **CHK-18** |
| Proceso piloto | `partes.informes.consultaDetallada` |

**Plantilla regla 19:** GEN-03 + GEN-15; mutación REPX en sidecar Framework; heurística MUST en host Partes. No apply PHP.

---

## 1. Resumen

El usuario pide **«Agrupá por cliente»**. El turno planifica `addGroupByField` con `field: clienteNombre`. El sidecar crea **Group Header** (GroupField + label) y **Group Footer** vacío (28px). Si ya existe el par header+footer para ese campo → noop `ya_agrupado` y mensaje en Smart Capture.

---

## 2. GEN adoptados

| GEN | Rol |
|-----|-----|
| GEN-03 | Turno Smart Capture |
| GEN-15 | Diseñador + Guardar/Descartar |
| GEN-16 | BYOK opcional; heurística obligatoria para prompt canónico |

GEN-37 no es Must en CHK-18 (solo prompt canónico).

---

## 3. Alcance

### 3.1 En alcance (CHK-18)

| CHK | Prompt | Acción | assert REPX |
|-----|--------|--------|-------------|
| CHK-18 | Agrupá por cliente | `addGroupByField` `{ field: clienteNombre }` | `GroupHeaderBand`, `clienteNombre`, `GroupFooter` |

**Layout eval:** `partes_consultaDetallada_principal` (sin grupo en backup). Lab manual puede usar otra variante.

**Idempotencia:** `details` contiene `noop:addGroupByField:ya_agrupado`; clave `partes.emissionDesign.group.alreadyGrouped`.

**Reparación:** header con GroupField sin `GroupFooter_{field}` → apply añade solo footer (`applied >= 1`).

### 3.2 Fuera de alcance

- CHK-19 (expresión «Cliente:» y estilos en header de grupo)
- CHK-20 (`addGroupSum`)
- CHK-21 (segundo nivel en un turno)
- LAB-21 en esta oleada

---

## 4. Criterios de aceptación

| ID | Criterio |
|----|----------|
| CA-B6-01 | Heurística / matriz: prompt canónico → una acción `addGroupByField` / `clienteNombre` |
| CA-B6-02 | Apply inicial en layout sin grupo: header + footer 28px + GroupField |
| CA-B6-03 | Re-apply: noop `ya_agrupado` + mensaje i18n en SC |
| CA-B6-04 | L-B-10 sin regresión (banner Guardar/Descartar tras apply exitoso) |
| CA-B6-05 | Script `eval-emission-ia-oleada6-apply.ps1` PASS (con sidecar) |

---

## 5. Contrato sidecar (extensión oleada 6)

Semántica ampliada de **`addGroupByField`** (sin nuevo `type`):

- Crea `GroupHeaderBand` + `GroupFooterBand` vacío emparejado (`GroupFooter_{safeName}`).
- No crea totales (CHK-20).

Implementación: `LayoutGroupByFieldApplier.cs`.