# Verificación host — CC Framework #20 — Smart Capture turno accionable

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-11-15 |
| **Host** | PaqSuite-IA-Partes-Atención |
| **Origen** | Framework CC PQ #20 hallazgo 2 (Popup genérico en carga diaria) |
| **SoT Framework** | [F1](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/main/docs/08-control/f1-20261115-GEN-03-cc20-verificacion-agente.md) · [F](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/main/docs/08-control/f-20261115-GEN-03-cc20-verificacion-implementacion.md) · [I](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/main/docs/08-control/i-20261115-GEN-03-cc20-cierre-documental.md) |
| **SPEC/HU/TR host** | [SPEC-010](../../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) · [HU-010](../../03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md) · [TR-010](./TR-010-smart-capture-carga-diaria.md) |
| **Autorización** | Usuario 15/11: dar por Finalizado Smart Capture en carga de partes (residual smoke incluido) |

## Resultado

**Aprobado con observaciones** (smoke UI CA-FW-20-04/05 residual autorizado).

No hay SPEC-update en Partes: el original SPEC-010 ya estaba **Finalizado**; se documenta R-SC-36 / CA-36 / RN-TR-26.

## Evidencia código (D 14/11, sin cambio 15/11)

| Pieza | Contrato |
|-------|----------|
| `PartesTareaSmartCaptureTurnService` | LLM/build sin fields → **4290** / `partes.smartCapture.llmFailed` / 502 |
| `partesSmartCaptureTurn.ts` | `envelopeError` → `respuesta` i18n; sin credencial → `configurationRequired`; 5xx → `turnError` |
| i18n 5 locales | `partes.smartCapture.llmFailed` |
| PHPUnit | `ApiV1PartesSmartCaptureTurnTest` assert 4290 |
| Vitest | `partesSmartCaptureTurn.test.ts` (`llmFailed`) |
| OpenAPI | `OpenApiPathsPartesOperacion` 409 (4201) + 502 (4290) |

## Residual

Smoke manual carga diaria + BYOK real; Playwright `partes-smart-capture.spec.ts` ambiental (14/11).
