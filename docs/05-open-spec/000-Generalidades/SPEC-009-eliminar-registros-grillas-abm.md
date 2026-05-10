# SPEC-009 – Eliminar registros en grillas ABM

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-009 |
| Título | Acción eliminar en grillas ABM con confirmación y permisos |
| Épica / carpeta | 000 – Generalidades |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-009](../../03-historias-usuario/000-Generalidades/HU-009-eliminar-registros-grillas-abm.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

En pantallas ABM con grilla, cada fila expone eliminación sujeta a **Permiso_Baja**, reglas de negocio de eliminabilidad, confirmación modal y manejo de errores de backend (403/422), con refresco coherente y `data-testid` automatizable.

---

## 2. Alcance

### 2.1 En alcance

- Control Eliminar por registro; habilitación según permiso y estado del registro.
- Modal confirmación con identificación del registro; Cancelar / Confirmar.
- Éxito: refresh o remove fila + feedback; política baja lógica vs física según recurso.

### 2.2 Fuera de alcance

- Definición de endpoint por módulo (usa el existente del ABM).

---

## 3. Actores y contexto

- Usuario autenticado con permisos del menú del ABM.
- `PQ_RolAtributo.Permiso_Baja`, opción `pq_menus` asociada.

---

## 4. Comportamiento funcional

- Errores: mensaje claro; no corromper estado de grilla.
- Gherkin en HU-009.

---

## 5. Criterios verificables

- [ ] Sin permiso: control oculto/deshabilitado.
- [ ] Confirmación y llamada al endpoint del recurso.
- [ ] Manejo 403/422 según HU.
- [ ] Convención `data-testid` por feature.

---

## 6. Impacto técnico (visión para TR)

- Patrón reutilizable en columnas de acciones; integración con estándar grillas.

---

## 7. Riesgos y supuestos

- Condiciones de carrera si el registro cambia entre apertura modal y confirmación.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-009 |

---

**Trazabilidad:** [HU-009](../../03-historias-usuario/000-Generalidades/HU-009-eliminar-registros-grillas-abm.md).
