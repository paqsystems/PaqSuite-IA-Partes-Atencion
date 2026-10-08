# MONO — ABM Empresas (GEN-06) y apariencia A1

Aplica a hosts con **`PAQSUITE_TENANCY=single`** (Partes de Atención y productos mono-empresa).

Complementa: [`integracion-framework-sdk.md`](./integracion-framework-sdk.md) · Framework GEN-06 / SPEC-001-19 (tema empresa).

---

## Contrato API en MONO

| Operación | Permitido | Campos expuestos |
|-----------|-----------|-------------------|
| `GET /api/v1/admin/empresas` | Sí | `id`, `nombreEmpresa`, `habilitada`, `theme` |
| `GET/PUT …/empresas/{id}` | Sí | Igual |
| `POST` alta / candidatos ERP | **No** | Backend: `empresas.alta.monoForbidden` |
| `nombreBd`, `erpEmpresaId` | **No** en MONO | Solo MULTI / dictionary + conexión por empresa |

Persistencia host: `pq_empresa` (`id`, `nombre`, `activo`, `theme`) vía SP `pq_sp_admin_empresas_*`.

El **`id`** de `pq_empresa` es el **código de empresa** visible en UI (no confundir con ID ERP de instalaciones MULTI).

---

## UI (`EmpresasAdminPage` SDK + host)

### Must en MONO

1. **`tenancyMode="single"`** en el wrapper del host (`frontend/src/features/admin/security/EmpresasAdminPage.tsx`).
2. **Grilla:** columna **Código** (`id`); **ocultar** columna «Base de datos».
3. **Modal edición:** campo solo lectura **Código** = `id`; **no** mostrar «ID ERP» ni «Base de datos» (evita `undefined` cuando la API no devuelve `erpEmpresaId`).
4. **Vista previa de apariencia:** botón **Aplicar** que invoque el **puente A1 del host** (`applyDevExtremeTheme` en `frontend/src/theme/devExtremeThemeSwitcher.ts`), que delega en el SDK (`applyDxTheme` + `applyShellTokens` + catálogo `paqsuite.*`); **no** duplicar `empresaThemeCatalog` ni CSS de shell. **Cancelar** restaura el tema grabado (`onRestoreCommittedTheme`). Si cambia el grupo DX (Generic ↔ Material ↔ Fluent), `reloadOnGroupChange: true` recarga la SPA.
5. **Guardar:** tras `PUT`, actualizar `empresas[]` en sesión auth y reaplicar tema (sesión coherente sin re-login).
6. **i18n:** claves producto `admin.empresas.field.codigo`, `admin.empresas.applyTheme`, `admin.empresas.applyThemeHint`, `admin.empresas.monoNote` (5 locales); mapeo SDK → host en `securityEmpresasSdkI18n.ts`.

### Props SDK (react-core ≥ oleada con este fix)

| Prop | Uso host MONO |
|------|----------------|
| `onPreviewTheme` | `applyDevExtremeTheme` (SDK tokens + tema DX) |
| `onRestoreCommittedTheme` | Restaurar tema al cancelar tras vista previa |
| `onEmpresaSaved` | `patchAuthSession` + `applyDevExtremeTheme` |

### MULTI (referencia)

- Alta desde candidatos ERP, columnas `nombreBd` / `erpEmpresaId`, selector de empresa activa (GEN-05). No aplicar las ocultaciones MONO.

---

## Checklist adopción (nuevo proyecto MONO)

- [ ] `EmpresasAdminPage` con `tenancyMode="single"` y callbacks de tema
- [ ] SP admin empresas sin campos ERP/BD en respuesta
- [ ] Puente A1: `devExtremeThemeSwitcher.ts` (SDK; sin fork de catálogo ni `shellAppearanceBridge.css`)
- [ ] `registerEmpresasAdminI18nResources` en bootstrap i18n (labels `appearance.paqsuite.*`)
- [ ] `ThemeProvider` en `main.tsx` (tema empresa tras login; login público sin A1)
- [ ] i18n empresas (código, aplicar, nota MONO)
- [ ] Smoke: editar empresa → Aplicar tema → shell y grilla cambian; Cancelar revierte; Guardar persiste

---

## Trazabilidad

| GEN | Notas |
|-----|--------|
| GEN-06 | ABM empresas; MONO = solo edición |
| GEN-19 | Tema A1 por empresa (`theme` en `pq_empresa`) |
