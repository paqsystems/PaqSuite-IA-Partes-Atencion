# Revisión de ambigüedad — SPEC-006-update — CC PQ #8 (informe Paquete de Horas)

| Campo | Valor |
|-------|--------|
| **SPEC-update** | [SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe](./SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **Paso** | A1 |
| **Fecha** | 2026-10-30 |
| **Origen** | CC PQ **#8** · decisiones PQ en chat 30/10/2026 |
| **Estado A1** | **Cerrado** |

## Resultado general

- **Apto** — sin ambigüedades bloqueantes para HU/TR.

## Decisiones cerradas (PQ)

| ID | Tema | Decisión |
|----|------|----------|
| **Q1** | Cliente en UI | **Obligatorio** para supervisor/asistente antes de consultar; cliente funcional = implícito. Sin `clienteId` → API **422**; FE sin búsqueda automática al mount sin cliente. |
| **Q2** | Saldo multi-cliente en una grilla | **No aplica** — una consulta = un cliente; una fila «Saldo inicial» + running balance. |
| **Q3** | Capa de cálculo (regla 74) | **SP T-SQL** `pq_sp_partes_informe_paquete_horas` en `backend/scripts/sql/partes/`; **retirar** rama PHP `PartesInformeOperations::paqueteHoras` en runtime **sqlsrv** (SpCaller delega a EXEC). Tests sqlite pueden mantener dispatcher PHP documentado como excepción test-only en TR. |
| **Q4** | Referencia SQL del responsable | PQ puede aportar **SELECT/SP referencial**; TR lo adopta o replica semántica del SPEC §4.1. No bloquea B/C si aún no está el archivo. |
| **Q5** | Destaque compras (`es_tarea=0`) | **OK** — duración `-hh:mm` + estilo rojo en grilla web; export con signo textual. |
| **Q6** | Pivot / Saldo | Sin columna Saldo en pivot (sin cambio). |

## Ambigüedades críticas

- Ninguna tras Q1–Q6.

## Ambigüedades menores (TR)

| Tema | Cierre |
|------|--------|
| Clave i18n `clienteRequerido` | TR fija nombre exacto en catálogo `partes.informe.*`. |
| Shape JSON SP → API | Mantener envelope actual (`items`, `saldoInicial`, camelCase); SP devuelve JSON o result sets documentados en TR. |
| Fila saldo inicial `es_tarea` | Presentación: no usar estilo “compra”; TR alinea con SPEC §4.3. |

## Veredicto

- Puede pasar a **HU (Parte B):** **Sí**
