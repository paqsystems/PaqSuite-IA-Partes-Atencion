import { describe, expect, it } from 'vitest'
import { resolveDxReportUrl } from './resolveDxReportUrl'

describe('resolveDxReportUrl', () => {
  it('usa el código del proceso Consulta detallada (no el id numérico)', () => {
    expect(
      resolveDxReportUrl({
        processCode: 'partes.informes.consultaDetallada',
        reportId: 1,
        fallbackReportUrl: 'partes.consultaDetallada.principal',
      }),
    ).toBe('partes.consultaDetallada.principal')
  })

  it('prioriza reportCode explícito', () => {
    expect(
      resolveDxReportUrl({
        processCode: 'partes.informes.consultaDetallada',
        reportId: 1,
        reportCode: 'partes.consultaDetallada.alt',
        fallbackReportUrl: 'partes.consultaDetallada.principal',
      }),
    ).toBe('partes.consultaDetallada.alt')
  })

  it('nunca trata un id numérico puro como reportUrl', () => {
    expect(
      resolveDxReportUrl({
        processCode: 'otro.proceso',
        reportId: 99,
        fallbackReportUrl: '42',
      }),
    ).toBe('otro.proceso')
  })
})
