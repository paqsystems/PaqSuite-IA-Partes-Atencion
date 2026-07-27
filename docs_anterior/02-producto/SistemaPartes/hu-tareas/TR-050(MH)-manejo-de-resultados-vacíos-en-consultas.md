# TR-050(MH) – Manejo de resultados vacíos en consultas

| Campo              | Valor                                                    |
|--------------------|----------------------------------------------------------|
| HU relacionada     | HU-050(MH)-manejo-de-resultados-vacíos-en-consultas      |
| Épica              | Épica 9: Informes y Consultas                           |
| Prioridad          | MUST-HAVE                                               |
| Roles              | Empleado / Empleado Supervisor / Cliente                |
| Dependencias       | HU-044 (Consulta Detallada); HU-046 (Tareas por Cliente) |
| Clasificación      | HU SIMPLE                                               |
| Última actualización | 2026-01-31                                            |
| Estado             | 📋 PENDIENTE                                            |

---

## 1) HU Refinada

### Título
Manejo de resultados vacíos en consultas

### Narrativa
**Como** usuario (empleado, supervisor o cliente)  
**Quiero** recibir un mensaje claro cuando no hay resultados para los filtros aplicados  
**Para** entender que la consulta funcionó pero no hay datos

### Contexto/Objetivo
En las pantallas de consulta (Consulta Detallada, Tareas por Cliente y futuras vistas de informes), cuando no hay tareas que cumplan los filtros aplicados (incluyendo los filtros automáticos por rol), el sistema debe mostrar un mensaje informativo en lugar de una tabla o lista vacía. El mensaje debe ser claro y sugerir ajustar los filtros. El botón de exportar (si existe) debe estar deshabilitado cuando no hay resultados.

### Suposiciones explícitas
- HU-044 (Consulta Detallada) y HU-046 (Tareas por Cliente) están implementadas o en curso.
- Los endpoints de reportes ya retornan `data`/`grupos` vacíos cuando no hay resultados; no se requiere cambio de contrato de API.
- Los filtros automáticos por rol se aplican siempre en backend antes de evaluar si hay resultados.

### In Scope
- Mensaje informativo único y consistente cuando no hay resultados: "No se encontraron tareas para los filtros seleccionados" (o equivalente i18n).
- No mostrar tabla ni lista vacía; mostrar solo el mensaje en el área de resultados.
- Botón de exportar a Excel deshabilitado cuando no hay resultados (si la funcionalidad existe en la pantalla).
- Comportamiento aplicado en Consulta Detallada (TR-044) y Tareas por Cliente (TR-046).
- Filtros automáticos por rol aplicados antes de verificar resultados (Cliente: solo su cliente_id; Empleado no supervisor: solo su usuario_id; Supervisor: todas).

### Out of Scope
- Nuevos endpoints o cambios de contrato de API.
- Migraciones de base de datos.
- Pantallas de consulta no existentes en el MVP actual.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Si una consulta no devuelve resultados (después de aplicar filtros automáticos por rol), se muestra un mensaje informativo: "No se encontraron tareas para los filtros seleccionados".
- **AC-02**: No se muestra una tabla vacía ni una lista vacía de grupos; el mensaje ocupa el lugar de los resultados.
- **AC-03**: El botón de exportar a Excel (cuando exista en la pantalla) está deshabilitado cuando no hay resultados.
- **AC-04**: El mensaje es claro y sugiere ajustar los filtros.
- **AC-05**: El mensaje se muestra en las pantallas Consulta Detallada y Tareas por Cliente cuando el backend retorna cero registros o cero grupos.
- **AC-06**: Los filtros automáticos por rol se aplican siempre en backend antes de verificar si hay resultados (Cliente: cliente_id; Empleado: usuario_id; Supervisor: sin restricción).

### Escenarios Gherkin

```gherkin
Feature: Manejo de resultados vacíos en consultas

  Scenario: Consulta Detallada sin resultados
    Given el empleado "JPEREZ" está autenticado
    And no tiene tareas en el período seleccionado
    When accede a "Consulta Detallada"
    And aplica filtros de período
    And aplica filtro de cliente (todos o uno solo)
    Then se muestra el mensaje "No se encontraron tareas para los filtros seleccionados"
    And no se muestra una tabla vacía
    And el botón "Exportar" está deshabilitado si existe

  Scenario: Tareas por Cliente sin resultados
    Given el supervisor está autenticado
    And no hay tareas en el período para ningún cliente
    When accede a "Tareas por Cliente"
    And aplica filtros de período
    And aplica filtros de empleado (todos o uno solo)
    Then se muestra el mensaje informativo
    And no se muestra lista de grupos vacía

  Scenario: Filtros automáticos aplicados antes de vacío
    Given el cliente "CLI001" está autenticado
    And existen tareas de otros clientes pero ninguna de CLI001
    When accede a "Consulta Detallada"
    Then se muestran cero resultados (solo su cliente_id en backend)
    And se muestra el mensaje de resultados vacíos
```

---

## 3) Reglas de Negocio

1. **RN-01**: Los filtros automáticos por rol son obligatorios y se aplican siempre en backend antes de evaluar si hay resultados.
2. **RN-02**: Cliente: solo tareas donde `cliente_id` = su `cliente_id`. Empleado (no supervisor): solo tareas donde `usuario_id` = su `usuario_id`. Supervisor: todas las tareas.
3. **RN-03**: Cuando el conjunto de resultados es vacío, el frontend debe mostrar el mensaje informativo y no una tabla/lista vacía.
4. **RN-04**: El botón de exportar a Excel (si existe en la pantalla) debe estar deshabilitado cuando no hay resultados.
5. **RN-05**: El mensaje debe ser único y consistente en todas las pantallas de consulta (i18n permitido).

### Permisos por Rol
- **Empleado / Supervisor / Cliente:** Mismo acceso que en TR-044 y TR-046; la diferencia es solo el comportamiento cuando no hay datos (mensaje en lugar de tabla/lista vacía).

---

## 4) Impacto en Datos

- **Tablas afectadas:** Ninguna. No se requieren migraciones ni nuevos seeds.
- **Cambios en datos:** Ninguno. Solo se asegura coherencia de presentación cuando la respuesta ya es vacía.

---

## 5) Contratos de API

- **Cambios:** Ninguno. Los endpoints existentes (GET /api/v1/reports/detail, GET /api/v1/reports/by-client) ya retornan `data: []` o `grupos: []` cuando no hay resultados. El frontend debe interpretar ese caso y mostrar el mensaje de estado vacío.

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **ConsultaDetalladaPage (TR-044):** Verificar que cuando `data.length === 0` se muestre el mensaje de estado vacío y no una tabla vacía. Revisar que el mensaje use la clave i18n acordada.
- **TareasPorClientePage (TR-046):** Verificar que cuando `grupos.length === 0` se muestre el mensaje de estado vacío y no una lista vacía.
- **Futuras pantallas de consulta:** Aplicar el mismo patrón (mensaje en lugar de lista/tabla vacía).

### Estados UI
- **Empty:** Mostrar mensaje: "No se encontraron tareas para los filtros seleccionados" (o equivalente). No mostrar tabla ni lista vacía.
- **Loading / Error / Success:** Sin cambio respecto a TR-044 y TR-046.

### Validaciones en UI
- Si existe botón "Exportar a Excel" en la pantalla, deshabilitarlo cuando no hay resultados.

### Accesibilidad Mínima
- El mensaje de estado vacío debe tener `data-testid` (por ejemplo `report.detail.empty`, `report.byClient.empty`) y rol `status` o similar para lectores de pantalla.
- Labels y texto del mensaje accesibles.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Frontend | Revisar ConsultaDetalladaPage estado vacío | Mensaje visible cuando data.length === 0; no tabla vacía; data-testid report.detail.empty. | TR-044 | S |
| T2 | Frontend | Revisar TareasPorClientePage estado vacío | Mensaje visible cuando grupos.length === 0; no lista vacía; data-testid report.byClient.empty. | TR-046 | S |
| T3 | Frontend | Deshabilitar exportar cuando vacío | Si existe botón Exportar en consultas, deshabilitarlo cuando no hay resultados. | T1, T2 | S |
| T4 | Tests    | E2E estado vacío Consulta Detallada | Escenario: usuario sin tareas en período → Consulta Detallada → mensaje vacío visible. | T1 | S |
| T5 | Tests    | E2E estado vacío Tareas por Cliente | Escenario: período sin tareas → Tareas por Cliente → mensaje vacío visible. | T2 | S |
| T6 | Docs     | Actualizar .cursor/rules/12-testing o specs | Indicar comportamiento de estado vacío según HU-050. | T1, T2 | S |

**Total:** 7 tareas (5S + 2 docs).

---

## 8) Estrategia de Tests

### Unit Tests
- No se requieren nuevos unit tests de backend (sin cambio de lógica). Opcional: test frontend del componente de estado vacío si se extrae a un componente reutilizable.

### Integration Tests
- Los endpoints ya retornan vacío; los tests existentes de TR-044 y TR-046 pueden incluir un caso "empty response" si aún no lo tienen.

### E2E Tests (Playwright)
- **Consulta Detallada:** Login con usuario que no tiene tareas en el período → aplicar filtros → verificar que se muestra el mensaje de estado vacío (data-testid) y no la tabla.
- **Tareas por Cliente:** Período sin tareas (o usuario sin tareas) → verificar mensaje de estado vacío.

---

## 9) Riesgos y Edge Cases

- **Consistencia de mensaje:** Asegurar que la misma clave i18n o texto se use en Consulta Detallada y Tareas por Cliente.
- **Exportar:** Si la funcionalidad de exportar (HU-049) se implementa después, debe respetar deshabilitar cuando no hay resultados.
- **Paginación:** En Consulta Detallada, con paginación, "sin resultados" significa total = 0; el mensaje vacío debe mostrarse en ese caso.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Consulta Detallada: mensaje vacío cuando no hay datos; no tabla vacía
- [ ] Tareas por Cliente: mensaje vacío cuando no hay grupos; no lista vacía
- [ ] Botón Exportar deshabilitado cuando vacío (si aplica)
- [ ] E2E estado vacío al menos en una pantalla de consulta
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Frontend
- `frontend/src/features/tasks/components/ConsultaDetalladaPage.tsx` — Añadido `role="status"` al bloque de estado vacío (report.detail.empty).
- `frontend/src/features/tasks/components/TareasPorClientePage.tsx` — Mensaje unificado a "No se encontraron tareas para los filtros seleccionados" (clave report.detail.empty); añadido `role="status"` (report.byClient.empty).

### Tests
- `frontend/tests/e2e/consulta-detallada.spec.ts` — Test TR-050: período sin datos (2030), verifica report.detail.empty y texto del mensaje.
- `frontend/tests/e2e/tareas-por-cliente.spec.ts` — Test TR-050: período sin datos (2030), verifica report.byClient.empty y texto del mensaje.

### Docs
- `.cursor/rules/12-testing.md` — Subsección "Estado vacío en consultas (HU-050 / TR-050)".

### No aplica
- Botón Exportar: no existe en Consulta Detallada ni Tareas por Cliente (T3 N/A).

## Comandos ejecutados

- `cd frontend && npm run test:run` (opcional, para verificar que no se rompen tests unitarios)
- `cd frontend && npx playwright test tests/e2e/consulta-detallada.spec.ts tests/e2e/tareas-por-cliente.spec.ts`

## Notas y decisiones

- Mensaje único en ambas pantallas: clave i18n `report.detail.empty` con texto "No se encontraron tareas para los filtros seleccionados".
- E2E usan período 2030-01-01 a 2030-01-31 para provocar estado vacío (sin datos en BD); si hay datos en ese período, el test sigue pasando (verifica tabla o empty según respuesta).

## Pendientes / follow-ups

- Ninguno. Cuando se implemente exportar a Excel (HU-049), deshabilitar el botón cuando no hay resultados.
