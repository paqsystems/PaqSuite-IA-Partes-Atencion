# TR-001-update – Errores de validación en contexto de modal (frontend)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-001-update](../../../03-historias-usuario/updates/001-Generalidades/HU-001-ui-errores-validacion-contexto-carga-update.md) |
| **SPEC relacionada** | [SPEC-001-update](../../../05-open-spec/updates/001-Generalidades/SPEC-001-ui-errores-validacion-contexto-carga-update.md) |
| **Épica** | 001 — Generalidades (transversal) |
| **Prioridad** | MUST-HAVE |
| **Estado** | En Control Calidad |
| **Última actualización** | 2026-10-05 |

**Origen:** `00-ControlCalidad-PQ` · 05/10/2026 · CC #5 · ABM Usuarios  
**Regla BASE:** `.cursor/rules/base/20-frontend/36-ui-errores-validacion-contexto-carga.mdc`  
**Patrón de referencia:** `frontend/src/features/partes/carga/CargaDiariaPage.tsx` (`renderFormErrorAlert` dentro del Popup cuando `formOpen`)

---

## 1) Resumen técnico

Separar errores de **listado** y de **formulario modal** (o condicionar el render al estado `formOpen`). Incorporar alerta con `role="alert"` y `data-testid` en el contenido del Popup **antes** de los campos. No cambiar APIs.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|----------------|
| AC-U01 | `UsuariosAdminPage`: fallo en `createAdminUsuario` / `updateAdminUsuario` → `adminUsuariosFormError` visible con Popup abierto; `setFormOpen(false)` no se ejecuta en error. |
| AC-U02 | `MaestroCrudPage`: fallo en `savePartesResource` con Popup abierto → `{testIdPrefix}FormError` dentro del Popup. |
| AC-U03 | `RolesAdminPage`, `PermisosAdminPage` (Popups de rol/permiso), `EmpresasAdminPage`, `ClienteTiposTareaPage`: mismo patrón. |
| AC-U04 | Errores de `load()` del grid siguen pudiendo mostrarse en página cuando **no** hay modal abierto. |
| AC-U05 | Vitest o test de componente + al menos un E2E Playwright (usuarios o maestro) en verde. |

---

## 3) Reglas de implementación

| ID | Implementación |
|----|----------------|
| RN-TR-U01 | Estados `listError` y `formError` (recomendado) o una sola fuente con render: `!formOpen ? listAlert : null` + `formOpen ? formAlert : null` dentro del Popup. |
| RN-TR-U02 | En `handleSave`, asignar solo `formError`; en `load` / delete fuera del modal, `listError`. Limpiar `formError` al abrir/cerrar modal. |
| RN-TR-U03 | Componente opcional `FormContextErrorAlert` en `frontend/src/shared/ui/` para evitar duplicación (props: `message`, `testId`). |
| RN-TR-U04 | Popup con `contentRender` (Permisos): incluir la alerta dentro del render del contenido, no fuera del árbol del Popup. |

### Inventario inicial (host Partes)

| Archivo | Acción |
|---------|--------|
| `features/admin/security/UsuariosAdminPage.tsx` | Corregir (caso CC) |
| `features/admin/security/RolesAdminPage.tsx` | Corregir |
| `features/admin/security/PermisosAdminPage.tsx` | Corregir (contentRender) |
| `features/admin/security/EmpresasAdminPage.tsx` | Corregir |
| `features/partes/maestros/MaestroCrudPage.tsx` | Corregir (todas las pantallas que lo consumen) |
| `features/partes/maestros/ClienteTiposTareaPage.tsx` | Corregir |
| `features/partes/carga/CargaDiariaPage.tsx` | Solo regresión / referencia |

---

## 4) Plan de tareas

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Helper/alerta compartida + refactor `UsuariosAdminPage` | AC-U01 + testid |
| T2 | Frontend | Refactor `MaestroCrudPage` y consumidores | AC-U02 |
| T3 | Frontend | Admin roles, permisos, empresas, `ClienteTiposTareaPage` | AC-U03 |
| T4 | Tests | Vitest unit (render condicional) + E2E guardado contraseña inválida | AC-U05 |

**Orden:** T1 → T2 → T3 → T4.

---

## 5) i18n y testid

| Clave / id | Uso |
|------------|-----|
| `auth.password.policyUnsafe` (y familia) | Mensaje usuario (ya en `authMessages.ts`) |
| `adminUsuariosFormError` | Alerta en Popup usuarios |
| `{testIdPrefix}FormError` | Maestros Partes |

---

## 6) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-05 | Volcado Parte G desde CC-PQ #5. |
| 2026-10-05 | Implementación T1–T4: `FormContextErrorAlert`, pantallas admin/maestros, Vitest `UsuariosAdminPage.test.tsx`. |
