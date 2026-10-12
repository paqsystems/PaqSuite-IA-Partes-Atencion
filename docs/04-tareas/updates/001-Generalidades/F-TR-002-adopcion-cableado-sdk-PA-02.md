# F — Verificación documental TR-002 PA-02 (openspec-05)

Fecha: 2026-10-11 · Tras F1 PA-02

| Campo | Valor |
|-------|--------|
| **TR** | [TR-002](../../001-Generalidades/TR-002-adopcion-cableado-sdk.md) (slice PA-02) |
| **HU** | [HU-002](../../../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) |
| **SPEC** | [SPEC-002](../../../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) |
| **F1** | [F1 PA-02](./F1-TR-002-adopcion-cableado-sdk-PA-02.md) — Aprobado |
| **Origen** | CC-PQ #6 · PA-02 |
| **Resultado F** | **Aprobado** |

## Alcance contrastado

GEN-20: eliminar `ApiEnvelope` del host; auth y `GET /api/v1/system/status` usan `PaqSuite\LaravelCore\Http\Responses\ApiResponse`. Éxito: `error === 0`, `resultado` objeto. No UI. PA-03…PA-14 fuera de este F.

## Matriz CA

| CA | Veredicto | Evidencia |
|----|-----------|-----------|
| AC-ENV-01 / CA-ENV-01 | **OK** | No existe `backend/app/Support/ApiEnvelope.php`. `AuthController` y `SystemStatusController` importan `ApiResponse`. Login real (`LoginController`) ya usaba el SDK. PHPUnit 7/7. |
| R-SDK-ENV-01 | **OK** | Feature: login y status `assertSame(0, json('error'))` y `resultado` array. |
| R-SDK-ENV-02 | **OK** | Sin helper host que serialice `error` null o `error` como objeto. |

## Tests (Parte E)

F1 Aprobado. Manual de usuario: no aplica (ops/contrato API).

## Observaciones no bloqueantes

1. Schema OpenAPI sigue llamándose `ApiEnvelope` (forma JSON); no es la clase PHP.
2. `AuthController` del scaffold no está enrutado; se alineó igual para no dejar un segundo contrato.

## Parte I (esta corrida)

Slice PA-02 promovido a originales en `001-Generalidades`. El SPEC/HU/TR-002-**update** permanece para PA-03…PA-14. No se borra la familia update.

## Veredicto

PA-02 cerrado. Cola CC #6 siguiente: PA-03 (GEN-06 usuarios).
