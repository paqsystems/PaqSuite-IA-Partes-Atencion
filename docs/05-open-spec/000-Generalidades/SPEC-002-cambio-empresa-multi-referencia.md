# SPEC-002 – Cambio de empresa activa (referencia MULTI; no aplica MONO)

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-002 |
| Título | Cambio de empresa activa — referencia MULTI |
| Épica / carpeta | 000 – Generalidades |
| Estado | No aplica (instalación MONO) |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-002](../../03-historias-usuario/000-Generalidades/HU-002-cambio-empresa-activa.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

En despliegue **MONO** esta capacidad **no se implementa**: no hay `PQ_Empresa`, selector de empresa ni `X-Company-Id`. Este SPEC documenta la **intención de referencia** para un eventual producto **MULTI** y mantiene trazabilidad con HU-002.

---

## 2. Alcance

### 2.1 En alcance (solo si el producto evoluciona a MULTI)

- Selector de empresa en menú de usuario.
- Listado de empresas según acceso del usuario.
- Contexto de empresa en requests (p. ej. `X-Company-Id`) y validación de pertenencia.

### 2.2 Fuera de alcance (repo actual PaqSuite-IA-Partes-Atencion, MONO)

- Toda implementación de cambio de empresa activa.

---

## 3. Actores y contexto

- **Actor (MULTI):** usuario con acceso a una o más sociedades.
- **Datos (MULTI):** `PQ_Empresa`, `Pq_Permiso` con dimensión empresa según modelo legado.

---

## 4. Comportamiento funcional

Ver criterios de aceptación en HU-002 (referencia MULTI): cambio sin cerrar sesión, encabezado o contexto acordado, ocultar selector si una sola empresa.

---

## 5. Criterios verificables

- [ ] **MONO:** ningún flujo ni UI de empresa activa en esta instalación.
- [ ] **MULTI (futuro):** cumplir CA de HU-002 cuando el alcance lo incorpore.

---

## 6. Impacto técnico (visión para TR)

- **MONO:** ninguno.
- **MULTI:** middleware tenancy, resolución de DB/company, actualización de seguridad y menú.

---

## 7. Riesgos y supuestos

- No usar este SPEC como requisito de entrega en MONO; evitar confusión en revisiones de código.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial — consolidación HU-002 MONO/MULTI |

---

**Trazabilidad:** [HU-002](../../03-historias-usuario/000-Generalidades/HU-002-cambio-empresa-activa.md).
