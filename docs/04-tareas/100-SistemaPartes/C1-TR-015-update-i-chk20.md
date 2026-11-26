# C1 — Revisión de ambigüedad TR-015-update-i (CHK-20)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-i](./TR-015-update-i-group-sum-minutos-chk20.md) |
| Fecha | 2026-11-24 |

## Resultado general

**Apto para D1** — conflicto footer doble identificado y acotado en TR; planner y matriz alineados.

## Críticas

Ninguna bloqueante.

## Menores

| # | Observación | Mitigación en D1 |
|---|-------------|------------------|
| M1 | Implementación actual crea `GroupFooter_{campoSuma}` — incumple CHK-18/20 | TR §2: reutilizar pie de grupo |
| M2 | Copy actual `Total duracionMinutos:` vs matriz «Total minutos» | Alinear expresión ACT-03 |
| M3 | `XRSummary` vs `sumSum` en XML serializado | Test assert needles; ajustar binding si falta `sumSum` |
| M4 | Noop suma sin sufijo dedicado hoy (`noop:addGroupSum`) | Opcional `ya_totalizado` para script re-apply |

## Contradicciones SPEC / HU / TR

Ninguna.

## Supuestos validados

- PHPUnit `test_heuristics_group_and_sum` ya cubre ambas acciones (reforzar orden).
- Apply es secuencial en un request (regla 11 batch / TR-015-update-b).
- CHK-19 header independiente del subtotal footer.

## Preguntas abiertas

Ninguna bloqueante.

## Veredicto

**Puede pasar a D1/D: Sí**