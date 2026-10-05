# Verificación F1 + F — Control de Calidad PQ #4 (04/10/2026)

| Campo | Valor |
|-------|--------|
| **Control** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) · CC #4 |
| **Fecha control** | 04/10/2026 |
| **Fecha verificación** | 05/10/2026 |
| **Parte I** | No ejecutada: los updates siguen abiertos hasta que el usuario marque **Finalizado** |

## TRs / HUs / SPECs cubiertos

| Update | Alcance CC #4 |
|--------|----------------|
| [TR-003-update](./TR-003-maestros-y-catalogos-update.md) · [HU-003-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-003-maestros-y-catalogos-update.md) · [SPEC-003-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-003-maestros-y-catalogos-update.md) | Código de usuario en asistentes; selector vinculable; unicidad asistente/cliente |
| [TR-006-update](./TR-006-consultas-dashboard-navegacion-update.md) · [HU-006-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-006-consultas-dashboard-navegacion-update.md) · [SPEC-006-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion-update.md) | Filtro y columnas de tipo de cliente en Paquete de Horas |

---

# F1 — Verificación del agente

## Resultado

- **Aprobado** en evidencia automática (API + Vitest del pivot). La UI no se recorrió en el navegador: no había servidor de desarrollo en marcha.

## Evidencia revisada

| AC | Evidencia | Estado |
|----|-----------|--------|
| AC-U01 | `GET /partes/asistentes?code=ADUP` devuelve `usuarioCodigo`; columna `usuarioCodigo` en `AsistentesPage` | OK |
| AC-U02 | `GET /partes/catalogos/usuarios-vinculables` omite asignados e inhabilitados; `exceptoUserId` reincorpora el de la fila. SelectBox de ambos ABM consume ese catálogo | OK |
| AC-U03 | Segundo asistente, cliente cruzado y segundo cliente con el mismo `userId` → 422 `partes.maestros.exclusividadUserId`. PUT de la misma fila → 200 | OK |
| AC-U04 | Usuario `activo = false` → 422 `partes.maestros.usuarioNoVinculable` | OK |
| AC-U05 | Movimientos de paquete traen `tipoClienteCode` / `tipoClienteDescripcion`; saldo inicial los deja vacíos | OK |
| AC-U06 | `tipoClienteId` recorta saldo inicial y movimientos al tipo pedido | OK |
| AC-U07 | `buildPaqueteHorasPivotFields` incluye ambos campos, captions i18n y no incluye `saldo` | OK |

Comandos: `php artisan test` filtrado a los casos de maestros e informe (5 pruebas, 57 aserciones) y `vitest` de `partesInformePivotFields.test.ts` (3 pruebas).

---

# F — openspec-05 (vs SPEC / HU / TR)

## Resumen ejecutivo

La implementación sigue el SPEC-update, la HU-update y la TR-update del CC #4. Completitud cubierta en API y en la UI descrita por las TR. Corrección cubierta por los tests de exclusividad, catálogo y filtro. Coherencia: camelCase en API, `NOT EXISTS` en el catálogo, filtro de tipo solo en Paquete de Horas, i18n en cinco locales.

## Completitud

| Ítem CC | Estado |
|---------|--------|
| Código de usuario en grilla de asistentes | ✓ |
| No duplicar usuario en asistente ni en cliente | ✓ |
| Selector limitado a activos, habilitados y no asignados | ✓ |
| Filtro Tipo de cliente en Paquete de Horas (web y mobile) | ✓ |
| Columnas código y descripción de tipo (grilla y pivot) | ✓ |

## Próximos pasos

1. Revisión humana de la UI en asistentes, clientes y Paquete de Horas.
2. Marcar los updates **Finalizado** cuando esa revisión cierre.
3. Parte **I** para unificar, cuando se pida.
