# TR-007 – Mobile Capacitor del módulo Sistema Partes

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-007-mobile-capacitor](../../03-historias-usuario/100-SistemaPartes/HU-007-mobile-capacitor.md) |
| **SPEC relacionada** | [SPEC-007-mobile-capacitor](../../05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Asistente / supervisor / cliente (mismas reglas; UX native) |
| **Dependencias** | [TR-002](./TR-002-identidad-funcional-y-acceso.md) … [TR-006](./TR-006-consultas-dashboard-navegacion.md) (dominio/API); Capacitor GEN del `frontend/` |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Pendiente (D implementado — verificar F1; Capacitor scaffold incompleto) |
| **Última actualización** | 2026-07-30 (D) |

**Origen:** [HU-007](../../03-historias-usuario/100-SistemaPartes/HU-007-mobile-capacitor.md)  
**Referencia SPEC:** [SPEC-007](../../05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md)

---

## 1) HU refinada (resumen)

### In scope
- Capacitor Android+iOS: config URL + health; login empresa→`X-Paq-Cliente`.
- Shell native: menú filtrado; post-login → dashboard; sin timer auto.
- Kardex partes (CRUD según rol/`cerrado`); formulario una tarea = misma UX.
- Informe Paquete de Horas (híbrido + gráfico barra DX) + menú Informes + atajo dashboard.
- Policy `partesMobilePolicy` + tests; `build:mobile` + `cap sync`.

### Out of scope
- ABM, pivot, masivo, Excel, impresos, openInNewTab, seguridad admin, IA chatbot, RN/Flutter.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | Config URL + health antes de save; Preferences; login empresa primero |
| AC-02 | `X-Paq-Cliente` desde empresa; inválida → `tenant.invalid` |
| AC-03 | `resultado.partes` gobierna menú (cliente sin carga) |
| AC-04 | Sin ABM/pivot/masivo/Excel/seguridad admin en native (policy + menú) |
| AC-05 | Kardex default día actual; CRUD asistente/supervisor; cliente RO |
| AC-05b | Formulario única UX kardex (± menú Carga sin divergencia) |
| AC-06 | Mismas validaciones SPEC-004/TR-004 |
| AC-07 | Cerrada no editable/eliminable |
| AC-08 | Informe: total + desglose cliente + desglose tipo + gráfico barra |
| AC-08b | Menú Informes + atajo dashboard |
| AC-09 | Navegación in-app |
| AC-10 | `npm run build:mobile` + `npx cap sync` OK |

---

## 3) Reglas

R-MO-01…11.

| ID | Implementación |
|----|----------------|
| RN-TR-01 | Policy: archivo `frontend/src/features/partes/mobile/partesMobilePolicy.ts` (+ `partesMobilePolicy.test.ts`). |
| RN-TR-02 | Allowlist native (prefijos/rutas): `/partes` (dashboard), `/partes/consulta`, `/partes/carga` (opcional → mismo form), `/partes/informes/paquete-horas`. Deny: `/archivos/partes/**`, `/partes/proceso-masivo`, `/partes/informes/consulta-detallada`, `/partes/informes/consultas-agrupadas`, pivot/excel/admin seguridad GEN. |
| RN-TR-03 | Deep-link a ruta deny → redirect `/partes` + toast i18n `mobile.routeExcluded`. |
| RN-TR-04 | Gráfico informe: **`devextreme-react/chart`** serie tipo **`bar`** (eje categorías = cliente **o** tipo según tab/segmento activo; default = desglose por **cliente**). Sin Pie/Polar/más widgets. |
| RN-TR-05 | Fachada API: **`GET /partes/informes/paquete-horas`** orquesta dashboard snapshot (totales) + 2 llamadas agregación (`eje=cliente`, `eje=tipo`) o un SP compuesto; FE no recalcula reglas. |
| RN-TR-06 | SP opcional D1: `pq_sp_partes_informe_paquete_horas` (un resultset totales + dos de desglose) **o** orquestación PHP de `pq_sp_partes_dashboard_snapshot` + `pq_sp_partes_informe_agrupado` ×2. C1: **orquestación PHP** Must; SP compuesto = Should si performance lo pide. |
| RN-TR-07 | Dashboard mobile periodo UI: selector **mes** compacto (default mes sistema); sin auto-timer; pull-to-refresh + botón. |
| RN-TR-08 | Kardex: default `fechaDesde=fechaHasta=hoy`; FAB/agregar + acciones tarjeta; detalle modal/drawer; form reutilizable. |
| RN-TR-09 | Menú seed: ítem `partes_informe_paquete_horas` → `/partes/informes/paquete-horas` (grupo Informes); visible en native (no filtrado por policy). |
| RN-TR-10 | Smoke MVP: unit policy + `build:mobile`; humo emulador **manual** checklist (§8); CI E2E dispositivo **no** Must. |

---

## 4) Datos / backend

| Pieza | Detalle |
|-------|---------|
| Reuso | `GET /partes/tareas` CRUD TR-004; `GET /partes/dashboard`; `GET /partes/informes/agrupado` |
| Nuevo | `GET /partes/informes/paquete-horas?fechaDesde&fechaHasta&clienteId?&usuarioId?` → `{ totalMinutos, cantidadTareas, porCliente[], porTipo[] }` |
| Menú | Seed ítem informe + flags/filtrado client policy |

Sin nuevas tablas.

---

## 5) Frontend native

| Pieza | Ruta / pieza | Notas |
|-------|--------------|--------|
| Config | GEN Capacitor | Solo URL API; testids `mobileConfig*` |
| Login | GEN + `loginTenant` | Empresa → header |
| Dashboard | `/partes` | Cards indicadores + atajo informe; pull-to-refresh |
| Kardex | `/partes/consulta` | List/cards DX; CRUD; empty i18n |
| Form tarea | modal/página desde kardex | Misma validación TR-004; sin IA |
| Informe | `/partes/informes/paquete-horas` | Totales + 2 listas + Chart bar |
| Policy | `partesMobilePolicy` | Allow/deny + tests |
| i18n | `mobile.*`, `partes.mobile.*` | |

Patrón:

```typescript
if (isNativeApp()) {
  return <PartesConsultaKardexMobileView />;
}
return <PartesConsultaWebView />; // o redirect policy
```

---

## 6) Plan de tareas

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1 | FE | Policy + tests + filtro menú/deep-link | AC-04,09 | M |
| T2 | FE | Config/login verify (GEN) + post-login `/partes` | AC-01…03 | S |
| T3 | FE | Dashboard mobile + pull | AC-03 | M |
| T4 | FE | Kardex + form CRUD | AC-05…07 | L |
| T5 | BE+FE | Fachada paquete-horas + pantalla + Chart bar + menú/atajo | AC-08,08b | M |
| T6 | Build | `build:mobile` + `cap sync`; checklist humo | AC-10 | S |
| T7 | Docs | OpenAPI fachada; runbook smoke mobile Partes | | S |

---

## 7) Tests

- Vitest: `partesMobilePolicy` allow/deny; deep-link redirect.
- Feature: `GET .../paquete-horas` delimitación por rol (reusa asserts dashboard/agrupado).
- E2E web: no sustituye native; opcional skip.
- Manual: emulador Android (URL `10.0.2.2`) — login tenant, kardex alta, informe+gráfico.

---

## 8) Checklist humo emulador (AC-10 complemento)

1. Config URL + health OK + save.  
2. Login empresa+user+pass → dashboard.  
3. Kardex día actual; alta tarea; editar; cerrada RO.  
4. Informe paquete horas: total + listas + barra.  
5. Menú sin ABM/masivo/pivot.  
6. `npm run build:mobile` + `npx cap sync` sin error.

---

## 9) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Bypass menú por URL | Policy en router guard native |
| Chart DX en Capacitor | Solo `chart` bar; sin pivot |
| Duplicar form carga | Un componente compartido |

---

## 10) Checklist DoD

- [ ] AC-01…10  
- [ ] Policy + fachada + Chart bar  
- [ ] build:mobile + sync  
- [ ] Humo emulador documentado  

---

## 11) Informe C1

# Revisión de ambigüedad - TR-007

## Resultado general
- **Apto con observaciones** (absorbidas)

## Críticas cerradas
- Policy → **`partesMobilePolicy.ts`** + allowlist/denylist §RN-TR-02/03
- Gráfico → **DX Chart tipo `bar`**, default serie por **cliente**
- Fachada → **`GET /partes/informes/paquete-horas`**; orquestación PHP Must
- Rutas native → `/partes`, `/partes/consulta`, `/partes/informes/paquete-horas` (+ `/partes/carga` opcional)
- Dashboard periodo → selector **mes** compacto; sin auto-refresh
- Smoke → unit + build:mobile; emulador manual; CI dispositivo no Must
- Menú informe → seed `partes_informe_paquete_horas` + atajo dashboard

## Menores
- SP compuesto paquete horas = Should
- Menú Carga = opcional seed; misma UX form

## Veredicto
- **Puede pasar a D1/D: Sí**

---

## 12) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte C + C1: TR mobile; policy, Chart bar, fachada, rutas y smoke cerrados. |
| 2026-07-30 | Parte D: `partesMobilePolicy`, kardex, paquete-horas + Chart bar, fachada API, menú/guard native. **Pendiente:** scaffold Capacitor (`build:mobile` / `cap sync`) — no hay android/ios en este clone. |

---

**Siguiente:** F1 de TR-007 tras bootstrap Capacitor GEN + humo emulador.
