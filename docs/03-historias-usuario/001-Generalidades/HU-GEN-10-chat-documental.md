# HU-GEN-10-chat-documental — Chat documental del asistente IA

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-10-chat-documental |
| **SPEC origen** | [SPEC-001-10-chat-asistente-ia.md](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001 — Generalidades / Chat Asistente IA |
| **Prioridad** | Should |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-30) |
| **Última actualización** | 2026-05-30 |
| **Dependencias** | HU-GEN-01-menu-avatar; HU-GEN-10-configuracion-asistente-ia; HU-GEN-10-mensajes-asistente-ia |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Punto de entrada | Menú avatar → `Chat Asistente IA` |
| Apertura de experiencia | Nueva pestaña |
| Sin configuración válida | Superficie vacía con CTA |
| Corpus inicial Fase 1 | Manual + documentación operativa estable |
| Resultado esperado | Respuesta orientativa con referencia documental |

## Narrativa

Como **usuario autenticado**,  
quiero **abrir un chat asistente IA desde el menú avatar y hacer consultas sobre el sistema**,  
para **recibir orientación operativa apoyada en la documentación aprobada sin perder mi pantalla actual**.

## Contexto funcional

SPEC-001-10 define que el chat se abre en una nueva pestaña del portal, se configura por usuario y en Fase 1 responde sobre `99-manual-usuario` y documentación operativa funcional estable. No ejecuta acciones ni reemplaza soporte humano.

## Alcance incluido

- Entrada `Chat Asistente IA` en menú avatar.
- Apertura del chat en nueva pestaña del portal.
- Convivencia con el acceso de ayuda externa simple mientras ambos flujos existan.
- Mensaje inicial al abrir conversación.
- Consultas textuales en lenguaje natural.
- Límite inicial de `2.000` caracteres para consultas de texto solo.
- Límite inicial de `1.000` caracteres para consultas que incluyan texto e imágenes.
- Contador visible, bloqueo de envío y mensaje claro al exceder el límite.
- Respuestas orientativas con referencia documental cuando sea posible.
- Estado sin configuración: mensaje de falta de configuración + CTA al perfil.

## Fuera de alcance

- Ejecución automática de acciones dentro del sistema.
- Uso de corpus técnico o metodológico excluido por SPEC.
- Envío automático a soporte.
- Persistencia de adjuntos.

## Reglas de negocio

1. El chat se abre desde el menú avatar y no embebido en la pantalla actual.
2. Si el usuario no tiene configuración válida, el chat no falla: informa el faltante y muestra CTA a configuración.
3. La Fase 1 consulta solo `99-manual-usuario` y documentación operativa funcional estable aprobada.
4. La respuesta debe posicionarse como orientación útil, no como resolución garantizada.
5. Si hay referencia documental disponible, debe priorizarse sobre respuesta puramente generativa.
6. Una consulta de texto solo no puede superar `2.000` caracteres.
7. Una consulta que incluya texto e imágenes no puede superar `1.000` caracteres de texto.
8. La UI debe mostrar contador de caracteres y bloquear el envío cuando se exceda el límite.

## Criterios de aceptación

- [ ] El usuario puede abrir `Chat Asistente IA` desde el menú avatar.
- [ ] El chat se abre en una nueva pestaña del portal y la pestaña original permanece disponible.
- [ ] La opción de chat puede coexistir con la ayuda externa simple sin reemplazarla.
- [ ] Si el usuario tiene configuración válida, puede enviar consultas textuales y recibir respuesta.
- [ ] Si falta configuración, ve un estado vacío con mensaje claro y CTA al perfil.
- [ ] Una consulta de texto solo mayor a `2.000` caracteres no se envía.
- [ ] Una consulta con texto e imágenes mayor a `1.000` caracteres no se envía.
- [ ] El usuario ve contador visible y mensaje claro cuando supera el máximo permitido.
- [ ] El chat utiliza solo el corpus documental definido para Fase 1.
- [ ] Las respuestas se presentan como orientación operativa.

## Escenarios Gherkin

```gherkin
Feature: Chat documental del asistente IA

  Scenario: Abrir chat desde avatar
    Given un usuario autenticado
    When selecciona "Chat Asistente IA" en el menú avatar
    Then se abre una nueva pestaña del portal con el chat
    And la pestaña original permanece disponible

  Scenario: Consultar con configuración válida
    Given un usuario autenticado con configuración válida del chat
    When envía una consulta textual sobre el sistema
    Then recibe una respuesta orientativa
    And la respuesta se apoya en el corpus documental aprobado

  Scenario: Exceder límite de texto solo
    Given un usuario autenticado con configuración válida del chat
    When intenta enviar una consulta de texto solo mayor a 2000 caracteres
    Then el envío es bloqueado
    And ve un mensaje indicando el máximo permitido

  Scenario: Abrir chat sin configuración
    Given un usuario autenticado sin configuración válida
    When abre el chat
    Then ve un mensaje indicando que falta configuración
    And ve una CTA para ir a su perfil
```

## Supuestos explícitos

- El chat en nueva pestaña usa una ruta interna del portal.

## Riesgos de ambigüedad

- Si no se delimita bien el corpus en implementación, la IA podría responder con fuentes no aprobadas.

## Veredicto B1

**Lista para TR:** Sí con observaciones

Observación: la UX concreta del contador y del mensaje de validación deberá detallarse en TR.










