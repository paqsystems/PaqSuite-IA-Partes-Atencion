# Ritual — bump SDK PaqSuite (Partes)

Igual que actualizar `laravel/framework` o `devextreme`: cambiar el pin, regenerar lock, smoke.

Plan de adopción sin forks GEN: [plan-partes-adopcion-sdk-sin-fork-gen.md](./plan-partes-adopcion-sdk-sin-fork-gen.md).

## Versiones objetivo (oleada grid i18n + menú scaffold 0.1.13)

| Paquete | Objetivo | Registry |
|---------|----------|----------|
| `paqsuite/laravel-core` | **1.3.8** (refresh catálogo; sin cambio contrato Partes esperado) | Satis `http://100.110.69.93/satis` |
| `@paqsuite/react-core` | **≥ 2.4.13** (`registerGridI18nResources`, menú/shell alineado create-app **0.1.13**) | Verdaccio `http://100.110.69.93:4873` |
| `@paqsuite/create-app` (scaffold, diff obligatorio) | **0.1.13** | Verdaccio (no es dep runtime de Partes) |

## Versiones instaladas en repo (2026-09-23 — Fase 0 en curso)

| Paquete | Pin / lock | Nota |
|---------|------------|------|
| `paqsuite/laravel-core` | `^1.3.7` → lock **1.3.7** | **1.3.8** aún no listado en Satis |
| `@paqsuite/react-core` | `^2.4.12` → lock **2.4.12** | **2.4.13** no publicada en Verdaccio (`npm view` → `ETARGET`) |
| Vercel tarball | `frontend/vendor/paqsuite-react-core-2.4.12.tgz` | Actualizar a 2.4.13+ tras publicación + `vercel-install.sh` |

**Bloqueo Fase 0:** publicar en registry la oleada Framework antes de `registerGridI18nResources` (Fase 1.1).

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
# .npmrc: @paqsuite → Verdaccio
npm install @paqsuite/react-core@2.4.13   # o última ≥ 2.4.13 con grid i18n
npm list @paqsuite/react-core

# Vercel (sin Verdaccio):
npm pack @paqsuite/react-core --pack-destination vendor
# Renombrar tarball y actualizar frontend/scripts/vercel-install.sh
```

Tras instalar react-core ≥ 2.4.13: diff `node_modules/@paqsuite/react-core/src/index.ts` vs template `@paqsuite/create-app@0.1.13` (`ShellPage`, `i18n.ts`).

## Tras el bump

1. Commit `composer.lock` / `package-lock.json` (+ pins si cambiaron).
2. Fase 1: `registerGridI18nResources(i18n)` en `frontend/src/i18n/i18n.ts` (regla **43**).
3. Smoke: health, login, cambio idioma → menú contextual pie de grilla traducido.
4. Redeploy Forge + build FE (builder con red a `srv-pq`).

Detalle deploy: [`deploy-sdk-package-repos.md`](./deploy-sdk-package-repos.md).
