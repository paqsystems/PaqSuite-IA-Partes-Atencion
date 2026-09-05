# Reportes y emisiones (adopción GEN-15)

## Objetivo

Definir, en lenguaje de producto, cómo `SistemaPartes` **adopta el motor GEN de reportes / emisiones** del Framework (`15` / SPEC-001-15) sin redefinir el orquestador, la ventana Emitir ni DevExtreme Reporting.

Este documento es la **definición conceptual de producto**. No es SPEC, HU ni TR; alimenta la posterior generación Open-Spec (flujo SDD / Partes A→C).

Hay **dos frentes** que el módulo debe cubrir:

1. **Diseño:** procesos para diseñar informes (layouts DX) y declarar **qué salidas** tiene cada proceso emisible.
2. **Emisión operativa:** montar el **emisor** (ventana Emitir) en **Consulta detallada**, usando el mismo universo de datos que esa consulta.

---

## 1. Qué se reutiliza y qué define Partes

Norma Framework: [`PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones.md) y SPEC-001-15.

| Capa | Responsabilidad |
|------|-----------------|
| **Framework** | Motor de emisión; ventana **Emitir**; diseñador DX (reportes + plantillas mail); canales de presentación; async + bitácora `source=emission`; permiso `emission.design`; parámetros `Emission*` |
| **Partes** | Seed de **procesos emisibles**; puerto **dataset** de cada proceso; reporte/plantilla **inicial**; **dónde** se muestra Emitir; quién puede emitir (permiso de menú host) |

Anti-patrones (igual que GEN):

- Llamar «emisión» al **Exportar Excel de la grilla** (GEN-11). Conviven.
- Meter reglas de negocio de partes dentro del orquestador GEN.
- Inventar un historial de emisiones paralelo a la bitácora general.

---

## 2. Modelo: proceso emisible

Unidad funcional GEN: **proceso**. En Partes, el primer proceso emisible es **Consulta detallada**.

Por proceso el producto declara (seed/SQL; **sin ABM web GEN del catálogo en v1**):

| Declaración | Sentido de negocio |
|-------------|-------------------|
| Código de proceso | Identidad estable (p. ej. `partes.informes.consultaDetallada`) |
| Vínculo a menú host | Autorizar **emitir** con el mismo permiso que entra a la pantalla (`partes_consulta_detallada`) |
| Dataset | Qué filas/campos alimentan el reporte (puerto `resolveDataset`) |
| Reportes DX | Layouts diseñables asociados a ese dataset; uno **principal** |
| Plantillas mail | Asunto + HTML breve; el documento formal va en **PDF adjunto** |
| Canales habilitados | Subconjunto de salidas de presentación (ver §4) |
| Modos | Consolidado y/o segmentado |
| Flags | Vista previa obligatoria, visibilidad mobile, etc. |

Cambiar **qué salidas existen** para un proceso = actualizar seed/SQL (o el diseñador para layouts ya dados de alta). No hay pantalla GEN de «ABM de procesos emisibles» en v1.

---

## 3. Frente A — Diseñar informes y definir salidas

### 3.1 Quién diseña

| Actor / permiso | Diseñar reportes y plantillas mail | Emitir desde Consulta detallada |
|-----------------|-------------------------------------|----------------------------------|
| Usuario con permiso GEN **`emission.design`** | Sí (solo **desktop**) | Sí, si además tiene el menú de la consulta |
| Usuario con menú Consulta detallada, sin `emission.design` | **No** | Sí |
| Cliente / asistente / supervisor | Según menú de Informes | Según menú de Informes |
| Mobile | **No** (GEN: sin diseñador ni preview) | **No** en este alcance (ver §6) |

**D-EM-01.** Emitir no exige `emission.design`. Diseñar sí.

### 3.2 Dónde se diseña

Superficie **desktop** del Framework (`EmissionReportDesignerPage` / APIs `/api/v1/emissions/design/...`). Incluye **selección de proceso** GEN (lista + confirmación; también si hay un solo proceso). El módulo **no** inventa un picker ni hardcodea el proceso.

El módulo **no** inventa un diseñador propio. Puede exponer el acceso (ítem de menú o acción de administración) solo a quienes tienen `emission.design`.

El diseñador opera **sobre el dataset del proceso elegido**: no se agregan campos que el puerto no exponga.

**D-EM-02.** Un reporte o plantilla pertenece siempre a un proceso emisible. No se diseña «en el aire».

**D-EM-02b.** La entrada al diseñador usa la superficie GEN multi-proceso; el seed Must de Partes puede aportar un solo proceso activo (lista unitaria).

**D-EM-03.** Un solo reporte **principal** y una sola plantilla mail **principal** por proceso.

### 3.3 Definir salidas disponibles por proceso

Las salidas de **presentación** que el Framework ofrece (MVP GEN):

| Canal | ¿Consulta detallada v1? |
|-------|-------------------------|
| PDF | **Sí** |
| Impresión | **Sí** (desktop; en la práctica PDF + diálogo de impresión) |
| Excel / CSV de **reporte** (layout DX) | **Sí** |
| Mail + PDF adjunto | **Sí** |
| ZIP documental / lotes segmentados | **No** en v1 de Partes |

**D-EM-04.** Consulta detallada: `permiteConsolidado = sí`. `permiteSegmentado = no` (MONO / una empresa; el universo ya está delimitado por rol y filtros).

**D-EM-05.** `requiereVistaPrevia = no` en Consulta detallada v1 (preview opcional en desktop). Mobile no ofrece preview.

**D-EM-06.** Parámetro GEN `EmissionEnabled`: el host Partes lo deja en **Sí** cuando se adopta el motor. Con **No**, no se monta Emitir.

Integración (TXT banco, interfaces `tipo_proceso = I`): **fuera de alcance** de Partes y del MVP GEN de presentación.

### 3.4 Alta del catálogo (proceso, no pantalla de usuario final)

1. Seed/SQL: proceso `partes.informes.consultaDetallada` + canales §3.3 + vínculo a `partes_consulta_detallada`.
2. Seed: al menos un **reporte DX inicial** (principal) y, si el canal mail está activo, una **plantilla mail inicial**.
3. Seed: parámetros Programa `Emission` (`EmissionEnabled`, umbrales async, retención de artefactos) — idempotente GEN.
4. El equipo con `emission.design` ajusta layouts sin tocar código.

Menú Informes permanece `tipo_proceso = C` (consulta / informe / emisión de presentación). **No** se usa `E`.

---

## 4. Frente B — Emisor en Consulta detallada

### 4.1 Dónde vive

Misma pantalla web ya definida en `06-consultas-dashboard-y-navegacion.md`:

- Ruta: `/partes/informes/consulta-detallada`
- Código de proceso emisible: **`partes.informes.consultaDetallada`**
- Permiso de emitir = permiso de **entrar** a esa opción de menú

La ventana **Emitir** (GEN, `@paqsuite/react-core`) se monta en esa pantalla (toolbar / acción explícita). No es un ítem de menú aparte.

**D-EM-07.** Primer (y único Must) proceso emisible del módulo: **Consulta detallada**. Consultas agrupadas, Paquete de horas y dashboard **no** montan Emitir en esta definición.

### 4.2 Dataset (qué se emite)

El dataset es el **mismo universo** que la grilla de Consulta detallada en ese momento:

- solo `es_tarea = true`;
- mismas restricciones por perfil (cliente / asistente / supervisor);
- mismos filtros aplicados (periodo, cliente, asistente si aplica, tipo, cerrado, etc.);
- mismos atributos de negocio (fecha, cliente, asistente, tipo, duración, marcas, observación, Erp Cliente, Erp Articulo).

Duración en el dataset técnico: **minutos**. Presentación en reporte: **`hh:mm`**, coherente con la consulta.

**D-EM-08.** La emisión **no** inventa filas ni ignora el filtro de rol. Si la consulta está vacía, Emitir permanece **visible** y **deshabilitado** (mismo criterio que exportar sin datos).

### 4.3 Relación con Excel de grilla

| Acción | Origen | Qué sale |
|--------|--------|----------|
| Exportar grilla / pivot | GEN-11 / GEN-12 | Lo **visible** en pantalla |
| Emitir → Excel de reporte | GEN-15 | Layout **declarado** del proceso |

**D-EM-09.** Conviven en Consulta detallada. No se elimina el export de grilla en v1.

### 4.4 Quién emite

Quien puede **consultar** Consulta detallada puede **emitir**, sujeto a `EmissionEnabled` y a canales del proceso.

No se exige ser supervisor. El cliente emite solo el universo de su organización (igual que la grilla).

### 4.5 Ejecución y trazabilidad

- Emisiones livianas: síncronas desde la ventana.
- Pesadas (umbrales `EmissionAsyncMaxMB` / `EmissionAsyncMaxRows`): async GEN; aviso por bandeja, no un inbox de Partes.
- Cada emisión deja evento en bitácora general (`source=emission`). Partes **no** crea tabla de historial propia.

Mail: cuerpo breve + PDF. No se diseña el parte como tabla enorme dentro del correo.

---

## 5. Navegación

Sin carpeta nueva de menú obligatoria.

| Entrada | Tipo |
|---------|------|
| Informes → Consulta detallada | Proceso `C` + **Emitir** embebido |
| Acceso al diseñador | Desktop + `emission.design` (menú o administración; detalle de ítem → SPEC/TR) |

Agrupadas y Paquete de horas siguen siendo informes de **consulta**; no emisores en este alcance.

---

## 6. Mobile

Coherente con `10-mobile.md` y GEN-15:

| Capacidad | Mobile Partes v1 |
|-----------|------------------|
| Diseñador de reportes / plantillas | **No** |
| Preview de emisión | **No** |
| Ventana Emitir en **Consulta detallada** | **No** — esa pantalla es web (grilla/pivot). El kardex mobile no es este proceso |
| Informe Paquete de horas / dashboard mobile | Sin emisor documental en esta definición |

Una emisión corta GEN desde kardex, si se desea, es **evolución** (otro proceso emisible + policy mobile).

---

## 7. Fuera de alcance de esta definición

- Emitir desde consultas agrupadas, Paquete de horas o dashboard.
- ABM web del catálogo de procesos emisibles.
- Matriz de permisos **por reporte** (usar/editar/eliminar/compartir).
- Formatos de integración / menú `I`.
- Unificar o deprecar el Excel de grilla.
- Informe de facturación / ERP (sigue en `07-fuera-de-alcance-y-evolucion.md`).
- Consolidado multi-empresa / grupos (`tenancy=single`: feature off).

---

## 8. Decisiones de producto (resumen)

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
| D-EM-10 | Canales v1: PDF, impresión, Excel de reporte, mail+PDF |

---

## 9. Relación con Open-Spec

Tras esta base, el flujo A→C baja a SPEC/HU/TR de **adopción Partes** (puerto dataset, seed, montaje Emitir + diseñador), reutilizando GEN-15 sin duplicar el motor.

- **A → D1 (2026-08-25):** [SPEC-011](../../05-open-spec/100-SistemaPartes/SPEC-011-reportes-emisiones.md) · [HU-011](../../03-historias-usuario/100-SistemaPartes/HU-011-reportes-emisiones.md) · [TR-011](../../04-tareas/100-SistemaPartes/TR-011-reportes-emisiones.md) · [D1](../../04-tareas/100-SistemaPartes/d1/D1-TR-011-reportes-emisiones.md). Siguiente: **D**.

Trazas Framework a no reabrir:

- SPEC-001-15; HU/TR-GEN-15 (motor, ventana Emitir, diseñador, async, bitácora, adopción).

---

## Criterio final

Partes declara **qué proceso** emite, **qué datos** manda y **qué canales** habilita. El Framework resuelve **cómo** se diseña el layout y **cómo** se genera PDF, mail o Excel de reporte.
