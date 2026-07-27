# HU-GEN-01-menu-avatar — Menú avatar y preferencias personales

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-01-menu-avatar |
| **SPEC origen** | [SPEC-001-01-experiencia-base.md](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001 — Generalidades / Experiencia base |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-28 |
| **Dependencias** | HU-GEN-01-shell-layout; HU-GEN-02-cambio-contrasena; HU-GEN-02-login-sesion |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Definir menú avatar (login y post-login) | Menú desplegable post-login en header |
| Flujo post-login: menú avatar con preferencias | Entradas apariencia, idioma/tema vía HUs |
| Persistencia `users.locale` y `users.theme` | Preferencias guardadas server-side |
| Criterio menú avatar login/post-login | Avatar solo post-login en shell |
| Trazabilidad HU-SPEC: Avatar | Objeto de esta HU |

## Narrativa

Como **usuario autenticado**,  
quiero **un menú bajo mi avatar con acciones personales y preferencias**,  
para **gestionar apariencia, navegación y sesión sin mezclarlas con el menú de procesos**.

## Contexto funcional

SPEC-001-01 incluye la definición del **menú avatar** como parte de la navegación principal. En post-login, el menú avatar concentra **preferencias personales** (idioma/tema persisten en `users.locale` y `users.theme` según flujo SPEC). Las acciones de seguridad (cambio/cierre de sesión) pertenecen al marco SPEC-001-02 e integran aquí como enlaces.

## Alcance incluido

- Avatar en extremo derecho del header post-login.
- Menú desplegable con acciones personales y de sesión.
- Entrada **Apariencia** → flujo HU-GEN-01-apariencia-temas.
- Entrada **Asistente IA** → HU-GEN-01-ayuda-externa (Should).
- Entradas **Cambiar contraseña** y **Cerrar sesión** → HU-GEN-02.
- Toggle **Abrir en nueva pestaña** con persistencia (afecta HU-GEN-01-menu-general-sidebar).
- Selector de idioma **fuera** de este menú (HU-GEN-01-idioma, flujo SPEC paso 1 y header).

## Fuera de alcance

- Opción “Cambiar empresa activa” (MULTI; SPEC-001-05).
- Administración funcional de seguridad vía UI (SPEC-001-02 fuera de alcance).
- Contenido del menú general de procesos (HU-GEN-01-menu-general-sidebar).

## Reglas de negocio

1. Acciones del avatar son personales o de sesión; no duplican ítems del menú general.
2. Preferencias (`locale`, `theme`, nueva pestaña) persisten **server-side por usuario** (flujo SPEC paso 3).
3. En MONO no se muestra “Cambiar empresa”.
4. Cerrar sesión: invalidar sesión backend, limpiar cliente, redirigir a login (alcance SPEC-001-02 login/ciclo sesión).

## Criterios de aceptación

- [ ] Clic en avatar abre/cierra menú con opciones definidas.
- [ ] Toggle “nueva pestaña” persiste y afecta navegación del sidebar.
- [ ] “Cerrar sesión” termina sesión y lleva a login.
- [ ] “Cambiar contraseña” abre flujo SPEC-001-02 (HU-GEN-02-cambio-contrasena).
- [ ] “Apariencia” enlaza a selector de temas (HU-GEN-01-apariencia-temas).
- [ ] “Asistente IA” visible solo si hay URL configurada (HU ayuda, Should).
- [ ] Usuario sin foto: avatar genérico sin error.
- [ ] E2E: logout desde avatar; toggle nueva pestaña persiste tras recargar sesión.

## Escenarios Gherkin

```gherkin
Feature: Menú avatar (SPEC-001-01)

  Scenario: Abrir menú avatar post-login
    Given un usuario autenticado en el shell
    When hace clic en su avatar
    Then ve opciones de apariencia, seguridad y sesión
    And no ve opción de cambiar empresa

  Scenario: Persistir preferencia abrir en nueva pestaña
    Given un usuario autenticado
    When activa "Abrir en nueva pestaña" en el menú avatar
    And recarga la sesión
    Then la preferencia sigue activa
    And afecta la navegación del sidebar

  Scenario: Cerrar sesión desde avatar
    Given un usuario autenticado
    When selecciona "Cerrar sesión"
    Then la sesión se invalida
    And es redirigido al login

  Scenario: Acceso a apariencia desde avatar
    Given un usuario autenticado
    When selecciona "Apariencia"
    Then accede al flujo de selección de tema
```

## Supuestos explícitos

- Entrada “Perfil” y foto de usuario: no detalladas en SPEC-001-01; opcional en TR.
- Modal de confirmación en logout: SPEC-001-02 no lo exige; cierre inmediato asumido.
- Campo `users.menu_abrir_nueva_pestana`: nombre de columna no en SPEC; definir en TR.

## Preguntas abiertas

- ¿Orden exacto de ítems en el menú desplegable? (SPEC no lo fija)

## Riesgos de ambigüedad

- Solapamiento selector idioma (header) vs menú avatar: flujo SPEC separa idioma del avatar; mantener en TR.

## Veredicto B1

**Lista para TR:** Sí










