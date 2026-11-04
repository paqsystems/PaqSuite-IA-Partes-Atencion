# Verificación F — CC PQ #15 (04/11/2026)

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#15** |
| **Parte D** | [D-CC-PQ-15-2026-11-04.md](./D-CC-PQ-15-2026-11-04.md) |
| **Parte E** | [E-CC-PQ-15-2026-11-04.md](./E-CC-PQ-15-2026-11-04.md) |
| **Plan tests** | [D-VERIFICACION-005-update-masivo-informar-clientes.md](./D-VERIFICACION-005-update-masivo-informar-clientes.md) |
| **Veredicto F** | **Apto** — F1 código ↔ TR OK + regresión automatizada OK |
| **F1/F** | **Aprobado** 04/11/2026 |
| **Parte I** | 04/11/2026 — fusionado en SPEC/HU/TR-005 base |

---

## 1) Criterios de aceptación

| CA | Resultado F | Evidencia |
|----|-------------|-----------|
| **CA-15-01** Ícono informar clientes | **OK** | `ProcesoMasivoPage` + testid; Feature |
| **CA-15-02** Empresa emisora | **OK** | Notification + `resolveEmpresaNombre` |
| **CA-15-03** URL tenant + código; sin password | **OK** | `PartesMasivoAccessUrlBuilder` + cuerpo mail |
| **CA-15-04** Locale destinatario (5) | **OK** | `PartesMasivoLocaleTest` + `lang/*/partes_masivo.php` |
| **CA-15-05** Destinatarios universo filtrado | **OK** | SP + Feature preview/envío |
| **CA-15-06** Guard salida | **OK** | `useMasivoLeaveGuard` + Vitest dirty; smoke UI manual opcional post-deploy |
| **CA-15-07** Plantilla v1 i18n | **OK** | Sin UI editor; claves fijas |

---

## 1.1) F1 — código ↔ TR

| Pieza | Estado |
|-------|--------|
| POST/GET informar-clientes | **OK** |
| SP destinatarios JOIN | **OK** |
| Mail i18n + URL | **OK** |
| Guard FE | **OK** |
| Tests E | **OK** (12 PHPUnit + 15 Vitest) |

**Veredicto F1:** **Aprobado**.

---

## 2) Regresión automatizada

| Capa | Resultado |
|------|-----------|
| PHPUnit masivo / informar / locale | **OK** (12) |
| Vitest masivo dirty / paging / selection | **OK** (15) |

---

## 3) Smoke manual UI

Opcional post-deploy preview: ícono mail → confirmación N → log mail; dirty → diálogo leave. No bloquea F1/F con evidencia automatizada + code review.
