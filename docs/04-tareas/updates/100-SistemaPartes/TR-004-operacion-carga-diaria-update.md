# TR-004-update – Carga diaria: decimal, Excel, layouts GEN-11 y tipo default

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-004-operacion-carga-diaria-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-004-operacion-carga-diaria-update.md) |
| **SPEC relacionada** | [SPEC-004-operacion-carga-diaria-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-004-operacion-carga-diaria-update.md) |
| **TR base** | [TR-004-operacion-carga-diaria](../../100-SistemaPartes/TR-004-operacion-carga-diaria.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-09-15 |

**Origen:** `00-ControlCalidad-PQ` · 09/08/2026 · CC #3 (duración decimal; plantillas ajenas; tipo default)

---

## 1) In scope / Out of scope

### In scope
- Columna visible `duracionHoras` (o `duracionDecimal`) **sin** `customizeText` a `hh:mm`; mantener columna reloj aparte.
- `canExport` (GEN-11-export) en `ProcessDataGrid` de `/partes/carga-diaria`.
- Listado `GET /api/v1/grid-layouts`: todas las plantillas del par `proceso`+`gridId`; DTO `isOwner`; no filtrar solo por `user_id`.
- Alta: `tipoTareaId` = id con `isDefault`/`is_default`; SelectBox bindeado (no placeholder si hay default). Catálogo camelCase.

### Out of scope
- Import Excel SPEC-009; SC (TR-010-update); selección masivo (TR-005-update).
- Reinventar toolbar de layouts o exporter (GEN-11).

---

## 2) AC (mapeo HU)

| AC HU | Verificación técnica |
|-------|----------------------|
| CA-CC3-01 | Columna `hh:mm` + columna number `minutos/60` (precision 2). Vitest helper. |
| CA-CC3-02 | `canExport={true}` (o eq. ProcessDataGrid); Excel con campo decimal numérico |
| CA-CC3-03 | Feature: usuario B crea layout; usuario A `GET /grid-layouts` lo ve (`isOwner: false`); PUT/DELETE B → 403/3003 |
| CA-CC3-04 | Vitest/E2E: `openCreate` deja `tipoTareaId` = default; SelectBox `value` coincidente; API catálogo expone `isDefault` |

---

## 3) Implementación

| ID | Pieza | Notas |
|----|--------|-------|
| RN-TR-CC3-01 | `CargaDiariaPage` | Hoy `duracionHoras` se muestra como `hh:mm` vía `customizeText`. Separar: col. Duración = `hh:mm`; col. Duración decimal = number (`2.25`). i18n `partes.tarea.duracionDecimal`. |
| RN-TR-CC3-02 | `ProcessDataGrid` carga | `canExport` alineado a informes (`canExport={rows.length > 0 && !loading}` o siempre enabled si GEN lo permite en vacío). |
| RN-TR-CC3-03 | `GridLayoutsController::index` | Quitar `where('user_id', $userId)` del **listado**. Filtrar por `proceso`+`grid_id`. Mapear `isOwner` = `user_id`/`created_by_user_id` == caller. PUT/DELETE siguen siendo solo owner. Acceso de negocio: SP `pq_sp_grid_layout_*` si ya existen en GEN; si el host aún usa Eloquent de referencia, no ampliar CRUD ad-hoc — alinear al contrato GEN-11. |
| RN-TR-CC3-04 | Tipo default | Asegurar camelCase `isDefault` en catálogo tipos; no usar `items[0]` como default silencioso si no es `is_default`. Tras `setForm`, el `dataSource` de tipos debe incluir el id (genéricos al abrir). Evitar mismatch string/number en `valueExpr`. |
| RN-TR-CC3-05 | Tests | Feature layouts 2 usuarios; Vitest decimal + `resolveDefaultTipoId`; E2E humo alta con tipo precargado. |

**Proceso/gridId carga:** `partes.carga.diaria` / `cargaDiaria` (ya en FE).

---

## 4) Plan

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1 | FE | Columnas duración hh:mm + decimal; i18n | CA-CC3-01 | S |
| T2 | FE | `canExport` carga diaria | CA-CC3-02 | S |
| T3 | BE | Listado layouts compartido + `isOwner` | CA-CC3-03 | M |
| T4 | FE/BE | Tipo default en alta (catálogo + SelectBox) | CA-CC3-04 | M |
| T5 | Tests | Feature layouts; Vitest; E2E humo | T1–T4 | M |

**Orden:** T1 ∥ T2 ∥ T3 ∥ T4 → T5.

---

## 5) Tests

| Capa | Casos |
|------|--------|
| Feature | `GET grid-layouts` incluye layout de otro user; PUT ajeno 403 |
| Vitest | `135 min → 2.25`; default tipo por `isDefault` no por primer `code` |
| E2E | Nueva tarea: tipo no vacío; opcional export visible |

---

## 6) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-09-15 | Parte G CC-PQ #3 (09/08/2026). |
| 2026-09-15 | Parte D: columnas hh:mm + decimal, `exportEnabled`, listado layouts compartido + `isOwner`, tipo default sin fallback a `items[0]`. |
