# TR-GEN-10-catalogo-proveedores-ia — Catálogo inicial de proveedores IA

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-10-catalogo-proveedores-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-catalogo-proveedores-ia.md) |
| **SPEC relacionada** | [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Should |
| **Dependencias** | HU-GEN-10-configuracion-asistente-ia; SPEC-001-04-configuracion-global; TR-GEN-01-menu-avatar; TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-30 |

**Origen:** [HU-GEN-10-catalogo-proveedores-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-catalogo-proveedores-ia.md)  
**Referencia SPEC:** [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Catálogo inicial de proveedores IA.

### Narrativa
Como usuario autenticado que configura el chat quiero elegir entre proveedores IA soportados y ver su documentación de onboarding para configurar mi integración personal con criterios claros y consistentes.

### In scope / Out of scope
- **In scope:** tabla dedicada de catálogo, seed inicial, endpoint protegido de lectura, exposición de `providerId`, nombre visible, capacidades declaradas, requisito de `baseUrl` y `supportUrl`, consumo desde la configuración personal del chat.
- **Out of scope:** ABM de proveedores en UI, inclusión de proveedores fuera del catálogo inicial, validación exhaustiva de modelos en tiempo real.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario ve todos los proveedores del catálogo inicial al configurar el chat.
- **AC-02**: Cada proveedor expone nombre visible y datos suficientes para selección.
- **AC-03**: El usuario puede acceder a la URL de onboarding (`supportUrl`) de cada proveedor.
- **AC-04**: La UI distingue si un proveedor requiere `baseUrl` editable.
- **AC-05**: Todos los proveedores del catálogo inicial están disponibles como opciones soportadas en la primera HU.
- **AC-06**: El catálogo visible en frontend es consistente con el seed inicial y el catálogo documental.

### Escenarios Gherkin

```gherkin
Feature: Catálogo inicial de proveedores IA

  Scenario: Visualizar proveedores soportados
    Given un usuario autenticado en la configuración del chat
    When abre el selector de proveedor
    Then ve todos los proveedores del catálogo inicial

  Scenario: Ver ayuda del proveedor
    Given un usuario autenticado
    When selecciona un proveedor del catálogo
    Then puede acceder a la URL de onboarding asociada

  Scenario: Identificar proveedor con baseUrl editable
    Given un usuario autenticado
    When selecciona un proveedor que requiere endpoint propio
    Then la UI le informa que debe completar baseUrl
```

---

## 3) Reglas de Negocio

1. **RN-01**: El catálogo inicial incluye `ollama`, `openai`, `anthropic`, `googleGemini`, `azureOpenAi`, `openRouter`, `groq` y `mistral`.
2. **RN-02**: Todos los proveedores del catálogo inicial se consideran soportados en la primera HU.
3. **RN-03**: `providerId` es la clave lógica estable usada por frontend y backend.
4. **RN-04**: `supportUrl` es un dato del catálogo de proveedores y no de la configuración sensible del usuario.
5. **RN-05**: Si un proveedor requiere `baseUrl`, el catálogo debe exponer esa condición para que la UI la comunique claramente.
6. **RN-06**: El catálogo visible al usuario se obtiene desde backend y no debe quedar hardcodeado en frontend.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-30)

**Fuentes revisadas:** HU-GEN-10-catalogo-proveedores-ia, SPEC-001-10-chat-asistente-ia, `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-proveedores.md`, `docs/02-producto/SistemaPartes/modelo-datos.md`, `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`, `docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`, `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`, backend (`routes/api.php`, `OpenApiSchemas.php`, `UserPreferencesController`, `UserPreferencesTest`, modelos actuales), frontend (`features/preferences/*`, `features/avatar/*`, `features/theme/*`, `ShellLayout.tsx`, `protectedRoutes.tsx`).

### Resultado general

- **Estado:** Apto
- **Puede pasar a D1:** **Sí**, con resoluciones C1 aceptadas para evitar divergencias entre seed, API y UI.

### Aceptación stakeholder (2026-05-30)

Se aceptan las recomendaciones de ajuste de la TR bajo §3.1. El slice queda cerrado con:

- estructura frontend en `frontend/src/features/preferences/`;
- `401` obligatorio y `403` no aplicable en el MVP actual salvo policy nueva;
- verificación explícita contra el catálogo documental editable;
- orden estable del catálogo para evitar divergencias visuales y de snapshot.

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|------------------------------|
| AMB-C01 | **Ubicación frontend desalineada con el código real** | La TR propone `frontend/src/features/profile/components/*`, pero hoy existen `features/preferences/*` y no hay slice `profile`; dos programadores podrían crear superficies distintas. | **D1-1:** concentrar este slice en `frontend/src/features/preferences/` y dejar la composición visual dentro de la pantalla/sección de preferencias del usuario. |
| AMB-C02 | **Orden y filtro del catálogo no están cerrados** | AC-01/AC-06 exigen consistencia visible, pero la TR no define si la API devuelve todos, solo activos ni el orden estable; eso rompe snapshots, seeds y UX. | **D1-2:** `GET /api/v1/chat-assistant/providers` devuelve solo proveedores activos, ordenados por el catálogo inicial aprobado en documentación/seed: `ollama`, `openai`, `anthropic`, `googleGemini`, `azureOpenAi`, `openRouter`, `groq`, `mistral`. |
| AMB-C03 | **Mapping DB ↔ API no explicitado** | Producto usa columnas sugeridas `provider_id`, `nombre_visible`, `soporta_imagenes`, `requiere_base_url_editable`, `url_onboarding`; la API usa `providerId`, `displayName`, `supportsVision`, `requiresBaseUrl`, `supportUrl`. Sin mapping cerrado puede haber contratos distintos. | **D1-3:** cerrar DTO/backend mapper canónico tabla → API con esos nombres camelCase y sin exponer columnas internas (`id_proveedor`, `activo`, `observacion`). |
| AMB-C04 | **403 no está justificado por el modelo actual** | La TR pide 401/403, pero en el MVP vigente los endpoints de preferencias operan con regla simple “usuario autenticado”; agregar una policy ad hoc ampliaría alcance silenciosamente. | **D1-4:** mantener endpoint protegido solo por autenticación en MVP; documentar **401 sí** y **403 no aplica por ahora**, salvo que una TR hermana introduzca una policy específica. |
| AMB-C05 | **Sincronización catálogo documental vs seed técnico** | La HU ya advertía riesgo de divergencia; la TR no define cuál es la fuente operativa para validar AC-06. | **D1-5:** tomar `asistente-ia-proveedores.md` como fuente editable aprobada y reflejarla 1:1 en seed/test fixture; agregar test de integración o snapshot de catálogo esperado. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | `supportUrl` en UI | Abrir en nueva pestaña externa con `noopener,noreferrer`; no navegar dentro del shell. |
| AMB-M02 | `requiresBaseUrl` en UX | Mostrar hint contextual al seleccionar proveedor; no bloquear todavía otros campos fuera del slice de configuración. |
| AMB-M03 | E2E mínimos | Cubrir al menos dos flujos: listado completo y visibilidad del link/hint al cambiar de proveedor. |
| AMB-M04 | Nombre del cliente HTTP frontend | Reutilizar patrón de `preferencesApi.ts`/`apiRequest`; no crear cliente duplicado para este slice. |
| AMB-M05 | Seeds QA | Además del seed principal, contemplar fixture con un proveedor inactivo para asegurar que no se expone en API/UI. |

### Contradicciones TR ↔ código ↔ HU

| Contradicción | Resolución |
|---------------|------------|
| TR §6 apunta a `features/profile/*`, pero el código vigente concentra preferencias del usuario en `features/preferences/*` | **Cerrado:** D1 implementa el catálogo dentro del slice `preferences` y no crea un árbol `profile` paralelo. |
| TR §5.2 documenta 403 sin definir permiso/policy, mientras la HU y el patrón actual hablan de “usuario autenticado” | **Cerrado:** en MVP el endpoint queda con autenticación simple; 403 se retira de los casos obligatorios de test/documentación salvo cambio explícito de seguridad. |
| AC-06 exige consistencia con catálogo documental, pero la TR no definía mecanismo de verificación | **Cerrado:** el seed técnico y los tests tomarán como referencia el catálogo editable aprobado. |

### Supuestos detectados

- El catálogo inicial es acotado y no paginado; `resultado.items` alcanza para MVP.
- Todos los proveedores iniciales se cargan activos en la primera entrega.
- `supportsVision` es una capacidad declarada del catálogo y no una validación dinámica contra el proveedor remoto.
- El selector de proveedor vive en la configuración personal del usuario y no en una pantalla administrativa separada.

### Preguntas para decisión humana

- Todas cerradas con la aceptación stakeholder de esta revisión.

### Recomendaciones de ajuste de la TR

- **Aplicadas en esta revisión.**

### Veredicto C1

**Apta para D1 — C1 definitivamente cerrado.** El slice queda implementable con estructura frontend real, contrato API cerrado y sincronización explícita entre catálogo documental y seed técnico.

---

## 3.2) Resoluciones C1 — pre-D1 (2026-05-30)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Estructura frontend | Implementar dentro de `frontend/src/features/preferences/`; la configuración del asistente cuelga de preferencias del usuario. |
| R-C1-02 | Filtro catálogo | `GET /api/v1/chat-assistant/providers` expone solo proveedores activos. |
| R-C1-03 | Orden catálogo | Orden estable del catálogo inicial aprobado: `ollama`, `openai`, `anthropic`, `googleGemini`, `azureOpenAi`, `openRouter`, `groq`, `mistral`. |
| R-C1-04 | Contrato API | Cada item expone exactamente `providerId`, `displayName`, `supportsVision`, `requiresBaseUrl`, `supportUrl`. |
| R-C1-05 | Campos internos | No exponer `id_proveedor`, flags internos ni notas administrativas del catálogo. |
| R-C1-06 | Seguridad | Endpoint protegido por autenticación + `X-Paq-Cliente`; `403` no aplica en MVP mientras no exista policy adicional. |
| R-C1-07 | Fuente de verdad | El catálogo editable `asistente-ia-proveedores.md` se refleja 1:1 en seed/backend y sirve de referencia para AC-06. |
| R-C1-08 | Integración frontend | Consumir el catálogo con utilidades del slice `preferences` y testear selector, hint `requiresBaseUrl` y link `supportUrl`. |

---

## 3.3) Plan D1 — Implementación (2026-05-30)

### Alcance entendido

Exponer un catálogo inicial de proveedores IA activos y ordenados de forma estable para que la configuración personal del asistente pueda consumirlo desde frontend. El slice cubre modelo/lectura backend, endpoint protegido, tipado/consumo frontend y validación de coherencia con el catálogo documental. **Fuera:** ABM desde UI, validación dinámica contra APIs remotas de proveedores y ampliaciones fuera del catálogo inicial.

### Fuentes leídas

- SPEC: `docs/05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md`
- HU: `docs/03-historias-usuario/001-Generalidades/HU-GEN-10-catalogo-proveedores-ia.md`
- TR: `docs/04-tareas/001-Generalidades/TR-GEN-10-catalogo-proveedores-ia.md`
- Contexto: `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-proveedores.md`
- Regla local: `.cursor/rules/local/01-tablas-seguridad-compartidas-sql.md`
- Código: `backend/routes/api.php`, `backend/app/OpenApi/OpenApiSchemas.php`, `backend/tests/Feature/OpenApiDocumentationTest.php`, `frontend/src/shared/http/client.ts`, `frontend/src/features/preferences/preferencesApi.ts`, `frontend/src/features/avatar/components/AvatarMenu.tsx`

### Impacto esperado

#### Base de datos

- Consumir la tabla `PQ_PARTES_ASISTENTE_IA_PROVEEDORES` como catálogo funcional.
- D1 **no** propone migración Laravel para crear/alterar tablas `pq_SistemaPartes_*` desde el repo; si la tabla no existe en el entorno, su provisión queda fuera del alcance de este slice y debe resolverse por esquema del cliente/script externo autorizado.
- El seed del slice se limita a **filas** del catálogo inicial y verificación contra la fuente documental aprobada.

#### Backend

- Nuevo modelo Eloquent para el catálogo (`AsistenteIaProveedor`) con mapeo de columnas legacy/SQL Server a DTO camelCase.
- Nuevo controller de lectura del catálogo protegido por auth + tenant.
- Nueva ruta `GET /api/v1/chat-assistant/providers` en `routes/api.php`.
- Extensión de `OpenApiSchemas.php` y de `OpenApiDocumentationTest.php` para incluir el nuevo path/schema.
- Feature test dedicado para validar orden estable, filtro por activos, envelope y 401.

#### Frontend

- Cliente API específico para leer el catálogo reutilizando `apiRequest`.
- Tipado `providerCatalog` y consumo dentro de `frontend/src/features/preferences/components/`.
- Integración del selector, link `supportUrl` y hint `requiresBaseUrl` en la superficie real de preferencias del usuario.

#### Tests

- Unit para mapping y reglas de presentación (`requiresBaseUrl`, `supportUrl`).
- Integration backend del endpoint con seed controlado.
- E2E de la configuración del asistente consumiendo el catálogo visible.

#### Documentación

- Actualizar matriz `matriz-permisos-mvp.md`.
- Mantener trazabilidad explícita con `asistente-ia-proveedores.md`.

#### DevOps

- Sin cambios de infraestructura.
- Si se necesitara carga inicial de filas en entornos QA/dev, resolver con seeder/fixture del slice, no con DDL desde el repo.

### Decisiones D1 (cerradas en C1)

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Fuente backend | Modelo `AsistenteIaProveedor` sobre `PQ_PARTES_ASISTENTE_IA_PROVEEDORES`, sin exponer columnas internas. |
| D1-2 | Endpoint | `GET /api/v1/chat-assistant/providers` autenticado, con filtro `activo=true` y orden estable `R-C1-03`. |
| D1-3 | DTO API | `providerId`, `displayName`, `supportsVision`, `requiresBaseUrl`, `supportUrl`. |
| D1-4 | Frontend | Consumo desde `features/preferences/` y no desde un árbol `profile` paralelo. |
| D1-5 | Seed/fixture | Fixture/seed 1:1 con `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-proveedores.md`. |
| D1-6 | Seguridad | 401 obligatorio; 403 no aplica en MVP actual salvo policy nueva. |

### Orden de trabajo

1. Modelar catálogo backend y fixture/seed de filas aprobadas.
2. Exponer endpoint + route + OpenAPI + test feature.
3. Implementar cliente/tipos frontend y conectar selector/hints en `features/preferences`.
4. Añadir E2E y sincronizar documentación/matriz.

### Archivos previstos

| Capa | Archivos |
|------|----------|
| Backend | `backend/app/Models/AsistenteIaProveedor.php`, `backend/app/Http/Controllers/ChatAssistantProviderCatalogController.php`, `backend/routes/api.php`, `backend/app/OpenApi/OpenApiSchemas.php`, `backend/tests/Feature/ChatAssistantProviderCatalogTest.php`, `backend/tests/Feature/OpenApiDocumentationTest.php` |
| Seed / fixture | `backend/database/seeders/Mvp/ChatAssistantProviderCatalogSeeder.php` o fixture equivalente del slice (solo filas; sin DDL) |
| Frontend | `frontend/src/features/chatAssistant/api/getProviderCatalog.ts`, `frontend/src/features/chatAssistant/model/providerCatalog.ts`, `frontend/src/features/preferences/components/ChatAssistantSettingsSection.tsx`, `frontend/src/features/preferences/components/ChatAssistantProviderHelpLink.tsx`, `frontend/src/features/preferences/components/ChatAssistantProviderFields.tsx` |
| E2E | `frontend/tests/e2e/chat-assistant-settings.spec.ts` (nuevo) o extensión del spec de configuración del asistente |
| Docs | `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` |

### Tests a ejecutar

- Backend: feature del catálogo con 200/401, activos solamente y orden estable.
- Backend: `OpenApiDocumentationTest`.
- Frontend unit: mapping y reglas visuales de `requiresBaseUrl`.
- E2E: listado completo, link onboarding y hint contextual.

### Dudas / bloqueos

- Confirmar disponibilidad real de `PQ_PARTES_ASISTENTE_IA_PROVEEDORES` en el entorno objetivo antes de la parte D. D1 asume tabla provisionada fuera del repo o ya existente.

### Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**. El plan se limita al catálogo inicial, su endpoint y el consumo desde la configuración del asistente, sin ABM ni validación dinámica contra proveedores externos.

## 4) Impacto en Datos

### Tablas afectadas
- `PQ_PARTES_ASISTENTE_IA_PROVEEDORES` (nueva / recomendada en producto): catálogo funcional de proveedores IA.

### Seed mínimo para tests
- Fila activa por cada proveedor inicial: `ollama`, `openai`, `anthropic`, `googleGemini`, `azureOpenAi`, `openRouter`, `groq`, `mistral`.
- Al menos un proveedor con `requiere_base_url_editable = true`.
- Al menos un proveedor con `soporta_imagenes = false` o `false` en seed de prueba si se quiere validar degradación visual del selector.
- Al menos un proveedor inactivo para verificar que no se expone en API/UI.
- El orden y contenido esperado del seed deben verificarse contra `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-proveedores.md`.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Código, matriz y OpenAPI deben coincidir. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/chat-assistant/providers` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### GET `/api/v1/chat-assistant/providers`

**Autorización:** usuario autenticado.

**Request:** sin body.

**Response 200:** envelope con catálogo inicial de proveedores **activos**, ordenados de forma estable según el catálogo documental aprobado.

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "items": [
      {
        "providerId": "ollama",
        "displayName": "Ollama",
        "supportsVision": true,
        "requiresBaseUrl": true,
        "supportUrl": "https://ollama.com/download"
      }
    ]
  }
}
```

**Response 401:** no autenticado.

**Response 403:** no aplica en el MVP actual mientras la regla siga siendo “usuario autenticado” sin policy adicional.

**OpenAPI (L5-Swagger):**

- [ ] Endpoint documentado en controller/DTO.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuesta 401 documentada.
- [ ] 403 documentado solo si en una oleada posterior se agrega policy específica.
- [ ] Envelope documentado.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Agregar fila para `GET /api/v1/chat-assistant/providers`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/chatAssistant/api/getProviderCatalog.ts` (nuevo): lectura del catálogo desde backend.
- `frontend/src/features/chatAssistant/model/providerCatalog.ts` (nuevo): tipado de items del catálogo.
- `frontend/src/features/preferences/components/ChatAssistantSettingsSection.tsx` (nuevo o ajuste): selector de proveedor con datos del catálogo.
- `frontend/src/features/preferences/components/ChatAssistantProviderHelpLink.tsx` (nuevo o ajuste): acceso a `supportUrl`.
- `frontend/src/features/preferences/components/ChatAssistantProviderFields.tsx` (nuevo o ajuste): indicación visual de si `baseUrl` es requerido.

### data-testid sugeridos
- `chatAssistantProviderSelect`
- `chatAssistantProviderOptionOllama`
- `chatAssistantProviderSupportLink`
- `chatAssistantProviderRequiresBaseUrlHint`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Crear modelo/repository del catálogo y seed inicial de proveedores | Seed consistente con catálogo documental |
| T2 | Backend | Exponer `GET /api/v1/chat-assistant/providers` | OpenAPI + 401 |
| T3 | Frontend | Consumir el catálogo en la configuración personal del chat | Selector visible con todos los proveedores |
| T4 | Frontend | Exponer `supportUrl` e indicador de `baseUrl` requerida | UX clara de onboarding |
| T5 | Tests | Integration endpoint + unit mapping catálogo + E2E selector/ayuda | AC verdes y orden estable validado |
| T6 | Docs | Matriz endpoint ↔ permiso y trazabilidad con catálogo documental | Coherencia visible |

---

## 8) Estrategia de Tests

- **Unit:** mapeo `providerId` → modelo frontend; interpretación de `requiresBaseUrl` y `supportUrl`.
- **Integration:** `GET /api/v1/chat-assistant/providers` con 200/401; verificar que solo devuelve activos, en orden estable y alineados al catálogo documental editable.
- **E2E:**  
  - abrir configuración y ver todos los proveedores del catálogo;  
  - seleccionar proveedor con `supportUrl` y verificar acceso visible a onboarding;  
  - seleccionar proveedor que requiere `baseUrl` y verificar hint contextual.

---

## 9) Riesgos y Edge Cases

- Desincronización entre catálogo documental y seed técnico.
- Provider marcado como soportado pero con metadatos incompletos para la UI.
- `supportUrl` desactualizada o rota.
- Cambios futuros en el catálogo pueden romper orden o snapshots visuales si no se respeta el orden estable definido en `R-C1-03`.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Seed inicial consistente con catálogo funcional aprobado
- [ ] Integración validada con configuración personal del chat

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401 documentado y 403 solo si aplica policy específica
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR










