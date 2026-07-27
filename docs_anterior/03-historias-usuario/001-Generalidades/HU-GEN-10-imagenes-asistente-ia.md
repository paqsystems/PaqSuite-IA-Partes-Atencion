# HU-GEN-10-imagenes-asistente-ia — Adjuntos de imágenes en el chat

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-10-imagenes-asistente-ia |
| **SPEC origen** | [SPEC-001-10-chat-asistente-ia.md](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001 — Generalidades / Chat Asistente IA |
| **Prioridad** | Should |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-30) |
| **Última actualización** | 2026-05-30 |
| **Dependencias** | HU-GEN-10-configuracion-asistente-ia; HU-GEN-10-chat-documental |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Soporte de imágenes | Opcional según proveedor/modelo |
| Límites | Hasta 4 imágenes, `5 MB`, `png/jpg/jpeg/webp` |
| Privacidad | Sin persistencia local |
| Tipos de interacción | Texto, solo imágenes o combinación |

## Narrativa

Como **usuario autenticado con un proveedor compatible**,  
quiero **adjuntar imágenes a mi consulta del chat**,  
para **explicar mejor errores, pantallas o documentos relevantes del sistema**.

## Contexto funcional

SPEC-001-10 permite imágenes solo si `supportsVision` y el modelo configurado lo soportan. Las imágenes no se guardan en el sistema y se usan solo para la consulta en curso.

## Alcance incluido

- Adjuntar imágenes a una consulta del chat.
- Consultas con texto, solo imágenes o combinación de ambos.
- Validación de formatos `png`, `jpg`, `jpeg`, `webp`.
- Validación de tamaño máximo `5 MB` por archivo.
- Límite de hasta `4` imágenes por interacción.
- Mensajes controlados cuando la capacidad no esté disponible.

## Fuera de alcance

- Persistencia de imágenes en el portal.
- Edición de imágenes dentro del chat.
- Garantías de OCR o interpretación perfecta.
- Tipos de archivo distintos a los definidos.

## Reglas de negocio

1. Solo se permiten imágenes si el proveedor/modelo activo soporta visión.
2. La interacción puede tener texto, imágenes o ambos.
3. Cada archivo no puede superar `5 MB`.
4. El máximo por interacción es `4` imágenes.
5. Las imágenes se envían al proveedor externo configurado y se descartan tras el análisis.
6. Un formato inválido o un exceso de tamaño no debe romper la aplicación.

## Criterios de aceptación

- [ ] Un usuario con proveedor compatible puede adjuntar imágenes válidas.
- [ ] El sistema admite interacciones con texto, solo imágenes o combinación de ambos.
- [ ] El sistema rechaza formatos no admitidos con error controlado.
- [ ] El sistema rechaza imágenes que exceden `5 MB` con error controlado.
- [ ] El sistema rechaza más de `4` imágenes por interacción.
- [ ] Si el proveedor/modelo no soporta visión, la UI informa indisponibilidad.
- [ ] Las imágenes no se persisten como histórico del portal.

## Escenarios Gherkin

```gherkin
Feature: Adjuntos de imágenes en el chat

  Scenario: Enviar consulta con texto e imágenes
    Given un usuario autenticado con proveedor compatible con imágenes
    When adjunta una o más imágenes válidas
    And envía una consulta
    Then la consulta es procesada correctamente

  Scenario: Proveedor sin soporte de visión
    Given un usuario autenticado con proveedor sin soporte de imágenes
    When intenta adjuntar una imagen
    Then ve un mensaje de indisponibilidad
    And el chat no se rompe

  Scenario: Exceder límites de imágenes
    Given un usuario autenticado
    When intenta adjuntar un archivo inválido o exceder los límites
    Then recibe un error controlado
    And la interacción no se envía
```

## Supuestos explícitos

- La validación de soporte de visión depende de la configuración activa del usuario.

## Preguntas abiertas

- ¿La UI debe permitir reordenar o quitar imágenes antes del envío?

## Riesgos de ambigüedad

- Si el soporte de visión depende del modelo además del proveedor, la validación debe quedar bien resuelta en TR.

## Veredicto B1

**Lista para TR:** Sí con observaciones

Observación: la verificación combinada proveedor/modelo para soporte de visión debe cerrarse técnicamente en TR.










