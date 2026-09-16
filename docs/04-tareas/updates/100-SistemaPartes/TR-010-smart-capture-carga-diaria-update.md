# TR-010-update – Smart Capture: setField visible y apply parcial

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-010-smart-capture-carga-diaria-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-010-smart-capture-carga-diaria-update.md) |
| **SPEC relacionada** | [SPEC-010-smart-capture-carga-diaria-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria-update.md) |
| **TR base** | [TR-010-smart-capture-carga-diaria](../../100-SistemaPartes/TR-010-smart-capture-carga-diaria.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-09-15 |

**Origen:** `00-ControlCalidad-PQ` · 09/08/2026 · CC #3 (IA no actualiza controles). Evidencia: `CC33-PQ-2026-09-15.png` (PQ + LACAPOL + 1:25 hrs; SelectBox en «Seleccionar…»).

---

## 1) In scope

- El turno **emite** `setField` por cada lookup 1-1 (cliente, asistente, tipo) y por observación/fecha/duración **válida**.
- El FE **aplica** esos `setField` al state que bindea los DX (`form.clienteId`, `usuarioId`, `duracionMinutos`, etc.) **después** de tener el `dataSource` cargado.
- Duración no múltiplo del tramo (ej. 85 min): `needsRefine` de duración; **no** omitir `setField` de cliente/asistente del mismo turno.
- Tras `setField` cliente: recargar universo tipos (`onClienteIdChange`) y luego aplicar tipo si vino en el mismo batch (ordenar actions).

## Out of scope

- Redefinir GEN-03, path del turno, save en BE, mobile.

---

## 2) AC

| AC HU | Verificación |
|-------|----------------|
| CA-CC3-07 | Feature: mensaje con códigos/nombres únicos → `actions` contienen `setField` clienteId y asistenteId; Vitest `applyPartesAction` deja form con esos ids; UI SelectBox `value` no null |
| CA-CC3-08 | Duración 85 / `1:25`: sin `setField` duracionMinutos (o needsRefine); sí setField de lookups OK |
| CA-CC3-09 | setField observacion/fecha/tipo se refleja en controles |

---

## 3) Implementación

| ID | Pieza | Notas |
|----|--------|-------|
| RN-TR-CC3-20 | `PartesTareaSmartCaptureTurnService` | No responder solo `replyText`. Lookups 1 → `setField` con id numérico. Duración inválida: rama refine **sin** `return` temprano que descarte acciones ya acumuladas. |
| RN-TR-CC3-21 | `applyPartesAction` / `CargaDiariaPage` | `setForm` debe ser el mismo state del SelectBox. Si `applySmartCaptureActions` es sync y `onClienteIdChange` async, **await** y aplicar `tipoTareaId` después. `formRef` coherente con `form` (evitar apply sobre snapshot stale). |
| RN-TR-CC3-22 | Duración `1:25` | Parser: `h:mm` y `h.mm` de reloj ≠ decimal. 1:25 = 85 min → inválido con tramo 15; 1.25 h = 75 min → válido. No confundir en el prompt/parser. |
| RN-TR-CC3-23 | Tests | Feature LACAPOL+PQ (fixtures); Vitest apply; E2E smoke: enviar texto y assert `partesCargaCliente` / asistente con valor. |

Campos canónicos sin cambio: `clienteId`, `asistenteId`, `tipoTareaId`, `fecha`, `duracionMinutos`, `observacion`, `sinCargo`, `presencial`.

---

## 4) Plan

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1 | BE | Acciones parciales; no dropear lookups si duración inválida | CA-CC3-07/08 | M |
| T2 | FE | Apply a controles DX + orden cliente→tipos | CA-CC3-07/09 | M |
| T3 | Tests | Feature + Vitest + E2E smoke modal | T1–T2 | M |

---

## 5) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-09-15 | Parte G CC-PQ #3 (09/08/2026). |
| 2026-09-15 | Parte D: `setField` parcial, parser reloj vs decimal, apply a SelectBox tras dataSource, `needsRefine` de duración sin dropear lookups. |
