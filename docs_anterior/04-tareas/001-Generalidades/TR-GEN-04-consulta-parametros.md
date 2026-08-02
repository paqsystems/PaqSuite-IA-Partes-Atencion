# TR-GEN-04 — Consulta de parámetros (solo lectura)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-04-consulta-parametros](../../03-historias-usuario/001-Generalidades/HU-GEN-04-consulta-parametros.md) |
| **SPEC relacionada** | [SPEC-001-04-configuracion-global](../../05-open-spec/001-Generalidades/SPEC-001-04-configuracion-global.md) |
| **Producto** | [RN-Sistema-Partes.md](../../02-producto/SistemaPartes/RN-Sistema-Partes.md) |
| **Épica** | 001 — Generalidades / Configuración global |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-02-autorizacion-menu-api; TR-GEN-03-grillas-listados; `PQ_parametros_gral` + seed SistemaPartes |
| **Estado** | Pendiente |
| **Última actualización** | 2026-07-02 (Parte I — CC PQ #9) |

**Origen:** [HU-GEN-04-consulta-parametros](../../03-historias-usuario/001-Generalidades/HU-GEN-04-consulta-parametros.md)  
**Referencia SPEC:** [SPEC-001-04-configuracion-global](../../05-open-spec/001-Generalidades/SPEC-001-04-configuracion-global.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Consulta informativa de parámetros generales del módulo SistemaPartes.

### Narrativa
Como usuario con permiso de consulta, quiero ver descripción, valor y ayuda de cada parámetro ERP, para entender la configuración vigente sin modificarla desde el portal.

### In scope / Out of scope
- **In scope:** menú General → Consulta de parámetros; `GET /api/v1/config/parametros`; grilla DevExtreme solo lectura; filtro `Programa=SistemaPartes`.
- **Out of scope:** edición web (MONO HU-007); ABM filas; parámetros de otros módulos en MVP; export Excel (Should posterior).

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Grilla muestra caption (descripción), valor legible, tooltip cuando exista; **sin** columna Clave.
- **AC-02:** API GET devuelve ítems ordenados por `CAPTION` (descripción) ASC con `tipoValor` y `valorMostrado`.
- **AC-03:** No hay botón Editar ni endpoints de persistencia de parámetros en SistemaPartes.
- **AC-04:** Seed menú `grp_general` + `pw_consultaparametros` + ruta `/general/parametros`.
- **AC-05:** Sin `Permiso_Repo` → 403 API y ítem de menú oculto.
- **AC-06:** Booleanos visibles como Sí/No (i18n).
- **AC-07:** Tooltip por fila cuando `TOOLTIP` no vacío.
- **AC-08:** ≥ 1 E2E supervisor ve descripción conocida (caption de `MinutosWeb`); clave técnica no visible en grilla.

### Escenarios Gherkin

Ver HU-GEN-04 § Escenarios Gherkin (copia autoritativa en HU).

---

## 3) Reglas de Negocio

1. **RN-01:** Solo lectura; administración ERP/herramientas internas (producto §10.6).
2. **RN-02:** Filtro `Programa = 'SistemaPartes'` case-insensitive.
3. **RN-03:** Orden `CAPTION` (descripción) ASC; si `CAPTION` vacío, `Clave` como desempate.
4. **RN-04:** `valorMostrado` según `ParametrosGralTipoValor::fromRow()` + columna `Valor_*` efectiva.
5. **RN-05:** `CAPTION` / `TOOLTIP` desde BD como fallback; UI resuelve **`parametros.SistemaPartes.{Clave}.caption|tooltip`** en los 5 locales (CC PQ #7, 2026-06-18).
6. **RN-06:** Permiso `Permiso_Repo` sobre procedimiento `pw_consultaparametros`.
7. **RN-07:** No paginar en MVP (≤ 57 filas); respuesta lista completa en `resultado.items[]`.

Fuente producto: [RN-Sistema-Partes.md](../../02-producto/SistemaPartes/RN-Sistema-Partes.md).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-03)

**Fuentes revisadas:** HU-GEN-04, SPEC-001-04, [RN-Sistema-Partes.md](../../02-producto/SistemaPartes/RN-Sistema-Partes.md), `ParametrosGralTipoValor.php`, `PqParametrosGral.php`, `PqParametrosGralSistemaPartesSeeder.php`, seed JSON 57 claves, `routes/api.php` (`/config/parametros-carga` existente), TR-GEN-02-autorizacion-menu-api, TR-GEN-03-grillas-listados, `paqsuite_mvp.php` (sin `grp_general` aún).

### Resultado general

- **Estado:** Apto con observaciones
- **Ambigüedades bloqueantes:** 0
- **Puede pasar a D1:** **Sí** (aplicar resoluciones §3.2)

### Ambigüedades críticas

| ID | Tema | Riesgo | Estado | Resolución (→ D1) |
|----|------|--------|--------|-------------------|
| AMB-C01 | Paginación vs 57 claves | Over-engineering | **Cerrado** (R-C1-01) | Lista completa sin paginación en MVP. |
| AMB-C02 | Permiso consulta | ¿Autenticado vs Repo? | **Cerrado** (R-C1-02) | `Permiso_Repo` en `pw_consultaparametros`. |
| AMB-C03 | Formato valor API | UI vs backend i18n | **Cerrado** (R-C1-03) | API devuelve `valorMostrado` string + `tipoValor`; booleanos localizados en UI con `tipoValor=B`. |
| AMB-C04 | Grupo menú General | No existe en seed MVP | **Cerrado** (R-C1-04) | Crear `grp_general` + ítem último en seed. |
| AMB-C05 | Columnas `CAPTION`/`TOOLTIP` | Modelo Eloquent sin fillable | **Cerrado** (R-C1-06) | Lectura SQL/`DB::table` o ampliar select en servicio; no depender de `$fillable`. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M01 | Fechas en `valorMostrado` | ISO-8601 en API; UI formatea `dd/MM/yyyy` si `tipoValor=D`. |
| AMB-M02 | Claves i18n booleanos | Reutilizar `pedidos.carga.cabecera.si` / `no` (producto §4); no duplicar prefijo. |
| AMB-M03 | Tabla ausente en dev (R2) | Mismo criterio que bootstrap dev: **200** con `items: []` + log; no 503 en MVP. |
| AMB-M04 | Tooltip en grilla | Columna `tooltip` visible; opcional `hint` en fila vía `cellHintEnabled` si DX lo soporta — no bloqueante. |
| AMB-M05 | Path API vs `parametros-carga` | Nuevo `GET /config/parametros` (listado); no reutilizar ni mezclar con `parametros-carga`. |

### Contradicciones TR ↔ HU ↔ producto

| Contradicción | Resolución |
|---------------|------------|
| Producto §6 JSON sin envelope vs TR §5.2 envelope MONO | **Envelope obligatorio** (`error`, `respuesta`, `resultado.items`). |
| HU CA-07 tooltip vs columna oculta | Columna tooltip visible por defecto; AC-07 cumple con valor en celda o hint. |
| Ninguna otra bloqueante | — |

### Supuestos detectados

- Tabla `PQ_parametros_gral` con columnas `CAPTION`/`TOOLTIP` tras bootstrap/seed (seeder ya las escribe).
- Supervisor MVP recibe `Permiso_Repo` en nuevo procedimiento vía seed permisos.
- Frontend enruta `/general/parametros` bajo shell autenticado (TR-GEN-01).

### Preguntas para decisión humana

(Ninguna bloqueante — cerradas en §3.2.)

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1

| ID | Decisión |
|----|----------|
| R-C1-01 | Sin paginación; envelope `{ items, programa, total }` bajo `resultado`. |
| R-C1-02 | Autorización: `VisibilityPermissionGuard` + `Permiso_Repo` + procedimiento `pw_consultaparametros`. |
| R-C1-03 | Servicio `ParametrosConsultaService::listarPorPrograma(string $programa)` reutilizando `ParametrosGralTipoValor`. |
| R-C1-04 | Menú: `grp_general` orden **60**; ítem `pw_consultaparametros` orden **61** (último del grupo). |
| R-C1-05 | Prohibido registrar `PUT`/`PATCH`/`POST`/`DELETE` de parámetros en OpenAPI SistemaPartes. |
| R-C1-06 | Mapeo fila: `CAPTION`/`TOOLTIP` vía query explícita; fallback caption = `Clave`, tooltip = `""`. |
| R-C1-07 | Controller `ParametrosController` bajo `Api/V1/Config/`; registrar ruta junto a `parametros-carga`. |
| R-C1-08 | Feature `ParametrosConsultaFeatureTest`: 200 supervisor, 403 sin Repo, 401 sin token. |

---

## 3.3) Plan D1 — Implementación (2026-06-03)

### Alcance entendido

Backend: servicio lectura + endpoint GET + seed menú/permisos + tests. Frontend: página grilla solo lectura + cliente API + i18n columnas. Sin mutación ni export Excel.

### Orden sugerido

```text
1. ParametrosConsultaService + unit (mapeo tipos)
2. ParametrosController + ruta + policy + OpenAPI
3. Seed paqsuite_mvp.php + paqsuite_visibility.php + permisos supervisor
4. Feature tests 401/403/200
5. parametrosConsultaApi.ts + ParametrosConsultaPage + ruta React
6. i18n + E2E smoke (MinutosWeb visible, sin botón editar)
7. matriz-permisos-mvp.md
```

### Dependencias D1

- TR-GEN-02-autorizacion-menu-api (policy existente).
- TR-GEN-03-grillas-listados (`DataGridDx`).
- Seed `PQ_PARAMETROS_GRAL.SistemaPartes.seed.json` en tenant dev.

### Fuera de D1

- Export Excel; edición MONO HU-007; parámetros otros programas.

---

## 4) Impacto en Datos

### Tablas afectadas
- Lectura: `PQ_parametros_gral` (`Programa`, `Clave`, `tipo_valor`, `Valor_*`, `CAPTION`, `TOOLTIP`).
- Seed menú: `pq_menus` vía `config/paqsuite_mvp.php`.
- Permisos: `pq_permisos` / seed roles (supervisor con Repo).

### Seed mínimo para tests
- Tabla `PQ_parametros_gral` con seed [PQ_PARAMETROS_GRAL.SistemaPartes.seed.json](../../backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.SistemaPartes.seed.json).
- Rol supervisor con `Permiso_Repo` en `pw_consultaparametros`.
- Ítem menú en seed MVP.

---

## 5) Contratos de API y OpenAPI

> Envelope: [`envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/config/parametros` | Bearer + `X-Paq-Cliente` | `Permiso_Repo` (`pw_consultaparametros`) | No |

**Prohibido en este slice:** `PUT`, `PATCH`, `POST`, `DELETE` sobre parámetros.

### 5.2 Detalle por operación

#### GET `/api/v1/config/parametros`

**Autorización:** `Permiso_Repo` + procedimiento `pw_consultaparametros`.

**Query:**

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `programa` | string | `SistemaPartes` | Filtro `Programa` case-insensitive |

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "items": [
      {
        "clave": "MinutosWeb",
        "caption": "Minutos web",
        "tooltip": "…",
        "tipoValor": "I",
        "valorMostrado": "30"
      }
    ],
    "programa": "SistemaPartes",
    "total": 57
  }
}
```

| Propiedad | Tipo | Regla |
|-----------|------|--------|
| `clave` | string | `Clave` |
| `caption` | string | `CAPTION` o clave si null |
| `tooltip` | string | `TOOLTIP` o `""` |
| `tipoValor` | string | S/T/I/D/B/N |
| `valorMostrado` | string | Valor efectivo como texto homogéneo |

**Orden `items[]`:** `CAPTION` ASC; si `CAPTION` vacío, `Clave` como desempate. Campo `clave` sigue en JSON (uso interno); **no** se muestra en grilla UI.

**Response 401:** no autenticado.

**Response 403:** sin `Permiso_Repo`.

### 5.3 OpenAPI (L5-Swagger)

- [ ] Operación en `ConfigController` o `ParametrosController`
- [ ] `security`: Bearer + `X-Paq-Cliente`
- [ ] 401/403 documentados
- [ ] Verificado en `/api/documentation`

### 5.4 Actualización matriz permisos

- [x] Fila `GET /api/v1/config/parametros` → `Permiso_Repo` / `pw_consultaparametros` en `matriz-permisos-mvp.md`

---

## 6) Cambios Frontend

### Pantallas / componentes

| Proceso | Componente | Ruta |
|---------|------------|------|
| `pw_consultaparametros` | `ParametrosConsultaPage` | `/general/parametros` |

- `DataGridDx` sin columna acciones; sin `rowActions`.
- Columnas visibles: `caption`, `valorMostrado`, `tooltip`; `tipoValor` oculta por defecto. **Sin** columna `clave`.
- Booleanos: `customizeText` con i18n Sí/No cuando `tipoValor === 'B'`.
- Filas: `mapParametroConsultaRow` (`resolveParametroConsultaTexts.ts`) al fetch y al cambiar `i18n.language`.
- Recursos: `frontend/src/locales/parametros/SistemaPartes.*.json` fusionados en `i18n.ts`.

### data-testid sugeridos
- `page-parametros-consulta`
- `grid-parametros-consulta`

### Config / menú
- `backend/config/paqsuite_mvp.php` — `grp_general`, ítem consulta.
- `backend/config/paqsuite_visibility.php` — clave `consultaParametros` → `pw_consultaparametros`.
- `frontend/src/app/routes` — ruta protegida `/general/parametros`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | `ParametrosConsultaService` + GET endpoint | OpenAPI + unit |
| T2 | Backend | Policy `Permiso_Repo` + config visibility | 403 test |
| T3 | Backend | Seed menú `grp_general` + permisos MVP | Menú visible supervisor |
| T4 | Frontend | `ParametrosConsultaPage` + API client | Grilla solo lectura |
| T5 | Frontend | i18n columnas + booleanos | AC-06, AC-07 |
| T6 | Tests | Feature 200/403 + E2E smoke | AC-08 |
| T7 | Docs | Matriz permisos | Checklist §10 |

---

## 8) Estrategia de Tests

- **Unit:** mapeo `valorMostrado` por cada `tipo_valor`; filtro programa case-insensitive.
- **Integration:** GET 200 supervisor; GET 403 sin permiso; envelope válido.
- **E2E:** supervisor abre pantalla; grilla contiene caption de parámetro conocido; no existe botón editar; clave técnica no visible.

---

## 9) Riesgos y Edge Cases

- **R1:** BD ERP sin `CAPTION`/`TOOLTIP` → usar `Clave` como caption y tooltip vacío (clave no se muestra en grilla).
- **R2:** Tabla ausente en dev → 200 con `items: []` o error controlado (alinear con bootstrap dev).
- **R3:** Confusión con MONO HU-007 editable → documentar en UI subtítulo «solo consulta».

---

## 10) Checklist final

### Checklist del slice
- [x] AC-01…AC-08 (D1; feature 200/403 con SQL en tanda 2)
- [x] Sin endpoints de mutación
- [x] Menú General operativo

### Checklist normas transversales
- [x] Policy en código
- [x] Matriz actualizada
- [x] OpenAPI coherente
- [x] Envelope JSON
- [x] Tests 401/403 (401 verde; 403 skip sin tenant SQL)
- [x] E2E smoke `consultas-d1.spec.ts` (MinutosWeb, sin editar)

---

## Archivos previstos (D1)

### Backend
- `app/Services/Config/ParametrosConsultaService.php` (nuevo)
- `app/Http/Controllers/Api/V1/Config/ParametrosController.php` (nuevo)
- `config/paqsuite_visibility.php`, `config/paqsuite_mvp.php`
- `tests/Feature/Api/Config/ParametrosConsultaFeatureTest.php`

### Frontend
- `frontend/src/features/config/pages/ParametrosConsultaPage.tsx`
- `frontend/src/features/config/api/parametrosConsultaApi.ts`
- `frontend/src/locales/*.json` — `parametros.column.*`

---

## Historial CC PQ #3 (09/06/2026) — Parte I 09/06/2026

Alineación visual columna Valor en consulta de parámetros.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | `alignment="center"` columna Valor (título + celdas) | `ParametrosConsultaPage.tsx` |
| T2 | Vitest columnas | `ParametrosConsultaPage.test.tsx` |

## Historial CC PQ #9 (02/07/2026) — Parte I 02/07/2026

Alta parámetro `ActualizarPrecioCopia` en seed JSON (58 claves), i18n fallback 5 locales y script SQL caption/tooltip. Sin cambio de contrato API ni UI salvo fila dinámica en listado.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | Entrada seed + producto §10.6 | `PQ_PARAMETROS_GRAL.SistemaPartes.seed.json` |
| T2 | i18n `parametros.SistemaPartes.ActualizarPrecioCopia.*` | `SistemaPartes.{es,en,pt,fr,it}.json` |
| T3 | Vitest caption | `resolveParametroConsultaTexts.test.ts` |

Unificación delta CC PQ #9 (archivo `*-update` eliminado en Parte I). Evidencia: [F-CC-PQ-9-cierre-formal](../../02-producto/SistemaPartes/INDICE-Sistema-Partes.md).










