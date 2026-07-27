# TR-025(MH) – Edición de tipo de tarea

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-025(MH)-edición-de-tipo-de-tarea       |
| Épica              | Épica 6: Gestión de Tipos de Tarea (ABM)  |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-024 (creación / listado)                |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-06                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Edición de tipo de tarea

### Narrativa
**Como** supervisor  
**Quiero** editar la información de un tipo de tarea existente  
**Para** mantener actualizados los datos del catálogo.

### Contexto/Objetivo
Formulario de edición accesible desde el listado. Código en solo lectura; editable: descripción, genérico, por defecto, activo, inhabilitado. Validación descripción no vacía; mismas reglas que creación: por defecto → genérico forzado; solo un tipo por defecto (si se marca por defecto y ya existe otro distinto al actual, error 2117). Al guardar: actualización en BD, mensaje de confirmación, cambios visibles en listado.

### Suposiciones explícitas
- Tabla `PQ_PARTES_TIPOS_TAREA` existe.
- Solo supervisores pueden editar.
- El código no se modifica (identificador estable).

### In Scope
- Acceso a edición desde listado (ej. enlace "Editar" por fila).
- Formulario con datos actuales; código solo lectura.
- Editable: descripción, genérico, por defecto, activo, inhabilitado.
- Validación descripción no vacía; regla por defecto → genérico; único por defecto (2117 al intentar marcar otro).
- PUT/PATCH actualiza tipo; mensaje éxito; reflejo en listado.

### Out of Scope
- Creación/eliminación (HU-024, HU-026).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la edición desde el listado (ej. "Editar" por tipo).
- **AC-02**: Se carga el formulario con los datos actuales del tipo.
- **AC-03**: El código del tipo no es modificable (solo lectura).
- **AC-04**: El supervisor puede modificar: descripción, genérico, por defecto, activo, inhabilitado.
- **AC-05**: El sistema valida que la descripción no esté vacía.
- **AC-06**: Si por defecto = true entonces genérico = true (forzado); si se marca "por defecto", "genérico" se marca y deshabilita.
- **AC-07**: Solo puede haber un tipo por defecto; si se marca "por defecto" y ya existe otro (distinto al actual), error 2117.
- **AC-08**: Si el tipo actual es "por defecto" y se desmarca, se permite (debe quedar al menos un tipo genérico disponible según contexto).
- **AC-09**: Al guardar se actualiza el tipo en BD y se muestra mensaje de confirmación.
- **AC-10**: Los cambios se reflejan en el listado.
- **AC-11**: Si el tipo no existe o no es supervisor: 404 o 403 según diseño.

### Escenarios Gherkin

```gherkin
Feature: Edición de Tipo de Tarea

  Scenario: Editar tipo de tarea
    Given el supervisor está en el listado de tipos de tarea
    When hace clic en "Editar" del tipo "DESARROLLO"
    Then se muestra el formulario con código "DESARROLLO" en solo lectura
    And descripción, genérico, por defecto, activo, inhabilitado editables
    When modifica la descripción y guarda
    Then el tipo se actualiza en la base de datos
    And se muestra mensaje de confirmación
    And al volver al listado se ven los cambios
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden editar tipos de tarea.
2. **RN-02**: El campo `code` no debe ser modificable.
3. **RN-03**: `descripcion` obligatoria (no vacía).
4. **RN-04**: Mismas reglas que creación: por defecto → genérico forzado; único por defecto (2117).

### Permisos por Rol
- **Supervisor:** Puede editar.
- **Empleado (no supervisor):** 403.
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_TIPOS_TAREA`: UPDATE (descripcion, is_generico, is_default, activo, inhabilitado) WHERE id = :id.

### Migración + Rollback
- No se requiere nueva migración.

### Seed Mínimo para Tests
- Tipo de tarea existente; otro tipo con is_default = true para test 2117; usuario supervisor.

---

## 5) Contratos de API

### Endpoint: GET `/api/v1/tipos-tarea/{id}` (si no existe)

**Descripción:** Obtener un tipo de tarea por ID. Solo supervisores.

**Response 200 OK:** Objeto tipo de tarea (id, code, descripcion, is_generico, is_default, activo, inhabilitado, created_at, updated_at).  
**Response 404:** Tipo no encontrado.  
**Response 403:** No supervisor.

### Endpoint: PUT `/api/v1/tipos-tarea/{id}` (o PATCH)

**Descripción:** Actualizar tipo de tarea. Solo supervisores. No se acepta cambio de `code`.

**Request Body:**
```json
{
  "descripcion": "Desarrollo de software y mantenimiento",
  "is_generico": true,
  "is_default": false,
  "activo": true,
  "inhabilitado": false
}
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tipo de tarea actualizado correctamente",
  "resultado": {
    "id": 1,
    "code": "DESARROLLO",
    "descripcion": "Desarrollo de software y mantenimiento",
    "is_generico": true,
    "is_default": false,
    "activo": true,
    "inhabilitado": false,
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Response 422:** Descripción vacía; o ya existe otro tipo por defecto (2117).  
**Response 404:** Tipo no encontrado.  
**Response 403:** No supervisor.

---

## 6) Cambios Frontend

### Pantallas/Componentes
- Formulario de edición (ruta ej. `/tipos-tarea/:id/editar`).
- Código mostrado en solo lectura; campos editables: descripción, genérico, por defecto, activo, inhabilitado.
- Botón Guardar; mensajes de error desde API (incl. 2117).
- data-testid: `tipoTareaEditar.form`, `tipoTareaEditar.code`, `tipoTareaEditar.descripcion`, `tipoTareaEditar.submit`.

### Estados UI
- Loading al cargar y al enviar; Error (422, 404, 2117); Success.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | GET /api/v1/tipos-tarea/{id} (si no existe) + PUT/PATCH | 200/404/422/403 | — | S |
| T2 | Backend  | Validación descripción no vacía y único por defecto (2117) en actualización | 422 si aplica | T1 | S |
| T3 | Frontend | Página edición tipo de tarea (código readonly; por defecto → genérico) | Cumple AC | T1 | M |
| T4 | Tests    | Unit + integration (actualizar ok, validación, 2117, 404, 403) | Tests pasan | T1, T2 | S |
| T5 | Tests    | E2E: listado → Editar → modificar → guardar → listado | ≥1 E2E | T3 | M |
| T6 | Docs     | Specs GET/PUT tipos-tarea/{id} | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit:** Servicio actualización; validación descripción; validación único is_default (2117).
- **Integration:** PUT 200, 422 (descripción vacía, 2117), 404, 403.
- **E2E:** Flujo editar tipo de tarea.

---

## 9) Riesgos y Edge Cases

- Editar tipo inexistente: 404 consistente.
- Id no numérico o inválido: 404 o 422 según convención.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend GET + PUT + validaciones + 2117
- [x] Frontend formulario edición + código readonly
- [x] Unit/integration/E2E ok
- [x] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `TipoTareaController::show`, `update`; `TipoTareaService::getById`, `update`. Código no editable.

### Frontend
- `frontend/src/features/tipoTarea/components/TiposTareaEditarPage.tsx`, estilos en `TiposTareaPage.css`.
- Servicio `getTipoTarea`, `updateTipoTarea`. Ruta `/tipos-tarea/:id/editar`.

### Tests
- `TipoTareaControllerTest::test_show_*`, `test_update_*`. E2E en `tipos-tarea.spec.ts` (editar tipo).

## Comandos ejecutados

- `php artisan test tests/Feature/Api/V1/TipoTareaControllerTest.php`
- `npm run test:e2e` (tipos-tarea).

## Notas y decisiones

- Código mostrado en solo lectura en formulario de edición. Misma regla por defecto → genérico que en creación.

## Pendientes / follow-ups

- Ninguno.
