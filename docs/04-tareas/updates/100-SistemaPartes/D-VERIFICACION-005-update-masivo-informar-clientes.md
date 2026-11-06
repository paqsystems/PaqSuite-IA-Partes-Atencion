# D — Verificación TR-005-update (informar clientes + emisiones menú)

Jira: [PIAPARTES-45](https://paqsystems.atlassian.net/browse/PIAPARTES-45)

| Campo | Valor |
|-------|--------|
| **TR** | [TR-005-supervision-proceso-masivo.md](../../100-SistemaPartes/TR-005-supervision-proceso-masivo.md) (Parte I 04/11/2026) |
| **Estado TR** | Finalizado |
| **Alcance** | CC-PQ #7 + #15 (mail masivo) · smoke emisión vacía por proceso menú · E2E Emitir · [D-VERIFICACION-CC-PQ-15](./D-VERIFICACION-CC-PQ-15-2026-11-04.md) |

---

## 1) Objetivo

Validar de punta a punta:

1. **Proceso masivo** — informar novedades a clientes (supervisor, filtros, mail i18n, guard salida).
2. **Emisiones GEN-15** — cada ítem de menú Partes con **Emitir** incorporado abre el flujo de emisión (diálogo GEN).
3. **Smoke API** — job PDF con **dataset 0 filas** (reporte vacío) por `processCode` host.

---

## 2) Precondiciones entorno

| Ítem | Detalle |
|------|---------|
| Tenant | `X-Paq-Cliente` / `?cliente=` coherente (ej. DEMO local) |
| Usuario | Supervisor (`admin` seed) para masivo y emisiones |
| Parámetros | `EmissionEnabled = S`; `PartesMasivoMaxIds` según seed |
| SQL prod | `pq_sp_partes_masivo_destinatarios_novedad.sql` desplegado |
| Mail | `MAIL_MAILER=log` o fake en tests; SMTP real solo manual opcional |
| FE | Web (no Capacitor) para masivo y Emitir |

---

## 3) Matriz TR-005 — informar clientes (CC #7 / #15)

| ID | Caso | Tipo | Comando / ruta | Esperado |
|----|------|------|----------------|----------|
| M-01 | Ícono mail toolbar | E2E | `partes-masivo-selection.spec.ts` | `partesMasivoInformarClientes` visible |
| M-02 | Preview destinatarios | API | `GET .../masivo/informar-clientes/preview` | `destinatarioCount` ≥ 0 |
| M-03 | Envío OK | Feature | `ApiV1PartesMasivoTest::test_informar_clientes_preview_y_envio` | 200, Notification fake |
| M-04 | Sin destinatarios | Feature | `test_informar_clientes_sin_destinatarios` | 422 `partes.masivo.sinDestinatarios` |
| M-05 | No supervisor | Feature | `test_informar_clientes_forbidden_no_supervisor` | 403 `partes.masivo.forbidden` |
| M-06 | Tope destinatarios | Manual / Feature | Param bajo + muchos clientes con user | 422 `partes.masivo.topeExcedido` |
| M-07 | Mail locale EN | Manual | Cliente user `locale=en` | Asunto/cuerpo EN en log |
| M-08 | URL tenant | Manual | Cuerpo mail | `https://{tenant}.partesatencion.paqsystems.com` |
| M-09 | Confirmación N | E2E / Manual | Clic ícono | Popup `partesMasivoInformarConfirmDialog` |
| M-10 | Guard salida | Manual | Selección sin aplicar + link menú | `partesMasivoLeaveInformarDialog` |
| M-11 | beforeunload | Manual | Recarga con dirty | Prompt navegador |
| M-12 | i18n 5 locales | Vitest / Manual | Cambiar idioma shell | Claves `partes.masivo.mailInformarClientes.*` |
| M-13 | Native excluido | Policy | `partesMobilePolicy` | Sin ruta masivo |
| M-14 | JOIN destinatarios | Unit | `PartesMasivoLocaleTest` + code review | Sin `whereIn` N incierto |

### 3.1 Pasos manuales masivo (supervisor)

1. Login supervisor → **Proceso masivo**.
2. Filtro fechas (hoy–hoy); cliente con `user_id` + email en Framework.
3. Ícono mail habilitado; confirmar N y rango; enviar → envelope OK.
4. Revisar log mail: empresa, periodo, código usuario, enlace forgot **sin** password.
5. Marcar filas / tocar campos lote **sin** aplicar → intentar ir a Carga diaria → diálogo guard.
6. **Enviar correos y salir** → navegación OK; **Salir sin enviar** → navegación sin mail.

---

## 4) Emisiones — procesos con Emitir en menú

Catálogo SoT FE: `frontend/src/features/partes/informes/partesEmissionMenuCatalog.ts`

| processCode | Ruta | testid Emitir | testid página |
|-------------|------|---------------|---------------|
| `partes.informes.consultaDetallada` | `/partes/informes/consulta-detallada` | `partesConsultaDetalladaEmit` | `partesConsultaDetalladaPage` |
| `partes.informes.paqueteHoras` | `/partes/informes/paquete-horas` | `partesPaqueteHorasEmit` | `partesPaqueteHorasPage` |

**Fuera de este E2E:** diseñador `/emisiones/disenador` (permiso `emission.design`) — cubierto en `partes-emisiones.spec.ts` (caso diseñador).

---

## 5) Smoke — reporte vacío (0 filas) por proceso

### 5.1 Automatizado (CI / local)

```powershell
cd backend
php artisan test --filter=EmissionEmptyReportPerProcessTest
```

Por cada proceso: `POST /api/v1/emissions/jobs` (pdf, consolidated), `selectedTareaIds` fantasma → `datasetRowCount = 0`, download PDF 200.

### 5.2 Contra API levantada

```powershell
# Backend en :8010, tenant DEMO
php backend/scripts/dev/smoke-emissions-empty-report.php http://127.0.0.1:8010/api/v1 DEMO admin Paqsystems
```

Salida JSON: por `processCode`, `status=done`, `datasetRowCount=0`. Exit code 0 = OK.

**Nota:** en entorno sin reporte emisible no estándar, crear uno vía diseñador o usar seed `PqEmissionSeeder` + reporte smoke (como en Feature test).

---

## 6) E2E — llegar a Emitir por menú

```powershell
cd frontend
npx playwright test tests/e2e/partes-emisiones.spec.ts
```

| Test | Qué valida |
|------|------------|
| Bucle `partesEmissionMenuEntries` | Login → ruta informe → botón Emitir → `emission.dialog` |
| Diseñador | `/emisiones/disenador` → confirmar proceso → host DX |

Variables opcionales: `PW_LOGIN_USER`, `PW_LOGIN_PASSWORD`, base URL Playwright config.

---

## 7) Regresión automatizada recomendada (Parte E)

```powershell
# Backend
cd backend
php artisan test --filter=ApiV1PartesMasivoTest
php artisan test --filter=EmissionEmptyReportPerProcessTest
php artisan test --filter=PartesMasivoLocaleTest

# Frontend
cd frontend
npm run test -- --run src/features/partes/masivo/
npm run test -- --run src/features/partes/informes/partesEmissionMenuCatalog.test.ts
npx playwright test tests/e2e/partes-masivo-selection.spec.ts tests/e2e/partes-emisiones.spec.ts
```

---

## 8) Criterio de cierre D / F1

- [ ] Todos los ítems §3 matriz M-01…M-14 verificados (auto o manual documentado).
- [ ] Smoke §5 exit 0 en preview/staging acordado.
- [ ] E2E §6 verde contra backend real o CI con stack efímero.
- [ ] Sin regresión en `ApiV1EmissionsPartesTest` (suite TR-011).
- [ ] TR-005-update estado **Finalizado** tras revisión humana (Parte I).

---

## 9) Riesgos conocidos

| Riesgo | Mitigación |
|--------|------------|
| ~~Tests Feature masivo fallan por drift seeder `PQ_TASK_PROCESS`~~ | **Resuelto:** `PqTaskProcessFrameworkSeeder` filtra columnas; menú SQLite vía `SpCaller` + `pq_sp_admin_menu_list_enabled` |
| Paquete horas E2E: Emitir disabled sin cliente | Elegir cliente en pantalla o seed API |
| PDF vacío en DX fake vs remote | Smoke valida contrato API; PDF visual = manual TR-011 |
