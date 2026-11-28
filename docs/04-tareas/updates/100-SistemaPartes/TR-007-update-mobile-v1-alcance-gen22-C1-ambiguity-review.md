# Revisión de ambigüedad C1 — TR-007-update mobile v1

**TR:** [TR-007-update-mobile-v1-alcance-gen22.md](./TR-007-update-mobile-v1-alcance-gen22.md)  
**HU:** [HU-007-update-mobile-v1-alcance-gen22.md](../../../03-historias-usuario/updates/100-SistemaPartes/HU-007-update-mobile-v1-alcance-gen22.md)  
**SPEC:** [SPEC-007-mobile-v1-alcance-update.md](../../../05-open-spec/updates/100-SistemaPartes/SPEC-007-mobile-v1-alcance-update.md)  
**Fecha:** 27/11/2026 · Parte **C1** · Jira PIAPARTES-85

## Resultado general

- **Estado: Apto** — Tras cerrar decisiones **D-MV1-C1-01 … D-MV1-C1-05** en la TR §4.1, dos implementadores no deberían divergir en post-login, menú, policy ni división carga/consulta legacy. **Puede pasar a D1/D: Sí.**

## Ambigüedades críticas (resueltas en TR §4.1)

| ID | Tema | Riesgo (antes) | Cierre |
|----|------|----------------|--------|
| **C1-C1** | `resolveNativeMobileHomePath` sin menú API cargado en login | Uno usa `/partes/carga` fijo; otro espera `onItemsLoaded` | **D-MV1-C1-01** |
| **C1-C2** | Cadena `firstLogin` → cambio clave → destino | Vuelta a `/partes` dashboard | **D-MV1-C1-02** |
| **C1-C3** | Ruta native `/partes/consulta` vs policy | Redirect silencioso a consulta detallada | **D-MV1-C1-03** |
| **C1-C4** | UX `/partes/carga`: solo form vs kardex+form | Paridad distinta a HU | **D-MV1-C1-04** |
| **C1-C5** | Menú API con `/partes/carga-diaria` sin mapper | Tap menú cae en ruta denegada | **D-MV1-C1-05** (RN-MV1-TR-09 **MUST**) |

## Ambigüedades menores

| ID | Tema | Tratamiento |
|----|------|-------------|
| C1-M1 | Paginación consulta detallada `pageSize=0` en mobile | D: si &gt; N filas, usar `sliceKardexPage` GEN o acotar periodo; no nuevo endpoint |
| C1-M2 | Unit `resolveNativeMobileHomePath` | Recomendado en D1; no bloquea |
| C1-M3 | Gráfico paquete horas | Opcional v1 — ya alineado HU/TR §11 |
| C1-M4 | Conservar archivo `ConsultaKardexMobilePage.tsx` | Tras extraer form, **eliminar** binding a router; archivo puede quedar hasta refactor T5 |

## Contradicciones TR ↔ HU ↔ SPEC

| Tema | Resolución |
|------|------------|
| Gráfico bar TR-007 vs opcional v1 | Documentado §11 TR — **v1 no exige** |
| RN-MV1-TR-06 texto «Navigate prohibido» | Cerrado D-MV1-C1-03 — solo deny/redirect home |

Ninguna bloqueante abierta.

## Supuestos detectados

- APIs `fetchInformeTareas`, `listTareas`, `fetchPaqueteHoras` y envelope GEN-20 sin cambio (coherente SPEC §7).
- `createMobilePolicy`: allowlist **explícita** sin prefijo `/partes`; no hace falta entrada `/partes` para hijos si cada ruta de proceso está listada (GEN `matchesAllowlistEntry`).
- Config/login GEN-22 ya operativo en rama (CA-MV1-06 hereda TR-007 D).

## Preguntas para decisión humana

Ninguna tras §4.1 (27/11/2026).

## Recomendaciones para D1

1. Documento `D1-TR-007-update-mobile-v1` con orden T1…T9 y archivos exactos.
2. Smoke web (CA-MV1-10) en el mismo PR que policy.
3. No reintroducir `/partes` en allowlist aunque el router redirija.

## Checklist C1 (resumen)

| # | Eje | OK |
|---|-----|-----|
| 1 | Alcance TR ⊆ HU ⊆ SPEC | Sí |
| 2 | AC ↔ tareas T1–T10 | Sí |
| 3 | API / envelope | Sí (reuso; sin huecos) |
| 4 | Seguridad / policy | Sí (cerrado C1-C3) |
| 5 | Seed / datos | N/A v1 |
| 6 | Flujos UI login-shell | Sí (C1-C1/C1-C2) |
| 7 | Tests §8 | Sí |
| 8 | Coherencia interna TR | Sí tras §4.1 |

## Veredicto

- **Puede pasar a D1/D: Sí**