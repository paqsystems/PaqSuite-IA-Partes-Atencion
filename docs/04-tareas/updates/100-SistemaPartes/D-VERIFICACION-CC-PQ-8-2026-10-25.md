# Verificación F — CC PQ #8 (25/10/2026)

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#8** · [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52) |
| **Updates** | TR-006-update (informe saldo + destaque) · TR-013-update (duración 1–240 h) — fusionados Parte I **06/11/2026** |
| **Plan D1** | [D1-TR-006-013-update-cc-pq-8.md](./d1/D1-TR-006-013-update-cc-pq-8.md) |
| **Veredicto F** | **Apto** — código ↔ TR + **smoke manual PQ** (informe Paquete de horas y rango de duración en carga) |
| **F1/F** | **Aprobado** 06/11/2026 |
| **Parte I** | 06/11/2026 — fusionado en SPEC/HU/TR-006 y SPEC/HU/TR-013 base; updates CC #8 eliminados |

---

## 1) Criterios de aceptación (CC #8)

| CA | Resultado F | Evidencia |
|----|-------------|-----------|
| **CA-01** Carga paquetes 1–240 h; rechaza fuera de rango | **OK** | `PAQUETE_MIN_HORAS = 1`; SP upsert `< 60`; Operations `60…14400`; tests Feature/Vitest; smoke PQ selector |
| **CA-02** Misma regla UI / API / SP / SC / i18n | **OK** | Helpers FE, FormRequest/Operations, `pq_sp_partes_paquete_upsert`, prompt SC 1–240 |
| **CA-03** Compras destacadas en Duración (`es_tarea = 0`) | **OK** | `PaqueteHorasPage` `-hh:mm` + clase `partes-paquete-horas-duration-credit`; smoke PQ |
| **CA-04** Tareas duración normal | **OK** | Formatter solo aplica signo/rojo si `esTarea === false` |
| **CA-05** Saldo inicial S₀ por cliente (`fecha < fechaDesde`) | **OK** | SP `pq_sp_partes_informe_paquete_horas`; smoke PQ informe |
| **CA-06** Running por renglón `(fecha, id)` | **OK** | Window T-SQL + fila sintética; smoke PQ |
| **CA-07** Clientes distintos no mezclan saldo | **OK** | `clienteId` obligatorio; una consulta = un cliente |

---

## 1.1) F1 — código ↔ TR (06/11/2026)

| Pieza | Evidencia | Estado |
|-------|-----------|--------|
| Duración FE | `partesPaqueteDuration.ts` — min 1 h; default alta `PAQUETE_MIN_MINUTOS` | **OK** |
| Duración API/SP | `PartesPaqueteOperations` + `pq_sp_partes_paquete_upsert.sql` 60…14400 step 60 | **OK** |
| Informe FE | Sin load sin `clienteId`; destaque compras; saldo firmado | **OK** |
| Informe BE/SP | Manifiesto `partes-sp-pq_sp_partes_informe_paquete_horas`; EmissionPort EXEC SP | **OK** |
| Tests | Feature paquetes 1 h; Vitest persist 60/241 h | **OK** (no reejecutados en esta F) |

**Veredicto F1:** **Aprobado**. Observación menor: el `SelectBox` de duración aún tenía fallback `8 * 60` al vaciar valor; corregido en el mismo cierre a `PAQUETE_MIN_MINUTOS`.

---

## 2) Smoke manual UI (PQ — 06/11/2026)

1. Informe **Paquete de horas**: saldo y presentación de compras verificados por el usuario.
2. **Carga de paquetes**: rango de selección de horas 1–240 verificado por el usuario.

**Resultado manual:** **Aprobado** (PQ).

---

## 3) Deploy

SP informe y upsert de paquetes via manifiesto host / `deploy-sp`. Sin migración de datos (no había paquetes &lt; 8 h válidos).
