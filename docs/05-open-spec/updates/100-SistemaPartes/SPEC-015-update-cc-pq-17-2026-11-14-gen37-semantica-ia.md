# SPEC-015-update — GEN-37 semántica para IA diseño

Jira: [PIAPARTES-53](https://paqsystems.atlassian.net/browse/PIAPARTES-53) (Epic IA; label `update` / `GEN-37`)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-cc-pq-17-gen37-ia |
| Título | GEN-37 — description + synonyms en schema Consulta detallada (IA diseño) |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Pendiente de Revisión** (D 2026-11-14) |
| Última actualización | 2026-11-14 |
| SPEC base | [SPEC-015](../../100-SistemaPartes/SPEC-015-ia-diseno-informes.md) |
| SPEC oleada 2 B | [SPEC-015-update oleada 2 B](./SPEC-015-ia-diseno-informes-oleada-2-b-update.md) |
| Origen | `00-ControlCalidad-PQ` · CC #17 · **14/11/2026** |
| SoT Framework | GEN-37 D5 — si hay IA diseño, `description`+`synonyms` **Must** |
| HU / TR | [HU-015-update GEN-37](../../../03-historias-usuario/updates/100-SistemaPartes/HU-015-update-cc-pq-17-2026-11-14-gen37.md) · [TR-015-update GEN-37](../../../04-tareas/updates/100-SistemaPartes/TR-015-update-cc-pq-17-2026-11-14-gen37.md) |

**Plantilla regla 19:**  
IA diseño Partes: consumir **mismo** JSON GEN-15/37 del proceso `partes.informes.consultaDetallada` (`label`+`description`+`synonyms`). No catálogo paralelo en planner ni sidecar.

---

## 1. Alcance

- Completar `description` y `synonyms` en **todos** los fields de `partes.informes.consultaDetallada.json`.
- Planner / LLM del turno SC: incluir vocabulario semántico del JSON (no hardcode de producto en Framework).
- Smoke: frase con sinónimo (p. ej. «sumá las horas») → acción sobre `duracionMinutos`.
- R-37-04: sin sinónimo que mapee a dos `name` distintos.

## 2. Fuera de alcance

- Ampliar IA a `paqueteHoras` (L-B-01).
- Reabrir camino A Prompt-to-Report.
- Cambiar whitelist de acciones layout.

## 3. Criterios

- [ ] CA-37-02 / CA-37-06 (CC #17)
- [ ] Prompt LLM lista name + label + synonyms del schema host

## 4. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-14 | Parte G CC #17 |
