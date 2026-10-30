# SPEC-015 – IA en diseño de informes (diseñador GEN-15)

Jira: PIAPARTES-53

## Encuadre host vs GEN (leer primero)

| Capa | Documento SoT |
|------|----------------|
| **Capacidad transversal** (sidecar, schema JSON, caminos A/B/C, normas persistencia) | Framework [`15-reportes-emisiones-update-4-ia-diseno.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-update-4-ia-diseno.md) |
| **Adopción y piloto Partes** (Consulta detallada, smoke, matriz F1, TR Partes) | Este SPEC-015 + [`19-ia-diseno-informes.md`](../../02-producto/Sistema-Partes-IA/19-ia-diseno-informes.md) |

Este OpenSpec **no** redefine GEN-15 ni sustituye SPEC-001-15; baja el piloto del **primer host** sobre la norma Framework update-4.

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-015 |
| Título | Adopción Partes — IA en diseño de layouts (piloto Consulta detallada + producto condicional) |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | A1 apto — **Especificado** (C1 TR-015 2026-10-30) |
| **A1** | [`SPEC-015-ia-diseno-informes-A1-ambiguity-review.md`](./SPEC-015-ia-diseno-informes-A1-ambiguity-review.md) |
| HU relacionada(s) | [HU-015-ia-diseno-informes](../../03-historias-usuario/100-SistemaPartes/HU-015-ia-diseno-informes.md) |
| TR relacionada(s) | [TR-015-piloto-dx-reporting-ia](../../04-tareas/100-SistemaPartes/TR-015-piloto-dx-reporting-ia.md) |
| Depende de | [SPEC-011](./SPEC-011-reportes-emisiones.md) (diseñador `/emisiones/disenador`, `emission.design`, proceso `partes.informes.consultaDetallada`); [SPEC-008](./SPEC-008-asistente-ia-chat-documental.md) (BYOK / timeout LLM); Framework **GEN-15** / SPEC-001-15; **GEN-16**; **GEN-03** _(solo oleada 2 caminos B/C)_ |
| Fuentes | Framework [update-4 IA diseño](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-update-4-ia-diseno.md); [`19-ia-diseno-informes.md`](../../02-producto/Sistema-Partes-IA/19-ia-diseno-informes.md); [`15-reportes-emisiones.md`](../../02-producto/Sistema-Partes-IA/15-reportes-emisiones.md); DevExpress [AI for Reporting](https://docs.devexpress.com/XtraReports/405211/ai-for-devexpress-reporting) |

**Plantilla regla 19 (oleada piloto):**  
Diseño informes Partes: **piloto** IA DevExpress 26.1 en sidecar GEN-15. Validar Prompt-to-Report + schema semántico Consulta detallada. **No** reimplementar diseñador ni contratos envelope 470x. SoT: SPEC-015 + TR-015. Host: `partes.informes.consultaDetallada`, `emission.design`, sidecar `dx-reporting`.

**Plantilla regla 19 (oleada 2 — ejemplo camino B):**  
Diseño informes Partes: adoptar **GEN-03** en `/emisiones/disenador` tras veredicto piloto. UI/motor = `SmartCapturePanel` + turno host. **No** reimplementar panel GEN. SoT: SPEC-015 + TR-016.

---

## 1. Resumen ejecutivo

- **Problema:** quien diseña el informe de Consulta detallada debe dominar el diseñador DevExpress; pedidos en lenguaje natural acelerarían layout, agrupaciones y expresiones sin desarrollo ad hoc.
- **Resultado esperado (oleada 1 — Must):** evidencia reproducible de **IA DevExpress Reporting 26.1** en el sidecar Framework, con **schema semántico** de campos del proceso Must, smoke desde Partes en `/emisiones/disenador`, y **matriz veredicto A/B/C** que fija la oleada 2.
- **Resultado esperado (oleada 2 — condicional):** productizar el camino elegido (**A** IA nativa DX, **B** Smart Capture GEN-03, **C** híbrido) **sin** reabrir Emitir, dataset de consulta ni envelope GEN-15 existente (SPEC-011).

---

## 2. Alcance

### 2.1 En alcance — oleada 1 (piloto Must)

| # | Ítem |
|---|------|
| 1 | Extender **metadata de campos** del proceso `partes.informes.consultaDetallada` (nombre natural, descripción breve, sinónimos opcionales) consumible por IA — vía `ProcessFieldSchemaCatalog` / `data/schemas/*.json` en sidecar Framework y alineado al puerto PHP [`PartesConsultaDetalladaEmissionPort`](../../../backend/app/Services/Emissions/PartesConsultaDetalladaEmissionPort.php). |
| 2 | Habilitar y probar capacidades **IA DevExpress 26.1** documentadas (generación Prompt-to-Report, expresiones, datos de prueba; modificación sobre REPX existente **según lo que confirme el piloto**). |
| 3 | Validar **licencia**, costo/consumo y compatibilidad **BYOK GEN-16** vs configuración exigida por DevExpress (global del servicio vs credencial por usuario — cerrar en A1/TR-015). |
| 4 | Ejecutar **batería de pruebas funcionales** §4.4 (español) sobre Consulta detallada; documentar evidencia (capturas, logs, REPX resultante). |
| 5 | Entregar **matriz F1** §5 (veredicto **A**, **B** o **C**) antes de iniciar TR-016 / implementación productiva. |
| 6 | Smoke Partes: flujo existente selección proceso → confirmar → diseñador DX **sin regresión**; persistencia Save / Save As GEN-15 intacta. |

### 2.2 En alcance — oleada 2 (condicional; redactar TR-016 tras F1)

| Veredicto | Contenido mínimo |
|-----------|------------------|
| **A — DX suficiente** | Productizar configuración sidecar + schema semántico + runbook operación; manual usuario diseñador con IA nativa. |
| **B — Smart Capture** | Adoptar **GEN-03**: `SmartCapturePanel` bajo el DX en el mismo host Partes (`ReportDesignerHostPage` / `renderDesigner`); turno host; acciones layout; excepción documentada a GEN-03 §2 (diseñador documental ≠ informe consulta). |
| **C — Híbrido** | UI Smart Capture (consistencia Carga diaria) + backend que invoca **API Prompt-to-Report** DevExpress con schema semántico; recarga diseñador; BYOK GEN-16. |

En todos los caminos: **persistencia** = Save del diseñador DevExpress + APIs `/api/v1/emissions/design/...` (SPEC-011); la IA **no** persiste layout en un turno paralelo.

### 2.3 Fuera de alcance

- Redefinir motor GEN-15 (orquestador Emitir, `EmissionDialog`, envelope `4700–4799`, async jobs).
- Cambiar universo de emisión, `selectedTareaIds` o filtros de Consulta detallada (SPEC-011).
- IA en **Emitir**, Consulta detallada, dashboard, mobile o procesos emisibles futuros **sin** ampliación explícita de SPEC.
- Chat documental (SPEC-008) mutando REPX.
- Nuevo ítem de menú lateral solo para IA (superficie = diseñador existente §4.8 SPEC-011).
- RAG / historial servidor de diseño (v1 = hilo cliente o UI DX según camino).
- Bump `@paqsuite/react-core` **obligatorio** en oleada 1 (solo si el piloto lo exige documentado en TR-015).

---

## 3. Actores y contexto

| Actor | Diseñador + IA (desktop) |
|-------|---------------------------|
| Usuario con **`emission.design`** + desktop | Sí — único actor del alcance |
| Usuario solo con Emitir (menú consulta) | **No** diseña; no monta asistente de diseño |
| Mobile / native | **No** (coherente SPEC-011 §4.9) |
| Cliente / sin Partes usable | No opera shell (SPEC-002) |

**Precondiciones**

- SPEC-011 adoptado: `/emisiones/disenador`, sidecar DX configurado (`VITE_DX_REPORTING_*`), proceso Must seedeado.
- GEN-16 operativo en instalación (TR-008) cuando el piloto o producto use BYOK PaqSuite.
- Oleada 2 camino B/C: pin `@paqsuite/react-core` con exports Smart Capture (como SPEC-010).

---

## 4. Comportamiento funcional

### 4.1 Flujo UX común (Must — no cambiar SPEC-011)

| ID | Norma |
|----|--------|
| R-ID-01 | El usuario **selecciona proceso emisible** y **confirma** antes del diseñador DX (GEN-15 Q13). MVP: un solo proceso Must. |
| R-ID-02 | La asistencia IA opera sobre el **reporte seleccionado** y el **schema autorizado** de ese proceso; **no** inventa campos fuera del catálogo. |
| R-ID-03 | Tras cambios propuestos por IA, el usuario **revisa** en el diseñador y **guarda** con Save / Save As DevExpress; catálogo GEN actualiza vía callbacks existentes. |
| R-ID-04 | Mismo shell Partes: `ProcessPageLayout`, `EmissionReportDesignerPage`, `DxReportDesignerPanel` (sin segundo diseñador paralelo). |

### 4.2 Schema semántico (Must oleada 1)

| ID | Norma |
|----|--------|
| R-ID-10 | Cada campo técnico del dataset Consulta detallada expone **label** (nombre natural ES), **description** opcional y **synonyms[]** opcional (ej. técnico → `usuarioNombre`, labels: Técnico, operador). |
| R-ID-11 | La metadata es **por proceso**, no por variante `.repx`; reutilizable en oleada 2 y en futuros consumidores (Smart Capture, consultas NL) sin diccionario duplicado. |
| R-ID-12 | Campos ERP / negocio no semánticos en el mensaje del usuario → rechazo o mensaje claro (prueba §4.4 #7). |

### 4.3 Persistencia y seguridad (Must)

| ID | Norma |
|----|--------|
| R-ID-20 | Prohibido persistir layout REPX desde un “turno IA” sin pasar por Save DX + APIs design GEN ya adoptadas. |
| R-ID-21 | No ampliar permisos: solo quien tiene **`emission.design`** accede al diseñador y a la IA de diseño. |
| R-ID-22 | Oleada 1: **no** alterar rutas `/api/v1/emissions/*` ni códigos envelope 4701–4712. |

### 4.4 Batería piloto (Must — evidencia TR-015 / F1)

| # | Escenario | Criterio éxito mínimo |
|---|-----------|------------------------|
| 1 | «Listá los partes por fecha y cliente» | Layout generado usa solo campos autorizados; visible en diseñador. |
| 2 | «Agrupá por cliente ordenados por fecha» | Agrupación / orden coherente con schema. |
| 3 | «Totalizá minutos por técnico y mostrá horas» | Expresión numérica razonable (minutos → presentación horas según diseño). |
| 4 | «Solo partes sin cargo» | Filtro / condición en layout, no datos inventados. |
| 5 | «Formato horizontal, título, encabezado, número de página» | Presentación aplicada. |
| 6 | Sobre informe ya abierto: «Agregá subtotal de horas» | **Modificación** preserva resto del layout (evaluar calidad; driver veredicto A vs B/C). |
| 7 | «Agregá rentabilidad de cada parte» | **Rechazo** o respuesta sin campo inexistente. |
| 8 | Guardar como variante | Save As / catálogo GEN-15 OK (código reporte, principal opcional). |

### 4.5 Oleada 2 — Smart Capture (solo camino B/C; detalle en TR-016)

| ID | Norma |
|----|--------|
| R-ID-30 | Montaje: panel GEN **debajo** del DX dentro de `renderDesigner`; BYOK y Preferencias iguales a SPEC-010. |
| R-ID-31 | Turno: envelope GEN-03 v1; authz `emission.design`; mobile excluido. |
| R-ID-32 | Acciones layout: whitelist documentada en TR-016; no XML REPX crudo sin validación desde LLM. |

---

## 5. Matriz veredicto F1 (Must salida oleada 1)

Documento en `docs/04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-*.md` (o equivalente Parte F1).

| Dimensión | Pregunta | Impacto |
|-----------|----------|---------|
| Licencia / costo DX IA | ¿Permitido en nuestra suscripción? | Go / no-go piloto productivo |
| BYOK | ¿GEN-16 por usuario compatible con requisito DX? | A vs configuración servicio |
| Generar (pruebas 1–5) | ¿Calidad aceptable ES? | A probable |
| Modificar (prueba 6) | ¿Web 26.1 alcanza? | Si no → B o C |
| Regresión GEN-15 | ¿Save / catálogo OK? | Must pass |
| Esfuerzo SC | ¿Costo camino B vs A? | Decisión económica |

**Decisión:** una fila **Veredicto = A | B | C** firmada en F1 desbloquea redacción C1 de TR-016.

---

## 6. Criterios de aceptación SPEC (verificables en F oleada 1)

- [ ] **CA-15-01** Schema semántico Consulta detallada desplegado en sidecar (evidencia archivo o catálogo).
- [ ] **CA-15-02** IA DevExpress habilitada en entorno piloto (evidencia config + doc DevExpress).
- [ ] **CA-15-03** Pruebas §4.4 #1–#5 ejecutadas con resultado documentado.
- [ ] **CA-15-04** Prueba #6 ejecutada; conclusión explícita sobre modificación web.
- [ ] **CA-15-05** Prueba #7 demuestra no invención de campos.
- [ ] **CA-15-06** Prueba #8 — Save As / catálogo sin romper SPEC-011.
- [ ] **CA-15-07** Matriz §5 completa con veredicto A/B/C.
- [ ] **CA-15-08** Smoke Partes `/emisiones/disenador` sin regresión (E2E o checklist TR-015).

Oleada 2: CA adicionales en TR-016 / HU-015 según veredicto.

---

## 7. Trazabilidad GEN

| GEN | Rol en SPEC-015 |
|-----|-----------------|
| **GEN-15** | Diseñador, catálogo reportes, APIs design, sidecar DX — **adoptar**, no redefinir |
| **GEN-16** | BYOK / timeout — prereq piloto y producto |
| **GEN-03** | Oleada 2 caminos B/C — adoptar panel y turno; ficha regla 44 |
| **GEN-20** | Envelope turno SC si camino B/C |

---

## 8. Riesgos y decisiones A1 (cerradas 2026-10-30)

Informe: [`SPEC-015-ia-diseno-informes-A1-ambiguity-review.md`](./SPEC-015-ia-diseno-informes-A1-ambiguity-review.md). **Veredicto: Apto con observaciones.**

| ID | Tema | Decisión |
|----|------|----------|
| **D-A1-01** | Modificación web vs WinForms | No paridad Must; prueba §4.4 #6 = input matriz F1. |
| **D-A1-02** | BYOK GEN-16 vs DX sidecar | Piloto camino A: credencial LLM en **entorno sidecar** (startup .NET), no auto-sync `LlmPreferencesPanel`. B/C: GEN-16 en turno host. Ver observación O-15-01. |
| **D-A1-03** | Versión FE vs sidecar | Prod: reporting **26.1** + sidecar **26.1**; `UseDevelopmentMode` solo lab local. |
| **D-A1-04** | GEN-03 “no informes” | Excepción solo diseñador + `emission.design` (oleada 2 B/C). |
| **D-A1-05** | TR-016 antes de F1 | Prohibido producto oleada 2 antes de veredicto F1. |
| **D-A1-06** | Licencia DX + NuGet IA | TR-015 verifica wizard IA en lab; go/no-go licencia. |
| **D-A1-07** | Schema semántico | Must `label` todas columnas; Should synonyms en campos ambiguos; JSON host, sin hardcode C# Partes en sidecar. |

### Observaciones (no bloquean HU)

- **O-15-01:** Unificar BYOK usuario entre chat y DX nativo queda **post-piloto** si camino A.
- **O-15-02:** Ciclo OpenSpec Framework para ia-diseno GEN aún **pendiente** (update-4 es producto).

---

## 9. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-30 | Parte A0: Epic PIAPARTES-53; origen producto IA diseño. |
| 2026-10-30 | Parte A: SPEC-015 (oleada piloto DX + producto condicional A/B/C). |
| 2026-10-30 | Encuadre GEN: Framework update-4; producto Partes `19-ia-diseno-informes.md` (renumerado desde 18). |
| 2026-10-30 | Parte A1: **Apto con observaciones**; decisiones D-A1-01…07. |
| 2026-10-30 | Parte B: [HU-015](../../03-historias-usuario/100-SistemaPartes/HU-015-ia-diseno-informes.md). |
| 2026-10-30 | Parte C: [TR-015](../../04-tareas/100-SistemaPartes/TR-015-piloto-dx-reporting-ia.md). |
| 2026-10-30 | Parte C1: TR **Apto con observaciones** → ciclo **Especificado**. |
| 2026-10-30 | Parte D: [D-VERIFICACION-TR-015](../../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-2026-10-30.md) (F1 en curso; veredicto pendiente batería IA). |
