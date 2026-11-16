---
specId: SPEC-012
titulo: Tareas programadas (soporte técnico)
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-11-15
procesoErrores: P13
openSpec: docs/05-open-spec/001-Generalidades/SPEC-001-13-host-adopcion-tareas-programadas.md
---

# Tareas programadas (soporte técnico)

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación, infraestructura ni codificación. No documentar componentes del SDK (manual Framework).

## Resumen

**Tareas programadas** permite a **soporte técnico o administración** definir **cuándo** debe ejecutarse una tarea automática del sistema y **revisar el resultado** (éxito, fallo u omisión). En Partes de Atención, el proceso habitual es **copia de seguridad de la base de datos** de la instalación. No es una pantalla de Partes para asistentes ni clientes.

## Funcionamiento

### Acceder

1. Menú **Parámetros** → grupo **Soporte técnico** → **Tareas programadas** (solo si tu usuario tiene permiso).
2. La pantalla tiene cuatro solapas: **Procesos**, **Definiciones**, **Ejecuciones** e **Historial**.

### Procesos

Lista los tipos de tarea que el sistema puede ejecutar. En Partes suele aparecer **copia de base de datos** (`BACKUP_DATABASE`). Sirve de catálogo de referencia; la configuración operativa se hace en **Definiciones**.

### Definiciones

1. Abrí **Definiciones**.
2. **Nueva** (o editá una existente): elegí el proceso, la **frecuencia** (diaria, semanal, etc.) y los parámetros que muestra el formulario (por ejemplo si el nombre del archivo incluye fecha).
3. Activá o desactivá la definición según corresponda. Una definición **inactiva** no corre sola en la agenda, pero en instalaciones actuales **sí** puede ejecutarse **manualmente** si tenés permiso (botón Ejecutar / Simular en la fila).
4. Guardá. El sistema calcula la **próxima corrida** cuando la definición está activa.

### Ejecuciones (monitoreo)

Muestra tareas **en curso** o recientes que aún no cerraron. Podés **ejecutar manualmente** o **simular** desde aquí (según permiso). Si una copia manual queda mucho tiempo como «en curso» pero el servicio ya terminó, usá **Liberar colgadas** (o pedí a infraestructura que lo hagan) antes de volver a lanzar.

### Historial

Lista corridas **finalizadas** con estado (exitosa, fallida, omitida), hora y **motivo** o mensaje cuando el sistema lo informa. Sirve para auditoría y para saber si la última copia programada terminó bien.

### Copia manual de base

1. En **Definiciones** o **Ejecuciones**, ejecutá la copia de base.
2. La pantalla puede responder **rápido** y dejar el estado **en curso**: es normal; el cierre lo completa el **programador del servidor** (agenda del backend), no hay que mantener el navegador abierto minutos.
3. Revisá **Historial** tras unos minutos para confirmar el resultado.

La copia **no** descarga un archivo al navegador: el destino lo configura **infraestructura** (almacenamiento autorizado por la organización).

## Particularidades

- **Solo web:** no está en la app móvil.
- **Una empresa por instalación (MONO):** no se elige otra empresa en el formulario de copia.
- Puede haber **una sola** copia en curso por instalación: si otra ya corre, la nueva puede quedar **omitida**.
- Ventanas de mantenimiento del proveedor de base pueden hacer fallar u omitir una corrida; conviene reintentar fuera de ese horario.

### Web vs mobile

| Tema | Web | Mobile |
|------|-----|--------|
| Tareas programadas | Sí (con permiso) | No |
| Copia de base | Sí | No |

## Condiciones de uso

- Permiso de menú para **Tareas programadas** (Soporte técnico).
- Perfil Partes válido (igual que el resto del portal).
- Copias automáticas requieren que en el servidor esté activa la **agenda Laravel** (`schedule:run`); eso lo configura infraestructura, no esta pantalla.

## Errores posibles en este proceso

Identificador de catálogo: **P13**. Ver [CATALOGO-ERRORES.md](./CATALOGO-ERRORES.md). Siempre pueden aparecer errores **P00** (sesión, conexión).

| Síntoma (resumen) | Qué hacer |
|-------------------|-----------|
| Historial vacío pero «debería haber corridas» | Refrescar; si persiste, escalar a soporte técnico |
| Definición activa sin «próxima corrida» | Guardar de nuevo la definición o pedir backfill operativo a infra |
| Copia **fallida** con mensaje genérico | Revisar motivo en Historial; escalar con hora y definición usada |
| Copia **omitida** | Otra copia en curso o ventana de mantenimiento; esperar y reintentar |
| Manual queda «en curso» mucho tiempo | Usar Liberar colgadas o comando operativo; no relanzar en paralelo |

## Preguntas frecuentes

### ¿Puedo bajar el `.bak` desde esta pantalla?

No. La copia se guarda en el almacenamiento que configuró la organización. Para restaurar o descargar, contactá a infraestructura.

### ¿Los asistentes ven esta pantalla?

No, salvo que tengan permiso de soporte en el menú.

### ¿Desactivar una definición impide una copia manual?

En la versión actual del producto, **podés ejecutar manualmente** aunque la definición esté inactiva; la agenda automática **sí** respeta el interruptor activo/inactivo.