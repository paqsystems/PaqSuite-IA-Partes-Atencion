# TR-007-update – Mobile v1 allowlist y procesos GEN-22 (3 pantallas)

Jira: PIAPARTES-85

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-007-update-mobile-v1](../../../03-historias-usuario/updates/100-SistemaPartes/HU-007-update-mobile-v1-alcance-gen22.md) |
| **SPEC relacionada** | [SPEC-007-mobile-v1-alcance-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-007-mobile-v1-alcance-update.md) |
| **Épica Jira** | PIAPARTES-83 |
| **Tipo TR** | `TR-MOB` |
| **Superficie** | `CAPACITOR` (rama `isNativeApp()` en `frontend/`) |
| **Repositorio propietario** | PaqSuite-IA-Partes-Atencion |
| **Contrato API** | Sin cambios BE v1 — reutilizar APIs TR-004 / TR-006 (`fetchInformeTareas`, `fetchPaqueteHoras`, `listTareas` CRUD carga) |
| **Prioridad** | MUST-HAVE (release mobile v1) |
| **Clasificación** | HU COMPLEJA (delta sobre TR-007) |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-11-27 |
| **TR base** | [TR-007-mobile-capacitor](../../100-SistemaPartes/TR-007-mobile-capacitor.md) |
| **Revisión C1** | **Apto** — [C1 mobile v1](./TR-007-update-mobile-v1-alcance-gen22-C1-ambiguity-review.md) (2026-11-27) |

**Adopción GEN-22 (MUST):** `isNativeApp`, `createMobilePolicy`, `genMobileExclusions`, `MobileRouteGuard`, `MobileMenuShell` / menú filtrado, `MobileConfigPanel`, `ConsultaKardexList`, `ProcessPageLayout`, `AuthLoginLayout`. **Prohibido** clonar engine de policy ni presentador kardex GEN.

---

## 1) Resumen del delta (v1 vs TR-007 / código actual)

| Tema | TR-007 / código hoy | TR-007-update v1 |
|------|---------------------|------------------|
| Allowlist | `/partes`, `/partes/consulta`, `/dashboard`, `/chat-assistant` + kardex legacy | Solo `/partes/carga`, `/partes/informes/consulta-detallada`, `/partes/informes/paquete-horas` + auth (`/change-password`, `/select-empresa`, `/login`…) |
| `partesWebOnlyExclusions` | **Bloquea** `/partes/informes/consulta-detallada` | **Quitar** ese patrón; seguir bloqueando agrupadas, emisiones, admin, maestros |
| Post-login | `resolvePostLoginRoute` → `/partes` (dashboard) | Primera ruta **permitida** entre 3 procesos (orden menú 30100→40100→40300) |
| `MobileRouteGuard` | `homePath="/partes"`, `bypassPaths={['/dashboard']}` | `homePath` = primera ruta permitida; **sin** bypass dashboard |
| `/partes/carga` | Misma vista que consulta (`ConsultaKardexMobilePage` + CRUD kardex) | Formulario individual SPEC-004 (extraer/reusar popup de kardex); **sin** grilla `CargaDiariaPage` |
| Consulta detallada | Policy **deniega** ruta; página solo web `ProcessDataGrid` | Allowlist **sí**; rama native = `ConsultaKardexList` + `fetchInformeTareas` **solo lectura** |
| Dashboard / chat | Montados en router native | Denegados policy + sin ruta útil en native |

**Backend:** ningún endpoint nuevo; envelope GEN-20 sin cambios.

---

## 2) Criterios de aceptación → verificación técnica

| CA HU | Verificación TR |
|-------|-----------------|
| CA-MV1-01…04 | T1–T4 policy, menú, router, post-login |
| CA-MV1-03, R-MV1-U10 | T5 formulario `/partes/carga` + tests validación TR-004 |
| CA-MV1-05, R-MV1-U11 | T6 consulta detallada kardex RO + API detallada |
| CA-MV1-07 | T7 `PaqueteHorasPage` native sin pivot/DataGrid (kardex/Chart opcional) |
| CA-MV1-06 | Sin cambio si TR-007 D ya cerró config/login GEN |
| CA-MV1-08 | `partesMobilePolicy.test.ts` actualizado |
| CA-MV1-09 | `npm run build:mobile` + `npx cap sync` |
| CA-MV1-10 | Tests/policy no alteran ramas `!isNativeApp()`; smoke web login → dashboard intacto |

---

## 3) Estado actual del código (baseline D)

| Archivo | Situación | Acción v1 |
|---------|-----------|-----------|
| `frontend/src/features/partes/mobile/partesMobilePolicy.ts` | Allowlist incluye dashboard, consulta legacy, chat; excluye consulta-detallada | T1 |
| `frontend/src/features/partes/mobile/partesMobilePolicy.test.ts` | Expectativas TR-007 antiguas | T1 |
| `frontend/src/features/auth/postLoginRouter.ts` | Siempre `/partes` tras login MONO | T2 |
| `frontend/src/features/partes/RequirePartesMobilePolicy.tsx` | `homePath="/partes"`, bypass dashboard | T3 |
| `frontend/src/app/AppRouter.tsx` | `/partes/carga` → `ConsultaRoute` kardex; `/partes` dashboard | T4 |
| `ConsultaKardexMobilePage.tsx` | Kardex CRUD `listTareas` día actual | T5 — dejar de usar en `/partes/consulta`; extraer form; o renombrar uso solo carga si aplica diseño |
| `PartesConsultasPages.tsx` `ConsultaDetalladaPage` | Grilla web en native | T6 — `if (native) return <ConsultaDetalladaMobileView />` |
| `PartesMenuSidebar.tsx` | Solo `partesMobilePolicy.filterItems` | T4 — opcional `mapMenuRouteForNative` (`/partes/carga-diaria` → `/partes/carga`) |
| `PaqueteHorasPage.tsx` | Rama `native` parcial | T7 — confirmar sin `ProcessDataGrid`/pivot en native |

---

## 4) Reglas de implementación

| ID | Regla |
|----|--------|
| RN-MV1-TR-01 | **Allowlist exacta v1:** `'/partes/carga'`, `'/partes/informes/consulta-detallada'`, `'/partes/informes/paquete-horas'`, `'/change-password'`, `'/select-empresa'`. **No** listar: `'/partes'`, `'/partes/consulta'`, `'/dashboard'`, `'/chat-assistant'`. Auth/guest vía `genMobileExclusions` + rutas login/forgot ya fuera del shell. |
| RN-MV1-TR-02 | **`partesWebOnlyExclusions`:** mantener bloqueo maestros, masivo, carga-diaria, carga-paquetes, agrupadas, emisiones, admin, params, tareas-programadas. **Eliminar** líneas que bloquean `consulta-detallada`. |
| RN-MV1-TR-03 | **`RequirePartesMobilePolicy`:** `homePath={resolveNativeMobileHomePath()}` (helper host); quitar `bypassPaths`. Deep-link denegado → `MobileRouteGuard` + `t('mobile.exclusion.*')` (A1-M5). |
| RN-MV1-TR-04 | **`resolveNativeMobileHomePath`:** dado menú ya filtrado por policy + SPEC-002, devolver `routeName` del primer proceso visible en orden **30100 → 40100 → 40300** (`pq_menus` / `menuKey` estable: `partes_carga_diaria`, `partes_consulta_detallada`, `partes_informe_paquete_horas`). Si ninguno → `/change-password` o pantalla error documentada (cliente sin procesos improbable). |
| RN-MV1-TR-05 | **`postLoginRouter`:** si `isNativeApp()`, tras gate empresa, navegar a `resolveNativeMobileHomePath()` en lugar de `'/partes'`. Web conserva `'/partes'`. |
| RN-MV1-TR-06 | **Router:** `/partes/carga` → `PartesCargaMobilePage` (nuevo o extraído); **sin** ruta native `/partes/consulta` (D-MV1-C1-03). Web sin cambio. `/partes` y `/dashboard` en native → redirect `replace` a `homePath` v1. |
| RN-MV1-TR-07 | **Carga mobile:** montar formulario una tarea (campos/popup hoy en `ConsultaKardexMobilePage`); `saveTarea`/`deleteTarea` TR-004; `data-testid` estables (`partesCargaMobile*` o reutilizar existentes); cliente = sin ruta. **Prohibido** `CargaDiariaPage` en native. |
| RN-MV1-TR-08 | **Consulta detallada mobile:** `ConsultaKardexList` + mapper fila `fetchInformeTareas` (mismo query que web); filtros DX compactos; default **fechaDesde=fechaHasta=hoy** al mount (A1-M1); **sin** FAB alta, **sin** `onItemTap` edición, **sin** `ProcessDataGrid`, **sin** Emitir. Detalle = drawer/modal **solo lectura**. |
| RN-MV1-TR-09 | **Menú native (MUST):** `transformPartesMenuItems` + policy; mapper `routeName` menú `/partes/carga-diaria` → `/partes/carga` en native (`onNavigate` o nodo transformado) — D-MV1-C1-05. |
| RN-MV1-TR-10 | **Paquete horas native:** reutilizar lógica datos actual; presentación kardex/cards; **sin** `ConsultaGrillaPivotShell`/`PivotGrid` en `isNativeApp()`; gráfico `bar` DX **opcional** (A1-M2) — si se omite, no falla DoD. |
| RN-MV1-TR-11 | **Tests Vitest:** tabla de rutas permitidas/denegadas alineada a CA-MV1-04; caso `consulta-detallada` **permitida**; `dashboard`/`chat`/**denegados**. |
| RN-MV1-TR-12 | **i18n:** claves producto para títulos kardex consulta detallada; no duplicar `grid.*` GEN. |

### 4.1 Decisiones de diseño (C1 cerradas — 27/11/2026)

| ID | Decisión |
|----|----------|
| **D-MV1-C1-01** | **`resolveNativeMobileHomePath(session)`** no depende del menú API en el instante del login. Usar catálogo estático ordenado: `partes_carga_diaria` → `/partes/carga`, `partes_consulta_detallada` → `/partes/informes/consulta-detallada`, `partes_informe_paquete_horas` → `/partes/informes/paquete-horas`. Saltar entrada si `partesMobilePolicy.isAllowed(ruta)` es false **o** SPEC-002 excluye (cliente → omitir carga). Primera entrada válida gana. Si ninguna: `/select-empresa` si gate lo exige; si no, `/change-password` solo en `firstLogin`; en otro caso pantalla `EmptyState` con i18n `mobile.noProcessAllowed` (clave producto; TR/D). |
| **D-MV1-C1-02** | Tras **`firstLogin`** y cambio de clave exitoso, native usa el **mismo** `resolveNativeMobileHomePath` (nunca `/partes`). Web sin cambio. |
| **D-MV1-C1-03** | En native **no** registrar ruta útil para `/partes/consulta` ni redirigir a consulta detallada; policy + guard envían a `homePath`. Eliminar `ConsultaRoute` dual para native en `AppRouter`. |
| **D-MV1-C1-04** | **`PartesCargaMobilePage`:** pantalla centrada en **formulario una tarea** (alta/edición vía popup o inline, patrón TR-004). Lista kardex del día **opcional** debajo del form; **no** es superficie principal ni sustituye consulta detallada. Extraer popup compartido desde `ConsultaKardexMobilePage` (T5). |
| **D-MV1-C1-05** | Mapper menú native **`/partes/carga-diaria` → `/partes/carga`** en `onNavigate` / `transformPartesMenuItems` es **obligatorio** (RN-MV1-TR-09 MUST), no opcional. |

---

## 5) Contrato FE por ruta (native)

| Ruta | Componente | API dominio |
|------|------------|-------------|
| `/partes/carga` | `PartesCargaMobilePage` | `listTareas` (solo si precarga), `saveTarea`, `deleteTarea`, catálogos TR-004 |
| `/partes/informes/consulta-detallada` | `ConsultaDetalladaMobileView` (o bloque en page) | `fetchInformeTareas` (+ catálogos filtros) |
| `/partes/informes/paquete-horas` | `PaqueteHorasPage` rama native | `fetchPaqueteHoras` |

Patrón consulta detallada (orientativo):

```typescript
if (!isNativeApp()) {
  return <ConsultaDetalladaWebGrid />
}
return (
  <ProcessPageLayout title={pageTitle}>
    <ConsultaKardexList
      items={rows.map(mapConsultaDetalladaToKardexItem)}
      filtersSlot={/* DateBox + SelectBox filtros */}
      onItemTap={openReadOnlyDetalle}
      onRefresh={load}
      t={(key) => t(key)}
      data-testid="partesConsultaDetalladaKardex"
    />
  </ProcessPageLayout>
)
```

Mapper nuevo en host (`mapConsultaDetalladaToKardexItem`) — no copiar desde Framework.

---

## 6) Plan de tareas (D1/D)

| ID | Capa | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1 | FE | Actualizar allowlist + exclusions + tests `partesMobilePolicy` | RN-MV1-TR-01/02/11; CA-MV1-04, CA-MV1-08 | S |
| T2 | FE | `resolveNativeMobileHomePath` + cablear `postLoginRouter` / `ChangePasswordPage` si comparte ruta | RN-MV1-TR-04/05; CA-MV1-02 | M |
| T3 | FE | `RequirePartesMobilePolicy` homePath dinámico; quitar bypass dashboard | RN-MV1-TR-03; CA-MV1-04 | S |
| T4 | FE | `AppRouter` redirects `/partes`, `/dashboard`; separar `/partes/carga` | RN-MV1-TR-06; CA-MV1-03 | M |
| T5 | FE | Extraer `PartesCargaMobilePage` / form desde kardex legacy | RN-MV1-TR-07; CA-MV1-03 | L |
| T6 | FE | Vista kardex RO consulta detallada + mapper + default día | RN-MV1-TR-08; CA-MV1-05 | L |
| T7 | FE | Ajustar `PaqueteHorasPage` native (sin pivot/grid desktop) | RN-MV1-TR-10; CA-MV1-07 | M |
| T8 | FE | Menú: mapper ruta carga-diaria → `/partes/carga` (MUST) | RN-MV1-TR-09; D-MV1-C1-05; CA-MV1-01 | S |
| T9 | QA | `npm run build:mobile`, `cap sync`, smoke manual emulador (checklist §8) | CA-MV1-09 | M |
| T10 | Docs | Manual usuario SPEC-007 §mobile v1 cuando F1; `partesMobileProcess.css` si nuevos testids | — | S |

**Orden recomendado D1:** T1 → T3 → T2 → T4 → T8 → T5 → T6 → T7 → T9.

---

## 7) Backend / datos

| Ítem | Acción |
|------|--------|
| SP / migraciones | **Ninguna** v1 |
| OpenAPI | Sin cambios |
| Menú seed | Sin cambios ids; rutas web `pq_menus` siguen `/partes/carga-diaria` — mapping native en FE (RN-MV1-TR-09) |

---

## 8) Tests

| Tipo | Alcance |
|------|---------|
| Vitest | `partesMobilePolicy.test.ts` completo v1; opcional mapper consulta detallada (1 caso fila → `KardexItem`) |
| Unit | `resolveNativeMobileHomePath` con menú mock (3 procesos / solo consulta / cliente sin carga) |
| Feature BE | No obligatorio v1 (sin cambio API) |
| Manual native | Checklist §9 |
| Web regression | Login → `/partes` dashboard; consulta detallada DataGrid — smoke rápido post-cambios |

---

## 9) Checklist smoke emulador (v1)

1. Config URL + health (GEN) si entorno limpio.  
2. Login tenant → **no** dashboard; aterriza en carga **o** primer proceso con permiso.  
3. Menú solo muestra hasta 3 procesos.  
4. Carga en `/partes/carga`: alta válida; tarea cerrada no editable.  
5. Consulta detallada: kardex día actual; detalle sin editar/borrar; sin emitir.  
6. Paquete horas: cliente obligatorio (no cliente); sin pivot.  
7. Deep-link `/chat-assistant` o `/partes/consulta` → bloqueo/redirect + mensaje.  
8. `npm run build:mobile` + `cap sync` OK.

---

## 10) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Duplicar form carga y kardex antiguo | Extraer componente compartido popup TR-004 |
| Post-login antes de menú cargado | Resolver home desde catálogo estático de rutas v1 + permisos sesión (`resultado.partes`) |
| Consulta detallada con `pageSize=0` pesado en mobile | Misma API que web; paginación kardex GEN si el listado supera umbral (TR acotar en D si hace falta) |
| Regresión TR-007 chat/dashboard | CA-MV1-10 + tests policy |

---

## 11) Discrepancias SPEC / HU

Ninguna bloqueante. HU permite gráfico paquete opcional (A1-M2); TR-007 exigía bar — **v1 no exige** gráfico para DoD.

---

## 12) Definition of Done (TR)

- [ ] RN-MV1-TR-01…12 aplicadas  
- [ ] CA-MV1-01…10 verificables (evidencia E/F1)  
- [ ] Sin `ProcessDataGrid` / pivot en ramas native tocadas  
- [ ] `partesMobilePolicy.test.ts` verde  
- [ ] `build:mobile` verde  

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-27 | Parte C SDD — TR-update desde SPEC-007-update + HU-007-update (PIAPARTES-83). |
| 2026-11-27 | Parte C1 — veredicto **Apto**; decisiones D-MV1-C1-01…05 en §4.1. |
| 2026-11-27 | Parte D1 — [plan](./D1-TR-007-update-mobile-v1-alcance-gen22.md). |
| 2026-11-27 | Parte D — T1–T8, T6–T7 FE; Vitest policy/home/menú OK; `npm run build:mobile` OK. Pendiente T9 smoke emulador §9. |
| 2026-11-27 | Parte E — `php artisan test`: abort por `max_execution_time` 80s tras ExampleTest (exit 255); 3 fallos BE previos no mobile en admin/emisiones. `npm run test`: 241/242 OK; fallo `ReportDesignerHostPage.test.tsx` (timeout DX, ajeno). Subset mobile: 14 tests OK (policy, home, menú, consultaDetalladaHostContext). E2E no corrido (§8). Jira PIAPARTES-85 comentario E. |