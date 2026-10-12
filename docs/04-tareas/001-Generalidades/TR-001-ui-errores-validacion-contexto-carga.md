# TR-001 – Errores de validación en contexto de modal (frontend)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-001](../../03-historias-usuario/001-Generalidades/HU-001-ui-errores-validacion-contexto-carga.md) |
| **SPEC relacionada** | [SPEC-001](../../05-open-spec/001-Generalidades/SPEC-001-ui-errores-validacion-contexto-carga.md) |
| **Épica** | 001 — Generalidades (transversal) |
| **Prioridad** | MUST-HAVE |
| **Estado** | Finalizado |
| **Última actualización** | 2026-10-11 |

**Origen:** `00-ControlCalidad-PQ` · 05/10/2026 · CC #5 · ABM Usuarios  
**Regla BASE:** `.cursor/rules/base/20-frontend/36-ui-errores-validacion-contexto-carga.mdc`  
**Patrón de referencia:** `frontend/src/features/partes/carga/CargaDiariaPage.tsx` (`renderFormErrorAlert` dentro del Popup cuando `formOpen`)  
**F1/F:** [D-VERIFICACION-CC-PQ-05](../updates/001-Generalidades/D-VERIFICACION-CC-PQ-05-2026-10-05.md)

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
| RN-TR-U03 | Componente `FormContextErrorAlert` en `frontend/src/shared/ui/` (props: `message`, `testId`). |
| RN-TR-U04 | Popup con `contentRender` (Permisos): incluir la alerta dentro del render del contenido, no fuera del árbol del Popup. |

### Inventario (host Partes)

| Archivo | Acción |
|---------|--------|
| `features/admin/security/UsuariosAdminPage.tsx` | Corregido (caso CC) |
| `features/admin/security/RolesAdminPage.tsx` | Corregido |
| `features/admin/security/PermisosAdminPage.tsx` | Corregido (`contentRender`) |
| `features/admin/security/EmpresasAdminPage.tsx` | Corregido |
| `features/partes/maestros/MaestroCrudPage.tsx` | Corregido |
| `features/partes/maestros/ClienteTiposTareaPage.tsx` | Corregido |
| `features/partes/carga/CargaDiariaPage.tsx` | Referencia / regresión |

---

## 4) Plan de tareas

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Helper/alerta compartida + refactor `UsuariosAdminPage` | AC-U01 + testid |
| T2 | Frontend | Refactor `MaestroCrudPage` y consumidores | AC-U02 |
| T3 | Frontend | Admin roles, permisos, empresas, `ClienteTiposTareaPage` | AC-U03 |
| T4 | Tests | Vitest unit (render condicional) + E2E guardado contraseña inválida | AC-U05 |

**Orden:** T1 → T2 → T3 → T4. **Hecho** 2026-10-05 / E2E 2026-10-11.

---

## 5) i18n y testid

| Clave / id | Uso |
|------------|-----|
| `auth.password.policyUnsafe` (y familia) | Mensaje usuario (ya en `authMessages.ts`) |
| `adminUsuariosFormError` | Alerta en Popup usuarios |
| `{testIdPrefix}FormError` | Maestros Partes |

Tests: `frontend/src/features/admin/security/UsuariosAdminPage.test.tsx`, `frontend/src/shared/ui/modalFormErrors.test.ts`, `frontend/tests/e2e/partes-admin-usuarios-form-error.spec.ts`.

---

## 6) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-05 | Volcado Parte G desde CC-PQ #5 (TR-update). |
| 2026-10-05 | Implementación T1–T4: `FormContextErrorAlert`, pantallas admin/maestros, Vitest. |
| 2026-10-11 | F1/F Aprobado. Parte I: promovido a original `001-Generalidades`; update eliminado. Estado → Finalizado. |
