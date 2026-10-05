import { describe, expect, it } from 'vitest'
import { shouldShowPageListError } from './FormContextErrorAlert'

describe('shouldShowPageListError', () => {
  it('oculta error de listado con modal abierto', () => {
    expect(shouldShowPageListError(true, 'falló')).toBe(false)
  })

  it('muestra error de listado sin modal', () => {
    expect(shouldShowPageListError(false, 'falló')).toBe(true)
  })

  it('no muestra sin mensaje', () => {
    expect(shouldShowPageListError(false, null)).toBe(false)
  })
})
