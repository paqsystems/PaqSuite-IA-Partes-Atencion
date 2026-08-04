# HU-GEN-01-apariencia-temas — Apariencia DevExtreme por usuario

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-01-apariencia-temas |
| **SPEC origen** | [SPEC-001-01-experiencia-base.md](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001 — Generalidades / Experiencia base |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-31 |
| **Dependencias** | HU-GEN-01-menu-avatar; HU-GEN-01-shell-layout |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Reglas de apariencia/temas (producto §8.1) | Selector y aplicación de tema |
| Tema por defecto `generic.light` | Fallback explícito |
| Preferencia por usuario en MONO | Persistencia users.theme |
| Flujo post-login: menú avatar preferencias | Entrada desde avatar |
| Persistencia users.theme | AC persistencia |
| Criterio: tema sin ambigüedad | AC medibles |
| Trazabilidad HU-SPEC: §8.1 tema | Objeto de esta HU |

## Narrativa

Como **usuario autenticado en MONO**,  
quiero **elegir la apariencia global desde el menú avatar**,  
para **trabajar con un tema que se aplique a toda la interfaz y se recuerde entre sesiones**.

## Contexto funcional

SPEC-001-01 define reglas de apariencia/temas con valor MVP **`generic.light`** para usuario sin preferencia. En MONO la apariencia es **preferencia personal por usuario** (no por empresa; matriz SPEC-001-05). Acceso vía menú avatar (flujo post-login del SPEC).

## Alcance incluido

- Opción **Apariencia** en menú avatar con listado de temas del catálogo cerrado (referencia técnica: contexto DevExtreme citado en SPEC §8.1).
- Aplicación inmediata del tema a shell y controles de la UI.
- Persistencia en `users.theme` server-side (flujo SPEC paso 3).
- Fallback: valor nulo o inválido → **`generic.light`**.

## Fuera de alcance

- Implementación visual pixel-perfect definitiva (SPEC fuera de alcance).
- Tema por empresa/tenant (MULTI; SPEC-001-05).
- ThemeBuilder o temas arbitrarios por cliente.

## Reglas de negocio

1. Tema por defecto (usuario sin preferencia): **`generic.light`** (SPEC §8.1).
2. En MONO la apariencia es **preferencia personal**, no configuración de administración.
3. Un solo tema activo por usuario en toda la UI web.
4. Solo valores del **catálogo cerrado** DevExtreme (detalle de nombres en TR; SPEC referencia contexto).

## Criterios de aceptación

- [ ] Desde menú avatar se abre selector con temas disponibles.
- [ ] Al elegir tema, la UI cambia sin recargar la página completa.
- [ ] Valor persiste en `users.theme` y se aplica al siguiente login.
- [ ] Tema inválido o nulo en BD usa fallback `generic.light`.
- [ ] Error al guardar: revertir al último válido y notificar.
- [ ] E2E: cambiar tema → tema activo verificable en contenedor raíz.

## Escenarios Gherkin

```gherkin
Feature: Apariencia y temas (SPEC-001-01 §8.1)

  Scenario: Tema por defecto generic.light
    Given un usuario sin users.theme
    When accede al shell post-login
    Then la interfaz usa el tema generic.light

  Scenario: Cambiar y persistir tema
    Given un usuario autenticado
    When selecciona un tema desde Apariencia en el menú avatar
    Then la UI aplica el tema inmediatamente
    And users.theme se persiste
    And en el próximo login ve el mismo tema

  Scenario: Tema inválido en base de datos
    Given un usuario con users.theme con valor inválido
    When accede al portal
    Then se aplica generic.light sin error fatal

  Scenario: No hay tema por empresa en MONO
    Given un usuario autenticado en modo MONO
    When abre Apariencia
    Then configura solo su preferencia personal
    And no ve opción de tema por empresa
```

## Supuestos explícitos

- Lista completa de temas DevExtreme (generic, material, fluent): SPEC cita catálogo cerrado en contexto; enumerar en TR.
- Integración DevExtreme ThemeSwitcher: stack referenciado en entradas requeridas del SPEC (`devextreme-norms.md`).

## Preguntas abiertas

- ¿Temas adicionales a `generic.light` / `generic.dark` incluidos en MVP?

## Riesgos de ambigüedad

- Consistencia con SPEC-001-03 (checklist: consistencia con tema activo): pantallas futuras deben respetar tema en TR transversal.

## Veredicto B1

**Lista para TR:** Sí con observaciones (catálogo exacto de temas)

## Cierre F

- **Resultado:** Aprobada con observaciones.
- **Soporte de verificación:** [TR-GEN-01-apariencia-temas](../../04-tareas/001-Generalidades/TR-GEN-01-apariencia-temas.md) y [F-GEN-01-02-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-01-02-cierre-formal.md).
- **Observaciones:** el cierre F confirma catálogo ampliado, flujo `Aplicar/Confirmar/Cancelar`, i18n del modal y herencia de paleta del shell; la evidencia backend se apoya en validaciones previas documentadas y en tests frontend recientes.










