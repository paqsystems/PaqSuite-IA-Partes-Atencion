# TR-015(MH) – Creación de tipo de cliente

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-015(MH)-creación-de-tipo-de-cliente     |
| Épica              | Épica 4: Gestión de Tipos de Cliente (ABM) |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-014 (listado tipos de cliente)          |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-06                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Creación de tipo de cliente

### Narrativa
**Como** supervisor  
**Quiero** crear un nuevo tipo de cliente  
**Para** clasificar los clientes del sistema.

### Contexto/Objetivo
Formulario de alta de tipo de cliente con código (obligatorio, único), descripción (obligatoria), activo (checkbox, default true), inhabilitado (checkbox, default false). Validaciones en backend y frontend. Tras guardar, mensaje de confirmación y redirección al listado o opción de crear otro.

### Suposiciones explícitas
- Tabla `PQ_PARTES_TIPOS_CLIENTE` existe (code, descripcion, activo, inhabilitado).
- Solo supervisores pueden crear tipos de cliente.
- Código único a nivel de tabla.

### In Scope
- Formulario: código, descripción, activo (default true), inhabilitado (default false).
- Validaciones: código no vacío, único; descripción no vacía.
- POST crear tipo de cliente; mensaje éxito; redirección a listado o "crear otro".

### Out of Scope
- Edición/eliminación (HU-016, HU-017).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder al formulario de creación (desde listado "Crear").
- **AC-02**: Campos: Código (obligatorio, único), Descripción (obligatorio), Activo (checkbox, default true), Inhabilitado (checkbox, default false).
- **AC-03**: El sistema valida código no vacío (backend y/o frontend).
- **AC-04**: El sistema valida código único (backend; 409 o 422 con código error ej. 4102).
- **AC-05**: El sistema valida descripción no vacía.
- **AC-06**: Al guardar correctamente se crea el tipo en BD y se muestra mensaje de confirmación.
- **AC-07**: Tras crear: redirección al listado de tipos de cliente o opción "Crear otro".
- **AC-08**: Usuario no supervisor no puede acceder (403).

### Escenarios Gherkin

```gherkin
Feature: Creación de Tipo de Cliente

  Scenario: Crear tipo de cliente válido
    Given el supervisor está en el formulario de creación de tipo de cliente
    When ingresa código "PYME" y descripción "Pequeña y mediana empresa"
    And deja activo=true e inhabilitado=false
    And envía el formulario
    Then el tipo se crea en la base de datos
    And se muestra mensaje de confirmación
    And es redirigido al listado o puede crear otro

  Scenario: Código duplicado
    Given ya existe un tipo con código "CORP"
    When el supervisor intenta crear otro tipo con código "CORP"
    Then el sistema no crea el tipo
    And muestra error de código único
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden crear tipos de cliente.
2. **RN-02**: `code` obligatorio y único.
3. **RN-03**: `descripcion` obligatoria.
4. **RN-04**: `activo` e `inhabilitado` booleanos con valores por defecto true y false.

### Permisos por Rol
- **Supervisor:** Puede crear.
- **Empleado (no supervisor):** 403.
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_TIPOS_CLIENTE`: INSERT (code, descripcion, activo, inhabilitado).

### Migración + Rollback
- No se requiere nueva migración; tabla existe.

### Seed Mínimo para Tests
- Tipo con código único para tests de duplicado; usuario supervisor.

---

## 5) Contratos de API

### Endpoint: POST `/api/v1/tipos-cliente`

**Descripción:** Crear tipo de cliente. Solo supervisores.

**Autenticación:** Requerida.  
**Autorización:** Solo supervisor → 403 si no.

**Request Body:**
```json
{
  "code": "PYME",
  "descripcion": "Pequeña y mediana empresa",
  "activo": true,
  "inhabilitado": false
}
```

**Response 201 Created:**
```json
{
  "error": 0,
  "respuesta": "Tipo de cliente creado correctamente",
  "resultado": {
    "id": 2,
    "code": "PYME",
    "descripcion": "Pequeña y mediana empresa",
    "activo": true,
    "inhabilitado": false,
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Response 422:** Validación (code vacío, descripcion vacía).  
**Response 409 (o 422):** Código duplicado (código error ej. 4102).  
**Response 403:** No supervisor.

---

## 6) Cambios Frontend

### Pantallas/Componentes
- Formulario de creación (ruta ej. `/tipos-cliente/nuevo`).
- Campos: código, descripción, activo, inhabilitado.
- Validación en UI: requeridos; mensajes de error desde API.
- Botón Guardar; tras éxito redirección o "Crear otro".
- data-testid: `tipoClienteCrear.form`, `tipoClienteCrear.code`, `tipoClienteCrear.descripcion`, `tipoClienteCrear.submit`.

### Estados UI
- Loading al enviar, Error (validación o 409), Success.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | POST /api/v1/tipos-cliente + validación + código único | 201/422/409/403 | — | M |
| T2 | Frontend | Formulario creación tipo de cliente | Cumple AC | T1 | M |
| T3 | Tests    | Unit + integration (crear ok, validación, duplicado, 403) | Tests pasan | T1 | S |
| T4 | Tests    | E2E: listado → Crear → llenar → guardar → listado | ≥1 E2E | T2 | M |
| T5 | Docs     | Specs POST tipos-cliente | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit:** Servicio creación; validación código único.
- **Integration:** POST 201, 422 (campos vacíos), 409/422 (código duplicado), 403.
- **E2E:** Flujo completo crear tipo de cliente.

---

## 9) Riesgos y Edge Cases

- Código duplicado por concurrencia: constraint único en BD + código error consistente.

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend POST + validaciones
- [x] Frontend formulario + mensajes
- [x] Unit/integration/E2E ok
- [x] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `TipoClienteController::store`, `TipoClienteService::create`, validación code/descripcion, 409 código duplicado (4102).

### Frontend
- `frontend/src/features/tipoCliente/components/TiposClienteNuevaPage.tsx`, estilos en `TiposClientePage.css`.
- Servicio `createTipoCliente` en `tipoCliente.service.ts`. Ruta `/tipos-cliente/nuevo`.

### Tests
- `TipoClienteControllerTest::test_store_*`. E2E en `tipos-cliente.spec.ts` (crear tipo).

## Comandos ejecutados

- `php artisan test tests/Feature/Api/V1/TipoClienteControllerTest.php`
- `npm run test:e2e` (tipos-cliente).

## Notas y decisiones

- Código único validado en servicio; 409 con error 4102. Formulario redirige al listado tras crear.

## Pendientes / follow-ups

- Ninguno.
