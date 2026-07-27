# F-GEN-02-admin — Cierre paso E (tests automatizados)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-001-02-admin-mantenimiento-roles-permisos](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md) |
| **Fecha** | 2026-06-19 |
| **Alcance** | Verificación automatizada epic admin (D1 + D) + entorno dev |
| **Detalle ejecución** | [E-GEN-02-admin-tests.md](E-GEN-02-admin-tests.md) |
| **Veredicto** | **Apto** |

## Cobertura por slice

| TR / HU | Tests automatizados |
|---------|---------------------|
| TR-GEN-02-admin-roles | `AdminSecurityFeatureTest` (CRUD, flag, 403) + E2E `roles-admin.spec.ts` |
| TR-GEN-02-admin-rol-atributos | `AdminSecurityFeatureTest` (readOnly + sync) |
| TR-GEN-02-admin-permisos | `AdminSecurityFeatureTest` (CRUD + lookup) + E2E pantalla permisos |
| TR-GEN-02-admin-permisos-bulk | `AdminSecurityFeatureTest` (batch) + E2E validación ancla |

## Resumen comandos

| Área | Comando | Resultado |
|------|---------|-----------|
| Backend Feature | `php artisan test --filter=AdminSecurityFeatureTest` | 9 passed |
| Frontend Unit | `npm run test` | 156 passed |
| Frontend E2E | `npx playwright test tests/e2e/admin-security/` | 2 passed |
| Build | `npm run build` | OK |
| Smoke API vivo | health + login + admin/roles | OK |

## Entorno dev

- Backend: `http://127.0.0.1:8000`
- Frontend: `http://127.0.0.1:5173`
- Flag: `ADMIN_SECURITY_UI_ENABLED=true`
- Rutas UI: `/admin/roles`, `/admin/permisos`

## Observaciones (no bloqueantes)

| ID | Tema |
|----|------|
| OBS-E-01 | Sin Vitest unitario dedicado admin (E2E + Feature suficientes v1) |
| OBS-E-02 | E2E batch: solo validación negativa en CI |
| OBS-E-03 | OpenAPI `/admin/*` pendiente |

## Próximo paso

QA manual en navegador (alta rol, atributos, permiso individual, batch feliz) y activación productiva cuando corresponda.






