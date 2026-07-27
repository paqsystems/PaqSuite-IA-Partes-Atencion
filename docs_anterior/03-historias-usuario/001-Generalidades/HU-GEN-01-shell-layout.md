# HU-GEN-01-shell-layout — Shell principal post-login

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-01-shell-layout |
| **SPEC origen** | [SPEC-001-01-experiencia-base.md](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001 — Generalidades / Experiencia base |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-09 (Parte I — CC PQ #3) |
| **Dependencias** | HU-GEN-02 (login y sesión); SPEC-001-05 (tenancy MONO) |
| **TR** | [TR-GEN-01-shell-layout](../../04-tareas/001-Generalidades/TR-GEN-01-shell-layout.md) — C1/D1/D cerrados 2026-05-29 |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Shell/layout trazable a producto §5 MONO | AC: shell sin selector de empresa |
| Definir shell y zonas (header, sidebar, área principal, footer) | Alcance: cuatro zonas identificables |
| Flujo post-login: shell completo | AC: entrada directa tras login |
| Entregable shell/navegación | Layout estable post-login |
| Fuera alcance: pixel-perfect | Sección fuera de alcance |
| Trazabilidad HU-SPEC: Shell, zonas | Objeto completo de esta HU |

## Narrativa

Como **usuario autenticado** del portal SistemaPartes,  
quiero **disponer de un shell estable con header, sidebar, área principal y footer**,  
para **navegar entre procesos sin perder contexto global ni controles de sesión**.

## Contexto funcional

SPEC-001-01 establece lineamientos base de experiencia para el arranque del producto (layout, navegación, idioma, apariencia, ayuda). Esta HU implementa el **shell principal** y sus **zonas de pantalla** en estado **post-login**, alineado al modo MONO (sin selector de empresa). Estado de ejecución del SPEC: implementable en MVP.

## Alcance incluido

- Layout post-login con cuatro zonas: header, sidebar (contenedor), área principal, footer.
- Entrada directa al shell tras login exitoso (paso 2 del flujo login vs post-login del SPEC).
- Header: contenedor para marca, **grupo de tres controles del menú** (hamburguesa, expandir/contraer árbol, vista operativa — lógica en HU-GEN-01-menu-general-sidebar), selector de idioma y avatar (contenido en HUs hermanas).
- Sidebar: contenedor listo para menú dinámico (contenido en HU-GEN-01-menu-general-sidebar).
- Área principal: outlet de rutas / procesos.
- Footer: versión, identidad de sesión legible, leyenda institucional.
- Comportamiento responsive que preserve la semántica de las cuatro zonas.

## Fuera de alcance

- Implementación visual pixel-perfect definitiva (SPEC fuera de alcance).
- Optimización avanzada de accesibilidad o performance UI (SPEC fuera de alcance).
- Contenido del menú lateral (HU-GEN-01-menu-general-sidebar).
- Login, logout y recuperación de contraseña (HU-GEN-02).
- Selector de idioma, temas y menú avatar (HUs hermanas SPEC-001-01).

## Reglas de negocio

1. En **MONO** no se muestra selector de empresa en header ni avatar (coherente con producto §5 referenciado por SPEC).
2. Tras login, el usuario accede al **shell completo**; el menú general se carga vía API (flujo SPEC paso 2).
3. El header permanece visible y consistente al cambiar de proceso en el área principal.
4. El footer no reemplaza toasts, confirmaciones ni indicadores de carga del proceso activo.
5. Sesión inválida o expirada: redirigir a login sin renderizar shell.
6. **CC PQ #3:** SelectBox/Lookup/DropDown DevExtreme: durante carga del catálogo, indicador **«cargando…»** (i18n) y control **deshabilitado** hasta finalizar el fetch.
7. **CC PQ #3:** Si la búsqueda deja **exactamente un** ítem, seleccionarlo automáticamente (patrón transversal reutilizable en pantallas 101).

## Criterios de aceptación

- [ ] Tras autenticación válida, el usuario ve el shell completo sin pantalla intermedia de empresa.
- [ ] Existen las cuatro zonas identificables (header, sidebar, main, footer) en viewport desktop.
- [ ] El área principal renderiza la ruta activa sin desmontar header/sidebar/footer.
- [ ] En viewport reducido, el sidebar puede colapsarse u ocultarse sin perder acceso al menú.
- [ ] El footer muestra al menos versión de aplicación e identificador o nombre de usuario visible.
- [ ] No hay selector de empresa en el shell (MONO).
- [ ] Error al cargar preferencias de usuario: shell visible con valores por defecto del SPEC (`es`, `generic.light` vía HUs de idioma/tema).
- [ ] E2E: login → shell visible con `data-testid` en zonas clave.
- [ ] El header expone los tres `data-testid` de controles de menú documentados en TR-GEN-01-menu-general-sidebar.
- [x] **CA-CC3-01:** SelectBox de referencia aplica indicador cargando y bloqueo durante fetch.
- [x] **CA-CC3-02:** Con un solo match tras búsqueda, el valor queda seleccionado sin segundo clic.
- [x] **CA-CC3-03:** Textos vía i18n (5 locales); `data-testid` estables.
- [x] **CA-CC3-04:** Patrón documentado para reutilizar en HU de proceso (101-004, 101-006, etc.).

## Escenarios Gherkin

```gherkin
Feature: Shell principal post-login (SPEC-001-01)

  Scenario: Usuario autenticado accede al shell completo
    Given un usuario con sesión válida en modo MONO
    When completa el login exitosamente
    Then ve header, sidebar, área principal y footer
    And no ve selector de empresa

  Scenario: Navegación entre procesos mantiene el shell
    Given un usuario autenticado en el shell
    When navega a otro proceso desde el sidebar
    Then header, sidebar y footer permanecen visibles
    And el área principal muestra el proceso activo

  Scenario: Sesión inválida no muestra shell
    Given un token de sesión inválido o expirado
    When intenta acceder a una ruta protegida del shell
    Then es redirigido a la pantalla de login
    And no se renderiza el shell

  Scenario: Shell usable en viewport reducido
    Given un usuario autenticado en viewport móvil
    When abre el menú lateral
    Then puede acceder a la navegación sin perder las cuatro zonas funcionales
```

## Supuestos explícitos

- Identificadores `data-testid` (`shell-header`, `shell-sidebar`, etc.): convención de tests del proyecto; no definidos en SPEC-001-01.
- Ruta de inicio post-login (dashboard u otra): no especificada en SPEC-001-01; definir en TR.
- Detalle responsive (colapso sidebar): inferido del alcance de zonas; comportamiento exacto en TR.

## Preguntas abiertas

- ~~¿Cuál es la ruta de landing inmediata tras login?~~ → **Resuelto en TR §3.2 D1-1:** `/dashboard`.

## Riesgos de ambigüedad

- Integración con HU-GEN-01-idioma y HU-GEN-01-menu-avatar en header: coordinar en TR para no duplicar controles.
- Referencias técnicas a `shell-layout.md` del contexto MONO: detalle de implementación fuera del SPEC; validar en TR.

## Veredicto B1

**Lista para TR:** Sí con observaciones (ruta de inicio post-login pendiente).

## Cierre F

- **Resultado:** Aprobada.
- **Soporte de verificación:** [TR-GEN-01-shell-layout](../../04-tareas/001-Generalidades/TR-GEN-01-shell-layout.md) y [F-GEN-01-02-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-01-02-cierre-formal.md).
- **Observaciones:** sin hallazgos críticos abiertos para las cuatro zonas, guard de sesión y comportamiento responsive base.










