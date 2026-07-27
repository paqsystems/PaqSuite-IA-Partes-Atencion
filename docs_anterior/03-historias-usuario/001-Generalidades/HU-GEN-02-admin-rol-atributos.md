# HU-GEN-02-admin-rol-atributos — Administración de atributos de rol

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-admin-rol-atributos |
| **SPEC origen** | [SPEC-001-02-admin-mantenimiento-roles-permisos.md](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad (post-MVP) |
| **Prioridad** | Should |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-18) |
| **Última actualización** | 2026-06-18 |
| **Dependencias** | HU-GEN-02-admin-roles; HU-GEN-02-autorizacion-menu-api; HU-GEN-01-menu-general-sidebar |

## Trazabilidad SPEC

| Criterio SPEC | Cobertura en esta HU |
|---------------|----------------------|
| CA-07 | Atributos A/B/M/R; AccesoTotal exento |
| CA-08 | Coherencia menú ↔ atributos |
| F4 | Mantenimiento atributos |
| AMB-M-02-ADM-03 | Contrato API atributos (TR) |

Referencia Tango: HU-014 / TR-014.

## Narrativa

Como **usuario autorizado a administrar seguridad**,  
quiero **configurar permisos granulares por opción de menú** para roles sin acceso total,  
para **limitar qué procesos ve cada perfil y qué acciones ABM puede ejecutar** (alta, baja, modificación, consulta).

## Contexto funcional

Cuando `Pq_Rol.AccesoTotal = false`, el acceso a menú y acciones en pantalla se gobierna con **`PQ_RolAtributo`**: una fila por combinación **rol + procedimiento/opción de menú**, con flags `Permiso_Alta`, `Permiso_Baja`, `Permiso_Modi`, `Permiso_Repo`. Rol con acceso total **no** requiere esta pantalla (mensaje informativo si se accede por error).

## Alcance incluido

- Acceso desde acción **Atributos** en grilla de roles (HU-GEN-02-admin-roles).
- Vista árbol o grilla de opciones habilitadas en `pq_menus`.
- Marcar/desmarcar A/B/M/R por opción.
- Guardar matriz persistiendo en `PQ_RolAtributo` (unicidad rol + opción).
- Coherencia con `AuthorizedMenuBuilder` y `VisibilityPermissionGuard`.
- i18n y DevExtreme; tests según TR.

## Fuera de alcance

- Definición del árbol `pq_menus` (seed menú — HU-GEN-01).
- Asignación usuario–rol (`Pq_Permiso`).
- Roles con `AccesoTotal = true` (solo mensaje; sin edición granular).

## Reglas de negocio

1. Solo roles con **`AccesoTotal = false`** admiten edición de atributos.
2. Opciones mostradas provienen de `pq_menus` (`enabled` coherente con menú general).
3. Unicidad: una fila por `(id_rol, procedimiento/opción)`.
4. Cambios impactan menú visible y acciones UI/API para usuarios con ese rol asignado.
5. Autorización del proceso: misma regla que roles/permisos admin.

## Decisiones cerradas (A1 / B1)

| Tema | Decisión |
|------|----------|
| Rol AccesoTotal | Pantalla read-only o aviso; sin matriz editable |
| Campos | A/B/M/R alineados a convención MONO existente en seed MVP |

## Criterios de aceptación

- [ ] **CA-01:** Desde roles, acción Atributos abre pantalla del rol seleccionado.
- [ ] **CA-02:** Rol con `AccesoTotal = true` muestra aviso y no permite editar matriz granular.
- [ ] **CA-03:** Rol acotado: lista opciones de menú y permite marcar A/B/M/R.
- [ ] **CA-04:** Guardar persiste en `PQ_RolAtributo` sin duplicar filas por opción.
- [ ] **CA-05:** Usuario de prueba con ese rol ve menú coherente con atributos guardados.
- [ ] **CA-06:** Usuario sin autorización → 403.
- [ ] **CA-07:** Validación backend alineada con flags expuestos en UI.
- [ ] **CA-08:** E2E mínimo: abrir atributos de rol seed `vendedor.acotado.mvp` y verificar opciones.

## Escenarios Gherkin

```gherkin
Feature: Atributos de rol (MONO)

  Scenario: Configurar permisos granulares
    Given un rol R con AccesoTotal false
    And un administrador autorizado
    When abre atributos de R
    And habilita consulta en opción de menú M
    And guarda
    Then un usuario con rol R ve la opción M en su menú

  Scenario: Rol con acceso total
    Given un rol con AccesoTotal true
    When intenta editar atributos granulares
    Then se informa que no requiere configuración granular

  Scenario: Unicidad rol-opción
    Given atributos ya guardados para rol R y opción M
    When guarda nuevamente la misma opción
    Then actualiza la fila existente sin duplicar
```

## Supuestos explícitos

- Seed menú y seed atributos MVP (`paqsuite:seed-seguridad-mvp`) como baseline en dev.
- Contrato REST exacto se cierra en TR (AMB-M-02-ADM-03).

## Preguntas abiertas

- Ninguna bloqueante para HU; contrato API en TR.

## Riesgos de ambigüedad

- Desalineación seed atributos vs opciones menú nuevas: re-ejecutar seeds o migración idempotente (TR).

## Veredicto B1

**Lista para TR:** Sí










