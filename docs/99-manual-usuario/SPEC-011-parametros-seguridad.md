---
specId: SPEC-011
titulo: Parámetros generales y seguridad
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-10-05
openSpec: docs/05-open-spec/100-SistemaPartes/
---

# Parámetros generales y seguridad

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

**Parámetros** permiten ajustar comportamientos del portal (autenticación, Partes, importación, emisiones) sin tocar el código. **Seguridad** administra usuarios, roles y permisos que determinan **qué menús** ve cada persona. Ninguna de estas pantallas reemplaza el **perfil funcional** de Partes (asistente / cliente): ambas capas se combinan.

## Funcionamiento

### Parámetros

1. Menú **Parámetros** → elegí el programa (**Auth**, **Partes**, **Emission**, **ExcelImport**, etc., según lo que muestre tu instalación).
2. Revisá el listado de parámetros (nombre y valor actual en texto).
3. **Editar** abre un control acorde al tipo (número, sí/no, texto).
4. Guardá. El efecto depende del parámetro (por ejemplo tramo de duración, tope de masivo, refresco del dashboard, habilitar importación o emisiones).

Parámetros relevantes para el día a día de Partes se describen en [SPEC-001](./SPEC-001-objetos-datos-y-glosario.md).

### Seguridad (usuarios, roles, permisos)

1. Menú **Seguridad** → **Usuarios**, **Roles** o **Permisos** (según permiso).
2. **Usuarios**: altas de cuenta de login, habilitación, asignación de roles.
3. **Roles**: agrupan permisos; un usuario puede tener uno o más roles.
4. **Permisos**: definen acceso a ítems de menú (Archivos, Carga diaria, Informes, diseñador, etc.).

El alta de **asistente** o **cliente** de Partes se hace en **Archivos**; el usuario de login se crea o vincula en **Seguridad** y en el maestro Partes.

## Particularidades

- **Seguridad** y **Parámetros** completos no están en la **app móvil**.
- Un usuario puede tener permiso de menú pero **sin perfil Partes** no opera el módulo (mensaje al ingresar).
- Revocar un rol no borra tareas históricas; puede impedir entrar a pantallas.

## Condiciones de uso

- Rol de administrador o supervisor con permisos de administración.
- Cambios sensibles conviene documentarlos internamente en la organización.

## Errores posibles en este proceso

Identificador de catálogo: **P11**. Ver [CATALOGO-ERRORES.md](./CATALOGO-ERRORES.md).

| Clave | Qué hacer |
|-------|-----------|
| `roles.delete.hasPermisos` | Quitar el rol de usuarios o permisos antes de eliminar |
| Validación al editar parámetro | Corregir valor según tipo (número, sí/no) |
| **P00** | Sesión, conexión, perfil Partes |

## Preguntas frecuentes

### ¿Cambiar el tramo de 15 minutos?

En **Parámetros → Partes** (nombre visible según catálogo de su instalación).

### ¿Por qué un usuario no ve Archivos?

Le falta permiso de menú o no es supervisor/administrador; el perfil **cliente** nunca ve Archivos.

### ¿Dónde habilito importar Excel o emitir reportes?

En **Parámetros** del programa correspondiente (**ExcelImport**, **Emission**), si están disponibles en el menú.
