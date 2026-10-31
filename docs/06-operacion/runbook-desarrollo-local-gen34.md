# Runbook — desarrollo local (GEN-34 + PARTE O)

Complementa el dispatcher BASE (PARTE **O**) y la regla transversal **`.cursor/rules/base/00-arquitectura/16-entorno-desarrollo-gen34.mdc`** (vía symlink BASE en hosts). Este runbook es la **implementación Partes** del contrato común.

## Objetivo

Al iniciar el lab en Cursor, la base operativa del tenant configurado en `backend/.env` debe quedar alineada al código (migraciones Laravel + manifiesto Framework/host + SP de dominio Partes), **antes** de `php artisan serve` y `npm run dev`.

## Base de datos efectiva

| Variable / mecanismo | Rol |
|---------------------|-----|
| `PAQSUITE_CLIENTE` + `PAQSUITE_PROYECTO` | Identifican la fila de instalación (p. ej. `DEV` + `partesatencion`). |
| `PAQSUITE_INSTALACION_RESOLVER=sql` | Lookup en catálogo `EMPRESAS_CONEXION` (conexión central en `.env`). |
| `DB_*` / `DB_COMPANY_DATABASE` | En lab deben ser **coherentes** con la fila DEV (típico RDS: `paqsystems_partesatencion_dev`). |

El script aplica la conexión tenant con `backend/scripts/forge/bootstrap-tenant-sqlsrv.php` (misma lógica que Forge post-deploy). No usar solo `DB_DATABASE` del `.env` sin bootstrap si el runtime HTTP resuelve por instalación.

## Comandos (raíz del repo Partes)

| Acción | Comando |
|--------|---------|
| Solo sincronizar SQL | `.\scripts\dev\sync-installation-sql.ps1` |
| Sync sin SP host (más rápido) | `.\scripts\dev\sync-installation-sql.ps1 -SkipHostSp` |
| Stack completo (sync + cola + backend + frontend) | `.\scripts\dev\start-dev.ps1 -All` |
| Stack sin tocar BD | `.\scripts\dev\start-dev.ps1 -All -SkipSqlSync` |

Equivalente PHP (desde `backend/`):

```text
php scripts/dev/run-local-gen34-sync.php
php scripts/dev/run-local-gen34-sync.php --skip-host-sp
```

## Secuencia interna del sync

1. `migrate --force`
2. `paqsuite:installation:update --cliente=… --proyecto=… --non-interactive`
3. `paqsuite:installation:status`
4. `scripts/deploy-sp.php` (SP en `backend/database/sp/` y `backend/scripts/sql/partes/`)

Los SP GEN del SDK (`pq_sp_task_*`, etc.) entran por **`installation:update`** (manifiesto `paqsuite/laravel-core` + host `database/manifest/partesatencion-{semver}.json`). El semver del manifiesto host se deriva del pin en `backend/composer.json` salvo override `PAQSUITE_HOST_MANIFEST`.

## URLs típicas (`start-dev.ps1 -All`)

| Servicio | URL |
|----------|-----|
| Frontend | http://localhost:3000 |
| Backend | http://127.0.0.1:8010 |
| OpenAPI | http://127.0.0.1:8010/api/documentation |
| Login lab DEV | http://localhost:3000/login?cliente=DEV |

## Modo gateway vs SQL directo

| Modo | Condición | Sync SQL local | ¿Levantar entorno dev? |
|------|-----------|----------------|-------------------------|
| **Directo** | `EMPRESAS_CONEXION` sin `agent_id`+`client_id` | **Sí** (migrate + update + deploy-sp) | **Sí** |
| **Gateway** | `agent_id` y `client_id` informados | **No** (exit code **2**, mensaje en consola) | **Sí** — `start-dev.ps1 -All` continúa; el SQL del cliente se aplica vía agente/Forge, no desde el PC |

En gateway, aborta **solo** la actualización SQL local; no es un error de arranque. El backend local sigue sirviendo la app; las operaciones de negocio en runtime usan el contrato gateway.

Si el sync devuelve exit **1** (checksum, SP, red, permisos), `start-dev.ps1 -All` **no** levanta el stack hasta corregir el fallo o usar `-SkipSqlSync` a sabiendas.

## Prompt en Cursor

Frases: «Iniciá / inicia el entorno de desarrollo» → el agente debe ejecutar sync GEN-34 (salvo «sin tocar la BD») y luego levantar el stack (preferir `start-dev.ps1 -All`).

## Verificación rápida post-sync (GEN-13)

Tras bump de `laravel-core` / `@paqsuite/react-core` o tras sync local:

```powershell
cd backend
php artisan paqsuite:installation:status --cliente=DEV --proyecto=partesatencion
php artisan test tests/Unit/SchedulingTaskDefinitionRepositoryTest.php
```

Manifiesto esperado alineado al pin de `composer.json` (p. ej. `paqsuite-framework-1.3.18-beta.18+partesatencion-1.3.18-beta.18`).

Checklist operativo (listado UI, guardar definición, `parameter_set`, wrappers PHP):

| SoT | Documento |
|-----|-----------|
| **Framework (Must)** | `PaqSuite-IA-FRAMEWORK` → `docs/06-operacion/adopcion-gen-13-tareas-programadas-host.md` §6 |
| **Partes (perfil + rutas)** | [adopcion-gen-13-tareas-programadas.md](./adopcion-gen-13-tareas-programadas.md) § Inventario + checklist bump |

Si la grilla **Definiciones** está vacía o el save muestra error genérico, revisar `storage/logs/laravel.log` (fatal en `TaskDefinitionRepository` o SQL en `parameter_set`) antes de atribuirlo a datos.

## Referencias

- Framework: `docs/02-producto/34-instalacion-actualizacion-proyecto.md` §4.8 y §5.5
- Framework cableado GEN-13: `docs/06-operacion/adopcion-gen-13-tareas-programadas-host.md`
- Forge: `backend/scripts/forge/run-post-deploy-backend.php`
- GEN-13 Partes: [adopcion-gen-13-tareas-programadas.md](./adopcion-gen-13-tareas-programadas.md)
