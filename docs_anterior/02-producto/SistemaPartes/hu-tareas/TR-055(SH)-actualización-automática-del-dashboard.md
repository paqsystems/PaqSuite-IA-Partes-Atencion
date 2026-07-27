# TR-055(SH) – Actualización automática del dashboard

| Campo                | Valor                                                       |
|----------------------|-------------------------------------------------------------|
| HU relacionada       | HU-055(SH)-actualización-automática-del-dashboard           |
| Épica                | Épica 10: Dashboard                                        |
| Prioridad            | SHOULD-HAVE                                                |
| Roles                | Empleado / Empleado Supervisor / Cliente                    |
| Dependencias         | HU-051 (Dashboard principal); TR-051                       |
| Clasificación        | HU SIMPLE                                                  |
| Última actualización | 2026-02-07                                                 |
| Estado               | ✅ IMPLEMENTADO                                             |

---

## 1) HU Refinada

### Título
Actualización automática del dashboard

### Narrativa
**Como** usuario (empleado, supervisor o cliente)  
**Quiero** que el dashboard se actualice automáticamente  
**Para** ver información siempre actualizada

### Contexto/Objetivo
El dashboard se actualiza automáticamente cada X minutos (ej. 5, configurable). Se muestra un indicador de última actualización (ej. "Actualizado hace 2 minutos"). El usuario puede actualizar manualmente con un botón "Actualizar". Durante la actualización se muestra un indicador de carga. Los datos se refrescan sin recargar toda la página (AJAX/fetch). Los datos actualizados respetan los filtros automáticos por rol. La actualización automática puede ser opcional o deshabilitable según UX; el intervalo debe ser configurable.

### Suposiciones explícitas
- TR-051 está implementado: GET /api/v1/dashboard y pantalla Dashboard con selector de período.
- Polling (setInterval) es suficiente para MVP; WebSockets no son obligatorios.
- Intervalo por defecto: 5 minutos; configurable vía constante, variable de entorno o (futuro) preferencia de usuario.
- Filtros por rol se aplican en cada request al backend (sin cambio de contrato).

### In Scope
- Actualización automática cada X minutos (configurable; ej. 5).
- Indicador de última actualización (ej. "Actualizado hace X minutos").
- Botón "Actualizar" para refresco manual.
- Indicador de carga durante la actualización (manual o automática).
- Refresco de datos vía fetch/AJAX sin recargar la página.
- Filtros automáticos por rol respetados en cada actualización.
- Opcional: posibilidad de deshabilitar la actualización automática (según UX).

### Out of Scope
- WebSockets o Server-Sent Events.
- Configuración del intervalo por usuario (puede ser constante o env en frontend).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El dashboard se actualiza automáticamente cada X minutos (ej. 5, valor configurable).
- **AC-02**: Se muestra un indicador de última actualización (ej. "Actualizado hace 2 minutos").
- **AC-03**: El usuario puede actualizar manualmente con un botón "Actualizar".
- **AC-04**: Durante la actualización (automática o manual) se muestra un indicador de carga.
- **AC-05**: Los datos se refrescan sin recargar toda la página (AJAX/fetch).
- **AC-06**: Los datos actualizados respetan los filtros por rol (cliente: su cliente; empleado: sus tareas; supervisor: todas).
- **AC-07**: El intervalo de actualización automática es configurable (constante, env o similar).
- **AC-08**: data-testid para botón Actualizar, indicador de última actualización e indicador de carga (E2E).

### Escenarios Gherkin

```gherkin
Feature: Actualización automática del dashboard

  Scenario: Indicador de última actualización
    Given el usuario está autenticado
    When accede al dashboard
    Then ve un indicador de última actualización (ej. "Actualizado hace 0 min")
    And tras una actualización automática el indicador se actualiza

  Scenario: Actualización manual
    Given el usuario está en el dashboard
    When hace clic en el botón "Actualizar"
    Then se muestra indicador de carga
    And los datos se refrescan sin recargar la página
    And el indicador de última actualización se actualiza

  Scenario: Actualización automática (polling)
    Given el usuario está en el dashboard
    When pasan X minutos (intervalo configurado)
    Then los datos se refrescan automáticamente
    And el indicador de última actualización refleja el cambio
    And los datos respetan el rol del usuario (filtros automáticos)
```

---

## 3) Reglas de Negocio

1. **RN-01**: Filtros automáticos por rol se aplican en cada request de actualización (igual que en carga inicial).
2. **RN-02**: Intervalo de actualización automática configurable (valor por defecto ej. 5 minutos).
3. **RN-03**: Actualización automática opcional: puede deshabilitarse (p. ej. cuando la pestaña no está visible o por preferencia) según decisión de UX.
4. **RN-04**: Al actualizar (manual o automático), se reutiliza el mismo período seleccionado en el dashboard.

### Permisos por Rol
- No cambian: mismo contrato GET /api/v1/dashboard con filtros por rol en backend.

---

## 4) Impacto en Datos

- **Tablas afectadas:** Ninguna nueva. Mismas lecturas que el dashboard (GET /api/v1/dashboard).
- **Cambios en datos:** Ninguno. Solo comportamiento de frontend (polling y refresco).

---

## 5) Contratos de API

- Sin cambios. Se reutiliza GET /api/v1/dashboard con fecha_desde, fecha_hasta (y token). El frontend invoca el mismo endpoint en cada actualización (manual o por timer).

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados
- **Dashboard:**
  - Timer/interval para llamar a getDashboard() cada X minutos (configurable).
  - Estado "última actualización" (timestamp); texto "Actualizado hace X minutos" (actualizable cada minuto en UI si se desea).
  - Botón "Actualizar" que dispara getDashboard() y muestra loading.
  - Indicador de carga durante fetch (manual o automático).
  - Limpiar timer al desmontar el componente (o cuando la pestaña no está visible, opcional) para evitar fugas y llamadas innecesarias.
- **Configuración:** Constante o variable de entorno para intervalo (ej. DASHBOARD_REFRESH_INTERVAL_MS = 300000 para 5 min).

### Estados UI
- Loading: mientras se ejecuta una actualización (manual o automática).
- Idle: datos mostrados; indicador "Actualizado hace X min" visible.
- Error: mensaje y opción de reintentar (heredado de TR-051).

### Accesibilidad Mínima
- data-testid: dashboard.botonActualizar, dashboard.ultimaActualizacion, dashboard.loading (o el existente del dashboard).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo     | Descripción | DoD | Dependencias | Estimación |
|----|----------|-------------|-----|--------------|------------|
| T1 | Frontend | Configuración del intervalo de actualización | Constante o env (ej. 5 min); documentar. | — | S |
| T2 | Frontend | Timer de actualización automática | setInterval que llama a getDashboard() con el período actual; clear al desmontar (y opcionalmente al ocultar pestaña). | TR-051 | M |
| T3 | Frontend | Estado "última actualización" e indicador | Timestamp de última carga exitosa; texto "Actualizado hace X minutos" (actualizable cada minuto en UI). | TR-051 | S |
| T4 | Frontend | Botón "Actualizar" | Dispara getDashboard(); muestra loading durante el fetch. | TR-051 | S |
| T5 | Frontend | Indicador de carga durante actualización | Visible tanto en actualización manual como automática (sin duplicar pantalla completa si ya existe). | T2, T4 | S |
| T6 | Tests    | E2E actualización manual | Clic en Actualizar → loading → datos actualizados; indicador de última actualización. | T3, T4 | M |
| T7 | Tests    | Unit o E2E intervalo (opcional) | Verificar que tras el intervalo se dispara una nueva petición (mock o avance de tiempo). | T2 | S |
| T8 | Docs     | Documentar intervalo y comportamiento | README o docs. | T1 | S |

**Total:** 8 tareas.

---

## 8) Estrategia de Tests

- **Unit (frontend):** Lógica de "Actualizado hace X min" (cálculo de diferencia); limpieza del timer en desmontaje.
- **E2E:** Clic en "Actualizar" → loading → datos visibles; indicador de última actualización presente y actualizado.
- **Opcional:** Test con avance de tiempo (fake timer) para comprobar que el polling dispara una nueva petición.

---

## 9) Riesgos y Edge Cases

- Pestaña en segundo plano: considerar pausar o reducir frecuencia del polling para ahorrar recursos (opcional).
- Múltiples pestañas: cada una con su propio timer; no hay sincronización entre pestañas (aceptable para MVP).
- Error en actualización automática: no bloquear la UI; mostrar datos previos y opcionalmente mensaje de error o reintento silencioso.

---

## 10) Checklist Final

- [ ] AC cumplidos
- [ ] Actualización automática cada X minutos (configurable)
- [ ] Indicador de última actualización y botón Actualizar
- [ ] Loading durante actualización; datos sin recargar página
- [ ] Filtros por rol respetados en cada actualización
- [ ] E2E y docs actualizados

---

## Archivos creados/modificados

- **Frontend:** `src/app/Dashboard.tsx` (DASHBOARD_REFRESH_INTERVAL_MS 5 min, setInterval, lastUpdatedAt, minutesAgo, botón Actualizar, indicador "Actualizado hace X min"); `src/app/Dashboard.css` (.dashboard-ultima-actualizacion, .dashboard-btn-secondary:disabled).
- **Tests:** `frontend/tests/e2e/dashboard.spec.ts` (TR-055: botonActualizar, ultimaActualizacion).

## Comandos ejecutados

- `npm run test:e2e` (frontend) para validar dashboard.spec.ts.

## Notas y decisiones

- Intervalo fijo 5 minutos (constante en código). Timer se limpia al desmontar el componente.
- El texto "Actualizado hace X min" se actualiza cada 60 s mediante un setInterval secundario.

## Pendientes / follow-ups

- Ninguno.
