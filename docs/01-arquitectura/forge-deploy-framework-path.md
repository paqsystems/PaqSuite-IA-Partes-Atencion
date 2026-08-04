# Deploy Forge — backend Partes + Framework path

Fecha: 2026-08-04

## Problema

`backend/composer.json` declara:

```json
"paqsuite/laravel-core": "@dev"
```

con repository **path** a `../../PaqSuite-IA-FRAMEWORK/packages/php/laravel-core`.

Eso funciona en desarrollo local (repos hermanos bajo `Programacion/`).  
En **Laravel Forge** solo se clona Partes → Composer falla con:

`Source path "../../PaqSuite-IA-FRAMEWORK/packages/php/laravel-core" is not found`

## Solución

Antes de `composer install`, el release debe ver ese path. Script del repo:

`backend/scripts/forge-ensure-framework.sh`

1. Clona/actualiza Framework en `$HOME/PaqSuite-IA-FRAMEWORK` (rama `main` por defecto).
2. Crea symlink `…/releases/PaqSuite-IA-FRAMEWORK` → ese home.
3. Desde `backend/`, `../../PaqSuite-IA-FRAMEWORK/...` resuelve bien.
4. Composer copia el paquete a `vendor/` (`symlink: false` en `composer.json`).

### Requisitos en el server

- El site Forge debe poder clonar `paqsystems/PaqSuite-IA-FRAMEWORK` (deploy key o GitHub App con acceso al repo Framework).
- PHP / git en PATH (ya los tiene Forge).

### Variables opcionales

| Variable | Default |
|----------|---------|
| `PAQSUITE_FRAMEWORK_REPO` | `git@github.com:paqsystems/PaqSuite-IA-FRAMEWORK.git` |
| `PAQSUITE_FRAMEWORK_REF` | `main` |
| `PAQSUITE_FRAMEWORK_HOME` | `$HOME/PaqSuite-IA-FRAMEWORK` |

### Fragmento para el Deploy Script de Forge

Insertar **antes** de `composer install` (desde el root del release; ajustar si el script ya hace `cd`):

```bash
bash backend/scripts/forge-ensure-framework.sh

cd backend
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
```

Si el Root Directory del site es `backend/`, el release root es el contenido de `backend/` y el path relativo del `composer.json` **no** coincide con el layout del repo completo. En ese caso conviene:

- Root Directory vacío (repo entero), **o**
- Ajustar el script / el `url` del path (menos recomendable).

Layout esperado del release: `releases/<id>/backend/composer.json` (repo Partes completo).

## Frontend (Vercel)

Análogo: `@paqsuite/react-core` vía `file:../../PaqSuite-IA-FRAMEWORK/...`.  
Si el build Vercel falla por eso, hace falta registry npm, submodule, o clone del Framework en el install command. Ver `docs/01-arquitectura/frontend-api-base-url-y-env.md`.

## Checklist post-cambio

- [ ] Deploy key Forge → repo Framework
- [ ] Deploy Script incluye `forge-ensure-framework.sh` antes de Composer
- [ ] Redeploy backend OK
- [ ] `php artisan about` / health en Forge
