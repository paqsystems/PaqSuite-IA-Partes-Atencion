# HU-GEN-02-politicas-endpoints — Políticas de autorización por endpoint

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-politicas-endpoints |
| **SPEC origen** | [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-28 |
| **Dependencias** | HU-GEN-02-autorizacion-menu-api; HU-GEN-02-modelo-roles-permisos-seed |
| **Norma TR transversal** | [`docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`](../../04-tareas/_NORMAS-TRANSVERSALES-TR.md) §1 |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Criterio: cada endpoint protegido MVP tiene política documentada | Matriz endpoint ↔ permiso **y** OpenAPI |
| Reglas autorización backend por endpoint (entregable SPEC) | Middleware/policies + anotaciones OpenAPI |
| Entregable SPEC: documentar en TR/OpenAPI por slice | Contrato publicado en `/api/documentation` |
| Criterio: permisos menú no reemplazan controles backend | RN explícita |
| Trazabilidad HU-SPEC: Políticas por endpoint | Objeto de esta HU |

## Narrativa

Como **arquitecto del API**,  
quiero **que cada endpoint protegido valide permisos en backend**,  
para **que ocultar el menú no sea la única barrera de seguridad**.

## Contexto funcional

SPEC-001-02 exige que **cada endpoint protegido del MVP** tenga política o regla documentada, y que los **permisos de menú no reemplacen controles backend** (criterio medible). El entregable del SPEC indica documentar en **TR/OpenAPI por slice**; en SistemaPartes el contrato publicado es **OpenAPI (L5-Swagger)** en `/api/documentation`, con anotaciones desde `backend/OpenApi.php` (SPEC MVP §6.1).

**En términos simples:** no alcanza con implementar el control en código ni con una tabla interna en markdown. Cada ruta protegida debe quedar **descrita en OpenAPI** con quién puede llamarla y qué pasa si falla la autenticación (401) o el permiso (403). La matriz endpoint ↔ permiso y el OpenAPI deben **decir lo mismo**.

Visibilidad por fila de datos (cliente/vendedor/supervisor) es HU hermana.

## Alcance incluido

- Middleware o policies en rutas `api/v1/*` autenticadas.
- Matriz documentada: endpoint ↔ permiso/rol requerido.
- **Publicación OpenAPI** (L5-Swagger) alineada a la matriz: ver sección siguiente.
- Respuestas **401** (no autenticado) y **403** (sin permiso).
- Servicio de autorización reutilizable desde controllers.
- Lista blanca de endpoints públicos (login, recuperación, health) **sin** `security` en OpenAPI.
- Actualización de matriz **y** OpenAPI con cada slice SPEC-101-xx (regla de mantenimiento).

### Definición OpenAPI (obligatoria por endpoint)

Para cada operación del MVP que exija autenticación/autorización, el contrato OpenAPI debe incluir como mínimo:

| Elemento OpenAPI | Qué debe reflejar |
|------------------|-------------------|
| **`security`** | Esquema de autenticación (p. ej. Bearer token Sanctum) en rutas protegidas |
| **Header `X-Paq-Cliente`** | Tenancy MONO (SPEC MVP §3); documentado como parámetro/header requerido donde aplique |
| **Respuesta `401`** | Sin token o token inválido |
| **Respuesta `403`** | Token válido pero sin permiso para la operación |
| **Descripción de la operación** | Permiso, rol o atributo requerido (`Permiso_Alta`, `Permiso_Modi`, etc.) en texto o extensión acordada en TR |
| **Envelope JSON** | Errores coherentes con `error`, `respuesta`, `resultado` — ver [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md) y `_NORMAS-TRANSVERSALES-TR.md` §2 |

Rutas públicas (login, recuperación, health): declaradas **sin** bloque `security`. Endpoints de negocio: **nunca** publicarse en OpenAPI sin su política de acceso documentada.

Fuente técnica: anotaciones en controllers/DTOs + raíz `backend/OpenApi.php`; spec generado accesible en **`/api/documentation`**.

**Norma transversal para parte C:** toda TR de slice debe incorporar esta definición vía [`docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`](../../04-tareas/_NORMAS-TRANSVERSALES-TR.md) y la plantilla [`docs/04-tareas/_PLANTILLA-TR-SLICE.md`](../../04-tareas/_PLANTILLA-TR-SLICE.md) (sección 5).

## Fuera de alcance

- Filtros de visibilidad de datos SistemaPartes por perfil (HU-GEN-02-visibilidad-datos-SistemaPartes).
- ABM seguridad UI (SPEC fuera de alcance).
- 2FA (SPEC fuera de alcance).

## Reglas de negocio

1. Toda ruta protegida exige autenticación + autorización explícita.
2. **Permisos de menú no reemplazan controles backend** (criterio SPEC).
3. Token válido sin permiso para la operación → **403**.
4. Sin token → **401**.
5. Matriz publicada en documentación versionada (`docs/04-tareas/` o anexo TR).
6. **Coherencia OpenAPI:** lo documentado en OpenAPI debe coincidir con la política real del endpoint (middleware/policy) y con la matriz endpoint ↔ permiso. No se acepta endpoint implementado y protegido que **no** figure en OpenAPI con su `security` y respuestas 401/403, salvo lista blanca explícita.
7. Cada nuevo slice `SPEC-101-xx` agrega o actualiza sus operaciones en OpenAPI **en el mismo PR/slice** que el código.

## Criterios de aceptación

- [ ] Inventario de endpoints MVP con política asignada (tabla única publicada).
- [ ] **Cada endpoint protegido del inventario aparece en OpenAPI** con `security`, header `X-Paq-Cliente` (si aplica) y respuestas **401** y **403** documentadas.
- [ ] Rutas públicas (login, recuperación, health) listadas en OpenAPI **sin** `security`.
- [ ] OpenAPI generado accesible en `/api/documentation` y coherente con la matriz endpoint ↔ permiso.
- [ ] Llamada sin token → 401.
- [ ] Token válido sin permiso → 403 sin efecto lateral en datos.
- [ ] Feature tests: al menos un caso 403 por recurso crítico.
- [ ] Ningún controller confía solo en ocultar botones en UI.
- [ ] Matriz referencia entregable `matriz-permisos-mvp.md` donde aplique.

## Escenarios Gherkin

```gherkin
Feature: Políticas por endpoint (SPEC-001-02)

  Scenario: Request sin autenticación
    Given un endpoint protegido del MVP
    When se invoca sin token
    Then responde HTTP 401

  Scenario: Token válido sin permiso
    Given un usuario autenticado sin permiso de alta
    When intenta POST en recurso protegido
    Then responde HTTP 403
    And no modifica datos

  Scenario: Token válido con permiso
    Given un usuario con permiso adecuado en seed
    When invoca el endpoint autorizado
    Then la operación procede según reglas de negocio

  Scenario: Menú oculto no implica endpoint abierto
    Given un ítem oculto en menú para el usuario
    When intenta acceder directamente al endpoint del ítem
    Then recibe HTTP 403 si no tiene permiso backend

  Scenario: OpenAPI refleja la política del endpoint
    Given un endpoint protegido publicado en OpenAPI
    When se consulta su operación en /api/documentation
    Then declara el esquema security requerido
    And documenta respuestas 401 y 403
    And la descripción indica el permiso o rol exigido
```

## Supuestos explícitos

- Atributos `Permiso_Alta`, `Permiso_Modi`, `Permiso_Baja`, `Permiso_Repo`: convención del modelo seed; no listados en SPEC-001-02.
- Envelope JSON de errores: convención SPEC MVP §6.1.
- Framework Laravel policies/middleware + L5-Swagger: stack TR.
- Formato exacto del esquema `securitySchemes` (Bearer): detalle en TR; raíz en `backend/OpenApi.php`.

## Preguntas abiertas

- ¿Ubicación canónica de la tabla viva endpoint ↔ permiso durante desarrollo?
- ¿Se documenta el permiso requerido solo en `description` o también con extensión/vendor tag acordado en TR?

## Riesgos de ambigüedad

- Matriz, código y OpenAPI deben actualizarse juntos; riesgo de contrato desactualizado si solo se documenta en markdown.

## Veredicto B1

**Lista para TR:** Sí con observaciones (formato `securitySchemes` y extensión permiso en OpenAPI)










