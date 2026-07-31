# TR-001 – Modelo de datos del módulo Sistema Partes

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-001-modelo-datos-modulo](../../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) |
| **SPEC relacionada** | [SPEC-001-modelo-datos-modulo](../../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Implementación técnica (DBA / backend); sin UI de usuario final |
| **Dependencias** | Esquema Framework mínimo (`users` y dependencias Sanctum) ya migrado |
| **Clasificación** | HU COMPLEJA (DDL + integridad + seed + docs) |
| **Estado** | Pendiente (D implementado — verificar F1) |
| **Última actualización** | 2026-07-30 (D) |

**Origen:** [HU-001](../../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md)  
**Referencia SPEC:** [SPEC-001](../../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md)

---

## 1) HU refinada (resumen)

### Título
Modelo de datos del módulo Sistema Partes

### Narrativa
Como equipo de implementación del módulo Partes, quiero el esquema `PQ_PARTES_*` instalado, íntegro y documentado, para construir identidad, maestros y registro de tareas sobre un contrato estable separado de `users`.

### Contexto / objetivo
Habilitar persistencia de dominio (asistentes, clientes, catálogos, tareas) con PK/FK/UNIQUE, exclusividad asistente/cliente, un solo tipo default, `row_version` y seed mínimo. Bloqueante para TR-002…007.

### Supuestos
- Motor de aceptación: **SQL Server** (`datetime2(3)`, `rowversion`, índice filtrado). MySQL: adaptaciones documentadas en §4.6 sin cambiar contrato funcional.
- Tabla `users` existe con PK `id` (bigint) antes de aplicar estas migraciones.
- Seed de asistentes `admin`/`PQ` supervisores → **TR-002** (identidad); aquí Must = tipo de tarea genérico default.
- Runtime de ABM/carga vía SP de pantallas → TR posteriores; en esta TR sí objetos de integridad nombrados abajo.

### In scope
- Seis tablas `PQ_PARTES_*` según SPEC-001 §4.4 (incluye `row_version`).
- Índices UNIQUE, FK dominio + FK a `users`, defaults de bits.
- Trigger(s) exclusividad `user_id` + SP de assert de exclusividad + SP `pq_sp_partes_tipos_tarea_marcar_default`.
- Seed mínimo tipo default genérico (`code = GEN`).
- Sync docs `md-sistema-partes.md` y `09-modelo-datos-tecnico.md`.
- Tests de migración / integridad (PHPUnit Feature o equivalente).

### Out of scope
- UI, APIs de negocio de maestros/carga/consultas, gate `resultado.partes`, menú, mobile.
- CHECK DDL de múltiplo de duración / máx. 1440.
- Limpieza de columna legado `users.supervisor` (nota documental; TR host opcional).
- Seed de maestros demo (clientes, tipos cliente, asistentes); solo tipo de tarea default.
- Validación de exclusividad **en endpoints HTTP** (llega con TR-003 al invocar el SP assert desde upserts).

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | Tras `migrate`, existen las seis tablas `PQ_PARTES_*` con columnas SPEC-001 §4.4 |
| AC-02 | PK `id` IDENTITY en todas; UNIQUE en `code` de USUARIOS, CLIENTES, TIPOS_CLIENTE, TIPOS_TAREA |
| AC-03 | Defaults bits: `activo=1`, `inhabilitado=0`, `supervisor=0`, `is_generico`/`is_default`/`sin_cargo`/`presencial`/`cerrado` según tabla |
| AC-04 | UNIQUE `PQ_PARTES_USUARIOS.user_id`; UNIQUE filtrado (o equivalente) `PQ_PARTES_CLIENTES.user_id` WHERE NOT NULL |
| AC-05 | FK dominio §4.5 + FK `user_id`→`users.id` (NULL OK en clientes); sin `ON DELETE CASCADE` hacia dominio |
| AC-06 | Insert/update cruzado del mismo `users.id` en USUARIOS y CLIENTES es rechazado por **trigger**. El SP `pq_sp_partes_assert_user_id_exclusividad` rechaza el mismo caso cuando se invoca (capa SP; tests Feature lo ejercitan). Endpoints HTTP de maestros lo invocarán en TR-003. |
| AC-07 | `pq_sp_partes_tipos_tarea_marcar_default @p_tipo_tarea_id` deja un solo `is_default=1`, fuerza `is_generico=1` y desmarca el anterior en la misma operación; id inexistente → error |
| AC-08 | Seed deja ≥1 fila TIPOS_TAREA con `is_generico=1` e `is_default=1` |
| AC-09 | Docs de dominio no presentan `users.supervisor` como fuente de verdad |
| AC-10 | `md-sistema-partes.md` y `09-modelo-datos-tecnico.md` alineados (incl. `row_version`, vínculo por `user_id`) |
| AC-11 | `migrate:rollback` de las migraciones de este TR elimina tablas/objetos creados sin dejar huérfanos de FK Partes |

### Escenarios Gherkin

```gherkin
Feature: Esquema PQ_PARTES_* (TR-001)
  Como implementador
  Quiero migraciones e integridad reproducibles
  Para habilitar el resto del módulo

  Scenario: Migración crea tablas
    Given la BD unificada con tabla "users"
    When se aplican las migraciones TR-001
    Then existen las seis tablas "PQ_PARTES_*" con PK "id"
    And "PQ_PARTES_REGISTRO_TAREA" tiene columna "row_version"

  Scenario: FK clientes permite user_id NULL
    Given un "users.id" válido
    When inserto cliente con "user_id" NULL
    Then el insert es aceptado
    When inserto cliente con "user_id" inexistente
    Then el motor rechaza por FK

  Scenario: Exclusividad asistente/cliente
    Given "users.id" = U ya en "PQ_PARTES_USUARIOS"
    When intento insertar el mismo U en "PQ_PARTES_CLIENTES"
    Then la operación falla por trigger de exclusividad

  Scenario: Marcar tipo default atómico
    Given tipo A con "is_default" = 1 e "is_generico" = 1
    When ejecuto "pq_sp_partes_tipos_tarea_marcar_default" para tipo B genérico
    Then tipo B queda "is_default" = 1 e "is_generico" = 1
    And tipo A queda "is_default" = 0
    And no hay dos filas con "is_default" = 1

  Scenario: Seed tipo default
    Given se ejecutó el seeder Partes de TR-001
    Then existe al menos un "PQ_PARTES_TIPOS_TAREA" con "is_generico" = 1 e "is_default" = 1
```

---

## 3) Reglas de negocio

| ID | Regla |
|----|--------|
| R-MD-01…11 | Según SPEC-001 / HU-001 (prefijo, UNIQUE, exclusividad, default atómico, `row_version`, timestamps). |
| RN-TR-01 | Nombres de objetos SQL de esta TR (fijos): ver §4.5. |
| RN-TR-02 | Escrituras futuras de maestros/tipos **MUST** usar `pq_sp_partes_tipos_tarea_marcar_default` (o wrapper PHP que lo invoque) cuando cambien `is_default`. |
| RN-TR-03 | Exclusividad: trigger = red de seguridad siempre. Escrituras de negocio (TR-003+) **MUST** llamar `pq_sp_partes_assert_user_id_exclusividad` antes del INSERT/UPDATE de `user_id`. |
| RN-TR-04 | No borrar físicamente maestros referenciados; política de inhabilitar queda en SPEC-003. |

**Permisos:** N/A usuario final. Solo operadores de deploy/migrate.

---

## 4) Impacto en datos

### 4.1 Tablas a crear

Orden de migración sugerido (respetar FKs):

1. `PQ_PARTES_TIPOS_CLIENTE`
2. `PQ_PARTES_TIPOS_TAREA`
3. `PQ_PARTES_USUARIOS` (FK → `users`)
4. `PQ_PARTES_CLIENTES` (FK → `users`, `PQ_PARTES_TIPOS_CLIENTE`)
5. `PQ_PARTES_CLIENTE_TIPO_TAREA`
6. `PQ_PARTES_REGISTRO_TAREA` (+ `row_version`)

Columnas: **contrato canónico SPEC-001 §4.4** (no duplicar aquí salvo notas de implementación).

### 4.2 Índices / constraints

| Objeto | Definición |
|--------|------------|
| UNIQUE | `code` en USUARIOS, CLIENTES, TIPOS_CLIENTE, TIPOS_TAREA |
| UNIQUE | `PQ_PARTES_USUARIOS.user_id` |
| UNIQUE filtrado | `PQ_PARTES_CLIENTES.user_id` WHERE `user_id IS NOT NULL` (SQL Server) |
| UNIQUE | (`cliente_id`, `tipo_tarea_id`) en CLIENTE_TIPO_TAREA |
| FK | Según SPEC §4.5; `onDelete` / `onUpdate` = **restrict** / **no action** |

### 4.3 `row_version`

- SQL Server: columna tipo **`rowversion`** (sin default manual; el motor la mantiene).
- Exposición API futura: token opaco (p. ej. hex/base64 del valor); detalle en TR-004.
- MySQL: equivalente documentado = columna `version` `BINARY(8)` o `BIGINT` incrementado en SP de update (TR de adaptación); no bloquea contrato.

### 4.4 Migración + rollback

- Archivos Laravel bajo `backend/database/migrations/` con timestamp `2026_07_30_*` (o siguiente libre).
- Preferible: una migración por tabla o un batch ordenado con `down()` simétrico (DROP TABLE en orden inverso + DROP TRIGGER/SP).
- Scripts SQL raw (`DB::unprepared`) para trigger, índice filtrado, `rowversion` y SP si Blueprint no alcanza.

### 4.5 Objetos SQL nombrados (cerrado en esta TR)

| Tipo | Nombre |
|------|--------|
| Trigger | `tr_pq_partes_usuarios_exclusividad_user_id` — AFTER INSERT/UPDATE en `PQ_PARTES_USUARIOS`: si `user_id` existe en CLIENTES → RAISE (mensaje contiene `PARTES_EXCLUSIVIDAD_USER_ID`) |
| Trigger | `tr_pq_partes_clientes_exclusividad_user_id` — AFTER INSERT/UPDATE en `PQ_PARTES_CLIENTES` (solo si `user_id` NOT NULL): si existe en USUARIOS → RAISE (mismo token `PARTES_EXCLUSIVIDAD_USER_ID`) |
| SP | `pq_sp_partes_assert_user_id_exclusividad` — params: `@p_user_id bigint`, `@p_lado nvarchar(20)` con valores **`usuario`** \| **`cliente`**. Si `@p_lado='usuario'` y el id ya está en CLIENTES (no null) → error; si `@p_lado='cliente'` y el id ya está en USUARIOS → error; `@p_user_id` NULL con lado `cliente` → no-op OK |
| SP | `pq_sp_partes_tipos_tarea_marcar_default` — param: `@p_tipo_tarea_id bigint`. Transacción: si no existe fila → error; set destino `is_generico=1`, `is_default=1`; set `is_default=0` en el resto |

### 4.6 Seed

| Seeder | Contenido |
|--------|-----------|
| `PqPartesTiposTareaSeeder` | Upsert idempotente por `code = **GEN**`; `descripcion` = `General`; `is_generico=1`, `is_default=1`, `activo=1`, `inhabilitado=0` |
| Registro en `DatabaseSeeder` | Invocar tras seeds Framework que crean `users` |

**No Must aquí:** filas USUARIOS para `admin`/`PQ` → TR-002.

### 4.7 MySQL (nota)

| SQL Server | MySQL |
|------------|--------|
| `datetime2(3)` | `datetime(3)` |
| `rowversion` | `version` BIGINT o BINARY + bump en SP |
| UNIQUE filtrado | UNIQUE(`user_id`) con NULL múltiples OK en InnoDB, o generated column |
| Trigger T-SQL | Trigger MySQL equivalente misma semántica |

---

## 5) Contratos de API y OpenAPI

**N/A** — sin endpoints de negocio en esta TR.

OpenAPI: sin cambio. Futuras APIs (TR-003+) consumirán este esquema.

---

## 6) Cambios frontend

**N/A.**

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1 | DB | Migraciones 6 tablas + índices/FK/defaults | AC-01…05; rollback OK | L |
| T2 | DB | `row_version` en REGISTRO_TAREA | Columna presente; insert no requiere valor | S |
| T3 | DB | Triggers exclusividad + SP `pq_sp_partes_assert_user_id_exclusividad` | AC-06; tests Feature | M |
| T4 | DB | SP `pq_sp_partes_tipos_tarea_marcar_default` | AC-07; test atómico + id inexistente | M |
| T5 | DB | Seeder `code=GEN` + wire `DatabaseSeeder` | AC-08; idempotente | S |
| T6 | Docs | Sync `md-sistema-partes.md` + `09-modelo-datos-tecnico.md` | AC-09, AC-10; diagrama con `row_version` | S |
| T7 | Tests | Feature/integration PHPUnit: migrate schema, exclusividad, SP default, seed | Suite verde en CI local SQL Server (o skip documentado si no hay SQL Server en CI) | M |
| T8 | DevOps | Nota deploy: `php artisan migrate --force` + seed Partes en runbook/PR | Aviso commit/versión listo | S |

**Orden:** T1 → T2 → T3 → T4 → T5 → T7; T6 en paralelo tras T1; T8 al cerrar.

---

## 8) Estrategia de tests

| Capa | Qué |
|------|-----|
| Unit | N/A fuerte (poco PHP de dominio aún). Opcional: helper que invoca SP mockeable. |
| Integration / Feature | `RefreshDatabase` o migrate en entorno test: existencia columnas; UNIQUE violado; FK; trigger exclusividad; SP marcar default; seeder. |
| E2E Playwright | **N/A** (sin UI). Cubierto por Feature tests. |

Datos de prueba: crear `users` temporales en el test; no depender de producción.

---

## 9) Riesgos y edge cases

| Riesgo | Mitigación |
|--------|------------|
| CI sin SQL Server | Documentar skip o job opcional; aceptación formal en entorno SQL Server |
| Trigger vs bulk seed | Seed no debe violar exclusividad; orden seed claro |
| Doble default por UPDATE directo | Prohibido en convención; solo SP; test de regresión |
| `users.id` tipo distinto | Alinear FK al tipo real de `users.id` del host (`bigint` / `unsignedBigInteger`) |
| Nombre tabla `users` vs `USERS` | Usar nombre real del host Laravel (`users`) |

---

## 10) Checklist final

- [ ] AC-01…11 cumplidos
- [ ] Migración + rollback + seed
- [ ] Triggers + SP desplegados
- [ ] Feature tests OK
- [ ] Docs alineados
- [ ] Aviso deploy (migrate + seed) en PR/commit
- [ ] Frontend / OpenAPI / E2E: N/A marcado

---

## 11) Discrepancias SPEC ↔ HU

- SPEC §6 menciona seed `admin`/`PQ` en impacto técnico; HU-001 y esta TR lo difieren a **TR-002** (Must de seed aquí = tipo `GEN` default).
- R-MD-04 pide SP/backend **y** trigger: en TR-001 quedan **trigger + SP assert**; el cableado HTTP/i18n de esa validación es **TR-003**.

---

## 3.1) Informe C1 (2026-07-30)

# Revisión de ambigüedad - TR-001

## Resultado general
- Estado: **Apto con observaciones** (observaciones menores absorbidas en esta misma corrida C1)

## Ambigüedades críticas
- ~~R-MD-04 / AC-06: “validación SP/backend” vs solo trigger~~ → **cerrado C1:** SP `pq_sp_partes_assert_user_id_exclusividad` + triggers; HTTP en TR-003.

## Ambigüedades menores
- ~~Nombre param SP default / code seed “GEN o DEFAULT”~~ → **cerrado:** `@p_tipo_tarea_id`; seed `code=GEN`.
- CI sin SQL Server: permanece como riesgo (§9); no bloquea D1 si el entorno de aceptación es SQL Server.
- Estructura exacta de archivos de migración (1 vs N): libre mientras el orden FK y el rollback cumplan AC-11.

## Contradicciones TR ↔ HU ↔ SPEC
- Ninguna abierta tras el ajuste C1. Diferimiento seed `admin`/`PQ` → TR-002 explícito y alineado a HU-001.

## Supuestos detectados
- `users.id` compatible con FK bigint del host Laravel.
- Aceptación formal de AC en SQL Server; MySQL solo nota de adaptación.

## Preguntas para decisión humana
- Ninguna bloqueante para D1.

## Recomendaciones de ajuste de la TR
- Aplicadas en esta corrida (AC-06/07, §4.5–4.6, T3, RN-TR-03, out of scope HTTP).

## Veredicto
- **Puede pasar a D1/D: Sí**

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte C: TR creada desde SPEC-001 + HU-001. |
| 2026-07-30 | C1: apto con observaciones; SP assert exclusividad; seed `GEN`; params SP fijos. |
| 2026-07-30 | Parte D: migraciones 6 tablas + integridad; seeder GEN; docs `row_version`; tests Feature (+ verify sqlsrv). |

---

**Siguiente paso metodológico:** **D1** (`ai-planning-mode`) cuando se autorice → luego **D** ejecución.
