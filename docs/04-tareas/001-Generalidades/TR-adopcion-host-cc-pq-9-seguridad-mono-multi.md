# TR-adopcion-host — CC PQ #9 — Seguridad MONO (omit dictionary Tango)

| Campo | Valor |
|-------|--------|
| **ID** | TR-adopcion-host-cc-pq-9-seguridad-mono-multi |
| **Estado** | Finalizado |
| **Repo** | **PaqSuite-IA-Partes-Atencion** (host MONO) |
| **Framework** | SPEC-001-06-update CC #9 · SPEC-001-34-update CC #9 (contrato canónico MONO/MULTI — permanece en Framework) |
| **Origen** | `00-ControlCalidad-PQ` · CC **#9** · 30/10/2026 |
| **Última actualización** | 2026-11-06 |

## 1) Alcance (host)

Adoptar en Partes el release de **`paqsuite/laravel-core`** sin redefinir DDL de plataforma. El host **omite** el SP dictionary Tango incompatible (`IDEmpresa`) y consume el pin vigente.

**Fuera de alcance de esta TR:** unificación canónica MULTI/MONO de tablas de seguridad (eso es Framework GEN-06 / GEN-34). Lógica de negocio Partes; pantallas de dominio.

## 2) Restricciones de datos (Must)

| Restricción | Verificación |
|-------------|----------------|
| PK/FK `id` sin renumerar | Conteo filas estable post-deploy |
| `users.codigo` y passwords | Login usuario conocido |
| `nombre` / labels visibles | Sin reset de denominación |
| Matriz permisos | Mismo menú efectivo |

Prohibido: `migrate:fresh`, truncates seguridad en preview/prod.

## 3) Piezas host

| ID | Tarea | Resultado |
|----|--------|-----------|
| T1 | Pin laravel-core + react-core | `paqsuite/laravel-core` **1.3.18-beta.24** (`composer.json`) |
| T2 | Manifiesto `omitFrameworkEntryIds` | `fw-sp-pq_sp_admin_empresa_tango_dictionary` en `partesatencion-1.3.18-beta.24.json` |
| T3 | No redeploy SP Tango dictionary | `pq_sp_admin_empresa_candidatos_erp` / `create_alta` omitidos en MONO |
| T4 | Pipeline Forge | migrate → `deploy-sp` → `installation:update` |
| T5 | Smoke | Login, menú, `installation:update` 10/10 (CC #10 D-VERIFICACION); empresas SDK `tenancyMode=single` |

## 4) Criterios de aceptación

- [x] **CA-H01** Deploy preview/prod completa sin error `IDEmpresa`. (`installation:update` OK en Forge; D-VERIFICACION CC #10)
- [x] **CA-H02** Credenciales y permisos equivalentes al pre-deploy. (operación continua 2026-10/11; backup y shell usados)
- [x] **CA-H03** Documentación pin en repo (`composer.json`, manifiestos `database/manifest/partesatencion-1.3.18-beta.*.json`, [RELEASE-PARTES.md](../../06-operacion/RELEASE-PARTES.md)).

## 5) Frontera Framework

El contrato único MONO/MULTI de `pq_empresa` / SP `pq_sp_admin_empresa_*` **sigue abierto en Framework** (SPEC-001-06-update / SPEC-001-34-update CC PQ #9). Esta TR cierra solo la **adopción host** vía omit + pin.

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-30 | Parte G — TR-update en `updates/`. |
| 2026-11-06 | Parte I — **Finalizado**. Host verificado en operación (sin `IDEmpresa`; manifiesto omit vigente). |
