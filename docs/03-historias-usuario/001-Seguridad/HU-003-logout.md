# HU-003 – Cerrar sesión

## Épica
001 – Seguridad y Acceso

## Clasificación
MUST-HAVE

## Rol
Usuario autenticado

## Narrativa

Como usuario autenticado quiero cerrar sesión de forma segura para que mi token deje de ser válido y nadie pueda usar mi sesión.

## Criterios de aceptación

- El usuario puede cerrar sesión desde el **menú de usuario** (debajo del avatar en el header).
- La opción "Cerrar sesión" está visible en el DropDownButton/Menu del usuario.
- Al seleccionar cerrar sesión, el sistema invalida el token en el backend (si aplica).
- El frontend elimina el token de localStorage/sessionStorage.
- El frontend elimina el token y cualquier estado de sesión en memoria (no hay contexto de empresa activa en MONO).
- El usuario es redirigido a la pantalla de login.
- Tras el logout, las peticiones con el token anterior reciben 401 Unauthorized.
- No se requiere confirmación para cerrar sesión (acción inmediata).

## Reglas de negocio

- El logout debe ser explícito (usuario lo solicita).
- No hay timeout automático de sesión en esta historia (puede definirse en otra).

## Dependencias

- HU-001 (Login)
- Menú de usuario implementado (M01_UserMenu_Dropdown)

## Especificación (Open-Spec)

- [SPEC-003 – Cerrar sesión](../../05-open-spec/001-Seguridad/SPEC-003-cerrar-sesion.md)

## Referencias

- `docs/design/paqsystems-main-shell-design.md` – Sección 4.2 Menú de usuario
- `docs/ui/mockups/mockup-spec-mainlayout.md` – M01_UserMenu_Dropdown
