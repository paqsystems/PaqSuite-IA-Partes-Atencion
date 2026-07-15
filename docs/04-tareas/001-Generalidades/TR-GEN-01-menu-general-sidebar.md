# TR-GEN-01-menu-general-sidebar — Menú general y sidebar dinámico

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-01-menu-general-sidebar](../../03-historias-usuario/001-Generalidades/HU-GEN-01-menu-general-sidebar.md) |
| **SPEC relacionada** | [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-01-shell-layout; TR-GEN-02-login-sesion; [TR-GEN-02-autorizacion-menu-api](TR-GEN-02-autorizacion-menu-api.md); [TR-GEN-02-modelo-roles-permisos-seed](TR-GEN-02-modelo-roles-permisos-seed.md) (usuarios/roles de prueba) |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-30 (D implementado) |

**Origen:** [HU-GEN-01-menu-general-sidebar](../../03-historias-usuario/001-Generalidades/HU-GEN-01-menu-general-sidebar.md)  
**Referencia SPEC:** [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Menú general y sidebar dinámico por permisos.

### Narrativa
Como usuario autenticado quiero ver en el sidebar solo los procesos autorizados para acceder a los 11 procesos del menú MVP sin hardcodear visibilidad en frontend.

### In scope / Out of scope
- **In scope:** consumo API de menú, render jerárquico, estado activo, fallback por error, seed de 11 ítems MVP, **tres controles de header** (sidebar, expandir/contraer árbol, vista operativa).
- **Out of scope:** ABM de menús, reglas de autorización en backend fuera del contrato, pantalla login con menú lateral.

**Contexto reingeniería (MONO transversal):** [`menu-general.md`](../../00-contexto/_mono/01-experiencia-base/menu-general.md) § Tres controles, [`shell-layout.md`](../../00-contexto/_mono/01-experiencia-base/shell-layout.md).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Existe seed idempotente con los 11 ítems del menú MVP del SPEC.
- **AC-02**: Sidebar muestra únicamente opciones autorizadas por usuario.
- **AC-03**: Ítem activo queda resaltado y padres expandidos.
- **AC-04**: Si falla API de menú, el shell permanece usable con fallback.
- **AC-05**: Usuario sin ítems autorizados ve estado vacío informativo sin error.
- **AC-06**: E2E valida visibilidad diferencial por permisos.
- **AC-07**: Hamburguesa muestra/oculta el panel sidebar; el shell sigue usable.
- **AC-08**: Control expandir/contraer aplica a todas las ramas del TreeView (estado persistido).
- **AC-09**: Vista `operationalOnly` lista solo nodos con `routePath` (operativos); agrupadores sin ruta no aparecen como filas.
- **AC-10**: Tras logout/login con **otro** usuario, controles de menú cargan estado de ese usuario (no heredan el anterior).
- **AC-11**: No hay persistencia de controles por empresa ni global del sistema.
- **AC-12**: Textos visibles del menú respetan idioma activo (coord. TR-GEN-01-idioma).

### Escenarios Gherkin

```gherkin
Feature: Menú general sidebar

  Scenario: Sidebar muestra ítems autorizados post-login
    Given un usuario autenticado con rol acotado (vendedor granular)
    When el shell carga el menú desde la API
    Then ve solo los ítems autorizados para su perfil
    And no ve ítems sin permiso

  Scenario: Rol con acceso total ve menú MVP completo
    Given un usuario autenticado con AccesoTotal en su rol
    When el shell carga el menú desde la API
    Then ve los 11 ítems habilitados del menú MVP

  Scenario: Navegación resalta ítem activo
    Given un usuario con menú cargado
    When selecciona "Pedidos ingresados"
    Then el ítem queda resaltado
    And los agrupadores padres quedan expandidos

  Scenario: Fallo de API no rompe el layout
    Given un usuario autenticado
    When la API de menú falla o devuelve vacío
    Then el shell permanece usable
    And se muestra menú mínimo o mensaje informativo

  Scenario: Ocultar panel sidebar con hamburguesa
    Given un usuario autenticado en el shell
    When pulsa el control de hamburguesa
    Then el panel del sidebar queda oculto
    And el área principal se redimensiona

  Scenario: Expandir y contraer todas las ramas
    Given un menú jerárquico cargado y sidebar visible
    When alterna el control expandir/contraer todo
    Then todas las ramas quedan en el mismo estado expandido o contraído

  Scenario: Vista solo opciones operativas
    Given un menú con agrupadores y procesos hijos
    When activa vista operationalOnly
    Then no aparecen agrupadores sin routePath
    And aparecen los procesos hijos autorizados

  Scenario: Login no muestra menú de procesos
    Given un usuario en pantalla de login
    Then no ve sidebar con ítems de procesos del menú MVP
```

---

## 3) Reglas de Negocio

1. **RN-01**: El frontend renderiza visibilidad según payload de API, sin reglas hardcodeadas por rol.
2. **RN-02**: El catálogo mínimo de menú MVP contiene 11 ítems alineados al SPEC.
3. **RN-03**: Orden de visualización usa `order` del payload.
4. **RN-04**: En MONO no existe variación de menú por cambio de empresa.
5. **RN-05**: Preferencia "abrir en nueva pestaña" (slice avatar) afecta navegación del sidebar.
6. **RN-06**: Los tres controles de menú son **presentación**; no filtran permisos ni sustituyen la API.
7. **RN-07**: `menuDisplayMode = operationalOnly` aplana la vista a nodos con `routePath`; orden por `order` del payload.
8. **RN-08**: Persistencia **por usuario** (`users` / preferences API, keyed por `userId`) **o por terminal** (`localStorage` con `{appId}.{userId}.menu.*`). **Prohibido:** por empresa, tenant, `PQ_PARAMETROS_GRAL` o default global para todos los usuarios.
9. **RN-09**: Logout o cambio de usuario en el mismo navegador debe cargar el estado del **nuevo** usuario, sin heredar el anterior.

---

## 3.1) Controles del header (implementación)

| Control | Estado | Persistencia | Componente sugerido |
|---------|--------|--------------|------------------------|
| Hamburguesa | `sidebarVisible: boolean` | `localStorage` `{appId}.{userId}.menu.sidebarVisible` **o** preferencia usuario en API | `MenuToolbarControls` → callback layout |
| Expandir/contraer todo | `menuTreeExpanded: boolean` | Idem con `.treeExpanded` | DevExtreme TreeView `expandAll` / `collapseAll` |
| Vista menú | `menuDisplayMode: 'allBranches' \| 'operationalOnly'` | Idem con `.displayMode` | Transformación del árbol antes de render |

**Alcance de persistencia:** nunca incluir `empresaId`, `tenantId` ni claves globales sin `userId`. Ver `menu-general.md` § Alcance de persistencia.

**Inferencia proceso vs agrupador (sin columnas nuevas):** usar solo campos legacy de `pq_menus`. Ver algoritmo **D1-1** (§3.4).

---

## 3.2) Informe C1 — Revisión de ambigüedad (2026-05-29)

**Fuentes revisadas:** HU-GEN-01-menu-general-sidebar, SPEC-001-01, `menu-general.md`, TR-GEN-01-shell-layout (§3.2 D1), TR-GEN-02-autorizacion-menu-api (§3.2–3.3), TR-GEN-02-modelo-roles-permisos-seed, `paqsuite_mvp.php`, código frontend (`menuApi`, `useUserMenu`, `SidebarMenu`, `ShellLayout`), regla local tablas SQL compartidas.

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (tras aplicar resoluciones §3.3; **no requiere replan de alcance**)

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|-------------------------------|
| AMB-C01 | **Supervisor “11 ítems” vs `AccesoTotal`** | Tests/integration exigen `count raíz = 11` (§4 L165) pero TR-GEN-02 C1-5 acepta **todo** `pq_menus.enabled` legacy (~150+ nodos) | Tests verifican **presencia de los 11 `procedimiento` MVP** (árbol aplanado), no `count(raíz) = 11`. E2E supervisor: ítems MVP + legacy OK. |
| AMB-C02 | **Estado hamburguesa duplicado** | `ShellLayout` ya colapsa sidebar; TR introduce `useMenuPresentation.sidebarVisible` | **Unificar** en `useMenuPresentation`; `ShellLayout` consume hook (no dos fuentes de verdad). |
| AMB-C03 | **Esquema seed §4 vs BD legacy** | TR menciona columnas `menuKey`, `isEnabled` en `pq_menus`; tabla compartida usa `procedimiento`, `enabled`, `routeName`, `idparent` | Seed/API **solo legacy**; `menuKey`/`labelKey`/`routePath` son **DTO API** (ya en `AuthorizedMenuBuilder`). **Sin DDL** (regla `.cursor/rules/local/01-tablas-seguridad-compartidas-sql.md`). |
| AMB-C04 | **Prueba `operationalOnly` sin agrupadores MVP** | Seed MVP crea ítems planos; vendedor acotado devuelve 4 hojas con `routePath` → modos iguales | E2E `operationalOnly` con **payload mock** (agrupador + hijos) **o** supervisor + árbol legacy anidado; no insertar grupos artificiales solo para test si no aporta producto. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | Backend T1/T2 en plan §7 | **Ya cumplidos** en TR-GEN-02-autorizacion-menu-api + seed (`paqsuite:seed-menus-mvp`, `UserMenuTest`). Esta TR = **frontend + presentación + tests UI**. |
| AMB-M02 | `MenuToolbarControls` vs botones en `ShellHeader` | Extraer componente; `ShellHeader` lo monta (shell-layout ya expone testids). |
| AMB-M03 | Rutas/archivos §6 desactualizados | Extender `menuApi.ts`, `useUserMenu.ts`, `protectedRoutes.tsx`; **no** crear `getUserMenu.ts` / `routes.tsx` duplicados. |
| AMB-M04 | `data-testid` §6 vs código | Estandarizar en **`menuSidebarList`**, `menuSidebarItem-{menuKey}`, `menuSidebarEmptyState`, `menuSidebarErrorState`; migrar desde `sidebar-menu*`. |
| AMB-M05 | Response 403 menú (§5.2) | Alinear TR-GEN-02: **403** solo sin `Pq_Permiso`; **200 `[]`** sin atributos (`vendedor.sinMenu.mvp`). |
| AMB-M06 | Ejemplo JSON §5.2 incompleto | Incluir en D1 shape completo: `id`, `text`, `procedimiento`, `children` (D1-6 autorizacion-api). |
| AMB-M07 | `SistemaPartesSecuritySeeder` (§4 L170) | Nombre real: `php artisan paqsuite:seed-seguridad-mvp`. |
| AMB-M08 | DevExtreme no instalado | Añadir `devextreme` + `devextreme-react` en D1 (README ya lo anticipa). |
| AMB-M09 | AC-12 i18n | MVP: mostrar `text` API; `labelKey` preparado para TR-GEN-01-idioma (sin bloquear slice). |
| AMB-M10 | RN-05 nueva pestaña | Fuera de alcance → TR-GEN-01-menu-avatar. |
| AMB-M11 | Persistencia API vs localStorage | MVP: **`localStorage` `{appId}.{userId}.menu.*`**; API preferences cuando exista TR menu-avatar (misma clave lógica). |
| AMB-M12 | Mapeo §8 → seed (HU pregunta abierta) | Tabla canónica: `config/paqsuite_mvp.php` → `menuItems` + `vendedorAcotadoProcedimientos`. |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| HU Gherkin supervisor “11 ítems habilitados MVP” vs API AccesoTotal legacy | HU/TR tests: **subconjunto MVP verificado**; supervisor puede ver más (SPEC no prohíbe legacy enabled). |
| TR §4 seed “11 nodos raíz” vs jerarquía legacy | Seed asegura **11 procedimientos MVP existentes y enabled**; jerarquía (`idparent`) **no se mueve** si fila ya existe. |
| SPEC controles menú vs shell ya implementado | Controles 1 operativo en shell; 2–3 **stub** — esta TR los activa con TreeView. |

### Supuestos detectados

- Rutas SPA MVP definidas en `paqsuite_mvp.menuItems[].routeName`; router debe registrar placeholders para cada `routePath` autorizado.
- `appId` persistencia = `SistemaPartes` (coherente con keys auth en localStorage).
- Matriz permisos ya incluye `GET /api/v1/user/menu` (TR-GEN-02).

### Preguntas para decisión humana

- ~~AMB-C04: mock vs agrupador en BD~~ → **Cerrado (2026-05-29):** **mock E2E + árbol legacy** supervisor (§3.3 R-C1-08).
- ~~Clasificación proceso/agrupador~~ → **Cerrado (2026-05-29):** atributos legacy `tipo_proceso` + `routeName` (§3.4 D1-1); **sin columnas nuevas**.

### Veredicto C1

**Apto con observaciones para D1.** Sin replan de alcance; resoluciones §3.3 cierran ambigüedades antes de codificar.

---

## 3.3) Resoluciones C1 — pre-D1 (2026-05-29)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Tests supervisor | Assert **11 procedimientos MVP** en árbol aplanado; no `count(raíz)=11`. |
| R-C1-02 | Backend menú/seed | **No reimplementar**; consumir TR-GEN-02 entregables. |
| R-C1-03 | Seed `pq_menus` | Solo upsert legacy; **prohibido DDL** en tablas compartidas. |
| R-C1-04 | `sidebarVisible` | Un solo hook `useMenuPresentation`; refactor `ShellLayout`. |
| R-C1-05 | TreeView | DevExtreme TreeView en sidebar; toggles 2–3 conectados. |
| R-C1-06 | testids | Adoptar §6 (`menuSidebarList`, etc.); deprecar `sidebar-menu*`. |
| R-C1-07 | Persistencia MVP | `localStorage` keyed `{appId}.{userId}.menu.*`; logout limpia contexto usuario. |
| R-C1-08 | E2E `operationalOnly` | **Confirmado humano:** mock E2E con agrupador **+** árbol legacy supervisor; **sin** filas agrupadoras artificiales en seed MVP. |
| R-C1-09 | i18n AC-12 | `text` visible en MVP; `labelKey` sin traducir hasta TR idioma. |
| R-C1-10 | Mapeo seed | Fuente: `backend/config/paqsuite_mvp.php` (`menuItems`, `vendedorAcotadoProcedimientos`). |
| R-C1-11 | 403 menú | Igual TR-GEN-02 D1-4/D1-5. |
| R-C1-12 | OpenAPI | Oleada F (sin cambio en D). |
| R-C1-13 | Proceso vs agrupador | **`tipo_proceso` + `routeName`** legacy (§3.4 D1-1); **prohibido** columna/`nodeType` en tabla. |

---

## 3.4) Plan D1 — Implementación (2026-05-29)

### Alcance entendido

Evolucionar sidebar MVP: TreeView DevExtreme, tres controles de presentación con persistencia por usuario, navegación por `routePath`, estados vacío/error, tests E2E de permisos y `operationalOnly`. **Backend menú/seed ya entregados** (TR-GEN-02); micro-ajuste de `nodeType` en mapper API.

### Fuentes leídas

- TR §3.2 C1, resoluciones §3.3, HU/SPEC-001-01, `menu-general.md`
- Código: `AuthorizedMenuBuilder`, `menuApi.ts`, `useUserMenu.ts`, `SidebarMenu.tsx`, `ShellLayout`/`ShellHeader`
- `config/paqsuite_mvp.php`, `UserMenuTest.php`

### Decisiones D1

| # | Tema | Decisión |
|---|------|----------|
| D1-1 | **Proceso vs agrupador** (`pq_menus`) | Sin columnas nuevas. Algoritmo canónico (backend → `nodeType` en DTO): (1) si `trim(routeName) !== ''` → **`process`**; (2) si no, si `upper(trim(tipo_proceso)) === 'P'` → **`process`**; (3) else → **`group`**. Exponer también `tipoProceso` en JSON (valor legacy). Frontend **no re-infiere** si API trae `nodeType`. |
| D1-2 | `operationalOnly` | Lista plana de nodos con `nodeType === 'process'` (equivalente a `routePath` presente tras D1-1), ordenados por `order`; sin filas agrupador. |
| D1-3 | Persistencia controles | `localStorage` claves `SistemaPartes.{userId}.menu.sidebarVisible` \| `.treeExpanded` \| `.displayMode`; defaults: `true`, `true`, `allBranches`. |
| D1-4 | Unificación sidebar visible | `useMenuPresentation` es **única fuente**; eliminar estado duplicado en `ShellLayout`. |
| D1-5 | DevExtreme | Instalar `devextreme` + `devextreme-react`; TreeView en sidebar; tema base light (TR apariencia después). |
| D1-6 | testids | Migrar a `menuSidebarList`, `menuSidebarItem-{menuKey}`, `menuSidebarEmptyState`, `menuSidebarErrorState`. |
| D1-7 | Rutas MVP | Registrar en `protectedRoutes.tsx` placeholders para los 11 `routePath` de `paqsuite_mvp.menuItems` (reuse `ProcessPlaceholderPage` donde aplique). |
| D1-8 | E2E `operationalOnly` | **Mock** payload con agrupador + hijos **y** caso manual/E2E supervisor con SQL Server (árbol legacy anidado). |
| D1-9 | i18n AC-12 | Mostrar `text` del API; `labelKey` reservado para TR-GEN-01-idioma. |
| D1-10 | Fallback error menú | Shell usable; `menuSidebarErrorState` + mensaje; no menú hardcodeado por rol. |

#### Algoritmo D1-1 (referencia implementación)

```text
resolveNodeType(pqMenu):
  if trim(pqMenu.routeName) != ""     → process
  if upper(trim(pqMenu.tipo_proceso)) == "P" → process
  else                                → group
```

**Notas:**
- Seed MVP ya usa `tipo_proceso = 'P'` y `routeName` en ítems operativos.
- Agrupadores legacy suelen tener `tipo_proceso != 'P'` y `routeName` vacío.
- `nodeType` en API es **derivado**, no se persiste en BD.

### Impacto esperado

| Capa | Cambios |
|------|---------|
| DB | **Ninguno** (sin DDL; regla tablas compartidas) |
| Backend | **Ajuste menor** `AuthorizedMenuBuilder::mapMenuNode` (D1-1) + campo `tipoProceso` en JSON; test unit PHP `resolveMenuNodeType` |
| Frontend | TreeView, hooks presentación, flatten operational, router, testids, DevExtreme |
| Tests | Unit TS (`flattenOperationalMenu`, `resolveNodeType` mirror); E2E permisos + toggles + operationalOnly mock; reuse `UserMenuTest` |
| Docs | TR §3.4; regla local sin cambio |

### Orden de trabajo (D)

| Paso | Tarea | Archivos / comandos |
|------|-------|---------------------|
| P0 | Ajuste mapper `nodeType` + `tipoProceso` | `AuthorizedMenuBuilder.php`, test PHP |
| P1 | Instalar DevExtreme | `npm install devextreme devextreme-react` |
| P2 | Utilidades menú | `resolveMenuNodeType.ts` (mirror test), `flattenOperationalMenu.ts` |
| P3 | `useMenuPresentation` + storage | `useMenuPresentation.ts`, `menuPresentationStorage.ts` |
| P4 | `MenuToolbarControls` | extraer de `ShellHeader`; conectar toggles 2–3 |
| P5 | Refactor `ShellLayout` | consumir `useMenuPresentation` |
| P6 | `MenuSidebarTree` + `ShellSidebar` | TreeView, activo, expand parents |
| P7 | Extender `useUserMenu` | error/empty states, testids |
| P8 | Rutas MVP | `protectedRoutes.tsx` |
| P9 | Unit tests frontend | flatten, nodeType, presentation storage |
| P10 | E2E | permisos supervisor/acotado/sinMenu; toggles; operationalOnly **mock**; cambio usuario AC-10 |
| P11 | Smoke manual | login vendedor/supervisor → menú real SQL Server |
| P12 | Cierre TR | §3.5 verificación D, checklist |

### Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Legacy con `tipo_proceso` inconsistente | Priorizar `routeName` no vacío (regla 1 D1-1) |
| DevExtreme bundle size | Import modular; solo TreeView en MVP |
| Rutas menu ≠ router | Mapa único desde `paqsuite_mvp.php` |
| Estado sidebar duplicado | P5 unifica en hook |
| operationalOnly sin agrupadores en acotado | P10 mock + supervisor legacy |

### Tests a ejecutar (post-D)

- `php artisan test --filter=UserMenuTest` (+ nuevo test nodeType si P0)
- `npm run test` (unit frontend)
- `npm run test:e2e` (casos menú nuevos + smoke shell existente)
- Smoke manual API + UI (supervisor árbol legacy, vendedor acotado 4 ítems)

### Dudas / bloqueos

- **Ninguno** tras decisiones humanas 2026-05-29.

### Confirmación de alcance

Sin ABM menú, sin DDL, sin reimplementar endpoint/seed, sin i18n completo (AC-12 parcial), sin nueva pestaña (RN-05 → avatar TR).

---

## 3.5) Verificación D (2026-05-30)

| Verificación | Resultado |
|--------------|-----------|
| Backend P0: `MenuNodeTypeResolver` + `tipoProceso` en API | OK — 3 unit tests |
| DevExtreme TreeView + toggles header | OK |
| `useMenuPresentation` + persistencia `SistemaPartes.{userId}.menu.*` | OK |
| Rutas MVP (11 `routePath`) | OK |
| Unit frontend (flatten, nodeType, storage) | 13 passed |
| E2E menú (permisos, operationalOnly mock, cambio usuario) | 7 casos nuevos + smoke |
| Backend suite completa | 33 passed |

---

## 4) Impacto en Datos

### Tablas afectadas
- `pq_menus` (seed idempotente de **filas** MVP; tabla compartida — ver regla local `01-tablas-seguridad-compartidas-sql.md`: **sin DDL**).
- `PQ_RolAtributo` / `Pq_Permiso` / `Pq_Rol`: **no modificar en esta TR** (TR-GEN-02-modelo-roles-permisos-seed).

### Seed mínimo para tests

**Catálogo de menú (`pq_menus`):**

- Comando existente: `php artisan paqsuite:seed-menus-mvp` — upsert idempotente de **11 procedimientos** MVP desde `config/paqsuite_mvp.php`.
- Columnas legacy: `procedimiento`, `text`, `orden`, `enabled`, `routeName`, `idparent`, `tipo`, `tipo_proceso`.
- `menuKey`, `labelKey`, `routePath`, `nodeType` en API = **mapeo DTO** (`AuthorizedMenuBuilder`), no columnas de tabla.
- Si fila MVP ya existe: solo `enabled=true`; **no reubicar** `idparent` (respeta árbol legacy compartido).
- Para tests **vista operativa**: **mock E2E** con agrupador + hijos **y** supervisor + árbol legacy (§3.4 D1-8); sin agrupadores artificiales en seed MVP.

**Usuarios de prueba para visibilidad del sidebar** (creados en [TR-GEN-02-modelo-roles-permisos-seed](TR-GEN-02-modelo-roles-permisos-seed.md); consumidos aquí):

| Usuario sugerido | Rol / permiso | Objetivo del test |
|------------------|---------------|-------------------|
| `supervisor.mvp` | Rol **Supervisor** con **`AccesoTotal = true` en `Pq_Rol`** | Debe recibir **todos** los ítems `pq_menus` habilitados (11 procesos MVP) vía `GET /api/v1/user/menu` |
| `vendedor.acotado.mvp` | Rol **Vendedor**: `AccesoTotal = false` en `Pq_Rol` + `PQ_RolAtributo` subconjunto §4.5 TR seed | Debe ver **solo** esos ítems en sidebar; **no** debe ver ítems sin atributo (ej. stock, deuda, logs) |
| `vendedor.sinMenu.mvp` | Usuario con `Pq_Permiso` válido para login pero **sin** atributos de menú | Menú vacío controlado + mensaje informativo (AC-05) |

**Subconjunto sugerido para rol acotado (documentar en seed/matriz):**

| Ítem menú MVP (SPEC §8) | Incluido en rol acotado |
|-------------------------|-------------------------|
| Carga pedidos/presupuestos | Sí |
| Presupuestos ingresados | Sí |
| Pedidos ingresados | Sí |
| Pedidos pendientes (pantalla) | No |
| Deuda / cheques / historial / stock / tratativas / logs | No |
| Dashboard | **Sí** — KPIs en ARS del mes en curso: presupuestos abiertos, pedidos ingresados, pedidos pendientes (totales), cliente top presupuestos abiertos, cliente top pedidos ingresados (detalle en [TR-GEN-02-modelo-roles-permisos-seed](TR-GEN-02-modelo-roles-permisos-seed.md) §4.5) |

**Criterios de validación en tests:**

- [ ] Integration (existente `UserMenuTest`): supervisor incluye **11 procedimientos MVP** (aplanado); vendedor acotado = allow-list `vendedorAcotadoProcedimientos`.
- [ ] Integration: token **rol acotado** → subconjunto estricto; ningún `menuKey` fuera del allow-list.
- [ ] E2E: login supervisor → sidebar contiene `pedidosIngresados` **y** ítem excluido del acotado (ej. `stock`).
- [ ] E2E: login vendedor acotado → ve `pedidosIngresados` **y no** ve `stock`.

**Coordinación:** permisos en `php artisan paqsuite:seed-seguridad-mvp` (TR-GEN-02); no duplicar lógica aquí.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1. Código, matriz y OpenAPI deben coincidir.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/user/menu` | Bearer + `X-Paq-Cliente` | Usuario autenticado + permisos aplicados por backend | No |
| GET | `/api/v1/users/me/preferences` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### GET `/api/v1/user/menu`

**Autorización:** sesión válida; backend filtra ítems según permisos del usuario.

**Request:** sin body.

**Response 200:** envelope con arreglo jerárquico de menús:

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": [
    {
      "id": 30,
      "menuKey": "pedidosIngresados",
      "labelKey": "menu.pedidosIngresados",
      "text": "Pedidos Ingresados",
      "routePath": "/pedidos/ingresados",
      "procedimiento": "pw_pedidosingresados",
      "tipoProceso": "P",
      "order": 30,
      "nodeType": "process",
      "children": []
    }
  ]
}
```

`nodeType` derivado por **D1-1** (`tipo_proceso` + `routeName`); no es columna de tabla.

**Response 401:** no autenticado.

**Response 403:** autenticado **sin** `Pq_Permiso` vigente (`auth.noPermission`). Usuario con permiso pero **sin atributos de menú** → **200** `resultado: []` (TR-GEN-02 D1-5).

**OpenAPI (L5-Swagger):**

- [ ] Anotaciones en controller/DTO del endpoint de menú.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401 y 403 documentadas.
- [ ] Envelope `error/respuesta/resultado` validado.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [x] Fila `GET /api/v1/user/menu` registrada (TR-GEN-02-autorizacion-menu-api).
- [ ] OpenAPI verificado en oleada F.

---

## 6) Cambios Frontend (post-C1)

> **Baseline:** existen `menuApi.ts`, `useUserMenu.ts`, `SidebarMenu.tsx` (lista plana), toggles 1–3 en `ShellHeader` (2–3 disabled). Esta TR evoluciona a TreeView + presentación persistente.

### Pantallas / componentes
- `frontend/src/app/layout/MenuToolbarControls.tsx` (nuevo): extrae toggles desde `ShellHeader`.
- `frontend/src/app/layout/ShellHeader.tsx`: monta `MenuToolbarControls`.
- `frontend/src/app/layout/ShellLayout.tsx`: consume `useMenuPresentation` (unifica visibilidad sidebar).
- `frontend/src/app/layout/ShellSidebar.tsx`: DevExtreme TreeView, ítem activo, estados vacío/error.
- `frontend/src/features/menu/menuApi.ts`: tipos y fetch (existente — extender si hace falta).
- `frontend/src/features/menu/hooks/useUserMenu.ts`: carga, error, fallback (existente — extender).
- `frontend/src/features/menu/hooks/useMenuPresentation.ts` (nuevo): `sidebarVisible`, `menuTreeExpanded`, `menuDisplayMode` + persistencia `{appId}.{userId}.menu.*`.
- `frontend/src/features/menu/utils/flattenOperationalMenu.ts` (nuevo): transformación `operationalOnly`.
- `frontend/src/features/menu/components/MenuSidebarTree.tsx` (nuevo): TreeView DevExtreme.
- `frontend/src/app/router/protectedRoutes.tsx`: rutas alineadas a `routePath` del menú MVP.

### data-testid (canónicos post-C1)
- `menuSidebarList`
- `menuSidebarItem-{menuKey}`
- `menuSidebarEmptyState`
- `menuSidebarErrorState`
- `menuToggleSidebar`, `menuToggleExpandAll`, `menuToggleDisplayMode` (header — ya en shell)

### Dependencias npm (D1)
- `devextreme`, `devextreme-react`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T0 | Backend | Ajuste `nodeType`/`tipoProceso` en mapper (D1-1) | Pendiente D |
| T1 | Backend | Endpoint `GET /api/v1/user/menu` | **Cumplido** (TR-GEN-02-autorizacion-menu-api) |
| T2 | Backend | Seed `pq_menus` 11 MVP | **Cumplido** (`paqsuite:seed-menus-mvp`) |
| T2b | Backend | Seed seguridad acotado/acceso total | **Cumplido** (TR-GEN-02 seed) |
| T3 | Frontend | TreeView + `useUserMenu` + estados vacío/error | Pendiente D |
| T4 | Frontend | Resaltado ruta activa + padres expandidos | Pendiente D |
| T4b | Frontend | `MenuToolbarControls` + `useMenuPresentation` + persistencia userId | Pendiente D |
| T4c | Frontend | `operationalOnly` + expandir/contraer TreeView | Pendiente D |
| T5 | Tests | Integration (reuse) + E2E permisos y controles | Pendiente D |
| T6 | Docs | Matriz + OpenAPI oleada F | Matriz OK |

---

## 8) Estrategia de Tests

- **Unit:** `resolveNodeType` (D1-1), `flattenOperationalMenu`, orden por `order`, persistencia presentation.
- **Integration:** API `GET /api/v1/user/menu` con 200, 401 y 403.
- **E2E:**  
  - usuario **AccesoTotal** (supervisor): sidebar con ítems acotados + no acotados (ej. pedidos y stock);  
  - usuario **rol acotado** (vendedor): solo subconjunto documentado;  
  - usuario sin atributos de menú: estado vacío informativo;
  - hamburguesa oculta/muestra panel; expandir/contraer todo; `operationalOnly` sin filas de agrupador (**mock E2E** + supervisor legacy);
  - login usuario B tras configurar controles como usuario A: usuario B ve **su** estado, no el de A.

---

## 9) Riesgos y Edge Cases

- Desalineación entre `routePath` del backend y rutas del frontend puede romper navegación.
- Seed incompleto puede dejar el menú MVP fuera de especificación.
- Respuesta de menú con estructura inválida puede romper render sin validación defensiva.
- Reglas de permisos duplicadas en frontend generarían divergencia con backend.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Seed de 11 ítems del menú MVP cargado y versionado
- [ ] Pruebas con **AccesoTotal** y **rol acotado** documentadas y en verde
- [ ] Tres controles de menú (AC-07–AC-09) planificados y probados
- [ ] Dependencias con shell/login validadas

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR











