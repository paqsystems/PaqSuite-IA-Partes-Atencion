# SPEC-010 – Acceso a ayuda externa (Asistente IA)

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-010 |
| Título | Enlace a chat compartido desde menú avatar |
| Épica / carpeta | 000 – Generalidades |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-010](../../03-historias-usuario/000-Generalidades/HU-010-acceso-ayuda-externa-chat-compartido.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

Opción **“Asistente IA”** en menú usuario abre recurso externo en **nueva pestaña**, con URL **configurable** sin hardcode en componente visível y redirección/ruta interna preferida; comportamiento seguro si falta configuración.

---

## 2. Alcance

### 2.1 En alcance

- Menú avatar: etiqueta “Asistente IA”, ícono opcional, `data-testid`.
- Nueva ventana/pestaña; sesión SPA intacta.
- Patrón: UI → ruta/mecanismo interno → URL final.
- Config global (o por módulo según arquitectura) con valor inicial documentado en HU.
- Si no hay URL o es inválida: ocultar o mensaje claro; sin navegación rota.

### 2.2 Fuera de alcance

- Alojamiento del chat externo; contenido de manuales.

---

## 3. Actores y contexto

- Usuario autenticado; complemento a `docs/99-Manual-Usuario`.

---

## 4. Comportamiento funcional

- Gherkin HU-010 (abrir ayuda, sin config, cambio URL sin deploy frontend).

---

## 5. Criterios verificables

- [ ] Opción en menú avatar con tests automatizables.
- [ ] URL no embebida literalmente en JSX visible; config backend/env/parámetros.
- [ ] Degradación elegante sin config.

---

## 6. Impacto técnico (visión para TR)

- Endpoint redirect o config read; `UserMenu.tsx` integración.
- Posible `PQ_PARAMETROS_GRAL` o env según decisión.

---

## 7. Riesgos y supuestos

- Políticas CORS/popup; URL final visible en barra destino aceptable si flujo inicia interno.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-010 |

---

**Trazabilidad:** [HU-010](../../03-historias-usuario/000-Generalidades/HU-010-acceso-ayuda-externa-chat-compartido.md).
