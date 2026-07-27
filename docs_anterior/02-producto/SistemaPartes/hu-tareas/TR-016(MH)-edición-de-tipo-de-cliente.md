# TR-016(MH) – Edición de tipo de cliente

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-016(MH)-edición-de-tipo-de-cliente      |
| Épica              | Épica 4: Gestión de Tipos de Cliente (ABM) |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-015 (creación / listado)                |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-06                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Edición de tipo de cliente

### Narrativa
**Como** supervisor  
**Quiero** editar la información de un tipo de cliente existente  
**Para** mantener actualizado el catálogo.

### Contexto/Objetivo
Formulario de edición accesible desde el listado (o detalle). Código en solo lectura; editable: descripción, activo, inhabilitado. Validación descripción no vacía. Al guardar, actualización en BD, mensaje de confirmación y cambios visibles en listado.

### Suposiciones explícitas
- Tabla `PQ_PARTES_TIPOS_CLIENTE` existe.
- Solo supervisores pueden editar.
- El código no se modifica (identificador estable).

### In Scope
- Acceso a edición desde listado (ej. enlace "Editar" por fila).
- Formulario con datos actuales; código solo lectura.
- Editable: descripción, activo, inhabilitado.
- Validación descripción no vacía.
- PUT/PATCH actualiza tipo; mensaje éxito; reflejo en listado.

### Out of Scope
- Creación/eliminación (HU-015, HU-017).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la edición desde el listado (ej. "Editar" por tipo).
- **AC-02**: Se carga el formulario con los datos actuales del tipo.
- **AC-03**: El código del tipo no es modificable (solo lectura).
- **AC-04**: El supervisor puede modificar: descripción, activo, inhabilitado.
- **AC-05**: El sistema valida que la descripción no esté vacía.
- **AC-06**: Al guardar se actualiza el tipo en BD y se muestra mensaje de confirmación.
- **AC-07**: Los cambios se reflejan en el listado de tipos de cliente.
- **AC-08**: Si el tipo no existe o no es supervisor: 404 o 403 según diseño.

### Escenarios Gherkin

```gherkin
Feature: Edición de Tipo de Cliente

  Scenario: Editar tipo de cliente
    Given el supervisor está en el listado de tipos de cliente
    When hace clic en "Editar" del tipo "CORP"
    Then se muestra el formulario con código "CORP" en solo lectura
    And descripción, activo e inhabilitado editables
    When modifica la descripción y guarda
    Then el tipo se actualiza en la base de datos
    And se muestra mensaje de confirmación
    And al volver al listado se ven los cambios
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden editar tipos de cliente.
2. **RN-02**: El campo `code` no debe ser modificable.
3. **RN-03**: `descripcion` obligatoria (no vacía).

### Permisos por Rol
- **Supervisor:** Puede editar.
- **Empleado (no supervisor):** 403.
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_TIPOS_CLIENTE`: UPDATE (descripcion, activo, inhabilitado) WHERE id = :id.

### Migración + Rollback
- No se requiere nueva migración.

### Seed Mínimo para Tests
- Tipo de cliente existente; usuario supervisor.

---

## 5) Contratos de API

### Endpoint: PUT `/api/v1/tipos-cliente/{id}` (o PATCH)

**Descripción:** Actualizar tipo de cliente. Solo supervisores. No se acepta cambio de `code`.

**Autenticación:** Requerida.  
**Autorización:** Solo supervisor → 403.

**Request Body:**
```json
{
  "descripcion": "Corporativo y grandes cuentas",
  "activo": true,
  "inhabilitado": false
}
```

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tipo de cliente actualizado correctamente",
  "resultado": {
    "id": 1,
    "code": "CORP",
    "descripcion": "Corporativo y grandes cuentas",
    "activo": true,
    "inhabilitado": false,
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Response 422:** Descripción vacía.  
**Response 404:** Tipo no encontrado.  
**Response 403:** No supervisor.

---

## 6) Cambios Frontend

### Pantallas/Componentes
- Formulario de edición (ruta ej. `/tipos-cliente/:id/editar`).
- Código mostrado en solo lectura; campos editables: descripción, activo, inhabilitado.
- Botón Guardar; mensajes de error desde API.
- data-testid: `tipoClienteEditar.form`, `tipoClienteEditar.code`, `tipoClienteEditar.descripcion`, `tipoClienteEditar.submit`.

### Estados UI
- Loading al cargar y al enviar; Error (422, 404); Success.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | GET /api/v1/tipos-cliente/{id} (si no existe) + PUT/PATCH | 200/404/422/403 | — | S |
| T2 | Backend  | Validación descripción no vacía en actualización | 422 si vacía | T1 | S |
| T3 | Frontend | Página edición tipo de cliente (código readonly) | Cumple AC | T1 | M |
| T4 | Tests    | Unit + integration (actualizar ok, validación, 404, 403) | Tests pasan | T1, T2 | S |
| T5 | Tests    | E2E: listado → Editar → modificar → guardar → listado | ≥1 E2E | T3 | M |
| T6 | Docs     | Specs GET/PUT tipos-cliente/{id} | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit:** Servicio actualización; validación descripción.
- **Integration:** PUT 200, 422 (descripción vacía), 404, 403.
- **E2E:** Flujo editar tipo de cliente.

---

## 9) Riesgos y Edge Cases

- Editar tipo inexistente: 404 consistente.
- Id no numérico o inválido: 404 o 422 según convención.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend GET + PUT + validaciones
- [x] Frontend formulario edición + código readonly
- [x] Unit/integration/E2E ok
- [x] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `TipoClienteController::show`, `update`; `TipoClienteService::getById`, `update`. Código no editable.

### Frontend
- `frontend/src/features/tipoCliente/components/TiposClienteEditarPage.tsx`, estilos en `TiposClientePage.css`.
- Servicio `getTipoCliente`, `updateTipoCliente`. Ruta `/tipos-cliente/:id/editar`.

### Tests
- `TipoClienteControllerTest::test_show_*`, `test_update_*`. E2E en `tipos-cliente.spec.ts` (editar tipo).

## Comandos ejecutados

- `php artisan test tests/Feature/Api/V1/TipoClienteControllerTest.php`
- `npm run test:e2e` (tipos-cliente).

## Notas y decisiones

- Código mostrado en solo lectura en formulario de edición.

## Pendientes / follow-ups

- Ninguno.
