# SPEC-001-01 - Experiencia base

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionadas** | `docs/03-historias-usuario/001-Generalidades/HU-GEN-01-*.md` (6 HU; índice en README de la carpeta) |
| **TR relacionadas** | `docs/04-tareas/001-Generalidades/TR-GEN-01-*.md` (6 TR; índice en README de la carpeta) |
| **Estado** | En revisión |
| **Revisión A1** | Apto con observaciones (2026-05-28) |

## Objetivo

Definir lineamientos base de experiencia de usuario para el arranque del producto: layout, navegación, idioma, apariencia y ayuda contextual.

## Estado de ejecución

Pendiente de programación en este repositorio.

## Decisiones humanas (producto SistemaPartes)

| Tema | Decisión |
|------|----------|
| Idioma y tema por defecto | Fijados en **producto SistemaPartes** → `PROD-Sistema-Partes.md` **§8.1** |
| Menú mínimo MVP por perfil | Fijado en **producto SistemaPartes** → mismo documento **§8** (ítems) + permisos §7 |

## Valores por defecto (copia producto §8.1)

| Parámetro | Valor MVP | Notas |
|-----------|-----------|--------|
| Idioma por defecto | `es` | Si `users.locale` vacío → `navigator.language`; si no soportado → `es` |
| Tema por defecto | `generic.light` | Preferencia por usuario en MONO; catálogo DevExtreme en contexto |

## Menú MVP (copia producto §8)

1. Carga de pedidos/presupuestos.
2. Presupuestos ingresados.
3. Pedidos ingresados.
4. Pedidos pendientes.
5. Deuda de clientes.
6. Cheques en cartera.
7. Historial de ventas.
8. Stock.
9. Tratativas / seguimiento de presupuestos.
10. Dashboard.
11. Logs de integración.

Visibilidad por perfil: permisos §7 + seed `pq_menus` (no duplicar reglas en este SPEC).

## Controles del menú lateral (header)

Tres acciones **independientes** en el header (detalle en contexto `menu-general.md`):

| Control | Función |
|---------|---------|
| Hamburguesa | Mostrar u ocultar el **panel** del sidebar |
| Expandir / contraer | Expandir o contraer **todas** las ramas del árbol (con sidebar visible) |
| Vista del menú | `allBranches` (agrupadores + operativos) o `operationalOnly` (solo nodos con ruta/proceso) |

Son preferencias de **presentación**; no reemplazan autorización ni filtran permisos en backend.

**Persistencia:** por **usuario** o por **terminal/navegador** (detalle en `menu-general.md` § Alcance de persistencia). **Nunca** por empresa ni global para todos los usuarios.

## Flujo login vs post-login

1. **Login:** selector de idioma (y branding); sin menú lateral de procesos.
2. **Post-login:** shell completo; menú general desde API; menú avatar con preferencias (idioma/tema).
3. **Persistencia:** `users.locale` y `users.theme` tras guardar en avatar.

## Fuente de verdad de producto (obligatoria)

- `docs/02-producto/SistemaPartes/PROD-Sistema-Partes.md` — §5 MONO, §7 perfiles, **§8 menú**, **§8.1 defaults**
- `docs/02-producto/SistemaPartes/INDICE-Sistema-Partes.md` — tenancy y shell operativo

## Entradas requeridas

- Definición de producto anterior.
- `docs/_base/shell-layout-principal.md`
- `docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md` (contrato API)
- `docs/frontend/devextreme-norms.md` (si aplica en el repo)

## Fuentes (contexto MONO)

Subcarpeta: `docs/00-contexto/_mono/01-experiencia-base/`

- `apariencia-temas.md`, `ayuda-externa-asistente.md`, `idioma-multilingual.md`, `menu-avatar.md`, **`menu-general.md`** (tres controles header), **`shell-layout.md`**

## Alcance

- Definir shell principal y zonas de pantalla (header, sidebar, área principal, footer).
- Definir navegación principal, menús y avatar (login y post-login).
- Definir estrategia de idioma inicial (valores en producto §8.1).
- Definir reglas de apariencia/temas (valores en producto §8.1).
- Definir puntos de ayuda externa o asistida.

## Fuera de alcance

- Implementación visual pixel-perfect definitiva.
- Optimización avanzada de accesibilidad o performance UI.
- Detalle de ítems de negocio SistemaPartes fuera del menú §8 de producto.

## Entregables verificables

- Shell/navegación: este SPEC + producto §5/§8 + contexto `shell-layout.md`.
- Menú MVP: lista en producto **§8** (11 ítems); visibilidad por perfil vía §7 y `pq_menus`/permisos.
- Defaults: tabla en producto **§8.1** (idioma `es`, tema `generic.light`).

## Criterios de aceptación medibles (SPEC + producto)

- [ ] Shell/layout trazable a producto §5 MONO y contexto `shell-layout.md`.
- [ ] Menú general y menú avatar: login/post-login + **tres controles** (sidebar, expandir árbol, vista operativa) en `menu-general.md` / `menu-avatar.md`.
- [ ] Idioma inicial y fallback sin ambigüedad: producto **§8.1** + `idioma-multilingual.md`.
- [ ] Tema por defecto sin ambigüedad: producto **§8.1** + `apariencia-temas.md`.

## Trazabilidad HU

| HU | Tema SPEC |
|----|-----------|
| HU-GEN-01-shell-layout | Shell, zonas |
| HU-GEN-01-menu-general-sidebar | Menú §8 producto |
| HU-GEN-01-menu-avatar | Avatar |
| HU-GEN-01-idioma | §8.1 idioma |
| HU-GEN-01-apariencia-temas | §8.1 tema |
| HU-GEN-01-ayuda-externa | Ayuda (Should) |

## Estado F de la oleada

### HUs/TR con cierre F formal en esta etapa

- `HU-GEN-01-shell-layout` / `TR-GEN-01-shell-layout` -> **Aprobada**
- `HU-GEN-01-idioma` / `TR-GEN-01-idioma` -> **Aprobada**
- `HU-GEN-01-apariencia-temas` / `TR-GEN-01-apariencia-temas` -> **Aprobada con observaciones**

Soporte consolidado: `docs/04-tareas/001-Generalidades/F-GEN-01-02-cierre-formal.md`.

### Pendiente para cierre total de la SPEC

- `HU-GEN-01-menu-general-sidebar` / `TR-GEN-01-menu-general-sidebar`
- `HU-GEN-01-menu-avatar` / `TR-GEN-01-menu-avatar`
- `HU-GEN-01-ayuda-externa` / `TR-GEN-01-ayuda-externa`

### Criterio de lectura de estado

Esta SPEC se replica como base documental y debe considerarse pendiente de programación, pero **no** se considera cerrada en forma total mientras existan HU/TR asociadas sin cierre formal.

---

## Listas DevExtreme (SelectBox / Lookup / DropDown) — CC PQ #3

1. **Carga de datos:** mientras se completa el `dataSource`, mostrar indicador **«cargando…»** (i18n, tamaño discreto) y **bloquear** interacción hasta finalizar el fetch.
2. **Búsqueda con único resultado:** si al escribir texto de búsqueda queda **un solo ítem**, seleccionarlo automáticamente.
3. **Alcance:** transversal a todo el portal salvo excepción documentada en HU de proceso.

## Historial de cambios

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 09/06/2026 | CC PQ #3 | Listas DX: cargando+bloqueo; auto-match único ítem |
| 09/06/2026 | Parte I | Unificación `SPEC-001-01-experiencia-base-update` |








