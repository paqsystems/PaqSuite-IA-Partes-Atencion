# SPEC-013-update – CC PQ #8 (25/10/2026) — Duración paquetes 1–240 h

## Metadatos

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-013-update-cc-pq-8-2026-10-25-duracion-paquetes |
| **Título** | Carga de paquetes — ampliar rango de duración a 1–240 horas |
| **Épica / carpeta** | `100-SistemaPartes` |
| **Estado** | Pendiente (**Parte A** + **A1** cerradas 2026-10-30) |
| **A1** | [A1-ambiguity-review](./SPEC-013-update-cc-pq-8-2026-10-25-duracion-paquetes-A1-ambiguity-review.md) — **Cerrado** |
| **Última actualización** | 2026-10-30 |
| **SPEC base** | [SPEC-013-carga-paquetes-horas](../../100-SistemaPartes/SPEC-013-carga-paquetes-horas.md) |
| **HU-update** | [HU-013-update-cc-pq-8-2026-10-25-duracion-paquetes](../../../03-historias-usuario/updates/100-SistemaPartes/HU-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) |
| **TR-update** | [TR-013-update-cc-pq-8-2026-10-25-duracion-paquetes](../../../04-tareas/updates/100-SistemaPartes/TR-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) |
| **Origen** | `00-ControlCalidad-PQ` · CC **#8** · 25/10/2026 · [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52) |
| **Producto** | [`16-carga-paquetes-horas.md`](../../../02-producto/Sistema-Partes-IA/16-carga-paquetes-horas.md) (actualizar tramo en Parte I) |

---

## 1. Resumen ejecutivo

- **Problema:** el CC PQ #8 corrige el piso de duración de la **carga de paquetes de horas**: debe aceptar compras desde **1 hora** (no 8), manteniendo el techo **240 horas** enteras.
- **Resultado esperado:** mismo proceso SPEC-013 (grilla + modal + Smart Capture), con validación coherente en UI, API/SP, SC, i18n y tests.

---

## 2. Alcance

### 2.1 En alcance

- Cambiar regla de **duración** en captura de paquetes (`es_tarea = false`): horas enteras **1 … 240 inclusive**.
- Persistencia: `duracion_minutos` múltiplo de 60, rango **60 … 14400**.
- Constantes y helpers FE (`partesPaqueteDuration.ts`), validación persist modal, API `POST/PUT /api/v1/partes/paquetes`, SP `pq_sp_partes_paquete_upsert` (y list/get si validan rango).
- Smart Capture paquete: parser de duración y mensajes de rechazo alineados.
- i18n claves de rango (`partes.paquete.validation.*`) en **es, en, pt, fr, it**.
- Tests unitarios existentes (`partesPaqueteDuration.test.ts`, `cargaPaquetesPersistValidation.test.ts`) y Feature API límites.
- **Delta documental** sobre SPEC-013 §2.1 y §4.3 (sustituir «8 y 240» por «1 y 240»).

### 2.2 Fuera de alcance

- Informe **Paquete de Horas** (SPEC-006-update hermano CC #8).
- Carga diaria / tramos 15 min (SPEC-004).
- Cambio de unidad (se mantienen horas enteras, no decimales en UI paquete).
- Mobile carga paquetes (sigue fuera SPEC-013).

---

## 3. Actores y contexto

| Actor | Efecto |
|-------|--------|
| Supervisor | Único operador del ABM paquetes; puede registrar compras desde 1 h. |
| Informe / clientes | Consumen minutos ya persistidos; no cambia convención suma/resta. |

**Precondiciones:** SPEC-013 operativo; tipo default y clientes usables (SPEC-003).

**Estado actual (referencia D):** FE fija `PAQUETE_MIN_HORAS = 8` en `partesPaqueteDuration.ts`; tests asumen 8–240.

---

## 4. Comportamiento funcional

### 4.1 Regla de duración (delta normativo)

| Tema | Antes (SPEC-013 base) | Después (este update) |
|------|------------------------|------------------------|
| Horas enteras permitidas | 8 … 240 | **1 … 240** |
| Minutos persistidos | 480 … 14400 | **60 … 14400** |
| Step UI | 1 hora | 1 hora (sin cambio) |
| Decimales / fracciones | Rechazados | Rechazados |

### 4.2 Validación por capa

1. **Modal / SelectBox horas:** opciones generadas con `buildPaqueteDuracionOptions` desde **1** hasta **240**; **default en alta = 1 h** (A1 Q2).
2. **API:** rechazo **422** con clave i18n si `duracionMinutos` ∉ [60, 14400] o no múltiplo de 60.
3. **SP upsert:** misma regla (defensa en profundidad; regla 74).
4. **Smart Capture:** si el turno propone horas fuera de rango, reply de error SC sin aplicar al draft.

### 4.3 Reglas numeradas (delta)

| ID | Regla |
|----|--------|
| R-PH-DUR-01 | Paquete Must aceptar **1 h** como mínimo (CC #8 CA-01). |
| R-PH-DUR-02 | Máximo **240 h** sin cambio (CC #8 CA-01). |
| R-PH-DUR-03 | Misma regla en UI, API, SP, SC e i18n (CC #8 CA-02). |
| R-PH-DUR-04 | Edición de paquete existente con duración &lt; 8 h (cargado tras el fix) Must seguir editable sin forzar rescale. |

### 4.4 Relación con informe

No altera signos ni columnas del informe (SPEC-006 §4.3b). Paquetes de 1 h restan 60 minutos en cuenta corriente igual que paquetes mayores.

---

## 5. Criterios verificables

- [ ] **CA-01** (CC #8) Acepta 1 h y 240 h; rechaza 0 h, 241 h y valores no enteros en horas.
- [ ] **CA-02** (CC #8) Consistencia UI + API/SP + SC + i18n + tests automatizados.
- [ ] Regresión: paquetes 8–240 h siguen válidos.
- [ ] OpenAPI / mensajes envelope usan claves `partes.paquete.*`, no prosa hardcoded.

---

## 6. Impacto técnico (visión TR)

| Pieza | Acción |
|-------|--------|
| `frontend/.../partesPaqueteDuration.ts` | `PAQUETE_MIN_HORAS = 1`; opciones 1…240 |
| `CargaPaquetesPage` / modal | Sin cambio de forma; usa helpers |
| SC paquete | Validar rango en servicio turno |
| `PartesPaqueteController` + FormRequest | Rules min/max minutos |
| SP `pq_sp_partes_paquete_upsert` | CHECK o RAISERROR rango |
| Tests | Actualizar expectativas 8→1 en tests de límite inferior |
| Producto `16-carga-paquetes-horas.md` | Alinear D-PH tramo (Parte I) |

---

## 7. Riesgos y supuestos

- **Supuesto:** no hay paquetes legacy &lt; 8 h en producción; si existieran, ya serían inválidos bajo regla anterior — no requiere migración de datos.
- **Riesgo bajo:** SC entrenado con ejemplos «8 horas» — actualizar hints i18n opcionalmente.

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-30 | Parte G volcado inicial |
| 2026-10-30 | **Parte A** — SPEC-update completo (CC #8 ítem duración) |
| 2026-10-30 | **A1** — rango 1–240; default 1 h; SP upsert |
