# SPEC-001-07 - Importar Excel

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionadas** | [HU-GEN-07-plantilla-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-plantilla-excel.md), [HU-GEN-07-carga-staging-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-carga-staging-excel.md), [HU-GEN-07-grilla-procesamiento-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-grilla-procesamiento-excel.md), [HU-GEN-07-historial-importaciones](../../03-historias-usuario/001-Generalidades/HU-GEN-07-historial-importaciones.md) |
| **Estado** | Especificado |
| **Revisión A1** | Apto con observaciones (2026-06-09; cierre formal § Revisión A1 — 2026-06-16) — decisiones §6.1 parcial cerradas |

## Objetivo

Dejar especificado el marco funcional/técnico de importación por Excel como capacidad prevista (fuera de implementación MVP inicial).

## Estado de ejecución

Documental — **4 HU enriquecidas (parte B)** + **4 TR (parte C)**; sin implementación en MVP portal.

## Entradas requeridas

- Documentación de importar-excel en `docs/00-contexto/_mono/importar-excel/`.
- **Patrón UI componente embebido (host):** [`patron-componente-excel-embebido.md`](../../00-contexto/_mono/importar-excel/patron-componente-excel-embebido.md) — contrato `onComplete`, modal, grilla solo errores, export (decisiones 2026-06-16).

## Fuentes

Subcarpeta: `docs/00-contexto/_mono/importar-excel/`

- `PQ_EXCEL_Diagrama_Mermaid.md`
- `PQ_EXCEL_Documento_Conceptual_Funcional_v3.md`
- `PQ_EXCEL_Modelo_de_Datos_v3.md`
- `PQ_EXCEL_SQL_Server_Tablas_y_Create.md`

## Decisiones humanas (cerradas parcialmente)

| Tema | Decisión |
|------|----------|
| `PermiteProcesamientoParcial = true` y **todas** las filas con error | **No** se habilita el procesamiento. |
| `PermiteProcesamientoParcial = true` y **cero** filas con error | Se procesa **todo** el conjunto; estado **`procesada`**. |
| `PermiteProcesamientoParcial = true` y mezcla error + válidas | Solo filas válidas; estado **`procesada_parcial`**. |
| **Fila ajustada** (`FilaAjustadaAutomaticamente`) | Trim o limpieza de caracteres no imprimibles (contexto §7); **solo auditoría** en staging; **sin mostrar al usuario** en esta etapa. |
| **Plantilla modelo** | Botón **Descargar plantilla modelo** en toolbar del proceso si `GeneraPlantilla = 1` (default); columnas, comentarios (`OBLIGATORIO` + `Observaciones`), formato por `TipoDato` — conceptual §12. |

Detalle y casos borde: `PQ_EXCEL_Documento_Conceptual_Funcional_v3.md` §6.1 y §7.

## Alcance

- Modelo conceptual de importación.
- Estructura de datos y tablas asociadas.
- Flujo general de procesamiento.
- **Por proceso:** política `PermiteProcesamientoParcial` — si ≥ 1 fila con error en staging, permite o bloquea el procesamiento del resto (contexto `importar-excel` §6.1).

## Fuera de alcance

- Desarrollo completo de importación Excel en MVP portal (`INDICE-Sistema-Partes.md`).
- TR e implementación (partes C y D) hasta priorizar epic importar Excel.

## Inventario de tablas y entidades

| Objeto | Rol |
|--------|-----|
| `PQ_EXCEL_PROCESOS` | Configuración fija por proceso (`PermiteProcesamientoParcial`, `HandlerBackend`, flags normalización) |
| `PQ_EXCEL_PROCESOS_CAMPOS` | Definición de columnas Excel vs campos internos |
| `PQ_EXCEL_IMPORTACIONES` | Cabecera de lote / sesión de importación |
| `PQ_EXCEL_IMPORTACIONES_FILAS` | Staging persistente por fila |
| `PQ_EXCEL_IMPORTACIONES_FILAS_ERRORES` | Errores detallados por fila (auditoría) |
| `PQ_EXCEL_IMPORTACIONES_NOTIFICACIONES` | Toast / bandeja / resultado (procesos asíncronos) |
| `PQ_EXCEL_VW_HISTORIAL_IMPORTACIONES` | Vista consulta de historial |

Diagrama ER: `PQ_EXCEL_Diagrama_Mermaid.md`.

## Flujo extremo a extremo (documental)

**Modo canónico (UI embebida):** ver [patron-componente-excel-embebido.md](../../00-contexto/_mono/importar-excel/patron-componente-excel-embebido.md) y [TR-GEN-07-ui-embebida-host](../../04-tareas/001-Generalidades/TR-GEN-07-ui-embebida-host.md).

1. Pantalla **host** expone toolbar: exportar plantilla + importar (modal).
2. **Descarga plantilla** si `GeneraPlantilla = 1`.
3. Modal: archivo `.xlsx` + hoja → validación estructural y staging.
4. Si error estructural → mensaje en modal (sin grilla).
5. Si cero errores por fila → procesar (según política) → `onComplete` al host con `validRows`.
6. Si hay errores por fila → grilla modal **solo errores**; export errores; política parcial define procesamiento y payload.
7. Lote en **historial** (`HU-GEN-07-historial-importaciones`).

**Modo legado (rutas `/excel-import/*`):** upload en página → grilla full-page todas las filas → Procesar manual; se mantiene para historial detalle y diagnóstico D1.

## Trazabilidad HU (parte B)

| HU | TR | Foco | Orden |
|----|-----|------|-------|
| [HU-GEN-07-plantilla-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-plantilla-excel.md) | [TR-GEN-07-plantilla-excel](../../04-tareas/001-Generalidades/TR-GEN-07-plantilla-excel.md) | Exportar plantilla modelo `.xlsx` | 1 |
| [HU-GEN-07-carga-staging-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-carga-staging-excel.md) | [TR-GEN-07-carga-staging-excel](../../04-tareas/001-Generalidades/TR-GEN-07-carga-staging-excel.md) | Carga, validación estructural, staging | 2 |
| [HU-GEN-07-grilla-procesamiento-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-grilla-procesamiento-excel.md) | [TR-GEN-07-grilla-procesamiento-excel](../../04-tareas/001-Generalidades/TR-GEN-07-grilla-procesamiento-excel.md) | Grilla resultados, política parcial, procesar | 3 |
| [HU-GEN-07-historial-importaciones](../../03-historias-usuario/001-Generalidades/HU-GEN-07-historial-importaciones.md) | [TR-GEN-07-historial-importaciones](../../04-tareas/001-Generalidades/TR-GEN-07-historial-importaciones.md) | Historial consultable | 4 |
| [HU-GEN-07-ui-embebida-host](../../03-historias-usuario/001-Generalidades/HU-GEN-07-ui-embebida-host.md) | [TR-GEN-07-ui-embebida-host](../../04-tareas/001-Generalidades/TR-GEN-07-ui-embebida-host.md) | Componente embebido host + `onComplete` | 5 (D2) |

## Entregables verificables

- Resumen funcional/técnico para abrir SPEC dedicado de importación Excel.
- Inventario de tablas y entidades involucradas (§ arriba).
- **4 HU** enriquecidas en `docs/03-historias-usuario/001-Generalidades/`.

## Criterios de aceptación medibles

- Flujo de importación identificado de extremo a extremo en forma documental (§ Flujo e2e).
- Datos requeridos y restricciones técnicas listados explícitamente (fuentes `_mono/importar-excel/`).
- HU derivadas con criterios de aceptación y Gherkin trazables al contexto.

---

## Revisión A1 — cierre (2026-06-16)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a HU (MVP portal)** | **No** — epic importar Excel fuera de `INDICE-Sistema-Partes.md` |
| **Puede abrir epic / HU futura** | **Sí** — contexto `_mono/importar-excel/` cerrado para derivar HU |

### Checklist A1 (resumen)

| Área | Estado | Notas |
|------|--------|-------|
| Alcance / fuera de alcance | OK | Documental; desarrollo completo fuera del MVP portal |
| Actores / permisos | Obs. | Propuesta: mismo permiso que la pantalla host (`AMB-Q-07-01`) |
| Flujo e2e | OK | 10 pasos § Flujo extremo a extremo; alineado a conceptual §5 |
| Reglas de negocio | OK | §6.1 `PermiteProcesamientoParcial` y casos borde cerrados en SPEC |
| Encabezado / estructura Excel | OK | Fila 1 fija; coincidencia exacta; sin tildes ni símbolos (conceptual §3–§4) |
| Datos | OK | 7 objetos `PQ_EXCEL_*` + vista historial; DDL en `PQ_EXCEL_SQL_Server_Tablas_y_Create.md` |
| Normalización §7 | OK | `FilaAjustadaAutomaticamente` solo auditoría BD; sin UI en esta etapa |
| UI / i18n | OK | DevExtreme (`FileUploader`, `DataGrid`); paridad `HU-GEN-03-grillas-listados` |
| APIs | Pendiente TR | Contrato REST (plantilla, carga, staging, procesar, historial) no definido a nivel SPEC |
| Criterios aceptación | OK | Medibles y trazables a fuentes `_mono/importar-excel/` |

### Ambigüedades críticas

Ninguna bloqueante para el **estado documental** del SPEC (no hay HU MVP que implementar en portal).

### Ambigüedades menores

| ID | Tema | Resolución |
|----|------|------------|
| AMB-M-07-01 | Convención `HandlerBackend` | Plug-in por `CodigoProceso` (p. ej. `Importacion.{Modulo}.{Accion}Handler`); contrato en TR carga/grilla |
| AMB-M-07-02 | Formato booleano en plantilla | `0`/`1`, `N`/`S` o `VERDADERO`/`FALSO` **por proceso** según handler destino (conceptual §12) |
| AMB-M-07-03 | Override `MantenerEspaciosEnBlanco` / `MantenerCaracteresEspeciales` por lote | Defaults del proceso en v1 (`AMB-Q-07-02`); override opcional en TR si se requiere |
| AMB-M-07-04 | Paginación grilla staging | API paginada para lotes grandes; definir en TR `carga-staging` / `grilla-procesamiento` |
| AMB-M-07-05 | Prefijo tablas `PQ_EXCEL_` | Canónico según MONO SQL; sin alias `pq_excel_*` en documentación nueva |

### Supuestos detectados

- Motor de importación **genérico** configurable por filas en `PQ_EXCEL_PROCESOS` / `PQ_EXCEL_PROCESOS_CAMPOS`.
- Validación de **negocio** delegada al `HandlerBackend` del proceso; el importador no duplica reglas ERP.
- Generación de plantilla reutiliza patrones de exportación transversal (`HU-GEN-03-exportaciones`).
- Parser Excel asume **fila 1** como encabezado sin configuración adicional.
- Cada ejecución es un **lote aislado** (`GuidImportacion`); concurrencia multiusuario sin mezcla de staging.
- Archivos grandes: flujo **asíncrono** (`EsAsincronica`) con notificaciones en `PQ_EXCEL_IMPORTACIONES_NOTIFICACIONES`.
- Integración en producto: pantalla **host** por proceso (menú / ABM); no pantalla única transversal en v1.

### Preguntas para decisión humana

| ID | Tema | Propuesta default |
|----|------|-------------------|
| AMB-Q-07-01 | Permiso dedicado vs permiso del proceso host | Mismo permiso que la pantalla que invoca la importación |
| AMB-Q-07-02 | Override flags normalización por lote | Defaults del proceso en v1 |
| AMB-Q-07-03 | Reproceso en mismo lote tras corregir Excel | Nueva importación en v1 |

### Recomendaciones de ajuste del SPEC

- [x] Incorporar decisiones humanas §6.1 (`PermiteProcesamientoParcial`, `FilaAjustadaAutomaticamente`).
- [x] Inventario de tablas y entidades (§ Inventario).
- [x] Flujo extremo a extremo documental (10 pasos).
- [x] Trazabilidad a 4 HU en `docs/03-historias-usuario/001-Generalidades/`.
- [x] Al generar TR: contrato API en orden plantilla → carga → grilla/procesamiento → historial.
- [x] Resolver `AMB-Q-07-*` en TR del epic (no bloquean A1 ni B1).

### Veredicto

**Apto con observaciones** para cierre **A1 documental**. **Autoriza parte B** (HU derivadas).

---

## Parte B — cierre (2026-06-11)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto B1** | **Cerrado** — 4 HU enriquecidas |
| **¿Puede pasar a parte C (TR) en MVP portal?** | **No** — epic posterior; sin prioridad en release MVP |
| **¿Listo para parte C cuando se priorice epic?** | **Sí** — resolver AMB-Q-07-* en TR |

### Entregables parte B

| Entregable | Estado |
|------------|--------|
| `HU-GEN-07-plantilla-excel` | Enriquecida |
| `HU-GEN-07-carga-staging-excel` | Enriquecida |
| `HU-GEN-07-grilla-procesamiento-excel` | Enriquecida |
| `HU-GEN-07-historial-importaciones` | Enriquecida |
| Índice HU README 001-Generalidades | Actualizado |

### Preguntas abiertas (no bloqueantes MVP)

| ID | Tema | HU | Propuesta default |
|----|------|-----|-------------------|
| AMB-Q-07-01 | Permiso dedicado vs permiso del proceso host | plantilla | Mismo permiso que pantalla host |
| AMB-Q-07-02 | Override flags normalización por lote | carga-staging | Defaults del proceso en v1 |
| AMB-Q-07-03 | Reproceso en mismo lote tras corregir Excel | grilla-procesamiento | Nueva importación en v1 |

### Veredicto

**B1 cerrado** para SPEC-001-07. **No avanzar a parte D** en MVP portal. Al priorizar el epic importar Excel, ejecutar TR en orden plantilla → carga → grilla/procesamiento → historial.

---

## Parte C — cierre (2026-06-16)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto C1** | **Cerrado** — 4 TR generadas; aptas para D1 |
| **¿Puede pasar a parte D en MVP portal?** | **No** — epic posterior; sin prioridad en release MVP |
| **¿Listo para parte D cuando se priorice epic?** | **Sí** — ver [F-GEN-07-cierre-c1](../../04-tareas/001-Generalidades/F-GEN-07-cierre-c1.md) |

### Entregables parte C

| Entregable | Estado |
|------------|--------|
| `TR-GEN-07-plantilla-excel` | Generada |
| `TR-GEN-07-carga-staging-excel` | Generada |
| `TR-GEN-07-grilla-procesamiento-excel` | Generada |
| `TR-GEN-07-historial-importaciones` | Generada |
| `F-GEN-07-cierre-c1` | C1 cerrado |
| Índice TR README 001-Generalidades | Actualizado |
| Índice HU README 001-Generalidades | Actualizado (columna TR) |

### Trazabilidad HU → TR

| HU | TR | Orden D1 |
|----|-----|----------|
| [HU-GEN-07-plantilla-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-plantilla-excel.md) | [TR-GEN-07-plantilla-excel](../../04-tareas/001-Generalidades/TR-GEN-07-plantilla-excel.md) | 1 |
| [HU-GEN-07-carga-staging-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-carga-staging-excel.md) | [TR-GEN-07-carga-staging-excel](../../04-tareas/001-Generalidades/TR-GEN-07-carga-staging-excel.md) | 2 |
| [HU-GEN-07-grilla-procesamiento-excel](../../03-historias-usuario/001-Generalidades/HU-GEN-07-grilla-procesamiento-excel.md) | [TR-GEN-07-grilla-procesamiento-excel](../../04-tareas/001-Generalidades/TR-GEN-07-grilla-procesamiento-excel.md) | 3 |
| [HU-GEN-07-historial-importaciones](../../03-historias-usuario/001-Generalidades/HU-GEN-07-historial-importaciones.md) | [TR-GEN-07-historial-importaciones](../../04-tareas/001-Generalidades/TR-GEN-07-historial-importaciones.md) | 4 |

### Decisiones cerradas en C1 (resumen)

| ID | Decisión |
|----|----------|
| AMB-Q-07-01 | Permiso = mismo que pantalla host (`ProcedimientoHost`) |
| AMB-Q-07-02 | Flags normalización: defaults del proceso; sin override UI v1 |
| AMB-Q-07-03 | Sin reproceso en mismo lote; nueva importación v1 |
| Infra | `EXCEL_IMPORT_ENABLED` default `false` hasta activar epic |

### Veredicto

**C1 cerrado** para SPEC-001-07. Epic **listo para D1** cuando se priorice importar Excel fuera del MVP portal.








