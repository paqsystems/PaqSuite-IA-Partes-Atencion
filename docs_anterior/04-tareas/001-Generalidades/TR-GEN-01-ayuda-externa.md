# TR-GEN-01-ayuda-externa — Ayuda externa / Asistente IA

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-01-ayuda-externa](../../03-historias-usuario/001-Generalidades/HU-GEN-01-ayuda-externa.md) |
| **SPEC relacionada** | [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-01-shell-layout; TR-GEN-01-menu-avatar; TR-GEN-02-login-sesion; SPEC-001-04-configuracion-global |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-28 (resincronizada con HU) |

**Origen:** [HU-GEN-01-ayuda-externa](../../03-historias-usuario/001-Generalidades/HU-GEN-01-ayuda-externa.md)  
**Referencia SPEC:** [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Acceso a ayuda externa desde menú avatar.

### Narrativa
Como usuario autenticado quiero abrir ayuda operativa/asistente externo desde el avatar para consultar información sin perder la sesión ni la pantalla actual.

### In scope / Out of scope
- **In scope:** ítem "Asistente IA" en avatar, apertura en nueva pestaña, URL configurable sin hardcode, comportamiento seguro ante URL ausente o inválida.
- **Out of scope:** motor de ayuda, contenido de chat/manual, traducción del sitio externo.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Si existe URL configurada, clic abre ayuda en nueva pestaña.
- **AC-02**: Si no existe URL, el ítem se oculta o muestra indisponibilidad controlada.
- **AC-03**: Cambiar URL de configuración no requiere redeploy frontend.
- **AC-04**: URL inválida no rompe la SPA y muestra error controlado.
- **AC-05**: E2E verifica que la pestaña original mantiene shell operativo.

### Escenarios Gherkin

```gherkin
Feature: Ayuda externa

  Scenario: Abrir ayuda con URL configurada
    Given un usuario autenticado
    And existe URL de ayuda configurada
    When selecciona "Asistente IA" en el menú avatar
    Then se abre una nueva pestaña con la URL configurada
    And la pestaña del portal permanece activa

  Scenario: Sin URL configurada
    Given un usuario autenticado
    And no hay URL de ayuda configurada
    When abre el menú avatar
    Then no ve el ítem de ayuda o ve mensaje de indisponibilidad

  Scenario: URL inválida no rompe la aplicación
    Given un usuario autenticado
    And la URL configurada es inválida
    When intenta abrir la ayuda
    Then ve un mensaje de error controlado
    And puede seguir usando el portal
```

---

## 3) Reglas de Negocio

1. **RN-01**: Slice de prioridad **Should**; no bloquea la salida de los slices Must.
2. **RN-02**: Acción de ayuda abre siempre destino externo en nueva pestaña.
3. **RN-03**: URL de ayuda se obtiene desde configuración backend, no hardcodeada en UI.
4. **RN-04**: Preferencia `openInNewTab` del sidebar no altera comportamiento de ayuda externa.
5. **RN-05**: Si la configuración no está disponible, la experiencia debe degradar de forma controlada.

---

## 4) Impacto en Datos

### Tablas afectadas
- Tabla/configuración global de parámetros (definición en SPEC-001-04), por ejemplo clave `externalHelpUrl`.

### Seed mínimo para tests
- Parámetro global con URL válida.
- Parámetro global ausente.
- Parámetro global con URL inválida.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1. Código, matriz y OpenAPI deben coincidir.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/config/public` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### GET `/api/v1/config/public`

**Autorización:** usuario autenticado.

**Request:** sin body.

**Response 200:** envelope con configuración pública mínima para UI:

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "externalHelpUrl": "https://help.example.com"
  }
}
```

**Response 401:** no autenticado.

**Response 403:** sin permiso para leer configuración pública (si aplica policy explícita).

**OpenAPI (L5-Swagger):**

- [ ] Endpoint documentado en controller/DTO.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401/403 documentadas.
- [ ] Envelope documentado.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Agregar/confirmar fila para `GET /api/v1/config/public`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/help/api/getExternalHelpConfig.ts` (nuevo): lectura de URL de ayuda.
- `frontend/src/features/help/model/helpConfig.ts` (nuevo): tipado de config.
- `frontend/src/features/help/utils/openExternalHelp.ts` (nuevo): validación/abertura segura.
- `frontend/src/features/avatar/components/AvatarMenu.tsx`: ítem condicional "Asistente IA".
- `frontend/src/shared/notifications/showUiMessage.ts` (nuevo o ajuste): aviso de URL inválida/indisponible.

### data-testid sugeridos
- `avatarMenuItemExternalHelp`
- `externalHelpUnavailableMessage`
- `externalHelpInvalidUrlMessage`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Exponer `GET /api/v1/config/public` con campo `externalHelpUrl` | OpenAPI + 401/403 |
| T2 | Frontend | Implementar ítem condicional de ayuda en avatar | Visible según configuración |
| T3 | Frontend | Implementar utilitario de apertura segura con validación URL | Sin crash ante URL inválida |
| T4 | Frontend | Gestionar mensajes controlados de indisponibilidad/error | UX degradada correctamente |
| T5 | Tests | Integration de config + E2E apertura en nueva pestaña | Casos verdes |
| T6 | Docs | Matriz endpoint ↔ permiso y nota de priorización Should | Trazabilidad clara |

---

## 8) Estrategia de Tests

- **Unit:** validador de URL de ayuda y utilitario de apertura segura.
- **Integration:** `GET /api/v1/config/public` con 200/401/403.
- **E2E:**  
  - con URL válida abre nueva pestaña y mantiene shell original;  
  - sin URL/URL inválida muestra estado controlado.

---

## 9) Riesgos y Edge Cases

- Bloqueo de popups del navegador puede impedir apertura de ayuda.
- URL mal configurada puede generar percepción de error externo; requiere mensaje claro.
- Endpoint de configuración sobredimensionado puede exponer datos innecesarios.
- Al ser Should, puede quedar parcialmente implementado si no se corta por alcance explícito.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Priorización Should confirmada para release objetivo
- [ ] Integración validada con menú avatar

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR











