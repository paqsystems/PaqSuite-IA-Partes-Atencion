# Indice del modulo Sistema Partes

## Objetivo

Consolidar en un solo documento el mapa funcional del modulo `SistemaPartes`, sus epicas, dependencias y relacion con la documentacion base de producto y con el contexto comun del framework.

Este indice no reemplaza a `PROD-Sistema-Partes.md`, `RN-Sistema-Partes.md` ni `modelo-datos.md`. Su objetivo es servir como puerta de entrada para entender el modulo antes de regenerar `SPEC`, `HU` y `TR`.

## Documentos base del modulo

| Documento | Rol |
|-----------|-----|
| `PROD-Sistema-Partes.md` | Vision, alcance MVP, actores, flujo E2E y roadmap |
| `RN-Sistema-Partes.md` | Reglas de negocio por dominio funcional |
| `modelo-datos.md` | Modelo fisico y conceptual del modulo |

## Documentacion operativa complementaria

| Documento | Rol |
|-----------|-----|
| `docs/backend/SistemaPartes/arranque-base-datos-inicial.md` | Runbook operativo para reconstruir la base inicial del modulo |
| `ANEXO-RESGUARDO-HUs-00-028-055-056.md` | Respaldo documental de HUs sensibles ya absorbidas parcial o totalmente |

## Relacion con el contexto comun

El modulo reutiliza el contexto comun definido en `docs/00-contexto/_mono/` para:

- autenticacion, sesion y menu,
- shell y estructura general del sitio,
- grillas, pivots, exportaciones y layouts,
- patrones ABM,
- parametros generales,
- criterios MONO vs MULTI.

Las definiciones especificas del negocio de partes deben vivir en la carpeta `docs/02-producto/SistemaPartes/`.

En particular, las consultas de `SistemaPartes` deben leerse como procesos que heredan del framework comun:

- presentacion en grilla como base;
- vista pivot cuando el analisis lo requiera;
- exportacion a Excel;
- gestion de layouts sobre grillas o pivots cuando corresponda.

## Cobertura documental actual del modulo

La base vigente del modulo debe leerse con esta jerarquia:

1. `PROD-Sistema-Partes.md` para vision, alcance, actores y mapa funcional.
2. `RN-Sistema-Partes.md` para reglas de negocio, acceso, captura, consultas y dashboard.
3. `modelo-datos.md` para modelo conceptual y fisico.
4. `docs/backend/SistemaPartes/arranque-base-datos-inicial.md` para arranque tecnico.
5. `ANEXO-RESGUARDO-HUs-00-028-055-056.md` solo como respaldo historico y resguardo de absorcion.
6. `docs/00-contexto/_mono/` para framework comun.

Las HUs del modulo no deberian ser la fuente primaria para regenerar el contexto si la informacion ya existe en esos documentos.

## Flujo E2E prioritario

1. Login del usuario.
2. Ingreso al shell principal.
3. Registro de tarea diaria.
4. Visualizacion de tareas o resumen.

## Mapa de epicas del modulo

| Epica | Tema | Estado documental esperado |
|-------|------|----------------------------|
| 0 | Infraestructura inicial del modulo | Base de datos, seeds y tablas del modulo |
| 1 | Autenticacion de empleado | Debe existir a nivel producto aunque hoy falte en `hu-historias` |
| 2 | Perfil / sesion de usuario | Debe existir a nivel producto aunque hoy falte en `hu-historias` |
| 3 | Clientes | ABM y asignacion de tipos de tarea |
| 4 | Tipos de cliente | Catalogo y mantenimiento |
| 5 | Empleados | ABM y detalle |
| 6 | Tipos de tarea | Catalogo, generico, default, asignaciones |
| 7 | Registro de tareas | Carga diaria, edicion, validaciones y listados |
| 8 | Proceso masivo | Cierre y reapertura supervisor |
| 9 | Informes y consultas | Detalle, agrupaciones y exportacion |
| 10 | Dashboard y navegacion | Inicio, resumenes y menu lateral del modulo |

## Dependencias funcionales principales

```text
Acceso y sesion
    -> Catalogos maestros
    -> Registro de tareas
    -> Consultas e informes
    -> Dashboard

Clientes + Tipos de tarea
    -> Registro de tareas

Registro de tareas
    -> Proceso masivo
    -> Informes
    -> Dashboard
```

## Huecos documentales actuales a tener en cuenta

- La ausencia de `HU-001` a `HU-011` en `hu-historias` ya no debe compensarse leyendo tareas sueltas: autenticacion, perfil y clientes con acceso quedaron absorbidos en `PROD` y `RN`.
- La decision funcional de menu del modulo ya debe leerse alineada con el contexto comun basado en `pq_menus`, no como menu hardcodeado en HUs de transicion.
- El modelo conceptual de identidad (`users`, `PQ_PARTES_USUARIOS`, `PQ_PARTES_CLIENTES`) debe leerse desde `RN-Sistema-Partes.md` y `modelo-datos.md`, no solo desde historias o tareas.
- El anexo de resguardo sirve para tranquilidad documental, pero no reemplaza la base principal del modulo.

## Mapa de absorcion de HUs

Estado conservador luego de esta pasada:

- **Absorbidas u operativamente reemplazadas:** `HU-00`, `HU-014` a `HU-038`, `HU-039` a `HU-050`, `HU-055`.
- **Absorbidas con resguardo historico recomendado:** `HU-028`, `HU-056`.
- **Absorbidas por contexto o documentacion base aunque no existan en `hu-historias`:** bloque equivalente a `HU-001` a `HU-011`, consolidado entre `PROD`, `RN`, `_mono` y `hu-tareas`.
- **A revisar antes de eliminación definitiva solo por cautela documental:** `HU-051` a `HU-054`, para verificar que su valor residual sea ya solo de detalle de UX o trazabilidad y no de definicion conceptual.

Este mapa no elimina archivos. Su funcion es dejar asentado que la base documental principal ya absorbio gran parte del significado que antes estaba disperso en HUs.

## Convencion para regeneracion documental

Al regenerar artefactos del modulo, el orden recomendado es:

1. leer este indice;
2. leer `PROD-Sistema-Partes.md`;
3. leer `RN-Sistema-Partes.md`;
4. leer `modelo-datos.md`;
5. leer la documentacion operativa si el cambio toca arranque o infraestructura del modulo;
6. complementar con `docs/00-contexto/_mono/`;
7. recien entonces generar `SPEC`, `HU` y `TR`.
