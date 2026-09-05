# 100 – Sistema Partes (Tareas técnicas)

Épica alineada a `docs/05-open-spec/100-SistemaPartes/` y `docs/03-historias-usuario/100-SistemaPartes/`.

| TR | Título | HU | SPEC | Estado |
|----|--------|----|------|--------|
| [TR-001](./TR-001-modelo-datos-modulo.md) | Modelo de datos del módulo | [HU-001](../../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) | [SPEC-001](../../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) | D hecho (F1 pendiente) |
| [TR-002](./TR-002-identidad-funcional-y-acceso.md) | Identidad funcional y acceso | [HU-002](../../03-historias-usuario/100-SistemaPartes/HU-002-identidad-funcional-y-acceso.md) | [SPEC-002](../../05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md) | D hecho (F1 pendiente) |
| [TR-003](./TR-003-maestros-y-catalogos.md) | Maestros y catálogos | [HU-003](../../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) | [SPEC-003](../../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) | D hecho (F1 pendiente) |
| [TR-004](./TR-004-operacion-carga-diaria.md) | Operación / carga diaria | [HU-004](../../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) | [SPEC-004](../../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) | D hecho (F1 pendiente) |
| [TR-005](./TR-005-supervision-proceso-masivo.md) | Supervisión / proceso masivo | [HU-005](../../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) | [SPEC-005](../../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) | F1 OK (obs.) |
| [TR-006](./TR-006-consultas-dashboard-navegacion.md) | Consultas, dashboard y navegación | [HU-006](../../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) | [SPEC-006](../../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) | D hecho (F1 pendiente) |
| [TR-007](./TR-007-mobile-capacitor.md) | Mobile Capacitor | [HU-007](../../03-historias-usuario/100-SistemaPartes/HU-007-mobile-capacitor.md) | [SPEC-007](../../05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md) | D hecho (F1 + Capacitor scaffold pendientes) |
| [TR-008](./TR-008-asistente-ia-chat-documental.md) | Asistente IA — chat documental | [HU-008](../../03-historias-usuario/100-SistemaPartes/HU-008-asistente-ia-chat-documental.md) | [SPEC-008](../../05-open-spec/100-SistemaPartes/SPEC-008-asistente-ia-chat-documental.md) | Pendiente de Revisión (F1 2026-08-01) |
| [TR-009](./TR-009-importacion-partes-excel.md) | Importación Excel (GEN-14) en Carga diaria | [HU-009](../../03-historias-usuario/100-SistemaPartes/HU-009-importacion-partes-excel.md) | [SPEC-009](../../05-open-spec/100-SistemaPartes/SPEC-009-importacion-partes-excel.md) | E hecho (F1 pendiente) |
| [TR-010](./TR-010-smart-capture-carga-diaria.md) | Smart Capture en Carga diaria (GEN-03) | [HU-010](../../03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md) | [SPEC-010](../../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) | Pendiente (C+C1) |
| [TR-011](./TR-011-reportes-emisiones.md) | Reportes / emisiones (GEN-15) en Consulta detallada | [HU-011](../../03-historias-usuario/100-SistemaPartes/HU-011-reportes-emisiones.md) | [SPEC-011](../../05-open-spec/100-SistemaPartes/SPEC-011-reportes-emisiones.md) | Pendiente (C+C1 2026-08-25; D1 listo) |

## Estrategia

1. **Parte C + C1** TR-001…007 — **completado** (2026-07-30); **TR-008** — **2026-08-01**; **TR-009** — **2026-08-02** (C+C1); **TR-011** — **2026-08-25** (C+C1).
2. Batch de ambigüedades HU ya cerrado (2026-07-30); observaciones B1 de HU-009 cerradas en TR-009.
3. **D1:** planes en [`d1/`](./d1/) (TR-002…011). **D1-TR-011** listo (2026-08-25). Siguiente: **D (implementar TR-011)** (autorización).
4. **D 001→007:** implementado (2026-07-30). **TR-008:** D/E/F1 cerrados. **TR-009:** D implementado (2026-08-02); E/F1 pendientes.
