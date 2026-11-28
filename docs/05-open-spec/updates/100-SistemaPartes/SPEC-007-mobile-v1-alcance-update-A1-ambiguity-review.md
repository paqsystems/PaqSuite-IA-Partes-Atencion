# Revisión de ambigüedad — SPEC-007-update mobile v1

**SPEC:** [SPEC-007-mobile-v1-alcance-update.md](./SPEC-007-mobile-v1-alcance-update.md)  
**Fecha:** 27/11/2026 · Parte **A1** · Jira PIAPARTES-83

## Resultado general

- **Estado: Apto** — Tras cerrar decisiones **D-MV1-A1-01 … D-MV1-A1-04** en el SPEC-update §2.1, dos implementadores no deberían divergir en policy, rutas ni alcance CRUD v1. Avanzar a **Parte B** (HU).

## Ambigüedades críticas (resueltas en SPEC)

| ID | Tema | Riesgo (antes del cierre) | Cierre |
|----|------|---------------------------|--------|
| **A1-C1** | Aterrizaje post-login sin dashboard | Un dev monta `/partes`; otro hub de tiles | **D-MV1-A1-01** |
| **A1-C2** | Ruta native de carga vs menú web | `/partes/carga` vs reutilizar `/partes/carga-diaria` en router | **D-MV1-A1-02** |
| **A1-C3** | CRUD en consulta detallada kardex | Lectura vs alta/editar en mismo proceso | **D-MV1-A1-03** |
| **A1-C4** | Allowlist shell (`/dashboard`, chat, change-password) | Policy inconsistente con CA-MV1-02 | **D-MV1-A1-04** |

## Ambigüedades menores

| ID | Tema | Tratamiento |
|----|------|-------------|
| A1-M1 | Periodo default consulta detallada | **Día actual** (alineado producto `21-mobile`); HU puede repetir |
| A1-M2 | Gráficos en paquete horas mobile | **Opcional** v1; no bloquea HU |
| A1-M3 | Reuso de `ConsultaKardexMobilePage` vs pantalla nueva para consulta detallada | TR: mismo presentador kardex, **API** `GET` consulta detallada (no listado genérico `/partes/consulta`) |
| A1-M4 | Android+iOS smoke dispositivo | F1 / runbook; fuera de SPEC funcional |
| A1-M5 | Deep-link a ruta excluida | Patrón GEN: redirect + toast `mobile.exclusion.*` (TR) |

## Supuestos detectados

- Capacitor ya configurado en `frontend/` (baseline TR-007).
- Backend y SP de consulta detallada / paquete horas **sin cambio de contrato** en v1 (solo FE/policy).
- GEN-22 exports disponibles en pin react-core del host (misma oleada que `v1.3.0-CLOUDSMITH`).
- Cliente sin carga: ya resuelto SPEC-002.

## Preguntas para decisión humana

Ninguna pendiente tras §2.1 del SPEC-update (27/11/2026).

## Recomendaciones para Parte B (HU)

1. Una HU-update (o HU-007-update) que liste explícitamente CA-MV1-01…07 como criterios de aceptación.
2. Narrar **no regresión web** (`isNativeApp() === false` sin cambio de menú).
3. No reintroducir chat ni dashboard en menú native en v1.

## Checklist 10 ejes (resumen)

| # | Eje | OK |
|---|-----|-----|
| 1 | Alcance | Sí — 3 procesos + exclusiones tabla §2.2 |
| 2 | Actores | Sí — matriz §3 |
| 3 | Flujo | Sí — post-login D-MV1-A1-01 |
| 4 | Reglas negocio | Sí — R-MV1-01…08 + herencia SPEC-004/006 |
| 5 | Datos | Sí — mismas APIs web |
| 6 | UI | Sí — kardex + formulario carga |
| 7 | APIs | Sí — reutilización explícita |
| 8 | Casos especiales | Observación menor deep-link (A1-M5) |
| 9 | CA | Sí — CA-MV1-01…07 testeables |
| 10 | Trazabilidad | Sí — SPEC base + producto + PIAPARTES-83 |

## Veredicto

- **Puede pasar a HU: Sí**