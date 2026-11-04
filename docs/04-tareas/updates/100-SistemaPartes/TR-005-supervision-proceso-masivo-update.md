# TR-005-update – Informar novedades a clientes (proceso masivo)

Jira: [PIAPARTES-45](https://paqsystems.atlassian.net/browse/PIAPARTES-45)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-005-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-005-supervision-proceso-masivo-update.md) · [PIAPARTES-57](https://paqsystems.atlassian.net/browse/PIAPARTES-57) |
| **SPEC relacionada** | [SPEC-005-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-005-supervision-proceso-masivo-update.md) · [PIAPARTES-56](https://paqsystems.atlassian.net/browse/PIAPARTES-56) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-11-03 |
| **Origen** | `00-ControlCalidad-PQ` · CC #7 · 19/10/2026 · CC #14 · 03/11/2026 |

---

## 1) In / out

**In:** ícono toolbar; POST informar-clientes; SP/JOIN de destinatarios; Mail Engine; i18n por `users.locale`; URL tenant; guard salida; tests.

**Out:** auditoría Excel; GEN-15; SMTP host; native; password en mail; editor plantillas mail.

---

## 2) AC

| AC | Verificación |
|----|--------------|
| AC-CC7-M01 | Toolbar ícono + tooltip + testid |
| AC-CC7-M02 | Filtros = mismo contrato que `GET /partes/tareas` |
| AC-CC7-M03 | Destinatarios únicos cliente.user_id + email |
| AC-CC7-M04 | Mail asunto/cuerpo (fake) con período |
| AC-CC7-M05 | Sin password |
| AC-CC7-M06 | 422/clave i18n si 0 destinatarios |
| AC-CC7-M07 | 403 no supervisor |
| AC-CC7-M08 | SQL sin `whereIn` de N incierto (JOIN/EXISTS) |
| AC-CC14-M01 | Mail con empresa emisora + URL `https://{tenant}.partesatencion.paqsystems.com` |
| AC-CC14-M02 | Render por destinatario según `users.locale` |
| AC-CC14-M03 | `beforeunload` + blocker router; diálogo enviar mails |
| AC-CC14-M04 | Claves `partes.masivo.mailInformarClientes.*` en 5 JSON |

---

## 3) Contrato

| ID | Implementación |
|----|----------------|
| RN-CC7-M01 | `POST /api/v1/partes/masivo/informar-clientes` query/body de filtros igual al listado (fecha desde/hasta obligatorias). |
| RN-CC7-M02 | Recolección **en servidor** vía SP (p. ej. `pq_sp_partes_masivo_destinatarios_novedad`). JOIN clientes del universo filtrado `es_tarea = 1`. |
| RN-CC7-M03 | Deduplicar `user_id`. Omitir sin email / inhabilitado. |
| RN-CC7-M04 | Mail Engine GEN; plantilla v1 vía `partes.masivo.mailInformarClientes.*` (asunto/cuerpo HTML); locale = `normalizeLocale(users.locale)` por destinatario. |
| RN-CC14-M01 | URL acceso: `https://{tenant}.partesatencion.paqsystems.com` con `{tenant}` = código cliente instalación (header/sesión); incluir `users.codigo` en cuerpo. |
| RN-CC14-M02 | Empresa emisora: razón social / nombre desde catálogo empresa MONO (SP o sesión; TR documenta fuente). |
| RN-CC14-M03 | FE: flag `sessionDirty` masivo (ediciones no guardadas); al navegar/`beforeunload`, `confirm` i18n `partes.masivo.leaveConfirmInformarClientes` con acciones Enviar / Salir sin enviar / Cancelar. |
| RN-CC7-M05 | Confirmación FE con N antes del POST. |
| RN-CC7-M06 | Tope: reutilizar `PartesMasivoMaxIds` sobre cantidad de **destinatarios** o param `PartesMasivoNotifyMax`; 422 si excede. |
| RN-CC7-M07 | UI: `Button` ícono mail en toolbar del masivo (no DataGrid crudo nuevo). |
| RN-CC7-M08 | Excluido `isNativeApp()`. |

---

## 4) Tests

Plan exhaustivo: [D-VERIFICACION-005-update-masivo-informar-clientes.md](./D-VERIFICACION-005-update-masivo-informar-clientes.md).

- Feature supervisor OK + fake mailer; 403; 0 destinatarios; tope.
- Unit `PartesMasivoLocaleTest`; Vitest `masivoSessionDirty`, catálogo emisiones menú.
- E2E: `partes-masivo-selection.spec.ts` (ícono mail); `partes-emisiones.spec.ts` (Emitir por menú + diseñador).
- Smoke emisión vacía: `EmissionEmptyReportPerProcessTest` + `scripts/dev/smoke-emissions-empty-report.php`.

---

## 5) Tareas D

1. SP + endpoint + render mail por locale.
2. Toolbar + confirmación + guard salida.
3. i18n 5 locales (`mailInformarClientes`, diálogos guard).
4. Tests.
