# Control de Calidad — ST

| Campo | Valor |
|-------|--------|
| **ID archivo** | `00-ControlCalidad-ST` |
| **Responsable** | Sebastian Tomasini (ST) |
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
| **Comando de volcado** | `Corrige los errores del dd/MM/yyyy de ST` (o *Realiza las mejoras…* / *Procesa las solicitudes…*) |

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
2. Ejecutar **Parte G** (`Corrige… dd/MM/yyyy de ST`): §0 SPEC-update si cambia el alcance; HU-update / TR-update.
3. Implementar (**D**), tests (**E**), verificación (**F**).
4. Marcar updates **`Finalizado`** (manual) y **Parte I** (unificar).

## Índice de controles

| # | Fecha | Estado | Resumen |
|---|-------|--------|---------|
| 1 | 29/10/2026 | Especificado | Consulta Detallada: selector de filas + Emitir solo tildadas |

---

## Control de Calidad #1

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 29/10/2026 |
| **Responsable** | Sebastian Tomasini (ST) |
| **Estado** | Especificado |
| **Origen** | Prueba manual de Consulta Detallada de Partes (`/partes/informes/consulta-detallada`) |
| **Proceso** | `partes.informes.consultaDetallada` · menú `partes_consulta_detallada` |

### Hallazgos

En **Consulta Detallada de Partes** la grilla no ofrece selector de filas. El botón **Emitir** arma el documento con el universo completo de los filtros de pantalla (periodo, cliente, asistente, tipo y demás filtros de la consulta), sin recorte por filas elegidas por el usuario.

### Mejoras solicitadas

#### HU-006-consultas-dashboard-navegacion — selector de filas

*Procesado* · [HU-006-update](../03-historias-usuario/updates/100-SistemaPartes/HU-006-consultas-dashboard-navegacion-update.md)

- En la grilla de Consulta Detallada, agregar el selector de filas (casillas) para que el usuario pueda tildar una o varias filas.
- La columna del tilde incluye en el encabezado la casilla nativa de la grilla para tildar o destildar **todos** los registros de la grilla.
- La selección es la que usa **Emitir**. Exportar la grilla sigue siendo una acción distinta.

#### HU-011-reportes-emisiones — Emitir solo filas tildadas

*Procesado* · [HU-011-update](../03-historias-usuario/updates/100-SistemaPartes/HU-011-reportes-emisiones-update.md)

- Al ejecutar **Emitir**, el proceso debe incluir únicamente las filas tildadas en la grilla.
- Las filas del universo filtrado que no estén tildadas no entran en la emisión.
- Si no hay ninguna fila tildada, **Emitir** no debe procesar el universo completo.
- Alcance de este hito: vista grilla en web. Consulta Detallada no está en mobile.

### Criterios de aceptación preliminares

- [x] **CA-01** La grilla de Consulta Detallada muestra el selector de filas y permite tildar una o varias filas.
- [x] **CA-02** El encabezado de esa columna permite tildar y destildar todos los registros de la grilla.
- [x] **CA-03** **Emitir** procesa solo las filas tildadas.
- [x] **CA-04** Las filas no tildadas, aunque cumplan los filtros de pantalla, quedan fuera de la emisión.
- [x] **CA-05** Sin filas tildadas, **Emitir** no emite el universo completo de la consulta.
- [x] **CA-06** La exportación de la grilla no queda atada a esta selección.

### Derivación SDD

| Artefacto | Ruta |
|-----------|------|
| SPEC-006-update | `docs/05-open-spec/updates/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion-update.md` |
| SPEC-011-update | `docs/05-open-spec/updates/100-SistemaPartes/SPEC-011-reportes-emisiones-update.md` |
| HU-006-update | `docs/03-historias-usuario/updates/100-SistemaPartes/HU-006-consultas-dashboard-navegacion-update.md` |
| HU-011-update | `docs/03-historias-usuario/updates/100-SistemaPartes/HU-011-reportes-emisiones-update.md` |
| TR-006-update | `docs/04-tareas/updates/100-SistemaPartes/TR-006-consultas-dashboard-navegacion-update.md` |
| TR-011-update | `docs/04-tareas/updates/100-SistemaPartes/TR-011-reportes-emisiones-update.md` |
| D / E / F | `D-CC-ST-01-2026-10-29.md` · `E-CC-ST-01-2026-10-29.md` · `D-VERIFICACION-CC-ST-01-2026-10-29.md` |

Parte **I** aplicada (2026-10-29): contenido fusionado en SPEC-006/011, HU-006/011, TR-006/011 y manual SPEC-010. Evidencia D/E/F en `docs/04-tareas/updates/100-SistemaPartes/D-CC-ST-01-*` y `D-VERIFICACION-CC-ST-01-*`.
