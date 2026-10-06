# Partes de Atención — Manual de usuario

| Campo | Valor |
|-------|--------|
| **Versión documento** | 2026-10-05 |
| **Ámbito** | Módulo Partes de Atención |
| **Público** | Asistentes, supervisores, clientes y soporte funcional |
| **Corpus** | Fuente restringida del **Asistente IA** (menú avatar) |
| **Detalle** | [Índice](./README.md) y [objetos/datos](./SPEC-001-objetos-datos-y-glosario.md) |

> Manual de usuario — corpus Asistente IA. Sin detalles de implementación, infraestructura ni codificación.

## Definición conceptual e integral

**Partes de Atención** es el módulo para registrar y consultar la **dedicación** del equipo: quién trabajó, para qué **cliente**, en qué **fecha**, con qué **tipo de tarea**, durante **cuánto tiempo** y con qué **observación**. Esa información alimenta el **dashboard**, los **informes**, la **supervisión** (cierre de partes) y, opcionalmente, **reportes formales** y cruces con códigos del ERP.

El valor central es el flujo: **ingresar → cargar tareas → consultar o supervisar**. Los **catálogos** (Archivos) existen para que la carga sea coherente. Los **perfiles** limitan qué datos ve cada persona: un **cliente** solo su organización; un **asistente** su actividad; un **supervisor** un universo ampliado y herramientas de cierre masivo.

## Resumen operativo

Tras el login válido, el sistema abre el **Dashboard (Inicio)**. El menú lateral muestra solo las opciones permitidas para tu perfil y permisos. Cada **tarea cerrada** quedó supervisada y no se edita en el flujo normal hasta que un supervisor la reabra.

## Conceptos clave

| Concepto | Qué significa |
|----------|----------------|
| **Asistente** | Quien carga y consulta sus propias tareas |
| **Supervisor** | Asistente habilitado para ver/operar el universo ampliado, cerrar/reabrir y usar proceso masivo |
| **Cliente** | Usuario vinculado a una organización cliente: consulta solo sus datos; no carga ni administra catálogos |
| **Tarea / parte** | Registro de fecha, cliente, tipo, duración y observación |
| **Cerrada** | Parte ya supervisada: no se edita ni elimina en el flujo ordinario; un supervisor puede reabrirla |
| **Tramo de duración** | Paso mínimo de minutos (por defecto **15**): las duraciones deben ser múltiplos de ese valor |
| **Tipo genérico** | Tipo de tarea disponible para todos los clientes |
| **Tipo default** | Tipo sugerido al cargar; debe haber siempre uno usable |
| **Paquete de horas** | Informe con tareas y movimientos de horas del cliente (no es la pantalla de carga diaria) |

Detalle de cada objeto y campo: [SPEC-001](./SPEC-001-objetos-datos-y-glosario.md).

## Menú principal

| Grupo | Qué encontrarás | Quién suele verlo |
|-------|-----------------|-------------------|
| **Inicio** | Dashboard | Todos los perfiles Partes |
| **Archivos** | Asistentes, clientes, tipos de cliente, tipos de tarea, asignación tipos por cliente | Administración / supervisor |
| **Partes** | Carga diaria; proceso masivo | Asistente/supervisor; masivo solo supervisor |
| **Informes** | Consulta detallada, consultas agrupadas, paquete de horas | Según perfil |
| **Seguridad** | Usuarios, roles, permisos | Administradores |
| **Parámetros** | Auth, Partes, importación, emisiones | Administradores / supervisor |
| **Soporte Técnico** | Diseñador de emisiones | Quien tenga permiso de diseño |
| **Avatar** | Perfil Partes (solo lectura), **Asistente IA**, Preferencias de modelo, idioma, apariencia, salir | Todos |

**Cliente:** Inicio + Informes (+ perfil en el avatar). Sin Archivos, Partes, Seguridad ni Parámetros de administración.

## Mapa rápido de tareas

| Quiero… | Ir a… | Manual |
|---------|-------|--------|
| Entender objetos y datos | — | [SPEC-001](./SPEC-001-objetos-datos-y-glosario.md) |
| Entrar y entender mi perfil | Login / avatar | [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md) |
| Mantener catálogos | Archivos | [SPEC-003](./SPEC-003-maestros-y-catalogos.md) |
| Cargar el trabajo del día | Partes → Carga diaria | [SPEC-004](./SPEC-004-operacion-carga-diaria.md) |
| Importar planilla | Carga diaria → Importar | [SPEC-009](./SPEC-009-importacion-excel.md) |
| Cerrar muchas partes | Partes → Proceso masivo | [SPEC-005](./SPEC-005-supervision-proceso-masivo.md) |
| Ver totales e informes | Inicio / Informes | [SPEC-006](./SPEC-006-consultas-dashboard-navegacion.md) |
| Emitir un reporte | Consulta detallada → Emitir | [SPEC-010](./SPEC-010-emisiones-informes.md) |
| Parámetros o usuarios/roles | Parámetros / Seguridad | [SPEC-011](./SPEC-011-parametros-seguridad.md) |
| Usar el celular | App móvil | [SPEC-007](./SPEC-007-mobile-capacitor.md) |
| Ayuda documental (Asistente IA) | Avatar → Asistente IA | [SPEC-008](./SPEC-008-asistente-ia-y-preferencias.md) |
| Buscar un mensaje de error | — | [CATALOGO-ERRORES](./CATALOGO-ERRORES.md) |

## Asistente IA (ayuda documental)

1. Tras el login, abrí el **menú del avatar**.
2. Elegí **Asistente IA**.
3. Si no tenés configuración de modelo, andá a **Preferencias** y cargá la credencial que te dio tu organización.
4. Preguntá sobre operatoria de Partes; el asistente **orienta** según este manual y **no** crea ni modifica tareas.

La **captura inteligente** del formulario de carga es otra función (completar campos del parte). Ver [SPEC-004](./SPEC-004-operacion-carga-diaria.md) y [SPEC-008](./SPEC-008-asistente-ia-y-preferencias.md).

## Particularidades transversales

- La **delimitación de datos** la define tu perfil funcional; el menú solo oculta pantallas.
- En **web**, los informes pueden usar vista **pivot**; en **móvil** no.
- En **móvil** no hay maestros, proceso masivo, pivot, importación Excel, **emisión de reportes** ni administración de seguridad.
- **Emitir** en consulta detallada usa el universo filtrado del informe; no es el export de la grilla.
- Duración máxima de una tarea: **1440 minutos** (24 h).
- Si otro usuario modificó la misma tarea, el sistema pide **refrescar** e intentar de nuevo.

## Integración con otros sistemas

Si necesitás conectar otro sistema al producto, solicitá al administrador la **URL de documentación OpenAPI** del backend de tu instalación (ruta habitual: `/api/documentation`). Este manual no describe contratos de integración.

## Condiciones de uso

- Credenciales válidas **y** perfil Partes habilitado.
- Código de **empresa / instalación** correcto cuando el producto lo solicita (login o puente web).
- Permisos de menú según el rol asignado.

## Errores

Todos los mensajes catalogados, agrupados por proceso (**P00**–**P11**), están en [CATALOGO-ERRORES.md](./CATALOGO-ERRORES.md). En cualquier pantalla pueden aparecer errores **transversales** (conexión, sesión vencida, sin perfil Partes).

## Preguntas frecuentes

### ¿Cuál es la diferencia entre asistente y supervisor?

El supervisor es un asistente con facultad de supervisión: ve más datos, puede cerrar/reabrir y usar el proceso masivo.

### ¿El cliente puede cargar partes?

No. Solo consulta dedicación de su organización.

### ¿Dónde cambio mi nombre o email?

En esta versión el perfil Partes del avatar es **solo lectura**. Los cambios los hace administración (maestros / seguridad).
