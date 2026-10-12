import { describe, expect, it } from 'vitest'
import {
  mapThemeForApiPersistence,
  normalizeThemeFromApi,
  paqsuiteDxStockBridge,
} from '@paqsuite/react-core'

describe('SDK theme bridge (GEN-19)', () => {
  it('resuelve stock DX a clave paqsuite', () => {
    expect(normalizeThemeFromApi('generic.darkviolet')).toBe('paqsuite.violet.generic')
    expect(paqsuiteDxStockBridge['paqsuite.violet.generic']).toBe('generic.darkviolet')
  })

  it('persiste clave paqsuite canónica (pin 2.4.24)', () => {
    expect(mapThemeForApiPersistence('paqsuite.orange.material')).toBe('paqsuite.orange.material')
  })
})
