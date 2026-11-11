# Revisión de ambigüedad — SPEC-013-update — CC PQ #8 (duración 1–240 h)

| Campo | Valor |
|-------|--------|
| **SPEC** | Fusionado Parte I 06/11/2026 → [`SPEC-013-carga-paquetes-horas.md`](../../100-SistemaPartes/SPEC-013-carga-paquetes-horas.md) (archivo update eliminado) |
| **Paso** | A1 |
| **Fecha** | 2026-10-30 |
| **Estado A1** | **Cerrado** |

## Resultado general

- **Apto** — delta acotado; hereda contexto SPEC-013 base (A1 2026-10-20).

## Decisiones cerradas (PQ)

| ID | Tema | Decisión |
|----|------|----------|
| **Q1** | Rango | **1–240** h enteras (60–14400 min). |
| **Q2** | Default en alta | **1 h** (mínimo permitido). |
| **Q3** | Capa validación | UI + API + **`pq_sp_partes_paquete_upsert`** + SC (regla 74). |

## Veredicto

- Puede pasar a **HU (Parte B):** **Sí**
