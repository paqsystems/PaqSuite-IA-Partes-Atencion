# TR-013-update — Duración paquetes 1–240 h — CC PQ #8

| Campo | Valor |
|-------|--------|
| **ID** | TR-013-update-cc-pq-8-2026-10-25-duracion-paquetes |
| **Estado** | **Especificado** (Parte C 2026-10-30; C1 apto D1) |
| **Prioridad** | P0 |
| **TR base** | [TR-013-carga-paquetes-horas](../../100-SistemaPartes/TR-013-carga-paquetes-horas.md) |
| **HU-update** | [HU-013-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) |
| **SPEC-update** | [SPEC-013-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) |
| **Jira** | [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52) |
| **Última actualización** | 2026-10-30 |

## 1) Resumen

Bajar el piso de duración de paquetes de **8 h** a **1 h** (60 min), mantener techo **240 h** (14400 min), **default alta = 1 h**, coherencia UI / API / SP / Smart Capture / i18n / tests.

## 2) Criterios de aceptación (TR)

| ID | Criterio | HU |
|----|----------|-----|
| AC-01 | 1 h y 240 h OK; 0 / 241 / no entero → rechazo | CA-01 |
| AC-02 | Mismas reglas en todas las capas | CA-02, CA-05, CA-06 |
| AC-03 | Regresión 8–240 h | CA-03 |
| AC-04 | Envelope i18n | CA-04 |
| AC-05 | Default modal 1 h | CA-07 |
| AC-06 | Editar paquete 2 h OK | CA-08 |

## 3) Regla numérica (única)

| Concepto | Valor |
|----------|--------|
| Horas enteras UI | 1 … 240 |
| Minutos persistidos | 60 … 14400 |
| Step | 60 minutos |
| Default alta | 1 h (60 min) |

## 4) Cambios por capa

### 4.1 Frontend

| Archivo | Cambio |
|---------|--------|
| `frontend/src/features/partes/carga/partesPaqueteDuration.ts` | `PAQUETE_MIN_HORAS = 1`; `buildPaqueteDuracionOptions` rango 1–240 |
| `CargaPaquetesPage` / modal persist | Estado inicial duración = 60 min en **alta** |
| `cargaPaquetesPersistValidation` (si aplica) | Límites 60–14400 |
| `partesPaqueteSmartCaptureTurn.ts` | Validar parseo horas ∈ [1,240] entero |
| i18n `partes.paquete.validation.*` | Textos «1–240» (es, en, pt, fr, it) |

### 4.2 Backend API

| Pieza | Cambio |
|-------|--------|
| FormRequest / rules `PartesPaqueteController` | `min:60`, `max:14400`, múltiplo 60 |
| `PartesPaqueteOperations` (sqlite) | Misma regla si valida allí |

### 4.3 Stored procedures

| Archivo | Cambio |
|---------|--------|
| `backend/scripts/sql/partes/pq_sp_partes_paquete_upsert.sql` | Línea validación: `@p_duracion_minutos < 480` → **`< 60`** |
| `pq_sp_partes_paquete_list/get` | Solo si contienen CHECK de rango (revisar; list/get típicamente no) |

RAISERROR existente: `partes.paquete.duracionInvalida` (sin cambio clave).

### 4.4 Tests

| Archivo | Cambio |
|---------|--------|
| `partesPaqueteDuration.test.ts` | Primer option `{ horas: 1, minutos: 60 }` |
| `cargaPaquetesPersistValidation.test.ts` | Límite inferior 1 h |
| Feature API paquetes | POST 1 h OK; 30 min 422 |
| SC tests | Ajustar defaults 8 h → 1 h donde sea **default de form**, no escenario explícito 8 h |

## 5) API (sin cambio rutas)

`POST/PUT /api/v1/partes/paquetes` — body `duracionMinutos` Must ∈ [60, 14400] step 60.

## 6) Plan de tareas (D)

| ID | Descripción | DoD |
|----|-------------|-----|
| T1 | SP upsert + deploy-sp | AC-01, AC-02 |
| T2 | API validation | AC-01 |
| T3 | FE helpers + modal default | AC-01, AC-05 |
| T4 | SC + i18n | AC-02, AC-04 |
| T5 | Tests + OpenAPI | AC-03 |
| T6 | Doc producto `16-carga-paquetes-horas.md` | CA-09 HU |

## 7) Tests mínimos

- POST paquete 60 min → 201.
- POST 0, 30, 14460 min → 422.
- PUT paquete existente 120 min sin cambio forzado.
- SC turno «2 horas» → draft 120.

## 8) Fuera de alcance TR

Informe saldo (TR-006-update). Migración datos (no hay paquetes &lt; 8 h válidos legacy).

## 9) C1 — veredicto

**Apto para D1** — delta acotado; archivos concretos; sin ambigüedad de rango.
