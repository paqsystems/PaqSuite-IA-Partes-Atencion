# TR-GEN-02-admin-rol-atributos — Atributos de rol (`PQ_RolAtributo`)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-admin-rol-atributos](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-rol-atributos.md) |
| **SPEC relacionada** | [SPEC-001-02-admin-mantenimiento-roles-permisos](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md) |
| **Épica** | 001-Generalidades / Acceso y seguridad (post-MVP) |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-02-admin-roles; TR-GEN-02-autorizacion-menu-api; TR-GEN-01-menu-general-sidebar |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-19 (revisión C1 formal `/tr-ambiguity-review`) |

**Origen:** [HU-GEN-02-admin-rol-atributos](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-rol-atributos.md)  
**Referencia SPEC:** [SPEC-001-02-admin-mantenimiento-roles-permisos](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md)  
**Contexto:** [`mantenimiento-roles-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/mantenimiento-roles-permisos.md) · Tango [TR-014](https://github.com/paqsystems/PaqSuite-IA-TANGO/blob/main/docs/04-tareas/001-Seguridad/TR-014-administracion-atributos-rol.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Configuración granular A/B/M/R por opción de menú para roles sin acceso total.

### Narrativa
Como administrador de seguridad, quiero marcar permisos granulares por procedimiento de menú para limitar qué procesos ve cada perfil y qué acciones ABM puede ejecutar.

### In scope / Out of scope
- **In scope:** pantalla `/admin/roles/:rolId/atributos`; matriz opciones `pq_menus` × flags A/B/M/R; persistencia `PQ_RolAtributo`; aviso read-only si `acceso_total`; coherencia con menú API.
- **Out of scope:** definición del árbol menú (seed HU-GEN-01); asignación `Pq_Permiso`; roles con acceso total editables granularmente.

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Acción Atributos desde grilla roles abre pantalla del rol seleccionado.
- **AC-02:** Rol con `acceso_total = true` muestra aviso; matriz no editable.
- **AC-03:** Rol acotado: lista opciones menú habilitadas y permite marcar A/B/M/R.
- **AC-04:** Guardar persiste sin duplicar `(id_rol, procedimiento)`.
- **AC-05:** Usuario de prueba con ese rol ve menú coherente tras guardar (re-login o refresh menú).
- **AC-06:** Usuario sin autorización → 403.
- **AC-07:** Validación backend alinea flags con payload UI.
- **AC-08:** E2E: abrir atributos de `vendedor.acotado.mvp` y verificar opciones seed.

### Escenarios Gherkin

(Heredados de HU-GEN-02-admin-rol-atributos.)

---

## 3) Reglas de Negocio

1. **RN-01:** Solo roles con `acceso_total = false` admiten edición de matriz.
2. **RN-02:** Opciones listadas: `pq_menus` con `enabled = true` y `procedimiento` no vacío (excluir grupos puros si `tipo_proceso` indica grupo sin procedimiento operativo — filtrar nodos hoja + procesos con `routeName`).
3. **RN-03:** Unicidad `(id_rol, procedimiento)` — upsert por combinación.
4. **RN-04:** Autorización: `Permiso_Modi` (guardar) y `Permiso_Repo` (leer) sobre `pw_adminroles` vía `AdminSecurityAccessService`.
5. **RN-05:** Al menos uno de A/B/M/R debe ser true para persistir fila; si todos false → eliminar fila existente (política **omitir fila vacía**).
6. **RN-06:** `permiso_repo = true` es prerequisito para visibilidad en menú (`AuthorizedMenuBuilder`).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-19)

**Skill:** `/tr-ambiguity-review`

**Fuentes revisadas:** HU-GEN-02-admin-rol-atributos, SPEC-001-02-admin, `mantenimiento-roles-permisos.md`, TR-GEN-02-admin-roles (T0), TR-GEN-02-autorizacion-menu-api, Tango TR-014; código `PqRolAtributo.php`, `AuthorizedMenuBuilder.php`, `paqsuite_mvp.php` (menuItems), migración `uq_pq_rolatributo_rol_procedimiento`.

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (depende T0 multi-rol de TR-roles para AC-05)

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución (→ D1) |
|----|------|--------|-------------------|
| AMB-C1-AT-01 | AC-05 menú coherente post-guardar | Sin T0 multi-rol, test de unión es inválido | Feature test AC-05 **después** de T0; escenario: rol acotado + `permiso_repo` en opción → re-login → ítem visible en `GET /user/menu`. |
| AMB-C1-AT-02 | Catálogo filas GET | ¿Solo atributos existentes o todas las opciones menú? | GET devuelve **todas** las opciones `pq_menus` elegibles (RN-02), merge con flags existentes (default false). |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M-C1-AT-01 | Vista árbol vs grilla | **DataGrid** plana A/B/M/R (CheckBox DX); orden `pq_menus.orden`. |
| AMB-M-C1-AT-02 | Grupos `grp_*` | Incluir solo filas con `procedimiento` operativo (excluir grupos sin procedimiento asignable). |
| AMB-M-C1-AT-03 | i18n atributos | Prefijo `admin.roles.atributos*` + columnas A/B/M/R en 5 locales. |
| AMB-M-C1-AT-04 | Procedimiento gate | Mismo `pw_adminroles` (GET repo / PUT modi). |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| HU «árbol o grilla» | **Grilla** plana en v1 (R-C1-AT-01 previa). |
| SPEC CA-07 rol AccesoTotal vs pantalla atributos | GET `readOnly: true`; PUT → 422. |
| RN-06 repo ↔ menú vs atributos A/B/M en UI | UI muestra los cuatro flags; menú solo exige `permiso_repo` (documentado en tooltip columna Repo). |

### Supuestos detectados

- `AdminSecurityAccessService` y flag epic ya existen (TR-roles T0).
- Seed atributos `vendedor.acotado.mvp` es baseline de tests.

### Preguntas para decisión humana

(Ninguna bloqueante.)

### Recomendaciones de ajuste de la TR

- [x] Explicitar merge catálogo completo en GET — §3.2 R-C1-AT-05.
- [x] AC-05: re-login explícito en E2E, no solo F5.

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-19)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-AT-01 | Shape GET | `{ readOnly, rol, items[] }` con camelCase en JSON API. |
| R-C1-AT-02 | Shape PUT | `{ items: [{ procedimiento, permisoAlta, permisoBaja, permisoModi, permisoRepo }] }` transaccional. |
| R-C1-AT-03 | Rol acceso total | GET `readOnly: true`; PUT → 422 `admin.roles.atributosAccesoTotalReadOnly`. |
| R-C1-AT-04 | Gate | `pw_adminroles` vía `AdminSecurityAccessService`. |
| R-C1-AT-05 | Catálogo GET | Unión `pq_menus` elegibles + flags persistidos; procedimientos sin fila → flags false. |
| R-C1-AT-06 | AC-05 test | Re-login usuario de prueba tras guardar atributos; assert menú API. |

---

## 3.3) Plan D1 — Implementación (2026-06-19)

**Estado:** Cerrado (slice atributos dentro del epic admin; depende de T0 roles).

| # | Entrega | Estado |
|---|---------|--------|
| T1 | Endpoints GET/PUT `/admin/roles/{id}/atributos` en `AdminRoleController` | ✅ |
| T2 | `RoleAttributesService` + sync transaccional | ✅ |
| T3 | `RoleAttributesPage` + i18n columnas A/B/M/R | ✅ |
| T4 | Tests en `AdminSecurityFeatureTest` | ✅ |

---

## 3.4) Verificación D (2026-06-19)

| Verificación | Resultado |
|--------------|-----------|
| GET atributos rol acceso total → `readOnly: true` | OK — `testRoleAttributesReadOnlyForAccesoTotal` |
| PUT atributos acceso total → 422 | OK — mismo test |
| GET/PUT atributos rol acotado | OK — `testRoleAttributesCanBeSyncedForNonAccesoTotalRole` |
| `RoleAttributesPage` + checkboxes A/B/M/R | OK — build frontend |
| i18n columnas atributos (5 locales) | OK |
| Gate `pw_adminroles` | OK — vía `AdminRoleController` |

### Trazabilidad AC

| AC | Evidencia | Estado D |
|----|-----------|----------|
| AC-01 | Navegación desde grilla roles (UI) | ✅ |
| AC-02 | GET readOnly + mensaje UI acceso total | ✅ |
| AC-03 | GET items elegibles + edición flags | ✅ |
| AC-04 | PUT sync transaccional | ✅ |
| AC-05 | Coherencia menú post-guardar | ⏭ manual (OBS-02 acta F) |
| AC-06 | 403 vendedor acotado (Feature roles) | ✅ |
| AC-07 | Validación procedimiento elegible en service | ✅ |
| AC-08 | E2E atributos seed | ⏭ pendiente ampliación E2E |

---

## 3.5) Verificación E (2026-06-19)

Ver [E-GEN-02-admin-tests.md](E-GEN-02-admin-tests.md). Casos atributos en `AdminSecurityFeatureTest`: **Apto**.

---

## 4) Impacto en Datos

### Tablas afectadas

| Tabla | Operación |
|-------|-----------|
| `PQ_RolAtributo` | Upsert / delete filas vacías |
| `Pq_Rol` | Lectura (`acceso_total`) |
| `pq_menus` | Lectura catálogo |

### Seed mínimo para tests

- Rol `VendedorAcotado` + atributos seed existentes en `paqsuite:seed-seguridad-mvp`.
- Rol `Supervisor` con `acceso_total = true` (read-only path).

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Método | Path | Permiso |
|--------|------|---------|
| GET | `/api/v1/admin/roles/{id}/atributos` | `Permiso_Repo` + `pw_adminroles` |
| PUT | `/api/v1/admin/roles/{id}/atributos` | `Permiso_Modi` + `pw_adminroles` |

### 5.2 Detalle por operación

#### GET `/api/v1/admin/roles/{id}/atributos`

**Response 200 (rol acotado):**

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "readOnly": false,
    "rol": { "id": 3, "nombreRol": "VendedorAcotado", "accesoTotal": false },
    "items": [
      {
        "procedimiento": "pw_cargapedidos",
        "menuText": "Carga de Pedidos",
        "menuKey": "cargaPedidosPresupuestos",
        "permisoAlta": true,
        "permisoBaja": false,
        "permisoModi": true,
        "permisoRepo": true
      }
    ]
  }
}
```

**Response 200 (rol acceso total):** `readOnly: true`, `items: []`.

**404:** rol inexistente.

#### PUT `/api/v1/admin/roles/{id}/atributos`

**Request:** array `items` como arriba (solo procedimientos válidos en `pq_menus`).

**422:** rol con `acceso_total`; procedimiento desconocido; payload vacío inválido.

**Response 200:** `{ "actualizados": 12, "eliminados": 2 }` dentro de `resultado`.

### 5.3 Actualización matriz permisos

- [ ] Mismas filas que TR-GEN-02-admin-roles (`pw_adminroles`).

---

## 6) Cambios Frontend

### Pantallas

```text
frontend/src/features/admin/security/roles/
  RoleAttributesPage.tsx       # ruta /admin/roles/:rolId/atributos
  roleAttributesApi.ts
```

- Toolbar: título rol + botón **Guardar** (`Button` DX) + **Volver** a grilla roles.
- `DataGridDx` con columnas fijas A/B/M/R como `CheckBox` editables (`readOnly` si `readOnly` API).
- Banner informativo si `readOnly`.

### data-testid

| Elemento | testid |
|----------|--------|
| Página | `roles.atributos.page` |
| Grilla | `roles.atributos.grid` |
| Guardar | `roles.atributos.save` |
| Aviso acceso total | `roles.atributos.readOnlyBanner` |

### i18n

Claves nuevas sugeridas: `admin.roles.atributosTitle`, `admin.roles.atributosSave`, `admin.roles.atributosAccesoTotalMessage`, `admin.roles.atributosColumnAlta`, etc. (5 locales).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | `AdminRoleAttributesController` + service upsert | Feature tests AC-03–AC-07 |
| T2 | Frontend | `RoleAttributesPage` | AC-01–AC-03 |
| T3 | Tests | Integration menú post-guardar | AC-05 |
| T4 | E2E | atributos rol acotado seed | AC-08 |

---

## 8) Estrategia de Tests

- **Unit:** upsert elimina fila cuando todos false; rechaza acceso total.
- **Integration:** GET/PUT 200; 403 vendedor acotado sin modi admin; coherencia `GET /api/v1/user/menu` tras asignar rol a usuario test.
- **E2E:** navegar desde grilla roles → tildar repo en opción → guardar.

---

## 9) Riesgos y Edge Cases

- Opciones menú deshabilitadas (`enabled = false`) no aparecen pero atributos legacy pueden quedar — limpieza opcional batch (out of scope v1).
- Grupos `grp_*` sin `routeName`: mostrar solo si tienen procedimiento usable para atributos (alinear filtro con Tango TR-014).

---

## 10) Checklist final

(Checklist transversal § plantilla TR — igual TR-GEN-02-admin-roles.)

---

## Archivos creados/modificados (D1 2026-06-19)

- `app/Services/Admin/RoleAttributesService.php`
- `app/Http/Controllers/Api/V1/Admin/AdminRoleController.php` (showAttributes, updateAttributes)
- `frontend/src/features/admin/security/roles/RoleAttributesPage.tsx`
- `frontend/src/features/admin/security/roles/rolesAdminApi.ts`
- `tests/Feature/AdminSecurityFeatureTest.php` (atributos readOnly + sync)










