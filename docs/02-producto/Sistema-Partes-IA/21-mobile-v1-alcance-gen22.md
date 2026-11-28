# Mobile v1 — Partes-Atención (adopción GEN-22)

## Objetivo

Registrar la **decisión de producto** para la **primera versión mobile** del host **PaqSuite-IA-Partes-Atencion**: experiencia **Capacitor** (mismo `frontend/` + `android/` / `ios/`), sobre el **mismo backend** y las mismas reglas de dominio que web, con un menú operativo **reducido** a tres procesos de negocio.

Este documento es **definición conceptual de producto**. No reemplaza SPEC/HU/TR; alimenta la actualización de [SPEC-007](../../05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md) y el circuito SDD cuando corresponda.

Complementa [`10-mobile.md`](./10-mobile.md) (visión amplia) y acota explícitamente el **MVP v1** respecto de lo ya especificado en SPEC-007 (dashboard, consulta kardex en ruta `/partes/consulta`, chat, etc.).

---

## 1. Qué se reutiliza (GEN-22) y qué define Partes

**Norma Framework:** [`PaqSuite-IA-FRAMEWORK/docs/02-producto/22-experiencia-mobile.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/22-experiencia-mobile.md) · OpenSpec [`SPEC-001-22`](../../../PaqSuite-IA-FRAMEWORK/docs/05-open-spec/001-Generalidades/SPEC-001-22-experiencia-mobile.md).

| Capa | Responsabilidad |
|------|-----------------|
| **Framework (GEN-22)** | `isNativeApp`, `createMobilePolicy` / `genMobileExclusions`, `MobileConfigPanel`, layouts auth/shell GEN, patrón consultas **kardex**, login **tenant-first**, navegación in-app |
| **Partes (host)** | Allowlist de rutas v1, mappers dominio → kardex, formulario/wizard de **carga individual** (mismas reglas que carga diaria web), ramas `isNativeApp()` por proceso, i18n `partes.*` / `mobile.*`, `partesMobilePolicy` |

Plantilla de adopción (regla GEN del host):

```text
Mobile v1 de Partes-Atención: adoptar GEN-22. UI/motor = exports react-core (policy, config, kardex, shell).
No reimplementar stack Capacitor ni motor de exclusiones GEN.
SoT: SPEC-001-22 + 22-experiencia-mobile. Host: allowlist 3 procesos, SPEC-002/004/006 por proceso.
```

**Anti-patrón:** copiar presentadores kardex, policy engine o pantalla de config desde otro producto; duplicar login sin layouts GEN-04/GEN-01.

---

## 2. Decisión v1 — alcance de menú

### 2.1 Procesos incluidos (único menú operativo MVP)

Solo estos tres procesos deben ser **visibles y navegables** en native v1 para usuarios con permiso (más filtro de identidad SPEC-002):

| # | Nombre de negocio | Ítem menú (`text`) | `codigo` / permiso | Ruta web (referencia) | Rol en mobile v1 |
|---|-------------------|--------------------|--------------------|------------------------|------------------|
| 1 | **Carga de Partes** (carga diaria de tareas) | Carga diaria (rama **Partes**) | `partes_carga_diaria` | `/partes/carga-diaria` | Formulario / wizard de **una tarea**; mismas validaciones que [`05-operacion-diaria-y-supervision.md`](./05-operacion-diaria-y-supervision.md) y SPEC-004. **Sin** grilla desktop ni proceso masivo. |
| 2 | **Informe consulta detallada** | Consulta detallada (rama **Informes**) | `partes_consulta_detallada` | `/partes/informes/consulta-detallada` | Consulta en **kardex** (lectura); universo `es_tarea = true` y delimitación por rol como web ([`06-consultas-dashboard-y-navegacion.md`](./06-consultas-dashboard-y-navegacion.md)). **Sin** DataGrid desktop, **sin** pivot, **sin** Emitir (GEN-15). |
| 3 | **Informe paquete de horas** | Paquete de horas | `partes_informe_paquete_horas` | `/partes/informes/paquete-horas` | Cuenta corriente de horas (tareas + compras); cliente obligatorio donde aplique SPEC-006. Presentación kardex/cards; **sin** pivot. |

La carpeta de menú **Inicio → Dashboard** (`partes_dashboard`, `/partes`) **no** forma parte del MVP v1 acordado: el usuario entra al shell y accede por menú solo a los tres procesos anteriores (o aterrizaje acordado en TR: p. ej. primer ítem permitido o pantalla de accesos directos).

### 2.2 Capacidades transversales (obligatorias GEN, no cuentan como “cuarto proceso”)

| Capacidad | Norma |
|-----------|--------|
| Pantalla pre-login + **configuración URL API** + test health | GEN-22 §2; tenant **no** en engranaje |
| Login **empresa + usuario + contraseña** (`empresa` → `X-Paq-Cliente` en MONO) | [`10-mobile.md`](./10-mobile.md) · SPEC-002 |
| Cambio de contraseña | Ruta shell permitida si el producto la expone en allowlist |
| Gate Partes (`resultado.partes`) | SPEC-002; cliente sin carga de partes |

### 2.3 Fuera de alcance v1 (explícito)

Además de las **exclusiones GEN** (pivot, Excel, admin seguridad, `openInNewTab`, parámetros, tareas programadas, bitácora admin, etc.):

| Exclusión v1 Partes | Nota |
|---------------------|------|
| Dashboard inicio (`/partes`) | No es proceso del menú v1 |
| Ruta legacy `/partes/consulta` (kardex genérico TR-007 anterior) | Sustituida por **consulta detallada** mobile en ruta de informe |
| Maestros (`/archivos/partes/*`) | SPEC-003 web |
| Proceso masivo, carga de paquetes (compras) | SPEC-005 / [`16-carga-paquetes-horas.md`](./16-carga-paquetes-horas.md) |
| Consultas agrupadas, diseñador / Emitir | SPEC-006 / GEN-15 |
| Importación Excel | SPEC-009 / GEN-14 |
| Chat asistente | GEN-21; no requerido en v1 Partes |
| Smart Capture en modal de carga | Evolutivo; no bloqueante v1 |

Estas exclusiones **no** niegan la existencia en web; delimitan la **primera entrega mobile** en este host.

---

## 3. Comportamiento por proceso (producto)

### 3.1 Carga de Partes (carga individual)

- **Quién:** asistente y supervisor (no cliente).
- **Qué:** alta/edición de **una** tarea por flujo (`es_tarea = true`); supervisor puede elegir asistente propietario.
- **Reglas:** heredar SPEC-004 (cliente usable, tipo en universo, duración tramo, observación obligatoria, marcas, `cerrado`, optimistic lock).
- **UX mobile:** wizard o formulario en popup/pantalla secundaria; no replicar la grilla de carga diaria web.
- **IA / Smart Capture:** fuera de v1 salvo decisión posterior documentada en update.

### 3.2 Informe consulta detallada

- **Qué muestra:** mismas dimensiones funcionales que la consulta detallada web (asistente, cliente, fecha, tipo, duración `hh:mm`, marcas, observación, ERP cliente/artículo cuando existan), con filtro `es_tarea = true`.
- **Filtros:** periodo, clientes y asistentes dentro del universo del rol (SPEC-002 / SPEC-006); periodo por defecto = **día actual** salvo TR que alinee con web.
- **Presentación:** kardex (tarjetas + detalle); paginación o scroll; pull-to-refresh.
- **Escritura desde consulta:** opcional en v1; si se incluye CRUD en kardex, debe ser la **misma** UX de formulario que §3.1 (sin dos pantallas divergentes). Si v1 es solo lectura, documentar en TR y dejar carga solo en proceso 1.
- **Emisión:** prohibida en native (coherente [`15-reportes-emisiones.md`](./15-reportes-emisiones.md)).

### 3.3 Informe paquete de horas

- **Qué muestra:** movimientos de cuenta corriente (tareas y compras), saldo inicial, columna saldo en lectura, cliente obligatorio para roles que web exige (SPEC-006).
- **Presentación:** kardex/listas y resúmenes compactos; **sin** pivot; sin Emitir.
- **No confundir** con el informe “simplificado por dashboard” de SPEC-007 anterior: v1 usa el **proceso de menú** Paquete de horas y el contrato de datos del informe web, adaptado a kardex.

---

## 4. Navegación y policy del host

| Tema | Decisión v1 |
|------|-------------|
| Motor | `createMobilePolicy` + `genMobileExclusions` (GEN-22) |
| Allowlist producto | Solo rutas de los §3 + rutas shell mínimas (`/change-password`, etc.) acordadas en TR |
| Menú API | Filtrar con policy; ocultar raíz Inicio/Dashboard, Archivos, resto de Informes, Seguridad, Parámetros |
| Deep-link a ruta excluida | Patrón GEN: redirección home + mensaje i18n de exclusión |
| `openInNewTab` | Prohibido |

**Impacto esperado en código (para TR):** realinear `partesMobilePolicy` respecto del estado actual (allowlist con `/dashboard`, `/partes/consulta` y exclusión de `/partes/informes/consulta-detallada`) hacia esta definición v1.

---

## 5. Actores

| Actor | Carga de Partes | Consulta detallada | Paquete de horas |
|-------|-----------------|--------------------|------------------|
| Asistente | Sí (propias tareas) | Sí (universo propio) | Sí (según menú) |
| Supervisor | Sí (+ terceros no cerrados) | Sí (universo supervisor) | Sí |
| Cliente | No | Sí (solo su organización) | Sí (solo su organización, cliente fijo) |

---

## 6. Relación con documentos existentes

| Documento | Relación |
|-----------|----------|
| [`10-mobile.md`](./10-mobile.md) | Visión amplia; v1 **recorta** dashboard, consulta genérica y chat del menú operativo |
| [SPEC-007](../../05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md) | Debe **actualizarse** (update o nueva versión) para reflejar §2.1–2.3 y rutas policy |
| [`15-reportes-emisiones.md`](./15-reportes-emisiones.md) | Emitir y diseñador siguen **web** |
| [`16-carga-paquetes-horas.md`](./16-carga-paquetes-horas.md) | Carga de compras sigue **web**; informe paquete sí en v1 mobile |

---

## 7. Criterios de aceptación de producto (v1)

- [ ] **CA-MV1-01** Tras login Partes, el menú native muestra **solo** (o prioritariamente) los tres procesos §2.1, según permisos y SPEC-002.
- [ ] **CA-MV1-02** No hay acceso native a dashboard, maestros, masivo, pivot, Excel, admin, emisiones ni parámetros.
- [ ] **CA-MV1-03** Carga de partes mobile respeta SPEC-004; cliente no accede.
- [ ] **CA-MV1-04** Consulta detallada mobile usa kardex y el mismo universo filtrado que web (`es_tarea = true`); sin Emitir.
- [ ] **CA-MV1-05** Paquete de horas mobile respeta obligatoriedad de cliente y reglas de saldo de SPEC-006; sin pivot.
- [ ] **CA-MV1-06** Config URL + login tenant-first + navegación in-app cumplen GEN-22.
- [ ] **CA-MV1-07** `npm run build:mobile` y smoke Capacitor documentado en runbook del producto.

---

## 8. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| SPEC-007 ya implementado con otro allowlist | Este documento **prevalece** como decisión de producto v1; TR debe listar delta (policy, rutas, pantallas). |
| Consulta detallada con CRUD en kardex vs solo lectura | Cerrar en HU/TR v1; por defecto carga en proceso 1 y consulta detallada **lectura** si se busca mínimo viable. |
| Gráficos en paquete de horas mobile | Opcional v1; no condiciona la inclusión del proceso. |
| Release / tag | Convención producto (ej. `vX.Y.Z-mobile`) cuando F1 dispositivo cierre el alcance v1. |

---

## 9. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-27 | Decisión: primera mobile en Partes-Atención; menú v1 = Carga de Partes + Consulta detallada + Paquete de horas. Documento base producto (GEN-22). |

---

**Siguiente paso metodológico sugerido:** update de SPEC-007 (Parte A) → alineación HU/TR → D1/D en `partesMobilePolicy`, rutas y presentadores kardex por proceso.