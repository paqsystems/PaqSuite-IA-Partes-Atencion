# HU-GEN-10-configuracion-asistente-ia — Configuración personal del Chat Asistente IA

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-10-configuracion-asistente-ia |
| **SPEC origen** | [SPEC-001-10-chat-asistente-ia.md](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001 — Generalidades / Chat Asistente IA |
| **Prioridad** | Should |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-30) |
| **Última actualización** | 2026-05-30 |
| **Dependencias** | HU-GEN-01-menu-avatar; HU-GEN-10-catalogo-proveedores-ia |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Modalidad inicial de configuración | Solo por usuario |
| Configuración funcional | Proveedor, credencial, modelo y endpoint desde perfil |
| Persistencia de credenciales | Guardado separado de `users` |
| Sin configuración válida | CTA a configuración |

## Narrativa

Como **usuario autenticado**,  
quiero **configurar mi proveedor, credencial y modelo para el Chat Asistente IA desde mi perfil**,  
para **habilitar el uso del chat con consumo asociado a mi propia configuración**.

## Contexto funcional

SPEC-001-10 define que la primera versión del chat funciona con configuración **solo por usuario** y que cada usuario administra su propia integración desde el flujo de perfil. La credencial no debe guardarse en `users`, sino en una tabla dedicada y cifrada.

## Alcance incluido

- Sección de configuración del Chat Asistente IA dentro del perfil del usuario.
- Selección de `providerId` desde catálogo soportado.
- Carga de `apiKey`, `modelId` y `baseUrl` cuando el proveedor lo requiera.
- Visualización de `supportUrl` del proveedor para onboarding.
- Alta, edición, habilitación y actualización de la configuración personal.
- Validaciones de campos obligatorios por proveedor.

## Fuera de alcance

- Configuración compartida por tenant o empresa.
- Administración centralizada de credenciales por otro actor.
- Edición del catálogo de proveedores desde UI.
- Envío automático de consultas a soporte.

## Reglas de negocio

1. La primera versión permite **una configuración activa por usuario**.
2. `providerId` debe existir en el catálogo de proveedores soportados.
3. `supportUrl` se resuelve desde el catálogo de proveedores y no desde la configuración sensible del usuario.
4. La credencial debe persistirse cifrada fuera de `users`.
5. Si el proveedor requiere `baseUrl`, el campo es obligatorio para guardar.
6. El usuario puede editar su configuración sin afectar la de otros usuarios.

## Criterios de aceptación

- [ ] El usuario puede abrir la configuración del chat desde su perfil.
- [ ] Puede seleccionar un proveedor soportado y ver su ayuda de onboarding.
- [ ] Puede guardar `providerId`, `apiKey`, `modelId` y `baseUrl` cuando corresponda.
- [ ] Si el proveedor no requiere `baseUrl`, ese campo no bloquea el guardado.
- [ ] Si faltan datos obligatorios, el guardado es rechazado con mensaje controlado.
- [ ] La configuración guardada queda asociada al usuario actual y no a `users`.
- [ ] El usuario puede actualizar o deshabilitar su configuración.

## Escenarios Gherkin

```gherkin
Feature: Configuración personal del Chat Asistente IA

  Scenario: Guardar configuración válida
    Given un usuario autenticado en su perfil
    When selecciona un proveedor soportado
    And completa credencial y modelo requeridos
    Then la configuración se guarda correctamente
    And queda asociada solo a ese usuario

  Scenario: Proveedor con baseUrl obligatoria
    Given un usuario autenticado
    And selecciona un proveedor que requiere baseUrl
    When intenta guardar sin baseUrl
    Then ve un mensaje de validación
    And la configuración no se guarda

  Scenario: Consultar ayuda de onboarding
    Given un usuario autenticado en la configuración del chat
    When selecciona un proveedor
    Then ve un acceso a la URL de onboarding del proveedor
```

## Supuestos explícitos

- El perfil del usuario ya existe como flujo navegable o se completará en TR.
- La persistencia cifrada se implementará server-side según el modelo de datos aprobado.

## Preguntas abiertas

- ¿La deshabilitación elimina credencial o solo desactiva su uso?

## Riesgos de ambigüedad

- Si el perfil no tiene una superficie clara para configuración avanzada, puede requerir un subflujo específico.

## Veredicto B1

**Lista para TR:** Sí con observaciones

Observación: la diferencia entre deshabilitar una configuración y eliminar credencial queda abierta para resolución en TR.










