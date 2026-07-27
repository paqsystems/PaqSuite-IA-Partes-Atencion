# HU-GEN-01-menu-general-sidebar — Menú general y sidebar dinámico

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-01-menu-general-sidebar |
| **SPEC origen** | [SPEC-001-01-experiencia-base.md](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001 — Generalidades / Experiencia base |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-29 |
| **TR** | [TR-GEN-01-menu-general-sidebar](../../04-tareas/001-Generalidades/TR-GEN-01-menu-general-sidebar.md) — D1 §3.4 (2026-05-29) |
| **Dependencias** | HU-GEN-01-shell-layout; HU-GEN-02-autorizacion-menu-api; HU-GEN-02-modelo-roles-permisos-seed |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Definir navegación principal y menús | Sidebar dinámico desde API |
| Menú MVP 11 ítems (copia §8) | Seed alineado a lista SPEC |
| Visibilidad por perfil vía §7 y `pq_menus`/permisos | Frontend no hardcodea visibilidad |
| Flujo post-login: menú general desde API | Consumo API al montar shell |
| Criterio menú login/post-login | Solo post-login en sidebar |
| Trazabilidad HU-SPEC: Menú §8 producto | Objeto de esta HU |
| Controles menú (`docs/00-contexto/_mono/01-experiencia-base/menu-general.md`) | Tres toggles de presentación en header (norma MONO) |

## Narrativa

Como **usuario autenticado**,  
quiero **ver en el sidebar solo los procesos que tengo autorizados**,  
para **acceder a los 11 procesos del menú MVP sin opciones hardcodeadas por perfil en el cliente**.

## Contexto funcional

SPEC-001-01 define la navegación principal del portal. El menú MVP incluye 11 ítems (carga, presupuestos, pedidos, consultas, dashboard, logs, etc.). La **visibilidad por perfil** se rige por permisos §7 y seed `pq_menus`; el frontend solo renderiza lo autorizado (relación menú ↔ autorización en SPEC-001-02).

## Alcance incluido

- Consumo de API de menú de usuario al montar el shell post-login.
- Renderizado de árbol jerárquico devuelto por backend (filtrado por roles/permisos en HU-GEN-02-autorizacion-menu-api).
- Navegación hacia rutas SPA de cada ítem autorizado.
- Resaltado del ítem activo y expansión de padres del nodo activo.
- **Tres controles en header** (norma MONO en `docs/00-contexto/_mono/01-experiencia-base/menu-general.md`): (1) mostrar/ocultar panel sidebar; (2) expandir/contraer todas las ramas del árbol; (3) vista `allBranches` vs `operationalOnly` (solo ítems con ruta de proceso).
- Persistencia de los tres controles **por usuario o por terminal** (`sidebarVisible`, `menuTreeExpanded`, `menuDisplayMode`); **nunca** por empresa ni parámetro global del sistema (norma MONO en `menu-general.md` § Alcance de persistencia).
- Seed versionado e idempotente de `pq_menus` con los **11 ítems** del SPEC (agrupadores opcionales con `nodeType` group/process).
- Menú mínimo de fallback si la API falla o devuelve vacío (layout usable).
- Textos visibles respetan idioma activo (SPEC-001-01 / HU-GEN-01-idioma).
- Preferencia abrir en nueva pestaña (desde menú avatar, HU-GEN-01-menu-avatar).

## Fuera de alcance

- Detalle de ítems de negocio fuera del menú §8 del SPEC.
- ABM de menú en producción.
- Lógica de autorización en backend (HU-GEN-02-autorizacion-menu-api).
- Pantalla de login sin menú lateral (flujo SPEC paso 1).

## Reglas de negocio

1. El frontend **no** hardcodea visibilidad por perfil; solo renderiza la respuesta de la API (SPEC + SPEC-001-02).
2. Los 11 ítems del menú MVP deben existir en seed; visibilidad efectiva depende de permisos §7.
3. En MONO no se recarga menú al cambiar empresa (no aplica selector empresa).
4. Orden entre hermanos según campo `orden` del seed/API (supuesto técnico para TR).
5. Los controles de presentación del menú **no** ocultan ítems no autorizados ni conceden acceso; la API sigue siendo la fuente de visibilidad.
6. En modo `operationalOnly`, solo se listan nodos operativos (`routePath`); agrupadores sin ruta no se muestran como filas.
7. El estado de los tres controles se persiste **por usuario** o **por terminal/navegador**; **no** por empresa, tenant ni configuración global compartida por todos los usuarios.

## Criterios de aceptación

- [ ] Seed idempotente incluye los 11 procesos listados en SPEC-001-01 (menú MVP §8).
- [ ] Sidebar muestra solo ítems autorizados para el usuario de prueba (perfiles vía permisos).
- [ ] Al navegar, el ítem activo queda resaltado y sus padres expandidos.
- [ ] Fallo de API: mensaje o menú mínimo sin pantalla en blanco.
- [ ] Usuario sin ítems autorizados: layout usable con mensaje informativo.
- [ ] E2E: usuario con permisos ve ítem de pedidos; usuario sin permiso no lo ve.
- [ ] Hamburguesa oculta y muestra el panel sidebar sin perder sesión.
- [ ] Expandir/contraer afecta todas las ramas visibles; al elegir un ítem, sus padres quedan expandidos.
- [ ] Vista `operationalOnly` oculta agrupadores y muestra solo procesos navegables (con seed jerárquico de prueba).
- [ ] Tras logout y login con **otro** usuario, los tres controles reflejan el estado de **ese** usuario (o defaults), no el del anterior.
- [ ] No existe lectura/escritura de estos estados en parámetros por empresa ni en defaults globales de instalación.

## Escenarios Gherkin

```gherkin
Feature: Menú general sidebar (SPEC-001-01)

  Scenario: Sidebar muestra ítems autorizados post-login
    Given un usuario autenticado con permisos de vendedor
    When el shell carga el menú desde la API
    Then ve en el sidebar solo los ítems autorizados para su perfil
    And no ve ítems para los que no tiene permiso

  Scenario: Navegación resalta ítem activo
    Given un usuario con menú cargado
    When selecciona "Pedidos ingresados"
    Then el ítem queda resaltado
    And los agrupadores padres están expandidos

  Scenario: Fallo de API no rompe el layout
    Given un usuario autenticado
    When la API de menú falla o devuelve vacío
    Then el shell permanece usable
    And se muestra menú mínimo o mensaje informativo

  Scenario: Login no muestra menú de procesos
    Given un usuario en pantalla de login
    Then no ve sidebar con ítems de procesos del menú MVP

  Scenario: Ocultar y mostrar panel del sidebar
    Given un usuario autenticado con el shell visible
    When activa el control de hamburguesa para ocultar el sidebar
    Then el área principal ocupa el espacio del menú
    When vuelve a activar el control
    Then el sidebar vuelve a ser visible

  Scenario: Expandir y contraer todo el árbol
    Given un menú con agrupadores y el sidebar visible
    When activa expandir/contraer todo
    Then todas las ramas quedan expandidas o contraídas según el estado

  Scenario: Vista solo operativas
    Given un menú con agrupadores y procesos hijos autorizados
    When selecciona vista solo operativas
    Then no ve filas de agrupadores sin ruta
    And ve los procesos hijos autorizados ordenados
```

## Supuestos explícitos

- Endpoint `GET /api/v1/user/menu`, campos `routeName`, `procedimiento`: no definidos en SPEC-001-01; contrato en TR/HU-GEN-02-autorizacion-menu-api.
- Campo opcional `nodeType` (`group` \| `process`) derivado en API desde **`tipo_proceso` + `routeName`** de `pq_menus` (TR §3.4 D1-1); no columna nueva en BD.
- Nombre del archivo seed (`PQ_MENUS.seed.json`): convención de implementación.
- Ubicación física de los tres iconos en header: HU-GEN-01-shell-layout (contenedor); lógica en esta HU.

## Preguntas abiertas

- ~~¿Mapeo exacto ítem §8 → `routeName` y `procedimiento` en seed?~~ → **Resuelto C1:** `backend/config/paqsuite_mvp.php` (TR §3.3 R-C1-10).

## Riesgos de ambigüedad

- Matriz ítem ↔ permiso por perfil vive en producto §7; esta HU depende de seed y HU-GEN-02 correctamente alineados.

## Veredicto B1

**Lista para TR:** Sí con observaciones (contrato API y seed detallado en TR).










