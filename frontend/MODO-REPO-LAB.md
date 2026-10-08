# Modo repo-lab (SDK Framework local)

Desarrollo con el monorepo hermano `PaqSuite-IA-FRAMEWORK` (misma carpeta `C:\Programacion`).

| Capa | Configuración actual |
|------|----------------------|
| **FE** | **Deploy (default en repo):** pin Verdaccio en `package.json` (ej. `"@paqsuite/react-core": "2.4.22"`), `.npmrc` + `VERDACCIO_AUTH_TOKEN` para install; Vite sin alias al monorepo. **Lab opcional:** `file:../../PaqSuite-IA-FRAMEWORK/packages/js/react-core` + `PAQ_REPO_LAB=1` en `frontend/.env` (no commitear `file:` en ramas de release). |
| **BE** | Satis en `composer.json`; para lab PHP usar `PaqSuite-IA-FRAMEWORK\tools\sdk\sdk-link.ps1` (no commitear path en `composer.json`) |

Tras cambiar código en `packages/js/react-core`: `npm install` en `frontend/` y `npm run dev`.

**Antes de PR a `develop` / deploy Vercel:** volver el pin a Verdaccio (ej. `"@paqsuite/react-core": "2.4.22"`) y `npm install`. Ver `MANUAL-DEL-PROGRAMADOR.md` §5.5 en el repo Framework.

SoT: `docs/01-arquitectura/deploy-sdk-package-repos.md` § «Modo repo-lab local».
