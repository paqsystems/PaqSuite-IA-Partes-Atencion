# F1 — Tests TR-002 PA-05 / PA-06

Fecha: 2026-10-11

| Campo | Valor |
|-------|--------|
| **Alcance** | GEN-06 i18n empresas (PA-05) y roles (PA-06) |
| **Resultado** | **Aprobado** |

| Suite | Comando | Resultado |
|-------|---------|-----------|
| Vitest | `npx vitest run src/i18n/hostGenCatalogGuard.test.ts src/features/admin/security/gen06HostWiring.test.ts src/features/admin/security/EmpresasAdminPage.test.tsx` | **OK** (5 tests) |

Playwright: no corrido (sin cambio de flujo; captions GEN salen del SDK).
