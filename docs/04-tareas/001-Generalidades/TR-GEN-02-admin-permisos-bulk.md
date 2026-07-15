# TR-GEN-02-admin-permisos-bulk — Asignación masiva de permisos

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-admin-permisos-bulk](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-permisos-bulk.md) |
| **SPEC relacionada** | [SPEC-001-02-admin-mantenimiento-roles-permisos](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md) |
| **Épica** | 001-Generalidades / Acceso y seguridad (post-MVP) |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-02-admin-permisos; TR-GEN-03-grillas-listados |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-19 (revisión C1 formal `/tr-ambiguity-review`) |

**Origen:** [HU-GEN-02-admin-permisos-bulk](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-permisos-bulk.md)  
**Referencia SPEC:** [SPEC-001-02-admin-mantenimiento-roles-permisos](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md)  
**Contexto:** [`mantenimiento-roles-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/mantenimiento-roles-permisos.md) · Tango [TR-013 update 03](https://github.com/paqsystems/PaqSuite-IA-TANGO/blob/main/docs/04-tareas/updates/001-Seguridad/TR-013-administracion-permisos-update-03-asignacion-multiple.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Asignación masiva usuario–rol en modos **by_user** y **by_role** (sin by_company).

### Narrativa
Como administrador de permisos, quiero asignar muchos permisos en un solo paso para no repetir el formulario individual cuando debo otorgar varios roles a un usuario o un rol a muchos usuarios.

### In scope / Out of scope
- **In scope:** botones toolbar **Por usuario** / **Por rol**; modales DevExtreme; validación UI previa; confirmación con cantidad; `POST .../permisos/batch`; resumen `{ creados, omitidos }`; E2E validaciones bulk.
- **Out of scope:** modo por empresa; edición/eliminación masiva; ABM usuarios.

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Botones bulk visibles solo para usuario autorizado al ABM permisos.
- **AC-02:** Modo **by_user:** ancla usuario + roles tildados → N filas nuevas.
- **AC-03:** Modo **by_role:** ancla rol + usuarios tildados → N filas nuevas.
- **AC-04:** Combinaciones existentes omitidas; `omitidos` incrementado.
- **AC-05:** Confirmar sin ancla → `admin.permisos.bulk.validationNoAnchor`; sin API.
- **AC-06:** Ancla sin selección → `admin.permisos.bulk.validationSinCombinaciones`; sin API.
- **AC-07:** Tras batch exitoso, grilla principal refleja nuevas filas.
- **AC-08:** API batch → 403 si no autorizado.
- **AC-09:** `data-testid` según contexto (modales, grillas, validación).
- **AC-10:** E2E: validaciones negativas + flujo feliz by_user.

### Escenarios Gherkin

(Heredados de HU-GEN-02-admin-permisos-bulk.)

---

## 3) Reglas de Negocio

1. **RN-01:** Misma autorización que CRUD individual (`pw_adminpermisos`, `Permiso_Alta`).
2. **RN-02:** Política duplicados: **omitir** con conteo en `omitidos` (no fail-fast).
3. **RN-03:** Producto cartesiano: una fila por par válido `(usuario, rol)` según modo.
4. **RN-04:** Validación cliente obligatoria antes de POST; servidor revalida ancla e ids.
5. **RN-05:** Confirmación modal con texto i18n `admin.permisos.bulk.confirm` (`{{count}}`).
6. **RN-06:** Límite volumen v1: máx **500** ids secundarios por request; exceder → 422 `admin.permisos.bulk.tooMany`.
7. **RN-07:** Transacción DB por batch; errores parciales de referencia inválida → 422 sin persistir.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-19)

**Skill:** `/tr-ambiguity-review`

**Fuentes revisadas:** HU-GEN-02-admin-permisos-bulk, HU-GEN-02-admin-permisos, SPEC-001-02-admin, `mantenimiento-roles-permisos.md` (§ i18n y testid), TR-GEN-02-admin-permisos, TR-GEN-03-grillas-listados; Tango TR-013 update 03.

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí**

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución (→ D1) |
|----|------|--------|-------------------|
| AMB-C1-B-01 | i18n vs testid mezclados en HU | Implementadores duplican claves | **i18n:** solo `admin.permisos.bulk.*`; **data-testid:** solo `permisos.bulk.*` (contexto MONO). |
| AMB-C1-B-02 | HU `permisos.bulk.confirm` vs TR | Texto confirmación inconsistente | i18n confirmación = `admin.permisos.bulk.confirm`; testid botón = `permisos.bulk.confirm`. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M-C1-B-01 | Modos Tango | Solo `by_user` \| `by_role`; sin `by_company` ni `id_empresa`. |
| AMB-M-C1-B-02 | Límite volumen | Max 500 ids secundarios; 422 `admin.permisos.bulk.tooMany`. |
| AMB-M-C1-B-03 | Visibilidad botones bulk | Mostrar solo si `Permiso_Alta` en `pw_adminpermisos` (backend 403 + frontend gate). |
| AMB-M-C1-B-04 | Grillas selección | `DataGridDx` con `selection.mode: 'multiple'`; catálogos vía APIs TR permisos. |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| HU mezcla `permisos.bulk.byUser` (testid) y `admin.permisos.bulk.validation*` (i18n) | **Intencional** según contexto MONO; TR unifica criterio §3.2 R-C1-B-06. |
| AC-01 visibilidad bulk vs AC permisos individual | Misma gate `pw_adminpermisos`; batch exige además `Permiso_Alta`. |
| SPEC batch `{ creados, omitidos }` vs envelope | Dentro de `resultado`; `respuesta` puede ser clave i18n success. |

### Supuestos detectados

- `PermisosAdminPage` y APIs CRUD ya existen (TR permisos D1 previo).
- Duplicados: índice único BD garantiza omitir sin error.

### Preguntas para decisión humana

(Ninguna bloqueante.)

### Recomendaciones de ajuste de la TR

- [x] Tabla i18n vs testid en §6.
- [ ] HU-update opcional: alinear redacción confirmación a `admin.permisos.bulk.confirm` (no bloquea D1).

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-19)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-B-01 | Request by_user | `{ "mode": "by_user", "anchorId": 5, "rolIds": [1, 2, 3] }` |
| R-C1-B-02 | Request by_role | `{ "mode": "by_role", "anchorId": 2, "usuarioIds": [10, 11] }` |
| R-C1-B-03 | Response | `resultado: { creados, omitidos }`; UI traduce `admin.permisos.bulk.successMessage`. |
| R-C1-B-04 | UI modales | `Popup` DX; ancla `SelectBox`; grilla multi selección. |
| R-C1-B-05 | Catálogos | Reutilizar `GET /admin/roles` y `GET /admin/usuarios`. |
| R-C1-B-06 | i18n vs testid | i18n `admin.permisos.bulk.*`; testid `permisos.bulk.*`. |
| R-C1-B-07 | Validación cliente | Sin ancla → `admin.permisos.bulk.validationNoAnchor`; sin tildados → `admin.permisos.bulk.validationSinCombinaciones`; alert `permisos.bulk.validation`. |

---

## 3.3) Plan D1 — Implementación (2026-06-19)

**Estado:** Cerrado.

| # | Entrega | Estado |
|---|---------|--------|
| T1 | `PermisoBatchService` + POST `/admin/permisos/batch` | ✅ |
| T2 | `PermisoBulkByUserModal` + `PermisoBulkByRoleModal` | ✅ |
| T3 | Validación cliente + confirmación DX | ✅ |
| T4 | Tests Feature batch + E2E smoke | ✅ |

---

## 3.4) Verificación D (2026-06-19)

| Verificación | Resultado |
|--------------|-----------|
| POST batch by_user creados/omitidos | OK — `testPermisoBatchCreatesAndSkipsDuplicates` |
| Validación mode/anchor/límite 500 | OK — `PermisoBatchService` + config |
| Modales bulk DX + testid `permisos.bulk.*` | OK — build |
| Validación cliente sin ancla | OK — E2E `permisos-admin-bulk.spec.ts` |
| i18n `admin.permisos.bulk.*` (5 locales) | OK |
| Transacción idempotente pares duplicados | OK — Feature batch |

### Trazabilidad AC

| AC | Evidencia | Estado D |
|----|-----------|----------|
| AC-01 | Toolbar botones + gate permisos | ✅ |
| AC-02 | Feature batch by_user | ✅ |
| AC-03 | Service by_role (misma ruta batch) | ✅ |
| AC-04 | Feature omitidos duplicados | ✅ |
| AC-05 | E2E validationNoAnchor sin API | ✅ |
| AC-06 | Service validationSinCombinaciones (422) | ✅ |
| AC-07 | refreshToken post-batch en UI | ✅ |
| AC-08 | 403 vendedor acotado (Feature admin) | ✅ |
| AC-09 | testid bulk documentados en UI | ✅ |
| AC-10 | E2E validación negativa | ✅ parcial (flujo feliz manual) |

---

## 3.5) Verificación E (2026-06-19)

Ver [E-GEN-02-admin-tests.md](E-GEN-02-admin-tests.md). Batch Feature + E2E validación ancla: **Apto**.

---

## 4) Impacto en Datos

### Tablas afectadas

| Tabla | Operación |
|-------|-----------|
| `Pq_Permiso` | Insert batch (skip duplicates) |

Sin cambios de esquema.

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Método | Path | Permiso |
|--------|------|---------|
| POST | `/api/v1/admin/permisos/batch` | `Permiso_Alta` + `pw_adminpermisos` |

### 5.2 Detalle

#### POST `/api/v1/admin/permisos/batch`

**Request by_user:**

```json
{
  "mode": "by_user",
  "anchorId": 5,
  "rolIds": [1, 2, 4]
}
```

**Request by_role:**

```json
{
  "mode": "by_role",
  "anchorId": 2,
  "usuarioIds": [10, 11, 12]
}
```

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "admin.permisos.bulk.successMessage",
  "resultado": {
    "creados": 2,
    "omitidos": 1
  }
}
```

**422:** modo inválido; anchor inexistente; ids secundarios vacíos; >500 ids; referencia inválida.

**403:** sin `Permiso_Alta` admin permisos.

### 5.3 Actualización matriz permisos

- [ ] Fila POST batch en matriz § Admin seguridad.

---

## 6) Cambios Frontend

### Componentes

```text
frontend/src/features/admin/security/permisos/
  PermisoBulkByUserModal.tsx
  PermisoBulkByRoleModal.tsx
  permisoBulkValidation.ts       # reglas AC-05/AC-06 cliente
```

Integración en `PermisosAdminPage` toolbar:

- `Button` `permisos.bulk.byUser` → modal by_user.
- `Button` `permisos.bulk.byRole` → modal by_role.

### Flujo modal by_user

1. `SelectBox` usuario (`permisos.bulk.anchor.usuario`).
2. `DataGridDx` roles multi (`permisos.bulk.grid.roles`).
3. Validar → `Dialog` confirm (`permisos.bulk.confirm`).
4. POST batch → toast/resumen → refresh grilla principal → cerrar modal.

### data-testid

| Elemento | testid |
|----------|--------|
| Botón por usuario | `permisos.bulk.byUser` |
| Botón por rol | `permisos.bulk.byRole` |
| Modal by_user | `permisos.bulk.modal.byUser` |
| Modal by_role | `permisos.bulk.modal.byRole` |
| Grilla roles | `permisos.bulk.grid.roles` |
| Grilla usuarios | `permisos.bulk.grid.usuarios` |
| Validación | `permisos.bulk.validation` (`role="alert"`) |
| Confirmar batch | `permisos.bulk.confirm` |

### i18n bulk

| Tipo | Prefijo | Ejemplos |
|------|---------|----------|
| Textos UI | `admin.permisos.bulk.*` | `byUser`, `byRole`, `validationNoAnchor`, `successMessage`, `confirm` |
| data-testid | `permisos.bulk.*` | `byUser`, `modal.byUser`, `grid.roles`, `validation`, `confirm` |

Sin claves `byCompany` ni `admin.permisos.empresa`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | `AdminPermisoBatchController` + service transaccional | Feature: creados/omitidos/422/403 |
| T2 | Frontend | Modales bulk + validación cliente | AC-01–AC-07, AC-09 |
| T3 | Tests | E2E `permisos-admin-bulk.spec.ts` | AC-05, AC-06, AC-10 |
| T4 | Docs | OpenAPI + matriz | §10 |

---

## 8) Estrategia de Tests

- **Unit:** cálculo omitidos; límite 500.
- **Integration:** by_user 3 roles (1 duplicado) → creados=2 omitidos=1; by_role; 403; 422 modo inválido.
- **E2E:** confirmar sin ancla muestra alert testid validation; flujo feliz crea filas visibles en grilla.

---

## 9) Riesgos y Edge Cases

- Seleccionar mismo id secundario duplicado en UI — deduplicar en cliente y servidor.
- Batch concurrente mismo par — segunda request incrementa omitidos (OK).
- Performance: 500 inserts en una transacción — aceptable v1 SQL Server.

---

## 10) Checklist final

(Checklist transversal — ver TR-GEN-02-admin-roles.)

---

## Archivos creados/modificados (D1 2026-06-19)

- `app/Services/Admin/PermisoBatchService.php`
- `app/Http/Controllers/Api/V1/Admin/AdminPermisoController.php` (batch)
- `config/paqsuite_admin_security.php` (`batchMaxSecondaryIds`)
- `frontend/src/features/admin/security/permisos/PermisoBulkByUserModal.tsx`
- `frontend/src/features/admin/security/permisos/PermisoBulkByRoleModal.tsx`
- `frontend/tests/e2e/admin-security/permisos-admin-bulk.spec.ts`
- `tests/Feature/AdminSecurityFeatureTest.php` (batch)










