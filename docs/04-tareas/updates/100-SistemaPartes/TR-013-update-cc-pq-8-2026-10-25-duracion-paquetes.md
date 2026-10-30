# TR-013-update — Duración paquetes 1–240 h (CC PQ #8)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-013-carga-paquetes-horas](../../100-SistemaPartes/TR-013-carga-paquetes-horas.md) |
| **HU-update** | [HU-013-update-cc-pq-8-2026-10-25-duracion-paquetes](../../../03-historias-usuario/updates/100-SistemaPartes/HU-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) |
| **SPEC-update** | [SPEC-013-update-cc-pq-8-2026-10-25-duracion-paquetes](../../../05-open-spec/updates/100-SistemaPartes/SPEC-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) |
| **Estado** | Pendiente |
| **Última actualización** | 2026-10-30 |

## 1) Delta

| Capa | Cambio |
|------|--------|
| FE `CargaPaquetesPage` / modal | `min=1`, `max=240`, step 1 hora |
| API validación | Request rules 1–240 enteros |
| SP `pq_sp_partes_paquete_upsert` | Validar rango minutos 60–14400 |
| SC paquete | Mismo rango en parser turno |
| i18n | Actualizar claves rango (5 locales) |
| Tests | Feature boundary 1/240; Vitest form; E2E humo opcional |

## 2) Tareas

| ID | Descripción |
|----|-------------|
| T1 | Backend validation + SP |
| T2 | FE + SC |
| T3 | i18n + OpenAPI si aplica |
| T4 | Tests |

## 3) Tests mínimos

- POST paquete 1 h OK; 0 h 422; 241 h 422.
- SC aplica 2 h dentro de rango.
