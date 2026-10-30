# Plan de implementación — TR-015 (piloto IA diseño informes)

> Parte **D1** (2026-10-30). Implementación en **Parte D** (T1–T10). SoT TR: [TR-015-piloto-dx-reporting-ia.md](../TR-015-piloto-dx-reporting-ia.md) · C1 §9.

## Alcance entendido

**Oleada 1 — piloto** (no producto oleada 2 / TR-016):

- Sidecar **Framework** (`services/dx-reporting`): schema JSON semántico, catálogo sin hardcode Partes, NuGet **AI Reporting 26.1**, `Program.cs` + env LLM.
- **Partes:** despliegue/ops sidecar + credencial en **env del servicio .NET**; batería manual SPEC §4.4; doc **F1** + matriz A/B/C; regresión diseñador; párrafo manual.
- **Sin** rutas Laravel nuevas, **sin** Smart Capture, **sin** unificar BYOK GEN-16 con sidecar (Must piloto).

Repos: **PaqSuite-IA-FRAMEWORK** (T1–T5) + **PaqSuite-IA-Partes-Atencion** (T6–T10).

## Fuentes leídas

- SPEC-015, HU-015, TR-015 (C1 apto con obs.)
- Framework update-4 IA diseño; runbook sidecar
- `ProcessFieldSchemaCatalog.cs`, `partes.consultaDetallada.principal.json` (legacy sin labels)
- `PartesConsultaDetalladaEmissionPort.php` / TR-011 columnas §1
- `ReportDesignerHostPage.tsx`, `DxReportDesignerApp.tsx` (`developmentMode` en widget)
- FE pin: `devexpress-reporting*` **26.1.4** (`frontend/package.json`)

## Impacto esperado

### Framework — sidecar (`PaqSuite.DxReporting`)

| Acción | Detalle |
|--------|---------|
| **T1** | Extender parseo JSON: objetos columna con `label`, `description`, `synonyms[]`; exponer metadata al `DesignerModelFactory` / Field List si la API DX lo consume vía schema enriquecido (mínimo: persistir en catálogo interno para futuro `SetPromptAugmentation` / data source metadata). |
| **T2** | Crear **`data/schemas/partes.informes.consultaDetallada.json`** (14 columnas + labels ES + synonyms Should). **C1-15-01:** duplicar mismo contenido en **`partes.consultaDetallada.principal.json`** (reemplaza archivo actual) y documentar regla: variantes Save As = copiar schema base o symlink en deploy hasta resolver genérico por prefijo en T3. |
| **T3** | Eliminar `PartesConsultaDetalladaColumns` y ramas `consultaDetallada` / `partes.consulta` en `Resolve()`. Fallback genérico `Dataset` solo si no hay JSON. |
| **T4** | NuGet: `DevExpress.AIIntegration.AspNetCore.Reporting` + `Microsoft.Extensions.AI` (+ OpenAI o Azure según env). Registrar `IChatClient` **solo si** env completo; si falta, log warning y **no** registrar IA (C1-15-03). `AddDevExpressAI` + `AddWebReportingAIIntegration` + `AddPromptToReportConverter` (`FixLayoutErrors`, retry 5). |
| **T5** | Runbook + `services/dx-reporting/README.md`: variables `OPENAI_API_KEY` / `AZURE_OPENAI_*`, licencia DX IA, NuGet feed 26.1. |

**Archivos tocados (FW):**

- `Reporting/ProcessFieldSchemaCatalog.cs`
- `Reporting/DesignerModelFactory.cs` (si pasa metadata al JSON sample / IA)
- `Program.cs`
- `PaqSuite.DxReporting.csproj`
- `data/schemas/*.json`
- `docs/06-operacion/runbook-dx-reporting-sidecar.md`

**Tests FW (Should):** proyecto test o script `dotnet test` si existe; mínimo manual: `GetDesignerModel` con schema enriquecido.

### Partes — host

| Acción | Detalle |
|--------|---------|
| **T6** | Ops preview/local: sidecar `:5055`, `PAQSUITE_EMISSION_DX_MODE=remote`, LLM en **entorno del proceso sidecar** (no `backend/.env` para OpenAI). Documentar en PR/F1. Revisar **`developmentMode`** en `DxReportDesignerApp.tsx`: para piloto Forge, alinear con D-A1-03 sidecar (evitar mismatch FE 25.x vs 26.x); preferir quitar `developmentMode` hardcoded o gatear por `import.meta.env.DEV` en Parte D si C1 lo exige. |
| **T7** | Ejecutar pruebas §4.4 #1–#8 (español, usuario `emission.design`); capturas + REPX de muestra. |
| **T8** | `docs/04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-<fecha>.md`: escenarios, matriz SPEC §5, **Veredicto A \| B \| C**. |
| **T9** | `npm run test` Vitest `ReportDesignerHostPage.test.tsx`; E2E `partes-emisiones.spec.ts` diseñador (baseline TR-011). |
| **T10** | `docs/99-manual-usuario/Partes-Atencion.md`: IA piloto, Save obligatorio, key en sidecar (O-15-01). |

**Backend Partes:** **sin cambios** Must (RN-TR-15-02). Opcional script de validación: columnas JSON ⊆ puerto (dev-only).

**Sin:** `POST /api/v1/emissions/design/asistente/*`, `SmartCapturePanel` en diseñador, TR-016.

### Validación schema vs puerto (referencia)

Columnas Must (TR-011):  
`fecha`, `clienteCode`, `clienteNombre`, `usuarioCode`, `usuarioNombre`, `tipoTareaCode`, `tipoTareaDescripcion`, `duracionMinutos`, `sinCargo`, `presencial`, `cerrado`, `observacion`, `erpCliente`, `erpArticulo`.

## Orden de trabajo (Parte D)

| Fase | Tareas | Repo | Bloqueo |
|------|--------|------|---------|
| **1** | T1 → T3 | FW | Schema estable antes de IA |
| **2** | T2 | FW | Contenido ES (revisión producto Partes) |
| **3** | T4 → T5 | FW | NuGet + runbook |
| **4** | T6 | Partes | Sidecar desplegado con build FW |
| **5** | T9 | Partes | Tras T4 (smoke diseñador) |
| **6** | T7 → T8 | Partes | Tras T2+T6 |
| **7** | T10 | Partes | Tras T8 |

Paralelo permitido: T5 con T4; T9 con T7 una vez T4 en lab.

## Riesgos (D1)

| Riesgo | Mitigación |
|--------|------------|
| NuGet IA no restaura sin feed DX | Misma máquina con Unified Installer 26.1; runbook §1 |
| Wizard IA no visible | CA-07 checklist licencia; F1 matriz go/no-go |
| `reportUrl` Save As distinto de principal | T2 duplica JSON o T3 prefijo `partes.consultaDetallada.*` |
| Key LLM solo en Laravel | Prohibido Must; T6 doc Forge sidecar |
| PR FW sin merge antes de piloto Partes | Orden fase 1–3; tarball/tag interino documentado |
| Prompts ES débiles | O-TR-15-01 `SetPromptAugmentation` en T4 si F1 #1–#5 flojos |

## Tests a ejecutar (post-D)

```powershell
# Framework (si se añade test project)
cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\src\PaqSuite.DxReporting
dotnet build

# Partes backend — regresión emisiones
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php artisan test --filter=ApiV1EmissionsPartesTest

# Partes FE
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\frontend
npm run test -- --run src/features/partes/informes/ReportDesignerHostPage.test.tsx
npx playwright test tests/e2e/partes-emisiones.spec.ts
```

Manual Must: batería §4.4 completa (T7).

## Decisiones D1 (absorber en D)

1. **Fuente schema:** commit en repo **Framework**; Partes no mantiene copia divergente en git (deploy = artefacto del release sidecar).
2. **Alias JSON:** `partes.informes.consultaDetallada.json` + **`partes.consultaDetallada.principal.json`** mismo contenido en T2 (C1-15-01).
3. **LLM:** piloto con **OpenAI** o **Azure** — elegir uno en T4; documentar env en runbook; no commitear valores.
4. **IA off sin env:** sidecar arranca; diseñador clásico OK; wizard IA no (log).
5. **Laravel / envelope:** cero endpoints nuevos oleada 1.
6. **F1:** veredicto A/B/C obligatorio antes de cualquier TR-016.
7. **Framework TR GEN:** opcional paralelo (O-TR-15-03); no bloquea Partes D.

## Dudas / bloqueos

Ninguno bloqueante para **Parte D**.

## Confirmación de alcance

- Sin creep oleada 2 / SC: **Sí**
- Alineado SPEC/HU/TR/C1: **Sí**
- Cross-repo explícito: **Sí**

---

**Estado D1:** listo. **Parte D** autorizada por pedido de ciclo SDD (implementar T1–T10).

**Plan Jira:** PIAPARTES-55 · Epic PIAPARTES-53 → Implementando (D1).
