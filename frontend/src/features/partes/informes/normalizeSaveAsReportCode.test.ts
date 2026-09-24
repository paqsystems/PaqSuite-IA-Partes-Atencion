import { describe, expect, it } from 'vitest'
import {
  isKnownReportCode,
  normalizeSaveAsReportCode,
  slugifyReportSegment,
} from './normalizeSaveAsReportCode'

describe('normalizeSaveAsReportCode', () => {
  it('antepone el prefijo del proceso a un nombre libre (Save As)', () => {
    expect(normalizeSaveAsReportCode('partes.informes.consultaDetallada', 'Variante B')).toBe(
      'partes.consultaDetallada.variante-b',
    )
  })

  it('no altera un código que ya trae el prefijo', () => {
    expect(
      normalizeSaveAsReportCode(
        'partes.informes.consultaDetallada',
        'partes.consultaDetallada.principal',
      ),
    ).toBe('partes.consultaDetallada.principal')
  })

  it('detecta códigos conocidos sin importar mayúsculas', () => {
    expect(
      isKnownReportCode('Partes.ConsultaDetallada.Principal', [
        'partes.consultaDetallada.principal',
      ]),
    ).toBe(true)
    expect(isKnownReportCode('partes.consultaDetallada.nuevo', ['partes.consultaDetallada.principal'])).toBe(
      false,
    )
  })

  it('slugify limpia caracteres inválidos', () => {
    expect(slugifyReportSegment('Mi Reporte!!')).toBe('mi-reporte')
  })
})
