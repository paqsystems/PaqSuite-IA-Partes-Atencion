# Ritual — bump SDK PaqSuite (Partes)

Igual que actualizar `laravel/framework` o `devextreme`: cambiar el pin, regenerar lock, smoke.

Plan de adopción sin forks GEN: [plan-partes-adopcion-sdk-sin-fork-gen.md](./plan-partes-adopcion-sdk-sin-fork-gen.md).

## Versiones objetivo (oleada grid i18n + menú scaffold 0.1.13)

| Paquete | Objetivo | Registry |
|---------|----------|----------|
| `paqsuite/laravel-core` | **1.3.13-beta.1** (oleada GEN-35) | Cloudsmith `paqsystems/paqsuite-sdk` |
| `@paqsuite/react-core` | **2.4.24-beta.1** (`registerGridI18nResources`, menú/shell alineado create-app) | Cloudsmith `npm.cloudsmith.io/paqsystems/paqsuite-sdk` |
| `@paqsuite/create-app` (scaffold, diff obligatorio) | según oleada Framework | Cloudsmith (no es dep runtime de Partes) |

## Versiones instaladas en repo (deploy — default)

| Paquete | Pin / lock | Nota |
|---------|------------|------|
| `paqsuite/laravel-core` | **1.3.13-beta.1** | Cloudsmith en `backend/composer.json` (lock). Bump con `composer update` y `CLOUDSMITH_READ_TOKEN`. |
| `@paqsuite/react-core` | **2.4.24-beta.1** | Cloudsmith; `frontend/package-lock.json` resuelve tarball `npm.cloudsmith.io` |

Vercel: `scripts/vercel-install.sh` + `CLOUDSMITH_READ_TOKEN`. Vite **no** alias al monorepo salvo `PAQ_REPO_LAB=1`.

## Modo repo-lab (opcional, local)

Ver `frontend/MODO-REPO-LAB.md`. No commitear `file:` ni Composer `path` en ramas de release; volver a pins registry antes del PR.

## Backend

```bash
cd backend
# CLOUDSMITH_READ_TOKEN + auth.json efímero / X-API-KEY (ver adopcion-gen-35-cloudsmith.md)
composer update paqsuite/laravel-core
composer show paqsuite/laravel-core
```

## Frontend

```bash
cd frontend
# Modo registry (release empaquetado / Cloudsmith):
npm install @paqsuite/react-core@2.4.24-beta.1
npm list @paqsuite/react-core

# Vercel: CLOUDSMITH_READ_TOKEN (docs/06-operacion/adopcion-gen-35-cloudsmith.md).
#   cd frontend && .\scripts\refresh-react-core-lock.ps1
```

Tras instalar react-core ≥ 2.4.13: diff `node_modules/@paqsuite/react-core/src/index.ts` vs template `@paqsuite/create-app@0.1.13` (`ShellPage`, `i18n.ts`).

## Tras el bump

1. Commit `composer.lock` / `package-lock.json` (+ pins si cambiaron).
2. Fase 1: `registerGridI18nResources(i18n)` en `frontend/src/i18n/i18n.ts` (regla **43**).
3. Smoke: health, login, cambio idioma → menú contextual pie de grilla traducido.
4. Redeploy Forge + build FE (builder con red a `srv-pq`).

Detalle deploy: [`deploy-sdk-package-repos.md`](./deploy-sdk-package-repos.md).
