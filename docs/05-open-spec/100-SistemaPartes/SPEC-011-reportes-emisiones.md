# SPEC-011 – Reportes / emisiones — adopción GEN-15 en Partes

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-011 |
| Título | Reportes / emisiones — diseñar informes y emitir desde Consulta detallada (adopción GEN-15) |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-08-25 (CC Q13 selección proceso GEN) |
| Revisión A1 | Apto con observaciones — ambigüedades críticas de universo/MONO cerradas (2026-08-25) |
| HU relacionada(s) | [HU-011-reportes-emisiones](../../03-historias-usuario/100-SistemaPartes/HU-011-reportes-emisiones.md) |
| TR relacionada(s) | [TR-011-reportes-emisiones](../../04-tareas/100-SistemaPartes/TR-011-reportes-emisiones.md) |
| Depende de | [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md) (sesión y delimitación por rol); [SPEC-006](./SPEC-006-consultas-dashboard-navegacion.md) (Consulta detallada: filtros, columnas, empty); [SPEC-007](./SPEC-007-mobile-capacitor.md) (exclusión native de esa pantalla); Framework GEN-15 / SPEC-001-15 (docs en `PaqSuite-IA-FRAMEWORK`) |
| Fuentes | [`15-reportes-emisiones.md`](../../02-producto/Sistema-Partes-IA/15-reportes-emisiones.md) (D-EM-01…10); [`06-consultas-dashboard-y-navegacion.md`](../../02-producto/Sistema-Partes-IA/06-consultas-dashboard-y-navegacion.md); [`10-mobile.md`](../../02-producto/Sistema-Partes-IA/10-mobile.md); Framework [`15-reportes-emisiones.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones.md) / [SPEC-001-15](../../../PaqSuite-IA-FRAMEWORK/docs/05-open-spec/001-Generalidades/SPEC-001-15-reportes-emisiones.md) |

---

## 1. Resumen ejecutivo

- **Problema:** el usuario de Partes necesita **salidas documentales** (PDF, impresión, Excel de reporte, mail+PDF) del mismo universo que ve en **Consulta detallada**, y el equipo de diseño necesita **ajustar layouts** sin reescribir el motor.
- **Resultado esperado:** Partes **adopta** el motor GEN-15 (`@paqsuite/react-core` + `paqsuite/laravel-core`): seed del proceso emisible, puerto **dataset**, reporte/plantilla **iniciales**, ventana **Emitir** en Consulta detallada, y diseñador DX **desktop** con `emission.design`. **No** redefine orquestador, ventana Emitir ni runtime DX.

---

## 2. Alcance

### 2.1 En alcance

- Checklist de adopción GEN-15 §15 aplicado al host Partes (MONO / `tenancy=single`).
- Seed/SQL del **único** proceso emisible Must: código **`partes.informes.consultaDetallada`**, vinculado al menú host **`partes_consulta_detallada`**.
- Flags del proceso: `permiteConsolidado = sí`; `permiteSegmentado = no`; `requiereVistaPrevia = no`; canales §4.3.
- Puerto **`resolveDataset`** Must: mismo universo que la grilla de Consulta detallada (SPEC-006 §4.2) en ese momento; duración técnica en **minutos**; presentación en reporte **`hh:mm`**.
- Seed de **un reporte DX principal** y **una plantilla mail principal** (canal mail activo).
- Parámetros Programa `Emission`: adoptar diccionario GEN; al adoptar, el host deja **`EmissionEnabled = Sí`**. Umbrales async y retención = defaults GEN (sin override Partes).
- Montar **`EmissionDialog`** en `/partes/informes/consulta-detallada` (toolbar / acción explícita). Emitir = permiso de **entrar** a esa opción de menú.
- Superficie **desktop** de diseño: page GEN **`EmissionReportDesignerPage`** (selección de proceso Must + reportes + DX) + APIs `/api/v1/emissions/design/...`, permiso GEN **`emission.design`**. Acceso: ítem de menú §4.8. Partes **no** inventa picker ni hardcodea `processCode` (SPEC-001-15 Q13 / C1-15-36..39).
- Empty: Emitir **visible y deshabilitado** si no hay filas (D-EM-08; mismo criterio que exportar sin datos).
- Convivencia con Excel/CSV de **grilla** (GEN-11 / SPEC-006): no se elimina en v1 (D-EM-09).
- Async / bitácora / bandeja / purga: **reuso GEN** (`source=emission`; `EMISSION_BATCH`; `EMISSION_ARTIFACT_PURGE`). Partes no crea historial propio.
- i18n + `data-testid` prefijo GEN **`emission.*`**; claves Partes solo si el host necesita copy propio (empty/toolbar).
- Actualizar manual de usuario Partes: cómo Emitir desde Consulta detallada y quién puede diseñar.
- Policy mobile: **no** montar Emitir ni diseñador en native (esta pantalla ya está denegada; §4.9).

### 2.2 Fuera de alcance

- Redefinir el motor GEN (ventana Emitir, orquestación, DX Reporting, async, writers, envelope `4700–4799`).
- Emitir desde consultas agrupadas, Paquete de horas, dashboard, carga diaria o kardex mobile.
- ABM web del catálogo de procesos emisibles.
- Matriz de permisos **por reporte** (usar/editar/eliminar/compartir).
- ZIP documental / lotes segmentados; `permiteSegmentado`.
- Formatos de integración / menú `tipo_proceso = I`.
- Reintroducir `tipo_proceso = E`.
- Unificar o deprecar el Excel de grilla.
- Informe de facturación / ERP.
- Consolidado multi-empresa / grupos (`tenancy=single`: feature off).
- Emisiones **recurrentes** (handler propio en catálogo `13`) en este alcance.
- `resolveSegments` (el proceso no declara segmentado).
- Destinatarios de mail calculados por dataset (`resolveMailRecipients`): v1 = **manual** en la ventana (`mailTo`).
- Diseñador o preview en mobile.

---

## 3. Actores y contexto

| Actor | Diseñar (`emission.design` + desktop) | Emitir en Consulta detallada |
|-------|----------------------------------------|------------------------------|
| Usuario con menú `partes_consulta_detallada` y **sin** `emission.design` | No | Sí (si `EmissionEnabled = Sí`) |
| Usuario con `emission.design` **y** menú de la consulta | Sí | Sí |
| Cliente / asistente / supervisor | Según menú Informes | Según menú Informes; el **universo** sigue SPEC-002 |
| Usuario sin vínculo Partes usable | No opera el shell (SPEC-002) | N/A |
| Mobile / native | **No** | **No** (Consulta detallada no es pantalla mobile) |

**Precondiciones**

- Sesión Partes usable (SPEC-002).
- Consulta detallada operativa (SPEC-006): filtros, columnas, empty, `ProcessDataGrid`.
- Capacidad GEN-15 disponible en los paquetes del host (supuesto de adopción).
- `EmissionEnabled = Sí` para montar Emitir; con **No**, no se monta Emitir ni diseñador (norma GEN).

---

## 4. Comportamiento funcional

### 4.1 Adopción vs. invención (Must)

| ID | Norma |
|----|--------|
| R-EM-01 | Partes **adopta** GEN-15; no copia ni bifurca orquestador, `EmissionDialog`, diseñador ni familia `/api/v1/emissions/`. |
| R-EM-02 | Reglas de negocio de partes (universo, `es_tarea`, filtros) viven en el **puerto** y en la pantalla host; no en el orquestador GEN. |
| R-EM-03 | Menú Informes permanece `tipo_proceso = C`. No se usa `E`. |
| R-EM-04 | Exportar Excel de **grilla** ≠ Emitir. Conviven en Consulta detallada (D-EM-09). |

### 4.2 Proceso emisible Must (D-EM-07)

| Campo | Valor cerrado |
|-------|----------------|
| Código | **`partes.informes.consultaDetallada`** (alineado al `proceso` de grilla ya usado en FE) |
| Menú host (authz emitir) | **`partes_consulta_detallada`** |
| Ruta pantalla | `/partes/informes/consulta-detallada` |
| `permiteConsolidado` | **Sí** |
| `permiteSegmentado` | **No** |
| `requiereVistaPrevia` | **No** (preview opcional en desktop; no bloquea) |
| Alta catálogo | Solo **seed/SQL**; sin ABM web GEN v1 |

Otros informes (agrupadas, Paquete de horas) y el dashboard **no** montan Emitir en este SPEC.

### 4.3 Canales (D-EM-10 / D-EM-04)

| Canal GEN | Consulta detallada v1 |
|-----------|------------------------|
| PDF | **Sí** |
| Impresión (`print`) | **Sí** (desktop: mismo PDF + diálogo de impresión FE, norma GEN C1-15-11) |
| Excel de reporte | **Sí** |
| CSV de reporte | **Sí** (misma familia de salida de reporte que Excel; distinto del export de grilla) |
| Mail + PDF adjunto | **Sí** |
| ZIP | **No** |

Mail: cuerpo **breve** + PDF adjunto (Mail Engine propósito `documento`). No se diseña el parte como tabla enorme en el correo.

Destinatarios v1: el usuario los indica en la ventana Emitir (`mailTo` GEN). No hay Must de lista automática por cliente/asistente.

### 4.4 Dataset (D-EM-08)

El dataset es el **universo de negocio** de Consulta detallada **en el momento de preview o de disparar el job** (no un histórico completo sin filtros, no un universo inventado):

- solo `es_tarea = true`;
- mismas restricciones por perfil (cliente / asistente / supervisor) — **siempre en servidor** (SP MUST); los filtros de UI **acotan**, nunca amplían;
- mismos **filtros de pantalla** SPEC-006 §4.2: periodo, cliente, asistente (si aplica), tipo, cerrado (y los que esa pantalla ya exponga como filtro de consulta, no como recorte de grilla);
- **todas** las filas que cumplen ese universo (no solo la página actual de la grilla);
- mismos atributos de negocio: fecha, cliente (código + descripción/nombre), asistente (código + nombre), tipo (código + descripción), duración, sin cargo, presencial, cerrado, observación, Erp Cliente, Erp Articulo.

**No** es el conjunto «lo visible en pantalla» (D-EM-09): filter-row, filtros de columna, agrupación, column chooser ni **vista pivot** **no** recortan el dataset de emisión. Eso sigue siendo Excel/CSV de **grilla** (GEN-11/12). Emitir usa el layout **declarado** del proceso sobre el universo de filtros de pantalla.

| ID | Norma |
|----|--------|
| R-EM-05 | Duración en dataset técnico: **minutos**. Presentación en reporte: **`hh:mm`**. |
| R-EM-06 | Si el universo consultado tiene **0 filas**, Emitir permanece **visible** y **deshabilitado**. |
| R-EM-07 | El diseñador **no** agrega campos que el puerto no exponga (D-EM-02). |
| R-EM-08 | El FE envía al job/preview el **snapshot de filtros de pantalla** vigentes (no el estado visual de la grilla/pivot). El BE **revalida** universo + filtros. Shape JSON del snapshot → TR. Si el contrato GEN de `POST /jobs` no trae aún un campo genérico de contexto, el host lo aporta por extensión documentada en TR (sin saltarse el motor; **no** persistir el último filtro en sesión como única fuente). |

### 4.5 Ventana Emitir (Frente B)

| ID | Norma |
|----|--------|
| R-EM-09 | `EmissionDialog` se monta **en** Consulta detallada (acción explícita en la toolbar de la pantalla / `ProcessDataGrid`, convive con export GEN-11). No es ítem de menú aparte. Colocación pixel-perfect → TR; Must: visible en grilla y en vista pivot (el dataset no cambia al togglar pivot). |
| R-EM-10 | Quien puede **consultar** esa pantalla puede **emitir**, sujeto a `EmissionEnabled` y canales del proceso. No se exige `emission.design` ni ser supervisor (D-EM-01). |
| R-EM-11 | El cliente emite solo el universo de su organización (igual que la consulta). |
| R-EM-12 | Emisiones livianas: síncronas desde la ventana. Pesadas (umbrales `EmissionAsyncMaxMB` / `EmissionAsyncMaxRows`, criterio OR GEN): async; aviso por **bandeja** GEN, no un inbox de Partes. |
| R-EM-13 | Cada emisión deja evento en bitácora general (`source=emission`). Partes **no** crea tabla/historial propia. |
| R-EM-14 | `EmissionEnabled = No` → no se monta Emitir **ni** el diseñador; API GEN responde error `4700–4799`. |
| R-EM-23 | `permiteConsolidado = sí` en el **proceso** = modo **un documento** del universo filtrado (no segmentado). En MONO **no** se monta el selector de grupo empresario GEN-23 (prop/host del diálogo en off). |

Controles 100 % DevExtreme; tema shell A1.

### 4.6 Parámetros `Emission*` (D-EM-06)

| Clave | Norma Partes |
|-------|----------------|
| `EmissionEnabled` | Al adoptar, el seed/host Partes lo deja en **Sí**. (Default GEN de instalación virgen es No; este producto lo enciende.) |
| `EmissionAsyncMaxMB` | Default GEN **5** — no override Partes |
| `EmissionAsyncMaxRows` | Default GEN **2000** — no override Partes |
| `EmissionArtifactRetentionDays` | Default GEN **30** — no override Partes |

No hardcodear umbrales en código de producto. Edición posterior = ABM de parámetros (Programa `Emission`).

### 4.7 Diseño de informes (Frente A — D-EM-01…03)

| ID | Norma |
|----|--------|
| R-EM-15 | Diseñar exige **`emission.design`** + **desktop**. Emitir no exige diseñar. |
| R-EM-16 | Un reporte o plantilla pertenece **siempre** al proceso emisible. No se diseña «en el aire». |
| R-EM-17 | Un solo reporte **principal** y una sola plantilla mail **principal** por proceso (D-EM-03). |
| R-EM-18 | Sin `emission.design` → no se abre el diseñador (GEN **4709**). |
| R-EM-19 | El seed incluye layout DX inicial usable (tabular sobre el schema §4.4) y plantilla mail breve. El equipo con permiso ajusta layouts **sin** tocar código. |
| R-EM-20 | El seed **no** otorga `emission.design` al rol **cliente** por defecto. Asignación a roles de administración/diseño = detalle TR (seguridad). |

### 4.8 Acceso al diseñador (cierre Parte A)

Producto dejaba «menú o administración» al SPEC. Cierre:

| Campo | Valor |
|-------|--------|
| Superficie | Página desktop que monta **`EmissionReportDesignerPage`** (GEN); el host inyecta `renderDesigner` DX. **No** sustituir por un picker Partes ni por `ReportDesignerHost` con `processCode` fijo como única entrada. |
| Entrada | **Ítem de menú** propio, **no** embebido en Consulta detallada |
| Código de menú | **`partes_disenador_emisiones`** |
| Carpeta | **Parámetros** (`pq_menus` padre parámetros; no Informes) |
| Ruta | **`/emisiones/disenador`** |
| `tipo_proceso` | **`C`** |
| Visibilidad | Desktop; oculto/denegado en native (policy SPEC-007) |
| Autorización de uso | Permiso GEN **`emission.design`** (API design). El ítem de menú se asigna solo a roles que deban diseñar; sin el permiso GEN la API sigue en **4709**. |
| Selección de proceso | **Must GEN** (lista + confirmación; también si N=1). Seed Must de Partes = un proceso activo (`partes.informes.consultaDetallada`). Opcional: mapear `?processCode=` → `initialProcessCode` (solo preselecciona; **no** omite lista ni auto-confirma). |

El diseñador GEN lista los procesos emisibles activos del host. En este SPEC el seed Must aporta **solo** `partes.informes.consultaDetallada`; la UI igual muestra la lista unitaria y exige confirmación antes del DX (Q13). Futuros procesos emisibles Partes = otro seed/SPEC; no requieren reescribir el picker.

Quien tiene `emission.design` y el menú del diseñador puede diseñar **aunque** no tenga menú de Consulta detallada (emitir y diseñar son gates distintos, D-EM-01).

### 4.9 Mobile (especialización Partes vs GEN)

GEN-15 permite flujo corto de **emitir** en mobile. Este producto **no**: Consulta detallada es **web** (grilla/pivot); el kardex no es este proceso (D-EM-07 / `10-mobile.md`).

| Capacidad | Mobile Partes v1 |
|-----------|------------------|
| Diseñador | **No** |
| Preview | **No** |
| Emitir en Consulta detallada | **No** — ruta ya denegada (SPEC-007 / policy) |
| Emitir desde kardex / Paquete de horas / dashboard | **No** (evolución: otro proceso emisible) |

Una emisión corta GEN desde kardex, si se desea, es **otro** SPEC.

### 4.10 Reglas numeradas (resumen)

| ID | Regla |
|----|--------|
| R-EM-01…04 | Adopción GEN; negocio en puerto; menú `C`; convive Excel grilla |
| R-EM-05…08 | Dataset = consulta filtrada; hh:mm; vacío → Emitir disabled; snapshot filtros |
| R-EM-09…14, 23 | Emitir embebido; authz = menú consulta; async/bitácora GEN; flag capacidad; MONO sin selector grupo |
| R-EM-15…20 | Diseñar = `emission.design` + desktop; anclado a proceso; un principal; seed inicial |
| R-EM-21 | Persistencia/catálogo/jobs vía **SP** (MUST BASE / GEN-15). Puerto dataset vía SP. |
| R-EM-22 | Solo el proceso `partes.informes.consultaDetallada` es Must emisible. |

---

## 5. Criterios verificables

- [ ] Existe seed del proceso `partes.informes.consultaDetallada` con canales §4.3, flags §4.2 y `menu_process_code = partes_consulta_detallada`.
- [ ] `GET /api/v1/emissions/processes/partes.informes.consultaDetallada` devuelve esos metadatos (usuario con menú de la consulta).
- [ ] Puerto `resolveDataset` registrado; emitir sin puerto → rechazo de contrato GEN (p. ej. 4706).
- [ ] En Consulta detallada (web, `EmissionEnabled = Sí`, con filas) hay acción **Emitir** que abre `EmissionDialog` (`data-testid` `emission.*` / `emissions.*` GEN).
- [ ] Sin filas: Emitir visible y **disabled**.
- [ ] `EmissionEnabled = No`: no se monta Emitir; API de jobs no opera la capacidad.
- [ ] Usuario con menú de la consulta y **sin** `emission.design` puede emitir (al menos PDF) y **no** abre el diseñador.
- [ ] Dataset emitido respeta rol + filtros de **pantalla** + `es_tarea = true`; incluye **todas** las filas del universo (no solo la página); filter-row/pivot **no** recortan la emisión; duración en reporte en `hh:mm`.
- [ ] Canales ZIP y modo segmentado **no** se ofrecen para este proceso.
- [ ] Excel de **grilla** sigue disponible junto a Emitir.
- [ ] Mail documental = cuerpo breve + PDF; destinatarios capturados en la ventana.
- [ ] Ítem de menú `partes_disenador_emisiones` → `/emisiones/disenador`; native no lo expone; sin `emission.design` → 4709.
- [ ] La página monta `EmissionReportDesignerPage` **sin** `processCode` fijo; con seed de un solo proceso, la lista GEN es visible (unitaria) y el DX no opera hasta confirmar proceso; schema/reportes = `partes.informes.consultaDetallada`.
- [ ] Seed: un reporte principal + una plantilla mail principal.
- [ ] Una emisión OK deja evento bitácora `source=emission`; no hay pantalla/tabla de historial Partes.
- [ ] Consultas agrupadas, Paquete de horas y dashboard **no** montan Emitir.
- [ ] Mobile: Consulta detallada y diseñador siguen fuera de policy; no hay emisor documental en kardex en este SPEC.
- [ ] Menú nuevo no usa `tipo_proceso = E`.
- [ ] Manual de usuario documenta Emitir y diseñar.
- [ ] Tests (Feature puerto/seed + unit FE + E2E humo Consulta detallada) cubren al menos: emitir PDF con datos, disabled vacío, convive export grilla, diseñador gated.

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| DB / seed | Fila `PQ_EMISSION_PROCESSES` (+ reportes/plantillas); `EmissionEnabled = S` en adopción Partes; ítem `pq_menus` diseñador; no seedear ZIP/segmentado |
| Backend | Registrar puerto `resolveDataset` del proceso; SP de lectura alineada a informe consulta detallada (reutilizar familia SPEC-006 si existe); gate `partes_consulta_detallada` al emitir; no reimplementar `/emissions/*` |
| Frontend | Montar `EmissionDialog` / `useEmission` en `ConsultaDetalladaPage`; pasar snapshot de filtros; disabled sin datos; página con **`EmissionReportDesignerPage`** en `/emisiones/disenador` (sin hardcode de proceso; opcional `initialProcessCode`); `renderDesigner` DX del host; no montar en `isNativeApp()` |
| Mobile | Policy: deny diseñador + (ya deny) consulta detallada |
| Config | Programa `Emission`; umbrales GEN |
| Docs | Manual usuario; este SPEC; HU-011 / TR-011 |
| OpenAPI | Adopción de familia GEN; documentar extensión de contexto/filtros si el DTO host la añade |

Authz emitir: permiso menú **`partes_consulta_detallada`** → GEN **4703** si falta.  
Authz diseñar: **`emission.design`** → **4709**.

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| Motor GEN aún smoke (FakeDx / designer stub) | Supuesto: host consume paquetes versionados. TR declara gap si el runtime DX real no está en la versión pinneada y fija mínimo usable (PDF stub vs real). |
| `POST /jobs` GEN sin campo de filtros | R-EM-08: TR documenta extensión de contexto; el Must de negocio no se relaja. |
| `EmissionEnabled` GEN insert-if-absent = No | Adopción Partes **enciende** a Sí (D-EM-06); no pisar umbrales numéricos ya editados en ABM. |
| Confundir Excel grilla vs Excel emisión | R-EM-04; copy/toolbar distintos (`emission.*` vs export GEN-11). |
| Historial paralelo | Prohibido; bitácora `17`. |
| Usuario espera Emitir en mobile/kardex | Fuera de alcance; copy/manual. |
| Licencia DX Reporting | Dependencia GEN/host; TR de despliegue. |
| Layout visual exacto del reporte inicial | Must: schema §4.4 cubierto en tabular; estética fina = diseñador post-seed / Should TR. |

**Supuestos**

- SPEC-001-15 y HU/TR-GEN-15 son la norma del motor; este SPEC solo especializa el **host Partes**.
- Consulta detallada web (SPEC-006) ya existe como superficie de montaje.
- MONO: sin selector de grupo empresario.

---

## 8. Decisiones de producto absorbidas

| ID | Decisión |
|----|----------|
| D-EM-01 | Diseñar = `emission.design` + desktop; emitir = menú de la consulta |
| D-EM-02 | Reporte/plantilla siempre anclados a un proceso |
| D-EM-02b | Diseñador = superficie GEN multi-proceso (lista+confirmación, también N=1); host no hardcodea |
| D-EM-03 | Un principal por tipo (reporte / plantilla) y proceso |
| D-EM-04 | Consulta detallada: consolidado sí, segmentado no |
| D-EM-05 | Vista previa no obligatoria en v1 |
| D-EM-06 | `EmissionEnabled = Sí` al adoptar |
| D-EM-07 | Must: solo Consulta detallada |
| D-EM-08 | Dataset = consulta filtrada; vacío → Emitir deshabilitado |
| D-EM-09 | Excel de grilla y Excel de emisión conviven |
| D-EM-10 | Canales v1: PDF, impresión, Excel/CSV de reporte, mail+PDF |

**Cierre adicional Parte A (no estaba numerado en producto):** acceso al diseñador = menú `partes_disenador_emisiones` bajo Parámetros, ruta `/emisiones/disenador` (§4.8). Destinatarios mail v1 = manual en ventana.

**Cierres A1 (2026-08-25):** universo de emisión ≠ lo visible en grilla/pivot (D-EM-09); todas las filas del filtro de pantalla; MONO sin selector GEN-23 (R-EM-23).

**CC 2026-08-25 (GEN Q13):** el diseñador **no** queda fijo al proceso Must en el host. Partes adopta la selección GEN (lista + confirmación, también N=1); seed Must sigue siendo un solo proceso `partes.informes.consultaDetallada`.

---

## 9. Preguntas abiertas

Ninguna bloqueante tras A1. Refinamientos → B/C / TR:

- Shape JSON exacto del snapshot de filtros de pantalla y extensión del body GEN.
- DDL/nombres SP del puerto (reuso vs SP dedicada) y nombres canónicos de columnas del dataset (camelCase vs SQL).
- Estética del layout DX inicial (márgenes, agrupación, totales).
- Qué roles concretos reciben `emission.design` además de «no cliente por defecto».
- Versión mínima de paquetes GEN-15; runtime DX real vs stub.
- Campo UI `mailTo` en `EmissionDialog` (smoke GEN puede no mostrarlo aún): el canal mail sigue Must; el host **no** inventa una ventana paralela — si GEN no trae el control, TR declara gap y mínimo usable.

---

## 10. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-25 | Parte A: SPEC-011 desde `15-reportes-emisiones.md` (D-EM-01…10) + adopción GEN-15 / SPEC-001-15. |
| 2026-08-25 | A1: universo = filtros de pantalla (todas las filas; no filter-row/pivot/página); R-EM-23 MONO sin GEN-23; (texto «diseñador fijo» **retirado** en CC Q13). |
| 2026-08-25 | CC Q13 / adopción: §4.8 → `EmissionReportDesignerPage` + lista GEN Must (también N=1); sin hardcode `processCode`; alinea SPEC-001-15 C1-15-36..39. |
| 2026-08-25 | Parte B: enlazada HU-011. |
| 2026-08-25 | Parte B1: HU-011 enriquecida (CA-21, dataset §4.4, impresión, umbrales). |
| 2026-08-25 | Parte C: enlazada TR-011. |
| 2026-08-25 | Parte C1: TR-011 apta con observaciones. |
| 2026-08-25 | Parte D1: plan [D1-TR-011](../../04-tareas/100-SistemaPartes/d1/D1-TR-011-reportes-emisiones.md). |
| 2026-08-25 | Parte D1: plan de implementación listo. |

---

**Trazabilidad:** fuente producto `15-…`; GEN-15 (motor, ventana, diseñador, async, bitácora, adopción). [HU-011](../../03-historias-usuario/100-SistemaPartes/HU-011-reportes-emisiones.md) · [TR-011](../../04-tareas/100-SistemaPartes/TR-011-reportes-emisiones.md) (C+C1). No reabrir trazas Framework HU/TR-GEN-15.

---

## Resultado A1 — revisión de ambigüedad (2026-08-25)

### Resultado general

- Estado inicial: **No apto** (universo emitido vs. lo visible en grilla/pivot/página; colisión `permiteConsolidado` proceso vs. selector GEN-23).
- Estado tras cierres (lectura D-EM-04 / D-EM-09, sin nuevo alcance): **Apto con observaciones**.

### Ambigüedades críticas cerradas

| # | Tema | Decisión |
|---|------|----------|
| Q1 | Qué filas se emiten | Universo de **filtros de pantalla** + rol + `es_tarea`; **todas** las filas coincidentes; **no** filter-row, columnas, agrupación, chooser ni pivot (eso es GEN-11/12) |
| Q2 | Página de grilla | La paginación de la grilla **no** recorta la emisión |
| Q3 | `permiteConsolidado` | Modo un documento del universo; MONO **sin** selector de grupo empresario (R-EM-23) |
| Q4 | Diseñador y N procesos | **CC Q13:** selección GEN Must (lista+confirmación, también N=1); seed Must Partes = `partes.informes.consultaDetallada`; diseñar no exige menú de la consulta |

### Observaciones (no bloquean B)

- Prefijo i18n/`data-testid`: adoptar el **efectivo GEN** (`emissions.*` en smoke actual); no inventar un segundo catálogo Partes salvo copy de toolbar/empty.
- Colocación exacta del botón Emitir en la toolbar → TR (Must: visible en grilla y pivot).
- Estado loading de la consulta: Should deshabilitar Emitir hasta haber resultado; Must sigue siendo 0 filas → disabled.
- Roles seed de `emission.design`, SP, JSON de filtros, DX real vs stub, UI `mailTo` → TR.

### Veredicto

- Puede pasar a HU: **Sí**.
