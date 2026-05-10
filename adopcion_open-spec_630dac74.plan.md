---
name: Adopcion Open-Spec
overview: Definiré una integración incremental de Open-Spec interno sobre el flujo HU/TR existente, sin dependencias externas, creando una plantilla oficial de SPEC y actualizando reglas/prompts para trazabilidad y compatibilidad hacia atrás.
todos:
  - id: definir-estructura-open-spec
    content: Definir estructura de carpetas Open-Spec y convención de nombres SPEC/SPEC-update
    status: completed
  - id: crear-plantilla-spec
    content: Crear plantilla oficial _template-spec.md con metadatos, estados y trazabilidad
    status: completed
  - id: actualizar-reglas-nucleo
    content: Actualizar dispatcher + reglas de estado/TR para incluir etapa Open-Spec
    status: completed
  - id: crear-prompts-open-spec
    content: Agregar prompts openspec-01…04 (SPEC, HU, TR, verificación) en BASE; enlazar desde prompts/
    status: completed
  - id: documentar-adopcion
    content: Generar guía de adopción con detalle de cambios y archivos/reglas impactados
    status: completed
isProject: false
---

# Integración Open-Spec en metodología HU/TR

## Objetivo
Incorporar Open-Spec como capa previa de definición funcional/técnica sin romper el flujo vigente `HU -> TR -> Ejecución -> updates`, manteniendo compatibilidad con comandos actuales del dispatcher.

## Decisión de alcance (cerrada)
- Se adopta **Open-Spec interno** en este repositorio.
- No se clona ni integra ningún repositorio externo de Open-Spec.
- No se agregan dependencias, CLI ni servicios de terceros para esta etapa.
- Toda la adopción se resuelve con documentación, reglas y prompts locales.

## Instalación técnica (Open-Spec interno)
- **Comandos de instalación:** No aplica.
- **Integración de GitHub externo:** No aplica.
- **Instalación de plugin:** No aplica.
- **Instalación de aplicación adicional:** No aplica.
- **Requisito técnico real:** crear/ajustar archivos Markdown de método (docs, reglas y prompts) dentro del repo.

## Alcance de implementación (cuando apruebes)
- Crear carpeta y base documental de Open-Spec.
- Crear una plantilla reutilizable de SPEC.
- Ajustar reglas de estado y trazabilidad para incluir SPEC.
- Actualizar dispatcher y prompts para operar con SPEC y SPEC-update.
- Documentar en un archivo único qué se hizo y qué queda pendiente.

## Archivos a crear
- [docs/05-open-spec/README.md](docs/05-open-spec/README.md)
- [docs/05-open-spec/_template-spec.md](docs/05-open-spec/_template-spec.md)
- [docs/05-open-spec/guia-adopcion-open-spec.md](docs/05-open-spec/guia-adopcion-open-spec.md)
- [prompts/openspec-01-SPEC-desde-contexto.md](prompts/openspec-01-SPEC-desde-contexto.md)
- [prompts/openspec-02-HU-desde-SPEC.md](prompts/openspec-02-HU-desde-SPEC.md)
- [prompts/openspec-03-TR-desde-SPEC-y-HU.md](prompts/openspec-03-TR-desde-SPEC-y-HU.md)
- [prompts/openspec-04-verificar-implementacion.md](prompts/openspec-04-verificar-implementacion.md)
- [.cursor/rules/base/00-arquitectura/08-open-spec-gobernanza.md](.cursor/rules/base/00-arquitectura/08-open-spec-gobernanza.md)

## Archivos a actualizar
- [.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md](.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md)
- [.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md](.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md)
- [.cursor/rules/base/00-arquitectura/04-user-story-to-task-breakdown.md](.cursor/rules/base/00-arquitectura/04-user-story-to-task-breakdown.md)
- [AGENTS.md](AGENTS.md)
- [docs/README.md](docs/README.md) (si existe)
- [.cursor/rules/base/90-documentacion/90-manual-usuario.md](.cursor/rules/base/90-documentacion/90-manual-usuario.md) (solo ajuste de referencia a SPEC cuando aplique)

## Diseño propuesto
- Open-Spec será fuente de verdad de alcance detallado.
- HU conserva foco de negocio y aceptación funcional.
- TR conserva foco de ejecución técnica.
- Para correcciones: primero `SPEC-update`, luego `HU-update`/`TR-update`.
- Estados HU/TR incluirán `Especificado` como puente entre `Pendiente` y `Pendiente de Revisión`.
- Trazabilidad mínima obligatoria:
  - `SPEC -> HU/TR`
  - `HU/TR -> SPEC`

## Compatibilidad y transición
- No se renombra ni mueve `docs/03-historias-usuario/` ni `docs/04-tareas/`.
- Se agrega `docs/05-open-spec/` en forma aditiva.
- Los comandos actuales siguen funcionando; se agregan comandos/ramas para SPEC.
- La unificación de updates se extiende para considerar SPEC cuando exista.

## Entregable de documentación solicitado
Además de la plantilla, se generará [docs/05-open-spec/guia-adopcion-open-spec.md](docs/05-open-spec/guia-adopcion-open-spec.md) con:
- Resumen de decisiones tomadas.
- Flujo metodológico actualizado paso a paso.
- Lista completa de archivos/reglas modificados.
- Criterios de migración gradual para historias ya existentes.

## Pasos técnicos para incorporar Open-Spec en este proyecto
0. Verificación inicial mínima:
   - Confirmar que existe `docs/` (en este repo: enlaces `docs/_base` y `docs/_mono` hacia documentación compartida), `.cursor/rules/` (enlaces `base` y `mono` hacia reglas compartidas) y carpeta `prompts/` en la raíz del repositorio (no bajo `docs/`).
   - Confirmar que el equipo acepta Open-Spec interno (sin tooling externo).
   - Confirmar nomenclatura de carpeta: `docs/05-open-spec/`.
1. Crear la carpeta base `docs/05-open-spec/` con `README.md` y plantilla `_template-spec.md`.
2. Definir convención de nombres y versionado (`SPEC-xxx-...`, `-update`, `-update-01`) alineada con HU/TR.
3. Crear regla de gobernanza de SPEC en `.cursor/rules/base/00-arquitectura/08-open-spec-gobernanza.md` (estructura, estados, trazabilidad, merge de updates).
4. Actualizar `.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md` para incluir estado `Especificado` y transición con SPEC.
5. Actualizar `.cursor/rules/base/00-arquitectura/04-user-story-to-task-breakdown.md` para exigir referencias cruzadas TR <-> SPEC.
6. Agregar prompts **`prompts/openspec-01-SPEC-desde-contexto.md`**, **`openspec-02-HU-desde-SPEC.md`**, **`openspec-03-TR-desde-SPEC-y-HU.md`** y **`openspec-04-verificar-implementacion.md`** (canónicos en **PaqSuite-IA-BASE** `.cursor/prompts/`).
7. Extender `.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md` para:
   - ciclo **Open-Spec completo** **A→B→C→D→E→F** (`openspec-01` … `openspec-04`),
   - correcciones **G→D→E→F→H→I** con **§0** / SPEC-update antes de implementar cuando cambia el alcance,
   - unificación SPEC-update en **G** (punto 11),
   - verificación implementación ↔ docs con **`openspec-04`** (paralelo interno a `/opsx:verify`).
8. Actualizar `AGENTS.md` y `docs/README.md` (si existe) para reflejar la nueva metodología oficial.
9. Ajustar `.cursor/rules/base/90-documentacion/90-manual-usuario.md` para usar SPEC como referencia funcional cuando aplique.
10. Documentar todo en `docs/05-open-spec/guia-adopcion-open-spec.md` con checklist de operación diaria.

## Operación diaria esperada (después de implementar)
1. Armar contexto del requerimiento.
2. **SPEC** (**A**, `openspec-01`).
3. **HU** desde SPEC (**B**, `openspec-02`).
4. **TR** desde SPEC + HU (**C**, `openspec-03`).
5. Ejecutar TR (**D**).
6. Tests (**E**).
7. Verificación documental (**F**, `openspec-04`).
8. Ante CC/correcciones: **G** (§0 SPEC-update si cambia el «qué» + volcado CC) → **D** → **E** → **F** → **H** → **I**.
9. Unificar updates **Finalizado**; en **I**, SPEC-update (punto **11**) antes que HU/TR cuando exista.