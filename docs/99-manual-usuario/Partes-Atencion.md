# Partes de Atención — Manual de usuario

| Campo | Valor |
|-------|--------|
| **Versión documento** | 2026-07-31 |
| **Ámbito** | Módulo Partes de Atención |
| **Público** | Asistentes, supervisores, clientes y soporte funcional |
| **Detalle por capacidad** | Ver [índice](./README.md) (SPEC-002 … SPEC-007) |

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

**Partes de Atención** permite registrar el tiempo dedicado a clientes y tipos de tarea, consultar dedicación y supervisar el cierre de partes. Cada persona entra con un **perfil funcional**: asistente, supervisor (asistente con facultad de supervisión) o cliente.

Tras el login válido, el sistema abre el **Dashboard** (Inicio). El menú lateral muestra solo las opciones permitidas para tu perfil y permisos.

## Conceptos clave

| Concepto | Qué significa |
|----------|----------------|
| **Asistente** | Quien carga y consulta sus propias tareas |
| **Supervisor** | Asistente habilitado para ver/operar el universo ampliado, cerrar/reabrir y usar proceso masivo |
| **Cliente** | Usuario vinculado a una organización cliente: consulta solo sus datos; no carga ni administra catálogos |
| **Tarea / parte** | Registro de fecha, cliente, tipo, duración y observación |
| **Cerrada** | Parte ya supervisada: no se edita ni elimina en el flujo ordinario; un supervisor puede reabrirla |
| **Tramo de duración** | Paso mínimo de minutos (por defecto **15**): las duraciones deben ser múltiplos de ese valor |
| **Tipo genérico** | Tipo de tarea disponible para todos los clientes (no se asigna cliente a cliente) |
| **Tipo default** | Tipo sugerido al cargar; debe haber siempre uno usable |

## Menú principal

| Grupo | Qué encontrarás | Quién suele verlo |
|-------|-----------------|-------------------|
| **Inicio** | Dashboard | Todos los perfiles Partes |
| **Archivos** | Asistentes, clientes, tipos de cliente, tipos de tarea, asignación tipos por cliente | Quienes tienen permiso de administración |
| **Partes** | Carga diaria; proceso masivo | Asistente/supervisor; masivo solo supervisor |
| **Informes** | Consulta detallada, consultas agrupadas, paquete de horas | Según perfil (cliente: solo su organización) |
| **Seguridad** | Usuarios, roles, permisos (genérico del sistema) | Administradores / supervisor |
| **Parámetros** | Parámetros Auth y Parámetros Partes | Administradores |
| **Avatar** | Perfil Partes (solo lectura), idioma, apariencia, salir | Todos |

**Cliente:** Inicio + Informes (+ perfil en el avatar). Sin Archivos, Partes, Seguridad ni Parámetros de administración.

## Mapa rápido de tareas

| Quiero… | Ir a… | Manual |
|---------|-------|--------|
| Entrar y entender mi perfil | Login / avatar | [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md) |
| Mantener catálogos | Archivos | [SPEC-003](./SPEC-003-maestros-y-catalogos.md) |
| Cargar el trabajo del día | Partes → Carga diaria | [SPEC-004](./SPEC-004-operacion-carga-diaria.md) |
| Cerrar muchas partes | Partes → Proceso masivo | [SPEC-005](./SPEC-005-supervision-proceso-masivo.md) |
| Ver totales e informes | Inicio / Informes | [SPEC-006](./SPEC-006-consultas-dashboard-navegacion.md) |
| Usar el celular | App móvil | [SPEC-007](./SPEC-007-mobile-capacitor.md) |

## Particularidades transversales

- La **delimitación de datos** (qué filas ves) la define tu perfil funcional; el menú solo oculta pantallas.
- En **web**, los informes pueden usar vista **pivot**; en **móvil** no.
- En **móvil** no hay maestros, proceso masivo, pivot ni administración de seguridad.
- Duración máxima de una tarea: **1440 minutos** (24 h).
- Si otro usuario modificó la misma tarea, el sistema pide **refrescar** e intentar de nuevo.

## Condiciones de uso

- Credenciales válidas **y** perfil Partes habilitado (asistente o cliente activo).
- Instalación / tenant correcto cuando el producto lo solicita.
- Permisos de menú según el rol de seguridad asignado.

## Errores frecuentes (visión general)

| Síntoma | Clave típica | Qué hacer |
|---------|--------------|-----------|
| Usuario/clave incorrectos | `auth.invalidCredentials` | Verificar datos |
| Sin perfil Partes | `partes.auth.noFunctionalProfile` | Pedir alta al administrador |
| Error de conexión | `infra.transport` | Reintentar; si sigue, avisar a soporte |
| Función no disponible en el celular | `mobile.routeExcluded` | Usar la versión web |

El detalle por pantalla está en cada SPEC del índice.

## Preguntas frecuentes

### ¿Cuál es la diferencia entre asistente y supervisor?

El supervisor es un asistente con facultad de supervisión: ve más datos, puede cerrar/reabrir y usar el proceso masivo.

### ¿El cliente puede cargar partes?

No. Solo consulta dedicación de su organización (dashboard e informes).

### ¿Dónde cambio mi nombre o email de dominio?

En esta versión el perfil Partes del avatar es **solo lectura**. Los cambios los hace administración (maestros / seguridad).
