# Plan D1 — CC PQ #8 (TR-006-update + TR-013-update)

## Alcance entendido

Corrección informe Paquete de Horas (cliente obligatorio, saldo SP/sqlsrv, destaque compras) + rango paquetes 1–240 h. Sin CC #9 Framework en este plan.

## Orden de trabajo

1. Backend: validación `clienteId`; `paqueteHoras` exige cliente; `SpCaller` sqlsrv → SP.
2. SQL: `pq_sp_partes_informe_paquete_horas.sql` (window PQ + S₀); upsert min 60 min.
3. FE: PaqueteHorasPage sin load mount; duración/saldo signed; CargaPaquetes default 1 h.
4. SC/parser/i18n/authMessages duración 1–240.
5. Tests + ejecutar suite Partes.

## Confirmación de alcance

Sin cambio fuera de SPEC/HU/TR CC #8: **Sí**.
