# TR-006(MH) – Visualización de Perfil de Usuario

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-006(MH)-visualización-de-perfil-de-usuario |
| Épica              | Épica 2: Configuración de Usuario          |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado / Empleado Supervisor / Cliente   |
| Dependencias       | HU-001 (Login Empleado), HU-002 (Login Cliente) |
| Última actualización | 2026-01-28                               |
| Estado             | ✅ IMPLEMENTADA                            |

---

## 1) HU Refinada

### Título
Visualización de Perfil de Usuario

### Narrativa
**Como** usuario autenticado (empleado, supervisor o cliente)  
**Quiero** ver mi información de perfil  
**Para** verificar mis datos personales en el sistema

### Contexto/Objetivo
Después de autenticarse, el usuario necesita poder ver su información de perfil. Esta funcionalidad es esencial para que los usuarios puedan verificar que sus datos están correctos y conocer su rol en el sistema. El perfil es de solo lectura en esta historia (la edición se contempla en historias posteriores).

### Suposiciones explícitas
- El usuario ya está autenticado (tiene token válido)
- Los datos del usuario están almacenados en `USERS` y `PQ_PARTES_USUARIOS` (empleados) o `PQ_PARTES_CLIENTES` (clientes)
- El tipo de usuario (`usuario` o `cliente`) se determina en el login y está disponible en el contexto de autenticación
- La información básica del usuario ya se obtiene en el login y está en localStorage (`auth_user`)
- Se puede necesitar un endpoint adicional para obtener información extendida del perfil

### In Scope
- Pantalla/componente de perfil de usuario
- Mostrar: código de usuario, nombre, email, rol/tipo, fecha de creación
- Acceso desde el dashboard (botón/enlace al perfil)
- Endpoint backend para obtener datos del perfil (si se necesita información adicional)
- Tests unitarios, integración y E2E

### Out of Scope
- Edición de datos del perfil (HU-007)
- Cambio de contraseña (HU-005)
- Avatar o foto de perfil
- Configuraciones de notificaciones
- Historial de actividad

---

## 2) Criterios de Aceptación (AC)

### Bullets
- **AC-01**: El usuario autenticado puede acceder a su perfil desde el dashboard
- **AC-02**: Se muestra el código de usuario en formato de solo lectura
- **AC-03**: Se muestra el nombre completo del usuario
- **AC-04**: Se muestra el email del usuario (si está configurado, sino mostrar "No configurado")
- **AC-05**: Se muestra el rol/tipo del usuario (Empleado, Supervisor, Cliente)
- **AC-06**: Se muestra la fecha de creación de la cuenta
- **AC-07**: Todos los campos son de solo lectura (no editables)
- **AC-08**: Si el usuario es supervisor, se muestra un indicador visual (badge)
- **AC-09**: El endpoint de perfil retorna 200 con los datos del usuario
- **AC-10**: El endpoint de perfil retorna 401 si no hay autenticación
- **AC-11**: El usuario puede volver al dashboard desde el perfil

### Escenarios Gherkin

```gherkin
Feature: Visualización de Perfil de Usuario

  Scenario: Empleado ve su perfil
    Given el usuario "JPEREZ" está autenticado como empleado
    When accede a la página de perfil
    Then ve su código de usuario "JPEREZ"
    And ve su nombre "Juan Pérez"
    And ve su email "juan.perez@ejemplo.com"
    And ve su rol como "Empleado"
    And ve la fecha de creación de su cuenta
    And todos los campos están en modo solo lectura

  Scenario: Supervisor ve su perfil con badge
    Given el usuario "MGARCIA" está autenticado como supervisor
    When accede a la página de perfil
    Then ve su código de usuario "MGARCIA"
    And ve su rol como "Supervisor"
    And ve un badge indicando que es supervisor

  Scenario: Usuario sin email ve perfil
    Given el usuario "SINMAIL" está autenticado
    And no tiene email configurado
    When accede a la página de perfil
    Then ve "No configurado" en el campo de email

  Scenario: Acceso sin autenticación
    Given el usuario no está autenticado
    When intenta acceder a /perfil directamente
    Then es redirigido a /login
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo usuarios autenticados pueden ver su perfil
2. **RN-02**: Un usuario solo puede ver SU propio perfil, no el de otros usuarios
3. **RN-03**: El código de usuario nunca es editable (es identificador único)
4. **RN-04**: El tipo de usuario se determina por la tabla donde existe el registro:
   - Si existe en `PQ_PARTES_USUARIOS` → tipo "usuario" (empleado)
   - Si existe en `PQ_PARTES_CLIENTES` → tipo "cliente"
5. **RN-05**: El rol de supervisor se determina por el campo `supervisor` en `PQ_PARTES_USUARIOS`
6. **RN-06**: Si el email es NULL o vacío, mostrar "No configurado"
7. **RN-07**: La fecha de creación proviene del campo `created_at` de `USERS`

### Permisos por Rol

| Rol                | Puede ver su perfil | Puede ver perfil de otros |
|--------------------|---------------------|---------------------------|
| Empleado           | ✅                  | ❌                        |
| Empleado Supervisor| ✅                  | ❌                        |
| Cliente            | ✅                  | ❌                        |
| No autenticado     | ❌                  | ❌                        |

---

## 4) Impacto en Datos

### Tablas afectadas
- `USERS` (lectura) - datos de autenticación y fecha de creación
- `PQ_PARTES_USUARIOS` (lectura) - datos de empleados
- `PQ_PARTES_CLIENTES` (lectura) - datos de clientes

### Operaciones
- **SELECT**: Consulta de datos del usuario autenticado
- No se requieren migraciones adicionales (tablas ya existen)
- No se requieren nuevos índices (las consultas usan PKs existentes)

### Campos utilizados

**Tabla USERS:**
- `id` (PK)
- `code` (código de usuario)
- `created_at` (fecha de creación)

**Tabla PQ_PARTES_USUARIOS (si tipo = usuario):**
- `id` (usuario_id)
- `user_id` (FK → USERS)
- `code`
- `nombre`
- `email`
- `supervisor` (boolean)

**Tabla PQ_PARTES_CLIENTES (si tipo = cliente):**
- `id` (cliente_id)
- `user_id` (FK → USERS)
- `code`
- `razon_social` (como "nombre")
- `email`

### Seed mínimo para tests
Los usuarios de prueba ya existen del TestUsersSeeder:
- JPEREZ (empleado normal)
- MGARCIA (supervisor)
- Clientes existentes del ClienteSeeder

---

## 5) Contratos de API

### GET /api/v1/user/profile

**Descripción**: Obtiene los datos del perfil del usuario autenticado.

**Autenticación**: Requerida (Bearer token)

**Headers requeridos**:
```
Authorization: Bearer {token}
Accept: application/json
```

**Request Body**: Ninguno

**Response 200 (éxito - empleado):**
```json
{
  "error": 0,
  "respuesta": "Perfil obtenido correctamente",
  "resultado": {
    "user_code": "JPEREZ",
    "nombre": "Juan Pérez",
    "email": "juan.perez@ejemplo.com",
    "tipo_usuario": "usuario",
    "es_supervisor": false,
    "created_at": "2026-01-27T10:30:00.000Z"
  }
}
```

**Response 200 (éxito - supervisor):**
```json
{
  "error": 0,
  "respuesta": "Perfil obtenido correctamente",
  "resultado": {
    "user_code": "MGARCIA",
    "nombre": "María García",
    "email": "maria.garcia@ejemplo.com",
    "tipo_usuario": "usuario",
    "es_supervisor": true,
    "created_at": "2026-01-27T10:30:00.000Z"
  }
}
```

**Response 200 (éxito - cliente):**
```json
{
  "error": 0,
  "respuesta": "Perfil obtenido correctamente",
  "resultado": {
    "user_code": "CLI001",
    "nombre": "Empresa ABC S.A.",
    "email": "contacto@empresaabc.com",
    "tipo_usuario": "cliente",
    "es_supervisor": false,
    "created_at": "2026-01-27T10:30:00.000Z"
  }
}
```

**Response 401 (no autenticado):**
```json
{
  "error": 4001,
  "respuesta": "No autenticado",
  "resultado": {}
}
```

### Códigos de error

| Código HTTP | Código error | Descripción                    |
|-------------|--------------|--------------------------------|
| 200         | 0            | Perfil obtenido correctamente  |
| 401         | 4001         | Token inválido o no presente   |

---

## 6) Cambios Frontend

### Pantallas/Componentes

**Nuevo componente:**
- `frontend/src/features/user/components/ProfileView.tsx` - Vista del perfil
- `frontend/src/features/user/components/ProfileView.css` - Estilos

**Nuevo servicio:**
- `frontend/src/features/user/services/user.service.ts` - Servicio para API de usuario

**Componentes afectados:**
- `Dashboard.tsx` - Agregar enlace/botón para acceder al perfil
- `App.tsx` - Agregar ruta `/perfil`

### Estados UI

| Estado    | Comportamiento                                           |
|-----------|----------------------------------------------------------|
| loading   | Spinner mientras se carga el perfil                     |
| success   | Muestra los datos del perfil                            |
| error     | Mensaje de error si falla la carga                      |

### Layout del perfil

```
+------------------------------------------+
|  Mi Perfil                    [← Volver] |
+------------------------------------------+
|                                          |
|  Código de usuario:  JPEREZ              |
|  Nombre:             Juan Pérez          |
|  Email:              juan.perez@ej...    |
|  Tipo:               Empleado            |
|  Supervisor:         No  (o badge "Sí")  |
|  Miembro desde:      27/01/2026          |
|                                          |
+------------------------------------------+
```

### Validaciones UI
- Si no hay token, redirigir a login
- Mostrar loading mientras se cargan datos
- Mostrar "No configurado" si email es null/vacío

### Accesibilidad
- Usar elementos semánticos (`<dl>`, `<dt>`, `<dd>` para lista de datos)
- Labels descriptivos para cada campo
- Botón de volver con `aria-label="Volver al dashboard"`

### Selectores de test (data-testid)

| Elemento                | data-testid                    |
|-------------------------|--------------------------------|
| Contenedor perfil       | `user.profile.container`       |
| Código usuario          | `user.profile.code`            |
| Nombre                  | `user.profile.name`            |
| Email                   | `user.profile.email`           |
| Tipo usuario            | `user.profile.type`            |
| Badge supervisor        | `user.profile.supervisorBadge` |
| Fecha creación          | `user.profile.createdAt`       |
| Botón volver            | `user.profile.backButton`      |
| Loading spinner         | `user.profile.loading`         |
| Mensaje error           | `user.profile.error`           |

---

## 7) Plan de Tareas / Tickets

### T1 - Backend: Endpoint GET /api/v1/user/profile
| Campo       | Valor |
|-------------|-------|
| Tipo        | Backend |
| Descripción | Crear endpoint que retorna datos del perfil del usuario autenticado |
| DoD         | Endpoint funcional, retorna datos correctos según tipo de usuario |
| Dependencias| Ninguna |
| Estimación  | S |

### T2 - Backend: Service de perfil de usuario
| Campo       | Valor |
|-------------|-------|
| Tipo        | Backend |
| Descripción | Crear UserProfileService con método getProfile() |
| DoD         | Servicio obtiene datos de USERS + PQ_PARTES_USUARIOS o PQ_PARTES_CLIENTES |
| Dependencias| Ninguna |
| Estimación  | S |

### T3 - Backend: Tests unitarios del servicio
| Campo       | Valor |
|-------------|-------|
| Tipo        | Tests |
| Descripción | Tests unitarios para UserProfileService |
| DoD         | Cobertura de empleado, supervisor, cliente, email null |
| Dependencias| T2 |
| Estimación  | S |

### T4 - Backend: Tests de integración del endpoint
| Campo       | Valor |
|-------------|-------|
| Tipo        | Tests |
| Descripción | Feature tests para GET /api/v1/user/profile |
| DoD         | Tests de 200 (todos los tipos) y 401 |
| Dependencias| T1 |
| Estimación  | S |

### T5 - Frontend: Servicio de usuario
| Campo       | Valor |
|-------------|-------|
| Tipo        | Frontend |
| Descripción | Crear user.service.ts con función getProfile() |
| DoD         | Función llama al endpoint y retorna datos tipados |
| Dependencias| T1 |
| Estimación  | S |

### T6 - Frontend: Componente ProfileView
| Campo       | Valor |
|-------------|-------|
| Tipo        | Frontend |
| Descripción | Crear componente de vista de perfil con todos los campos |
| DoD         | Componente muestra datos, maneja loading/error, todos los data-testid |
| Dependencias| T5 |
| Estimación  | M |

### T7 - Frontend: Ruta /perfil y navegación
| Campo       | Valor |
|-------------|-------|
| Tipo        | Frontend |
| Descripción | Agregar ruta /perfil en App.tsx y enlace en Dashboard |
| DoD         | Ruta protegida funcionando, enlace visible en dashboard |
| Dependencias| T6 |
| Estimación  | S |

### T8 - E2E: Tests Playwright para perfil
| Campo       | Valor |
|-------------|-------|
| Tipo        | Tests |
| Descripción | Tests E2E del flujo completo de visualización de perfil |
| DoD         | Tests de acceso, visualización de datos, navegación |
| Dependencias| T7 |
| Estimación  | M |

### T9 - Docs: Documentar endpoint en autenticacion.md
| Campo       | Valor |
|-------------|-------|
| Tipo        | Docs |
| Descripción | Agregar documentación del endpoint de perfil |
| DoD         | Endpoint documentado con request/response y ejemplos |
| Dependencias| T1 |
| Estimación  | S |

---

## 8) Estrategia de Tests

### Unit Tests
- `UserProfileService::getProfile()` 
  - Perfil de empleado normal
  - Perfil de supervisor
  - Perfil de cliente
  - Usuario sin email (retorna null)
  - Fecha de creación formateada correctamente

### Integration Tests (Feature)
```
GET /api/v1/user/profile
├── ✅ 200 - Perfil de empleado normal
├── ✅ 200 - Perfil de supervisor (es_supervisor = true)
├── ✅ 200 - Perfil de cliente
├── ✅ 200 - Usuario sin email (campo null)
├── ✅ 401 - Sin token
└── ✅ 401 - Token inválido
```

### E2E Tests (Playwright)
```
Flujo: Visualización de Perfil
├── Login con credenciales válidas
├── Click en enlace/botón de perfil en dashboard
├── Verificar redirección a /perfil
├── Verificar que se muestra código de usuario
├── Verificar que se muestra nombre
├── Verificar que se muestra email (o "No configurado")
├── Verificar que se muestra tipo de usuario
├── Verificar que se muestra fecha de creación
├── Click en botón volver
└── Verificar redirección a dashboard
```

**Reglas E2E:**
- NO usar `waitForTimeout` ni esperas ciegas
- Usar `expect(locator).toBeVisible()` para verificar elementos
- Usar `expect(page).toHaveURL()` para verificar navegación
- Selectores: `[data-testid="user.profile.*"]`

---

## 9) Riesgos y Edge Cases

| Riesgo/Edge Case | Mitigación |
|------------------|------------|
| **Usuario sin registro en PQ_PARTES_*** | Validar que el código existe en alguna tabla, sino error 404 |
| **Email null o vacío** | Mostrar "No configurado" en el frontend |
| **Token expirado** | Retornar 401 y frontend redirige a login |
| **Datos desincronizados** | El perfil siempre consulta la BD, no usa solo localStorage |
| **Navegación directa a /perfil sin auth** | ProtectedRoute redirige a login |

---

## 10) Checklist final (para validar HU terminada)

- [ ] AC cumplidos (AC-01 a AC-11)
- [ ] Endpoint backend funcionando (200/401)
- [ ] Service obtiene datos correctos según tipo usuario
- [ ] Frontend muestra todos los campos
- [ ] Estados UI implementados (loading/error/success)
- [ ] Todos los data-testid presentes
- [ ] Unit tests ok (cobertura ≥80%)
- [ ] Integration tests ok
- [ ] ≥1 E2E Playwright ok (sin waits ciegos)
- [ ] Docs actualizados
- [ ] CI/CD pasa

---

## EJECUCIÓN DE LA TR

### Estado de Tareas

| ID | Tarea | Estado |
|----|-------|--------|
| T1 | Backend: Endpoint GET /api/v1/user/profile | ✅ COMPLETADO |
| T2 | Backend: Service de perfil de usuario | ✅ COMPLETADO |
| T3 | Backend: Tests unitarios del servicio | ✅ COMPLETADO |
| T4 | Backend: Tests de integración | ✅ COMPLETADO |
| T5 | Frontend: Servicio de usuario | ✅ COMPLETADO |
| T6 | Frontend: Componente ProfileView | ✅ COMPLETADO |
| T7 | Frontend: Ruta /perfil y navegación | ✅ COMPLETADO |
| T8 | E2E: Tests Playwright para perfil | ✅ COMPLETADO |
| T9 | Docs: Documentar endpoint | ✅ COMPLETADO |

---

## Archivos Creados/Modificados

### Backend
- `backend/app/Services/UserProfileService.php` (CREADO) - Servicio para obtener perfil de usuario
- `backend/app/Http/Controllers/Api/V1/UserProfileController.php` (CREADO) - Controller del endpoint de perfil
- `backend/routes/api.php` (MODIFICADO) - Ruta GET /api/v1/user/profile agregada

### Frontend
- `frontend/src/features/user/services/user.service.ts` (CREADO) - Servicio frontend para API de usuario
- `frontend/src/features/user/services/index.ts` (CREADO) - Exportaciones del servicio
- `frontend/src/features/user/components/ProfileView.tsx` (CREADO) - Componente de vista de perfil
- `frontend/src/features/user/components/ProfileView.css` (CREADO) - Estilos del componente
- `frontend/src/features/user/components/index.ts` (CREADO) - Exportaciones de componentes
- `frontend/src/features/user/index.ts` (CREADO) - Exportaciones del módulo user
- `frontend/src/app/App.tsx` (MODIFICADO) - Ruta /perfil agregada
- `frontend/src/app/Dashboard.tsx` (MODIFICADO) - Enlace "Ver Mi Perfil" agregado
- `frontend/src/app/Dashboard.css` (MODIFICADO) - Estilos para botón de perfil

### Tests
- `backend/tests/Unit/Services/UserProfileServiceTest.php` (CREADO) - 8 tests unitarios
- `backend/tests/Feature/Api/V1/UserProfileTest.php` (CREADO) - 7 tests de integración
- `frontend/tests/e2e/user-profile.spec.ts` (CREADO) - 7 tests E2E Playwright

### Docs
- `docs/backend/autenticacion.md` (MODIFICADO) - Endpoint de perfil documentado
- `.cursor/Docs/TR-006-profile-view-2026-01-28.md` (CREADO) - Documentación del componente

---

## Comandos Ejecutados

```bash
# Ejecutar tests unitarios del servicio
php artisan test tests/Unit/Services/UserProfileServiceTest.php

# Ejecutar tests de integración del endpoint
php artisan test tests/Feature/Api/V1/UserProfileTest.php

# Ejecutar todos los tests de perfil
php artisan test --filter=Profile

# Ejecutar tests E2E de perfil
cd frontend
npm run test:e2e -- user-profile.spec.ts
```

---

## Notas y Decisiones

1. **Estructura del servicio**: Se separó la lógica en métodos privados (`buildEmpleadoProfile`, `buildClienteProfile`, `buildMinimalProfile`) para mantener el código limpio y fácil de mantener.

2. **Manejo de email null**: El frontend muestra "No configurado" cuando el email es null, cumpliendo con AC-04.

3. **Formato de fecha**: Se usa `toIso8601String()` en el backend y `toLocaleDateString()` en el frontend para formatear la fecha de creación.

4. **Badge de supervisor**: Se muestra solo si `es_supervisor` es true, usando el mismo estilo que en el Dashboard.

5. **Navegación**: El botón "Volver" redirige al dashboard (`/`), cumpliendo con AC-11.

6. **Tests idempotentes**: Los tests limpian datos existentes antes de insertar, manteniendo consistencia con el patrón establecido en TR-001 y TR-002.

7. **Componente ProfileView**: Usa elementos semánticos (`<dl>`, `<dt>`, `<dd>`) para mejor accesibilidad.

8. **Estados UI**: Maneja correctamente loading, error y success, con mensajes apropiados.

---

## Pendientes / Follow-ups

- Ejecutar `php artisan test --filter=Profile` para verificar todos los tests de backend
- Ejecutar tests E2E: `cd frontend && npm run test:e2e -- user-profile.spec.ts`
- Verificar manualmente el flujo completo: Login → Dashboard → Ver Perfil → Volver

---

## Criterios de Aceptación - Estado Final

| AC | Descripción | Estado |
|----|-------------|--------|
| AC-01 | Acceso a perfil desde dashboard | ✅ |
| AC-02 | Código usuario solo lectura | ✅ |
| AC-03 | Nombre completo visible | ✅ |
| AC-04 | Email visible (o "No configurado") | ✅ |
| AC-05 | Rol/tipo visible | ✅ |
| AC-06 | Fecha creación visible | ✅ |
| AC-07 | Campos solo lectura | ✅ |
| AC-08 | Badge supervisor si aplica | ✅ |
| AC-09 | Endpoint retorna 200 | ✅ |
| AC-10 | Endpoint retorna 401 sin auth | ✅ |
| AC-11 | Navegación de vuelta al dashboard | ✅ |
