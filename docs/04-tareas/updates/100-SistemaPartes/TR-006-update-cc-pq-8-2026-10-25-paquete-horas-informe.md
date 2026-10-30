# TR-006-update — Informe Paquete de Horas (saldo SP + destaque) — CC PQ #8

| Campo | Valor |
|-------|--------|
| **ID** | TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe |
| **Estado** | **Especificado** (Parte C 2026-10-30; C1 apto D1) |
| **Prioridad** | P0 |
| **TR base** | [TR-006-consultas-dashboard-navegacion](../../100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) |
| **HU-update** | [HU-006-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **SPEC-update** | [SPEC-006-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **Referencia producto** | [`18-obtencion-saldo-por-renglon.md`](../../../02-producto/Sistema-Partes-IA/18-obtencion-saldo-por-renglon.md) |
| **Referencia SQL PQ** | [`pq_sp_partes_informe_paquete_horas-REFERENCE-saldo-window.sql`](../../../../backend/scripts/sql/partes/pq_sp_partes_informe_paquete_horas-REFERENCE-saldo-window.sql) |
| **Jira** | [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52) |
| **Última actualización** | 2026-10-30 |

## 1) Resumen

Corregir el informe **Paquete de Horas**: saldo inicial S₀ y saldo running **por cliente** vía **`pq_sp_partes_informe_paquete_horas`** (T-SQL, regla **74**); validar **`clienteId` obligatorio** en API; destacar duración de compras en FE. Retirar cálculo en **`PartesInformeOperations::paqueteHoras`** para **sqlsrv** (mantener dispatch PHP **solo** en driver `sqlite` tests, documentado).

## 2) Criterios de aceptación (TR)

| ID | Criterio | HU |
|----|----------|-----|
| AC-01 | 422 sin `clienteId` (supervisor/asistente) | CA-01 |
| AC-02 | FE no auto-carga sin cliente | CA-02 |
| AC-03 | Destaque `-hh:mm` rojo compras | CA-03 |
| AC-04 | Tareas duración normal | CA-04 |
| AC-05 | Una fila saldo inicial; S₀ correcto | CA-05 |
| AC-06 | Running por `(fecha, id)` | CA-06 |
| AC-07 | Un cliente por consulta | CA-07 |
| AC-08 | Filtro tipo cliente en S₀ y periodo | CA-08 |
| AC-09 | sqlsrv EXEC SP; no PHP prod | CA-10 |
| AC-10 | Tests integración + Vitest | CA-03…CA-07 |

## 3) API

Sin cambio de path. Refuerzo validación en controller **antes** de SP.

| Método | Path | Query |
|--------|------|-------|
| GET | `/api/v1/partes/informes/paquete-horas` | `fechaDesde`, `fechaHasta` **req**; `clienteId` **req** si actor ≠ cliente funcional; `tipoClienteId` opcional; `usuarioId` opcional (supervisor) |

**422:** fechas faltantes; `clienteId` faltante (HU CA-01). Clave i18n alineada a envelope GEN-20 (p. ej. extensión de validación existente o `partes.informe.clienteRequerido` — definir en implementación, Must ser clave i18n).

**200 éxito** (sin cambio shape):

```json
{
  "items": [ { "esSaldoInicial": true, "saldo": 120, "duracionMinutos": 120, ... } ],
  "total": 5,
  "saldoInicial": 120,
  "fechaDesde": "2026-10-01",
  "fechaHasta": "2026-10-31"
}
```

Cada ítem movimiento incluye `saldo` (minutos, **algebraico**). `esTarea` bool. Fila saldo inicial: `esSaldoInicial=true`, `id=-1` (convención actual PHP), `observacion` / i18n vía clave en FE si aplica.

## 4) Stored procedure

### 4.1 Artefacto

| Archivo | Acción |
|---------|--------|
| `backend/scripts/sql/partes/pq_sp_partes_informe_paquete_horas.sql` | **Crear** `CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_informe_paquete_horas` |
| Manifiesto host / `deploy-sp` | Incluir script en pipeline Forge |

### 4.2 Parámetros (alineado TR-006 base + actor)

| Parámetro | Tipo | Notas |
|-----------|------|--------|
| `@p_fecha_desde` | DATE | req |
| `@p_fecha_hasta` | DATE | req |
| `@p_cliente_id` | INT | **req** (validar NULL → RAISERROR clave negocio) |
| `@p_tipo_cliente_id` | INT | NULL = todos tipos en universo |
| `@p_usuario_id` | INT | NULL = todos (supervisor) |
| `@p_actor_tipo_funcional` | NVARCHAR(20) | Delimitación |
| `@p_actor_asistente_id` | INT | |
| `@p_actor_cliente_id` | INT | Cliente funcional |
| `@p_actor_es_supervisor` | BIT | |

Delimitación actor: **misma semántica** que `PartesInformeOperations::baseScoped` / `paqueteScoped` (join `PQ_PARTES_TIPOS_CLIENTE`, filtros supervisor/asistente/cliente). TR implementación: extraer criterios a comentario en SP o vista interna; **no** duplicar reglas distintas a PHP actual.

### 4.3 Lógica de saldo (norma + referencia PQ)

**Signo:**

```sql
CASE WHEN es_tarea = 1 THEN 1 ELSE -1 END * duracion_minutos
```

**Referencia window (PQ)** — sobre el universo filtrado (un `cliente_id` por consulta):

```sql
SUM(CASE WHEN es_tarea = 1 THEN 1 ELSE -1 END * duracion_minutos)
    OVER (
        PARTITION BY cliente_id
        ORDER BY fecha, id
        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
    ) AS saldo
```

Ver archivo [`pq_sp_partes_informe_paquete_horas-REFERENCE-saldo-window.sql`](../../../../backend/scripts/sql/partes/pq_sp_partes_informe_paquete_horas-REFERENCE-saldo-window.sql).

**Algoritmo Must del SP:**

1. CTE `mov` — registros del universo (actor + `@p_cliente_id` + tipo cliente), **todas las fechas** necesarias para window.
2. `@saldo_inicial` = `SUM(delta)` donde `fecha < @p_fecha_desde` (mismo universo).
3. CTE `running` — aplicar window sobre `mov` (paridad con referencia PQ).
4. Resultset filas periodo: `fecha BETWEEN @p_fecha_desde AND @p_fecha_hasta`, `ORDER BY fecha, id`.
5. **Prefijar** una fila sintética saldo inicial (`es_saldo_inicial=1`, `saldo=@saldo_inicial`, columnas detalle vacías salvo cliente).
6. Serializar items a **`items_json`** + columnas `saldo_inicial`, `total` (misma forma que devuelve hoy PHP en `PartesInformeOperations::paqueteHoras`).

**NOLOCK** en lecturas a tablas físicas (regla 75).

### 4.4 Salida SP (1 fila)

| Columna | Tipo | Notas |
|---------|------|--------|
| `saldo_inicial` | INT | S₀ minutos |
| `items_json` | NVARCHAR(MAX) | Array snake_case; controller existente camelCase |
| `total` | INT | Count items incl. saldo inicial |

## 5) Backend PHP

| Pieza | Acción |
|-------|--------|
| `PartesInformeController::paqueteHoras` | Validar `clienteId` si no cliente funcional; resolver cliente sesión para actor cliente |
| `SpCaller` | **Quitar** `pq_sp_partes_informe_paquete_horas` de `isPartesInformeProcedure` **o** condicionar dispatch PHP a `driver === 'sqlite'` únicamente |
| `PartesInformeOperations::paqueteHoras` | Mantener para tests sqlite; comentario `@test-only parity` |
| OpenAPI / FormRequest | Documentar `clienteId` required |

## 6) Frontend

| Pieza | Acción |
|-------|--------|
| `PaqueteHorasPage.tsx` | No `load` en mount sin `clienteId` (supervisor/asistente); selector cliente obligatorio |
| Grid Duración | `customizeText` / `cellPrepared`: si `esTarea === false` → `-` + `formatHhMm` + clase rojo (`partes-paquete-horas-duration-credit` o tema DX) |
| Columna Saldo | `hh:mm` signo algebraico |
| i18n | `partes.informe.saldoInicialLabel` (5 locales) |
| `partesInformePivotCatalog` | Sin campo Saldo (verificar test existente) |
| `data-testid` | Mantener estables; opcional celda duración compra |

## 7) Plan de tareas (D)

| ID | Capa | Descripción | DoD |
|----|------|-------------|-----|
| T1 | DB | SP `pq_sp_partes_informe_paquete_horas.sql` + deploy | AC-05…07, AC-09 |
| T2 | BE | Validación cliente + SpCaller sqlsrv | AC-01, AC-09 |
| T3 | FE | Cliente obligatorio + estilos duración | AC-02…04 |
| T4 | i18n | Saldo inicial + errores | AC-05 |
| T5 | Tests | Feature 422; fixture 2 clientes; Vitest formatter | AC-01…07 |
| T6 | Docs | Producto `18-obtencion-saldo` si delta menor | — |

**Estimación:** T1 L, T3 M, resto S–M.

## 8) Tests mínimos

1. **API:** supervisor, fechas OK, sin `clienteId` → 422.
2. **API:** cliente C, S₀=-60, periodo 1 tarea 120 min + 1 paquete 60 min → saldos [-60, 60, 0] en filas (ejemplo ilustrativo; assert orden e ids).
3. **API:** dos clientes en datos; consulta solo A → cero filas de B.
4. **Vitest:** formatter celda compra negativa.
5. **sqlite:** suite CI sigue pasando vía PHP shim.

## 9) Riesgos / dependencias

- Paridad exacta actor/tipo cliente vs PHP — validar con fixture copiado de test integración existente.
- SP JSON: tamaño respuesta grande periodos largos — aceptado (mismo patrón actual).

## 10) C1 — veredicto

**Apto para D1** — contrato API/SP/FE acotado; referencia SQL PQ incorporada; excepción test-only sqlite explícita.
