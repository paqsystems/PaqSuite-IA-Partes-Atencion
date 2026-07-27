# HU-GEN-02-admin-roles — Administración de roles

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-admin-roles |
| **SPEC origen** | [SPEC-001-02-admin-mantenimiento-roles-permisos.md](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad (post-MVP) |
| **Prioridad** | Should |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-18) |
| **Última actualización** | 2026-06-18 |
| **Dependencias** | HU-GEN-02-login-sesion; HU-GEN-02-modelo-roles-permisos-seed; HU-GEN-03-patron-abm; HU-GEN-02-autorizacion-menu-api |

## Trazabilidad SPEC

| Criterio SPEC | Cobertura en esta HU |
|---------------|----------------------|
| CA-06 | ABM roles, unicidad nombre, baja condicionada |
| CA-07 (parcial) | `AccesoTotal` simplifica necesidad de atributos |
| CA-05 | Autorización del proceso |
| F4 (mantenimiento rol) | Narrativa, AC |
| AMB-M-02-ADM-05 | Ítems menú admin roles (TR) |

## Narrativa

Como **usuario autorizado a administrar seguridad**,  
quiero **dar de alta, editar, listar y eliminar roles reutilizables** (`Pq_Rol`),  
para **definir perfiles de acceso que luego se asignen a usuarios mediante permisos**.

## Contexto funcional

Epic **post-MVP** derivado de [`mantenimiento-roles-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/mantenimiento-roles-permisos.md). En **MONO / SistemaPartes** no interviene empresa. Referencia Tango: HU-012. Patrón UI: [`patrones-abm.md`](../../00-contexto/_mono/03-ui-transversal/patrones-abm.md) + HU-GEN-03.

## Alcance incluido

- Listado en grilla: nombre, descripción, indicador **Acceso total**.
- Alta y edición en modal DevExtreme: `NombreRol`, `DescripcionRol`, `AccesoTotal`.
- Baja con confirmación; bloqueada si el rol tiene filas en `Pq_Permiso` (política sin cascada).
- Acción **Atributos** por fila cuando `AccesoTotal = false` → navega a HU-GEN-02-admin-rol-atributos.
- i18n `admin.roles.*`; `data-testid` en grilla y acciones.
- API `/api/v1/admin/roles` (detalle en TR).

## Fuera de alcance

- Asignación usuario–rol (`Pq_Permiso`) — HU-GEN-02-admin-permisos.
- Edición de atributos granulares — HU-GEN-02-admin-rol-atributos.
- ABM de usuarios.
- Seed MVP (`paqsuite:seed-seguridad-mvp`).

## Reglas de negocio

1. Solo accede quien tenga **`AccesoTotal`** en algún rol asignado **o** permisos ABM en la opción de menú del proceso (SPEC A1).
2. `NombreRol` obligatorio y único.
3. Rol con **`AccesoTotal = true`** no requiere mantenimiento de `PQ_RolAtributo` para operar a nivel menú.
4. No eliminar rol referenciado en `Pq_Permiso`; mensaje claro al operador.
5. Cambios en roles impactan menú/acciones vía unión de permisos (no recalcular en frontend).

## Decisiones cerradas (A1 / B1)

| Tema | Decisión |
|------|----------|
| Empresa | No visible; no aplica MONO |
| Baja de rol en uso | **Bloqueada** (sin cascada automática) |
| Patrón UI | Modal sobre grilla (HU-GEN-03) |

## Criterios de aceptación

- [ ] **CA-01:** Usuario autorizado lista roles con columnas nombre, descripción, acceso total.
- [ ] **CA-02:** Alta de rol válido persiste en `Pq_Rol` y refresca grilla.
- [ ] **CA-03:** Edición actualiza nombre, descripción y `AccesoTotal`.
- [ ] **CA-04:** Intento de alta con nombre duplicado → error 422 visible.
- [ ] **CA-05:** Eliminar rol **sin** permisos asignados → baja exitosa con confirmación.
- [ ] **CA-06:** Eliminar rol **con** permisos asignados → rechazado con mensaje.
- [ ] **CA-07:** Rol con `AccesoTotal = false` muestra acción **Atributos** operativa.
- [ ] **CA-08:** Usuario sin autorización → 403 API / sin acceso a pantalla.
- [ ] **CA-09:** Textos vía i18n `admin.roles.*` en cinco idiomas.
- [ ] **CA-10:** E2E smoke: listar y abrir modal alta (referencia Tango roles-admin).

## Escenarios Gherkin

```gherkin
Feature: Administración de roles (MONO)

  Scenario: Listar roles autorizado
    Given un usuario con permiso sobre administración de roles
    When accede a la pantalla de roles
    Then ve la grilla con roles existentes

  Scenario: Crear rol con acceso total
    Given un usuario autorizado en roles
    When completa alta con nombre único y AccesoTotal activo
    And confirma
    Then el rol queda visible en el listado

  Scenario: Nombre duplicado
    Given existe un rol "Supervisor"
    When intenta crear otro rol "Supervisor"
    Then recibe error de validación
    And no se crea el registro

  Scenario: Eliminar rol en uso
    Given un rol con asignaciones en Pq_Permiso
    When intenta eliminarlo
    Then la operación es rechazada
    And se informa que el rol está en uso

  Scenario: Navegar a atributos
    Given un rol sin AccesoTotal
    When pulsa Atributos en la fila
    Then accede al mantenimiento de atributos de ese rol
```

## Supuestos explícitos

- Tabla `Pq_Rol` existe (seed o migración MVP).
- Rutas y opciones de menú admin se seedean en TR (AMB-M-02-ADM-05).

## Preguntas abiertas

- Ninguna bloqueante para TR.

## Riesgos de ambigüedad

- Conflicto entre roles seed MVP y nombres creados en UI: validar unicidad case-sensitive según BD (TR).

## Veredicto B1

**Lista para TR:** Sí










