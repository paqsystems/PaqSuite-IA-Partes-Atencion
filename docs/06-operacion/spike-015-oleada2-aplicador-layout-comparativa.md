# Spike — aplicador de acciones layout (oleada 2 B)

| Campo | Valor |
|-------|--------|
| Objetivo | Comparar **A** mutación cliente (JS/designer web) vs **B** patch REPX en sidecar |
| Duración orientativa | 1–2 días |
| SPEC | [SPEC-015-update oleada 2](../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-2-b-update.md) |
| Evidencia | Completar tabla §4 al final de este archivo (o anexo en PR spike) |

## 0) Preparación común (ambos caminos)

1. **Backend Partes** en `:8000` (o el que use tu `.env.local`), tenant `DEV`.
2. **Sidecar** Framework en `:5055`:
   - `cd PaqSuite-IA-FRAMEWORK/services/dx-reporting/src/PaqSuite.DxReporting`
   - `dotnet run` (si el puerto está ocupado: `scripts/stop-sidecar-port-5055.ps1` y repetir).
3. **Health:** `GET http://127.0.0.1:5055/health` → 200.
4. **Frontend:** `cd frontend` → `npm run dev` → URL canónica con proxy `/DXXRD` (`.env.local`: `VITE_DX_REPORTING_HOST=http://127.0.0.1:5055`).
5. **Login** usuario con permiso **`emission.design`**.
6. **Diseñador:** `http://127.0.0.1:3000/emisiones/disenador?cliente=DEV` → proceso **Consulta detallada** → abrir o crear variante (Save As si hace falta).
7. **Piloto A apagado** (recomendado para no ruido): comentar temporalmente `enableReportDesignerAiServices()` en `DxReportDesignerApp.tsx` o sidecar sin `OPENAI_API_KEY`.
8. **Field List:** debe mostrar campos del schema (p. ej. `clienteNombre`, `duracionMinutos`) — si ves solo `id`/`label`, el spike de REPX igual sirve pero **A** falla schema; anotar en evidencia.

### Tres acciones canónicas (misma batería en A y B)

Usar **`name` técnico** del schema FW `partes.informes.consultaDetallada.json`, no etiquetas ES:

| ID acción | Efecto esperado en lienzo | Criterio pass |
|-----------|---------------------------|---------------|
| **ACT-01** | Título de informe / `DisplayName` visible en propiedades | Usuario ve título nuevo sin reabrir sesión |
| **ACT-02** | Agrupación por **`clienteNombre`** | Aparece group header/footer o equivalente DX |
| **ACT-03** | Subtotal **suma** de **`duracionMinutos`** en pie de grupo | Celda con expresión tipo `sumSum([duracionMinutos])` o equivalente; **Validate Bindings** sin XRE023 por usar `[clienteNombre]`/`[duracionMinutos]` |

Capturar: screenshot lienzo + **Validate Bindings** + fragmento REPX (sidecar `data/layouts/<reportUrl>.repx`).

---

## 1) Camino A — Cliente (host / API web del Report Designer)

### Hipótesis

El widget `devexpress-reporting-react` expone (o permite obtener) una instancia del diseñador web para ejecutar comandos de layout sin round-trip REPX.

### Paso A.1 — Localizar la instancia en runtime

1. Con el diseñador cargado, abrir **DevTools → Consola**.
2. Buscar referencias (una suele existir según versión 26.1):
   - Expansión del árbol Knockout en el panel del diseñador (Elements → nodo con `dxrd-*` / `dx-designer`).
   - Probar en consola (ajustar si falla):  
     `document.querySelector('.dxrd-surface')` y contexto KO asociado.
3. Documentar **qué objeto** obtuviste (screenshot + 2–3 líneas de notas). Si **no** hay API estable en 30 min → marcar **riesgo alto** camino A.

**Referencia DX:** [Report Designer (Web)](https://docs.devexpress.com/XtraReports/400248/web-reporting/report-designer) y búsqueda en doc «Customize Menu Actions» / client-side customization.

### Paso A.2 — Prototipo mínimo en código (branch spike, no merge)

1. En `DxReportDesignerApp.tsx`, agregar callback temporal `CustomizeElements` o extender `BeforeRender`:
   - Exponer `window.__partesDesignerSpike = { ping: () => 'ok' }` desde callback cuando el modelo esté listo (patrón a descubrir en A.1).
2. Botón dev bajo el diseñador (`data-testid="spikeApplyAct01"`) que dispare secuencia **ACT-01 → ACT-02 → ACT-03** vía la instancia encontrada.
3. **No** persistir aún: solo mutación en memoria del diseñador.

### Paso A.3 — Prueba manual por acción

| Paso | Acción |
|------|--------|
| 1 | Informe simple (tabla detail o vacío) abierto |
| 2 | Click botón spike / consola → **ACT-01** |
| 3 | ¿Cambio visible < 5 s? ¿Undo del diseñador sigue funcionando? |
| 4 | Repetir **ACT-02**, **ACT-03** |
| 5 | **Validate Bindings** |
| 6 | **Guardar** (Save) → REPX en sidecar + callback Laravel OK (regresión TR-011) |

### Paso A.4 — Métricas a anotar

- Tiempo por acción (percepción + consola Performance si aplica).
- ¿API documentada vs hacks KO?
- ¿Acciones rollback/undo?
- ¿Funciona con informe **ya abierto** (prueba 6 SPEC) sin recargar página?

---

## 2) Camino B — Sidecar (patch REPX / API .NET XtraReports)

### Hipótesis

Cargar REPX, mutar con `DevExpress.XtraReports.UI` en servidor, guardar con `FileReportStorage`, **refrescar** diseñador en el cliente.

### Paso B.1 — REPX de partida

1. Guardar informe de prueba `spike-layout-b-1` desde el diseñador (Save As).
2. Verificar archivo:  
   `PaqSuite-IA-FRAMEWORK/services/dx-reporting/src/PaqSuite.DxReporting/data/layouts/spike-layout-b-1.repx`  
   (el nombre = `reportUrl` exacto del catálogo).

### Paso B.2 — Spike backend (branch Framework o script local)

Opción rápida **sin** endpoint nuevo: consola .NET one-off o test `xUnit` en `dx-reporting` que:

1. `XtraReport report = new XtraReport(); report.LoadLayoutFromXml(path);`
2. **ACT-01:** `report.DisplayName = "Spike B titulo";`
3. **ACT-02:** agregar `GroupHeaderBand` / `GroupField` sobre `[clienteNombre]` (API server-side).
4. **ACT-03:** en `GroupFooterBand`, label con `summaryFunc = Sum`, `dataMember` / binding `duracionMinutos`.
5. `report.SaveLayoutToXml(outPath)`.

Ejecutar script → abrir **mismo** `reportUrl` en el diseñador (F5 o cerrar/abrir variante).

### Paso B.3 — Prototipo endpoint (si B.2 OK)

1. `POST /api/v1/spike/layout-actions` (solo DEV) body:

```json
{
  "reportUrl": "spike-layout-b-1",
  "processCode": "partes.informes.consultaDetallada",
  "actions": [
    { "type": "setTitle", "value": "Spike B titulo" },
    { "type": "groupBy", "field": "clienteNombre" },
    { "type": "groupSum", "field": "duracionMinutos" }
  ]
}
```

2. Implementación usa mismo código que B.2 + escribe vía `FileReportStorage`.
3. Front: tras 200, **forzar reload** del diseñador (`key={reportUrl + revision}` en `DxReportDesignerApp` o acción DX «Reopen» si existe).

### Paso B.4 — Prueba manual

Misma tabla que A.3, pero disparo desde Postman/curl al endpoint (o script), luego refresh diseñador.

### Paso B.5 — Métricas a anotar

- Latencia POST + refresh total.
- ¿Pérdida de selección/undo del usuario?
- ¿Conflictos si el usuario tenía cambios sin guardar? (debe rechazar o avisar — anotar comportamiento actual).
- Mantenibilidad: líneas C# por acción vs frágilidad XML manual.

---

## 3) Matriz de decisión (completar post spike)

| Criterio | Peso | Camino A (JS) | Camino B (REPX) |
|----------|------|---------------|-----------------|
| ACT-01..03 pass en lienzo | Alto | | |
| Validate Bindings OK (`name` schema) | Alto | | |
| Sin recarga full page (ideal) | Medio | | |
| API estable / documentada | Alto | | |
| Esfuerzo TR oleada 2 | Medio | | |
| Riesgo regresión Save/TR-011 | Medio | | |

**Veredicto spike:** `A` \| `B` \| **Híbrido** (definir qué acciones en cada capa).

---

## 4) Registro de ejecución (rellenar)

| Fecha | Ejecutor | Camino | ACT-01 | ACT-02 | ACT-03 | Notas |
|-------|----------|--------|--------|--------|--------|-------|
| | | A | | | | |
| 2026-11-10 | Lab ST | B | — | **OK** | — | `spike-layout-b-1` · script `PaqSuite-IA-FRAMEWORK/services/dx-reporting/scripts/spike/Apply-Act02-GroupBy.ps1` · Group Header `clienteNombre` + recarga variante en diseñador |

---

## 4.1) Deuda Field List (`Dataset` / `componentX`) — fix sidecar 2026-11-10

| ID | Estado | Descripción |
|----|--------|-------------|
| **D1–D3** | Aplicado en Framework `DesignerModelFactory` | `GetDesignerModel` resuelve schema por **`processCode`** (no solo `reportUrl`); reutiliza `JsonDataSource` por **nombre** en `ComponentStorage`; si el activo es stub **`Dataset`**, repunta al origen canónico del proceso (p. ej. **Consulta detallada**). |
| **D4** | Pendiente TR aplicador | Limpiar `componentN` / stub al **guardar** layout (no en este cambio). |
| **Lab** | Manual una vez | Borrar `Dataset` + `component*` en Field List → **Save** en variantes ya sucias; reabrir con sidecar actualizado y validar que no se dupliquen stubs al refrescar. |

---

## 5) Siguiente SDD

- Veredicto spike → anexo en TR oleada 2 (Parte C).
- Si **B gana** con refresh obligatorio: documentar UX («El asistente aplicó cambios; el diseñador se actualizó»).
- Si **A gana parcial**: híbrido solo para acciones no expuestas en JS.
