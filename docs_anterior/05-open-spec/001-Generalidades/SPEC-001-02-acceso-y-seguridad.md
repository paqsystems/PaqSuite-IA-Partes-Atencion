# SPEC-001-02 - Acceso y seguridad

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionadas** | `docs/03-historias-usuario/001-Generalidades/HU-GEN-02-*.md` (8 HU; índice en README) |
| **TR relacionadas** | `docs/04-tareas/001-Generalidades/TR-GEN-02-*.md` (8 TR; índice en README) |
| **Estado** | Especificado |
| **Revisión A1** | Apto con observaciones (2026-05-28) |

## Objetivo

Establecer el marco inicial de autenticación, sesión, autorización y administración de seguridad para el MVP.

## Estado de ejecución

Pendiente de programación en este repositorio.

## Decisiones humanas (producto SistemaPartes)

| Tema | Decisión |
|------|----------|
| Perfiles funcionales y visibilidad de datos | Tabla en **producto** `PROD-Sistema-Partes.md` **§7.1–7.3** |
| Minutos de inactividad (sesión web) | Parámetro de producto **`MinutosWeb`** (§10.6 parámetros); consumo en SPEC-001-04 |
| Matriz permisos / endpoints | Entregable en implementación; roles vía seed (`Pq_Permiso`), no ABM UI en MVP |

## Fuente de verdad de producto (obligatoria)

- `docs/02-producto/SistemaPartes/PROD-Sistema-Partes.md` — §7 seguridad, §7.4 autenticación
- `docs/02-producto/SistemaPartes/INDICE-Sistema-Partes.md` — reglas tenancy y Must de consultas

## Contrato API (envelope)

Toda respuesta `/api/v1/*` usa el envelope MONO **`error` / `respuesta` / `resultado`**: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md). Las TR aplican [`_NORMAS-TRANSVERSALES-TR.md`](../../04-tareas/_NORMAS-TRANSVERSALES-TR.md) §1–§2.

## Fuentes (contexto MONO)

`docs/00-contexto/_mono/02-acceso-y-seguridad/` — login, permisos, menú-autorización, administración; mantenimiento UI post-MVP: [`mantenimiento-roles-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/mantenimiento-roles-permisos.md) → [SPEC-001-02-admin-mantenimiento-roles-permisos](SPEC-001-02-admin-mantenimiento-roles-permisos.md).

## Alcance

- Login y ciclo de sesión.
- Recuperación y cambio de contraseña.
- **Expiración de sesión por inactividad** (parámetro `MinutosWeb` en producto).
- Modelo usuarios, roles y permisos (seed; sin ABM seguridad en UI).
- Relación menú ↔ autorización (backend + API menú).
- Visibilidad por perfil funcional según producto §7.3.

## Fuera de alcance

- 2FA, login social, anti-fuerza bruta avanzado.
- Administración funcional completa de seguridad vía UI (usuarios/roles/permisos ABM) en **MVP portal** — especificada para post-MVP en [SPEC-001-02-admin-mantenimiento-roles-permisos](SPEC-001-02-admin-mantenimiento-roles-permisos.md) (contexto: [`mantenimiento-roles-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/mantenimiento-roles-permisos.md)).

## Perfiles funcionales MVP (resumen desde producto)

| Perfil | Visibilidad de datos |
|--------|----------------------|
| Cliente | Solo su `cod_cliente` |
| Vendedor | Clientes asignados al vendedor del login |
| Supervisor | Todos los clientes del tenant |

Regla: un login = un cliente **o** un vendedor (nunca ambos). Detalle: producto §7.2.

## Entregables verificables

- Contrato de autenticación MVP: login, recuperación, cambio de contraseña, expiración (documentar en TR/OpenAPI por slice).
- Matriz permisos mínima por rol: seed + `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` (crear al implementar `SPEC-101-06` / HU-GEN-02-seed).
- Reglas de autorización backend por endpoint: `HU-GEN-02-politicas-endpoints` + tabla viva en implementación.
- **Norma transversal TR (OpenAPI + seguridad):** `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md` — obligatoria en **toda** TR de slice (`001-*` y `101-SistemaPartes`); plantilla base: `docs/04-tareas/_PLANTILLA-TR-SLICE.md`.

## Normas transversales para parte C (TR)

Al generar TR desde este SPEC (y desde cualquier `SPEC-101-xx`), aplicar:

1. Plantilla: `docs/04-tareas/_PLANTILLA-TR-SLICE.md`
2. Checklist OpenAPI/autorización: `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md` §1 y §5
3. HU de referencia: `HU-GEN-02-politicas-endpoints` (cada endpoint protegido → policy + matriz + OpenAPI coherente)

## Criterios de aceptación medibles

- [ ] Cada endpoint protegido del MVP tiene política o regla documentada **en código, matriz y OpenAPI** (`_NORMAS-TRANSVERSALES-TR.md`).
- [ ] Visibilidad cliente/vendedor/supervisor: tabla única en producto **§7.3** reflejada en `HU-GEN-02-visibilidad-datos-SistemaPartes`.
- [ ] Explícito: permisos de menú **no** reemplazan controles backend (SPEC + HU-02 políticas).

## Trazabilidad HU

| HU | Tema SPEC |
|----|-----------|
| HU-GEN-02-login-sesion | Login, bootstrap sesión, logout |
| HU-GEN-02-recuperacion-contrasena | Recuperación |
| HU-GEN-02-cambio-contrasena | Cambio / primer ingreso |
| HU-GEN-02-expiracion-inactividad | `MinutosWeb` |
| HU-GEN-02-modelo-roles-permisos-seed | Seed roles/permisos |
| HU-GEN-02-autorizacion-menu-api | Menú ↔ autorización |
| HU-GEN-02-politicas-endpoints | Políticas por endpoint |
| HU-GEN-02-visibilidad-datos-SistemaPartes | Perfiles §7.3 |

## Estado F de la oleada

### HUs/TR con cierre F formal en esta etapa

- `HU-GEN-02-login-sesion` / `TR-GEN-02-login-sesion` -> **Aprobada con observaciones**
- `HU-GEN-02-cambio-contrasena` / `TR-GEN-02-cambio-contrasena` -> **Aprobada**
- `HU-GEN-02-recuperacion-contrasena` / `TR-GEN-02-recuperacion-contrasena` -> **Aprobada con observaciones**

Soporte consolidado: `docs/04-tareas/001-Generalidades/F-GEN-01-02-cierre-formal.md`.

### Pendiente para cierre total de la SPEC

- `HU-GEN-02-expiracion-inactividad` / `TR-GEN-02-expiracion-inactividad`
- `HU-GEN-02-modelo-roles-permisos-seed` / `TR-GEN-02-modelo-roles-permisos-seed`
- `HU-GEN-02-autorizacion-menu-api` / `TR-GEN-02-autorizacion-menu-api`
- `HU-GEN-02-politicas-endpoints` / `TR-GEN-02-politicas-endpoints`
- `HU-GEN-02-visibilidad-datos-SistemaPartes` / `TR-GEN-02-visibilidad-datos-SistemaPartes`

### Criterio de lectura de estado

Esta SPEC se replica como base documental y debe considerarse pendiente de programación, pero **no** se considera cerrada en forma total mientras existan HU/TR asociadas sin cierre formal o con observaciones de entorno pendientes.








