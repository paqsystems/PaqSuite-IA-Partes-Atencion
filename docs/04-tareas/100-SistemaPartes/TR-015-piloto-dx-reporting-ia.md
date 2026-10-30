# TR-015 – Piloto IA en diseño de informes (sidecar DX + schema Consulta detallada)

Jira: PIAPARTES-55


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-015-ia-diseno-informes](../../03-historias-usuario/100-SistemaPartes/HU-015-ia-diseno-informes.md) |
| **SPEC relacionada** | [SPEC-015-ia-diseno-informes](../../05-open-spec/100-SistemaPartes/SPEC-015-ia-diseno-informes.md) |
| **Norma GEN** | Framework [`15-reportes-emisiones-update-4-ia-diseno.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-update-4-ia-diseno.md) |
| **Épica** | 100 — Sistema Partes (PIAPARTES-53) |
| **Prioridad** | MUST-HAVE (oleada piloto) |
| **Roles** | Diseñador = `emission.design` + desktop + menú `/emisiones/disenador` |
| **Dependencias** | [TR-011](./TR-011-reportes-emisiones.md) (diseñador + sidecar cableado); Framework `services/dx-reporting` 26.1; credencial LLM **despliegue sidecar** (D-A1-02) |
| **Clasificación** | HU COMPLEJA (piloto cross-repo) |
| **Estado** | **Especificado** (Parte C1 — 2026-10-30) |
| **Revisión C1** | **Apto con observaciones** — §9 |

**Origen:** [HU-015](../../03-historias-usuario/100-SistemaPartes/HU-015-ia-diseno-informes.md)  
**Plan D1:** [D1-TR-015-piloto-dx-reporting-ia.md](./d1/D1-TR-015-piloto-dx-reporting-ia.md)  
**Producto Partes:** [19-ia-diseno-informes.md](../../02-producto/Sistema-Partes-IA/19-ia-diseno-informes.md)

**Referencias técnicas:**

- Sidecar: `PaqSuite-IA-FRAMEWORK/services/dx-reporting/`
- Runbook: `PaqSuite-IA-FRAMEWORK/docs/06-operacion/runbook-dx-reporting-sidecar.md`
- Host FE: `frontend/src/features/partes/informes/ReportDesignerHostPage.tsx`, `dxReportingConfig.ts`, `DxReportDesignerPanel.tsx`
- Puerto dataset (alineación schema): `backend/app/Services/Emissions/PartesConsultaDetalladaEmissionPort.php`
- DevExpress: [Prompt-to-Report Web](https://docs.devexpress.com/XtraReports/405485/ai-powered-functionality/web-reporting/prompt-to-report-in-web-report-designer)

**Plantilla regla 19:** Piloto IA DX sidecar GEN-15 + schema semántico; no reimplementar diseñador; no nuevas rutas envelope 470x oleada 1.

---

## 1) HU refinada (resumen)

### In scope (oleada 1)

- Schema JSON semántico **`partes.informes.consultaDetallada`** (labels Must; synonyms Should — D-A1-07).
- Sidecar: NuGet IA + `Program.cs` (`AddDevExpressAI`, `AddWebReportingAIIntegration`, `AddPromptToReportConverter`); env LLM sidecar.
- Refactor `ProcessFieldSchemaCatalog`: leer `label`/`description`/`synonyms`; **eliminar** fallback C# hardcode `consultaDetallada` (objetivo update-4).
- Despliegue artefacto schema en sidecar (path `data/schemas/`).
- Batería manual §4.4 SPEC (#1–#8) + documento matriz F1.
- Regresión: `ReportDesignerHostPage` + Save/Save As sin cambios de contrato Laravel.
- Runbook ops: variables sidecar, licencia DX IA, alineación FE 26.1.

### Out of scope

- Nuevas rutas `/api/v1/emissions/*` ni turno Smart Capture (oleada 2 / TR-016 post-F1).
- Emitir, Consulta detallada, mobile, chat REPX.
- Puente BYOK GEN-16 → sidecar por usuario (O-15-01; post-piloto).
- Productizar camino A/B/C (solo **evidencia** F1).

### Decisiones TR (cierran HU / A1)

| Tema | Decisión |
|------|----------|
| `processCode` schema | Archivo canónico **`partes.informes.consultaDetallada.json`**. **C1-15-01:** el sidecar debe resolver también `reportUrl` del reporte principal seed (`partes.consultaDetallada.principal` y variantes Save As) — vía segundo JSON, symlink de despliegue, o ampliación `Resolve()` documentada en T2/T3 (no depender del fallback C#). |
| Columnas | Mismo set que TR-011 §1 / puerto emisión (14 campos) |
| Credencial LLM piloto | **Forge/env del proceso sidecar** (`OPENAI_API_KEY` o `AZURE_OPENAI_*` + deployment); **no** leer preferencias usuario Laravel en Must |
| FE reporting | `devextreme-reporting-react` **26.1.x** alineado sidecar preview/prod; `UseDevelopmentMode` solo Development sidecar |
| Modificación REPX (#6) | Probar Wizard/prompt sobre informe abierto; documentar límite web (D-A1-01) |
| Evidencia F1 | `docs/04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-2026-10-30.md` (o fecha cierre F1) |
| Matriz veredicto | Tabla SPEC §5 en mismo doc F1; fila **Veredicto = A \| B \| C** obligatoria |

---

## 2) Criterios de aceptación (AC)

Mapeo HU CA-01…09 → verificación técnica:

| AC | Verificación |
|----|----------------|
| CA-01 | Archivo schema en sidecar + Field List / metadata en diseñador; labels visibles en JSON |
| CA-02 | E2E manual o test existente `ReportDesignerHostPage`: lista proceso → confirm → `GetDesignerModel` 200; Save sin 470x nuevo |
| CA-03 | Informe F1: escenarios #1–#5 con capturas o REPX exportado |
| CA-04 | Informe F1: escenario #6 + párrafo conclusión modificación web |
| CA-05 | Informe F1: escenario #7 |
| CA-06 | Informe F1: escenario #8 (Save As + catálogo) |
| CA-07 | Sidecar arranca con paquetes IA; Wizard muestra opción AI Prompt-to-Report en lab |
| CA-08 | Doc F1 completo con matriz y veredicto |
| CA-09 | Feature/policy existente: sin `emission.design` → 4709; native deny `/emisiones` (sin cambio TR-011) |

---

## 3) Reglas de implementación

| ID | Regla |
|----|--------|
| RN-TR-15-01 | **Prohibido** persistir REPX desde script IA fuera Save DX + callbacks GEN existentes. |
| RN-TR-15-02 | Oleada 1 **sin** endpoints Laravel nuevos para IA. |
| RN-TR-15-03 | Schema `name` de columna = camelCase puerto; añadir `label` (string ES) Must en las 14 columnas. |
| RN-TR-15-04 | Sidecar: registrar IA solo si env LLM presente; log claro si falta (no fallar silencioso el diseñador base). |
| RN-TR-15-05 | No commitear secrets; documentar nombres env en runbook Framework (ampliar § sidecar IA). |
| RN-TR-15-06 | Trabajo Framework en repo **PaqSuite-IA-FRAMEWORK**; Partes aporta contenido JSON + verificación host + F1. |
| RN-TR-15-07 | Pruebas #1–#7 en **español**; operador con `emission.design`. |

---

## 4) Contratos y artefactos

### 4.1 Schema JSON (host → sidecar)

Ubicación sidecar: `src/PaqSuite.DxReporting/data/schemas/partes.informes.consultaDetallada.json`

Ejemplo mínimo (extender con labels/synonyms reales):

```json
{
  "name": "Consulta detallada",
  "columns": [
    {
      "name": "usuarioNombre",
      "type": "string",
      "label": "Técnico / asistente",
      "description": "Nombre del asistente que registró la tarea.",
      "synonyms": ["técnico", "operador", "asistente"]
    }
  ]
}
```

**Must:** las 14 columnas de TR-011 §1 con `label`. **Should:** synonyms en `usuarioNombre`, `duracionMinutos`, `sinCargo`, `clienteNombre`, `fecha`.

### 4.2 Sidecar `Program.cs` (Framework)

- Paquetes: `DevExpress.AIIntegration.AspNetCore.Reporting`, `Microsoft.Extensions.AI`, proveedor OpenAI o Azure (doc DX 405485).
- Registro patrón:

```csharp
// Pseudocódigo — ver doc DevExpress 26.1
builder.Services.AddSingleton<IChatClient>(/* from env */);
builder.Services.AddDevExpressAI(c => c.AddWebReportingAIIntegration(a =>
    a.AddPromptToReportConverter(o => o.FixLayoutErrors().SetRetryAttemptCount(5))));
```

- Mantener `UseDevelopmentMode` solo `IsDevelopment()`.

### 4.3 Host Partes (sin lógica IA en Laravel)

- Verificar pin npm reporting 26.1 en `frontend/package.json` cuando se cierre piloto prod.
- `VITE_DX_REPORTING_*` sin cambio de contrato respecto TR-011.
- Opcional Vitest: schema file committed en Framework + test catálogo parsea synonyms (Framework unit).

---

## 5) Plan de tareas

| ID | Repo | Tipo | Descripción | DoD |
|----|------|------|-------------|-----|
| **T1** | FW | Sidecar | Ampliar `ProcessFieldSchemaCatalog.TryReadFile` para `label`, `description`, `synonyms` en JSON | Unit/manual: schema enriquecido leído |
| **T2** | FW | Sidecar | Redactar **`partes.informes.consultaDetallada.json`** + alias para `reportUrl` principal/variantes (C1-15-01) | 14 labels; synonyms Should |
| **T3** | FW | Sidecar | Quitar fallback C# `PartesConsultaDetalladaColumns` / strings `consultaDetallada` | Solo JSON + generic placeholder |
| **T4** | FW | Sidecar | Integrar NuGet IA + `Program.cs` + env sample en README/runbook | Wizard IA visible en lab con key |
| **T5** | FW | Docs | Ampliar `runbook-dx-reporting-sidecar.md` § IA (env, licencia, NuGet) | Ops puede repetir piloto |
| **T6** | Partes | Ops | Preview/Forge: sidecar remote + env LLM sidecar (no `.env` Laravel para DX key) | CA-07 en entorno acordado |
| **T7** | Partes | QA | Ejecutar pruebas §4.4 #1–#8; capturas | CA-03…06 |
| **T8** | Partes | Docs | Redactar `D-VERIFICACION-TR-015-*.md` + matriz A/B/C | CA-08 |
| **T9** | Partes | Tests | Regresión: `ReportDesignerHostPage.test.tsx` + E2E humo diseñador (TR-011 baseline) | CA-02 |
| **T10** | Partes | Manual | Párrafo en manual usuario: IA piloto, Save obligatorio, key sidecar | CA-09 doc |

**Orden sugerido:** T1 → T2 → T3 → T4 → T5 (Framework) → T6 → T7 → T8; T9 en paralelo tras T4; T10 tras T8.

---

## 6) Estrategia de tests

| Capa | Alcance oleada 1 |
|------|------------------|
| Framework unit | Parse JSON columnas enriquecidas; opcional smoke `GetDesignerModel` con schema |
| Partes Vitest | Sin cambio Must salvo regresión diseñador (T9) |
| Partes E2E | Reutilizar `partes-emisiones.spec.ts` diseñador gated; no exigir automatizar prompts IA |
| Manual Must | Batería §4.4 + checklist licencia D-A1-06 |

---

## 7) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Licencia DX no incluye IA | CA-07 / matriz go/no-go |
| Web no modifica REPX (#6) | Documentar; veredicto B/C |
| Key solo en sidecar vs expectativa BYOK usuario | Manual ops O-15-01 |
| Drift schema vs puerto | Diff automatizable: columnas JSON vs lista puerto PHP |
| Cross-repo deploy schema | T2/T6: documentar copia o submodule path en runbook |

---

## 8) Checklist DoD (pre-F1)

- [ ] T1–T5 Framework mergeados o tarball acordado
- [ ] Schema desplegado en sidecar del entorno piloto
- [ ] IA wizard operativo en lab con credencial sidecar
- [ ] F1 doc con #1–#8 y veredicto A/B/C
- [ ] Regresión Save / diseñador Partes OK
- [ ] Sin rutas Laravel nuevas IA
- [ ] TR-016 **no** iniciada

---

## 9) Revisión C1 (2026-10-30)

**Estado:** **Apto con observaciones**  
**Puede pasar a D1/D:** Sí (oleada piloto; leer observaciones)

### Cobertura

| Chequeo | Resultado |
|---------|-----------|
| T1–T10 vs CA-01…09 | OK — F1 manual cubre CA-03…08; T9 CA-02; T4+T6 CA-07 |
| Framework vs Partes (update-4) | OK — RN-TR-15-06; sidecar + JSON en FW; F1/ops en Partes |
| Sin creep oleada 2 / SC | OK — §1 out of scope + DoD TR-016 bloqueada |
| A1 D-A1-02 BYOK sidecar | OK — §1 credencial env sidecar |
| Sin rutas Laravel IA | OK — RN-TR-15-02 |

### Críticas cerradas en esta TR

| ID | Tema | Cierre |
|----|------|--------|
| **C1-15-01** | `reportUrl` vs `processCode` | Hoy existe `partes.consultaDetallada.principal.json` sin metadata semántica. T2/T3 deben garantizar schema enriquecido para **processCode GEN** y para **reportUrl** usado por el diseñador (principal + Save As). Eliminar fallback C# al hacerlo. |
| **C1-15-02** | Contenido labels ES | T2: revisión negocio Partes (producto) antes de merge Framework; una sola fuente JSON en repo FW (no fork host). |
| **C1-15-03** | IA deshabilitada sin key | RN-TR-15-04: diseñador base Must seguir operando; wizard IA ausente o deshabilitado con log explícito — no 500 en `GetDesignerModel`. |

### Observaciones (D1)

| ID | Tema | Acción D1 |
|----|------|-----------|
| **O-TR-15-01** | Prompt ES / `SetPromptAugmentation` | Should en T4 si calidad piloto #1–#5 insuficiente; no bloquea D. |
| **O-TR-15-02** | Pin FE `devextreme-reporting-react` 26.1 | Documentar en T6/T8 si preview Forge aún en 25.x lab; prod Must 26.1 al productizar A. |
| **O-TR-15-03** | TR Framework hermana | Considerar task FW `TR-GEN-15-ia-diseno` paralela (O-15-02 SPEC); Partes no sustituye. |
| **O-TR-15-04** | Test unit catálogo | Should T1: test .NET que parsea `synonyms`; no Must si manual F1 alcanza. |

### Contradicciones TR ↔ HU ↔ SPEC

Ninguna bloqueante. HU CA-09 mezcla permisos + manual: T10 cubre manual; permisos = regresión TR-011 (sin duplicar Feature).

### Preguntas humanas

Ninguna bloqueante para D1.

### Recomendaciones D1

- Plan explícito en `docs/04-tareas/100-SistemaPartes/d1/D1-TR-015-piloto-dx-reporting-ia.md` (Parte D1).
- Orden estricto T1→T3 antes de T7 (schema estable para pruebas).
- F1: plantilla matriz copiada de SPEC-015 §5 + fila veredicto firmada.

---

## 10) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-30 | Parte C: TR-015 (Jira PIAPARTES-55). |
| 2026-10-30 | Parte C1: **Apto con observaciones**; cierres C1-15-01…03. |
| 2026-10-30 | Parte D1: [D1-TR-015-piloto-dx-reporting-ia.md](./d1/D1-TR-015-piloto-dx-reporting-ia.md). |
| 2026-10-30 | Parte D (en curso): FW T1–T5 sidecar (schema semántico, IA condicional, runbook); Partes T6 ops doc + `developmentMode` solo DEV; Vitest diseñador OK. [D-VERIFICACION-TR-015](../updates/100-SistemaPartes/D-VERIFICACION-TR-015-2026-10-30.md) + manual SPEC-010 § IA piloto. **Pendiente:** batería §4.4 manual + veredicto A/B/C + E2E lab. |
