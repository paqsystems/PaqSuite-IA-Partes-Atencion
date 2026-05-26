# TR-007(SH) – Edición de perfil de usuario

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-007(SH)-edición-de-perfil-de-usuario   |
| Épica              | Épica 2: Configuración de Usuario         |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado / Empleado Supervisor / Cliente   |
| Dependencias       | HU-006 (visualización de perfil)           |
| Última actualización | 2026-02-05                               |
| Estado             | ✅ COMPLETADO                                |

---

## 1) HU Refinada

### Título
Edición de perfil de usuario

### Narrativa
**Como** usuario autenticado (empleado, supervisor o cliente)  
**Quiero** editar mi nombre y email  
**Para** mantener actualizada mi información personal.

### Contexto/Objetivo
El usuario accede a su perfil (pantalla TR-006) y dispone de una opción "Editar perfil". Puede modificar nombre y email. El código de usuario no es modificable. El sistema valida formato de email y unicidad (excluyendo al propio usuario). Los cambios se persisten en PQ_PARTES_USUARIOS (empleados) o PQ_PARTES_CLIENTES (clientes).

### In Scope
- Opción "Editar perfil" en la pantalla de perfil.
- Formulario con: nombre (editable), email (editable, opcional), código (solo lectura).
- Endpoint PUT /api/v1/user/profile con body { nombre, email }.
- Validación backend: nombre requerido, email formato válido, email único si se modifica (excluyendo el propio usuario).
- Mensaje de confirmación y actualización inmediata de la vista.

### Out of Scope
- Cambio de contraseña (TR-005).
- Modificación del código de usuario.

---

## 2) Criterios de Aceptación

- **AC-01**: El usuario autenticado puede acceder a "Editar perfil" desde la pantalla de perfil.
- **AC-02**: El usuario puede modificar su nombre.
- **AC-03**: El usuario puede modificar su email (opcional).
- **AC-04**: El sistema valida que el email tenga formato válido (si se proporciona).
- **AC-05**: El sistema valida que el email sea único (excluyendo al propio usuario).
- **AC-06**: El código de usuario no es modificable (solo lectura).
- **AC-07**: Al guardar se muestran mensaje de confirmación y los cambios se reflejan en el perfil.
- **AC-08**: Usuario no autenticado no puede acceder al endpoint (401).

---

## 3) Reglas de Negocio

1. **RN-01**: Solo el usuario autenticado puede editar su propio perfil (identificado por token).
2. **RN-02**: El campo `code` no es modificable.
3. **RN-03**: `nombre` es obligatorio.
4. **RN-04**: `email` es opcional; si se proporciona: formato válido y único (excluyendo el registro del usuario actual).
5. **RN-05**: Empleados: actualizar PQ_PARTES_USUARIOS (nombre, email). Clientes: actualizar PQ_PARTES_CLIENTES (nombre, email).

---

## 4) Contratos de API

### PUT /api/v1/user/profile

**Autenticación:** Requerida (Bearer token).

**Body (JSON):**
```json
{
  "nombre": "Juan Pérez Actualizado",
  "email": "nuevo@ejemplo.com"
}
```

| Campo   | Tipo   | Requerido | Descripción        |
|---------|--------|-----------|--------------------|
| nombre  | string | Sí        | Nombre completo    |
| email   | string | No        | Email (único)      |

**Response 200 (éxito):**
```json
{
  "error": 0,
  "respuesta": "Perfil actualizado correctamente",
  "resultado": {
    "user_code": "JPEREZ",
    "nombre": "Juan Pérez Actualizado",
    "email": "nuevo@ejemplo.com",
    "tipo_usuario": "usuario",
    "es_supervisor": false,
    "created_at": "2026-01-27T10:30:00.000Z"
  }
}
```

**Response 401:** No autenticado.  
**Response 422:** Validación (nombre vacío, email inválido, email duplicado). `resultado.errors` con detalle por campo.

---

## 5) Plan de Tareas

| Id  | Tipo     | Descripción |
|-----|----------|-------------|
| T1  | Backend  | UserProfileService::updateProfile + validación nombre/email único |
| T2  | Backend  | UpdateProfileRequest + UserProfileController::update + ruta PUT /user/profile |
| T3  | Frontend | user.service updateProfile() + ProfileView opción Editar perfil + formulario |
| T4  | Tests    | Unit servicio, integration endpoint, E2E edición perfil |
| T5  | Docs     | specs/endpoints/user-profile-update.md, checklist TR-007 |

---

## 6) data-testid (Frontend)

| Elemento              | data-testid                    |
|-----------------------|---------------------------------|
| Enlace/botón Editar   | `user.profile.editLink`         |
| Formulario edición    | `user.profile.editForm`         |
| Input nombre          | `user.profile.editNombre`       |
| Input email           | `user.profile.editEmail`        |
| Botón Guardar         | `user.profile.editSubmit`       |
| Botón Cancelar        | `user.profile.editCancel`       |
| Mensaje éxito         | `user.profile.editSuccess`      |
| Mensaje error         | `user.profile.editError`        |

---

## 7) Checklist Final

- [x] AC cumplidos
- [x] Backend: PUT /user/profile + validaciones
- [x] Frontend: Editar perfil + formulario + mensajes
- [x] Unit + integration tests (E2E opcional)
- [x] Docs/specs actualizados

---

## Archivos creados/modificados

### Backend
- `app/Http/Requests/User/UpdateProfileRequest.php` (nuevo)
- `app/Services/UserProfileService.php` (updateProfile)
- `app/Http/Controllers/Api/V1/UserProfileController.php` (update)
- `routes/api.php` (PUT /user/profile)
- `tests/Unit/Services/UserProfileServiceTest.php` (4 tests updateProfile)
- `tests/Feature/Api/V1/UserProfileTest.php` (4 tests PUT profile)

### Frontend
- `src/features/user/services/user.service.ts` (updateProfile, UpdateProfileResult)
- `src/features/user/components/ProfileView.tsx` (Editar perfil, formulario, data-testid)
- `src/features/user/components/ProfileView.css` (estilos editar perfil)

### Docs
- `docs/04-tareas/TR-007(SH)-edición-de-perfil-de-usuario.md` (nuevo)
- `specs/endpoints/user-profile-update.md` (nuevo)

## Comandos ejecutados

- `php artisan test tests/Unit/Services/UserProfileServiceTest.php tests/Feature/Api/V1/UserProfileTest.php --filter="updateProfile|put_profile"` (8 passed)

## Notas y decisiones

- **Request:** buildEmailUniqueRule debe retornar tipo `ValidationRule|Unique` (Rule::unique() devuelve Rules\Unique).
- **Código:** No modificable; solo nombre y email editables.

## Pendientes / follow-ups

- E2E Playwright para flujo Editar perfil (opcional).
