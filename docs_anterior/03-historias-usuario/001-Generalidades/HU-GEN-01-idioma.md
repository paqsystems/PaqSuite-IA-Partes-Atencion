# HU-GEN-01-idioma — Selector de idioma e i18n base

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-01-idioma |
| **SPEC origen** | [SPEC-001-01-experiencia-base.md](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001 — Generalidades / Experiencia base |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-31 |
| **Dependencias** | HU-GEN-01-shell-layout; HU-GEN-02-login-sesion |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Estrategia de idioma inicial (producto §8.1) | Reglas de resolución de idioma |
| Idioma por defecto `es` | Fallback explícito |
| Si `users.locale` vacío → `navigator.language` | Regla de negocio 2 |
| Si no soportado → `es` | AC fallback |
| Flujo login: selector de idioma | Selector en login |
| Flujo post-login: persistencia `users.locale` | Persistencia server-side |
| Criterio: idioma sin ambigüedad | AC medibles |
| Trazabilidad HU-SPEC: §8.1 idioma | Objeto de esta HU |

## Narrativa

Como **usuario del portal**,  
quiero **elegir el idioma de la interfaz en login y en el header**,  
para **leer labels y mensajes en mi idioma preferido con persistencia entre sesiones**.

## Contexto funcional

SPEC-001-01 fija la estrategia de idioma para el MVP. Valores por defecto (copia producto §8.1): idioma **`es`**; resolución con `users.locale`, `navigator.language` y fallback a `es`. La preferencia es **por usuario** en MONO (no por empresa/tenant).

## Alcance incluido

- Selector visible en **login** (flujo SPEC paso 1) y en **header** post-login (no en menú avatar).
- Cambio de idioma sin recarga completa de la aplicación.
- Regla de idioma inicial según tabla §8.1 del SPEC.
- Persistencia: autenticado → `users.locale`; no autenticado → preferencia temporal en cliente.
- Textos de sistema (shell, menús, validaciones base) vía claves i18n.

## Fuera de alcance

- Traducción de datos de negocio (nombres de clientes, artículos, etc.).
- Detalle de ítems de negocio SistemaPartes fuera del shell (SPEC fuera de alcance).
- Optimización avanzada de accesibilidad (SPEC fuera de alcance).

## Reglas de negocio

1. Idioma por defecto del producto: **`es`** (SPEC §8.1).
2. Si `users.locale` tiene valor → usar ese valor.
3. Si `users.locale` vacío → `navigator.language`; si no soportado → **`es`**.
4. La preferencia de idioma es **por usuario**, no por tenant (MONO).
5. El idioma activo debe distinguirse visualmente en el selector.

## Criterios de aceptación

- [ ] Selector presente en pantalla de login y en header del shell.
- [ ] Al cambiar idioma, labels visibles del shell y login se actualizan al instante.
- [ ] Usuario autenticado: cambio persiste en `users.locale` y se recupera en próximo login.
- [ ] Usuario sin locale: aplica `navigator.language` con fallback a `es`.
- [ ] Idioma no soportado cae a `es` sin error fatal.
- [ ] Fallo al guardar `users.locale`: mantener idioma en cliente y avisar.
- [ ] E2E: cambiar idioma en header → textos clave actualizados.

## Escenarios Gherkin

```gherkin
Feature: Selector de idioma (SPEC-001-01 §8.1)

  Scenario: Idioma por defecto es español
    Given un usuario nuevo sin users.locale
    And navigator.language no está soportado
    When accede al portal
    Then la interfaz se muestra en español (es)

  Scenario: Persistir idioma tras login
    Given un usuario autenticado
    When cambia el idioma desde el header
    Then users.locale se actualiza
    And en el próximo login ve el mismo idioma

  Scenario: Selector en login
    Given un visitante en pantalla de login
    Then ve selector de idioma
    And puede cambiar idioma antes de autenticarse

  Scenario: Fallback desde navigator.language
    Given un usuario con users.locale vacío
    And navigator.language es un idioma soportado
    When accede al portal
    Then la interfaz usa navigator.language
```

## Supuestos explícitos

- Catálogo de idiomas soportados (`en`, `pt`, etc.): SPEC solo fija default `es`; lista completa en TR.
- Archivos de recursos i18n y claves: implementación frontend; no en SPEC-001-01.

## Preguntas abiertas

- ¿Cuáles idiomas además de `es` entran en el catálogo cerrado MVP?

## Riesgos de ambigüedad

- Referencia técnica a `idioma-multilingual.md` en SPEC apunta a contexto MONO; detalle de archivos locale en TR.

## Veredicto B1

**Lista para TR:** Sí con observaciones (catálogo de idiomas MVP)

## Cierre F

- **Resultado:** Aprobada.
- **Soporte de verificación:** [TR-GEN-01-idioma](../../04-tareas/001-Generalidades/TR-GEN-01-idioma.md) y [F-GEN-01-02-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-01-02-cierre-formal.md).
- **Observaciones:** el comportamiento de locale quedó además consolidado en recuperación de contraseña mediante propagación del `locale` al enlace de reset.










