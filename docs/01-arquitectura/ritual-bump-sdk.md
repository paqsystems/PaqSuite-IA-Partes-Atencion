# Ritual — bump SDK PaqSuite (Partes)

Igual que actualizar `laravel/framework` o `devextreme`: cambiar el pin, regenerar lock, smoke.

## Versiones actuales (`1.2.0-FINAL`)

| Paquete | Constraint | Registry |
|---------|------------|----------|
| `paqsuite/laravel-core` | `^1.3.3` (lock **1.3.3**) | Satis `http://100.110.69.93/satis` |
| `@paqsuite/react-core` | `2.2.1` | Verdaccio `http://100.110.69.93:4873` |
| `@paqsuite/create-app` (scaffold) | `0.1.8` | Verdaccio (no es dep runtime de Partes) |

## Backend

```bash
cd backend
# Tailscale activo (acceso a srv-pq)
composer update paqsuite/laravel-core
composer show paqsuite/laravel-core   # versions: * 1.3.x
```

## Frontend

```bash
cd frontend
# .npmrc ya apunta @paqsuite → Verdaccio
npm update @paqsuite/react-core
# o pin exacto en package.json y:
npm install
npm list @paqsuite/react-core
```

## Tras el bump

1. Commit `composer.lock` / `package-lock.json` (+ `package.json` / `composer.json` si cambió el pin).
2. Smoke: health, login, una capacidad GEN tocada por el changelog del SDK.
3. Redeploy Forge + build FE (builder con red a `srv-pq`).

Detalle deploy: [`deploy-sdk-package-repos.md`](./deploy-sdk-package-repos.md).
