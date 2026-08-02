# F-GEN-08 — Cierre revisión C1 (epic Pivots)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-001-08-pivots](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md) |
| **Fecha** | 2026-06-11 |
| **Alcance** | Revisión C1 de las 4 TR-GEN-08-* |
| **Veredicto** | **Apto con observaciones** — las 4 TR pueden pasar a **D1** |

## Resultado por TR

| TR | Estado C1 | Bloqueantes D1 |
|----|-----------|----------------|
| [TR-GEN-08-motor-metadata-pivots](TR-GEN-08-motor-metadata-pivots.md) | Apto con observaciones | Ninguno |
| [TR-GEN-08-pivotgrid-visualizacion](TR-GEN-08-pivotgrid-visualizacion.md) | Apto con observaciones | Ninguno |
| [TR-GEN-08-layouts-pivot](TR-GEN-08-layouts-pivot.md) | Apto con observaciones | Ninguno |
| [TR-GEN-08-exportacion-pivot](TR-GEN-08-exportacion-pivot.md) | Apto con observaciones | Ninguno |

## Decisiones transversales cerradas en C1

| Tema | Decisión |
|------|----------|
| Base de datos catálogo | Tablas `pq_pivots_*` en **BD tenant** SistemaPartes (desviación documentada vs MONO Dictionary DB). |
| Permisos API | `procedimiento_host` en `pq_pivots_consultas` → `Permiso_Repo` del proceso host. |
| Agregación | Dataset plano en API; PivotGrid agrega en cliente. |
| Convivencia grilla/pivot | `tipo_proceso === 'informe'` **o** `mostrarGrillaYPivot`; seed Informes ajustado al activar epic. |
| Diseños guardados | Paridad TR-GEN-03-layouts-grilla; prefijo i18n `pivotLayout.*`; sin límite por consulta. |
| Export pivot | Client-side; oculto en modo grilla; paridad guardado archivo TR-GEN-03. |
| Flags infra `config/public` | `pivotsEnabled`, `pivotLayoutsEnabled` default **false** hasta deploy epic. |

## Orden D1 recomendado

```text
1. TR-GEN-08-motor-metadata-pivots
2. TR-GEN-08-pivotgrid-visualizacion
3. TR-GEN-08-layouts-pivot
4. TR-GEN-08-exportacion-pivot
```

## Matriz permisos — filas previstas (aplicar en D1)

| Método | Path | Permiso |
|--------|------|---------|
| GET | `/api/v1/pivots/consultas/{consultaId}/metadata` | `Permiso_Repo` en `procedimiento_host` |
| POST | `/api/v1/pivots/consultas/{consultaId}/data` | Idem |
| POST | `/api/v1/pivots/consultas/{consultaId}/validate-structure` | Idem |
| GET/POST/PUT/DELETE | `/api/v1/pivot-configs*` | Idem (usuario autenticado + permiso consulta; PUT/DELETE solo creador) |

**Estado:** aplicada en [matriz-permisos-mvp.md](matriz-permisos-mvp.md) § Pivots (2026-06-11).

## Fuera de alcance confirmado

- MVP portal release actual (epic posterior).
- `pq_pivots_aud` en D1 v1.
- Export pivot server-side.
- Dictionary DB como segunda conexión.

## Próximo paso

~~**D1** por TR~~ **Completado.** Ver [F-GEN-08-cierre-formal](F-GEN-08-cierre-formal.md).






