import { describe, expect, it } from 'vitest'
import { catalogCodeFromDxUrl, isUnknownEmissionReportCode } from './emissionReportCatalog'

describe('emissionReportCatalog', () => {
  it('isUnknownEmissionReportCode distingue Save As de un código ya catalogado', () => {
    expect(
      isUnknownEmissionReportCode('partes.consultaDetallada.principal', [
        'partes.consultaDetallada.principal',
      ]),
    ).toBe(false)
    expect(
      isUnknownEmissionReportCode('partes.consultaDetallada.copia', [
        'partes.consultaDetallada.principal',
      ]),
    ).toBe(true)
  })

  it('catalogCodeFromDxUrl toma el último segmento', () => {
    expect(catalogCodeFromDxUrl('partes.consultaDetallada.principal')).toBe(
      'partes.consultaDetallada.principal',
    )
  })
})
