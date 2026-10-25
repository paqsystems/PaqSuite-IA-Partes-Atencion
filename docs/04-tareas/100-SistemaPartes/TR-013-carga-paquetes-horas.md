# TR-013 – Carga de paquetes de horas (ABM + Smart Capture modal)

Jira: PIAPARTES-48

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-013-carga-paquetes-horas](../../03-historias-usuario/100-SistemaPartes/HU-013-carga-paquetes-horas.md) |
| **SPEC relacionada** | [SPEC-013-carga-paquetes-horas](../../05-open-spec/100-SistemaPartes/SPEC-013-carga-paquetes-horas.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | **Solo supervisor** (`resultado.partes.esSupervisor = true`); asistente no supervisor y cliente **403** |
| **Dependencias** | [TR-002](./TR-002-identidad-funcional-y-acceso.md), [TR-003](./TR-003-maestros-y-catalogos.md), [TR-004](./TR-004-operacion-carga-diaria.md) (tramo, modal, `rowVersion`, fecha futura), [TR-006](./TR-006-consultas-dashboard-navegacion.md) (informe verificación), [TR-008](./TR-008-asistente-ia-chat-documental.md) (BYOK/timeout), [TR-010](./TR-010-smart-capture-carga-diaria.md) (turno GEN-03 base) |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Pendiente de Revisión |
| **Revisión C1** | Apto (ver §11) |
| **Última actualización** | 2026-10-21 |

**Origen:** [HU-013](../../03-historias-usuario/100-SistemaPartes/HU-013-carga-paquetes-horas.md)  
**Referencia SPEC:** [SPEC-013](../../05-open-spec/100-SistemaPartes/SPEC-013-carga-paquetes-horas.md) · A1 [review](../../05-open-spec/100-SistemaPartes/SPEC-013-carga-paquetes-horas-A1-ambiguity-review.md)

---

## 1) HU refinada (resumen)

### Narrativa

Como supervisor quiero ABM de compras de horas (`es_tarea = false`) en pantalla propia y Smart Capture en el modal, para alimentar el informe Paquete de Horas sin mezclar con carga diaria.

### In scope

- API REST `/api/v1/partes/paquetes` (list/get/upsert/delete) + gate supervisor.
- SP familia **`pq_sp_partes_paquete_*`** (list/get/upsert/delete); fuerza `es_tarea = 0` y defaults §SPEC-013.
- FE: `CargaPaquetesPage` — filtros (default **mes calendario**), `ProcessDataGrid`, Popup ABM, errores en modal.
- Menú `partes_carga_paquetes` id **30300** bajo Partes (30000); permiso distinto de carga diaria.
- `filterMenuForNonSupervisor` + `partesMobilePolicy` excluyen ruta mobile.
- GEN-11: `proceso=partes.carga.paquetes`, `gridId=cargaPaquetes`; layouts compartidos (TR-004).
- SC: **mismo** `POST /api/v1/partes/tareas/asistente/turn` con `draftContext.processKind = 'paquete'`; rama en `PartesTareaSmartCaptureTurnService` (o servicio dedicado invocado desde el mismo controller).
- Duración de paquete: horas enteras de 8 a 240 inclusive; persistencia en minutos equivalentes (`480` a `14400`).
- i18n `partes.paquete.*`, hint SC `partes.paquete.smartCapture.hint`.
- Tests Feature + Vitest + E2E humo; OpenAPI.

### Out of scope

- Mobile; Excel import/export Must; GEN-15; chat documental; masivo; DDL saldos; endpoints `/partes/tareas` para paquetes.

### Decisiones TR (cierran HU B1)

| Tema | Decisión |
|------|----------|
| API base | **`/api/v1/partes/paquetes`** (no reutilizar CRUD `/tareas`) |
| SP | Familia **`pq_sp_partes_paquete_*`** (no ampliar upsert tarea con flag cliente) |
| Authz | Middleware nuevo **`partes.supervisor`** → 403 `partes.paquete.soloSupervisor` |
| Menú | `id=30300`, `codigo=partes_carga_paquetes`, ruta `/partes/carga-paquetes`, `label_key=menu.partes_carga_paquetes` |
| Default fechas FE | 1.er y último día del **mes calendario** del sistema |
| `usuario_id` update | SP upsert **no** recibe cambio de propietario en update; conserva fila |
| notPaquete | **422** `partes.paquete.notPaquete` |
| Fecha futura API | Mismo flag **`confirmarFechaFutura`** que TR-004 |
| `rowVersion` | String hex opaco (TR-004 RN-TR-04) |
| Turno SC | Path **`POST .../tareas/asistente/turn`** + `processKind: 'paquete'` |
| `draftContext` paquete | `mode`, `id`, `processKind`, `clienteId`, `fecha`, `duracionMinutos`, `observacion`, `esSupervisor` |
| Actions SC | `setField` solo: `clienteId`, `fecha`, `duracionMinutos`, `observacion`; `save` → FE guarda vía API paquetes |
| Export grilla | `canExport={false}` (o omitir); no viola SPEC |

---

## 2) Criterios de aceptación (AC)

Mapeo HU CA-01…20:

| AC | Verificación |
|----|--------------|
| AC-01 | Feature: asistente/cliente → 403 en `/partes/paquetes*`; menú oculto vía `filterMenuForNonSupervisor` |
| AC-02 | Vitest/E2E: al montar página, `fechaDesde`/`fechaHasta` = mes en curso |
| AC-03 | Feature list sin fechas → 422 `partes.paquete.fechasRequeridas`; con fechas solo `es_tarea=0` |
| AC-04 | Feature store: flags forzados + `usuarioId` = asistente supervisor |
| AC-05 | Feature/Vitest: validación obligatorios y horas enteras 8–240 |
| AC-06 | Feature: fecha futura sin flag → 422 `partes.paquete.fechaFuturaConfirmacion` |
| AC-07 | Feature update con cliente inhabilitado histórico OK; cambio a inhabilitado → 422 catálogo |
| AC-08 | Vitest: `formError` en Popup; limpia al cancelar |
| AC-09 | Feature update: `usuarioId` inmutable |
| AC-10 | E2E/Feature: delete con confirmación DX |
| AC-11 | Feature informe paquete tras alta (resta minutos) — reutilizar patrón `ApiV1PartesInformeTest` |
| AC-12 | Feature update/delete id tarea → 422 `notPaquete` |
| AC-13 | Feature store ignora `esTarea=true` en body |
| AC-14 | Feature list carga diaria no devuelve paquetes |
| AC-15 | Vitest: SC montado solo con modal abierto |
| AC-16 | Feature grid-layouts par paquetes compartido |
| AC-17 | Mock turno sin credencial → 409 configurationRequired |
| AC-18 | Feature turno: ambigüedad cliente; fecha futura pendingChoice |
| AC-19 | Vitest: action save llama `store`/`update` paquetes |
| AC-20 | i18n 5 locales + testids `partesCargaPaquetes*` |

---

## 3) Reglas de implementación

| ID | Regla |
|----|--------|
| RN-TR-01 | List exige `fechaDesde` + `fechaHasta` (ISO date); si faltan → 422 `partes.paquete.fechasRequeridas`. |
| RN-TR-02 | Query opcional `clienteId`; NULL = todos usables del universo supervisor en SP. |
| RN-TR-03 | Upsert insert: `p_usuario_id` = `asistenteId` actor; update: SP ignora cambio de `usuario_id`. |
| RN-TR-04 | Upsert fuerza `es_tarea=0`, `tipo_tarea_id`=default (`is_default`), `sin_cargo`/`presencial`/`cerrado`=0. |
| RN-TR-05 | Get/update/delete: si fila `es_tarea=1` → 422 `partes.paquete.notPaquete`. |
| RN-TR-06 | Fecha futura: `confirmarFechaFutura=true` en body POST/PUT (igual TR-004). |
| RN-TR-07 | `rowVersion` stale → **409** `partes.paquete.conflictoVersion`. |
| RN-TR-08 | Turno: si `draftContext.processKind !== 'paquete'` → comportamiento TR-010 existente. |
| RN-TR-09 | Turno paquete: rechazar si `!esSupervisor`; no emitir `setField` asistente/tipo/bits. |
| RN-TR-10 | Hint SC: clave **`partes.paquete.smartCapture.hint`**. |
| RN-TR-11 | Operations MONO: `PartesPaqueteOperations` registrado en dispatcher SP (patrón `PartesTareaOperations`). |
| RN-TR-12 | Scripts SQL Server: `backend/scripts/sql/partes/pq_sp_partes_paquete_*.sql` (deploy install/update). |

R-PH-01…19 y R-PH-SC-01…05 del SPEC aplican en SP/FE.

---

## 4) Datos — SP

### 4.1 Contratos (nombres cerrados)

| SP | Rol |
|----|-----|
| `pq_sp_partes_paquete_list` | Filtro fechas + cliente opcional; solo `es_tarea=0`; paginación; actor supervisor |
| `pq_sp_partes_paquete_get` | Por `@p_id`; 422 lógico si no paquete |
| `pq_sp_partes_paquete_upsert` | Insert/update; valida obligatorios, horas enteras 8–240, cliente usable (alta/cambio); optimistic lock |
| `pq_sp_partes_paquete_delete` | Solo paquete; `row_version` |

Parámetros actor (como tareas): `@p_actor_usuario_id`, `@p_actor_asistente_id`, `@p_actor_es_supervisor`, `@p_actor_tipo_funcional`, …

List: `@p_fecha_desde`, `@p_fecha_hasta`, `@p_cliente_id` NULL, `@p_page`, `@p_page_size`.

Upsert body conceptual: `clienteId`, `fecha`, `duracionMinutos`, `observacion`, `rowVersion?`, `confirmarFechaFutura?`.

**Tipo default:** subconsulta `PQ_PARTES_TIPOS_TAREA` con `is_default=1` activo; si no existe → 422 `partes.paquete.tipoDefaultFaltante`.

### 4.2 Menú / seed

| Campo | Valor |
|-------|--------|
| `id` | **30300** |
| `parent_id` | 30000 |
| `codigo` / `procedimiento` | `partes_carga_paquetes` |
| `ruta` | `/partes/carga-paquetes` |
| `orden` | 30300 |
| `label_key` | `menu.partes_carga_paquetes` |

Actualizar `PqMenuSeeder` + `productI18n.ts` (5 locales). Matriz menú Framework: visibilidad por rol vía filtro FE supervisor (mismo patrón masivo); no reutilizar permiso Excel `partes_carga_diaria`.

---

## 5) Contratos API

Base: `/api/v1/partes/paquetes` — Bearer + tenant + `partes.profile` + **`partes.supervisor`**.

| Método | Path | Notas |
|--------|------|-------|
| GET | `/partes/paquetes` | Query: `fechaDesde`, `fechaHasta`, `clienteId?`, `page`, `pageSize` |
| GET | `/partes/paquetes/{id}` | |
| POST | `/partes/paquetes` | Body + `confirmarFechaFutura?` |
| PUT | `/partes/paquetes/{id}` | + `rowVersion` |
| DELETE | `/partes/paquetes/{id}` | Body/query `rowVersion` |

**Smart Capture (sin path nuevo):**

| Método | Path |
|--------|------|
| POST | `/partes/tareas/asistente/turn` |

Request Must incluir `draftContext.processKind = 'paquete'` cuando el panel está en `CargaPaquetesPage`.

Respuesta envelope GEN-20; errores 403/409/422 alineados a claves `partes.paquete.*` y GEN SC (4201).

OpenAPI: `OpenApiPathsPartesOperacion` — tag **Partes Paquetes** + ampliar descripción turno.

---

## 6) Frontend

| Pieza | Detalle |
|-------|---------|
| Ruta | `/partes/carga-paquetes` |
| Componente | `frontend/src/features/partes/carga/CargaPaquetesPage.tsx` (+ form types, validation, API client) |
| Router | `AppRouter.tsx` — ruta autenticada Partes |
| Menú | `PartesMenuSidebar`: extender `filterMenuForNonSupervisor` con `partes_carga_paquetes` / `/partes/carga-paquetes` |
| Mobile | `partesMobilePolicy`: denegar `/partes/carga-paquetes` |
| Filtros | `DateBox` `dd/MM/yyyy` + máscara (regla 35); cliente `SelectBox` catálogo TR-003 |
| Grid | `ProcessDataGrid` `proceso="partes.carga.paquetes"` `gridId="cargaPaquetes"`; duración en horas enteras; **sin** `canExport` Must |
| Modal | Popup ABM; `listError` / `formError` separados (regla 36); `data-testid` `partesCargaPaquetesFormError` |
| Duración | Selector propio de horas enteras 8–240; API/SP persisten minutos equivalentes |
| SC | `SmartCapturePanel` en Popup; `buildSmartCaptureTurnRequest` con `processKind`; `applyPartesPaqueteAction` (mapper campos reducido) |
| i18n | `partes.paquete.*`, `menu.partes_carga_paquetes` en `productI18n` |

---

## 7) Plan de tareas (D1)

Plan detallado: [D1-TR-013-carga-paquetes-horas](./d1/D1-TR-013-carga-paquetes-horas.md) (2026-10-21).

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | DB | Scripts `pq_sp_partes_paquete_*` + registro install | AC list/upsert |
| T2 | BE | `PartesPaqueteOperations` + `PartesPaqueteController` + rutas + `partes.supervisor` | AC API |
| T3 | BE | Extender turno SC rama `paquete` + tests turn | AC-15…19 |
| T4 | Seed | Menú 30300 + i18n menú | AC-01 menú |
| T5 | FE | `CargaPaquetesPage` filtros+grid+modal | AC-02…10 |
| T6 | FE | SC panel + draft mapper | AC-15…19 |
| T7 | FE | Policy menú + mobile | AC-01, mobile |
| T8 | Tests | Feature `ApiV1PartesPaquetesTest`; Vitest validation/SC; E2E humo alta | AC suite |
| T9 | Docs | OpenAPI + párrafo manual usuario (SC paquete ≠ chat) | RN-TR |

**Orden:** T1 → T2 → T4 → T5 → T6 → T3 → T7 → T8 → T9.

---

## 8) Tests

| Capa | Casos |
|------|--------|
| Feature | 403 roles; list fechas; CRUD paquete; notPaquete 422; 409 version; fecha futura; informe integración |
| Feature SC | Turn con `processKind=paquete`; cliente ambiguo; save action |
| Vitest | `cargaPaquetesPersistErrorKey`; default mes filtros; apply SC actions |
| E2E | Supervisor abre paquetes, alta mínima, aparece en grilla |

---

## 9) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Drift SP gateway vs Operations | Misma firma en SQL scripts y adapter |
| SC comparte path con tareas | `processKind` obligatorio en FE paquetes; test regresión TR-010 |
| Tipo default ausente en lab | Seed TR-003 + test instalación |

---

## 10) Checklist pre-D

- [x] AC-01…20 mapeados
- [x] SP + menú 30300 documentados
- [x] C1 apto
- [x] HU/SPEC enlazados
- [x] D1 plan ([D1-TR-013](./d1/D1-TR-013-carga-paquetes-horas.md))

---

## 10 bis) Cierre de implementación (Parte D)

### Entregado

- Backend: middleware `partes.supervisor`, controller/repository/operations de paquetes, rutas CRUD `/api/v1/partes/paquetes`, dispatcher SP y validaciones de `es_tarea`, propietario, fecha futura, horas enteras 8–240 y `rowVersion`.
- Datos: scripts `backend/scripts/sql/partes/pq_sp_partes_paquete_{list,get,upsert,delete}.sql`.
- Frontend: `CargaPaquetesPage`, ruta, filtros mensuales, grilla/modal, menú 30300, i18n, policy mobile y Smart Capture `processKind=paquete`.
- Smart Capture: rama backend y adapters frontend; `save` queda delegado al ABM de paquetes.
- Tests: Feature CRUD/informe/SC, Vitest y E2E preparado.

### Verificación

| Suite | Resultado |
|-------|-----------|
| Backend completo | **120 passed, 7 skipped, 822 assertions** |
| Frontend Vitest completo | **56 archivos, 173 tests passed** |
| Feature paquetes | **7 passed, 48 assertions** |
| Smart Capture Partes | **19 passed, 107 assertions** |
| E2E paquetes | **Bloqueado**: el servidor Vite inició, pero Playwright no produjo avance ni salida adicional; se detuvo tras ~40 s. |

### Bloqueos para revisión

- Ejecutar el E2E en un entorno con backend/API accesible y revisar por qué el navegador no completó el login/primer request en la ejecución local.
- Aplicar los cuatro scripts SP en SQL Server antes del despliegue gateway.

### Trazabilidad D

- Estado actualizado a **Pendiente de Revisión**.
- D1: [D1-TR-013](./d1/D1-TR-013-carga-paquetes-horas.md).

---

## 11) Informe C1

## Resultado general

- **Apto**

## Críticas cerradas

- API dedicada `/partes/paquetes` vs mezclar en `/tareas`.
- SP familia `pq_sp_partes_paquete_*`.
- Middleware `partes.supervisor`.
- Menú id **30300** y código `partes_carga_paquetes`.
- SC: mismo endpoint turno + `processKind: 'paquete'`.
- 422 `notPaquete`; 409 conflicto; fecha futura flag compartido.

## Menores

- Nombres exactos de columnas DTO list (p. ej. `registradoNombre`) se fijan en D alineando grilla.
- SQL scripts pueden entregarse en T1 aunque gateway agente quede para release posterior (patrón TR-004).

## Contradicciones

- Ninguna con SPEC-013 / HU-013 / A1.

## Veredicto

- **Puede pasar a D1/D: Sí**

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-21 | Parte C + C1: TR-013 desde SPEC/HU; contratos API/SP/menú/SC cerrados. |
| 2026-10-21 | Jira catch-up: Task **PIAPARTES-48** (columna Especificado). |
| 2026-10-21 | Parte D1: [D1-TR-013](./d1/D1-TR-013-carga-paquetes-horas.md). |
