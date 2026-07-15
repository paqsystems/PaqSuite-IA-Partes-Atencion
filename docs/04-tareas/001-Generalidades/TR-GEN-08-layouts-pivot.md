# TR-GEN-08-layouts-pivot — Diseños guardados y toolbar del pivot

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-08-layouts-pivot](../../03-historias-usuario/001-Generalidades/HU-GEN-08-layouts-pivot.md) |
| **SPEC relacionada** | [SPEC-001-08-pivots](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Could |
| **Dependencias** | TR-GEN-08-pivotgrid-visualizacion; TR-GEN-03-layouts-grilla (paridad) |
| **Estado** | Pendiente de programación (2026-06-11) |
| **Última actualización** | 2026-06-11 (D1 layouts-pivot) |

**Origen:** [HU-GEN-08-layouts-pivot](../../03-historias-usuario/001-Generalidades/HU-GEN-08-layouts-pivot.md)  
**Referencia SPEC:** [SPEC-001-08-pivots](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md) § AMB-P08-01…07  
**Paridad:** [TR-GEN-03-layouts-grilla](TR-GEN-03-layouts-grilla.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Guardar, compartir y recuperar diseños pivot por `consulta_id` (`pq_pivots_config`).

### In scope / Out of scope
- **In scope:** API CRUD diseños, `pq_pivots_config_last_used`, toolbar Actualizar + layouts, plantilla inicial vacía, sufijo ` (*)`, sin límite de diseños por consulta (AMB-Q-P08-02 cerrada).
- **Out of scope:** catálogo metadata, export Excel, layouts grilla tabular.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Toolbar: Actualizar, selector, Guardar, Guardar como, Eliminar según `persistencia` metadata.
- **AC-02**: Diseños propios con sufijo ` (*)` (`pivotLayout.ownerMarker`); no persiste en `nombre`.
- **AC-03**: Plantilla inicial (`configId: null`) resetea pivot vacía.
- **AC-04**: Plantilla inicial + Guardar → flujo Guardar como (POST).
- **AC-05**: Actualizar re-fetch dataset (`pivotRefresh`); no solo `reload()` local.
- **AC-06**: Nombre duplicado en `consulta_id` → HTTP 409, `error: 2001`, `pivotLayout.duplicateName`.
- **AC-07**: Al montar, restaura último diseño si `restaurarUltimoDiseno` y existe registro.
- **AC-08**: No creador: PUT/DELETE → 403 (`error: 3001`).
- **AC-09**: `data-testid`: `pivotLayoutSelect`, `pivotLayoutSave`, `pivotLayoutSaveAs`, `pivotLayoutDelete`, `pivotLayoutSaveAsDialog`, `pivotRefresh`.
- **AC-10**: E2E: guardar diseño → salir → volver → diseño restaurado.
- **AC-11**: Sin límite artificial de cantidad de diseños por consulta.

### Escenarios Gherkin

(Heredados de HU.)

---

## 3) Reglas de Negocio

1. **RN-01**: Unicidad `(consulta_id, nombre)` en `pq_pivots_config` activos no eliminados.
2. **RN-02**: `configId` API ↔ `pivot_id` BD (AMB-M-P08-03).
3. **RN-03**: Listar/aplicar: cualquier usuario con permiso consulta.
4. **RN-04**: PUT/DELETE solo creador (`usuario_creador_id`).
5. **RN-05**: Plantilla inicial: `configId: null` → pivot vacía; Guardar = Guardar como.
6. **RN-06**: `configuracion_json` = fields DX serializados (áreas, summaryType, filtros internos).
7. **RN-07**: Actualizar dispara `refreshToken` + POST data (patrón `ConsultaGridPage`).
8. **RN-08**: Orden toolbar: `[actualizar] → [diseños] → [export] → [extras]`.
9. **RN-09**: Sin límite de diseños por `consulta_id` (AMB-Q-P08-02).
10. **RN-10**: Borrado lógico (`eliminado = 1`).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-11)

**Fuentes revisadas:** HU-GEN-08-layouts-pivot, SPEC-001-08 §17–17.1, TR-GEN-03-layouts-grilla §3.1–3.3, TR-GEN-08-motor-metadata-pivots, `GridLayoutToolbar`, `useGridLayouts`, `frontend-pivotgrid-devextreme-agregaciones-y-menu.md` §6–7.

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (paridad cerrada con TR-GEN-03-layouts-grilla)

### Ambigüedades críticas

| ID | Tema | Riesgo | Estado | Qué hacer (D1 / código) |
|----|------|--------|--------|-------------------------|
| AMB-C01 | **Cargar sin GET by id** | Listado sin `configuracionJson` | **Cerrado** (R-C1-01) | `PUT /active` → `GET /active` → aplicar JSON; sin `GET /{id}` en v1. |
| AMB-C02 | **Duplicado nombre** | Sobrescritura silenciosa | **Cerrado** (R-C1-02) | HTTP **409**, `error: 2001`, `pivotLayout.duplicateName`. |
| AMB-C03 | **Tamaño JSON** | Payload enorme | **Cerrado** (R-C1-03) | Máx **512 KB**; `error: 1000` si excede. |
| AMB-C04 | **Actualizar vs reload** | Datos obsoletos tras refresh | **Cerrado** (R-C1-04) | `pivotRefresh` incrementa `refreshToken` → re-POST `.../data` (TR motor); no `dataSource.reload()` solo. |
| AMB-C05 | **Plantilla inicial vs pivotBase** | Confundir reset vacío con base analista | **Cerrado** (R-C1-05) | `configId: null` = field panel **vacío**; no modifica `pivotBase` metadata. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M-P08-02 | `pq_pivots_config_last_used` | PK `(user_id, consulta_id)`; `pivot_id` nullable. |
| AMB-M02 | `pivotLayoutsEnabled` | `config/public`; default **false**; requiere también `pivotsEnabled`. |
| AMB-M03 | Versión definición obsoleta | Toast i18n `pivotLayout.versionMismatch` si `version_definicion_consulta` < actual; permite cargar. |
| AMB-M04 | Renombrar diseño | Fuera MVP (solo PUT `configuracionJson`). |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| Paridad `gridLayout.*` vs `pivotLayout.*` | Prefijos distintos; misma semántica HTTP/envelope. |
| `usuario_creador_id` varchar en MONO vs bigint users | **SistemaPartes:** `created_by_user_id` bigint FK `users.id` (paridad `pq_grid_layouts`). |
| HU pide orden toolbar con export | Export en TR-GEN-08-exportacion-pivot; slot reservado en `PivotGridBlock`. |

### Supuestos detectados

- `PivotGridDataSource.fields()` serializable a `configuracionJson`.
- Policy creador replica `GridLayoutPolicy`.
- Sin límite de diseños (AMB-Q-P08-02 cerrada).

### Preguntas para decisión humana

(Ninguna bloqueante.)

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-11)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Cargar | `PUT /pivot-configs/active` → `GET /active` → aplicar `configuracionJson` |
| R-C1-02 | Duplicado | 409 + `pivotLayout.duplicateName` |
| R-C1-03 | Tamaño JSON | 512 KB máx; validación server-side |
| R-C1-04 | Actualizar | `refreshToken` + POST data motor |
| R-C1-05 | Plantilla inicial | `configId: null` → pivot vacía (no pivotBase) |
| R-C1-06 | No creador PUT/DELETE | 403 + `error: 3001` |
| R-C1-07 | Límite diseños | **Ninguno** por `consulta_id` |
| R-C1-08 | Flags infra | `pivotLayoutsEnabled` + `pivotsEnabled` en `config/public` |

### Flujo usuario: Cargar diseño (resuelve AMB-C01)

```text
1. GET /pivot-configs?consultaId=           → lista (sin configuracionJson)
2. Usuario elige diseño en SelectBox
3. PUT /pivot-configs/active               → { consultaId, configId }
4. GET /pivot-configs/active?consultaId=     → configuracionJson completo
5. Frontend: aplicar fields al PivotGridDataSource
```

### Flujo usuario: Guardar desde plantilla inicial

```text
configId === null → botón Guardar abre Popup Guardar como → POST /pivot-configs
```

---

## 4) Impacto en Datos

### Tablas nuevas (BD tenant — R-C1-01 motor-metadata)

#### `pq_pivots_config`

| Columna | Tipo | Notas |
|---------|------|-------|
| `pivot_id` | PK bigint | API `configId` |
| `consulta_id` | varchar(100) | FK lógica catálogo |
| `nombre` | nvarchar(200) | único por consulta |
| `configuracion_json` | nvarchar(max) | diseño pivot |
| `created_by_user_id` | bigint FK `users.id` | Paridad `pq_grid_layouts` (C1) |
| `version_definicion_consulta` | int | |
| `eliminado` | bit | borrado lógico |
| `activo` | bit | |
| timestamps | datetime | |

**Índice único:** `UX_pq_pivots_config_consulta_nombre` en `(consulta_id, nombre)` WHERE `eliminado = 0`.

#### `pq_pivots_config_last_used`

| Columna | Tipo | Notas |
|---------|------|-------|
| `user_id` | FK users | |
| `consulta_id` | varchar | |
| `pivot_id` | FK nullable | `NULL` = plantilla inicial |
| `updated_at` | datetime | |

**PK:** `(user_id, consulta_id)`.

### Seed mínimo

- Supervisor: diseño `Vista resumen` en consulta piloto.
- Usuario acotado: sin diseños (prueba plantilla inicial).

---

## 5) Contratos de API y OpenAPI

> Bearer + tenant · usuario autenticado con permiso consulta.

### 5.1 Endpoints

| Método | Path | Auth | Permiso |
|--------|------|------|---------|
| GET | `/api/v1/pivot-configs` | Bearer + tenant | Permiso consulta |
| GET | `/api/v1/pivot-configs/active` | Bearer + tenant | Permiso consulta |
| POST | `/api/v1/pivot-configs` | Bearer + tenant | Permiso consulta |
| PUT | `/api/v1/pivot-configs/{configId}` | Bearer + tenant | Solo creador |
| DELETE | `/api/v1/pivot-configs/{configId}` | Bearer + tenant | Solo creador |
| PUT | `/api/v1/pivot-configs/active` | Bearer + tenant | Permiso consulta |

**Query GET list:** `consultaId` (requerido).

### 5.2 Detalle

#### GET `/api/v1/pivot-configs?consultaId=`

```json
{
  "error": 0,
  "respuesta": "",
  "resultado": {
    "items": [
      {
        "configId": 1,
        "nombre": "Ventas por mes",
        "createdByUserId": 12,
        "isOwner": true,
        "updatedAt": "2026-06-11T12:00:00Z"
      }
    ]
  }
}
```

#### GET `/api/v1/pivot-configs/active?consultaId=`

```json
{
  "error": 0,
  "respuesta": "",
  "resultado": {
    "configId": 1,
    "nombre": "Ventas por mes",
    "configuracionJson": { }
  }
}
```

Plantilla inicial: `configId: null`, `nombre: null`, `configuracionJson: null`.

#### POST `/api/v1/pivot-configs`

**Body:** `{ "consultaId", "nombre", "configuracionJson" }`  
**409** duplicado `pivotLayout.duplicateName`.

#### PUT `/api/v1/pivot-configs/{configId}`

**Body:** `{ "configuracionJson" }` — solo creador.

#### PUT `/api/v1/pivot-configs/active`

**Body:** `{ "consultaId", "configId": number | null }`

### 5.3 Matriz permisos

- [x] Sección **Pivots — diseños guardados** en [matriz-permisos-mvp.md](matriz-permisos-mvp.md).

---

## 6) Cambios Frontend

### Componentes

```text
frontend/src/features/pivotLayouts/
  api/pivotLayoutsApi.ts
  hooks/usePivotLayouts.ts
  components/PivotLayoutToolbar.tsx
```

- Montar en `toolbarEnd` de `PivotGridBlock` (orden: refresh → layouts → export).
- Reutilizar patrones `GridLayoutToolbar` / `useGridLayouts`.

### Flujo Cargar

```text
PUT /pivot-configs/active → GET /active → aplicar configuracionJson al PivotGridDataSource
```

### Plantilla inicial

- Opción fija en SelectBox (`pivotLayout.initialTemplate`).
- Reset: sin campos en áreas del field panel.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Migración `pq_pivots_config` + last_used | Índices |
| T2 | Backend | `PivotConfigController` + Policy creador | Feature tests |
| T3 | Frontend | `PivotLayoutToolbar` + hook | AC-01–AC-05 |
| T4 | Frontend | Integrar en `PivotGridBlock` | Orden toolbar |
| T5 | Frontend | i18n `pivotLayout.*` | 5 idiomas |
| T6 | Tests | E2E persistencia diseño | AC-10 |

---

## 8) Estrategia de Tests

- **Integration:** create, duplicate 409, 403 no creador, active roundtrip.
- **E2E:** guardar → F5 → diseño restaurado.

---

## 9) Riesgos y Edge Cases

- Diseño guardado con `version_definicion_consulta` obsoleta → advertencia i18n al cargar.
- `configuracionJson` incompatible tras upgrade DX → migración manual fuera MVP.

---

## 10) Checklist final

- [x] Paridad funcional con TR-GEN-03-layouts-grilla verificada
- [x] Envelope MONO + permiso consulta + 403 creador
- [x] i18n `pivotLayout.*` (5 idiomas)
- [x] E2E persistencia diseño (AC-10)

---

## Archivos creados/modificados (D1)

### Backend
- `app/Http/Controllers/Api/V1/Pivots/PivotConfigController.php`
- `app/Services/Pivots/PivotConfigService.php`
- `app/Models/PqPivotConfig.php`, `PqPivotConfigLastUsed.php`
- `database/migrations/2026_06_11_110000_create_pq_pivots_config_tables.php`
- `database/seeders/Pivots/PivotCatalogPilotSeeder.php` (diseño «Vista resumen»)
- `routes/api.php` (`/api/v1/pivot-configs*`)
- `tests/Feature/Api/Pivots/PivotConfigFeatureTest.php`

### Frontend
- `frontend/src/features/pivotLayouts/**`
- `frontend/src/shared/pivot/components/PivotGridBlock.tsx` (handle captura diseño)
- `frontend/src/shared/pivot/components/ConsultaGrillaPivotShell.tsx` (integración toolbar)
- `frontend/tests/e2e/pivot-layout-persistencia.spec.ts`










