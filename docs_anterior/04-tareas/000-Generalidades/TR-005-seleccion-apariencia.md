# TR-005 – Selección de apariencia (look & feel) por usuario

### 0) Tabla de metadatos

| Campo              | Valor |
|--------------------|-------|
| HU relacionada     | HU-005 – Selección de apariencia (look & feel) por usuario |
| SPEC relacionada   | [Ver SPEC](../../05-open-spec/000-Generalidades/SPEC-005-seleccion-apariencia.md) |
| Épica              | 000 – Generalidades |
| Prioridad          | SHOULD-HAVE |
| Roles              | Usuario |
| Dependencias       | Ver HU/SPEC; TR de seguridad base: migraciones `users`, `pq_*`. |
| Clasificación      | HU SIMPLE |
| Última actualización | 2026-05-09 |
| Estado             | Pendiente |

**Origen:** [HU](../../03-historias-usuario/000-Generalidades/HU-005-seleccion-apariencias.md)

**Referencia SPEC:** [SPEC](../../05-open-spec/000-Generalidades/SPEC-005-seleccion-apariencia.md)

---

### 1) HU Refinada

Derivar del contenido de la HU enlazada y del SPEC; ejecutar backend/frontend/tests según plan en §7.

---

### 2) Criterios de Aceptación (AC)

- [ ] AC de la HU verificados contra el SPEC (prioridad SPEC si hay conflicto de alcance).
- [ ] Comportamiento **MONO** sin tenant operativo.
- [ ] API bajo `/api/v1/` con errores homogéneos si aplica.
- [ ] Selectores `data-testid` en UI crítica si hay frontend.

### Escenarios Gherkin

```gherkin
Feature: SPEC-005-seleccion-apariencia

  Scenario: Flujo feliz principal
    Given un usuario válido según SPEC
    When ejecuta la acción principal de la HU
    Then el sistema responde conforme a los AC

  Scenario: Entrada inválida o no autorizado
    Given credenciales o permisos insuficientes
    When intenta la acción
    Then recibe error controlado sin filtrar datos sensibles

  Scenario: Persistencia coherente con modelo de datos
    Given el esquema en `docs/modelo-datos/`
    When se persiste o consulta información
    Then se usan tablas/columnas alineadas al diccionario MONO
```

---

### 3) Reglas de Negocio

1. Seguir reglas de negocio explícitas en la HU y el SPEC.
2. **MONO:** ignorar dimensión empresa en `pq_permiso` salvo columna legada `id_empresa = 1`.

---

### 4) Impacto en Datos

- Tablas / objetos: Preferencia usuario (columnas según SPEC)
- Migraciones: ejecutar `php artisan migrate`; seed cuando la HU referencie menú o datos base.

---

### 5) Contratos de API (si aplica)

- Definir en implementación según SPEC: métodos, payloads JSON (camelCase en API), códigos 400/401/403/404/422/500 según corresponda.

---

### 6) Cambios Frontend (si aplica)

- Pantallas y componentes indicados en SPEC/HU; estados loading/empty/error; accesibilidad básica.

---

### 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | DB | Confirmar migraciones y semillas necesarias | migrate + seed idempotente |
| T2 | Backend | Servicios/controladores alineados a capas | Validación + errores consistentes |
| T3 | Frontend | UI según SPEC | `data-testid` en controles clave |
| T4 | Tests | Unit + integración API + ≥1 E2E crítico | CI verde |

---

### 8) Estrategia de Tests

- Unit: reglas de dominio y permisos.
- Integration: endpoints con Sanctum.
- E2E: flujo principal sin waits ciegos.

---

### 9) Riesgos y Edge Cases

- Inconsistencia entre `permission_slugs` (json en `users`) y modelo rol/`pq_permiso`: documentar fuente de verdad en implementación (HU de permisos).

---

### 10) Checklist final (para validar HU terminada)

- [ ] AC cumplidos
- [ ] Migración + rollback + seed donde aplique
- [ ] Backend y frontend listos
- [ ] Tests y documentación OpenAPI si hay API nueva
- [ ] IA log / playbook actualizado si el proyecto lo exige
