# Deploy Partes — SDK vía Satis + Verdaccio (modelo empaquetado)

Fecha: 2026-08-14 · Actualizado: 2026-08-15 · Rama: `1.2.0-FINAL`  
Target: `paqsuite/laravel-core@^1.3.3` · `@paqsuite/react-core@2.2.1` · scaffold `@paqsuite/create-app@0.1.8`

> Guías Framework: `GUIA_PRUEBA_INSTALACION.md`, `GUIA_ACTUALIZACION_PROYECTO.md`  
> Adopción: `PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-sdk-registry.md` (evoluciona a registries)  
> Legado path/clone: [`forge-deploy-framework-path.md`](./forge-deploy-framework-path.md)

---

## Contrato

| Capa | Dependencia | Registry |
|------|-------------|----------|
| Backend | `paqsuite/laravel-core: ^1.3.3` | Satis `http://100.110.69.93/satis` |
| Frontend | `@paqsuite/react-core: 2.2.1` | Verdaccio `http://100.110.69.93:4873` (`frontend/.npmrc`) |

**Sin** path/`file:` al monorepo Framework.  
**Sin** `git+https` / VCS GitHub como contrato de producto.  
**Sin** `forge-ensure-framework.sh` en Deploy Script.

El install/build produce el artefacto; el deploy sirve **vendor** + **dist** ya resueltos (como Laravel/DevExtreme).

## Modo repo-lab local (opcional)

El **contrato de repo** es registry (tabla anterior). Para desarrollo contra el monorepo hermano `C:\Programacion\PaqSuite-IA-FRAMEWORK`, ver `frontend/MODO-REPO-LAB.md`:

- **FE:** pin temporal `file:../../PaqSuite-IA-FRAMEWORK/packages/js/react-core` + `PAQ_REPO_LAB=1` en Vite — **no** commitear en ramas de release.
- **BE:** Satis en `composer.json`; lab PHP con `PaqSuite-IA-FRAMEWORK\tools\sdk\sdk-link.ps1` (no commitear `path` en `composer.json`).

Vercel y Forge usan siempre el flujo empaquetado descrito abajo.

---

## Prerrequisito de red

El **builder** (local, Forge o CI) debe alcanzar `srv-pq` (Tailscale → `100.110.69.93`).

| Entorno | Acción |
|---------|--------|
| Dev local | Tailscale activo; `.npmrc` + Satis en `composer.json` |
| Forge | Server con ruta a Satis; `composer install` en Deploy Script |
| Vercel | Build con acceso a Verdaccio (subnet router / CI que prebuild) |

Si Vercel no ve Tailscale: el fallo es de **infra del builder**, no se vuelve a `git+https` en `package.json`.

---

## Forge (backend)

Si el Deploy Script de Forge se ejecuta desde la raíz del release:

```bash
bash "$FORGE_RELEASE_DIRECTORY/scripts/forge-composer-install.sh"
cd $FORGE_RELEASE_DIRECTORY/backend
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart || true
# Warmup: evita timeout del health check post-deploy (primera request fría).
curl -fsS --max-time 20 "http://127.0.0.1/up" >/dev/null || true
```

El entrypoint de la raíz delega en `backend/scripts/forge-composer-install.sh`.
También es válido ejecutar directamente el script del backend después de hacer
`cd $FORGE_RELEASE_DIRECTORY/backend`.

`composer.json` ya trae `"secure-http": false` y el repo Satis.

El script valida antes de instalar que el lock no contenga el repositorio
`path` legado a `PaqSuite-IA-FRAMEWORK` y que Forge pueda leer
`http://100.110.69.93/satis/packages.json`. Si falla esa comprobación, no reutilizar
`vendor/`: publicar una release nueva con el `composer.lock` versionado y
corregir la ruta Tailscale/VPN del servidor Forge.

### Health check post-deploy (Forge)

Forge hace ping externo tras cada deploy. Si falla con *«The health check endpoint timed out»*:

1. En el sitio → **Settings → Deployments → Health check**, URL: **`/up`** (o `/api/v1/health`).
2. El host expone `GET /up` → `200` texto `OK` (sin DB). Tras cambiar la ruta, redeploy para refrescar `route:cache`.
3. En Cloudflare (dominio `on-forge.com` / custom): permitir User-Agent `Laravel-Healthcheck/1.0` o las IPs Forge `209.38.170.132`, `206.189.255.228`, `139.59.222.70` (si Bot Fight / WAF bloquea, el check hace timeout).
4. Incluir el `curl` de warmup del Deploy Script (arriba) para que la primera request no sea la del health check remoto.

---

## Vercel (frontend)

El builder público **no** resuelve la IP Tailscale `100.110.69.93` ni MagicDNS privado. **No** usar tarball `vendor/` como contrato de release (solo emergencia documentada).

| Item | Valor |
|------|--------|
| Root Directory | `frontend` |
| Install | `bash scripts/vercel-install.sh` → `npm install` con `.npmrc` (Verdaccio HTTPS Funnel) |
| Secret | `VERDACCIO_AUTH_TOKEN` en Vercel |
| Registry `@paqsuite` | `https://srv-pq.tail6726a3.ts.net` (override: `PAQSUITE_NPM_REGISTRY`) |
| Lock | `@paqsuite/react-core@2.4.15` — regenerar con `frontend/scripts/refresh-react-core-lock.ps1` tras publicar en Verdaccio |

Runbook detallado: [`docs/06-operacion/verdaccio-vercel-conectividad.md`](../06-operacion/verdaccio-vercel-conectividad.md).

- Build: `npm run build` → `dist/`
- En el dashboard Vercel: **no** definir `NPM_CONFIG_REGISTRY` / registry Tailscale plano
- `VITE_API_BASE_URL` según [`frontend-api-base-url-y-env.md`](./frontend-api-base-url-y-env.md)

Bump de `react-core`: publicar en Verdaccio → `refresh-react-core-lock` → commit lock → redeploy sin caché.

---

## Bump de versión SDK

```bash
# Backend
cd backend && composer update paqsuite/laravel-core

# Frontend
cd frontend && npm update @paqsuite/react-core
```

Verificar: `composer show paqsuite/laravel-core` · `npm list @paqsuite/react-core`

---

## Checklist cutover

- [x] Satis responde `1.3.3`; Verdaccio `2.2.1` (+ `create-app@0.1.8`)
- [ ] Locks committeados
- [ ] Deploy Script sin ensure-framework
- [ ] Smoke `GET /api/v1/health` + login
- [ ] Build FE produce `dist/`
