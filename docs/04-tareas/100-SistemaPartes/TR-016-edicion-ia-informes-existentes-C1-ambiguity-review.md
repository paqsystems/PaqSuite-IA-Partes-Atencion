# TR-016 — Revisión de ambigüedad (Parte C1)

| Campo | Valor |
|-------|-------|
| TR | [TR-016-edicion-ia-informes-existentes-prompt-to-report](./TR-016-edicion-ia-informes-existentes-prompt-to-report.md) |
| HU | [HU-016-ia-edicion-informes-existentes](../../03-historias-usuario/100-SistemaPartes/HU-016-ia-edicion-informes-existentes.md) |
| SPEC | [SPEC-016-prompt-to-report-edicion-informes](../../05-open-spec/100-SistemaPartes/SPEC-016-prompt-to-report-edicion-informes.md) |
| Fecha | 2026-11-06 (ART) |
| **Veredicto** | **Apto con observaciones** |
| Jira | [PIAPARTES-60](https://paqsystems.atlassian.net/browse/PIAPARTES-60) |

## Resultado general

- Estado: **Apto con observaciones**

## Ambigüedades críticas

- Ninguna que bloquee D1: la TR obliga resultado funcional (modificación real del REPX cargado) y camino técnico abierto en T1/T2 sin contradicción con SPEC/HU.

## Ambigüedades menores

- **CA-016-08 / degradación:** la TR permite “comportamiento DX o doc mínima” si DevExpress no expone mensaje; conviene en D1 concretar qué ve el operador (banner, icono deshabilitado, o solo runbook) para pasar F1 sin discusión.
- **Preservación layout (CA-016-03):** “diff controlado de REPX” no define herramienta; en evidencia F1 puede bastar before/after + descripción explícita de zonas no tocadas.
- **RN-TR-16-05:** “endpoint nuevo en sidecar” es vago; si T2 no agrega REST, omitir la regla en implementación o acotarla a health en DoD.

## Contradicciones TR ↔ HU ↔ SPEC

- Ninguna detectada: TR ⊆ HU ⊆ SPEC en alcance, permisos y prohibición de persistencia paralela.

## Supuestos detectados

- T1 confirmará si Prompt-to-Report web modifica reporte ya abierto o si hace falta composición; la TR ya prevé alternativa Framework/GEN (alineado SPEC-016 R-016-05).
- E2E automatizado puede quedar fuera si depende de LLM (TR §8); coherente con dependencias declaradas.

## Preguntas para decisión humana

1. ¿Criterio mínimo de “degradación visible” sin IA: solo manual DX o también mensaje en UI host? (afecta T4/T6)
2. Si T2 añade código solo en Framework, ¿el PR de cierre es dual repo o tarball/pin documentado? (operación release, no bloque funcional)

## Recomendaciones de ajuste de la TR

- En D1/F1: una línea de verificación explícita para CA-016-08 (qué observa el usuario sin credencial LLM).
- En evidencia: nombrar archivo o plantilla `D-VERIFICACION-TR-016-*.md` con checklist copiable del §2 de la TR.

## Veredicto

- **Puede pasar a D1/D: Sí** (con observaciones no bloqueantes arriba)
