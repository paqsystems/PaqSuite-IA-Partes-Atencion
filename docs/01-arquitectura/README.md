# Documentación de Arquitectura – ERP PaqSuite

Índice maestro de la documentación arquitectónica del proyecto ERP. Esta carpeta contiene la definición técnica, el modelo de datos, las decisiones clave y los mapas visuales que describen el sistema.

---

## Modo de instalación de **este** repositorio

**MONO (mono-empresa)** — declaración y criterios: **[`00-modo-instalacion.md`](./00-modo-instalacion.md)**. La documentación multi-DB / `X-Company-Id` de varios archivos es **referencia ERP**; el MVP **Partes de Atención** usa **una sola base de datos** y **sin tenancy**.

---

## Contenido de la carpeta

| Documento | Descripción |
|-----------|-------------|
| [00-modo-instalacion.md](./00-modo-instalacion.md) | **MONO** vs MULTI: modo adoptado en este repo y checklist |
| [01-arquitectura-proyecto.md](./01-arquitectura-proyecto.md) | Arquitectura técnica del backend: capas, responsabilidades, multiempresa, bases de datos |
| [10-arquitectura-programacion.md](./10-arquitectura-programacion.md) | Arquitectura de **programación**: mapeo a carpetas Laravel/React, flujo MONO, tests y anti‑patrones |
| [02-modelo-datos-overview.md](./02-modelo-datos-overview.md) | Vista general del modelo de datos (en definición) |
| [03-modelo-datos-modulos.md](./03-modelo-datos-modulos.md) | Modelo de datos por módulo (en definición) |
| [04-decisiones-clave.md](./04-decisiones-clave.md) | Decisiones estructurales y justificaciones técnicas (en definición) |
| [05-mapa-arquitectura-backend.md](./05-mapa-arquitectura-backend.md) | Diagramas visuales: capas, flujo de request, separación de bases |
| [06-mapa-visual-seguridad-roles-permisos-menu.md](./06-mapa-visual-seguridad-roles-permisos-menu.md) | Modelo de seguridad: roles, permisos, menú y acciones |
| [07-mapa-visual-tenancy-resolucion-db.md](./07-mapa-visual-tenancy-resolucion-db.md) | Tenancy, Dictionary DB vs Company DB, flujo de resolución |
| [08-roadmap-madurez-arquitectonica-erp.md](./08-roadmap-madurez-arquitectonica-erp.md) | Evolución de la arquitectura en 5 niveles de madurez |
| [09-bug-validacion-unique-multiconexion-laravel10.md](./09-bug-validacion-unique-multiconexion-laravel10.md) | Laravel 10: `Rule::unique` multi-DB; `orderBy` + query `dir` (listados Partes Producción) |
| [ui/01_MainLayout_PostLogin_Specification.md](./ui/01_MainLayout_PostLogin_Specification.md) | Especificación del Shell principal post-login (DevExtreme, theming por empresa, responsive) |
| [ui/02-frontend-folder-structure.md](./ui/02-frontend-folder-structure.md) | Estructura ideal de carpetas del frontend React + DevExtreme, lista para Cursor |

---

## Resumen ejecutivo

### Este producto (MVP Partes de Atención): **MONO**

- **Una base de datos** para seguridad y negocio.
- Sin **`X-Company-Id`**, sin Dictionary/Company separadas en runtime.
- Capas, OpenAPI, tests y disciplina de documentación iguales al lineamiento PaqSuite; reglas multi-empresa solo donde el equipo las adapte explícitamente.

### Arquitectura por capas (referencia común MULTI / MONO)

1. **API / Controllers** – Reciben requests, extraen contexto, delegan a servicios
2. **Application Services** – Casos de uso, orquestación, reglas funcionales
3. **Domain** – Entidades, reglas invariantes
4. **Infrastructure (Repositories)** – Acceso a datos
5. **Base de Datos** – Dictionary DB y Company DB

### Modelo multiempresa *(referencia ERP; ver modo en [00-modo-instalacion.md](./00-modo-instalacion.md))*

- **Multiusuario, multiempresa, multirroles**
- Usuario puede pertenecer a varias empresas
- Tenant definido por header `X-Company-Id`
- **Dictionary DB**: usuarios, empresas, roles, permisos, asignaciones
- **Company DB**: datos operativos por empresa (clientes, ventas, stock, etc.)

### Seguridad

- Autorización por operación
- Validación en cada request: en **MONO**, **autenticación → permiso específico**; el modelo “pertenencia a empresa” es referencia **MULTI** (ver `01-arquitectura-proyecto.md` §7)
- Formato de permisos: `{recurso}.{accion}` (ej: `clientes.read`, `clientes.create`)
- El menú refleja permisos, pero la seguridad real se valida en backend

### UI / Frontend (Shell post-login)

- Frontend: **React + DevExtreme React Components**
- Theming por empresa (lista cerrada de themes, CSS precompilados)
- Responsive:
  - Desktop/Tablet: Shell completo + dashboard configurable
  - Mobile: **sin dashboard** y **sin solapas**, orientado a ejecución de procesos
- Fuente técnica: `ui/01_MainLayout_PostLogin_Specification.md`

---

## Guía de lectura recomendada

| Si necesitas… | Lee primero |
|---------------|--------------|
| Entender la arquitectura general | [01-arquitectura-proyecto.md](./01-arquitectura-proyecto.md) |
| Implementar capas en código (MONO) | [10-arquitectura-programacion.md](./10-arquitectura-programacion.md) |
| Ver diagramas rápidos | [05-mapa-arquitectura-backend.md](./05-mapa-arquitectura-backend.md) |
| Entender seguridad y permisos | [06-mapa-visual-seguridad-roles-permisos-menu.md](./06-mapa-visual-seguridad-roles-permisos-menu.md) |
| Entender tenancy y bases de datos | [07-mapa-visual-tenancy-resolucion-db.md](./07-mapa-visual-tenancy-resolucion-db.md) |
| Entender el Shell UI post-login | [ui/01_MainLayout_PostLogin_Specification.md](./ui/01_MainLayout_PostLogin_Specification.md) |
| Estructura del frontend para Cursor | [ui/02-frontend-folder-structure.md](./ui/02-frontend-folder-structure.md) |
| Planificar evolución futura | [08-roadmap-madurez-arquitectonica-erp.md](./08-roadmap-madurez-arquitectonica-erp.md) |

---

## Documentos en definición

Los siguientes documentos están preparados para completarse cuando se formalicen las definiciones:

- **02-modelo-datos-overview.md** – Entidades base, modelo relacional, identificadores
- **03-modelo-datos-modulos.md** – Entidades por módulo, relaciones, reglas de integridad
- **04-decisiones-clave.md** – Decisiones estructurales, alternativas descartadas, riesgos

---

## Relación con otros documentos

- **docs/00-contexto/** – Contexto global del ERP, onboarding, guías corporativas
- **docs/01-arquitectura/ui/** – Especificaciones del Shell UI y estructura frontend (Cursor)
- **docs/ui/** – Mockups, Figma y documentación visual (frames, exports, guías)
- **.cursor/rules/** – Reglas de desarrollo (API, backend, seguridad, etc.)