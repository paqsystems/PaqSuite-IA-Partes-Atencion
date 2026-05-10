# HU-012 – Administración de roles

## Épica
001 – Seguridad y Acceso

## Clasificación
MUST-HAVE

## Rol
Administrador del sistema

## Narrativa

Como administrador quiero gestionar los roles del sistema para definir conjuntos de permisos reutilizables que se **asignan a usuarios** mediante `Pq_Permiso` (**MONO:** sin dimensión empresa).

## Criterios de aceptación

- El administrador puede listar roles de la tabla `Pq_Rol`.
- El listado muestra: NombreRol, DescripcionRol, AccesoTotal.
- El administrador puede crear un rol: NombreRol, DescripcionRol, AccesoTotal.
- AccesoTotal = true indica que el rol tiene acceso a todas las opciones (supervisor).
- El administrador puede editar un rol existente.
- El administrador puede eliminar un rol solo si no tiene permisos asignados (o se define política de cascada).
- Los atributos de rol (PQ_RolAtributo) se gestionan por separado (HU-014).

## Tabla involucrada

- `Pq_Rol`: IDRol, NombreRol, DescripcionRol, AccesoTotal

## Reglas de negocio

- Solo administradores pueden gestionar roles.
- Un rol con AccesoTotal no requiere permisos granulares en PQ_RolAtributo.

## Dependencias

- HU-001 (Login)
- Tabla `Pq_Rol` creada

## Especificación (Open-Spec)

- [SPEC-012 – Administración de roles](../../05-open-spec/001-Seguridad/SPEC-012-administracion-roles.md)

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema Pq_Rol
