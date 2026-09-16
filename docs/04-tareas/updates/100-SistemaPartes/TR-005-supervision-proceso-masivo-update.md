# TR-005-update – Proceso masivo: selección estable

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-005-supervision-proceso-masivo-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-005-supervision-proceso-masivo-update.md) |
| **SPEC relacionada** | [SPEC-005-supervision-proceso-masivo](../../100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) (sin SPEC-update) |
| **TR base** | [TR-005-supervision-proceso-masivo](../../100-SistemaPartes/TR-005-supervision-proceso-masivo.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-09-15 |

**Origen:** `00-ControlCalidad-PQ` · 09/08/2026 · CC #3 (tilde de fila / seleccionar todos se borra)

---

## 1) Diagnóstico (para D)

En `ProcesoMasivoPage`, `Selection mode="multiple"` + `selectedRowKeys` controlado. El síntoma CC (tilde que se limpia al marcar) encaja con:

- `onSelectionChanged` disparado con `selectedRowKeys: []` al re-render / cambio de `dataSource` / apply layout GEN;
- o recarga del listado en el mismo tick que el click.

No hay cambio de contrato API de lote.

---

## 2) In scope

- Conservar selección de usuario hasta destilde explícito, cambio de filtros que dispare `load()`, o refresh post-acción de lote.
- Incluye check de cabecera / select-all de página y el flujo «seleccionar todos del resultado» (SPEC-005).

## Out of scope

- Semántica atómica, atributos, Excel, plantillas (layouts: TR-004-update API compartida).

---

## 3) AC

| AC HU | Verificación |
|-------|----------------|
| CA-CC3-05 | Vitest del handler: click fila no termina en `selectedKeys=[]`. E2E o smoke: tildar 1 fila → check sigue. |
| CA-CC3-06 | Cabecera / select-all no se auto-limpia. |

---

## 4) Implementación

| ID | Pieza | Notas |
|----|--------|-------|
| RN-TR-CC3-10 | `onSelectionChanged` | Ignorar eventos vacíos si son de deselect masivo por remount (p. ej. no vaciar si `e.currentDeselectedRowKeys` vacío y `dataSource` acaba de cambiar). Alternativa DX: no pasar `selectedRowKeys` controlado si el grid GEN resetea; usar uncontrolled + ref. Elegir en D1 la que no luche con `ProcessDataGrid`. |
| RN-TR-CC3-11 | `dataSource={rows}` | Evitar nueva referencia de array en cada render si no cambió el listado (estabiliza Selection). |
| RN-TR-CC3-12 | Tests | Unit del reducer de selección; E2E Playwright masivo (hoy ausente) al menos humo de 1 tilde. |

---

## 5) Plan

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1 | FE | Estabilizar Selection vs re-render | CA-CC3-05/06 | M |
| T2 | Tests | Vitest handler + E2E humo tilde | T1 | M |

---

## 6) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-09-15 | Parte G CC-PQ #3 (09/08/2026). Bug técnico; sin SPEC-update. |
| 2026-09-15 | Parte D: `reduceMasivoSelection` ignora vaciado espurio y conserva claves off-page. |
