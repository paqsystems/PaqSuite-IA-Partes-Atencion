# Integración PaqSuite IA Framework — Partes de Atención (SDK)

Estado: **Fase 1-2** (auth/sesión + menú/shell/i18n/empresas).  
Producto host: `PaqSuite-IA-Partes-Atencion`.  
Framework: `PaqSuite-IA-FRAMEWORK` vía `paqsuite/laravel-core` + `@paqsuite/react-core`.

---

## Dependencias

| Capa | Paquete | Resolución local |
|------|---------|------------------|
| Backend | `paqsuite/laravel-core: @dev` | `../../PaqSuite-IA-FRAMEWORK/packages/php/laravel-core` (symlink) |
| Frontend | `@paqsuite/react-core` | `file:../../PaqSuite-IA-FRAMEWORK/packages/js/react-core` + alias Vite al `src/index.ts` + `auth.css` / `shell.css` |


Frontend local (sin registry npm):

```json
"@paqsuite/react-core": "file:../../PaqSuite-IA-FRAMEWORK/packages/js/react-core"
```

> `npm install` con `"*"` falla si el paquete no está publicado; usar `file:`.

### Auth UI (GEN-01) — Must

Norma Framework: [`adopcion-auth-ui.md`](../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-auth-ui.md) · [`adopcion-shell-ui.md`](../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-shell-ui.md).

En este host:

| Pieza | Estado |
|-------|--------|
| `import '@paqsuite/react-core/auth.css'` + `shell.css` en `main.tsx` | Sí |
| Alias Vite + `paths` TS a CSS y `src/index.ts` | Sí |
| `AuthLoginLayout` / `AuthCardLayout` / `ShellLayout` | Sí |
| i18n login + shell + dashboard (5 locales) | Sí |
| Helper auth hero | `frontend/src/features/auth/partesAuthHero.ts` |

Config producto: `PAQSUITE_PROYECTO=partesatencion`, `PAQSUITE_TENANCY=single`, `PAQSUITE_DB=unified`.

Instalación demo: fila `EMPRESAS_CONEXION` (`proyecto=partesatencion`, `cliente=demo`) + mapa config `DEMO|partesatencion`.

`.env` local apunta a SQL Server `PAQSYSTEMS_PARTESATENCION_DEMO` (`DB_CONNECTION=sqlsrv`). Completar `DB_PASSWORD` con la clave SQL de `Axoft`.

---

## Deploy local (migrate + seed + SP)

Orden en la BD operativa (`PAQSYSTEMS_PARTESATENCION_DEMO`) — **ya ejecutado en lab DEMO**:

```bash
cd backend
# 1) SP (sqlcmd) — una vez / tras cambios de scripts
sqlcmd -S "192.168.41.2,1433" -U Axoft -P <pass> -d PAQSYSTEMS_PARTESATENCION_DEMO -C -i database/sp/<script>.sql
# 2) esquema + datos
php artisan migrate:fresh --force --seed
```

Smoke: `POST /api/v1/auth/login` con `X-Paq-Cliente: DEMO` → `admin`/`Paqsystems` o `PQ`/`PaqSystems26*` → 200 + token.

PHPUnit sigue en sqlite in-memory (`phpunit.xml`); no usa la BD demo.

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
