# HU-GEN-10-mensajes-asistente-ia — Mensajes editables del Chat Asistente IA

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-10-mensajes-asistente-ia |
| **SPEC origen** | [SPEC-001-10-chat-asistente-ia.md](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001 — Generalidades / Chat Asistente IA |
| **Prioridad** | Should |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-30) |
| **Última actualización** | 2026-05-30 |
| **Dependencias** | HU-GEN-10-chat-documental |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Textos de experiencia | Mensaje inicial y cierre externos al código |
| Regla de cierre a soporte | Solo cuando la IA no tenga confianza suficiente |
| Placeholder de contacto | Dato de soporte reemplazable |

## Narrativa

Como **usuario del Chat Asistente IA**,  
quiero **ver un mensaje inicial claro y un mensaje final de derivación a soporte cuando la IA no tenga suficiente confianza**,  
para **entender qué esperar del chat y cómo continuar si la respuesta no alcanza**.

## Contexto funcional

SPEC-001-10 define que el saludo inicial y el mensaje de soporte viven en archivos Markdown editables del proyecto. También define que el cierre a soporte no se agrega siempre, sino solo ante baja confianza o insuficiencia de orientación.

## Alcance incluido

- Mensaje inicial editable al abrir el chat o iniciar conversación nueva.
- Mensaje final editable de derivación a soporte.
- Uso del mensaje final solo cuando la IA no tenga confianza suficiente.
- Placeholder o mecanismo equivalente para reemplazar el dato real de contacto.

## Fuera de alcance

- Personalización del mensaje por usuario.
- Edición de los mensajes desde UI del portal.
- Envío automático de correo o ticket a soporte.

## Reglas de negocio

1. El mensaje inicial se resuelve desde archivo Markdown editable del proyecto.
2. El mensaje de cierre a soporte se resuelve desde archivo Markdown editable del proyecto.
3. El mensaje final no debe aparecer en todas las respuestas; solo aplica cuando la orientación resulte insuficiente o la IA exprese baja confianza.
4. El dato de contacto a soporte debe poder completarse sin hardcodear el valor final en la lógica.

## Criterios de aceptación

- [ ] Al abrir una conversación nueva se muestra el mensaje inicial.
- [ ] El mensaje inicial proviene del archivo Markdown definido.
- [ ] El mensaje de cierre a soporte proviene del archivo Markdown definido.
- [ ] El cierre a soporte aparece solo en respuestas con baja confianza o insuficiencia de orientación.
- [ ] El contenido admite un placeholder para el dato real de soporte.
- [ ] Si la respuesta es suficientemente confiable, el cierre a soporte no se muestra.

## Escenarios Gherkin

```gherkin
Feature: Mensajes editables del Chat Asistente IA

  Scenario: Mostrar mensaje inicial
    Given un usuario abre una conversación nueva del chat
    Then ve el mensaje inicial definido para la experiencia

  Scenario: Mostrar cierre por baja confianza
    Given un usuario realiza una consulta
    And la IA no tiene confianza suficiente en la respuesta
    Then la respuesta incluye el mensaje final de derivación a soporte

  Scenario: No mostrar cierre cuando la respuesta es suficiente
    Given un usuario realiza una consulta
    And la IA responde con confianza suficiente
    Then la respuesta no incluye el mensaje final de derivación a soporte
```

## Supuestos explícitos

- La evaluación de “baja confianza” se definirá en TR mediante una regla técnica o heurística controlada.

## Preguntas abiertas

- ¿Conviene conservar trazabilidad visible de por qué se mostró el mensaje de soporte?

## Riesgos de ambigüedad

- Si la regla de baja confianza queda demasiado abierta, la experiencia puede ser inconsistente.

## Veredicto B1

**Lista para TR:** Sí con observaciones

Observación: la regla concreta para detectar baja confianza queda pendiente de definición técnica en TR.










