# TR-002-update – Notificación de acceso (ABM Usuarios)

Jira: PIAPARTES-44

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-002-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-002-identidad-funcional-y-acceso-update.md) |
| **SPEC relacionada** | [SPEC-002-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso-update.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Estado** | Pendiente |
| **Última actualización** | 2026-10-20 |
| **Origen** | `00-ControlCalidad-PQ` · CC #7 · 19/10/2026 · ítem mail ABM Usuarios |

---

## 1) In scope / out

**In:** acción Notificar en wrapper GEN-06 (alta, edición, consulta si hubiere); `POST` notify-access; `PasswordPolicy`; Mail Engine; `firstLogin`; i18n; tests.

**Out:** fork `UsuariosAdminPage`; SMTP propio; native; mail masivo (TR-005-update).

---

## 2) AC de implementación

| AC | Verificación |
|----|--------------|
| AC-CC7-U01 | UI acción + testid `adminUsuariosNotify` |
| AC-CC7-U02 | Disabled sin email; visible/habilitada en alta persistida, edición y consulta si existe |
| AC-CC7-U03 | Generador + persistencia + `firstLogin` |
| AC-CC7-U04 | Mail asunto/cuerpo (fake) |
| AC-CC7-U05 | Login siguiente → change-password |
| AC-CC7-U06 | Error en contexto (regla 36) |
| AC-CC7-U07 | Wrapper sigue siendo SDK; sin SMTP host |

---

## 3) Contrato técnico

| ID | Implementación |
|----|----------------|
| RN-CC7-U01 | `POST /api/v1/admin/usuarios/{id}/notify-access` autenticado, permiso admin usuarios. Body vacío. Envelope GEN-20. |
| RN-CC7-U02 | Generar password con `PasswordPolicy` (params GEN simple/segura + longitud). |
| RN-CC7-U03 | Persistir hash; `firstLogin = true`. No devolver password en `resultado`. |
| RN-CC7-U04 | Mail Engine GEN; claves i18n `partes.notifyAccess.subject` / `.body*` (host) o familia GEN si existiera. |
| RN-CC7-U05 | Asunto: `{empresaCodigo} - {t('partes.notifyAccess.subjectSuffix')}`. |
| RN-CC7-U06 | URL = config pública SPA (`FRONTEND_URL` / equivalente documentado en `.env.example`). |
| RN-CC7-U07 | 422 `admin.usuarios.notify.emailRequired` (o clave envelope acordada) si no hay email. |
| RN-CC7-U08 | UI: prop/slot SDK si existe; si no, toolbar host al lado del wrapper **sin** copiar GEN-06. Gap SDK en TR al implementar. Misma acción en alta, edición y consulta (si el SDK o el host exponen vista RO). |
| RN-CC7-U09 | Excluido mobile. |

**SP:** si el ABM users ya va por SP laravel-core, reutilizar update de password/`firstLogin`. Si el path actual es excepción auth (Eloquent users), no abrir SQL ad-hoc de dominio Partes.

---

## 4) Tests

- Backend: feature notify (fake mailer), 422 sin email, política de clave.
- Vitest: habilitación del control; no fork de página.
- E2E: humo operador dispara notify (mail interceptado) **opcional** si el entorno de mail lo permite.

---

## 5) Tareas D (cuando se autorice Parte D)

1. Generador + endpoint + mail.
2. UI acción GEN-06.
3. i18n 5 locales.
4. Tests.
