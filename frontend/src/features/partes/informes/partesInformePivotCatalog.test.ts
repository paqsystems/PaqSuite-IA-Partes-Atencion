import { describe, expect, it } from 'vitest'
import type { TFunction } from 'i18next'
import {
  buildPartesConsultaDetalladaPivotCatalog,
  buildPartesConsultasAgrupadasPivotCatalog,
  buildPartesPaqueteHorasPivotCatalog,
} from './partesInformePivotCatalog'

const t = ((key: string) => key) as TFunction

describe('partesInformePivotCatalog (GEN-12)', () => {
  it('consulta detallada expone mostrarGrillaYPivot y consultaId estable', () => {
    const catalog = buildPartesConsultaDetalladaPivotCatalog(t, 'es')
    expect(catalog.consultaId).toBe('partes.consultaDetallada')
    expect(catalog.mostrarGrillaYPivot).toBe(true)
    expect(catalog.soloPivot).toBe(false)
    expect(catalog.campos.length).toBeGreaterThan(0)
    expect(catalog.campos.every((c) => c.dataField.length > 0)).toBe(true)
  })

  it('consultas agrupadas y paquete horas usan ids de proceso distintos', () => {
    const agrupadas = buildPartesConsultasAgrupadasPivotCatalog(t)
    const paquete = buildPartesPaqueteHorasPivotCatalog(t, 'es')
    expect(agrupadas.consultaId).toBe('partes.consultasAgrupadas')
    expect(paquete.consultaId).toBe('partes.informes.paqueteHoras')
    expect(agrupadas.mostrarGrillaYPivot).toBe(true)
    expect(paquete.mostrarGrillaYPivot).toBe(true)
  })

  it('normaliza dataType numérico en campos', () => {
    const catalog = buildPartesConsultaDetalladaPivotCatalog(t, 'es')
    const duracion = catalog.campos.find((c) => c.dataField === 'duracionMinutos')
    expect(duracion?.dataType).toBe('number')
  })
})
