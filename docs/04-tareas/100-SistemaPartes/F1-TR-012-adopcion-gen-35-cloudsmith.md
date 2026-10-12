# Verificación del agente - TR-012

Fecha: 2026-10-09 · Paso **F1** (antes de F / openspec-05)

## Resultado

- **Aprobado con observaciones**

Install local del SDK desde Cloudsmith está evidenciado. Smoke de deploy (Forge/Vercel/login en prod) **no** se ejecutó. No afirmar cierre operativo de CA-05, CA-06 ni CA-07.

## Evidencia revisada

- SPEC-012, HU-012, TR-012 (alcance ops, sin UI).
- `backend/composer.json`: repo `https://composer.cloudsmith.io/paqsystems/paqsuite-sdk/`, pin `1.3.13-beta.1`, `secure-http: true`.
- `backend/composer.lock`: `paqsuite/laravel-core` **1.3.13-beta.1**, dist `https://dl.cloudsmith.io/basic/paqsystems/paqsuite-sdk/composer/files/...` (sin Satis).
- `composer show paqsuite/laravel-core` en esta máquina: **1.3.13-beta.1**.
- `frontend/package.json`: `@paqsuite/react-core` **2.4.24-beta.1**.
- `frontend/package-lock.json`: `resolved` `https://npm.cloudsmith.io/paqsystems/paqsuite-sdk/@paqsuite/react-core/-/react-core-2.4.24-beta.1.tgz`.
- `frontend/.npmrc`: scope Cloudsmith + `${CLOUDSMITH_READ_TOKEN}` (sin valor literal).
- `frontend/scripts/vercel-install.sh` y `refresh-react-core-lock.*`: exigen `CLOUDSMITH_READ_TOKEN`.
- `backend/scripts/forge-composer-install.sh`: exige token; lock debe contener `cloudsmith.io`; `auth.json` efímero + http-basic `dl.cloudsmith.io`.
- `backend/.gitignore` incluye `auth.json`.
- Docs: instructivo, `deploy-sdk-package-repos.md`, ritual bump, Verdaccio marcado legado, rollback en instructivo §6.
- No hay workflows GHA nuevos.
- Grep código: no `CLOUDSMITH_API_KEY`; no `_authToken=` con valor.

## Hallazgos críticos

- Ninguno que impida el origen Cloudsmith en repo (locks + manifests).

## Advertencias

1. **CA-05 / CA-06 / CA-07** (Forge `composer show`, log Vercel, login/health desplegado): no hay evidencia de corrida. El usuario cargó secretos; el agente no vio un deploy.
2. **`composer.cloudsmith.io` + `X-API-KEY`** dio 401 en esta PC; el lock se generó con URL de entitlement `dl.cloudsmith.io/<token>/...` y luego se reescribió a `/basic/` para **no** commitear el token. Forge debe usar `auth.json` http-basic a `dl.cloudsmith.io` (ya está en el script).
3. **CA-04** (`vercel-install.sh` sin token → exit 1): el script lo declara; **no** se ejecutó bash (Git Bash/WSL no disponible). Lógica leída, no corrida.
4. **`tsc -b`**: errores en `node_modules/@paqsuite/react-core` (paquete 2.4.24-beta.1). No se tomó como DoD de esta TR.
5. Vitest suite completa: 2 timeouts DX; los mismos tests aislados pasaron.
6. E2E Playwright **no** se corrió (TR: sin UI nueva).
7. `backend/auth.json` local existe (gitignored); no debe entrar al commit.

## Sugerencias

- En F, no marcar CA-05/06/07 hasta un deploy real.
- Tras el primer Forge: confirmar que `composer install` baja el zip `/basic/` con el mismo token.

## Tests

| Qué | Evidencia |
|-----|-----------|
| `php artisan test` | 104 passed, 7 skipped |
| Vitest | 142 passed; 2 timeouts en suite completa; aislados OK |
| Tests de tema / atributos rol | Ajustados al pin (IAT-79, `titulo` / `permisoAlta`) |
| Playwright | No ejecutado |
| Forge / Vercel smoke | No ejecutado |

## Pendientes

- Smoke Forge y Vercel (CA-05, CA-06).
- Login + health post-deploy (CA-07).
- Commit/PR (no autorizado en F1).
- Borrar `VERDACCIO_AUTH_TOKEN` en Vercel **después** de producción (HU R-CS-04).

## Recomendación final

Puede pasar a **F** con el mismo veredicto: implementación de **consumo SDK** verificada en disco y tests locales; **deploy no verificado**. F debe listar CA-05/06/07 como pendientes, no como cumplidos.
