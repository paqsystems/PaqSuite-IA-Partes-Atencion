# D-VERIFICACION — TR-015-update-e T1 (CHK-04)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-e](./TR-015-update-e-page-header-informes.md) § T1 |
| SPEC | [SPEC-015-update oleada 5](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) |
| D1 | [D1-TR-015-update-e-oleada5-t1-chk04](./d1/D1-TR-015-update-e-oleada5-t1-chk04.md) |
| Smoke | [smoke-oleada5-page-header-chk04.md](../../06-operacion/smoke-oleada5-page-header-chk04.md) |
| Fecha | 2026-11-20 |

---

## Verificación agente (F1)

### Resultado

**Aprobado** — T1 CHK-04 listo para commit / parte I del hito; oleada 5 T2+ pendiente.

### Evidencia revisada

| Eje | Estado |
|-----|--------|
| Alcance | Solo `addPageHeaderColumnLabels`; sin T2–T4 |
| Código FW | `LayoutPageHeaderColumnLabelsApplier`, `LayoutPageHeaderLayout`, noop `sin_columnas_detalle` + `ya_colocados` |
| Código Partes | `DesignLayoutPageHeaderHeuristics`, planner payload JSON objeto `{}`, FE apply + i18n |
| Frontend | AC-08 sin columnas; mensaje `columnLabelsAlreadyPlaced`; fix `payload[]` → `{}` |
| Manual UI | Captura SC: mensaje ya colocados en **Spike-B-titulo** (2026-11-20) |
| Trazabilidad | Matriz CHK-04 `mvp`; guía manual §CHK-04 |

### Tests (ejecutados 2026-11-20)

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=DesignLayout` | **OK** — 42 tests, 130 assertions |
| `dotnet test … --filter LayoutPageHeaderColumnLabelsTests` | **OK** — 4 tests |
| `.\eval-emission-ia-oleada5-apply.ps1` | **OK** — `PASS CHK-04` (sidecar `:5055`) |

### Criterios T1 (SPEC / HU)

| ID | Descripción | Veredicto |
|----|-------------|-----------|
| CA-B5-01 | Planner CHK-04 → `addPageHeaderColumnLabels` | **OK** (PHPUnit + checklist matriz) |
| CA-B5-02 | Apply fila 3 `lblPageHdr_*` | **OK** (.NET + apply script + lab manual) |
| AC-08 | Sin detalle → i18n; sin error HTTP | **OK** (noop `sin_columnas_detalle`) |
| — | Títulos ya presentes → aviso usuario | **OK** (noop `ya_colocados` + captura SC) |

### Hallazgos / correcciones en cierre

| Tema | Resolución |
|------|------------|
| Apply 400 con `payload: []` | Planner/FE serializan objeto `{}` |
| Mensaje genérico apply fallido | Causa: deserialization sidecar; corregido arriba |
| `eval` con spike ya etiquetado | Script acepta `noop:…:ya_colocados` si REPX cumple `repxContains` |
| Matriz `repxContains` | Alineada a bindings de `spike-layout-b-1` (sin `fecha` en detalle) |

### Pendientes (fuera T1)

- Commit opcional FW + Partes (usuario autoriza).
- Parte **I** SDD del TR oleada 5 cuando se cierre épica completa o por acuerdo de producto.
- **T2** CHK-06 `setPageHeaderEmpresa`.

### Recomendación final

Dar por **cerrado T1** a nivel F1. Siguiente trabajo: hito **T2** según TR § T2.