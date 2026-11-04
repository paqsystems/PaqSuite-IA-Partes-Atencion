# HU-005-update – Informar novedades a clientes (proceso masivo)

Jira: [PIAPARTES-57](https://paqsystems.atlassian.net/browse/PIAPARTES-57)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-005-update |
| Título | Informar por mail a clientes desde Proceso masivo |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Especificado |
| Última actualización | 2026-11-03 |
| SPEC origen | [SPEC-005-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-005-supervision-proceso-masivo-update.md) · [PIAPARTES-56](https://paqsystems.atlassian.net/browse/PIAPARTES-56) |
| TR relacionada(s) | [TR-005-update](../../../04-tareas/updates/100-SistemaPartes/TR-005-supervision-proceso-masivo-update.md) · [PIAPARTES-45](https://paqsystems.atlassian.net/browse/PIAPARTES-45) |
| Origen | `00-ControlCalidad-PQ` · CC #7 · 19/10/2026 · CC #15 · 04/11/2026 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Especificado |

---

## Narrativa

Como supervisor  
quiero informar por mail a los clientes (con usuario) que aparecen en el proceso masivo filtrado  
para que sepan que la empresa registró asistencias a su entidad, puedan revisarlas en el sitio y realizar observaciones, con el enlace, su código de usuario e idioma del destinatario, sin enviar la contraseña.

---

## Alcance incluido

- Ícono mail + tooltip «Informar a clientes» en Proceso masivo.
- Destinatarios: usuarios únicos de clientes del resultado filtrado con email.
- Mail: asunto con empresa emisora; cuerpo v1 hardcode i18n con período, URL `https://{tenant}.partesatencion.paqsystems.com`, `users.codigo`; locale = `users.locale`; forgot si no recuerda clave.
- Confirmación con N destinatarios y rango de fechas; Mail Engine GEN.
- Guard al salir con cambios pendientes: ofrecer enviar mails a clientes.

## Fuera de alcance

- Auditoría Excel + mails selectivos.
- GEN-15 / PDF.
- Mobile.
- Password en el mail (eso es HU-002-update).
- Editor de plantillas de correo (futuro).

---

## Criterios de aceptación

- [ ] **CA-CC7-M01** Ícono mail visible al supervisor en masivo; tooltip Informar a clientes; testid `partesMasivoInformarClientes`.
- [ ] **CA-CC7-M02** El envío usa **todo** el universo que deja el filtro (todas las páginas; no requiere tildes; no filtra extra por cerrado).
- [ ] **CA-CC7-M03** Solo clientes con usuario usable + email; destinatarios únicos.
- [ ] **CA-CC7-M04** Asunto y cuerpo según SPEC-005-update (período = filtros desde/hasta).
- [ ] **CA-CC7-M05** No incluye contraseña; menciona recupero en login.
- [ ] **CA-CC7-M06** Sin destinatarios → no envía; mensaje i18n.
- [ ] **CA-CC7-M07** Asistente no supervisor no ve / 403.
- [ ] **CA-CC15-M01** Cuerpo incluye razón social / nombre empresa emisora (CC #15).
- [ ] **CA-CC15-M02** URL canónica tenant + código usuario; mail en idioma del destinatario (5 locales).
- [ ] **CA-CC15-M03** Al abandonar pantalla con cambios sin persistir, diálogo ofrece enviar mails a clientes.
- [ ] **CA-CC15-M04** Plantillas v1 fijas i18n; sin ABM de plantillas.
