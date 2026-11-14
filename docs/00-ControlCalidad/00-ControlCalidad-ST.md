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
| 2 | 05/11/2026 | Pendiente | Diseñador: Prompt-to-Report IA sobre informe **ya confeccionado** (no solo creación nueva) |
| 3 | 07/11/2026 | Pendiente | Sidecar diseñador: schema por `processCode` + no exponer placeholder **Dataset** (`id`/`label`) |

---

## Control de Calidad #3

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 07/11/2026 |
| **Responsable** | Sebastian Tomasini (ST) |
| **Estado** | Pendiente |
| **Origen** | Lab TR-015 / TR-016: informes `Report-prueba-ia-1`, nuevo **Pirulin** — wizard y Field List muestran **Consulta detallada** y además **Dataset** (solo `id`, `label`) |
| **Proceso** | Diseñador GEN-15 · `/emisiones/disenador` · `partes.informes.consultaDetallada` |
| **Trazabilidad base** | [TR-011](../04-tareas/100-SistemaPartes/TR-011-reportes-emisiones.md) · [TR-015](../04-tareas/100-SistemaPartes/TR-015-piloto-dx-reporting-ia.md) · [TR-016](../04-tareas/100-SistemaPartes/TR-016-edicion-ia-informes-existentes-prompt-to-report.md) · [SPEC-016](../05-open-spec/100-SistemaPartes/SPEC-016-prompt-to-report-edicion-informes.md) · Framework `services/dx-reporting` (`DesignerModelFactory`, `ProcessFieldSchemaCatalog`) |

### Hallazgos

1. **`GetDesignerModel` ignora `processCode`** que el host ya envía en query (`DXXRD/GetDesignerModel?processCode=partes.informes.consultaDetallada`). En `DesignerModelFactory` el parámetro no participa en la resolución del schema (`_ = processCode`).
2. El schema se resuelve **solo** por nombre de archivo del layout (`reportUrl`, p. ej. `Report-prueba-ia-1`, `Pirulin`). Si el nombre no coincide con `data/schemas/*.json` ni contiene `consultaDetallada`, el sidecar usa el **fallback** codificado: origen **Dataset** con columnas **`id`** y **`label`**.
3. Variantes guardadas con nombres “libres” quedan atadas al fallback: el wizard **Add Data Source** solo ofrece ese origen de dos campos; **Prompt-to-Report** y bindings quedan inutilizables para Partes.
4. En informes nuevos (evidencia **Pirulin**), el operador ve **dos** orígenes: el correcto (**Consulta detallada**, ~14 campos) **y** **Dataset** (`id`/`label`). Comportamiento confuso y riesgo de enlazar controles al origen equivocado.
5. Existe `BackendDesignerSchemaClient` (API Laravel `field-schema`) pero **no** se integra en `GetDesignerModel` para hosts con backend configurado.

**Causa raíz (Framework):** resolución de dataset del diseñador desacoplada del **proceso emisible** seleccionado en el shell GEN-15.

### Mejoras solicitadas

#### Sidecar — schema por proceso y un solo data source visible

*Sugerencia: TR-015-update / TR-016-update + cambio en repo **PaqSuite-IA-FRAMEWORK** (`dx-reporting`)*

1. **`DesignerModelFactory`:** resolver schema en orden: (a) `processCode` del request; (b) heurística actual por `reportUrl`; (c) opcional `BackendDesignerSchemaClient`; (d) fallback `id`/`label` **solo** si no hay proceso ni schema de archivo.
2. **`BuildDataSources`:** cuando (a)–(c) producen schema válido, el diccionario entregado a `GetModel` debe contener **un único** `JsonDataSource` (p. ej. “Consulta detallada”). **No** incluir el placeholder **Dataset** en el wizard ni en la lista de orígenes del asistente.
3. **`BindPrimaryDataSource`:** al enlazar el schema del proceso, **reemplazar** el origen primario del reporte; si el REPX traía un `JsonDataSource` fallback de sesiones anteriores, **no** dejarlo duplicado en `ComponentStorage` / Field List (limpiar o no reinyectar el fallback).
4. **Tests sidecar:** `GetDesignerModel` con `reportUrl=Pirulin` + `processCode=partes.informes.consultaDetallada` → un solo origen, ≥14 columnas; sin entrada `Dataset` con dos campos.
5. **Regresión:** layout `partes_consultaDetallada_principal` y Paquete de horas (`partes.informes.paqueteHoras`) siguen con su schema; Prompt-to-Report creación nueva (TR-015) sin regresión.

**¿Es posible no agregar Dataset?** **Sí**, en producto: el fallback es una decisión de implementación en `ProcessFieldSchemaCatalog.ResolveSchema`, no una obligación de DevExpress. Debe limitarse a informes sin proceso y sin schema conocido (p. ej. smoke genérico del Framework).

### Criterios de aceptación preliminares

- [ ] **CA-ST3-01** Con proceso **Consulta detallada** confirmado en el host, abrir variante `Pirulin` (o cualquier código sin `consultaDetallada` en el nombre) muestra **un** data source con columnas de negocio del proceso.
- [ ] **CA-ST3-02** El wizard **Add Data Source** no lista **Dataset** (`id`/`label`) cuando el proceso tiene schema en catálogo o API.
- [ ] **CA-ST3-03** Informes legacy `Report-prueba-ia-1` recargados con `processCode` correcto muestran Field List completo sin depender de renombrar el `.repx`.
- [ ] **CA-ST3-04** Batería Prompt-to-Report (TR-015 §4.4 y TR-016 escenario #6) desbloqueada: wizard con metadata de campos reales; sin error por schema mínimo.
- [ ] **CA-ST3-05** Documentación: nota en runbook Framework §6 + referencia en evidencia F1.

### Derivación SDD

| Artefacto | Ruta prevista |
|-----------|----------------|
| SPEC-update | `docs/05-open-spec/updates/100-SistemaPartes/SPEC-011-…` o acotado a SPEC-015/016 según Parte G |
| TR-update | `docs/04-tareas/updates/100-SistemaPartes/TR-011-…` / TR-016-update (sidecar) |
| Implementación SoT | `PaqSuite-IA-FRAMEWORK/services/dx-reporting` |

Siguiente paso: Parte **G** (`Corrige los errores del 07/11/2026 de ST`) o ampliar TR-016 en curso con tareas explícitas T2/T4.

---

## Control de Calidad #2

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 05/11/2026 |
| **Responsable** | Sebastian Tomasini (ST) |
| **Estado** | Pendiente |
| **Jira** | [PIAPARTES-58](https://paqsystems.atlassian.net/browse/PIAPARTES-58) |
| **Origen** | Prueba manual post piloto TR-015 (lab 2026-11-04): wizard Prompt-to-Report operativo al **iniciar** un informe; gap al **editar** un `.repx` existente |
| **Proceso** | Diseño de informes GEN-15 · ruta `/emisiones/disenador` · permiso `emission.design` · proceso piloto `partes.informes.consultaDetallada` |
| **Trazabilidad base** | [SPEC-015](../05-open-spec/100-SistemaPartes/SPEC-015-ia-diseno-informes.md) §4.4 **#6** · [HU-015](../03-historias-usuario/100-SistemaPartes/HU-015-ia-diseno-informes.md) · [TR-015](../04-tareas/100-SistemaPartes/TR-015-piloto-dx-reporting-ia.md) · F1 [D-VERIFICACION-TR-015](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-2026-10-30.md) |

### Resumen del hito

| # | Ítem | Notas |
|---|------|--------|
| 1 | **Prompt-to-Report en edición** | Incorporar (o exponer en web) asistencia IA conversacional cuando el usuario abre un informe **ya guardado** en el diseñador y pide cambios incrementales (p. ej. subtotales, formato, filtros visuales), **sin** depender únicamente del flujo de **informe nuevo** / wizard de creación. |
| 2 | **Fuera de alcance de este hito** | Sustituir Save / Save As GEN-15; Emitir (SPEC-011); oleada 2 Smart Capture (TR-016); unificación BYOK GEN-16 ↔ sidecar. |

### Hallazgos

En el piloto oleada 1 (sidecar `dx-reporting` + `AddPromptToReportConverter`) la generación Prompt-to-Report **completó** al crear un layout desde cero (evidencia lab «Report prueba ia 1»). En cambio, el escenario **SPEC §4.4 #6** — modificar un informe **ya abierto** con un prompt del tipo «Agregá subtotal de horas» — permanece **pendiente** en la batería F1 y es el driver documentado para veredicto **A vs B/C** (SPEC-015 §5).

DevExpress documenta Prompt-to-Report en Web Report Designer orientado al **wizard de creación**; la API `GeneratePromptToReportAsync` sobre `report` existente no está garantizada en web sobre REPX ya cargado ([SPEC-015 A1](../05-open-spec/100-SistemaPartes/SPEC-015-ia-diseno-informes-A1-ambiguity-review.md) **D-A1-01**). El producto debe **cerrar el gap operativo** para el diseñador Partes: mismo permiso y schema semántico del proceso, flujo «abrir variante → pedir cambio → revisar → Save».

### Mejoras solicitadas

#### SPEC-016 — Prompt-to-Report sobre informe existente

*Ciclo SDD iniciado desde A0/A* · [SPEC-016](../05-open-spec/100-SistemaPartes/SPEC-016-prompt-to-report-edicion-informes.md) · Jira [PIAPARTES-58](https://paqsystems.atlassian.net/browse/PIAPARTES-58)

- Al editar un informe **ya confeccionado** (variante `.repx` del catálogo GEN-15 del proceso emisible), el usuario con IA habilitada en sidecar debe poder **invocar Prompt-to-Report** (o superficie equivalente acordada con DevExpress 26.1) **sobre el layout cargado**, no solo al crear un informe nuevo.
- El prompt debe respetar el **schema semántico** del proceso (`partes.informes.consultaDetallada` en piloto; extensible por proceso sin hardcode C# por producto).
- Tras la propuesta de la IA, el usuario **revisa** en el diseñador y persiste con **Save** o **Save As** (R-ID-03 SPEC-015); sin auto-guardado silencioso.
- **No** exigir paridad WinForms en este hito; sí evidencia reproducible del escenario **#6** (y regresión: creación nueva sigue operativa).

### Criterios de aceptación preliminares

- [ ] **CA-ST2-01** Con un `.repx` existente abierto en `/emisiones/disenador`, el operador accede a asistencia Prompt-to-Report (o UI acordada) **sin** tener que iniciar un informe en blanco.
- [ ] **CA-ST2-02** Prompt de ejemplo SPEC **#6** («Agregá subtotal de horas» o equivalente sobre campos del schema) produce cambio visible revisable en el diseñador.
- [ ] **CA-ST2-03** **Save** / **Save As** persisten la variante vía callbacks GEN-15 existentes; catálogo coherente.
- [ ] **CA-ST2-04** Flujo de **informe nuevo** (wizard / creación) **no** regresa; batería §4.4 #1–#2 siguen válidos.
- [ ] **CA-ST2-05** Sidecar sin credencial LLM: comportamiento degradado documentado (sin wizard IA), sin romper edición manual.
- [ ] **CA-ST2-06** Evidencia F1: capturas + REPX muestra + entrada en runbook/evidence Framework si aplica cambio sidecar.

### Derivación SDD

| Artefacto | Ruta |
|-----------|----------------------------|
| SPEC-016 | `docs/05-open-spec/100-SistemaPartes/SPEC-016-prompt-to-report-edicion-informes.md` |
| HU / TR | Se generarán en las Partes B/C después de A1 |

Siguiente paso: Parte **A1**, revisión de ambigüedad de SPEC-016.

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
