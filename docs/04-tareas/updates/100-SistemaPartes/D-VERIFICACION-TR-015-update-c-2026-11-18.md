# D-VERIFICACION — TR-015-update-c (oleada 3 apariencia)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-c](../../100-SistemaPartes/TR-015-update-c-apariencia-controles-informes.md) |
| Fecha auto | 2026-11-18 |
| CC | [CC-PQ-18](../../../00-ControlCalidad/CC-PQ-18-2026-11-18-oleada3-apariencia-controles.md) |
| Smoke manual | [smoke-oleada3-apariencia-controles.md](../../../06-operacion/smoke-oleada3-apariencia-controles.md) |

---

## Verificación agente (F1) — automático

### Resultado

**Aprobado con observaciones** — capas D/E cubiertas; **falta smoke UI humano** (§ siguiente) para parte I.

### Evidencia revisada

| Eje | Estado |
|-----|--------|
| Alcance | `setControlAppearance` + planner + CHK-08-format / CHK-27 en matriz |
| Código | `LayoutControlAppearanceApplier.cs`, `DesignLayoutAppearanceHeuristics.php`, whitelist |
| Tests | Ver tabla abajo |
| Trazabilidad | SPEC/HU/TR/CC oleada 3 |

### Tests (ejecutados 2026-11-18)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | **OK** — 36 tests, 116 assertions |
| `php vendor/bin/phpunit --filter DesignLayoutChecklistPlannerTest` | **OK** — 24 tests (incl. CHK-08-format, CHK-27) |
| `dotnet test … --filter FullyQualifiedName~LayoutControlAppearance` | **OK** — 1 test |
| `.\eval-emission-ia-oleada3-apply.ps1` | **OK** — `PASS CHK-08-format` + `PASS CHK-27` (2026-11-18, sidecar `:5055` lab) |

### Criterios SPEC (auto vs manual)

| CA | Descripción | Auto |
|----|-------------|------|
| CA-B3-01 / HU CA-03 | Planner CHK-27 + CHK-08-format | **OK** (PHPUnit matriz) |
| CA-B3-02 / HU CA-01–02 | Apply muta REPX | **OK** (.NET unit + apply script pendiente sidecar) |
| CA-B3-03 / HU CA-04 | Visible en diseñador + L-B-10 | **Pendiente** smoke manual |
| CA-B3-04 / HU CA-05 | Sanitize campo inválido | **OK** (`test_sanitize_drops_unknown_field`) |

### Pendientes

1. Operador: smoke manual § **Smoke manual** (2 prompts).
2. Con sidecar activo: `eval-emission-ia-oleada3-apply.ps1` → anexar salida aquí.
3. Tras ambos CHK **OK**: cerrar parte **I** (TR/HU/SPEC **Finalizado**).

---

## Smoke manual (operador)

Registrar cuando completes [smoke-oleada3-apariencia-controles.md](../../../06-operacion/smoke-oleada3-apariencia-controles.md):

| CHK | Variante | Resultado | Operador | Fecha ART | Notas |
|-----|----------|-----------|----------|-----------|-------|
| CHK-08-format | Spike B titulo / apply | **OK** | Producto | 2026-11-18 | apply script + lab |
| CHK-27 | principal | **OK** | Producto | 2026-11-18 | apply script + lab |

### Apply script (opcional, anotar salida)

```text
PASS CHK-08-format
PASS CHK-27
Oleada 3 apply: passed=2 failed=0
```

---

## Veredicto final oleada 3

| Fase | Estado |
|------|--------|
| F1 automático | **Aprobado** |
| Smoke UI | **OK** — ver [D-CIERRE-oleada3](./D-CIERRE-oleada3-apariencia-2026-11-18.md) |
| Parte I (Finalizado) | **Finalizado** (2026-11-18) |