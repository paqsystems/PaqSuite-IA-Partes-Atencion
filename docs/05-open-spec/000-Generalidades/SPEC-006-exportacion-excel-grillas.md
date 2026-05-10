# SPEC-006 – Exportación a Excel desde grillas

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-006 |
| Título | Exportación Excel (tres modalidades) desde grillas |
| Épica / carpeta | 000 – Generalidades |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-006](../../03-historias-usuario/000-Generalidades/HU-006-exportacion-excel.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

Toda grilla alineada al estándar debe ofrecer exportación a XLSX con tres modalidades (básica, formateada con totales, tabla dinámica para pivots), respetando filtros, orden y permisos del usuario, con convenciones `data-testid` y límites razonables de filas.

---

## 2. Alcance

### 2.1 En alcance

- Botón toolbar “Exportar a Excel”; deshabilitado sin datos + mensaje “No hay datos para exportar”.
- Modalidades: (1) planilla básica, (2) formateada (default), (3) pivot → tabla dinámica Excel si aplica.
- Selección de modalidad; opción página actual vs todas las filas si hay paginación (con tope ej. 10.000).
- Nombre archivo: `{proceso}_{fecha}.xlsx` (u homólogo).
- Permisos: mismos que la vista en pantalla.

### 2.2 Fuera de alcance

- Export batch programático; PDF; columnas solo-export distintas de la grilla.

---

## 3. Actores y contexto

- Usuario con acceso a la grilla y datos según ABM/proceso.
- Identificación `proceso` + `grid_id` (HU-001).

---

## 4. Comportamiento funcional

- Client-side con datos en memoria cuando aplique; si paginación servidor → posible endpoint dedicado por proceso (contrato en TR).
- Gherkin y reglas detalladas en HU-006 (totales, formatos locale, pivot solo en vistas pivot).

---

## 5. Criterios verificables

- [ ] Botón con `data-testid="grid.{proceso}.{grid_id}.exportExcel"` y sub-testids de menú/opciones.
- [ ] Tres modalidades según tipo grilla; default formateada.
- [ ] Filtros/orden reflejados; límite filas con mensaje claro.
- [ ] Accesibilidad: `aria-label` traducible.

---

## 6. Impacto técnico (visión para TR)

- Utilidades `xlsx` / ExcelJS / DevExtreme export; extensión `DataGridDX` y grillas existentes.
- Posibles endpoints export si server-side paging.

---

## 7. Riesgos y supuestos

- Memoria/tiempo con datasets grandes; UX de progreso si aplica.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-006 |

---

**Trazabilidad:** [HU-006](../../03-historias-usuario/000-Generalidades/HU-006-exportacion-excel.md).
