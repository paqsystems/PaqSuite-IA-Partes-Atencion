# Verificación F — CC PQ #13 (02/11/2026)

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#13** |
| **Parte E** | [E-CC-PQ-13-2026-11-03.md](./E-CC-PQ-13-2026-11-03.md) |
| **Veredicto F** | **Apto** — regresión automatizada OK; smoke manual UI **OK tenant DEV** (03/11/2026, PQ) |
| **F1/F** | **Aprobado** 03/11/2026 — checklist §4 validado en DEV (Field List Paquete de horas + FilterRow maestros/masivo) |
| **Parte I** | 03/11/2026 — fusionado en TR-003, TR-005, TR-011 base (updates CC #13 no versionados; cierre documental acotado) |

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

---

## 6) Deploy (preview/prod)

- Código API/FE + REPX runtime (`PartesEmissionDesignRepxBuilder`); catálogo emisión ya cubierto por migración CC #12 si aplica.
- **Sidecar DX (Must diseñador):** copiar `backend/resources/emissions/schemas/*.json` → `data/schemas/` del sidecar (`scripts/forge/sync-dx-reporting-schemas.sh` en post-deploy Forge; local `scripts/dev/sync-dx-reporting-schemas.ps1`).
- Smoke post-deploy: `php backend/scripts/dev/smoke-paquete-horas-designer-schema.php` (sidecar accesible).
- Filtros listado: parámetros query en Operations maestros/masivo (sin migración CC #13 adicional).

---

## 7) Cierre

- **F1/F:** Aprobado 03/11/2026 (DEV, PQ).
- **Parte I:** historial CC #13 en TR-003, TR-005, TR-011 base — ver [00-ControlCalidad-PQ §#13](../../../00-ControlCalidad/00-ControlCalidad-PQ.md).
