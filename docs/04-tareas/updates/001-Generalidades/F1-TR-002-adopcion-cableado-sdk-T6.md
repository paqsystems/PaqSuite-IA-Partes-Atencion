# F1 — Tests TR-002 T6 (smoke + envelope + GEN-06 residual)

Fecha: 2026-10-11

| Campo | Valor |
|-------|--------|
| **Alcance** | AC-ENV-01 tests, AC-ADM-06 residual forks, AC-SMK-01 empresas SDK ES/EN/FR, pin gap PA-03/04 |
| **Resultado** | **Aprobado** (con obs: PA-03/04/14 bloqueados por pin) |

| Suite | Resultado |
|-------|-----------|
| `php vendor/bin/phpunit tests/Unit/ApiEnvelopeTest.php tests/Feature/ApiV1AuthAndSystemTest.php` | **OK** (7 tests) |
| Vitest `gen06HostWiring.test.ts` + `UsuariosAdminPage.test.tsx` + `i18nDxLocaleBoot.test.ts` + `shellI18n.test.ts` | **OK** (10 tests) |
| Playwright `partes-admin-empresas-sdk-smoke.spec.ts` | **OK** (ES / EN / FR) |
| Playwright `partes-admin-usuarios-form-error.spec.ts` | **OK** (CC #5) |
