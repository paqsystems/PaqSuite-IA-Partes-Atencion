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

```bash
cd $FORGE_RELEASE_DIRECTORY/backend
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart || true
```

`composer.json` ya trae `"secure-http": false` y el repo Satis.

---

## Vercel (frontend)

- Root Directory: `frontend`
- Install: `npm install` (lee `.npmrc` → Verdaccio)
- Build: `npm run build` → `dist/` (SDK bundlado)
- `VITE_API_BASE_URL` según [`frontend-api-base-url-y-env.md`](./frontend-api-base-url-y-env.md)

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
