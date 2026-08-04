# Deploy Partes — SDK vía repos de paquete (F2/F4)

Fecha: 2026-08-04 · Rama producto: `1.2.0`

> **Canónico Framework:** [`adopcion-sdk-registry.md`](../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-sdk-registry.md)  
> **Legado path/clone:** [`forge-deploy-framework-path.md`](./forge-deploy-framework-path.md) (no usar en sitios nuevos)

---

## Contrato Partes

| Capa | Dependencia |
|------|-------------|
| Backend | `paqsuite/laravel-core: ^1.3.1` → VCS `https://github.com/paqsystems/laravel-core.git` |
| Frontend | `@paqsuite/react-core` → `github:paqsystems/react-core#v2.2.0` |

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

1. Variable / credencial git con **lectura** a `paqsystems/react-core` (PAT o GitHub integration que alcance ese repo privado).
2. `npm ci` / `npm install` debe clonar `github:paqsystems/react-core#v2.2.0`.
3. Build de producción: `npm run build` → **Vite** (`build:check` / `typecheck` opcionales; el source del SDK aún no pasa `tsc -b` limpio).
4. Mantener `VITE_API_BASE_URL` según [`frontend-api-base-url-y-env.md`](./frontend-api-base-url-y-env.md).

---

## Checklist cutover

- [ ] Acceso git Forge → `laravel-core`
- [ ] Acceso git Vercel/CI → `react-core`
- [ ] Deploy Script **sin** `forge-ensure-framework.sh`
- [ ] `composer.lock` / `package-lock.json` regenerados con las deps nuevas
- [ ] Smoke: `GET /api/v1/health` + build FE Vercel
