---
specId: SPEC-002
titulo: Identidad funcional y acceso a Partes
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-07-31
openSpec: docs/05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md
---

# Identidad funcional y acceso a Partes

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

Para usar Partes de Atención necesitás credenciales válidas **y** un perfil funcional habilitado: **asistente**, **supervisor** (asistente con supervisión) o **cliente**. Si la contraseña es correcta pero no tenés vínculo Partes usable, el sistema no te deja operar el módulo. Desde el menú del avatar podés ver tu perfil Partes en solo lectura.

## Funcionamiento

### Ingresar al módulo

1. Abrí la aplicación web o móvil.
2. Completá instalación/empresa si el producto lo pide, luego usuario y contraseña.
3. Si es el primer ingreso, el sistema puede pedir **cambio de contraseña** antes de continuar.
4. Con perfil Partes válido, aterrizás en el **Dashboard (Inicio)**.

### Ver mi perfil Partes

1. Abrí el menú del **avatar** (tu usuario).
2. Consultá tipo funcional, código, nombre, si sos supervisor y datos de contacto de dominio.
3. Es información de **solo lectura** en esta versión.

### Si te quitan el acceso estando logueado

1. Un administrador inhabilita o quita tu vínculo Partes.
2. La próxima acción o refresco de sesión en Partes falla.
3. Debés volver a iniciar sesión; ya no podrás operar el módulo.

## Particularidades

- Un mismo usuario **no** puede ser a la vez asistente y cliente.
- Inactivo o inhabilitado = sin acceso a Partes, aunque la clave sea correcta.
- El menú puede ocultar pantallas; **no** reemplaza la regla de “qué datos veo”.
- Tras login el destino habitual es **/partes** (Dashboard).

### Límites / cupos visibles al usuario

- No aplica cupo numérico en este SPEC.

### Web vs mobile

| Tema | Web | Mobile |
|------|-----|--------|
| Login | Usuario y contraseña (+ tenant si aplica) | Empresa primero, luego usuario y contraseña |
| Perfil | Avatar | Avatar / equivalente in-app |
| Destino post-login | Dashboard Partes | Dashboard Partes |

## Condiciones de uso

- Permisos / roles: credenciales del sistema + perfil Partes activo.
- Empresa / instalación: debe ser válida cuando el producto la solicita.
- Sin perfil Partes o con perfil inconsistente la función **no** está disponible.

## Errores de validación

| Qué ve el usuario (mensaje o síntoma) | Código / clave i18n (si existe) | Causa habitual | Qué hacer |
|---------------------------------------|--------------------------------|----------------|-----------|
| Usuario o contraseña incorrectos | `auth.invalidCredentials` | Credenciales erróneas | Verificar usuario y clave |
| El mail no existe | `auth.emailNotFound` | Email no registrado | Usar el mail correcto o pedir alta |
| Las contraseñas no coinciden | `auth.password.mismatch` | Confirmación distinta | Repetir la nueva clave |
| Contraseña demasiado corta / insegura | `auth.password.tooShort` / `auth.password.policyUnsafe` | No cumple política | Cumplir longitud y reglas indicadas |
| Enlace de restablecimiento inválido | `auth.resetTokenInvalid` | Token vencido o usado | Solicitar un nuevo enlace |
| Instalación o tenant no válido | `tenant.invalid` | Código de empresa incorrecto | Verificar con el administrador |

## Errores de lógica

| Qué ve el usuario | Código / clave | Regla de negocio | Qué hacer |
|-------------------|----------------|------------------|-----------|
| No tiene perfil funcional de Partes habilitado | `partes.auth.noFunctionalProfile` | Falta vínculo asistente/cliente usable | Contactar al administrador |
| Perfil Partes inconsistente | `partes.auth.inconsistentProfile` | Doble vínculo o datos contradictorios | Contactar al administrador |
| Sesión vencida por inactividad | `auth.sessionExpired` | Tiempo de idle superado | Volver a iniciar sesión |
| No tiene empresas habilitadas | `shell.blockedNoCompany` | Sin empresa en la sesión | Pedir alta de empresa / permisos |

## Errores técnicos posibles

| Qué ve el usuario | Código / HTTP (si aplica) | Causa posible | Qué hacer / a quién escalar |
|-------------------|---------------------------|---------------|------------------------------|
| Error de conexión | `infra.transport` | Red, proxy o servidor | Reintentar; si persiste, soporte |
| Error inesperado | `infra.unexpected` | Fallo interno | Reintentar; reportar hora y pantalla |
| No se pudo enviar el correo de recuperación | `mailEngine.sendFailed` | Mail del servidor | Avisar a administración / soporte |

## Preguntas frecuentes

### ¿Por qué me rechaza si la contraseña es correcta?

Porque Partes exige además un perfil funcional habilitado (asistente o cliente). Pedí al administrador que te vincule.

### ¿Dónde edito mi nombre o email de dominio?

En el MVP el perfil del avatar es solo lectura. Los cambios se hacen por administración (maestros / seguridad).

### ¿Qué es un supervisor?

Un asistente marcado como supervisor: no es un tipo de login distinto.
