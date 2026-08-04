# HU-GEN-02-admin-permisos-bulk — Asignación masiva de permisos

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-admin-permisos-bulk |
| **SPEC origen** | [SPEC-001-02-admin-mantenimiento-roles-permisos.md](../../05-open-spec/001-Generalidades/SPEC-001-02-admin-mantenimiento-roles-permisos.md) |
| **Épica** | 001 — Generalidades / Acceso y seguridad (post-MVP) |
| **Prioridad** | Should |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-18) |
| **Última actualización** | 2026-06-18 |
| **Dependencias** | HU-GEN-02-admin-permisos; HU-GEN-02-admin-roles; HU-GEN-03-grillas-listados |

## Trazabilidad SPEC

| Criterio SPEC | Cobertura en esta HU |
|---------------|----------------------|
| CA-02 | Masivo por usuario |
| CA-03 | Masivo por rol |
| CA-04 | Validaciones UI bulk |
| CA-05 | Autorización batch = individual |
| F2, F3 | Flujos masivos |
| AMB-M-02-ADM-02 | Refresh sesión (TR) |

Referencia Tango: HU-013 update 03 / TR-013 update 03 (sin modo `by_company`).

## Narrativa

Como **usuario autorizado a administrar permisos**,  
quiero **asignar muchos permisos usuario–rol en un solo paso**,  
para **no repetir el formulario individual cuando debo otorgar varios roles a un usuario o un rol a muchos usuarios**.

## Contexto funcional

Dos modos en toolbar de **Permisos** (HU-GEN-02-admin-permisos):

1. **Por usuario:** ancla un usuario + grilla multi-selección de roles.
2. **Por rol:** ancla un rol + grilla multi-selección de usuarios.

Producto cartesiano → una fila `Pq_Permiso` por combinación válida. Duplicados existentes se **omiten**; resumen `{ creados, omitidos }`.

## Alcance incluido

- Botones **Por usuario** / **Por rol** en toolbar (`permisos.bulk.byUser`, `permisos.bulk.byRole`).
- Modales DevExtreme con ancla (SelectBox) + DataGrid `selection.mode = multiple`.
- Validación **cliente** antes de API:
  - sin ancla → `admin.permisos.bulk.validationNoAnchor` (`{{field}}`);
  - ancla ok pero cero tildados → `admin.permisos.bulk.validationSinCombinaciones`.
- Confirmación con cantidad estimada (`permisos.bulk.confirm`).
- `POST /api/v1/admin/permisos/batch` con modes `by_user` | `by_role`.
- Mensaje éxito `admin.permisos.bulk.successMessage` con `{{creados}}`, `{{omitidos}}`.
- E2E validaciones bulk (Tango `permisos-admin-bulk.spec.ts` adaptado).

## Fuera de alcance

- Modo **por empresa** (Tango MULTI).
- Edición / eliminación masiva.
- ABM de usuarios o empresas.

## Reglas de negocio

1. Misma autorización que ABM individual de permisos.
2. Unicidad `(id_usuario, id_rol)`; duplicados → omitir + contabilizar en `omitidos`.
3. Referencias (usuario, rol) deben existir al confirmar batch.
4. No invocar batch si validación UI falla.
5. Límite de volumen: documentar en TR si aplica timeout (obs. Tango §9).

## Decisiones cerradas (A1 / B1)

| Tema | Decisión |
|------|----------|
| Política duplicados | **Omitir** con resumen |
| Modos MONO | Solo `by_user` y `by_role` |
| i18n bulk | Claves Tango sin `byCompany` |

## Criterios de aceptación

- [ ] **CA-01:** Botones bulk visibles solo para usuario autorizado al ABM permisos.
- [ ] **CA-02:** Modo **por usuario**: U + roles {R1,R2} crea hasta 2 filas nuevas.
- [ ] **CA-03:** Modo **por rol**: R + usuarios {U1,U2} crea hasta 2 filas nuevas.
- [ ] **CA-04:** Combinación ya existente se omite; resumen incrementa `omitidos`.
- [ ] **CA-05:** Confirmar sin ancla → mensaje `validationNoAnchor`; sin llamada API.
- [ ] **CA-06:** Ancla sin filas tildadas → `validationSinCombinaciones`; sin llamada API.
- [ ] **CA-07:** Tras batch exitoso, grilla principal refleja nuevas asignaciones.
- [ ] **CA-08:** API batch devuelve 403 si usuario no autorizado.
- [ ] **CA-09:** `data-testid` según contexto: modales, grillas, `permisos.bulk.validation`.
- [ ] **CA-10:** E2E: tres modos de validación negativa + flujo feliz por usuario (opcional en CI).

## Escenarios Gherkin

```gherkin
Feature: Asignación masiva de permisos (MONO)

  Scenario: Por usuario crea combinaciones rol
    Given un usuario autorizado en permisos
    When elige asignar por usuario
    And selecciona usuario U y roles R1 y R2
    And confirma
    Then se crean permisos U+R1 y U+R2 que no existían
    And el resumen muestra creados y omitidos

  Scenario: Por rol asigna a varios usuarios
    Given un usuario autorizado
    When elige asignar por rol
    And selecciona rol R y usuarios U1 y U2
    And confirma
    Then se crean permisos U1+R y U2+R según corresponda

  Scenario: Duplicado omitido
    Given ya existe permiso U+R
    When ejecuta batch que incluye U+R
    Then no duplica la fila
    And el resumen indica omisión

  Scenario: Confirmar sin ancla
    When confirma en modal por usuario sin elegir usuario
    Then ve admin.permisos.bulk.validationNoAnchor
    And no se invoca la API batch

  Scenario: Sin selección en grilla
    Given eligió usuario U
    And no tildó ningún rol
    When confirma
    Then ve admin.permisos.bulk.validationSinCombinaciones
    And no se invoca la API batch
```

## Supuestos explícitos

- CRUD individual y catálogos operativos (HU-GEN-02-admin-permisos).
- Componentes portados desde Tango omitiendo empresa.

## Preguntas abiertas

- Ninguna bloqueante para TR.

## Riesgos de ambigüedad

- AMB-M-02-ADM-02: si hace falta re-login del usuario afectado — documentar en TR, no bloquea HU.

## Veredicto B1

**Lista para TR:** Sí










