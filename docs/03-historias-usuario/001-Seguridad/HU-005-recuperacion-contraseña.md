# HU-005 – Recuperación de contraseña

## Épica
001 – Seguridad y Acceso

## Clasificación
SHOULD-HAVE

## Estado de alcance

**Estado:** En Control Calidad — pendiente cierre de [HU-005 – update](../updates/001-Seguridad/HU-005-recuperacion-contraseña-update.md) (CC PQ 06/04/2026) y [HU-005 – update 01](../updates/001-Seguridad/HU-005-recuperacion-contraseña-update-01.md) (CC PQ 18/04/2026).

## Rol
Usuario que olvidó su contraseña

## Narrativa

Como usuario que olvidé mi contraseña quiero solicitar un enlace de restablecimiento por email para poder acceder nuevamente al sistema.

## Criterios de aceptación

- En la pantalla de login existe el enlace "¿Olvidaste tu contraseña?".
- Al hacer clic, se muestra formulario para ingresar email.
- El sistema valida que el email exista en `users`.
- Si el email existe, se genera token de restablecimiento y se envía email (o se simula en desarrollo).
- Se muestra mensaje genérico: "Si el email existe, recibirás instrucciones".
- El token tiene validez limitada (ej. 60 minutos).
- El usuario recibe enlace que abre formulario: contraseña nueva, confirmación.
- Al enviar, se actualiza `users.password_hash` y se invalida el token.
- Se redirige al login con mensaje de éxito.
- Si el token expiró o es inválido, se muestra error apropiado.

## Reglas de negocio

- No revelar si el email existe o no (seguridad).
- Requiere configuración de mail (`MAIL_*` en `.env`).

## Dependencias

- HU-001 (Login)
- Configuración de envío de correos

## Especificación (Open-Spec)

- [SPEC-005 – Recuperación de contraseña](../../05-open-spec/001-Seguridad/SPEC-005-recuperacion-contrasena.md)

## Referencias

- Laravel: `password.reset`, `ForgotPasswordController`, `ResetPasswordController`
