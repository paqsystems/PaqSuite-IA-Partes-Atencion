# F — Verificación documental TR-012 PA-01 (openspec-05)

Fecha: 2026-10-11 · Tras F1 del update (path Composer)

| Campo | Valor |
|-------|--------|
| **TR** | [TR-012](./TR-012-adopcion-gen-35-cloudsmith.md) (delta PA-01 unificado en Parte I) |
| **HU** | [HU-012](../../03-historias-usuario/100-SistemaPartes/HU-012-adopcion-gen-35-cloudsmith.md) |
| **SPEC** | [SPEC-012](../../05-open-spec/100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md) |
| **F1** | [F1-TR-012-update](./F1-TR-012-adopcion-gen-35-cloudsmith-update.md) — Aprobado con observaciones |
| **Origen** | CC-PQ #6 · PA-01 |
| **Resultado F** | **Aprobado con observaciones** |

## Alcance contrastado

Cerrar recaída lab: `composer.json` / `composer.lock` en git **sin** repo `path` al Framework; lock `laravel-core` dist Cloudsmith; Forge rechaza path/Satis; lab path solo local. Sin UI, sin pin npm, sin GHA, sin PA-02…PA-14.

## Matriz CA (update → unificado)

| CA | Veredicto | Evidencia |
|----|-----------|-----------|
| AC-CS-11 / CA-CS-11 Sin path en `composer.json` | **OK** | Un repo Cloudsmith `composer.cloudsmith.io/paqsystems/paqsuite-sdk/`; grep path/Satis en json+lock vacío |
| AC-CS-12 / CA-CS-12 Lock Cloudsmith | **OK** | `paqsuite/laravel-core` `1.3.13-beta.1`, zip `dl.cloudsmith.io/basic/paqsystems/paqsuite-sdk/...`; json/lock sin slug entitlement |
| AC-CS-13 / CA-CS-13 Lab local | **OK** | Instructivo; `MODO-REPO-LAB.md`; `deploy-sdk-package-repos.md` |
| AC-CS-14 Forge rechaza path/Satis; sin token falla | **OK** (PHP + código) | Validación PHP: actual 0, path json 4, Satis 6, path lock 2. `exit 1` si falta token (leído, bash E2E no corrido) |
| FE / npm | **N/A** (fuera de alcance) | Sin cambio de pin |

## Código vs TR

- `backend/scripts/forge-composer-install.sh`: token (env o `.env`); rechaza path/Satis; lock debe ser Cloudsmith; `auth.json` efímero; reescribe repo a `dl.cloudsmith.io/<token>/…` en el checkout del deploy (`composer.cloudsmith.io` 404 con token entitlement).
- `composer.json` commiteado **sin** slug.

## Tests (Parte E)

No hay suite de producto para este delta. F1: PHP de validación Forge + `composer show` local **1.3.13-beta.1**. PHPUnit/Vitest/Playwright no ejecutados (ops).

## Observaciones no bloqueantes

1. Script bash Forge **completo** no se ejecutó (mutaría `composer.json` local).
2. Deploy Forge de **este** delta no corrido. CA-05 original (2026-10-09) no se revalidó.
3. `composer show` en esta PC puede listar URL de entitlement del vendor; el **lock** a git es `/basic/`.
4. `auth.json` local gitignored.

## Parte I (esta corrida)

SPEC/HU/TR-012-update → **Finalizado** y fusionados en originales. Archivos `*-update` de la familia 012 eliminados. F1 del update se conserva junto a F1 original. SPEC-002-update (PA-02…14) **no** se unifica.

## Veredicto

Implementación alineada a R-CS-PATH-01…04 y CA-CS-11…14. Ciclo del delta PA-01 cerrado. Residual: smoke Forge post-deploy de este lock; resto CC #6 en SPEC-002-update.
