# Verificación del agente - TR-012 PA-01 (path Composer)

Fecha: 2026-10-11 · Paso **F1** (antes de F / openspec-05)

TR/SPEC/HU: familia [TR-012](./TR-012-adopcion-gen-35-cloudsmith.md) (delta unificado 11/10/2026)  
F: [F-TR-012-PA-01](./F-TR-012-adopcion-gen-35-cloudsmith-PA-01.md)

## Resultado

- **Aprobado con observaciones**

Artefactos en disco cumplen CA-CS-11…14. No hay evidencia de deploy Forge. El script bash completo **no** se ejecutó (mutaría `composer.json` local).

## Evidencia revisada

| AC | Evidencia | Estado |
|----|-----------|--------|
| CA-CS-11 | `backend/composer.json`: un solo repo Composer `https://composer.cloudsmith.io/paqsystems/paqsuite-sdk/`. Grep `type: path` / `paqsuite-local` / Satis en json+lock: sin matches. | OK |
| CA-CS-12 | `composer.lock` `paqsuite/laravel-core` **1.3.13-beta.1**, `dist.type=zip`, URL `https://dl.cloudsmith.io/basic/paqsystems/paqsuite-sdk/composer/files/paqsuite-laravel-core/1.3.13-beta.1/…zip`. PHP: json/lock **sin** slug de entitlement. | OK |
| CA-CS-13 | Instructivo: lab path solo local (`sdk-link.ps1`). `frontend/MODO-REPO-LAB.md` y `deploy-sdk-package-repos.md`: no commitear path. | OK |
| CA-CS-14 | Lógica PHP del script: checkout actual exit **0**; `composer.json` con path → **4**; Satis → **6**; lock path → **2**. Token ausente → exit **1** (leído en script, no corrido bash). | OK (PHP; no bash) |

Otros:

- `backend/.gitignore` incluye `auth.json`.
- `composer show paqsuite/laravel-core` en esta máquina: **1.3.13-beta.1**.
- FE / npm fuera de alcance del delta (ya Cloudsmith).
- Sin migraciones, UI, SP, OpenAPI.

## Hallazgos críticos

- Ninguno que impida el contrato Cloudsmith en git (`composer.json` + lock `/basic/`).

## Advertencias

1. **`forge-composer-install.sh` completo no se ejecutó.** Mutaría `composer.json` y haría `composer install --no-dev`. Solo se reprodujo el bloque PHP de validación.
2. **`composer show` local** puede listar URL de entitlement del *install*. El **lock** está sanitizado a `/basic/`.
3. **Deploy Forge no corrido.** El script reescribe el repo a `dl.cloudsmith.io/<token>/…` porque `composer.cloudsmith.io` respondió **404** en Parte D.
4. PHPUnit / Vitest / Playwright **no** se ejecutaron (ops, sin UI).
5. `backend/auth.json` existe en disco (gitignored).

## Tests

| Qué | Evidencia |
|-----|-----------|
| Validación PHP Forge (actual / path / Satis / lock path) | exit 0 / 4 / 6 / 2 |
| `composer show paqsuite/laravel-core` | 1.3.13-beta.1 |
| `php artisan test` / Vitest / Playwright | No ejecutado |
| Script bash Forge E2E / Deploy Forge | No ejecutado |

## Pendientes

- Smoke Forge post-deploy (F lo deja como observación).
- Commit/PR (no autorizado en F1/F/I).

## Recomendación final

Consumo Composer **sin path** verificado en disco; rechazo path/Satis verificado en PHP. F **Aprobado con observaciones**.
