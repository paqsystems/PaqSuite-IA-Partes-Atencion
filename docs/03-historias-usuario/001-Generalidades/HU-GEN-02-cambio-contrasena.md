# HU-GEN-02-cambio-contrasena — Cambio de contraseña y primer ingreso

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-cambio-contrasena |
| **SPEC origen** | [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-31 |
| **Dependencias** | HU-GEN-02-login-sesion; HU-GEN-01-menu-avatar |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Cambio de contraseña (alcance) | Flujo autenticado + primer ingreso |
| Entregable: contrato autenticación MVP (cambio contraseña) | AC + OpenAPI en TR |
| Integración menú avatar (SPEC-001-01 flujo) | Entrada desde avatar |
| Fuera alcance: ABM seguridad UI | Out of scope |
| Trazabilidad HU-SPEC: Cambio / primer ingreso | Objeto de esta HU |

## Narrativa

Como **usuario autenticado**,  
quiero **cambiar mi contraseña desde el menú avatar**,  
para **mantener mi cuenta segura y cumplir el cambio obligatorio en el primer acceso si aplica**.

## Contexto funcional

SPEC-001-02 incluye **cambio de contraseña** en el marco de autenticación MVP. Acceso desde menú avatar (integración SPEC-001-01). Sin ABM de usuarios en portal. Detalle de política de clave y flag primer ingreso: supuestos para TR (producto §7.4 referenciado indirectamente).

## Alcance incluido

- Flujo “Cambiar contraseña” desde menú avatar.
- Campos: contraseña actual, nueva, confirmación.
- Validación: actual correcta; nueva cumple política; confirmación coincide.
- Éxito: actualizar credencial almacenada.
- Flujo obligatorio post-login si el usuario requiere cambio inicial (supuesto `first_login`; TR).

## Fuera de alcance

- Recuperación sin sesión (HU-GEN-02-recuperacion-contrasena).
- Administración de usuarios por ABM (SPEC fuera de alcance).
- 2FA (SPEC fuera de alcance).

## Reglas de negocio

1. No se puede omitir cambio obligatorio mientras aplique condición de primer ingreso (supuesto; confirmar en TR).
2. Errores con mensaje claro sin revelar detalle interno de validación.
3. Sesión expirada durante el flujo: redirect a login.

## Criterios de aceptación

- [ ] Cambio con contraseña actual correcta actualiza credencial y permite operar.
- [ ] Contraseña actual incorrecta: rechazo sin cambiar credencial.
- [ ] Usuario en primer ingreso obligatorio no accede a procesos hasta completar cambio.
- [ ] Sesión expirada durante flujo: redirect a login.
- [ ] E2E: usuario first_login → forzado a cambiar → shell accesible.
- [ ] E2E: campos vacíos, actual incorrecta, confirmación distinta → rechazo sin cambiar credencial.
- [ ] E2E: tras cambio exitoso, login con nueva contraseña OK y con la anterior falla.
- [ ] Endpoint documentado en TR/OpenAPI.

## Escenarios Gherkin

```gherkin
Feature: Cambio de contraseña (SPEC-001-02)

  Scenario: Cambio exitoso desde menú avatar
    Given un usuario autenticado
    When ingresa contraseña actual correcta y nueva válida
    Then la credencial se actualiza
    And puede continuar operando

  Scenario: Contraseña actual incorrecta
    Given un usuario autenticado
    When ingresa contraseña actual incorrecta
    Then el cambio es rechazado
    And la credencial no cambia

  Scenario: Campos obligatorios vacíos
    Given un usuario autenticado en el formulario de cambio
    When intenta enviar sin completar los campos
    Then el cambio es rechazado
    And la credencial no cambia

  Scenario: Confirmación distinta a la nueva
    Given un usuario autenticado
    When la nueva contraseña y la confirmación no coinciden
    Then el cambio es rechazado
    And la credencial no cambia

  Scenario: Login con nueva contraseña tras cambio
    Given un usuario que cambió su contraseña exitosamente
    When inicia sesión con la nueva contraseña
    Then accede al sistema
    And no puede iniciar sesión con la contraseña anterior

  Scenario: Primer ingreso obligatorio
    Given un usuario autenticado que debe cambiar contraseña
    When intenta acceder a procesos del shell
    Then es redirigido al flujo de cambio de contraseña
    And tras completarlo accede al shell

  Scenario: Sesión expirada durante cambio
    Given un usuario en flujo de cambio de contraseña
    When la sesión expira
    Then es redirigido al login
```

## Supuestos explícitos

- Campo `first_login` y política “obligatorio primer acceso”: no explícitos en SPEC-001-02; heredados de producto §7.4 / TR.
- ¿Reautenticación tras cambio o sesión continua?: decisión TR.
- Política de complejidad y rechazo “nueva igual a actual”: TR/producto.

## Preguntas abiertas

- ¿Tras cambio exitoso se mantiene sesión o se exige re-login?

## Riesgos de ambigüedad

- Flag primer ingreso no está en criterios medibles del SPEC; riesgo de omitir en implementación.

## Veredicto B1

**Lista para TR:** Sí con observaciones (first_login y política de clave)

## Cierre F

- **Resultado:** Aprobada.
- **Soporte de verificación:** [TR-GEN-02-cambio-contrasena](../../04-tareas/001-Generalidades/TR-GEN-02-cambio-contrasena.md) y [F-GEN-01-02-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-01-02-cierre-formal.md).
- **Observaciones:** el cierre F confirma flujo autenticado, gate `firstLogin`, i18n de la pantalla y cobertura funcional/e2e sin hallazgos críticos abiertos.










