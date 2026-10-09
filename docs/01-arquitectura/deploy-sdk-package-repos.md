# Deploy Partes — SDK vía Cloudsmith (modelo empaquetado)

Fecha: 2026-08-14 · Actualizado: 2026-10-09 (GEN-35)  
Target: `paqsuite/laravel-core@1.3.13-beta.1` · `@paqsuite/react-core@2.4.24-beta.1`  
Instructivo: [`docs/06-operacion/adopcion-gen-35-cloudsmith.md`](../06-operacion/adopcion-gen-35-cloudsmith.md)

> Guías Framework: `GUIA_PRUEBA_INSTALACION.md`, `GUIA_ACTUALIZACION_PROYECTO.md`  
> Adopción: `PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-sdk-registry.md` (evoluciona a registries)  
> Legado path/clone: [`forge-deploy-framework-path.md`](./forge-deploy-framework-path.md)

---

## Contrato

| Capa | Dependencia | Registry |
|------|-------------|----------|
| Backend | `paqsuite/laravel-core: 1.3.13-beta.1` | Cloudsmith `composer.cloudsmith.io/paqsystems/paqsuite-sdk` |
| Frontend | `@paqsuite/react-core: 2.4.24-beta.1` | Cloudsmith `npm.cloudsmith.io/paqsystems/paqsuite-sdk` (`frontend/.npmrc`) |

**Sin** path/`file:` al monorepo Framework.  
**Sin** `git+https` / VCS GitHub como contrato de producto.  
**Sin** `forge-ensure-framework.sh` en Deploy Script.

El install/build produce el artefacto; el deploy sirve **vendor** + **dist** ya resueltos (como Laravel/DevExtreme).

## Modo repo-lab local (opcional)

El **contrato de repo** es registry (tabla anterior). Para desarrollo contra el monorepo hermano `C:\Programacion\PaqSuite-IA-FRAMEWORK`, ver `frontend/MODO-REPO-LAB.md`:

- **FE:** pin temporal `file:../../PaqSuite-IA-FRAMEWORK/packages/js/react-core` + `PAQ_REPO_LAB=1` en Vite — **no** commitear en ramas de release.
- **BE:** Cloudsmith en `composer.json`; lab PHP con `PaqSuite-IA-FRAMEWORK\tools\sdk\sdk-link.ps1` (no commitear `path` en `composer.json`).

Vercel y Forge usan siempre el flujo empaquetado descrito abajo.

---

## Prerrequisito de red

El **builder** (local, Forge o Vercel) alcanza Cloudsmith por HTTPS. **No** hace falta Tailscale para paquetes SDK.

| Entorno | Acción |
|---------|--------|
| Dev local | `CLOUDSMITH_READ_TOKEN`; `.npmrc` + Cloudsmith en `composer.json` |
| Forge | Env `CLOUDSMITH_READ_TOKEN`; `forge-composer-install.sh` |
| Vercel | Env `CLOUDSMITH_READ_TOKEN`; `scripts/vercel-install.sh` |

Tailscale puede seguir para SQL/RDP, no para bajar el SDK.

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

`composer.json` trae `"secure-http": true` y el repo Cloudsmith.

El script exige `CLOUDSMITH_READ_TOKEN`, rechaza `laravel-core` como `path` y
exige que el lock resuelva desde `cloudsmith.io`. Genera `auth.json` efímero
(no se commitea).

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
| Install | `bash scripts/vercel-install.sh` → `npm install` con `.npmrc` (Cloudsmith) |
| Secret | `CLOUDSMITH_READ_TOKEN` en Vercel |
| Registry `@paqsuite` | `https://npm.cloudsmith.io/paqsystems/paqsuite-sdk/` (override: `PAQSUITE_NPM_REGISTRY`) |
| Lock | `@paqsuite/react-core@2.4.24-beta.1` — regenerar con `frontend/scripts/refresh-react-core-lock.ps1` |

Runbook: [`docs/06-operacion/adopcion-gen-35-cloudsmith.md`](../06-operacion/adopcion-gen-35-cloudsmith.md). Verdaccio/Funnel: legado [`verdaccio-vercel-conectividad.md`](../06-operacion/verdaccio-vercel-conectividad.md).

- Build: `npm run build` → `dist/`
- En el dashboard Vercel: **no** definir `NPM_CONFIG_REGISTRY` / registry Tailscale plano
- `VITE_API_BASE_URL` según [`frontend-api-base-url-y-env.md`](./frontend-api-base-url-y-env.md)

Bump de `react-core`: publicar en Cloudsmith (Framework) → `refresh-react-core-lock` → commit lock → redeploy sin caché.

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
