# Nota CC Preview — 08/10/2026 (empresas i18n, código rol, MinutosWeb)

Hallazgos en deploy **develop Preview** y correcciones en este host (+ docs Framework).

## 1) Tooltip Inactividad web (`MinutosWeb`)

- **Síntoma:** tooltip sin aclarar que **0** no corta la sesión.
- **Fix:** i18n `parametros.Auth.MinutosWeb.tooltip` en `frontend/src/i18n/locales/{es,en,pt,fr,it}/common.json`.
- Framework: seed `AuthParametrosSeeder` alineado (tras bump `laravel-core`).

## 2) ABM empresas — claves i18n crudas

- **Síntoma:** modal con `admin.empresas.form.*` sin traducir.
- **Causa:** pin/SDK sin `registerEmpresasAdminI18nResources` efectivo.
- **Fix host:**
  - Fallback `frontend/src/i18n/empresasAdminHostFallback.ts` registrado en `i18n.ts` (sin overwrite).
  - Luego `registerEmpresasAdminI18nResources?.(i18n, 'common')` si el SDK lo exporta.
- Framework: catálogo embebido en `useEmpresasAdminTranslate` + guía [`adopcion-gen-06-seguridad-empresas.md`](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK).

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
