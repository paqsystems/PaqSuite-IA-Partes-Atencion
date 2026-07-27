# HU-GEN-02-modelo-roles-permisos-seed — Modelo roles/permisos y seed MVP

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-modelo-roles-permisos-seed |
| **SPEC origen** | [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-28 |
| **Dependencias** | SPEC-001-05 (MONO); HU-GEN-01-menu-general-sidebar (seed menú) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Modelo usuarios, roles y permisos (seed; sin ABM UI) | Seeds idempotentes |
| Matriz permisos mínima por rol (entregable) | `matriz-permisos-mvp.md` |
| Roles vía seed Pq_Permiso (decisión SPEC) | AC seed documentado |
| Perfiles Cliente / Vendedor / Supervisor | Usuarios de prueba alineados |
| Fuera alcance: ABM seguridad UI | Out of scope |
| Trazabilidad HU-SPEC: Seed roles/permisos | Objeto de esta HU |

## Narrativa

Como **equipo de despliegue**,  
quiero **roles, permisos y usuarios iniciales cargados de forma reproducible**,  
para **probar login y autorización del MVP sin ABM de seguridad en UI**.

## Contexto funcional

SPEC-001-02 establece modelo de **usuarios, roles y permisos vía seed**, sin administración funcional en UI. La matriz mínima por rol es entregable en `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`. Perfiles funcionales MVP: Cliente, Vendedor, Supervisor (tabla SPEC). Un login = un cliente **o** un vendedor (nunca ambos).

## Alcance incluido

- Seeds idempotentes para desarrollo y CI.
- Roles MVP alineados a perfiles funcionales del SPEC (Cliente, Vendedor, Supervisor).
- Usuarios de prueba con asignaciones `Pq_Permiso`.
- Seed de menú coordinado con HU-GEN-01-menu-general-sidebar (11 ítems §8).
- Documentación de matriz rol ↔ capacidades menú MVP en ruta del entregable SPEC.
- MONO: una asignación usuario–rol típica por instalación.

## Fuera de alcance

- Pantallas ABM de usuarios/roles/permisos (SPEC fuera de alcance).
- Administración funcional completa de seguridad vía UI.
- Anti-fuerza bruta avanzado (SPEC fuera de alcance).

## Reglas de negocio

1. Usuario de prueba sin `Pq_Permiso` no es caso feliz de login (HU-GEN-02-login-sesion).
2. Seeds no borran datos de producción; upsert por claves naturales en dev/test.
3. Re-ejecutar seed no duplica filas ni rompe integridad.
4. Perfiles alineados a visibilidad §7.3 (tabla SPEC): Cliente, Vendedor, Supervisor.

## Criterios de aceptación

- [ ] Comando seed documentado y ejecutable en dev/CI.
- [ ] Tras seed: al menos 3 usuarios (cliente, vendedor, supervisor) autentican según HU-GEN-02-login-sesion.
- [ ] Matriz rol/permiso publicada en `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`.
- [ ] Re-ejecutar seed no duplica filas.
- [ ] Conflicto de clave natural duplicada: fallo explícito del seeder.

## Escenarios Gherkin

```gherkin
Feature: Seed roles y permisos (SPEC-001-02)

  Scenario: Seed idempotente en desarrollo
    Given un entorno de desarrollo limpio
    When ejecuta el comando de seed de seguridad
    Then existen roles para Cliente, Vendedor y Supervisor
    And existen usuarios de prueba con Pq_Permiso

  Scenario: Re-ejecución no duplica datos
    Given un entorno ya sembrado
    When ejecuta el seed nuevamente
    Then no hay filas duplicadas
    And la integridad referencial se mantiene

  Scenario: Usuario de prueba puede autenticar
    Given el seed completado
    When el usuario vendedor de prueba inicia sesión
    Then autentica correctamente según HU-GEN-02-login-sesion

  Scenario: Matriz documentada
    Given el seed MVP completado
    Then existe matriz-permisos-mvp.md con rol ↔ capacidades menú
```

## Supuestos explícitos

- Nombres de tablas `Pq_Rol`, `Pq_Permiso`, `PQ_RolAtributo`: referenciados en SPEC; esquema exacto en TR.
- `AccesoTotal` en **`Pq_Rol`** (Supervisor=true): convención TR; no en SPEC.
- En MONO, `Pq_Permiso` **sin** `IDEmpresa`; sin tabla pivote rol-permiso.

## Preguntas abiertas

- Ninguna bloqueante para TR (mapeo `Pq_Rol.NombreRol` ↔ `functionalProfile` cerrado en TR §4.2).

## Riesgos de ambigüedad

- Seed menú y seed permisos deben alinearse con visibilidad §7; coordinar con HU-GEN-02-visibilidad-datos-SistemaPartes.

## Veredicto B1

**Lista para TR:** Sí con observaciones (esquema tablas y códigos de rol)










