# HU-GEN-02-recuperacion-contrasena — Recuperación de contraseña

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-recuperacion-contrasena |
| **SPEC origen** | [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-31 |
| **Dependencias** | HU-GEN-02-login-sesion; HU-GEN-01-idioma |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Recuperación de contraseña (alcance) | Flujo completo solicitud → nueva clave |
| Entregable: contrato autenticación MVP (recuperación) | AC + OpenAPI en TR |
| Integración i18n (HU-GEN-01-idioma) | Mail en idioma vigente del sitio |
| Fuera alcance: 2FA, login social | Out of scope |
| Trazabilidad HU-SPEC: Recuperación | Objeto de esta HU |

## Narrativa

Como **usuario que olvidó su contraseña**,  
quiero **solicitar restablecimiento por email y definir una nueva contraseña**,  
para **volver a acceder sin intervención de un administrador**.

## Contexto funcional

SPEC-001-02 incluye **recuperación de contraseña** en el marco de autenticación MVP. El contrato detallado (endpoints, códigos HTTP) se documenta en TR/OpenAPI según entregable verificable del SPEC. Administración ABM de usuarios: fuera de alcance.

## Alcance incluido

- Enlace “¿Olvidaste tu contraseña?” en pantalla de login.
- Formulario de solicitud con email registrado.
- Generación de token temporal y envío de correo.
- **Correo de recuperación** (asunto, cuerpo y textos del enlace) en el **idioma i18n vigente en el sitio** al momento de la solicitud (HU-GEN-01-idioma).
- Mensaje **siempre genérico** en solicitud (no filtrar existencia de email).
- Pantalla/enlace para nueva contraseña + confirmación con token válido.
- Actualización de credencial; redirección a login.

## Fuera de alcance

- Cambio de contraseña estando autenticado (HU-GEN-02-cambio-contrasena).
- 2FA, login social (SPEC fuera de alcance).
- ABM de usuarios en UI (SPEC fuera de alcance).

## Reglas de negocio

1. Respuesta idéntica para email existente y no existente (privacidad).
2. Token de recuperación de un solo uso (duración en TR).
3. Token expirado o usado no permite restablecer contraseña.
4. **Idioma del correo:** el mail enviado debe usar el **mismo locale i18n activo en el portal** cuando el usuario envía la solicitud (selector de idioma en login o, si aplica, `users.locale` del usuario identificado internamente). Asunto, cuerpo y etiquetas del enlace vía **claves i18n**, no textos fijos en un solo idioma. Si el locale activo no está soportado, aplicar fallback **`es`** (SPEC-001-01 §8.1).

## Criterios de aceptación

- [ ] Flujo completo: solicitud → mail (mock en test) → nueva contraseña → login con nueva clave.
- [ ] Token expirado: error claro y opción de reintentar.
- [ ] Respuesta de solicitud idéntica para email existente y no existente.
- [ ] Correo de recuperación generado en el idioma vigente del sitio al solicitar (verificar con locale distinto de `es` en test).
- [ ] Fallo de envío de mail: log de error; usuario ve mensaje genérico de solicitud recibida.
- [ ] E2E: solicitar recuperación → completar con token de prueba → login exitoso.
- [ ] Endpoints documentados en TR/OpenAPI.

## Escenarios Gherkin

```gherkin
Feature: Recuperación de contraseña (SPEC-001-02)

  Scenario: Solicitud con email registrado
    Given un email existente en users
    When solicita recuperación de contraseña
    Then ve mensaje genérico de confirmación
    And recibe correo con enlace de restablecimiento (entorno real)

  Scenario: Correo en idioma vigente del sitio
    Given el selector de idioma en login está en "en"
    And un email existente en users
    When solicita recuperación de contraseña
    Then el correo enviado tiene asunto y cuerpo en inglés
    And los textos provienen de claves i18n del locale activo

  Scenario: Solicitud con email no registrado
    Given un email que no existe
    When solicita recuperación
    Then ve el mismo mensaje genérico que email existente

  Scenario: Restablecer con token válido
    Given un token de recuperación válido
    When ingresa nueva contraseña y confirmación
    Then puede login con la nueva contraseña

  Scenario: Token expirado
    Given un token de recuperación expirado
    When intenta restablecer contraseña
    Then ve error claro
    And puede solicitar uno nuevo
```

## Supuestos explícitos

- Duración del token (p. ej. 60 min): no en SPEC-001-02; definir en TR.
- Política de complejidad de contraseña: producto/TR.
- Tabla/mecanismo de tokens reset: TR.

## Preguntas abiertas

- ¿Canal de mail y plantilla acordados para SistemaPartes MVP?

## Riesgos de ambigüedad

- Dependencia de infraestructura de mail no detallada en SPEC-001-02.

## Veredicto B1

**Lista para TR:** Sí con observaciones (token TTL, mail, política de clave)

## Cierre F

- **Resultado:** Aprobada con observaciones.
- **Soporte de verificación:** [TR-GEN-02-recuperacion-contrasena](../../04-tareas/001-Generalidades/TR-GEN-02-recuperacion-contrasena.md) y [F-GEN-01-02-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-01-02-cierre-formal.md).
- **Observaciones:** el flujo funcional e i18n quedaron alineados con la TR; la evidencia backend automática sigue condicionada por un problema previo del entorno/seed SQL Server.










