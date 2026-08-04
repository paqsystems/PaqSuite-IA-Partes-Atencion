# F-GEN-02-admin — Cierre revisión C1 (epic Admin roles y permisos)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-001-02-admin-mantenimiento-roles-permisos](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md) |
| **Fecha** | 2026-06-19 |
| **Alcance** | Revisión C1 formal (`/tr-ambiguity-review`) de las 4 TR-GEN-02-admin-* contra HU, SPEC, normas transversales y **código actual** |
| **Veredicto** | **Apto con observaciones** — **autorizado D1** en orden 1→4 |

## Resultado por TR

| TR | Estado C1 | Bloqueantes D1 |
|----|-----------|----------------|
| [TR-GEN-02-admin-roles](TR-GEN-02-admin-roles.md) | Apto con observaciones | Ninguno; **T0 multi-rol obligatorio** en este slice |
| [TR-GEN-02-admin-rol-atributos](TR-GEN-02-admin-rol-atributos.md) | Apto con observaciones | Depende T0 (AC-05 menú) |
| [TR-GEN-02-admin-permisos](TR-GEN-02-admin-permisos.md) | Apto con observaciones | Depende T0 para escenarios multi-rol |
| [TR-GEN-02-admin-permisos-bulk](TR-GEN-02-admin-permisos-bulk.md) | Apto con observaciones | Depende TR permisos D1 previo en cadena |

## Checklist C1 transversal

| Área | Estado | Notas |
|------|--------|-------|
| Trazabilidad TR ⊆ HU ⊆ SPEC | OK | Sin scope creep (sin ABM users, sin by_company) |
| Envelope MONO en ejemplos API | OK | `resultado` objeto; corregido lookup paginado permisos |
| Matriz + OpenAPI planificados | OK | § matriz prevista; OpenAPI en D1 mismo PR |
| 401/403 por endpoint | OK | Documentados; flag off → 404 admin |
| Tenancy `X-Paq-Cliente` | OK | Bearer + header en tablas §5 |
| Seed / perfiles QA | OK | `supervisor.mvp`, `vendedor.acotado.mvp`, multi-rol en Feature T0 |
| Tests AC ↔ §8 TR | OK | E2E Tango adaptados; union 2 roles en T0 |
| Coherencia TR ↔ HU | Obs. | HU bulk mezcla prefijos i18n/testid — cerrado en TR |
| Coherencia TR ↔ código | Obs. | `->first()` en 3 servicios; `User::permiso()` HasOne — T0 |

## Hallazgos código vs TR (incorporados en C1)

| Hallazgo | Resolución C1 |
|----------|----------------|
| `AuthorizedMenuBuilder` / `VisibilityPermissionGuard` / `SessionContextBuilder` usan un solo `PqPermiso` | T0 `UserRoleUnionService` + OR roles/atributos |
| `SessionContextBuilder.security.roles` devuelve un rol | Array con todos los roles; `accesoTotal` OR; perfil comercial ERP sin cambio |
| `User` sin `permisos()` HasMany | Añadir relación; migrar auth paths |
| `PublicConfigController` sin flag admin | Añadir `securityAdminEnabled` (paridad `excelImportEnabled`) |
| `Pq_Rol` sin índice único nombre | Migración `uq_pq_rol_nombre_rol` |
| Lookup TR decía `name`; BD `name_user` | API `nameUser` / `usuarioNombre` |
| TR-GEN-02-autorizacion-menu-api D1-1 «N/A multi-rol» | Superseded por T0 admin — TR-update §3.4 |

## Decisiones transversales cerradas en C1

| Tema | Decisión |
|------|----------|
| T0 multi-rol | Obligatorio en TR-roles antes de QA epic |
| Perfil comercial login | No deriva de multi-rol; sigue `CommercialProfileResolver` / ERP |
| Admin gate | `AdminSecurityAccessService`: AccesoTotal **OR** atributos A/B/M/R del procedimiento admin |
| Flag epic | `securityAdminEnabled` default **false**; API admin 404 si off |
| Atributos API | REST dedicado `GET/PUT .../roles/{id}/atributos` |
| Batch duplicados | Omitir; `{ creados, omitidos }`; max 500 ids |
| i18n bulk | Textos `admin.permisos.bulk.*`; testid `permisos.bulk.*` |
| Refresh sesión v1 | Re-login recomendado; sin revocación masiva tokens |
| Alta ABM UI | Botón **+** DevExtreme (TR-GEN-03) en roles y permisos individual |

## Orden D1 recomendado

```text
1. TR-GEN-02-admin-roles           (T0 + CRUD + seed menú + flag)
2. TR-GEN-02-admin-rol-atributos   (matriz PQ_RolAtributo)
3. TR-GEN-02-admin-permisos        (CRUD + lookup usuarios)
4. TR-GEN-02-admin-permisos-bulk   (POST batch + modales)
```

## Matriz permisos — filas previstas (aplicar en D1)

| Método | Path | Permiso |
|--------|------|---------|
| GET | `/api/v1/admin/roles` | `Permiso_Repo` + `pw_adminroles` |
| POST | `/api/v1/admin/roles` | `Permiso_Alta` + `pw_adminroles` |
| PUT | `/api/v1/admin/roles/{id}` | `Permiso_Modi` + `pw_adminroles` |
| DELETE | `/api/v1/admin/roles/{id}` | `Permiso_Baja` + `pw_adminroles` |
| GET | `/api/v1/admin/roles/{id}/atributos` | `Permiso_Repo` + `pw_adminroles` |
| PUT | `/api/v1/admin/roles/{id}/atributos` | `Permiso_Modi` + `pw_adminroles` |
| GET | `/api/v1/admin/permisos` | `Permiso_Repo` + `pw_adminpermisos` |
| POST | `/api/v1/admin/permisos` | `Permiso_Alta` + `pw_adminpermisos` |
| PUT | `/api/v1/admin/permisos/{id}` | `Permiso_Modi` + `pw_adminpermisos` |
| DELETE | `/api/v1/admin/permisos/{id}` | `Permiso_Baja` + `pw_adminpermisos` |
| POST | `/api/v1/admin/permisos/batch` | `Permiso_Alta` + `pw_adminpermisos` |
| GET | `/api/v1/admin/usuarios` | `Permiso_Repo` + `pw_adminpermisos` |

**Estado:** documentado en [matriz-permisos-mvp.md](matriz-permisos-mvp.md) § Admin seguridad; verificar OpenAPI en D1.

## Observaciones no bloqueantes (D1)

1. TR-update `TR-GEN-02-autorizacion-menu-api` §3.4 (nota supersession D1-1) — incluir en PR de T0.
2. HU-update opcional bulk: unificar redacción confirmación a `admin.permisos.bulk.confirm`.
3. Tests Feature admin: skip sin SQL Server tenant (patrón GEN-07) si aplica CI.
4. No ejecutar bootstrap destructivo en `Ankas_del_sur` para probar admin.
5. Manual usuario seguridad — post-D1 opcional.

## Fuera de alcance confirmado

- MVP portal release actual.
- ABM usuarios en portal.
- Modo masivo por empresa (Tango MULTI).
- Edición / eliminación masiva de permisos.

## Smoke test post-D1 (mínimo)

1. `ADMIN_SECURITY_UI_ENABLED=true` + seed menú admin.
2. Login `supervisor.mvp` → Seguridad → roles y permisos.
3. T0: usuario con 2 roles → menú = unión.
4. Rol acotado → atributos → guardar → re-login usuario afectado.
5. Permiso individual + bulk by_user; verificar `omitidos`.
6. `vendedor.acotado.mvp` sin atributos admin → 403 API.

## Próximo paso

**Parte D1** con `/ai-planning-mode` o implementación directa en orden 1→4.

**Referencias Tango (E2E):** `roles-admin.spec.ts`, `permisos-admin.spec.ts`, `permisos-admin-bulk.spec.ts` — omitir empresa y ABM users.






