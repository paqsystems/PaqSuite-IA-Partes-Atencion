# SPEC-010-update – Smart Capture: aplicar campos a los controles del modal

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-010-update |
| Título | Smart Capture — el turno debe actualizar los controles del formulario, no solo el hilo |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-09-15 |
| SPEC base | [SPEC-010-smart-capture-carga-diaria](../../100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) |
| HU relacionada(s) | [HU-010-smart-capture-carga-diaria-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-010-smart-capture-carga-diaria-update.md) |
| TR relacionada(s) | [TR-010-smart-capture-carga-diaria-update](../../../04-tareas/updates/100-SistemaPartes/TR-010-smart-capture-carga-diaria-update.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Fuente | `00-ControlCalidad-PQ` |
| Fecha | 09/08/2026 |
| Control | #3 |
| Ítem | Cargas de Partes Diarios — Implementar IA |
| Evidencia | Captura del modal: hilo SC reconoce asistente/cliente/duración y el formulario permanece en «Seleccionar…». Archivo en repo: `CC33-PQ-2026-09-15.png` (el CC cita `CC#3-PQ-2026-09-16.png`). |

---

## 1. Resumen del delta

El SPEC-010 ya exige `actions` → draft. El control de calidad muestra el fallo de producto: el asistente **responde en el hilo** y **no pinta** Asistente, Cliente, Tipo, Duración ni Observación.

Este update **cierra** esa laguna de aceptación:

```text
Asistencia IA en modal de Carga diaria: adoptar GEN-03. UI/motor = SmartCapturePanel + applySmartCaptureActions.
SoT: Framework SPEC-001-03. Host: POST /api/v1/partes/tareas/asistente/turn; draft = estado visible de los controles DX del modal.
```

---

## 2. Comportamiento (refuerzo §4.2 paso 6 y R-SC-08)

| ID | Norma |
|----|--------|
| R-SC-31 | Cada dato **resoluble** (lookup 1 coincidencia, fecha no futura, duración válida, observación, flags) **debe** emitirse como `setField` (u action GEN equivalente) **y** el FE **debe** reflejarlo de inmediato en el control visible (SelectBox, DateBox, TextBox, CheckBox). Un `replyText` sin mutación de controles **no** cumple el criterio. |
| R-SC-32 | Lo resoluble se aplica **aunque** otro campo del mismo turno sea inválido o ambiguo. Ejemplo CC: duración `1:25` / 85 min (no múltiplo del tramo) **no** impide aplicar asistente y cliente si el lookup es único. La duración inválida → `needsRefine` / mensaje; **sin** bloquear el resto. |
| R-SC-33 | Tras `setField` de `clienteId`, el universo de tipos se recarga y el SelectBox de tipo muestra el valor aplicado (default o propuesto). Tras `setField` de `asistenteId` / `duracionMinutos`, el SelectBox correspondiente muestra la opción (no queda en placeholder «Seleccionar…»). |

No cambia: grabación solo con `save` o botón Guardar; sin auto-save por substring; mobile excluido.

---

## 3. Criterios verificables (delta)

- [ ] Turno de texto con asistente único + cliente único aplica ambos SelectBox del modal (dejan de mostrar «Seleccionar…»).
- [ ] Si la duración no es múltiplo del tramo, el hilo lo indica y **igual** se ven cliente/asistente ya aplicados.
- [ ] Observación/fecha/tipo resolubles aparecen en sus controles, no solo en el chat del panel.
- [ ] Test FE: `apply` de `setField` deja el form state que bindea los DX; test de turno (Feature o contract) emite `setField` para lookups 1-1.

---

## 4. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-09-15 | Parte G CC-PQ #3 (09/08/2026): R-SC-31…33 — controles visibles + apply parcial si duración inválida. |
