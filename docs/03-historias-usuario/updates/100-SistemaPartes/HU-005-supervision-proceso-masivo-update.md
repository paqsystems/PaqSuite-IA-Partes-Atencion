# HU-005-update – Informar novedades a clientes (proceso masivo)

Jira: PIAPARTES-45

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-005-update |
| Título | Informar por mail a clientes desde Proceso masivo |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-10-20 |
| SPEC origen | [SPEC-005-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-005-supervision-proceso-masivo-update.md) |
| TR relacionada(s) | [TR-005-update](../../../04-tareas/updates/100-SistemaPartes/TR-005-supervision-proceso-masivo-update.md) |
| Origen | `00-ControlCalidad-PQ` · CC #7 · 19/10/2026 · ítem mail novedades en Partes masivos |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

---

## Narrativa

Como supervisor  
quiero informar por mail a los clientes (con usuario) que aparecen en el proceso masivo filtrado  
para avisarles que hay novedades de atención en el período, con el enlace y su usuario, sin enviar la contraseña.

---

## Alcance incluido

- Ícono mail + tooltip «Informar a clientes» en Proceso masivo.
- Destinatarios: usuarios únicos de clientes del resultado filtrado con email.
- Mail: asunto con código empresa; cuerpo con período, URL, usuario; forgot si no recuerda clave.
- Confirmación con N; Mail Engine GEN.

## Fuera de alcance

- Auditoría Excel + mails selectivos.
- GEN-15 / PDF.
- Mobile.
- Password en el mail (eso es HU-002-update).

---

## Criterios de aceptación

- [ ] **CA-CC7-M01** Ícono mail visible al supervisor en masivo; tooltip Informar a clientes; testid `partesMasivoInformarClientes`.
- [ ] **CA-CC7-M02** El envío usa **todo** el universo que deja el filtro (todas las páginas; no requiere tildes; no filtra extra por cerrado).
- [ ] **CA-CC7-M03** Solo clientes con usuario usable + email; destinatarios únicos.
- [ ] **CA-CC7-M04** Asunto y cuerpo según SPEC-005-update (período = filtros desde/hasta).
- [ ] **CA-CC7-M05** No incluye contraseña; menciona recupero en login.
- [ ] **CA-CC7-M06** Sin destinatarios → no envía; mensaje i18n.
- [ ] **CA-CC7-M07** Asistente no supervisor no ve / 403.
