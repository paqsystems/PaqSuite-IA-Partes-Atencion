# TR-GEN-03-layouts-grilla — Layouts persistentes (`pq_grid_layouts`)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-03-layouts-grilla](../../03-historias-usuario/001-Generalidades/HU-GEN-03-layouts-grilla.md) |
| **SPEC relacionada** | [SPEC-001-03-ui-transversal](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-03-grillas-listados; TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-09 (Parte I — CC PQ #2 y #3) |

**Cierre F formal:** [F-GEN-03-cierre-formal](F-GEN-03-cierre-formal.md)

**Origen:** [HU-GEN-03-layouts-grilla](../../03-historias-usuario/001-Generalidades/HU-GEN-03-layouts-grilla.md)  
**Referencia SPEC:** [SPEC-001-03-ui-transversal](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md) § Layouts persistentes  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Guardar, compartir y recuperar formatos de grilla por `proceso` + `grid_id`.

### In scope / Out of scope
- **In scope:** tabla `pq_grid_layouts`, preferencia último layout por usuario, API CRUD, toolbar Guardar / Guardar como / Cargar / Eliminar, integración con `DataGridDx`, unicidad de nombre por proceso+grilla, sin límite de cantidad.
- **Out of scope:** exportación, ABM, pivots, plantilla sistema como fila BD (estado “sin layout_id” = plantilla DX por defecto).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Guardar como crea registro con `layout_name` único en `proceso`+`grid_id`.
- **AC-02**: Guardar actualiza solo si el usuario es creador.
- **AC-03**: Cargar aplica `state_json` al DataGrid.
- **AC-04**: Eliminar solo creador; 403 para otros.
- **AC-05**: Duplicado de nombre → error negocio (rango 2000) sin sobrescribir.
- **AC-06**: Al entrar a pantalla, aplica último layout del usuario si existe.
- **AC-07**: Con plantilla sistema activa, Guardar dispara flujo Guardar como.
- **AC-08**: Toolbar layouts en franja superior de `DataGridDx`.
- **AC-09**: i18n + `data-testid` en controles de layout.
- **AC-10**: E2E: guardar → recargar → formato restaurado (dashboard o proceso referencia).
- **AC-11**: Sin infra (flag config): controles ocultos/deshabilitados; listado sigue operativo.
- **AC-12**: Layouts con `isOwner: true` muestran sufijo **` (*)`** en el selector (`gridLayout.ownerMarker` i18n); no se persiste en `layout_name`.
- **AC-13**: Al seleccionar **Plantilla del sistema** (`layoutId: null`), `DataGridDx.applyState(null)` ejecuta `instance.state(null)` para restaurar columnas/estado por defecto del proceso.

### Escenarios Gherkin

(Heredados de HU.)

---

## 3) Reglas de Negocio

1. **RN-01**: Unicidad `(proceso, grid_id, layout_name)`.
2. **RN-02**: `created_by_user_id` = usuario autenticado al crear.
3. **RN-03**: Cualquier usuario autenticado puede listar y **aplicar** layouts del par proceso+grid_id.
4. **RN-04**: Solo creador PUT/DELETE.
5. **RN-05**: `state_json` = serialización `DataGrid.state()` (columnas, filter, sort, grouping, summary, widths).
6. **RN-06**: Último usado: tabla auxiliar o columnas en preferencias — ver §4.
7. **RN-07**: Sin límite de registros por usuario en MVP.
8. **RN-08**: Layouts propios se distinguen en UI con sufijo ` (*)`.
9. **RN-09**: Plantilla del sistema no es fila BD; su selección resetea el DataGrid al baseline del proceso (`instance.state(null)`).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-01)

**Fuentes revisadas:** HU-GEN-03-layouts-grilla, SPEC-001-03 § Layouts, `grillas.md`, `_NORMAS-TRANSVERSALES-TR.md`, `envelope-respuestas.md`, `matriz-permisos-mvp.md`, TR-GEN-03-grillas-listados.

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (aplicar resoluciones §3.2; ajuste menor en §5.2)

### Ambigüedades críticas

> **Qué significa «resolver» en C1:** no es una tarea pendiente para vos como producto. La revisión detectó huecos; **§3.2 ya fija la decisión**. En D1/D solo hay que **implementar** lo de §3.2 y §5.2. Si estás de acuerdo con §3.2, no hace falta decidir nada más.

| ID | Tema | Riesgo | Estado | Qué hacer (D1 / código) |
|----|------|--------|--------|-------------------------|
| AMB-C01 | **Cargar layout sin `GET /{id}`** | El listado no trae `stateJson`; no estaba claro cómo aplicar un layout del selector | **Cerrado** (R-C1-01) | Ver flujo abajo § «Flujo usuario: Cargar». No agregar `GET /{id}` en MVP. |
| AMB-C02 | **Duplicado `409/2001`** | Código HTTP y `error` del envelope mezclados | **Cerrado** (R-C1-02) | En POST duplicado: HTTP **409** + cuerpo `{ error: 2001, respuesta: "gridLayout.duplicateName", resultado: {} }`. |
| AMB-C03 | **Ejemplos API sin envelope** | Backend podría devolver JSON «plano» | **Cerrado** (R-C1-03) | Todas las respuestas usan envelope MONO; ver ejemplos §5.2. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | `gridLayoutsEnabled` | Ampliar `GET /api/v1/config/public` con `gridLayoutsEnabled: true` por defecto en MVP; si `false`, ocultar toolbar (AC-11). |
| AMB-M02 | `GET /active` vacío | `resultado: { layoutId: null, stateJson: null }` o `{}` — elegir **shape fijo** en OpenAPI (D1: objeto con `layoutId` nullable). |
| AMB-M03 | Tamaño `state_json` | Validar **512 KB** en request (ya en §9); respuesta 1000 si excede. |
| AMB-M04 | Renombrar layout | Fuera MVP (solo `stateJson` en PUT) — confirmado. |
| AMB-M05 | Permiso “solo creador” en PUT/DELETE | **403** + `error: 3001` (autorización), no 200 con error genérico. |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| HU “API CRUD” vs listado sin detalle | Cargar usa **active**, no GET by id en MVP. |
| AC-05 TR vs HU CA-09 duplicado nombre | Coherentes; clave i18n única `gridLayout.duplicateName`. |

### Supuestos detectados

- Tabla `users` y auth Sanctum ya operativos (TR-GEN-02-login-sesion).
- `state_json` compatible con `DataGrid.state()` / `state(state)` DevExtreme.

### Preguntas para decisión humana

(Ninguna bloqueante — cerradas en §3.2.)

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-01)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Flujo **Cargar** | `PUT /active` → `GET /active` → aplicar estado; sin `GET /{id}` en MVP. |
| R-C1-02 | Duplicado nombre | HTTP **409**, `error: 2001`, clave `gridLayout.duplicateName`. |
| R-C1-03 | Envelope | Ejemplos y OpenAPI con envelope MONO completo. |
| R-C1-04 | Flag infra | `gridLayoutsEnabled` en `config/public`; default **true** tras migración. |
| R-C1-05 | Plantilla sistema | `layoutId: null` en `last_used` / active = plantilla DX por defecto del proceso. |

---

## 3.3) Plan D1 — Implementación (2026-06-01)

**Depende de:** TR-GEN-03-grillas-listados entregada (`DataGridDx` + slots `toolbarEnd`).

### Alcance entendido

Migración `pq_grid_layouts` + `pq_grid_layout_last_used`, API CRUD + active (envelope MONO), policies creador, frontend `GridLayoutToolbar` + `useGridLayouts`, integración en `DataGridDx`, flag `gridLayoutsEnabled` en `config/public`, tests Feature + E2E persistencia.

### Decisiones D1

| ID | Decisión |
|----|----------|
| D1-1 | Flujo Cargar: `PUT /active` → `GET /active` → `state()` (sin `GET /{id}`) |
| D1-2 | Duplicado: HTTP 409, `error: 2001`, `gridLayout.duplicateName` |
| D1-3 | No creador PUT/DELETE: HTTP 403, `error: 3001` |
| D1-4 | `gridLayoutsEnabled: true` en `GET /api/v1/config/public` |
| D1-5 | Validación `stateJson` max 512 KB |
| D1-6 | Seed test: layout `Vista default` en `pw_dashboard`/`main` para supervisor |

### Orden de trabajo

1. Migración + models `GridLayout`, `GridLayoutLastUsed`
2. `GridLayoutController` + FormRequest + Policy
3. Rutas `api.php` + OpenAPI + matriz (ya esbozada)
4. Feature tests: list, create, duplicate 409, 403 no creador, active roundtrip
5. `gridLayoutsApi.ts`, `useGridLayouts.ts`, `GridLayoutToolbar.tsx`
6. Montar en `DataGridDx` `toolbarEnd` (primero en cadena R-C1-02 grillas)
7. i18n `gridLayout.*`
8. E2E: guardar → F5 → columnas persisten
9. Ampliar `config/public` controller/response

### Confirmación de alcance

**Sí** — sin export, ABM, renombrar layout, ni `GET /{id}`.

### Flujo usuario: Cargar un layout (resuelve AMB-C01)

```text
1. GET /grid-layouts?proceso=&gridId=     → lista nombres (sin stateJson)
2. Usuario elige "Mi vista" (id=5) en el SelectBox
3. PUT /grid-layouts/active               → body: { proceso, gridId, layoutId: 5 }
4. GET /grid-layouts/active?proceso=&gridId= → resultado.stateJson completo
5. Frontend: dataGrid.instance.state(stateJson)
```

Al **entrar a la pantalla**, solo hace falta el paso **4** (y 5): el backend ya conoce el último layout del usuario en `pq_grid_layout_last_used`.

### Flujo usuario: Guardar como con nombre duplicado (resuelve AMB-C02)

```text
POST /grid-layouts con layoutName ya usado en ese proceso+gridId
→ HTTP 409
→ { "error": 2001, "respuesta": "gridLayout.duplicateName", "resultado": {} }
→ UI muestra i18n; no se pierde la vista actual
```

---

## 4) Impacto en Datos

### Tablas nuevas

#### `pq_grid_layouts`

| Columna | Tipo | Notas |
|---------|------|-------|
| `id` | PK bigint | |
| `proceso` | varchar(128) | `pq_menus.procedimiento` |
| `grid_id` | varchar(64) | |
| `layout_name` | nvarchar(128) | |
| `created_by_user_id` | bigint FK → users | |
| `state_json` | nvarchar(max) | JSON estado DX |
| `created_at` / `updated_at` | datetime | |

**Índice único:** `UX_pq_grid_layouts_proceso_grid_layout` en `(proceso, grid_id, layout_name)`.

#### `pq_grid_layout_last_used` (recomendado)

| Columna | Tipo | Notas |
|---------|------|-------|
| `user_id` | FK users | |
| `proceso` | varchar | |
| `grid_id` | varchar | |
| `layout_id` | FK nullable → `pq_grid_layouts` | `NULL` = plantilla sistema |
| `updated_at` | datetime | |

**PK o único:** `(user_id, proceso, grid_id)`.

### Seed mínimo para tests

- Usuario supervisor: layout `Vista default` en `pw_dashboard` / `main`.
- Usuario acotado: sin layouts (prueba plantilla sistema).

### Migración

- `backend/database/migrations/YYYY_MM_DD_create_pq_grid_layouts_tables.php`

---

## 5) Contratos de API y OpenAPI

> Bearer + `X-Paq-Cliente` · envelope MONO · usuario autenticado (sin `Permiso_*` adicional salvo política global).

### 5.1 Endpoints

| Método | Path | Auth | Permiso | Público |
|--------|------|------|---------|---------|
| GET | `/api/v1/grid-layouts` | Bearer + tenant | Usuario autenticado | No |
| GET | `/api/v1/grid-layouts/active` | Bearer + tenant | Usuario autenticado | No |
| POST | `/api/v1/grid-layouts` | Bearer + tenant | Usuario autenticado | No |
| PUT | `/api/v1/grid-layouts/{id}` | Bearer + tenant | Solo creador | No |
| DELETE | `/api/v1/grid-layouts/{id}` | Bearer + tenant | Solo creador | No |
| PUT | `/api/v1/grid-layouts/active` | Bearer + tenant | Usuario autenticado | No |

**Query GET list:** `proceso`, `gridId` (requeridos).

### 5.2 Detalle

#### GET `/api/v1/grid-layouts`

**Response 200 (envelope completo — AMB-C03):**

```json
{
  "error": 0,
  "respuesta": "",
  "resultado": {
    "items": [
      {
        "id": 1,
        "layoutName": "Mi vista",
        "createdByUserId": 12,
        "isOwner": true,
        "updatedAt": "2026-06-01T12:00:00Z"
      }
    ]
  }
}
```

(No incluir `state_json` en listado; solo en GET active o GET by id si se agrega.)

#### GET `/api/v1/grid-layouts/active?proceso=&gridId=`

Devuelve layout completo del último usado del usuario.

**Response 200 (envelope):**

```json
{
  "error": 0,
  "respuesta": "",
  "resultado": {
    "layoutId": 1,
    "layoutName": "Mi vista",
    "stateJson": { }
  }
}
```

Si plantilla sistema: `layoutId: null`, `layoutName: null`, `stateJson: null`.

**Flujo Cargar (selector):** `PUT /active` con `layoutId` elegido → `GET /active` → aplicar `stateJson` al grid (C1 R-C1-01).

#### POST `/api/v1/grid-layouts`

**Body:** `{ "proceso", "gridId", "layoutName", "stateJson" }`  
**201** creado · **409** con `error: 2001` y `respuesta: "gridLayout.duplicateName"` si nombre duplicado en `proceso`+`gridId`.

#### PUT `/api/v1/grid-layouts/{id}`

**Body:** `{ "stateJson" }` opcional `{ "layoutName" }` si se permite renombrar (MVP: solo state).  
**403** si no es creador.

#### DELETE `/api/v1/grid-layouts/{id}`

**403** si no es creador.

#### PUT `/api/v1/grid-layouts/active`

**Body:** `{ "proceso", "gridId", "layoutId": number | null }` — `null` = plantilla sistema.

**Errores:** 401, 403 (si aplica), validación 1000.

### 5.3 Matriz permisos

- [ ] Agregar sección **Layouts de grilla** en `matriz-permisos-mvp.md`.

---

## 6) Cambios Frontend

### Componentes

```text
frontend/src/features/gridLayouts/
  api/gridLayoutsApi.ts
  hooks/useGridLayouts.ts
  components/GridLayoutToolbar.tsx
  model/gridLayoutTypes.ts
```

- Integrar `GridLayoutToolbar` en slot `toolbarEnd` de `DataGridDx`.
- Flujos: SelectBox layouts + botones DX; Popup nombre en Guardar como.
- `useGridLayouts(proceso, gridId)` orquesta list, active, save, delete.

### Serialización

```typescript
// Aplicar / capturar
dataGridRef.instance.state();
dataGridRef.instance.state(state);
```

### Plantilla sistema

- `activeLayoutId === null` y sin fila en last_used → estado default columnas del proceso.
- **Guardar** en ese estado → abrir diálogo Guardar como (POST).

### data-testid

- `gridLayoutSelect`, `gridLayoutSave`, `gridLayoutSaveAs`, `gridLayoutDelete`, `gridLayoutSaveAsDialog`

### Degradación sin infra

- `GET /api/v1/config/public` o flag en features: `gridLayoutsEnabled: boolean`.
- Si `false`: no renderizar `GridLayoutToolbar`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | DB | Migración `pq_grid_layouts` + `pq_grid_layout_last_used` | Migración verde |
| T2 | Backend | Model, repository, `GridLayoutController` | Envelope |
| T3 | Backend | Policies creador + tests Feature 401/403/duplicate | OpenAPI |
| T4 | Frontend | `gridLayoutsApi` + `useGridLayouts` | |
| T5 | Frontend | `GridLayoutToolbar` + integración `DataGridDx` | AC-08 |
| T6 | i18n | `gridLayout.*` 5 locales | |
| T7 | Tests | Integration API + E2E layout persist | AC-10 |

---

## 8) Estrategia de Tests

- **Integration:** CRUD + duplicate name + 403 no creador + active last used.
- **E2E:** `grid-layouts.spec.ts` en `/consultas/historial` (toolbar, Guardar como, sufijo propios).

---

## 9) Riesgos y Edge Cases

- Payload JSON grande → validar tamaño máximo en backend (ej. 512 KB).
- Renombrar layout: fuera MVP salvo necesidad explícita.
- Concurrencia: dos usuarios crean mismo nombre → segundo recibe error duplicado.

---

## 10) Verificación F formal (2026-06-01)

- **Tests:** `GridLayoutTest` 6 OK; E2E `grid-layouts.spec.ts` 2 OK
- **Evidencia:** migración `pq_grid_layouts`, `GridLayoutController`, `GridLayoutToolbar`, flag `gridLayoutsEnabled`

**CC PQ #2 (05/06/2026) — F1/F:** [F-CC-PQ-02-GEN-03-cierre-formal](F-CC-PQ-02-GEN-03-cierre-formal.md) — Aprobado con observaciones (09/06/2026). E2E reorientados a `/consultas/historial` (3 OK).

---

## 11) Checklist final

- [x] AC cumplidos
- [x] Matriz actualizada (`matriz-permisos-mvp.md` § layouts)
- [x] Coherente con patrón MONO layouts (TR + código)

---

## Historial CC PQ #2 (05/06/2026) — Parte I 09/06/2026

Corrección layouts: sufijo propios y reset plantilla del sistema.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | Sufijo ` (*)` en selector si `isOwner` | `GridLayoutToolbar.tsx`, i18n `gridLayout.ownerMarker` |
| T2 | Plantilla sistema restaura grilla original | `DataGridDx.tsx` → `instance.state(null)` |
| T3 | Reglas estándar | `08-devextreme-grid-standards.md` §1.11, `grillas.md` |
| T4 | E2E toolbar + Guardar como + sufijo | `grid-layouts.spec.ts` en `/consultas/historial` |

---

## Historial CC PQ #3 (09/06/2026) — Parte I 09/06/2026

Corrección: persistencia de totalizadores del pie (footer Summary) al guardar/cargar layouts.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | Auditar `captureCurrentState` vs `instance.state().summary` | `DataGridDx.tsx`, `useGridLayouts.ts` |
| T2 | Persistir/restaurar `paqSummaryTotalItems` sin placeholders | `PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY`, `filterRealSummaryItems` |
| T3 | Regla mono layouts + totalizadores | `08-devextreme-grid-standards.md` §1.11 |
| T4 | Vitest serialización layout con footer | `dataGridDxLayoutState.test.ts` |

---

## Orden en bloque GEN-03

**2/4** — Requiere `DataGridDx` (TR-GEN-03-grillas-listados).










