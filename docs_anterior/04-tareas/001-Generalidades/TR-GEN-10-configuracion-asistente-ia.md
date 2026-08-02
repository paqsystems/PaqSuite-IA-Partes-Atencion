# TR-GEN-10-configuracion-asistente-ia — Configuración personal del Chat Asistente IA

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-10-configuracion-asistente-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-configuracion-asistente-ia.md) |
| **SPEC relacionada** | [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-10-catalogo-proveedores-ia; TR-GEN-01-menu-avatar; TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-30 |

**Origen:** [HU-GEN-10-configuracion-asistente-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-configuracion-asistente-ia.md)  
**Referencia SPEC:** [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Configuración personal del Chat Asistente IA.

### Narrativa
Como usuario autenticado quiero configurar mi proveedor, credencial y modelo para el Chat Asistente IA desde preferencias del usuario para habilitar el uso del chat con consumo asociado a mi propia configuración.

### In scope / Out of scope
- **In scope:** sección de configuración en preferencias del usuario, lectura de catálogo soportado, persistencia cifrada por usuario, validación de campos obligatorios por proveedor, actualización y deshabilitación de configuración.
- **Out of scope:** configuración compartida por tenant, administración centralizada por otro actor, edición del catálogo desde UI, envío automático a soporte.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario puede abrir la configuración del chat desde preferencias del usuario.
- **AC-02**: Puede seleccionar un proveedor soportado y ver su ayuda de onboarding.
- **AC-03**: Puede guardar `providerId`, `apiKey`, `modelId` y `baseUrl` cuando corresponda.
- **AC-04**: Si el proveedor no requiere `baseUrl`, ese campo no bloquea el guardado.
- **AC-05**: Si faltan datos obligatorios, el guardado es rechazado con mensaje controlado.
- **AC-06**: La configuración guardada queda asociada al usuario actual y no a `users`.
- **AC-07**: El usuario puede actualizar o deshabilitar su configuración.

### Escenarios Gherkin

```gherkin
Feature: Configuración personal del Chat Asistente IA

  Scenario: Guardar configuración válida
    Given un usuario autenticado en preferencias del usuario
    When selecciona un proveedor soportado
    And completa credencial y modelo requeridos
    Then la configuración se guarda correctamente
    And queda asociada solo a ese usuario

  Scenario: Proveedor con baseUrl obligatoria
    Given un usuario autenticado
    And selecciona un proveedor que requiere baseUrl
    When intenta guardar sin baseUrl
    Then ve un mensaje de validación
    And la configuración no se guarda

  Scenario: Consultar ayuda de onboarding
    Given un usuario autenticado en la configuración del chat
    When selecciona un proveedor
    Then ve un acceso a la URL de onboarding del proveedor
```

---

## 3) Reglas de Negocio

1. **RN-01**: La primera versión permite una sola configuración activa por usuario.
2. **RN-02**: `providerId` debe existir en el catálogo de proveedores soportados.
3. **RN-03**: `supportUrl` se obtiene desde el catálogo de proveedores y no desde la configuración sensible del usuario.
4. **RN-04**: La credencial debe persistirse cifrada fuera de `users`.
5. **RN-05**: Si el proveedor requiere `baseUrl`, el campo es obligatorio para guardar.
6. **RN-06**: El usuario puede editar su configuración sin afectar la de otros usuarios.
7. **RN-07**: Deshabilitar una configuración no implica necesariamente eliminar la credencial; la definición exacta se cierra en implementación del slice.
8. **RN-08**: `supportUrl` se resuelve desde el catálogo de proveedores y no se persiste duplicado en la tabla sensible de credenciales.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-30)

**Fuentes revisadas:** HU-GEN-10-configuracion-asistente-ia, SPEC-001-10-chat-asistente-ia, TR-GEN-10-catalogo-proveedores-ia, `docs/02-producto/SistemaPartes/modelo-datos.md`, `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`, `docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`, `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`, backend (`routes/api.php`, `UserPreferencesController`, `OpenApiSchemas.php`, `UserPreferencesTest`, modelos existentes, `config/app.php`), frontend (`features/preferences/*`, `features/theme/*`, `ShellLayout.tsx`, `protectedRoutes.tsx`, cliente `apiRequest`).

### Resultado general

- **Estado:** Apto
- **Puede pasar a D1:** **Sí**, con resoluciones C1 aceptadas para cerrar el contrato del recurso personal y el comportamiento de habilitar/deshabilitar.

### Aceptación stakeholder (2026-05-30)

Se aceptan las recomendaciones de ajuste de la TR y los supuestos detectados bajo §3.1. El slice queda cerrado con:

- shape explícito de `GET` cuando no existe configuración;
- inclusión de `hasApiKey` y `supportsVision` en el recurso personal;
- estructura frontend dentro de `frontend/src/features/preferences/`;
- `403` no aplicable en el MVP actual salvo policy nueva;
- supuestos operativos aceptados: una configuración por usuario, consulta paralela de catálogo + recurso personal, `supportUrl` no duplicado en credenciales y reutilización de `isEnabled` + `supportsVision` por slices hermanos.

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|------------------------------|
| AMB-C01 | **Contrato GET cuando no existe configuración** | La TR dice “estado vacío” pero no define shape; dos implementaciones pueden devolver `{}`, campos `null` o 404, rompiendo frontend y tests. | **D1-1:** `GET /api/v1/chat-assistant/me/configuration` responde `200` con envelope estable y `resultado` que incluya al menos `hasConfiguration`, `hasApiKey`, `providerId`, `modelId`, `baseUrl`, `isEnabled`; si no existe configuración, `hasConfiguration=false` y el resto en valores seguros/no sensibles. |
| AMB-C02 | **Edición de configuración sin reexponer `apiKey`** | El usuario debe poder editar configuración existente, pero la API no puede devolver la credencial completa; falta cerrar si `PUT` exige reenviar `apiKey` siempre o permite preservarla. | **D1-2:** `PUT` acepta `apiKey` obligatorio en alta y opcional en edición; si llega vacío/ausente en una configuración ya existente, conserva la credencial cifrada previa. El GET nunca devuelve `apiKey`, solo `hasApiKey` y opcionalmente un `apiKeyHint` enmascarado. |
| AMB-C03 | **Deshabilitar vs eliminar credencial** | RN-07 deja la definición “a implementación”; eso puede terminar en delete físico o solo toggle lógico, alterando seguridad y UX. | **D1-3:** `PATCH /status` solo cambia `isEnabled`; no elimina ni blanquea `apiKey_encrypted`. La eliminación/borrado físico queda fuera de este slice. |
| AMB-C04 | **`supportsVision` no quedó cerrado en el contrato** | SPEC y modelo de datos lo contemplan, e imágenes/chat dependen de esa capacidad; si esta TR no define si se persiste/devuelve, las TR hermanas pueden asumir fuentes distintas. | **D1-4:** la configuración persistida y leída expone `supportsVision` como capacidad resuelta del proveedor/configuración activa; backend la deriva del catálogo/configuración y la devuelve en GET/PUT/PATCH sin requerir que la UI la capture manualmente. |
| AMB-C05 | **Ubicación frontend desalineada con el código existente** | La TR apunta a `features/profile/*`, pero el repo hoy usa `features/preferences/*` y no existe slice `profile`; eso abre dos implementaciones incompatibles. | **D1-5:** implementar la sección dentro del flujo real de preferencias del usuario (`frontend/src/features/preferences/`) y reutilizar componentes del catálogo/configuración allí. |
| AMB-C06 | **403 no justificado por el patrón actual** | Igual que en preferencias, el modelo actual usa regla simple “usuario autenticado”; introducir una policy adicional cambiaría alcance sin decisión explícita. | **D1-6:** documentar y testear `401` y `422` como obligatorios; `403` queda “no aplica en MVP actual” salvo aparición posterior de una policy específica. |
| AMB-C07 | **Proveedor inactivo referenciado por una configuración existente** | La TR lo marca como edge case, pero no define si el GET falla, si la UI bloquea o si permite editar. | **D1-7:** el GET conserva la configuración histórica del usuario; la UI la muestra como inconsistente/no disponible y obliga a seleccionar un proveedor activo para guardar cambios futuros. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | Método `PUT` | Tratarlo como upsert del recurso personal completo, no como patch parcial arbitrario. |
| AMB-M02 | `baseUrl` opcional | Si el proveedor no la requiere, persistir `""` o `null` controlado server-side sin invalidar el recurso. |
| AMB-M03 | Link de onboarding | Reutilizar `supportUrl` del catálogo y abrirlo en nueva pestaña externa. |
| AMB-M04 | E2E mínimos | Cubrir alta válida y edición/deshabilitación sin reingresar credencial cuando ya existe. |
| AMB-M05 | Cifrado | Resolver server-side con el mecanismo estándar de Laravel; nunca cifrar ni almacenar secretos en frontend/localStorage. |

### Contradicciones TR ↔ código ↔ HU

| Contradicción | Resolución |
|---------------|------------|
| TR §6 usa `features/profile/*`, pero el frontend actual no tiene ese slice y centraliza preferencias en `features/preferences/*` | **Cerrado:** D1 implementa la configuración del asistente dentro de `features/preferences/*`. |
| HU deja abierta la diferencia entre deshabilitar y eliminar; TR repite la ambigüedad en RN-07 | **Cerrado:** este slice solo deshabilita lógicamente (`isEnabled`), sin borrar credencial. |
| SPEC/modelo de datos mencionan `supportsVision`, pero §5.2 no lo devuelve ni lo usa | **Cerrado:** el contrato de configuración debe devolver `supportsVision` para coordinar con chat/imágenes. |
| TR exige 403 en todos los endpoints aunque la regla funcional declarada es “usuario autenticado” | **Cerrado:** 403 se marca como no aplicable en MVP actual. |

### Supuestos detectados

- Existe una única configuración personal por usuario en la primera versión.
- El frontend de configuración siempre consulta el catálogo de proveedores en paralelo al recurso personal.
- `supportUrl` no se persiste duplicado en la tabla de credenciales.
- El chat podrá usar `isEnabled` + `supportsVision` del recurso personal para degradar UX sin volver a pedir datos sensibles.

### Preguntas para decisión humana

- Ninguna adicional. Las decisiones bloqueantes quedan cerradas con la aceptación stakeholder de esta revisión.

### Recomendaciones de ajuste de la TR

- **Aplicadas en esta revisión.**

### Veredicto C1

**Apta para D1 — C1 definitivamente cerrado.** El slice queda implementable con contrato de recurso personal explícito, preservación segura de credencial y deshabilitación lógica sin borrado.

---

## 3.2) Resoluciones C1 — pre-D1 (2026-05-30)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | GET sin configuración | `GET /api/v1/chat-assistant/me/configuration` devuelve `200` con envelope estable y `resultado` que informa `hasConfiguration=false`. |
| R-C1-02 | Lectura segura | El GET nunca devuelve `apiKey`; expone `hasApiKey` y, si aporta valor de UX, solo un hint enmascarado. |
| R-C1-03 | Alta vs edición | `PUT` funciona como upsert del recurso personal: alta requiere `apiKey`; edición puede conservar la credencial previa si no se envía una nueva. |
| R-C1-04 | Deshabilitación | `PATCH /api/v1/chat-assistant/me/configuration/status` solo cambia `isEnabled`; no borra la credencial ni elimina el registro. |
| R-C1-05 | Capacidad de visión | `supportsVision` se resuelve server-side y forma parte del recurso leído/devuelto para coordinar con chat e imágenes. |
| R-C1-06 | Estructura frontend | La UI de este slice vive en `frontend/src/features/preferences/`; no se crea un árbol paralelo `features/profile/`. |
| R-C1-07 | Seguridad | Endpoints protegidos por autenticación + `X-Paq-Cliente`; `401` y `422` obligatorios; `403` no aplica mientras no exista policy adicional. |
| R-C1-08 | Proveedor inactivo legado | La configuración existente puede leerse, pero la UI debe marcarla como inconsistente y exigir un proveedor activo para guardar cambios. |

---

## 3.3) Plan D1 — Implementación (2026-05-30)

### Alcance entendido

Implementar el recurso personal de configuración del asistente IA por usuario, con persistencia cifrada de la credencial, lectura segura del estado actual, guardado tipo upsert y deshabilitación lógica. El slice cubre backend, frontend, validaciones y integración con el catálogo de proveedores. **Fuera:** configuración compartida por tenant, ABM de proveedores, borrado físico de credenciales y envío automático a soporte.

### Fuentes leídas

- SPEC: `docs/05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md`
- HU: `docs/03-historias-usuario/001-Generalidades/HU-GEN-10-configuracion-asistente-ia.md`
- TR: `docs/04-tareas/001-Generalidades/TR-GEN-10-configuracion-asistente-ia.md`
- TR hermana: `docs/04-tareas/001-Generalidades/TR-GEN-10-catalogo-proveedores-ia.md`
- Regla local: `.cursor/rules/local/01-tablas-seguridad-compartidas-sql.md`
- Código: `backend/routes/api.php`, `backend/app/Http/Controllers/UserPreferencesController.php`, `backend/app/OpenApi/OpenApiSchemas.php`, `backend/tests/Feature/UserPreferencesTest.php`, `frontend/src/features/preferences/preferencesApi.ts`, `frontend/src/features/preferences/useUserPreferences.ts`, `frontend/src/app/router/protectedRoutes.tsx`, `frontend/src/app/layout/ShellLayout.tsx`

### Impacto esperado

#### Base de datos

- Consumir la tabla `PQ_PARTES_ASISTENTE_IA_CREDENCIALES` como almacenamiento sensible por usuario.
- D1 **no** propone migración Laravel para crear/alterar tablas `pq_SistemaPartes_*` desde el repo; si la tabla no existe en el entorno, su provisión queda fuera del slice y deberá resolverse por esquema del cliente/script externo autorizado.
- Uso obligatorio de cifrado server-side para `apiKey`.

#### Backend

- Nuevo modelo/repository para la configuración sensible por usuario.
- Nuevo controller para `GET`, `PUT` y `PATCH /status`.
- Requests específicos para upsert y toggle de estado.
- Mapper/resolvedor del recurso personal para devolver `hasConfiguration`, `hasApiKey`, `supportsVision`, `isEnabled` y valores seguros.
- Extensión de `OpenApiSchemas.php`, `routes/api.php` y tests feature/OpenAPI.

#### Frontend

- Cliente API específico para leer/guardar/deshabilitar la configuración del asistente.
- Hook de estado del recurso personal reutilizando `apiRequest`.
- Superficie de preferencias del usuario con selector de proveedor, campos dependientes, ayuda de onboarding y toggle de estado.
- Integración con catálogo para `supportUrl`, `requiresBaseUrl` y capacidad derivada `supportsVision`.

#### Tests

- Unit frontend para validaciones de formulario y comportamiento `baseUrl`.
- Integration backend para GET/PUT/PATCH con 200/401/422.
- E2E de alta, edición sin reenviar `apiKey`, deshabilitación y proveedor inactivo legado.

#### Documentación

- Actualizar matriz `matriz-permisos-mvp.md`.
- Reflejar en OpenAPI los tres endpoints y el shape sin configuración.

#### DevOps

- Sin cambios de infraestructura.
- Si QA/dev requiere datos base, resolver con fixtures/seed controlado del slice, sin DDL en el repo.

### Decisiones D1 (cerradas en C1)

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Recurso personal | Controller y mapper dedicados para `GET/PUT/PATCH /api/v1/chat-assistant/me/configuration`. |
| D1-2 | Credencial | `apiKey` se cifra server-side y nunca vuelve completa al frontend; el GET solo devuelve `hasApiKey` y eventualmente hint enmascarado. |
| D1-3 | Upsert | `PUT` crea o actualiza; si en edición no llega `apiKey`, se preserva la credencial previa. |
| D1-4 | Estado | `PATCH /status` solo cambia `isEnabled`; no borra registro ni secreto. |
| D1-5 | Capacidad visual | `supportsVision` se resuelve server-side desde proveedor/configuración activa y se devuelve al frontend. |
| D1-6 | UI | El formulario vive en la superficie real de preferencias del usuario, no en un slice `profile` inexistente. |
| D1-7 | Seguridad | 401 y 422 obligatorios; 403 no aplica en MVP actual salvo policy nueva. |
| D1-8 | Legado | Si el proveedor quedó inactivo, el GET sigue leyendo la configuración y la UI obliga a reseleccionar antes de guardar. |

### Orden de trabajo

1. Modelar recurso personal y resolver almacenamiento cifrado/mapping seguro.
2. Exponer `GET`, `PUT`, `PATCH /status` con requests, route, OpenAPI y tests feature.
3. Implementar cliente/hook frontend y formulario de preferencias del asistente.
4. Integrar catálogo, validaciones dependientes del proveedor y toggle de estado.
5. Añadir E2E y alinear documentación/matriz.

### Archivos previstos

| Capa | Archivos |
|------|----------|
| Backend | `backend/app/Models/AsistenteIaCredencial.php`, `backend/app/Http/Controllers/ChatAssistantConfigurationController.php`, `backend/app/Http/Requests/UpsertChatAssistantConfigurationRequest.php`, `backend/app/Http/Requests/UpdateChatAssistantConfigurationStatusRequest.php`, `backend/app/Support/ChatAssistantConfigurationMapper.php`, `backend/app/Support/ChatAssistantConfigurationErrorCodes.php`, `backend/routes/api.php`, `backend/app/OpenApi/OpenApiSchemas.php`, `backend/tests/Feature/ChatAssistantConfigurationTest.php`, `backend/tests/Feature/OpenApiDocumentationTest.php` |
| Frontend | `frontend/src/features/chatAssistant/api/getMyConfiguration.ts`, `frontend/src/features/chatAssistant/api/saveMyConfiguration.ts`, `frontend/src/features/chatAssistant/api/updateMyConfigurationStatus.ts`, `frontend/src/features/chatAssistant/model/myChatAssistantConfiguration.ts`, `frontend/src/features/chatAssistant/hooks/useMyChatAssistantConfiguration.ts`, `frontend/src/features/preferences/components/ChatAssistantSettingsSection.tsx`, `frontend/src/features/preferences/components/ChatAssistantProviderFields.tsx`, `frontend/src/features/preferences/components/ChatAssistantProviderHelpLink.tsx` |
| Superficie UI | `frontend/src/features/preferences/pages/ChatAssistantSettingsPage.tsx`, `frontend/src/app/router/protectedRoutes.tsx` (si hace falta crear ruta protegida para la superficie de preferencias del asistente) |
| E2E | `frontend/tests/e2e/chat-assistant-settings.spec.ts` |
| Docs | `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` |

### Tests a ejecutar

- Backend: `ChatAssistantConfigurationTest` con 200/401/422, shape vacío estable, preservación de credencial y proveedor inactivo legado.
- Backend: `OpenApiDocumentationTest`.
- Frontend unit: validación `baseUrl` requerida/no requerida y flujo de edición sin reingresar `apiKey`.
- E2E: alta válida, error por `baseUrl` faltante, deshabilitación y edición conservando secreto.

### Dudas / bloqueos

- Confirmar disponibilidad real de `PQ_PARTES_ASISTENTE_IA_CREDENCIALES` en el entorno objetivo antes de la parte D.
- El repo aún no muestra una superficie consolidada de “preferencias del usuario”; para la implementación se perfila una ruta protegida mínima del asistente dentro de `features/preferences`, a confirmar cuando se ejecute D.

### Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**. El plan cubre solo la configuración personal del asistente, su almacenamiento seguro y su UI mínima en preferencias del usuario, sin introducir administración multiusuario ni borrado físico de credenciales.

## 4) Impacto en Datos

### Tablas afectadas
- `PQ_PARTES_ASISTENTE_IA_CREDENCIALES` (nueva / recomendada): configuración sensible del chat por usuario.
- `PQ_PARTES_ASISTENTE_IA_PROVEEDORES` (lectura): fuente de proveedores soportados y `supportUrl`.

### Seed mínimo para tests
- Usuario autenticado sin configuración previa.
- Usuario autenticado con configuración válida activa.
- Usuario autenticado con configuración deshabilitada.
- Proveedor con `requiere_base_url_editable = true`.
- Proveedor con `requiere_base_url_editable = false`.
- Proveedor inactivo referenciado por una configuración previa para verificar lectura segura y obligación de reselección.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Código, matriz y OpenAPI deben coincidir. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/chat-assistant/me/configuration` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| PUT | `/api/v1/chat-assistant/me/configuration` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| PATCH | `/api/v1/chat-assistant/me/configuration/status` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### GET `/api/v1/chat-assistant/me/configuration`

**Autorización:** usuario autenticado.

**Request:** sin body.

**Response 200:** envelope con la configuración actual del usuario o estado vacío si no existe, manteniendo shape estable del recurso personal.

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "hasConfiguration": true,
    "hasApiKey": true,
    "providerId": "ollama",
    "modelId": "llama3.1",
    "baseUrl": "http://localhost:11434",
    "supportsVision": true,
    "isEnabled": true
  }
}
```

**Response 401:** no autenticado.

**Response 403:** no aplica en el MVP actual mientras la regla siga siendo “usuario autenticado” sin policy adicional.

**Ejemplo sin configuración:**

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "hasConfiguration": false,
    "hasApiKey": false,
    "providerId": "",
    "modelId": "",
    "baseUrl": "",
    "supportsVision": false,
    "isEnabled": false
  }
}
```

#### PUT `/api/v1/chat-assistant/me/configuration`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "providerId": "ollama",
  "apiKey": "secret-value",
  "modelId": "llama3.1",
  "baseUrl": "http://localhost:11434"
}
```

Reglas de request:

- `PUT` funciona como upsert del recurso personal completo.
- En alta, `apiKey` es obligatoria.
- En edición, si `apiKey` llega vacía o ausente y ya existe configuración previa, se preserva la credencial cifrada existente.

**Response 200:** envelope con resumen de configuración guardada, sin exponer credencial completa.

```json
{
  "error": 0,
  "respuesta": "chatAssistant.configurationSaved",
  "resultado": {
    "hasConfiguration": true,
    "hasApiKey": true,
    "providerId": "ollama",
    "modelId": "llama3.1",
    "baseUrl": "http://localhost:11434",
    "supportsVision": true,
    "isEnabled": true
  }
}
```

**Response 401:** no autenticado.

**Response 403:** no aplica en el MVP actual mientras no exista policy adicional.

**Response 422:** datos obligatorios ausentes o `baseUrl` faltante cuando el proveedor lo requiera.

#### PATCH `/api/v1/chat-assistant/me/configuration/status`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "isEnabled": false
}
```

**Response 200:** envelope con estado actualizado.

Regla del slice:

- `PATCH /status` solo cambia `isEnabled`; no elimina el registro ni borra la credencial cifrada.

**Response 401:** no autenticado.

**Response 403:** no aplica en el MVP actual mientras no exista policy adicional.

**OpenAPI (L5-Swagger):**

- [ ] Endpoints documentados en controller/DTO.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401 y 422 documentadas.
- [ ] 403 documentado solo si en una oleada posterior se agrega policy específica.
- [ ] Envelope documentado.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Agregar filas para `GET /api/v1/chat-assistant/me/configuration`, `PUT /api/v1/chat-assistant/me/configuration` y `PATCH /api/v1/chat-assistant/me/configuration/status`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/preferences/components/ChatAssistantSettingsSection.tsx`: formulario principal de configuración personal.
- `frontend/src/features/chatAssistant/api/getMyConfiguration.ts` (nuevo): lectura de configuración actual.
- `frontend/src/features/chatAssistant/api/saveMyConfiguration.ts` (nuevo): guardado de configuración.
- `frontend/src/features/chatAssistant/api/updateMyConfigurationStatus.ts` (nuevo): habilitar/deshabilitar configuración.
- `frontend/src/features/chatAssistant/model/myChatAssistantConfiguration.ts` (nuevo): tipado.
- `frontend/src/features/preferences/components/ChatAssistantProviderFields.tsx`: visibilidad y validación de `baseUrl`.
- `frontend/src/features/preferences/components/ChatAssistantProviderHelpLink.tsx`: acceso a `supportUrl`.

### Reglas de integración frontend

- La pantalla consulta en paralelo el catálogo de proveedores y el recurso personal del usuario.
- `supportUrl` se consume desde el catálogo y no se persiste duplicado en la configuración sensible.
- La UI puede usar `isEnabled` y `supportsVision` del recurso personal para coordinar el acceso a chat e imágenes sin volver a pedir secretos.

### data-testid sugeridos
- `chatAssistantSettingsSection`
- `chatAssistantConfigurationProviderSelect`
- `chatAssistantConfigurationApiKeyInput`
- `chatAssistantConfigurationModelIdInput`
- `chatAssistantConfigurationBaseUrlInput`
- `chatAssistantConfigurationSaveButton`
- `chatAssistantConfigurationStatusToggle`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Crear modelo/repository de configuración personal y persistencia cifrada | Sin uso de `users`; sin exponer credencial |
| T2 | Backend | Exponer endpoints GET/PUT/PATCH de configuración personal | OpenAPI + 401/422 |
| T3 | Frontend | Implementar sección de configuración del chat dentro de preferencias del usuario | AC funcionales visibles |
| T4 | Frontend | Integrar selector de proveedor, `supportUrl` y lógica de `baseUrl` requerida | Validaciones correctas |
| T5 | Tests | Integration API + unit de validación + E2E del formulario | Casos verdes, edición sin reenviar `apiKey` y deshabilitación cubiertas |
| T6 | Docs | Matriz endpoint ↔ permiso y trazabilidad con TR catálogo | Coherencia completa |

---

## 8) Estrategia de Tests

- **Unit:** validación de formulario según proveedor; regla `baseUrl` requerida/no requerida.
- **Integration:** GET/PUT/PATCH configuración personal con 200, 401 y 422; verificar shape estable sin configuración, preservación de credencial existente, `supportsVision` resuelto y lectura segura de proveedor inactivo legado.
- **E2E:**  
  - guardar configuración válida;  
  - intentar guardar proveedor que requiere `baseUrl` sin completarla;  
  - editar configuración existente sin reingresar `apiKey`;  
  - deshabilitar configuración sin eliminar credencial.

---

## 9) Riesgos y Edge Cases

- Exposición accidental de la credencial en responses, logs o errores.
- Divergencia entre regla de `baseUrl` del catálogo y validación del formulario.
- Proveedor inactivo heredado que obligue a reselección sin romper lectura ni perder trazabilidad de la configuración existente.
- Inconsistencia si el catálogo cambia y la configuración del usuario referencia un proveedor inactivo.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Persistencia cifrada fuera de `users`
- [ ] Integración validada con preferencias de usuario y catálogo de proveedores

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401 y 422 documentados; 403 solo si aplica policy específica
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 solo si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR










