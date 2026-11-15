# SPEC-011-update — GEN-37 catálogo semántico (emisiones)

Jira: [PIAPARTES-11](https://paqsystems.atlassian.net/browse/PIAPARTES-11) (Epic base; label `update` / `GEN-37`)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-011-update-cc-pq-17-gen37 |
| Título | Adopción GEN-37 — labels (y captionKey) en schemas diseñador emisiones |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Pendiente de Revisión** (D 2026-11-14) |
| Última actualización | 2026-11-14 |
| SPEC base | [SPEC-011-reportes-emisiones](../../100-SistemaPartes/SPEC-011-reportes-emisiones.md) |
| Origen | `00-ControlCalidad-PQ` · CC #17 · **14/11/2026** |
| SoT Framework | GEN-37 · [37-catalogo-semantico…](../../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/37-catalogo-semantico-campos-dataset.md) · [SPEC-001-37](../../../../PaqSuite-IA-FRAMEWORK/docs/05-open-spec/001-Generalidades/SPEC-001-37-catalogo-semantico-campos-dataset.md) |
| HU / TR | [HU-011-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-011-reportes-emisiones-update-cc-pq-17-2026-11-14-gen37.md) · [TR-011-update](../../../04-tareas/updates/100-SistemaPartes/TR-011-update-cc-pq-17-2026-11-14-gen37.md) |
| Complemento IA | [SPEC-015-update GEN-37](./SPEC-015-update-cc-pq-17-2026-11-14-gen37-semantica-ia.md) |

**Plantilla regla 19:**  
Emisiones Partes: adoptar **GEN-37** sobre el mismo artefacto GEN-15 `emission-designer-schemas/{processCode}.json`. No reimplementar catálogo ni hardcode en sidecar. SoT: Framework SPEC-001-37. Host: contenido `label` (+ `captionKey`) por proceso emisible DX.

---

## 1. Cambio de alcance

| Antes (SPEC-011) | Ahora (este update) |
|------------------|---------------------|
| Schema diseñador con `name`+`type` (Field List técnico) | Todo field con **`label`** humano (ES fallback) |
| captionKey no exigido | **Recomendado** `captionKey` → i18n host (5 locales) |
| Deuda GEN-37 explícita en Framework | Host **cierra** contenido; wire `name` **sin cambio** |

## 2. ProcessCodes

| processCode | label Must | captionKey | description/synonyms |
|-------------|------------|------------|----------------------|
| `partes.informes.consultaDetallada` | Sí | Recomendado | **Must** (ver SPEC-015-update GEN-37) |
| `partes.informes.paqueteHoras` | Sí | Recomendado | Recomendado (sin IA diseño en MVP L-B-01) |

## 3. Fuera de alcance

- Redefinir contrato JSON Schema (Framework).
- Cambiar claves wire del dataset / puertos EmissionDataset.
- Catálogo paralelo, ABM runtime de labels, hardcode C# por producto.
- Mezclar con captions GEN-14 Excel.

## 4. Criterios verificables

- [ ] CA-37-01 / CA-37-03 / CA-37-04 / CA-37-05 / CA-37-07 (CC #17)
- [ ] Manifiesto + validate V-EM-04 OK
- [ ] Tests unit host: cada field tiene `label`

## 5. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-14 | Parte G CC #17 — SPEC-update GEN-37 adopción host |
