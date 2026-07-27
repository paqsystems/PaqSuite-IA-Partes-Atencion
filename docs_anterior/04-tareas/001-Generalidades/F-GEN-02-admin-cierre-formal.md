# Cierre F Formal — Bloque GEN-02-admin (SPEC-001-02-admin)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


## Alcance del cierre

Cubre los cuatro slices planificados y verificados (D1 + verificación D + tests automatizados):

| TR | HU |
|----|-----|
| [TR-GEN-02-admin-roles](TR-GEN-02-admin-roles.md) | [HU-GEN-02-admin-roles](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-roles.md) |
| [TR-GEN-02-admin-rol-atributos](TR-GEN-02-admin-rol-atributos.md) | [HU-GEN-02-admin-rol-atributos](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-rol-atributos.md) |
| [TR-GEN-02-admin-permisos](TR-GEN-02-admin-permisos.md) | [HU-GEN-02-admin-permisos](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-permisos.md) |
| [TR-GEN-02-admin-permisos-bulk](TR-GEN-02-admin-permisos-bulk.md) | [HU-GEN-02-admin-permisos-bulk](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-permisos-bulk.md) |

**SPEC:** [SPEC-001-02-admin-mantenimiento-roles-permisos.md](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md)

**Revisión C1 previa:** [F-GEN-02-admin-cierre-c1](F-GEN-02-admin-cierre-c1.md)  
**Implementación D1:** [F-GEN-02-admin-cierre-d1](F-GEN-02-admin-cierre-d1.md)

## Resultado global

- **Aprobado con observaciones**

Epic **implementado en código** (D1 + D completos). **Fuera del release MVP portal** hasta activar `ADMIN_SECURITY_UI_ENABLED=true` y migración índice rol en tenant objetivo.

## Resumen por slice

| Slice | Resultado D | Observación principal |
|-------|-------------|------------------------|
| TR-GEN-02-admin-roles | Aprobado | T0 multi-rol + CRUD + flag + menú seed |
| TR-GEN-02-admin-rol-atributos | Aprobado | GET/PUT atributos; read-only acceso total |
| TR-GEN-02-admin-permisos | Aprobado | CRUD + lookup usuarios + filtros UI |
| TR-GEN-02-admin-permisos-bulk | Aprobado | Batch transaccional + validación cliente E2E |

## Verificación automatizada (2026-06-19)

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=AdminSecurityFeatureTest` | **9 passed** (91 assertions) |
| `npx playwright test tests/e2e/admin-security/` | **2 passed** |
| `npm run build` | OK |

## Activación en entorno

1. `.env`: `ADMIN_SECURITY_UI_ENABLED=true`
2. Migración: `2026_06_19_120000_add_unique_index_pq_rol_nombre_rol.php`
3. Seeds: `paqsuite:seed-menus-mvp` + `paqsuite:seed-seguridad-mvp`
4. Smoke: login `supervisor.mvp` → menú Seguridad → Roles / Permisos

## Matriz permisos

Actualizada en [`matriz-permisos-mvp.md`](matriz-permisos-mvp.md) § Admin seguridad (aplicado D1).

## Observaciones (no bloqueantes)

| ID | Tema | Notas |
|----|------|-------|
| OBS-01 | OpenAPI `/admin/*` | Contratos en TR; anexo OpenAPI global pendiente |
| OBS-02 | AC-05 atributos re-login menú | Coherencia menú post-atributos: verificación manual recomendada |
| OBS-03 | E2E flujo feliz batch completo | Smoke validación negativa; confirmación + API mock en CI |
| OBS-04 | TR-update multi-rol | Anotar supersession D1-1 en `TR-GEN-02-autorizacion-menu-api` §3.4 |

## Fuera de alcance confirmado

- MVP portal release actual (flag default `false`).
- ABM usuarios en portal.
- Dimensión empresa en UI permisos.
- Bootstrap destructivo tablas ERP.

## Veredicto

**F formal cerrado** — SPEC-001-02-admin listo como epic implementado. **Paso E (2026-06-19):** [F-GEN-02-admin-cierre-e.md](F-GEN-02-admin-cierre-e.md). Pendiente activación productiva.






