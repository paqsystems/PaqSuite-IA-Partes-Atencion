# SPEC-016 – Prompt-to-Report IA sobre informes existentes

Jira: [PIAPARTES-58](https://paqsystems.atlassian.net/browse/PIAPARTES-58)

## Encuadre host vs GEN

| Capa | Documento SoT |
|------|---------------|
| Capacidad transversal | Framework GEN-15 / sidecar `dx-reporting` / DevExpress Reporting 26.1 |
| Adopción Partes | Este SPEC: diseñador existente `/emisiones/disenador`, proceso `partes.informes.consultaDetallada`, schema y evidencia |
| Base funcional | [SPEC-015](./SPEC-015-ia-diseno-informes.md), especialmente §4.4 escenario #6 |

Este SPEC define la siguiente evolución del piloto IA: asistir la edición de un informe ya confeccionado. No redefine GEN-15 ni crea un diseñador paralelo.

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-016 |
| Título | Adopción Partes — Prompt-to-Report IA sobre informes existentes |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | A1 aclarado funcionalmente · HU/TR **Especificado** (C1 — 2026-11-06) |
| Última actualización | 2026-11-06 |
| Jira | [PIAPARTES-58](https://paqsystems.atlassian.net/browse/PIAPARTES-58) |
| HU relacionada(s) | [HU-016-ia-edicion-informes-existentes](../../03-historias-usuario/100-SistemaPartes/HU-016-ia-edicion-informes-existentes.md) · [PIAPARTES-59](https://paqsystems.atlassian.net/browse/PIAPARTES-59) |
| TR relacionada(s) | [TR-016-edicion-ia-informes-existentes-prompt-to-report](../../04-tareas/100-SistemaPartes/TR-016-edicion-ia-informes-existentes-prompt-to-report.md) · [PIAPARTES-60](https://paqsystems.atlassian.net/browse/PIAPARTES-60) |
| Depende de | GEN-15 / SPEC-011; [SPEC-015](./SPEC-015-ia-diseno-informes.md); sidecar `dx-reporting` 26.1; permiso `emission.design` |

## 1. Resumen ejecutivo

El piloto actual demuestra generación Prompt-to-Report al iniciar un informe nuevo, pero no cierra el flujo de modificación conversacional sobre una variante `.repx` ya guardada.

El resultado esperado es que, con un informe existente ya cargado en el diseñador, un usuario autorizado pueda solicitar un cambio incremental en lenguaje natural, revisar el resultado dentro del diseñador y guardarlo mediante los mecanismos GEN-15 existentes.

## 2. Alcance

### 2.1 En alcance

- Exponer Prompt-to-Report, o una superficie web equivalente acordada con DevExpress 26.1, sobre el informe `.repx` que el usuario ya seleccionó y cargó en el diseñador.
- Aplicar prompts incrementales sobre ese layout existente, preservando las partes no solicitadas del informe.
- Usar el schema semántico autorizado del proceso `partes.informes.consultaDetallada`.
- Validar como escenario principal: «Agregá subtotal de horas».
- Permitir revisión humana antes de persistir.
- Mantener Save y Save As mediante callbacks y APIs GEN-15 existentes.
- Conservar operativo el flujo de creación de informes nuevos.
- Producir evidencia reproducible: captura, logs relevantes y REPX resultante.

### 2.2 Fuera de alcance

- Reemplazar `EmissionReportDesignerPage`, `DxReportDesignerPanel` o GEN-15.
- Cambiar Emitir, filtros, dataset, `selectedTareaIds` o envelope de SPEC-011.
- Persistir automáticamente cambios desde el turno IA.
- Crear un menú o ruta separada exclusivamente para IA.
- Llevar el flujo a mobile.
- Implementar Smart Capture GEN-03 o unificar BYOK GEN-16 con la credencial singleton del sidecar dentro de este SPEC.
- Exigir paridad con WinForms si la superficie web documentada no la ofrece.

## 3. Actores y contexto

| Actor | Permiso / contexto |
|-------|--------------------|
| Diseñador de informes | Tiene `emission.design` y abre una variante existente |
| Sidecar DX | Tiene IA habilitada y credencial configurada, o informa degradación segura |
| Sistema | Carga el informe y el schema autorizado del proceso antes del prompt |

Precondiciones:

- El usuario accede al diseñador mediante el flujo GEN-15 existente.
- Existe una variante `.repx` guardada en el catálogo del proceso.
- El sidecar y la versión FE están alineados con DevExpress Reporting 26.1.

## 4. Comportamiento funcional

### 4.1 Flujo principal

1. El usuario selecciona o abre una variante ya confeccionada.
2. El diseñador carga el layout existente y el schema del proceso.
3. El usuario invoca la asistencia IA sin iniciar un informe en blanco.
4. El usuario formula un cambio incremental, por ejemplo: «Agregá subtotal de horas».
5. La IA propone o aplica el cambio sobre el layout cargado.
6. El usuario revisa visualmente el resultado y puede continuar editando manualmente.
7. El usuario guarda con Save o Save As mediante GEN-15.

### 4.2 Reglas de negocio

- **R-016-01:** La asistencia de edición solo está disponible dentro del diseñador autorizado por `emission.design`.
- **R-016-02:** El contexto del prompt es el informe cargado y el schema autorizado; no se deben inventar campos.
- **R-016-03:** Un cambio solicitado debe preservar el resto del layout, salvo que el usuario pida explícitamente reemplazarlo.
- **R-016-04:** La IA no persiste por sí sola: Save/Save As sigue siendo una acción explícita del usuario.
- **R-016-05:** La modificación real del REPX existente es obligatoria. Si la API o UI DX no la soporta directamente, debe definirse e implementarse una alternativa técnica aprobada por Framework/GEN antes de considerar cumplido el SPEC; informar una limitación no cuenta como cumplimiento funcional.
- **R-016-06:** Sin credencial o disponibilidad LLM, la edición manual del informe debe continuar funcionando y la degradación debe ser visible.
- **R-016-07:** El flujo Prompt-to-Report de creación nueva no debe romperse.

## 5. Criterios verificables

- [ ] **CA-016-01** Con un `.repx` existente abierto, el usuario puede acceder a la asistencia sin iniciar un informe nuevo.
- [ ] **CA-016-02** El prompt «Agregá subtotal de horas» produce un cambio visible y revisable sobre el informe existente ya cargado.
- [ ] **CA-016-03** El resto del layout existente se conserva cuando el cambio incremental es válido.
- [ ] **CA-016-04** Save y Save As persisten mediante el contrato GEN-15 existente.
- [ ] **CA-016-05** La creación de informes nuevos continúa operativa.
- [ ] **CA-016-06** Un campo inexistente se rechaza o no se inventa.
- [ ] **CA-016-07** Sin IA disponible, la edición manual no queda bloqueada.
- [ ] **CA-016-08** La evidencia incluye entorno, prompt, resultado, REPX y logs necesarios para repetir la prueba.

## 6. Impacto técnico (visión para TR)

- **Frontend:** `ReportDesignerHostPage` y superficie `EmissionReportDesignerPage` / `DxReportDesignerPanel`; sin segundo diseñador.
- **Sidecar:** validar soporte real de modificación sobre reporte cargado en Web Report Designer 26.1; cualquier cambio transversal debe quedar en Framework.
- **Schema:** reutilizar metadata por `processCode`; no hardcodear campos del producto en C# del sidecar.
- **Persistencia:** conservar APIs de diseño y callbacks GEN-15; no agregar una vía paralela de guardado.
- **Seguridad:** reutilizar `emission.design`, headers y envelope vigentes.
- **Evidencia:** ampliar la batería de SPEC-015 con el escenario de edición y su regresión de creación nueva.

## 7. Riesgos y decisiones pendientes para A1

- DevExpress puede exponer Prompt-to-Report solo en el wizard de creación web; la implementación deberá resolver ese gap mediante una alternativa Framework/GEN compatible con el requisito de edición.
- La API `GeneratePromptToReportAsync` sobre un reporte existente podría no estar disponible para el flujo web; no asumir paridad con consola ni dar el SPEC por cumplido con una limitación.
- Si la vía nativa DX no alcanza, la alternativa técnica debe definirse en Framework/GEN; no se implementará un motor local en Partes.
- La credencial del sidecar puede ser de servicio y no la preferencia BYOK del usuario; este SPEC no resuelve esa unificación.

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-05 | Parte A: SPEC-016 creado desde el hito CC-ST #2 para edición IA sobre informes existentes. |
| 2026-11-05 | A1 aclaración de alcance: la IA debe modificar el informe ya cargado en el diseñador; documentar una limitación no es cumplimiento. |
| 2026-11-06 | Parte B: HU-016 derivada exclusivamente desde este SPEC. |
| 2026-11-06 | Parte C: TR-016 generada desde SPEC-016 + HU-016 (Jira PIAPARTES-60). |
| 2026-11-06 | Parte C1: TR-016 apta con observaciones — ciclo SPEC + HU + TR **Especificado**. |

