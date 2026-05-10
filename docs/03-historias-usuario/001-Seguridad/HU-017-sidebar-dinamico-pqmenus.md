# HU-017 – Sidebar dinámico desde pq_menus

| Campo | Valor |
|-------|-------|
| Épica | 001 – Seguridad y Acceso |
| Prioridad | MUST-HAVE |
| Rol | Usuario autenticado |
| Dependencias | HU-016 (API de menú del usuario), Sidebar actual |
| Última actualización | 2026-04-09 |
| Estado | En Control Calidad |

**Trazabilidad técnica:** [TR-017-sidebar-dinamico-pqmenus-update-01](../../04-tareas/updates/001-Seguridad/TR-017-sidebar-dinamico-pqmenus-update-01.md) (navegación, ítem activo y rama expandida).

---

## Épica
001 – Seguridad y Acceso

## Clasificación
MUST-HAVE

## Rol
Usuario autenticado

## Narrativa

Como usuario autenticado quiero que el menú lateral (sidebar) se genere dinámicamente desde la tabla `pq_menus` y respete mis permisos, para ver solo las opciones a las que tengo acceso y mantener consistencia con el sistema de atributos de rol.

## Criterios de aceptación

- El componente Sidebar consume el endpoint de menú del usuario (HU-016) al montar el layout.
- El menú ya no está hardcodeado; las opciones provienen exclusivamente de la respuesta de la API.
- Se mantienen los ítems fijos mínimos (Inicio, Perfil) según diseño: pueden venir de la API o definirse en frontend si no están en `pq_menus`.
- La estructura jerárquica se respeta: secciones (level0), subopciones (level1, level2) según `parentId` y `orden`.
- Cada ítem navegable usa `routeName` (o `procedimiento`) de la respuesta para construir la ruta (ej. `/admin/usuarios`, `/admin/roles`).
- Se mantiene el comportamiento existente: toggle sidebar, abrir en nueva pestaña (preferencia usuario), overlay en móvil.
- Si la API falla o devuelve vacío, se muestra menú mínimo (Inicio, Perfil) o mensaje apropiado sin romper el layout.
- Se mantienen los estilos y niveles de indentación actuales (sidebar-item-level0, sidebar-item-level1, etc.).
- Los textos visibles usan `text` de la API; se puede aplicar i18n si la clave existe en locales.
- El sidebar no depende de `esAdmin`/`esSupervisor` para mostrar u ocultar la sección Administración; la visibilidad viene de la API.
- **Ítem activo:** la opción resaltada como actual es la que mejor coincide con la URL (ruta más larga / más específica frente a `location.pathname`), de modo que en rutas anidadas no quede marcado solo un padre genérico cuando existe un hijo que encaja.
- **Rama expandida:** al cargar o cambiar de ruta, los nodos padre del ítem activo permanecen expandidos en el TreeView (el árbol no vuelve colapsado al primer nivel ocultando el contexto del proceso elegido).
- **Clic en enlace vs. expandir:** el clic en `NavLink` o enlace «nueva pestaña» no debe interferir con la interacción del nodo del árbol (p. ej. `stopPropagation` donde corresponde), manteniendo coherente expandir/colapsar y navegación.

## Reglas de negocio

- El menú refleja exactamente lo que devuelve la API; no se mezcla lógica hardcodeada de permisos en el frontend.
- Inicio y Perfil pueden ser ítems de `pq_menus` con IDs conocidos o ítems especiales definidos en frontend (documentar decisión).

## Dependencias

- HU-016 (API de menú del usuario)
- Sidebar actual (AppLayout, Sidebar.tsx)
- Rutas del frontend coherentes con `routeName` de `pq_menus`

## Especificación (Open-Spec)

- [SPEC-017 – Sidebar dinámico desde `pq_menus`](../../05-open-spec/001-Seguridad/SPEC-017-sidebar-dinamico-pqmenus.md)

## Referencias

- `frontend/src/app/Sidebar.tsx` – Implementación actual
- `frontend/src/features/admin/pages/RolAtributosPage.tsx` – Patrón de estructura jerárquica (parentId, orden)
- `docs/01-arquitectura/ui/01_MainLayout_PostLogin_Specification.md`
