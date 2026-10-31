# SPEC-015 — Revisión de ambigüedad (Parte A1)

| Campo | Valor |
|-------|-------|
| SPEC | [SPEC-015-ia-diseno-informes.md](./SPEC-015-ia-diseno-informes.md) |
| Fecha | 2026-10-30 (ART) |
| **Veredicto** | **Apto con observaciones** |
| Jira | PIAPARTES-53 |

## Criterio

El SPEC puede bajar a **HU-015 / TR-015** (oleada piloto) sin reabrir alcance GEN-15 ni Emitir (SPEC-011). Quedan observaciones operativas (BYOK usuario vs sidecar) que TR-015 debe verificar, no bloquean B/C.

---

## Temas §8 — resolución

### 1. Modificación conversacional web vs WinForms

| Decisión | **D-A1-01** |
|----------|-------------|
| Hecho | DevExpress documenta Prompt-to-Report en **Web Report Designer** vía Wizard / integración `AddPromptToReportConverter` (creación con o sin data source; metadata de campos en prompt). API `GeneratePromptToReportAsync` con `report` existente está orientada a **host de consola**, no garantizada en web sobre REPX ya abierto. |
| Cierre | **No** exigir paridad WinForms en oleada 1. Prueba SPEC §4.4 **#6** es **evidencia de matriz F1**, no CA Must de paridad. TR-015 debe intentar: (a) Wizard con data source del proceso; (b) modificación sobre layout ya cargado si la UI web lo permite. |
| Si #6 insuficiente | Matriz §5 favorece **B** o **C**; no invalida el SPEC. |

### 2. BYOK usuario (GEN-16) vs API key sidecar (DX)

| Decisión | **D-A1-02** |
|----------|-------------|
| Hecho | DevExpress IA Reporting registra `IChatClient` **singleton en startup** del sidecar (.NET), típicamente `AZURE_OPENAI_*` / `OPENAI_API_KEY` en entorno del **proceso sidecar** ([doc DX BYOK](https://docs.devexpress.com/XtraReports/405211/ai-for-devexpress-reporting)). GEN-16 PaqSuite persiste credencial **por usuario** en Laravel para chat / Smart Capture. |
| Cierre piloto (camino A) | Oleada 1 **Must:** credencial LLM = **despliegue del sidecar** (secret Forge / env del servicio `dx-reporting`), **no** lectura automática de `LlmPreferencesPanel` por request. |
| Observación | Unificar “una sola key por usuario” entre chat y diseñador DX **no** está resuelto en GEN; post-F1 camino A puede quedar ops-by-tenant o abrir TR Framework de puente dinámico. |
| Camino B/C | GEN-16 **sí** aplica al turno host (SC); sidecar Prompt-to-Report en **C** puede usar key de servicio o puente diseñado en TR-016. |

### 3. Versión FE DX vs sidecar 26.1

| Decisión | **D-A1-03** |
|----------|-------------|
| Cierre | **Preview/prod:** npm `devextreme-reporting-react` **26.1** alineado al sidecar **26.1**. |
| Lab | `UseDevelopmentMode` + `CheckClientLibraryVersions = false` **solo** `Development` del sidecar (runbook existente); **prohibido** depender de ello en Forge/Vercel prod. |

### 4. Conflicto GEN-03 “no informes”

| Decisión | **D-A1-04** |
|----------|-------------|
| Cierre | Excepción normativa **solo** diseñador documental: ruta con `emission.design`, proceso emisible confirmado, persistencia Save GEN-15. **No** aplica a Consulta detallada ni grillas. SoT: Framework update-4 §1; regla 19 + SPEC-015 oleada 2 B/C. |

### 5. TR-016 antes de F1

| Decisión | **D-A1-05** |
|----------|-------------|
| Cierre | **Prohibido** implementación productiva SC completa (oleada 2) antes de veredicto F1 y TR-016 C1. Sin cambio. |

### 6. Licencia DevExpress + IA (nuevo §8)

| Decisión | **D-A1-06** |
|----------|-------------|
| Hecho | Además de `VITE_DEVEXPRESS_LICENSE_KEY` (UI), packs `DevExpress.AIIntegration.AspNetCore.Reporting` requieren suscripción DX vigente y NuGet AI listados en doc 405485. |
| Cierre | TR-015 **Must** checklist: restore NuGet + wizard IA visible en lab; si falla licencia → matriz go/no-go §5. |

### 7. Schema semántico (nuevo §8)

| Decisión | **D-A1-07** |
|----------|-------------|
| Cierre | Piloto Must incluye `label` en **todas** las columnas Consulta detallada; `description` / `synonyms` **Should** en campos ambiguos (`usuarioNombre`, `duracionMinutos`, `sinCargo`). Formato: update-4 §4. Host Partes publica JSON; sidecar **no** añade ramas C# permanentes por producto (retirar hardcode piloto en TR Framework). |

---

## Observaciones (no bloquean HU)

1. **O-15-01:** BYOK unificado usuario (GEN-16) ↔ diseñador DX queda **fuera** del Must del piloto camino A; documentar en manual ops si la key es corporativa en sidecar.
2. **O-15-02:** OpenSpec Framework (delta SPEC-001-15 o HU GEN-15 ia-diseno) **pendiente** en repo Framework; Partes no sustituye ese ciclo.

---

## Siguiente SDD

- **Parte D1** → plan `d1/D1-TR-015-piloto-dx-reporting-ia.md` · **Parte D** implementación.
