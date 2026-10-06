# Manual del programador — PaqSuite Partes de Atención

| Campo | Valor |
|-------|--------|
| **Producto** | PaqSuite IA — Partes de Atención (`SistemaPartes`, portal MONO) |
| **Público** | Programadores, integradores y soporte técnico que necesitan entender el sistema de punta a punta |
| **Fecha** | 5 de octubre de 2026 |
| **Complementa** | Este documento enseña. No sustituye SPEC, HU, TR ni el contrato OpenAPI. |

Este texto está pensado para que alguien que entra al repositorio por primera vez pueda responder dos preguntas:

1. **¿Cómo está construido el proyecto y cómo se trabaja en él?** (visión técnica)
2. **¿Qué puede hacer el sistema hoy?** (visión funcional)

Al final hay un **anexo** sobre **emisiones GEN-15** y el **sidecar DevExpress Reporting**, que el host usa para diseñar informes y emitir PDF/Excel/correo desde Consulta detallada.

---

## Cómo leer este documento

Si venís a **programar**, empezá por la Parte 1. Ahí está el mapa del monorepo, las capas, las reglas que no se improvisan y el circuito OpenSpec (cómo nace un cambio: SPEC → HU → TR → código → tests → verificación).

Si venís a **entender qué hace el producto**, andá a la Parte 2. Ahí está el inventario de prestaciones: maestros, carga diaria, supervisión masiva, consultas, dashboard, Excel, IA, mobile, emisiones, etc.

Si venís a **tocar informes o el diseñador DX**, leé la Parte 1 (auth, tenant, envelope, proxy Vite) y el **Anexo A (emisiones / Reporting)**.

Si tenés que **configurar lab local o un sitio en Forge (dev/prod)**, usá **§1.12** (variables por ambiente). El detalle extendido del FE está en `docs/01-arquitectura/frontend-api-base-url-y-env.md`.

Los manuales de usuario (operatoria para asistentes, supervisores y clientes) viven en `docs/99-manual-usuario/`. Este archivo es el equivalente para quien escribe código o consume la API.

---

# Parte 1 — Cómo entender técnicamente el desarrollo

## 1.1 Qué es Partes de Atención, en una frase técnica

Partes de Atención es un **módulo de registro y consulta de dedicación**: cada asistente (o supervisor en su nombre) registra **tareas** con fecha, cliente, tipo, duración y observación; el sistema permite **supervisar**, **agrupar** y **emitir informes** sobre ese trabajo.

No es un ERP ni un sistema de facturación automática en el MVP: las columnas `erp_cliente` / `erp_articulo` en el maestro de clientes son **referencias** para análisis y emisiones futuras, no integración en tiempo real con Tango.

Hay tres “caras” del mismo backend:

- la **aplicación web** (React + DevExtreme);
- la **aplicación mobile** (Capacitor, mismo frontend, vistas kardex y rutas acotadas);
- **consumidores de API** (si se exponen endpoints documentados en OpenAPI) con el mismo envelope y auth Sanctum.

## 1.2 El monorepo

El repositorio es un monorepo de producto **MONO** (un tenant = una empresa = una base unificada en el MVP). Estructura:

```text
PaqSuite-IA-Partes-Atencion/
├── backend/          Laravel 10 — API REST `/api/v1`
├── frontend/         React + Vite + DevExtreme (+ Capacitor android/ios)
├── docs/             producto, OpenSpec, HU, TR, operación, manuales
├── .cursor/rules/    normas que el asistente y el equipo deben cumplir
└── prompts/          prompts del circuito OpenSpec (si aplica en el clone)
```

Herencia compartida de PaqSuite (no reinventar login, envelope, grillas, menú, etc.):

- `docs/_base/` y `docs/00-contexto/_mono/` — arquitectura y patrones MONO
- `.cursor/rules/base/` — reglas transversales (API, DB, frontend, mobile, Git)

El producto **adopta capacidades GEN del Framework** (login, i18n, menú, grillas, pivots, Excel, chat IA, emisiones, mobile). El host aporta el dominio Partes: catálogo `PQ_PARTES_*`, gate de identidad funcional, menú, permisos, APIs/operaciones de negocio, pantallas de carga, consultas y dashboard.

Config canónica: `PAQSUITE_PROYECTO=partesatencion`, `PAQSUITE_TENANCY=single`, `PAQSUITE_DB=unified`. Detalle: `docs/01-arquitectura/integracion-framework-sdk.md`.

## 1.3 Stack y puertos locales habituales

| Capa | Tecnología | Puerto / URL típica |
|------|------------|---------------------|
| Frontend | React 18, Vite, TypeScript, DevExtreme, i18next | `http://localhost:3000` (ver `frontend/vite.config.ts`) |
| Backend | Laravel 10, PHP, Sanctum | `http://127.0.0.1:8010` (convención local del repo; proxy Vite apunta ahí) |
| OpenAPI | L5-Swagger | `http://127.0.0.1:8010/api/documentation` |
| Reporting (sidecar) | ASP.NET DevExpress Reporting | `http://127.0.0.1:5055` (proxy `/DXXRD`, `/DXXRDV`, `/DXXQB` en Vite) |
| Base | SQL Server (referencia); sqlite en tests/E2E | `backend/.env` (`DB_DATABASE`) |

Health: `GET /api/v1/health` — no requiere tenant ni token.

En producción el patrón de URLs está documentado en `docs/01-arquitectura/frontend-api-base-url-y-env.md`:

| Entorno | Frontend (ejemplo) | Backend API |
|---------|-------------------|-------------|
| Operativo | `https://partesatencionpaqsystems.vercel.app` | `https://backend.partesatencion.paqsystems.com` |
| Desarrollo | `https://partesatencionpaqsystemsdev.vercel.app` | `https://backenddev.partesatencion.paqsystems.com` |

Entrada por subdominio cliente (puente web): `https://{cliente}.partesatencion.paqsystems.com` → redirect al SPA con `?cliente={CODIGO}` (GEN-19; ver `bootstrapClienteFromWindow` en `frontend/src/main.tsx`).

Header obligatorio en API: `X-Paq-Cliente: {cliente}` (código de instalación en `EMPRESAS_CONEXION`, proyecto `partesatencion`).

En desarrollo local, si `PAQSUITE_INSTALACION_RESOLVER=config`, el mapa fallback incluye `DEMO|partesatencion` y `PAQ|partesatencion` en `backend/config/paqsuite.php`.

## 1.4 Arquitectura del backend (cómo pensar un cambio)

La API es REST pura. El contrato de respuesta es siempre el **envelope MONO**:

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": { }
}
```

- `error` es un **entero** (`0` = OK; distinto de 0 = error controlado).
- `resultado` **siempre** es un objeto; nunca `null` ni ausente. Si no hay datos, `{}`.
- El status HTTP clasifica (401, 403, 404, 422…); el cuerpo no cambia de forma.

Capas (regla: el controller **no** acumula lógica de negocio):

| Capa | Carpeta típica | Responsabilidad |
|------|----------------|-----------------|
| Routes | `backend/routes/api.php` | Prefijo `v1`, middleware tenant + Sanctum + `partes.profile` |
| Controllers | `backend/app/Http/Controllers/Api/V1/Partes/` | Validar entrada, llamar service, devolver envelope |
| Services | `backend/app/Services/Partes/` | Reglas: tareas, maestros, informes, smart capture |
| Middleware | `EnsurePartesFunctionalProfile`, `partes.notCliente` | Revalidar perfil funcional Partes en cada request de negocio |
| Gate login | `PostLoginBusinessGate` (host) | Tras auth GEN OK, resolver asistente vs cliente vs denegado |
| OpenAPI | anotaciones `@OA` + paquete laravel-core | Contrato Swagger |
| Tests | `backend/tests/Unit`, `Feature`, `Integration` | PHPUnit |

Flujo mental de un request autenticado de Partes:

1. Header `X-Paq-Cliente` → middleware de instalación resuelve la conexión SQL del tenant (`PAQSUITE_PROYECTO=partesatencion`).
2. Bearer Sanctum → usuario de sesión Framework (`users`).
3. **`partes.profile`**: lookup `users.id` → `PQ_PARTES_USUARIOS` o `PQ_PARTES_CLIENTES` (activo, no inhabilitado, exclusividad). Si falla → 403 con clave i18n de producto.
4. **`partes.notCliente`**: bloquea rutas de carga/maestros/masivo a perfiles cliente.
5. Service aplica reglas (tramos de duración, `es_tarea`, cerrado, universo supervisor).
6. Persistencia vía operaciones nombradas como SP (`pq_sp_partes_*`); en runtime MONO muchas rutas están implementadas en `PartesTareaOperations` / `PartesMaestrosOperations` con Query Builder alineado al contrato SP (scripts en `backend/database/sp/`).
7. `ApiResponse::success` / `error` arma el envelope.

**Norma de acceso a datos (BASE):** el diseño canónico es **stored procedures**. El código nuevo de negocio no debe ampliar SQL ad-hoc “por comodidad”. Los services Partes ya declaran contratos `pq_sp_partes_*`; al extender el módulo, entregar o actualizar el script T-SQL y el `dispatch` correspondiente.

**Delimitación de datos:** la primera capa es el **perfil funcional** (cliente solo su `cliente_id`, asistente su actividad, supervisor universo ampliado). Los permisos de menú ocultan pantallas pero **no** sustituyen ese filtro. Preferir JOIN/EXISTS; nunca `whereIn` con miles de IDs (regla BASE 77).

## 1.5 Arquitectura del frontend (cómo pensar una pantalla)

El frontend es una SPA. Tras el login y el gate Partes, el **shell** (layout, menú lateral, avatar) es capacidad GEN. El dominio vive en `frontend/src/features/partes/` y `frontend/src/app/AppRouter.tsx`.

Convenciones que no se negocian:

- **DevExtreme obligatorio** para controles interactivos. No introducir `<select>` / `<button>` nativos si DX cubre el caso.
- **Todo texto visible sale de i18n** (`frontend/src/locales/` y catálogo `productI18n.ts` en los cinco idiomas). Idioma por defecto: español.
- Tras init i18n: `registerGridI18nResources(i18n)` y `syncDevExtremeLocale` (regla anti-fork GEN 43).
- **`data-testid` estables** vía `inputAttr` / `elementAttr`; los tests no se atan al DOM interno de DevExtreme.
- Variables, métodos y propiedades en **camelCase**.
- En mobile, consultas y listados usan **kardex** (`ConsultaKardexMobilePage`). Pivot, importación Excel, ABM de seguridad, emisiones, maestros y “abrir en pestaña nueva” están **excluidos** de native (`partesMobilePolicy.ts`).

Rutas principales (web): ver §1.9 y `AppRouter.tsx`. En native, `/partes/consulta` y `/partes/carga` resuelven al kardex móvil; la carga diaria “de escritorio” es `/partes/carga-diaria` (solo web).

Si el cambio toca **web y puede usarse en mobile**, evaluar `isNativeApp()` y `partesMobilePolicy`.

## 1.6 Tenancy, login e identidad funcional

Partes es **MONO** en el MVP: no hay selector de empresa operativo salvo preparación MULTI en el SDK (`/select-empresa` existe por GEN pero el producto documenta `single`).

Tras credenciales Framework válidas, el **gate de negocio Partes** decide si la sesión puede operar el módulo:

| Perfil funcional | Condición | Universo de datos (capa 1) |
|------------------|-----------|----------------------------|
| **Asistente** | Fila en `PQ_PARTES_USUARIOS` vinculada a `users.id` | Propia actividad |
| **Supervisor** | Asistente con `supervisor = 1` | Universo supervisor (terceros, masivo, etc.) |
| **Cliente** | Fila en `PQ_PARTES_CLIENTES` con `user_id` no nulo | Solo su organización (`cliente_id`) |
| **Solo Framework** | Sin vínculo en tablas Partes | **Denegado** al circuito Partes (403) |

Reglas críticas:

- Mismo `users.id` en asistente **y** cliente → configuración inconsistente → denegar.
- Cliente: **no** carga diaria, **no** maestros, **no** proceso masivo (`partes.notCliente`).
- `firstLogin` → cambio de contraseña obligatorio **antes** del shell (alineación GEN / PedidosWeb).

Auth (Sanctum): login, forgot/reset, change password, expiración por inactividad. Mobile: **tenant (empresa) primero** en UI, luego usuario y contraseña.

Menú: `GET /api/v1/user/menu` según `pq_menus` + permisos; cada nodo con `labelKey` y traducción en host (`MenuSidebar` con `t` + `locale`).

## 1.7 Modelo de datos (lo mínimo para no perderse)

Prefijo de tablas del módulo: **`PQ_PARTES_`**. Identidad Framework: `users` (fuera del prefijo).

Núcleo:

| Tabla | Rol |
|-------|-----|
| `PQ_PARTES_USUARIOS` | Asistentes; flag `supervisor` |
| `PQ_PARTES_CLIENTES` | Clientes del módulo; opcional `user_id` para perfil cliente |
| `PQ_PARTES_TIPOS_CLIENTE` | Catálogo tipos de cliente |
| `PQ_PARTES_TIPOS_TAREA` | Catálogo tipos de tarea (incluye tipo default / genérico) |
| `PQ_PARTES_CLIENTE_TIPO_TAREA` | Tipos habilitados por cliente |
| `PQ_PARTES_REGISTRO_TAREA` | **Tarea / parte** (fecha, cliente, tipo, minutos, flags, `cerrado`, `es_tarea`) |

Conceptos que gobiernan casi toda la operatoria:

| Concepto | Significado |
|----------|-------------|
| **`es_tarea = true`** | Registro del proceso de **carga diaria** y de consultas/dashboard que listan “tareas” (excluye otros usos futuros del mismo registro) |
| **`cerrado`** | Parte supervisada: flujo ordinario sin edición/borrado; supervisor puede reabrir según reglas |
| **Tramo de duración** | Parámetro `PartesDuracionTramoMin` en `PQ_PARAMETROS_GRAL` (default **15** min); duraciones en múltiplos |
| **Presentación `hh:mm`** | UI web/mobile; persistencia en **minutos enteros** |

Detalle de columnas: `docs/02-producto/Sistema-Partes-IA/09-modelo-datos-tecnico.md`, diagrama `docs/modelo-datos/md-sistema-partes.md`, SPEC `docs/05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md`.

## 1.8 Cómo se desarrolla de verdad: OpenSpec

En este equipo **no se “codea de oído”** una feature de negocio. El circuito es:

```text
Producto (docs/02-producto/Sistema-Partes-IA)
    → A  SPEC (docs/05-open-spec)
    → B  HU  (docs/03-historias-usuario)
    → C  TR  (docs/04-tareas)
    → D  Implementación
    → E  Tests
    → F  Verificación vs documentos
```

Correcciones de control de calidad (PQ) entran por `docs/00-ControlCalidad/` y recorren **G → D → E → F → I** (volcado a updates, código, tests, cierre, unificación).

Convención de IDs:

| Ámbito | Carpeta / prefijo | Ejemplo |
|--------|-------------------|---------|
| Generalidades (GEN) | `docs/05-open-spec/001-Generalidades/`, updates | UI errores validación, capacidades transversales |
| Sistema Partes | `100-SistemaPartes` | `SPEC-004-operacion-carga-diaria`, `HU-004`, `TR-004` |

Mientras un cambio de alcance está en curso, **no se edita el SPEC/HU/TR base**: se escribe un `*-update.md` en `docs/.../updates/`. Recién en Parte I se unifica.

Si estás por implementar una TR, el orden mental es: leer SPEC (fuente de verdad) → HU (criterios de aceptación) → TR (pasos técnicos) → código. Si el SPEC y el código discrepan, gana el SPEC hasta que haya un update explícito.

## 1.9 Mapa de carpetas que vas a tocar

### Backend

| Ruta | Qué hay |
|------|---------|
| `backend/routes/api.php` | Rutas `/api/v1`, grupos `partes.profile` |
| `backend/app/Http/Controllers/Api/V1/Partes/` | Tareas, maestros, informes, turno smart capture |
| `backend/app/Services/Partes/` | Núcleo de negocio + smart capture |
| `backend/app/Services/Partes/SmartCapture/` | Extracción LLM / propuesta de campos |
| `backend/app/Http/Middleware/EnsurePartesFunctionalProfile.php` | Middleware `partes.profile` |
| `backend/database/sp/` | Scripts SP Partes y GEN (excel, etc.) |
| `backend/config/paqsuite.php` | Tenancy, pivots, instalaciones fallback |

### Frontend

| Ruta | Qué hay |
|------|---------|
| `frontend/src/app/AppRouter.tsx` | Rutas autenticadas |
| `frontend/src/features/partes/carga/` | Carga diaria, validación, Excel, smart capture |
| `frontend/src/features/partes/masivo/` | Proceso masivo supervisor |
| `frontend/src/features/partes/maestros/` | ABM catálogos Archivos |
| `frontend/src/features/partes/informes/` | Dashboard, consultas, paquete horas, emisiones, diseñador DX |
| `frontend/src/features/partes/mobile/` | Kardex, policy, config mobile |
| `frontend/src/features/auth/` | Login, shell, gate, i18n producto |
| `frontend/src/features/admin/security/` | ABM seguridad GEN (web) |
| `frontend/src/features/chatAssistant/` | Chat documental BYOK |

### Docs

| Ruta | Qué hay |
|------|---------|
| `docs/02-producto/Sistema-Partes-IA/` | Definición funcional (fuente conceptual) |
| `docs/05-open-spec/100-SistemaPartes/` | SPEC de slices |
| `docs/03-historias-usuario/100-SistemaPartes/` | HU |
| `docs/04-tareas/100-SistemaPartes/` | TR y cierres D/E/F |
| `docs/99-manual-usuario/` | Manuales de usuario / corpus chat |
| `docs/06-operacion/` | Deploy, smoke mobile, URLs API |

## 1.10 Tests y calidad

- **Backend:** PHPUnit (`backend/tests`). Unitarios de services, Feature de endpoints (envelope, 403 gate Partes, happy path).
- **Frontend:** Vitest (`*.test.ts` / `*.test.tsx` junto al código).
- **E2E:** Playwright en `frontend/tests/e2e/`; al cerrar TR de frontend relevante: `npm run test:all` en `frontend/`.
- Tras implementación de una TR: Parte E (suite) y Parte F (alineación SPEC/HU/TR ↔ código).

No se hace commit ni push sin autorización explícita. `main` solo se actualiza por PR desde `develop` (regla BASE 18).

## 1.11 Flags de producto (`.env` / config)

| Variable (concepto) | Efecto |
|---------------------|--------|
| `PIVOTS_ENABLED` / `PIVOT_LAYOUTS_ENABLED` | Vista pivot en consultas (default on en `paqsuite.php`; ajustar por deploy) |
| `PAQSUITE_GRID_LAYOUTS_ENABLED` | Layouts de grilla GEN-11 |
| `EXCEL_IMPORT_ENABLED` / flag público equivalente | Importación Excel GEN-14 (proceso Partes en carga diaria; TR-009) |
| `ADMIN_SECURITY_UI_ENABLED` | ABM roles/permisos en web (excluido mobile) |
| `PAQSUITE_INSTALACION_RESOLVER` | `config` (mapa PHP) vs `sql` (`EMPRESAS_CONEXION` en Forge) |

Sin el flag, la UI y a veces el menú no exponen el proceso aunque el código exista. La matriz completa lab vs Forge está en **§1.12**.

## 1.12 Variables de entorno por ambiente (lab, Forge dev, Forge prod)

Partes reparte configuración en **tres lugares**: `backend/.env` (Forge → *Environment* del sitio Laravel), `frontend/.env` (solo desarrollo local con Vite) y **variables de build en Vercel** (FE desplegado). En mobile, además, la URL base queda en el bundle o en Preferences (§1.14).

Plantillas de referencia: `backend/.env.example`, `frontend/.env.example`.

### Topología recordatorio

| Capa | Lab (tu PC) | Forge **develop** | Forge **main** (operativo) |
|------|-------------|-------------------|----------------------------|
| Frontend | `npm run dev` → `:3000` | Vercel `develop` / `*dev.vercel.app` | Vercel `main` / custom `*.partesatencion.paqsystems.com` |
| Backend API | `php artisan serve` → `:8010` (convención repo) | `https://backenddev.partesatencion.paqsystems.com` | `https://backend.partesatencion.paqsystems.com` |

El FE en Vercel **siempre** llama al BE por URL absoluta (`VITE_API_BASE_URL`). En lab, el FE usa **same-origin** y el proxy de Vite reenvía `/api` al backend local.

### Backend — `backend/.env`

| Variable | Lab local | Forge dev | Forge prod |
|----------|-----------|-----------|------------|
| **`APP_ENV`** | `local` | `production` (habitual en Forge) | `production` |
| **`APP_DEBUG`** | `true` | `true` solo si el equipo lo autoriza en dev | **`false`** |
| **`APP_URL`** | `http://127.0.0.1:8010` | `https://backenddev.partesatencion.paqsystems.com` | `https://backend.partesatencion.paqsystems.com` |
| **`APP_KEY`** | Generar con `php artisan key:generate` | Secreto estable en Forge (no rotar sin plan) | Idem |
| **`PAQSUITE_PROYECTO`** | `partesatencion` | `partesatencion` | `partesatencion` |
| **`PAQSUITE_TENANCY`** | `single` | `single` | `single` |
| **`PAQSUITE_DB`** | `unified` | `unified` | `unified` |
| **`PAQSUITE_INSTALACION_RESOLVER`** | `config` (PHPUnit / sin PAQSYSTEMS) o `sql` (igual que Forge) | **`sql`** | **`sql`** |
| **`DB_*`** (`sqlsrv`) | Instancia alcanzable (ej. `PAQSYSTEMS_PARTESATENCION_DEMO`) o **sqlite** en tests | Credenciales del sitio; la BD **por tenant** la resuelve `EMPRESAS_CONEXION` | Idem prod |
| **`PAQSUITE_CENTRAL_*`** | Host/catálogo `PAQSYSTEMS` si `resolver=sql` | Obligatorio con multidominio | Obligatorio |
| **`FRONTEND_URL`** | `http://localhost:3000` | URL del SPA dev (Vercel develop) | URL operativa o custom domain |
| **`FRONTEND_SPA_URL_DEV`** | Opcional en lab | `https://partesatencionpaqsystemsdev.vercel.app` (enlaces reset mail) | — |
| **`FRONTEND_SPA_URL_PROD`** | — | — | `https://partesatencionpaqsystems.vercel.app` (+ custom domains) |
| **`MAIL_MAILER`** | `log` (sin SMTP) | SMTP / servicio real según deploy | Producción |
| **`PIVOTS_ENABLED`** / **`PIVOT_LAYOUTS_ENABLED`** | Según necesidad (`true` en `paqsuite.php` por defecto) | Ajustar por tenant | Ajustar por tenant |
| **`ADMIN_SECURITY_UI_ENABLED`** | `true` si probás ABM admin | `true`/`false` según política | Suele activarse cuando el menú Seguridad debe verse |
| **`PAQSUITE_EMISSION_DX_MODE`** | `fake` / stub o `remote` si tenés sidecar local | `remote` cuando el sidecar DX esté operativo | `remote` |
| **`PAQSUITE_EMISSION_DX_SERVICE_URL`** | `http://127.0.0.1:5055/api/v1/dx-reporting` (sidecar local) | URL del sidecar (a menudo `127.0.0.1` si comparte VM con Laravel) | URL pública o interna del sidecar |
| **`PAQSUITE_EMISSION_DX_API_KEY`** | Secreto compartido con el sidecar | Idem | Idem (rotación coordinada) |
| **`PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS`** | Default `60` | Igual | Igual |

**Tras cambiar `.env` en Forge:**

```bash
php artisan config:cache
php artisan queue:restart   # si hay workers para emisiones/excel async
```

**Capacidades que no van solo en `.env`:** flags como **`ExcelImportEnabled`** (programa `ExcelImport`) y **`EmissionEnabled`** (programa `Emission`) viven en **`PQ_PARAMETROS_GRAL`** tras migrate/seed (`PqExcelImportSeeder`, `PqEmissionSeeder`). En lab, activarlos en la BD que uses; en Forge, verificar en pantalla Parámetros o SQL tras cada deploy de catálogo.

**Resolver `config` en lab:** el mapa `DEMO|partesatencion` / `PAQ|partesatencion` en `backend/config/paqsuite.php` apunta a tu `DB_*` del `.env`. Útil sin catálogo central; **no** sustituye `EMPRESAS_CONEXION` en multidominio real.

### Frontend web — `frontend/.env` (solo lab)

| Variable | Lab | Vercel develop | Vercel production |
|----------|-----|----------------|-------------------|
| **`VITE_API_BASE_URL`** | **Vacío / no definir** | `https://backenddev.partesatencion.paqsystems.com` | `https://backend.partesatencion.paqsystems.com` |
| **`VITE_API_PROXY_TARGET`** | `http://127.0.0.1:8010` (alineado a `vite.config.ts`) | No aplica (no hay proxy en build) | No aplica |
| **`VITE_DEVEXTREME_LICENSE_KEY`** | LCP 26.1 local | Misma clave en env Vercel Preview | Misma clave en env Vercel Production |
| **`VITE_DX_REPORTING_HOST`** | `http://127.0.0.1:5055` | Sidecar dev o rewrites Vercel (ver Anexo A / `plan-dx-reporting-sidecar-aws-gateway.md`) | Sidecar prod o rewrites same-origin |
| **`VITE_DX_REPORTING_DIRECT`** | Normalmente omitido (proxy Vite) | `true` solo en modo cross-origin explícito | Idem si no hay rewrites `/DXXRD*` |

Cambios en Vercel → **redeploy** (Vite embebe las `VITE_*` en el build).

### Chequeo rápido

| Ambiente | Qué mirar en DevTools → Network |
|----------|----------------------------------|
| **Lab** | Requests a `http://127.0.0.1:3000/api/v1/...` (proxy) |
| **Vercel + Forge** | Requests a `https://backend(dev).partesatencion.paqsystems.com/api/v1/...` |
| **Health** | `GET /api/v1/health` sin token |

CORS: con FE y BE en orígenes distintos, `backend/config/cors.php` debe incluir dominios Vercel y custom (`docs/01-arquitectura/frontend-api-base-url-y-env.md`).

### Mobile (build APK)

No usa `VITE_API_PROXY_TARGET`. La URL por defecto sale del bootstrap SDK (`projectSlug: 'partesatencion'`) y/o variables de build mobile del host; override en engranaje. Ver **§1.14**.

---

## 1.13 Por dónde empezar si tenés que programar mañana

1. Levantar backend en `:8010` y frontend en `:3000`; abrir OpenAPI y el health.
2. Loguearte con un usuario **supervisor** y uno **asistente** y uno **cliente** (si hay datos seed).
3. Leer `docs/02-producto/Sistema-Partes-IA/01-vision-y-alcance.md` y el README de la carpeta (orden de lectura).
4. Para un bug o mejora: no parchear a ciegas. Si cambia el “qué”, hay SPEC-update (Parte G). Si solo es implementación de una TR ya apta, Parte D.
5. Cualquier listado de tareas o informes debe respetar **perfil funcional** + filtros de pantalla (`es_tarea`, fechas, cerrado).

## 1.14 Mobile — APK lab/debug vs producción (variables y build)

El APK **no elige el entorno en runtime** por sí solo: la URL base de la API queda **embebida en el bundle** al compilar con `npm run build:mobile`. El usuario puede **sobrescribir** esa URL desde el engranaje de configuración (`MobileConfigPanel` / `mobileConfigOpen`) y probar `GET /api/v1/health`. El **tenant** se ingresa en el login (`loginTenant`), no en el engranaje.

Referencia: `docs/06-operacion/runbook-smoke-mobile-partes.md`, `docs/_base/01-mobile/03-comandos-generacion-aplicaciones.md`.

### Variables de entorno (build time)

El bootstrap native usa `bootstrapApiBaseUrl` con `projectSlug: 'partesatencion'` (`frontend/src/main.tsx`). Revisar plantillas `.env` del frontend y documentación en `docs/01-arquitectura/frontend-api-base-url-y-env.md` para `VITE_API_BASE_URL` (web) y variables mobile del SDK.

| Variable | ¿Afecta al APK native? | Uso |
|----------|------------------------|-----|
| URL base API mobile (SDK / env del host) | **Sí** | Default de API en dispositivo; override en Preferences |
| `VITE_DEVEXTREME_LICENSE` | Sí (build) | Obligatoria para compilar |
| `VITE_API_BASE_URL` | Web | Proxy `/api` en Vite hacia `VITE_API_PROXY_TARGET` (default `8010`) |

**Valores típicos de URL API:**

| Entorno | URL de ejemplo |
|---------|----------------|
| **Producción** | `https://backend.partesatencion.paqsystems.com/api/v1` |
| **Desarrollo Forge** | `https://backenddev.partesatencion.paqsystems.com/api/v1` |
| **Lab — emulador Android** | `http://10.0.2.2:8010/api/v1` |
| **Lab — teléfono físico (LAN)** | `http://192.168.x.x:8010/api/v1` |

### Tipo de build Gradle (debug vs release)

Para **lab con HTTP**, `assembleDebug` (cleartext en manifest debug). Para **producción HTTPS**, `assembleRelease` firmado.

### Secuencia de build (resumen)

```powershell
cd frontend
npm run build:mobile
npx cap sync android
cd android
.\gradlew assembleDebug   # lab
# .\gradlew assembleRelease   # prod
```

### Allowlist mobile Partes

Rutas permitidas y exclusiones web-only: `frontend/src/features/partes/mobile/partesMobilePolicy.ts` (maestros, masivo, carga-diaria escritorio, consultas detalladas web, emisiones, admin).

---

# Parte 2 — Prestaciones del funcionamiento

Esta parte describe **qué puede hacer el sistema hoy**, agrupado como lo ve un usuario o un integrador. El detalle de pantallas está en `docs/99-manual-usuario/Partes-Atencion.md` y los SPEC por capacidad.

## 2.1 Acceso, sesión y preferencias

- Login con usuario y contraseña (en mobile: empresa + usuario + contraseña).
- Recuperación y cambio de contraseña (Framework GEN).
- Gate Partes post-login: sin perfil funcional válido no hay operación del módulo.
- Cierre por inactividad según parámetros Auth.
- Idiomas: español, inglés, portugués, francés, italiano.
- Apariencia / tema (GEN-08).
- Preferencia de abrir procesos en **pestaña nueva** (solo web; prohibido en mobile).
- Layouts de grilla y pivot layouts cuando los flags lo habilitan.
- Menú lateral según permisos; títulos de proceso vía `labelKey` + i18n host.

## 2.2 Dashboard (Inicio)

Ruta web: `/partes` (`PartesDashboardPage`). Resumen de dedicación según **perfil** y filtros de periodo:

- indicadores y accesos a informes relacionados;
- solo registros con **`es_tarea = true`**;
- cliente ve solo su organización; asistente su actividad; supervisor universo ampliado.

En mobile, el dashboard y el informe **paquete de horas** están en la allowlist.

## 2.3 Maestros (Archivos)

ABM en modal sobre grilla (patrón producto). Catálogos:

- asistentes (`PQ_PARTES_USUARIOS`);
- clientes (incl. referencias ERP opcionales);
- tipos de cliente;
- tipos de tarea (default / genérico);
- asignación de tipos de tarea por cliente.

**Cliente** y **mobile** no acceden a estas pantallas.

## 2.4 Carga diaria de tareas

Ruta web: `/partes/carga-diaria`. Grilla de trabajo filtrada (fecha, asistente propietario si supervisor, etc.):

- alta, edición y baja según rol y estado **`cerrado`**;
- duración en tramos `hh:mm` (parámetro de tramo);
- flags: presencial, sin cargo;
- al grabar desde este proceso, **`es_tarea = true`** siempre.

### Importación Excel (GEN-14)

Desde carga diaria: plantilla, staging, validación y hidratación de la grilla para revisar y grabar. Proceso host documentado en `docs/02-producto/Sistema-Partes-IA/13-importacion-partes-excel.md` (TR-009). Excluido en mobile.

### Smart Capture (asistente operativo en el modal)

Panel en el modal de alta/edición: texto, voz o imagen → propuesta de campos (BYOK, permisos). **No** es el chat del avatar. Detalle: `docs/02-producto/Sistema-Partes-IA/14-smart-capture.md`, endpoint `POST /api/v1/partes/tareas/asistente/turn`.

## 2.5 Supervisión — proceso masivo

Ruta: `/partes/proceso-masivo`. Solo **supervisor**:

- filtrar y seleccionar tareas del universo supervisor;
- aplicar cambios en lote (atributos permitidos y/o **`cerrado`**);
- límites de negocio y técnicos documentados en SPEC-005 / TR-005.

Excluido en mobile.

## 2.6 Consultas e informes

| Proceso | Qué ofrece |
|---------|------------|
| **Consulta detallada** | Grilla (y pivot si flag) de tareas individuales; columnas ERP del cliente cuando existen; botón **Emitir** (GEN-15) |
| **Consultas agrupadas** | Agrupaciones por cliente, asistente, tipo, fecha, etc. |
| **Paquete de horas** | Vista analítica con totales y gráfico; disponible también en mobile |

Todas consideran **`es_tarea = true`** salvo que un SPEC posterior documente otra cosa. Export de grilla ≠ **Emitir** (emisión usa dataset filtrado del proceso, no solo la página visible de la grilla).

## 2.7 Inteligencia artificial (dos asistentes)

### Chat Asistente IA (ayuda documental — GEN-21)

Desde el avatar (`/chat-assistant`). Responde sobre manuales (`docs/99-manual-usuario/` + corpus GEN). **No** graba tareas. BYOK (GEN-16).

### Smart Capture (operativo)

Solo en el modal de carga diaria; ver §2.4.

## 2.8 Parámetros generales

Pantallas `/parametros/{programa}` (Auth, Partes, etc.): listado y edición por tipo según GEN-10. Tramo de duración y topes de masivo viven en `PQ_PARAMETROS_GRAL` programa `Partes`.

## 2.9 Administración de seguridad (opcional)

Si `ADMIN_SECURITY_UI_ENABLED` está activo: usuarios, roles, permisos, empresas (según despliegue). **Excluido de mobile.**

## 2.10 Emisiones y diseñador de reportes

- **Emitir** en Consulta detallada: PDF, impresión, Excel, CSV, correo (canales habilitados por seed/proceso).
- **Diseñador:** `/emisiones/disenador` (Soporte Técnico en menú); layouts DevExpress Reporting.
- Detalle producto: `docs/02-producto/Sistema-Partes-IA/15-reportes-emisiones.md`, TR-011.

## 2.11 Mobile (Capacitor)

Misma API y reglas de negocio; presentación kardex para consulta/carga móvil, dashboard reducido, paquete de horas, chat in-app. Ver §1.12 (Forge) y §1.14 (APK), y `docs/99-manual-usuario/SPEC-007-mobile-capacitor.md`.

Excluido en native: maestros, carga diaria escritorio, masivo, consultas detalladas/agrupadas web, pivot, Excel, emisiones, admin seguridad, `openInNewTab`.

## 2.12 Visión de sistema (sin ERP en el MVP)

```text
Usuario (web / mobile)
        │
        ▼
API Laravel + gate Partes
        │
        ▼
SQL Server (PQ_PARTES_* + GEN: menú, permisos, excel, emisiones, LLM)
        │
        ├── Consultas / dashboard / masivo
        └── Emisiones → orquestador GEN + sidecar DX Reporting (diseño / render)
```

No hay sincronización bidireccional con ERP en el MVP; las referencias ERP en maestros son datos maestros del módulo.

---

# Anexo A — Emisiones GEN-15 y sidecar DevExpress Reporting

## A.1 Qué es esta integración

Partes **no** implementa su propio motor de reportes. Adopta **GEN-15**:

- catálogo de **procesos emisibles** (seed/SQL);
- ventana **Emitir** en Consulta detallada;
- **diseñador** de layouts en `/emisiones/disenador`;
- bitácora y permisos `emission.design` según Framework.

El **render** y el diseñador visual usan **DevExpress Reporting** en un **sidecar** ASP.NET. En desarrollo local, Vite proxifica `/DXXRD`, `/DXXRDV`, `/DXXQB` hacia el host configurado (`VITE_DX_REPORTING_HOST`, default `5055`).

Arquitectura y despliegue AWS/gateway: `docs/01-arquitectura/plan-dx-reporting-sidecar-aws-gateway.md`.

## A.2 Qué define el host Partes

| Responsabilidad Partes | Dónde |
|------------------------|--------|
| Proceso emisible `partes.informes.consultaDetallada` (código estable) | Seed / SQL + menú |
| Puerto **dataset**: mismas filas que la consulta detallada filtrada | `PartesInformeOperations` + bridge FE |
| Montar **Emitir** en la pantalla | `ConsultaDetalladaPage` + SDK emisiones |
| Diseñador y altas de reporte | `ReportDesignerHostPage`, lazy chunk DX |

Anti-patrón: confundir **Exportar Excel de la grilla** (GEN-11) con **Emitir** (GEN-15).

## A.3 Qué necesita el programador al trabajar en local

1. Backend Laravel en `:8010` con emisiones habilitadas y datos de prueba.
2. Sidecar Reporting en `:5055` (o el puerto que configure `VITE_DX_REPORTING_HOST`).
3. Frontend Vite en `:3000` para que el proxy unifique origen y cookies de sesión DX.
4. Permiso de menú para consulta detallada y, para diseño, `emission.design`.

## A.4 Dónde mirar en el código

| Tema | Dónde |
|------|--------|
| Rutas API informes / emisiones | `backend/routes/api.php`, `PartesInformeController` |
| Dataset | `backend/app/Services/Partes/PartesInformeOperations.php` |
| UI consulta + emitir | `frontend/src/features/partes/informes/PartesConsultasPages.tsx` |
| Diseñador lazy | `frontend/src/features/partes/informes/ReportDesignerHostPage.tsx`, `DxReportDesignerApp.tsx` |
| Proxy Vite | `frontend/vite.config.ts` |

---

## Documentos de cabecera (si hace falta profundizar)

| Necesitás… | Abrí… |
|------------|--------|
| Definición de producto (orden de lectura) | `docs/02-producto/Sistema-Partes-IA/README.md` |
| SPEC del módulo | `docs/05-open-spec/100-SistemaPartes/` |
| Modelo de datos | `docs/02-producto/Sistema-Partes-IA/09-modelo-datos-tecnico.md` |
| Operatoria de usuario | `docs/99-manual-usuario/Partes-Atencion.md` |
| Identidad y perfiles | `docs/02-producto/Sistema-Partes-IA/02-actores-identidad-y-acceso.md` |
| Envelope API | `docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md` |
| Metodología OpenSpec | `docs/_base/_OPEN-SPEC-METODOLOGIA.md` |
| URLs deploy y API | `docs/01-arquitectura/frontend-api-base-url-y-env.md` |
| Variables lab / Forge / Vercel | Este manual **§1.12** |
| APK Android lab vs prod | Este manual **§1.14**; `docs/06-operacion/runbook-smoke-mobile-partes.md` |
| Adopción SDK / ritual bump | `docs/01-arquitectura/plan-partes-adopcion-sdk-sin-fork-gen.md` |
| Contrato vivo de endpoints | `/api/documentation` (Swagger) |

Este manual describe el sistema **tal como está implementado al 5 de octubre de 2026**. Si un SPEC-update o un control de calidad cambia el alcance, prevalecen el SPEC unificado y OpenAPI.
