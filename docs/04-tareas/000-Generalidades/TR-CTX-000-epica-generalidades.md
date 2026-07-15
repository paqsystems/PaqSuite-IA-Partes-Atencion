# TR-CTX-000 – Épica Generalidades: trazabilidad y coordinación de entregas

> Estado del bloque: **histórico / deprecado**. Esta TR-CTX se conserva para trazabilidad del esquema anterior; para lectura operativa vigente de generalidades, consultar `docs/05-open-spec/001-Generalidades/README.md`.

### 0) Tabla de metadatos

| Campo              | Valor |
|--------------------|-------|
| HU relacionada     | N/A — SPEC de contexto de épica (`SPEC-CTX-000`); las HU operativas están en `docs/03-historias-usuario/000-Generalidades/` (HU-001 … HU-010). |
| SPEC relacionada   | [SPEC-CTX-000](../../05-open-spec/000-Generalidades/SPEC-CTX-000-generalidades.md) |
| Épica              | 000 – Generalidades |
| Prioridad          | MUST-HAVE (marco transversal) |
| Roles              | Equipo técnico / PO |
| Dependencias       | Ninguna como bloqueo; las TR por HU concretan el trabajo. |
| Clasificación      | HU COMPLEJA (contenedor) |
| Última actualización | 2026-05-09 |
| Estado             | Pendiente |

**Origen:** documento de épica Open-Spec.

**Referencia SPEC:** [SPEC-CTX-000](../../05-open-spec/000-Generalidades/SPEC-CTX-000-generalidades.md).

---

### 1) HU Refinada

Este TR **no** sustituye a las HU; consolida el **marco MONO** de generalidades (shell, grillas, i18n, parámetros) y enlaza las TR derivadas de cada SPEC operativo del bloque histórico `000-Generalidades`. **In scope:** coherencia entre entregas, dependencias entre TR. **Out of scope:** implementación detallada (cada TR-00x) y el bloque vigente `001-Generalidades`.

---

### 2) Criterios de Aceptación (AC)

- [ ] Cada SPEC operativo en `000-Generalidades` tiene TR enlazada en metadatos y en la HU correspondiente.
- [ ] No se introduce lógica multi-tenant operativa (`X-Company-Id`, `PQ_Empresa`) en entregas MONO.
- [ ] Las preferencias de usuario (idioma, pestañas, apariencia) se modelan por usuario, no por compañía.

### Escenarios Gherkin

```gherkin
Feature: Trazabilidad de épica Generalidades

  Scenario: Desarrollador localiza trabajo por SPEC
    Given un SPEC operativo en 000-Generalidades
    When abre la tabla de metadatos del SPEC
    Then encuentra enlace a la TR y a la HU relacionada

  Scenario: Implementación respeta modo MONO
    Given una tarea de generalidades
    When se implementa backend o API
    Then no se exige cabecera de compañía ni tabla PQ_Empresa operativa
```

---

### 3) Reglas de Negocio

1. Las reglas de negocio operativas viven en cada HU/SPEC concreto; este TR solo fija el marco **MONO** descrito en `SPEC-CTX-000`.

---

### 4) Impacto en Datos

- **Tablas:** según TR derivadas (`pq_grid_layouts`, `pq_parametros_gral`, `users` para preferencias, etc.).
- **Migración + rollback:** por TR operativa; sin DDL adicional solo por este contenedor.

---

### 5) Contratos de API (si aplica)

N/A a nivel de contenedor; ver TR por funcionalidad.

---

### 6) Cambios Frontend (si aplica)

N/A a nivel de contenedor; ver TR por funcionalidad.

---

### 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Docs | Mantener enlaces SPEC ↔ HU ↔ TR al crear o mover TR | Metadatos actualizados |
| T2 | Docs | Revisar dependencias entre TR (p. ej. layouts antes de exportación Excel) | Nota de orden en TR afectadas |

---

### 8) Estrategia de Tests

- Validación manual de enlaces y de que `php artisan migrate` + seed siguen alineados al modelo en `docs/modelo-datos/`.

---

### 9) Riesgos y Edge Cases

- Riesgo de **alcance creep** si se mezclan requisitos MULTI de referencia con MONO; mitigar citando explícitamente `SPEC-CTX-000`.

---

### 10) Checklist final (para validar HU terminada)

- [ ] TR operativas de la épica enlazadas
- [ ] Ninguna entrega contradice decisiones MONO del SPEC de contexto
