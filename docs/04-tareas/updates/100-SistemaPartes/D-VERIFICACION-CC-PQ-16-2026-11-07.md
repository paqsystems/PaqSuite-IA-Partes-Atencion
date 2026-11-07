# Verificación F — CC PQ #16 (07/11/2026)

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#16** |
| **Parte E** | [E-CC-PQ-16-2026-11-07.md](./E-CC-PQ-16-2026-11-07.md) |
| **TR** | [TR-002-update-02](./TR-002-identidad-funcional-y-acceso-update-02.md) |
| **Veredicto F** | **Apto** — regresión automatizada OK; smoke manual preview **no ejecutado** en esta sesión |
| **F1/F** | **Aprobado con observaciones** 07/11/2026 |
| **Parte I** | Pendiente de merge/deploy (sin PR en esta sesión) |

---

# Verificación del agente — TR-002-update-02

## Resultado

- **Aprobado con observaciones**

## Evidencia revisada

- SPEC/HU/TR-002-update-02 vs código host (`PartesUsuariosFormPopup`, wrapper `PartesUsuariosAdminPage`, `UsuariosController::store`).
- Retiro `NotifyUsuarioAccessToolbar` / diálogo masivo.
- OpenAPI create sin password obligatorio (Parte D).
- Salida de tests E (Vitest, Playwright, PHPUnit).

## Hallazgos críticos

- Ninguno.

## Advertencias

1. **CA-16-02 (alta):** el ícono `adminUsuariosNotifyEmail` se muestra solo en **edición**; en **alta** el envío es automático al guardar (CA-16-04). Coherente con el objetivo del CC #16 pero difiere del wording literal «ícono en alta y edición».
2. **Deuda Framework:** formulario host mínimo (`PartesUsuariosFormPopup`) en lugar de props SDK en `UsuariosFormPopup` — documentado en TR §RN-CC16-SDK.
3. **E2E integración** (`PW_INTEGRATION=1`) no corrida en F1.
4. **Smoke manual** en preview `partesatencion-dev` no corrido en F1.
5. **Manual usuario** SPEC-002: sin actualización en esta oleada (opcional post-deploy).

## Sugerencias

- Tras merge: smoke manual alta+edición en preview con tenant real.
- Épica Framework: props GEN-06 para email suffix y ocultar password en create.
- Limpiar claves i18n obsoletas del toolbar masivo (`partes.notifyAccess.open`, etc.) en un CC menor si ya no hay referencias.

## Tests

- Comandos: ver [E-CC-PQ-16](./E-CC-PQ-16-2026-11-07.md).
- Resultado: Vitest 13/13 · Playwright 6/6 · PHPUnit 8/8.

## Pendientes

- PR → `develop` (Parte N).
- Smoke preview operador (recomendado antes de prod).
- Parte **I** unificación SPEC-002 base cuando el usuario cierre la épica notify.

## Recomendación final

Aprobar cierre F1/F del CC #16 sobre base de tests automatizados; validar preview manual antes de marcar prod OK.

---

## 1) Criterios de aceptación

| CA | Resultado F | Evidencia |
|----|-------------|-----------|
| **CA-16-01** Sin toolbar/diálogo masivo | **OK** | Código + Vitest wrapper |
| **CA-16-02** Ícono junto a Email | **OK** (obs. alta) | Edición: E2E + Vitest; alta: N/A (auto-notify) |
| **CA-16-03** Alta sin contraseña manual | **OK** | E2E + PHPUnit policy |
| **CA-16-04** Auto-notify post-alta | **OK** | E2E + `form-error` mock |
| **CA-16-05** Reenvío en edición | **OK** | E2E |
| **CA-16-06** i18n + regla 36 + regresión | **OK** | 5 locales + PHPUnit notify |

---

## 1.1) F1 — revisión código ↔ TR (07/11/2026)

| Pieza | Evidencia | Estado |
|-------|-----------|--------|
| Retiro masivo | Sin `NotifyUsuarioAccessToolbar` / dialog masivo | **OK** |
| Form popup | `partesUsuariosFormPopup.tsx` — ícono edición, notify post-create | **OK** |
| Wrapper listado | `partesUsuariosAdminPage.tsx` montado desde `UsuariosAdminPage.tsx` | **OK** |
| BE create | `UsuariosController::store` — password opcional + generador | **OK** |
| Notify | `notifyAccess` sin cambio; regresión tests | **OK** |
| Tests | [E-CC-PQ-16](./E-CC-PQ-16-2026-11-07.md) | **OK** |

**Veredicto F1:** **Aprobado con observaciones** (ícono solo en edición; deuda SDK; sin smoke preview).

---

## 2) Regresión automatizada

| Capa | Resultado |
|------|-----------|
| Vitest `admin/security/` | **OK** (13) |
| Playwright CC #16 + form-error | **OK** (6) |
| PHPUnit usuarios + notify + auto password | **OK** (8) |
| Playwright integración `PW_INTEGRATION=1` | **No corrido** |

---

## 3) Smoke manual UI

**No ejecutado** en F1 (07/11/2026). Checklist recomendado post-deploy:

1. `/admin/usuarios?cliente=…` — sin botón global «Notificar acceso».
2. **Alta** con email — sin campo contraseña; tras guardar, mail o error en modal.
3. **Edición** usuario con email — ícono junto a mail → reenvío.
4. Usuario sin email — ícono deshabilitado en edición.

---

## 4) Deploy

Sin migraciones nuevas. Verificar Mail Engine en preview/prod para CA-16-04/05 en entorno real.
