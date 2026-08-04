---
specId: SPEC-003
titulo: Maestros y catálogos de Partes
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-08-01
openSpec: docs/05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md
---

# Maestros y catálogos de Partes

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

Desde **Archivos** administrás los catálogos que alimentan la carga de partes: asistentes, clientes, tipos de cliente, tipos de tarea y la asignación de tipos por cliente. También podés vincular o revocar el acceso de login de un cliente. El perfil **cliente** no administra maestros.

## Funcionamiento

### Alta o edición de un catálogo

1. Menú **Archivos** → elegí el catálogo (Asistentes, Clientes, Tipos…).
2. Revisá el listado (código y descripción/nombre).
3. Usá **Alta** o **Editar**; se abre una ventana sobre el listado.
4. Completá los campos y guardá.
5. Para dejar de usar un registro sin perder historial, preferí **Inhabilitar**.

### Dar o quitar acceso a un cliente

1. Abrí el cliente en Archivos.
2. Asociá un usuario del sistema existente (habilitar acceso) o revocá el vínculo.
3. El cliente como entidad de catálogo sigue existiendo aunque no tenga login.

### Referencias ERP del cliente

En el alta/edición de un cliente podés completar, de forma opcional, **Erp Cliente** y **Erp Articulo** (hasta 15 caracteres cada uno). Se usan para cruzar los informes del módulo con el sistema de facturación (ERP); no son obligatorios y se ven también en el listado.

### Tipos de tarea y asignaciones

1. Mantener tipos (incluye el genérico y el marcado como **default**).
2. Asignar a un cliente solo tipos **no genéricos**.
3. Siempre debe quedar un tipo default usable; no inhabilites el default sin designar otro.

## Particularidades

- Preferí **inhabilitar** antes que eliminar si hay partes históricas.
- Tipos genéricos: disponibles para todos; **no** se asignan cliente a cliente.
- El alta de usuarios de login se hace en **Seguridad**, no desde el maestro Partes.
- **No disponible en la app móvil.**

### Límites / cupos visibles al usuario

- Un solo tipo de tarea marcado como default a la vez.
- Un usuario del sistema no puede estar vinculado a dos perfiles Partes a la vez (asistente y cliente).

### Web vs mobile

| Tema | Web | Mobile |
|------|-----|--------|
| Archivos / maestros | Sí | No (excluido) |

## Condiciones de uso

- Permisos / roles: ítems de menú Archivos + permiso de administración (típico: supervisor / admin).
- Cliente funcional: **no** ve ni opera Archivos.
- Selectores de carga solo ofrecen registros activos y habilitados.

## Errores de validación

| Qué ve el usuario (mensaje o síntoma) | Código / clave i18n (si existe) | Causa habitual | Qué hacer |
|---------------------------------------|--------------------------------|----------------|-----------|
| El código es obligatorio | `partes.maestros.codeRequired` | Código vacío | Completar código |
| Ya existe un registro con ese código | `partes.maestros.codeDuplicate` | Código repetido | Usar otro código |
| Debe seleccionar un usuario Framework | `partes.maestros.userIdRequired` | Falta usuario de login | Elegir usuario del sistema |
| Debe indicar el cliente | `partes.maestros.clienteIdRequired` | Falta cliente | Seleccionar cliente |
| Erp Cliente / Erp Articulo supera el largo permitido | `partes.maestros.validationFailed` | Más de 15 caracteres | Acortar el valor a 15 caracteres o menos |

## Errores de lógica

| Qué ve el usuario | Código / clave | Regla de negocio | Qué hacer |
|-------------------|----------------|------------------|-----------|
| No tiene permiso para administrar maestros | `partes.maestros.forbidden` | Sin permiso ABM | Pedir permiso o usar otro usuario |
| Usuario ya vinculado a otro perfil Partes | `partes.maestros.userIdExclusive` | Exclusividad asistente/cliente | Elegir otro usuario o liberar el vínculo |
| No se puede asignar un tipo genérico a un cliente | `partes.maestros.tipoGenericoNoAsignable` | Genéricos no se asignan | Elegir un tipo no genérico |
| No se puede inhabilitar el tipo default | `partes.maestros.tipoDefaultNoInhabilitar` | Debe haber default usable | Marcar otro default primero |
| No se puede eliminar: tiene referencias | `partes.maestros.hasReferences` / `partes.maestros.deleteConReferencias` | Historial de tareas | Inhabilitar en lugar de borrar |
| Registro no encontrado | `partes.maestros.notFound` | Ya no existe o fue quitado | Refrescar el listado |

## Errores técnicos posibles

| Qué ve el usuario | Código / HTTP (si aplica) | Causa posible | Qué hacer / a quién escalar |
|-------------------|---------------------------|---------------|------------------------------|
| Error de conexión | `infra.transport` | Red o servidor | Reintentar; soporte si persiste |
| Error inesperado | `infra.unexpected` | Fallo interno | Reportar a soporte |

## Preguntas frecuentes

### ¿Puedo borrar un cliente que ya tiene partes?

No conviene / el sistema lo impide si hay referencias. Inhabilitalo.

### ¿Un cliente sin login es válido?

Sí: puede figurar en catálogo y en partes; solo no ingresa a la aplicación.

### ¿Por qué no veo Archivos en el celular?

Los maestros son solo web.

### ¿Para qué sirven Erp Cliente y Erp Articulo?

Son referencias opcionales al código del cliente y del artículo en el sistema ERP, para poder cruzar información en los informes de facturación. No afectan la operación normal del módulo si se dejan vacíos.
