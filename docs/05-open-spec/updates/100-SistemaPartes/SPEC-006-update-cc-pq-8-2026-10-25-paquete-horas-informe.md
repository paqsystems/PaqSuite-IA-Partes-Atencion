# SPEC-006-update — CC PQ #8 (25/10/2026) — Informe Paquete de Horas (saldo y destaque)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe |
| **SPEC base** | [SPEC-006-consultas-dashboard-navegacion](../../100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) §4.3b |
| **Estado** | Pendiente |
| **Última actualización** | 2026-10-30 |
| **Origen** | CC **#8** · [`18-obtencion-saldo-por-renglon.md`](../../../02-producto/Sistema-Partes-IA/18-obtencion-saldo-por-renglon.md) |
| **HU-update** | [HU-006-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **TR-update** | [TR-006-update](../../../04-tareas/updates/100-SistemaPartes/TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |

## Delta

### Saldo por renglón (bugfix)

Implementar cálculo acumulado por **cliente** según producto §4.3b y nota técnica 18:

1. **Saldo inicial:** suma/resta movimientos con `fecha < fechaDesde` (`es_tarea=1` suma, `es_tarea=0` resta minutos).
2. **Filas del periodo:** orden **`fecha` ASC, `id` ASC**; saldo running = saldo anterior ± minutos de la fila.
3. SP de informe Must devolver columna `saldo` coherente; FE muestra fila sintética «Saldo inicial».
4. Clientes distintos no mezclan acumulados (CA-05…CA-07).

### Destaque visual paquetes (`es_tarea = 0`)

En columna **Duración** del informe (grilla; export si aplica):

- Presentación **negativa** (`-hh:mm`) **y/o** estilo rojo (clase DX / `cellPrepared`).
- Filas `es_tarea = 1` sin destaque (CA-03, CA-04).

## Fuera de alcance

- Pivot: campo Saldo sigue excluido (SPEC base).
- Mobile kardex paquete (SPEC-007) salvo paridad mínima acordada en TR.

## Criterios verificables

- [ ] Saldo inicial + running balance coinciden con SQL referencia 18 para fixture de prueba.
- [ ] Paquetes visualmente distinguibles en duración.
