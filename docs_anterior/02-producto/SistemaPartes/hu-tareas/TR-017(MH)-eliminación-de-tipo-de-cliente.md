# TR-017(MH) – Eliminación de tipo de cliente

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-017(MH)-eliminación-de-tipo-de-cliente  |
| Épica              | Épica 4: Gestión de Tipos de Cliente (ABM) |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-016 (edición / listado)                 |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-06                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Eliminación de tipo de cliente

### Narrativa
**Como** supervisor  
**Quiero** eliminar un tipo de cliente que ya no se utiliza  
**Para** mantener el catálogo limpio.

### Contexto/Objetivo
Opción eliminar desde listado o detalle. Antes de eliminar: verificar si hay clientes asociados al tipo. Si hay clientes asociados → error y no se permite. Si no hay → diálogo de confirmación mostrando código y descripción; al confirmar, DELETE en BD, mensaje de éxito y el tipo desaparece del listado.

### Suposiciones explícitas
- Tabla `PQ_PARTES_TIPOS_CLIENTE`; tabla `PQ_PARTES_CLIENTES` con FK tipo_cliente_id.
- No se puede eliminar tipo con clientes asociados (integridad referencial).
- Código de error de negocio: 2115 (tipo tiene clientes asociados) según HU.

### In Scope
- Acción "Eliminar" desde listado o detalle.
- Backend: verificar clientes asociados; si hay, retornar error 422 (código 2115); si no, DELETE.
- Diálogo de confirmación con código y descripción del tipo.
- Tras confirmar: DELETE, mensaje éxito, actualizar listado (o redirigir).

### Out of Scope
- Eliminación física vs soft delete: según diseño del proyecto (PQ_PARTES_TIPOS_CLIENTE sin soft delete típicamente es DELETE físico).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la opción de eliminar desde listado o detalle.
- **AC-02**: Antes de eliminar el sistema verifica si el tipo tiene clientes asociados.
- **AC-03**: Si tiene clientes asociados: se muestra error y no se permite la eliminación (código 2115).
- **AC-04**: Si no tiene clientes: se muestra diálogo de confirmación.
- **AC-05**: El diálogo muestra código y descripción del tipo a eliminar.
- **AC-06**: El usuario debe confirmar la eliminación.
- **AC-07**: Al confirmar se elimina el tipo de la BD y se muestra mensaje de confirmación.
- **AC-08**: El tipo desaparece del listado (o se redirige al listado actualizado).
- **AC-09**: Usuario no supervisor no puede eliminar (403).

### Escenarios Gherkin

```gherkin
Feature: Eliminación de Tipo de Cliente

  Scenario: Eliminar tipo sin clientes asociados
    Given el supervisor está en el listado de tipos de cliente
    And existe el tipo "TEST" sin clientes asociados
    When hace clic en "Eliminar" del tipo "TEST"
    Then se muestra el diálogo de confirmación con código y descripción
    When confirma la eliminación
    Then el tipo se elimina de la base de datos
    And se muestra mensaje de confirmación
    And el tipo ya no aparece en el listado

  Scenario: No eliminar tipo con clientes asociados
    Given el tipo "CORP" tiene al menos un cliente asociado
    When el supervisor intenta eliminar el tipo "CORP"
    Then el sistema no elimina el tipo
    And muestra error indicando que tiene clientes asociados (código 2115)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden eliminar tipos de cliente.
2. **RN-02**: No se puede eliminar un tipo de cliente si tiene clientes asociados (integridad referencial). Código de error: **2115**.
3. **RN-03**: Eliminación solo tras confirmación explícita del usuario.

### Permisos por Rol
- **Supervisor:** Puede eliminar (si no hay clientes asociados).
- **Empleado (no supervisor):** 403.
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_TIPOS_CLIENTE`: DELETE WHERE id = :id (solo si no existe FK desde PQ_PARTES_CLIENTES).
- `PQ_PARTES_CLIENTES`: verificación COUNT(*) WHERE tipo_cliente_id = :id antes del DELETE.

### Migración + Rollback
- No se requiere nueva migración; FK ya debe existir.

### Seed Mínimo para Tests
- Tipo con 0 clientes (eliminable); tipo con 1+ clientes (no eliminable); usuario supervisor.

---

## 5) Contratos de API

### Endpoint: DELETE `/api/v1/tipos-cliente/{id}`

**Descripción:** Eliminar tipo de cliente. Solo si no tiene clientes asociados. Solo supervisores.

**Autenticación:** Requerida.  
**Autorización:** Solo supervisor → 403.

**Response 200 OK:**
```json
{
  "error": 0,
  "respuesta": "Tipo de cliente eliminado correctamente",
  "resultado": {}
}
```

**Response 422 (tipo con clientes asociados):**
```json
{
  "error": 2115,
  "respuesta": "No se puede eliminar el tipo de cliente porque tiene clientes asociados.",
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
- Llamada DELETE al confirmar; si 422 con código 2115: mostrar mensaje "tiene clientes asociados"; si 200: mensaje éxito y actualizar listado (o redirigir).
- data-testid: `tipoClienteEliminar.boton`, `tipoClienteEliminar.dialogo`, `tipoClienteEliminar.confirmar`, `tipoClienteEliminar.cancelar`.

### Estados UI
- Loading al eliminar; Error (2115 u otro); Success.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | DELETE /api/v1/tipos-cliente/{id} + verificación clientes | 200 o 422 (2115) o 404/403 | — | M |
| T2 | Frontend | Diálogo confirmación eliminar (código + descripción) | Cumple AC | T1 | S |
| T3 | Frontend | Integrar eliminación en listado (y detalle si aplica) | Mensajes 2115 y éxito; actualizar listado | T2 | S |
| T4 | Tests    | Unit + integration (eliminar ok, 422 con 2115, 404, 403) | Tests pasan | T1 | S |
| T5 | Tests    | E2E: eliminar tipo sin clientes; intentar eliminar con clientes | ≥1 E2E | T3 | M |
| T6 | Docs     | Specs DELETE tipos-cliente; código 2115 en domain-error-codes | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit:** Servicio eliminación; verificación clientes asociados; lanzar excepción o retorno con código 2115.
- **Integration:** DELETE 200 (sin clientes), 422 con error 2115 (con clientes), 404, 403.
- **E2E:** Confirmar eliminación; rechazar eliminación cuando hay clientes.

---

## 9) Riesgos y Edge Cases

- Tipo eliminado por otro usuario entre abrir diálogo y confirmar: 404 al DELETE; mostrar mensaje acorde.
- Código 2115 debe estar documentado en specs/errors/domain-error-codes.md.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend DELETE + verificación clientes + código 2115
- [x] Frontend diálogo + mensajes
- [x] Unit/integration/E2E ok
- [x] Docs (incl. código 2115) actualizados

---

## Archivos creados/modificados

### Backend
- `TipoClienteController::destroy`, `TipoClienteService::delete` (verificación `clientes()->exists()`; 422 con error 2115).

### Frontend
- Modal de confirmación en `TiposClientePage.tsx`; mensaje específico cuando API devuelve 2115 (tiene clientes asociados).

### Tests
- `TipoClienteControllerTest::test_destroy_*`. E2E en `tipos-cliente.spec.ts` (eliminar tipo).

## Comandos ejecutados

- `php artisan test tests/Feature/Api/V1/TipoClienteControllerTest.php`
- `npm run test:e2e` (tipos-cliente).

## Notas y decisiones

- Código 2115 documentado en specs/errors/domain-error-codes.md. Frontend muestra mensaje claro cuando no se puede eliminar por tener clientes.

## Pendientes / follow-ups

- Ninguno.
