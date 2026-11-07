# SPEC-002-update-02 – Notificación de acceso por usuario (refino CC #16)

Jira: PIAPARTES-44 (misma épica; trazabilidad CC **#16** 07/11/2026)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-002-update-02 |
| Título | ABM Usuarios — notificar acceso en el formulario (no masivo) |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Especificado |
| Última actualización | 2026-11-07 |
| SPEC base | [SPEC-002-identidad-funcional-y-acceso](../../100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md) |
| Update anterior | [SPEC-002-update](./SPEC-002-identidad-funcional-y-acceso-update.md) — backend `notify-access` válido; **UI toolbar masiva queda obsoleta** |
| HU relacionada(s) | [HU-002-update-02](../../../03-historias-usuario/updates/100-SistemaPartes/HU-002-identidad-funcional-y-acceso-update-02.md) |
| TR relacionada(s) | [TR-002-update-02](../../../04-tareas/updates/100-SistemaPartes/TR-002-identidad-funcional-y-acceso-update-02.md) |
| Origen | `00-ControlCalidad-PQ` · **CC #16** · 07/11/2026 |
| Depende de | GEN-04 (`firstLogin`, `PasswordPolicy`) · GEN-06 (`UsuariosAdminPage` / `UsuariosFormPopup`) · GEN-20 · endpoint `POST .../notify-access` (ya implementado) |

---

## 1. Resumen ejecutivo

- **Problema:** la entrega de **TR-002-update** (Parte D 06/11/2026) ubicó «Notificar acceso» en un **toolbar masivo** con selector de usuario, alejado del flujo de alta/edición y del pedido original de **CC #7**.
- **Resultado esperado:** notificación **por usuario en contexto**: ícono junto al mail en el popup GEN-06; en **alta** sin contraseña manual, envío automático tras guardar; en **edición** reenvío bajo demanda.

---

## 2. Alcance

### 2.1 En alcance

1. **Retirar** el flujo masivo (`NotifyUsuarioAccessToolbar` + diálogo con lista de usuarios).
2. **Ícono de envío** adyacente al campo **Email** en popup **alta** y **edición** (y consulta RO si el SDK la expone). Habilitado solo con email no vacío (trim).
3. **Alta:** ocultar / no exigir contraseña en UI; el **servidor** genera clave conforme `PasswordPolicy`, persiste hash + `firstLogin=true` (misma semántica que `notify-access`).
4. **Alta:** tras **Guardar** exitoso con email, **disparar envío de acceso** para el `id` creado (reutilizar lógica/mail de `notify-access`). Error de mail visible en el modal (regla 36); decisión A1: informar antes de cerrar si falló el envío post-alta.
5. **Edición:** el ícono llama `POST /api/v1/admin/usuarios/{id}/notify-access` para el usuario del formulario.
6. Mantener contrato de mail, asunto, URL tenant, i18n 5 locales, exclusiones mobile.
7. Preferir **extensión SDK** (`UsuariosFormPopup`: props para ocultar password en create, `renderEmailSuffix`, callback notify) antes que fork de `UsuariosAdminPage`. Si el pin actual no lo permite: **deuda Framework** documentada en TR + solución host mínima acotada en TR (sin copiar listado/grilla).

### 2.2 Fuera de alcance

- Selector masivo de usuarios para notificar.
- Cambiar copy del mail de acceso (sigue CC #7 / update-01).
- Mail proceso masivo (SPEC-005).
- SMTP host paralelo.
- Fork completo de GEN-06.

---

## 3. Actores y contexto

| Actor | Acción |
|-------|--------|
| Operador ABM Usuarios | Alta con email → guardar → mail automático; o edición → reenvío con ícono |
| Destinatario | Recibe credenciales; `firstLogin` fuerza cambio (GEN-04) |
| Sistema | Genera clave en alta o en notify; persiste; envía |

Precondiciones notify manual (edición): usuario persistido, email válido, Mail Engine operativo.

---

## 4. Comportamiento funcional

### 4.1 UI

- No existe botón global «Notificar acceso» sobre la grilla.
- En el popup de usuario, junto al **Email**: ícono/botón compacto (tooltip i18n `partes.notifyAccess.sendTooltip` o clave GEN acordada).
- `data-testid`: `adminUsuariosNotifyEmail` (ícono); errores `adminUsuariosNotifyFormError` en el popup.

### 4.2 Alta

1. Operador completa código, nombre, email (sin campo contraseña).
2. **Guardar** → API create acepta ausencia de password en body; BE genera clave policy-compliant, persiste usuario.
3. Si hay email → encadena notify (mismo resultado que endpoint dedicado).
4. Éxito: cerrar modal y refrescar grilla (o mensaje éxito acordado en TR). Fallo notify: mostrar error **en el modal**; datos del usuario persistidos.

### 4.3 Edición

1. Ícono habilitado con email.
2. Click → `notify-access` sin cerrar el modal salvo éxito acordado en TR (A1: mantener modal abierto ante error).

### 4.4 Backend

- `POST notify-access`: sin cambio de contrato salvo alineación de mensajes.
- `POST create usuario` (admin): `password` **opcional**; si falta o vacío → generar con `PolicyCompliantPasswordGenerator` + `PasswordPolicy` antes de persistir; marcar `firstLogin=true` cuando el flujo sea “acceso notificado” (alta con email o notify).

---

## 5. Criterios verificables (CA-16)

- [ ] **CA-16-01** Sin toolbar/diálogo masivo con selector de usuarios.
- [ ] **CA-16-02** Ícono junto a Email; disabled sin mail; testid estable.
- [ ] **CA-16-03** Alta sin contraseña en UI; generación server-side.
- [ ] **CA-16-04** Guardar en alta + email → mail enviado (o error en modal).
- [ ] **CA-16-05** Edición → ícono reenvía vía `notify-access`.
- [ ] **CA-16-06** i18n 5 locales; regla 36; tests.

---

## 6. Impacto técnico (visión TR)

- FE: retirar `NotifyUsuarioAccessDialog` masivo; integrar en formulario (SDK o wrapper documentado).
- BE: relajar validación `password` required en store; servicio compartido create+notify.
- Framework (opcional épica): export/props en `UsuariosFormPopup` — bump documentado en TR.
- Tests: actualizar Vitest; feature create sin password + notify encadenado.

---

## 7. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-07 | Parte G CC-PQ #16 — corrige UI de SPEC-002-update / TR D 06/11/2026 |
