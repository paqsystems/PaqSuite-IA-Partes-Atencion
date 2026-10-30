# PROVISORIO — Checklist deploy beta.12 + CC PQ #8 / #9

> **Uso:** controlar deploy en preview/develop, prueba local en otra base y producción.  
> **Borrar este archivo** cuando CC #8 y adopción host CC #9 queden verificados y cerrados.

| Campo | Valor |
|-------|--------|
| **PR** | [#93](https://github.com/paqsystems/PaqSuite-IA-Partes-Atencion/pull/93) → `develop` |
| **Rama origen** | `v1.3.0-CLOUDSMITH` |
| **Pin** | `paqsuite/laravel-core` **1.3.18-beta.12** |
| **Pipeline Forge** | `post-deploy.sh` → `run-post-deploy-backend.php` (tenant `EMPRESAS_CONEXION` + migrate + SP + `installation:update`) |
| **BD tenant DEV** | `paqsystems_partesatencion_dev` (RDS; SoT `EMPRESAS_CONEXION` cliente **DEV** — no confundir con `DB_DATABASE` plantilla del `.env`) |
| **Creado** | 2026-10-30 |

---

## 1) Inventario SQL esperado

### 1.1 Tablas nuevas

| ☐ | Resultado |
|---|-----------|
| ☐ | **Ninguna** en este salto (beta.11 → beta.12 + Partes CC #8). |

### 1.2 Campos nuevos (ADD)

| ☐ | Tabla | Columna | Debe quedar |
|---|--------|---------|-------------|
| ☐ | `pq_empresa` | `erp_empresa_id` | `BIGINT NULL`; datos existentes sin tocar valores de negocio |
| ☐ | `pq_empresa` | `nombre_bd` | `NVARCHAR(128) NULL` |
| ☐ | `pq_menus` | `label_key` | `NVARCHAR(128) NULL` — en Partes suele existir; script Framework idempotente |
| ☐ | `pq_menus` | `labelKey` | `NVARCHAR(128) NULL` — opcional (esquema Tango); Partes puede no tenerla |

### 1.3 Campos alterados (MODIFY)

| ☐ | Tabla | Columna | Debe quedar | Nota |
|---|--------|---------|-------------|------|
| ☐ | `pq_menus` | `label_key` | `NOT NULL` | Solo si corrió `fw-sp-pq_menus_labelKey_not_null_pilot` y no hay NULL |
| ☐ | `pq_menus` | `labelKey` | `NOT NULL` | Solo si existe columna `labelKey` y corrió el piloto |

**No deben cambiar:** `users.id`, `users.codigo`, passwords; `pq_empresa.id`, `pq_empresa.nombre`; vínculos permisos (mismos `user_id` / `rol_id` / `empresa_id`).

### 1.4 SP nuevos (deben existir)

| ☐ | Procedimiento | Origen | Acción |
|---|---------------|--------|--------|
| ☐ | `dbo.pq_sp_partes_informe_paquete_horas` | Partes CC #8 | Script: `backend/scripts/sql/partes/pq_sp_partes_informe_paquete_horas.sql` — **no** está en `deploy-sp.php`; aplicar en BD **manual** en cada entorno sqlsrv |

Comportamiento: `@p_cliente_id` obligatorio; `saldo_inicial` + `items_json` + `total`; fila «Saldo inicial» (`id = -1`); running balance tarea **+** / paquete **−**; orden `fecha`, `id`.

### 1.5 SP que **no** deben desplegarse en MONO (CC #9)

Manifiesto host: `omitFrameworkEntryIds` → `fw-sp-pq_sp_admin_empresa_tango_dictionary`.

| ☐ | Procedimiento | Verificación |
|---|---------------|--------------|
| ☐ | `pq_sp_admin_empresa_candidatos_erp` | No redeploy Tango / sin dependencia `IDEmpresa` |
| ☐ | `pq_sp_admin_empresa_create_alta` | Idem (alta ERP dictionary MULTI) |

### 1.6 SP alterados (redeploy vía `installation:update`)

| ☐ | Procedimiento | Debe quedar |
|---|---------------|-------------|
| ☐ | `pq_sp_admin_empresa_list` | Solo columnas canónicas `pq_empresa`; **sin** error `IDEmpresa` |
| ☐ | `pq_sp_admin_empresa_get` | Idem + `@id` |
| ☐ | `pq_sp_admin_empresa_update` | Update `nombre`, `activo`, `theme`; devuelve `erpEmpresaId`, `nombreBd` |
| ☐ | `pq_sp_admin_menu_list_enabled` | Rama SDK con `label_key` → `labelKey` |
| ☐ | `pq_sp_user_menu` | `labelKey` desde `label_key` |
| ☐ | `pq_sp_partes_paquete_upsert` | Duración **60…14400** min, múltiplo **60** (script; prod valida también en PHP) |

### 1.7 Otros objetos

| ☐ | Objeto | Nota |
|---|--------|------|
| ☐ | `pq_installation_*` | Registro de pasos GEN-34 OK; no truncar |
| ☐ | `PQ_PARAMETROS_GRAL.valor_logico` / `valor_entero` | Columnas **calculadas** (migración host); SP emission/excel del **core** |
| ☐ | `pq_emission_jobs` | Columnas `notification_id`, `segment_key`, `communication_intents_json`, `artifact_path` (migración host) |
| ☐ | SP `pq_sp_emission_core` / `pq_sp_excel_core` | Solo **laravel-core** vía `installation:update` (sin copia en `backend/database/sp/`) |
| ☐ | Manifiesto host | `database/manifest/partesatencion-1.3.18-beta.12.json` (semver = pin core) |
| ☐ | GHA `manifest-validate` | Secret `CLOUDSMITH_READ_TOKEN` (no `COMPOSER_AUTH`) |

---

## 2) SQL de comprobación (copiar/pegar)

Ajustar nombre de BD si hace falta. Ejecutar en **dictionary** salvo SP Partes que vivan en la BD unificada del host.

```sql
-- A) Columnas GEN-06
SELECT c.name, t.name AS tipo, c.is_nullable
FROM sys.columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
WHERE c.object_id = OBJECT_ID('dbo.pq_empresa')
  AND c.name IN ('erp_empresa_id', 'nombre_bd');

-- B) Conteos pre/post (anotar antes del deploy)
SELECT 'users' AS t, COUNT(*) AS n FROM dbo.users WITH (NOLOCK)
UNION ALL SELECT 'pq_empresa', COUNT(*) FROM dbo.pq_empresa WITH (NOLOCK)
UNION ALL SELECT 'pq_permisos', COUNT(*) FROM dbo.pq_permisos WITH (NOLOCK);

-- C) Smoke CC #9
EXEC dbo.pq_sp_admin_empresa_list;

-- D) SP informe CC #8 (tras script manual)
SELECT OBJECT_ID('dbo.pq_sp_partes_informe_paquete_horas', 'P') AS sp_informe_paquete;
```

---

## 3) Fase A — Deploy preview / `develop` (Forge)

| ☐ | Paso |
|---|------|
| ☐ | PR #93 mergeado a `develop` |
| ☐ | Deploy Forge termina **sin error** |
| ☐ | `installation:update` completa (sin `IDEmpresa`) |
| ☐ | Inventario §1.2–1.6 en BD preview |
| ☐ | Script manual `pq_sp_partes_informe_paquete_horas.sql` aplicado |
| ☐ | Login usuario conocido |
| ☐ | Menú equivalente al pre-deploy |
| ☐ | ABM / listado empresas (SDK single) |
| ☐ | Informe **Paquete de horas**: saldo + fila inicial + destaque compras |
| ☐ | Carga paquete: **1 h** OK; **0 h** / **>240 h** rechazado |

**Anotaciones preview:**

```
Fecha:
Conteos pre:
Conteos post:
Errores log:
Responsable:
```

---

## 4) Fase B — Prueba local (otra base)

| ☐ | Paso |
|---|------|
| ☐ | Base distinta de preview/prod (restore o copia) |
| ☐ | `composer install` con lock beta.12 |
| ☐ | `php artisan migrate --force` |
| ☐ | `php scripts/deploy-sp.php` |
| ☐ | `php artisan paqsuite:installation:update --cliente=… --proyecto=… --non-interactive` |
| ☐ | Script manual informe paquete horas |
| ☐ | Misma checklist SQL §2 + smoke §3 |

**Anotaciones local:**

```
BD:
Fecha:
OK / fallos:
```

---

## 5) Fase C — Deploy productivo

| ☐ | Paso |
|---|------|
| ☐ | Preview + local OK |
| ☐ | Ventana acordada / backup BD |
| ☐ | Conteos §2 **antes** del deploy |
| ☐ | Mismo pipeline Forge § pipeline |
| ☐ | Script manual SP informe |
| ☐ | Smoke login, menú, empresas, informe paquete, carga 1 h |
| ☐ | Conteos §2 **después** = mismos totales (salvo altas normales) |

**Anotaciones prod:**

```
Fecha:
Conteos pre:
Conteos post:
Ticket / CC cerrado:
```

---

## 6) Cierre

| ☐ | Acción |
|---|--------|
| ☐ | CC PQ **#9** — CA-H01…H03 verificados → actualizar `00-ControlCalidad-PQ.md` / TR adopción |
| ☐ | CC PQ **#8** — PIAPARTES-52 verificado en prod |
| ☐ | **Eliminar** este archivo (`docs/06-operacion/PROVISORIO-checklist-deploy-beta12-cc8-cc9.md`) |

---

## Referencias

- [TR-adopcion-host-cc-pq-9](../04-tareas/updates/001-Generalidades/TR-adopcion-host-cc-pq-9-seguridad-mono-multi-update.md)
- [TR-006-update CC #8](../04-tareas/updates/100-SistemaPartes/TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md)
- [TR-013-update CC #8](../04-tareas/updates/100-SistemaPartes/TR-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md)
- `scripts/forge/post-deploy.sh`
