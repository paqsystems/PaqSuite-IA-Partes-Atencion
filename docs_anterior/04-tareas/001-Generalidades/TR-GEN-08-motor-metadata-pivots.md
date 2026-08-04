# TR-GEN-08-motor-metadata-pivots — Motor y catálogo de consultas pivotables

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-08-motor-metadata-pivots](../../03-historias-usuario/001-Generalidades/HU-GEN-08-motor-metadata-pivots.md) |
| **SPEC relacionada** | [SPEC-001-08-pivots](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Could |
| **Dependencias** | TR-GEN-02-login-sesion; TR-GEN-02-autorizacion-menu-api |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-11 (D1 motor-metadata) |

**Origen:** [HU-GEN-08-motor-metadata-pivots](../../03-historias-usuario/001-Generalidades/HU-GEN-08-motor-metadata-pivots.md)  
**Referencia SPEC:** [SPEC-001-08-pivots](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md)  
**MONO:** [arquitectura_motor_pivots_y_flujo.md](../../00-contexto/_mono/pivots/arquitectura_motor_pivots_y_flujo.md), [especificacion_tecnica_consultas_pivotables.md](../../00-contexto/_mono/pivots/especificacion_tecnica_consultas_pivotables.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Motor backend de metadata pivotable, validaciones y ejecución del dataset base.

### Narrativa
Como desarrollador/analista quiero metadata formal en Dictionary DB por `consulta_id` para que el frontend reciba definición efectiva, valide filtros y obtenga datos sin hardcodear pivots.

### In scope / Out of scope
- **In scope:** tablas `pq_pivots_consultas`, `pq_pivots_campos`, `pq_pivots_plantillas`, `pq_pivots_plantillas_det`, `pq_pivots_validaciones`; resolución definición efectiva; API metadata y dataset; validaciones metadata/filtros/estructura/volumen; envelope MONO; seeds SQL de consulta piloto.
- **Out of scope:** PivotGrid UI, diseños guardados (`TR-GEN-08-layouts-pivot`), export Excel pivot, ABM web de catálogos, MVP portal release actual.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: `GET metadata` devuelve definición efectiva para `consulta_id` activo con `pivotHabilitado = true`.
- **AC-02**: Respuesta incluye `pivotBase`, `campos`, `filtrosGenerales`, `restricciones`, `exportacion`, `persistencia`, `configuracionGeneral`.
- **AC-03**: Metadata inválida (campo faltante en `pivotBase`) → error negocio sin dataset.
- **AC-04**: Filtros obligatorios faltantes bloquean `POST data` con `error: 1000` y clave i18n.
- **AC-05**: Exceso de `maximoRegistrosBase` respeta `bloquearSiExcedeVolumen`.
- **AC-06**: Validación estructura pivot rechaza combinaciones fuera de límites (filas/columnas/métricas).
- **AC-07**: JSON de metadata no expone `nombreTecnico` al frontend en labels visibles.
- **AC-08**: `pivotHabilitado = false` → metadata indica solo grilla (`permiteCambiarAVistaPivot: false`).
- **AC-09**: `admite_drilldown` expuesto por consulta (AMB-Q-P08-01 cerrada).
- **AC-10**: Tests Feature: metadata 200, data 401, filtro obligatorio 422/1000.

### Escenarios Gherkin

(Heredados de HU.)

---

## 3) Reglas de Negocio

1. **RN-01**: Tabla canónica diseños: `pq_pivots_config` (no `PQ_PIVOTS` legacy).
2. **RN-02**: Toda consulta pivotable tiene exactamente una `pivotBase`.
3. **RN-03**: Herencia plantilla global → overrides locales en `pq_pivots_campos`.
4. **RN-04**: Métricas con `agregacionDefault` y `agregacionesPermitidas` compatibles con `tipoDato`.
5. **RN-05**: Filtros generales afectan grilla y pivot por igual.
6. **RN-06**: Validaciones en capas: metadata → filtros → estructura → volumen (arquitectura §7).
7. **RN-07**: `configId` (frontend) ↔ `pivot_id` (BD) mapeo 1:1 (AMB-M-P08-03).
8. **RN-08**: Drill-down solo si `admite_drilldown = 1` por consulta.
9. **RN-09**: Fuente dataset según `fuente_tipo` / `fuente_nombre` de `pq_pivots_consultas`.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-11)

**Fuentes revisadas:** HU-GEN-08-motor-metadata-pivots, SPEC-001-08, `arquitectura_motor_pivots_y_flujo.md`, `especificacion_tecnica_consultas_pivotables.md` §19–§21, `modelo_datos_pivots_y_catalogo.md`, `_NORMAS-TRANSVERSALES-TR.md`, `envelope-respuestas.md`, `matriz-permisos-mvp.md`, TR-GEN-08-* hermanas, backend (`routes/api.php`, consultas comerciales existentes).

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (aplicar resoluciones §3.2)

> **Qué significa «resolver» en C1:** §3.2 fija la decisión; D1 solo implementa.

### Ambigüedades críticas

| ID | Tema | Riesgo | Estado | Qué hacer (D1 / código) |
|----|------|--------|--------|-------------------------|
| AMB-C01 | **Dictionary DB vs BD única SistemaPartes** | MONO asume Dictionary DB; el portal hoy usa **una** conexión SQL (`DB_DATABASE`) | **Cerrado** (R-C1-01) | En epic SistemaPartes: tablas `pq_pivots_*` en **la misma BD** del tenant. Documentar desviación respecto MONO; no exigir segunda conexión en v1. |
| AMB-C02 | **Shape metadata API** | Implementaciones divergentes sin contrato fijo | **Cerrado** (R-C1-02) | Shape §21 `especificacion_tecnica_consultas_pivotables.md` + envelope MONO; `versionDefinicion` obligatorio. |
| AMB-C03 | **Agregación servidor vs cliente** | Doble lógica o performance impredecible | **Cerrado** (R-C1-03) | `POST data` devuelve **filas planas**; PivotGrid DevExtreme agrega en cliente. |
| AMB-C04 | **Permiso por `consulta_id`** | API no sabe qué `Permiso_Repo` validar | **Cerrado** (R-C1-04) | Columna `procedimiento_host` en `pq_pivots_consultas` (ej. `pw_historialventas`); policy usa mismo gate que consulta comercial. |
| AMB-C05 | **HTTP 404 vs 200 con error** | Metadata de consulta inactiva | **Cerrado** (R-C1-05) | `consultaId` inexistente/inactivo → **404**, `error: 4004`, clave `pivot.consultaNotFound`. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M-P08-01 | `PQ_PIVOTS` legacy | Solo `pq_pivots_config` en código nuevo. |
| AMB-M-P08-02 | `pq_pivots_config_last_used` | DDL en TR-GEN-08-layouts-pivot. |
| AMB-M-P08-03 | `configId` vs `pivot_id` | API `configId`; BD `pivot_id`; OpenAPI documenta mapeo. |
| AMB-M04 | `nombreTecnico` en API | Incluir en `campos[]` solo como `dataField` interno; **caption** siempre `nombreVisible`. |
| AMB-M05 | Paginación dataset | Default `tamanoPagina: 500`; máx `restricciones.maximoRegistrosBase` o 5000 el menor. |
| AMB-M06 | `pq_pivots_aud` | **Fuera D1** epic v1; tabla opcional en migración comentada o fase Should. |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| MONO Dictionary DB vs repo SistemaPartes monoconexión | Misma BD tenant en portal; nota en migración. |
| HU menciona `admite_drilldown` en catálogo; API metadata | Exponer `admiteDrilldown` en GET metadata (AC-09). |
| `validate-structure` no está en HU | Mantener en TR como endpoint auxiliar; invocado desde layouts/pivotgrid antes de guardar diseño. |

### Supuestos detectados

- Auth Sanctum + `X-Paq-Cliente` operativos (TR-GEN-02).
- Executor dataset reutiliza patrón de consultas comerciales (query/view por `fuente_nombre`).
- Seed piloto enlaza `CONSULTA_PILOTO_PIVOT` → `procedimiento_host = pw_historialventas` (o consulta Informes acordada).

### Preguntas para decisión humana

(Ninguna bloqueante — cerradas en §3.2.)

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-11)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Conexión BD | **SistemaPartes epic:** `pq_pivots_*` en BD tenant actual; sin Dictionary DB separada en v1. |
| R-C1-02 | Contrato metadata | Shape §21 especificación técnica + envelope MONO. |
| R-C1-03 | Agregación | Dataset plano en API; PivotGrid agrega en cliente. |
| R-C1-04 | Permiso | `procedimiento_host` en catálogo; `Permiso_Repo` del procedimiento host. |
| R-C1-05 | Consulta no encontrada | HTTP 404, `error: 4004`, `pivot.consultaNotFound`. |
| R-C1-06 | Volumen excedido | HTTP 200 con `error: 2000` **o** 422 según `bloquearSiExcedeVolumen`; si bloquea → no devolver `items`. |
| R-C1-07 | Campos visibles | `nombreVisible` en UI; `nombreTecnico` solo como `dataField` en JSON API. |

---

## 4) Impacto en Datos

### Tablas nuevas (BD tenant — ver R-C1-01)

| Tabla | Rol |
|-------|-----|
| `pq_pivots_consultas` | Catálogo consultas pivotables (+ `procedimiento_host` para permisos) |
| `pq_pivots_campos` | Campos por consulta |
| `pq_pivots_plantillas` | Plantillas globales |
| `pq_pivots_plantillas_det` | Detalle plantillas |
| `pq_pivots_validaciones` | Reglas por consulta |
| `pq_pivots_aud` | Auditoría opcional (Should v1) |

> `pq_pivots_config` y `pq_pivots_config_last_used`: ver **TR-GEN-08-layouts-pivot**.

### Seed mínimo para tests

- Consulta `CONSULTA_PILOTO_PIVOT` con `pivot_habilitado = 1`, `admite_drilldown = 1`, campos y `pivotBase` coherentes.
- Consulta `CONSULTA_SOLO_GRILLA` con `pivot_habilitado = 0`.

### Migración

- `backend/database/migrations/YYYY_MM_DD_create_pq_pivots_catalog_tables.php` (conexión dictionary si aplica).

---

## 5) Contratos de API y OpenAPI

> Bearer + `X-Paq-Cliente` · envelope MONO · `Permiso_Repo` del proceso host.

### 5.1 Endpoints

| Método | Path | Auth | Permiso | Público |
|--------|------|------|---------|---------|
| GET | `/api/v1/pivots/consultas/{consultaId}/metadata` | Bearer + tenant | `Permiso_Repo` proceso host | No |
| POST | `/api/v1/pivots/consultas/{consultaId}/data` | Bearer + tenant | `Permiso_Repo` proceso host | No |
| POST | `/api/v1/pivots/consultas/{consultaId}/validate-structure` | Bearer + tenant | `Permiso_Repo` | No |

**Path param:** `consultaId` = `pq_pivots_consultas.consulta_id`.

### 5.2 Detalle

#### GET `/api/v1/pivots/consultas/{consultaId}/metadata`

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "",
  "resultado": {
    "consultaId": "VENTAS_RESUMEN",
    "versionDefinicion": 1,
    "pivotHabilitado": true,
    "admiteDrilldown": true,
    "configuracionGeneral": {
      "mostrarGrillaYPivot": true,
      "vistaInicial": "grilla"
    },
    "pivotBase": { },
    "campos": [ ],
    "filtrosGenerales": [ ],
    "restricciones": { },
    "exportacion": { },
    "persistencia": { }
  }
}
```

**404:** `consultaId` inexistente o inactivo (`error: 4004`).

#### POST `/api/v1/pivots/consultas/{consultaId}/data`

**Body:** `{ "filtros": { }, "pagina": 1, "tamanoPagina": 500 }`

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "",
  "resultado": {
    "items": [ ],
    "totalRegistros": 1200,
    "truncado": false
  }
}
```

**1000:** filtro obligatorio faltante · **2000:** volumen excedido si `bloquearSiExcedeVolumen`.

#### POST `/api/v1/pivots/consultas/{consultaId}/validate-structure`

**Body:** `{ "filas": [], "columnas": [], "valores": [], "filtrosInternos": [] }`  
Valida límites de `restricciones` antes de aplicar diseño en UI.

### 5.3 Matriz permisos

- [ ] Sección **Pivots — metadata/dataset** en `matriz-permisos-mvp.md` (o anexo epic pivots).

---

## 6) Cambios Frontend

### Alcance mínimo en este slice

- `frontend/src/shared/services/pivotMetadataApi.ts` — cliente GET metadata + POST data.
- Tipos TypeScript alineados a resultado API (sin UI pivot aún).

### data-testid

(N/A en este slice backend-first; E2E en TR-GEN-08-pivotgrid-visualizacion.)

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Migraciones catálogo `pq_pivots_*` | Tablas en Dictionary DB |
| T2 | Backend | `PivotMetadataResolver` + herencia plantillas | Unit tests integridad |
| T3 | Backend | `PivotMetadataController` GET metadata | OpenAPI + envelope |
| T4 | Backend | `PivotDatasetController` POST data | Filtros obligatorios + límite volumen |
| T5 | Backend | Seed consulta piloto | Feature test metadata 200 |
| T6 | Frontend | `pivotMetadataApi.ts` + tipos | Consumo en TR-08-02 |
| T7 | Tests | Feature 401/403/404/1000 | PHPUnit |

---

## 8) Estrategia de Tests

- **Unit:** resolución plantilla+campo, validación pivotBase vs campos.
- **Integration:** metadata 200, data con filtro obligatorio, volumen bloqueado.
- **E2E:** diferido a TR-GEN-08-pivotgrid-visualizacion.

---

## 9) Riesgos y Edge Cases

- Consulta con `version_definicion` distinta a diseño guardado → warning en layouts TR.
- Fuentes `procedure` / `api` externas: timeout y mensaje i18n.
- Catálogo en Dictionary DB no disponible en dev → flag degradación documentado en bootstrap.

---

## 10) Checklist final

- [x] AC cumplidos (metadata, filtros 1000, estructura, volumen, solo-grilla)
- [x] OpenAPI anotado en `PivotController` (matriz permisos epic: pendiente anexo)
- [x] Envelope MONO en todos los endpoints
- [x] Sin ampliación fuera de HU/SPEC

---

## Archivos creados/modificados (D1)

### Backend
- `app/Http/Controllers/Api/V1/Pivots/PivotController.php`
- `app/Services/Pivots/PivotMetadataResolver.php`
- `app/Services/Pivots/PivotDatasetExecutor.php`
- `app/Services/Pivots/PivotStructureValidator.php`
- `app/Exceptions/PivotFlowException.php`
- `app/Support/PivotErrorCodes.php`
- `database/migrations/2026_06_11_100000_create_pq_pivots_catalog_tables.php`
- `database/seeders/Pivots/PivotCatalogPilotSeeder.php`
- `routes/api.php` (prefijo `/api/v1/pivots/consultas`)
- `config/paqsuite_mvp.php` (`pivotsEnabled`, `pivotLayoutsEnabled`)
- `app/Http/Controllers/PublicConfigController.php`
- `tests/Feature/Api/Pivots/PivotMetadataFeatureTest.php`
- `tests/Unit/Services/Pivots/PivotMetadataResolverTest.php`
- `tests/Support/SeedsPivotCatalog.php`

### Frontend
- `frontend/src/shared/services/pivotMetadataApi.ts`
- `frontend/src/shared/types/pivotMetadata.ts`










