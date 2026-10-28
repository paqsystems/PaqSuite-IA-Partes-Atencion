# RELEASE — Partes-Atención (manifiesto host GEN-34)

| Campo | Valor |
|-------|--------|
| **Proyecto** | `partesatencion` |
| **Manifiesto host** | `backend/database/manifest/partesatencion-{semver}.json` |
| **Pin SDK** | `paqsuite/laravel-core` mismo `{semver}` que el manifiesto Framework |
| **Deploy Forge** | [`scripts/forge/post-deploy.sh`](../../scripts/forge/post-deploy.sh) |

## Convenciones

| Concepto | Regla |
|----------|--------|
| `manifestId` host | `partesatencion-{semver}` |
| Prefijos `id` | `partes-migration-*`, `partes-sp-*` |
| Merge | Framework primero, host después (`manifest:validate` / CI) |
| Excluidos del manifiesto | `seed_*.sql`, `ensure_*.sql`, SP duplicados del core (mismo basename en vendor) |

## Checklist por release con SQL nuevo

1. Migration o SP en `backend/database/`.
2. `php tools/build-installation-manifest.php --semver=<release>`.
3. `php tools/validate-installation-manifest.php --semver=<release>`.
4. PR con JSON actualizado + CI verde.
5. Forge: `migrate --force` + `paqsuite:installation:update` si el pin de `laravel-core` exporta el comando (ver `post-deploy.sh`) + `deploy-sp.php`.
6. Smoke AC-04 (preview): health → login → menú → API representativa.

## Smoke AC-04 (piloto MONO directo)

| Paso | Acción |
|------|--------|
| 1 | `GET /api/v1/health` → 200 |
| 2 | Login con `X-Paq-Cliente` del entorno |
| 3 | `GET /api/v1/menu` (o ruta menú acordada) |
| 4 | `GET /api/v1/partes/tipos-cliente` (endpoint negocio con SP) |
| 5 | `php artisan paqsuite:installation:status --cliente=$PAQSUITE_CLIENTE --proyecto=partesatencion` |

## Releases

| semver / tag | manifestId | Notas |
|--------------|------------|--------|
| 1.3.17-beta.1 | partesatencion-1.3.17-beta.1 | GEN-34 fase 5 — manifiesto host v1 (57 entradas host + merge Framework) |

## Variables Forge (AC-05)

| Variable | Ejemplo preview |
|----------|-----------------|
| `PAQSUITE_CLIENTE` | `dev` (preview; mismo código que `EMPRESAS_CONEXION` / `X-Paq-Cliente`) |
| `PAQSUITE_PROYECTO` | `partesatencion` |
| `PAQSUITE_INSTALACION_RESOLVER` | `sql` |
| `PAQSUITE_HOST_MANIFEST` | (opcional) ruta absoluta al JSON si no usa default en config |

Referencia Framework (repo PaqSuite-IA-FRAMEWORK): `docs/06-operacion/RELEASE-FRAMEWORK-MANIFEST.md`, `docs/06-operacion/runbook-ciclo-vida-instalacion.md`.
