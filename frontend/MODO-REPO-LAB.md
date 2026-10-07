# Modo repo-lab (SDK Framework local)

Desarrollo con el monorepo hermano `PaqSuite-IA-FRAMEWORK` (misma carpeta `C:\Programacion`).

| Capa | Configuración actual |
|------|----------------------|
| **FE** | Pin registry en `package.json` (ej. `"@paqsuite/react-core": "2.4.16"`). Para lab: `file:../../PaqSuite-IA-FRAMEWORK/...` + `PAQ_REPO_LAB=1` al correr Vite. **Default (deploy):** sin alias; `node_modules` desde Verdaccio (`.npmrc` + `scripts/vercel-install.sh`). |
| **BE** | Satis en `composer.json`; para lab PHP usar `PaqSuite-IA-FRAMEWORK\tools\sdk\sdk-link.ps1` (no commitear path en `composer.json`) |

Tras cambiar código en `packages/js/react-core`: `npm install` en `frontend/` y `npm run dev`.

**Antes de PR a `develop` / deploy Vercel:** volver el pin a Verdaccio (ej. `"@paqsuite/react-core": "2.4.16"`) y `npm install`. Ver `MANUAL-DEL-PROGRAMADOR.md` §5.5 en el repo Framework.

SoT: `docs/01-arquitectura/deploy-sdk-package-repos.md` § «Modo repo-lab local».
