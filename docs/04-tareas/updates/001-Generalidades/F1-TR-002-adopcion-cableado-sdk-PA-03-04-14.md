# F1 — Tests TR-002 PA-03 / PA-04 / PA-14

Fecha: 2026-10-12

| Campo | Valor |
|-------|--------|
| **Alcance** | Pin `@paqsuite/react-core` **2.4.25-beta.1**; wrappers usuarios/permisos; i18n SDK; aliases API `name`/`userId`; E2E CC#5 |
| **Resultado** | **Aprobado** |

| Suite | Resultado |
|-------|-----------|
| PHPUnit `ApiV1AdminSeguridadTest` | **OK** (12 tests; incluye contrato SDK `name`/`userId`) |
| Vitest admin GEN-06 + i18n boot | **OK** (12 tests) |
| Playwright `partes-admin-usuarios-form-error.spec.ts` | **OK** (`adminUsuariosFormError` en Popup SDK) |
| Playwright `partes-admin-empresas-sdk-smoke.spec.ts` | **OK** (ES/EN/FR) |

**testids SDK (documentados vs SPEC-001 host):** alta `admin.usuarios.create`, formulario `admin.usuarios.form`, guardar `admin.usuarios.save`. Error de modal: **`adminUsuariosFormError`** (igual que CC #5).
