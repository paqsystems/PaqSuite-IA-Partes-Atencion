# HU-011 – Reportes / emisiones desde Consulta detallada

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-011 |
| Título | Diseñar informes y emitir salidas documentales de Consulta detallada (adopción GEN-15) |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-08-25 (CC Q13 CA-15) |
| SPEC origen | [SPEC-011-reportes-emisiones](../../05-open-spec/100-SistemaPartes/SPEC-011-reportes-emisiones.md) |
| TR relacionada(s) | [TR-011-reportes-emisiones](../../04-tareas/100-SistemaPartes/TR-011-reportes-emisiones.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-011 | Dónde en esta HU |
|--------------------------------|------------------|
| Adopta GEN-15; no redefine motor (R-EM-01/02) | Alcance; Fuera de alcance; R-EM-01/02 |
| Único proceso Must `partes.informes.consultaDetallada` (R-EM-22, D-EM-07) | Alcance; CA-01; R-EM-22 |
| Authz emitir = menú `partes_consulta_detallada` (R-EM-10, D-EM-01) | Actores; CA-06; R-EM-10 |
| Flags consolidado sí / segmentado no / preview no obligatoria (D-EM-04/05, R-EM-23) | Alcance; CA-01, CA-03, CA-09; R-EM-23 |
| Canales PDF, impresión, Excel/CSV de reporte, mail+PDF; sin ZIP (D-EM-10) | Alcance; CA-08, CA-11; R-EM-04 |
| Dataset = filtros de pantalla + rol + `es_tarea`; todas las filas; no grilla/pivot/chooser/agrupación (R-EM-05…08, A1 Q1–Q2) | CA-07; R-EM-05…08 |
| Atributos de negocio §4.4 (fecha, cliente, asistente, tipo, duración, marcas, cerrado, observación, ERP) | CA-07 |
| Servidor revalida; filtros no amplían; snapshot no es sesión (R-EM-08) | CA-07; R-EM-08 |
| Impresión = mismo PDF + diálogo FE (§4.3) | CA-08 |
| Preview no obligatoria; no bloquea emitir (D-EM-05) | CA-03 |
| Umbrales `Emission*` defaults GEN 5 / 2000 / 30; sin hardcode; no pisar numéricos ABM (§4.6 / §7) | Alcance; Supuestos |
| Tests Feature + unit FE + E2E humo (§5) | CA-21 |
| Controles DX / i18n-testid GEN (§4.5, A1) | CA-03; Alcance |
| Usuario sin identidad Partes usable (§3) | Actores |
| Duración minutos → `hh:mm` en reporte (R-EM-05) | CA-07 |
| Vacío → Emitir visible y disabled (R-EM-06, D-EM-08) | CA-04 |
| Emitir embebido en Consulta detallada; no menú aparte (R-EM-09) | Alcance; CA-03; R-EM-09 |
| Convive Excel de grilla (R-EM-04, D-EM-09) | CA-10 |
| `EmissionEnabled` Sí al adoptar; No → no monta Emitir ni diseñador (R-EM-14, D-EM-06) | Precondiciones; CA-05 |
| Async / bitácora GEN; sin historial Partes (R-EM-12/13) | Alcance; CA-12 |
| Diseñar = `emission.design` + desktop (R-EM-15…20, D-EM-01…03); selección proceso GEN Q13 | Actores; CA-13…16 |
| Menú diseñador Parámetros `/emisiones/disenador` (§4.8) | CA-13 |
| Diseñador = page GEN con lista+confirmación; seed Must = Consulta detallada (§4.8 CC) | CA-15 |
| MONO sin selector grupo (R-EM-23, A1 Q3) | CA-09 |
| Mobile sin Emitir ni diseñador (R-EM-06 mobile / §4.9) | Fuera de alcance; CA-17 |
| Otras pantallas Informes/dashboard sin Emitir (R-EM-22) | Fuera de alcance; CA-18 |
| Menú `tipo_proceso = C`; no `E` (R-EM-03) | CA-19 |
| Manual usuario | CA-20 |
| SP MUST (R-EM-21) | Alcance; R-EM-21 |
| Criterios verificables SPEC §5 | CA-01…21 |

---

## Narrativa

Como usuario de Partes con acceso a Consulta detallada  
quiero emitir un PDF, una impresión, un Excel/CSV de reporte o un mail con PDF del universo que consulté  
para entregar un documento formal sin confundirlo con el export de la grilla.

Como usuario con permiso de diseñar reportes  
quiero ajustar el layout del informe de Consulta detallada en desktop  
para cambiar la presentación sin pedir un desarrollo.

---

## Contexto funcional

Tras registrar tareas, Consulta detallada permite leer la dedicación. Esta historia **adopta** el motor GEN de reportes/emisiones: Partes declara **qué** proceso emite, **qué datos** manda y **qué canales** habilita; el Framework resuelve **cómo** se diseña el layout y **cómo** se genera la salida. El primer y único proceso Must es Consulta detallada. Diseñar y emitir son permisos distintos: emitir no exige diseñar.

El dataset **no** es «lo visible en pantalla»: es el universo de **filtros de pantalla** (periodo, cliente, asistente si el perfil lo permite, tipo, cerrado) más el recorte de rol y solo tareas. La paginación, el filter-row, la agrupación, el column chooser y el pivot **no** recortan el documento.

### Precondiciones (SPEC §3)

- Sesión Partes usable (SPEC-002 / HU-002).
- Consulta detallada operativa (filtros de pantalla, empty, grilla/pivot).
- Capacidad GEN de emisiones disponible en la instalación.
- Para **montar** Emitir y el diseñador: parámetro `EmissionEnabled = Sí` (al adoptar, Partes lo deja en Sí).

### Actores

| Actor | Diseñar | Emitir en Consulta detallada |
|-------|---------|------------------------------|
| Usuario con menú Consulta detallada, sin `emission.design` | No | Sí (si la capacidad está encendida) |
| Usuario con `emission.design` y menú del diseñador | Sí (desktop) | Sí, si además tiene el menú de la consulta |
| Cliente / asistente / supervisor | Según menú y `emission.design` | Según menú Informes; universo: cliente = su organización; asistente no supervisor = sus tareas; supervisor = universo supervisor |
| Cliente por defecto | El seed **no** le da `emission.design` | Puede emitir su organización si tiene el menú |
| Usuario sin identidad Partes usable | No opera el módulo | N/A |
| Mobile / native | No | No |

---

## Alcance incluido

- Declarar por seed el proceso emisible **Consulta detallada** (código `partes.informes.consultaDetallada`, menú host `partes_consulta_detallada`, ruta `/partes/informes/consulta-detallada`): un documento consolidado, **sin** segmentado, vista previa **no** obligatoria (opcional en desktop, no bloquea), canales Must, un reporte principal y una plantilla mail principal. Alta solo por seed (sin ABM web del catálogo).
- Al adoptar: dejar `EmissionEnabled = Sí`. Umbrales GEN sin override Partes: `EmissionAsyncMaxMB = 5`, `EmissionAsyncMaxRows = 2000`, `EmissionArtifactRetentionDays = 30`. No hardcodear umbrales; no pisar valores numéricos ya editados en ABM. Edición posterior = parámetros Programa `Emission`.
- Reuso GEN de async, bitácora `source=emission`, bandeja y purga de artefactos. Sin historial propio de Partes.
- En Consulta detallada **web**: acción **Emitir** en la toolbar de la pantalla (visible en grilla y en vista pivot) que abre la ventana GEN de emisión (controles DevExtreme). No es un ítem de menú aparte. Colocación exacta → TR.
- Dataset = filtros **de pantalla** + perfil + `es_tarea`; **todas** las filas; duración técnica en minutos y **`hh:mm`** en el documento. Atributos: fecha, cliente (código + nombre), asistente (código + nombre), tipo (código + descripción), duración, sin cargo, presencial, cerrado, observación, Erp Cliente, Erp Articulo. Filter-row, filtros de columna, agrupación, column chooser, pivot y página **no** recortan. El servidor revalida; los filtros no amplían el universo. El snapshot viaja con preview/job (no solo «último filtro en sesión»).
- Canales: PDF, impresión (mismo PDF + diálogo de impresión), Excel de reporte, CSV de reporte, mail con cuerpo **breve** + PDF (sin tabla enorme en el correo). Destinatarios: el usuario los indica en la ventana.
- Convive con exportar Excel/CSV de **grilla**.
- Diseñador desktop: ítem **Parámetros** `partes_disenador_emisiones` → `/emisiones/disenador` (`tipo_proceso = C`), anclado al único proceso Must; exige `emission.design`. Sin ese permiso, no abre (autorización GEN). El menú se asigna a roles que deban diseñar; el seed no lo da al cliente por defecto.
- i18n e identificadores de prueba del canal GEN (efectivo `emissions.*`); copy Partes solo toolbar/empty si hace falta.
- Manual de usuario: cómo emitir y quién puede diseñar.
- Solo web para esta capacidad.

---

## Fuera de alcance

- Redefinir la ventana Emitir, el diseñador o el motor GEN.
- Emitir desde consultas agrupadas, Paquete de horas, dashboard, carga diaria o kardex mobile.
- ABM web del catálogo de procesos emisibles.
- Permisos por reporte (usar/editar/eliminar/compartir).
- ZIP / lotes segmentados.
- Formatos de integración / tipo de menú `I` o `E`.
- Unificar o quitar el Excel de grilla.
- Informe de facturación / ERP.
- Consolidado multi-empresa / selector de grupo.
- Emisiones recurrentes programadas.
- Destinatarios de mail calculados automáticamente (`resolveMailRecipients`).
- `resolveSegments` (el proceso no declara segmentado).
- Diseñador, preview o Emitir en mobile.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-EM-01 | Partes adopta el motor GEN de emisiones; no lo reimplementa. |
| R-EM-02 | El universo (rol, tareas, filtros de pantalla) lo resuelve Partes; el motor solo orquesta salidas. |
| R-EM-03 | Informes y diseñador usan tipo de menú consulta (`C`); no se usa `E`. |
| R-EM-04 | Exportar la grilla ≠ Emitir; conviven en Consulta detallada. |
| R-EM-05 | Dataset técnico de duración: **minutos**. En el documento: **`hh:mm`**. |
| R-EM-06 | Sin filas en el universo consultado, Emitir se ve y queda deshabilitado. |
| R-EM-07 | No se diseñan campos que el proceso no exponga. |
| R-EM-08 | Snapshot de filtros de pantalla al previsualizar o emitir; el servidor **revalida**; los filtros **no amplían** el universo; no usar «último filtro en sesión» como única fuente. |
| R-EM-09 | Emitir vive en Consulta detallada (grilla y pivot); no es menú aparte. |
| R-EM-10 | Quien entra a Consulta detallada puede emitir; no hace falta diseñar ni ser supervisor. |
| R-EM-11 | El cliente solo emite datos de su organización. |
| R-EM-12 | Emisiones pesadas van en segundo plano; el aviso es la bandeja GEN, no un inbox de Partes. |
| R-EM-13 | Cada emisión queda en la bitácora general; Partes no tiene historial propio. |
| R-EM-14 | Con capacidad apagada no se ofrece Emitir ni diseñador. |
| R-EM-15 | Diseñar exige permiso de diseño y desktop. |
| R-EM-16 | Reporte y plantilla pertenecen al proceso; no se diseñan sueltos. |
| R-EM-17 | Un reporte principal y una plantilla mail principal por proceso. |
| R-EM-18 | Sin permiso de diseño no se abre el diseñador. |
| R-EM-19 | Hay un layout y una plantilla iniciales; luego se ajustan en el diseñador. |
| R-EM-20 | El seed no da permiso de diseño al rol cliente por defecto. |
| R-EM-21 | La resolución de datos de negocio va por SP (MUST). |
| R-EM-22 | El único proceso emisible Must es Consulta detallada. |
| R-EM-23 | Un solo documento del universo; en esta instalación no hay selector de grupo de empresas. |

---

## Criterios de aceptación

- [ ] **CA-01** El proceso Consulta detallada queda declarado (seed) con canales Must (PDF, impresión, Excel de reporte, CSV de reporte, mail+PDF; sin ZIP), un documento consolidado, sin segmentado, preview no obligatoria, `menu_process_code = partes_consulta_detallada`, un reporte principal y una plantilla mail principal.
- [ ] **CA-02** Con capacidad encendida, un usuario con menú de Consulta detallada puede consultar los metadatos del proceso (canales y flags acordes) y emitir. Si falta el puerto de datos del proceso, la emisión se rechaza.
- [ ] **CA-03** En `/partes/informes/consulta-detallada` (web), con capacidad encendida y con filas, existe la acción **Emitir** (grilla y pivot) que abre la ventana GEN (`emissions.*`); no hay ítem de menú aparte. La vista previa, si se ofrece, es **opcional** y **no** impide emitir.
- [ ] **CA-04** Con 0 filas en el universo consultado, Emitir sigue visible y está deshabilitado.
- [ ] **CA-05** Con `EmissionEnabled = No` no se monta Emitir ni el diseñador; no se puede operar la capacidad.
- [ ] **CA-06** Un usuario con menú de la consulta y **sin** permiso de diseño puede emitir al menos un PDF y **no** abre el diseñador.
- [ ] **CA-07** El documento respeta perfil (cliente = su org; asistente no supervisor = sus tareas; supervisor = universo supervisor) + filtros de pantalla (periodo, cliente, asistente si aplica, tipo, cerrado) + solo tareas; incluye **todas** las filas (no la página); filter-row, columna, agrupación, column chooser y pivot **no** reducen el documento; campos §4.4 presentes; duración en `hh:mm`. Ampliar filtros en cliente **no** trae filas de otro universo (el servidor revalida).
- [ ] **CA-08** El usuario puede elegir PDF, impresión (mismo PDF + diálogo de impresión), Excel de reporte, CSV de reporte y mail+PDF. No se ofrecen ZIP ni modo segmentado.
- [ ] **CA-09** No aparece selector de grupo de empresas. La salida es un único documento del universo filtrado.
- [ ] **CA-10** El export Excel/CSV de **grilla** sigue disponible junto a Emitir.
- [ ] **CA-11** Canal mail: cuerpo breve + PDF adjunto (sin tabla enorme en el correo); destinatarios indicados en la ventana (sin lista automática). Si GEN no muestra el campo, no se inventa otra pantalla (gap a TR).
- [ ] **CA-12** Una emisión correcta deja evento en bitácora general (`source=emission`); no hay pantalla/tabla de historial Partes. Emisión pesada (umbrales GEN 5 MB **o** 2000 filas): aviso por bandeja GEN, no inbox Partes.
- [ ] **CA-13** Ítem de menú diseñador bajo Parámetros, código `partes_disenador_emisiones`, ruta `/emisiones/disenador`, tipo `C`; native no lo expone.
- [ ] **CA-14** Sin `emission.design` el diseñador no se abre (autorización GEN, p. ej. 4709). Emitir sin menú de la consulta se deniega (autorización del proceso host, p. ej. 4703).
- [ ] **CA-15** La página del diseñador monta la superficie GEN (`EmissionReportDesignerPage`) **sin** hardcodear `processCode`. Con el seed Must (un solo proceso activo `partes.informes.consultaDetallada`) la lista de procesos es visible (unitaria) y el DX no opera hasta confirmar el proceso; tras confirmar, schema/reportes = ese proceso. Seed tabular cubre atributos §4.4; un principal de reporte y uno de plantilla mail. Estética fina (márgenes, totales) → diseñador post-seed / TR, no bloquea Must. Opcional: `initialProcessCode` / `?processCode=` solo preselecciona (no omite confirmación).
- [ ] **CA-16** Quien tiene `emission.design` y el menú del diseñador puede diseñar aunque no tenga menú de Consulta detallada.
- [ ] **CA-17** En mobile no hay Emitir de Consulta detallada, ni diseñador, ni preview, ni emisor documental en kardex / Paquete de horas / dashboard.
- [ ] **CA-18** Consultas agrupadas, Paquete de horas y dashboard **no** ofrecen Emitir.
- [ ] **CA-19** El ítem nuevo de menú no usa tipo `E`.
- [ ] **CA-20** El manual de usuario explica cómo emitir desde Consulta detallada y quién puede diseñar.
- [ ] **CA-21** Hay pruebas automáticas (API/puerto-seed + unitarios de pantalla + E2E humo) que cubren al menos: emitir PDF con datos, Emitir deshabilitado sin filas, convive export de grilla, diseñador con permiso denegado.

### Escenarios Gherkin

```gherkin
Feature: Emisión documental en Consulta detallada
  Como usuario con acceso a Consulta detallada
  Quiero emitir un documento del universo consultado
  Para entregar PDF, Excel de reporte o mail sin usar el export de grilla

  Scenario: Emitir PDF del universo de pantalla
    Given un usuario con menú de Consulta detallada y capacidad de emisión encendida
    And filtros de pantalla que devuelven varias tareas (más de una página de grilla)
    When abre Emitir y elige PDF
    Then obtiene un documento con todas esas tareas (no solo la página)
    And figuran fecha, cliente, asistente, tipo, duración en hh:mm, marcas, cerrado, observación y referencias ERP si existen
    And queda un evento en la bitácora general de emisiones
    And puede emitir sin haber usado vista previa

  Scenario: Sin datos o capacidad apagada
    Given la consulta filtrada no tiene filas
    Then la acción Emitir se ve y está deshabilitada
    Given EmissionEnabled en No
    When el usuario entra a Consulta detallada
    Then no se ofrece Emitir ni el diseñador

  Scenario: Pivot y filter-row no recortan; convive export de grilla
    Given un universo de 20 tareas según filtros de pantalla
    And el usuario aplica un filtro de columna o cambia a vista pivot
    When emite
    Then el documento incluye las 20 tareas
    And el export de grilla sigue disponible junto a Emitir

  Scenario: Permisos de emitir y de diseñar son distintos
    Given un usuario con menú de Consulta detallada y sin permiso de diseño
    When emite un PDF
    Then la emisión se completa
    And no puede abrir el diseñador
    Given un usuario cliente
    When emite Consulta detallada
    Then el documento no incluye tareas de otros clientes

  Scenario: Mail e impresión
    Given Consulta detallada con filas y capacidad encendida
    When elige impresión
    Then la salida es el mismo PDF con diálogo de impresión
    When elige mail
    Then el correo lleva cuerpo breve y PDF adjunto
    And los destinatarios se indican en la ventana (sin lista automática)

  Scenario: Diseñador desktop y exclusión mobile / otras pantallas
    Given un usuario con permiso de diseño y el menú del diseñador
    When abre Parámetros / diseñador de emisiones
    Then ve la lista GEN de procesos emisibles (aunque haya uno solo)
    And el diseñador DX no opera hasta confirmar el proceso
    When confirma el proceso Consulta detallada
    Then trabaja el layout / schema de ese proceso
    And no está disponible en mobile
    Given Consultas agrupadas, Paquete de horas, dashboard o kardex native
    Then no aparece Emitir de este proceso
```

---

## Supuestos explícitos

- El motor GEN-15 (ventana Emitir, diseñador, bitácora, async, purga) está en los paquetes del host; esta HU no reabre HU/TR-GEN-15.
- Consulta detallada web ya existe (SPEC-006 / HU-006) como superficie de montaje.
- Instalación MONO: sin grupo de empresas.
- Códigos estables: proceso `partes.informes.consultaDetallada`, menú consulta `partes_consulta_detallada`, menú diseñador `partes_disenador_emisiones`, rutas `/partes/informes/consulta-detallada` y `/emisiones/disenador`.
- Prefijo de textos e identificadores = efectivo GEN (`emissions.*` en smoke); no un segundo catálogo Partes salvo toolbar/empty.
- Loading de la consulta: **Should** deshabilitar Emitir hasta haber resultado; **Must** = 0 filas → disabled (A1).
- Al adoptar, Partes **enciende** `EmissionEnabled`; no pisa umbrales numéricos ya editados en ABM.
- Layout inicial Must = tabular con schema §4.4; estética fina → diseñador / Should TR.
- Colocación pixel del botón Emitir, JSON de filtros, nombres SP/columnas, roles con `emission.design` (además de no cliente), versión de paquetes, DX real vs stub y UI `mailTo` → TR. Canal mail sigue Must; sin ventana paralela.

---

## Preguntas abiertas

| # | Pregunta | Destino | Resolución |
|---|----------|---------|------------|
| 1 | Shape JSON del snapshot de filtros / extensión del job GEN | TR-011 §1 / §5 | Cerrado: `hostContext` camelCase en preview/jobs; puerto lee request si el DTO GEN no lo propaga |
| 2 | SP y nombres canónicos de columnas del dataset | TR-011 §1 | Cerrado: `pq_sp_partes_tarea_list` + `p_page_size=0`; columnas camelCase de la grilla TR-006 |
| 3 | Roles concretos con `emission.design` (además de no cliente) | TR-011 §1 / C1 | Cerrado: SUPERVISOR sí; CLIENTE y ASISTENTE no |
| 4 | Gap UI `mailTo` y runtime DX real vs stub | TR-011 §1 / C1 | Cerrado: mail Must síncrono; sin `MailRecipients`; async mail = gap GEN si el worker pierde `mailTo` |
| 5 | Estética del layout DX inicial (márgenes, agrupación, totales) | TR-011 §1 | Cerrado: Must = tabular columnas §1; estética fina → diseñador post-seed |

Ninguna bloquea Parte C.

---

## Riesgos de ambigüedad

| Riesgo | Mitigación en SPEC/HU |
|--------|------------------------|
| Confundir Emitir con export de grilla | CA-10 / R-EM-04 / D-EM-09 |
| Emitir solo la página o lo visible en pivot/chooser | CA-07 / A1 Q1–Q2 |
| Montar selector de grupo por `permiteConsolidado` | CA-09 / R-EM-23 |
| Exigir diseñar para emitir | CA-06 / D-EM-01 |
| Inventar historial de emisiones | CA-12 / R-EM-13 |
| Emitir en agrupadas / mobile / kardex | CA-17, CA-18 |
| Tabla enorme en el cuerpo del mail | CA-11 |
| Ampliar universo con filtros de UI | CA-07 / R-EM-08 |
| Preview obligatoria por error de flag | CA-01, CA-03 / D-EM-05 |

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-25 | Parte B: HU-011 desde SPEC-011 (post A1). |
| 2026-08-25 | Parte B1: atributos §4.4, impresión=PDF, preview opcional, umbrales, revalidación servidor, CA-21 tests, Gherkin 6 escenarios, actores perfil. |
| 2026-08-25 | CC Q13: CA-15 deja proceso fijo; monta `EmissionReportDesignerPage` con lista GEN (N=1 visible) + confirmación. |
| 2026-08-25 | Parte C: enlazada TR-011; preguntas abiertas cerradas en la TR. |
| 2026-08-25 | Parte C1: TR-011 apta con observaciones (hostContext/jobId, pageSize 0, 4704). |
