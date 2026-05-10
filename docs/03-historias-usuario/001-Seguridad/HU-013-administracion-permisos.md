# HU-013 – Administración de permisos (asignaciones)

## Épica

001 – Seguridad y Acceso

## Alcance de instalación

Esta historia aplica a despliegue **MONO** (mono-empresa): **no existe tabla `PQ_Empresa` ni dimensión empresa** en el modelo de seguridad. Las asignaciones son **usuario + rol** únicamente.

## Clasificación

MUST-HAVE

## Rol

Administrador del sistema

## Narrativa

Como administrador quiero **asignar roles a usuarios** mediante la tabla de permisos para controlar qué puede hacer cada usuario en el sistema.

## Criterios de aceptación

- El administrador puede listar las asignaciones de la tabla `Pq_Permiso` (o nomenclatura homóloga acordada en implementación).
- Cada asignación vincula **usuario + rol** (sin empresa).
- El listado permite filtrar por **usuario** y **rol**.
- El administrador puede crear una asignación: seleccionar **usuario** y **rol**.
- La combinación **(IDUsuario, IDRol)** debe ser **única** (un mismo usuario no puede tener duplicada la misma asignación de rol).
- El administrador puede editar una asignación (cambiar el **rol** asociado al usuario, según reglas de producto).
- El administrador puede eliminar una asignación (quitar ese rol al usuario).
- Un usuario **sin ninguna asignación** en `Pq_Permiso` no puede acceder al sistema tras el login (o recibe bloqueo coherente con la política de seguridad).
- Se validan que **usuario** y **rol** existan antes de crear o actualizar.

## Tabla involucrada

- `Pq_Permiso`: en MONO almacena la **asignación usuario–rol**. Clave lógica recomendada: **(IDUsuario, IDRol)** única.  
  *Nota:* Si en un legado físico persistiera columna `IDEmpresa`, en este producto **no** se expone en UI ni en reglas de negocio y puede mantenerse un valor constante de instalación única solo por compatibilidad de esquema, hasta normalizar el DDL.

## Reglas de negocio

- Solo **administradores** pueden gestionar permisos (asignaciones usuario–rol).
- Un usuario puede tener **varios roles** (varias filas en `Pq_Permiso`), si el producto lo permite; la capacidad efectiva resulta de la **unión** de los derechos definidos en los **atributos por menú** de cada rol (u homólogo según HU afines).

## Dependencias

- HU-001 (Login)
- HU-010 (Usuarios), HU-012 (Roles)
- Tablas `users`, `Pq_Rol`, `Pq_Permiso`

## Especificación (Open-Spec)

- [SPEC-013 – Administración de permisos (asignaciones usuario–rol)](../../05-open-spec/001-Seguridad/SPEC-013-administracion-permisos.md)
