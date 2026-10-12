# Nota CC Preview — 08/10/2026 (empresas i18n, código rol, MinutosWeb)

Hallazgos en deploy **develop Preview** y correcciones en este host (+ docs Framework).

## 1) Tooltip Inactividad web (`MinutosWeb`)

- **Síntoma:** tooltip sin aclarar que **0** no corta la sesión.
- **Fix:** i18n `parametros.Auth.MinutosWeb.tooltip` en `frontend/src/i18n/locales/{es,en,pt,fr,it}/common.json`.
- Framework: seed `AuthParametrosSeeder` alineado (tras bump `laravel-core`).

## 2) ABM empresas — claves i18n crudas

- **Síntoma:** modal/listado con `admin.empresas.*` / `admin.empresas.form.*` sin traducir.
- **Causa real (react-core 2.4.17):** `EmpresasAdminPage` del SDK hace `const translate = t ?? ((key) => key)`. Sin prop `t`, **nunca** consulta i18next; el fallback en `i18n.ts` solo no alcanza.
- **Fix host (definitivo):**
  - Pasar `t={translateEmpresasAdminSdk}` en `EmpresasAdminPage.tsx` (mismo patrón que `RolesAdminPage` / `SecurityRolesPage`).
  - Claves SDK en `locales/*/common.json` + `empresasAdminHostFallback` + fallback ES en `securityEmpresasSdkI18n.ts`.
  - Registrar `appearancePaqsuiteI18nCatalogs` del SDK cuando exista.
- **No confundir** con `i18nNamespace="common"`: esa prop **no existe** en el SDK 2.4.17 y se ignoraba.

## 3) Permisos bulk — código de rol vacío

- **Síntoma:** grilla Roles columna Código vacía; SelectBox `undefined — {nombre}`.
- **Causa:** `SpRolAdminRepository::mapRow` omitía `codigo` (el SP sí lo devolvía).
- **Fix:** `mapRow` incluye `codigo` + `activo`; FE normaliza al cargar y `formatRol` sin `undefined`.
- Ver CC Framework: `docs/00-ControlCalidad/CC-Partes-permisos-bulk-codigo-rol-host.md`.

## Smoke post-deploy Preview

- [ ] Parámetros Auth → hint Inactividad: menciona valor **0**.
- [ ] `/admin/empresas` → Editar: títulos/labels traducidos (no claves).
- [ ] `/admin/permisos` → + Usuario → grilla Roles: columna Código poblada.
- [ ] Bulk + Perfil → SelectBox `CODIGO — Nombre` sin `undefined`.
