# Ritual — bump SDK PaqSuite (Partes)

Igual que actualizar `laravel/framework` o `devextreme`: cambiar el pin, regenerar lock, smoke.

Plan de adopción sin forks GEN: [plan-partes-adopcion-sdk-sin-fork-gen.md](./plan-partes-adopcion-sdk-sin-fork-gen.md).

## Versiones objetivo (oleada grid i18n + menú scaffold 0.1.13)

| Paquete | Objetivo | Registry |
|---------|----------|----------|
| `paqsuite/laravel-core` | **1.3.8** (refresh catálogo; sin cambio contrato Partes esperado) | Satis `http://100.110.69.93/satis` |
| `@paqsuite/react-core` | **≥ 2.4.13** (`registerGridI18nResources`, menú/shell alineado create-app **0.1.13**) | Verdaccio `http://100.110.69.93:4873` |
| `@paqsuite/create-app` (scaffold, diff obligatorio) | **0.1.13** | Verdaccio (no es dep runtime de Partes) |

## Versiones instaladas en repo (2026-09-23 — repo-lab)

| Paquete | Pin / lock | Nota |
|---------|------------|------|
| `paqsuite/laravel-core` | `1.3.8` → lock **1.3.8** | Resolución local por Composer `path` |
| `@paqsuite/react-core` | `file:../../PaqSuite-IA-FRAMEWORK/packages/js/react-core` → lock **2.4.14** | Resolución local por dependencia `file:` |
| Vercel tarball | No actualizado en modo repo-lab | Requiere un release empaquetado separado |

## Modo repo-lab (Fase 0 local)

Para trabajar sin depender de Tailscale, Verdaccio ni Satis, el host puede resolver
el checkout sibling `C:\Programacion\PaqSuite-IA-FRAMEWORK` directamente:

- Frontend: `file:../../PaqSuite-IA-FRAMEWORK/packages/js/react-core`.
- Backend: repositorio Composer `path` a `../../PaqSuite-IA-FRAMEWORK/packages/php/laravel-core`.

Este modo es para desarrollo/lab local. No es portable a Vercel ni a un builder que
no tenga ambos checkouts; el release empaquetado debe volver a usar artefactos
versionados y actualizar el script de instalación correspondiente.

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
