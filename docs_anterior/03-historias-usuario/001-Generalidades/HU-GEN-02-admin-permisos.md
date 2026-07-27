# HU-GEN-02-admin-permisos — Administración de permisos (asignación individual)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-admin-permisos |
| **SPEC origen** | [SPEC-001-02-admin-mantenimiento-roles-permisos.md](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad (post-MVP) |
| **Prioridad** | Should |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-18) |
| **Última actualización** | 2026-06-18 |
| **Dependencias** | HU-GEN-02-admin-roles; HU-GEN-02-login-sesion; HU-GEN-03-patron-abm |

## Trazabilidad SPEC

| Criterio SPEC | Cobertura en esta HU |
|---------------|----------------------|
| CA-01 | CRUD individual usuario–rol |
| CA-05 | Autorización |
| CA-08 | Efecto en menú vía asignaciones |
| CA-10 | Sin ABM usuarios; solo lookup |
| F1 | Flujo alta individual |
| AMB-M-02-ADM-01 | `id_empresa` opaco en backend |

## Narrativa

Como **usuario autorizado a administrar permisos**,  
quiero **asignar, editar y quitar roles a usuarios existentes** de forma individual,  
para **controlar quién puede operar en el portal y con qué perfil**, sin gestionar datos maestros de usuario en SistemaPartes.

## Contexto funcional

En **MONO**, cada fila de `Pq_Permiso` vincula **usuario + rol** (empresa fija en backend si el esquema legado lo exige). Referencia Tango HU-013 adaptada: **sin selector de empresa**. Usuarios provienen del **ERP / sync** — catálogo read-only. Un usuario puede acumular **varios roles**; la autorización efectiva es la **unión** de permisos.

## Alcance incluido

- Grilla de asignaciones: usuario, rol (sin columna empresa en UI).
- Filtros por usuario y/o rol.
- Alta en modal: SelectBox usuario + SelectBox rol (formato código – descripción).
- Edición: cambiar rol de una asignación existente.
- Baja con confirmación (`admin.permisos.confirmDelete`).
- Validación unicidad par usuario+rol → 422.
- i18n `admin.permisos.*` (excepto claves bulk y empresa).
- API CRUD `/api/v1/admin/permisos`.

## Fuera de alcance

- Asignación masiva — HU-GEN-02-admin-permisos-bulk.
- Alta/edición/baja de `users`.
- Modo por empresa (Tango MULTI).

## Reglas de negocio

1. Usuario y rol deben existir y estar habilitados según política del módulo.
2. Combinación **(id_usuario, id_rol)** única en MONO.
3. Eliminar la última asignación de un usuario le impide operar tras el login (RN existente SPEC-001-02).
4. Autorización: `AccesoTotal` **o** atributos ABM en opción de menú de permisos.
5. Backend resuelve `id_empresa` constante; no viaja en payload del cliente.

## Decisiones cerradas (A1 / B1)

| Tema | Decisión |
|------|----------|
| Campos visibles | Usuario + rol únicamente |
| Multi-rol | Permitido (N filas por usuario) |
| Catálogo usuarios | GET admin/usuarios o equivalente read-only |

## Criterios de aceptación

- [ ] **CA-01:** Listado muestra asignaciones con filtros por usuario y rol.
- [ ] **CA-02:** Alta individual crea fila en `Pq_Permiso` y actualiza grilla.
- [ ] **CA-03:** Edición cambia el rol de la asignación.
- [ ] **CA-04:** Baja elimina la fila tras confirmación.
- [ ] **CA-05:** Duplicado usuario+rol → 422 con mensaje i18n.
- [ ] **CA-06:** Referencias inválidas (usuario/rol inexistente) → 422.
- [ ] **CA-07:** Usuario sin autorización → 403 / pantalla no accesible.
- [ ] **CA-08:** Lookup de usuarios **no** permite crear ni editar usuarios.
- [ ] **CA-09:** i18n `admin.permisos.*` en cinco idiomas; `data-testid`: `permisos.admin`, `permisos.grid`, `permisos.create`, `permisos.delete`.
- [ ] **CA-10:** E2E: cargar pantalla, abrir modal alta (Tango `permisos-admin.spec.ts` adaptado).

## Escenarios Gherkin

```gherkin
Feature: Administración de permisos individual (MONO)

  Scenario: Asignar rol a usuario
    Given un usuario autorizado en permisos
    And existen usuario U y rol R en catálogos
    When crea asignación U + R
    Then la fila aparece en el listado

  Scenario: Combinación duplicada
    Given ya existe permiso U + R
    When intenta crear la misma combinación
    Then recibe error 422
    And no se duplica el registro

  Scenario: Quitar acceso
    Given una asignación existente
    When elimina el permiso confirmando
    Then la fila desaparece del listado

  Scenario: Sin permiso para el proceso
    Given un usuario sin AccesoTotal ni atributos en menú permisos
    When intenta acceder a administración de permisos
    Then recibe 403 o no ve la opción de menú

  Scenario: Solo lookup de usuarios
    Given el modal de alta de permiso
    When abre el selector de usuario
    Then puede buscar usuarios existentes
    And no existe acción para crear usuario nuevo
```

## Supuestos explícitos

- `users` poblado por sync ERP antes de asignar permisos en producción.
- Roles disponibles vía HU-GEN-02-admin-roles o seed previo.

## Preguntas abiertas

- Ninguna bloqueante para TR.

## Riesgos de ambigüedad

- AMB-M-02-ADM-04: middleware `RequireAdmin` vs atributos granulares — resolver en TR permisos.

## Veredicto B1

**Lista para TR:** Sí










