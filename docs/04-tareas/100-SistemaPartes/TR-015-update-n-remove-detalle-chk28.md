# TR-015-update-n – `removeDetailField` (CHK-28, oleada 12)

| Campo | Valor |
|-------|--------|
| Estado | **Finalizado** |
| Jira | [PIAPARTES-81](https://paqsystems.atlassian.net/browse/PIAPARTES-81) (Story [PIAPARTES-80](https://paqsystems.atlassian.net/browse/PIAPARTES-80)) |
| HU / SPEC | [HU-015-update-n](../../03-historias-usuario/100-SistemaPartes/HU-015-update-n-remove-detalle-chk28.md) · [SPEC oleada 12](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-12-remove-detail-chk28-update.md) |
| TR previa | [TR-015-update-m](./TR-015-update-m-imagenes-chk15-17.md) |
| D1 | [D1-TR-015-update-n](./d1/D1-TR-015-update-n-chk28.md) |

## 1) Contrato acciones (whitelist v1.3 — remove)

| Acción | Payload | Sidecar (applier) | CHK |
|--------|---------|-------------------|-----|
| `removeDetailField` | `{ "field": "observacion" }` | `LayoutDetailFieldRemoveApplier` (nuevo) | 28 |

Host Partes:

| Componente | Cambio |
|------------|--------|
| `DesignLayoutWhitelist` | permitir `removeDetailField` |
| `DesignLayoutRemoveDetailFieldHeuristics` | nuevo; prompt + sinónimos schema |
| `DesignLayoutActionPlanner` | evaluar remove detail **antes** de `DesignLayoutRemovalIntentHeuristics` |
| `DesignLayoutRemovalIntentHeuristics` | sin cambio semántico para CHK-29+ |
| i18n | claves applied / noop `ya_ausente` |
| Tests | `DesignLayoutChecklistPlannerTest` CHK-28; `DesignLayoutActionPlannerTest` remove + regresión CHK-29 |

## 2) Algoritmo applier (sidecar)

1. Resolver `field` del payload; `AssertSchemaField`.
2. Obtener `DetailBand`.
3. Recorrer controles `XRLabel` (y solo labels v1) con:
   - `Name` == `lblDetail_{sanitizedField}`, **o**
   - binding `Text` BeforePrint contiene `[field]`.
4. Eliminar cada match del band; `return` true si hubo ≥1 eliminación.
5. Si ningún match → `return` false (host mapea a noop).

**Sí** eliminar `lblPageHdr_{field}` en PageHeader cuando existe (paridad columna detalle + encabezado). No tocar GroupHeader ni otros bandas en v1.

## 3) Matriz y scripts

| Artefacto | Repo |
|-----------|------|
| Matriz CHK-28 → `mvp` | Framework `15-reportes-emisiones-ia-checklist-matriz.json` |
| Eval apply | `backend/scripts/eval-emission-ia-oleada12-chk28-apply.ps1` (nuevo) |
| Fixture | Layout lab con observación en detalle (base emisor o pre-step `addDetailField`) |

### Delta matriz CHK-28

```json
{
  "estado": "mvp",
  "plannerExpect": "mapped",
  "expectedActions": [
    { "action": "removeDetailField", "payload": { "field": "observacion" } }
  ],
  "applyAssert": { "repxNotContains": ["lblDetail_observacion"] },
  "testLayers": ["phpunit_planner", "dotnet_apply", "ps_apply"]
}
```

## 4) Tests (parte E)

| Capa | Comando | Resultado 2026-11-25 |
|------|---------|-------------------|
| Planner | `php vendor/bin/phpunit --filter DesignLayout` | OK (59) |
| Sidecar | `dotnet test --filter LayoutDetailFieldRemove` | OK (2) |
| Apply | `.\eval-emission-ia-oleada12-chk28-apply.ps1` | **PASS** apply + noop |

## 5) Smoke manual

1. Abrir diseñador consulta detallada con columna observación visible.
2. Smart Capture: **«Quitá la columna observación del detalle»**.
3. Verificar ausencia en lienzo; Guardar copia de prueba.
4. Repetir prompt → mensaje noop.
5. Probar **«Sacá la agrupación por cliente»** → sin mapeo (CHK-29).

Guía: [prompts oleada 12](../../06-operacion/prompts-nuevo-hilo-oleada-12-chk28-sdd.md).

## 6) Entregables F

| Doc | Nombre previsto |
|-----|-----------------|
| F1 | `D-VERIFICACION-TR-015-update-n-chk28-YYYY-MM-DD.md` |
| F | [F-TR-015-update-n-oleada12-chk28](./F-TR-015-update-n-oleada12-chk28.md) |
| F1 | [D-VERIFICACION 2026-11-26](./D-VERIFICACION-TR-015-update-n-chk28-2026-11-26.md) — **Aprobado** |
| L-B-02 | Actualizar whitelist v1.3 en `20-ia-diseno-informes-limitaciones-mvp-b.md` |

## 7) Dependencias

- Sidecar `dx-reporting` en `:5055` (rebuild + restart tras merge Framework).
- Sin bump obligatorio `laravel-core` / `react-core` salvo contrato apply ya estable.