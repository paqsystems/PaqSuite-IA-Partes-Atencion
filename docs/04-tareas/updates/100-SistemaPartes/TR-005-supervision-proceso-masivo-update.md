# TR-005-update – Informar novedades a clientes (proceso masivo)

Jira: PIAPARTES-45

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-005-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-005-supervision-proceso-masivo-update.md) |
| **SPEC relacionada** | [SPEC-005-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-005-supervision-proceso-masivo-update.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Estado** | Pendiente |
| **Última actualización** | 2026-10-20 |
| **Origen** | `00-ControlCalidad-PQ` · CC #7 · 19/10/2026 · ítem mail novedades masivo |

---

## 1) In / out

**In:** ícono toolbar; POST informar-clientes; SP/JOIN de destinatarios; Mail Engine; i18n; tests.

**Out:** auditoría Excel; GEN-15; SMTP host; native; password en mail.

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

---

## 3) Contrato

| ID | Implementación |
|----|----------------|
| RN-CC7-M01 | `POST /api/v1/partes/masivo/informar-clientes` query/body de filtros igual al listado (fecha desde/hasta obligatorias). |
| RN-CC7-M02 | Recolección **en servidor** vía SP (p. ej. `pq_sp_partes_masivo_destinatarios_novedad`). JOIN clientes del universo filtrado `es_tarea = 1`. |
| RN-CC7-M03 | Deduplicar `user_id`. Omitir sin email / inhabilitado. |
| RN-CC7-M04 | Mail Engine GEN; i18n `partes.masivo.notify.*`. |
| RN-CC7-M05 | Confirmación FE con N antes del POST. |
| RN-CC7-M06 | Tope: reutilizar `PartesMasivoMaxIds` sobre cantidad de **destinatarios** o param `PartesMasivoNotifyMax`; 422 si excede. |
| RN-CC7-M07 | UI: `Button` ícono mail en toolbar del masivo (no DataGrid crudo nuevo). |
| RN-CC7-M08 | Excluido `isNativeApp()`. |

---

## 4) Tests

- Feature supervisor OK + fake mailer; 403; 0 destinatarios; tope.
- Unit mapper destinatarios.
- Vitest: ícono/testid; disabled nativo.
- E2E humo: login supervisor → masivo → ícono visible (envío opcional).

---

## 5) Tareas D

1. SP + endpoint.
2. Toolbar + confirmación.
3. i18n 5 locales.
4. Tests.
