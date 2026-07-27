# HU-GEN-11-mobile-login-tenant — Login con tenant (MONO mobile)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-11-mobile-login-tenant |
| **SPEC origen** | [SPEC-001-11-mobile-capacitor](../../05-open-spec/001-Generalidades/SPEC-001-11-mobile-capacitor.md) |
| **Patrón** | [04-patron-login-tenant-mobile-mono.md](../../_base/01-mobile/04-patron-login-tenant-mobile-mono.md) |
| **Épica** | 001 — Generalidades / Mobile |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-30) |
| **Dependencias** | HU-GEN-11-mobile-capacitor-scaffold; SPEC-001-02; HU-GEN-02-login-sesion |

## Narrativa

Como **usuario de la app mobile MONO**,  
quiero **ingresar empresa (tenant), usuario y contraseña en un solo formulario**,  
para **conectarme a la base correcta antes de autenticarme**.

## Alcance incluido

- Campo **tenant** visible **solo** si `isNativeApp()` (`data-testid="loginTenant"`).
- Orden UI: tenant → usuario → contraseña.
- Normalización tenant: `trim` + minúsculas; patrón slug.
- Cliente HTTP: enviar `X-Paq-Cliente` con tenant del formulario en **todas** las requests native (login incluido).
- Persistir tenant activo + token en `@capacitor/preferences` post-login.
- Precargar tenant en login tras logout (D1-14).
- Flujo `firstLogin` → `/change-password` (heredado HU-GEN-02-cambio-contrasena).
- Web: **sin** campo tenant (sin regresión).

## Fuera de alcance

- Recuperación / reset contraseña en v1 mobile (ocultar enlaces).
- Selector de empresa MULTI (`X-Company-Id`).
- Config override URL API (HU-GEN-11-mobile-config-api).

## Reglas de negocio

1. **Orden backend:** tenant válido → resolución SQL → validación credenciales.
2. Tenant inválido: error i18n; **sin** token.
3. Cambio de tenant: re-login obligatorio.
4. Credenciales inválidas con tenant válido: 401 genérico (igual web).
5. Prohibido autenticar sin `X-Paq-Cliente`.

## Criterios de aceptación

- [x] **CA-01:** Login native muestra tenant, usuario, contraseña.
- [ ] **CA-02:** Login web **no** muestra tenant (regresión pendiente).
- [x] **CA-03:** `POST /auth/login` incluye header `X-Paq-Cliente` = tenant ingresado.
- [x] **CA-04:** Tenant `desarrollo` + usuario seed → login exitoso.
- [ ] **CA-05:** Tenant inexistente → error sin sesión (no probado en smoke).
- [x] **CA-06:** Token + tenant persistidos en Preferences.
- [x] **CA-07:** Requests autenticadas post-login usan mismo tenant.
- [ ] **CA-08:** `firstLogin` redirige a change-password en native (no probado).
- [x] **CA-09:** i18n `login.tenant`; tests unit documentados en TR.

## Escenarios Gherkin

```gherkin
Feature: Login tenant-first mobile

  Scenario: Login exitoso con tenant válido
    Given la app native en pantalla login
    When ingreso tenant "desarrollo", usuario y contraseña válidos
    Then recibo token y entro al shell

  Scenario: Tenant inválido
    When ingreso tenant "no-existe" y credenciales
    Then veo error de tenant y no hay sesión
```

## Veredicto B1

**Lista para TR** (`TR-GEN-11-mobile-login-tenant`).










