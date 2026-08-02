# TR-GEN-02-admin-permisos — ABM permisos individual (`Pq_Permiso`)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-admin-permisos](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-permisos.md) |
| **SPEC relacionada** | [SPEC-001-02-admin-mantenimiento-roles-permisos](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md) |
| **Épica** | 001-Generalidades / Acceso y seguridad (post-MVP) |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-02-admin-roles; TR-GEN-03-patron-abm; TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-19 (revisión C1 formal `/tr-ambiguity-review`) |

**Origen:** [HU-GEN-02-admin-permisos](../../03-historias-usuario/001-Generalidades/HU-GEN-02-admin-permisos.md)  
**Referencia SPEC:** [SPEC-001-02-admin-mantenimiento-roles-permisos](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md)  
**Contexto:** [`mantenimiento-roles-permisos.md`](../../00-contexto/_mono/02-acceso-y-seguridad/mantenimiento-roles-permisos.md) · Tango [TR-013](https://github.com/paqsystems/PaqSuite-IA-TANGO/blob/main/docs/04-tareas/001-Seguridad/TR-013-administracion-permisos.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Asignación individual usuario–rol (`Pq_Permiso`) sin ABM de usuarios.

### Narrativa
Como administrador de permisos, quiero asignar, editar y quitar roles a usuarios existentes para controlar perfiles en el portal sin gestionar datos maestros de usuario.

### In scope / Out of scope
- **In scope:** grilla asignaciones; filtros usuario/rol; modal alta/edición; baja confirmada; lookup usuarios read-only; API CRUD; i18n `admin.permisos.*` (sin claves bulk/empresa).
- **Out of scope:** batch masivo (TR bulk); ABM `users`; dimensión empresa en UI.

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Listado con filtros por usuario y rol.
- **AC-02:** Alta individual crea fila y refresca grilla.
- **AC-03:** Edición cambia el rol de la asignación.
- **AC-04:** Baja elimina fila tras confirmación i18n.
- **AC-05:** Duplicado `(id_usuario, id_rol)` → 422.
- **AC-06:** Referencias inválidas → 422.
- **AC-07:** Sin autorización → 403 / pantalla inaccesible.
- **AC-08:** Lookup usuarios no permite crear/editar usuarios.
- **AC-09:** i18n + `data-testid`: `permisos.admin`, `permisos.grid`, `permisos.create`, `permisos.delete`.
- **AC-10:** E2E smoke pantalla + modal alta (Tango `permisos-admin.spec.ts` adaptado).

### Escenarios Gherkin

(Heredados de HU-GEN-02-admin-permisos.)

---

## 3) Reglas de Negocio

1. **RN-01:** Unicidad `(id_usuario, id_rol, id_empresa)` con `id_empresa = monoEmpresaId` fijo en backend.
2. **RN-02:** Multi-rol permitido: N filas por usuario.
3. **RN-03:** Usuario y rol deben existir; lookup usuarios solo `activo = true` **y** `inhabilitado = false`.
4. **RN-04:** Eliminar última asignación de un usuario impide login posterior (comportamiento existente SPEC-001-02).
5. **RN-05:** Autorización vía `pw_adminpermisos` + `AdminSecurityAccessService`.
6. **RN-06:** Catálogo usuarios: solo listado paginado/búsqueda — sin POST/PUT/DELETE en `/admin/usuarios`.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-19)

**Skill:** `/tr-ambiguity-review`

**Fuentes revisadas:** HU-GEN-02-admin-permisos, SPEC-001-02-admin, `mantenimiento-roles-permisos.md`, TR-GEN-03-patron-abm, TR-GEN-02-admin-roles; código `User.php` (`name_user`, `activo`, `inhabilitado`), `PqPermiso` migración `uq_pq_permiso_rol_empresa_usuario`, `SessionContextBuilder.php`.

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí**

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución (→ D1) |
|----|------|--------|-------------------|
| AMB-C1-P-01 | Campo nombre usuario en API | TR decía `name`; modelo usa `name_user` | JSON API: `usuarioNombre` mapeado desde `users.name_user`; lookup `{ id, codigo, nameUser }`. |
| AMB-C1-P-02 | Multi-rol + login | Alta segunda asignación no refleja unión hasta T0 | Depende T0 TR-roles; Feature test crea 2 filas mismo usuario y verifica menú post re-login. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M-C1-P-01 | Lookup paginado | Shape MONO estándar en `resultado`: `{ items, page, page_size, total, total_pages }`. |
| AMB-M-C1-P-02 | Lookup roles en modal | `GET /admin/roles` requiere gate `pw_adminpermisos` **o** `pw_adminroles` repo (supervisor AccesoTotal OK). |
| AMB-M-C1-P-03 | Alta UI | Botón **+** grilla DX (TR-GEN-03); `data-testid` `permisos.create` en flujo alta. |
| AMB-M-C1-P-04 | DELETE resultado | `resultado: {}` (objeto vacío, nunca null). |
| AMB-M-C1-P-05 | Refresh sesión | Igual epic: re-login recomendado v1. |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| HU CA-09 `permisos.create` vs patrón + DX | `permisos.create` = testid estable del flujo alta (wrapper/+ DX). |
| SPEC CA-10 sin ABM users vs lookup | `GET /admin/usuarios` solo GET; sin mutaciones. |
| Unicidad SPEC `(id_usuario, id_rol)` vs índice BD `(id_rol, id_empresa, id_usuario)` | Equivalente funcional con `id_empresa` fijo. |

### Supuestos detectados

- T0 admin (roles TR) desplegado antes de QA permisos multi-rol.
- Usuarios ERP en `users` poblados por sync en prod.

### Preguntas para decisión humana

(Ninguna bloqueante.)

### Recomendaciones de ajuste de la TR

- [x] Corregir §5.2 lookup y listado a `nameUser` / `usuarioNombre`.
- [x] Documentar filtro lookup usuarios activos.

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-19)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-P-01 | Permisos API | GET/PUT/DELETE → Repo/Modi/Baja; POST → Alta en `pw_adminpermisos`. |
| R-C1-P-02 | Columnas grilla | `usuarioCodigo`, `usuarioNombre`, `rolNombre`, `idRol`, `id`. |
| R-C1-P-03 | Edición | PUT solo `idRol`; `idUsuario` inmutable. |
| R-C1-P-04 | Filtros | Query `usuarioId`, `rolId`. |
| R-C1-P-05 | Lookup usuarios | `nameUser` desde `users.name_user`; filtro activo ∧ ¬inhabilitado. |
| R-C1-P-06 | Paginación lookup | Default `pageSize=20`, max 50. |
| R-C1-P-07 | Duplicado | 422 `admin.permisos.duplicateAssignment`. |

---

## 3.3) Plan D1 — Implementación (2026-06-19)

**Estado:** Cerrado.

| # | Entrega | Estado |
|---|---------|--------|
| T1 | `AdminPermisoController` CRUD + lookup usuarios | ✅ |
| T2 | `AdminPermisoService` + `AdminUsuarioLookupController` | ✅ |
| T3 | `PermisosAdminPage` + `PermisoFormModal` + filtros | ✅ |
| T4 | Tests Feature + i18n 5 locales | ✅ |

---

## 3.4) Verificación D (2026-06-19)

| Verificación | Resultado |
|--------------|-----------|
| GET/POST/PUT/DELETE permisos | OK — `testPermisosCrudAndLookup` |
| Duplicado asignación → 422 | OK — mismo test |
| GET lookup usuarios paginado | OK — mismo test |
| `PermisosAdminPage` + filtros + modal | OK — build + E2E abre pantalla |
| i18n `admin.permisos.*` (5 locales) | OK |
| Inyección `id_empresa` backend | OK — `AdminPermisoService` |

### Trazabilidad AC

| AC | Evidencia | Estado D |
|----|-----------|----------|
| AC-01 | Filtros SelectBox + Feature list | ✅ |
| AC-02 | Feature POST create | ✅ |
| AC-03 | Feature PUT update rol | ✅ |
| AC-04 | Feature DELETE + confirmDelete DX | ✅ |
| AC-05 | Feature duplicate 422 | ✅ |
| AC-06 | Feature referencias inválidas (service) | ✅ |
| AC-07 | Feature 403 + AdminSecurityGate | ✅ |
| AC-08 | Lookup read-only (sin POST users) | ✅ |
| AC-09 | testid `permisos.admin`, `permisos.grid`, `permisos.create` | ✅ |
| AC-10 | E2E pantalla permisos (bulk spec navega) | ✅ parcial |

---

## 3.5) Verificación E (2026-06-19)

Ver [E-GEN-02-admin-tests.md](E-GEN-02-admin-tests.md). CRUD + lookup en Feature: **Apto**.

---

## 4) Impacto en Datos

### Tablas afectadas

| Tabla | Operación |
|-------|-----------|
| `Pq_Permiso` | CRUD |
| `users` | Lectura lookup |
| `Pq_Rol` | Lectura lookup |

### Seed mínimo para tests

- Usuarios seed MVP + roles seed.
- Crear segunda asignación multi-rol para usuario test en Feature test (no alterar seed global).

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Método | Path | Permiso |
|--------|------|---------|
| GET | `/api/v1/admin/permisos` | `Permiso_Repo` + `pw_adminpermisos` |
| POST | `/api/v1/admin/permisos` | `Permiso_Alta` + `pw_adminpermisos` |
| PUT | `/api/v1/admin/permisos/{id}` | `Permiso_Modi` + `pw_adminpermisos` |
| DELETE | `/api/v1/admin/permisos/{id}` | `Permiso_Baja` + `pw_adminpermisos` |
| GET | `/api/v1/admin/usuarios` | `Permiso_Repo` + `pw_adminpermisos` |

### 5.2 Detalle por operación

#### GET `/api/v1/admin/permisos`

**Query:** `usuarioId`, `rolId` (opcionales).

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "items": [
      {
        "id": 10,
        "idUsuario": 5,
        "usuarioCodigo": "vendedor.acotado.mvp",
        "usuarioNombre": "Vendedor Acotado MVP",
        "idRol": 3,
        "rolNombre": "VendedorAcotado"
      }
    ]
  }
}
```

#### POST `/api/v1/admin/permisos`

**Request:**

```json
{
  "idUsuario": 5,
  "idRol": 4
}
```

Backend inyecta `id_empresa` desde config. **422:** `admin.permisos.duplicateAssignment`.

#### PUT `/api/v1/admin/permisos/{id}`

```json
{ "idRol": 2 }
```

Validar unicidad al cambiar rol.

#### DELETE `/api/v1/admin/permisos/{id}`

**200:** `{}` en resultado.

#### GET `/api/v1/admin/usuarios`

**Query:** `search` (codigo/nameUser), `page`, `pageSize` (default 20, max 50).

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "items": [
      { "id": 5, "codigo": "vendedor.acotado.mvp", "nameUser": "Vendedor Acotado MVP" }
    ],
    "page": 1,
    "page_size": 20,
    "total": 1,
    "total_pages": 1
  }
}
```

Solo usuarios con `activo = true` e `inhabilitado = false`.

### 5.3 Actualización matriz permisos

- [x] Filas § Admin seguridad en matriz (D1 2026-06-19).

---

## 6) Cambios Frontend

### Pantallas

```text
frontend/src/features/admin/security/permisos/
  PermisosAdminPage.tsx
  PermisoFormModal.tsx         # SelectBox usuario + rol (lookup)
  permisosAdminApi.ts
```

- Filtros toolbar: `SelectBox` usuario, `SelectBox` rol (clearable).
- Modal alta: usuario + rol; edición: solo rol (usuario read-only).
- Toolbar reserva espacio para botones bulk (TR hermana) — sin implementarlos aquí.

### data-testid

Según contexto: `permisos.admin`, `permisos.grid`, `permisos.create`, `permisos.delete`, `permisos.filters.usuario`, `permisos.filters.rol`.

### i18n

`admin.permisos.*` en cinco locales (tabla en `mantenimiento-roles-permisos.md`).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | `AdminPermisoController` + `AdminUsuarioLookupController` | Feature CRUD + 422 |
| T2 | Frontend | `PermisosAdminPage` + modal | AC-01–AC-09 |
| T3 | Tests | E2E `permisos-admin.spec.ts` | AC-10 |
| T4 | Docs | OpenAPI + matriz | §10 |

---

## 8) Estrategia de Tests

- **Integration:** alta; duplicado; delete; lookup usuarios sin mutación; 403.
- **E2E:** supervisor abre pantalla, filtra, abre modal alta (mock selección).

---

## 9) Riesgos y Edge Cases

- Usuario ERP deshabilitado post-asignación — listado puede mostrar asignación válida; login falla por otras reglas (documentar).
- Edición que deja duplicado usuario+rol distinto id → 422.

---

## 10) Checklist final

(Checklist transversal — ver TR-GEN-02-admin-roles.)

---

## Archivos creados/modificados (D1 2026-06-19)

- `app/Services/Admin/AdminPermisoService.php`
- `app/Http/Controllers/Api/V1/Admin/AdminPermisoController.php`
- `app/Http/Controllers/Api/V1/Admin/AdminUsuarioLookupController.php`
- `frontend/src/features/admin/security/permisos/PermisosAdminPage.tsx`
- `frontend/src/features/admin/security/permisos/PermisoFormModal.tsx`
- `frontend/src/features/admin/security/permisos/permisosAdminApi.ts`
- `tests/Feature/AdminSecurityFeatureTest.php` (CRUD + lookup)










