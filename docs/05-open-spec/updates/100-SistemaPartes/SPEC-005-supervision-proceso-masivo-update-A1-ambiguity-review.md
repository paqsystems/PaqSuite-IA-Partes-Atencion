# Revisión de ambigüedad - SPEC-005-update — Informar clientes (proceso masivo)

| Campo | Valor |
|-------|--------|
| **SPEC** | [`SPEC-005-supervision-proceso-masivo-update.md`](./SPEC-005-supervision-proceso-masivo-update.md) |
| **Paso** | A1 (cierre de preguntas 20/10/2026) |
| **Fecha** | 2026-10-20 |
| **Origen** | CC-PQ #7 19/10/2026 |

## Resultado general

- Estado: **Apto**

Destinatarios = usuarios de **clientes** de **todo lo que deje el filtro**. Sin distribuidor. Sin filtro extra por `cerrado`. Motor Mail Engine GEN.

## Ambigüedades críticas

- Ninguna.

## Ambigüedades menores

- **Ícono deshabilitado vs 422** si no hay destinatarios: TR elige (recomendación: disable UI + 422 si llaman igual).
- **Fallo parcial de mail:** se reporta OK/error; sin reintento automático.
- **Tope:** `PartesMasivoMaxIds` vs param propio; lo decide la TR.

## Supuestos detectados

- Universo = resultado filtrado **completo** (todas las páginas), no solo la página visible.
- No se envía a asistentes (salvo el mismo `user_id` del cliente).
- «Código de usuario» = `users.usuario`.

## Preguntas para decisión humana

1. ¿Solo partes cerradas o el filtro tal cual? — **cerrada 20/10/2026: todo lo que deje el filtro, por el momento.**
2. Un mail por `users.id` si el mismo usuario aparece en varios clientes — **pactado** (deduplicar).

## Recomendaciones de ajuste del SPEC

- Aplicado: universo = filtro completo; sin cerrado implícito; sin distribuidor.

## Veredicto

- Puede pasar a HU: **Sí**
