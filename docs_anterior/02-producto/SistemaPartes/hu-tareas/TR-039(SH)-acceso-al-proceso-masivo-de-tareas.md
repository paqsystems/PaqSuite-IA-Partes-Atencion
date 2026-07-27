# TR-039(SH) – Acceso al proceso masivo de tareas

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-039(SH)-acceso-al-proceso-masivo-de-tareas |
| Épica              | Épica 8: Proceso Masivo de Tareas          |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Empleado Supervisor                        |
| Dependencias       | HU-001 (autenticación)                     |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-07                               |
| Estado             | Pendiente                                  |

---

## 1) HU Refinada

### Título
Acceso al proceso masivo de tareas

### Narrativa
**Como** supervisor  
**Quiero** acceder a la funcionalidad de proceso masivo de tareas  
**Para** gestionar eficientemente el estado de múltiples tareas.

### Contexto/Objetivo
Punto de entrada a la sección "Proceso Masivo" desde el menú principal. Solo supervisores. La página muestra filtros y tabla de tareas (contenido completo en TR-040, TR-041, etc.). Si un usuario no supervisor intenta acceder: 403 o redirección y mensaje claro.

### Suposiciones explícitas
- Usuario autenticado (HU-001). Permisos según `supervisor = true` en backend.
- Se puede reutilizar o extender GET /api/v1/tasks/all (TR-034) para listar tareas con filtros.
- Ruta frontend ej. `/tareas/proceso-masivo` o `/proceso-masivo`.

### In Scope
- Sección "Proceso Masivo" accesible desde menú/panel (solo supervisores).
- Página muestra filtros y tabla de tareas (estructura; filtros y datos en TR-040).
- 403 o redirección si usuario no supervisor; mensaje claro de permisos.

### Out of Scope
- Lógica de filtros (HU-040), selección múltiple (HU-041), procesamiento (HU-042, HU-043).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El supervisor puede acceder a la sección "Proceso Masivo" desde el menú principal.
- **AC-02**: Solo los usuarios con `supervisor = true` pueden acceder a esta funcionalidad.
- **AC-03**: Si un usuario no supervisor intenta acceder, se muestra error 403 o redirección.
- **AC-04**: La página muestra los filtros y la tabla de tareas (estructura inicial).
- **AC-05**: Se muestra un mensaje claro si el usuario no tiene permisos.

### Escenarios Gherkin

```gherkin
Feature: Acceso al Proceso Masivo

  Scenario: Supervisor accede a Proceso Masivo
    Given el supervisor está autenticado
    When accede a la sección "Proceso Masivo"
    Then se muestra la página con filtros y tabla de tareas

  Scenario: Empleado no puede acceder
    Given un empleado no supervisor está autenticado
    When intenta acceder a "Proceso Masivo"
    Then recibe 403 o es redirigido
    And ve un mensaje claro de falta de permisos
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo supervisores (`supervisor = true`) pueden acceder al proceso masivo.
2. **RN-02**: Código de error 403 (3101) si usuario no es supervisor.

### Permisos por Rol
- **Supervisor:** Acceso completo a la sección Proceso Masivo.
- **Empleado (no supervisor):** 403 o redirección.
- **Cliente:** Sin acceso.

---

## 4) Impacto en Datos

### Tablas Afectadas
- Ninguna nueva; reutiliza consultas de tareas (TR-034 / GET tasks/all).

### Migración + Rollback
- No se requiere migración.

### Seed Mínimo para Tests
- Usuario supervisor y empleado no supervisor.

---

## 5) Contratos de API

### Reutilización
- **GET /api/v1/tasks/all** (TR-034): listado de todas las tareas con filtros. Solo supervisores. 403 si no supervisor.
- No se añaden endpoints nuevos en esta TR; solo acceso a la pantalla y verificación de permisos en frontend y en llamadas existentes.

**Autorización:** Solo supervisor (403 / 3101 si no).

---

## 6) Cambios Frontend

### Pantallas/Componentes
- Nueva ruta **Proceso Masivo** (ej. `/tareas/proceso-masivo` o `/proceso-masivo`).
- Página con título "Proceso Masivo", área de filtros (placeholder o básico) y tabla de tareas (vacía o con datos vía GET tasks/all).
- Enlace en menú principal / Dashboard para supervisores: "Proceso Masivo".
- Si el usuario no es supervisor: redirección a `/` o pantalla de error con mensaje claro.

### Estados UI
- Loading, Error (403 / sin permisos), Success (página con filtros y tabla).

### data-testid sugeridos
- `procesoMasivo.page`, `procesoMasivo.filtros`, `procesoMasivo.tabla`, `procesoMasivo.sinPermisos`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Frontend | Ruta y página Proceso Masivo (solo supervisor) | Página accesible con filtros y tabla; 403/redirección no supervisor | — | M |
| T2 | Frontend | Enlace "Proceso Masivo" en menú/Dashboard (solo para supervisores) | Enlace visible solo si supervisor | T1 | S |
| T3 | Tests    | E2E: supervisor accede; empleado redirigido o 403 | ≥1 E2E Playwright | T1, T2 | S |
| T4 | Docs     | Actualizar specs si aplica | Docs actualizados | T1 | S |

---

## 8) Estrategia de Tests

- **Integration:** GET /api/v1/tasks/all con supervisor 200, con empleado 403 (ya cubierto en TR-034 si existe).
- **E2E:** Login supervisor → Proceso Masivo → ve página; login empleado → intenta Proceso Masivo → redirección o mensaje.

---

## 9) Riesgos y Edge Cases

- Acceso directo por URL: el frontend debe validar rol y redirigir o mostrar error si no supervisor.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Frontend: ruta + página + enlace solo supervisor
- [ ] No supervisor: 403 o redirección y mensaje claro
- [ ] ≥1 E2E ok
- [ ] Docs actualizados

---

## Archivos creados/modificados

### Backend
- (Sin cambios; reutiliza GET tasks/all y SupervisorRoute en frontend.)

### Frontend
- `frontend/src/app/App.tsx` – Ruta `/tareas/proceso-masivo` con SupervisorRoute y ProcesoMasivoPage.
- `frontend/src/app/Dashboard.tsx` – Enlace "Proceso Masivo" para supervisores.
- `frontend/src/features/tasks/components/ProcesoMasivoPage.tsx` – Página con filtros y tabla (TR-039 a TR-043).
- `frontend/src/features/tasks/components/ProcesoMasivoPage.css` – Estilos.
- `frontend/src/features/tasks/components/index.ts` – Export ProcesoMasivoPage.

### Tests
- E2E: pendiente de añadir en `frontend/tests/e2e/` (supervisor accede, empleado redirigido).

## Comandos ejecutados

## Notas y decisiones

## Pendientes / follow-ups
