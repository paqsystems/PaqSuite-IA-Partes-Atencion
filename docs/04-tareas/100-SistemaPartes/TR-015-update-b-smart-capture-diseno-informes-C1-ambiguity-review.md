# TR-015-update-b — Revisión de ambigüedad (Parte C1)

| Campo | Valor |
|-------|-------|
| TR | [TR-015-update-b-smart-capture-diseno-informes](./TR-015-update-b-smart-capture-diseno-informes.md) |
| HU | [HU-015-update](../../03-historias-usuario/100-SistemaPartes/HU-015-update-ia-diseno-informes-camino-b.md) |
| SPEC | [SPEC-015-update oleada 2 B](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-2-b-update.md) |
| Fecha | 2026-11-13 (ART) |
| **Veredicto** | **Apto con observaciones** |
| Jira | [PIAPARTES-62](https://paqsystems.atlassian.net/browse/PIAPARTES-62) · HU [PIAPARTES-61](https://paqsystems.atlassian.net/browse/PIAPARTES-61) |

## Resultado general

- Estado: **Apto con observaciones**
- Alcance TR ⊆ HU ⊆ SPEC; decisiones D-01…D-10 cierran A1/spike.
- Contratos turn (Laravel GEN-20) y apply (sidecar) suficientes para D1.
- Observaciones no bloquean D1 si se aplican los ajustes menores volcados en la TR (C1).

## Ambigüedades críticas

- Ninguna que impida D1 tras el ajuste **D-11** (proxy same-origin `/api/layout` → sidecar). Sin eso, dos implementadores podrían: (a) FE→`:5055` con CORS, o (b) proxy Vite/Forge, o (c) proxy Laravel — contratos distintos.

## Ambigüedades menores

1. **Tipo desconocido en `actions[]`:** §5.1 decía “422 / omitir” (doble vía). **Cierre C1:** en turno LLM → **omitir** acción inválida + `replyText`; **422** solo si el cliente POST apply/turn manda `type` fuera de whitelist de forma explícita malformada.
2. **Diseñador con cambios sin guardar:** apply escribe REPX en disco + refresh → se pierden edits in-memory. **Cierre C1:** antes de apply, si el host detecta dirty → confirmar (i18n); al aplicar, refresh descarta in-memory (documentar L-B-11).
3. **`reportUrl` obligatorio:** informe recién creado debe tener `reportUrl` de catálogo GEN antes del turno (flujo TR-011 create). Si no hay variante → no habilitar turn / mensaje claro.
4. **Auth sidecar apply:** “alineada a DXXRD” era vaga. **Cierre C1:** v1 = mismo alcance de red que DXXRD (proxy same-origin); sin API key en browser; endurecimiento (key/server-side) = Should/runbook si hace falta en Forge.
5. **Envelope sidecar:** apply responde `{ ok, applied, … }` (no GEN-20). FE mapea a mensaje i18n; no confundir con envelope Laravel.
6. **§10 vs §5.1 `setPageOrientation`:** §10 sugería diferir; **Cierre C1:** sigue **Must** en whitelist; si el apply C# falla en lab, documentar gap en F1 y no bloquear el resto de ACT-01..03.
7. **E2E Playwright:** Should (TR §1/§8); F1 Must = checklist manual — coherente.

## Contradicciones TR ↔ HU ↔ SPEC

- Ninguna de alcance.
- Mecanismo aplicador: SPEC dejaba abierto JS vs REPX; TR D-02 cierra con spike B (válido).
- TR-016 marcado obsoleto: alineado a SPEC metadatos.

## Supuestos detectados

- Schema `partes.informes.consultaDetallada.json` y `GetDesignerModel`+`processCode` ya desplegados (TR-015 / Framework).
- BYOK GEN-16 usable como en TR-010 (mismo vault usuario; distinto endpoint).
- `SmartCapturePanel` montable bajo `renderDesigner` sin fork GEN.
- Spike scripts `SpikeApplyAct0*` son base del servicio C# productivo (T1).

## Preguntas para decisión humana

Ninguna **bloqueante**. Opcional para release:

1. ¿En Forge el proxy de `/api/layout` vive en Vite/nginx del front o se prefiere proxy Laravel en oleada posterior? (C1 asume **proxy front same-origin** v1.)

## Recomendaciones de ajuste de la TR (aplicadas en C1)

- Añadir **D-11** (proxy `/api/layout`).
- Unificar rechazo de `type` inválido.
- UX dirty designer + requisito `reportUrl`.
- Aclarar respuesta sidecar ≠ envelope GEN-20.
- Enlazar este informe en metadatos TR; Estado → **Especificado**.

## Veredicto

- **Puede pasar a D1/D: Sí** (con observaciones menores arriba; críticas mitigadas en TR)
