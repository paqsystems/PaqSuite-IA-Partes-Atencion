# Verificación F — CC PQ #14 (03/11/2026)

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#14** |
| **Parte E** | [E-CC-PQ-14-2026-11-03.md](./E-CC-PQ-14-2026-11-03.md) |
| **Veredicto F** | **Apto** — regresión automatizada OK + **smoke manual UI OK** (DEV, tenant **DEV**, PQ 03/11/2026) |
| **F1/F** | **Aprobado** 03/11/2026 — §1.1 código ↔ TR + §4 manual |
| **Parte I** | 03/11/2026 — fusionado en SPEC/HU/TR-003 base; updates CC #14 eliminados |

---

## 1) Criterios de aceptación

| CA | Resultado F | Evidencia |
|----|-------------|-----------|
| **CA-14-01** Clientes — columnas usuario y mail en grilla | **OK** | Manual DEV §4.1; `PartesMaestrosPages` + list API `usuarioCodigo`/`email` |
| **CA-14-02** Asistentes — FilterRow usuario | **OK** | Manual §4.2; Vitest + Playwright CC #14; Operations `p_usuario` |
| **CA-14-03** Clientes — FilterRow usuario (y mail) | **OK** | Manual §4.3; E2E + Unit |
| **CA-14-04** Sin regresión CC #12/#13 | **OK** | Manual §4.4; suite CC #13 sin cambio de alcance |

---

## 1.1) F1 — revisión código ↔ TR (03/11/2026)

| Pieza | Evidencia | Estado |
|-------|-----------|--------|
| Columnas grilla Clientes | `PartesMaestrosPages.tsx` — `usuarioCodigo`, `email` | **OK** |
| Mapper FilterRow FE | `maestroListPaging.ts` → `usuario`, `email`; `partesMaestrosApi.ts` | **OK** |
| API list | `PartesMaestrosController::list` — query `usuario`, `email` | **OK** |
| Operations | `usuariosList` / `clientesList` — join + `applyContainsFilter` | **OK** |
| Tests | [E-CC-PQ-14](./E-CC-PQ-14-2026-11-03.md) | **OK** (Vitest 3, Playwright 2, Unit 6) |

**Veredicto F1:** **Aprobado** (consistente con [D-CC-PQ-14](./D-CC-PQ-14-2026-11-03.md)).

---

## 2) Regresión automatizada

| Capa | Resultado |
|------|-----------|
| Vitest `maestroListPaging.test.ts` | **OK** (3) |
| Playwright `partes-cc-pq-14-filterrow-usuario.spec.ts` | **OK** (2) |
| PHPUnit Unit `PartesMaestrosOperationsFilterTest` | **OK** (6) |
| PHPUnit Feature `ApiV1PartesMaestrosTest` (CC #14) | **No OK** local — drift seed SQLite (`PQ_TASK_PROCESS.process_description`); no bloquea cierre F manual |

---

## 3) Smoke manual UI (PQ — 03/11/2026)

Entorno: `start-dev.ps1 -All` · frontend `http://localhost:3000` · backend `8010` · tenant **DEV** · repo labo SDK.

### 4.1 CA-14-01 — Clientes

1. `/archivos/partes/clientes?cliente=DEV`
2. Grilla muestra columnas **Usuario** y **Mail** coherentes con modal.

### 4.2 CA-14-02 — Asistentes FilterRow usuario

1. `/archivos/partes/asistentes?cliente=DEV`
2. FilterRow columna **usuario** → criterio parcial → filas y total acotados.

### 4.3 CA-14-03 — Clientes FilterRow usuario / mail

1. FilterRow **usuario** y **mail** → comportamiento equivalente a nombre/código.

### 4.4 CA-14-04 — Regresión

1. Paginación remota maestros operativa.
2. FilterRow nombre/código (CC #13) sin regresión en smoke rápido.

**Resultado manual:** **Aprobado** (PQ).

---

## 5) Deploy

Sin migraciones de esquema Partes en este CC. Código FE/BE host; runtime MONO vía `PartesMaestrosOperations` (SP list maestros = follow-up gateway si aplica).
