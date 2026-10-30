# TR-adopcion-host — CC PQ #9 (30/10/2026) — Adopción release Framework seguridad MONO

| Campo | Valor |
|-------|--------|
| **ID** | TR-adopcion-host-cc-pq-9-seguridad-mono-multi-update |
| **Estado** | Pendiente |
| **Repo** | **PaqSuite-IA-Partes-Atencion** (host MONO) |
| **Framework** | [SPEC-001-06-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/05-open-spec/updates/001-Generalidades/SPEC-001-06-update-cc-pq-9-2026-10-30-seguridad-mono-multi.md) · [SPEC-001-34-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/05-open-spec/updates/001-Generalidades/SPEC-001-34-update-cc-pq-9-2026-10-30-install-single-tenancy.md) |
| **Origen** | `00-ControlCalidad-PQ` · CC **#9** · 30/10/2026 |
| **Última actualización** | 2026-10-30 |

## 1) Alcance

Adoptar en Partes el release de **`paqsuite/laravel-core`** y **`@paqsuite/react-core`** que cierre CA-FW-01…07 **sin** redefinir DDL de plataforma en el host. Solo pin, manifiesto host GEN-34, migraciones/SP **si** el SPEC Framework las exige para alinear columnas, y smoke Forge.

**Fuera de alcance:** lógica de negocio Partes; pantallas dominio; parches permanentes duplicando SP Framework.

## 2) Restricciones de datos (Must)

| Restricción | Verificación smoke |
|-------------|-------------------|
| PK/FK `id` sin renumerar | Conteo filas pre/post |
| `users.codigo` y passwords | Login usuario conocido |
| `nombre` / labels visibles | Copia 1:1 tras rename |
| Matriz permisos | Mismo menú efectivo |

Prohibido: `migrate:fresh`, truncates seguridad en preview/prod.

## 3) Tareas host (D)

| ID | Tarea |
|----|--------|
| T1 | Pin laravel-core + react-core según nota release Framework |
| T2 | Actualizar `RELEASE-PARTES.md` / manifiesto host: exclusiones `single` |
| T3 | Retirar SP/manifest duplicados cuando core unifique familia `pq_sp_admin_empresas_*` |
| T4 | Pipeline Forge: orden migrate → `deploy-sp` (host) → `installation:update` |
| T5 | Smoke CA-FW-05/07: login, menú, empresas SDK `tenancyMode=single` |

## 4) Criterios de aceptación

- [ ] **CA-H01** Deploy preview/prod completa sin error `IDEmpresa`.
- [ ] **CA-H02** Credenciales y permisos equivalentes al pre-deploy.
- [ ] **CA-H03** Documentación pin en commit chore correspondiente.

## 5) Dependencia

Bloqueado en implementación Framework ([TR-GEN-06-seguridad-mono-multi-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/04-tareas/updates/001-Generalidades/TR-GEN-06-seguridad-mono-multi-update.md), [TR-GEN-34-install-single-tenancy-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/04-tareas/updates/001-Generalidades/TR-GEN-34-install-single-tenancy-update.md)) hasta release publicado en Cloudsmith.
