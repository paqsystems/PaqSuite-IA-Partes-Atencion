# Open-Spec (especificaciones funcionales / técnicas)

Carpeta **`docs/05-open-spec/`** del producto: **fuente de verdad de alcance detallado** antes y durante el ciclo HU → TR → implementación.

## Contenido típico

| Elemento | Descripción |
|---------|-------------|
| `_template-spec.md` | Plantilla para nuevos SPEC. |
| `guia-adopcion-open-spec.md` | Guía de adopción y checklist operativo (repo). |
| `*/SPEC-*.md` | Especificaciones por épica/feature (subcarpetas alineadas a HU/TR). |
| `updates/` | **SPEC-update** (misma jerarquía relativa que `docs/03-historias-usuario/updates/` y `docs/04-tareas/updates/`). |

## Metodología compartida

Documento de referencia (en repos con `docs/_base`): **`docs/_base/_OPEN-SPEC-METODOLOGIA.md`**.

## Reglas

- Gobernanza: `.cursor/rules/base/00-arquitectura/08-open-spec-gobernanza.md`
- Estados HU/TR + puente **Especificado**: `.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md`
- Dispatcher (comandos **A–Q**): `.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md`

## Prompts (raíz `prompts/`, canónicos en PaqSuite-IA-BASE `.cursor/prompts/`)

- `openspec-01-SPEC-desde-contexto.md` — SPEC desde contexto (**A**)  
- `openspec-02-HU-desde-SPEC.md` — HU desde SPEC (**B**)  
- `openspec-03-TR-desde-SPEC-y-HU.md` — TR desde SPEC + HU (**C**)  
- `openspec-04-verificar-implementacion.md` — verificación tipo `/opsx:verify` interna (**F**)  
