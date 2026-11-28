---
specId: SPEC-014
titulo: Sincronización de maestros con otros sistemas
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-11-16
openSpec: docs/05-open-spec/100-SistemaPartes/SPEC-014-apis-importar-datos-maestros.md
---

# Sincronización de maestros con otros sistemas

> Manual de usuario — corpus Asistente IA. Sin contratos HTTP ni credenciales técnicas.

## Resumen

Además del ABM en **Archivos**, la instalación puede ofrecer un **canal de integración** para que un sistema externo (ERP, planilla propia, etc.) **cree o actualice** los mismos maestros: clientes, asistentes, tipos de cliente, tipos de tarea y asignaciones cliente–tipo. Ese canal **no tiene pantalla** en el portal: lo usan equipos de sistemas con usuario y permiso de integración.

## Funcionamiento

### Quién opera

- **No** es para asistentes ni clientes del día a día.
- Lo ejecuta **integración / soporte** con credenciales de API y un permiso dedicado distinto del ABM de Archivos.
- Los supervisores que cargan en Archivos **no necesitan** este canal; convive con la operatoria manual.

### Qué puede sincronizar

Los mismos objetos que en [SPEC-003](./SPEC-003-maestros-y-catalogos.md):

- Clientes
- Asistentes
- Tipos de cliente
- Tipos de tarea
- Asignaciones de tipos por cliente

Las reglas de negocio (códigos únicos, tipos default, inhabilitados, etc.) son las del ABM: una fila inválida se rechaza y el resto del lote puede guardarse según el informe de la respuesta.

### Qué no hace

- **No** importa partes ni tareas del día (eso es carga diaria o importación Excel en web).
- **No** crea usuarios de login automáticamente salvo lo que el contrato de integración defina para asistentes/clientes; el vínculo cliente–usuario sigue pudiendo hacerse desde Archivos.
- **No** está disponible en la **app móvil**.

## Particularidades

- El sistema origen debe enviar los maestros en un **orden lógico** (por ejemplo tipos antes que clientes); si falta una referencia, la fila correspondiente falla.
- Reenviar los mismos datos no debería duplicar registros si el código de integración es estable (comportamiento de actualización).

## Condiciones de uso

- Contrato y ejemplos en la documentación **OpenAPI** del backend (`/api/documentation`), sección de integración de maestros.
- El administrador debe asignar el permiso de integración y proveer URL del API, tenant (`X-Paq-Cliente`) y política de tokens.

## Errores posibles en este proceso

No hay pantalla de usuario: los rechazos se ven en el **informe de la integración** (cantidad aceptada/rechazada y detalle por fila). Escalá a soporte técnico con el mensaje y el código de fila que devuelva el sistema origen.

Errores de sesión o conexión: **P00** en [CATALOGO-ERRORES.md](./CATALOGO-ERRORES.md).

## Preguntas frecuentes

### ¿Reemplaza Archivos?

No. Archivos sigue siendo la vía humana; la API es para cargas masivas o sincronización con ERP.

### ¿Dónde pido el detalle técnico?

Al administrador: URL OpenAPI y permiso `partes.integracion.maestros` (nombre visible puede variar en su instalación).