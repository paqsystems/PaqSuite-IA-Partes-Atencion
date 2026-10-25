# HU-002-update – Notificación de acceso (ABM Usuarios)

Jira: PIAPARTES-44

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-002-update |
| Título | Enviar mail de ingreso al sitio desde ABM Usuarios |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-10-20 |
| SPEC origen | [SPEC-002-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso-update.md) |
| SPEC base | [SPEC-002](../../../05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md) |
| TR relacionada(s) | [TR-002-update](../../../04-tareas/updates/100-SistemaPartes/TR-002-identidad-funcional-y-acceso-update.md) |
| Origen | `00-ControlCalidad-PQ` · CC #7 · 19/10/2026 · ítem «Envio mail a usuario para acceder al sitio» |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

---

## Narrativa

Como operador de seguridad (ABM Usuarios)  
quiero notificar por mail a un usuario con el enlace del sitio y credenciales iniciales  
para que pueda entrar a Partes de Atención y deba cambiar la clave en el primer acceso.

---

## Alcance incluido

- Acción **Notificar** en ABM Usuarios (GEN-06, sin fork de pantalla), en **alta**, **edición** y **consulta** (si hubiere).
- Habilitar cuando hay email; enviar solo si el usuario está persistido.
- Password aleatoria según política GEN; `firstLogin = true`.
- Mail: asunto con código de empresa + ingreso al sistema de Partes; cuerpo con propósito, URL y usuario/contraseña.
- Mail Engine GEN; i18n; testids.

## Fuera de alcance

- Fork GEN-06; SMTP propio; mobile admin; mail de novedades del masivo (HU-005-update).

---

## Criterios de aceptación

- [ ] **CA-CC7-U01** Existe acción Notificación (`adminUsuariosNotify`) en ABM Usuarios.
- [ ] **CA-CC7-U02** Sin email la acción está deshabilitada; con email, habilitada en alta persistida, edición y consulta (si existe).
- [ ] **CA-CC7-U03** Al notificar se genera clave que cumple la política parametrizada, se persiste y `firstLogin` queda en true.
- [ ] **CA-CC7-U04** El mail incluye código de empresa en el asunto, URL clicable, usuario y contraseña.
- [ ] **CA-CC7-U05** El próximo login con esa cuenta obliga cambio de clave (GEN-04, ya pactado).
- [ ] **CA-CC7-U06** Fallo de mail o de validación se muestra en el contexto de la acción (no solo detrás del overlay).
- [ ] **CA-CC7-U07** No hay pantalla/motor de usuarios duplicado ni SMTP host paralelo.
