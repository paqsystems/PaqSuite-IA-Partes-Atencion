# UI Stack y Restricciones (OBLIGATORIO)

## Framework UI
- Todo el frontend debe construirse usando **DevExtreme React Components** (DevExpress).
- No usar MUI/Ant/Bootstrap components. HTML/CSS nativo solo para layout estructural mínimo (wrappers).
- Los componentes principales del layout se implementarán con:
  - `Toolbar` (TopBar)
  - `Drawer` (Sidebar colapsable / overlay)
  - `TreeView` o `List` (menú de procesos)
  - `DropDownButton` / `Menu` (menú usuario)
  - `Popup` (cambiar empresa / cambiar contraseña / perfil)
  - `ScrollView` (scroll en menú y contenido)
  - `TabPanel` SOLO en desktop/tablet si está habilitado el modo solapas (en mobile NO aplica)
  - `ResponsiveBox` o CSS Grid (layout de tarjetas en dashboard)

## Reglas de permisos
- La UI **NO decide permisos**.
- El menú lateral se renderiza según un payload de backend (módulos y procesos permitidos por usuario/empresa).
- El colapso del menú (hamburguesa) es solo comportamiento visual (no seguridad).

---

# Theming por Empresa (Implementación técnica Opción A)

## Objetivo
Cada empresa (tenant) puede elegir una apariencia (theme) DevExtreme distinta para diferenciar “colorido” y estilo.
La selección se realiza por empresa y se aplica al iniciar el contexto (tras elegir empresa).

## Estrategia (Opción A – CSS precompilados)
- Se soporta una **lista cerrada** de themes precompilados.
- Para cada theme existe un archivo CSS generado y versionado en el repo.
- Al seleccionar empresa:
  1) backend retorna `ThemeName` (string)
  2) frontend carga dinámicamente el CSS correspondiente (sin redeploy)
  3) recién luego monta el `MainLayout` (evitar flash de estilos)

## Lista cerrada de Themes Permitidos
(esta lista puede ajustarse, pero es “cerrada”; el usuario solo elige dentro de ella)

- `material.blue.light`
- `material.blue.dark`
- `material.teal.light`
- `material.teal.dark`
- `material.purple.light`
- `material.purple.dark`
- `material.orange.light`
- `material.orange.dark`
- `generic.light`       (fallback simple)
- `generic.dark`        (fallback simple)

## Ubicación de assets de theme
- `/src/assets/themes/<ThemeName>.css`
Ejemplo:
- `/src/assets/themes/material.blue.light.css`
- `/src/assets/themes/material.blue.dark.css`

## Aplicación del theme (regla)
- El theme se aplica agregando/reemplazando un `<link id="dx-theme-link" ...>` en el `<head>`
- Si el `ThemeName` no existe o falla, fallback a `generic.light`.

## Persistencia (Shared/Dictionary DB)
Tabla sugerida:
- `PQ_SYS_CompanyUiSettings`
  - `CompanyId` (PK/FK)
  - `ThemeName` (varchar)  // uno de la lista cerrada
  - `LogoUrl` (varchar, nullable) // opcional
  - `UpdatedAt`, `UpdatedBy`

Endpoint sugerido:
- `GET /api/company/{companyId}/ui-settings`

---

# Responsive (Desktop / Tablet / Mobile)

## Breakpoints (referencia)
- Mobile: < 768px
- Tablet: 768px – 1024px
- Desktop: > 1024px

## Mobile – Reglas (SIN dashboard, SIN solapas)
- El sistema en mobile prioriza “ejecución de procesos”, no analítica.
- Layout mobile:
  - TopBar fijo (hamburguesa + logo + empresa abreviada + usuario)
  - Drawer overlay (menú procesos) se abre/cierra
  - Área central: 
    - Pantalla de “Acceso rápido” (botones / lista de procesos frecuentes)
    - o “Últimos procesos”
    - o navegación directa al proceso seleccionado
- NO se muestra dashboard en mobile.
- NO se habilita modo solapas en mobile.

Pantalla de home mobile (propuesta mínima):
- `Procesos frecuentes` (lista corta, configurable en futuro)
- `Búsqueda de procesos` (opcional futuro)
- `Últimos procesos` (lista)

## Tablet
- Similar a desktop, pero:
  - Sidebar puede iniciar colapsado
  - Dashboards pueden mostrarse en 1–2 columnas
  - Modo solapas permitido (si user/empresa lo habilita)

## Desktop
- Sidebar fijo (colapsable) + dashboards completos + (opcional) solapas.

---

# Modo Solapas (NO Mobile)

## Alcance
- Solo aplica en Desktop/Tablet.
- Preferencia por usuario (y opcionalmente por empresa si se decide).
- En mobile, este modo se ignora.

## Implementación
- Si `OpenProcessesInTabs = true`:
  - abrir procesos en `TabPanel`
- Si `false`:
  - navegación normal a pantalla única


# Secuencia de Inicialización Post-Login

1) Usuario autenticado
2) Usuario selecciona empresa
3) Backend valida pertenencia
4) Backend retorna:
   - Permisos
   - Menú permitido
   - UI Settings (ThemeName)
5) Frontend:
   - Aplica Theme
   - Carga stores globales (auth, company, menu)
   - Monta MainLayout

# Mobile - Particularidades

MobileHome NO es una versión simplificada del dashboard.
Es una pantalla orientada exclusivamente a ejecución de procesos.
No contiene KPIs, gráficos ni widgets analíticos.