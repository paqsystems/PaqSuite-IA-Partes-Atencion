# Deploy Partes — SDK vía repos de paquete (F2/F4)

Fecha: 2026-08-04 · Rama producto: `1.2.0`

> **Canónico Framework:** [`adopcion-sdk-registry.md`](../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-sdk-registry.md)  
> **Legado path/clone:** [`forge-deploy-framework-path.md`](./forge-deploy-framework-path.md) (no usar en sitios nuevos)

---

## Contrato Partes

| Capa | Dependencia |
|------|-------------|
| Backend | `paqsuite/laravel-core: ^1.3.1` → VCS `https://github.com/paqsystems/laravel-core.git` |
| Frontend | `@paqsuite/react-core` → `git+https://github.com/paqsystems/react-core.git#v2.2.0` |

**Sin** path a `PaqSuite-IA-FRAMEWORK`. **Sin** `forge-ensure-framework.sh` en el Deploy Script.

---

## F2 — Auth en Forge (backend)

1. En el server Forge: deploy key o GitHub App con **lectura** a `paqsystems/laravel-core` (además del repo Partes).
2. Composer resuelve el VCS en `composer install` (HTTPS con credenciales del server, o SSH si la key está cargada).
3. Deploy Script sugerido:

```bash
cd $FORGE_RELEASE_DIRECTORY/backend
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart || true
```

Web Directory típico: `backend/public` (repo completo Partes, no solo subfolder backend como root sin ajustar paths).

---

## F2 — Auth en Vercel (frontend)

### Causa típica del fallo

```text
git@github.com: Permission denied (publickey)
ls-remote ssh://git@github.com/paqsystems/react-core.git
```

Vercel **no tiene SSH** hacia repos privados. Hay que clonar por **HTTPS + PAT**.

### Configuración Must

1. **Environment Variable** en el proyecto Vercel (Production + Preview):
   - Name: `GITHUB_TOKEN`
   - Value: PAT (classic `repo` o fine-grained con Contents: Read sobre `paqsystems/react-core`)
   - Sensitive / encrypted

2. **Root Directory:** `frontend` (si el site apunta al monorepo Partes).

3. **Install / Build** (ya en `frontend/vercel.json`):
   - Install: `bash scripts/vercel-install.sh` (reescribe git→HTTPS con el token + `npm install`)
   - Build: `npm run build`
   - Output: `dist`

4. Mantener `VITE_API_BASE_URL` según [`frontend-api-base-url-y-env.md`](./frontend-api-base-url-y-env.md).

### Checklist Vercel

- [ ] `GITHUB_TOKEN` con lectura a `react-core`
- [ ] Root Directory = `frontend`
- [ ] Redeploy tras setear el token
- [ ] Log de install **sin** `Permission denied (publickey)`

---

## Checklist cutover

- [ ] Acceso git Forge → `laravel-core`
- [ ] `GITHUB_TOKEN` en Vercel → `react-core`
- [ ] Deploy Script Forge **sin** `forge-ensure-framework.sh`
- [ ] `composer.lock` / `package-lock.json` con deps nuevas
- [ ] Smoke: `GET /api/v1/health` + build FE Vercel verde
