# IA en diseño de informes — adopción Partes (piloto GEN-15)

Jira: PIAPARTES-53

## Encuadre (importante)

| Documento | Rol |
|-----------|-----|
| **[Framework — 15-update-4-ia-diseno](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-update-4-ia-diseno.md)** | **Norma transversal:** sidecar, schema JSON por `processCode`, caminos A/B/C, persistencia Save GEN. **SoT GEN.** |
| **Este archivo (`19`)** | **Producto Partes:** primer host piloto, proceso Must, decisiones de módulo, smoke y SDD local. |
| **[15-reportes-emisiones.md](./15-reportes-emisiones.md)** | Adopción GEN-15 sin IA (Emitir + diseñador); no mezclar con este tema. |
| **[SPEC-015](../../05-open-spec/100-SistemaPartes/SPEC-015-ia-diseno-informes.md)** | OpenSpec **adopción piloto Partes** (no sustituye update-4 Framework). |

Partes **no** define el diseñador IA para TANGO, PedidosWeb u otros hosts: aporta **dataset**, **schema semántico** de Consulta detallada y **evidencia F1** que el Framework reutiliza.

---

## Objetivo (módulo)

Usuarios con **`emission.design`** diseñan y modifican layouts de **Consulta detallada** (`partes.informes.consultaDetallada`) en **`/emisiones/disenador`**, con lenguaje natural, **sin** reemplazar DevExpress ni el flujo de [HU-011](../../03-historias-usuario/100-SistemaPartes/HU-011-reportes-emisiones.md).

---

## Qué aporta Partes vs Framework

| Partes | Framework |
|--------|-----------|
| Proceso emisible seed + [`PartesConsultaDetalladaEmissionPort`](../../backend/app/Services/Emissions/PartesConsultaDetalladaEmissionPort.php) | `EmissionReportDesignerPage`, APIs `/api/v1/emissions/design/*` |
| Contenido **label / description / synonyms** ES del schema | Sidecar `dx-reporting`, `ProcessFieldSchemaCatalog`, IA DX 26.1 (camino A) |
| [`ReportDesignerHostPage`](../../frontend/src/features/partes/informes/ReportDesignerHostPage.tsx), menú, permisos | Runbook sidecar, contrato JSON §4 update-4 |
| Batería piloto §4.4 SPEC-015 (frases negocio partes) | Evitar hardcode `consultaDetallada` en C# del catálogo |
| Oleada 2 B/C: turno SC + acciones layout (si F1 lo exige) | GEN-03 / GEN-16 exports |

**Artefacto schema (evolución):** enriquecer  
`PaqSuite-IA-FRAMEWORK/services/dx-reporting/.../data/schemas/partes.informes.consultaDetallada.json`  
(o clave alineada al `processCode` GEN) con metadata §4 update-4; columnas **técnicas** = mismas que el puerto de emisión.

---

## Relación con otras capacidades Partes

| Capacidad | Rol |
|-----------|-----|
| **GEN-15** (15-reportes-emisiones) | Base adoptada TR-011; IA es extensión, no redefinición |
| **GEN-16** BYOK | Credenciales compartidas con chat y Smart Capture |
| **GEN-03** ([14-smart-capture.md](./14-smart-capture.md)) | Solo oleada 2 camino B/C; patrón UX Carga diaria |
| **Chat** ([12](./12-asistente-ia-ayuda-y-chat-documental.md)) | No muta REPX |

---

## Decisiones cerradas (planificación)

- **Proceso primero:** selección proceso emisible → confirmar → diseñador DX (Q13 GEN-15).
- **MVP:** un solo proceso Must — `partes.informes.consultaDetallada`.
- **Persistencia:** Save DevExpress + catálogo GEN; IA **no** persiste layout en turno paralelo.
- **Mobile:** excluido (HU-011).
- **Contexto IA:** por **proceso**, no por cada variante `.repx`.
- **Shell:** mismo host y `VITE_DX_REPORTING_*` que hoy.

---

## Oleadas SDD (Partes)

### Oleada 1 — Piloto Must

Validar en instalación Partes + sidecar Framework:

- IA DevExpress 26.1 (Prompt-to-Report, expresiones; modificación REPX según capacidad web).
- Schema semántico Consulta detallada.
- Matriz **go/no-go** → veredicto **A | B | C** (SPEC-015 §5).

Referencias: [AI for Reporting](https://docs.devexpress.com/XtraReports/405211/ai-for-devexpress-reporting), [Prompt-to-Report web](https://docs.devexpress.com/XtraReports/405485/ai-powered-functionality/web-reporting/prompt-to-report-in-web-report-designer).

### Oleada 2 — Producto (condicional)

| Veredicto | Partes implementa |
|-----------|-------------------|
| **A** | Schema + smoke; operación según runbook Framework |
| **B** | `SmartCapturePanel` bajo DX + turno host (excepción diseñador documental) |
| **C** | UI SC + puente a API Prompt-to-Report sidecar |

---

## Repos y trazabilidad

| Repo | Artefactos |
|------|------------|
| **Partes** | SPEC-015, HU-015, TR-015/016, smoke E2E diseñador |
| **Framework** | update-4, sidecar, futura HU/TR GEN-15 ia-diseno |

---

## Próximo paso SDD

- **Parte D** — cierre: batería §4.4 manual con sidecar + LLM → firmar veredicto en [D-VERIFICACION-TR-015](../../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-2026-10-30.md).
