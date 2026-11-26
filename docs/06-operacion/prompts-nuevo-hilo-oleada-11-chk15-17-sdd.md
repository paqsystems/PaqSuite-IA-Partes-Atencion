# Prompts — nuevo hilo SDD oleada 11 (CHK-15, 16, 17)

Documento para **copiar y pegar** en un chat nuevo (Cursor agente). Incluye trazabilidad **Jira** y rutas markdown.

## Trazabilidad

| Rol | Clave / ruta |
|-----|----------------|
| Épica SPEC-015 | [PIAPARTES-53](https://paqsystems.atlassian.net/browse/PIAPARTES-53) |
| Story HU | [PIAPARTES-78](https://paqsystems.atlassian.net/browse/PIAPARTES-78) — `HU-015-update-m` |
| Task TR | [PIAPARTES-79](https://paqsystems.atlassian.net/browse/PIAPARTES-79) — `TR-015-update-m` |
| SPEC | `docs/05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-11-imagenes-chk15-17-update.md` |
| HU | `docs/03-historias-usuario/100-SistemaPartes/HU-015-update-m-imagenes-chk15-17.md` |
| TR | `docs/04-tareas/100-SistemaPartes/TR-015-update-m-imagenes-chk15-17.md` |
| D1 | `docs/04-tareas/100-SistemaPartes/d1/D1-TR-015-update-m-chk15-17.md` |
| Matriz | `PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json` |
| Guía smoke | `docs/06-operacion/guia-manual-prompts-checklist-chk-emision-ia.md` |

**GEN:** adoptar apply sidecar Framework (regla 19). Host: planner + whitelist. No reimplementar appliers en PHP.

---

## Prompts canónicos Smart Capture (producto)

Usar en diseñador **después** de implementar parte D — también son la referencia de heurísticas.

| CHK | Prompt (español, pegar en SC) | Acción esperada |
|-----|------------------------------|-----------------|
| **CHK-16** | `Insertá el logo de la empresa` | `setPageHeaderLogo` → `{ "source": "empresa" }` |
| **CHK-15** | `Agregá una imagen de fondo al informe` | `setReportWatermarkImage` → `{ "source": "default" }` |
| **CHK-17** | `Mostrá clienteCode como código QR` | `addDetailBarCode` → `{ "field": "clienteCode", "symbology": "QRCode" }` |

### Alias heurística (v1)

| CHK | Variantes |
|-----|-----------|
| 15 | `Poné una imagen de fondo`, `Watermark en el informe`, `Fondo con imagen` |
| 16 | `Agregá el logo de la empresa`, `Logo arriba a la izquierda`, `Poné el logo` |
| 17 | `Código QR del cliente`, `QR con el código de cliente` |

### Orden smoke manual

CHK-16 → CHK-15 → CHK-17 (logo, fondo, QR con `clienteCode` en layout).

---

## Prompts dispatcher (chat agente) — en orden

Sustituí `{…}` solo si cambiás alcance. Dispatcher: `.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md`.

### A1 — Revisión ambigüedad SPEC (opcional si SPEC ya estable)

```text
@openspec-05-spec-ambiguity-review.md Revisá el SPEC
docs/05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-11-imagenes-chk15-17-update.md
y decime si está apto para generar HU/TR.
```

### B — HU desde SPEC (si querés enriquecer narrativa)

```text
@openspec-02-hu-desde-spec.md Generá o enriquecé la HU desde
docs/05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-11-imagenes-chk15-17-update.md
Destino: docs/03-historias-usuario/100-SistemaPartes/HU-015-update-m-imagenes-chk15-17.md
Jira Story: PIAPARTES-78. No ampliar alcance fuera de CHK-15..17.
```

### C + C1 — TR desde SPEC + HU

```text
@openspec-03-tr-desde-spec-hu.md Escribí o completá la TR
docs/04-tareas/100-SistemaPartes/TR-015-update-m-imagenes-chk15-17.md
desde SPEC oleada 11 y HU-015-update-m. C1: revisión ambigüedad TR antes de D1.
Jira Task: PIAPARTES-79.
```

### D1 — Plan sin código

```text
@openspec-04-d1-plan.md @ai-planning-mode Parte D1 para
docs/04-tareas/100-SistemaPartes/TR-015-update-m-imagenes-chk15-17.md
Usá D1 existente en docs/04-tareas/100-SistemaPartes/d1/D1-TR-015-update-m-chk15-17.md como base.
Repos: Partes-Atencion + Framework services/dx-reporting.
Al cerrar D1: comentar plan en PIAPARTES-79 y mover SDD a Implementando si aplica.
```

### D — Implementación

```text
Parte D autorizada. Implementá TR-015-update-m (oleada 11 CHK-15, 16, 17):
- Framework: appliers setReportWatermarkImage, setPageHeaderLogo, addDetailBarCode + tests + eval-emission-ia-oleada11-chk15-17-apply.ps1
- Partes: whitelist, heurísticas planner, i18n noop, tests DesignLayoutChecklistPlannerTest
- No commit/push sin mi ok explícito
- Regla 19: no copiar GEN al host; apply solo sidecar
Jira PIAPARTES-79. SPEC/HU/TR enlazados en docs/06-operacion/prompts-nuevo-hilo-oleada-11-chk15-17-sdd.md
```

### E — Tests

```text
Parte E para TR-015-update-m: ejecutá phpunit DesignLayout*, dotnet test appliers nuevos, script eval oleada 11. Actualizá TR con resultados.
```

### F1 / F — Verificación y cierre

```text
@agent-verification-guide Parte F1 para TR-015-update-m oleada 11 (CHK-15..17).
Después smoke manual (prompts canónicos § arriba) y si OK: F-TR-015-update-m-oleada11-chk15-17.md + D-VERIFICACION.
Comentar cierre en PIAPARTES-78 y PIAPARTES-79 (SDD → Finalizado/Verificando según runbook).
```

### Jira — sincronizar tras cada parte (agente)

```text
Según regla 09-jira-sdd: actualizar ledger y comentario SDD en PIAPARTES-78/79 al cerrar B, C1, D, E, F1, F.
Épica PIAPARTES-53 agrega estado si todas las TR hijas correspondientes avanzan.
```

---

## Checklist rápido implementador

- [ ] Sidecar health `:5055`
- [ ] Matriz v4 CHK-15..17 con `applyAssert`
- [ ] PHPUnit matriz verde
- [ ] Script eval PASS 3/3
- [ ] Smoke UI 16→15→17 con BYOK
- [ ] F + guía manual actualizada

---

## Prompt único (todo el circuito en un hilo)

Si preferís **un solo mensaje** al abrir el hilo nuevo:

```text
Implementá la oleada 11 SPEC-015 (CHK-15, 16, 17 — imágenes, logo, QR) siguiendo SDD:
- Lee SPEC/HU/TR/D1 en docs/05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-11-imagenes-chk15-17-update.md,
  HU-015-update-m, TR-015-update-m, D1-TR-015-update-m-chk15-17.md
- Matriz: PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json
- Jira: PIAPARTES-78 (HU), PIAPARTES-79 (TR), épica PIAPARTES-53
- Flujo: confirmar D1 → D → E → F1 → smoke con prompts canónicos (doc prompts-nuevo-hilo-oleada-11-chk15-17-sdd.md)
- GEN-03/15 sidecar; regla 74 no aplica a apply; regla 19 no forks
- Sin commit/push hasta que yo lo pida
```