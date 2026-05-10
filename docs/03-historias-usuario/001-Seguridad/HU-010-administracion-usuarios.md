# HU-010 – Administración de usuarios

## Épica
001 – Seguridad y Acceso

## Clasificación
MUST-HAVE

## Rol
Administrador del sistema

## Narrativa

Como administrador quiero gestionar los usuarios del sistema (alta, edición, baja) para controlar quién puede acceder y con qué permisos.

## Criterios de aceptación

- El administrador puede listar usuarios de la tabla `users`.
- El listado permite filtrar por código, nombre, email, activo, inhabilitado.
- El administrador puede crear un usuario: código, nombre, email, contraseña inicial, supervisor, activo, inhabilitado.
- El código debe ser único.
- El email debe ser único.
- El administrador puede editar un usuario existente (excepto código si es clave de integración).
- El administrador puede inhabilitar (soft) un usuario sin eliminarlo.
- Un usuario inhabilitado no puede acceder al sistema.
- Los permisos (Pq_Permiso) se gestionan por separado (HU-013).
- Se validan campos obligatorios y formatos (email, longitud mínima contraseña).

## Tabla involucrada

- `USERS`: id, codigo, name, email, password_hash, first_login, supervisor, activo, inhabilitado, token, created_at, updated_at

## Reglas de negocio

- Solo usuarios con rol de administrador pueden acceder a esta funcionalidad.
- No se elimina físicamente; se usa `inhabilitado = true` para desactivar.

## Dependencias

- HU-001 (Login)
- Tabla `users` creada

## Especificación (Open-Spec)

- [SPEC-010 – Administración de usuarios](../../05-open-spec/001-Seguridad/SPEC-010-administracion-usuarios.md)

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema users
