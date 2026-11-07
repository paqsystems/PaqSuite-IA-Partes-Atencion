# Plan de implementación - TR-002-update (Notificar acceso)

## Alcance entendido

Acción **Notificar** en ABM Usuarios (GEN-06, sin fork): genera clave según `PasswordPolicy`, persiste hash + `firstLogin=true`, envía mail con URL del sitio, usuario y clave. Endpoint `POST /api/v1/admin/usuarios/{id}/notify-access`.

## Fuentes leídas
- SPEC: `SPEC-002-identidad-funcional-y-acceso-update.md` (A1 Apto)
- HU: `HU-002-identidad-funcional-y-acceso-update.md`
- TR: `TR-002-identidad-funcional-y-acceso-update.md`
- Código: wrapper `UsuariosAdminPage`, `UsuariosController`, `SpUserAdminRepository`, `pq_sp_admin_usuarios_update`, `PasswordPolicy`, forgot/masivo vía Laravel Notification, SDK `UsuariosAdminPage` **sin** slot/prop extra

## Impacto esperado
### Base de datos
- SP host `pq_sp_admin_usuarios_update`: `@password_hash` opcional; si viene, actualiza password y `first_login=1`
- Sin cambio de esquema `users`

### Backend
- Generador de clave alineado a `PasswordPolicy`
- `POST .../notify-access` (gate `paqsuite.seguridadAdmin`)
- Mail: **Notification Laravel** (mismo camino que forgot/masivo). MailEngine GEN **no está bound** en el host y redacta `password`; no usarlo para este copy
- Envelope: 422 `admin.usuarios.notify.emailRequired`; mail fail `mailEngine.sendFailed`; sin password en `resultado`
- URL: `PasswordResetLinkBuilder::resolveSpaBaseUrl()` + `?cliente=`
- Código empresa en asunto: `X-Paq-Cliente`

### Frontend
- Toolbar host junto al wrapper SDK (gap: el SDK no expone acción extra ni consulta RO)
- Popup Notificar: selector de usuario persistido, botón `adminUsuariosNotify`, error en el modal (regla 36)
- i18n 5 locales `partes.notifyAccess.*`
- Excluido mobile

### Tests
- Feature: 422 sin email; éxito con Notification fake; `first_login`; login siguiente con `firstLogin=true`; password ausente en JSON
- Unit: generador cumple política simple/segura
- Vitest: toolbar + no fork de `UsuariosAdminPage`

### Documentación
- OpenAPI POST notify-access; TR secciones de cierre D; gap SDK

### DevOps
- Redeploy SP `pq_sp_admin_seguridad_core.sql` (manifiesto existente)
- `FRONTEND_URL` ya en `.env.example`

## Orden de trabajo
1. SP update + sqlite parity
2. Generador + servicio + endpoint + Notification
3. UI host + i18n
4. Tests
5. Cierre TR + Jira

## Riesgos
- SDK sin slot: Notificar no vive dentro del popup de alta/edición; toolbar + selector de usuario persistido (decisión TR RN-CC7-U08)
- MailEngine deprecado / no bound: Notification Laravel (decisión documentada)

## Tests a ejecutar
- `php artisan test --filter=NotifyAccess`
- `npx vitest run src/features/admin/security`

## Dudas / bloqueos
- Ninguno bloqueante. Consulta RO del SDK no existe → no se inventa vista RO.

## Confirmación de alcance
- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**
