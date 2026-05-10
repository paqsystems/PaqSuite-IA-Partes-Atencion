# SPEC-017 – Sidebar dinámico desde `pq_menus`

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-017 |
| Título | Sidebar consume API de menú; sin hardcode de permisos |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado (alineado a CC TR-017 updates) |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-017](../../03-historias-usuario/001-Seguridad/HU-017-sidebar-dinamico-pqmenus.md) |
| TR relacionada(s) | — (vincular TR-017 updates) |

---

## 1. Resumen ejecutivo

Al montar layout el Sidebar obtiene menú del usuario (SPEC-016); render jerárquico por `parentId`/`orden`; navegación con `routeName` o `procedimiento`; mantiene toggle, mobile overlay y preferencia nueva pestaña (HU-003 épica generalidades); ítem activo y rama expandida según reglas de HU-017.

---

## 2. Alcance

### 2.1 En alcance

- Sin menú hardcodeado salvo mínimos Inicio/Perfil si diseño lo exige (documentar fuente: API vs frontend).
- Fallo API o lista vacía: degradación a menú mínimo o mensaje sin romper layout.
- Textos desde campo `text`; i18n opcional si hay clave en locales.
- No usar flags `esAdmin`/`esSupervisor` para mostrar administración.
- **Ítem activo:** mejor coincidencia ruta larga vs `location.pathname`.
- **Rama expandida:** padres del nodo activo expandidos en TreeView.
- **Clic enlace vs expandir:** `stopPropagation` donde corresponda.

### 2.2 Fuera de alcance

- Edición de menú en caliente.

---

## 3. Actores y contexto

- Usuario autenticado; `Sidebar.tsx`, rutas React alineadas a seed `routeName`.

---

## 4. Comportamiento funcional

- Ver CA detallados en HU-017.

---

## 5. Criterios verificables

- [ ] Fetch menú al montar (o caché con invalidación login).
- [ ] Estructura niveles CSS existentes preservada.
- [ ] Ítem activo y expansión según CA.
- [ ] Playwright/E2E críticos según TR.

---

## 6. Impacto técnico (visión para TR)

- Refactor `Sidebar.tsx`, hooks de ruta, integración React Router `NavLink`.

---

## 7. Riesgos y supuestos

- Desalineación `routeName` seed vs router → SPEC-018 mitiga.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-017 |

---

**Trazabilidad:** [HU-017](../../03-historias-usuario/001-Seguridad/HU-017-sidebar-dinamico-pqmenus.md).
