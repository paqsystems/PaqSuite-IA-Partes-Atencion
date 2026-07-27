# HU-GEN-04 — Consulta de parámetros (solo lectura)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-04-consulta-parametros |
| **SPEC origen** | [SPEC-001-04-configuracion-global](../../05-open-spec/001-Generalidades/SPEC-001-04-configuracion-global.md) |
| **Producto** | [RN-Sistema-Partes.md](../../02-producto/SistemaPartes/RN-Sistema-Partes.md), §17.3.2 |
| **MONO** | [parametros-generales.md](../../00-contexto/_mono/04-configuracion-global/parametros-generales.md) (HU-007 — variante solo lectura) |
| **Épica** | 001 — Generalidades / Configuración global |
| **Prioridad** | Should |
| **Estado** | Pendiente |
| **Última actualización** | 2026-07-02 (Parte I — CC PQ #9) |
| **D1** | Implementado (2026-06-03) |
| **TR relacionada** | [TR-GEN-04-consulta-parametros](../../04-tareas/001-Generalidades/TR-GEN-04-consulta-parametros.md) |
| **Dependencias** | HU-GEN-03-grillas-listados; tabla `PQ_parametros_gral`; seed [PQ_PARAMETROS_GRAL.SistemaPartes.seed.json](../../backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.SistemaPartes.seed.json) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Lectura parámetros MVP (sin ABM web) | RN-01, RN-02, CA-03 |
| Inventario §10.6 + seed 58 claves | RN-03 |
| Tipos `tipo_valor` BASE | RN-04 |
| Marco MONO HU-007 (presentación listado) | RN-05, CA-01 |

## Narrativa

Como **usuario autenticado con permiso de consulta**,  
quiero **ver los parámetros generales del módulo SistemaPartes**,  
para **entender la configuración vigente sin modificar valores en el portal**.

## Contexto funcional

En **PaqSuite-IA-Tango** / MONO el mismo marco HU-007 permite **editar** valores. En **SistemaPartes** los valores provienen del **ERP**; esta pantalla es **informativa** (consulta). La administración sigue en ERP/herramientas internas (SPEC-001-04).

## Alcance incluido

- Grupo menú **General** (`grp_general`) con ítem **Consulta de parámetros** al **final** del grupo.
- `procedimiento`: `pw_consultaparametros`; ruta `/general/parametros`.
- API `GET /api/v1/config/parametros?programa=SistemaPartes` (solo lectura).
- Grilla DevExtreme: **descripción** (`CAPTION`), valor mostrado, tooltip; columna `tipo_valor` opcional oculta. **Sin columna Clave** en UI.
- Mapeo de valor según `ParametrosGralTipoValor` (mismo contrato que `SistemaPartesParameterService`).
- Booleanos con etiquetas Sí/No localizadas.
- Sin botón **Editar**, sin modal de edición, sin endpoints `PUT`/`PATCH`/`POST` de parámetros.

## Fuera de alcance

- ABM web de parámetros (MONO HU-007 editable).
- Alta/baja de filas en `PQ_parametros_gral`.
- Parámetros de otros módulos en MVP (solo `Programa = 'SistemaPartes'`).
- Export Excel (opcional Should posterior; no bloqueante MVP).

## Reglas de negocio

Fuente de verdad: **[RN-Sistema-Partes.md](../../02-producto/SistemaPartes/RN-Sistema-Partes.md)**.

1. **Solo lectura** en portal SistemaPartes.
2. **Filtro:** `Programa = 'SistemaPartes'` (comparación case-insensitive).
3. **Orden:** `CAPTION` (descripción) ascendente; si `CAPTION` vacío, `Clave` como desempate (sin mostrar clave en grilla).
4. **Valor mostrado:** texto homogéneo según `tipo_valor` → columna `Valor_*` efectiva.
5. **CAPTION / TOOLTIP:** desde BD; fallback seed JSON / i18n `parametrosGral.items.SistemaPartes.{clave}.*` si aplica.
6. **Permiso:** `Permiso_Repo` sobre `pw_consultaparametros` (misma familia que consultas comerciales).
7. Servicios runtime existentes (`SistemaPartesParameterService`) **no** se reemplazan; esta pantalla no altera su lectura.
8. **RN-P01 (CC PQ #9):** Parámetro `ActualizarPrecioCopia` (`tipo_valor = B`) visible en listado con caption «Actualizar precios al copiar comprobante», valor Sí/No y tooltip; sin edición web.

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Ubicación menú | Grupo **`grp_general`**; ítem **último** del grupo |
| Ruta / procedimiento | `/general/parametros` · `pw_consultaparametros` |
| Alcance datos | Solo **`Programa = 'SistemaPartes'`** (58 claves seed) |
| Edición | **Prohibida** en UI y API de esta HU |
| Permiso | **`Permiso_Repo`** en `pw_consultaparametros` |
| Referencia técnica | Reutilizar `ParametrosGralTipoValor` + modelo `PqParametrosGral`; no duplicar mapeo |
| Control grilla | `DataGridDx` listado; **sin** columna acciones ni columna **Clave** |
| Orden listado | Por **descripción** (`CAPTION` ASC) |

## Criterios de aceptación

- [ ] **CA-01:** Pantalla listado DevExtreme solo lectura con columnas descripción, valor, tooltip (sin Clave).
- [ ] **CA-02:** API GET devuelve ítems SistemaPartes con `valorMostrado` formateado por tipo.
- [ ] **CA-03:** No existe botón Editar ni endpoints de persistencia de parámetros en SistemaPartes.
- [ ] **CA-04:** Seed menú incluye `grp_general` + ítem `pw_consultaparametros`.
- [ ] **CA-05:** Usuario sin `Permiso_Repo` no ve ítem de menú ni obtiene 200 en API.
- [ ] **CA-06:** Booleanos muestran Sí/No (i18n), no `true`/`false` crudo.
- [ ] **CA-07:** Tooltip visible por fila cuando `TOOLTIP` no está vacío.
- [ ] **CA-08:** ≥ 1 E2E: supervisor ve listado con al menos una descripción conocida (ej. caption de `MinutosWeb`); la clave técnica no aparece en grilla.
- [x] **CA-CC3-01:** Encabezado columna Valor centrado.
- [x] **CA-CC3-02:** Celdas de valor centradas horizontalmente.
- [x] **CA-CC3-03:** Sin regresión en descripción, tooltip ni ausencia de columna Clave.

## Escenarios Gherkin

```gherkin
Feature: Consulta de parámetros SistemaPartes (solo lectura)

  Scenario: Listado informativo
    Given un supervisor con Permiso_Repo en pw_consultaparametros
    When abre Consulta de parámetros en menú General
    Then ve filas de PQ_PARAMETROS_GRAL con Programa SistemaPartes
    And cada fila muestra descripción, valor legible y tooltip cuando aplique
    And la columna Clave no se muestra en la grilla

  Scenario: Sin edición
    When el usuario está en Consulta de parámetros
    Then no existe botón Editar por fila
    And no existe endpoint PUT de parámetros en SistemaPartes

  Scenario: Booleano localizado
    Given un parámetro tipo_valor B con Valor_Bool true
    When se muestra en la grilla
    Then el valor visible es "Sí" (o equivalente i18n)

  Scenario: Sin permiso
    Given un usuario sin Permiso_Repo en pw_consultaparametros
    When intenta GET /api/v1/config/parametros
    Then HTTP 403
```

## Supuestos explícitos

- Tabla `PQ_parametros_gral` con columnas `CAPTION` y `TOOLTIP` (bootstrap dev).
- Referencia implementación listado: PaqSuite-IA-Tango (adaptar quitando edición).

## Preguntas abiertas

(Ninguna — cerradas en B1.)

## Veredicto B1

**Lista para TR** — generada en paso C.

## Veredicto C

**TR generada** — [TR-GEN-04-consulta-parametros](../../04-tareas/001-Generalidades/TR-GEN-04-consulta-parametros.md).

## Veredicto C1

**Apto con observaciones para D1** — revisión C1 en TR §3.1–3.3 (0 ambigüedades bloqueantes).

## Veredicto D1

**Implementado** — backend + frontend + menú seed según TR-GEN-04.

## Veredicto F

**Pendiente** — QA manual + F1 + F (2026-06-03). Ver [F-GEN-04-consulta-parametros-cierre](../../04-tareas/001-Generalidades/F-GEN-04-consulta-parametros-cierre.md).

## Historial CC PQ #9 (02/07/2026) — Parte I 02/07/2026

Alta parámetro `ActualizarPrecioCopia` en consulta solo lectura (58 claves seed). Unificación delta CC PQ #9 (archivo `*-update` eliminado en Parte I). Evidencia: [F-CC-PQ-9-cierre-formal](../../02-producto/SistemaPartes/INDICE-Sistema-Partes.md).










