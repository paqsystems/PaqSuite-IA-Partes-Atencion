# HU-GEN-02-login-sesion — Login, bootstrap de sesión y logout

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-login-sesion |
| **SPEC origen** | [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-31 |
| **Dependencias** | SPEC-001-05 (tenancy MONO, `X-Paq-Cliente`); HU-GEN-01-idioma |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Login y ciclo de sesión (alcance) | Login, bootstrap, logout |
| Entregable: contrato autenticación MVP (login, logout) | AC + OpenAPI en TR |
| Fuera alcance: 2FA, login social | Out of scope |
| MONO: sin selector empresa | Post-login directo al shell |
| Trazabilidad HU-SPEC: login, bootstrap, logout | Objeto de esta HU |

## Narrativa

Como **usuario del portal**,  
quiero **iniciar sesión con credenciales y cerrar sesión de forma segura**,  
para **acceder solo a los procesos autorizados y salir sin dejar sesión activa**.

## Contexto funcional

SPEC-001-02 establece el marco de **autenticación y ciclo de sesión** para el MVP. Incluye login y logout; recuperación, cambio de contraseña e inactividad están en HUs hermanas. Roles vía seed (`Pq_Permiso`), sin ABM UI. Producto §7.4 referenciado como fuente de autenticación.

## Alcance incluido

- Pantalla de login con credenciales y enlace a recuperación (flujo en HU hermana).
- API de autenticación con token/sesión.
- Post-login: **`POST /login` devuelve contexto completo** (token, perfil, preferencias); el frontend entra al shell sin llamada obligatoria a `/me`.
- Menú lateral vía `GET /user/menu` (HU hermana); no embebido en login.
- Validación: `Pq_Permiso` + vínculo comercial `cod_login` obligatorio (TR D-01).
- Logout: invalidar sesión backend, limpiar cliente, redirect login.
- `GET /auth/me` para recarga F5 (mismo contexto, sin token).

## Fuera de alcance

- Recuperación de contraseña (HU-GEN-02-recuperacion-contrasena).
- Cambio de contraseña y primer ingreso (HU-GEN-02-cambio-contrasena).
- Expiración por inactividad (`MinutosWeb`; HU-GEN-02-expiracion-inactividad).
- 2FA, login social, anti-fuerza bruta avanzado (SPEC fuera de alcance).
- Administración de seguridad vía UI (SPEC fuera de alcance).

## Reglas de negocio

1. Solo usuarios con permisos seed válidos (`Pq_Permiso`) operan tras autenticación.
2. En MONO no hay paso intermedio de selección de empresa (coherente SPEC-001-05).
3. Requests API autenticados incluyen token y **`X-Paq-Cliente`** según tenancy (producto/SPEC MVP referenciados).
4. Mensaje genérico ante credenciales inválidas (401); 403 por falta de permiso o perfil comercial pueden ser explícitos.
5. Sin `cod_login` comercial: rechazo 403 con motivo claro (`auth.noCommercialProfile`).

## Criterios de aceptación

- [ ] Login exitoso devuelve token y **contexto de sesión completo** en una respuesta.
- [ ] Credenciales incorrectas: HTTP 401 y mensaje genérico en UI.
- [ ] Usuario sin `Pq_Permiso` válido: 403 con mensaje de falta de acceso.
- [ ] Usuario sin `cod_login` comercial: 403 con mensaje explícito de perfil no configurado.
- [ ] Post-login: redirección al shell sin pantalla de empresa ni `/me` obligatorio.
- [ ] Logout invalida sesión; request con token anterior → 401.
- [ ] F5 con token válido: `/auth/me` refresca contexto.
- [ ] Tenant inválido: error controlado según arquitectura MONO.
- [ ] E2E: login válido → shell; logout → pantalla login.
- [ ] Contrato OpenAPI login/logout documentado en TR.

## Escenarios Gherkin

```gherkin
Feature: Login y sesión (SPEC-001-02)

  Scenario: Login exitoso en MONO
    Given un usuario con credenciales válidas, Pq_Permiso y cod_login comercial
    When inicia sesión con X-Paq-Cliente válido
    Then recibe token y contexto de sesión en una sola respuesta
    And es redirigido al shell sin selector de empresa

  Scenario: Credenciales inválidas
    Given credenciales incorrectas
    When intenta login
    Then recibe HTTP 401
    And ve mensaje genérico de error

  Scenario: Usuario sin permisos
    Given un usuario sin fila válida en Pq_Permiso
    When intenta login con credenciales correctas
    Then no accede al shell
    And ve mensaje de falta de acceso

  Scenario: Usuario sin vínculo comercial
    Given un usuario con Pq_Permiso pero sin cod_login
    When intenta login con credenciales correctas
    Then recibe HTTP 403
    And ve mensaje explícito de perfil comercial no configurado

  Scenario: Logout invalida sesión
    Given un usuario autenticado
    When cierra sesión
    Then el token queda invalidado
    And la siguiente petición autenticada responde 401
```

## Supuestos explícitos

- Campos `activo`, `inhabilitado`, `first_login` en `users`: validar en TR con producto §7.4.
- Laravel Sanctum como mecanismo de token.
- **Decisión D-01 (confirmada):** contexto completo en `POST /login`; `/me` solo recarga — [TR-GEN-02-login-sesion](../../04-tareas/001-Generalidades/TR-GEN-02-login-sesion.md) §3.1.

## Preguntas abiertas

- _(Ninguna crítica para MVP.)_

## Riesgos de ambigüedad

- Bootstrap post-login depende de HU-GEN-01 (preferencias) y HU-GEN-02-autorizacion-menu-api; orden de implementación en TR.

## Veredicto B1

**Lista para TR:** Sí — D-01 cerrada en TR.

## Cierre F

- **Resultado:** Aprobada con observaciones.
- **Soporte de verificación:** [TR-GEN-02-login-sesion](../../04-tareas/001-Generalidades/TR-GEN-02-login-sesion.md) y [F-GEN-01-02-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-01-02-cierre-formal.md).
- **Observaciones:** el cierre F valida el contrato y el comportamiento base del login/logout con la evidencia D ya registrada; la revalidación integral de backend no se repitió completa en esta oleada.










