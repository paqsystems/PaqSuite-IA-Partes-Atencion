# Runbook — Cierre TR/HU/SPEC updates (PQ) y cola viva

| Campo | Valor |
|-------|--------|
| **Última revisión** | 16/11/2026 |
| **Alcance** | CC PQ **#7–#10** (familia original en `docs/04-tareas/updates/001-Generalidades/` y `100-SistemaPartes/`) + **cola abierta** posterior |
| **Metodología** | `G → D → E → F → Finalizado (manual) → Parte I` — [`00-ControlCalidad-PQ.md`](../00-ControlCalidad/00-ControlCalidad-PQ.md) · dispatcher **Parte I** |

## Plantilla rápida (cualquier CC)

| Paso | Acción | Comando / criterio |
|------|--------|-------------------|
| 1 | Verificar D/E/F | `D-VERIFICACION-*` en `docs/04-tareas/updates/...` |
| 2 | Smoke manual pendiente en D | Completar § smoke del operador |
| 3 | **Finalizado** manual | SPEC-update + HU-update + TR-update (los tres) |
| 4 | **Parte I** acotada por fecha | `Unificá las historias de usuario de PQ de dd/MM/yyyy` |
| 5 | Limpieza | Borrar updates fusionados; dejar A1 huérfanos solo si el SPEC base ya absorbió el contenido |
| 6 | Índice CC | Actualizar fila en tabla índice de `00-ControlCalidad-PQ` si el bloque quedó cerrado |

Orden Parte I por familia: **SPEC-update → HU-update → TR-update** (solo metadato **Finalizado**).

---

## CC #7 (19/10/2026) — Mail usuarios + mail proceso masivo

| Ítem | TR / familia | Estado 16/11/2026 |
|------|----------------|-------------------|
| Mail ABM usuarios (CC #7 → refino **#16**) | TR-002 | **Cerrado** — Parte I **07/11/2026** → [TR-002](../04-tareas/100-SistemaPartes/TR-002-identidad-funcional-y-acceso.md) §13 |
| Informar clientes masivo (CC #7 → refino **#15**) | TR-005 | **Cerrado** — Parte I **04/11/2026** → [TR-005](../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md) |

**Evidencia:** `notify-access` / `informar-clientes` en `backend/routes/api.php`; [D-VERIFICACION-CC-PQ-16](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-16-2026-11-07.md); [D-VERIFICACION-005-update-masivo-informar-clientes](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-005-update-masivo-informar-clientes.md).

**Comandos históricos Parte I:**

```text
Unificá las historias de usuario de PQ de 04/11/2026
Unificá las historias de usuario de PQ de 07/11/2026
```

**Residual:** en `docs/05-open-spec/updates/100-SistemaPartes/` pueden quedar solo **A1** (`SPEC-002-*-A1*`, `SPEC-005-*-A1*`) sin SPEC/TR update — opcional borrar si el review ya está en el SPEC base.

---

## CC #8 (25/10/2026) — Paquete de horas (duración + informe)

| Ítem | TR / familia | Estado 16/11/2026 |
|------|----------------|-------------------|
| Duración 1–240 h | TR-013 | **Parte I 06/11/2026** → [TR-013](../04-tareas/100-SistemaPartes/TR-013-carga-paquetes-horas.md) |
| Destaque + saldo informe | TR-006 | **Parte I 06/11/2026** → [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) |

**Evidencia:** [D-VERIFICACION-CC-PQ-8](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-8-2026-10-25.md) · [D-VERIFICACION-CC-PQ-10](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-10-2026-10-30.md) (saldo tests).

**Comando Parte I:**

```text
Unificá las historias de usuario de PQ de 25/10/2026
```

(o el cierre unificado documentado **06/11/2026** en CC #8 / #10).

**Residual:** `SPEC-006-update-*-A1*` y `SPEC-013-update-*-A1*` en `updates/` — limpieza opcional.

---

## CC #9 (30/10/2026) — Seguridad MONO / GEN-34

| Ítem | Dónde vive | Estado 16/11/2026 |
|------|------------|-------------------|
| Hito **Framework** (CA-FW-01…07) | Repo `PaqSuite-IA-FRAMEWORK` | Seguimiento en CC #9; host no redefine DDL |
| Adopción **host Partes** | [TR-adopcion-host-cc-pq-9](../04-tareas/001-Generalidades/TR-adopcion-host-cc-pq-9-seguridad-mono-multi.md) | **Finalizado 06/11/2026** (promovido a ruta base, ya no en `updates/`) |

**Checklist host (hecho):** pin `laravel-core` · omit `fw-sp-pq_sp_admin_empresa_tango_dictionary` · CA-H01…H03 · [PROVISORIO-checklist-deploy](../06-operacion/PROVISORIO-checklist-deploy-beta12-cc8-cc9.md) § CC #9 [x].

**Parte I:** contenido absorción en TR base `001-Generalidades/` (no quedan TR en `updates/001-Generalidades/`).

---

## CC #10 (30/10/2026) — GEN-13 sqlsrv + saldo informe

| Ítem | TR / familia | Estado 16/11/2026 |
|------|----------------|-------------------|
| Tareas programadas (historial, `nextRunAt`, backup S3) | TR-GEN-13 | **Finalizado** — [TR-GEN-13](../04-tareas/001-Generalidades/TR-GEN-13-adopcion-tareas-programadas.md) · Parte I **06/11/2026** |
| Saldo informe (índice CC #10) | TR-006 | Unificado con CC #8 (ver arriba) |

**Evidencia:** [D-VERIFICACION-CC-PQ-10](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-10-2026-10-30.md) · runbook [adopcion-gen-13-tareas-programadas.md](./adopcion-gen-13-tareas-programadas.md).

**No quedan** archivos `TR-GEN-13-*` ni `TR-adopcion-host-*` bajo `docs/04-tareas/updates/`.

---

## Resumen cola original (6 TR en `updates/`)

A **16/11/2026** los seis TR que vivían en `docs/04-tareas/updates/001-Generalidades/` y `100-SistemaPartes/` de CC #7–#10 están **fusionados o promovidos** a ruta base **Finalizado**. La carpeta `updates/` en esas rutas **no** contiene ya esos TR.

---

## Cola viva — prioridad siguiente

### ~~P0~~ **Cerrado** · CC #17 (14/11/2026) — GEN-37

**Parte I 18/11/2026** — [D-VERIFICACION-CC-PQ-17](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-17-2026-11-14-gen37.md) (F1/F). Updates eliminados → [SPEC-011](../05-open-spec/100-SistemaPartes/SPEC-011-reportes-emisiones.md) §11 · [TR-011](../04-tareas/100-SistemaPartes/TR-011-reportes-emisiones.md) §20 · oleada 2 B §10 · [TR-015-update-b](../04-tareas/100-SistemaPartes/TR-015-update-b-smart-capture-diseno-informes.md).

TR-011/HU-011/SPEC-011: **Finalizado**. TR-015-update-b: **En Control Calidad** (T8/T9 oleada 2 B).

### P1 · CC #18 (15/11/2026) — GEN-13 chrome + i18n FE

| Estado | Acción |
|--------|--------|
| **Pendiente** (sin Parte G) | `Corrige los errores del CC #18 alineación GEN-13 chrome/i18n Partes de PQ` |
| TR destino probable | TR puntual en [TR-GEN-13](../04-tareas/001-Generalidades/TR-GEN-13-adopcion-tareas-programadas.md) o update temporal → Parte I en familia GEN-13 |

**No ejecutar Parte I** hasta ciclo D→E→F y **Finalizado** manual.

---

## Dependencias / Framework

| CC | Depende de |
|----|------------|
| #9 host | Release Framework seguridad MONO (CA-FW abiertos en Framework) |
| #10 / GEN-13 | Framework `adopcion-gen-13-tareas-programadas-host.md` + pins documentados en TR-GEN-13 |
| #17 | Parche mínimo validator en `laravel-core` (ver D-VERIFICACION-CC-PQ-17) |

---

## Comandos dispatcher (referencia)

| Objetivo | Comando natural |
|----------|-----------------|
| Volcar CC nuevo | `Corrige los errores del dd/MM/yyyy de PQ` |
| Unificar una fecha | `Unificá las historias de usuario de PQ de dd/MM/yyyy` |
| Unificar todo lo Finalizado | `Unificá las historias de usuario` |
| Solo SPEC | Atajo **M** (SPEC-update Finalizado → base) |