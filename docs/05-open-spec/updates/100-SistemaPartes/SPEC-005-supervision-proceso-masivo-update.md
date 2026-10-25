# SPEC-005-update – Informar novedades a clientes (proceso masivo)

Jira: PIAPARTES-45

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-005-update |
| Título | Mail de novedades a clientes desde Proceso masivo |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-10-20 |
| SPEC base | [SPEC-005-supervision-proceso-masivo](../../100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) |
| HU relacionada(s) | [HU-005-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-005-supervision-proceso-masivo-update.md) |
| TR relacionada(s) | [TR-005-update](../../../04-tareas/updates/100-SistemaPartes/TR-005-supervision-proceso-masivo-update.md) |
| Origen | `00-ControlCalidad-PQ` · CC #7 · 19/10/2026 · ítem «Envio mail a clientes para informar existencia de novedades» |
| A1 | [Apto con observaciones](./SPEC-005-supervision-proceso-masivo-update-A1-ambiguity-review.md) |
| Depende de | SPEC-005 (filtros/grilla) · SPEC-003 (`PQ_PARTES_CLIENTES.user_id`) · Mail Engine GEN / futuro GEN-30 |

---

## 1. Resumen ejecutivo

- **Problema:** el supervisor necesita avisar a los clientes que hay partes de atención en el período filtrado, sin emitir un PDF (eso es GEN-15).
- **Resultado esperado:** en **Proceso masivo**, un ícono de mail con tooltip «Informar a clientes» envía un aviso a los usuarios recolectados del universo **actualmente listado** (mismos filtros de la grilla).

Este mail **no** es la línea de «auditoría Excel + mails selectivos» de `07-fuera-de-alcance-y-evolucion.md` (esa sigue fuera). **Sí** entra al proceso masivo.

---

## 2. Alcance

### 2.1 En alcance

- Ícono mail en toolbar del proceso masivo (web, solo supervisor); tooltip i18n `partes.masivo.informarClientes`.
- Universo: **todo lo que deje el filtro** del proceso masivo (decisión A1 20/10/2026). No se exige tildado. No se agrega filtro implícito por `cerrado` / «aprobado». Solo `es_tarea = true` (igual que el masivo). Incluye **todas las páginas** del resultado filtrado, no solo la página visible.
- Destinatarios: usuarios distintos asociados a los **clientes** que aparecen en ese resultado (`PQ_PARTES_CLIENTES.user_id` usable: activo, no inhabilitado, con email).
- Deduplicar por `users.id` / email.
- Asunto: código de empresa + i18n tipo «Existen novedades de atención a su empresa».
- Cuerpo: hubo novedades de servicios en el **período filtrado** (fecha desde y hasta del proceso); recordar URL de acceso y **código de usuario**; indicar que si no recuerda la contraseña puede solicitar una nueva en el login (forgot GEN-04). **No** enviar contraseña en este mail.
- Motor: Mail Engine GEN; adoptar GEN-30 cuando esté listo, sin SMTP host.
- Confirmación previa con N destinatarios; envelope GEN-20; i18n; testids.
- Excluido mobile (masivo ya excluido).

### 2.2 Fuera de alcance

- Adjuntar PDF / emitir GEN-15 (Consulta detallada).
- Auditoría Excel + mails selectivos (evolución distinta).
- Enviar a asistentes (salvo que el mismo `users.id` sea el usuario del cliente).
- Cambio de atributos / cierre masivo (sigue SPEC-005 original).

---

## 3. Actores

| Actor | Puede informar |
|-------|----------------|
| Supervisor | Sí |
| Asistente no supervisor / cliente | No |

---

## 4. Comportamiento

1. Supervisor aplica filtros (periodo obligatorio, resto opcional) y ve la grilla.
2. Ícono mail habilitado si hay al menos un destinatario recolectable; si la grilla no tiene clientes con usuario+email, deshabilitado o 422 con clave i18n.
3. Confirmación: cantidad de mails a enviar (destinatarios únicos), rango desde–hasta.
4. Envío: un mail por destinatario único (o un lote Mail Engine); fallo parcial se reporta (cuántos OK / cuántos error) sin SMTP propio.
5. No altera `cerrado` ni atributos de tareas.

---

## 5. Criterios verificables

- [ ] Ícono mail + tooltip «Informar a clientes» + `data-testid` `partesMasivoInformarClientes`.
- [ ] Destinatarios = usuarios únicos de clientes presentes en **todo** el resultado filtrado, con email; sin extra filtro por cerrado.
- [ ] Asunto con código empresa; cuerpo con período, URL y usuario; sin password.
- [ ] Sin tildado obligatorio.
- [ ] Distinto de auditoría Excel+mails y de GEN-15.
- [ ] 5 locales; no mobile.

---

## 6. Impacto técnico

- `POST /api/v1/partes/masivo/informar-clientes` con los mismos query/filtros que el listado (periodo + opcionales). Recolección en servidor (no confiar en ids que mande el FE como única fuente; el FE puede mandar los mismos filtros).
- SP de lectura de clientes/usuarios del universo filtrado (MUST SP; no `whereIn` de N incierto desde PHP — JOIN/EXISTS).
- Tests: unit recolección; feature 403 no supervisor; feature 0 destinatarios; fake mailer.

---

## 7. Riesgos

- Volumen de mails: si el filtro es amplio, confirmar N; tope opcional análogo a `PartesMasivoMaxIds` (TR decide si reutiliza o param propio).

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-20 | Parte G CC-PQ #7 19/10/2026 |
| 2026-10-20 | Sin destinatarios «distribuidor» (atributo desactivado en esta versión). A1. |
| 2026-10-20 | Decisión humana: universo = **todo lo que deje el filtro**; sin filtro extra por cerrado. |
