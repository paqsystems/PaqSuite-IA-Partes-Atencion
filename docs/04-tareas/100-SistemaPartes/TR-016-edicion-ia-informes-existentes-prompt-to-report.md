# TR-016 – Edición IA de informes existentes (Prompt-to-Report sobre REPX cargado)

> **Obsoleto funcional (MVP Partes oleada 2):** camino A cancelado. Entrega productiva = [TR-015-update-b](./TR-015-update-b-smart-capture-diseno-informes.md) (Smart Capture + patch REPX). No continuar implementación de este TR salvo decisión explícita de reabrir A.

Jira: [PIAPARTES-60](https://paqsystems.atlassian.net/browse/PIAPARTES-60)

| Campo | Valor |
|-------|-------|
| **HU relacionada** | [HU-016-ia-edicion-informes-existentes](../../03-historias-usuario/100-SistemaPartes/HU-016-ia-edicion-informes-existentes.md) |
| **SPEC relacionada** | [SPEC-016-prompt-to-report-edicion-informes](../../05-open-spec/100-SistemaPartes/SPEC-016-prompt-to-report-edicion-informes.md) |
| **Norma GEN** | Framework [`15-reportes-emisiones-update-4-ia-diseno.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-update-4-ia-diseno.md); DevExpress [Prompt-to-Report — Web Report Designer](https://docs.devexpress.com/XtraReports/405485/ai-powered-functionality/web-reporting/prompt-to-report-in-web-report-designer) |
| **Épica** | 100 — Sistema Partes ([PIAPARTES-58](https://paqsystems.atlassian.net/browse/PIAPARTES-58)) |
| **Prioridad** | MUST-HAVE |
| **Roles** | Usuario con `emission.design`; desktop; `/emisiones/disenador` |
| **Dependencias** | [TR-011](./TR-011-reportes-emisiones.md) (diseñador + sidecar); [TR-015](./TR-015-piloto-dx-reporting-ia.md) (schema + `ReportingAiBootstrap`); Framework `services/dx-reporting` 26.1; credencial LLM en entorno sidecar (igual piloto TR-015) |
| **Clasificación** | HU COMPLEJA (cross-repo Framework + host) |
| **Estado** | **Implementando** (Parte D — 2026-11-06) |
| **Revisión C1** | [TR-016-edicion-ia-informes-existentes-C1-ambiguity-review](./TR-016-edicion-ia-informes-existentes-C1-ambiguity-review.md) — **Apto con observaciones** |
| **Origen** | [HU-016](../../03-historias-usuario/100-SistemaPartes/HU-016-ia-edicion-informes-existentes.md) |
| **Referencia SPEC** | [SPEC-016](../../05-open-spec/100-SistemaPartes/SPEC-016-prompt-to-report-edicion-informes.md) |

**Plan D1:** [D1-TR-016-edicion-ia-informes-existentes](./d1/D1-TR-016-edicion-ia-informes-existentes.md)

**Plantilla regla 19:**  
Diseño informes Partes: adoptar **GEN-15** y motor IA DevExpress en sidecar Framework; UI = `EmissionReportDesignerPage` + `ReportDesigner` existente. No reimplementar diseñador ni motor Prompt-to-Report en Partes. SoT: SPEC-016 + TR-016. Host: `/emisiones/disenador`, proceso `partes.informes.consultaDetallada`, sidecar `dx-reporting`.

**Referencias técnicas:**

- Sidecar: `PaqSuite-IA-FRAMEWORK/services/dx-reporting/src/PaqSuite.DxReporting/Reporting/ReportingAiBootstrap.cs`
- Host FE: `frontend/src/features/partes/informes/ReportDesignerHostPage.tsx`, `DxReportDesignerPanel.tsx`, `DxReportDesignerApp.tsx`, `enableReportDesignerAi.ts`
- Runbook: `PaqSuite-IA-FRAMEWORK/docs/06-operacion/runbook-dx-reporting-sidecar.md`

---

## 1) HU refinada (resumen)

### In scope

- Habilitar y probar **modificación conversacional** del layout cuando el usuario tiene un **informe existente cargado** en Web Report Designer (variante ya guardada en catálogo GEN-15), no solo creación nueva vía wizard.
- Superficie de invocación y revisión **dentro** del diseñador actual; sin segunda ruta de menú ni diseñador paralelo.
- Cumplir **CA-016-02** con el prompt canónico «Agregá subtotal de horas» sobre el proceso `partes.informes.consultaDetallada` y schema ya adoptado en TR-015.
- Preservación verificable del resto del layout salvo cambio explícito solicitado (CA-016-03).
- **Sin** guardado automático desde la IA; Save / Save As solo por acción del usuario (GEN-15).
- Regresión: flujo de **informe nuevo** con Prompt-to-Report sigue operativo (CA-016-09).
- Regresión: sin credencial LLM o IA deshabilitada, **edición manual** íntegra y mensaje/degradación visible (CA-016-08).
- Evidencia reproducible: entorno, pasos, capturas, REPX antes/después, logs sidecar relevantes (CA-016 implícito en SPEC §5 CA-016-08).

### Out of scope

- Nuevas rutas Laravel `/api/v1/emissions/*` dedicadas a IA.
- Persistencia de layout fuera de callbacks Save / Save As del diseñador.
- Smart Capture GEN-03, unificación BYOK GEN-16 con sidecar por request.
- Mobile, Emitir, cambio de dataset o filtros de consulta.

### Discrepancias / resolución

- HU exige **modificación real** del informe cargado. Si la integración DX web no expone el flujo sobre REPX abierto, la TR debe implementar la **alternativa acordada en Framework/GEN** (SPEC-016 §4.2 R-016-05); documentar limitación sin implementación **no** cumple HU.

---

## 2) Criterios de aceptación (AC)

Mapeo HU → verificación técnica:

| AC HU | Verificación |
|-------|----------------|
| CA-016-01 | Con `reportUrl` de variante existente, el usuario accede a la asistencia IA sin iniciar informe en blanco; `AIServicesEnabled` u mecanismo equivalente activo tras cargar diseñador |
| CA-016-02 | Prompt «Agregá subtotal de horas» → cambio visible en lienzo (p. ej. banda de totales / expresión) sobre el mismo `reportUrl` |
| CA-016-03 | Comparación visual o diff controlado de REPX: elementos no pedidos permanecen salvo efecto colateral documentado y mitigado |
| CA-016-04 | Tras propuesta IA, el usuario puede seguir editando manualmente antes de Save |
| CA-016-05 | Save / Save As persisten vía callbacks existentes; catálogo coherente |
| CA-016-06 | Pedido con campo inexistente → rechazo o mensaje claro; sin columnas inventadas |
| CA-016-07 | Policy/host: sin `emission.design` no hay acceso (sin cambio de contrato salvo brecha detectada) |
| CA-016-08 | Sidecar sin LLM: diseñador carga; edición manual OK; indicación de IA no disponible (comportamiento DX o doc mínima si DX no expone UI) |
| CA-016-09 | Creación nueva Prompt-to-Report sigue OK (smoke breve) |

---

## 3) Reglas de implementación

| ID | Regla |
|----|--------|
| RN-TR-16-01 | **Prohibido** persistir REPX desde un turno IA fuera del flujo Save/Save As del diseñador (R-ID-20 SPEC-015). |
| RN-TR-16-02 | Trabajo transversal de integración IA en repo **PaqSuite-IA-FRAMEWORK** (`services/dx-reporting`); Partes solo cablea host + evidencia + tests de regresión. |
| RN-TR-16-03 | No hardcodear columnas del producto en C# permanente; reutilizar schema JSON `partes.informes.consultaDetallada` (TR-015). |
| RN-TR-16-04 | Sin secrets en Git; variables LLM solo en entorno sidecar / Forge (mismo patrón TR-015). |
| RN-TR-16-05 | Cualquier endpoint nuevo en sidecar debe ser operacional (health/diagnóstico) y no sustituir envelope Laravel de negocio. |
| RN-TR-16-06 | Si se requiere API .NET no documentada en Web, alinear con documentación DevExpress 26.1 y dejar trazabilidad en runbook Framework. |

---

## 4) Impacto en datos

- **Sin** migraciones Laravel ni tablas nuevas para esta TR.
- Artefactos: REPX en catálogo emisiones existente; evidencia en docs de verificación F.

---

## 5) Contratos de API

- **Sin** nuevos endpoints Laravel de producto para IA.
- Contrato operativo: `GetDesignerModel` / `DXXRD` existentes (TR-011); sidecar expone servicios DX según configuración actual.

---

## 6) Cambios Frontend (Partes)

- `DxReportDesignerApp.tsx`: hoy llama `enableReportDesignerAiServices()` en `BeforeRender`; validar que con informe **cargado** la IA sigue habilitada y documentar orden de callbacks si hace falta.
- `ReportDesignerHostPage` / SDK: sin segundo diseñador; ajustes mínimos si el SDK requiere props adicionales para escenarios de edición.
- i18n: si el producto muestra mensajes propios de degradación, usar claves producto existentes o nuevas `partes.informes.*` en 5 locales (solo si hay texto nuevo visible).

---

## 7) Plan de tareas

| ID | Repo | Tipo | Descripción | DoD |
|----|------|------|-------------|-----|
| **T1** | FW | Investigación | Releer doc DX 26.1 Prompt-to-Report para **edición** sobre reporte abierto; enumerar API/UI soportadas | Nota técnica en runbook o comentario en `ReportingAiBootstrap` con enlace doc |
| **T2** | FW | Sidecar | Diseñar e implementar el camino que **aplica cambios al modelo del diseñador** con REPX ya cargado (nativo DX o composición aprobada); integrar con `AddPromptToReportConverter` existente sin romper creación nueva | Lab: #6 SPEC-015 equivalente con informe existente + prompt canónico |
| **T3** | FW | Sidecar | Asegurar augmentación de schema/prompts coherentes con edición incremental; no inventar campos | Validate bindings sin columnas fuera del JSON |
| **T4** | Partes | FE | Verificar `enableReportDesignerAiServices` y ciclo de vida del diseñador al cambiar `reportUrl` / cargar variante | Vitest/regresión existente + smoke manual |
| **T5** | Partes | QA manual | Script de prueba: abrir variante → prompt → revisar → Save As / Save | Evidencia en `D-VERIFICACION-TR-016-*.md` |
| **T6** | FW / Partes | Docs | Ampliar runbook sidecar + nota manual usuario (opcional breve) | Operador repite en lab |

**Orden sugerido:** T1 → T2 → T3 → T4 → T5 → T6.

---

## 8) Estrategia de tests

| Capa | Alcance |
|------|---------|
| Manual Must | CA-016-01…09 en lab con sidecar + LLM |
| Partes Vitest | Regresión `ReportDesignerHostPage.test.tsx` y, si aplica, ajuste mínimo |
| E2E | Opcional; no bloquear si el flujo depende de LLM/credencial (documentar en F1) |

---

## 9) Riesgos y edge cases

| Riesgo | Mitigación |
|--------|------------|
| DX Web no expone modificación sobre REPX abierto | T1/T2: alternativa Framework; no cerrar con “doc only” |
| Cambios IA rompen bindings (XRE023) | Schema + augmentación; prueba Validate Bindings; ajuste en T3 |
| Regresión creación nueva | CA-016-09 explícito en T5 |

---

## 10) Checklist DoD (pre-implementación cerrada)

- [ ] T2 entregado en Framework o integración acordada mergeada
- [ ] Evidencia manual con REPX antes/después y prompt canónico
- [ ] Regresión Save / catálogo GEN-15
- [ ] Regresión flujo informe nuevo
- [ ] Sin rutas Laravel nuevas de negocio para IA
- [ ] Runbook actualizado

---

## 11) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-06 | Parte C: TR-016 generada desde SPEC-016 + HU-016. |
| 2026-11-06 | Parte C1: revisión de ambigüedad — **Apto con observaciones** (`TR-016-*-C1-ambiguity-review.md`). Estado → **Especificado**. |
| 2026-11-06 | Parte D1: plan [D1-TR-016-edicion-ia-informes-existentes](./d1/D1-TR-016-edicion-ia-informes-existentes.md). |
| 2026-11-06 | Parte D: Framework — augmentación edición incremental (`ProcessFieldSchemaCatalog.BuildIncrementalEditPromptAugmentation` + `ReportingAiBootstrap`); runbook §6.1 (T1/T6). Partes — FE sin cambio (T4); Vitest `ReportDesignerHostPage` OK. Evidencia parcial [D-VERIFICACION-TR-016-2026-11-06](../updates/100-SistemaPartes/D-VERIFICACION-TR-016-2026-11-06.md). **Pendiente:** batería manual CA-016 (lab con LLM) y cierre T2 según veredicto operador. |
