# Sistema Partes IA

## Objetivo

Esta carpeta define la base conceptual canonica del modulo `SistemaPartes` en lenguaje humano.

Su finalidad es servir como **fuente de verdad principal** para regenerar, cuando corresponda:

- `SPEC`
- `HU`
- `TR`

La carpeta no reemplaza al contexto comun del framework en `docs/00-contexto/_mono/`. Tampoco reemplaza la documentacion operativa del backend. Su alcance es **el negocio propio del sistema de partes**.

## Principios de esta base

1. Aqui se documenta **que es** el modulo y **como debe comportarse** desde el punto de vista funcional.
2. Las generalidades del framework comun se reutilizan, pero no se redefinen.
3. El modelo de datos tecnico vive en un archivo propio separado del resto de la narrativa funcional.
4. Las dudas, contradicciones o ambiguedades se registran explicitamente y no se silencian.
5. Los artefactos derivados (`SPEC`, `HU`, `TR`) deben bajar a detalle, validacion, contratos, pruebas y ejecucion, sin volver a discutir la definicion base del modulo.

## Orden de lectura recomendado

1. `01-vision-y-alcance.md`
2. `02-actores-identidad-y-acceso.md`
3. `03-modelo-conceptual-del-dominio.md`
4. `04-maestros-y-catalogos.md`
5. `05-operacion-diaria-y-supervision.md`
6. `06-consultas-dashboard-y-navegacion.md`
7. `07-fuera-de-alcance-y-evolucion.md`
8. `08-dudas-y-ambiguedades.md`
9. `09-modelo-datos-tecnico.md`
10. `10-mobile.md`
11. `11-checklist-temas-definidos-del-modulo.md`
12. `12-asistente-ia-ayuda-y-chat-documental.md` — Asistente IA (chat documental) desde menú avatar; corpus Partes + generalidades Framework
13. `13-importacion-partes-excel.md` — Importación de partes (tareas) desde Excel bajo Carga de Partes
14. `14-smart-capture.md` — Smart Capture (asistente operativo) en el modal de alta/edición de tarea (Carga diaria)

## Relacion con otras carpetas

### Contexto comun

Los siguientes temas siguen dependiendo del framework comun y deben leerse en `docs/00-contexto/_mono/`:

- login y sesion comun;
- menu, shell y estructura general del sitio;
- grillas, pivots, exportaciones y layouts;
- patrones ABM;
- parametros generales;
- diferencias MONO vs MULTI.

### Documentacion operativa

Los temas de arranque tecnico, migraciones o reconstruccion de base deben leerse junto con:

- `docs/backend/SistemaPartes/arranque-base-datos-inicial.md`

## Relacion con la carpeta anterior

La carpeta `docs/02-producto/SistemaPartes/` contiene la documentacion previa del modulo, incluyendo:

- definiciones de producto;
- reglas de negocio;
- modelo de datos mixto;
- anexos de resguardo;
- historias y tareas historicas.

Esta nueva carpeta consolida esa informacion en un formato mas limpio y mas apto para ser usado como base de regeneracion documental.
