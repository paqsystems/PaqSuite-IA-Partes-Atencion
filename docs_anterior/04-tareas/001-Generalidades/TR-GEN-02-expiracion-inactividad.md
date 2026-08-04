# TR-GEN-02-expiracion-inactividad — Expiracion de sesion por inactividad

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-expiracion-inactividad](../../03-historias-usuario/001-Generalidades/HU-GEN-02-expiracion-inactividad.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-modelo-roles-permisos-seed, TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-06-09 (Parte I — CC PQ #1) |

**Origen:** [HU-GEN-02-expiracion-inactividad](../../03-historias-usuario/001-Generalidades/HU-GEN-02-expiracion-inactividad.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Titulo
Aplicar expiracion de sesion por inactividad usando parametro `MinutosWeb`.

### Narrativa
Como usuario autenticado, quiero que la sesion cierre tras inactividad configurable para reducir uso no autorizado.

### In scope / Out of scope
- In scope: lectura de `MinutosWeb`, detector de inactividad frontend y rechazo backend de sesion expirada.
- In scope: limpieza de estado local y redireccion a login.
- Out of scope: SSO, revocacion global de sesiones y politicas avanzadas de refresh.

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Superado `MinutosWeb`, la sesion se cierra automaticamente.
- **AC-02**: Actividad del usuario reinicia contador de inactividad.
- **AC-03**: API protegida con sesion expirada responde 401.
- **AC-04**: Se documenta default cuando `MinutosWeb` no exista.
- **AC-05**: Flujo queda cubierto con pruebas E2E de timeout reducido.
- **AC-06**: Tras expiración, shell y procesos quedan inaccesibles hasta nuevo login.

### Escenarios Gherkin

```gherkin
Feature: Expiracion por inactividad

  Scenario: Expiracion automatica
    Given un usuario autenticado
    And MinutosWeb configurado en N
    When no registra actividad durante N minutos
    Then se redirige al login con mensaje de sesion expirada

  Scenario: Actividad renueva sesion
    Given un usuario autenticado
    When registra actividad antes del timeout
    Then la sesion continua activa

  Scenario: API rechaza token expirado
    Given un usuario con sesion expirada por inactividad
    When llama un endpoint protegido
    Then recibe HTTP 401

  Scenario: Parametro MinutosWeb ausente
    Given MinutosWeb no configurado en parametros
    When el usuario inicia sesion
    Then se aplica un default documentado en TR
```

---

## 3) Reglas de Negocio

1. **RN-01**: `MinutosWeb` se obtiene desde configuracion global (SPEC-001-04).
2. **RN-02**: Eventos de actividad válidos que **reinician** el contador (lista cerrada — detalle en §6.1):
   - Interacciones DOM en fase **capture** sobre `document`: `pointerdown`, `click`, `touchstart`, `wheel`, `scroll`, `input`, `keydown` (**todas** las teclas, incluido `Tab`).
   - Navegación SPA: cambio de `location.pathname`.
   - Requests API autenticadas exitosas (evento `SistemaPartes:authenticated-request-succeeded`).
   - **No** reinician el contador: `mousemove` continuo, `focus`/`blur` aislados sin tecla ni puntero, visibilidad de pestaña, polling automático sin interacción del usuario.
3. **RN-03**: Sesion expirada fuerza 401 en endpoints protegidos.
4. **RN-04**: Si falta `MinutosWeb`, usar default trazable en documentacion.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-31)

**Fuentes revisadas:** HU-GEN-02-expiracion-inactividad, SPEC-001-02-acceso-y-seguridad, SPEC-001-04-configuracion-global, `PROD-Sistema-Partes.md`, `_NORMAS-TRANSVERSALES-TR.md`, backend (`config/sanctum.php`, `config/session.php`, `Authenticate.php`, `AuthController`, `AuthProvider` frontend, `authApi`), tests E2E actuales y baseline auth/login.

### Resultado general

- **Estado:** No apta
- **Puede pasar a D1:** **No**, hasta cerrar el mecanismo real de expiración, la fuente runtime de `MinutosWeb` y el default numérico del parámetro.

### Ambigüedades críticas

| ID | Tema | Riesgo | Recomendación / decisión necesaria |
|----|------|--------|------------------------------------|
| AMB-C01 | **Mecanismo real de expiración no cerrado** | La TR pide “detector frontend” y también “rechazo backend de sesión expirada”, pero hoy `sanctum.expiration = null` y no existe middleware/servicio de última actividad. Dos programadores podrían implementar soluciones muy distintas: logout local en frontend, expiración real de token en backend, o una mezcla inconsistente. | **C1-01:** decidir si el MVP usa: `A)` expiración real backend + 401 server-side, `B)` logout por inactividad controlado por frontend + 401 posterior por token invalidado, o `C)` un esquema híbrido explícitamente definido. |
| AMB-C02 | **Fuente runtime de `MinutosWeb` no especificada** | La TR dice que el valor viene de configuración global (`SPEC-001-04`), pero no define cómo llega al runtime actual: payload de login, `GET /auth/me`, `GET /config/public`, servicio de parámetros o config backend interna. | **C1-02:** cerrar una única fuente de lectura runtime para este slice; sin eso frontend y backend podrían leer valores distintos o bloquear la implementación. |
| AMB-C03 | **Default numérico de `MinutosWeb` sigue abierto** | HU/TR piden “default documentado”, pero no existe número en producto ni en SPEC-001-04. Eso cambia tests, UX y seguridad. | **C1-03:** definir el valor default concreto de `MinutosWeb` para MVP cuando el parámetro no exista. |
| AMB-C04 | **Eventos que reinician actividad no están cerrados** | RN-02 habla de “navegación, interacción y/o request API según definición del slice”; dos implementaciones podrían reiniciar por mousemove, click, teclado, route change, request exitosa, polling, visibilidad de pestaña, etc. | **C1-04:** cerrar el set mínimo de eventos que reinician el contador y aclarar si llamadas API exitosas cuentan como actividad. |
| AMB-C05 | **Multi-tab / pestaña en background no definido** | El estado local hoy vive en `localStorage` + `AuthProvider`, pero la TR no cierra si una pestaña activa renueva la sesión para todas, si un logout por inactividad se propaga entre pestañas, ni cómo se comporta una pestaña suspendida. | **C1-05:** decidir política MVP para múltiples pestañas y sincronización entre tabs. |
| AMB-C06 | **Semántica exacta del 401 por inactividad no aterrizada** | AC-03 exige 401 por sesión expirada, pero no define qué endpoint evidencia el vencimiento, ni si `POST /auth/logout` participa en la expiración automática o solo el frontend limpia estado y luego `/auth/me` falla. | **C1-06:** cerrar el contrato operativo mínimo de expiración: qué se invalida, cuándo y cómo se observa el 401 en la práctica. |

### Ambigüedades menores

| ID | Tema | Recomendación |
|----|------|---------------|
| AMB-M01 | Aviso previo | La HU lo deja opcional; si no entra al MVP, conviene explicitar “sin aviso previo” para evitar interpretaciones. |
| AMB-M02 | `403` en endpoints de sesión | Según norma transversal actual, `403` solo aplica cuando hay autorización funcional adicional; para inactividad probablemente la señal principal sea `401`. |
| AMB-M03 | `personal_access_tokens.expires_at` / `last_used_at` | Existen campos, pero la TR no define si serán la fuente real del timeout o solo soporte técnico sin uso en MVP. |
| AMB-M04 | E2E de timeout reducido | Falta cerrar cómo se inyecta/overridea `MinutosWeb` en test sin depender de parámetros globales aún no planificados. |

### Contradicciones TR ↔ HU ↔ SPEC ↔ código

| Contradicción | Impacto | Recomendación |
|---------------|---------|---------------|
| La TR promete “rechazo backend de sesión expirada”, pero `sanctum.expiration` está en `null` y no hay middleware de inactivity timeout | El backend hoy no tiene forma de vencer sesiones por inactividad de manera autónoma | Cerrar mecanismo real antes de D1 |
| La HU/TR dependen de `SPEC-001-04`, pero no existe todavía un servicio/canal runtime definido para leer `MinutosWeb` | Implementaciones podrían hardcodear, leer env o inventar endpoints | Definir fuente única de lectura |
| La TR lista `403` en `/auth/me` y `/auth/logout`, pero el slice de inactividad funcionalmente gira en torno a `401` | Puede sobredocumentarse un caso que no pertenece al objetivo del slice | Alinear contratos con la norma transversal “403 cuando aplique” |
| La TR habla de “cierre real de sesión”, pero el frontend actual solo limpia estado ante fallo de `/auth/me` o logout manual | Sin definición, “cierre real” puede significar cosas distintas entre frontend y backend | Precisar qué invalida la sesión |

### Supuestos detectados

- El mensaje i18n `auth.unauthenticated` ya existe y hoy se usa como texto de sesión expirada/relogin.
- El runtime actual usa token Bearer en `localStorage`; no hay refresh token ni cookie stateful como mecanismo principal del portal.
- `SPEC-001-04` todavía no cerró inventario/defaults de parámetros, por lo que este slice necesita una decisión humana explícita para `MinutosWeb`.

### Preguntas para decisión humana

- ¿Qué mecanismo querés para el MVP?
  - `A)` expiración real backend por inactividad
  - `B)` detector frontend que hace logout al vencer y deja que el backend responda 401 solo después de invalidar token
  - `C)` híbrido, si querés que lo definamos explícitamente
- ¿Cuál es el **default numérico** de `MinutosWeb` si el parámetro no existe?
- ¿Qué eventos reinician el contador? Mi propuesta mínima: `click`, `keydown`, navegación/routing y requests API exitosas iniciadas por el usuario.
- ¿Sincronizamos expiración entre múltiples pestañas (`storage` / logout global entre tabs) o lo dejamos fuera del MVP?
- ¿Incluimos aviso previo antes del cierre, o lo dejamos explícitamente fuera?

### Recomendaciones de ajuste de la TR

- Cerrar primero mecanismo técnico de expiración y fuente runtime de `MinutosWeb`.
- Explicitar si el MVP incluye o excluye aviso previo.
- Reescribir §5 para documentar `401` como respuesta principal del slice y dejar `403` solo “si aplica” por la norma transversal.
- Convertir RN-02 en una lista cerrada de eventos válidos de actividad.

### Veredicto C1

**No apta para D1** hasta resolver mecanismo de expiración, fuente runtime de `MinutosWeb` y default numérico. Tal como está, dos programadores podrían implementar expiración solo frontend, solo backend o híbrida, con tests y contratos incompatibles.

---

## 3.2) Resoluciones C1 — pre-D1

### Decisiones cerradas

| ID | Tema | Decisión |
|----|------|----------|
| R-C1-01 | Mecanismo MVP de expiración (`AMB-C01`) | **Opción B.** El timeout de inactividad se controla en frontend. Al vencer, la pestaña activa intenta `POST /api/v1/auth/logout` en modo best-effort; independientemente del resultado, limpia estado local, borra credenciales del navegador y redirige a login con mensaje de sesión expirada. |
| R-C1-02 | Eventos que reinician actividad (`AMB-C04`) | Reinician contador: interacciones de usuario, navegación dentro de la app y **llamadas API exitosas**. |
| R-C1-03 | Tecla `Tab` / foco sin acción (`AMB-C05`) | **Revocado CC PQ #3 (09/06/2026):** `Tab` y el resto de teclas en `keydown` **sí** reinician el contador. Solo quedan fuera eventos de foco aislados (`focus`/`blur`) sin tecla ni puntero. |
| R-C1-04 | Múltiples pestañas (`AMB-C05`) | La actividad de una pestaña **no propaga** ni reinicia el contador de otras pestañas. Cada pestaña mantiene su propio temporizador local. |
| R-C1-05 | Default de `MinutosWeb` (`AMB-C03`) | Si el parámetro no existe, el MVP usa **10 minutos** como default documentado. |
| R-C1-06 | Aviso previo (`AMB-M01`) | El MVP queda **explícitamente sin aviso previo** antes de la expiración. |
| R-C1-07 | Fuente runtime de `MinutosWeb` (`AMB-C02`) | El backend lee `MinutosWeb` desde configuración global y lo expone al frontend dentro del `sessionContext` en `POST /api/v1/auth/login` y `GET /api/v1/auth/me`, bajo un campo explícito de timeout de inactividad. |
| R-C1-08 | Semántica del `401` post-timeout (`AMB-C06`) | En el MVP, la expiración la inicia el frontend al vencer el contador local. La pestaña que vence intenta `logout` best-effort y limpia sesión local aunque falle. El backend sigue siendo fuente de verdad para acceso protegido: cualquier request posterior con token inválido o revocado responde **401** y obliga a relogin. |

### Aclaración operativa derivada de `R-C1-01` y `R-C1-04`

- La **actividad** no se comparte entre pestañas.
- Si una pestaña vence el timeout y logra revocar el token mediante `logout`, las demás pestañas conservarán su UI local hasta hacer una nueva request protegida; en ese momento deberán recibir **401** y forzar relogin.
- Esto **no** se considera propagación de actividad ni sincronización proactiva entre pestañas, sino efecto natural de compartir la misma credencial de autenticación.

### Pendientes para cerrar C1

- Sin pendientes críticos.

## 3.3) Veredicto C1 — cierre

### Resultado final

- **Estado:** Apta
- **Puede pasar a D1:** **Sí**

### Cierre de ambigüedades

- Queda cerrada la estrategia MVP como **timeout controlado en frontend** con cleanup local y `logout` best-effort.
- Queda cerrada la **fuente runtime** de `MinutosWeb` vía `sessionContext` en `login` y `auth/me`.
- Quedan cerrados el **default** (`10` minutos), la ausencia de **aviso previo**, la lista base de eventos de actividad y la política de **múltiples pestañas**.
- `401` queda como respuesta observable del backend cuando una request protegida llegue con token inválido o revocado tras la expiración.

### Observaciones para D1

- En planificación conviene renombrar RN-02 para dejar una lista cerrada de eventos válidos en vez de “y/o”.
- La sección de contratos API debe priorizar `401` y dejar `403` solo “si aplica” según la norma transversal.
- El campo del `sessionContext` para timeout debería nombrarse en `camelCase`, por ejemplo `inactivityTimeoutMinutes`.

---

## 3.4) Plan D1 — Implementación (2026-05-31)

### Alcance entendido

Implementar expiración de sesión por inactividad en el MVP con la estrategia cerrada en C1: el **frontend** controla el temporizador local usando `MinutosWeb`, lo obtiene desde el `sessionContext` devuelto por `POST /api/v1/auth/login` y `GET /api/v1/auth/me`, y al vencer limpia la sesión local, intenta `POST /api/v1/auth/logout` en modo best-effort y redirige a login con mensaje de sesión expirada. El **backend** no calcula inactividad real por sí solo en esta fase: su responsabilidad es exponer `inactivityTimeoutMinutes`, mantener el default documentado (`10`) y responder `401` cuando el token ya no sea válido. **Fuera de alcance:** refresh tokens, sincronización proactiva entre pestañas, aviso previo, revocación global de sesiones y un subsistema general de parámetros fuera de lo necesario para este slice.

### Fuentes leídas

- SPEC: `docs/05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md`
- SPEC relacionada: `docs/05-open-spec/001-Generalidades/SPEC-001-04-configuracion-global.md`
- HU: `docs/03-historias-usuario/001-Generalidades/HU-GEN-02-expiracion-inactividad.md`
- TR: `docs/04-tareas/001-Generalidades/TR-GEN-02-expiracion-inactividad.md`
- TR relacionadas: `TR-GEN-02-login-sesion.md`, `TR-GEN-02-politicas-endpoints.md`
- Norma transversal: `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`
- Backend actual: `backend/app/Http/Controllers/AuthController.php`, `backend/app/Services/Auth/LoginService.php`, `backend/app/Services/Auth/SessionContextBuilder.php`, `backend/config/sanctum.php`
- Frontend actual: `frontend/src/features/auth/AuthProvider.tsx`, `frontend/src/features/auth/authStorage.ts`, `frontend/src/features/auth/types.ts`, `frontend/src/shared/http/client.ts`, `frontend/src/app/layout/ShellLayout.tsx`, `frontend/src/app/router/RequireAuth.tsx`, `frontend/src/app/router/AppRoutes.tsx`
- Tests actuales: `backend/tests/Feature/AuthLoginTest.php`, `backend/tests/Feature/OpenApiDocumentationTest.php`, `frontend/tests/e2e/smoke.spec.ts`

### Impacto esperado

#### Base de datos

- Sin cambios DDL ni tablas nuevas para este slice.
- La fuente funcional de `MinutosWeb` sigue siendo configuración global; si aún no existe servicio/repository reusable de parámetros, este slice solo debe incorporar la lectura mínima necesaria en backend con fallback documentado.
- `personal_access_tokens` se reutiliza únicamente para revocación del token en logout; no se planifica usar `expires_at` ni `last_used_at` como motor de inactividad en MVP.

#### Backend

- Extender el contrato de `sessionContext` para incluir `inactivityTimeoutMinutes` en `camelCase`, tanto en `login` como en `auth/me`.
- Resolver lectura de `MinutosWeb` desde backend con un punto único de acceso y fallback explícito a `10`.
- Mantener el patrón real del repo: `LoginService` + `SessionContextBuilder` como ensambladores del contexto de sesión; no abrir un endpoint nuevo solo para timeout.
- Ajustar OpenAPI de `login`, `auth/me` y `logout` para reflejar el nuevo campo y priorizar `401` como respuesta clave del slice; `403` queda solo “si aplica” por regla funcional distinta a la autenticación.

#### Frontend

- Incorporar el timeout en el `SessionContext` persistido localmente.
- Implementar un mecanismo reusable de actividad/inactividad asociado al shell autenticado, no a la pantalla de login.
- Reiniciar el contador con interacciones de usuario, navegación dentro de la app y respuestas API exitosas.
- Al vencer el temporizador: limpiar estado local, intentar logout best-effort y redirigir a `/login`.
- Para cumplir `AC-06`, complementar el temporizador local con manejo homogéneo de `401` en el cliente HTTP o en el bootstrap/auth flow, de modo que una pestaña que conserve UI local pero use un token ya revocado sea expulsada al relogin en su próxima request protegida.

#### Tests

- Backend: verificar shape de `sessionContext` con `inactivityTimeoutMinutes`, fallback `10`, coherencia login/me y `401` posterior a logout.
- Frontend unit/component: cálculo/reinicio del contador, expiración con cleanup, cobertura de eventos RN-02 §6.1 (incl. `Tab`, `scroll`, capture DevExtreme).
- E2E: flujo autenticado con timeout corto, redirect a login, mensaje de expiración y rechazo posterior de shell/procesos.

#### Documentación

- Alinear la TR con una lista cerrada de eventos válidos de actividad.
- Actualizar la descripción de contratos/OpenAPI para que el slice quede documentado como timeout controlado en frontend con backend como fuente de verdad del acceso protegido.
- Mantener trazabilidad del default `10` minutos y de la ausencia de aviso previo.

#### DevOps

- Sin cambios de infraestructura.
- Para tests automatizados puede requerirse una vía controlada de override del timeout en ambiente de test (fixture, stub o config de testing), sin alterar el comportamiento productivo.

### Decisiones D1

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Fuente runtime | `inactivityTimeoutMinutes` viaja dentro del `sessionContext` de `POST /api/v1/auth/login` y `GET /api/v1/auth/me`; no se crea endpoint adicional de configuración pública para este slice. |
| D1-2 | Fallback backend | Si `MinutosWeb` no existe o no puede resolverse, backend devuelve `10` como default documentado. |
| D1-3 | Motor de timeout | El cronómetro vive en frontend y se activa solo dentro del shell autenticado. |
| D1-4 | Eventos válidos | Lista cerrada en **RN-02** y §6.1. Implementación: `bindInactivityActivityListeners` en `sessionInactivity.ts` con `{ capture: true }` en `document` (obligatorio para DevExtreme). |
| D1-5 | Expiración local | Al vencer el timeout, el frontend ejecuta cleanup local inmediato y luego intenta `logout` best-effort sin depender de su éxito para redirigir. |
| D1-6 | 401 transversal | El cliente autenticado debe traducir `401` en limpieza de sesión y vuelta a login para evitar que una pestaña quede “dentro” con token ya revocado. |
| D1-7 | Multi-tab | No se sincroniza actividad entre pestañas; la invalidación cruzada solo se observa cuando otra pestaña hace una request con token ya revocado y recibe `401`. |
| D1-8 | Backend scope | No se implementa expiración server-side por `last_used_at`, `expires_at` ni middleware de inactivity timeout en esta fase. |

### Orden de trabajo

1. Resolver en backend la lectura única de `MinutosWeb` con fallback `10`.
2. Extender `SessionContextBuilder`/`LoginService` y esquemas OpenAPI para incluir `inactivityTimeoutMinutes`.
3. Ajustar tests feature de auth para validar el nuevo campo y su consistencia entre `login` y `auth/me`.
4. Extender tipos y persistencia frontend (`SessionContext`, storage, bootstrap).
5. Implementar hook/guard de inactividad en la superficie autenticada (`ShellLayout` o wrapper equivalente) con eventos cerrados y cleanup local.
6. Incorporar manejo homogéneo de `401` en cliente HTTP/auth flow para expulsión al login cuando el token ya esté revocado.
7. Añadir E2E con timeout corto y actualizar documentación/matriz/OpenAPI.

### Archivos o módulos a revisar/tocar

| Capa | Archivos |
|------|----------|
| Backend auth | `backend/app/Services/Auth/LoginService.php`, `backend/app/Services/Auth/SessionContextBuilder.php`, `backend/app/Http/Controllers/AuthController.php` |
| Backend soporte | `backend/app/Services/...` o `backend/app/Support/...` para la lectura de `MinutosWeb` con fallback, según el patrón más simple del repo |
| Backend OpenAPI/tests | `backend/app/OpenApi/OpenApiSchemas.php`, `backend/tests/Feature/AuthLoginTest.php`, `backend/tests/Feature/OpenApiDocumentationTest.php` |
| Frontend auth | `frontend/src/features/auth/types.ts`, `frontend/src/features/auth/AuthProvider.tsx`, `frontend/src/features/auth/authStorage.ts` |
| Frontend shell | `frontend/src/app/layout/ShellLayout.tsx` y/o nuevo hook dedicado bajo `frontend/src/features/auth/` para el temporizador |
| Frontend HTTP/router | `frontend/src/shared/http/client.ts`, `frontend/src/app/router/RequireAuth.tsx`, `frontend/src/app/router/AppRoutes.tsx` si hace falta preservar mensaje/redirect consistente |
| E2E | `frontend/tests/e2e/*` (nuevo spec de expiración o ampliación de uno existente) |
| Docs | `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` si se decide reforzar la nota de `401` en endpoints de sesión |

### Riesgos

- Si el cliente HTTP no centraliza el `401`, una pestaña puede conservar UI local aunque el token ya haya sido revocado por otra pestaña, incumpliendo parcialmente `AC-06`.
- La lectura de `MinutosWeb` depende de una pieza de configuración global aún no materializada como servicio reusable; hay que evitar sobrediseñar un subsistema entero fuera de alcance.
- Los eventos de actividad pueden dispararse con demasiada frecuencia si se usa `mousemove` sin throttling; en D conviene elegir eventos discretos o controlar frecuencia.
- Los E2E de timeout son sensibles al tiempo; necesitan override corto y determinista para no volverse frágiles.

### Tests a ejecutar

- Backend: `backend/tests/Feature/AuthLoginTest.php`
- Backend: `backend/tests/Feature/OpenApiDocumentationTest.php`
- Frontend unit/component del hook/guard de inactividad y del manejo de `401` compartido
- E2E nuevo o ampliado para login → espera/inactividad → redirect a `/login` → intento de acceso protegido bloqueado

### Dudas / bloqueos

- No hay bloqueo funcional pendiente tras el C1.
- Queda a criterio de D la forma mínima de leer `MinutosWeb` en backend mientras `SPEC-001-04` no tenga todavía su slice implementado; debe resolverse con la menor superficie posible y sin abrir ABM/configuración adicional.
- En implementación habrá que decidir si el mensaje de expiración viaja por estado de navegación, query param o store efímero; cualquiera de las tres opciones sirve mientras no amplíe alcance.

### Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**. El plan se limita a exponer el timeout en el contexto de sesión, aplicar expiración local por inactividad, mantener el `401` como verdad backend del acceso protegido y cubrirlo con tests/documentación, sin introducir refresh, sincronización multi-tab proactiva ni un módulo general de parámetros fuera del slice.

---

## 3.5) Verificación D (2026-05-31)

| Verificación | Resultado |
|--------------|-----------|
| `sessionContext` expone `inactivityTimeoutMinutes` en backend | OK — `SessionContextBuilder` incorpora el campo y `OpenApiSchemas` documenta el contrato actualizado |
| Fallback documentado de `MinutosWeb` | OK — `InactivityTimeoutResolver` y `config/paqsuite_auth.php` devuelven `10` cuando no hay valor válido |
| Timeout local por inactividad en frontend | OK — `SessionLifecycleManager` controla el temporizador, reinicia por interacción/navegación/request exitosa y expira la sesión |
| `401` compartido fuerza salida al login | OK — `apiRequest` emite evento de expiración en requests autenticados con `401` y `AuthProvider` limpia sesión |
| Mensaje post-expiración en login | OK — `LoginPage` consume `expiredReasonKey` y muestra `auth.unauthenticated` |
| Cobertura unitaria frontend | OK — `npm test` pasó con 12 archivos / 37 tests incluyendo `sessionInactivity.test.ts` y `client.test.ts` |
| Cobertura E2E del flujo de timeout | OK — `npm run test:e2e -- tests/e2e/smoke.spec.ts` pasó con 6 tests incluyendo expiración por inactividad |
| Cobertura backend de integración | Parcial — se actualizaron `AuthLoginTest` y `OpenApiDocumentationTest`, pero `php artisan test` no pudo completarse por timeout de conexión SQL Server externo (`SQLSTATE[08001]`) |
| Sintaxis PHP de archivos backend modificados | OK — `php -l` sin errores en resolver, builder, esquemas OpenAPI, tests y config |

### Archivos planificados o ajustados en D

- Backend: `backend/config/paqsuite_auth.php`, `backend/app/Services/Auth/InactivityTimeoutResolver.php`, `backend/app/Services/Auth/SessionContextBuilder.php`, `backend/app/OpenApi/OpenApiSchemas.php`, `backend/tests/Feature/AuthLoginTest.php`, `backend/tests/Feature/OpenApiDocumentationTest.php`
- Frontend: `frontend/src/features/auth/AuthProvider.tsx`, `frontend/src/features/auth/LoginPage.tsx`, `frontend/src/features/auth/authEvents.ts`, `frontend/src/features/auth/authStorage.ts`, `frontend/src/features/auth/sessionInactivity.ts`, `frontend/src/features/auth/sessionInactivity.test.ts`, `frontend/src/features/auth/SessionLifecycleManager.tsx`, `frontend/src/features/auth/types.ts`, `frontend/src/shared/http/client.ts`, `frontend/src/shared/http/client.test.ts`, `frontend/src/app/App.tsx`
- Tests/mocks: `frontend/tests/e2e/smoke.spec.ts`, `frontend/tests/e2e/menu-sidebar.spec.ts`, `frontend/tests/e2e/theme.spec.ts`, `frontend/tests/e2e/locale.spec.ts`, `frontend/tests/e2e/change-password.spec.ts`, `frontend/tests/e2e/avatar-menu.spec.ts`, `frontend/src/features/preferences/userPreferences.test.ts`

---

## 4) Impacto en Datos

### Tablas afectadas
- Configuracion global (origen de `MinutosWeb`).
- `personal_access_tokens` o sesion equivalente para invalidacion.

### Seed minimo para tests
- Valor de `MinutosWeb` configurable por ambiente de test.
- Usuario autenticado con token valido.

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| GET | `/api/v1/auth/me` | Bearer Sanctum + `X-Paq-Cliente` | Usuario autenticado | No |
| POST | `/api/v1/auth/logout` | Bearer Sanctum + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operacion

#### GET `/api/v1/auth/me`
**Autorizacion:** usuario autenticado.
**Response 200:** envelope con sesion vigente.
**Response 401:** sesion expirada/invalida.
**Response 403:** autenticado sin permisos de contexto.

#### POST `/api/v1/auth/logout`
**Autorizacion:** usuario autenticado.
**Response 200:** cierre manual exitoso.
**Response 401:** token invalido/expirado.
**Response 403:** restriccion de politica (si aplica).

### 5.3 Actualizacion matriz permisos

- [ ] Confirmar que endpoints de sesion contemplan expiracion por 401.
- [ ] Documentar en descripcion OpenAPI la regla de inactividad.
- [ ] Verificar reflejo en `/api/documentation`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- Hook/global guard de inactividad en shell (`SessionLifecycleManager` + `useInactivityTimeout`).
- Mensaje de sesion expirada y redireccion a login.
- Reinicio de contador en eventos de actividad definidos (§6.1).

### 6.1 Eventos de actividad — fuente de verdad runtime

| Origen | Mecanismo | Cubre |
|--------|-----------|-------|
| Puntero / clic | `pointerdown`, `click` (capture) | Botones, menú, ítems de lista DevExtreme |
| Táctil | `touchstart` (capture) | Toques en móvil |
| Teclado | `keydown` (capture, incl. `Tab`) | Navegación entre campos, Enter, atajos |
| Scroll | `wheel`, `scroll` (capture) | Rueda, barra de scroll, táctil, Page Up/Down en contenedor |
| Campos | `input` (capture) | TextBox y similares |
| Routing | `location.pathname` | Navegación menú / sidebar |
| API | `SistemaPartes:authenticated-request-succeeded` | Requests autenticadas OK vía `apiRequest` |

**Módulos:** `frontend/src/features/auth/sessionInactivity.ts` (`bindInactivityActivityListeners`, `createInactivityController`), `useInactivityTimeout.ts`, `SessionLifecycleManager.tsx`.

**Regla DevExtreme:** los listeners van con `{ capture: true }` en `document`. No alcanza con `pointerdown` en bubble sobre `window`: DevExtreme intercepta y detiene propagación.

**Fuera de alcance como actividad:** `mousemove` continuo, `focus`/`blur` aislados, cambio de visibilidad de pestaña, timers o polling sin interacción.

### data-testid sugeridos
- `session-timeout-banner`
- `session-timeout-redirect`
- `session-activity-ping`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T1 | Backend | Exponer/leer `MinutosWeb` para autenticacion | Valor disponible y con default |
| T2 | Backend | Invalidar sesion por inactividad | 401 consistente en endpoints protegidos |
| T3 | Frontend | Implementar detector de actividad/inactividad | Redireccion al login al vencer timeout |
| T4 | Tests | Integration + E2E de expiracion | Cobertura 401 y flujo UX |
| T5 | Docs | OpenAPI + matriz + default documentado | Trazabilidad completa |

---

## 8) Estrategia de Tests

- **Unit:** logica de calculo de timeout y default de `MinutosWeb`.
- **Integration:** respuestas 401 por sesion vencida.
- **E2E:** inactividad simulada con timeout corto en ambiente de prueba.

---

## 9) Riesgos y Edge Cases

- Diferencias de reloj entre cliente y servidor.
- Pestanas multiples reactivando sesion de forma inconsistente.
- Parametro ausente o mal configurado en despliegues iniciales.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Inactividad aplica cierre real de sesion
- [ ] Default `MinutosWeb` documentado

### Checklist normas transversales
- [ ] Endpoints nuevos/modificados con policy en codigo
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en `/api/documentation` coherente con codigo y matriz
- [ ] 401/403 documentados por operacion protegida
- [ ] Envelope JSON respetado
- [ ] `X-Paq-Cliente` documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliacion de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

### Backend
- Middleware/servicio de expiracion por inactividad.

### Frontend
- Guard de sesion inactiva y notificacion de expiracion.

### OpenAPI
- Descripciones de respuestas 401/403 en endpoints de sesion.

### Docs
- `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`

---

## Historial CC PQ #1 (04/06/2026) — Parte I 09/06/2026

Corrección: umbral `MinutosWeb` desde **última actividad** (`lastActivityAt`), no desde inicio de sesión.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | Auditar `useInactivityTimeout` y eventos | `SessionLifecycleManager`, eventos pointer/keydown/navegación/API |
| T2 | Separar `sessionStart` de `lastActivityAt` | `createInactivityController` |
| T3 | Tests regresión | Vitest `sessionInactivity.test.ts`, E2E `smoke.spec.ts` |

## Historial CC PQ #3 (09/06/2026)

Corrección: el temporizador no consideraba interacciones DevExtreme (botones, menú, listas) ni scroll completo.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | Ampliar eventos RN-02 §6.1 con capture en `document` | `bindInactivityActivityListeners` en `sessionInactivity.ts` |
| T2 | Incluir `scroll` además de `wheel` | Grillas y contenedores `.dx-scrollable-*` |
| T3 | Incluir `Tab` en `keydown` | Revoca R-C1-03 original |
| T4 | Tests regresión | `sessionInactivity.test.ts` (capture + scroll + Tab) |
| T5 | Regla Cursor | `.cursor/rules/sesion-inactividad-expiracion.mdc` |










