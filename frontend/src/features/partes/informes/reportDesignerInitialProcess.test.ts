import { describe, expect, it } from 'vitest'
import { readInitialProcessCodeFromSearch } from './reportDesignerInitialProcess'

describe('readInitialProcessCodeFromSearch', () => {
  it('devuelve processCode del query', () => {
    expect(readInitialProcessCodeFromSearch('?processCode=partes.informes.consultaDetallada')).toBe(
      'partes.informes.consultaDetallada',
    )
  })

  it('trim y vacío → undefined', () => {
    expect(readInitialProcessCodeFromSearch('?processCode=%20')).toBeUndefined()
    expect(readInitialProcessCodeFromSearch('')).toBeUndefined()
    expect(readInitialProcessCodeFromSearch(new URLSearchParams())).toBeUndefined()
  })
})
