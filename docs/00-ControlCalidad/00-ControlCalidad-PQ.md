# Control de Calidad — PQ

| Campo | Valor |
|-------|--------|
| **ID archivo** | `00-ControlCalidad-PQ` |
| **Responsable** | Pablo Quarracino (PQ) |
| **Alcance** | Hallazgos de pruebas manuales y mejoras solicitadas por cliente en PaqSuite Partes de Atención |
| **Metodología** | Open-Spec / SDD — [`_OPEN-SPEC-METODOLOGIA.md`](../_base/_OPEN-SPEC-METODOLOGIA.md) |
| **Dispatcher** | Parte **G** (volcado), **H** (cierre opcional), ciclo **G → D → E → F → I** |

## Propósito

Registro operativo de **incidencias y mejoras** detectadas fuera del flujo automatizado de tests. Cada sesión de control se numera secuencialmente y conserva trazabilidad hasta su derivación a **SPEC-update**, **HU-update** y **TR-update** en `docs/.../updates/`.

Este archivo **no sustituye** SPEC, HU ni TR: es la **entrada** del circuito de correcciones (Parte G).

## Convenciones

| Tema | Regla |
|------|-------|
| **Fecha** | Formato `dd/MM/yyyy` en todo el documento |
| **Bloques** | `## Control de Calidad #N` — numeración incremental |
| **Ítems** | Preferir `### HU-XXX-slug` cuando la HU sea identificable |
| **Marcas de gestión** | `*Procesado*` tras volcado G; `*Sugerencia: HU-…*` si aún no hay HU asociable |
| **Comando de volcado** | `Corrige los errores del dd/MM/yyyy de PQ` (o *Realiza las mejoras…* / *Procesa las solicitudes…*) |

## Estados del bloque (*Referencia del control*)

| Estado | Significado |
|--------|-------------|
| **Pendiente** | Control registrado; ítems sin volcar a `updates/` |
| **Con Sugerencias** | Volcado parcial: quedan ítems con sugerencia de HU sin archivo generado |
| **A Programar** | Todas las entradas marcadas `*Procesado*`; pendiente cierre formal G/H |
| **Especificado** | Parte G (o H) cerró el bloque: volcado documental completo; cola activa en `docs/.../updates/` — **no** implica código implementado ni **`Finalizado`** en metadatos de HU/TR |

> El **Estado** bajo *Referencia del control* es independiente del **Estado** en metadatos de HU/TR ([`07-estado-hu-tr.md`](../../.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md)). Tras **Parte I**, los originales unificados quedan en **Finalizado**; el CC permanece como referencia histórica.

## Flujo tras registrar un control

1. Registrar hallazgos en el bloque `#N` con estado **Pendiente**.
2. Ejecutar **Parte G** (`Corrige… dd/MM/yyyy de PQ`): §0 SPEC-update si cambia el alcance; HU-update / TR-update.
3. Implementar (**D**), tests (**E**), verificación (**F**).
4. Marcar updates **`Finalizado`** (manual) y **Parte I** (unificar).

## Índice de controles

| # | Fecha | Estado | Resumen |
|---|-------|--------|---------|
| 1 | 31/07/2026 | Especificado | `es_tarea` + Paquete de Horas — F1/F Aprobado · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-01-2026-07-31.md)) |
| 2 | 01/08/2026 | Especificado | ERP clientes + informes — F1/F Aprobado · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-02-2026-08-01.md)) |

---

## Control de Calidad #2

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 01/08/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 01/08/2026 → [D-VERIFICACION-CC-PQ-02](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-02-2026-08-01.md) |
| **Parte I** | Unificado 01/08/2026 → originales [SPEC/HU/TR-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md), [003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md), [006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |

### Hallazgos

Incorporar en los clientes dos atributos referidos al sistema ERP, para obtener informes para facturación.

*Procesado* (unificado Parte I) → [SPEC-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) · [HU-001](../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) · [TR-001](../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md) · [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

### Errores encontrados - Mejoras solicitadas

#### Agregar atributo al maestro de Clientes

*Procesado* (unificado Parte I) → [SPEC-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) · [HU-001](../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) · [TR-001](../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md) · [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

Agregar dos atributos Erp_Cliente y Erp_Articulo, string de 15 caracteres cada uno, para poder referenciar atributos del ERP con el cual después se puedan hacer informes de facturación.

#### Incorporar Atributos a Informes

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

Incorporar ambos atributos en la consulta detallada y la consulta agrupada, tango en la grilla como en el pivot.


---

## Control de Calidad #1

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 31/07/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 01/08/2026 → [D-VERIFICACION-CC-PQ-01](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-01-2026-07-31.md) |
| **Parte I** | Unificado 01/08/2026 → originales [SPEC/HU/TR-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md), [004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md), [005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md), [006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |

### Hallazgos

Poder llevar una cuenta corriente de horas para aquellos clientes que abonan por paquete anticipado

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) (incluido en rehacer Paquete de Horas)

### Errores encontrados - Mejoras solicitadas

#### Agregar atributo booleano "EsTarea"

*Procesado* (unificado Parte I) → [SPEC-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) · [HU-001](../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) · [TR-001](../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md)

Agregar en la tabla de tareas un atributo booleano denominado "EsTarea"
True : es una tarea (cargada del proceso "Carga de Partes").
False : Es una compra de horas (en un proceso a definir)

#### Carga de Partes y Proceso Masivo - Asignar True a "EsTarea"

*Procesado* (unificado Parte I) → [SPEC-004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) · [HU-004](../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [TR-004](../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) · [SPEC-005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) · [HU-005](../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [TR-005](../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md)

- Solo traer registros donde este atributo es "true"
- Al grabar un parte (nuevo/Edicion), asignar "true" a este atributo

#### Informes "Carga detallada" y "Carga agrupada" y Dashboard

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

- Filtrar los registros para que consideren unicamente los que este atributo es "true"

#### Informe "Paquete de Horas" : Rehacer

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

el objetivo de este informe es poder llevar una cuenta corriente de horas para aquellos clientes que contratan paquetes de horas anticipadas
este informe debe ser tipo grilla/pivot con las siguientes características:
- seleccionar como filtros fecha desde, fecha hasta, cliente (cuando el usuario es asistente o supervisor).
- presentar los mismos atributos que "Carga Detallada".
- este informe NO debe filtrar por el campo "Estarea"
- debe agregar un registro "Saldo inicial" con la suma/resta de minutos hasta la fecha desde solicitada (exclusive). Si "Estarea"=true, suma, si es false, resta.
- agregar una columna Saldo, donde en el primer registro "saldo inicial" se coloca la sumatoria antedicha, y cada registro agrega el saldo anterior más sus minutos (sumando/restando)
- en el PIVOT, no incluir este atributo de Saldo.
