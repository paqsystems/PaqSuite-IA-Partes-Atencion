# Planes D1 — 100 Sistema Partes

Planes de implementación (parte D1) antes de ejecutar D.

| Plan | TR | Estado deps |
|------|-----|-------------|
| [D1-TR-002](./D1-TR-002-identidad.md) | Identidad | TR-001 **hecho** → **D hecho** |
| [D1-TR-003](./D1-TR-003-maestros.md) | Maestros | Bloquea: TR-002 |
| [D1-TR-004](./D1-TR-004-carga-diaria.md) | Carga diaria | Bloquea: TR-002 + TR-003 |
| [D1-TR-005](./D1-TR-005-masivo.md) | Masivo | F1 OK (obs.); Must+Should |
| [D1-TR-006](./D1-TR-006-consultas-dashboard.md) | Consultas/dashboard | Bloquea: TR-002…005 |
| [D1-TR-007](./D1-TR-007-mobile.md) | Mobile | Bloquea: TR-002…006 + scaffold Capacitor |

**Decisiones D1 absorbidas (2026-07-30):**
- Masivo: tope técnico **5000** si `PartesMasivoMaxIds=0` → 422 `partes.masivo.loteDemasiadoGrande`
- Dashboard post-login producto = `/partes`
- Paquete horas: orquestación PHP Must; Chart DX `bar`
- Carga: `Programa=Partes` para params GRAL
