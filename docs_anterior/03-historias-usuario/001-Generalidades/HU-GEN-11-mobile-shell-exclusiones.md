# HU-GEN-11-mobile-shell-exclusiones — Shell mobile y exclusiones

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-11-mobile-shell-exclusiones |
| **SPEC origen** | [SPEC-001-11-mobile-capacitor](../../05-open-spec/001-Generalidades/SPEC-001-11-mobile-capacitor.md) |
| **Épica** | 001 — Generalidades / Mobile |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-30) |
| **Dependencias** | HU-GEN-01-shell-layout; HU-GEN-11-mobile-login-tenant; regla `80-mobile` |

## Narrativa

Como **usuario mobile**,  
quiero **un shell simplificado sin funciones desktop-only**,  
para **operar solo procesos habilitados para mobile**.

## Alcance incluido

- Drawer menú overlay en native (< 768px pattern).
- Safe areas CSS (`env(safe-area-inset-*)`).
- **Selector idioma y apariencia/tema** en header mobile (**D1-17: Sí**).
- Filtrar menú/rutas excluidas en native:
  - pivot / informes pivot
  - `/excel-import/*`
  - `/admin/*`
- Sin toggle «pestañas separadas»; ignorar `openInNewTab`; navegación in-app únicamente.
- Bloqueo navegación directa a rutas excluidas (redirect o 404 mobile).

## Fuera de alcance

- Dashboard completo (v1 SistemaPartes).
- Chat assistant (salvo TR futura).
- Admin seguridad UI.
- Controles web de **expandir/contraer árbol menú** y **vista ramas** en native v1 (simplificación UX; ver TR-GEN-11-shell).

## Reglas de negocio

1. Permisos backend siguen aplicando; filtro client es **adicional**.
2. `window.open('_blank')` prohibido para ítems menú en native.
3. Preferencias GET pueden incluir `openInNewTab`; mobile no lo muestra ni aplica.

## Criterios de aceptación

- [x] **CA-01:** Menú drawer operativo en native (smoke Android emulador 2026-06-30).
- [x] **CA-02:** Rutas excluidas no aparecen en menú native.
- [ ] **CA-03:** Navegar manualmente a `/admin/roles` en native → bloqueado (smoke manual pendiente).
- [x] **CA-04:** Sin toggle pestañas separadas en menú avatar mobile.
- [x] **CA-05:** Selector idioma y tema visibles en header (paridad HU-GEN-01).
- [x] **CA-06:** Safe area no tapa header en dispositivo con notch / barra de estado.

## Veredicto B1

**Lista para TR** (`TR-GEN-11-mobile-shell`).










