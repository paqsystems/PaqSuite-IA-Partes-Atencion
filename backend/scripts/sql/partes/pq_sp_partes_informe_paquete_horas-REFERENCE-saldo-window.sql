-- Referencia CC PQ #8 (PQ) — saldo acumulado por renglón (window).
-- TR-006-update: adaptar con filtros actor, @p_cliente_id obligatorio, @p_fecha_desde/@p_fecha_hasta,
-- delimitación tipo_cliente, fila sintética «Saldo inicial» y salida JSON (items_json + saldo_inicial).
-- Regla 75: en el SP final añadir WITH (NOLOCK) en tablas físicas de lectura.

/*
Signo acordado (SPEC-006 §4.3):
  es_tarea = 1  → suma duracion_minutos al saldo
  es_tarea = 0  → resta duracion_minutos
Equivalente: CASE WHEN es_tarea = 1 THEN 1 ELSE -1 END * duracion_minutos
*/

SELECT
    cliente_id,
    fecha,
    id,
    es_tarea,
    duracion_minutos,
    SUM(CASE WHEN es_tarea = 1 THEN 1 ELSE -1 END * duracion_minutos)
        OVER (
            PARTITION BY cliente_id
            ORDER BY fecha, id
            ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
        ) AS saldo
FROM dbo.PQ_PARTES_REGISTRO_TAREA WITH (NOLOCK)
ORDER BY cliente_id, fecha, id;
