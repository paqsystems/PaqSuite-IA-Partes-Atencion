# HU-GEN-01-ayuda-externa — Asistente IA (ayuda externa)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-01-ayuda-externa |
| **SPEC origen** | [SPEC-001-01-experiencia-base.md](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001 — Generalidades / Experiencia base |
| **Prioridad** | Should |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-28 |
| **Dependencias** | HU-GEN-01-menu-avatar; SPEC-001-04 (lectura parámetros, si aplica) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Definir puntos de ayuda externa o asistida | Ítem Asistente IA en avatar |
| Alcance: ayuda contextual | Apertura externa sin abandonar sesión |
| Trazabilidad HU-SPEC: Ayuda (Should) | Prioridad Should |
| Fuera alcance pixel-perfect / perf | No bloquea MVP Must |

## Narrativa

Como **usuario autenticado**,  
quiero **abrir ayuda operativa o asistente externo desde el menú avatar**,  
para **consultar documentación o chat sin abandonar la sesión ni la pantalla en curso**.

## Contexto funcional

SPEC-001-01 incluye en su alcance los **puntos de ayuda externa o asistida**. Esta HU es **Should** (no Must del bloque experiencia base). Se integra en el menú avatar post-login. La URL de destino no está definida en SPEC-001-01; lectura vía configuración global (SPEC-001-04) es supuesto para TR.

## Alcance incluido

- Ítem **Asistente IA** (o equivalente) en menú avatar.
- Apertura en nueva pestaña/ventana; la SPA actual permanece abierta.
- URL destino resuelta vía backend/configuración (no hardcodeada en componente).
- Si no hay URL configurada: ocultar ítem o mensaje de indisponibilidad.
- URL inválida o recurso caído: no bloquear flujo principal del portal.

## Fuera de alcance

- Contenido del chat o manual externo.
- Traducción del sitio de ayuda.
- Implementación del motor de ayuda (solo enlace de acceso).

## Reglas de negocio

1. Acción disponible desde cualquier pantalla post-login **si está configurada** (Should).
2. No aplica la preferencia “abrir menú en nueva pestaña” del sidebar; siempre abre destino externo.
3. Etiqueta distinguible de perfil y cierre de sesión.

## Criterios de aceptación

- [ ] Con URL configurada, clic abre nueva pestaña con destino correcto.
- [ ] Sin URL: ítem oculto o mensaje claro de ayuda no disponible.
- [ ] Cambiar URL en configuración no requiere redeploy del frontend.
- [ ] URL mal formada: mensaje al usuario sin crash de la app.
- [ ] E2E: clic no desmonta shell de la pestaña original.

## Escenarios Gherkin

```gherkin
Feature: Ayuda externa (SPEC-001-01 Should)

  Scenario: Abrir ayuda con URL configurada
    Given un usuario autenticado
    And existe URL de ayuda configurada
    When selecciona "Asistente IA" en el menú avatar
    Then se abre una nueva pestaña con la URL configurada
    And la pestaña del portal permanece activa

  Scenario: Sin URL configurada
    Given un usuario autenticado
    And no hay URL de ayuda configurada
    When abre el menú avatar
    Then no ve el ítem de ayuda o ve mensaje de indisponibilidad

  Scenario: URL inválida no rompe la aplicación
    Given un usuario autenticado
    And la URL configurada es inválida
    When intenta abrir la ayuda
    Then ve un mensaje de error controlado
    And puede seguir usando el portal
```

## Supuestos explícitos

- Nombre del parámetro global de URL: no en SPEC-001-01; inventario en SPEC-001-04 / TR.
- Bloqueo de popups del navegador: manejo UX opcional en TR.

## Preguntas abiertas

- ¿Nombre del parámetro y default de URL de ayuda en producto §10.6?
- ¿Should incluido en primer release o diferido?

## Riesgos de ambigüedad

- Prioridad Should: puede posponerse sin bloquear TR de Must del shell; coordinar con roadmap MVP.

## Veredicto B1

**Lista para TR:** Sí con observaciones (parámetro URL y priorización release)










