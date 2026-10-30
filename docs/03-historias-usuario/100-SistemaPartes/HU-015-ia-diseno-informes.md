# HU-015 – IA en diseño de informes (piloto Consulta detallada)

Jira: PIAPARTES-54


## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-015 |
| Título | Asistencia IA en el diseñador de Consulta detallada — piloto oleada 1 (GEN-15 + sidecar DX) |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE (oleada piloto) |
| Estado | **Especificado** (Parte C1 — 2026-10-30) |
| SPEC origen | [SPEC-015-ia-diseno-informes](../../05-open-spec/100-SistemaPartes/SPEC-015-ia-diseno-informes.md) |
| TR posterior (condicional) | TR-016 _(post-F1; no redactada)_ |
| TR relacionada(s) | [TR-015-piloto-dx-reporting-ia](../../04-tareas/100-SistemaPartes/TR-015-piloto-dx-reporting-ia.md) |
| Norma GEN | Framework [`15-reportes-emisiones-update-4-ia-diseno.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-update-4-ia-diseno.md) |
| Producto Partes | [`19-ia-diseno-informes.md`](../../02-producto/Sistema-Partes-IA/19-ia-diseno-informes.md) |

**Plantilla regla 19:**  
Diseño informes Partes oleada 1: **piloto** IA DevExpress 26.1 en sidecar GEN-15 + schema semántico Consulta detallada. No reimplementar diseñador ni envelope 470x. SoT: SPEC-015 + TR-015. Host: `partes.informes.consultaDetallada`, `emission.design`, sidecar `dx-reporting`.

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-015 | Dónde en esta HU |
|--------------------------------|------------------|
| Encuadre host vs GEN (SoT update-4) | Metadatos; Supuestos |
| Oleada 1 piloto Must (§2.1) | Alcance; CA-01…08 |
| Flujo UX R-ID-01…04 (proceso primero, Save GEN) | Alcance; CA-02, CA-08; R-ID-01…04 |
| Schema semántico R-ID-10…12 | Alcance; CA-01; R-ID-10…12 |
| Persistencia R-ID-20…22 | Reglas; CA-08; Fuera alcance |
| Batería piloto §4.4 #1–#8 | CA-03…07 |
| Matriz F1 §5 (veredicto A/B/C) | CA-08 |
| Actores §3 (solo `emission.design`, desktop) | Actores; CA-09 |
| Fuera alcance Emitir / mobile / chat REPX (§2.3) | Fuera de alcance |
| A1 D-A1-01…07 | Supuestos; Reglas R-ID-BYOK |
| Oleada 2 B/C R-ID-30…32 | Fuera de alcance (TR-016 post-F1) |
| CA-15-01…08 SPEC §6 | CA-01…08 |

---

## Narrativa

Como usuario con permiso de **diseñar reportes** (`emission.design`)  
quiero describir en lenguaje natural cómo debe verse el informe de **Consulta detallada** dentro del diseñador DevExpress en `/emisiones/disenador`  
para acelerar layouts, agrupaciones y expresiones **sin** pedir desarrollo ad hoc, revisando el resultado y **guardando** con el flujo Save ya adoptado (HU-011).

Como responsable de producto / operaciones  
quiero un **piloto medible** (schema semántico, IA DX 26.1, pruebas §4.4 y matriz A/B/C)  
para decidir si productizamos IA nativa DevExpress, Smart Capture o un híbrido en oleada 2.

---

## Contexto funcional

HU-011 ya adoptó GEN-15: diseñador, Emitir y dataset de Consulta detallada. Esta historia **no** reabre emisión ni filtros/`selectedTareaIds`. Agrega **oleada 1**: validar asistencia IA sobre el **mismo** diseñador y proceso Must, con vocabulario de campos aportado por Partes y motor IA habilitado en el **sidecar Framework** (capacidad transversal update-4).

La IA **no** persiste REPX en un turno paralelo: el usuario **guarda** en DevExpress (R-ID-03 / D-A1).

Distinto del chat documental (HU-008) y de Smart Capture en Carga diaria (HU-010). Oleada 2 con GEN-03 en diseñador **no** es parte de esta HU (veredicto F1 → TR-016).

### Precondiciones

- HU-011 / SPEC-011 operativos: `/emisiones/disenador`, sidecar cableado (`VITE_DX_REPORTING_*`), proceso `partes.informes.consultaDetallada`.
- `EmissionEnabled = Sí` y capacidad diseño disponible.
- Entorno piloto con sidecar 26.1 y credencial LLM en **despliegue sidecar** (D-A1-02) para camino A del piloto.
- Puente dataset alineado: [`PartesConsultaDetalladaEmissionPort`](../../../backend/app/Services/Emissions/PartesConsultaDetalladaEmissionPort.php).

### Actores

| Actor | Diseñador + IA piloto |
|-------|------------------------|
| Usuario con **`emission.design`** + menú diseñador + desktop | Sí |
| Usuario con menú Consulta detallada sin `emission.design` | No |
| Mobile / native | No |
| Cliente por defecto (sin diseño en seed) | No |

---

## Alcance incluido (oleada 1 — Must)

- Publicar **schema semántico** JSON del proceso `partes.informes.consultaDetallada` (labels ES Must en todas las columnas; description/synonyms Should en campos ambiguos — D-A1-07) consumible por `ProcessFieldSchemaCatalog` del sidecar; columnas = puerto emisión (sin campos extra).
- Contribución Partes al despliegue del sidecar (artefacto JSON + documentación ops en TR-015); **no** ramas permanentes Partes en C# del Framework (objetivo update-4).
- Habilitar y smoke **IA DevExpress Reporting 26.1** en sidecar piloto (Prompt-to-Report / wizard; prueba #6 documenta límites web — D-A1-01).
- Ejecutar batería SPEC §4.4 escenarios **#1–#8** en español sobre Consulta detallada; evidencia en verificación F1.
- Completar **matriz veredicto** A | B | C (SPEC §5) antes de cualquier TR-016.
- **Regresión:** flujo selección proceso → confirmar → diseñador sin romper Save / Save As HU-011.
- Manual / nota operativa mínima: quién diseña, que la IA no reemplaza Save, credencial sidecar en piloto (O-15-01).

---

## Fuera de alcance

- Redefinir motor GEN-15, ventana Emitir, envelope 470x o APIs `/api/v1/emissions/*` (oleada 1).
- IA en Emitir, Consulta detallada, dashboard, mobile.
- Chat documental mutando REPX.
- Montar **Smart Capture** o producto oleada 2 **antes** de F1 (D-A1-05).
- Unificar BYOK usuario (GEN-16) con sidecar DX en Must del piloto (O-15-01).
- Nuevo ítem de menú solo para IA.
- Bump obligatorio `@paqsuite/react-core` salvo que TR-015 documente necesidad del piloto.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-ID-01 | Selección y confirmación de proceso emisible antes del DX (igual HU-011 / GEN Q13). |
| R-ID-02 | IA solo usa campos del schema autorizado del proceso; no inventa atributos. |
| R-ID-03 | Cambios propuestos por IA se confirman en diseñador y persisten solo vía Save / Save As GEN. |
| R-ID-04 | Misma superficie host: `ReportDesignerHostPage` + `EmissionReportDesignerPage`; sin segundo diseñador. |
| R-ID-10 | Schema por **processCode**, no por variante `.repx` suelta. |
| R-ID-BYOK | Piloto camino A: credencial LLM en **entorno sidecar**; no sync automático desde Preferencias usuario (D-A1-02). |
| R-ID-21 | Solo `emission.design` + desktop. |
| R-ID-22 | Oleada 1 sin nuevas rutas Laravel de negocio para IA DX nativa. |

---

## Criterios de aceptación

- [ ] **CA-01** Schema semántico Consulta detallada desplegado en sidecar (evidencia: JSON + Field List / metadata IA).
- [ ] **CA-02** En `/emisiones/disenador`, tras confirmar proceso Must, el diseñador carga sin regresión respecto HU-011 (lista proceso, schema, Save).
- [ ] **CA-03** Pruebas piloto **#1–#5** (§4.4) ejecutadas con resultado documentado (capturas o REPX).
- [ ] **CA-04** Prueba **#6** ejecutada; conclusión explícita sobre modificación web (input matriz; no exige paridad WinForms).
- [ ] **CA-05** Prueba **#7**: pedido de campo inexistente → rechazo o respuesta sin inventar datos.
- [ ] **CA-06** Prueba **#8**: Save As / catálogo GEN-15 OK.
- [ ] **CA-07** Sidecar piloto con IA DX habilitada (wizard Prompt-to-Report visible en lab; checklist licencia D-A1-06).
- [ ] **CA-08** Documento matriz F1 con veredicto **A**, **B** o **C** firmado (`D-VERIFICACION-TR-015-*`).
- [ ] **CA-09** Usuario sin `emission.design` no accede al diseñador ni a IA; mobile excluido.

### Escenarios Gherkin (oleada 1)

```gherkin
Feature: Piloto IA en diseño de informe Consulta detallada
  Como diseñador de reportes con emission.design
  Quiero usar lenguaje natural en el diseñador DevExpress
  Para obtener un layout más rápido sin salir del flujo GEN-15

  Scenario: Acceso y flujo base sin regresión
    Given un usuario con permiso emission.design y menú del diseñador
    When abre /emisiones/disenador
    Then ve la lista GEN de procesos y confirma Consulta detallada
    And el diseñador DX opera con el schema del proceso
    And puede guardar un layout con Save sin error de catálogo

  Scenario: Generación acotada al schema (ejemplo prueba 1)
    Given el piloto IA habilitado en sidecar y schema semántico desplegado
    When el diseñador solicita un informe "por fecha y cliente" en español
    Then el layout propuesto usa solo campos autorizados del schema
    And no aparecen columnas inventadas

  Scenario: Campo inexistente (prueba 7)
    When pide agregar "rentabilidad de cada parte"
    Then el sistema no agrega un campo fuera del catálogo
    And informa rechazo o limitación clara

  Scenario: Permisos
    Given un usuario sin emission.design
    When intenta abrir el diseñador
    Then no accede al diseñador ni a la IA
```

---

## Supuestos explícitos

- Capacidad transversal definida en Framework update-4; Partes es **primer host piloto**.
- Sidecar y NuGet IA son trabajo principal TR-015 (Framework repo + ops Partes).
- GEN-16 usuario **no** es prereq Must para activar IA DX en piloto (D-A1-02).
- Versión reporting FE y sidecar **26.1** en preview/prod (D-A1-03).
- Oleada 2 (SC / híbrido) depende exclusivamente de CA-08 / matriz F1.

---

## Preguntas abiertas (→ TR-015)

| # | Pregunta | Destino |
|---|----------|---------|
| 1 | Ruta exacta de despliegue JSON schema (sidecar path vs pipeline host) | TR-015 |
| 2 | Variables entorno sidecar (OpenAI vs Azure) en Forge preview | TR-015 |
| 3 | Retirar fallback C# `consultaDetallada` en catálogo Framework | TR-015 / TR Framework |
| 4 | Evidencia automatizada mínima (E2E diseñador vs checklist manual piloto) | TR-015 C1 |

Ninguna bloquea Parte C si se documenta en TR-015.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-30 | Parte B: HU-015 (Jira PIAPARTES-54). |
| 2026-10-30 | Parte C: enlazada [TR-015](../../04-tareas/100-SistemaPartes/TR-015-piloto-dx-reporting-ia.md). |
| 2026-10-30 | Parte C1: TR apta → HU **Especificado**. |
