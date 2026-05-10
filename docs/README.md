# Guía de Navegación – Documentación del Proyecto ERP PaqSuite

Este documento sirve como **índice de navegación** para toda la documentación del proyecto. Su objetivo es orientar al programador hacia los documentos correctos según su necesidad.

---

## Estructura Documental Principal

| Carpeta | Propósito | Cuándo consultar |
|---------|-----------|-------------------|
| **`00-contexto/`** | Contexto institucional, guías, onboarding, gobierno | Al incorporarse o antes de decisiones estructurales |
| **`01-arquitectura/`** | Arquitectura técnica, modelo de datos, seguridad, roadmap | Para entender diseño y decisiones técnicas |
| **`03-historias-usuario/`** | Historias de usuario (HU) | Antes de implementar funcionalidades |
| **`04-tareas/`** | Tareas técnicas (TR) | Al ejecutar o planificar implementación |
| **`05-open-spec/`** | Especificaciones Open-Spec (SPEC), plantilla y guía de adopción | Antes de derivar HU/TR o cerrar alcance detallado |
| **`06-operacion/`** | Deploy, infraestructura, CI/CD | Al desplegar o configurar entorno |
| **`api/`** | Contrato API, OpenAPI | Al consumir o desarrollar endpoints |
| **`backend/`** | Playbook Laravel, lógica de tareas | Al desarrollar backend |
| **`frontend/`** | Especificaciones, UI Layer, i18n, testing | Al desarrollar frontend |

---

## Documentos Clave por Área

### Contexto y Arquitectura

| Documento | Propósito |
|-----------|-----------|
| **`00-contexto/00-contexto-global-erp.md`** | Modelo multiempresa, Dictionary DB, Company DB, seguridad |
| **`00-contexto/01-guia-estructura-documental-corporativa.md`** | Organización de la documentación |
| **`00-contexto/02-guia-onboarding-30-minutos.md`** | Onboarding rápido para nuevos desarrolladores |
| **`01-arquitectura/README.md`** | Índice de arquitectura |
| **`01-arquitectura/01-arquitectura-proyecto.md`** | Capas, responsabilidades, tenancy |
| **`arquitectura.md`** | Visión general MVP (3 capas, web+mobile) |

### Historias y Tareas

| Documento | Propósito |
|-----------|-----------|
| **`03-historias-usuario/000-Generalidades/`** | Épica Generalidades: layouts persistentes de grillas |
| **`03-historias-usuario/001-Seguridad/`** | Épica Seguridad: login, selección empresa, logout, mantenimiento tablas (users, PQ_Empresa, roles, permisos, menú) |
| **`03-historias-usuario/Historia_PQ_MENUS_seed.md`** | Seed versionado del menú del sistema |
| **`04-tareas/`** | Tareas técnicas (TR) derivadas de las HU |
| **`05-open-spec/guia-adopcion-open-spec.md`** | Adopción Open-Spec y checklist |
| **`_base/_OPEN-SPEC-METODOLOGIA.md`** | Metodología y partes **A–Q** (enlace simbólico `_base`) |

### API y Backend

| Documento | Propósito |
|-----------|-----------|
| **`api/CONTRATO_BASE.md`** | Formato estándar de respuestas API |
| **`api/openapi.md`** | Documentación OpenAPI |
| **`backend/PLAYBOOK_BACKEND_LARAVEL.md`** | Convenciones, estructura, validación Laravel |
| **`backend/tareas.md`** | Lógica de registro de tareas |

### Frontend

| Documento | Propósito |
|-----------|-----------|
| **`frontend/frontend-specifications.md`** | Especificaciones generales |
| **`frontend/devextreme-norms.md`** | Uso de DevExtreme, licencia, comportamiento nativo |
| **`frontend/ui-layer-wrappers.md`** | Reglas de UI Layer, componentes reutilizables |
| **`frontend/features/features-structure.md`** | Organización de features |

### Operación y Testing

| Documento | Propósito |
|-----------|-----------|
| **`06-operacion/deploy-infraestructura.md`** | Infraestructura, pipeline, deploy |
| **`06-operacion/instructivo-optimizar-velocidad.md`** | Instructivo para medir y optimizar velocidad con evidencia |
| **`.cursor/rules/12-testing.md`** | Estrategia de testing, Vitest, Playwright |

---

## Reglas del Agente IA (.cursor/rules)

Las reglas en `.cursor/rules/` complementan esta documentación:

| Regla | Propósito |
|-------|-----------|
| `01-project-context.md` | Contexto del proyecto para el agente |
| `05-backend-policy.md` | Política backend Laravel |
| `06-api-contract.md` | Contrato API obligatorio |
| `08-security-sessions-tokens.md` | Seguridad, Sanctum, tenant |
| `10-i18n-and-testid.md` | i18n y data-testid obligatorios |
| `12-testing.md` | Estrategia de tests |
| `00-prompts-programados-dispatcher.md` | Comandos HU→TR, entorno de desarrollo (**ubicación actual:** `.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md`) |
| `21-Iniciar-tunel-SSH-para-MySql.md` | Túnel SSH para MySQL remoto |

---

## Orden de Lectura Recomendado

**Para desarrolladores nuevos:**
1. `00-contexto/00-contexto-global-erp.md` → contexto del ERP
2. `00-contexto/02-guia-onboarding-30-minutos.md` → onboarding
3. `01-arquitectura/01-arquitectura-proyecto.md` → arquitectura
4. HU y TR del módulo en el que se trabaja

**Para implementar una funcionalidad (con Open-Spec):**
1. SPEC en `docs/05-open-spec/` si el alcance es formal (ver `guia-adopcion-open-spec.md`).
2. Leer la HU en `03-historias-usuario/` y la TR en `04-tareas/`.
3. Dispatcher: `.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md` (núcleo Open-Spec **A–F**; tabla completa **A–Q**).

**Para implementar una funcionalidad (flujo mínimo):**
1. Leer la HU correspondiente en `03-historias-usuario/`
2. Leer o generar la TR en `04-tareas/` (ver `.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md`)
3. Consultar `backend/` o `frontend/` según el área

---

## Referencias Cruzadas

- **Reglas del agente IA:** `AGENTS.md` (raíz)
- **Migración MySQL:** `docs/migracion-mssql-a-mysql.md`
- **Docker/CI-CD futuro:** `docs/futuro/DOCKER-CICD.md`
