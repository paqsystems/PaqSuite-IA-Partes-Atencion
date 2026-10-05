# SPEC-001-update – Errores de validación en el contexto de carga (modal)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-001-ui-errores-validacion-contexto-carga-update |
| Título | Mostrar errores de validación y guardado dentro del modal de carga, no en la página de fondo |
| Épica / carpeta | `001-Generalidades` (transversal hosts PaqSuite IA) |
| Estado | Especificado |
| Última actualización | 2026-10-05 |
| HU relacionada(s) | [HU-001-update](../../../03-historias-usuario/updates/001-Generalidades/HU-001-ui-errores-validacion-contexto-carga-update.md) |
| TR relacionada(s) | [TR-001-update](../../../04-tareas/updates/001-Generalidades/TR-001-ui-errores-validacion-contexto-carga-update.md) |
| Regla BASE | `.cursor/rules/base/20-frontend/36-ui-errores-validacion-contexto-carga.mdc` |
| Origen | `00-ControlCalidad-PQ` · fecha 05/10/2026 · Control de Calidad #5 · ABM Usuarios (GEN-06) |

---

## 1. Resumen ejecutivo

- **Problema:** al guardar desde un formulario en **Popup** (alta/edición sobre grilla), los errores de validación del servidor (p. ej. política de contraseña) se muestran como texto en la **página principal** (debajo del título), quedando **detrás** del overlay del modal. El usuario no asocia el mensaje con el formulario activo.
- **Resultado esperado:** todo error devuelto por una acción de **guardar** o **validar** iniciada desde un modal de carga se presenta **en el mismo contexto visual** que el formulario: dentro del panel del Popup o mediante un diálogo modal (p. ej. `confirm` / toast bloqueante acordado), nunca solo en el layout de fondo mientras el Popup sigue abierto.

---

## 2. Alcance

### 2.1 En alcance

- Pantallas con patrón **grilla + Popup** de alta/edición (regla 24).
- Errores de **envelope** (`error: true`, clave `respuesta` i18n) y errores de **validación de campo** cuando el backend los exponga en el mismo flujo de guardado.
- Procesos **GEN** en el host (admin usuarios, roles, permisos, empresas) y procesos **de dominio** con el mismo patrón (maestros Partes, tipos de tarea por cliente, etc.).
- Referencia de implementación correcta existente: **Carga diaria** (`CargaDiariaPage` — alerta de formulario solo dentro del Popup cuando está abierto).

### 2.2 Fuera de alcance

- Errores de **carga inicial** del listado (GET del grid): pueden mostrarse en la página o en banner de proceso según regla 30 (loading).
- Errores en **pantalla completa** de formulario (sin Popup) o en **login** (flujo auth GEN-04).
- Sustituir el contrato envelope del backend.
- Mobile kardex con detalle en Popup sin cambio de contrato API (solo presentación del error en el Popup).

---

## 3. Actores y contexto

Operador o administrador con permiso del proceso. Precondición: modal de alta/edición **visible** (`formOpen === true` o equivalente).

---

## 4. Comportamiento funcional

### 4.1 Reglas numeradas

| ID | Regla |
|----|--------|
| R-UI-ERR-01 | Si un Popup de carga está **abierto**, los mensajes de error producidos por **Guardar** (o acción equivalente dentro del Popup) **no** se renderizan únicamente en el layout de la página de fondo. |
| R-UI-ERR-02 | El mensaje debe ser **visible sin cerrar el Popup**: banda `role="alert"` dentro del contenido del Popup (preferido), o diálogo modal DevExtreme con el texto resuelto vía i18n. |
| R-UI-ERR-03 | Al **cerrar** el Popup sin guardar, limpiar el estado de error **del formulario**; los errores de **listado** (si existían) pueden permanecer según el proceso. |
| R-UI-ERR-04 | Separar estado **`listError`** (carga de grilla / acciones fuera del modal) y **`formError`** (guardado dentro del modal), o un único estado con **regla de presentación** condicionada a `formOpen` (patrón Carga diaria). |
| R-UI-ERR-05 | Mensajes vía `resolveAuthMessage` / `t()` con claves de envelope; sin literales fijos en español en el componente. |
| R-UI-ERR-06 | `data-testid` estable para la alerta de formulario: `{prefix}FormError` (ej. `adminUsuariosFormError`). |

### 4.2 Flujo principal (guardar con error)

1. Usuario abre **Nuevo** / **Editar** → Popup visible.
2. Completa campos y pulsa **Guardar**.
3. API responde error (422 validación, política de contraseña, duplicado, etc.).
4. El Popup **permanece abierto**.
5. El usuario ve el mensaje **dentro del Popup** (o en diálogo modal), con foco visual en la zona de error.
6. Corrige y reintenta o cancela.

### 4.3 Anti-patrón (prohibido)

- `{error && <div role="alert">…</div>}` único bajo el `<h2>` de la página, compartido entre `load()`, `handleSave()` y `handleDelete()`, mientras `formOpen === true`.

---

## 5. Criterios verificables

- [ ] **CV-01** En ABM Usuarios, contraseña inválida al crear usuario: mensaje visible **dentro** del Popup «Nuevo usuario», no solo bajo el título «Usuarios».
- [ ] **CV-02** Tras error en el Popup, el listado sigue visible detrás pero el mensaje no queda **solo** en el fondo.
- [ ] **CV-03** Maestros Partes (`MaestroCrudPage` y pantallas que lo usan): mismo criterio al fallar guardado en el Popup.
- [ ] **CV-04** Admin roles / permisos / empresas: mismo criterio en sus Popups de alta-edición.
- [ ] **CV-05** Carga diaria mantiene el patrón actual (regresión cero).
- [ ] **CV-06** Tests automatizados cubren al menos un caso (Usuarios o maestro) con `data-testid` de alerta en el formulario.

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Frontend | Refactor estado error contextual; opcional helper compartido en `frontend/src/shared/ui/`; alinear páginas listadas en TR |
| Backend | Sin cambio de contrato |
| i18n | Reutilizar claves existentes (`auth.password.*`, `validation.failed`, dominio) |
| SDK GEN | Recomendación futura: export en `@paqsuite/react-core` si el patrón se repite en más hosts |

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| Popup DevExtreme sin `contentRender` | Permisos admin ya documenta children vs `contentRender`; la alerta debe ir en el mismo árbol que el formulario visible |
| Múltiples Popups anidados | El error se ata al Popup que disparó la acción (el más interno) |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-05 | Volcado Parte G desde CC-PQ #5. |
