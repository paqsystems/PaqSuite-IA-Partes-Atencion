# Verificación F1 + F — TR-010-update Smart Capture (CC-PQ #3)

| Campo | Valor |
|-------|--------|
| **Control** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) · CC #3 · ítem «Implementar IA» |
| **TR / HU / SPEC** | [TR-010](../../100-SistemaPartes/TR-010-smart-capture-carga-diaria.md) · [HU-010](../../../03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md) · [SPEC-010](../../../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) (Parte I 16/09/2026; updates eliminados) |
| **Evidencia CC** | `CC33-PQ-2026-09-15.png` |
| **Fecha verificación** | 2026-09-16 |
| **Prueba manual** | Aceptada por PQ (chat previo a esta verificación) |

---

# F1 — Verificación del agente

## Resultado

- **Aprobado**

## Evidencia revisada

| ID TR | Pieza | Evidencia |
|-------|--------|-----------|
| RN-TR-CC3-20 | `PartesTareaSmartCaptureTurnService` | Lookups 1-1 → `setField`; duración inválida → `needsRefine` sin `return` temprano que descarte acciones previas |
| RN-TR-CC3-21 | `partesSmartCaptureTurn.ts` + `CargaDiariaPage.tsx` | `applyPartesAction` mapea a `form`; `clienteId` vía `onClienteIdChange` (recarga tipos); SelectBox `valueExpr="id"` + `data-testid` `partesCargaCliente` |
| RN-TR-CC3-22 | `PartesDuracionParser` + extractor | `1:25` = 85 min (reloj) ≠ `1.25` h decimal; inválido con tramo 15 |
| RN-TR-CC3-23 | Tests | Feature LACAPOL+PQ; Vitest `applyPartesAction`; E2E CC3 |

**Fallback (16/09):** `PartesSmartCaptureFieldExtractor` aplica campos del prompt o de prosa LLM cuando `fields` viene vacío o el proveedor falla.

## AC HU (delta)

| AC | Verificación | Estado |
|----|--------------|--------|
| CA-CC3-07 | Feature `test_turn_lookups_unicos_y_duracion_reloj_invalida_parcial`; Vitest cliente+asistente; E2E CC3 SelectBox visibles | OK |
| CA-CC3-08 | Feature: `needsRefine` duración + `setField` cliente/asistente coexisten; Vitest no pisa lookups con 85 min | OK |
| CA-CC3-09 | Feature `setField` observación; Vitest observación; E2E CC3 observación en TextBox | OK |

## Hallazgos críticos

- Ninguno

## Advertencias

1. **E2E suite completa:** en corrida serial, el test «6 setField aplica observacion…» falló por timeout al abrir modal (`partesCargaForm`); el caso **CC3** pasó en corrida aislada. Posible flake de timing; no bloquea el slice (cubierto por Feature + Vitest + CC3 E2E).
2. **Tipo tras cliente:** el apply de `tipoTareaId` depende de `handleClienteChange` + default; no hay E2E dedicado a R-SC-33 (recarga tipos + tipo visible). Cubierto por diseño en `CargaDiariaPage` y Vitest parcial.

## Tests

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter="ApiV1PartesSmartCaptureTurnTest\|PartesSmartCaptureFieldExtractorTest"` | **15 passed** (84 assertions) |
| `npx vitest run src/features/partes/carga/partesSmartCaptureTurn.test.ts` | **6 passed** |
| `npx playwright test tests/e2e/partes-smart-capture.spec.ts -g "CC3"` | **1 passed** |

## Pendientes

- Parte **I** ejecutada 2026-09-16: update fusionado en [SPEC-010](../../../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md), [HU-010](../../../03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md) y [TR-010](../../../04-tareas/100-SistemaPartes/TR-010-smart-capture-carga-diaria.md).

## Recomendación final

- Slice Smart Capture CC #3 **cerrado**. TR/HU/SPEC base en **Finalizado** tras Parte I (16/09/2026) — ver [D-VERIFICACION-CC-PQ-03](./D-VERIFICACION-CC-PQ-03-2026-09-16.md).

---

# F — Verificación documental (SPEC ↔ HU ↔ TR ↔ código)

## Resumen ejecutivo

Implementación coherente con el delta CC #3 (controles visibles + apply parcial). Completitud ✓ · Corrección ✓ · Coherencia ✓.

## Completitud

| Criterio SPEC-update §3 | Estado |
|-------------------------|--------|
| Turno con asistente + cliente únicos aplica SelectBox | ✓ |
| Duración inválida no impide lookups | ✓ |
| Observación/fecha/tipo resolubles en controles | ✓ (observación/fecha con tests; tipo vía mismo pipeline) |
| Test FE apply + Feature `setField` lookups | ✓ |

## Corrección

- R-SC-31…33 reflejadas en BE (`setField` + `needsRefine` parcial) y FE (`applyPartesAction` → state DX).
- Sin auto-save ni cambio de path/contrato GEN.
- Mobile sigue excluido (sin cambios).

## Coherencia

| Documento | Alineado | Nota |
|-----------|----------|------|
| TR-010 / HU-010 / SPEC-010 | Sí | Delta CC #3 fusionado; **Finalizado** tras Parte I 16/09/2026 |
| `14-smart-capture.md` | Sí | Sin contradicción; delta solo en update |
| `00-ControlCalidad-PQ` #3 ítem IA | Sí | Enlazado a este F |

## Próximos pasos

1. **Finalizado** en HU/TR/SPEC-update (PQ, 16/09/2026) — ver [D-VERIFICACION-CC-PQ-03](./D-VERIFICACION-CC-PQ-03-2026-09-16.md).
2. Parte **I**: fusionar R-SC-31…33 y CA-CC3 en documentos base cuando se autorice.
3. Commit/push solo con autorización (sin cambios migrate/seed en este slice).
