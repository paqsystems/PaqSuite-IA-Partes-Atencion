# TR-GEN-02-visibilidad-datos-SistemaPartes — Visibilidad de datos por perfil funcional

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-visibilidad-datos-SistemaPartes](../../03-historias-usuario/001-Generalidades/HU-GEN-02-visibilidad-datos-SistemaPartes.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-modelo-roles-permisos-seed, TR-GEN-02-login-sesion, TR-GEN-02-politicas-endpoints |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-05-31 (D implementado) |

**Origen:** [HU-GEN-02-visibilidad-datos-SistemaPartes](../../03-historias-usuario/001-Generalidades/HU-GEN-02-visibilidad-datos-SistemaPartes.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Titulo
Aplicar visibilidad de clientes/comprobantes por perfil (cliente, vendedor, supervisor).

### Narrativa
Como usuario SistemaPartes, quiero ver solo los datos permitidos por mi perfil para proteger cartera y datos comerciales.

### In scope / Out of scope
- In scope: filtros en services/repositorios de consultas de clientes y comprobantes.
- In scope: helper central `visibleClientsForUser` para unificar criterio en backend.
- In scope: alineacion futura con slices funcionales de `SPEC-101`.
- Out of scope: ABM de asignaciones vendedor-cliente desde UI.

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Perfil Cliente solo accede a su `codCliente`.
- **AC-02**: Perfil Vendedor solo accede a clientes asignados.
- **AC-03**: Perfil Supervisor accede a todo cliente del tenant.
- **AC-04**: Acceso directo a dato fuera del universo visible retorna `404` sin fuga de datos.
- **AC-05**: `visibleClientsForUser` se reutiliza en consultas de slices `SPEC-101`.
- **AC-06**: Dashboard agrega solo datos del universo visible del perfil.
- **AC-07**: Usuario sin vínculo comercial válido recibe error controlado en login según `TR-GEN-02-login-sesion` (`auth.noCommercialProfile`).

### Escenarios Gherkin

```gherkin
Feature: Visibilidad por perfil funcional

  Scenario: Cliente restringido a su codigo
    Given un usuario perfil cliente con codCliente C1
    When consulta pedidos y comprobantes
    Then solo visualiza datos de C1

  Scenario: Vendedor no ve cartera ajena
    Given un usuario perfil vendedor V1
    When consulta clientes visibles
    Then obtiene solo clientes asignados a V1

  Scenario: Supervisor ve todo el tenant
    Given un usuario perfil supervisor
    When consulta listado de clientes
    Then ve todos los clientes del tenant

  Scenario: Intento de acceso directo fuera de alcance
    Given un vendedor autenticado
    When solicita un comprobante de cliente no asignado
    Then recibe 404 sin datos sensibles
```

---

## 3) Reglas de Negocio

1. **RN-01**: Un login representa exactamente un perfil funcional activo.
2. **RN-02**: Filtro de visibilidad se ejecuta siempre en backend, nunca solo en UI.
3. **RN-03**: `visibleClientsForUser` es la API interna unica para universo de clientes visibles.
4. **RN-04**: Politicas por endpoint y visibilidad por datos son capas complementarias.
5. **RN-05**: Todos los slices `SPEC-101-*` que consulten datos de clientes deben integrar `visibleClientsForUser`.
6. **RN-06**: Perfil funcional se resuelve con la cadena canónica ya adoptada en autenticación: `users.codigo` → `users.cod_usuario_web` → `PQ_PARTES_CLIENTES.cod_login` / `PQ_PARTES_USUARIOS.cod_login`.
7. **RN-07**: Esta TR es dueña del helper/base de visibilidad y también de los endpoints base `GET /api/v1/clientes`, `GET /api/v1/comprobantes/{id}` y `GET /api/v1/dashboard/resumen`, aunque los slices `SPEC-101-*` puedan extenderlos o reutilizarlos.
8. **RN-08**: Si el usuario tiene permiso base de consulta pero el recurso pedido queda fuera de su universo visible, la respuesta base del slice es `404` sin fuga de datos.

### Regla base `visibleClientsForUser(user)`

| `functionalProfile` | Universo de `cod_cliente` |
|---------------------|---------------------------|
| `cliente` | Solo el `cod_cliente` del login |
| `vendedor` | Clientes con `cod_vended` = vendedor del login |
| `supervisor` | Todos los clientes del tenant |

Fuente vínculo cerrada en C1: `users.codigo` → `users.cod_usuario_web` → `PQ_PARTES_CLIENTES.cod_login` / `PQ_PARTES_USUARIOS.cod_login` + flag `supervisor`.  
Filtro vendedor cerrado en C1: `PQ_PARTES_CLIENTES.cod_vended = codVendedor`.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-30)

**Fuentes revisadas:** HU-GEN-02-visibilidad-datos-SistemaPartes, SPEC-001-02-acceso-y-seguridad, `PROD-Sistema-Partes.md` (§7.2–§7.3, §10.2), `matriz-permisos-mvp.md`, `_NORMAS-TRANSVERSALES-TR.md`, backend (`CommercialProfileResolver`, `SessionContextBuilder`, modelos `PqPartesCliente` / `PqPartesUsuario` / `User`, `SecurityMvpSeeder`, `routes/api.php`), tests (`AuthLoginTest`, `SeedSeguridadMvpTest`).

### Resultado general

- **Estado:** Apta
- **Puede pasar a D1:** **Sí**, con resoluciones C1 aceptadas para cerrar ownership del slice, vínculo comercial y semántica de acceso fuera de alcance.

### Aceptación stakeholder (2026-05-31)

Se aceptan las decisiones humanas bloqueantes de esta revisión:

- la TR queda con **opción B** y es dueña también de `/api/v1/clientes`, `/api/v1/comprobantes/{id}` y `/api/v1/dashboard/resumen`;
- la fuente canónica del vínculo comercial es `users.codigo` → `users.cod_usuario_web` → `pq_SistemaPartes_* .cod_login`;
- para vendedor, el universo visible se obtiene con `PQ_PARTES_CLIENTES.cod_vended = codVendedor`;
- el acceso a un recurso fuera del universo visible responde **404**.

### Ambigüedades críticas

| ID | Tema | Riesgo | Recomendación / decisión necesaria |
|----|------|--------|------------------------------------|
| AMB-C01 | **Alcance del slice base vs endpoints concretos** | La TR se presenta como base transversal para `SPEC-101-*`, pero al mismo tiempo asume que este slice debe implementar `GET /api/v1/clientes`, `GET /api/v1/comprobantes/{id}` y `GET /api/v1/dashboard/resumen`. Dos programadores podrían implementar solo el helper base o, alternativamente, construir ahora endpoints de negocio completos. | **Cerrado:** opción **B** aceptada. Esta TR es dueña del helper/base y de esos tres endpoints base. |
| AMB-C02 | **Fuente canónica del vínculo login → perfil comercial mal cerrada** | La TR dice que el perfil funcional “se resuelve por `cod_login`”, pero el código actual usa `users.usuario` → `cod_usuario_web` y luego busca `PQ_PARTES_CLIENTES.cod_login` / `PQ_PARTES_USUARIOS.cod_login`. Eso permite implementaciones distintas del helper y de los futuros filtros. | **Cerrado:** aceptar explícitamente la cadena operativa ya implementada en `CommercialProfileResolver`: `users.codigo` → `users.cod_usuario_web` → `cod_login` comercial. |
| AMB-C03 | **Regla exacta del universo visible del vendedor/supervisor no está cerrada en SQL real** | La HU/TR hablan de “clientes asignados” y “clientes activos”, pero el modelo revisado solo evidencia `PQ_PARTES_CLIENTES.cod_vended`; no hay tabla `clientesde` usada en repo ni se documenta el campo de “activo”. Dos implementaciones podrían filtrar distinto. | **Cerrado:** para vendedor, usar `PQ_PARTES_CLIENTES.cod_vended = codVendedor`; para supervisor, la base del slice es “todos los clientes del tenant” sin un filtro adicional de activo no documentado. |
| AMB-C04 | **Acceso por ID fuera de alcance: `403` o `404`** | AC-04 y la HU aceptan `403 o 404`; sin criterio uniforme, distintos endpoints podrían filtrar distinto y romper consistencia de OpenAPI/tests. | **Cerrado:** el recurso fuera del universo visible responde **404**; el `403` queda reservado para falta de permiso base del endpoint. |
| AMB-C05 | **Contrato del helper `visibleClientsForUser` no está especificado** | La TR dice que es la “API interna única”, pero no define si devuelve lista de códigos, query builder, subquery reusable o specification. Dos programadores podrían crear helpers incompatibles entre sí y con los repositorios futuros. | **Cerrado para D1:** el helper debe definirse como la API interna única reusable del slice; su contrato técnico exacto se fijará en D1, pero no podrá duplicarse por endpoint. |
| AMB-C06 | **Frontend y E2E dependen de slices de consultas todavía inexistentes** | La TR pide selector de clientes, mensajes vacíos y dashboard filtrado, pero en el repo aún no existen esos endpoints ni esas pantallas de negocio; esto mezcla una base transversal con entregables de `SPEC-101-*`. | **Cerrado:** al tomarse la opción B, los endpoints base quedan en esta TR; los slices `SPEC-101-*` deberán reutilizar/extender esta base sin redefinir la regla de visibilidad. |

### Ambigüedades menores

| ID | Tema | Recomendación |
|----|------|---------------|
| AMB-M01 | Parámetro `tenant` en helper | En MONO no parece necesario pasarlo como parámetro si la consulta ya vive dentro de la base del cliente; D1 debe preferir `User` / contexto resuelto y no un tenant redundante. |
| AMB-M02 | Seed QA insuficiente | El seed actual crea un cliente cliente, vendedores y supervisor, pero no deja visible una cartera cruzada clara de dos vendedores para probar AC-02/AC-04. |
| AMB-M03 | Dashboard base | La TR menciona agregados del dashboard, pero no define shape de respuesta; D1 debe cerrar un contrato mínimo del resumen base. |
| AMB-M04 | Matriz permisos | La matriz ya referencia estos endpoints; con la opción B aceptada, esa referencia queda confirmada como ownership real del slice. |

### Contradicciones TR ↔ HU ↔ SPEC ↔ código

| Contradicción | Impacto | Recomendación |
|---------------|---------|---------------|
| La HU deja abierta la tabla/columna exacta del vínculo usuario ↔ perfil, pero la TR afirma `cod_login` como fuente cerrada | Puede ignorarse el alias `users.cod_usuario_web` ya implementado | Alinear la TR con `CommercialProfileResolver` |
| La TR menciona tablas ejemplo `clientes` / `clientesde`, pero el código actual modela `PQ_PARTES_CLIENTES` y `PQ_PARTES_USUARIOS` | Dos implementaciones pueden usar modelos/tablas distintas | Cerrar tablas reales o marcar las otras como meramente ilustrativas |
| El README del bloque la marca como “base; se extiende en SPEC-101”, pero la TR ahora será dueña de endpoints concretos | Podría leerse como contradicción de ownership | Ajustar narrativa/D1 para dejar claro que la TR crea la base y los endpoints iniciales que luego `SPEC-101-*` extienden |
| AC-07 reabre “login rechazado o error controlado”, pero `TR-GEN-02-login-sesion` ya cerró `auth.noCommercialProfile` como 403 controlado | Duplicación/confusión de comportamiento | Referenciar esa decisión y no dejarla abierta aquí |

### Supuestos detectados

- El carácter de supervisor puede derivarse desde `PQ_PARTES_USUARIOS.supervisor`.
- La base del slice no aplicará un filtro “activo” adicional mientras ese campo no esté documentado en el esquema real/contrato funcional.

### Preguntas para decisión humana

- Ninguna bloqueante. Las decisiones críticas quedan cerradas con la aceptación stakeholder de esta revisión.

### Recomendaciones de ajuste de la TR

- Reescribir §4 y §5 usando tablas reales (`PQ_PARTES_CLIENTES`, `PQ_PARTES_USUARIOS`, `users`) y no ejemplos ambiguos.
- Cerrar el contrato técnico mínimo de `visibleClientsForUser`.
- Alinear AC-07 con la decisión ya cerrada en `TR-GEN-02-login-sesion`.

### Veredicto C1

**Apta para D1 — C1 cerrado con decisiones humanas aceptadas.** Queda pendiente bajar estas resoluciones al plan técnico D1 y ajustar la narrativa de tablas/ownership, pero ya no hay bloqueos de alcance funcional.

---

## 3.2) Resoluciones C1 — pre-D1 (2026-05-31)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Ownership del slice | Esta TR implementa el helper/base de visibilidad y también los endpoints `GET /api/v1/clientes`, `GET /api/v1/comprobantes/{id}` y `GET /api/v1/dashboard/resumen`. |
| R-C1-02 | Fuente canónica del vínculo comercial | `users.codigo` → `users.cod_usuario_web` → `PQ_PARTES_CLIENTES.cod_login` / `PQ_PARTES_USUARIOS.cod_login`. |
| R-C1-03 | Filtro vendedor | El universo visible del vendedor se obtiene con `PQ_PARTES_CLIENTES.cod_vended = codVendedor`. |
| R-C1-04 | Filtro supervisor | Supervisor ve todos los clientes del tenant; no se agrega un filtro “activo” no documentado en esta TR. |
| R-C1-05 | Recurso fuera de alcance | Si el usuario tiene permiso base pero el recurso queda fuera de su universo visible, la respuesta base es `404` sin fuga de datos. |
| R-C1-06 | Helper base | `visibleClientsForUser` es obligatorio y reusable; D1 debe cerrar su forma técnica exacta sin duplicar lógica por endpoint. |
| R-C1-07 | AC-07 | Se alinea con `TR-GEN-02-login-sesion`: el usuario sin vínculo comercial válido recibe error controlado `auth.noCommercialProfile` durante login. |

---

## 3.3) Plan D1 — Implementación (2026-05-31)

### Alcance entendido

Implementar la base de visibilidad por perfil funcional y, además, los endpoints iniciales del slice: `GET /api/v1/clientes`, `GET /api/v1/comprobantes/{id}` y `GET /api/v1/dashboard/resumen`. El objetivo es que cliente, vendedor y supervisor operen sobre universos distintos desde backend, reutilizando una única regla interna de visibilidad y sin depender del menú. **Fuera:** ABM de asignaciones vendedor-cliente, filtros “activo” no documentados, y cualquier redefinición funcional por parte de slices `SPEC-101-*`.

### Fuentes leídas

- SPEC: `docs/05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md`
- HU: `docs/03-historias-usuario/001-Generalidades/HU-GEN-02-visibilidad-datos-SistemaPartes.md`
- TR: `docs/04-tareas/001-Generalidades/TR-GEN-02-visibilidad-datos-SistemaPartes.md`
- Producto: `docs/02-producto/SistemaPartes/PROD-Sistema-Partes.md` (§7.2–§7.3, §10.2)
- Dependencias: `TR-GEN-02-login-sesion`, `TR-GEN-02-politicas-endpoints`, `TR-GEN-02-modelo-roles-permisos-seed`
- Código: `backend/app/Services/Auth/CommercialProfileResolver.php`, `backend/app/Services/Auth/SessionContextBuilder.php`, `backend/app/Models/PqPartesCliente.php`, `backend/app/Models/PqPartesUsuario.php`, `backend/app/Models/User.php`, `backend/config/paqsuite_mvp.php`, `backend/tests/Feature/SeedSeguridadMvpTest.php`

### Impacto esperado

#### Base de datos

- Reuso de `PQ_PARTES_CLIENTES`, `PQ_PARTES_USUARIOS` y `users` como fuente del vínculo comercial.
- No se agregan migraciones DDL nuevas; las tablas comerciales siguen siendo legacy del cliente.
- Será necesario ampliar el seed/fixture QA con cartera cruzada suficiente para diferenciar vendedor vs supervisor y validar `404` fuera de universo visible.

#### Backend

- Crear servicio/helper único `visibleClientsForUser` sobre la cadena canónica: `users.codigo` → `users.cod_usuario_web` → `pq_SistemaPartes_* .cod_login`.
- Exponer los endpoints base del slice:
  - `GET /api/v1/clientes`
  - `GET /api/v1/comprobantes/{id}`
  - `GET /api/v1/dashboard/resumen`
- Aplicar dos capas complementarias:
  - permiso base del endpoint (`403` si falta)
  - visibilidad del universo de datos (`404` si el recurso queda fuera del universo visible)
- Mantener la lógica de visibilidad centralizada para que `SPEC-101-*` reutilice esta base y no la duplique.

#### Frontend

- El slice define el contrato base que consumirán selector de cliente, dashboards y consultas.
- No se requiere una pantalla autónoma en `001-Generalidades`, pero sí dejar explícito que los consumidores frontend de `SPEC-101-*` no podrán derivar el universo visible en cliente.
- Los estados “sin datos visibles” y selectores de cliente deberán integrarse luego sobre esta API base.

#### Tests

- Unit para `visibleClientsForUser` y, si se separa, para el resolvedor de acceso por ID.
- Integration para `/clientes`, `/comprobantes/{id}` y `/dashboard/resumen` con 200/401/403/404.
- E2E en los slices consumidores para verificar que vendedor A no vea cartera de vendedor B usando esta base.

#### Documentación

- OpenAPI para los tres endpoints del slice.
- Matriz `matriz-permisos-mvp.md` actualizada con la regla “aplica `visibleClientsForUser`”.
- Referencias explícitas para que los `SPEC-101-*` reutilicen la base sin redefinir ownership.

#### DevOps

- Sin cambios de infraestructura.
- La ejecución de tests seguirá dependiendo de SQL Server accesible, porque el slice trabaja sobre tablas comerciales legacy.

### Decisiones D1 (cerradas en C1)

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Ownership | Esta TR implementa helper/base y endpoints `GET /api/v1/clientes`, `GET /api/v1/comprobantes/{id}`, `GET /api/v1/dashboard/resumen`. |
| D1-2 | Vínculo comercial | Fuente canónica: `users.codigo` → `users.cod_usuario_web` → `PQ_PARTES_CLIENTES.cod_login` / `PQ_PARTES_USUARIOS.cod_login`. |
| D1-3 | Universo vendedor | `PQ_PARTES_CLIENTES.cod_vended = codVendedor`. |
| D1-4 | Universo supervisor | Todos los clientes del tenant, sin filtro adicional “activo” no documentado. |
| D1-5 | Recurso fuera de alcance | `404` si queda fuera del universo visible; `403` queda para falta de permiso base del endpoint. |
| D1-6 | Helper reusable | `visibleClientsForUser` es obligatorio y no puede duplicarse por endpoint o slice consumidor. |
| D1-7 | AC-07 | Se reutiliza la regla ya cerrada de login: `auth.noCommercialProfile`. |

### Orden de trabajo

1. Implementar el servicio/helper base de visibilidad reutilizando `CommercialProfileResolver` y `SessionContextBuilder`.
2. Definir el contrato técnico exacto de `visibleClientsForUser` para reusar en listados y accesos por ID.
3. Exponer `GET /api/v1/clientes`, `GET /api/v1/comprobantes/{id}` y `GET /api/v1/dashboard/resumen` con validación de permiso base + visibilidad.
4. Incorporar OpenAPI, matriz y tests feature del slice.
5. Dejar documentada la estrategia de reutilización obligatoria en `SPEC-101-*`.

### Archivos previstos

| Capa | Archivos |
|------|----------|
| Backend | `backend/app/Services/Visibility/VisibleClientsResolver.php`, `backend/app/Http/Controllers/ClientesController.php`, `backend/app/Http/Controllers/ComprobantesController.php`, `backend/app/Http/Controllers/DashboardResumenController.php`, `backend/routes/api.php`, `backend/app/OpenApi/OpenApiSchemas.php`, `backend/tests/Feature/ClientesVisibilityTest.php`, `backend/tests/Feature/ComprobantesVisibilityTest.php`, `backend/tests/Feature/DashboardResumenVisibilityTest.php`, `backend/tests/Feature/OpenApiDocumentationTest.php` |
| Soporte datos | `backend/app/Models/PqPartesCliente.php`, `backend/app/Models/PqPartesUsuario.php`, `backend/app/Models/User.php`, `backend/config/paqsuite_mvp.php`, `backend/database/seeders/Mvp/SecurityMvpSeeder.php` |
| Frontend / consumo futuro | Integración posterior desde los slices `SPEC-101-*` sobre el contrato base de este slice |
| Docs | `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` |

### Riesgos

- El esquema comercial legacy no documenta aún un flag “activo”; inventarlo en implementación ampliaría alcance.
- Si el helper devuelve un tipo demasiado acoplado a un endpoint, `SPEC-101-*` podría terminar duplicando lógica.
- La falta de datos seed suficientemente cruzados puede falsear validaciones de vendedor vs supervisor.
- El ownership dual (base + endpoints base) obliga a dejar muy clara la frontera con `SPEC-101-*` para evitar solapamientos.

### Tests a ejecutar

- Unit del helper/resolvedor de universo visible.
- Feature para `/api/v1/clientes`, `/api/v1/comprobantes/{id}` y `/api/v1/dashboard/resumen` con 200/401/403/404.
- `OpenApiDocumentationTest` para validar documentación de seguridad/contrato.
- Tests de seed/fixtures QA ampliados para cartera cruzada.

### Dudas / bloqueos

- No quedan bloqueos funcionales tras C1.
- D1 debe cerrar en la implementación el contrato técnico exacto de `visibleClientsForUser` (colección, query reusable o specification), pero sin alterar las decisiones funcionales ya cerradas.

### Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**. El plan implementa la matriz de visibilidad de §7.3, los tres endpoints base que la TR asume como propios y la reutilización futura en `SPEC-101-*`, sin abrir ABM comercial ni reglas no documentadas en producto.

---

## 3.4) Verificación D (2026-05-31)

### Implementación realizada

- **Backend:** se implementó el helper reusable `visibleClientsForUser` mediante `VisibleClientsResolver`, el guard `VisibilityPermissionGuard` para `Permiso_Repo`, el servicio `VisibilityDataService`, el modelo `PqPartesRegistroTarea` y el controller `VisibilityDataController`.
- **Endpoints:** quedaron expuestos `GET /api/v1/clientes`, `GET /api/v1/comprobantes/{id}` y `GET /api/v1/dashboard/resumen` bajo `auth:sanctum` + `paq.tenant`, con `403` para falta de permiso base y `404` para recurso fuera del universo visible.
- **Datos/seed:** se agregó configuración `paqsuite_visibility.php` y se amplió `SecurityMvpSeeder` para sembrar atributos `Permiso_Repo` de visibilidad por rol en el entorno MVP.
- **Documentación:** se actualizaron esquemas OpenAPI, `OpenApiDocumentationTest` y la matriz `matriz-permisos-mvp.md` para explicitar el uso de `visibleClientsForUser`.

### Verificación del agente - TR-GEN-02-visibilidad-datos-SistemaPartes

#### Resultado
- **Aprobado con observaciones**

#### Evidencia revisada
- Código backend nuevo/modificado: controller, services de visibilidad, modelo `PqPartesRegistroTarea`, config de procedimientos y seed de atributos.
- Documentación actualizada: `OpenApiSchemas.php`, `OpenApiDocumentationTest.php`, `matriz-permisos-mvp.md`.
- Tests agregados: `backend/tests/Feature/VisibilityDataTest.php`.

#### Hallazgos críticos
- No hay hallazgos críticos de implementación estática sobre el slice.

#### Advertencias
- La validación automatizada del backend quedó bloqueada por timeout de SQL Server (`SQLSTATE[08001]`), igual que en otros slices que dependen de tablas legacy.
- No se agregó frontend consumidor del slice porque esta TR define la base backend y el consumo efectivo queda para `SPEC-101-*`, tal como estaba acotado en D1.

#### Sugerencias
- Cuando haya conectividad estable con SQL Server, ejecutar la suite `VisibilityDataTest` y regenerar/validar OpenAPI para confirmar el contrato en runtime.
- Al implementar los slices `SPEC-101-*`, reutilizar `VisibleClientsResolver` y no reintroducir filtros locales en repositorios/controladores.

#### Tests
- Comandos:
  - `php -l backend/app/Http/Controllers/VisibilityDataController.php`
  - `php -l backend/app/Services/Visibility/VisibilityPermissionGuard.php`
  - `php -l backend/app/Services/Visibility/VisibleClientsResolver.php`
  - `php -l backend/app/Services/Visibility/VisibilityDataService.php`
  - `php -l backend/app/Models/PqPartesRegistroTarea.php`
  - `php -l backend/tests/Feature/VisibilityDataTest.php`
  - `php -l backend/tests/Feature/OpenApiDocumentationTest.php`
  - `php artisan test --filter=VisibilityDataTest`
  - `php artisan test --filter=OpenApiDocumentationTest`
- Resultado:
  - `php -l`: **OK**
  - `php artisan test --filter=VisibilityDataTest`: **bloqueado por SQL Server timeout**
  - `php artisan test --filter=OpenApiDocumentationTest`: **bloqueado por SQL Server timeout**

#### Pendientes
- Ejecutar tests feature/OpenAPI con SQL Server accesible.
- Integrar consumidores frontend reales en `SPEC-101-*` sobre esta base.

#### Recomendación final
- Mantener esta TR como **Pendiente** con observación explícita de validación backend pendiente por infraestructura.

---

## 4) Impacto en Datos

### Tablas afectadas
- `PQ_PARTES_CLIENTES`
- `PQ_PARTES_USUARIOS`
- `users`
- `PQ_PARTES_REGISTRO_TAREA`

### Seed minimo para tests
- Clientes semilla de al menos dos vendedores.
- Un usuario cliente, un vendedor y un supervisor.
- Un usuario sin vínculo comercial válido para confirmar el error controlado en login.
- Casos de datos cruzados para verificar no filtracion.

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice (referenciales de visibilidad)

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| GET | `/api/v1/clientes` | Bearer Sanctum + `X-Paq-Cliente` | `Permiso_Repo` + visibilidad perfil | No |
| GET | `/api/v1/comprobantes/{id}` | Bearer Sanctum + `X-Paq-Cliente` | `Permiso_Repo` + visibilidad perfil | No |
| GET | `/api/v1/dashboard/resumen` | Bearer Sanctum + `X-Paq-Cliente` | `Permiso_Repo` + visibilidad perfil | No |

### 5.2 Detalle por operacion

#### GET `/api/v1/clientes`
**Autorizacion:** permiso de consulta + filtro `visibleClientsForUser`.
**Response 200:** envelope con clientes dentro del universo visible.
**Response 401:** no autenticado.
**Response 403:** autenticado sin permiso base de consulta.
**Response 404:** no aplica para listados.

#### GET `/api/v1/comprobantes/{id}`
**Autorizacion:** permiso de consulta + validacion de pertenencia al universo visible.
**Response 200:** envelope con comprobante permitido.
**Response 401:** no autenticado.
**Response 403:** sin permiso base de consulta.
**Response 404:** recurso inexistente o fuera del universo visible del usuario.

#### GET `/api/v1/dashboard/resumen`
**Autorizacion:** permiso de consulta + filtros por perfil.
**Response 200:** envelope con agregados del universo visible.
**Response 401:** no autenticado.
**Response 403:** sin permiso para dashboard.

### 5.3 Actualizacion matriz permisos

- [x] Agregar regla "aplica visibleClientsForUser" para endpoints de consulta.
- [ ] Referenciar que los slices funcionales `SPEC-101-*` reutilizan esta base sin redefinir la regla de visibilidad.
- [ ] Validar 401/403 y descripciones en OpenAPI generado.

---

## 6) Cambios Frontend

### Pantallas / componentes
- Selector de cliente condicionado por universo visible.
- Mensajes de "sin datos visibles" para perfil sin cartera.
- Componentes de dashboard/consultas consumen solo resultados filtrados backend.
- `SPEC-101-*` puede extender estas superficies, pero no redefinir la regla backend del universo visible.

### data-testid sugeridos
- `clientes-selector`
- `clientes-visible-count`
- `resource-not-found-message`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T1 | Backend | Implementar helper `visibleClientsForUser(user)` | Devuelve universo correcto por perfil |
| T2 | Backend | Integrar helper en repos/services de consultas | Ninguna consulta saltea filtro |
| T3 | Backend | Enforzar 403/404 para acceso por ID fuera de alcance | Sin fuga de datos |
| T4 | Frontend | Adaptar selector y estados vacios | UX coherente por perfil |
| T5 | Tests | Integration por perfil + E2E cartera | 3 perfiles y resultados distintos |
| T6 | Coordinacion | Definir puntos de integracion futura con `SPEC-101` | Checklist de adopcion por slice |

---

## 8) Estrategia de Tests

- **Unit:** comportamiento de `visibleClientsForUser` para cliente/vendedor/supervisor.
- **Integration:** consultas 200/401/403/404 aplicando filtros de perfil.
- **E2E:** vendedor A no visualiza clientes/comprobantes de vendedor B.

---

## 9) Riesgos y Edge Cases

- Usuario mal configurado sin vinculo perfil-cliente/vendedor.
- Endpoints legacy de consulta no migrados al helper central.
- Diferencias entre filtros de listado y filtros por ID puntual.

---

## 10) Checklist final

### Checklist del slice
- [x] AC cubiertos por implementación backend (pendiente validación runtime completa con SQL Server accesible)
- [x] `visibleClientsForUser` implementado y reutilizable
- [ ] Estrategia de adopcion definida para `SPEC-101`

### Checklist normas transversales
- [x] Endpoints nuevos/modificados con policy en codigo
- [x] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en `/api/documentation` coherente con codigo y matriz
- [x] 401/403 documentados por operacion protegida
- [x] Envelope JSON respetado
- [x] `X-Paq-Cliente` documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [x] Sin ampliacion de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

### Backend
- Helper central `visibleClientsForUser`.
- Repos/services de consultas filtradas por perfil.
- Endpoints base `clientes`, `comprobantes/{id}` y `dashboard/resumen`.

### Frontend
- Consumo real pendiente para slices `SPEC-101-*`.

### OpenAPI
- Operaciones de consultas de clientes/comprobantes/dashboard con 401/403 y reglas de visibilidad.

### Docs
- `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md`
- Referencias cruzadas en TR `SPEC-101-*` al helper de visibilidad.
