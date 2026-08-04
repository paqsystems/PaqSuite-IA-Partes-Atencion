# HU-GEN-10-catalogo-proveedores-ia — Catálogo inicial de proveedores IA

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-10-catalogo-proveedores-ia |
| **SPEC origen** | [SPEC-001-10-chat-asistente-ia.md](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001 — Generalidades / Chat Asistente IA |
| **Prioridad** | Should |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-30) |
| **Última actualización** | 2026-05-30 |
| **Dependencias** | HU-GEN-10-configuracion-asistente-ia |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Catálogo de proveedores | Tabla dedicada y fuente editable |
| Soporte de proveedores del catálogo inicial | Todos soportados en primera HU |
| `supportUrl` | Resuelto desde catálogo |

## Narrativa

Como **usuario autenticado que configura el chat**,  
quiero **elegir entre proveedores IA soportados y ver su documentación de onboarding**,  
para **configurar mi integración personal con criterios claros y consistentes**.

## Contexto funcional

SPEC-001-10 define un catálogo inicial de proveedores soportados y establece que en la primera HU todos esos proveedores forman parte del alcance funcional. El catálogo también es la fuente de `supportUrl`.

## Alcance incluido

- Catálogo de proveedores soportados visible para selección en configuración.
- Visualización de nombre visible, capacidades declaradas y necesidad de `baseUrl`.
- Acceso a `supportUrl` por proveedor.
- Disponibilidad de todos los proveedores listados en el catálogo inicial.
- Fuente editable documental alineada con la tabla del producto.

## Fuera de alcance

- Alta o baja de proveedores desde UI de usuario.
- Validación exhaustiva de cada proveedor/modelo en tiempo real.
- Inclusión de proveedores fuera del catálogo inicial.

## Reglas de negocio

1. El catálogo inicial incluye `ollama`, `openai`, `anthropic`, `googleGemini`, `azureOpenAi`, `openRouter`, `groq` y `mistral`.
2. Todos los proveedores del catálogo inicial se consideran soportados en la primera HU.
3. `providerId` es la clave estable para frontend y backend.
4. `supportUrl` es un dato del catálogo y no de la configuración sensible del usuario.
5. Si un proveedor requiere `baseUrl`, la UI debe comunicarlo claramente.

## Criterios de aceptación

- [ ] El usuario ve todos los proveedores del catálogo inicial al configurar el chat.
- [ ] Cada proveedor muestra su nombre visible y datos suficientes para selección.
- [ ] El usuario puede acceder a la ayuda de onboarding de cada proveedor.
- [ ] Se distingue si un proveedor requiere `baseUrl` editable.
- [ ] Todos los proveedores del catálogo inicial están disponibles como opciones soportadas en la primera HU.
- [ ] El catálogo funcional visible es consistente con el catálogo documental inicial.

## Escenarios Gherkin

```gherkin
Feature: Catálogo inicial de proveedores IA

  Scenario: Visualizar proveedores soportados
    Given un usuario autenticado en la configuración del chat
    When abre el selector de proveedor
    Then ve todos los proveedores del catálogo inicial

  Scenario: Ver ayuda del proveedor
    Given un usuario autenticado
    When selecciona un proveedor del catálogo
    Then puede acceder a la URL de onboarding asociada

  Scenario: Identificar proveedor con baseUrl editable
    Given un usuario autenticado
    When selecciona un proveedor que requiere endpoint propio
    Then la UI le informa que debe completar baseUrl
```

## Supuestos explícitos

- El catálogo puede sembrarse desde documentación editable y luego persistirse en la tabla recomendada.

## Preguntas abiertas

- ¿Conviene mostrar notas resumidas del proveedor además del nombre visible?

## Riesgos de ambigüedad

- Si no se sincronizan catálogo documental y seed técnico, la UX puede divergir del runtime.

## Veredicto B1

**Lista para TR:** Sí con observaciones

Observación: la sincronización entre catálogo documental y seed técnico debe quedar controlada en TR para evitar divergencias visibles al usuario.










