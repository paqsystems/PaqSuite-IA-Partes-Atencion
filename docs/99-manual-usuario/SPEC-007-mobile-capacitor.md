---
specId: SPEC-007
titulo: App móvil de Partes de Atención
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-07-31
openSpec: docs/05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md
---

# App móvil de Partes de Atención

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

La app móvil (Capacitor) ofrece login con empresa, dashboard, consultas en formato **kardex** (tarjetas), carga individual de tareas (asistente/supervisor) e informe de paquete de horas. No incluye maestros, proceso masivo, pivot ni administración de seguridad: esas funciones quedan en la **web**.

## Funcionamiento

### Configurar el servidor (antes o fuera del login)

1. Abrí el ícono de **configuración** (engranaje).
2. Ingresá la URL base del API.
3. Probá la conexión.
4. Guardá. Queda en el dispositivo.
5. El **tenant/empresa no** se configura aquí: se indica en el login.

### Iniciar sesión

1. Completá **empresa (instalación)**, luego usuario y contraseña.
2. Las mismas reglas de perfil Partes que en web.
3. Aterrizaje en el dashboard.

### Consultar y cargar (kardex)

1. Abrí la consulta de partes: lista vertical de tarjetas (periodo por defecto: **día actual**).
2. Tocá una tarjeta para ver el detalle.
3. Si sos asistente/supervisor y la tarea no está cerrada, podés agregar, editar o eliminar según reglas de negocio.
4. El cliente solo consulta (solo lectura).

### Paquete de horas

1. Desde Informes o el acceso del dashboard.
2. Elegí el periodo (por defecto el mes actual).
3. Revisá totales, desgloses y el gráfico simple de barras.

## Particularidades

### Exclusiones en mobile

- Sin ABM de Archivos (maestros).
- Sin proceso masivo.
- Sin pivot / informes pivot.
- Sin importación Excel.
- Sin menú de administración de seguridad.
- Sin preferencia de “abrir en pestaña nueva”: la navegación es siempre dentro de la app.

### Límites / cupos visibles al usuario

- Mismas reglas de duración/tramo/observación que en web.
- Dashboard: **solo** refresco manual o pull-to-refresh (sin auto-timer).

### Web vs mobile

| Tema | Web | Mobile |
|------|-----|--------|
| Listados | Grilla / pivot | Kardex (tarjetas) |
| Maestros | Sí | No |
| Proceso masivo | Sí | No |
| Config API | `.env` / hosting | Pantalla de configuración + prueba health |
| Tenant | Según entorno | Campo de login |

## Condiciones de uso

- Misma política de perfiles que web.
- Menú filtrado: no aparecen opciones excluidas.
- URL del API alcanzable desde el dispositivo o emulador.

## Errores de validación

| Qué ve el usuario (mensaje o síntoma) | Código / clave i18n (si existe) | Causa habitual | Qué hacer |
|---------------------------------------|--------------------------------|----------------|-----------|
| Instalación o tenant no válido | `tenant.invalid` | Empresa mal escrita | Corregir el código de empresa |
| Duración / observación / fechas (igual que web) | `partes.tarea.*` | Mismas reglas | Ver manual de carga diaria |

## Errores de lógica

| Qué ve el usuario | Código / clave | Regla de negocio | Qué hacer |
|-------------------|----------------|------------------|-----------|
| Esta función no está disponible en la app móvil | `mobile.routeExcluded` | Función solo web | Abrir la versión web |
| No hay datos para el filtro | `partes.consulta.empty` | Sin filas | Ampliar periodo |
| Sin perfil Partes | `partes.auth.noFunctionalProfile` | Falta vínculo | Contactar administrador |

## Errores técnicos posibles

| Qué ve el usuario | Código / HTTP (si aplica) | Causa posible | Qué hacer / a quién escalar |
|-------------------|---------------------------|---------------|------------------------------|
| Error de conexión / falla la prueba de health | `infra.transport` | URL incorrecta, red, backend caído | Revisar URL (emulador Android suele usar `10.0.2.2`); soporte |
| Error inesperado | `infra.unexpected` | Fallo interno | Reportar a soporte |

## Preguntas frecuentes

### ¿Por qué no puedo cerrar 50 partes de golpe en el celular?

El proceso masivo es solo web.

### Emulador Android y no conecta

En configuración usá la URL hacia el host del PC (p. ej. `http://10.0.2.2:8010/api/v1`) y probá la conexión.

### ¿Dónde configuro la empresa?

En el **login**, no en el engranaje de URL del API.
