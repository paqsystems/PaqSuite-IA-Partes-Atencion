# Plan de implementación — TR-007-update mobile v1

## Tipo de trabajo

Update (delta TR-007 / SPEC-007-update).

## Alcance entendido

Recortar **Capacitor** a tres procesos: carga individual (`/partes/carga`), consulta detallada kardex solo lectura, paquete de horas. Actualizar **allowlist** `partesMobilePolicy`, **post-login** y **home** native, **router** y **menú** (mapper `carga-diaria` → `carga`), extraer pantalla de carga desde kardex legacy, rama kardex RO en consulta detallada, ajuste paquete horas sin pivot. **Sin** cambios BE/BD. Web intacta salvo helpers compartidos que ramifican con `isNativeApp()`.

## Artefacto gobernante

[TR-007-update-mobile-v1-alcance-gen22](./TR-007-update-mobile-v1-alcance-gen22.md) (decisiones C1 **D-MV1-C1-01…05** en §4.1).

## Fuentes leídas

- SPEC: [SPEC-007-mobile-v1-alcance-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-007-mobile-v1-alcance-update.md) (§2.1.1 D-MV1-A1-01…04)
- HU: [HU-007-update-mobile-v1-alcance-gen22](../../../03-historias-usuario/updates/100-SistemaPartes/HU-007-update-mobile-v1-alcance-gen22.md) (CA-MV1-01…10)
- TR: [TR-007-update](./TR-007-update-mobile-v1-alcance-gen22.md) · C1 [informe](./TR-007-update-mobile-v1-alcance-gen22-C1-ambiguity-review.md)
- Código baseline: `partesMobilePolicy.ts`, `postLoginRouter.ts`, `RequirePartesMobilePolicy.tsx`, `AppRouter.tsx`, `ConsultaKardexMobilePage.tsx`, `PartesConsultasPages.tsx`, `PaqueteHorasPage.tsx`, `PartesMenuSidebar.tsx`, `PartesMobileMenu.tsx`, `ShellPage.tsx`, `ChangePasswordPage.tsx`

## Impacto esperado

### Base de datos

Ninguno (rutas menú web siguen en `pq_menus`; mapping solo FE).

### Backend

Ninguno (reutilizar `listTareas`, `saveTarea`, `fetchInformeTareas`, `fetchPaqueteHoras`).

### Frontend (CAPACITOR / `isNativeApp()`)

| Área | Archivos previstos |
|------|-------------------|
| Policy T1 | `mobile/partesMobilePolicy.ts`, `partesMobilePolicy.test.ts` |
| Home / auth T2 | **Nuevo** `mobile/resolveNativeMobileHomePath.ts` (+ test); `postLoginRouter.ts`; `ChangePasswordPage.tsx` (post-cambio clave + cancelar sin hardcode `/partes` en native); posible extensión tipo `PostLoginRoute` |
| Guard T3 | `RequirePartesMobilePolicy.tsx` |
| Router T4 | `AppRouter.tsx` (redirects native, quitar `ConsultaRoute` en `/partes/carga` y `/partes/consulta`; rutas denegadas → guard) |
| Carga T5 | **Nuevo** `mobile/PartesCargaMobilePage.tsx` + componente popup compartido extraído de `ConsultaKardexMobilePage.tsx` |
| Consulta detallada T6 | `PartesConsultasPages.tsx` o vista dedicada `ConsultaDetalladaMobileView.tsx` + mapper kardex; i18n producto en `productI18n.ts` |
| Paquete horas T7 | `PaqueteHorasPage.tsx` (confirmar sin `ProcessDataGrid`/pivot en native) |
| Menú T8 | `PartesMenuSidebar.tsx` (`transformPartesMenuItems` o helper `mapNativeMenuRoute`) + `ShellPage.tsx` / `PartesMobileMenu.tsx` `onNavigate` |

Web: ramas `!isNativeApp()` sin cambio funcional (CA-MV1-10).

### Mobile (TR-MOB)

Superficie **Capacitor** en `frontend/`; no `mobile/` Flutter.

### Tests

- Vitest: `partesMobilePolicy.test.ts` (tabla v1), `resolveNativeMobileHomePath.test.ts` (cliente / 3 procesos / solo informes), opcional mapper consulta detallada.
- Existentes: `consultaDetalladaHostContext.test.ts` (emit native ya false); `partesMenuSidebar.test.ts` si se toca transform.
- Manual: checklist TR §9 tras T9.

### Documentación

T10 post-F1 (manual); este D1 + historial TR.

### DevOps

Tras D: `npm run build:mobile` + `npx cap sync` (T9). Sin variables `.env` nuevas.

## Orden de trabajo

Alineado a TR §6 (T1 → T3 → T2 → T4 → T8 → T5 → T6 → T7 → T9):

1. **T1 — Policy:** allowlist v1 (`/partes/carga`, consulta-detallada, paquete-horas + auth); quitar `/partes`, `/consulta`, `/dashboard`, `/chat-assistant`; sacar exclusión `consulta-detallada` de `partesWebOnlyExclusions`; tests.
2. **T3 — Guard:** `homePath` desde `resolveNativeMobileHomePath()`; sin `bypassPaths`.
3. **T2 — Home helper:** implementar catálogo estático D-MV1-C1-01 con skip cliente en carga (`tipoFuncional === 'cliente'` / mismo criterio que `filterMenuForCliente`); integrar en `postLoginRouter` y `ChangePasswordPage`.
4. **T4 — Router:** `/partes/carga` → `PartesCargaMobilePage`; eliminar uso native de `ConsultaRoute` en `/partes/consulta`; en native, `/partes` y `/dashboard` → redirect a home (o confiar guard — preferir `Navigate` explícito + guard).
5. **T8 — Menú MUST:** mapear `/partes/carga-diaria` → `/partes/carga` en `onNavigate` (Shell) y/o `routeName` en árbol transformado antes de policy.
6. **T5 — Carga:** extraer popup/form TR-004; pantalla form-centric; kardex día opcional debajo; dejar de enrutar kardex full en `/partes/carga`.
7. **T6 — Consulta detallada:** early return native con `ConsultaKardexList`; default fechas **hoy**; detalle RO; sin emitir/selección masiva.
8. **T7 — Paquete horas:** revisar rama `native` existente; sin pivot shell.
9. **T9 — QA:** build mobile + smoke §9.
10. **T10 — Docs:** en F1.

## Riesgos

| Riesgo | Mitigación D |
|--------|----------------|
| `postLoginRouter` tipado solo `'/partes'` | Ampliar union con rutas v1 o usar `string` acotado en rama native |
| Cliente sin `/partes/carga` en menú pero allowlist la tiene | Home helper omite carga si cliente (D-MV1-C1-01) |
| Duplicar lógica menú vs home | Catálogo estático por `menuKey` + misma policy `isAllowed` |
| `ConsultaDetalladaPage` ~600 líneas | Vista mobile en archivo nuevo; web sin mover grilla |
| `ChangePasswordPage` botón cancelar a `/partes` | Ramificar native → home helper |
| Regresión web | Tests policy + smoke login → `/partes` |

## Tests a ejecutar

```text
cd frontend && npm run test -- partesMobilePolicy resolveNativeMobileHomePath partesMenuSidebar consultaDetalladaHostContext
npm run build:mobile && npx cap sync
```

Manual: TR §9 (8 ítems).

## Dudas / bloqueos

Ninguno bloqueante tras C1. **C1-M2:** unit `resolveNativeMobileHomePath` recomendado en T2, no obligatorio para abrir D.

**Paginación consulta detallada:** si `fetchInformeTareas` devuelve volumen alto, usar paginación kardex GEN en T6 (C1-M1); no nuevo endpoint.

## Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí** (solo delta mobile v1; BE/BD sin cambios; web dashboard/grillas preservadas).

---

| Campo | Valor |
|-------|--------|
| TR | PIAPARTES-85 |
| Fecha D1 | 2026-11-27 |
| Parte SDD | D1 |