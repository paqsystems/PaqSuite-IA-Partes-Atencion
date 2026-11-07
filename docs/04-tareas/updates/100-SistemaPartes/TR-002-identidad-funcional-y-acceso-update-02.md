# TR-002-update-02 – UI notificación acceso por usuario (CC #16)

Jira: PIAPARTES-44

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-002-update-02](../../../03-historias-usuario/updates/100-SistemaPartes/HU-002-identidad-funcional-y-acceso-update-02.md) |
| **SPEC relacionada** | [SPEC-002-update-02](../../../05-open-spec/updates/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso-update-02.md) |
| **TR anterior** | [TR-002-update](./TR-002-identidad-funcional-y-acceso-update.md) — **mantener** backend notify-access; **sustituir** §6 UI toolbar |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Estado** | Finalizado (F1/F CC #16 · 07/11/2026) |
| **Última actualización** | 2026-11-07 |
| **Verificación** | [D-VERIFICACION-CC-PQ-16](./D-VERIFICACION-CC-PQ-16-2026-11-07.md) · [E-CC-PQ-16](./E-CC-PQ-16-2026-11-07.md) |
| **Origen** | `00-ControlCalidad-PQ` · **CC #16** · 07/11/2026 |

---

## 1) In scope / out

**In:** retiro flujo masivo FE; ícono junto a Email; alta sin password UI + generación BE; auto-notify post-create; notify en edición; i18n; tests; OpenAPI create si cambia contrato password.

**Out:** reescribir mail; fork `UsuariosAdminPage`; mobile; proceso masivo.

---

## 2) AC de implementación

| AC | Verificación |
|----|--------------|
| CA-16-01 | No `NotifyUsuarioAccessToolbar` ni popup con SelectBox de usuarios |
| CA-16-02 | Ícono `adminUsuariosNotifyEmail` junto a Email; disabled sin mail |
| CA-16-03 | Sin TextBox contraseña en create; POST create sin password |
| CA-16-04 | Feature/Vitest: alta + email → notify encadenado o 2 llamadas |
| CA-16-05 | Edición: click ícono → POST notify-access |
| CA-16-06 | i18n + regla 36 + regresión notify existente |

---

## 3) Contrato técnico

| ID | Implementación |
|----|----------------|
| RN-CC16-FE01 | Eliminar `NotifyUsuarioAccessToolbar`, `NotifyUsuarioAccessDialog` masivo y tests del combo; quitar montaje en `UsuariosAdminPage.tsx`. |
| RN-CC16-FE02 | Integrar acción en formulario usuario. **Orden preferido:** (a) bump `@paqsuite/react-core` si Framework publica props en `UsuariosFormPopup` (`hidePasswordOnCreate`, `renderEmailSuffix`, `onNotifyAccess`); (b) si no hay export en pin vigente: wrapper host **documentado** en PR (mínimo diff) sin copiar grilla/listado — citar deuda Framework en commit. |
| RN-CC16-FE03 | `data-testid`: `adminUsuariosNotifyEmail`, `adminUsuariosNotifyFormError`. |
| RN-CC16-BE01 | `UsuariosController::store`: `password` deja de ser `required`; si ausente/vacío → `$this->passwordGenerator->generate()` + `PasswordPolicy::evaluate` antes de persistir. |
| RN-CC16-BE02 | Tras create exitoso con email trim no vacío: invocar misma lógica que `notifyAccess` (extraer a servicio `AdminUsuarioNotifyAccessService` o método privado compartido) **sin** segundo round-trip obligatorio desde FE (FE puede orquestar 2 calls si TR lo simplifica en D1 — preferido **un solo** POST create que devuelve `notified: true/false` opcional en `resultado` si se documenta en OpenAPI). **Decisión D1:** por defecto FE `POST create` + `POST notify-access` secuencial en alta si create no encadena en BE. |
| RN-CC16-BE03 | `notifyAccess` sin cambio funcional. |
| RN-CC16-BE04 | Envelope GEN-20; 422 `admin.usuarios.notify.emailRequired` sin cambios. |
| RN-CC16-i18n | Claves nuevas tooltip ícono en 5 locales; retirar o dejar obsoletas claves solo-toolbar si no se usan. |
| RN-CC16-SDK | Épica Framework opcional: SPEC/HU en repo Framework para extender GEN-06 form popup — **no bloquea** D si se adopta (b) con aprobación en D1. |

**SP:** sin cambio de firma salvo que create usuario pase hash generado vía SP existente (`pq_sp_admin_usuarios_*`).

---

## 4) Plan Parte D1 (resumen)

1. BE: password opcional en store + tests.
2. FE: retirar toolbar masivo.
3. FE: ícono + wire notify (edición) y secuencia alta (guardar → notify).
4. i18n + OpenAPI.
5. Actualizar manual usuario SPEC-002 si aplica en F.

---

## 5) Tests

| Capa | Caso |
|------|------|
| PHPUnit | `store` sin password genera hash válido |
| PHPUnit | regresión `notify-access` |
| Feature | alta API + notify (fake mail) |
| Vitest | sin toolbar; ícono disabled/enabled; mock notify en edición |
| E2E | opcional smoke alta con email |

---

## 6) Artefactos a tocar (orientativo D)

| Archivo / área | Acción |
|----------------|--------|
| `frontend/src/features/admin/security/UsuariosAdminPage.tsx` | Quitar toolbar |
| `frontend/src/features/admin/security/NotifyUsuarioAccessDialog.tsx` | Eliminar o reducir a helpers del ícono en form |
| `backend/app/Http/Controllers/Api/V1/Admin/UsuariosController.php` | store password opcional |
| `OpenApiPathsAdmin.php` | create body |
| `packages` Framework | solo si hay contrato SDK acordado |

---

## 7) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-07 | Parte G CC-PQ #16 — TR-update-02 |
| 2026-11-07 | Parte D: BE password opcional; FE `PartesUsuariosFormPopup` + retiro toolbar; tests |
| 2026-11-07 | Parte E: E2E `partes-cc-pq-16-usuarios-notify-access.spec.ts`; integración opcional `PW_INTEGRATION=1`; PHPUnit `ApiV1AdminUsuariosAutoPasswordPolicyTest` |
| 2026-11-07 | Parte F/F1: veredicto **Aprobado con observaciones** — D-VERIFICACION / E-CC-PQ-16 |
