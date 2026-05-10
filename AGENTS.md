
# AGENTS.md – Guía del proyecto para el Agente (Cursor / IA)

## 1) Contexto
Este repositorio contiene el desarrollo de un **MVP web** para consultorías y empresas de servicios.

El sistema permite que cada empleado/asistente registre tareas diarias indicando:
- Fecha
- Cliente
- Proyecto 
- Descripción de la tarea
- Duración ( en lapsos de 15 minutos) 

Con esto se obtienen **informes de dedicación por cliente/proyecto** para análisis operativo, comercial y/o facturación.

> Regla de oro del MVP: priorizar valor completo del flujo E2E, sin sobre–ingeniería.

---

## 2) Consignas obligatorias (resumen)
El proyecto debe cumplir:
1. Definir un flujo E2E prioritario con principio/fin claros.
2. Planificar ese flujo con:
   - 3–5 historias Must-Have
   - 1–2 historias Should-Have
3. Producir artefactos:
   - Documentación de producto
   - Historias + tickets trazables
   - Arquitectura + modelo de datos
   - Backend (API + DB)
   - Frontend navegable
   - Tests (unit + integración + al menos 1 E2E)
   - Infra + deploy (CI/CD básico, secretos, URL accesible)

Ver detalle completo en: `docs/consignas-mvp.md`

---

## Open-Spec (metodología interna, sin CLI externo)

Para alcance formal antes de implementar:

- **Especificaciones:** `docs/05-open-spec/` (plantilla `_template-spec.md`, guía `guia-adopcion-open-spec.md`).
- **Flujo nuevo alcance (A–F):** **A→B→C→D→E→F** en el dispatcher — mismas letras en orden alfabético que en ejecución: SPEC (`openspec-01`) → HU (`openspec-02`) → TR (`openspec-03`) → ejecutar TR → tests → verificación (`openspec-04`).
- **Correcciones / CC:** **G→D→E→F→H→I** — **Parte G** (§0 = SPEC-update si cambia el «qué» + volcado CC), luego **D**/**E**/**F**, **H** (cerrar control), **I** (unificar; SPEC punto **11** antes que HU/TR).
- **Comandos en lenguaje natural:** Partes **A–Q** del dispatcher `.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md`.
- **Estado intermedio HU/TR:** **Especificado** (cuando SPEC y derivados están alineados): ver `07-estado-hu-tr.md` en la misma carpeta.
- **Documentación compartida:** `docs/_base/_OPEN-SPEC-METODOLOGIA.md`.

---

## 3) Flujo E2E prioritario (MVP)
**(COMPLETAR cuando se defina)**

Ejemplo típico para este proyecto:
- Registro → Login → Carga de tarea → Ver resumen (por día/semana) → Logout

Regla: toda historia/ticket/código/test debe soportar este flujo.

---

## 4) Historias Must / Should
Definición y criterios de aceptación en:
- `docs/_projects/SistemaPartes/hu-historias/` (historias)
- `docs/_projects/SistemaPartes/hu-tareas/` (tareas técnicas)
Esto es un ejemplo de un solo módulo o proyecto que contemplará toda la solución

Reglas:
- Must-Have: indispensables para que el flujo E2E tenga valor completo.
- Should-Have: mejoras opcionales, solo si no ponen en riesgo lo Must.

---

## 5) Artefactos y dónde se mantienen
Este repo mantiene la documentación en `/docs`:

- `docs/00-contexto/`  
  Contexto institucional, onboarding, guías corporativas.

- `docs/01-arquitectura/`  
  Arquitectura técnica, modelo de datos, seguridad, roadmap (ver `docs/01-arquitectura/README.md`).

- `docs/_projects/SistemaPartes/`  
  Historias de usuario (hu-historias/) y tareas técnicas (hu-tareas/) del módulo Partes.

- `docs/arquitectura.md`  
  Visión general del sistema (3 capas, web+mobile).

- `.cursor/rules/12-testing.md`  
  Estrategia, qué se testea, cómo correr tests, y el/los E2E.

- `docs/frontend/devextreme-norms.md`  
  Licencia, estilos y **principio de uso: comportamiento nativo DevExtreme primero** (evitar duplicar acciones que el widget ya ofrece).

- `.cursor/rules/33-devextreme-prefer-native-behavior.md`  
  Regla Cursor: priorizar comandos/flujos nativos de DevExtreme; grillas en detalle en `24-devextreme-grid-standards.md`.

- `.cursor/rules/34-obtencion-datos-performance.md`  
  Rendimiento y obtención de datos (API, tenant `company`, React): paginación, evitar N+1, warmup, medición. Operación: `docs/06-operacion/instructivo-optimizar-velocidad.md`; patrones del repo: `.cursor/Docs/eficiencia-tecnica.md`.

- `.cursor/rules/29-ui-catalogos-fk-codigo-descripcion.md`  
  Catálogos y FKs en UI: código y descripción en listados y selectores de catálogo; no mostrar IDs al usuario salvo excepción explícita en HU/TR. **§3** define nomenclatura (**selector de catálogo**; en código: `SelectBox`, `TagBox`, `Lookup`, etc.). **§3.1:** «Cargando…» obligatorio junto al caption mientras se obtienen opciones por API; en DevExtreme también `noDataText` durante la carga.

- `.cursor/rules/27-parametros-generales-por-modulo.md` / `.cursor/rules/28-plan-tareas-hu-parametros-generales.md`  
  Parámetros por módulo (`PQ_PARAMETROS_GRAL` en Company DB): encabezado **`X-Company-Id`**, filtro por `Programa`, coherencia con seeds y menú.

- `.cursor/rules/32-parametros-generales-ui-listado-y-edicion-por-tipo.md`  
  Pantalla de parámetros: **listado** solo lectura (valor como texto) + **Editar** con control por tipo (modal o panel en la misma ruta).

- `.cursor/rules/30-ui-abm-grilla-alta-edicion-modal.md`  
  ABM y transacciones con grilla inicial: alta/edición en **modal** sobre el listado por defecto; otra UI solo si HU/TR lo indica.

- `.cursor/rules/35-ui-formularios-carga-caption-izquierda.md`  
  Formularios de **carga/edición de datos**: caption del campo a la **izquierda** del control (no encima), salvo excepción en HU/TR.

- `.cursor/rules/31-estado-hu-tr.md` — valores del campo **Estado** en metadatos de HU y TR (Pendiente, Pendiente de Revisión, En Control Calidad, Finalizado)

- `docs/06-operacion/deploy-infraestructura.md`  
  Infra, pipeline, secretos, URL, cómo desplegar local/remote.

---

## 6) Reglas de trabajo para el agente IA
### 6.1 Alcance y priorización
- Priorizar el flujo E2E.
- Evitar features “nice to have” hasta cerrar Must-Have.
- No agregar dependencias pesadas sin justificación.

### 6.2 Calidad mínima del código (MVP, pero profesional)
- Nombres claros, estructura simple.
- Validaciones básicas del dominio.
- Manejo de errores consistente.
- Logs mínimos donde aporten valor.

### 6.3 Tests (obligatorio)
- Unit tests: lógica de dominio/servicios.
- Integration tests: API + DB (o repositorio) con fixtures.
- E2E: al menos 1 caso del flujo principal.
- **Por cada tarea/historia en frontend:** añadir ambos tipos — unitarios (Vitest en `src/`) y E2E (Playwright en `tests/e2e/`). Al cerrar: ejecutar `npm run test:all` en `frontend/`.
- Mantener instrucciones y checklist en `.cursor/rules/12-testing.md`.

### 6.4 Trazabilidad
Cada cambio relevante debe dejar rastro:
- Referenciar historia/ticket en commits (si se usa git con convención).
- Actualizar el doc correspondiente en `/docs` cuando cambie el alcance o diseño.

---

## 7) Integraciones / MCP / automatización (opcional)
Si el proyecto usa MCP (Jira, Playwright, DB), documentar configuración y variables en:
- `docs/agentes-y-mcp.md` (si existiera)
o en:
- `docs/06-operacion/deploy-infraestructura.md` (si afecta ejecución)

Nunca incluir credenciales reales en el repo.
Usar `.env.example` con placeholders.

---

## 8) Definition of Done (DoD) del MVP
Un Must-Have se considera terminado cuando:
- Cumple criterios de aceptación
- Tiene tests correspondientes (unit o integración, según aplique)
- No rompe el E2E
- Está documentado lo necesario
