# HU-GEN-02-expiracion-inactividad — Expiración de sesión por inactividad

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-expiracion-inactividad |
| **SPEC origen** | [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generalidades/SPEC-001-02-acceso-y-seguridad.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-06-09 (Parte I — CC PQ #1) |
| **Dependencias** | HU-GEN-02-login-sesion; SPEC-001-04 (parámetro `MinutosWeb`) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Expiración sesión por inactividad (alcance) | Detector frontend + 401 backend |
| Parámetro `MinutosWeb` (decisión SPEC) | Lectura configuración global |
| Entregable: contrato autenticación MVP (expiración) | AC + TR |
| Consumo SPEC-001-04 | Parámetro crítico MVP |
| Trazabilidad HU-SPEC: MinutosWeb | Objeto de esta HU |

## Narrativa

Como **usuario autenticado**,  
quiero **que la sesión expire tras un período sin actividad configurable**,  
para **reducir el riesgo de uso no autorizado en equipos compartidos**.

## Contexto funcional

SPEC-001-02 incluye explícitamente **expiración de sesión por inactividad**. El umbral se obtiene del parámetro **`MinutosWeb`** (producto §10.6; consumo en SPEC-001-04). Contrato de autenticación MVP debe documentar expiración en TR/OpenAPI.

## Alcance incluido

- Tiempo máximo de inactividad desde parámetro **`MinutosWeb`**.
- Frontend: detector de actividad del usuario y cierre de sesión al superar umbral.
- Backend: rechazo de sesión expirada con HTTP 401.
- Al expirar: limpiar sesión local, mensaje informativo, redirigir a login.
- Regla: actividad del usuario renueva contador de inactividad.

## Fuera de alcance

- Revocación manual de todas las sesiones del usuario.
- SSO o refresh token rotativo avanzado.
- 2FA (SPEC fuera de alcance).

## Reglas de negocio

1. El valor de minutos proviene de **`MinutosWeb`** (SPEC-001-02 → SPEC-001-04).
2. La actividad del usuario renueva el contador de inactividad.
3. Tras expiración, cualquier API protegida responde **401**.
4. Fallback si parámetro ausente: documentar default en HU-GEN-04 / TR (SPEC-001-04).

## Criterios de aceptación

- [ ] Tras superar `MinutosWeb` sin actividad, usuario en login con mensaje de sesión expirada.
- [ ] Token/sesión expirada no permite cargar shell ni procesos.
- [ ] Parámetro `MinutosWeb` leído desde configuración global (SPEC-001-04).
- [ ] Default documentado si parámetro ausente (antes de cierre del slice).
- [ ] E2E: simular inactividad (timeout reducido en test) → redirect login.
- [x] **CA-CC-01:** Tras login, si el usuario interactúa antes de `MinutosWeb`, la sesión **no** expira.
- [x] **CA-CC-02:** El contador de inactividad se **reinicia** con cada evento de actividad válido (TR-GEN-02 RN-02).
- [x] **CA-CC-03:** Sin actividad durante `MinutosWeb` → cierre de sesión y redirect login.
- [x] **CA-CC-04:** Test unitario o E2E que demuestre renovación del contador (no timeout fijo desde `loginAt`).

## Escenarios Gherkin

```gherkin
Feature: Expiración por inactividad (SPEC-001-02 / MinutosWeb)

  Scenario: Sesión expira por inactividad
    Given un usuario autenticado
    And MinutosWeb configurado en N minutos
    When permanece N minutos sin actividad
    Then la sesión se cierra
    And es redirigido al login con mensaje de sesión expirada

  Scenario: Actividad renueva contador
    Given un usuario autenticado
    When realiza actividad antes de alcanzar MinutosWeb
    Then la sesión permanece activa

  Scenario: API rechaza token expirado
    Given una sesión expirada por inactividad
    When llama a un endpoint protegido
    Then recibe HTTP 401

  Scenario: Parámetro MinutosWeb ausente
    Given MinutosWeb no configurado
    When el sistema necesita el umbral
    Then aplica default documentado en TR
```

## Supuestos explícitos

- Aviso previo N minutos antes del cierre: no en SPEC-001-02; opcional en TR.
- Pestaña en background: comportamiento exacto en TR.
- Llamadas API exitosas como actividad: cerrado en TR-GEN-02 RN-02 / §6.1.
- Lista cerrada de eventos de actividad (incl. `Tab`, scroll, capture DevExtreme): TR-GEN-02 §6.1 y regla `.cursor/rules/sesion-inactividad-expiracion.mdc`.

## Preguntas abiertas

- ¿Valor default numérico de `MinutosWeb` si falta en BD?
- ¿Aviso previo incluido en MVP?

## Riesgos de ambigüedad

- Dependencia de SPEC-001-04 / HU-GEN-04 para lectura del parámetro; implementar orden Fase 0.

## Veredicto B1

**Lista para TR:** Sí con observaciones (default MinutosWeb y aviso previo)










