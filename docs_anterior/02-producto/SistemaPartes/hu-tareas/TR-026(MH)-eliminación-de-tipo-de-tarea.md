# TR-026(MH) – Eliminación de tipo de tarea

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-026(MH)-eliminación-de-tipo-de-tarea   |
| Épica              | Épica 6: Gestión de Tipos de Tarea (ABM)  |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-025 (edición / listado)                 |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-06                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Eliminación de tipo de tarea

### Narrativa
**Como** supervisor  
**Quiero** eliminar un tipo de tarea que ya no se utiliza  
**Para** mantener el catálogo limpio.

### Contexto/Objetivo
Opción eliminar desde listado o detalle. Antes de eliminar: verificar si el tipo tiene tareas asociadas (`PQ_PARTES_REGISTRO_TAREA`) o clientes asociados (`PQ_PARTES_CLIENTE_TIPO_TAREA` o equivalente). Si tiene referencias → error 422 con código **2114** y no se permite. Si no tiene → diálogo de confirmación (código y descripción); al confirmar, DELETE en BD, mensaje de éxito y el tipo desaparece del listado.

### Suposiciones explícitas
- Tabla `PQ_PARTES_TIPOS_TAREA`; `PQ_PARTES_REGISTRO_TAREA` con FK tipo_tarea_id; tabla de relación cliente–tipo tarea con FK tipo_tarea_id.
- No se puede eliminar tipo con tareas registradas ni con clientes asociados (ClienteTipoTarea). Código de error: **2114** (especificado en HU y domain-error-codes).

### In Scope
- Acción "Eliminar" desde listado o detalle.
- Backend: verificar registros en RegistroTarea y ClienteTipoTarea; si hay, retornar 422 (código 2114); si no, DELETE.
- Diálogo de confirmación con código y descripción del tipo.
- Tras confirmar: DELETE, mensaje éxito, actualizar listado (o redirigir).

### Out of Scope
- Eliminación física vs soft delete: según diseño (típicamente DELETE físico).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la opción de eliminar desde listado o detalle.
- **AC-02**: Antes de eliminar el sistema verifica si el tipo tiene tareas asociadas o clientes asociados.
- **AC-03**: Si tiene tareas o clientes asociados: se muestra error y no se permite la eliminación (código 2114).
- **AC-04**: Si no tiene referencias: se muestra diálogo de confirmación.
- **AC-05**: El diálogo muestra código y descripción del tipo a eliminar.
- **AC-06**: El usuario debe confirmar la eliminación.
- **AC-07**: Al confirmar se elimina el tipo de la BD y se muestra mensaje de confirmación.
- **AC-08**: El tipo desaparece del listado (o se redirige al listado actualizado).
- **AC-09**: Usuario no supervisor no puede eliminar (403).

### Escenarios Gherkin

```gherkin
Feature: Eliminación de Tipo de Tarea

  Scenario: Eliminar tipo sin referencias
    Given el supervisor está en el listado de tipos de tarea
    And existe el tipo "TEST" sin tareas ni clientes asociados
    When hace clic en "Eliminar" del tipo "TEST"
    Then se muestra el diálogo de confirmación con código y descripción
    When confirma la eliminación
    Then el tipo se elimina de la base de datos
    And se muestra mensaje de confirmación
    And el tipo ya no aparece en el listado

  Scenario: No eliminar tipo con referencias
    Given el tipo "DESARROLLO" tiene al menos una tarea o un cliente asociado
    When el supervisor intenta eliminar el tipo "DESARROLLO"
    Then el sistema no elimina el tipo
    And muestra error indicando que está en uso (código 2114)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden eliminar tipos de tarea.
2. **RN-02**: No se puede eliminar un tipo de tarea si tiene tareas asociadas (RegistroTarea) o clientes asociados (ClienteTipoTarea). Código de error: **2114**.
3. **RN-03**: Eliminación solo tras confirmación explícita del usuario.

### Permisos por Rol
- **Supervisor:** Puede eliminar (si no hay referencias).
- **Empleado (no supervisor):** 403.
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_TIPOS_TAREA`: DELETE WHERE id = :id (solo si no existe FK desde PQ_PARTES_REGISTRO_TAREA ni desde tabla cliente_tipo_tarea).
- `PQ_PARTES_REGISTRO_TAREA`: verificación COUNT(*) WHERE tipo_tarea_id = :id.
- Tabla cliente–tipo tarea: verificación de existencia de filas con tipo_tarea_id = :id.

### Migración + Rollback
- No se requiere nueva migración; FKs ya deben existir.

### Seed Mínimo para Tests
- Tipo con 0 referencias (eliminable); tipo con 1+ tareas o clientes (no eliminable); usuario supervisor.

---

## 5) Contratos de API

### Endpoint: DELETE `/api/v1/tipos-tarea/{id}`

**Descripción:** Eliminar tipo de tarea. Solo si no tiene tareas ni clientes asociados. Solo supervisores.

**Autenticación:** Requerida.  
**Autorización:** Solo supervisor → 403.

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tipo de tarea eliminado correctamente",
  "resultado": {}
}
```

**Response 422 (tipo en uso):**
```json
{
  "error": 2114,
  "respuesta": "No se puede eliminar el tipo de tarea porque está en uso (tareas o clientes asociados).",
  "resultado": {}
}
```

**Response 404:** Tipo no encontrado.  
**Response 403:** No supervisor.

---

## 6) Cambios Frontend

### Pantallas/Componentes
- Botón/enlace "Eliminar" en listado (y opcionalmente en detalle).
- Diálogo de confirmación: mensaje con código y descripción del tipo; botones Cancelar y Confirmar.
- Llamada DELETE al confirmar; si 422 con código 2114: mostrar mensaje "está en uso"; si 200: mensaje éxito y actualizar listado.
- data-testid: `tipoTareaEliminar.boton`, `tipoTareaEliminar.dialogo`, `tipoTareaEliminar.confirmar`, `tipoTareaEliminar.cancelar`.

### Estados UI
- Loading al eliminar; Error (2114 u otro); Success.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | DELETE /api/v1/tipos-tarea/{id} + verificación tareas y clientes | 200 o 422 (2114) o 404/403 | — | M |
| T2 | Frontend | Diálogo confirmación eliminar (código + descripción) | Cumple AC | T1 | S |
| T3 | Frontend | Integrar eliminación en listado (y detalle si aplica) | Mensajes 2114 y éxito; actualizar listado | T2 | S |
| T4 | Tests    | Unit + integration (eliminar ok, 422 con 2114, 404, 403) | Tests pasan | T1 | S |
| T5 | Tests    | E2E: eliminar tipo sin referencias; intentar eliminar con referencias | ≥1 E2E | T3 | M |
| T6 | Docs     | Specs DELETE tipos-tarea; código 2114 en domain-error-codes | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit:** Servicio eliminación; verificación tareas y clientes asociados; retorno 2114 cuando aplica.
- **Integration:** DELETE 200 (sin referencias), 422 con error 2114 (con referencias), 404, 403.
- **E2E:** Confirmar eliminación; rechazar eliminación cuando hay referencias.

---

## 9) Riesgos y Edge Cases

- Tipo eliminado por otro usuario entre abrir diálogo y confirmar: 404 al DELETE; mostrar mensaje acorde.
- Código 2114 ya documentado en specs/errors/domain-error-codes.md.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend DELETE + verificación referencias + código 2114
- [x] Frontend diálogo + mensajes
- [x] Unit/integration/E2E ok
- [x] Docs (incl. código 2114) actualizados

---

## Archivos creados/modificados

### Backend
- `TipoTareaController::destroy`, `TipoTareaService::delete` (verificación registrosTarea y clientes; 422 con error 2114).

### Frontend
- Modal de confirmación en `TiposTareaPage.tsx`; mensaje específico cuando API devuelve 2114 (está en uso).

### Tests
- `TipoTareaControllerTest::test_destroy_*`. E2E en `tipos-tarea.spec.ts` (eliminar tipo).

## Comandos ejecutados

- `php artisan test tests/Feature/Api/V1/TipoTareaControllerTest.php`
- `npm run test:e2e` (tipos-tarea).

## Notas y decisiones

- Código 2114 documentado en specs/errors/domain-error-codes.md. Frontend muestra mensaje claro cuando no se puede eliminar por estar en uso.

## Pendientes / follow-ups

- Ninguno.
