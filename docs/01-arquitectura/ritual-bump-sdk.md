# Ritual — bump SDK PaqSuite (Partes)

Igual que actualizar `laravel/framework` o `devextreme`: cambiar el pin, regenerar lock, smoke.

Plan de adopción sin forks GEN: [plan-partes-adopcion-sdk-sin-fork-gen.md](./plan-partes-adopcion-sdk-sin-fork-gen.md).

## Versiones objetivo (oleada grid i18n + menú scaffold 0.1.13)

| Paquete | Objetivo | Registry |
|---------|----------|----------|
| `paqsuite/laravel-core` | **1.3.8** (refresh catálogo; sin cambio contrato Partes esperado) | Satis `http://100.110.69.93/satis` |
| `@paqsuite/react-core` | **≥ 2.4.13** (`registerGridI18nResources`, menú/shell alineado create-app **0.1.13**) | Verdaccio `http://100.110.69.93:4873` |
| `@paqsuite/create-app` (scaffold, diff obligatorio) | **0.1.13** | Verdaccio (no es dep runtime de Partes) |

## Versiones instaladas en repo (deploy — default)

| Paquete | Pin / lock | Nota |
|---------|------------|------|
| `paqsuite/laravel-core` | **1.3.9** | Satis en `backend/composer.json` |
| `@paqsuite/react-core` | **2.4.16** | Verdaccio; `frontend/package-lock.json` resuelve el tarball HTTPS (GEN-06: `SecurityRolesPage`, `EmpresasAdminPage`, `RolAtributosPage`) |

Vercel: `scripts/vercel-install.sh` + `VERDACCIO_AUTH_TOKEN`. Vite **no** alias al monorepo salvo `PAQ_REPO_LAB=1`.

## Modo repo-lab (opcional, local)

Ver `frontend/MODO-REPO-LAB.md`. No commitear `file:` ni Composer `path` en ramas de release; volver a pins registry antes del PR.

## Backend

```bash
cd backend
# Tailscale activo (acceso a srv-pq)
composer update paqsuite/laravel-core
composer show paqsuite/laravel-core
```

## Frontend

```bash
cd frontend
# Modo registry (release empaquetado):
npm install @paqsuite/react-core@2.4.13   # o última ≥ 2.4.13 con grid i18n
npm list @paqsuite/react-core

# Vercel (sin acceso directo a 100.110.69.93):
# Verdaccio vía Funnel HTTPS + VERDACCIO_AUTH_TOKEN (ver docs/06-operacion/verdaccio-vercel-conectividad.md).
# Tras publicar en Verdaccio:
#   cd frontend && .\scripts\refresh-react-core-lock.ps1
```

Tras instalar react-core ≥ 2.4.13: diff `node_modules/@paqsuite/react-core/src/index.ts` vs template `@paqsuite/create-app@0.1.13` (`ShellPage`, `i18n.ts`).

## Tras el bump

1. Commit `composer.lock` / `package-lock.json` (+ pins si cambiaron).
2. Fase 1: `registerGridI18nResources(i18n)` en `frontend/src/i18n/i18n.ts` (regla **43**).
3. Smoke: health, login, cambio idioma → menú contextual pie de grilla traducido.
4. Redeploy Forge + build FE (builder con red a `srv-pq`).

Detalle deploy: [`deploy-sdk-package-repos.md`](./deploy-sdk-package-repos.md).
