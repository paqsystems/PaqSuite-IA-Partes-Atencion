# TR-005 – Supervisión y proceso masivo

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-005-supervision-proceso-masivo](../../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) |
| **SPEC relacionada** | [SPEC-005-supervision-proceso-masivo](../../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Solo `resultado.partes.esSupervisor = true` |
| **Dependencias** | [TR-002](./TR-002-identidad-funcional-y-acceso.md), [TR-004](./TR-004-operacion-carga-diaria.md) (listado/tareas/`rowVersion`) |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Pendiente (D implementado — verificar F1) |
| **Última actualización** | 2026-07-30 (D) |

**Origen:** [HU-005](../../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md)  
**Referencia SPEC:** [SPEC-005](../../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md)

---

## 1) HU refinada (resumen)

### In scope
- Pantalla dedicada proceso masivo (menú Partes, solo supervisor).
- Atajo link desde carga diaria **sin** pasar filtros/selección.
- Listado filtrado (reutiliza criterios TR-004 / mismo SP list con actor supervisor).
- Selección explícita; «Seleccionar todos del resultado filtrado» (todas las páginas) + modal N si >1 página.
- Lote atómico cerrar/reabrir con `{ id, rowVersion }[]`; 409 total si conflicto; tope `PartesMasivoMaxIds`.
- i18n `partes.masivo.*` + testids.

### Out of scope
- Edición campos de negocio en lote; Excel; mails; mobile masivo; redefinir captura TR-004.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | Asistente/cliente: sin menú + 403 API lote |
| AC-02 | Supervisor lista con fechas (+ filtros opcionales) |
| AC-03 | Selección vacía → no API; i18n `partes.masivo.emptySelection` |
| AC-04 | Cerrar N → `cerrado=1`; no editables en carga ordinaria |
| AC-05 | Reabrir N → `cerrado=0` |
| AC-06 | Id inexistente en lote → error atómico; 0 cambios |
| AC-07 | Ya en estado objetivo + version OK → éxito idempotente |
| AC-08 | Confirm ejecución: acción + cantidad + resumen (rango fechas filtro + hasta 5 filas muestra) |
| AC-09 | Tras éxito, refresh grilla |
| AC-10 | i18n + testids |
| AC-11 | Tope `PartesMasivoMaxIds`: N>0 y selección>N → 422; default 0 = sin tope negocio |
| AC-12 | Atajo carga = link sin query/filtros |
| AC-13 | Algún `rowVersion` stale → 409; 0 cambios |
| AC-14 | Select-all = todo el filtro; si pages>1 → modal «Afectará a N partes. ¿Confirma?» |

---

## 3) Reglas

R-SU-01…09.

| ID | Implementación |
|----|----------------|
| RN-TR-01 | Param **`PartesMasivoMaxIds`**, programa **`Partes`**, default **`0`**. |
| RN-TR-02 | SP lote: **`pq_sp_partes_tarea_masivo_set_cerrado`**. |
| RN-TR-03 | Body API: `{ "accion": "cerrar"|"reabrir", "items": [ { "id", "rowVersion" } ] }`. |
| RN-TR-04 | Select-all: `GET /partes/tareas/ids` (mismos filtros que list, sin page) → `{ items: [{id, rowVersion}], total }` para armar selección completa. |
| RN-TR-05 | Listado masivo: reutilizar `GET /partes/tareas` (TR-004) con actor supervisor. |
| RN-TR-06 | Preview confirmación ejecución Must: `accion`, `cantidad`, `fechaDesde`/`fechaHasta` del filtro, hasta **5** ítems muestra (`id` + `fecha` + código asistente si hay). |

---

## 4) Datos

### 4.1 SP `pq_sp_partes_tarea_masivo_set_cerrado`

| Param | Tipo | Notas |
|-------|------|--------|
| `@p_accion` | nvarchar | `cerrar` \| `reabrir` |
| `@p_items_json` | nvarchar(max) | JSON array `[{"id":1,"rowVersion":"0x..."},…]` (o TVP si D1 prefiere; C1 cierra **JSON** por simplicidad host) |
| `@p_actor_asistente_id` | bigint | Sesión; debe ser supervisor dominio |
| `@p_max_ids` | int | Leído de param (0 = sin tope) |

Comportamiento: transacción; validar no vacío; tope; cada id existe + en universo supervisor + row_version match; set `cerrado`; idempotente si ya objetivo; cualquier fail → rollback; conflicto versión → señal 409 al adapter.

### 4.2 SP auxiliar (opcional si list no alcanza)

| SP | Uso |
|----|-----|
| `pq_sp_partes_tarea_list_ids` | Mismos filtros que list; devuelve solo `id` + `row_version` de **todo** el resultado filtrado (cap técnico documentado si total enorme; si supera tope param y tope>0, FE ya bloquea antes) |

### 4.3 Seed

| Clave | Default |
|-------|---------|
| `PartesMasivoMaxIds` | `0` |

Menú: ítem `partes_proceso_masivo` → `/partes/proceso-masivo`, solo supervisor (permiso menú + FE hide si `!esSupervisor`).

---

## 5) API

| Método | Path | Notas |
|--------|------|-------|
| GET | `/partes/tareas` | Reuso TR-004 (filtros) |
| GET | `/partes/tareas/ids` | Select-all data; exige fechas |
| POST | `/partes/tareas/masivo/set-cerrado` | Body §RN-TR-03; solo supervisor |

Errores: 403 `partes.masivo.forbidden`; 422 empty/tope/inválido; 409 `partes.tarea.conflictoVersion` (misma clave familia que TR-004) o `partes.masivo.conflictoVersion`.

**Cerrado C1:** usar **`partes.masivo.conflictoVersion`** en lote (distingue UX).

---

## 6) Frontend

| Pieza | Detalle |
|-------|---------|
| Ruta | `/partes/proceso-masivo` |
| Atajo carga | Link texto → misma ruta, **sin** search params |
| Filtros | Fechas obligatorias (default hoy como TR-004); cliente/asistente/estado opcionales |
| Grid | DX + selección; paginación |
| Select all | Llama `/tareas/ids`; si `total` implica >1 página UI → modal N; cancel → no selecciona |
| Acciones | Cerrar / Reabrir → confirm ejecución (preview RN-TR-06) → POST |
| Refresh | Reload list tras 200 |
| testids | `partesMasivoGrid`, `partesMasivoSelectAll`, `partesMasivoConfirmN`, `partesMasivoConfirmAction`, … |
| Mobile | No menú / no rutas native |

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1 | DB | SP masivo + list_ids | AC-04…07,13 | M |
| T2 | Seed | `PartesMasivoMaxIds=0` + menú | AC-11,12 | S |
| T3 | Backend | Controllers + 403/409/422 | AC-01,11,13 | M |
| T4 | Frontend | Pantalla + select-all + confirms + atajo | AC-02…10,12,14 | L |
| T5 | Tests | Feature atomic/idempotencia/tope/409; E2E humo 2 filas | Suite | L |
| T6 | Docs | OpenAPI | | S |

---

## 8) Tests

Feature: 403 no supervisor; empty; tope; id fantasma; stale version; idempotencia; cerrar 2.  
E2E: supervisor cierra 2 con confirm.

---

## 9) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Select-all con miles de ids | Respetar tope param; si 0, límite técnico request (p. ej. 5k) documentado en D1 + 422 `partes.masivo.loteDemasiadoGrande` |
| JSON items size | Mismo tope |

---

## 10) Checklist

- [ ] AC-01…14  
- [ ] SP + param + menú  
- [ ] FE + atajo  
- [ ] Tests + OpenAPI  

---

## 11) Informe C1

# Revisión de ambigüedad - TR-005

## Resultado general
- **Apto con observaciones** (absorbidas)

## Críticas cerradas
- Param → **`PartesMasivoMaxIds`** (programa `Partes`, default 0)
- SP → **`pq_sp_partes_tarea_masivo_set_cerrado`** (+ `list_ids`)
- Select-all data → **`GET /partes/tareas/ids`**
- Payload items → JSON `{id, rowVersion}[]`
- Preview ejecución → acción + N + rango fechas + hasta 5 muestras
- i18n conflicto lote → **`partes.masivo.conflictoVersion`**

## Menores
- Límite técnico duro si param=0: D1 fija número (sugerido 5000) con clave i18n arriba
- TVP vs JSON: JSON cerrado para MVP

## Veredicto
- **Puede pasar a D1/D: Sí**

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte C + C1: TR masivo; param/SP/select-all/preview cerrados. |
| 2026-07-30 | Parte D: `list_ids` + `masivo_set_cerrado`, param `PartesMasivoMaxIds`, menú, FE select-all/confirm, Feature tests OK. Tope técnico 5000. |

---

**Siguiente:** F1 de TR-005, o D de TR-006 cuando se autorice.
