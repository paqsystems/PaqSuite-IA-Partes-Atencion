---
specId: SPEC-001
titulo: Objetos, datos y glosario del módulo
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-10-05
openSpec: docs/05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md
---

# Objetos, datos y glosario del módulo

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

**Partes de Atención** registra **cuánto tiempo** dedicó cada **asistente** a cada **cliente**, clasificado por **tipo de tarea**, para consultar, supervisar y analizar esa dedicación. Los **datos maestros** (catálogos) definen quién opera, para quién se trabaja y cómo se clasifica el trabajo. El **registro de tarea** (parte) es el dato operativo central del día a día.

## Definición integral del proyecto

| Pregunta | Respuesta en lenguaje de negocio |
|----------|----------------------------------|
| ¿Para qué sirve? | Dejar trazabilidad fiable del trabajo realizado hacia clientes y proyectos de servicio. |
| ¿Quién lo usa? | Asistentes que cargan; supervisores que revisan y cierran; clientes que consultan su organización; administradores que mantienen catálogos y permisos. |
| ¿Qué es lo más importante? | La **carga diaria de tareas** (fecha, cliente, tipo, duración, observación). |
| ¿Qué no hace en esta versión? | No factura automáticamente ni reemplaza al sistema de gestión (ERP); las referencias ERP en el cliente son opcionales para cruzar informes. |
| ¿Cómo se protege la información? | Cada persona ve solo el universo que corresponde a su **perfil funcional** (asistente, supervisor o cliente), además de los permisos de menú. |

## Objetos del sistema (datos maestros y referenciales)

### Asistente

Persona interna que registra y consulta tareas.

| Dato | Significado |
|------|-------------|
| **Código** | Identificador corto único del asistente en el módulo. |
| **Nombre** | Nombre visible en listados y formularios. |
| **Usuario de login** | Cuenta con la que ingresa a la aplicación (se administra en Seguridad). |
| **Supervisor** | Si está marcado, puede ver más datos, cerrar/reabrir tareas y usar el proceso masivo. |
| **Activo / inhabilitado** | Un asistente inhabilitado no debe usarse en cargas nuevas. |
| **Correo** | Contacto de dominio (consulta en perfil; edición según política de administración). |

### Cliente

Organización para la cual se registra dedicación.

| Dato | Significado |
|------|-------------|
| **Código** | Identificador único del cliente en el módulo. |
| **Nombre** | Razón social o nombre comercial visible. |
| **Tipo de cliente** | Clasificación de negocio (segmentación; no es un permiso de seguridad). |
| **Erp Cliente** | Referencia opcional (hasta 15 caracteres) al código del cliente en el sistema de facturación, para informes. |
| **Erp Artículo** | Referencia opcional (hasta 15 caracteres) al artículo o concepto en el ERP, para informes. |
| **Acceso de usuario** | Si tiene login, puede consultar dashboard e informes solo de su organización. |
| **Activo / inhabilitado** | Cliente inhabilitado no aparece para cargas nuevas. |

### Tipo de cliente

Catálogo de clasificación de clientes (por ejemplo rubro o categoría comercial).

| Dato | Significado |
|------|-------------|
| **Código** | Identificador del tipo. |
| **Descripción** | Texto que se muestra al elegir el tipo. |
| **Activo / inhabilitado** | Tipos inhabilitados no se ofrecen en altas nuevas. |

### Tipo de tarea

Catálogo que describe la naturaleza del trabajo (soporte, desarrollo, reunión, etc.).

| Dato | Significado |
|------|-------------|
| **Código** | Identificador del tipo. |
| **Descripción** | Texto visible en carga y consultas. |
| **Genérico** | Si es genérico, cualquier cliente puede usarlo sin asignación especial. |
| **Por defecto (default)** | Tipo sugerido al abrir una tarea nueva; debe existir siempre uno usable y suele ser genérico. |
| **Activo / inhabilitado** | Tipos inhabilitados no se ofrecen en cargas nuevas. |

### Asignación tipo de tarea por cliente

Relación que habilita tipos **no genéricos** solo para clientes concretos.

| Dato | Significado |
|------|-------------|
| **Cliente** | Cliente al que se habilita el tipo. |
| **Tipo de tarea** | Tipo no genérico asignado. |

**Regla de uso:** al cargar una tarea, los tipos disponibles para un cliente son los **genéricos activos** más los **asignados** a ese cliente.

### Registro de tarea (parte / tarea)

Unidad de trabajo registrada en carga diaria o importación.

| Dato | Significado |
|------|-------------|
| **Fecha** | Día en que se realizó (o se imputa) el trabajo. |
| **Cliente** | Para quién fue el trabajo. |
| **Tipo de tarea** | Clasificación del trabajo. |
| **Duración** | Tiempo dedicado; en pantalla se muestra en **hh:mm** y a veces en horas decimales; debe respetar el **tramo** configurado (habitualmente 15 minutos) y el máximo de 24 horas. |
| **Observación** | Descripción obligatoria del trabajo realizado. |
| **Asistente propietario** | Quién realizó la tarea; el asistente común solo carga las propias; el supervisor puede indicar otro. |
| **Sin cargo** | Marca que la dedicación no se trata como trabajo facturable con cargo. |
| **Presencial** | Marca que hubo presencia física o prestación presencial. |
| **Cerrada** | La tarea fue supervisada: no se edita ni elimina en el flujo normal; un supervisor puede reabrirla. |

### Movimiento de paquete de horas

Registro que **no** es una tarea de carga diaria: representa compras o movimientos de horas prepagas del cliente. Se consulta en **Paquete de horas**, no en carga diaria ni en consultas que solo listan tareas.

| Dato | Significado |
|------|-------------|
| **Cliente** | Organización del paquete. |
| **Fecha** | Fecha del movimiento. |
| **Duración / importe según pantalla** | Aporte o consumo según el informe. |
| **Saldo** | Acumulado en la cuenta corriente del informe Paquete de horas. |

### Parámetros de Partes (referencia)

Valores que ajusta administración y afectan la operatoria visible:

| Parámetro (concepto) | Efecto para el usuario |
|------------------------|-------------------------|
| **Tramo de duración** | Paso mínimo de minutos (default 15): duraciones válidas en múltiplos. |
| **Tope proceso masivo** | Cantidad máxima de tareas por lote (si está configurado). |
| **Refresco del dashboard** | En web, segundos entre actualizaciones automáticas (0 = solo manual). |
| **Importación Excel** | Si está desactivada, no aparece la barra de plantilla/importar. |
| **Emisión de reportes** | Si está desactivada, no aparece **Emitir** en consulta detallada. |

## Glosario breve

| Término | Significado |
|---------|-------------|
| **Perfil funcional** | Rol de negocio en Partes: asistente, supervisor (asistente con flag) o cliente. |
| **Carga diaria** | Pantalla principal para alta, edición y baja de tareas en un periodo filtrado. |
| **Proceso masivo** | Cierre, reapertura o cambio de atributos en muchas tareas a la vez (solo supervisor, web). |
| **Consulta** | Lectura y análisis sin editar desde Informes o Dashboard. |
| **Captura inteligente** | Ayuda en el formulario de tarea para completar campos con texto o voz; no es el Asistente IA del avatar. |
| **Asistente IA (avatar)** | Chat de ayuda documental sobre cómo usar el sistema; no crea ni modifica partes. |

## Errores posibles en este documento

Este archivo es de referencia conceptual. Los mensajes de error aparecen al **operar** procesos; ver [CATALOGO-ERRORES.md](./CATALOGO-ERRORES.md).

## Preguntas frecuentes

### ¿Cuál es la diferencia entre tarea y paquete de horas?

La **tarea** es trabajo cargado en el día a día. El **paquete de horas** muestra también movimientos de compra o saldo de horas del cliente.

### ¿Debo completar Erp Cliente y Erp Artículo?

No son obligatorios; sirven para alinear informes con códigos del sistema de facturación.
