# F1 — Tests TR-002 PA-03 / PA-04 / PA-14

Fecha: 2026-10-12 (reejecución F1)

| Campo | Valor |
|-------|--------|
| **Alcance** | Pin `@paqsuite/react-core` **2.4.25-beta.1**; wrappers usuarios/permisos; i18n SDK; aliases API `name`/`userId`; E2E CC#5 |
| **Resultado** | **Aprobado** |

| Suite | Resultado |
|-------|-----------|
| PHPUnit `tests/Feature/Admin/ApiV1AdminSeguridadTest.php` | **OK** (12 tests, 101 assertions) |
| Vitest `gen06HostWiring` + `UsuariosAdminPage` + `hostGenCatalogGuard` + `i18nDxLocaleBoot` | **OK** (10 tests) |
| Playwright `partes-admin-usuarios-form-error.spec.ts` | **OK** (error en Popup SDK) |
| Playwright `partes-admin-empresas-sdk-smoke.spec.ts` | **OK** (ES / EN / FR) |

**testids SDK (vs SPEC-001 host):** alta `admin.usuarios.create`, formulario `admin.usuarios.form`, guardar `admin.usuarios.save`. Error de modal: **`adminUsuariosFormError`** (CC #5).
