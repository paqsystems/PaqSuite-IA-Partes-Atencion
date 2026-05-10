# SPEC-003 – Apertura de opción de menú en misma o nueva pestaña

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-003 |
| Título | Preferencia abrir menú en misma o nueva pestaña |
| Épica / carpeta | 000 – Generalidades |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-003](../../03-historias-usuario/000-Generalidades/HU-003-apertura-menu-misma-o-nueva-pestana.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

El usuario configura si las opciones de menú que navegan a un proceso abren en la misma pestaña (SPA) o en una nueva, con persistencia **server-side** en `users.menu_abrir_nueva_pestana` y control en el **menú del avatar**.

---

## 2. Alcance

### 2.1 En alcance

- Toggle “Abrir en nueva pestaña” en menú de usuario (header).
- Comportamiento: misma pestaña vs `target="_blank"` (o equivalente) según preferencia.
- Persistencia en `users.menu_abrir_nueva_pestana` (0 misma / 1 nueva); sincronizada entre dispositivos.
- Nueva pestaña mantiene sesión (token accesible); **solo frontend web**.

### 2.2 Fuera de alcance

- App móvil nativa / PWA mobile: no ofrecer ni aplicar la preferencia.

---

## 3. Actores y contexto

- Usuario autenticado (web).
- Depende de login (épica 001) y shell con menú usuario.

---

## 4. Comportamiento funcional

1. Por defecto: misma pestaña.
2. Al cambiar preferencia: guardar en backend y aplicar a clics posteriores en ítems de menú de procesos.
3. API o payload de login incluye lectura/escritura de la preferencia.

---

## 5. Criterios verificables

- [ ] Toggle visible en menú avatar; persistencia en `users.menu_abrir_nueva_pestana`.
- [ ] Navegación coherente con preferencia en sidebar/menú de procesos.
- [ ] Nueva pestaña no pierde autenticación.
- [ ] Mobile: ausencia de la opción o no aplicación documentada.

---

## 6. Impacto técnico (visión para TR)

- Migración/campo `users.menu_abrir_nueva_pestana`.
- Frontend: `Sidebar.tsx` / enlaces de menú respetando preferencia.
- API perfil/login PATCH preferencias.

---

## 7. Riesgos y supuestos

- Políticas de popup blocker en navegadores para `target="_blank"`; UX de error si aplica.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-003 |

---

**Trazabilidad:** [HU-003](../../03-historias-usuario/000-Generalidades/HU-003-apertura-menu-misma-o-nueva-pestana.md).
