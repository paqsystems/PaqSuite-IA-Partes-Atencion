# Planes D1 — 100 Sistema Partes

Planes de implementación (parte D1) antes de ejecutar D.

| Plan | TR | Estado deps |
|------|-----|-------------|
| [D1-TR-002](./D1-TR-002-identidad.md) | Identidad | TR-001 **hecho** → **D hecho** |
| [D1-TR-003](./D1-TR-003-maestros.md) | Maestros | Bloquea: TR-002 |
| [D1-TR-004](./D1-TR-004-carga-diaria.md) | Carga diaria | Bloquea: TR-002 + TR-003 |
| [D1-TR-005](./D1-TR-005-masivo.md) | Masivo | F1 OK (obs.); Must+Should |
| [D1-TR-006](./D1-TR-006-consultas-dashboard.md) | Consultas/dashboard | Bloquea: TR-002…005 |
| [D1-TR-007](./D1-TR-007-mobile.md) | Mobile | Bloquea: TR-002…006 + scaffold Capacitor |
| [D1-TR-008](./D1-TR-008-asistente-ia.md) | Asistente IA chat documental | TR-002 shell; paquetes Framework GEN-16/21; C1 OK |
| [D1-TR-009](./D1-TR-009-importacion-excel.md) | Importación Excel (GEN-14) en Carga diaria | TR-004 carga; paquetes GEN-14; C1 OK 2026-08-02 |
| [D1-TR-011](./D1-TR-011-reportes-emisiones.md) | Reportes / emisiones (GEN-15) en Consulta detallada | TR-006 informes; paquetes GEN-15; C1 OK 2026-08-25 |

**Decisiones D1 absorbidas (2026-07-30):**
- Masivo: tope técnico **5000** si `PartesMasivoMaxIds=0` → 422 `partes.masivo.loteDemasiadoGrande`
- Dashboard post-login producto = `/partes`
- Paquete horas: orquestación PHP Must; Chart DX `bar`
- Carga: `Programa=Partes` para params GRAL

**Decisiones D1 TR-008 (2026-08-01):**
- Persistencia LLM: **SP** (`SpLlmCredentialRepository`); no Eloquent smoke
- Active credential: **`pq_sp_llm_active_preference_*`**
- Preferencias BYOK: **modal** Must (sin página `/preferences` Must)
- Tope corpus v1: **~28 KiB** texto agregado

**Decisiones D1 TR-009 (2026-08-02):**
- Staging Excel: **SP contract** (+ ops MONO/sqlite); no Eloquent smoke como prod
- Toolbar: fila entre filtros y grid; `processCode=partes.tareas.import`
- GEN-17 bandeja: no Must; async `queued` = toast + sin refresh
- Fixture tests: `ExcelImportEnabled=S`; prod seed default `N`

**Decisiones D1 TR-011 (2026-08-25):**
- Catálogo/jobs emisión: **SP contract** (`SpEmissionRepository`); no Eloquent smoke
- `hostContext` FE: interceptor `fetch` (body); Dialog GEN intacto
- `hostContext` async: Cache por `jobId`
- DX: MinimalDx + diseñador placeholder
- Authz emitir: `MenuProcedimientoChecker`; dispatcher sync-noop
- `EmissionEnabled=S` adopción; 4704 tests fuerzan `N`
