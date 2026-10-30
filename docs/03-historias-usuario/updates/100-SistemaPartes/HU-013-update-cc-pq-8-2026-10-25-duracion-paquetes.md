# HU-013-update — Duración paquetes 1–240 horas

Jira: [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52)

## Metadatos

| Campo | Valor |
|-------|-------|
| **ID** | HU-013-update |
| **Título** | Ampliar rango de duración en carga de paquetes de horas (mínimo 1 h) |
| **Épica / carpeta** | `100-SistemaPartes` |
| **Clasificación** | MUST-HAVE (CC PQ #8) |
| **Estado** | **Especificado** (Parte B + B1 2026-10-30) |
| **Última actualización** | 2026-10-30 |
| **HU base** | [HU-013-carga-paquetes-horas](../../100-SistemaPartes/HU-013-carga-paquetes-horas.md) |
| **SPEC origen** | [SPEC-013-update-cc-pq-8-2026-10-25-duracion-paquetes](../../../05-open-spec/updates/100-SistemaPartes/SPEC-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) |
| **TR relacionada** | [TR-013-update-cc-pq-8-2026-10-25-duracion-paquetes](../../../04-tareas/updates/100-SistemaPartes/TR-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) |
| **Origen** | CC PQ **#8** · 25/10/2026 |

---

## Trazabilidad SPEC

| Criterio / entregable SPEC-013-update | Cobertura en esta HU |
|--------------------------------------|----------------------|
| Rango 1–240 h §4.1, CA-01 | CA-01, CA-03, CA-04 |
| Consistencia capas §4.2, CA-02 | CA-02, CA-05, CA-06 |
| Default alta 1 h §4.2 | CA-07 |
| SP upsert §4.2, R-PH-DUR-03 | CA-05 |
| Edición paquetes &lt; 8 h post-fix R-PH-DUR-04 | CA-08 |
| Sin cambio informe §4.4 | Fuera de alcance |
| Producto `16-carga-paquetes-horas.md` | CA-09 |

---

## Narrativa

Como **supervisor**  
quiero registrar compras de paquete desde **1 hora** hasta **240 horas** enteras, con el mismo ABM y Smart Capture  
para reflejar operaciones reales de bajo volumen sin forzar un mínimo de 8 horas.

---

## Contexto funcional

SPEC-013 base fijaba mínimo **8 h** (`PAQUETE_MIN_HORAS = 8`). CC #8 corrige el piso a **1 h** manteniendo techo 240 h y persistencia en minutos múltiplos de 60. El informe Paquete de Horas (HU-006-update hermano) consume los mismos registros; no cambia la regla de signos.

**Precondiciones:** HU-013 operativo; clientes y tipo default disponibles.

---

## Alcance incluido

- **FE:** `partesPaqueteDuration.ts` — `PAQUETE_MIN_HORAS = 1`; `buildPaqueteDuracionOptions` de 1 a 240; **default en alta = 1 h** en modal.
- **Modal / grilla** Carga paquetes: validación coherente con helpers; mensajes i18n `partes.paquete.validation.*` (es, en, pt, fr, it).
- **API** `POST/PUT /api/v1/partes/paquetes`: rechazo **422** si `duracionMinutos` ∉ [60, 14400] o no múltiplo de 60.
- **SP** `pq_sp_partes_paquete_upsert` (y validaciones en list/get si aplican): misma regla en profundidad.
- **Smart Capture** paquete: parser y reply de error si horas fuera de rango; no aplicar al draft inválido.
- **Tests:** actualizar límites inferiores 8→1 en unit y Feature API; regresión 8–240 sigue válida.
- **Documentación producto** [`16-carga-paquetes-horas.md`](../../../02-producto/Sistema-Partes-IA/16-carga-paquetes-horas.md) — tramo 1–240 (Parte I al cerrar implementación).

---

## Fuera de alcance

- Informe Paquete de Horas (HU-006-update).
- Carga diaria tramos 15 min (HU-004).
- Decimales de horas en UI paquete.
- Mobile carga paquetes.
- Cambiar máximo 240 h o unidad de persistencia.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-PH-DUR-01 | Paquete Must aceptar **1 h** como mínimo (60 minutos). |
| R-PH-DUR-02 | Máximo **240 h** (14400 minutos) sin cambio. |
| R-PH-DUR-03 | Misma regla en UI, API, SP, Smart Capture e i18n. |
| R-PH-DUR-04 | Paquete ya guardado con duración &lt; 8 h Must editarse sin forzar rescale a 8 h. |
| R-PH-DUR-05 | Horas enteras únicamente; fracciones rechazadas en UI y API. |

---

## Criterios de aceptación

- [ ] **CA-01** Alta con **1 h** y **240 h** exitosa; **0 h**, **241 h** y valores no enteros en horas rechazados con i18n.
- [ ] **CA-02** Mismo límite en modal, API, SP upsert, Smart Capture y tests automatizados (CA-02 CC #8).
- [ ] **CA-03** Regresión: paquetes entre 8 h y 240 h siguen válidos sin cambio de comportamiento.
- [ ] **CA-04** OpenAPI / envelope de error usan claves `partes.paquete.*`, no prosa hardcoded.
- [ ] **CA-05** SP upsert rechaza minutos fuera de [60, 14400] o no múltiplo de 60 aunque API sea bypassed.
- [ ] **CA-06** Smart Capture propone 0,5 h o 300 h → mensaje SC sin persistir draft inválido.
- [ ] **CA-07** Modal de **alta** abre con duración preseleccionada **1 h**.
- [ ] **CA-08** Editar paquete existente de 2 h guarda sin exigir 8 h mínimo.
- [ ] **CA-09** Doc producto D-PH alineada a 1–240 h (verificación documental en cierre F).

---

## Escenarios Gherkin

```gherkin
Feature: Duracion paquetes 1 a 240 horas (CC PQ #8)

  Scenario: Alta paquete de 1 hora
    Given un supervisor en Carga de paquetes
    When completa alta con duracion 1 hora y datos obligatorios
    Then el paquete persiste con duracion_minutos 60
    And aparece en grilla con formato hh:mm

  Scenario: Rechazo por debajo del minimo
    Given un supervisor en modal de alta
    When intenta guardar equivalente a 0 horas
    Then la UI o API rechaza con mensaje i18n de rango
    And no hay fila nueva en base

  Scenario: Consistencia API y SP
    Given un request API con duracionMinutos 30
    When invoca upsert de paquete
    Then responde 422
    And el SP no deja registro invalido si se invocara directamente

  Scenario: Default en alta
    Given un supervisor abre modal de alta de paquete
    Then el selector de horas muestra 1 hora por defecto
```

---

## Supuestos explícitos

- No hay paquetes legacy &lt; 8 h en producción bajo regla anterior; no se requiere migración de datos.
- SC puede tener hints con ejemplos «8 horas» — actualización i18n opcional, no bloqueante.

---

## Preguntas abiertas

- Ninguna bloqueante (A1 cerrado).

---

## Riesgos de ambigüedad

- **Bajo:** list/get SP si validan rango — TR confirma si solo upsert o también lectura.

---

## Veredicto B1

**HU apta para TR (Parte C)** — delta normativo acotado a duración; trazabilidad completa a SPEC-013-update.
