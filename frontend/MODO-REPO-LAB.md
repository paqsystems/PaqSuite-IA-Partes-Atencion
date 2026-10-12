# Modo repo-lab (SDK Framework local)

Desarrollo con el monorepo hermano `PaqSuite-IA-FRAMEWORK` (misma carpeta `C:\Programacion`).

| Capa | Configuración actual |
|------|----------------------|
| **FE** | **Deploy (default en repo):** pin Cloudsmith en `package.json` (ej. `"@paqsuite/react-core": "2.4.24-beta.1"`), `.npmrc` + `CLOUDSMITH_READ_TOKEN` para install; Vite sin alias al monorepo. **Lab:** `PAQ_REPO_LAB=1` en `frontend/.env` (gitignored) activa alias Vite a `PaqSuite-IA-FRAMEWORK/packages/js/react-core`. Opcional `file:…` (no commitear en ramas de release). |
| **BE** | Cloudsmith en `composer.json` para deploy. **Lab:** repo `path` a `packages/php/laravel-core` (symlink). No commitear path/lock path en ramas de release; revertir con `sdk-unlink.ps1`. |

Tras cambiar código en `packages/js/react-core`: `npm install` en `frontend/` y `npm run dev`.

**Antes de PR a `develop` / deploy Vercel:** volver el pin a Cloudsmith (ej. `"@paqsuite/react-core": "2.4.24-beta.1"`) y `npm install`. Ver `MANUAL-DEL-PROGRAMADOR.md` §5.5 en el repo Framework. Instructivo: `docs/06-operacion/adopcion-gen-35-cloudsmith.md`.

SoT: `docs/01-arquitectura/deploy-sdk-package-repos.md` § «Modo repo-lab local».
