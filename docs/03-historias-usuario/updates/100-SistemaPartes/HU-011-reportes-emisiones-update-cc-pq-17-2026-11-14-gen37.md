# HU-011-update — GEN-37 labels en schemas de emisiones

Jira: [PIAPARTES-25](https://paqsystems.atlassian.net/browse/PIAPARTES-25)

## Origen

| Campo | Valor |
|-------|--------|
| Control | `00-ControlCalidad-PQ` · CC #17 · **14/11/2026** |
| SPEC-update | [SPEC-011-update GEN-37](../../../05-open-spec/updates/100-SistemaPartes/SPEC-011-update-cc-pq-17-2026-11-14-gen37-catalogo-semantico.md) |
| HU base | [HU-011](../../100-SistemaPartes/HU-011-reportes-emisiones.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente de Revisión |

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-011-update-cc-pq-17-gen37 |
| Título | Como diseñador quiero ver nombres humanos de campos en el Field List (GEN-37) |
| Estado | Pendiente de Revisión |
| Clasificación | MUST-HAVE |

## Narrativa

Como usuario con `emission.design`, al abrir el diseñador DX de Consulta detallada o Paquete de horas, quiero que el Field List muestre **etiquetas humanas** (`label`) y no solo identificadores técnicos, alineado a GEN-37 sobre el mismo JSON del diseñador.

## Criterios de aceptación

1. Todo `fields[]` de ambos schemas emisibles tiene `label` ES no vacío y distinto del `name` (salvo código ya legible documentado).
2. Opcional: `captionKey` apuntando a i18n producto (5 locales) sin tocar GEN-14.
3. `fields[].name` idénticos a port-names / puerto (sin cambio de wire).
4. Manifiesto regenerado; validate V-EM-04 OK.
5. Sin catálogo paralelo ni labels hardcodeados en sidecar del host.

## Fuera de alcance

description/synonyms IA → HU-015-update GEN-37.
