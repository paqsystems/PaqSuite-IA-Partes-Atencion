# TR-024(MH) – Creación de tipo de tarea

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-024(MH)-creación-de-tipo-de-tarea      |
| Épica              | Épica 6: Gestión de Tipos de Tarea (ABM)  |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-023 (listado tipos de tarea)            |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-06                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Creación de tipo de tarea

### Narrativa
**Como** supervisor  
**Quiero** crear un nuevo tipo de tarea  
**Para** clasificar las tareas registradas en el sistema.

### Contexto/Objetivo
Formulario de alta con código (obligatorio, único), descripción (obligatoria), genérico (checkbox, default false), por defecto (checkbox, default false), activo (default true), inhabilitado (default false). Validaciones: código no vacío y único; descripción no vacía; si por defecto = true entonces genérico = true (forzado); solo puede haber un tipo con por defecto = true (error 2117 si ya existe otro). Al guardar: crear en BD, mensaje de confirmación, redirección al listado o "crear otro".

### Suposiciones explícitas
- Tabla `PQ_PARTES_TIPOS_TAREA` existe (code, descripcion, is_generico, is_default, activo, inhabilitado).
- Solo supervisores pueden crear tipos de tarea.
- Código único a nivel de tabla.
- Código de error 2117 documentado en domain-error-codes (solo un tipo por defecto).

### In Scope
- Formulario: código, descripción, genérico (default false), por defecto (default false), activo (default true), inhabilitado (default false).
- Validaciones: código no vacío, único; descripción no vacía; regla por defecto → genérico forzado; regla único por defecto (2117).
- UI: si se marca "por defecto", "genérico" se marca automáticamente y se deshabilita.
- POST crear tipo de tarea; mensaje éxito; redirección a listado o "crear otro".

### Out of Scope
- Edición/eliminación (HU-025, HU-026).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder al formulario de creación (desde listado "Crear").
- **AC-02**: Campos: Código (obligatorio, único), Descripción (obligatorio), Genérico (checkbox, default false), Por defecto (checkbox, default false), Activo (default true), Inhabilitado (default false).
- **AC-03**: El sistema valida código no vacío (backend y/o frontend).
- **AC-04**: El sistema valida código único (backend; 409 o 422 con código error ej. 4102 o equivalente).
- **AC-05**: El sistema valida descripción no vacía.
- **AC-06**: Si por defecto = true entonces genérico = true (forzado automáticamente).
- **AC-07**: Solo puede haber un tipo con por defecto = true; si se intenta crear otro, error (código 2117).
- **AC-08**: Si se marca "por defecto", el checkbox "genérico" se marca automáticamente y se deshabilita.
- **AC-09**: Al guardar correctamente se crea el tipo en BD y se muestra mensaje de confirmación.
- **AC-10**: Tras crear: redirección al listado de tipos de tarea o opción "Crear otro".
- **AC-11**: Usuario no supervisor no puede acceder (403).

### Escenarios Gherkin

```gherkin
Feature: Creación de Tipo de Tarea

  Scenario: Crear tipo de tarea válido
    Given el supervisor está en el formulario de creación de tipo de tarea
    When ingresa código "SOPORTE" y descripción "Soporte técnico"
    And deja genérico=false, por defecto=false, activo=true
    And envía el formulario
    Then el tipo se crea en la base de datos
    And se muestra mensaje de confirmación
    And es redirigido al listado o puede crear otro

  Scenario: Código duplicado
    Given ya existe un tipo con código "DESARROLLO"
    When el supervisor intenta crear otro tipo con código "DESARROLLO"
    Then el sistema no crea el tipo
    And muestra error de código único

  Scenario: Segundo tipo por defecto
    Given ya existe un tipo con por defecto = true
    When el supervisor intenta crear otro tipo con por defecto = true
    Then el sistema no crea el tipo
    And muestra error con código 2117
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores pueden crear tipos de tarea.
2. **RN-02**: `code` obligatorio y único.
3. **RN-03**: `descripcion` obligatoria.
4. **RN-04**: Si `is_default = true` entonces `is_generico = true` (forzado).
5. **RN-05**: Solo puede haber un tipo de tarea con `is_default = true` en todo el sistema. Código de error: **2117**.

### Permisos por Rol
- **Supervisor:** Puede crear.
- **Empleado (no supervisor):** 403.
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- `PQ_PARTES_TIPOS_TAREA`: INSERT (code, descripcion, is_generico, is_default, activo, inhabilitado).

### Migración + Rollback
- No se requiere nueva migración; tabla existe.

### Seed Mínimo para Tests
- Tipo con código único; tipo ya existente con is_default = true para test 2117; usuario supervisor.

---

## 5) Contratos de API

### Endpoint: POST `/api/v1/tipos-tarea`

**Descripción:** Crear tipo de tarea. Solo supervisores. Validar único por defecto (2117) y código único.

**Autenticación:** Requerida.  
**Autorización:** Solo supervisor → 403 si no.

**Request Body:**
```json
{
  "code": "SOPORTE",
  "descripcion": "Soporte técnico",
  "is_generico": false,
  "is_default": false,
  "activo": true,
  "inhabilitado": false
}
```

**Response 201 Created:**
```json
{
  "error": 0,
  "respuesta": "Tipo de tarea creado correctamente",
  "resultado": {
    "id": 5,
    "code": "SOPORTE",
    "descripcion": "Soporte técnico",
    "is_generico": false,
    "is_default": false,
    "activo": true,
    "inhabilitado": false,
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Response 422:** Validación (code vacío, descripcion vacía).  
**Response 409 (o 422):** Código duplicado (código error ej. 4102).  
**Response 422:** Ya existe otro tipo por defecto (código **2117**).  
**Response 403:** No supervisor.

---

## 6) Cambios Frontend

### Pantallas/Componentes
- Formulario de creación (ruta ej. `/tipos-tarea/nuevo`).
- Campos: código, descripción, genérico, por defecto, activo, inhabilitado.
- Validación en UI: requeridos; si "por defecto" se marca, "genérico" se marca y deshabilita.
- Botón Guardar; tras éxito redirección o "Crear otro".
- data-testid: `tipoTareaCrear.form`, `tipoTareaCrear.code`, `tipoTareaCrear.descripcion`, `tipoTareaCrear.generico`, `tipoTareaCrear.porDefecto`, `tipoTareaCrear.submit`.

### Estados UI
- Loading al enviar, Error (validación, 409, 2117), Success.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Backend  | POST /api/v1/tipos-tarea + validación + código único + regla único por defecto (2117) | 201/422/409/403 | — | M |
| T2 | Frontend | Formulario creación tipo de tarea (por defecto → genérico forzado y deshabilitado) | Cumple AC | T1 | M |
| T3 | Tests    | Unit + integration (crear ok, validación, duplicado, 2117, 403) | Tests pasan | T1 | S |
| T4 | Tests    | E2E: listado → Crear → llenar → guardar → listado | ≥1 E2E | T2 | M |
| T5 | Docs     | Specs POST tipos-tarea; código 2117 en domain-error-codes | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Unit:** Servicio creación; validación código único; validación único is_default (2117).
- **Integration:** POST 201, 422 (campos vacíos), 409/422 (código duplicado), 422 (2117), 403.
- **E2E:** Flujo completo crear tipo de tarea.

---

## 9) Riesgos y Edge Cases

- Dos requests simultáneos marcando por defecto: constraint o transacción; código 2117 consistente.
- Código 2117 debe estar documentado en specs/errors/domain-error-codes.md (ya existe).

---

## 10) Checklist Final

- [x] AC cumplidos
- [x] Backend POST + validaciones + 2117
- [x] Frontend formulario + mensajes
- [x] Unit/integration/E2E ok
- [x] Docs actualizados

---

## Archivos creados/modificados

### Backend
- `TipoTareaController::store`, `TipoTareaService::create`, validación code/descripcion, único is_default (2117), 409 código duplicado (4102).

### Frontend
- `frontend/src/features/tipoTarea/components/TiposTareaNuevaPage.tsx`, estilos en `TiposTareaPage.css`.
- Servicio `createTipoTarea` en `tipoTarea.service.ts`. Ruta `/tipos-tarea/nuevo`.

### Tests
- `TipoTareaControllerTest::test_store_*`. E2E en `tipos-tarea.spec.ts` (crear tipo).

## Comandos ejecutados

- `php artisan test tests/Feature/Api/V1/TipoTareaControllerTest.php`
- `npm run test:e2e` (tipos-tarea).

## Notas y decisiones

- Si is_default=true se fuerza is_generico=true en backend y en frontend (checkbox genérico deshabilitado cuando por defecto está marcado).

## Pendientes / follow-ups

- Ninguno.
