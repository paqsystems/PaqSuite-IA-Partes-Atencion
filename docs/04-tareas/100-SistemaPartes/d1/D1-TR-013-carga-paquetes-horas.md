# Plan de implementación - TR-013

## Tipo de trabajo

Original (Must MVP Partes — carga de paquetes de horas).

## Alcance entendido

ABM web **solo supervisor** de registros `PQ_PARTES_REGISTRO_TAREA` con **`es_tarea = false`** (compras/créditos de horas), en pantalla y menú **propios** (no carga diaria):

- API dedicada **`/api/v1/partes/paquetes`** (CRUD + envelope GEN-20).
- Runtime MONO: **`PartesPaqueteOperations`** (patrón `PartesTareaOperations`) + scripts T-SQL **`pq_sp_partes_paquete_*`** (gateway follow-up).
- Middleware **`partes.supervisor`** → 403 `partes.paquete.soloSupervisor`.
- FE: **`CargaPaquetesPage`** — filtros default **mes calendario**, `ProcessDataGrid` (`partes.carga.paquetes` / `cargaPaquetes`), Popup ABM, `listError`/`formError` (regla 36).
- Menú **`30300`** `partes_carga_paquetes` → `/partes/carga-paquetes`; oculto a no supervisores (FE) + 403 API.
- **Smart Capture** en modal: mismo **`POST /partes/tareas/asistente/turn`** con **`draftContext.processKind = 'paquete'`**; campos reducidos; `save` → API paquetes (TR-010 base).
- Reutilizar tramo **`GET /partes/parametros/duracion-tramo`**, validación fecha futura **`confirmarFechaFutura`**, `rowVersion` hex (TR-004).
- **No:** mobile, Excel Must, CRUD vía `/tareas`, pivot, chat documental.

## Artefacto gobernante

[SPEC-013-carga-paquetes-horas](../../05-open-spec/100-SistemaPartes/SPEC-013-carga-paquetes-horas.md)

## Fuentes leídas

- SPEC-013 + A1 review
- HU-013 (CA-01…20)
- TR-013 (C1 apto; T1–T9)
- Producto: `docs/02-producto/Sistema-Partes-IA/16-carga-paquetes-horas.md`, `14-smart-capture.md` (superficie paquete)
- Host ref.: `PartesTareaController`, `PartesTareaOperations`, `PartesTareaRepository`, `SpCaller`
- TR-004 `CargaDiariaPage`, `partesTareaApi`, duración/tramo, modal + SC (TR-010)
- TR-006 `ApiV1PartesInformeTest` (integración informe paquete-horas)
- Menú: `PqMenuSeeder` (bloque 30000), `PartesMenuSidebar.filterMenuForNonSupervisor`, `partesMobilePolicy`
- OpenAPI: `OpenApiPathsPartesOperacion.php`

## Dependencias (estado)

| TR | Requisito para TR-013 | Estado esperado |
|----|------------------------|-----------------|
| TR-002 | Perfil Partes, `esSupervisor`, gate asistente | **Hecho** |
| TR-003 | Catálogo clientes, tipo default | **Hecho** |
| TR-004 | Tramo, modal, `rowVersion`, fecha futura, grilla GEN-11 | **Hecho** |
| TR-006 | Informe `paquete-horas` consume `es_tarea=0` | **Hecho** |
| TR-008 | BYOK / timeout LLM | **Hecho** |
| TR-010 | Turno SC base en carga diaria | **Hecho** |

Sin bloqueo humano para arrancar **D**.

## Impacto esperado

### Base de datos

| Acción | Detalle |
|--------|---------|
| DDL | **Ninguna** (tabla `pq_partes_registro_tarea` + `es_tarea` ya existen) |
| Crear | Scripts T-SQL `backend/scripts/sql/partes/pq_sp_partes_paquete_list.sql`, `_get`, `_upsert`, `_delete` (contrato TR §4; despliegue install/update) |
| Runtime tests | **`PartesPaqueteOperations`** en PHP (parity TR-004: Query Builder + reglas RN-TR) — obligatorio para Feature en sqlite |
| Seed | Ítem menú **30300** en `PqMenuSeeder` (entre 30200 masivo y 40000 informes); `label_key` / i18n `menu.partes_carga_paquetes` |

### Backend

| Crear | Rol |
|-------|-----|
| `EnsurePartesSupervisor` | Alias `partes.supervisor`; lee `EnsurePartesFunctionalProfile::REQUEST_ATTR`; si `!esSupervisor` → 403 `partes.paquete.soloSupervisor` |
| `PartesPaqueteException` | Mapeo claves `partes.paquete.*` + HTTP (422/409) — espejo `PartesTareaException` |
| `PartesPaqueteOperations` | `dispatch` list/get/upsert/delete; filtro **`es_tarea=0`**; upsert fuerza defaults (tipo default, flags 0); insert `p_usuario_id` = asistente actor; update **no** cambia propietario |
| `PartesPaqueteRepository` | Wrapper SP params + `encodeRowVersion` (reutilizar helper tarea) |
| `PartesPaqueteController` | list/show/store/update/destroy; actor params como `PartesTareaController`; validación request |

| Modificar | Cambio |
|-----------|--------|
| `AppServiceProvider` | Registrar alias middleware `partes.supervisor` |
| `routes/api.php` | Grupo `partes.profile` + `partes.notCliente` + **`partes.supervisor`**: rutas `/partes/paquetes` (+ `{id}`) **antes** de colisiones |
| `SpCaller` | `isPartesPaqueteProcedure` → `PartesPaqueteOperations::dispatch` (no confundir con prefijo `pq_sp_partes_tarea_`) |
| `PartesTareaSmartCaptureTurnService` (+ prompt/resolver si aplica) | Si `draftContext.processKind === 'paquete'`: gate supervisor; prompt/campos solo cliente/fecha/duración/observación; negar asistente/tipo/bits; `save` sin upsert BE |
| `LlmPartesSmartCaptureProposal` / system prompt | Rama paquete (keywords observación vs descripción tarea) |
| `OpenApiPathsPartesOperacion.php` | Tag **Partes Paquetes** + paths CRUD; ampliar doc turno con `processKind=paquete` |
| Envelope catálogo (si falta) | Claves `partes.paquete.*` en responses documentadas |

**List tareas (AC-14):** verificar que `PartesTareaOperations::list` sigue filtrando **`es_tarea=1`** (o equivalente); si hoy lista todo, **ajuste mínimo** en list tarea para excluir paquetes (solo si test AC-14 lo exige).

### Frontend

| Crear | Rol |
|-------|-----|
| `partesPaqueteApi.ts` | list/get/create/update/delete + query fechas/cliente/paginación |
| `cargaPaquetesFormTypes.ts` / validation | Obligatorios, tramo, `confirmarFechaFutura`, persist error keys |
| `cargaPaquetesDefaultFilters.ts` | 1.er y último día mes calendario local |
| `CargaPaquetesPage.tsx` | `ProcessPageLayout`; filtros; grid; Popup ABM; sin export Must |
| `buildPartesPaqueteDraftContext.ts` | `processKind: 'paquete'` + campos reducidos |
| `applyPartesPaqueteSmartCaptureActions.ts` | `setField` cliente/fecha/duración/observación; `save` → `partesPaqueteApi` |
| Vitest | defaults mes; validation keys; apply SC; draft context |

| Modificar | Cambio |
|-----------|--------|
| `AppRouter.tsx` | Ruta `/partes/carga-paquetes` |
| `PartesMenuSidebar.tsx` | `filterMenuForNonSupervisor`: ocultar `partes_carga_paquetes` y `/partes/carga-paquetes` |
| `partesMobilePolicy.ts` | Excluir `/partes/carga-paquetes` |
| `productI18n.ts` | `menu.partes_carga_paquetes` + `partes.paquete.*` (5 locales) |
| `partesMenuSidebar.test.ts` | Supervisor vs no supervisor ve menú paquetes |
| `partesMobilePolicy.test.ts` | Denegación ruta paquetes |
| Popup SC | Montar `SmartCapturePanel` solo con modal abierto; hint `partes.paquete.smartCapture.hint`; credenciales LLM patrón TR-010 |

**Reutilizar:** helpers duración `partesTareaDuration`, `ProcessDataGrid` layouts (`partes.carga.paquetes`), catálogo clientes TR-003, patrón fecha futura confirm de carga diaria.

### Tests

| Capa | Casos (TR §8 / AC) |
|------|---------------------|
| Feature `ApiV1PartesPaquetesTest` | 403 asistente/cliente; list sin fechas; CRUD happy path; `notPaquete` 422; 409 version; fecha futura; store ignora `esTarea`; update `usuarioId` inmutable |
| Feature informe | Tras alta paquete, `GET informes/paquete-horas` refleja minutos (patrón test existente) |
| Feature SC | Turn `processKind=paquete`; 403 no supervisor; ambigüedad cliente; 4201 sin credencial; regresión turno tarea sin `processKind` |
| Vitest | Filtros mes; form errors; SC apply + save mock API |
| E2E | Supervisor: menú → alta mínima → fila en grilla (`partesCargaPaquetes*`) |

### Documentación

- `docs/99-manual-usuario/Partes-Atencion.md`: párrafo ABM paquetes + SC paquete ≠ chat avatar
- Regenerar OpenAPI (`php artisan l5-swagger:generate` o script repo)
- TR-013 historial + enlace a este D1

### DevOps

- **Sin** migrate Must
- Deploy: ejecutar scripts SP nuevos en SQL Server (install/update pipeline)
- Post-deploy smoke: supervisor CRUD + informe paquete-horas + SC con BYOK

## Orden de trabajo

Alineado a TR-013 §7:

1. **T1 — DB / Operations:** Implementar `PartesPaqueteOperations` (list/get/upsert/delete) + scripts T-SQL espejo; tests unitarios mínimos de reglas (tramo, `es_tarea`, tipo default).
2. **T2 — BE API:** Middleware `partes.supervisor`; repository + controller; rutas; `SpCaller` branch; manejo excepciones envelope.
3. **T4 — Seed + i18n menú:** `PqMenuSeeder` 30300; `productI18n` claves menú (5 locales).
4. **T5 — FE core:** API client + `CargaPaquetesPage` (filtros, grid, modal, validación, fecha futura, delete confirm).
5. **T6 — FE SC:** Draft context paquete + panel en Popup + apply/save adapters.
6. **T3 — BE SC:** Rama `processKind=paquete` en turn service + Feature tests SC (después de FE draft estable para contrato).
7. **T7 — Policy:** Menú supervisor + mobile exclusion + tests sidebar/policy.
8. **T8 — Tests:** Feature suite paquetes + E2E humo; ajuste list tareas si AC-14.
9. **T9 — Docs:** OpenAPI + manual.

**Paralelo posible:** T4 puede ir con T2; T6 puede esqueletarse tras T5 con SC deshabilitado hasta T3.

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Drift Operations vs scripts T-SQL | Misma firma parámetros; checklist deploy SP |
| SC regresión TR-010 | Tests turno sin `processKind` + con `paquete`; no tocar path turno |
| List tareas mezcla paquetes | Test AC-14; filtro `es_tarea=1` en list tarea si aplica |
| Tipo default ausente en lab | `PqPartesTiposTareaSeeder` + test 422 `tipoDefaultFaltante` |
| `rowVersion` encoding | Reutilizar `PartesTareaOperations::encodeRowVersion` |
| Permisos menú 30300 vs solo FE filter | MVP: filtro supervisor FE + API 403; matriz rol TR-002 opcional en seed permisos |
| Gateway agente sin SP desplegado | Operations cubren MONO/sqlite; documentar SP en release notes |

## Tests a ejecutar

```text
cd backend && php artisan test --filter ApiV1PartesPaquetes
cd backend && php artisan test --filter PartesTareaSmartCapture
cd frontend && npm run test -- --run src/features/partes/carga
cd frontend && npm run test:e2e -- tests/e2e/... (spec paquetes cuando exista)
```

Manual: supervisor alta paquete → informe Paquete de horas; SC ambigüedad cliente + fecha futura.

## Dudas / bloqueos

| # | Tema | Decisión D1 |
|---|------|-------------|
| 1 | ¿Repository separado o extender `PartesTareaRepository`? | **`PartesPaqueteRepository`** delgado (claridad contrato API distinta) |
| 2 | ¿Columnas grid list (nombre asistente/registrado)? | Alinear DTO list a columnas HU; reutilizar joins patrón tarea list filtrado `es_tarea=0` |
| 3 | ¿Permiso ABM menú 30300 para rol supervisor no acceso_total? | Igual masivo: **ocultar menú** si `!esSupervisor`; permisos rol estándar vía seed si ya hay patrón TR-005 |
| 4 | Scripts SQL carpeta vacía hoy | Crear `backend/scripts/sql/partes/` (TR RN-TR-12); no bloquea D en sqlite |

Sin bloqueos humanos.

## Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR-013: **Sí**
- API `/partes/paquetes`, SP familia paquete, menú 30300, SC `processKind=paquete`: **Sí**
- No mobile, no Excel Must, no endpoints paquete en `/tareas`: **Sí**

## Artefacto

Plan guardado en: `docs/04-tareas/100-SistemaPartes/d1/D1-TR-013-carga-paquetes-horas.md`

---

**Listo para Parte D** cuando autorices: «Ejecutá la TR-013» / «Implementá TR-013».
