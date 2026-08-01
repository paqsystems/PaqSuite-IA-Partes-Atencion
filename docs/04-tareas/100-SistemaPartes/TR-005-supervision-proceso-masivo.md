# TR-005 – Supervisión y proceso masivo

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-005-supervision-proceso-masivo](../../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) |
| **SPEC relacionada** | [SPEC-005-supervision-proceso-masivo](../../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Solo `resultado.partes.esSupervisor = true` |
| **Dependencias** | [TR-002](./TR-002-identidad-funcional-y-acceso.md), [TR-004](./TR-004-operacion-carga-diaria.md) (listado/tareas/`rowVersion`/tipos), GEN layouts/export grilla |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Finalizado |
| **Última actualización** | 2026-08-01 |

**Origen:** [HU-005](../../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md)  
**Referencia SPEC:** [SPEC-005](../../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md)  
**Producto:** [`05-operacion-diaria-y-supervision.md`](../../02-producto/Sistema-Partes-IA/05-operacion-diaria-y-supervision.md)

---

## 1) HU refinada (resumen)

### In scope
- Pantalla dedicada proceso masivo (menú Partes, solo supervisor) + atajo desde carga sin filtros.
- Listado filtrado (reutiliza `GET /partes/tareas` TR-004, actor supervisor).
- Selección explícita; select-all del resultado filtrado + modal N si >1 página.
- **ProcessDataGrid:** filter row, summary duración, column chooser, plantillas layout, export Excel.
- Lote atómico **cerrar/reabrir** (ya implementado) con `{ id, rowVersion }[]`.
- Lote atómico **actualizar atributos** — Must: `tipoTareaId`, `sinCargo`; Should: `presencial`, `usuarioId`, `fecha`.
- Tope `PartesMasivoMaxIds`; i18n `partes.masivo.*` + testids.
- **(CC-PQ #1, 31/07)** Listado/`list_ids` y lotes (`masivo_set_cerrado`, `masivo_actualizar`) operan solo sobre `es_tarea = 1`; id con `es_tarea = 0` en un lote → 422 atómico (`partes.masivo.noEsTarea`).

### Out of scope
- Atributos: cliente, duración/minutos, descripción.
- Importación Excel; mails; mobile masivo; redefinir captura TR-004.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01…14 | Igual HU (acceso, filtros, cerrar/reabrir, select-all, tope, 409, atajo, i18n) — **ya cubiertos en D previo** salvo regresión |
| AC-15 | Grilla = `ProcessDataGrid` (o wrapper GEN) con filterRow, totals, columnChooser, templates, export |
| AC-16 | POST actualizar `sinCargo` → N filas (incl. cerradas) |
| AC-17 | POST actualizar `tipoTareaId` válido para todas → N filas |
| AC-18 | `tipoTareaId` inválido para alguna fila → 422; 0 cambios |
| AC-19 | Should: mismos endpoints/UI para `presencial` / `usuarioId` / `fecha` (tarea T7; no bloquea DoD Must si D1 lo marca diferido) |
| AC-20 | Listado / `list_ids` del masivo no incluyen filas con `es_tarea = 0` |
| AC-21 | Lote (`set_cerrado` o `actualizar`) con algún id `es_tarea = 0` → 422 `partes.masivo.noEsTarea`; cero cambios |

---

## 3) Reglas

R-SU-01…10 (SPEC/HU).

| ID | Implementación |
|----|----------------|
| RN-TR-01 | Param **`PartesMasivoMaxIds`**, programa **`Partes`**, default **`0`**. |
| RN-TR-02 | SP cerrar/reabrir: **`pq_sp_partes_tarea_masivo_set_cerrado`** (existente). |
| RN-TR-02b | SP actualizar: **`pq_sp_partes_tarea_masivo_actualizar`**. |
| RN-TR-03 | Body cerrar/reabrir: `{ "accion": "cerrar"|"reabrir", "items": [ { "id", "rowVersion" } ] }`. |
| RN-TR-03b | Body actualizar: `{ "campos": { … }, "items": [ { "id", "rowVersion" } ] }` — al menos una clave de `campos` permitida. |
| RN-TR-04 | Select-all: `GET /partes/tareas/ids` → `{ items, total }`. |
| RN-TR-05 | Listado: `GET /partes/tareas` (TR-004). |
| RN-TR-06 | Preview confirmación: acción + N + rango fechas + hasta 5 muestras; si actualizar, incluir claves/valores de `campos`. |
| RN-TR-07 | `campos` Must: `tipoTareaId` (int nullable omitido), `sinCargo` (bool). Should: `presencial` (bool), `usuarioId` (int), `fecha` (`YYYY-MM-DD`). Omitir clave = no tocar ese campo. |
| RN-TR-08 | Validación tipo: para cada item, el `tipoTareaId` debe ser usable para el **cliente de esa fila** (misma regla SPEC-003/004). Fallo en una → rollback total. |
| RN-TR-09 | Actualizar atributos **no** exige `cerrado = 0` (R-SU-10). |
| RN-TR-10 | Error negocio lote atributos: 422 `partes.masivo.atributoInvalido` (o subclave tipo); conflicto versión: `partes.masivo.conflictoVersion`. |
| RN-TR-11 | Listado / `list_ids` filtran implícitamente `es_tarea = 1`; `masivo_set_cerrado` y `masivo_actualizar` rechazan (atómico, 422 `partes.masivo.noEsTarea`) cualquier id con `es_tarea = 0`. |

---

## 4) Datos

### 4.1 SP `pq_sp_partes_tarea_masivo_set_cerrado` (existente)

Sin cambio de contrato. Ver implementación D previa.

### 4.2 SP `pq_sp_partes_tarea_masivo_actualizar` (nuevo)

| Param | Tipo | Notas |
|-------|------|--------|
| `@p_campos_json` | nvarchar(max) | p. ej. `{"tipoTareaId":12,"sinCargo":true}` — solo claves presentes |
| `@p_items_json` | nvarchar(max) | `[{"id":1,"rowVersion":"…"},…]` |
| `@p_actor_asistente_id` | bigint | Debe ser supervisor dominio |
| `@p_max_ids` | int | Desde param (0 = sin tope negocio) |

Comportamiento: transacción; validar items no vacío + tope; cada id existe + universo supervisor + row_version; aplicar solo campos presentes; validar tipo↔cliente si viene `tipoTareaId`; si Should habilitados, validar asistente usable / fecha; cualquier fail → rollback; conflicto versión → señal 409.

### 4.3 SP auxiliar

| SP | Uso |
|----|-----|
| `pq_sp_partes_tarea_list_ids` | Select-all (existente) |

### 4.4 Seed

| Clave | Default |
|-------|---------|
| `PartesMasivoMaxIds` | `0` (ya seed) |

Menú: `partes_proceso_masivo` → `/partes/proceso-masivo` (existente).

---

## 5) API

| Método | Path | Notas |
|--------|------|-------|
| GET | `/partes/tareas` | Reuso TR-004 |
| GET | `/partes/tareas/ids` | Select-all |
| POST | `/partes/tareas/masivo/set-cerrado` | Existente |
| POST | `/partes/tareas/masivo/actualizar` | Nuevo; body RN-TR-03b; solo supervisor |

Errores: 403 `partes.masivo.forbidden`; 422 empty/tope/inválido/`partes.masivo.atributoInvalido`; 409 `partes.masivo.conflictoVersion`; tope técnico 5000 → `partes.masivo.loteDemasiadoGrande`.

---

## 6) Frontend

| Pieza | Detalle |
|-------|---------|
| Ruta | `/partes/proceso-masivo` |
| Atajo carga | Link → misma ruta, sin search params |
| Filtros | Fechas obligatorias; cliente/asistente/estado opcionales |
| Grid | **`ProcessDataGrid`** (reemplazar `DataGrid` crudo): selection, filterRow, summary duración, columnChooser, templates GEN, export Excel |
| Select all | `/tareas/ids` + modal N |
| Acciones Must | Cerrar / Reabrir (existente); **Actualizar tipo de tarea**; **Actualizar sin cargo** (puede unificarse en un panel «Aplicar cambios» con campos opcionales) |
| Acciones Should | Presencial / Asistente / Fecha en el mismo panel cuando T7 |
| Confirm | Preview RN-TR-06 → POST |
| Refresh | Reload list tras 200 |
| testids | `partesMasivoGrid`, `partesMasivoSelectAll`, `partesMasivoConfirmN`, `partesMasivoConfirmAction`, `partesMasivoApplyCampos`, `partesMasivoTipoTarea`, `partesMasivoSinCargo`, … |
| Mobile | No |

Presentación columnas alineada a carga/informes donde aplique (descripciones cliente/tipo; duración `hh:mm` en celda/pie).

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1–T6 | — | Cerrar/reabrir + select-all + param (**hecho D 2026-07-30**) | AC-01…14 (parcial) | — |
| T7a | DB | SP `pq_sp_partes_tarea_masivo_actualizar` (Must campos) | AC-16…18 | M |
| T7b | Backend | `POST .../masivo/actualizar` + envelope errores | AC-16…18,01 | M |
| T7c | Frontend | ProcessDataGrid + export/templates/filter/totals/chooser | AC-15 | L |
| T7d | Frontend | UI aplicar tipo + sin cargo + confirm | AC-16…18,08 | M |
| T7e | Tests | Feature update + tipo inválido atómico; E2E humo | Suite | M |
| T7f | Docs | OpenAPI + manual usuario | | S |
| T7g | Frontend/BE | Should: presencial, usuarioId, fecha | AC-19 | M |
| T8 | Backend | Filtro `es_tarea=1` en list/list_ids + guarda `noEsTarea` en `masivo_set_cerrado`/`masivo_actualizar` (CC-PQ #1) | AC-20/21 | M |
| T9 | Tests | Feature: masivo no lista compras; lote con compra → 422 | AC-20/21 | S |

**Orden sugerido ampliación:** T7a → T7b → T7c → T7d → T7e → T7f; T7g en cuanto Must esté estable (o en paralelo UI si no bloquea); T8/T9 en CC-PQ #1 (tras TR-001 con `es_tarea`).

---

## 8) Tests

**Regresión:** Feature cerrar/reabrir / 403 / tope / 409 / idempotencia.  
**Nuevos:** actualizar `sinCargo`; actualizar `tipoTareaId` OK; tipo inválido multi-cliente → 422 y 0 cambios; actualizar sobre fila cerrada; (Should) presencial/fecha/asistente.  
**E2E:** supervisor aplica sin cargo a 2 filas; smoke ProcessDataGrid export/chooser si el harness lo permite.

---

## 9) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Select-all miles de ids | Tope param + hard 5000 |
| Tipo único vs multi-cliente | Validación por fila en SP; mensaje claro |
| ProcessDataGrid vs DataGrid actual | Reemplazo acotado; no cambiar contrato list |
| Should diluye Must | T7g separado; D1 puede marcar AC-19 diferido |

---

## 10) Checklist

- [x] AC-01…14 (D previo + regresión Feature)
- [x] AC-15…18 (Must ampliación 2026-07-31)
- [x] AC-19 (Should: presencial / asistente / fecha) — UI + BE 2026-07-31
- [x] AC-20/21 (`es_tarea` en masivo, CC-PQ #1)
- [x] SP actualizar + API + FE ProcessDataGrid
- [x] Tests Feature + unit FE; OpenAPI/manual pendientes menores

---

## 11) Informe C1 (histórico + ampliación)

### C1 original (2026-07-30)
- Param / SP set_cerrado / select-all / preview / i18n conflicto — **cerrados**.

### C1 ampliación (2026-07-31)
- SP/API actualizar, payload `campos`, fallo tipo atómico, ProcessDataGrid — **cerrados y D Must hecho**.
- AC-19 UI Should — **cerrado** (presencial / asistente / fecha).

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte C + C1 + D: cerrar/reabrir, select-all, param, Feature OK. |
| 2026-07-31 | SPEC/HU-update producto: TR ampliación ProcessDataGrid + `masivo/actualizar` Must/Should; plan T7a–T7g. |
| 2026-07-31 | D ampliación Must: SP/API `masivo/actualizar`, ProcessDataGrid, UI tipo+sinCargo, Feature + unit. Should UI pendiente. |
| 2026-07-31 | D Should: UI presencial/asistente/fecha + Feature; AC-19 cerrado. |
| 2026-07-31 | F1: Aprobado con observaciones (sin E2E Playwright masivo; OpenAPI no versionado en repo). |
| 2026-07-31 | CC-PQ #1 (31/07/2026): listado/`list_ids` filtran `es_tarea=1`; lotes rechazan ids `es_tarea=0` (AC-20/21, RN-TR-11, T8/T9); [D-VERIFICACION-CC-PQ-01](../updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-01-2026-07-31.md). |
| 2026-08-01 | Parte I: fusionado TR-005-update (CC-PQ #1, 31/07) en esta TR; update eliminado. Estado → Finalizado. |

---

# Verificación del agente - TR-005 (F1 2026-07-31)

## Resultado
- **Aprobado con observaciones**

## Evidencia revisada
- SPEC-005 / HU-005 / TR-005 / producto `05-operacion-diaria-y-supervision.md`
- BE: `PartesTareaOperations::masivoActualizar`, route `POST .../masivo/actualizar`, controller
- FE: `ProcesoMasivoPage` ProcessDataGrid + panel Must/Should; `partesMasivoApi`; `buildMasivoCamposUpdate`
- Docs producto + OpenSpec + manual SPEC-005

## Hallazgos críticos
- Ninguno

## Advertencias
- No hay E2E Playwright del proceso masivo (solo Feature PHP + unit Vitest).
- Contrato OpenAPI del endpoint nuevo no aparece versionado en el repo (si el producto lo exige aparte, queda pendiente).
- Runtime SP vía Query Builder PHP (mismo patrón TR-004/masivo set-cerrado); script T-SQL gateway = follow-up heredado.

## Sugerencias
- Humo manual en UI: plantillas, export Excel, aplicar tipo+sin cargo y Should.
- E2E opcional: supervisor aplica sinCargo a 2 filas.

## Tests
- Comandos: `php artisan test --filter=ApiV1PartesMasivoTest`; `npx vitest run src/features/partes/masivo/`; `npx tsc --noEmit`
- Resultado: Feature 6 passed; Vitest 4 passed; tsc OK

## Pendientes
- E2E opcional; OpenAPI si aplica al pipeline del repo

## Recomendación final
- Puede mergearse / publicarse en `v1.1.0` con aviso de smoke UI post-deploy. Sin migrate nueva (solo código + docs).

**Siguiente:** commit/push; smoke UI proceso masivo.
