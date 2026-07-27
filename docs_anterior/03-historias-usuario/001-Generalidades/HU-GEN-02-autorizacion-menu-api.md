# HU-GEN-02-autorizacion-menu-api — Autorización de menú (backend)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-autorizacion-menu-api |
| **SPEC origen** | [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-28 |
| **Dependencias** | HU-GEN-02-login-sesion; HU-GEN-02-modelo-roles-permisos-seed; HU-GEN-01-menu-general-sidebar |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Relación menú ↔ autorización (backend + API menú) | Servicio + endpoint |
| Visibilidad por perfil vía permisos §7 y pq_menus | Filtrado backend |
| Criterio: permisos menú no reemplazan backend | Regla explícita RN4 |
| Trazabilidad HU-SPEC: Menú ↔ autorización | Objeto de esta HU |

## Narrativa

Como **sistema**,  
quiero **calcular el menú visible de cada usuario según roles y permisos seed**,  
para **que el sidebar refleje autorización real sin lógica hardcodeada en el cliente**.

## Contexto funcional

SPEC-001-02 incluye la **relación menú ↔ autorización** en backend y API de menú. El frontend (HU-GEN-01-menu-general-sidebar) solo renderiza la respuesta. Visibilidad de ítems por perfil: permisos §7 + `pq_menus` (referencia SPEC-001-01). **Ocultar en menú no autoriza por sí solo** — criterio medible del SPEC-001-02.

## Alcance incluido

- Servicio de autorización post-login basado en `Pq_Permiso` y roles seed (MONO: instalación única).
- Filtrado de ítems `pq_menus` según permisos del usuario.
- Endpoint protegido y autenticado que devuelve árbol ordenado para sidebar.
- Respuesta vacía o mínima controlada si no hay ítems autorizados (sin error 500).
- Regla documentada: menú visible ≠ autorización de endpoints de negocio.

## Fuera de alcance

- Renderizado del sidebar (HU-GEN-01-menu-general-sidebar).
- ABM de roles/atributos en UI (SPEC fuera de alcance).
- Políticas por endpoint de negocio (HU-GEN-02-politicas-endpoints).

## Reglas de negocio

1. El frontend no recalcula permisos; solo renderiza la respuesta API.
2. Ítems deshabilitados en `pq_menus` no se incluyen en menú visible.
3. Usuario sin opciones autorizadas: árbol vacío o mínimo acordado, sin error 500.
4. **Permisos de menú no reemplazan controles backend** (criterio SPEC-001-02).
5. Usuario con varios roles: unión de opciones autorizadas (supuesto inferido del modelo seed).

## Criterios de aceptación

- [ ] Usuario con acceso total (según seed) recibe ítems habilitados del menú MVP.
- [ ] Usuario con rol granular solo recibe ítems con permiso correspondiente.
- [ ] Usuario con dos roles recibe unión de opciones.
- [ ] Endpoint autenticado; sin token → 401.
- [ ] Tests de integración: perfiles acceso total, granular y sin atributos.
- [ ] OpenAPI del endpoint documentado en TR.

## Escenarios Gherkin

```gherkin
Feature: Autorización de menú API (SPEC-001-02)

  Scenario: Menú filtrado por permisos
    Given un usuario autenticado con rol vendedor granular
    When solicita GET user/menu
    Then recibe solo ítems autorizados para su rol
    And el árbol está ordenado

  Scenario: Acceso total en seed
    Given un usuario con rol supervisor de acceso total
    When solicita el menú
    Then recibe todos los ítems habilitados del seed MVP

  Scenario: Sin token
    Given una petición sin autenticación
    When solicita el menú
    Then recibe HTTP 401

  Scenario: Menú vacío controlado
    Given un usuario autenticado sin ítems autorizados
    When solicita el menú
    Then recibe respuesta vacía o mínima sin error 500
```

## Supuestos explícitos

- Lógica `AccesoTotal` vs `PQ_RolAtributo`: no detallada en SPEC-001-02; algoritmo en TR (contexto MONO).
- Campos de respuesta (`id`, `text`, `parentId`, `orden`, `routeName`): contrato TR.
- `pq_menus` corrupto (ciclos): manejo degradado en TR.

## Preguntas abiertas

- ¿Formato exacto del árbol JSON y códigos de atributo por ítem menú?

## Riesgos de ambigüedad

- Desalineación seed menú ↔ seed atributos produce menús vacíos; validar con matriz-permisos-mvp.md.

## Veredicto B1

**Lista para TR:** Sí con observaciones (algoritmo filtrado y contrato JSON)










