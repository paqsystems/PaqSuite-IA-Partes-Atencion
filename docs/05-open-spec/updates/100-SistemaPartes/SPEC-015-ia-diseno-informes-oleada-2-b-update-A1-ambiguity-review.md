# Revisión de ambigüedad — SPEC-015-update oleada 2 (camino B)

**SPEC:** [SPEC-015-ia-diseno-informes-oleada-2-b-update.md](./SPEC-015-ia-diseno-informes-oleada-2-b-update.md)  
**Fecha:** 2026-11-10 · Parte **A1**

## Resultado general

- **Estado: Apto con observaciones** — Listo para **Parte B (HU)** tras cerrar dos decisiones humanas (spike aplicador y alcance v1 de dictado/imagen).

## Ambigüedades críticas

Ninguna que impida HU; el **mecanismo de aplicación** (JS designer vs patch REPX) queda explícitamente como spike **Must** en §6 (dos implementadores podrían divergir **solo** en D, no en comportamiento visible).

## Ambigüedades menores

- Catálogo exacto de **acciones whitelist** (nombres y parámetros) no está enumerado en SPEC — debe detallarse en TR.
- Ruta API turno (`…/emissions/design/{processCode}/assistant/turn` u otra) — borrador en producto 19; confirmar en TR con envelope GEN-20.
- Relación con **SPEC-016 / TR-016** — marcar obsolescencia funcional en HU-update.

## Supuestos detectados

- Sidecar expone schema correcto por `processCode` (CC-ST #3 Framework).
- `SmartCapturePanel` admite montaje bajo `renderDesigner` sin conflicto de layout.
- Usuario con `emission.design` es el mismo actor que en SPEC-015 oleada 1.

## Preguntas para decisión humana

1. **Spike:** ¿prioridad patch REPX en sidecar vs mutación vía API web del diseñador en el host?
2. **L-B-08:** ¿v1 solo texto en SC o incluir voz desde el primer corte?

## Recomendaciones de ajuste del SPEC

- En Parte C (TR), anexar **tabla de acciones v1** (mínimo: agrupar, subtotal, título, orientación simple).
- En HU, reutilizar criterios §4.4 #1–#8 adaptados a flujo SC (sin wizard DX).

## Veredicto HU

**Proceder a Parte B** con observaciones anteriores documentadas en la HU como supuestos / dependencias spike.
