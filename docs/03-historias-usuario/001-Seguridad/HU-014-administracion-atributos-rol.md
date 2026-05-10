# HU-014 – Administración de atributos de rol

## Épica
001 – Seguridad y Acceso

## Clasificación
SHOULD-HAVE

## Rol
Administrador del sistema

## Narrativa

Como administrador quiero definir los permisos granulares de cada rol por opción de menú (Alta, Baja, Modificación, Reporte) para controlar el acceso detallado cuando el rol no tiene AccesoTotal.

## Criterios de aceptación

- El administrador puede listar los atributos de un rol (PQ_RolAtributo).
- Cada atributo vincula: rol + opción de menú + permisos (Alta, Baja, Modi, Repo).
- El administrador puede asignar permisos a un rol por cada opción de menú (pq_menus).
- Para cada opción se definen: Permiso_Alta, Permiso_Baja, Permiso_Modi, Permiso_Repo.
- Si el rol tiene AccesoTotal = true, no se requieren atributos (acceso total implícito).
- El administrador puede editar o eliminar atributos de rol.
- La combinación (IDRol, IDOpcionMenu, IDAtributo) debe ser única.

## Tabla involucrada

- `PQ_RolAtributo`: IDRol, IDOpcionMenu, IDAtributo, Permiso_Alta, Permiso_Baja, Permiso_Modi, Permiso_Repo

## Reglas de negocio

- Solo administradores pueden gestionar atributos de rol.
- Las opciones de menú provienen de `pq_menus`.
- IDAtributo suele ser un valor fijo (ej. 1) o catálogo de atributos según diseño.

## Dependencias

- HU-001 (Login)
- HU-012 (Roles)
- Tablas `Pq_Rol`, `pq_menus`, `PQ_RolAtributo`

## Especificación (Open-Spec)

- [SPEC-014 – Administración de atributos de rol](../../05-open-spec/001-Seguridad/SPEC-014-administracion-atributos-rol.md)

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema PQ_RolAtributo
