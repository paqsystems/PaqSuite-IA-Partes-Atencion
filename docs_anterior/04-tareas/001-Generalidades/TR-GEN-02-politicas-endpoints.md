# TR-GEN-02-politicas-endpoints — Marco transversal de politicas por endpoint

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-politicas-endpoints](../../03-historias-usuario/001-Generalidades/HU-GEN-02-politicas-endpoints.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-modelo-roles-permisos-seed, TR-GEN-02-login-sesion, TR-GEN-02-autorizacion-menu-api |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-05-30 |

**Origen:** [HU-GEN-02-politicas-endpoints](../../03-historias-usuario/001-Generalidades/HU-GEN-02-politicas-endpoints.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Titulo
Definir marco transversal para autorizar todos los endpoints protegidos del MVP.

### Narrativa
Como arquitectura API, necesitamos que cada endpoint protegido tenga politica en codigo, fila en matriz y contrato OpenAPI coherente.

### In scope / Out of scope
- In scope: estandar de autorizacion para `api/v1/*`, lista blanca publica y checklist transversal de cumplimiento.
- In scope: matriz viva endpoint ↔ permiso y reglas de mantenimiento por cada slice.
- Out of scope: implementar un endpoint funcional especifico de negocio (se ejecuta en TR de cada slice).

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Existe inventario inicial de endpoints MVP con politica asignada.
- **AC-02**: Toda operacion protegida en OpenAPI declara `security`, `X-Paq-Cliente`, `401` y `403` cuando exista autorizacion funcional adicional.
- **AC-03**: Toda ruta publica queda listada explicitamente sin `security` de Bearer/Sanctum.
- **AC-04**: Matriz endpoint ↔ permiso se actualiza en el mismo slice del cambio.
- **AC-05**: Ningun controller de negocio confia solo en ocultamiento UI/menu.

### Escenarios Gherkin

```gherkin
Feature: Politicas transversales por endpoint

  Scenario: Endpoint protegido sin token
    Given un endpoint protegido del inventario
    When se invoca sin Bearer token
    Then responde HTTP 401

  Scenario: Endpoint protegido sin permiso
    Given un usuario autenticado sin permiso requerido
    When invoca endpoint protegido
    Then responde HTTP 403
    And no genera efectos laterales

  Scenario: Coherencia OpenAPI
    Given un endpoint protegido publicado
    When se inspecciona /api/documentation
    Then aparece security, X-Paq-Cliente, 401 y 403

  Scenario: Menu oculto no implica endpoint abierto
    Given un usuario sin permiso de menu para un proceso
    When invoca directamente el endpoint del proceso
    Then recibe HTTP 403

  Scenario: Token valido con permiso
    Given un usuario autenticado con permiso requerido
    When invoca endpoint protegido
    Then recibe HTTP 200 en envelope valido
```

---

## 3) Reglas de Negocio

1. **RN-01**: Politica backend por endpoint es obligatoria para toda ruta protegida.
2. **RN-02**: Menu visible no reemplaza autorizacion de endpoint.
3. **RN-03**: Lista blanca publica inicial: login, recuperacion de contrasena, health.
4. **RN-04**: Cada TR funcional que agregue/edite endpoint debe actualizar matriz y OpenAPI en el mismo slice.
5. **RN-05**: Cualquier discrepancia codigo-matriz-openapi bloquea cierre de slice.
6. **RN-06**: En MONO, una ruta publica puede seguir exigiendo o documentando `X-Paq-Cliente` sin dejar de ser publica respecto de autenticacion Bearer/Sanctum.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-30)

**Fuentes revisadas:** HU-GEN-02-politicas-endpoints, SPEC-001-02-acceso-y-seguridad, `_NORMAS-TRANSVERSALES-TR.md`, `matriz-permisos-mvp.md`, `backend/OpenApi.php`, `routes/api.php`, `AuthController`, `UserMenuController`, `SessionContextBuilder`, `AuthorizedMenuBuilder`, `AuthServiceProvider`, `AuthLoginTest`, `UserMenuTest`, `OpenApiDocumentationTest`.

### Resultado general

- **Estado:** Apto
- **Puede pasar a D1:** **Sí**, con resoluciones C1 aceptadas para cerrar el patrón operativo real del repo y evitar sobrediseño arquitectónico.

### Aceptación stakeholder (2026-05-31)

Se formaliza retrospectivamente el C1 del slice luego de haber ejecutado D, preservando las decisiones ya adoptadas en la implementación/documentación:

- patrón backend basado en `paq.tenant` + `auth:sanctum` + servicios/controladores con envelope;
- distinción entre rutas públicas, autenticadas simples y autorizadas funcionalmente;
- `403` exigido cuando existe autorización funcional adicional a la autenticación;
- `logout` fuera de la lista blanca pública;
- `400 tenant.invalid` documentado como parte transversal MONO donde aplica.

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución aplicada |
|----|------|--------|---------------------|
| AMB-C01 | **“Policy por endpoint” vs patrón real del repo** | La HU/TR pueden inducir a una implementación con Laravel Policies estrictas en todos los módulos, mientras el código real resuelve gran parte de la autorización mediante middleware + servicios + `AuthFlowException`. | **Cerrado:** el marco acepta el patrón real del MVP; no se fuerza refactor masivo a `Policy` Laravel. |
| AMB-C02 | **Lista blanca pública vs tenancy MONO** | La documentación previa mezclaba “ruta pública” con “ruta sin `X-Paq-Cliente`”, lo que podía hacer que login/logout quedaran clasificados distinto entre TR, norma y OpenAPI. | **Cerrado:** una ruta pública es “sin Bearer/Sanctum”; el header `X-Paq-Cliente` puede seguir documentándose en rutas públicas MONO cuando aplique. |
| AMB-C03 | **Obligatoriedad de `403` en todo endpoint protegido** | Había riesgo de exigir `403` incluso en endpoints con regla simple de usuario autenticado, generando contratos/tests artificiales o inconsistentes con slices ya cerrados. | **Cerrado:** `403` se documenta cuando existe autorización funcional adicional a la autenticación. |
| AMB-C04 | **Ownership de baseline transversal** | No estaba claro si esta TR debía quedarse en norma documental o también ajustar baseline real del repo (OpenAPI auth/menu, matriz, tests). | **Cerrado:** el slice es dueño del baseline transversal auth/menu y de la norma reusable para futuros slices. |

### Ambigüedades menores

| ID | Tema | Resolución aplicada |
|----|------|---------------------|
| AMB-M01 | Descripción del permiso requerido en OpenAPI | Mantenerlo como obligación del slice funcional; en baseline transversal se valida `security` + responses críticas. |
| AMB-M02 | Frontend transversal | No se crea UI nueva; el manejo de `401/403` queda reutilizable en cliente compartido/slices. |
| AMB-M03 | Tenant en errores | `400 tenant.invalid` se reconoce como parte del marco transversal en endpoints alcanzados por `paq.tenant`. |

### Contradicciones TR ↔ HU ↔ código

| Contradicción | Resolución |
|---------------|------------|
| La HU pedía `401/403` por endpoint protegido, pero el repo ya tenía endpoints autenticados simples sin policy funcional separada | Se cerró con la regla “`403` cuando aplique autorización funcional adicional” |
| La documentación transversal listaba `logout` como público, pero código/matriz/OpenAPI ya lo trataban como autenticado | Se corrigió la norma transversal y se consolidó el baseline auth/menu |
| La narrativa hablaba de “políticas” de manera genérica, pero `AuthServiceProvider` no registra mappings reales | Se aceptó explícitamente el patrón operativo actual sin sobrediseñar |

### Supuestos detectados

- El baseline transversal suficiente para este slice es `auth/login`, `auth/logout`, `auth/me` y `user/menu`.
- Los slices funcionales futuros serán responsables de describir el permiso concreto en OpenAPI cuando agreguen endpoints propios.

### Preguntas para decisión humana

- Ninguna bloqueante. La revisión retrospectiva no abre nuevas decisiones funcionales.

### Recomendaciones de ajuste de la TR

- **Aplicadas en esta revisión y en D.**

### Veredicto C1

**Apta para D1 — C1 formalizado retrospectivamente y sin bloqueos.** El slice queda coherente con HU/SPEC y con el patrón realmente implementado en el repo.

---

## 3.2) Plan D1 — Implementación (2026-05-30)

### Alcance entendido

Consolidar el marco transversal de autorización por endpoint del MVP para que código, matriz y OpenAPI queden alineados bajo una misma regla operativa. Este slice no introduce un endpoint de negocio nuevo: define inventario inicial, patrón reusable de autorización backend, mantenimiento obligatorio de `matriz-permisos-mvp.md` y publicación coherente en `/api/documentation`. **Fuera:** refactor masivo de todos los controllers a Laravel Policies puras, implementación de cada endpoint de negocio y filtros de visibilidad por fila de datos.

### Fuentes leídas

- SPEC: `docs/05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md`
- HU: `docs/03-historias-usuario/001-Generalidades/HU-GEN-02-politicas-endpoints.md`
- TR: `docs/04-tareas/001-Generalidades/TR-GEN-02-politicas-endpoints.md`
- TR relacionadas: `TR-GEN-02-login-sesion.md`, `TR-GEN-02-autorizacion-menu-api.md`
- Norma transversal: `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`
- Matriz viva: `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`
- Código actual: `backend/OpenApi.php`, `backend/routes/api.php`, `backend/app/Http/Middleware/ValidatePaqTenant.php`, `backend/app/Http/Controllers/AuthController.php`, `backend/app/Http/Controllers/UserMenuController.php`, `backend/app/Services/Auth/SessionContextBuilder.php`, `backend/app/Services/Menu/AuthorizedMenuBuilder.php`, `backend/app/Providers/AuthServiceProvider.php`, `backend/tests/Feature/AuthLoginTest.php`, `backend/tests/Feature/UserMenuTest.php`

### Impacto esperado

#### Base de datos

- Sin cambios DDL nuevos para este slice.
- Reuso de `Pq_Permiso`, `Pq_Rol` y `PQ_RolAtributo` como fuente de autorización donde aplique.
- La “tabla viva” operativa del marco es documental: `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`.

#### Backend

- Formalizar un criterio reusable para distinguir:
  - rutas públicas explícitas;
  - rutas autenticadas por regla simple “usuario autenticado”;
  - rutas con autorización funcional basada en permisos/roles/atributos.
- Mantener el patrón real del repo: middleware `paq.tenant` + `auth:sanctum` + servicios/controladores que devuelven `401/403/400` en envelope, sin forzar refactor completo a `Policy` Laravel si no agrega valor en MVP.
- Usar `backend/OpenApi.php`, anotaciones en controllers y tests feature como punto de verificación transversal.

#### Frontend

- Impacto indirecto: manejo homogéneo de `401` y `403` en el cliente HTTP y en los slices que consumen endpoints protegidos.
- No se planifica una pantalla nueva; cualquier handling visual específico se implementa en los slices funcionales.

#### Tests

- Consolidar pruebas críticas 401/403/400 tenant en recursos ya existentes (`auth/*`, `/user/menu`) como baseline transversal.
- Definir como regla de mantenimiento que cada slice nuevo agregue al menos 401 y un 403 “si aplica” en sus tests API.

#### Documentación

- Cerrar el inventario inicial de endpoints MVP en `matriz-permisos-mvp.md`.
- Alinear la TR con el patrón efectivo del repo y con OpenAPI real generado en `/api/documentation`.
- Dejar explícito que la coherencia se valida por slice en el mismo PR/cambio.

#### DevOps

- Sin cambios de infraestructura.
- La publicación verificable sigue siendo `l5-swagger` + `GET /api/documentation`.

### Decisiones D1

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Patrón backend | En MVP se acepta `middleware` + servicios/controladores con `AuthFlowException`/envelope; no se fuerza migración total a `Policy` Laravel. |
| D1-2 | Clasificación endpoints | Separar explícitamente rutas públicas, autenticadas simples y autorizadas por permiso/atributo. |
| D1-3 | Matriz viva | `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` es la fuente documental canónica endpoint ↔ permiso/regla. |
| D1-4 | OpenAPI | Toda ruta protegida documenta `security`, `X-Paq-Cliente`, `401` y `403` cuando exista regla de autorización funcional; rutas públicas quedan sin `security`. |
| D1-5 | Tenant | `400 tenant.invalid` forma parte del marco transversal MONO y debe quedar visible en endpoints alcanzados por `paq.tenant`. |
| D1-6 | Frontend | El manejo uniforme de `401/403` se resuelve a nivel cliente compartido/slices, sin abrir una UI transversal propia en esta TR. |
| D1-7 | Cobertura mínima | Recursos críticos base para evidenciar el marco: `POST /auth/login`, `GET /auth/me`, `POST /auth/logout`, `GET /user/menu`. |

### Orden de trabajo

1. Normalizar el inventario inicial de endpoints MVP en la matriz viva.
2. Documentar el patrón transversal real del backend (`paq.tenant`, `auth:sanctum`, reglas simples vs permisos funcionales).
3. Alinear OpenAPI raíz + anotaciones de controllers base con ese inventario.
4. Consolidar baseline de tests 400/401/403 en auth y menú.
5. Dejar checklist reutilizable para que cada slice nuevo mantenga matriz, OpenAPI y tests en el mismo cambio.

### Archivos o módulos a revisar/tocar

| Capa | Archivos |
|------|----------|
| Backend | `backend/OpenApi.php`, `backend/routes/api.php`, `backend/app/Http/Middleware/ValidatePaqTenant.php`, `backend/app/Http/Controllers/AuthController.php`, `backend/app/Http/Controllers/UserMenuController.php`, `backend/app/Providers/AuthServiceProvider.php` |
| Servicios | `backend/app/Services/Auth/SessionContextBuilder.php`, `backend/app/Services/Menu/AuthorizedMenuBuilder.php` |
| Tests | `backend/tests/Feature/AuthLoginTest.php`, `backend/tests/Feature/UserMenuTest.php`, `backend/tests/Feature/OpenApiDocumentationTest.php` |
| Frontend | `frontend/src/shared/http/client.ts` (si se necesitara centralizar manejo base de 401/403) |
| Docs | `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`, TR funcionales del bloque `001-*` y futuros `101-*` |

### Riesgos

- Deriva entre código real, matriz y OpenAPI si la actualización sigue dependiendo de disciplina manual por slice.
- Esta TR habla de “políticas” en abstracto, pero el repo hoy no usa mappings reales en `AuthServiceProvider`; sobrediseñar eso ampliaría alcance silenciosamente.
- Existe heterogeneidad legítima entre endpoints: algunos tienen `403` por permiso funcional, otros solo regla “usuario autenticado”, y todos además pueden devolver `400 tenant.invalid`.

### Tests a ejecutar

- `backend/tests/Feature/AuthLoginTest.php`
- `backend/tests/Feature/UserMenuTest.php`
- `backend/tests/Feature/OpenApiDocumentationTest.php`
- Validación documental/manual de `matriz-permisos-mvp.md` contra `routes/api.php` y anotaciones OpenAPI base.

### Dudas / bloqueos

- Ninguno bloqueante para este slice tras la ejecución D.
- Si en una oleada posterior se decide adoptar `Policies` Laravel de forma estricta, eso debería abrirse como ajuste arquitectónico separado y no asumirse dentro de este slice.

### Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**. El plan se limita a consolidar el marco transversal, su inventario, la coherencia con OpenAPI y el baseline de autorización ya existente, sin introducir endpoints de negocio nuevos ni refactors arquitectónicos ajenos al MVP documentado.

---

## 3.3) Verificación D (2026-05-30)

| Verificación | Resultado |
|--------------|-----------|
| Norma transversal alineada a Bearer/Sanctum vs `tenant` | OK — `_NORMAS-TRANSVERSALES-TR.md` ahora distingue rutas públicas sin Bearer y `403` solo cuando aplica autorización funcional |
| Inconsistencia documental de `logout` en lista blanca pública | OK — removido de la lista blanca en `_NORMAS-TRANSVERSALES-TR.md` |
| OpenAPI base documenta `400 tenant.invalid` en endpoints baseline protegidos | OK — `AuthController` (`logout`, `me`, `changePassword`) y `UserMenuController` actualizados |
| OpenAPI baseline auth/menu documenta `security` esperado | OK — `OpenApiDocumentationTest` verifica `tenant` para login y `sanctum + tenant` para `logout`, `me`, `user/menu` |
| OpenAPI baseline auth/menu documenta responses críticas | OK — `OpenApiDocumentationTest` verifica `400/401` y `403` donde aplica |
| Matriz viva sigue coherente con rutas baseline | OK — `matriz-permisos-mvp.md` mantiene `logout`, `me` y `user/menu` con su regla actual |
| Refactor total a Laravel Policies | No aplicado — fuera de alcance del slice y explicitamente preservado como decisión D1 |

### Trazabilidad AC

| AC | Evidencia | Estado D |
|----|-----------|----------|
| AC-01 | Inventario inicial en `matriz-permisos-mvp.md` | ✅ |
| AC-02 | `OpenApiDocumentationTest` + anotaciones auth/menu | ✅ |
| AC-03 | `_NORMAS-TRANSVERSALES-TR.md` alineada a rutas públicas sin Bearer/Sanctum | ✅ |
| AC-04 | Regla de mantenimiento reforzada en norma + matriz viva ya existente | ✅ |
| AC-05 | Baseline backend sigue validando acceso real en `SessionContextBuilder` y `AuthorizedMenuBuilder` | ✅ |

### Ajustes D observados

- El marco quedó explícitamente alineado al patrón real del repo: middleware + servicios/controladores + envelope, sin exigir `Policy` Laravel en todos los módulos.
- `403` deja de tratarse como requisito ciego para cualquier endpoint autenticado simple; en esta base se exige cuando hay una autorización funcional adicional a la autenticación.
- En rutas públicas MONO, el header `X-Paq-Cliente` puede seguir documentándose mediante `tenant` sin convertir la operación en “protegida” por Bearer.

### Confirmación de alcance

Marco transversal, norma reusable, baseline OpenAPI y verificación auth/menu. **Fuera:** reescritura de todos los slices a policies Laravel, apertura de endpoints de negocio nuevos, visibilidad por fila de datos y cambios UI transversales.

---

## 4) Impacto en Datos

### Tablas afectadas
- `Pq_Permiso` (catalogo de permisos y relaciones)
- `Pq_Rol` (asignaciones de autorizacion)
- `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` como matriz viva documental

### Seed minimo para tests
- Roles/permisos base del seed MVP.
- Usuarios de prueba por perfil para validar 401/403.

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints cubiertos por el marco (no exhaustivo)

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| POST | `/api/v1/auth/login` | No | N/A | Si |
| POST | `/api/v1/auth/logout` | Bearer Sanctum + `X-Paq-Cliente` | Usuario autenticado | No |
| GET | `/api/v1/auth/me` | Bearer Sanctum + `X-Paq-Cliente` | Usuario autenticado | No |
| POST | `/api/v1/auth/password/change` | Bearer Sanctum + `X-Paq-Cliente` | Usuario autenticado | No |
| POST | `/api/v1/auth/password/forgot` | No | N/A | Si |
| GET | `/api/v1/health` | No | N/A | Si |
| GET | `/api/v1/user/menu` | Bearer Sanctum + `X-Paq-Cliente` | Permiso_Repo menu | No |
| GET/POST/PUT/DELETE | `/api/v1/pedidos/*` | Bearer Sanctum + `X-Paq-Cliente` | Segun matriz (`Permiso_Repo/Alta/Modi/Baja`) | No |
| GET/POST/PUT/DELETE | `/api/v1/presupuestos/*` | Bearer Sanctum + `X-Paq-Cliente` | Segun matriz | No |
| GET | `/api/v1/dashboard/*` | Bearer Sanctum + `X-Paq-Cliente` | Segun matriz + visibilidad perfil | No |

### 5.2 Norma de detalle por operacion (obligatoria)

Cada operacion protegida debe documentar:
- Autorizacion (permiso/rol o regla equivalente).
- Request y `X-Paq-Cliente`.
- Response 200 en envelope `error`/`respuesta`/`resultado` (`error` entero; `resultado` objeto, nunca `null`). Ver [`envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).
- Response 400 (`tenant.invalid`) cuando la operacion pase por `paq.tenant`.
- Response 401 (no autenticado).
- Response 403 (autenticado sin permiso) cuando exista una regla de autorizacion funcional adicional a la autenticacion.

Cada operacion publica debe documentar:
- Sin bloque `security` de Bearer/Sanctum.
- Justificacion de lista blanca.
- `X-Paq-Cliente` documentado si aplica tenancy MONO.
- Envelope de respuestas y errores funcionales.

### 5.3 Checklist transversal de publicacion OpenAPI

- [ ] Anotaciones en controller/DTO del slice.
- [ ] `security` presente en rutas protegidas.
- [ ] Header `X-Paq-Cliente` documentado donde aplique.
- [ ] Respuestas 400 tenant, 401 y 403 cuando aplique declaradas por operacion protegida.
- [ ] Permiso requerido visible en `description` (o extension acordada).
- [ ] Verificado spec final en `/api/documentation`.
- [ ] Matriz endpoint ↔ permiso actualizada en `matriz-permisos-mvp.md`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- Sin cambios directos en UI; impacto transversal en manejo uniforme de 401/403.
- Reutilizar manejo comun del cliente HTTP para 401/403 en los slices que lo necesiten; sin introducir una UI transversal nueva en este slice.

### data-testid sugeridos
- `http-401-handler`
- `http-403-handler`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T1 | Arquitectura | Publicar inventario endpoint ↔ permiso MVP | Tabla inicial acordada |
| T2 | Backend | Estandarizar criterio reusable por modulo (`auth:sanctum` + servicios/policies si aplica) | Regla comun documentada sin refactor fuera de alcance |
| T3 | Docs | Definir formato canonico de matriz permisos | Archivo vivo actualizado |
| T4 | OpenAPI | Normalizar anotaciones `security`, `tenant`, `401` y `403` cuando aplique | `/api/documentation` consistente |
| T5 | QA | Suite de regresion 400/401/403 transversal | Evidencia por recursos criticos |

---

## 8) Estrategia de Tests

- **Unit:** servicios/evaluadores de permiso por accion cuando el modulo tenga autorizacion funcional.
- **Integration:** pruebas 200/400/401 y 403 cuando aplique por endpoint critico del MVP.
- **E2E:** intento de acceso directo a rutas no autorizadas sin menu visible.

---

## 9) Riesgos y Edge Cases

- Drift entre matriz documental y autorizacion real en codigo.
- Endpoints nuevos sin actualizacion de OpenAPI en el mismo PR.
- Respuestas 403 inconsistentes entre modulos.
- Inconsistencia entre “ruta publica” y “ruta sin tenant” si no se distingue Bearer/Sanctum del header `X-Paq-Cliente`.

---

## 10) Checklist final

### Checklist del slice
- [x] AC cumplidos
- [x] Marco transversal acordado y documentado
- [x] Checklist operativo reutilizable por cada slice

### Checklist normas transversales
- [x] Endpoints baseline documentados con regla en codigo
- [x] Matriz endpoint ↔ permiso actualizada y mantenida como fuente viva
- [x] OpenAPI en `/api/documentation` coherente con codigo y matriz para baseline auth/menu
- [x] 400 tenant, 401 y 403 cuando aplica documentados por operacion protegida baseline
- [x] Envelope JSON respetado
- [x] `X-Paq-Cliente` documentado donde aplique
- [x] Tests API incluyen 401 (y 403 si aplica) en baseline auth/menu
- [x] Sin ampliacion de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

### Backend
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/UserMenuController.php`

### Frontend
- Sin cambio de codigo obligatorio en este slice.

### OpenAPI
- `backend/OpenApi.php`
- `backend/tests/Feature/OpenApiDocumentationTest.php`
- Controllers/DTOs baseline (`AuthController`, `UserMenuController`)

### Docs
- `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`
- `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`
- TR de cada slice con seccion 5 alineada a este marco.










