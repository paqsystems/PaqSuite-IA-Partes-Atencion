# Partes — menú i18n (GEN-07 piloto)

- Seed: `PqMenuSeeder` con `label_key` = `menu.{codigo}`.
- FE: `frontend/src/features/auth/productI18n.ts` (5 locales).
- SP catálogo: `backend/database/sp/pq_sp_admin_menu_list_enabled.sql` (manifiesto host).
- Ritual PR: `npm run test -- --run src/features/partes/partesMenuI18n.test.ts` (workflow `menu-i18n-validate.yml`).
- Deploy: tras pin laravel-core con comandos GEN-07, `php artisan paqsuite:menu-i18n:validate` en dictionary.
- Gate Forge: `PAQSUITE_MENU_I18N_INSTALL_GATE=true` solo cuando validate = 0 en el entorno objetivo.

Guía transversal: PaqSuite-IA-FRAMEWORK `docs/06-operacion/adopcion-menu-i18n.md`.
