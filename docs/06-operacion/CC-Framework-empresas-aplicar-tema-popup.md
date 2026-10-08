# CC Framework — ABM Empresas: botón Aplicar + preview tema en Popup

**Origen:** Partes de Atención (host) · Preview develop · 2026-10-08  
**Paquete:** `@paqsuite/react-core` (`EmpresasAdminPage` / `EmpresaFormPopup`)  
**Pin host actual:** `2.4.17`  
**Alcance:** **solo Framework** — el host ya cablea `onPreviewTheme` → `applyDevExtremeTheme` (A1). No forkear el formulario en el producto.

---

## 1) UX — botón «Aplicar» como ícono junto al lookup

### Síntoma / pedido
El botón **Aplicar** es un `Button` con texto en la fila de acciones (junto a Cancelar / Guardar). Se pide:

- Ícono **al lado del SelectBox de Apariencia** (mismo renglón que el lookup).
- Sin texto visible en el control.
- `hint` / tooltip = «Aplicar» (i18n: `admin.empresas.applyTheme`).
- Mantener `data-testid="admin.empresas.applyTheme"`.

### Dónde está hoy (2.4.17)
`src/features/empresas/EmpresaFormPopup.tsx`: `SelectBox` de theme y, más abajo, en el footer:

```tsx
{onPreviewTheme ? (
  <Button
    text={translate('admin.empresas.applyTheme')}
    type="normal"
    stylingMode="outlined"
    onClick={handleApplyTheme}
    elementAttr={{ 'data-testid': 'admin.empresas.applyTheme' }}
  />
) : null}
```

### Cambio esperado en Framework
1. Quitar el botón de texto del footer.
2. Agrupar lookup + ícono en un flex row, p. ej.:

```tsx
<div style={{ display: 'flex', gap: 8, alignItems: 'flex-end' }}>
  <div style={{ flex: 1 }}>
    <SelectBox /* theme … */ />
  </div>
  {onPreviewTheme ? (
    <Button
      icon="refresh" /* o icono DX acordado (p.ej. "palette" / "tips") */
      stylingMode="outlined"
      hint={translate('admin.empresas.applyTheme')}
      disabled={saving}
      onClick={handleApplyTheme}
      elementAttr={{ 'data-testid': 'admin.empresas.applyTheme' }}
    />
  ) : null}
</div>
```

3. Regla host/producto: acciones por ícono + `hint` i18n (misma convención GEN grillas).
4. Tests SDK: assert por `data-testid` + `hint`, no por texto del botón.

---

## 2) Preview A1 — Popup y controles no cambian de backcolor

### Síntoma
Al pulsar **Aplicar** (`onPreviewTheme` → host `applyDevExtremeTheme`):

- El **shell** / página de fondo puede actualizarse.
- El **Popup de edición** y los **controles DevExtreme dentro del modal** (TextBox, SelectBox, CheckBox, botones, fondo del overlay) **no** reflejan el nuevo tema (backcolor / tokens).

### Causa probable (Framework / DX)
`themes.current(dxKey)` + `applyShellTokens` actualizan documento/shell, pero:

- El `Popup` DX ya montado (overlay) **no re-aplica** estilos de tema a su wrapper ni a editores hijos.
- Los widgets dentro del popup quedan con colores del tema previo hasta remount o refresh.

El host **no** puede remountar el contenido interno del `EmpresaFormPopup` del SDK.

### Cambio esperado en Framework
Tras `onPreviewTheme(theme)` (o dentro del propio flujo de preview del SDK):

1. **Remount** del cuerpo del formulario (key = theme aplicado / contador de preview), **o**
2. Forzar refresh de widgets DX del popup (API DX vigente: recrear instancia / invalidar estilos del overlay), **y**
3. Asegurar que el contenedor del `Popup` herede tokens A1 (`data-theme` / CSS variables shell) — no solo `#root` / shell layout.

Criterio de aceptación:

- [ ] Con modal abierto, elegir otra apariencia → **Aplicar** → fondo del popup + inputs/botones del modal cambian al instante.
- [ ] **Cancelar** (con preview activo) restaura tema comprometido también en el popup (vía `onRestoreCommittedTheme` + mismo remount/refresh).
- [ ] Shell sigue coherente (comportamiento host actual).

### Fuera de alcance host
No duplicar `EmpresaFormPopup` en Partes. Tras fix en `react-core`, bump de pin en el host y smoke en `/admin/empresas`.

---

## Trazabilidad

| Ítem | Repo | Archivo |
|------|------|---------|
| UI Aplicar ícono | react-core | `EmpresaFormPopup.tsx` |
| Preview popup/controles | react-core | `EmpresaFormPopup.tsx` (+ tema A1 / DX si aplica) |
| Callback host (OK) | Partes | `EmpresasAdminPage.tsx` → `applyDevExtremeTheme` |

**Bump sugerido:** publicar `react-core` ≥ versión con este CC; host `package.json` → mismo pin.
