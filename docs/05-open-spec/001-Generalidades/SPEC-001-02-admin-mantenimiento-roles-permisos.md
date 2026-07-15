# SPEC-001-02-admin — Mantenimiento de roles y permisos (UI)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **SPEC padre** | [SPEC-001-02-acceso-y-seguridad](SPEC-001-02-acceso-y-seguridad.md) |
| **Contexto fuente** | [`mantenimiento-roles-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/mantenimiento-roles-permisos.md) |
| **Referencia implementación** | PaqSuite-IA-Tango — HU/TR-012, HU/TR-013, HU/TR-014, TR-013 update 03 |
| **Estado** | **D1 + F cerrado** (2026-06-19) — [F-GEN-02-admin-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-02-admin-cierre-formal.md) |
| **Revisión A1** | Apto con observaciones (2026-06-18) |

## Objetivo

Especificar el **mantenimiento administrativo** de roles, asignaciones usuario–rol y atributos granulares en **MONO**, adaptado a **SistemaPartes**: sin dimensión empresa en UI, sin ABM de usuarios (identidades desde ERP), con asignación **individual** y **masiva** (por usuario / por rol).

Complementa el MVP de [SPEC-001-02](SPEC-001-02-acceso-y-seguridad.md), que cubre login, seed y autorización **sin** ABM de seguridad en portal.

## Estado de ejecución

**Epic pendiente de programación (2026-06-19)** — fuera del MVP portal hasta activar flag; ver [F-GEN-02-admin-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-02-admin-cierre-formal.md).

## Fuentes (contexto MONO)

- [`mantenimiento-roles-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/mantenimiento-roles-permisos.md) — procesos y reglas operativas
- [`seguridad-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/seguridad-permisos.md) — modelo de tablas
- [`administracion-seguridad.md`](../../00-contexto/_mono/02-acceso-y-seguridad/administracion-seguridad.md) — visión ABM
- [`menu-y-autorizacion.md`](../../00-contexto/_mono/02-acceso-y-seguridad/menu-y-autorizacion.md) — efecto en menú
- [`patrones-abm.md`](../../00-contexto/_mono/03-ui-transversal/patrones-abm.md) — patrón UI

## Decisiones humanas (cerradas en revisión A1)

| Tema | Decisión |
|------|----------|
| Dimensión empresa | **No visible** en UI MONO; `id_empresa` constante en backend si el esquema legado lo exige |
| ABM usuarios | **Fuera de alcance SistemaPartes**; usuarios como catálogo read-only desde ERP / sync |
| Asignación individual | Una fila `Pq_Permiso` por par **usuario + rol** |
| Asignación masiva | Dos modos: **by_user** (1 usuario × N roles) y **by_role** (1 rol × N usuarios); **sin** modo by_company |
| Duplicados en batch | **Omitir** combinaciones existentes; resumen `{ creados, omitidos }` en UI |
| Multi-rol por usuario | **Permitido** en mantenimiento; autorización = **unión** de roles |
| Autorización del ABM | `AccesoTotal` en algún rol del usuario **o** atributos ABM en opción de menú del proceso |
| i18n | Claves `admin.permisos.*`, `admin.roles.*` alineadas a Tango; ver § i18n en contexto |
| Controles UI | DevExtreme obligatorio; `data-testid` según contexto |

## Alcance

### In scope

1. **ABM roles** (`Pq_Rol`): listado, alta, edición, baja condicionada; indicador `AccesoTotal`.
2. **ABM permisos** (`Pq_Permiso`):
   - individual: usuario + rol;
   - masivo por usuario;
   - masivo por rol;
   - listado con filtros por usuario y rol.
3. **ABM atributos de rol** (`PQ_RolAtributo`) cuando `AccesoTotal = false`.
4. **API admin** REST bajo `/api/v1/admin/` (permisos CRUD + batch; roles; atributos).
5. **Opciones de menú** en `pq_menus` para exponer procesos al sidebar dinámico.
6. **i18n** es/en/pt/fr/it; **E2E** mínimo validaciones bulk (referencia Tango `permisos-admin-bulk.spec.ts`).

### Fuera de alcance

- ABM de usuarios (`users`) en SistemaPartes.
- ABM de empresas y selector de tenant.
- Modo masivo **por empresa** (Tango TR-013 update 03).
- Edición / eliminación masiva de permisos.
- 2FA, políticas avanzadas de contraseña (ya en SPEC-001-02 base).
- Reemplazo del seed MVP (`paqsuite:seed-seguridad-mvp`); convive con mantenimiento UI.

## Modelo de datos (resumen)

| Tabla | Rol en este SPEC |
|-------|------------------|
| `Pq_Rol` | Definición de perfiles |
| `Pq_Permiso` | Asignación usuario ↔ rol (N filas por usuario permitidas) |
| `PQ_RolAtributo` | Permisos A/B/M/R por opción de menú |
| `users` | Solo lectura / lookup (SistemaPartes) |
| `pq_menus` | Catálogo de opciones para atributos y rutas admin |

Unicidad `Pq_Permiso` en MONO: `(id_rol, id_usuario)` (con `id_empresa` fijo si aplica esquema legado).

## Flujos funcionales

### F1 — Alta individual permiso

1. Administrador autorizado abre **Permisos**.
2. **Asignar permiso** → selecciona usuario y rol → guardar.
3. Sistema valida unicidad y existencia de referencias.
4. Listado se actualiza.

### F2 — Asignación masiva por usuario

1. **Por usuario** → ancla usuario (SelectBox).
2. Grilla multi de **roles** → tildar destinos.
3. Confirmación con cantidad estimada.
4. `POST .../permisos/batch` mode `by_user` → resumen creados/omitidos.

### F3 — Asignación masiva por rol

1. **Por rol** → ancla rol.
2. Grilla multi de **usuarios** (catálogo ERP/sync).
3. Confirmación y batch mode `by_role`.

### F4 — Mantenimiento rol y atributos

1. ABM roles según `patrones-abm.md`.
2. Si `AccesoTotal = false` → pantalla **Atributos** con árbol/grilla `pq_menus`.

## Contratos de API (orientativos)

| Método | Ruta | Notas |
|--------|------|-------|
| GET | `/api/v1/admin/permisos` | Filtros `usuario_id`, `rol_id` |
| POST | `/api/v1/admin/permisos` | Body `{ id_usuario, id_rol }` |
| PUT | `/api/v1/admin/permisos/{id}` | Cambio de rol |
| DELETE | `/api/v1/admin/permisos/{id}` | Baja asignación |
| POST | `/api/v1/admin/permisos/batch` | `{ mode: 'by_user' \| 'by_role', anchorId, rolIds[] \| usuarioIds[] }` |
| GET/POST/PUT/DELETE | `/api/v1/admin/roles` | CRUD roles |
| GET/PUT | `/api/v1/admin/roles/{id}/atributos` | Matriz atributos (contrato a detallar en TR) |
| GET | `/api/v1/admin/usuarios` | **Solo listado** para lookups (SistemaPartes) |

Envelope estándar MONO; errores **401**, **403**, **422** según [`envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

## Criterios de aceptación medibles (SPEC)

- **CA-01:** Usuario autorizado puede listar, crear, editar y eliminar asignaciones usuario–rol individuales.
- **CA-02:** Flujo **por usuario** crea N filas `(usuario fijo, rol)` sin duplicar existentes.
- **CA-03:** Flujo **por rol** crea N filas `(rol fijo, usuario)` sin duplicar existentes.
- **CA-04:** Validación UI: sin ancla → `admin.permisos.bulk.validationNoAnchor`; sin selección → `admin.permisos.bulk.validationSinCombinaciones`; no invoca API.
- **CA-05:** Usuario sin autorización recibe 403 en API y no ve acciones de mantenimiento.
- **CA-06:** ABM roles respeta unicidad de nombre y política de baja si rol en uso.
- **CA-07:** Atributos de rol persisten A/B/M/R por opción de menú; rol con `AccesoTotal` no exige atributos.
- **CA-08:** Tras asignación, menú del usuario afectado refleja unión de roles (API menú; no hardcode frontend).
- **CA-09:** Claves i18n documentadas en contexto presentes en los **cinco** locales.
- **CA-10:** SistemaPartes: no existe flujo de alta/edición de `users` en módulo seguridad.

## Trazabilidad HU (Parte B — B1 cerrada 2026-06-18)

| HU | Archivo | Tema | B1 |
|----|---------|------|-----|
| HU-GEN-02-admin-roles | [HU-GEN-02-admin-roles.md](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-roles.md) | ABM `Pq_Rol` | Lista para TR |
| HU-GEN-02-admin-rol-atributos | [HU-GEN-02-admin-rol-atributos.md](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-rol-atributos.md) | `PQ_RolAtributo` | Lista para TR |
| HU-GEN-02-admin-permisos | [HU-GEN-02-admin-permisos.md](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-permisos.md) | ABM individual `Pq_Permiso` | Lista para TR |
| HU-GEN-02-admin-permisos-bulk | [HU-GEN-02-admin-permisos-bulk.md](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-permisos-bulk.md) | Batch by_user / by_role | Lista para TR |

Índice: [`docs/03-historias-usuario/001-Generalidades/README.md`](../../03-historias-usuario/001-Generalidades/README.md).

## Trazabilidad TR (Parte C — C1 cerrada 2026-06-19)

| TR | HU | Orden D1 |
|----|-----|----------|
| [TR-GEN-02-admin-roles](../../04-tareas/001-Generalidades/TR-GEN-02-admin-roles.md) | HU-GEN-02-admin-roles | 1 |
| [TR-GEN-02-admin-rol-atributos](../../04-tareas/001-Generalidades/TR-GEN-02-admin-rol-atributos.md) | HU-GEN-02-admin-rol-atributos | 2 |
| [TR-GEN-02-admin-permisos](../../04-tareas/001-Generalidades/TR-GEN-02-admin-permisos.md) | HU-GEN-02-admin-permisos | 3 |
| [TR-GEN-02-admin-permisos-bulk](../../04-tareas/001-Generalidades/TR-GEN-02-admin-permisos-bulk.md) | HU-GEN-02-admin-permisos-bulk | 4 |

Acta C1: [F-GEN-02-admin-cierre-c1.md](../../04-tareas/001-Generalidades/F-GEN-02-admin-cierre-c1.md).

## Relación con MVP existente

| Componente MVP | Este SPEC |
|----------------|-----------|
| `paqsuite:seed-seguridad-mvp` | Sigue siendo bootstrap inicial |
| SPEC-001-02 «sin ABM UI» | Sigue vigente para **release MVP**; este slice es **post-MVP** |
| `AuthorizedMenuBuilder` / guards | Reutilizar; extender matriz permisos admin |

---

## Revisión A1 — cierre (2026-06-18)

> **Paso A1:** revisión de ambigüedades en SPEC (checklist, AMB-*, veredicto). Autoriza Parte B (HU) si no hay bloqueantes.

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Puede pasar a Parte D sin B/C** | **No** |
| **Bloqueantes documentales** | Ninguno |

### Checklist A1 (resumen)

| Área | Estado | Notas |
|------|--------|-------|
| Trazabilidad contexto | OK | Derivado de `mantenimiento-roles-permisos.md` + docs `_mono/02-acceso-y-seguridad/` |
| Alcance / fuera de alcance | OK | MONO sin empresa UI; sin ABM usuarios SistemaPartes; sin by_company |
| Coherencia SPEC-001-02 padre | OK | Post-MVP explícito; no contradice seed ni login MVP |
| Modelo de datos | OK | Multi-rol; unicidad usuario+rol; `id_empresa` opaco |
| Flujos e2e | OK | F1–F4 documentados; alineados a Tango TR-013 / update 03 |
| Reglas batch | OK | Omitir duplicados; validación UI previa |
| Autorización ABM | OK | AccesoTotal o atributos menú; misma política individual y batch |
| UI / i18n | OK | Claves alineadas a Tango; DevExtreme; tabla `data-testid` en contexto |
| APIs | Obs. | Contratos orientativos; OpenAPI en TR (Parte C) |
| SistemaPartes / ERP | OK | Usuarios lookup only; sync ERP prerequisito |
| Criterios aceptación | OK | CA-01 … CA-10 medibles |

### Ambigüedades críticas

Ninguna bloqueante para **Parte B**.

### Ambigüedades menores (resolver en TR)

| ID | Tema | Propuesta |
|----|------|-----------|
| AMB-M-02-ADM-01 | Valor fijo `id_empresa` en MONO | Constante en config/servicio; no exponer en API |
| AMB-M-02-ADM-02 | Refresh sesión tras cambio permisos | Documentar en TR: re-login o invalidar token según implementación |
| AMB-M-02-ADM-03 | Contrato exacto atributos rol | REST dedicado vs payload anidado en PUT rol — definir en TR-014 adaptado |
| AMB-M-02-ADM-04 | Middleware admin vs atributos granulares | Alinear `RequireAdmin` con regla AccesoTotal **o** atributos menú (obs. Tango TR-013 u03 §9) |
| AMB-M-02-ADM-05 | Procedimientos menú admin | Seed `pq_menus` para rutas `/admin/roles`, `/admin/permisos`, atributos — idempotente en TR |

### Supuestos detectados

- Esquema `Pq_Permiso` existente soporta múltiples filas por usuario (índice único rol+empresa+usuario con empresa constante).
- Catálogo usuarios para lookup proviene de `users` poblado por ERP/sync, no del ABM portal.
- Implementación UI puede portar componentes Tango omitiendo empresa y ABM usuarios.

### Recomendaciones de ajuste (aplicadas en A1)

- [x] i18n unificado con Tango (`validationSinCombinaciones`; sin `validationNoSeleccion` ni `byCompany` en MONO).
- [x] Dos modos bulk documentados (no tres).
- [x] SPEC hija de 001-02 con estado post-MVP explícito.
- [x] Al generar HU: referenciar matriz permisos admin en `matriz-permisos-mvp.md` o extensión dedicada (HU B1 2026-06-18).
- [x] Al generar TR: portar tests Tango `permisos-admin.spec.ts`, `permisos-admin-bulk.spec.ts` (TR C 2026-06-19).

### Veredicto

**Apto con observaciones** para cierre **A1**. **Autoriza Parte B** (generación de HU).








