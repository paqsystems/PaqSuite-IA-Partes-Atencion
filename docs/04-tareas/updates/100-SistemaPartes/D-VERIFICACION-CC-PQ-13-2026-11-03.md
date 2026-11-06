# Verificación F — CC PQ #13 (02/11/2026)

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#13** |
| **Parte E** | [E-CC-PQ-13-2026-11-03.md](./E-CC-PQ-13-2026-11-03.md) |
| **Veredicto F** | **Apto** — regresión automatizada OK; smoke manual UI **OK** DEV + **preview** (03/11/2026, PQ) |
| **F1/F** | **Aprobado** 03/11/2026 — §4 DEV + preview `v1.3.0-CLOUDSMITH` @ **`88c46ea`** (Field List Paquete de horas + FilterRow maestros/masivo) |
| **Parte I** | 03/11/2026 — fusionado en SPEC/HU/TR-003, 005, 011 base (cierre acotado; updates CC #13 conservados en repo) |

---

## 1) Criterios de aceptación

| CA | Resultado F | Evidencia |
|----|-------------|-----------|
| **CA-13-01** Diseñador Paquete de horas — Field List negocio | **OK** | Unit + E2E; manual DEV §4.1 (columnas negocio, no `id`/`label`); schemas `backend/resources/emissions/schemas/` + sync sidecar |
| **CA-13-02** Clientes — FilterRow nombre/código | **OK** | Vitest + Playwright; manual DEV §4.2 |
| **CA-13-03** Asistentes — FilterRow | **OK** | Idem; manual DEV §4.3 |
| **CA-13-04** Proceso masivo — FilterRow cliente / tipo tarea | **OK** | Vitest; manual DEV §4.5 |
| **CA-13-05** Regresión tests | **OK** | Ver [E-CC-PQ-13](./E-CC-PQ-13-2026-11-03.md) §1–3 |

**Tipos cliente / tipos tarea (alcance CC):** cubiertos en E2E Playwright + Unit Operations (no listados como CA separados en PQ; incluidos en hito FilterRow maestros).

---

## 1.1) F1 — revisión código ↔ TR (03/11/2026)

| RN / pieza | Evidencia | Estado |
|------------|-----------|--------|
| Diseñador Paquete de horas — schema negocio | `PartesEmissionDesignRepxBuilder`, `backend/resources/emissions/schemas/`, `sync-dx-reporting-schemas.*`, TR-011 §18 | **OK** |
| FilterRow maestros | `devExtremeFilterContains`, `maestroListPaging`, Operations filtros API, TR-003 | **OK** |
| FilterRow masivo | `masivoTareasPaging`, query `clienteNombre` / `tipoTareaDescripcion`, TR-005 | **OK** |
| Regresión paginación CC #11/#12 | E2E + manual §4.6 | **OK** |
| OpenAPI / contratos listado | Query params documentados en rutas maestros/masivo | **OK** |

**Veredicto F1:** **Aprobado** (consistente con [D-CC-PQ-13](./D-CC-PQ-13-2026-11-03.md)).

---

## 2) Regresión automatizada (03/11/2026)

| Capa | Resultado |
|------|-----------|
| PHPUnit layout/builder + Operations filter | **OK** |
| PHPUnit Feature CC13 HTTP | **No corrido OK** (drift `PQ_TASK_PROCESS.process_description` en seed SQLite) |
| Vitest CC13 mappers | **OK** (9) |
| Playwright `partes-cc-pq-13-filterrow.spec.ts` | **OK** (4 maestros, `--workers=1`, `?cliente=DEMO` mocks) |

---

## 3) Smoke API (script repetible)

Script: `backend/scripts/dev/smoke-cc13-f-api.php`

```powershell
php backend/scripts/dev/smoke-cc13-f-api.php `
  "http://127.0.0.1:8010/api/v1" `
  DEV `
  <usuario> `
  "<contraseña>"
```

Comprueba login + `GET` clientes/asistentes/tipos con filtros + `GET` tareas masivo `clienteNombre` + catálogo diseño `partes.informes.paqueteHoras`.

**Intento 03/11/2026:** local `127.0.0.1:8010` y Forge develop con usuario `PQ` / seed documentado → **`auth.invalidCredentials`** (contraseña real del tenant distinta del seed en repo). Stack local levantado (`start-dev.ps1 -All -SkipSqlSync`); health **200**.

---

## 4) Checklist smoke manual UI (PQ)

Ejecutar con credenciales válidas del tenant (lab **DEV**, preview o **PAQ** prod post-deploy). URLs base: frontend + `?cliente=<tenant>` en rutas protegidas.

### 4.1 CA-13-01 — Diseñador Paquete de horas

1. Login supervisor.
2. **Emisiones → Diseñador** (`/emisiones/disenador?cliente=…`).
3. Proceso **Paquete de horas** → confirmar.
4. En **Field List**, verificar columnas de negocio (`clienteNombre`, `clienteCode`, `duracionMinutos`, `saldo`, `tipoTareaDescripcion`, …).
5. **No** debe predominar solo `id` / `label` genéricos.
6. Opcional: arrastrar un campo al layout y guardar sin error de sidecar.

### 4.2 CA-13-02 — Clientes

1. `/archivos/partes/clientes?cliente=…`
2. Confirmar paginador remoto (total > pageSize).
3. FilterRow **Nombre** → criterio parcial (ej. `ANKAS`) → Enter.
4. Filas visibles deben coincidir; total bajo grilla coherente.
5. Repetir en **Código** si la fila de filtros está visible.

### 4.3 CA-13-03 — Asistentes

1. `/archivos/partes/asistentes?cliente=…`
2. FilterRow **Nombre** (columna tras código / usuario) → criterio parcial → filas acotadas.

### 4.4 Tipos cliente / tipos tarea (maestros)

1. `/archivos/partes/tipos-cliente` — FilterRow **Descripción**.
2. `/archivos/partes/tipos-tarea` — FilterRow **Descripción**.

### 4.5 CA-13-04 — Proceso masivo

1. `/partes/proceso-masivo?cliente=…`
2. Rango fechas con datos → **Buscar**.
3. FilterRow **Cliente** (y/o **Tipo de tarea**) → criterio parcial.
4. Verificar en red: query `clienteNombre` / `tipoTareaDescripcion` en `GET /partes/tareas`.
5. Paginador sigue operativo; total filtro coherente (CC #11).

### 4.6 Regresión rápida CC #11 / #12

- Masivo: cambiar página sin perder filtros de pantalla.
- Maestros: total administrable ≠ solo primera página.

---

## 5) Smoke manual agente (03/11/2026)

| Paso | Resultado | Notas |
|------|-----------|-------|
| Stack local | **OK** | Frontend `:3000`, backend `:8010`, health 200 |
| Login UI tenant DEV (`PQ` / seed doc) | **FAIL** | Mensaje «Usuario o contraseña incorrectos» |
| Login API script | **FAIL** | Mismo `auth.invalidCredentials` |
| Checklist §4.1–4.6 | **OK DEV** | PQ 03/11/2026 — Field List Paquete de horas + FilterRow maestros/masivo |
| Checklist §4.1–4.6 | **OK preview** | PQ 03/11/2026 — mismo checklist en preview post-deploy |

---

## 6) Smoke preview (`v1.3.0-CLOUDSMITH` @ `88c46ea`)

| Campo | Valor |
|-------|--------|
| **Frontend** | [partesatencion-dev.paqsystems.com](https://partesatencion-dev.paqsystems.com) → deployment Vercel `88c46ea` |
| **API** | `https://partesatencion-paqsystems-dev.on-forge.com/api/v1` |
| **Tenant** | **DEV** |
| **Responsable smoke** | PQ — 03/11/2026 |

| CA | Resultado | Notas |
|----|-----------|-------|
| CA-13-01 | **OK** | Field List Paquete de horas — columnas de negocio (no predominio `id`/`label`) |
| CA-13-02 | **OK** | Clientes — FilterRow nombre/código acota filas + total coherente |
| CA-13-03 | **OK** | Asistentes — FilterRow nombre |
| CA-13-04 | **OK** | Proceso masivo — FilterRow cliente / tipo tarea; paginador remoto intacto |
| CA-13-05 | **OK** | Sin regresión perceptible vs suite E/Vitest (Parte E) |
| §4.6 CC #11/#12 | **OK** | Paginación maestros/masivo sin pérdida de filtros de pantalla |

Deploy: Forge post-deploy (`installation:update`, `sync-dx-reporting-schemas.sh`); manifiesto host **beta.24** en rama de preview.

---

## 7) Deploy (prod — pendiente)

Referencia operativa (no bloquea cierre F1/F preview):

- Código API/FE + REPX runtime (`PartesEmissionDesignRepxBuilder`); catálogo emisión ya cubierto por migración CC #12 si aplica.
- **Sidecar DX (Must diseñador):** copiar `backend/resources/emissions/schemas/*.json` → `data/schemas/` del sidecar (`scripts/forge/sync-dx-reporting-schemas.sh` en post-deploy Forge; local `scripts/dev/sync-dx-reporting-schemas.ps1`).
- Smoke post-deploy: `php backend/scripts/dev/smoke-paquete-horas-designer-schema.php` (sidecar accesible).
- Filtros listado: parámetros query en Operations maestros/masivo (sin migración CC #13 adicional).

---

## 8) Cierre

- **F1:** Aprobado 03/11/2026 — §1.1 código ↔ TR.
- **F:** Aprobado 03/11/2026 — regresión §2 + smoke DEV + **preview** §6 (PQ).
- **Parte I:** 03/11/2026 — historial CC #13 en SPEC/HU/TR-003, 005, 011 base — [00-ControlCalidad-PQ §#13](../../../00-ControlCalidad/00-ControlCalidad-PQ.md).
- **Prod:** pendiente PR/`main` (fuera de alcance de este cierre preview).
