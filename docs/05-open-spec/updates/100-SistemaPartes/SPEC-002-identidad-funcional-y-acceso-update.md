# SPEC-002-update – Notificación de acceso (ABM Usuarios)

Jira: PIAPARTES-44

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-002-update |
| Título | Mail de acceso al sitio desde ABM Usuarios (alta, edición, consulta) |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-10-20 |
| SPEC base | [SPEC-002-identidad-funcional-y-acceso](../../100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md) |
| HU relacionada(s) | [HU-002-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-002-identidad-funcional-y-acceso-update.md) |
| TR relacionada(s) | [TR-002-update](../../../04-tareas/updates/100-SistemaPartes/TR-002-identidad-funcional-y-acceso-update.md) |
| Origen | `00-ControlCalidad-PQ` · CC #7 · 19/10/2026 · ítem «Envio mail a usuario para acceder al sitio» |
| A1 | [Apto con observaciones](./SPEC-002-identidad-funcional-y-acceso-update-A1-ambiguity-review.md) |
| Depende de | GEN-04 (`firstLogin`, `PasswordPolicy`, Mail Engine de forgot/reset) · GEN-06 (`UsuariosAdminPage`, sin fork) · GEN-20 envelope |

---

## 1. Resumen ejecutivo

- **Problema:** el operador necesita avisar a un usuario (cliente o interno) cómo entrar al sistema de Partes, con URL y credenciales iniciales, sin armar el correo a mano.
- **Resultado esperado:** en el ABM Usuarios (GEN-06) existe la acción **Notificación**: genera una contraseña que cumple la política parametrizada, persiste el hash, marca `firstLogin`, y envía un mail con asunto y cuerpo de producto.

---

## 2. Alcance

### 2.1 En alcance

- Acción **Notificación** / **Notificar** en el proceso ABM Usuarios ya cableado (`UsuariosAdminPage` SDK), habilitada en **alta**, **edición** y **consulta** (si el proceso expone vista de solo lectura). Decisión A1 20/10/2026.
- Habilitación: deshabilitada si `email` está vacío. En alta, el envío es sobre usuario **persistido** (tras Guardar, o el mismo POST de alta+notificar si el SDK lo permite en un solo paso). En edición y consulta el `users.id` ya existe.
- Generar contraseña aleatoria que cumpla `PasswordPolicy` del Framework (simple/segura y longitud de parámetros GEN).
- Persistir password (hash) y `firstLogin = true` para forzar cambio en el próximo acceso (contrato GEN-04 ya vigente en SPEC-002 §4.1 paso 5).
- Mail:
  - **Asunto:** código de empresa + texto i18n del tipo «ingreso a nuestro sistema de Partes de Atención».
  - **Cuerpo:** invitación a controlar los servicios prestados a su empresa; hipervínculo a la URL de acceso del sitio; credenciales (`usuario` y contraseña en claro **solo en este mail**).
- Motor de envío: **Mail Engine GEN** ya usado en recuperación de clave (`mailEngine.*`). No SMTP paralelo en el host. GEN-30 (Notification Service) se adopta cuando el SDK lo exporte, **sin** cambiar triggers ni copy de producto.
- i18n 5 locales; `data-testid` estable; errores de validación en el **mismo contexto** (regla 36 si hay modal).
- Persistencia de negocio / users vía contrato existente de admin usuarios (excepción auth GEN regla 74) + SP si el host ya persiste users por SP.

### 2.2 Fuera de alcance

- Reimplementar o copiar `UsuariosAdminPage` (regla 19 / 44 GEN-06).
- Redefinir login, forgot/reset o la pantalla de cambio de clave.
- WhatsApp / Telegram / bandeja GEN-17.
- Mobile: ABM usuarios **excluido** (regla 80).
- Mail de novedades del proceso masivo (SPEC-005-update).

---

## 3. Actores y contexto

| Actor | Acción |
|-------|--------|
| Operador con permiso de menú ABM Usuarios | Dispara Notificación |
| Destinatario (`users.email`) | Recibe mail; entra con usuario/clave; GEN-04 exige cambio si `firstLogin` |
| Sistema | Genera clave, persiste, envía |

Precondiciones: usuario persistido, email válido, Mail Engine configurado, política de clave GEN operativa.

---

## 4. Comportamiento funcional

1. El operador abre ABM Usuarios (wrapper fino GEN-06).
2. En **alta**, **edición** y **consulta** (si hubiere), **Notificar** está deshabilitada si `email` está vacío; habilitada con email.
3. Al confirmar Notificación sobre un `users.id` persistido:
   1. Generar password aleatorio que pase `PasswordPolicy::evaluate`.
   2. Guardar hash + `firstLogin = true`.
   3. Enviar mail (asunto/cuerpo §2.1). URL = origen público del SPA (config host, no hardcode de entorno).
   4. Respuesta envelope `{ error: 0, respuesta, resultado }` con confirmación **sin** devolver la clave al cliente HTTP (la clave solo viaja en el mail).
4. Fallos: sin email → 422 clave producto; mail no enviado → `mailEngine.sendFailed` / `mailEngine.configMissing` (catálogo existente); no cerrar el popup si el error es de la acción en modal.

### UI GEN-06

El host **no** forkea la página. Parte D: usar slot/prop oficial del SDK si existe; si no, **gap Framework** documentado + control host **junto** al wrapper (toolbar extra) sin copiar el listado/popup GEN.

---

## 5. Criterios verificables

- [ ] Botón/acción Notificación con `data-testid` (p. ej. `adminUsuariosNotify`) y tooltip/caption i18n.
- [ ] Deshabilitado sin email; habilitado con email en alta (persistido), edición y consulta si existe.
- [ ] Password generada cumple política parametrizada.
- [ ] Tras éxito, el próximo login con esa cuenta entra a cambio de clave (`firstLogin`).
- [ ] Mail con código empresa, URL clicable, usuario y contraseña.
- [ ] Sin SMTP propio; usa Mail Engine GEN.
- [ ] Sin fork de `UsuariosAdminPage`.
- [ ] 5 locales para asunto/cuerpo/captions.

---

## 6. Impacto técnico (visión para TR)

- Endpoint dedicado p. ej. `POST /api/v1/admin/usuarios/{id}/notify-access`.
- Reutilizar `PasswordPolicy` y Mail Engine laravel-core.
- Código empresa: dato de la empresa activa / instalación MONO (no inventar).
- Tests: unit política+generador; feature envío (fake mail); Vitest habilitación botón; E2E humo opcional.

---

## 7. Riesgos y supuestos

- Enviar la contraseña en claro por mail es un pedido explícito del CC; el riesgo se mitiga con `firstLogin` obligatorio.
- Si el SDK no expone acción extra en GEN-06, hay dependencia de bump o toolbar host mínima.
- «Proveedor link del sitio» del hallazgo = URL pública de acceso, no un proveedor OAuth.

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-20 | Parte G CC-PQ #7 19/10/2026 |
| 2026-10-20 | A1: Notificación acotada a **alta** (el CC no pide edición). |
| 2026-10-20 | Decisión humana: Notificar también en **edición** y **consulta** (si hubiere). |
