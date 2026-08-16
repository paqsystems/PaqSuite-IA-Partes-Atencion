# Integración PaqSuite IA Framework — Partes de Atención (SDK)

Estado: **Fase 1-2** (auth/sesión + menú/shell/i18n/empresas).  
Producto host: `PaqSuite-IA-Partes-Atencion`.  
Framework: `PaqSuite-IA-FRAMEWORK` vía `paqsuite/laravel-core` + `@paqsuite/react-core`.

---

## Dependencias

| Capa | Paquete | Resolución (`1.2.0-FINAL`+) |
|------|---------|------------------------------|
| Backend | `paqsuite/laravel-core: ^1.3.3` | Satis `http://100.110.69.93/satis` (corpus GEN empaquetado) |
| Frontend | `@paqsuite/react-core: 2.2.1` | Verdaccio `http://100.110.69.93:4873` |

Deploy / bump: [`deploy-sdk-package-repos.md`](./deploy-sdk-package-repos.md).  
Guías Framework: `GUIA_PRUEBA_INSTALACION.md`, `GUIA_ACTUALIZACION_PROYECTO.md`.

Debug local del SDK (modo C, no committear): link path temporal — ver Framework `adopcion-sdk-registry.md` §4.


### Auth UI (GEN-01) — Must

Norma Framework: [`adopcion-auth-ui.md`](../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-auth-ui.md) · [`adopcion-shell-ui.md`](../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-shell-ui.md).

En este host:

| Pieza | Estado |
|-------|--------|
| `import '@paqsuite/react-core/auth.css'` + `shell.css` en `main.tsx` | Sí |
| Resolución desde `node_modules` (Verdaccio `@paqsuite/react-core`) | Sí |
| `AuthLoginLayout` / `AuthCardLayout` / `ShellLayout` | Sí |
| i18n login + shell + dashboard (5 locales) | Sí |
| Helper auth hero | `frontend/src/features/auth/partesAuthHero.ts` |

Config producto: `PAQSUITE_PROYECTO=partesatencion`, `PAQSUITE_TENANCY=single`, `PAQSUITE_DB=unified`.

### Lookup instalación (GEN-18)

| Modo | Variable | Uso |
|------|----------|-----|
| SQL (lab/prod multi-cliente) | `PAQSUITE_INSTALACION_RESOLVER=sql` | Conexión `paqsuite_central` → BD `PAQSYSTEMS` + SP `pq_sp_empresas_conexion_get` |
| Config (PHPUnit / fallback) | `=config` | Mapa `DEMO\|partesatencion` en `config/paqsuite.php` |

Guía Framework: [`adopcion-instalacion-sql.md`](../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-instalacion-sql.md).

Fila canónica: `proyecto=partesatencion`, `cliente=DEMO` (UPPERCASE) → destino operativo `PAQSYSTEMS_PARTESATENCION_DEMO`.

**Opción A (actual):** el middleware solo valida la fila; la app sigue en `DB_*` / `sqlsrv`.

`.env` local: `DB_*` → operativa; `PAQSUITE_CENTRAL_*` → `PAQSYSTEMS`. Completar passwords `Axoft`.

---

## Deploy local (migrate + seed + SP)

### Catálogo PAQSYSTEMS (lookup instalación)

```bash
# En BD PAQSYSTEMS (una vez / tras cambios) — SP desde vendor del host
sqlcmd -S "192.168.41.2,1433" -U Axoft -P <pass> -d PAQSYSTEMS -C -i vendor/paqsuite/laravel-core/database/sp/pq_sp_empresas_conexion_get.sql
# Fila DEMO|partesatencion (si no existe): backend/database/sp/seed_empresas_conexion_partesatencion_demo.sql
```

`.env`: `PAQSUITE_INSTALACION_RESOLVER=sql` + `PAQSUITE_CENTRAL_*`.

### BD operativa (`PAQSYSTEMS_PARTESATENCION_DEMO`)

Orden — **ya ejecutado en lab DEMO**:

```bash
cd backend
# 1) SP de negocio (sqlcmd) — una vez / tras cambios de scripts
sqlcmd -S "192.168.41.2,1433" -U Axoft -P <pass> -d PAQSYSTEMS_PARTESATENCION_DEMO -C -i database/sp/<script>.sql
# 2) esquema + datos
php artisan migrate:fresh --force --seed
```

Smoke: `POST /api/v1/auth/login` con `X-Paq-Cliente: DEMO` → `admin`/`Paqsystems` o `PQ`/`PaqSystems26*` → 200 + token.

PHPUnit sigue en sqlite in-memory (`phpunit.xml`) con `PAQSUITE_INSTALACION_RESOLVER=config`; no usa PAQSYSTEMS.

Notas SQL Server host:
- FK self-ref `pq_menus.parent_id` sin `ON DELETE SET NULL` (restricción SQL Server).
- Timestamps seed / Sanctum: formato `Ymd H:i:s` + `SET DATEFORMAT ymd` / `PersonalAccessToken` custom.
## Huecos Framework / upstream (NO inventar contratos)

### 1. Adapters SP en el host

`laravel-core` expone **interfaces** (`MenuQueryRepository`, `UserEmpresasQueryRepository`, etc.) pero **no** incluye adapters PHP SP. El host implementa `App\Repositories\Sp\*`.

### 2. Tabla `pq_empresa` vs stub `pq_empresas`

Scripts canónicos del Framework en `pq_sp_user_empresas_list` / `pq_sp_user_empresa_allowed` referencian `pq_empresas`.  
Migración smoke y host Partes usan **`pq_empresa`**.  
Scripts locales corregidos en `backend/database/sp/`.  
**Fix pendiente upstream Framework.**

### 3. Preferencias usuario (`openInNewTab` / `activeLlmCredentialId`)

`UserPreferencesRepository` del Framework contempla `locale`, `openInNewTab`, `activeLlmCredentialId`.  
SP upstream: solo `pq_sp_user_locale_get/set`.  
Host Partes: **`pq_sp_user_preferences_get/set`** (`backend/database/sp/pq_sp_user_preferences.sql`) lee/escribe `locale` + `open_in_new_tab`.  
`activeLlmCredentialId` → `null` hasta Fase 5 / GEN-16.  
**Hueco a upstream.**

### 4. Permisos admin — `listByUserId`

No existe SP `listByUserId` de permisos; `MenuAuthorizationService` lo requiere.  
Host: `SpPermisoAdminRepository::listByUserId` reutiliza `pq_sp_user_empresas_list` (mapeo mínimo con `empresaId`).  
`create` / `deleteById` → `RuntimeException("GEN-06 no adoptado en Fase 1-2")`.

### 5. `InstalacionResolver`

Producción futura: SQL contra `PAQSYSTEMS.EMPRESAS_CONEXION`.  
Fase 1-2: **`ConfigMapInstalacionResolver`** del paquete con mapa `DEMO|partesatencion` en `config/paqsuite.php`.

### 6. Helper SP — drivers

`App\Repositories\Sp\SpCaller`:

- **sqlsrv:** `EXEC dbo.{procedure} @param = ?`
- **mysql:** `CALL {procedure}(?)`
- **sqlite (tests PHPUnit):** SQL equivalente inline (solo desarrollo/CI; documentado aquí). MySQL local sin scripts SP desplegados requiere deploy SQL Server o adaptar `CALL`.

Scripts SQL Server canónicos: `backend/database/sp/`.

---

## Excepciones GEN documentadas

| Caso | Vía |
|------|-----|
| Login Sanctum sobre `User` Eloquent | Excepción auth Framework (documentada) |
| Tests sqlite | Fallback SQL en `SpCaller` (no producción) |

---

## Fases NO incluidas

- Fase 3: admin seguridad (usuarios/roles/permisos ABM)
- Fase 4+: grid layouts (GEN-11)
- Capabilities: tasks, LLM, excel, pivots, chat

---

## Deploy post-merge (cuando aplique)

1. `composer update paqsuite/laravel-core` en `backend/`
2. `php artisan migrate --force`
3. `php artisan db:seed --force`
4. Ejecutar scripts `backend/database/sp/*.sql` en SQL Server (orden: preferencias, empresas, acceso, menú, parámetros)
5. Verificar `.env`: `PAQSUITE_PROYECTO=partesatencion`, headers tenant
6. Smoke: `GET /api/v1/health`, login `admin`/`Paqsystems` (o `PQ`/`PaqSystems26*`) con `X-Paq-Cliente: DEMO`

### Credenciales seed (SPEC-001-18 §8)

| Usuario | Password | Uso |
|---------|----------|-----|
| `admin` | `Paqsystems` | Ingreso genérico / desarrollo |
| `PQ` | `PaqSystems26*` | Uso interno PaqSystems |

`firstLogin` no obligatorio.

---

## Referencias

- Guía: `PaqSuite-IA-FRAMEWORK/docs/00-Conceptualizacion/04-guias/COMO_USAR_EL_FRAMEWORK_DESDE_UN_PROYECTO.md`
- Smoke: `apps/smoke-backend`, `apps/smoke-frontend`
