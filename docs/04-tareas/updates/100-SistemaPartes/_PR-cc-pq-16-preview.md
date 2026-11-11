## Summary

- CC PQ **#16** / **TR-002-update-02**: notificación de acceso por usuario en el popup ABM Usuarios (sin toolbar masivo).
- Alta sin contraseña manual; `POST create` + encadenado `notify-access`; ícono reenvío en edición.
- F1/F: [D-VERIFICACION-CC-PQ-16-2026-11-07.md](./D-VERIFICACION-CC-PQ-16-2026-11-07.md)

## Observaciones deploy

- **Sin** migraciones, seeds ni SQL.
- **Sin** variables `.env` nuevas.
- Requiere Mail Engine operativo para smoke real de envío.

## Test plan

- [ ] Preview Vercel: build OK
- [ ] Forge `develop`: deploy OK
- [ ] `/admin/usuarios` — sin botón masivo «Notificar acceso»
- [ ] Alta con email (sin campo contraseña) → guardar
- [ ] Edición → ícono junto a mail (`adminUsuariosNotifyEmail`)

## Trazabilidad

- Jira: PIAPARTES-44
- Tests: Vitest 13 · Playwright 6 · PHPUnit 8 (ver E-CC-PQ-16)
