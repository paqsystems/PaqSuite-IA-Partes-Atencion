# Revisión de ambigüedad - SPEC-002-update — Notificación de acceso (ABM Usuarios)

| Campo | Valor |
|-------|--------|
| **SPEC** | [`SPEC-002-identidad-funcional-y-acceso-update.md`](./SPEC-002-identidad-funcional-y-acceso-update.md) |
| **Paso** | A1 (cierre de preguntas 20/10/2026) |
| **Fecha** | 2026-10-20 |
| **Origen** | CC-PQ #7 19/10/2026 |

## Resultado general

- Estado: **Apto**

Trigger: ABM Usuarios, acción **Notificar** en **alta**, **edición** y **consulta** (si hubiere). Efecto: `PasswordPolicy` + `firstLogin` + mail. Motor: Mail Engine GEN, sin fork GEN-06.

## Ambigüedades críticas

- Ninguna.

## Ambigüedades menores

- **Momento del envío en alta:** Guardar y después Notificar, o un POST alta+notificar, si el usuario queda persistido antes del mail.
- **UI GEN-06:** slot/prop SDK vs toolbar host. Criterio: no fork.
- **Código de empresa / URL:** TR contra contrato empresas GEN y config SPA.

## Supuestos detectados

- En edición y consulta, Notificar **vuelve a generar** clave y marca `firstLogin` (mismo efecto que en alta).
- Copy del cuerpo igual para usuario interno o cliente.
- GEN-30 no listo: Mail Engine de forgot/reset.

## Preguntas para decisión humana

1. Notificar en **edición** y **consulta** — **cerrada 20/10/2026: sí**, además de alta; deshabilitada solo sin email.
2. Usuarios sin perfil Partes — el CC no restringe: ABM Usuarios = cualquier usuario con email.

## Recomendaciones de ajuste del SPEC

- Aplicado: alta + edición + consulta (si hubiere).

## Veredicto

- Puede pasar a HU: **Sí**
