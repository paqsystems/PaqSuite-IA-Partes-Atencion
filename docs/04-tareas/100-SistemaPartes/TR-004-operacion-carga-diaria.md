# TR-004 – Operación / carga diaria de tareas

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-004-operacion-carga-diaria](../../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) |
| **SPEC relacionada** | [SPEC-004-operacion-carga-diaria](../../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Asistente / Supervisor (`resultado.partes`); **no** cliente |
| **Dependencias** | [TR-001](./TR-001-modelo-datos-modulo.md) (`row_version`), [TR-002](./TR-002-identidad-funcional-y-acceso.md), [TR-003](./TR-003-maestros-y-catalogos.md) (catálogos / universo tipos) |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Pendiente (D implementado — verificar F1) |
| **Última actualización** | 2026-07-30 (D) |

**Origen:** [HU-004](../../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md)  
**Referencia SPEC:** [SPEC-004](../../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md)

---

## 1) HU refinada (resumen)

### In scope
- Pantalla web grilla + filtros (fechas default hoy; estado default **todas**).
- CRUD tareas con validaciones §4.4; bits default 0; limpiar tipo al cambiar cliente.
- Delimitación API por rol; cerrar/reabrir **individual** solo supervisor.
- Optimistic lock `rowVersion` → 409.
- Duración: tramos + editable; param **`PartesDuracionTramoMin`** default **15**.
- Paginación DevExtreme; menú Partes; SP MUST.
- Atajo texto mínimo a proceso masivo (SPEC-005) **sin** pasar filtros (solo UI link; sin lógica masiva aquí).

### Out of scope
- Masivo (TR-005); consultas/dashboard; mobile kardex; IA; Excel; ABM maestros.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | Asistente: solo propias; Asistente no editable; otro `usuarioId` → 403 |
| AC-02 | Supervisor: Asistente editable; cualquier asistente usable |
| AC-03 | Cliente: 403 APIs carga; sin menú |
| AC-04 | Duración: múltiplo de `PartesDuracionTramoMin` (def 15), >0, ≤1440; UI tramos+editable; paginación DX |
| AC-05 | Observación blank / catálogo no usable / tipo fuera universo → 422 |
| AC-06 | Fecha futura → warn confirmable; luego persiste |
| AC-07 | `cerrado=1` no edit/delete ordinario; supervisor cierra/reabre fila |
| AC-08 | Abrir: `fechaDesde`=`fechaHasta`=hoy |
| AC-08b | Filtro estado default **todas**; cerradas solo lectura |
| AC-09 | Sin fechas → no listado amplio + i18n |
| AC-10 | Cambio cliente limpia tipo fuera universo; grabar vacío → 422 |
| AC-10b | Alta: `sinCargo`/`presencial` false; obligatorios incompletos → 422 |
| AC-11 | i18n `partes.tarea.*` + testids |
| AC-12 | Update/delete con `rowVersion` stale → **409** + i18n refrescar |

### Gherkin
HU-004 + escenario 409 y tramo param ≠15 si se cambia seed en test.

---

## 3) Reglas

R-OP-01…12.

| ID | Implementación |
|----|----------------|
| RN-TR-01 | Param GRAL clave fija **`PartesDuracionTramoMin`**, programa **`Partes`** (o el `Programa` canónico del módulo en seed GEN); valor entero minutos; default **15**. |
| RN-TR-02 | API list exige `fechaDesde` + `fechaHasta` (ISO date); si faltan → 422 `partes.tarea.fechasRequeridas`. |
| RN-TR-03 | Query `estadoCerrado`: `todas` \| `abiertas` \| `cerradas` (default `todas`). |
| RN-TR-04 | `rowVersion` en JSON = string hex del `rowversion` SQL Server (opaco). |
| RN-TR-05 | Fecha futura: API **acepta** si body `confirmarFechaFutura=true`; si fecha > hoy y flag ausente/false → 422 `partes.tarea.fechaFuturaConfirmacion` (UI muestra warn y reenvía con flag). |
| RN-TR-06 | Deny `tipoFuncional=cliente`; asistente no supervisor no puede `esSupervisor` actions setCerrado. |

---

## 4) Datos — SP y param

### 4.1 SP (nombres cerrados)

| SP | Rol |
|----|-----|
| `pq_sp_partes_tarea_list` | Filtros + paginación + delimitación por `@p_actor_*` |
| `pq_sp_partes_tarea_get` | `@p_id` + delimitación |
| `pq_sp_partes_tarea_upsert` | Insert/update; valida obligatorios, tramo, universo, cerrado=0 en update de negocio; `@p_row_version` en update |
| `pq_sp_partes_tarea_delete` | Solo `cerrado=0` + derecho + row_version |
| `pq_sp_partes_tarea_set_cerrado` | `@p_id`, `@p_cerrado`, `@p_row_version`; solo si actor es supervisor dominio |

List params conceptuales: `@p_fecha_desde`, `@p_fecha_hasta`, `@p_cliente_id` NULL, `@p_usuario_id` NULL (filtro propietario), `@p_estado_cerrado`, `@p_page`, `@p_page_size`, ids del actor sesión.

### 4.2 Seed param

| Clave | Default | Notas |
|-------|---------|-------|
| `PartesDuracionTramoMin` | `15` | Tipo numérico GRAL; editable en pantalla parámetros GEN |

---

## 5) Contratos API

Base `/api/v1/partes/tareas` — Bearer + tenant + perfil Partes + middleware perfil.

| Método | Path | Notas |
|--------|------|-------|
| GET | `/partes/tareas` | Query: `fechaDesde`, `fechaHasta`, `clienteId?`, `usuarioId?`, `estadoCerrado?`, `page`, `pageSize` |
| GET | `/partes/tareas/{id}` | |
| POST | `/partes/tareas` | Body camelCase + `confirmarFechaFutura?` |
| PUT | `/partes/tareas/{id}` | + `rowVersion` + `confirmarFechaFutura?` |
| DELETE | `/partes/tareas/{id}` | Body o query `rowVersion` |
| POST | `/partes/tareas/{id}/cerrar` | Supervisor; `rowVersion` |
| POST | `/partes/tareas/{id}/reabrir` | Supervisor; `rowVersion` |
| GET | `/partes/parametros/duracion-tramo` | Devuelve `{ tramoMinutos: N }` leyendo GRAL (o incluir en bootstrap); evita hardcode FE |

**409** conflicto: `{ "error": <cat>, "respuesta": "partes.tarea.conflictoVersion", "resultado": {} }`.

Catálogos: reutilizar TR-003 (`/partes/catalogos/...`).

### OpenAPI
Documentar paths + 403/409/422 + `resultado.partes` no aplica aquí.

---

## 6) Frontend

| Pieza | Detalle |
|-------|---------|
| Ruta | `/partes/carga-diaria` |
| Menú | Partes → Carga diaria (asistente + supervisor; no cliente) |
| Filtros | Fechas (default hoy), cliente opcional, asistente (solo supervisor), estado cerrado (default todas) |
| Grid | DX ProcessDataGrid paginado; Cliente/Tipo = descripción; Sin cargo/Presencial visibles; duración celda `hh:mm` + campo `duracionHoras` (decimal) con sumatoria; códigos/minutos ocultos por defecto (chooser); filas `cerrado` read-only |
| Duración | SelectBox de tramos etiquetados `hh:mm` (`value` = minutos); valida múltiplo del tramo |
| Alta | Defaults bits false; tipo default del universo si existe |
| Cambio cliente | Limpia tipo si no ∈ universo; mensaje i18n |
| Fecha futura | Confirm dialog → re-POST con flag |
| Cerrar/reabrir | Botones fila solo si `esSupervisor` |
| Atajo masivo | Link texto a ruta TR-005 **sin** query de filtros |
| testids | `partesCargaFiltros`, `partesCargaGrid`, `partesCargaSave`, … |

IA: no UI.

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1 | DB | SP list/get/upsert/delete/set_cerrado | AC API | L |
| T2 | Seed | `PartesDuracionTramoMin=15` + ítem menú carga | Param + menú | S |
| T3 | Backend | Controllers + delimitación + 409/422 | AC-01…07,12 | L |
| T4 | Frontend | Página filtros+grid+editores+warns | AC-04…11 | L |
| T5 | Frontend | Acciones cerrar/reabrir + link masivo | AC-07 | S |
| T6 | Tests | Feature roles/validación/409; Vitest duración; E2E humo alta | Suite | L |
| T7 | Docs | OpenAPI | | S |

**Orden:** T1 → T2 → T3 → T4/T5 → T6 → T7.

---

## 8) Tests

| Capa | Casos |
|------|--------|
| Feature | Asistente 403 otro owner; cliente 403; duración; universo; cerrado; 409; setCerrado no asistente |
| Vitest | Múltiplo tramo; formato `hh:mm`; horas decimales; limpiar tipo |
| E2E | Abrir carga (fechas hoy) + alta mínima OK |

---

## 9) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Encoding `rowversion` | Hex mayúsculas estable en BE+FE |
| Confirm fecha futura solo FE | RN-TR-05 exige flag en API |
| Tramo 0 o inválido en param | SP/API: si param ≤0 → usar 15 y log/warn |

---

## 10) Checklist

- [ ] AC-01…12  
- [ ] SP + param + menú  
- [ ] FE grilla + validaciones  
- [ ] Tests + OpenAPI  

---

## 11) Informe C1

# Revisión de ambigüedad - TR-004

## Resultado general
- **Apto con observaciones** (absorbidas)

## Críticas cerradas
- Clave param → **`PartesDuracionTramoMin`**
- SP nombres → familia `pq_sp_partes_tarea_*`
- Fecha futura API → flag **`confirmarFechaFutura`**
- `rowVersion` → string hex opaco
- `estadoCerrado` enum cerrado

## Menores
- Programa GRAL exacto (`Partes`): D1 confirma convención seed GEN del host
- Edición inline vs popup en grid: libre DX si cumple AC
- Endpoint param tramo vs leer param GEN genérico: TR ofrece GET dedicado Must mínimo

## Contradicciones
- Ninguna con SPEC/HU batch

## Veredicto
- **Puede pasar a D1/D: Sí**

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte C + C1: TR carga diaria; param/SP/409/fecha futura cerrados. |
| 2026-07-30 | Parte D: Operations `tarea_*`, API `/partes/tareas`, param tramo, menú Carga diaria, FE grilla+modal, Feature+Vitest OK. SP T-SQL follow-up gateway. |
| 2026-07-31 | FE grilla: descripción Cliente/Tipo; Sin cargo/Presencial; duración `hh:mm` + sumatoria `duracionHoras`; Vitest helpers duración. |

---

**Siguiente:** F1 de TR-004, o D de TR-005 cuando se autorice.
