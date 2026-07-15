# Parte E — SPEC-001-02-admin (2026-06-19) — Ejecución de tests

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


> **Cierre paso E:** [F-GEN-02-admin-cierre-e.md](F-GEN-02-admin-cierre-e.md)  
> **Precondición:** D1 + D cerrados — [F-GEN-02-admin-cierre-formal.md](F-GEN-02-admin-cierre-formal.md)

## Alcance

Validación automatizada y smoke de entorno dev del epic **mantenimiento roles y permisos**:

| TR | Foco tests |
|----|------------|
| TR-GEN-02-admin-roles | CRUD roles, flag off 404, T0 multi-rol, E2E grilla |
| TR-GEN-02-admin-rol-atributos | GET/PUT atributos, read-only acceso total |
| TR-GEN-02-admin-permisos | CRUD permisos, lookup usuarios, filtros UI |
| TR-GEN-02-admin-permisos-bulk | Batch creados/omitidos, validación cliente E2E |

**Fecha ejecución:** 2026-06-19  
**Entorno:** `Ankas_del_sur`, tenant `desarrollo`, `ADMIN_SECURITY_UI_ENABLED=true`

---

## Entorno de desarrollo iniciado

| Servicio | URL | Estado |
|----------|-----|--------|
| Backend Laravel | http://127.0.0.1:8000 | OK |
| Frontend Vite | http://127.0.0.1:5173 | OK |

**Seeds (no destructivos):**

```powershell
php artisan paqsuite:seed-menus-mvp
php artisan paqsuite:seed-seguridad-mvp
```

**Config local:** `backend/.env` → `ADMIN_SECURITY_UI_ENABLED=true`  
**Login smoke:** `supervisor.mvp` / `SEED_MVP_PASSWORD` (`ChangeMeInLocalEnv`)

---

## Backend — PHPUnit

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=AdminSecurityFeatureTest` | **9 passed** (91 assertions) |

### Casos cubiertos

| Test | Slice |
|------|-------|
| `testAdminRoutesReturn404WhenFlagDisabled` | Flag infra |
| `testSupervisorCanListAndCreateRoles` | Roles CRUD |
| `testCannotDeleteRoleInUse` | Roles baja condicionada |
| `testDuplicateRoleNameReturns422` | Roles unicidad |
| `testRoleAttributesReadOnlyForAccesoTotal` | Atributos read-only |
| `testRoleAttributesCanBeSyncedForNonAccesoTotalRole` | Atributos sync |
| `testPermisosCrudAndLookup` | Permisos + lookup |
| `testPermisoBatchCreatesAndSkipsDuplicates` | Bulk |
| `testVendedorAcotadoCannotAccessAdminRoles` | Autorización 403 |

---

## Frontend — Vitest

| Comando | Resultado |
|---------|-----------|
| `npm run test` | **156 passed** (51 archivos) |

Sin tests unitarios dedicados al slice admin (cobertura vía E2E + build + integración API).

---

## Frontend — Playwright E2E

| Comando | Resultado |
|---------|-----------|
| `npx playwright test tests/e2e/admin-security/` | **2 passed** |

| Spec | Escenario |
|------|-----------|
| `roles-admin.spec.ts` | Grilla roles + flag habilitado |
| `permisos-admin-bulk.spec.ts` | Validación bulk sin ancla |

---

## Build producción

| Comando | Resultado |
|---------|-----------|
| `npm run build` | OK |

---

## Smoke API en servidor vivo

| Check | Resultado |
|-------|-----------|
| `GET /api/v1/health` | `error: 0` |
| `POST /api/v1/auth/login` (`supervisor.mvp`) | OK |
| `GET /api/v1/config/public` (autenticado) | `securityAdminEnabled: true` |
| `GET /api/v1/admin/roles` | 4 roles MVP + roles seed |

---

## Fuera de alcance paso E

- E2E flujo feliz batch completo con backend real (mock en CI).
- AC-05 atributos: re-login y coherencia menú (QA manual).
- OpenAPI global `/admin/*`.

---

## Veredicto Parte E

**Resultado:** **Aprobado**

Epic admin apto para uso en dev con flag activo. Pendiente QA manual extendida antes de activar en producción.






