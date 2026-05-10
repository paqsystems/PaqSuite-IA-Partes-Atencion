# Open-Spec (especificaciones funcionales / técnicas)

Carpeta **`docs/05-open-spec/`** del producto: **fuente de verdad de alcance detallado** antes y durante el ciclo HU → TR → implementación.

**Nota:** el número `SPEC-NNN` se alinea al **número de HU dentro de la misma épica** (`000-Generalidades`, `001-Seguridad`, …). Puede repetirse el mismo número entre carpetas (ej. SPEC-001 generalidades vs SPEC-001 seguridad).

## SPEC de contexto (antecedente lógico a las HU)

Documentos **SPEC-CTX-***: alcance de épica redactado como **insumo previo** al paso “HU desde SPEC” (reconstrucción coherente a partir de las HUs y docs existentes; ver §8 en cada CTX).

| SPEC-CTX | Épica | Enlace |
|----------|--------|--------|
| SPEC-CTX-000 | 000 – Generalidades | [SPEC-CTX-000-generalidades.md](000-Generalidades/SPEC-CTX-000-generalidades.md) |
| SPEC-CTX-001 | 001 – Seguridad y acceso | [SPEC-CTX-001-seguridad-y-acceso.md](001-Seguridad/SPEC-CTX-001-seguridad-y-acceso.md) |

## Índice de especificaciones (operativas por HU)

### 000 – Generalidades (`000-Generalidades/`)

| SPEC | Título | HU |
|------|--------|-----|
| [SPEC-001](000-Generalidades/SPEC-001-layouts-grilla.md) | Layouts persistentes de grillas | [HU-001](../03-historias-usuario/000-Generalidades/HU-001-layouts-grilla.md) |
| [SPEC-002](000-Generalidades/SPEC-002-cambio-empresa-multi-referencia.md) | Cambio empresa (MULTI ref.; no aplica MONO) | [HU-002](../03-historias-usuario/000-Generalidades/HU-002-cambio-empresa-activa.md) |
| [SPEC-003](000-Generalidades/SPEC-003-apertura-menu-pestanas.md) | Apertura menú misma/nueva pestaña | [HU-003](../03-historias-usuario/000-Generalidades/HU-003-apertura-menu-misma-o-nueva-pestana.md) |
| [SPEC-004](000-Generalidades/SPEC-004-seleccion-idioma.md) | Selección de idioma (i18n) | [HU-004](../03-historias-usuario/000-Generalidades/HU-004-seleccion-idioma.md) |
| [SPEC-005](000-Generalidades/SPEC-005-seleccion-apariencia.md) | Apariencia / tema por usuario | [HU-005](../03-historias-usuario/000-Generalidades/HU-005-seleccion-apariencias.md) |
| [SPEC-006](000-Generalidades/SPEC-006-exportacion-excel-grillas.md) | Exportación Excel desde grillas | [HU-006](../03-historias-usuario/000-Generalidades/HU-006-exportacion-excel.md) |
| [SPEC-007](000-Generalidades/SPEC-007-parametros-generales.md) | Parámetros generales por módulo | [HU-007](../03-historias-usuario/000-Generalidades/HU-007-Parametros-generales.md) |
| [SPEC-008](000-Generalidades/SPEC-008-multilingual-banderas.md) | Multilingual e iconografía banderas | [HU-008](../03-historias-usuario/000-Generalidades/HU-008-multilingual-idiomas-banderas.md) |
| [SPEC-009](000-Generalidades/SPEC-009-eliminar-registros-grillas-abm.md) | Eliminar en grillas ABM | [HU-009](../03-historias-usuario/000-Generalidades/HU-009-eliminar-registros-grillas-abm.md) |
| [SPEC-010](000-Generalidades/SPEC-010-ayuda-externa-asistente-ia.md) | Asistente IA / ayuda externa | [HU-010](../03-historias-usuario/000-Generalidades/HU-010-acceso-ayuda-externa-chat-compartido.md) |

### 001 – Seguridad y acceso (`001-Seguridad/`)

| SPEC | Título | HU |
|------|--------|-----|
| [SPEC-001](001-Seguridad/SPEC-001-login-usuario.md) | Login de usuario | [HU-001](../03-historias-usuario/001-Seguridad/HU-001-login-usuario.md) |
| [SPEC-003](001-Seguridad/SPEC-003-cerrar-sesion.md) | Cerrar sesión | [HU-003](../03-historias-usuario/001-Seguridad/HU-003-logout.md) |
| [SPEC-004](001-Seguridad/SPEC-004-cambio-contrasena.md) | Cambio de contraseña | [HU-004](../03-historias-usuario/001-Seguridad/HU-004-cambio-contraseña.md) |
| [SPEC-005](001-Seguridad/SPEC-005-recuperacion-contrasena.md) | Recuperación de contraseña | [HU-005](../03-historias-usuario/001-Seguridad/HU-005-recuperacion-contraseña.md) |
| [SPEC-010](001-Seguridad/SPEC-010-administracion-usuarios.md) | Administración de usuarios | [HU-010](../03-historias-usuario/001-Seguridad/HU-010-administracion-usuarios.md) |
| [SPEC-012](001-Seguridad/SPEC-012-administracion-roles.md) | Administración de roles | [HU-012](../03-historias-usuario/001-Seguridad/HU-012-administracion-roles.md) |
| [SPEC-013](001-Seguridad/SPEC-013-administracion-permisos.md) | Administración de permisos (asignaciones) | [HU-013](../03-historias-usuario/001-Seguridad/HU-013-administracion-permisos.md) |
| [SPEC-014](001-Seguridad/SPEC-014-administracion-atributos-rol.md) | Administración de atributos de rol | [HU-014](../03-historias-usuario/001-Seguridad/HU-014-administracion-atributos-rol.md) |
| [SPEC-015](001-Seguridad/SPEC-015-menu-sistema-seed.md) | Menú del sistema (seed versionado) | [HU-015](../03-historias-usuario/001-Seguridad/HU-015-menu-sistema.md) |
| [SPEC-016](001-Seguridad/SPEC-016-api-menu-usuario.md) | API de menú del usuario | [HU-016](../03-historias-usuario/001-Seguridad/HU-016-api-menu-usuario.md) |
| [SPEC-017](001-Seguridad/SPEC-017-sidebar-dinamico-pqmenus.md) | Sidebar dinámico desde `pq_menus` | [HU-017](../03-historias-usuario/001-Seguridad/HU-017-sidebar-dinamico-pqmenus.md) |
| [SPEC-018](001-Seguridad/SPEC-018-seed-menu-routename-enabled.md) | Seed menú `routeName` y `enabled` | [HU-018](../03-historias-usuario/001-Seguridad/HU-018-seed-menu-routeName-enabled.md) |

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
