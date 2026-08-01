# HU-001 – Modelo de datos del módulo Sistema Partes

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-001 |
| Título | Modelo de datos del módulo Sistema Partes |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | En Control Calidad |
| Última actualización | 2026-07-31 |
| SPEC origen | [SPEC-001-modelo-datos-modulo](../../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) |
| TR relacionada(s) | [TR-001-modelo-datos-modulo](../../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-001 | Dónde en esta HU |
|--------------------------------|------------------|
| Seis tablas `PQ_PARTES_*` §4.4 | Alcance; CA-01 |
| PK / UNIQUE `code` / defaults bits | CA-02, CA-03 |
| UNIQUE `user_id` asistentes y clientes | CA-04; R-MD-02/03 |
| FK dominio §4.5 + FK `user_id`→`users` | CA-05; R-MD-09 |
| Exclusividad asistente/cliente (SP + trigger) | CA-06; R-MD-04 |
| Un solo `is_default` vía SP/backend | CA-07; R-MD-06 |
| Seed tipo tarea genérico y default | CA-08 |
| `supervisor` solo en dominio | CA-09; R-MD-08 |
| Docs `md-sistema-partes` / `09` alineados | CA-10 |
| Fuera de alcance UI/gate/login | Fuera de alcance |

---

## Narrativa

Como equipo de producto/implementación del módulo Partes  
quiero disponer del esquema de datos de dominio (`PQ_PARTES_*`) instalado y documentado  
para que identidad, maestros y registro de tareas puedan construirse sobre un contrato estable, separado de la autenticación Framework (`users`).

---

## Contexto funcional

El módulo necesita persistir asistentes, clientes, catálogos y registros de tarea sin redefinir login Sanctum. El esquema es prerequisito de SPEC-002 (gate), SPEC-003 (ABM) y operación posterior. Motor de referencia: SQL Server (`datetime2(3)`); MySQL adaptable en TR.

---

## Alcance incluido

- Crear/desplegar las seis tablas: `PQ_PARTES_USUARIOS`, `PQ_PARTES_CLIENTES`, `PQ_PARTES_TIPOS_CLIENTE`, `PQ_PARTES_TIPOS_TAREA`, `PQ_PARTES_CLIENTE_TIPO_TAREA`, `PQ_PARTES_REGISTRO_TAREA` (incluye `row_version` para optimistic lock).
- Columnas, defaults de bits, PK IDENTITY, UNIQUE de `code` y de `user_id` según SPEC.
- FK entre tablas de dominio y FK formal `user_id` → `users.id` (NULL permitido en clientes).
- Trigger SQL Server + validación de escritura (SP/backend) para exclusividad asistente/cliente (R-MD-04).
- Validación de escritura (SP/backend) para un único `is_default` con `is_generico = 1` (sin índice filtrado).
- Seed mínimo: al menos un tipo de tarea genérico y marcado default.
- Alineación documental de `docs/modelo-datos/md-sistema-partes.md` y `docs/02-producto/Sistema-Partes-IA/09-modelo-datos-tecnico.md` al contrato.

---

## Fuera de alcance

- UI ABM, carga diaria, consultas, dashboard, mobile.
- Login, Sanctum, menú GEN, roles/permisos Framework.
- Gate post-login y `resultado.partes` (SPEC-002).
- CHECK de duración múltiplo de 15 / máx. 1440 en DDL (capa negocio / SPEC-004).
- Firmas de SP de runtime de negocio de operación (TR posteriores); en esta HU sí el soporte de integridad acordado (trigger exclusividad + validaciones de escritura documentadas).
- Facturación, costeo, auditoría avanzada, cuenta corriente.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-MD-01 | Prefijo `PQ_PARTES_*`; `users` fuera del prefijo. |
| R-MD-02 | `PQ_PARTES_USUARIOS.user_id` NOT NULL + UNIQUE. |
| R-MD-03 | `PQ_PARTES_CLIENTES.user_id` nullable; UNIQUE cuando no NULL. |
| R-MD-04 | Un `users.id` no puede estar en ambas tablas de dominio; SP/backend **y** trigger SQL Server. |
| R-MD-05 | `code` UNIQUE en USUARIOS, CLIENTES, TIPOS_CLIENTE, TIPOS_TAREA. |
| R-MD-06 | A lo sumo un `is_default = 1`; implica `is_generico = 1`; solo SP/backend; al marcar nuevo default se **desmarca** el anterior en la misma operación. |
| R-MD-07 | UNIQUE (`cliente_id`, `tipo_tarea_id`) en CLIENTE_TIPO_TAREA. |
| R-MD-08 | `supervisor` de negocio solo en `PQ_PARTES_USUARIOS` (no usar `users.supervisor`). |
| R-MD-09 | Vínculo por `user_id` → `users.id` con FK formal; NULL en clientes = sin acceso. |
| R-MD-10 | Timestamps `created_at`/`updated_at` en `datetime2(3)` (SQL Server). |

Campos de cada tabla: según SPEC-001 §4.4 (contrato canónico).

---

## Criterios de aceptación

- [ ] **CA-01** Existen las seis tablas `PQ_PARTES_*` con las columnas de SPEC-001 §4.4.
- [ ] **CA-02** Cada tabla tiene PK `id` IDENTITY; las de maestro/catálogo con `code` tienen UNIQUE en `code`.
- [ ] **CA-03** Defaults de bits aplicados (`activo=1`, `inhabilitado=0`, `supervisor=0`, `is_generico`/`is_default`/`sin_cargo`/`presencial`/`cerrado` según tabla).
- [ ] **CA-04** UNIQUE en `PQ_PARTES_USUARIOS.user_id`; UNIQUE (filtrado o equivalente) en `PQ_PARTES_CLIENTES.user_id` cuando no NULL.
- [ ] **CA-05** FK de dominio según SPEC §4.5; FK formal `user_id` → `users.id` en USUARIOS y CLIENTES (NULL OK en clientes); sin `ON DELETE CASCADE` destructivo hacia dominio.
- [ ] **CA-06** Insert/update que vincule el mismo `users.id` como asistente y cliente es rechazado por validación de escritura **y** por trigger SQL Server.
- [ ] **CA-07** No puede quedar más de un `is_default = 1`; marcar default exige `is_generico = 1` y desmarca el default previo en la misma escritura (SP/backend; sin índice filtrado).
- [ ] **CA-08** Seed mínimo deja un tipo de tarea con `is_generico = 1` e `is_default = 1`.
- [ ] **CA-09** La documentación de dominio no presenta `users.supervisor` como fuente de verdad del módulo.
- [ ] **CA-10** `md-sistema-partes.md` y `09-modelo-datos-tecnico.md` están alineados al SPEC-001 (vínculo por `user_id`, tablas y reglas).

---

## Escenarios Gherkin

```gherkin
Feature: Esquema de datos PQ_PARTES_*
  Como implementador del módulo Partes
  Quiero un esquema de dominio íntegro
  Para soportar identidad funcional y tareas

  Scenario: Tablas de dominio presentes tras migrate
    Given la base unificada del producto con tabla "users"
    When se aplican las migraciones del módulo Partes
    Then existen las seis tablas "PQ_PARTES_*" con PK "id"
    And "PQ_PARTES_USUARIOS.user_id" es NOT NULL y UNIQUE
    And "PQ_PARTES_CLIENTES.user_id" admite NULL

  Scenario: FK a users en clientes permite NULL
    Given un cliente de negocio sin acceso autenticado
    When se inserta en "PQ_PARTES_CLIENTES" con "user_id" NULL
    Then el insert es aceptado por la FK
    When se inserta con "user_id" de un "users.id" existente
    Then el insert es aceptado
    When se inserta con "user_id" inexistente
    Then el motor rechaza por FK

  Scenario: Exclusividad asistente y cliente
    Given un "users.id" ya vinculado en "PQ_PARTES_USUARIOS"
    When se intenta vincular el mismo "users.id" en "PQ_PARTES_CLIENTES"
    Then la operación es rechazada por validación de negocio o trigger

  Scenario: Un solo tipo de tarea default
    Given ya existe un tipo de tarea con "is_default" = 1 e "is_generico" = 1
    When se intenta marcar otro tipo como default vía escritura de negocio
    Then el default previo queda desmarcado en la misma operación
    And el nuevo tipo queda con "is_default" = 1 e "is_generico" = 1
    And no quedan dos filas con "is_default" = 1

  Scenario: Seed mínimo de tipo default
    Given se ejecutó el seed de catálogo Partes
    Then existe al menos un "PQ_PARTES_TIPOS_TAREA" con "is_generico" = 1 e "is_default" = 1
```

---

## Supuestos explícitos

- La instalación ya tiene esquema Framework mínimo (`users` y dependencias Sanctum) antes de aplicar este DDL.
- El entorno de referencia de aceptación es SQL Server; adaptaciones MySQL se detallan en TR sin cambiar el contrato funcional.
- La validación de `is_default` / exclusividad en “capa SP/backend” puede implementarse en el mismo TR aunque los SP de operación de pantallas lleguen en SPEC posteriores.
- Seed de asistentes demo vinculados a `admin`/`PQ` es opcional en esta HU (detalle identidad/maestros); el Must de seed aquí es el tipo default genérico.

---

## Preguntas abiertas

- Ninguna bloqueante. Nombres exactos de trigger/SP → Parte C (TR).

---

## Riesgos de ambigüedad

- UNIQUE filtrado de `CLIENTES.user_id` en SQL Server: detalle de sintaxis en TR; efecto funcional claro.

---

## Dependencias

- Tabla `users` (Framework) disponible.
- SPEC-001 cerrado en A1 (decisiones FK, exclusividad, `is_default`).

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte B + B1: HU creada y enriquecida desde SPEC-001. |
| 2026-07-30 | Batch: al marcar `is_default` se desmarca el anterior (atómico). |
| 2026-07-30 | Parte C: enlazada [TR-001](../../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md). |
