# SPEC-007-update – Mobile v1 Partes (alcance menú GEN-22)

Jira: PIAPARTES-83

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-007-update-mobile-v1 |
| Título | Mobile Capacitor v1 — menú reducido a Carga diaria, Consulta detallada y Paquete de horas |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-11-27 |
| SPEC base | [SPEC-007](../../100-SistemaPartes/SPEC-007-mobile-capacitor.md) |
| Fuentes producto | [`21-mobile-v1-alcance-gen22.md`](../../../02-producto/Sistema-Partes-IA/21-mobile-v1-alcance-gen22.md); [`10-mobile.md`](../../../02-producto/Sistema-Partes-IA/10-mobile.md) |
| Depende de | SPEC-002, SPEC-004, SPEC-006; Framework **GEN-22** ([SPEC-001-22](../../../../PaqSuite-IA-FRAMEWORK/docs/05-open-spec/001-Generalidades/SPEC-001-22-experiencia-mobile.md)) |
| HU relacionada(s) | *(pendiente Parte B — update HU-007 o HU-007-update)* |
| TR relacionada(s) | *(pendiente Parte C — update TR-007 o TR-007-update)* |
| `superficie` | `mobile-capacitor` |
| `stackCliente` | Capacitor (`frontend/` + android/ios) |
| `modalidad` | MONO |
| `backend` | PROPIO (reutiliza APIs web Partes) |
| `repositorioPropietario` | PaqSuite-IA-Partes-Atencion |

**Plantilla regla 19:**

```text
Mobile v1 Partes-Atención: adoptar GEN-22. UI/motor = isNativeApp, createMobilePolicy, MobileConfigPanel, ConsultaKardexList/shell GEN, ProcessPageLayout.
No reimplementar motor de exclusiones ni stack Capacitor.
SoT: SPEC-001-22 + 21-mobile-v1-alcance-gen22. Host: partesMobilePolicy, mappers kardex, formulario carga (SPEC-004), ramas native por ruta de menú.
```

---

## 1. Resumen ejecutivo

- **Problema:** SPEC-007 y la implementación actual contemplan dashboard, `/partes/consulta` (kardex genérico) y un menú más amplio; la decisión de producto **v1** acota la experiencia native a **tres procesos** alineados al menú web de negocio.
- **Resultado esperado:** actualizar contrato mobile (policy, rutas, pantallas) para que Capacitor en Partes exponga solo **Carga de partes** (carga individual), **Consulta detallada** (kardex) e **Informe paquete de horas**, con exclusiones GEN y de producto §2.3, sin redefinir dominio ni backend.

---

## 2. Alcance

### 2.1 En alcance (v1)

| # | Proceso menú | `codigo` | Ruta web ref. | Comportamiento native |
|---|--------------|----------|---------------|------------------------|
| 1 | Carga diaria | `partes_carga_diaria` | `/partes/carga-diaria` | Entrada native dedicada (p. ej. `/partes/carga`): formulario/wizard **una tarea**; reglas SPEC-004; sin grilla carga-diaria web |
| 2 | Consulta detallada | `partes_consulta_detallada` | `/partes/informes/consulta-detallada` | Kardex lectura (`es_tarea = true`); mismos filtros/universo SPEC-006 §detalle; **sin** DataGrid/pivot/Emitir |
| 3 | Paquete de horas | `partes_informe_paquete_horas` | `/partes/informes/paquete-horas` | Cuenta corriente SPEC-006; cliente obligatorio donde aplique; kardex/cards; **sin** pivot |

Transversal **GEN-22 (MUST):**

- Config pre-login URL API + health; login tenant-first (`X-Paq-Cliente`).
- `createMobilePolicy` + `genMobileExclusions` + allowlist host (`partesMobilePolicy`).
- Navegación in-app; i18n `mobile.*` / `partes.*`; `data-testid` estables.
- Shell/layouts GEN (auth, menú filtrado).

**Decisión v1 — consulta detallada y CRUD:** por defecto **carga solo en proceso 1** y consulta detallada **solo lectura** en v1 (cerrar en HU/TR si se habilita CRUD desde kardex con la misma UX que carga).

**Aterrizaje post-login:** sin dashboard menú v1; primer proceso permitido o pantalla de accesos (definir en TR).

### 2.2 Fuera de alcance (v1)

| Exclusión | Referencia |
|-----------|------------|
| Dashboard `/partes` | Producto §2.3 |
| Ruta `/partes/consulta` legacy | Sustituida por consulta detallada mobile |
| Maestros, masivo, carga paquetes (compras) | SPEC-003, 005, 016 |
| Consultas agrupadas, Emitir, diseñador | SPEC-006, GEN-15 |
| Excel, admin seguridad, parámetros, tareas programadas | GEN exclusions |
| Chat, Smart Capture en carga | GEN-21 / evolutivo |
| Flutter / RN | GEN-33 / fuera SPEC-007 |

Mantener exclusiones SPEC-007 base salvo donde este update las **reemplaza** explícitamente (dashboard y consulta kardex genérica).

---

## 3. Actores

| Actor | Carga | Consulta detallada | Paquete horas |
|-------|-------|--------------------|---------------|
| Asistente | Sí | Sí (universo propio) | Sí |
| Supervisor | Sí (+ terceros no cerrados) | Sí | Sí |
| Cliente | No | Sí (org propia) | Sí (cliente fijo) |

---

## 4. Reglas numeradas

| ID | Regla |
|----|--------|
| R-MV1-01 | Mismo backend/envelope que web; solo cambia UX native. |
| R-MV1-02 | Menú native = **solo** los tres procesos §2.1 (más cambio clave si allowlist lo incluye). |
| R-MV1-03 | `partesMobilePolicy`: **permitir** `/partes/informes/consulta-detallada`; **no** permitir `/partes`, `/dashboard` como proceso operativo v1, ni `/partes/consulta` legacy. |
| R-MV1-04 | Consultas = kardex; prohibido DataGrid desktop de proceso en native. |
| R-MV1-05 | Carga individual hereda SPEC-004; cliente sin carga. |
| R-MV1-06 | Consulta detallada: `es_tarea = true`; sin Emitir en native. |
| R-MV1-07 | Paquete horas: contrato datos SPEC-006; sin pivot. |
| R-MV1-08 | Adoptar exports GEN-22; no clonar presentadores policy/kardex/config. |

---

## 5. Criterios verificables

- [ ] **CA-MV1-01** Menú native muestra únicamente los tres procesos con permiso + SPEC-002.
- [ ] **CA-MV1-02** Policy deniega dashboard, `/partes/consulta`, maestros, masivo, excel, admin, emisiones, parámetros.
- [ ] **CA-MV1-03** Carga mobile cumple validaciones SPEC-004; cliente bloqueado.
- [ ] **CA-MV1-04** Consulta detallada kardex = universo web detalle; sin pivot/emitir.
- [ ] **CA-MV1-05** Paquete horas con cliente obligatorio según rol; sin pivot.
- [ ] **CA-MV1-06** Config + login tenant + in-app OK (GEN-22).
- [ ] **CA-MV1-07** `npm run build:mobile` + `cap sync` OK; tests `partesMobilePolicy` alineados.

---

## 6. Impacto técnico (visión TR)

| Capa | Cambios esperados |
|------|-------------------|
| `partesMobilePolicy.ts` | Allowlist y exclusiones según R-MV1-03 |
| Rutas / router | Rama `isNativeApp()` en consulta detallada; retirar o no montar legacy consulta/dashboard v1 |
| FE procesos | Carga individual; kardex consulta detallada; paquete horas (reusar/adaptar existentes) |
| Menú | Filtro policy + identidad; sin ítems fuera v1 |
| Tests | `partesMobilePolicy.test.ts`; smoke mobile runbook |
| Docs | HU/TR update; manual SPEC-007 cuando cierre |

---

## 7. Riesgos y preguntas abiertas

| Tema | Estado |
|------|--------|
| CRUD en kardex consulta detallada vs solo lectura | **Default:** solo lectura v1 (cerrar en HU) |
| Ruta native exacta para carga (`/partes/carga` vs alias menú) | TR debe fijar sin duplicar reglas |
| Gráficos en paquete horas mobile | Opcional v1 |
| Delta respecto código TR-007 ya mergeado | TR update lista archivos |

---

## 8. Relación con SPEC-007 base

Este documento **no invalida** SPEC-007 en bloque: lo **acota** para la release v1 acordada. La baseline SPEC-007 sigue referencia histórica; HU/TR y código deben tomar **este update** como fuente para el circuito PIAPARTES-83.

---

## 9. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-27 | Parte A SDD — SPEC update desde `21-mobile-v1-alcance-gen22.md` (PIAPARTES-83). |