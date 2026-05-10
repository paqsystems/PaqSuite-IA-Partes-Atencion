# Guía de adopción — Open-Spec interno (PaqSuite)

Este documento resume **decisiones**, **flujo**, **archivos tocados** y **migración gradual** para el Open-Spec interno en metodología HU/TR, **sin** CLI `@fission-ai/openspec`.

---

## 1. Decisiones

- Open-Spec **interno**: Markdown en `docs/05-open-spec/`, reglas en `.cursor/rules/base/`, prompts en `prompts/`.
- **SPEC** = fuente de verdad de alcance detallado; **HU** = negocio/CA; **TR** = ejecución técnica.
- Correcciones de alcance: **`SPEC-update`** primero, luego HU-update / TR-update.
- Unificación de SPEC-update en base con la **misma lógica de Estado: Finalizado** que HU/TR.
- Verificación post-implementación: **`openspec-04-verificar-implementacion.md`** (paralelo conceptual a `/opsx:verify`).

---

## 2. Flujo metodológico (paso a paso)

1. Contexto (producto, ticket, `docs/02-producto/`).  
2. **SPEC** (**A**, `prompts/openspec-01-SPEC-desde-contexto.md`) en `docs/05-open-spec/<subcarpeta>/` (plantilla `_template-spec.md`).  
3. **HU** (**B**, `openspec-02-HU-desde-SPEC.md`).  
4. **TR** (**C**, `openspec-03-TR-desde-SPEC-y-HU.md`).  
5. **Ejecutar TR** (Parte **D**).  
6. **Tests** (Parte **E**).  
7. **Verificación documental** (Parte **F**, `openspec-04-verificar-implementacion.md`).  
8. CC / correcciones: **G → D → E → F → H → I** (dispatcher § 2); **§0** en **G** = SPEC-update obligatorio si cambia el alcance.  
9. **Unificar** updates **Finalizado** (Parte **I**; SPEC punto **11** antes que HU/TR; **Q** solo SPEC).  

Referencia viva: `docs/_base/_OPEN-SPEC-METODOLOGIA.md`.

---

## 3. Archivos y reglas (implementación)

### Creados (proyecto / BASE)

| Ruta | Rol |
|------|-----|
| `docs/05-open-spec/README.md` | Índice local de Open-Spec. |
| `docs/05-open-spec/_template-spec.md` | Plantilla SPEC. |
| `docs/05-open-spec/guia-adopcion-open-spec.md` | Este documento. |
| `PaqSuite-IA-BASE/.cursor/prompts/openspec-01-SPEC-desde-contexto.md` | SPEC desde contexto (**A**). |
| `PaqSuite-IA-BASE/.cursor/prompts/openspec-02-HU-desde-SPEC.md` | HU ← SPEC (**B**). |
| `PaqSuite-IA-BASE/.cursor/prompts/openspec-03-TR-desde-SPEC-y-HU.md` | TR ← SPEC + HU (**C**). |
| `PaqSuite-IA-BASE/.cursor/prompts/openspec-04-verificar-implementacion.md` | Verificación docs ↔ código (**F**). |
| `PaqSuite-IA-BASE/.cursor/rules/00-arquitectura/08-open-spec-gobernanza.md` | Gobernanza SPEC / updates / unificación. |

### Actualizados (BASE, compartidos vía `rules/base`)

| Ruta | Cambio |
|------|--------|
| `01-prompts-programados-dispatcher.md` | Tabla **A–Q** en orden alfabético igual al de las secciones; **A–F** núcleo (alfabético = cronológico); **G–I** CC / cierre / unificar; **J–M** atajos; **N–Q** transversales. |
| `07-estado-hu-tr.md` | Estado **Especificado** y vínculo con SPEC. |
| `04-user-story-to-task-breakdown.md` | Campo **SPEC relacionada** y trazabilidad TR ↔ SPEC. |
| `90-documentacion/90-manual-usuario.md` | SPEC como referencia funcional cuando exista. |

### Actualizados (repo producto)

| Ruta | Cambio |
|------|--------|
| `AGENTS.md` | Mención Open-Spec y rutas. |
| `docs/README.md` | Fila / flujo `05-open-spec/`. |

---

## 4. Convención de nombres

- Archivos: `SPEC-XXX-slug-corto.md` (XXX alineado a HU/TR del mismo número cuando aplique).  
- Updates: `SPEC-XXX-slug-update.md`, `SPEC-XXX-slug-update-01.md`, … en `docs/05-open-spec/updates/<subcarpeta>/`.  
- Detalle: regla **08-open-spec-gobernanza**.

---

## 5. Migración gradual (historias ya existentes)

| Situación | Acción recomendada |
|-----------|--------------------|
| HU/TR sin SPEC | Opcional: crear SPEC **a posteriori** desde HU+TR (comando **A** o instrucción explícita) y enlazar; no bloquea trabajo ya **Finalizado**. |
| Trabajo activo | Antes de nueva implementación mayor, agregar SPEC o SPEC-update y enlazar. |
| Solo fixes de CC | Si el CC es puramente técnico/bug, puede bastar HU-update/TR-update; si cambia **alcance** acordado, **SPEC-update** obligatorio primero. |

---

## 6. Checklist operativo diario

- [ ] SPEC actualizado y enlazado desde HU y TR.  
- [ ] Tras implementación: **Q** (`openspec-04`) + **D** (tests) cuando el cambio sea sustancial.  
- [ ] Updates solo unificados con **Estado: Finalizado** (HU, TR, SPEC).  
- [ ] Consultar dispatcher para partes **A–Q** (núcleo **A–F** + operación **G–M** + atajos **N–Q**).  

---

## 7. Pendiente evolutivo (opcional)

- Afinar textos de comandos del dispatcher según uso del equipo.  
- Alinear metadatos **Estado** del SPEC en tablas de todos los SPEC legacy que se creen manualmente.

---

*Integración alineada al plan `adopcion_open-spec_630dac74.plan.md` (raíz del repo).*
