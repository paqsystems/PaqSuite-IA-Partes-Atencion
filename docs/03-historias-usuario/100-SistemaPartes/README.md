# 100 – Sistema Partes (Historias de usuario)

Épica alineada a `docs/05-open-spec/100-SistemaPartes/`.

| HU | Título | SPEC | Estado |
|----|--------|------|--------|
| [HU-001](./HU-001-modelo-datos-modulo.md) | Modelo de datos del módulo | [SPEC-001](../../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) | Pendiente · [TR-001](../../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md) |
| [HU-002](./HU-002-identidad-funcional-y-acceso.md) | Identidad funcional y acceso | [SPEC-002](../../05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md) | Pendiente · [TR-002](../../04-tareas/100-SistemaPartes/TR-002-identidad-funcional-y-acceso.md) |
| [HU-003](./HU-003-maestros-y-catalogos.md) | Maestros y catálogos | [SPEC-003](../../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) | Pendiente · [TR-003](../../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md) |
| [HU-004](./HU-004-operacion-carga-diaria.md) | Operación / carga diaria | [SPEC-004](../../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) | Pendiente · [TR-004](../../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) |
| [HU-005](./HU-005-supervision-proceso-masivo.md) | Supervisión / proceso masivo | [SPEC-005](../../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) · [TR-005](../../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md) | F1 OK (obs.) |
| [HU-006](./HU-006-consultas-dashboard-navegacion.md) | Consultas, dashboard y navegación | [SPEC-006](../../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [TR-006](../../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) | Pendiente (C1 OK → D1) |
| [HU-007](./HU-007-mobile-capacitor.md) | Mobile Capacitor | [SPEC-007](../../05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md) · [TR-007](../../04-tareas/100-SistemaPartes/TR-007-mobile-capacitor.md) | Pendiente (C1 OK → D1) |

## Estrategia

1. **Parte B cerrada** (HU-001…007 + B1).
2. **Batch de ambigüedades residuales — cerrado a nivel funcional** (2026-07-30). Detalle técnico (nombres SP, claves param exactas, policy/tests, smoke CI) → **Parte C (TR)**.
3. Siguiente: **Parte C (TR)** por HU en orden 001→007.

## Preguntas abiertas residuales (batch)

Detalle en cada HU; resumen para la sesión de cierre:

| HU | Temas típicos (no inventar respuesta aún) |
|----|-------------------------------------------|
| 001 | **Cerrado** (funcional); SP/trigger nombres → TR |
| 002 | **Cerrado** (funcional); SP nombres → TR |
| 003 | **Cerrado** (funcional); SP nombres → TR |
| 004 | **Cerrado** (funcional); SP/claves param exactas → TR |
| 005 | **Cerrado** (funcional); SP/clave param tope → TR |
| 006 | **Cerrado** (funcional); claves param / SP → TR |
| 007 | **Cerrado** (funcional); policy rutas + smoke CI → TR |
