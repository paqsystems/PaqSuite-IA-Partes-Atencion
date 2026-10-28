# Informe Paquete de Horas

## Sugerencia

Como obtener en forma efectiva el saldo anterior y el saldo de cada renglon del informe.

## Saldo Anterior

hacer una tabla temporal : codigo cliente y saldo
considerar que hay un parámetro @fechadesde

1) saldo Anterior
select cod_client, sum( case when estarea=1 then 1 else -1 end * duracion) as saldo
from pq_partes_registro_tarea
where fecha<@fechadesde 
group by cod_client

esta query sirve para agregar en cada cliente el saldo anterior a la fecha desde ingresada

2) saldo por renglon

select cod_client, fecha, id, 
    sum( case when estarea=1 then 1 else -1 end * duracion) as saldo
from pq_partes_registro_tarea
group by cod_client, fecha_id    

3) Integrar con registros de la consulta

- Hacer una consulta con PQ_PARTES_REGISTRO_TAREAS por los filtros solicitados (fecha desde, fecha hasta, etc)
- Agregar a este resultado los registros de saldo anterior de cada cliente obtenido en 1)
- hacer join con la tabla 2) mediante
  3.cliente=2.cliente y (3.fecha>2.fecha or (3.fecha=2.fecha and 3.id>=2.id)
  campo saldo = sum(2.saldo)
  