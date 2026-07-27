# HU-GEN-02-visibilidad-datos-SistemaPartes — Visibilidad por perfil funcional (SistemaPartes)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.

| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-visibilidad-datos-SistemaPartes |
| **SPEC origen** | [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-28 |
| **Dependencias** | HU-GEN-02-login-sesion; HU-GEN-02-politicas-endpoints |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Visibilidad por perfil funcional (alcance §7.3) | Filtros en services/repos |
| Tabla perfiles MVP en SPEC (Cliente/Vendedor/Supervisor) | RN y AC alineados |
| Criterio: tabla §7.3 reflejada en esta HU | Matriz en alcance |
| Regla: login = cliente o vendedor, nunca ambos | RN4 |
| Criterio: menú no sustituye filtros datos | RN5 |
| Trazabilidad HU-SPEC: Perfiles §7.3 | Objeto de esta HU |

## Narrativa

Como **usuario del portal SistemaPartes**,
quiero **ver solo clientes y comprobantes permitidos según mi perfil funcional**,
para **que un vendedor no acceda a cartera ajena y un cliente solo vea su propia información**.

## Contexto funcional

SPEC-001-02 incluye **visibilidad por perfil funcional** según producto §7.3. Tabla resumen en el SPEC:

| Perfil | Visibilidad de datos |
|--------|----------------------|
| Cliente | Solo su `cod_cliente` |
| Vendedor | Clientes asignados al vendedor del login |
| Supervisor | Todos los clientes del tenant |

Regla SPEC: un login = un cliente **o** un vendedor (nunca ambos). Los permisos de menú **no sustituyen** estos filtros (criterio medible SPEC-001-02).

## Alcance incluido

- Aplicar filtros de visibilidad en repositories/services de consultas y comprobantes del MVP.
- Asociación login ↔ perfil funcional (cliente o vendedor o supervisor).
- Selección de cliente en UI (vendedor/supervisor) solo entre conjunto permitido.
- Cliente: `cod_cliente` fijo al propio; sin selector de otro cliente.
- Intento de acceso a datos fuera de alcance → 403 o 404 sin filtrar datos ajenos.
- Coherencia con consultas Must del MVP (pedidos, presupuestos, deuda, cheques, historial, dashboard).

## Fuera de alcance

- Administración de asignaciones vendedor–cliente en UI portal (ERP/herramientas internas).
- Permisos granulares ABM por atributo de menú (HU-GEN-02-politicas-endpoints).
- ABM seguridad UI (SPEC fuera de alcance).

## Reglas de negocio

1. **Cliente:** solo su `cod_cliente` (SPEC tabla perfiles).
2. **Vendedor:** solo clientes asignados al vendedor del login.
3. **Supervisor:** todos los clientes del tenant.
4. Un login vinculado a **un solo** cliente **o** un solo vendedor; nunca ambos (SPEC).
5. Visibilidad de menú **no sustituye** filtros de datos (criterio SPEC-001-02).
6. Usuario mal configurado (sin vínculo válido): bloqueo en login o error operativo documentado en TR.

## Criterios de aceptación

- [ ] Matriz SPEC §7.3 implementada en capa de datos (no solo documentada).
- [ ] API listado de clientes para carga respeta perfil.
- [ ] Consulta de comprobante ajeno por ID → 403 o 404 sin exponer datos sensibles.
- [ ] Tests integración: 3 perfiles, mismos datos semilla, resultados distintos.
- [ ] E2E: vendedor A no ve cliente de vendedor B en selector.
- [ ] Dashboard agrega solo comprobantes del universo visible (coherente SPEC MVP Must).

## Escenarios Gherkin

```gherkin
Feature: Visibilidad por perfil (SPEC-001-02 §7.3)

  Scenario: Cliente ve solo su cartera
    Given un usuario perfil Cliente con cod_cliente C1
    When consulta pedidos o deuda
    Then solo ve comprobantes de C1
    And no puede seleccionar otro cliente

  Scenario: Vendedor ve clientes asignados
    Given un usuario perfil Vendedor V1
    When consulta listado de clientes
    Then solo ve clientes asignados a V1
    And no ve clientes de otro vendedor

  Scenario: Supervisor ve todo el tenant
    Given un usuario perfil Supervisor
    When consulta listado de clientes
    Then ve todos los clientes activos del tenant

  Scenario: Acceso directo a ID ajeno
    Given un vendedor V1 autenticado
    When solicita comprobante de cliente no asignado
    Then recibe 403 o 404
    And no recibe datos del comprobante ajeno
```

## Supuestos explícitos

- Tablas `clientes`, `clientesde`, vínculo users↔vendedor/cliente: modelo SistemaPartes; no en SPEC-001-02.
- Implementación `visibleClientsForUser()`: patrón TR.
- Detalle dashboard §4.1: SPEC MVP referenciado por SPEC-001-02 fuente producto.

## Preguntas abiertas

- ¿Columna/tabla exacta de vínculo usuario ↔ vendedor o cliente en esquema SistemaPartes?

## Riesgos de ambigüedad

- Visibilidad de menú vs datos: dos capas; omitir filtros en services anula el criterio SPEC.

## Veredicto B1

**Lista para TR:** Sí con observaciones (modelo de vínculo usuario-perfil)
