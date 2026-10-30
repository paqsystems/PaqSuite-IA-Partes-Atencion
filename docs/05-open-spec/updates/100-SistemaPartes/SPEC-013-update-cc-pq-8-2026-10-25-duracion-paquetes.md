# SPEC-013-update — CC PQ #8 (25/10/2026) — Duración paquetes 1–240 h

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-013-update-cc-pq-8-2026-10-25-duracion-paquetes |
| **SPEC base** | [SPEC-013-carga-paquetes-horas](../../100-SistemaPartes/SPEC-013-carga-paquetes-horas.md) |
| **Estado** | Pendiente |
| **Última actualización** | 2026-10-30 |
| **Origen** | `00-ControlCalidad-PQ` · CC **#8** · 25/10/2026 · [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52) |
| **HU-update** | [HU-013-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) |
| **TR-update** | [TR-013-update](../../../04-tareas/updates/100-SistemaPartes/TR-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) |

## Delta

- **Duración de carga de paquetes:** horas enteras **de 1 a 240 inclusive** (reemplaza piso **8** horas del SPEC-013 §2.1 / §4.3).
- Persistencia: minutos `60` … `14400` (`duracion_minutos`).
- Alinear validación UI (NumberBox/spin), API/SP `pq_sp_partes_paquete_*`, Smart Capture paquete, i18n (`partes.paquete.validation.durationRange`) y tests (CA-01, CA-02 CC #8).

## Fuera de alcance

- Informe Paquete de Horas (SPEC-006-update hermano).
- Cambio de tramo carga diaria (SPEC-004).

## Criterios verificables

- [ ] Acepta 1 h y 240 h; rechaza 0, 241 y decimales.
- [ ] Mismo rango en SC modal paquete y mensajes i18n ES/EN.
