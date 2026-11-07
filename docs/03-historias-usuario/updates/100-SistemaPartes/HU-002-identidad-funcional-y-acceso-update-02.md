# HU-002-update-02 – Notificar acceso en el formulario de usuario

Jira: PIAPARTES-44

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-002-update-02 |
| Título | Notificar acceso por usuario en alta y edición (ABM Usuarios) |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE (corrección CC #16) |
| Estado | Finalizado (F CC #16 · 07/11/2026) |
| Última actualización | 2026-11-07 |
| SPEC origen | [SPEC-002-update-02](../../../05-open-spec/updates/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso-update-02.md) |
| HU anterior | [HU-002-update](./HU-002-identidad-funcional-y-acceso-update.md) |
| TR relacionada(s) | [TR-002-update-02](../../../04-tareas/updates/100-SistemaPartes/TR-002-identidad-funcional-y-acceso-update-02.md) |
| Origen | `00-ControlCalidad-PQ` · **CC #16** · 07/11/2026 |

---

## Narrativa

Como operador del ABM Usuarios  
quiero que al dar de alta o editar un usuario con email pueda notificarle el acceso al sitio sin un proceso masivo ni ingresar contraseñas a mano  
para que reciba automáticamente (o cuando yo reenvíe) la URL y credenciales iniciales conforme a la política de seguridad.

---

## Alcance incluido

- Quitar notificación masiva desacoplada del ABM.
- Ícono de envío junto al campo Email (alta y edición).
- Alta sin campo contraseña; generación en servidor.
- Envío automático tras alta con email.
- Reenvío en edición mediante el ícono.
- Mismo mail y `firstLogin` que HU-002-update.

## Fuera de alcance

- Toolbar con combo de usuarios (`adminUsuariosNotify` masivo).
- Cambios al mail de novedades del proceso masivo.
- Mobile admin.

---

## Criterios de aceptación

- [x] **CA-16-01** No hay flujo masivo de selección de usuario para notificar.
- [x] **CA-16-02** Ícono junto a Email en edición; deshabilitado sin mail; `adminUsuariosNotifyEmail` (alta: auto-notify al guardar).
- [x] **CA-16-03** En alta el operador no ingresa contraseña; el sistema la genera según política.
- [x] **CA-16-04** Al guardar un alta con email válido, se envía el mail de acceso (éxito o error visible en el modal).
- [x] **CA-16-05** En edición, el ícono reenvía credenciales con `notify-access` del usuario abierto.
- [x] **CA-16-06** Mensajes i18n en 5 locales; errores de guardado/envío en el popup (regla 36); regresión de tests notify.

---

## Reglas de negocio (resumen)

| ID | Regla |
|----|--------|
| RN-16-01 | Notificar es **por usuario en contexto**, no un lote desacoplado. |
| RN-16-02 | Sin email → ícono deshabilitado; no envío automático en alta sin email. |
| RN-16-03 | Contraseña inicial siempre generada por el sistema (política parametrizada). |
| RN-16-04 | El mail no devuelve la clave en la respuesta HTTP; solo en el correo. |
